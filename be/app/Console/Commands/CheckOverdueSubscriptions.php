<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InsuranceSubscription;
use App\Notifications\OverduePaymentNotification;
use App\Notifications\PolicyClosureNotification;
use Carbon\Carbon;

class CheckOverdueSubscriptions extends Command
{
    protected $signature = 'insurance:check-overdue';
    protected $description = 'Check for overdue insurance subscriptions and send notifications';

    public function handle()
    {
        $this->info('Checking for overdue subscriptions...');

        $today = Carbon::today();
        
        // Get subscriptions that are due and not closed
        $overdueSubscriptions = InsuranceSubscription::with(['patient', 'plan'])
            ->whereIn('status', ['pending', 'active', 'lapsed'])
            ->where('next_due_date', '<=', $today)
            ->get();

        $this->info("Found {$overdueSubscriptions->count()} subscriptions to process");

        $notificationsSent = 0;
        $subscriptionsClosed = 0;

        foreach ($overdueSubscriptions as $subscription) {
            // Calculate how many months overdue
            $daysPastDue = $today->diffInDays(Carbon::parse($subscription->next_due_date));
            $monthsOverdue = (int) floor($daysPastDue / 30);

            // Increment due count if this is a new overdue period
            if ($subscription->last_payment_at) {
                $daysSinceLastPayment = $today->diffInDays(Carbon::parse($subscription->last_payment_at));
                $calculatedDueCount = max(0, (int) floor($daysSinceLastPayment / 30) - 1);
                
                if ($calculatedDueCount > $subscription->due_count) {
                    $subscription->due_count = $calculatedDueCount;
                    $subscription->save();
                }
            } else {
                // No payment ever made
                $daysSinceStart = $today->diffInDays(Carbon::parse($subscription->started_at));
                $calculatedDueCount = (int) floor($daysSinceStart / 30);
                
                if ($calculatedDueCount > $subscription->due_count) {
                    $subscription->due_count = $calculatedDueCount;
                    $subscription->save();
                }
            }

            // Handle notifications and closures based on due_count
            if ($subscription->due_count >= 4) {
                // Close subscription after 4 months
                $subscription->update(['status' => 'closed']);
                $subscription->logEvent('subscription_auto_closed', [
                    'reason' => 'missed_4_months',
                    'due_count' => $subscription->due_count,
                ]);

                // Send closure notification
                $subscription->patient->notify(new PolicyClosureNotification($subscription));
                
                $this->warn("Closed subscription #{$subscription->id} - 4 months overdue");
                $subscriptionsClosed++;

            } elseif ($subscription->due_count === 3) {
                // Final warning
                $subscription->update(['status' => 'lapsed']);
                $subscription->logEvent('final_warning_sent', [
                    'due_count' => $subscription->due_count,
                ]);

                $subscription->patient->notify(new OverduePaymentNotification($subscription, 'final'));
                
                $this->info("Sent final warning for subscription #{$subscription->id}");
                $notificationsSent++;

            } elseif ($subscription->due_count === 2) {
                // Second reminder (escalate to SMS)
                $subscription->update(['status' => 'lapsed']);
                $subscription->logEvent('second_reminder_sent', [
                    'due_count' => $subscription->due_count,
                ]);

                $subscription->patient->notify(new OverduePaymentNotification($subscription, 'second'));
                
                $this->info("Sent second reminder for subscription #{$subscription->id}");
                $notificationsSent++;

            } elseif ($subscription->due_count === 1) {
                // First reminder
                $subscription->update(['status' => 'lapsed']);
                $subscription->logEvent('first_reminder_sent', [
                    'due_count' => $subscription->due_count,
                ]);

                $subscription->patient->notify(new OverduePaymentNotification($subscription, 'first'));
                
                $this->info("Sent first reminder for subscription #{$subscription->id}");
                $notificationsSent++;
            }
        }

        $this->info("✓ Processed {$overdueSubscriptions->count()} subscriptions");
        $this->info("✓ Sent {$notificationsSent} notifications");
        $this->info("✓ Closed {$subscriptionsClosed} subscriptions");

        return Command::SUCCESS;
    }
}