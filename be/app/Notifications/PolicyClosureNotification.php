<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InsuranceSubscription;

class PolicyClosureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(InsuranceSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['mail', 'nexmo'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Health Insurance Policy Closed')
            ->greeting("Dear {$notifiable->first_name} {$notifiable->last_name},")
            ->line('Your health insurance policy has been closed due to non-payment.')
            ->line("Subscription ID: {$this->subscription->id}")
            ->line("Closure Date: " . now()->format('F d, Y'))
            ->line("Reason: Missed 4 consecutive monthly payments")
            ->line('Your coverage has ended. If you wish to reactivate your policy, please contact our office.')
            ->line('Thank you for being with us.');
    }

    public function toNexmo($notifiable): array
    {
        return [
            'from' => config('services.nexmo.sms_from'),
            'to' => $notifiable->phone,
            'message' => "Your health insurance policy (ID: {$this->subscription->id}) has been closed due to non-payment. Please contact us to discuss reactivation options.",
        ];
    }
}