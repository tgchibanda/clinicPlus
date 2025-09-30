<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InsuranceSubscription;

class OverduePaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $reminderType;

    public function __construct(InsuranceSubscription $subscription, string $reminderType = 'first')
    {
        $this->subscription = $subscription;
        $this->reminderType = $reminderType;
    }

    public function via($notifiable): array
    {
        // Use SMS for second reminder onwards
        if ($this->reminderType === 'second' || $this->reminderType === 'final') {
            return ['mail', 'nexmo']; // or 'twilio'
        }
        
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $monthlyTotal = $this->subscription->calculateMonthlyTotal();
        $dueCount = $this->subscription->due_count;

        $subject = match($this->reminderType) {
            'first' => 'Payment Reminder - Health Insurance Subscription',
            'second' => 'Urgent: Payment Overdue - Health Insurance',
            'final' => 'Final Notice - Health Insurance Payment Required',
            default => 'Payment Reminder',
        };

        $greeting = "Dear {$notifiable->first_name} {$notifiable->last_name},";

        $message = match($this->reminderType) {
            'first' => "Your health insurance payment of \${$monthlyTotal} is now overdue. Please make payment as soon as possible to keep your coverage active.",
            'second' => "This is your second reminder. Your health insurance payment has been overdue for {$dueCount} months. Total amount due: \${$monthlyTotal * $dueCount}. Please pay immediately to avoid policy closure.",
            'final' => "FINAL NOTICE: Your health insurance policy will be closed if payment is not received within 30 days. You have missed {$dueCount} months of payments. Total outstanding: \${$monthlyTotal * $dueCount}.",
            default => "Please make your health insurance payment.",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line("Subscription ID: {$this->subscription->id}")
            ->line("Monthly Amount: \${$monthlyTotal}")
            ->line("Months Overdue: {$dueCount}")
            ->line("Total Outstanding: \$" . ($monthlyTotal * $dueCount))
            ->action('Make Payment', url("/insurance/payment/{$this->subscription->id}"))
            ->line('Thank you for your prompt attention to this matter.');
    }

    public function toNexmo($notifiable): array
    {
        $monthlyTotal = $this->subscription->calculateMonthlyTotal();
        
        return [
            'from' => config('services.nexmo.sms_from'),
            'to' => $notifiable->phone,
            'message' => "URGENT: Your health insurance payment of \${$monthlyTotal} is {$this->subscription->due_count} months overdue. Please pay immediately to avoid policy closure. Subscription ID: {$this->subscription->id}",
        ];
    }
}