<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Pharmacy;
use Illuminate\Http\Request;

trait ScopesToPharmacy
{
    protected function currentPharmacy(Request $request): Pharmacy
    {
        return $request->user()->pharmacists->pharmacies;
    }
}
