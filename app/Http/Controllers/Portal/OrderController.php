<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreOrderRequest;
use App\Models\Batch;
use App\Models\Medicine;
use App\Notifications\NewOrderPlaced;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function page(): View
    {
        return view('User.orders');
    }

    public function checkoutPage(): View
    {
        return view('User.checkout');
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['items', 'pharmacy'])
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
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
                    'message' => 'الكمية المطلوبة من "'.($medicine->brand_name_ar ?? 'أحد الأدوية').'" غير متوفرة حالياً في هذه الصيدلية.',
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

        return response()->json(['order' => $order->load('items')], 201);
    }
}
