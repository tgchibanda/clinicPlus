<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InsuranceSubscription;
use App\Notifications\OverduePaymentNotification;
use App\Notifications\PolicyClosureNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOverdueSubscriptions extends Command
{
    protected $signature = 'insurance:check-overdue';
    protected $description = 'Check for overdue insurance subscriptions and send notifications';

    public function handle()
    {
        $this->info('Checking for overdue subscriptions...');

        $today = Carbon::today();

        // Query subscriptions that are not closed
        $subs = InsuranceSubscription::with(['patient', 'plan'])
            ->whereNotIn('status', ['closed'])
            ->where(function ($q) use ($today) {
                $q->where('next_due_date', '<=', $today)
                  ->orWhereNull('next_due_date');
            })
            ->get();

        $this->info("Found {$subs->count()} subscriptions to process");

        $notificationsSent = 0;
        $subscriptionsClosed = 0;

        foreach ($subs as $subscription) {
            DB::beginTransaction();
            try {
                // Calculate months overdue relative to next_due_date OR started_at if next_due_date absent
                $referenceDate = $subscription->next_due_date ? Carbon::parse($subscription->next_due_date) : Carbon::parse($subscription->started_at);
                $monthsOverdue = $referenceDate->diffInMonths($today);

                // Update due_count if the calculated overdue months exceed stored value
                if ($monthsOverdue > (int)$subscription->due_count) {
                    $subscription->due_count = $monthsOverdue;
                }

                // compute last_notification_level if you store it; if you don't, we use due_count to decide
                // Decide actions
                if ($subscription->due_count >= 4) {
                    // Close subscription
                    $subscription->status = 'closed';
                    $subscription->save();
                    $subscription->logEvent('subscription_auto_closed', [
                        'reason' => 'missed_4_months',
                        'due_count' => $subscription->due_count,
                    ]);

                    if ($subscription->patient) {
                        $subscription->patient->notify(new PolicyClosureNotification($subscription));
                    }

                    $this->warn("Closed subscription #{$subscription->id} - {$subscription->due_count} months overdue");
                    $subscriptionsClosed++;
                } elseif ($subscription->due_count === 3) {
                    if ($subscription->status !== 'lapsed' || $subscription->last_notification !== 'final') {
                        $subscription->status = 'lapsed';
                        $subscription->last_notification = 'final';
                        $subscription->save();

                        if ($subscription->patient) {
                            $subscription->patient->notify(new OverduePaymentNotification($subscription, 'final'));
                        }

                        $subscription->logEvent('final_warning_sent', ['due_count' => $subscription->due_count]);
                        $this->info("Sent final warning for subscription #{$subscription->id}");
                        $notificationsSent++;
                    }
                } elseif ($subscription->due_count === 2) {
                    if ($subscription->last_notification !== 'second') {
                        $subscription->status = 'lapsed';
                        $subscription->last_notification = 'second';
                        $subscription->save();

                        if ($subscription->patient) {
                            $subscription->patient->notify(new OverduePaymentNotification($subscription, 'second'));
                        }

                        $subscription->logEvent('second_reminder_sent', ['due_count' => $subscription->due_count]);
                        $this->info("Sent second reminder for subscription #{$subscription->id}");
                        $notificationsSent++;
                    }
                } elseif ($subscription->due_count === 1) {
                    if ($subscription->last_notification !== 'first') {
                        $subscription->status = 'lapsed';
                        $subscription->last_notification = 'first';
                        $subscription->save();

                        if ($subscription->patient) {
                            $subscription->patient->notify(new OverduePaymentNotification($subscription, 'first'));
                        }

                        $subscription->logEvent('first_reminder_sent', ['due_count' => $subscription->due_count]);
                        $this->info("Sent first reminder for subscription #{$subscription->id}");
                        $notificationsSent++;
                    }
                } else {
                    // no action required
                    $subscription->save();
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Failed processing subscription {$subscription->id}: ".$e->getMessage());
                $this->error("Failed processing subscription {$subscription->id}: ".$e->getMessage());
            }
        }

        $this->info("✓ Processed {$subs->count()} subscriptions");
        $this->info("✓ Sent {$notificationsSent} notifications");
        $this->info("✓ Closed {$subscriptionsClosed} subscriptions");

        return Command::SUCCESS;
    }
}