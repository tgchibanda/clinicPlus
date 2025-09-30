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

class PaymentWebhookController extends Controller
{
    public function handleWebhook(Request $request): JsonResponse
    {
        // Implement webhook signature verification based on your payment provider
        // Example for Stripe:
        // $signature = $request->header('Stripe-Signature');
        // Verify signature...

        $payload = $request->all();

        // Store raw webhook data
        \Log::info('Payment webhook received', $payload);

        // Parse webhook based on provider
        // This is a generic example - customize based on your provider
        
        try {
            $subscriptionId = $payload['metadata']['subscription_id'] ?? null;
            $amount = $payload['amount'] ?? 0;
            $status = $payload['status'] ?? 'pending';
            $transactionRef = $payload['transaction_id'] ?? null;

            if (!$subscriptionId) {
                return response()->json(['success' => false, 'message' => 'Missing subscription_id'], 400);
            }

            $subscription = InsuranceSubscription::findOrFail($subscriptionId);

            DB::beginTransaction();

            $payment = InsurancePayment::create([
                'subscription_id' => $subscription->id,
                'amount' => $amount / 100, // Convert cents to dollars
                'payment_method' => 'online',
                'transaction_ref' => $transactionRef,
                'paid_at' => now(),
                'status' => $status === 'succeeded' ? 'completed' : 'failed',
                'raw_payload' => $payload,
            ]);

            if ($status === 'succeeded') {
                $subscription->total_paid_amount += $payment->amount;
                
                if (!$subscription->first_payment_at) {
                    $subscription->first_payment_at = now();
                }
                
                $subscription->last_payment_at = now();
                $subscription->due_count = 0;
                $subscription->next_due_date = Carbon::parse($subscription->next_due_date)->addMonth();

                $completedPayments = $subscription->getCompletedPaymentsCount();
                if ($completedPayments >= 3 && !$subscription->coverage_starts_at) {
                    $subscription->coverage_starts_at = Carbon::parse($subscription->started_at)->addMonths(3);
                    $subscription->status = 'active';
                }

                $subscription->save();
                $subscription->logEvent('webhook_payment_received', ['transaction_ref' => $transactionRef]);
            }

            DB::commit();

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}