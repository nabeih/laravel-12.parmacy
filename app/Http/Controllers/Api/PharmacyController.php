<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Pharmacy;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    /**
     * قائمة الصيدليات (بحث/استعلام).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pharmacies = Pharmacy::query()
            ->where('status', '!=', 'suspended')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => $pharmacies,
        ]);
    }

    /**
     * صيدلية واحدة + توفر الأدوية النشطة لديها.
     */
    public function show(Pharmacy $pharmacy)
    {
        Batch::deactivateExpired();

        $items = Batch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->with('medicines')
            ->get()
            ->groupBy('medicine_id')
            ->map(fn ($batches) => [
                'medicine' => $batches->first()->medicines,
                'quantity' => $batches->sum('quantity'),
                'price' => $batches->min('selling_price'),
            ])
            ->filter(fn ($item) => $item['medicine'] !== null)
            ->values();

        return response()->json([
            'status' => true,
            'data' => [
                'pharmacy' => $pharmacy,
                'available_items' => $items,
            ],
        ]);
    }
}