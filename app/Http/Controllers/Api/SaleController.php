<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Sale;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    private function currentPharmacy(Request $request)
    {
        return $request->user()->pharmacists->pharmacies;
    }

    /**
     * فواتير البيع الخاصة بالصيدلية الحالية.
     */
    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $sales = Sale::with(['sale_items.medicines', 'users'])
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $sales,
        ]);
    }

    /**
     * إنشاء فاتورة بيع مع خصم المخزون من الدُفعات (أقرب-انتهاء أولاً).
     */
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

                    $batches = Batch::where('pharmacy_id', $pharmacy->id)
                        ->where('medicine_id', $medicineId)
                        ->where('is_active', true)
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
            return response()->json([
                'status' => false,
                'message' => $e->errors(),
            ], 422);
        }

        if (! empty($lowStockBatchIds)) {
            Batch::with('medicines')->whereIn('id', $lowStockBatchIds)->get()->each(
                fn ($batch) => $request->user()->notify(new LowStockAlert($batch))
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة فاتورة البيع بنجاح.',
            'data' => $sale->load('sale_items'),
        ], 201);
    }
}