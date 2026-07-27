<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $pharmacies = Pharmacy::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(9, ['*'], 'pharmacies_page');

        $medicines = Medicine::query()
            ->with(['manufacturer', 'category', 'dosageForm'])
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('brand_name_ar', 'like', "%{$q}%")
                        ->orWhere('brand_name_en', 'like', "%{$q}%")
                        ->orWhereHas('manufacturer', function ($m) use ($q) {
                            $m->where('name_ar', 'like', "%{$q}%")
                                ->orWhere('name_en', 'like', "%{$q}%");
                        })
                        ->orWhereHas('category', function ($c) use ($q) {
                            $c->where('name_ar', 'like', "%{$q}%")
                                ->orWhere('name_en', 'like', "%{$q}%");
                        })
                        ->orWhereHas('dosageForm', function ($d) use ($q) {
                            $d->where('name_ar', 'like', "%{$q}%")
                                ->orWhere('name_en', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->paginate(9, ['*'], 'medicines_page');

        return view('User.search', compact('pharmacies', 'medicines', 'q'));
    }
}
