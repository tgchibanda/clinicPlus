<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\InsurancePlan;
use App\Models\InsuranceSubscription;
use App\Models\PolicyClaim;
use App\Models\InsurancePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class InsuranceSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InsuranceSubscription::with(['patient', 'plan', 'dependents.plan']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('start_date')) {
            $query->where('started_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('started_at', '<=', $request->end_date);
        }

        $perPage = $request->input('per_page', 15);
        $subscriptions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions
        ]);
    }


    public function update(Request $request, int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,active,lapsed,closed',
            'notes' => 'nullable|string',
            'plan_id' => 'nullable|exists:insurance_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $subscription->status;

        $subscription->update($request->only(['status', 'notes', 'plan_id']));

        if ($request->has('status') && $oldStatus !== $request->status) {
            $subscription->logEvent('status_changed', [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'changed_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'subscription' => $subscription->fresh(),
            'message' => 'Subscription updated successfully'
        ]);
    }

    public function close(int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::findOrFail($id);

        $subscription->update(['status' => 'closed']);
        $subscription->logEvent('subscription_closed', [
            'closed_by' => auth()->id(),
            'reason' => 'manual_closure'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription closed successfully'
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::with([
            'patient',
            'plan',
            'dependents.plan',
            'payments' => function ($q) {
                $q->orderBy('paid_at', 'desc');
            },
            'events' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(50);
            }
        ])->findOrFail($id);

        // monthly total - try model method if exists, otherwise calculate fallback
        $monthlyTotal = method_exists($subscription, 'calculateMonthlyTotal')
            ? $subscription->calculateMonthlyTotal()
            : ($subscription->monthly_total ?? 0);

        // is coverage active - try model method if exists
        $isCoverageActive = method_exists($subscription, 'isCoverageActive')
            ? (bool)$subscription->isCoverageActive()
            : (($subscription->status ?? '') === 'covered');

        // completed payments count - preferable model method
        $completedPayments = method_exists($subscription, 'getCompletedPaymentsCount')
            ? $subscription->getCompletedPaymentsCount()
            : (int)($subscription->payments()->where('status', 'completed')->count());

        // optionally include policy balance
        $policyBalance = $this->getPolicyBalance($subscription->id);

        return response()->json([
            'success' => true,
            'subscription' => $subscription,
            'monthly_total' => (float)$monthlyTotal,
            'is_coverage_active' => (bool)$isCoverageActive,
            'completed_payments' => (int)$completedPayments,
            'policy_balance' => (float)$policyBalance,
        ]);
    }

    public function addPayment(Request $request, int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'paid_at' => 'nullable|date',
            'note' => 'nullable|string',
            // optionally accept 'apply_to' or similar
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $paidAt = $request->input('paid_at') ? Carbon::parse($request->input('paid_at')) : Carbon::now();

            $payment = InsurancePayment::create([
                'subscription_id' => $subscription->id,
                'amount' => (float)$request->input('amount'),
                'payment_method' => $request->input('payment_method'),
                'transaction_ref' => $request->input('reference') ?? null,
                'paid_at' => $paidAt,
                'status' => 'completed',
                'note' => $request->input('note') ?? null,
            ]);

            // Update subscription totals and dates
            $subscription->total_paid_amount = (float)$subscription->total_paid_amount + (float)$payment->amount;

            if (empty($subscription->first_payment_at)) {
                $subscription->first_payment_at = $payment->paid_at;
            }

            $subscription->last_payment_at = $payment->paid_at;
            $subscription->due_count = 0;

            // Set or advance next_due_date by one month from existing next_due_date (or from now)
            if ($subscription->next_due_date) {
                try {
                    $subscription->next_due_date = Carbon::parse($subscription->next_due_date)->addMonth();
                } catch (\Exception $e) {
                    $subscription->next_due_date = Carbon::now()->addMonth();
                }
            } else {
                $subscription->next_due_date = Carbon::now()->addMonth();
            }

            // Update status based on completed payments (business logic)
            $completedPayments = $subscription->payments()->where('status', 'completed')->count();
            if ($completedPayments >= 3 && empty($subscription->coverage_starts_at)) {
                $subscription->coverage_starts_at = Carbon::parse($subscription->started_at)->addMonths(3);
                $subscription->status = 'covered';
            } elseif ($subscription->status === 'pending' && $completedPayments > 0) {
                $subscription->status = 'active';
            }

            $subscription->save();

            $subscription->logEvent('payment_recorded', [
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'recorded_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'subscription' => $subscription->fresh(),
                'message' => 'Payment recorded successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /subscriptions/{id}/payments
     * or GET /payments?subscription_id={id}
     */
    public function getPayments(Request $request, $id = null): JsonResponse
    {
        try {
            // Determine subscription id (URL param preferred, then query param)
            $subscriptionId = $id ?? $request->query('subscription_id');

            if (!$subscriptionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'subscription_id required'
                ], 400);
            }

            // Validate subscription exists (optional, but helpful)
            $subscription = InsuranceSubscription::find($subscriptionId);
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            // Query payments: prefer paid_at ordering (fallback to created_at)
            $payments = InsurancePayment::where('subscription_id', $subscription->id)
                ->orderByDesc('paid_at')
                ->orderByDesc('created_at')
                ->get();

            // Normalize fields if needed (optional)
            // e.g. map transaction_ref -> reference for front-end convenience
            $payments = $payments->map(function ($p) {
                return [
                    'id' => $p->id,
                    'subscription_id' => $p->subscription_id,
                    'amount' => (float) $p->amount,
                    'payment_method' => $p->payment_method ?? $p->method ?? null,
                    'transaction_ref' => $p->transaction_ref ?? $p->reference ?? null,
                    'note' => $p->note ?? $p->note ?? null,
                    'paid_at' => $p->paid_at ? $p->paid_at->toDateTimeString() : ($p->created_at ? $p->created_at->toDateTimeString() : null),
                    'status' => $p->status ?? null,
                    'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $payments,
            ]);
        } catch (\Throwable $e) {
            Log::error('getPayments error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /subscriptions/{id}/claims
     * or GET /policy_claims?subscription_id={id}
     */
    public function getClaims(Request $request, $id = null): JsonResponse
    {
        try {
            $subscriptionId = $id ?? $request->query('subscription_id');

            if (!$subscriptionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'subscription_id required'
                ], 400);
            }

            $subscription = InsuranceSubscription::find($subscriptionId);
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            // Query claims ordered newest first. Eager-load related plan/payment if needed.
            $claims = PolicyClaim::where('subscription_id', $subscription->id)
                ->orderByDesc('created_at')
                ->get();

            // Map to a simpler payload for front-end
            $claims = $claims->map(function ($c) {
                return [
                    'id' => $c->id,
                    'subscription_id' => $c->subscription_id,
                    'consultation_id' => $c->consultation_id,
                    'claim_holder_first_name' => $c->claim_holder_first_name,
                    'claim_holder_last_name' => $c->claim_holder_last_name,
                    'claim_holder_dob' => $c->claim_holder_dob ? (string) $c->claim_holder_dob : null,
                    'claim_holder_relationship' => $c->claim_holder_relationship,
                    'amount' => (float) $c->amount,
                    'claim_category' => $c->claim_category ?? null,
                    'status' => $c->status ?? null,
                    'created_at' => $c->created_at ? $c->created_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $claims,
            ]);
        } catch (\Throwable $e) {
            Log::error('getClaims error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch claims',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getPolicyBalance($subscriptionId): float
    {
        $totalPaid = InsurancePayment::where('subscription_id', $subscriptionId)
            ->where('status', 'completed')
            ->sum('amount');

        $totalClaimed = PolicyClaim::where('subscription_id', $subscriptionId)
            ->sum('amount');

        return (float)($totalPaid - $totalClaimed);
    }

    /**
     * Verify policy by policy number (or id) and return summary including balance.
     */
    public function verifyByPolicyNumber(Request $request): JsonResponse
    {
        $request->validate([
            'policy_number' => 'required|string',
        ]);

        $policyNumber = $request->input('policy_number');

        $subscription = InsuranceSubscription::where('policy_number', $policyNumber)
            ->orWhere('id', $policyNumber) // allow ID lookups too
            ->with('patient', 'plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active policy found with that number.'
            ], 404);
        }

        // Only allow usage if policy is 'covered' (matured) — adjust business rule if needed
        if (($subscription->status ?? '') !== 'covered') {
            return response()->json([
                'success' => false,
                'message' => 'This policy is not yet matured or covered.'
            ], 422);
        }

        $balance = $this->getPolicyBalance($subscription->id);
        $monthlyTotal = method_exists($subscription, 'calculateMonthlyTotal')
            ? $subscription->calculateMonthlyTotal()
            : ($subscription->monthly_total ?? 0);

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'policy_number' => $subscription->policy_number,
                'plan' => optional($subscription->plan)->name,
                'monthly_total' => (float)$monthlyTotal,
                'balance' => (float)$balance,
            ],
            'patient' => [
                'id' => $subscription->patient->id ?? null,
                'first_name' => $subscription->patient->first_name ?? null,
                'last_name' => $subscription->patient->last_name ?? null,
            ],
        ]);
    }

    public function verifyConsultationByPolicyNumber(Request $request)
    {
        $request->validate([
            'policy_number' => 'required|string',
        ]);

        $policyNumber = $request->input('policy_number');

        // Fetch subscription by policy number or ID
        $subscription = InsuranceSubscription::where('policy_number', $policyNumber)
            ->with('patient')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active policy found with that number.'
            ], 404);
        }

        if ($subscription->status != 'covered') {
            return response()->json([
                'message' => 'This policy is not yet matured.'
            ], 404);
        }

        // Calculate current balance
        $balance = $this->getPolicyBalance($subscription->id);
        // or $subscription->balance will get the balance because of the relationship

        return response()->json([
            'id' => $subscription->id,
            'status' => $subscription->status,
            'policy_number' => $subscription->policy_number,
            'plan' => $subscription->plan->name,
            'balance' => $balance,
            'patient' => [
                'id' => $subscription->patient->id ?? null,
                'first_name' => $subscription->patient->first_name ?? null,
                'last_name' => $subscription->patient->last_name ?? null,
            ],
        ]);
    }

}
