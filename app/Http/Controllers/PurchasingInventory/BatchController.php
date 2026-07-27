<?php

namespace App\Http\Controllers\PurchasingInventory;

use App\Http\Controllers\Concerns\ScopesToPharmacy;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    use ScopesToPharmacy;

    /**
     * Batches are created/merged automatically from purchase invoices
     * (see PurchaseController@store) — this is a read-only inventory view.
     */
    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        Batch::deactivateExpired();

        $batches = Batch::with('medicines')
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return view('Batch.index', compact('batches'));
    }
}
