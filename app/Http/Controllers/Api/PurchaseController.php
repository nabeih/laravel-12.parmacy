<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    private function currentPharmacy(Request $request)
    {
        return $request->user()->pharmacists->pharmacies;
    }

    /**
     * فواتير الشراء الخاصة بصيدلية الصيدلاني الحالي.
     */
    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $purchases = Purchase::with('purchase_items.medicines')
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $purchases,
        ]);
    }

    /**
     * إنشاء فاتورة شراء وإدخال/دمج المخزون في الدُفعات تلقائياً.
     */
    public function store(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        Batch::deactivateExpired();

        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
            'supplier_name' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'medicine_id' => 'required|array|min:1',
            'medicine_id.*' => 'required|exists:medicines,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'purchase_price' => 'required|array',
            'purchase_price.*' => 'required|numeric|min:0',
            'selling_price' => 'required|array',
            'selling_price.*' => 'required|numeric|min:0',
            'expiry_date' => 'required|array',
            'expiry_date.*' => 'required|date',
            'lot_number' => 'nullable|array',
            'lot_number.*' => 'nullable|string|max:255',
            'manufacturing_date' => 'nullable|array',
            'manufacturing_date.*' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $pharmacy) {
            $totalAmount = 0;
            foreach ($request->quantity as $index => $qty) {
                $totalAmount += $qty * $request->purchase_price[$index];
            }

            $purchase = Purchase::create([
                'pharmacy_id' => $pharmacy->id,
                'invoice_number' => $request->invoice_number,
                'supplier_name' => $request->supplier_name,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->medicine_id as $index => $medicineId) {
                $quantity = (int) $request->quantity[$index];
                $purchasePrice = $request->purchase_price[$index];
                $sellingPrice = $request->selling_price[$index];
                $expiryDate = $request->expiry_date[$index];
                $lotNumber = $request->lot_number[$index] ?? null;
                $manufacturingDate = $request->manufacturing_date[$index] ?? null;

                $purchaseItem = $purchase->purchase_items()->create([
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'expiry_date' => $expiryDate,
                    'lot_number' => $lotNumber,
                    'manufacturing_date' => $manufacturingDate,
                ]);

                $existingBatch = Batch::where('pharmacy_id', $pharmacy->id)
                    ->where('medicine_id', $medicineId)
                    ->where('purchase_price', $purchasePrice)
                    ->where('selling_price', $sellingPrice)
                    ->whereDate('expiry_date', $expiryDate)
                    ->where(function ($w) use ($lotNumber) {
                        $lotNumber ? $w->where('lot_number', $lotNumber) : $w->whereNull('lot_number');
                    })
                    ->where(function ($w) use ($manufacturingDate) {
                        $manufacturingDate ? $w->whereDate('manufacturing_date', $manufacturingDate) : $w->whereNull('manufacturing_date');
                    })
                    ->first();

                if ($existingBatch) {
                    $existingBatch->increment('quantity', $quantity);
                } else {
                    Batch::create([
                        'pharmacy_id' => $pharmacy->id,
                        'medicine_id' => $medicineId,
                        'purchase_item_id' => $purchaseItem->id,
                        'quantity' => $quantity,
                        'purchase_price' => $purchasePrice,
                        'selling_price' => $sellingPrice,
                        'expiry_date' => $expiryDate,
                        'lot_number' => $lotNumber,
                        'manufacturing_date' => $manufacturingDate,
                        'is_active' => ! Carbon::parse($expiryDate)->isPast(),
                    ]);
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة فاتورة الشراء بنجاح.',
        ], 201);
    }
}