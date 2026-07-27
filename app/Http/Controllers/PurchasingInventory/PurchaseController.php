<?php

namespace App\Http\Controllers\PurchasingInventory;

use App\Http\Controllers\Concerns\ScopesToPharmacy;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    use ScopesToPharmacy;

    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $purchases = Purchase::with('purchase_items')
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return view('Purchase.index', compact('purchases'));
    }

    public function create(Request $request)
    {
        $medicines = Medicine::with(['manufacturer', 'category', 'dosageForm', 'activeIngredients'])
            ->where('is_active', 1)
            ->get();

        return view('Purchase.create', compact('medicines'));
    }

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

                // Same medicine + same price + same expiry date + same lot/manufacturing date
                // already in stock for this pharmacy => it's physically the same batch, so merge
                // the quantity in. Anything different is a new batch.
                $existingBatch = Batch::where('pharmacy_id', $pharmacy->id)
                    ->where('medicine_id', $medicineId)
                    ->where('purchase_price', $purchasePrice)
                    ->where('selling_price', $sellingPrice)
                    ->whereDate('expiry_date', $expiryDate)
                    ->where(function ($w) use ($lotNumber) {
                        $lotNumber
                            ? $w->where('lot_number', $lotNumber)
                            : $w->whereNull('lot_number');
                    })
                    ->where(function ($w) use ($manufacturingDate) {
                        $manufacturingDate
                            ? $w->whereDate('manufacturing_date', $manufacturingDate)
                            : $w->whereNull('manufacturing_date');
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
                        // Stock received already expired is never sellable.
                        'is_active' => ! Carbon::parse($expiryDate)->isPast(),
                    ]);
                }
            }
        });

        return redirect()->route('purchase.index')->with('success', 'تم اضافة فاتورة الشراء بنجاح.');
    }
}
