<?php

namespace App\Http\Controllers\UsersPharmacies;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function index()
    {
        $pharmacies = Pharmacy::with('pharmacists.users')->latest()->get();
        return view('Pharmacy.index', compact('pharmacies'));
    }

    public function create()
    {
        $pharmacists = Pharmacist::with('users')->whereDoesntHave('pharmacies')->get();
        return view('Pharmacy.create', compact('pharmacists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pharmacist_id' => 'required|exists:pharmacists,id|unique:pharmacies,pharmacist_id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'phone' => 'required|string|max:13',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'status' => 'required|in:opne,closed,suspended',
            'is_verified' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('pharmacies/logos', 'public');
        }
        $validated['is_verified'] = $request->boolean('is_verified');

        Pharmacy::create($validated);

        return redirect()->route('pharmacy.index')->with('success', 'تم اضافة الصيدلية بنجاح.');
    }
}
