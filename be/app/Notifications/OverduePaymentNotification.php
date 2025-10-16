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

    public function __construct(InsuranceSubscription $subscription, $reminderType = 'first')
    {
        $this->subscription = $subscription;
        $this->reminderType = $reminderType;
    }

    // removed return type for broader compatibility
    public function via($notifiable)
    {
        // Use SMS for second reminder onwards
        if ($this->reminderType === 'second' || $this->reminderType === 'final') {
            return ['mail', 'nexmo']; // or 'twilio' depending on your channels
        }

        return ['mail'];
    }

    // removed return type for compatibility
    public function toMail($notifiable)
    {
        // Make sure calculateMonthlyTotal exists on the model. If not, compute fallback.
        $monthlyTotal = 0;
        if (method_exists($this->subscription, 'calculateMonthlyTotal')) {
            try {
                $monthlyTotal = $this->subscription->calculateMonthlyTotal();
            } catch (\Throwable $e) {
                $monthlyTotal = $this->subscription->monthly_total ?? 0;
            }
        } else {
            $monthlyTotal = $this->subscription->monthly_total ?? 0;
        }

        $dueCount = (int) ($this->subscription->due_count ?? 0);

        // build subject via switch (compatible)
        switch ($this->reminderType) {
            case 'second':
                $subject = 'Urgent: Payment Overdue - Health Insurance';
                break;
            case 'final':
                $subject = 'Final Notice - Health Insurance Payment Required';
                break;
            case 'first':
            default:
                $subject = 'Payment Reminder - Health Insurance Subscription';
                break;
        }

        $firstName = isset($notifiable->first_name) ? $notifiable->first_name : '';
        $lastName = isset($notifiable->last_name) ? $notifiable->last_name : '';
        $greeting = "Dear {$firstName} {$lastName},";

        // build message via switch
        switch ($this->reminderType) {
            case 'second':
                $message = "This is your second reminder. Your health insurance payment has been overdue for {$dueCount} months. Total amount due: \$" . number_format($monthlyTotal * $dueCount, 2) . ". Please pay immediately to avoid policy closure.";
                break;
            case 'final':
                $message = "FINAL NOTICE: Your health insurance policy will be closed if payment is not received within 30 days. You have missed {$dueCount} months of payments. Total outstanding: \$" . number_format($monthlyTotal * $dueCount, 2) . ".";
                break;
            case 'first':
            default:
                $message = "Your health insurance payment of \$" . number_format($monthlyTotal, 2) . " is now overdue. Please make payment as soon as possible to keep your coverage active.";
                break;
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line("Subscription ID: {$this->subscription->id}")
            ->line("Monthly Amount: \$" . number_format($monthlyTotal, 2))
            ->line("Months Overdue: {$dueCount}")
            ->line("Total Outstanding: \$" . number_format($monthlyTotal * $dueCount, 2));

        // If you have a payment route in front-end, adjust below URL as needed.
        try {
            $mail = $mail->action('Make Payment', url("/insurance/payment/{$this->subscription->id}"));
        } catch (\Throwable $e) {
            // ignore URL issues in CLI environment
        }

        return $mail->line('Thank you for your prompt attention to this matter.');
    }

    public function toNexmo($notifiable)
    {
        $monthlyTotal = 0;
        if (method_exists($this->subscription, 'calculateMonthlyTotal')) {
            try {
                $monthlyTotal = $this->subscription->calculateMonthlyTotal();
            } catch (\Throwable $e) {
                $monthlyTotal = $this->subscription->monthly_total ?? 0;
            }
        } else {
            $monthlyTotal = $this->subscription->monthly_total ?? 0;
        }

        return [
            'from' => config('services.nexmo.sms_from'),
            'to' => $notifiable->phone,
            'message' => "URGENT: Your health insurance payment of \$" . number_format($monthlyTotal, 2) . " is {$this->subscription->due_count} months overdue. Please pay immediately to avoid policy closure. Subscription ID: {$this->subscription->id}",
        ];
    }
}