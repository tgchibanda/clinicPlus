<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsuranceSubscription;
use App\Models\InsurancePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class InsuranceReportController extends Controller
{
    public function subscriptions(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        $status = $request->input('status');

        $query = InsuranceSubscription::whereBetween('started_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        $subscriptions = $query->with(['patient', 'plan'])->get();

        $summary = [
            'total_subscriptions' => $subscriptions->count(),
            'active' => $subscriptions->where('status', 'active')->count(),
            'pending' => $subscriptions->where('status', 'pending')->count(),
            'lapsed' => $subscriptions->where('status', 'lapsed')->count(),
            'closed' => $subscriptions->where('status', 'closed')->count(),
            'total_revenue' => $subscriptions->sum('total_paid_amount'),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $payments = InsurancePayment::whereBetween('paid_at', [$startDate, $endDate])
            ->with(['subscription.patient', 'subscription.plan'])
            ->where('status', 'completed')
            ->get();

        $summary = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'by_method' => $payments->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            }),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'payments' => $payments,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'subscriptions'); // subscriptions or payments
        $format = $request->input('format', 'csv');
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        if ($type === 'subscriptions') {
            return $this->exportSubscriptions($startDate, $endDate, $format);
        } else {
            return $this->exportPayments($startDate, $endDate, $format);
        }
    }

    private function exportSubscriptions($startDate, $endDate, $format)
    {
        $subscriptions = InsuranceSubscription::whereBetween('started_at', [$startDate, $endDate])
            ->with(['patient', 'plan', 'dependents'])
            ->get();

        $csvData = [];
        $csvData[] = [
            'Subscription ID',
            'Patient Name',
            'Patient Phone',
            'Plan',
            'Status',
            'Started At',
            'Coverage Starts At',
            'Total Paid',
            'Next Due Date',
            'Dependents Count',
            'Monthly Total',
        ];

        foreach ($subscriptions as $subscription) {
            $csvData[] = [
                $subscription->id,
                $subscription->patient->first_name . ' ' . $subscription->patient->last_name,
                $subscription->patient->phone,
                $subscription->plan->name ?? 'N/A',
                $subscription->status,
                $subscription->started_at->format('Y-m-d'),
                $subscription->coverage_starts_at ? $subscription->coverage_starts_at->format('Y-m-d') : 'Pending',
                $subscription->total_paid_amount,
                $subscription->next_due_date->format('Y-m-d'),
                $subscription->dependents->count(),
                $subscription->calculateMonthlyTotal(),
            ];
        }

        $filename = "subscriptions_" . now()->format('Y-m-d_His') . ".csv";

        return $this->generateCsv($csvData, $filename);
    }

    private function exportPayments($startDate, $endDate, $format)
    {
        $payments = InsurancePayment::whereBetween('paid_at', [$startDate, $endDate])
            ->with(['subscription.patient', 'subscription.plan'])
            ->where('status', 'completed')
            ->get();

        $csvData = [];
        $csvData[] = [
            'Payment ID',
            'Subscription ID',
            'Patient Name',
            'Plan',
            'Amount',
            'Payment Method',
            'Transaction Ref',
            'Paid At',
            'Status',
        ];

        foreach ($payments as $payment) {
            $csvData[] = [
                $payment->id,
                $payment->subscription_id,
                $payment->subscription->patient->first_name . ' ' . $payment->subscription->patient->last_name,
                $payment->subscription->plan->name ?? 'N/A',
                $payment->amount,
                $payment->payment_method,
                $payment->transaction_ref ?? 'N/A',
                $payment->paid_at->format('Y-m-d H:i:s'),
                $payment->status,
            ];
        }

        $filename = "payments_" . now()->format('Y-m-d_His') . ".csv";

        return $this->generateCsv($csvData, $filename);
    }

    private function generateCsv(array $data, string $filename)
    {
        $handle = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}