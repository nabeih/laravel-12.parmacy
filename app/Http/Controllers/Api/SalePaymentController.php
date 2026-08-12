<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Sale_Payment;
use Illuminate\Http\Request;

class SalePaymentController extends Controller
{
    private function currentPharmacy(Request $request)
    {
        return $request->user()->pharmacists->pharmacies;
    }

    /**
     * دفعات المبيعات الخاصة بالصيدلية الحالية.
     */
    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $payments = Sale_Payment::with('sales')
            ->whereHas('sales', fn ($q) => $q->where('pharmacy_id', $pharmacy->id))
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $payments,
        ]);
    }

    /**
     * إضافة دفعة على فاتورة بيع.
     */
    public function store(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'payment_method' => 'required|string|max:255',
            'transaction_id' => 'nullable|in:bank_transfer,palbay,jawalbay',
            'reference_number' => 'nullable|string|max:255',
            'sender_name' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'receipt_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        // يجب أن تتبع الفاتورة لهذه الصيدلية فعلاً.
        Sale::where('pharmacy_id', $pharmacy->id)->findOrFail($validated['sale_id']);

        if ($request->hasFile('receipt_image')) {
            $validated['receipt_image'] = $request->file('receipt_image')->store('sale-payments/receipts', 'public');
        }

        $payment = Sale_Payment::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة الدفعة بنجاح.',
            'data' => $payment,
        ], 201);
    }
}