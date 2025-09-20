<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Prescription;
use App\Models\Drug;
use Illuminate\Http\Request;
use DB;

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
        $validated = $request->validate([
            'pharmacist_id'   => 'required|exists:users,id',
            'patient_id' => 'required|exists:patients,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'payment_method' => 'required|in:cash,voucher',
            'voucher_code' => 'required_if:payment_method,voucher|nullable|string',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $saleItems = [];

            // Calculate total and validate stock
            foreach ($validated['items'] as $itemData) {
                $drug = Drug::find($itemData['drug_id']);
                
                if ($drug->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for {$drug->name}");
                }

                $itemTotal = $drug->selling_price * $itemData['quantity'];
                $totalAmount += $itemTotal;

                $saleItems[] = [
                    'drug_id' => $itemData['drug_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $drug->selling_price,
                    'total_price' => $itemTotal
                ];
            }

            // Create sale
            $sale = Sale::create([
                'patient_id' => $validated['patient_id'],
                'prescription_id' => $validated['prescription_id'],
                'pharmacist_id'   => $validated['pharmacist_id'],
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'voucher_code' => $validated['voucher_code']
            ]);

            // Create sale items and update stock
            foreach ($saleItems as $itemData) {
                SaleItem::create(array_merge($itemData, ['sale_id' => $sale->id]));
                
                $drug = Drug::find($itemData['drug_id']);
                $drug->decrement('stock_quantity', $itemData['quantity']);
            }

            // Update prescription items if prescription sale
            if ($validated['prescription_id']) {
                $prescription = Prescription::find($validated['prescription_id']);
                
                foreach ($validated['items'] as $itemData) {
                    $prescriptionItem = $prescription->items()
                        ->where('drug_id', $itemData['drug_id'])->first();
                    
                    if ($prescriptionItem) {
                        $prescriptionItem->increment('quantity_dispensed', $itemData['quantity']);
                    }
                }

                // Update prescription status
                $this->updatePrescriptionStatus($prescription);
            }

            return response()->json([
                    "success" => true,
                    "message" => "Payment updated!",
                    "data" => $sale
                ], 201);
        });



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