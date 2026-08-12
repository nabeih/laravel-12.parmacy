<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\MedicineRequest;
use App\Models\Pharmacy;
use App\Models\PharmacyRequest;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * إحصائيات لوحة المدير.
     */
    public function admin(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'users_count' => \App\Models\User::count(),
                'pharmacies_count' => Pharmacy::count(),
                'medicines_count' => Medicine::count(),
                'pending_medicine_requests' => MedicineRequest::where('status', 'pending')->count(),
                'pending_pharmacy_requests' => PharmacyRequest::where('status', 'pending')->count(),
                'total_sales' => Sale::sum('final_amount'),
                'total_purchases' => Purchase::sum('total_amount'),
                'recent_medicine_requests' => MedicineRequest::with('pharmacy')->latest()->take(5)->get(),
                'recent_pharmacy_requests' => PharmacyRequest::with('pharmacist.users')->latest()->take(5)->get(),
            ],
        ]);
    }

    /**
     * إحصائيات لوحة الصيدلاني (صيدليته).
     */
    public function pharmacist(Request $request)
    {
        $pharmacy = $request->user()->pharmacists?->pharmacies;

        if (! $pharmacy) {
            return response()->json(['status' => false, 'message' => 'لا توجد صيدلية مرتبطة.'], 404);
        }

        Batch::deactivateExpired();

        $lowStock = Batch::where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('quantity', '<=', Batch::LOW_STOCK_THRESHOLD)
            ->with('medicines')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'pharmacy' => $pharmacy,
                'total_sales' => Sale::where('pharmacy_id', $pharmacy->id)->sum('final_amount'),
                'total_purchases' => Purchase::where('pharmacy_id', $pharmacy->id)->sum('total_amount'),
                'low_stock_items' => $lowStock,
                'pending_medicine_requests' => MedicineRequest::where('pharmacy_id', $pharmacy->id)->where('status', 'pending')->count(),
                'active_batches_count' => Batch::where('pharmacy_id', $pharmacy->id)->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * إحصائيات لوحة المستخدم.
     */
    public function customer(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'data' => [
                'active_doses_count' => $user->doses()->where('active', true)->count(),
                'orders_count' => $user->orders()->count(),
                'recent_orders' => $user->orders()->with('pharmacy')->latest()->take(3)->get(),
                'unread_notifications' => $user->unreadNotifications->count(),
            ],
        ]);
    }
}