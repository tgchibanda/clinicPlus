<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\InsurancePlan;
use App\Models\InsuranceSubscription;
use App\Models\InsuranceDependent;
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
            $query->whereHas('patient', function($q) use ($search) {
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

    public function show(int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::with([
            'patient', 
            'plan', 
            'dependents.plan',
            'payments' => fn($q) => $q->orderBy('paid_at', 'desc'),
            'events' => fn($q) => $q->orderBy('created_at', 'desc')->limit(50)
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'subscription' => $subscription,
            'monthly_total' => $subscription->calculateMonthlyTotal(),
            'is_coverage_active' => $subscription->isCoverageActive(),
            'completed_payments' => $subscription->getCompletedPaymentsCount(),
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

    public function addPayment(Request $request, int $id): JsonResponse
    {
        $subscription = InsuranceSubscription::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $payment = InsurancePayment::create([
                'subscription_id' => $subscription->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_ref' => $request->reference,
                'paid_at' => $request->paid_at ?? now(),
                'status' => 'completed',
            ]);

            // Update subscription
            $subscription->total_paid_amount += $payment->amount;
            
            if (!$subscription->first_payment_at) {
                $subscription->first_payment_at = $payment->paid_at;
            }
            
            $subscription->last_payment_at = $payment->paid_at;
            $subscription->due_count = 0; // Reset overdue count
            $subscription->next_due_date = Carbon::parse($subscription->next_due_date)->addMonth();

            // Check if coverage should start
            $completedPayments = $subscription->getCompletedPaymentsCount();
            if ($completedPayments >= 3 && !$subscription->coverage_starts_at) {
                $subscription->coverage_starts_at = Carbon::parse($subscription->started_at)->addMonths(3);
                $subscription->status = 'active';
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
}