<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    /**
     * دُفعات المخزون الخاصة بصيدلية الصيدلاني الحالي.
     */
    public function index(Request $request)
    {
        Batch::deactivateExpired();

        $pharmacy = $request->user()->pharmacists->pharmacies;

        $batches = Batch::with('medicines')
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $batches,
        ]);
    }
}