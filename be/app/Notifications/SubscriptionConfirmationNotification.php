<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InsuranceSubscription;

class SubscriptionConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(InsuranceSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $monthlyTotal = $this->subscription->calculateMonthlyTotal();
        
        return (new MailMessage)
            ->subject('Welcome to Health Insurance Program')
            ->greeting("Dear {$notifiable->first_name} {$notifiable->last_name},")
            ->line('Thank you for enrolling in our health insurance program!')
            ->line("Subscription ID: {$this->subscription->id}")
            ->line("Plan: {$this->subscription->plan->name}")
            ->line("Monthly Amount: \${$monthlyTotal}")
            ->line("Start Date: " . $this->subscription->started_at->format('F d, Y'))
            ->line("Next Payment Due: " . $this->subscription->next_due_date->format('F d, Y'))
            ->line('**Important:** Your coverage will become effective after 3 months of continuous contributions.')
            ->action('View Subscription Details', url("/insurance/subscription/{$this->subscription->id}"))
            ->line('We look forward to serving your healthcare needs!');
    }
}