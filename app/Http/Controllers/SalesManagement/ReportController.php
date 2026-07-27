<?php

namespace App\Http\Controllers\SalesManagement;

use App\Http\Controllers\Concerns\ScopesToPharmacy;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Sale_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ScopesToPharmacy;

    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $sales = Sale::where('pharmacy_id', $pharmacy->id)
            ->whereBetween('invoice_date', [$from, $to]);

        $salesCount = (clone $sales)->count();
        $salesRevenue = (clone $sales)->sum('final_amount');

        $topMedicines = Sale_Item::with('medicines')
            ->whereHas('sales', function ($q) use ($pharmacy, $from, $to) {
                $q->where('pharmacy_id', $pharmacy->id)->whereBetween('invoice_date', [$from, $to]);
            })
            ->select('medicine_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $purchases = Purchase::where('pharmacy_id', $pharmacy->id)
            ->whereBetween('purchase_date', [$from, $to]);

        $purchasesCount = (clone $purchases)->count();
        $purchasesSpend = (clone $purchases)->sum('total_amount');

        $batches = Batch::with('medicines')->where('pharmacy_id', $pharmacy->id)->where('is_active', 1);

        $lowStockBatches = (clone $batches)->where('quantity', '<=', Batch::LOW_STOCK_THRESHOLD)->orderBy('quantity')->get();
        $nearExpiryBatches = (clone $batches)->whereDate('expiry_date', '<=', now()->addDays(30))->orderBy('expiry_date')->get();
        $totalStockQty = (clone $batches)->sum('quantity');

        return view('Report.index', compact(
            'from',
            'to',
            'salesCount',
            'salesRevenue',
            'topMedicines',
            'purchasesCount',
            'purchasesSpend',
            'lowStockBatches',
            'nearExpiryBatches',
            'totalStockQty'
        ));
    }
}
