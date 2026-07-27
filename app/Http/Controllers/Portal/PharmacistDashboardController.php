<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
use Illuminate\Http\Request;

class PharmacistDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pharmacist = Pharmacist::with('pharmacies')->where('user_id', $request->user()->id)->first();

        return view('pharmacist.dashboard', compact('pharmacist'));
    }
}
