<?php

namespace App\Http\Controllers\SalesManagement;

use App\Http\Controllers\Concerns\ScopesToPharmacy;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Sale;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    use ScopesToPharmacy;

    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $sales = Sale::with(['users', 'sale_items'])
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return view('Sale.index', compact('sales'));
    }

    public function create(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        Batch::deactivateExpired();

        // Sell by medicine, not by batch — stock is aggregated across all of a
        // medicine's active batches for this pharmacy; the nearest-expiry batch is
        // what actually gets sold from first (see store()).
        $medicines = Batch::with('medicines')
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', 1)
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('medicine_id')
            ->map(function ($batches) {
                $soonest = $batches->sortBy('expiry_date')->first();

                return (object) [
                    'medicine_id' => $soonest->medicine_id,
                    'medicine' => $soonest->medicines,
                    'available_quantity' => $batches->sum('quantity'),
                    'next_expiry' => $soonest->expiry_date,
                    'preview_price' => $soonest->selling_price,
                ];
            })
            ->values();

        return view('Sale.create', compact('medicines'));
    }

    public function store(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        Batch::deactivateExpired();

        $request->validate([
            'invoice_number' => 'required|integer|unique:sales,invoice_number',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,bank,palpay',
            'status' => 'required|in:complete,cancelled',
            'notes' => 'nullable|string|max:1000',
            'medicine_id' => 'required|array|min:1',
            'medicine_id.*' => 'required|exists:medicines,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'receipt_image' => 'required_if:payment_method,bank,palpay|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $lowStockBatchIds = [];

        try {
            $sale = DB::transaction(function () use ($request, $pharmacy, &$lowStockBatchIds) {
                $discount = $request->input('discount', 0);
                $tax = $request->input('tax', 0);
                $totalAmount = 0;
                $items = [];

                foreach ($request->medicine_id as $index => $medicineId) {
                    $requested = (int) $request->quantity[$index];

                    // First-Expired-First-Out: deduct from the soonest-to-expire
                    // batches first, splitting across batches if one isn't enough.
                    $batches = Batch::where('pharmacy_id', $pharmacy->id)
                        ->where('medicine_id', $medicineId)
                        ->where('is_active', 1)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date')
                        ->get();

                    $available = $batches->sum('quantity');
                    if ($requested > $available) {
                        $medicineName = optional($batches->first()?->medicines)->brand_name_en ?? "دواء #{$medicineId}";
                        throw ValidationException::withMessages([
                            'quantity' => "الكمية المطلوبة لـ {$medicineName} أكبر من المتوفر ({$available}).",
                        ]);
                    }

                    $remaining = $requested;
                    foreach ($batches as $batch) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $take = min($remaining, $batch->quantity);
                        $subtotal = $take * $batch->selling_price;
                        $profit = $subtotal - ($take * $batch->purchase_price);
                        $totalAmount += $subtotal;

                        // Only alert on the transition into low-stock, not on every
                        // subsequent sale of an already-low item.
                        $afterQty = $batch->quantity - $take;
                        if ($batch->quantity > Batch::LOW_STOCK_THRESHOLD && $afterQty <= Batch::LOW_STOCK_THRESHOLD) {
                            $lowStockBatchIds[] = $batch->id;
                        }

                        $items[] = [
                            'batch_id' => $batch->id,
                            'medicine_id' => $batch->medicine_id,
                            'quantity' => $take,
                            'purchase_price' => $batch->purchase_price,
                            'selling_price' => $batch->selling_price,
                            'subtotal' => $subtotal,
                            'profit' => $profit,
                        ];

                        $remaining -= $take;
                    }
                }

                $finalAmount = $totalAmount - $discount + $tax;

                $sale = Sale::create([
                    'pharmacy_id' => $pharmacy->id,
                    'invoice_number' => $request->invoice_number,
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'tax' => $tax,
                    'final_amount' => $finalAmount,
                    'payment_method' => $request->payment_method,
                    'receipt_image' => $request->hasFile('receipt_image')
                        ? $request->file('receipt_image')->store('sales/receipts', 'public')
                        : null,
                    'status' => $request->status,
                    'notes' => $request->notes,
                    'created_by' => $request->user()->id,
                ]);

                foreach ($items as $item) {
                    $sale->sale_items()->create($item);
                    Batch::where('id', $item['batch_id'])->decrement('quantity', $item['quantity']);
                }

                return $sale;
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if (! empty($lowStockBatchIds)) {
            Batch::with('medicines')->whereIn('id', $lowStockBatchIds)->get()->each(
                fn ($batch) => $request->user()->notify(new LowStockAlert($batch))
            );
        }

        return redirect()->route('sale.index')->with('success', 'تم اضافة فاتورة البيع بنجاح.');
    }
}
