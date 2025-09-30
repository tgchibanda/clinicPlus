<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InsuranceSubscription;

class PaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $subscription;

    public function __construct($payment, InsuranceSubscription $subscription)
    {
        $this->payment = $payment;
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Receipt - Health Insurance')
            ->greeting("Dear {$notifiable->first_name} {$notifiable->last_name},")
            ->line('We have received your payment. Thank you!')
            ->line("Transaction Reference: {$this->payment->transaction_ref}")
            ->line("Amount Paid: \${$this->payment->amount}")
            ->line("Payment Date: " . $this->payment->paid_at->format('F d, Y'))
            ->line("Payment Method: " . ucfirst($this->payment->payment_method))
            ->line("Subscription ID: {$this->subscription->id}")
            ->line("Next Payment Due: " . $this->subscription->next_due_date->format('F d, Y'))
            ->line("Total Paid to Date: \${$this->subscription->total_paid_amount}")
            ->action('View Subscription', url("/insurance/subscription/{$this->subscription->id}"))
            ->line('Thank you for your continued trust in our services!');
    }
}