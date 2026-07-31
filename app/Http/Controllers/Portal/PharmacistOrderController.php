<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacistOrderController extends Controller
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['ready', 'cancelled'],
        'ready' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function index(Request $request): View
    {
        $pharmacy = $request->user()->pharmacists?->pharmacies;

        $orders = $pharmacy
            ? $pharmacy->orders()->with(['items', 'user'])->latest()->get()
            : collect();

        return view('Pharmacist.orders', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $pharmacy = $request->user()->pharmacists?->pharmacies;

        if (! $pharmacy || $order->pharmacy_id !== $pharmacy->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,processing,ready,delivered,cancelled'],
        ]);

        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($validated['status'], $allowed, true)) {
            return back()->with('error', 'لا يمكن تغيير حالة الطلب إلى هذه الحالة من حالتها الحالية.');
        }

        $order->update(['status' => $validated['status']]);
        $order->user->notify(new OrderStatusUpdated($order));

        return back()->with('success', 'تم تحديث حالة الطلب رقم #'.$order->id.'.');
    }
}
