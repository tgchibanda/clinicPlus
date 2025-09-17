<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function stockReport()
    {
        $drugs = Drug::with('saleItems')
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $lowStockDrugs = $drugs->where('stock_quantity', '<=', function ($drug) {
            return $drug->minimum_stock_level;
        });

        $expiredDrugs = Drug::where('expiry_date', '<', now())->get();

        return view('reports.stock', compact('drugs', 'lowStockDrugs', 'expiredDrugs'));
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $sales = Sale::with('patient', 'pharmacist', 'items.drug')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalTransactions = $sales->count();

        $topDrugs = SaleItem::with('drug')
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('drug_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('drug_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        return view('reports.sales', compact('sales', 'totalSales', 'totalTransactions', 'topDrugs', 'startDate', 'endDate'));
    }
}
