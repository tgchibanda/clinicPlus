<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Prescription;
use App\Models\Drug;
use Illuminate\Http\Request;
use App\Models\InsuranceSubscription;
use Carbon\Carbon;
use App\Models\PolicyClaim;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('patient', 'pharmacist', 'items.drug')
            ->latest()->paginate(15);
        return response()->json([
            "success" => true,
            "message" => "Sales Details retrieved successfully.",
            "data" => $sales
        ], 200);
    }

    public function create(Prescription $prescription = null)
    {
        $prescription->load('patient', 'items.drug');
        $drugs = Drug::where('stock_quantity', '>', 0)->get();
        return view('sales.create', compact('prescription', 'drugs'));
    }

    public function store(Request $request)
{
    // validate
    $validated = $request->validate([
        'pharmacist_id'   => 'required|exists:users,id',
        'patient_id'      => 'required|exists:patients,id',
        'prescription_id' => 'nullable|exists:prescriptions,id',
        'items'           => 'required|array|min:1',
        'items.*.drug_id' => 'required|exists:drugs,id',
        'items.*.quantity' => 'required|integer|min:1',
        // optional: payments and policy_claim are validated below
    ]);

    // Extract payments and policy_claim safely
    $paymentsInput = $request->input('payments', []); // array of {method, amount, description}
    $policyClaimInput = $request->input('policy_claim', null);

    DB::beginTransaction();

    try {
        // 1) Calculate totals and validate stock
        $totalAmount = 0;
        $saleItems = [];
        foreach ($validated['items'] as $itemData) {
            $drug = Drug::findOrFail($itemData['drug_id']);
            if ($drug->stock_quantity < $itemData['quantity']) {
                throw new \Exception("Insufficient stock for {$drug->name}");
            }
            $itemTotal = $drug->selling_price * $itemData['quantity'];
            $totalAmount += $itemTotal;
            $saleItems[] = [
                'drug_id' => $itemData['drug_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $drug->selling_price,
                'total_price' => $itemTotal,
            ];
        }

        // Determine consultation id (if prescription provided)
        $consultationId = null;
        if (!empty($validated['prescription_id'])) {
            $prescription = Prescription::find($validated['prescription_id']);
            if ($prescription) {
                $consultationId = $prescription->consultation_id ?? null;
            }
        }

        // 2) Handle policy_claim (if provided)
        $policyClaimRecord = null;
        if ($policyClaimInput && isset($policyClaimInput['subscription_id']) && isset($policyClaimInput['amount'])) {
            $subscriptionId = $policyClaimInput['subscription_id'];
            $amount = (float)$policyClaimInput['amount'];

            $subscription = InsuranceSubscription::find($subscriptionId);
            if (!$subscription) {
                throw new \Exception("Subscription not found for policy_claim.subscription_id={$subscriptionId}");
            }

            // Normalize DOB to Y-m-d or null
            $dob = null;
            $dobRaw = $policyClaimInput['claim_for']['date_of_birth'] ?? null;
            if ($dobRaw) {
                try {
                    $dob = \Carbon\Carbon::parse($dobRaw)->toDateString();
                } catch (\Exception $e) {
                    $dob = null;
                }
            }

            // create policy claim - link to consultation if available
            $policyClaimRecord = PolicyClaim::create([
                'subscription_id' => $subscription->id,
                'consultation_id' => $consultationId,
                'claim_holder_first_name' => $policyClaimInput['claim_for']['first_name'] ?? null,
                'claim_holder_last_name'  => $policyClaimInput['claim_for']['last_name'] ?? null,
                'claim_holder_dob'        => $dob,
                'claim_holder_relationship' => $policyClaimInput['claim_for']['relationship'] ?? null,
                'amount' => $amount,
                'note' => $policyClaimInput['note'] ?? null,
                'status' => 'processed',
                'claim_category' => 'Medication'
            ]);

            // Record the policy payment entry in medication_payments (insurance payment)
            DB::table('medication_payments')->insert([
                'consultation_id' => $consultationId,
                'amount' => (float)$policyClaimRecord->amount,
                'payment_method' => 'policy_claim',
                'transaction_ref' => 'policy_claim#' . $policyClaimRecord->id,
                'insurance_id' => $policyClaimRecord->subscription_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        // 3) Handle explicit payments array (payload.payments)
        // We'll accumulate payment records to insert and also determine what to set as sale.payment_method
        $paymentRecords = []; // will be inserted into medication_payments
        $paymentMethodsSeen = []; // track methods to decide sale.payment_method
        if (is_array($paymentsInput)) {
            foreach ($paymentsInput as $p) {
                $method = $p['method'] ?? 'cash';
                $amount = isset($p['amount']) ? (float)$p['amount'] : 0.0;
                $description = $p['description'] ?? null;

                if ($amount <= 0) continue;

                $paymentRecords[] = [
                    'consultation_id' => $consultationId,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'transaction_ref' => $description,
                    'insurance_id' => null,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];

                $paymentMethodsSeen[] = $method;
            }
        }

        // 4) Insert sale (choose payment_method from payments or policy)
        // Decide sale.payment_method:
        // - if policy claim exists and no explicit payments => 'policy_claim'
        // - if single payment method seen => use that method
        // - if multiple => 'multiple'
        $salePaymentMethod = null;
        if ($policyClaimRecord && empty($paymentMethodsSeen)) {
            $salePaymentMethod = 'policy_claim';
        } elseif (count(array_unique($paymentMethodsSeen)) === 1) {
            $salePaymentMethod = $paymentMethodsSeen[0];
        } elseif (count(array_unique($paymentMethodsSeen)) > 1) {
            $salePaymentMethod = 'multiple'; // fallback; ensure DB accepts this OR change to allowed value
        } else {
            // no explicit payments and no policy claim -> default to cash
            $salePaymentMethod = $paymentMethodsSeen[0] ?? 'cash';
        }

        // If your DB payment_method is ENUM, ensure $salePaymentMethod is permitted.
        // If you prefer to always use strings, alter column to VARCHAR in migration.
        $sale = Sale::create([
            'patient_id' => $validated['patient_id'],
            'prescription_id' => $validated['prescription_id'] ?? null,
            'pharmacist_id' => $validated['pharmacist_id'],
            'total_amount' => $totalAmount,
            'payment_method' => $salePaymentMethod,
        ]);

        // 5) Insert sale items and decrement stock
        foreach ($saleItems as $itemData) {
            SaleItem::create(array_merge($itemData, ['sale_id' => $sale->id]));
            $drug = Drug::findOrFail($itemData['drug_id']);
            $drug->decrement('stock_quantity', $itemData['quantity']);
        }

        // 6) Insert medication_payments (explicit payments)
        foreach ($paymentRecords as $rec) {
            // attach sale/consultation/prescription context if needed
            DB::table('medication_payments')->insert($rec);
        }

        // 7) If policy claim existed and medication_payments were created earlier,
        //    ensure any explicit payments meant to be associated to the policy have insurance_id set.
        //    (You may want to set insurance_id for payments that belong to the policy — currently we only created policy_claim record)
        //    If you want to associate explicit payment rows to the policy, update those rows here.

        // 8) If prescription exists update prescription items quantities and status
        if (!empty($validated['prescription_id'])) {
            $prescription = Prescription::find($validated['prescription_id']);
            if ($prescription) {
                foreach ($validated['items'] as $itemData) {
                    $prescriptionItem = $prescription->items()
                        ->where('drug_id', $itemData['drug_id'])->first();
                    if ($prescriptionItem) {
                        $prescriptionItem->increment('quantity_dispensed', $itemData['quantity']);
                    }
                }
                // update prescription status helper if you have it
                $this->updatePrescriptionStatus($prescription);
            }
        }

        DB::commit();

        return response()->json([
            "success" => true,
            "message" => "Payment updated!",
            "data" => $sale
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        // log and return error
        \Log::error("Store sale error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            "success" => false,
            "message" => "Failed to store sale: " . $e->getMessage()
        ], 500);
    }
}


    private function updatePrescriptionStatus(Prescription $prescription)
    {
        $allItems = $prescription->items;
        $fullyDispensed = $allItems->every(function ($item) {
            return $item->quantity_dispensed >= $item->quantity_prescribed;
        });

        $partiallyDispensed = $allItems->some(function ($item) {
            return $item->quantity_dispensed > 0;
        });

        if ($fullyDispensed) {
            $prescription->update(['status' => 'completed']);
        } elseif ($partiallyDispensed) {
            $prescription->update(['status' => 'partial']);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('patient', 'prescription', 'pharmacist', 'items.drug');
        return view('sales.show', compact('sale'));
    }
}
