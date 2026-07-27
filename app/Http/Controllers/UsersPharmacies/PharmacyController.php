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
}
