<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreOrderRequest;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Order;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['ready', 'cancelled'],
        'ready' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /**
     * طلبات المستخدم (العميل).
     */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items', 'pharmacy'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    /**
     * إنشاء طلب (وصول إلى إيصال الدفع).
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $requestedItems = collect($validated['items']);

        Batch::deactivateExpired();

        $availability = Batch::query()
            ->where('pharmacy_id', $validated['pharmacy_id'])
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->whereIn('medicine_id', $requestedItems->pluck('medicine_id'))
            ->get()
            ->groupBy('medicine_id')
            ->map(fn ($batches) => [
                'quantity' => $batches->sum('quantity'),
                'price' => $batches->min('selling_price'),
            ]);

        $medicines = Medicine::whereIn('id', $requestedItems->pluck('medicine_id'))->get()->keyBy('id');

        $lineItems = [];
        $total = 0;

        foreach ($requestedItems as $line) {
            $medicine = $medicines->get($line['medicine_id']);
            $stock = $availability->get($line['medicine_id']);

            if (! $medicine || ! $stock || $stock['quantity'] < $line['qty']) {
                return response()->json([
                    'status' => false,
                    'message' => 'الكمية المطلوبة من "' . ($medicine->brand_name_ar ?? 'أحد الأدوية') . '" غير متوفرة حالياً في هذه الصيدلية.',
                ], 422);
            }

            $lineItems[] = [
                'medicine_id' => $medicine->id,
                'name_ar' => $medicine->brand_name_ar,
                'name_en' => $medicine->brand_name_en,
                'price' => $stock['price'],
                'qty' => $line['qty'],
            ];
            $total += $stock['price'] * $line['qty'];
        }

        $order = DB::transaction(function () use ($request, $validated, $lineItems, $total) {
            $order = $request->user()->orders()->create([
                'pharmacy_id' => $validated['pharmacy_id'],
                'status' => 'pending',
                'delivery_address' => $validated['delivery_address'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            $order->items()->createMany($lineItems);

            return $order;
        });

        $pharmacistUser = $order->pharmacy->pharmacists?->users;
        $pharmacistUser?->notify(new NewOrderPlaced($order));

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الطلب بنجاح.',
            'data' => $order->load('items'),
        ], 201);
    }

    /**
     * طلبات صيدلية الصيدلاني الحالي.
     */
    public function pharmacistIndex(Request $request)
    {
        $pharmacy = $request->user()->pharmacists?->pharmacies;

        $orders = $pharmacy
            ? $pharmacy->orders()->with(['items', 'user'])->latest()->get()
            : collect();

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    /**
     * تحديث حالة طلب (يتبع مسار الحالات الصحيح).
     */
    public function updateStatus(Request $request, Order $order)
    {
        $pharmacy = $request->user()->pharmacists?->pharmacies;

        if (! $pharmacy || $order->pharmacy_id !== $pharmacy->id) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,processing,ready,delivered,cancelled'],
        ]);

        $allowed = self::TRANSITIONS[$order->status] ?? [];
        if (! in_array($validated['status'], $allowed, true)) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكن تغيير حالة الطلب إلى هذه الحالة من حالتها الحالية.',
            ], 422);
        }

        $order->update(['status' => $validated['status']]);
        $order->user->notify(new OrderStatusUpdated($order));

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الطلب رقم #' . $order->id . '.',
            'data' => $order,
        ]);
    }
}