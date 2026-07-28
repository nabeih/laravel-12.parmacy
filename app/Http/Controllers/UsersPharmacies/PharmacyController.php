<?php

namespace App\Http\Controllers\UsersPharmacies;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;

class PharmacyController extends Controller
{
    /**
     * Pharmacies are created via PharmacyRequestController's approval flow
     * (a pharmacist proposes, admin reviews/finalizes) — this is a read-only
     * list of already-approved pharmacies.
     */
    public function index()
    {
        $pharmacies = Pharmacy::with('pharmacists.users')->latest()->get();
        return view('Pharmacy.index', compact('pharmacies'));
    }

    public function suspend($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->status = 'suspended';
        $pharmacy->save();

        return redirect()->route('pharmacy.index')->with('success', 'Pharmacy suspended successfully.');
    }
    public function activate($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->status = 'opne';
        $pharmacy->save();

        return redirect()->route('pharmacy.index')->with('success', 'Pharmacy activated successfully.');
    }
}
