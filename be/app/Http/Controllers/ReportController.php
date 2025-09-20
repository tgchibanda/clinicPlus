<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Dashboard summary (used by /api/reports)
     */
    public function index()
{
    $now          = Carbon::now();
    $monthStart   = $now->copy()->startOfMonth();
    $monthEnd     = $now->copy()->endOfMonth();

    // Quick stats
    $patientsCount        = Patient::count();
    $patientsThisMonth    = Patient::whereBetween('created_at', [$monthStart, $monthEnd])->count();

    $salesTotal           = (float) Sale::sum('total_amount');
    $salesThisMonth       = (float) Sale::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');

    $lowStockCount        = Drug::whereColumn('stock_quantity', '<=', 'minimum_stock_level')->count();
    $activeRxCount        = Prescription::whereIn('status', ['pending', 'partial'])->count();

    $expiringSoonCount    = Drug::whereNotNull('expiry_date')
        ->where('expiry_date', '>=', $now)
        ->where('expiry_date', '<', $now->copy()->addMonths(3))
        ->count();

    $expiredCount         = Drug::whereNotNull('expiry_date')
        ->where('expiry_date', '<', $now)
        ->count();

    $todaysSalesTotal     = (float) Sale::whereDate('created_at', $now->toDateString())->sum('total_amount');
    $todaysSalesCount     = Sale::whereDate('created_at', $now->toDateString())->count();

    $todaysPatientsCount  = Patient::whereDate('visit_date', $now->toDateString())->count();
    $todaysRxCount        = Prescription::whereDate('created_at', $now->toDateString())->count();

    // ✅ Updated: count doctors by joining user_roles
    $activeDoctorsCount   = DB::table('user_roles')
        ->where('role', 'doctor')
        ->distinct('user_id')
        ->count('user_id');

    $drugsCount           = Drug::count();

    // Recent patients
    $recentPatients = Patient::with('doctor')
        ->whereDate('visit_date', $now->toDateString())
        ->latest('visit_date')
        ->limit(5)
        ->get()
        ->map(function ($p) {
            return [
                'id'         => $p->id,
                'first_name' => $p->first_name,
                'last_name'  => $p->last_name,
                'full_name'  => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')),
                'status'     => $p->status,
                'visit_time' => optional($p->visit_date)->format('H:i'),
                'doctor'     => $p->doctor ? ['id' => $p->doctor->id, 'name' => $p->doctor->name] : null,
            ];
        })
        ->values();

    $recentSales = Sale::with('patient')
        ->whereDate('created_at', $now->toDateString())
        ->latest()
        ->limit(5)
        ->get()
        ->map(function ($s) {
            return [
                'id'            => $s->id,
                'payment_method'=> $s->payment_method,
                'total_amount'  => (float) $s->total_amount,
                'created_at'    => $s->created_at,
                'created_time'  => optional($s->created_at)->format('H:i'),
                'patient'       => $s->patient ? [
                    'id'        => $s->patient->id,
                    'first_name'=> $s->patient->first_name,
                    'last_name' => $s->patient->last_name,
                    'full_name' => trim(($s->patient->first_name ?? '') . ' ' . ($s->patient->last_name ?? '')),
                ] : null,
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'message' => 'Reports dashboard loaded.',
        'data'    => [
            'patients_count'              => $patientsCount,
            'patients_this_month'         => $patientsThisMonth,
            'sales_total'                 => $salesTotal,
            'sales_this_month'            => $salesThisMonth,
            'low_stock_count'             => $lowStockCount,
            'active_prescriptions_count'  => $activeRxCount,
            'expiring_soon_count'         => $expiringSoonCount,
            'expired_count'               => $expiredCount,
            'todays_sales_total'          => $todaysSalesTotal,
            'todays_sales_count'          => $todaysSalesCount,
            'todays_patients_count'       => $todaysPatientsCount,
            'todays_prescriptions_count'  => $todaysRxCount,
            'active_doctors_count'        => $activeDoctorsCount,
            'drugs_count'                 => $drugsCount,
            'recent_patients'             => $recentPatients,
            'recent_sales'                => $recentSales,
        ],
    ], 200);
}


    /**
     * Stock report (used by /api/reports/stock)
     */
    public function stockReport()
    {
        $drugs = Drug::orderBy('stock_quantity', 'asc')->get([
            'id', 'name', 'description', 'category', 'stock_quantity', 'minimum_stock_level',
            'selling_price', 'unit', 'expiry_date'
        ]);

        // Totals
        $totalStockValue = (float) $drugs->sum(function ($d) {
            return (float) $d->stock_quantity * (float) $d->selling_price;
        });

        $totalDrugs       = $drugs->count();
        $categoriesCount  = $drugs->pluck('category')->filter()->unique()->count();

        // Low / critical / expiring / expired
        $criticalLowCount = $drugs->filter(fn($d) => (int) $d->stock_quantity <= 5)->count();
        $lowStockCount    = $drugs->filter(fn($d) => (int) $d->stock_quantity <= (int) $d->minimum_stock_level)->count();
        $expiredCount     = $drugs->filter(fn($d) => $d->expiry_date && Carbon::parse($d->expiry_date)->lt(Carbon::now()))->count();
        $expiringSoonCount= $drugs->filter(function ($d) {
            if (!$d->expiry_date) return false;
            $dt = Carbon::parse($d->expiry_date);
            return $dt->gte(Carbon::now()) && $dt->lt(Carbon::now()->copy()->addMonths(3));
        })->count();

        // By category
        $byCategory = $drugs->groupBy('category')->map(function ($group, $cat) {
            $value = $group->sum(fn($d) => (float) $d->stock_quantity * (float) $d->selling_price);
            return [
                'category' => $cat ?: 'Uncategorized',
                'items'    => $group->count(),
                'value'    => (float) $value,
            ];
        })->values();

        // Status distribution
        $good = $drugs->filter(function ($d) {
            $expired = $d->expiry_date && Carbon::parse($d->expiry_date)->lt(Carbon::now());
            $low     = (int) $d->stock_quantity <= (int) $d->minimum_stock_level;
            return !$expired && !$low;
        })->count();

        $low = $drugs->filter(function ($d) {
            $expired = $d->expiry_date && Carbon::parse($d->expiry_date)->lt(Carbon::now());
            $low     = (int) $d->stock_quantity <= (int) $d->minimum_stock_level;
            return $low && !$expired;
        })->count();

        $statusCounts = [
            [ 'key' => 'good',    'title' => 'Good Stock', 'count' => $good,         'classText' => 'text-success', 'classBadge' => 'bg-success', 'classBar' => 'bg-success' ],
            [ 'key' => 'low',     'title' => 'Low Stock',  'count' => $low,          'classText' => 'text-warning', 'classBadge' => 'bg-warning', 'classBar' => 'bg-warning' ],
            [ 'key' => 'expired', 'title' => 'Expired',    'count' => $expiredCount, 'classText' => 'text-danger',  'classBadge' => 'bg-danger',  'classBar' => 'bg-danger'  ],
        ];

        // Serialize drugs minimally for the table
        $rows = $drugs->map(function ($d) {
            return [
                'id'                  => $d->id,
                'name'                => $d->name,
                'description'         => $d->description,
                'category'            => $d->category,
                'stock_quantity'      => (int) $d->stock_quantity,
                'minimum_stock_level' => (int) $d->minimum_stock_level,
                'selling_price'       => (float) $d->selling_price,
                'unit'                => $d->unit,
                'expiry_date'         => $d->expiry_date,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Stock report loaded.',
            'data'    => [
                'drugs'               => $rows,
                'critical_low_count'  => $criticalLowCount,
                'low_stock_count'     => $lowStockCount,
                'expiring_soon_count' => $expiringSoonCount,
                'expired_count'       => $expiredCount,
                'total_stock_value'   => $totalStockValue,
                'total_drugs'         => $totalDrugs,
                'categories_count'    => $categoriesCount,
                'by_category'         => $byCategory,
                'status_counts'       => $statusCounts,
            ],
        ], 200);
    }

    /**
     * Sales report (used by /api/reports/sales)
     * Accepts ?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function salesReport(Request $request)
    {
        // Normalize dates
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        try {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfMonth();
        }
        try {
            $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();
        } catch (\Throwable $e) {
            $end   = Carbon::now()->endOfMonth();
        }

        $sales = Sale::with('patient')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $totalRevenue       = (float) $sales->sum('total_amount');
        $transactionsCount  = $sales->count();

        // Top payment method
        $topPayment = Sale::select('payment_method', DB::raw('COUNT(*) as cnt'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('payment_method')
            ->orderByDesc('cnt')
            ->first();

        // Optional: Top drugs in period
        $topDrugs = SaleItem::with('drug')
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->select('drug_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('drug_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'drug_id'        => $row->drug_id,
                    'drug_name'      => optional($row->drug)->name,
                    'total_quantity' => (int) $row->total_quantity,
                    'total_revenue'  => (float) $row->total_revenue,
                ];
            })
            ->values();

        $rows = $sales->map(function ($s) {
            return [
                'id'             => $s->id,
                'payment_method' => $s->payment_method,
                'total_amount'   => (float) $s->total_amount,
                'created_at'     => $s->created_at,
                'patient'        => $s->patient ? [
                    'id'         => $s->patient->id,
                    'first_name' => $s->patient->first_name,
                    'last_name'  => $s->patient->last_name,
                    'full_name'  => trim(($s->patient->first_name ?? '') . ' ' . ($s->patient->last_name ?? '')),
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Sales report loaded.',
            'data'    => [
                'rows'               => $rows,
                'total_revenue'      => $totalRevenue,
                'transactions_count' => $transactionsCount,
                'top_payment_method' => optional($topPayment)->payment_method,
                'top_drugs'          => $topDrugs,
                'period'             => [
                    'start_date' => $start->toDateString(),
                    'end_date'   => $end->toDateString(),
                ],
            ],
        ], 200);
    }
}
