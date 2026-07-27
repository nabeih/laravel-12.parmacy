<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Active_Ingredient;
use App\Models\Dosage_Form;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Medicine_Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $medicines = Medicine::with([
            'manufacturer',
            'category',
            'dosageForm',
            'activeIngredients'
        ])
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
                        })
                        ->orWhereHas('activeIngredients', function ($i) use ($q) {
                            $i->where('name_ar', 'like', "%{$q}%")
                                ->orWhere('name_en', 'like', "%{$q}%");
                        });
                });
            })
            ->get();

        return view('admin.medicine.index', compact('medicines', 'q'));
    }

    public function create()
    {

        $manufacturers = Manufacturer::where('is_active', 1)->get();
        $categories = Medicine_Category::where('is_active', 1)->get();
        $active_ingredients = Active_Ingredient::where('is_active', 1)->get();
        $dosage_forms = Dosage_Form::where('is_active', 1)->get();

        return view('admin.medicine.create', compact(
            'manufacturers',
            'categories',
            'active_ingredients',
            'dosage_forms'
        ));
    }

    public function store(Request $request)
    {
        // Validate the request data
        // brand_name_en	brand_name_ar	manufacturer_id	category_id	dosage_form_id	reference_price	barcode	requires_prescription	description_en	description_ar	notes	is_active
        $request->validate([
            'brand_name_en' => 'required|string|max:255',
            'brand_name_ar' => 'required|string|max:255',
            'manufacturer' => 'required|exists:manufacturers,id',
            'category' => 'required|exists:medicine_categories,id',
            'dosage_form' => 'required|exists:dosage_forms,id',
            'reference_price' => 'required|numeric',
            'barcode' => 'required|string|max:255',
            'requires_prescription' => 'nullable|boolean',
            'description_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'active_ingredient' => 'required|exists:active_ingredients,id',
            'notes' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',


        ]);
        // dd($request->all());
        DB::transaction(function () use ($request) {
            $medicine = Medicine::create([
                'brand_name_en' => $request->brand_name_en,
                'brand_name_ar' => $request->brand_name_ar,
                'manufacturer_id' => $request->manufacturer,
                'category_id' => $request->category,
                'dosage_form_id' => $request->dosage_form,
                'reference_price' => $request->reference_price,
                'barcode' => $request->barcode,
                'requires_prescription' => $request->has('requires_prescription') ? 1 : 0,
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                // 'active_ingredient_id' => $request->active_ingredient,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active'),
            ]);

            // Associer les principes actifs à des valeurs de concentration et à des unités
            if ($request->has('active_ingredient')) {
                foreach ($request->active_ingredient as $index => $activeIngredientId) {
                    $strengthValue = $request->strength_value[$index] ?? null;
                    $strengthUnit = $request->strength_unit[$index] ?? null;

                    if ($strengthValue && $strengthUnit) {
                        $medicine->activeIngredients()->attach($activeIngredientId, [
                            'strength_value' => $strengthValue,
                            'strength_unit' => $strengthUnit,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('medicine.index')->with('success', 'تم اضافة دواء للقائمة.');
    }

    public function edit($id)
    {
        $medicine = Medicine::with('activeIngredients')->findOrFail($id);

        $manufacturers = Manufacturer::where('is_active', 1)->get();
        $categories = Medicine_Category::where('is_active', 1)->get();
        $active_ingredients = Active_Ingredient::where('is_active', 1)->get();
        $dosage_forms = Dosage_Form::where('is_active', 1)->get();

        return view('admin.medicine.edit', compact(
            'medicine',
            'manufacturers',
            'categories',
            'active_ingredients',
            'dosage_forms'
        ));
    }

    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        $request->validate([
            'brand_name_en' => 'required|string|max:255',
            'brand_name_ar' => 'required|string|max:255',
            'manufacturer' => 'required|exists:manufacturers,id',
            'category' => 'required|exists:medicine_categories,id',
            'dosage_form' => 'required|exists:dosage_forms,id',
            'reference_price' => 'required|numeric',
            'barcode' => 'required|string|max:255',
            'requires_prescription' => 'nullable|boolean',
            'description_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'active_ingredient' => 'required|exists:active_ingredients,id',
            'notes' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $medicine) {
            $medicine->update([
                'brand_name_en' => $request->brand_name_en,
                'brand_name_ar' => $request->brand_name_ar,
                'manufacturer_id' => $request->manufacturer,
                'category_id' => $request->category,
                'dosage_form_id' => $request->dosage_form,
                'reference_price' => $request->reference_price,
                'barcode' => $request->barcode,
                'requires_prescription' => $request->has('requires_prescription') ? 1 : 0,
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active'),
            ]);

            $syncData = [];
            if ($request->has('active_ingredient')) {
                foreach ($request->active_ingredient as $index => $activeIngredientId) {
                    $strengthValue = $request->strength_value[$index] ?? null;
                    $strengthUnit = $request->strength_unit[$index] ?? null;

                    if ($strengthValue && $strengthUnit) {
                        $syncData[$activeIngredientId] = [
                            'strength_value' => $strengthValue,
                            'strength_unit' => $strengthUnit,
                        ];
                    }
                }
            }
            $medicine->activeIngredients()->sync($syncData);
        });

        return redirect()->route('medicine.index')->with('success', 'تم تعديل الدواء بنجاح.');
    }

    public function destroy($id)
    {
        Medicine::destroy($id);
        return redirect()->route('medicine.index')->with('success', 'تم حذف الدواء بنجاح.');
    }

    public function trash()
    {
        $medicines = Medicine::onlyTrashed()->with([
            'manufacturer',
            'category',
            'dosageForm',
        ])->get();
        return view('admin.medicine.trash', compact('medicines'));
    }

    public function restore($id)
    {
        Medicine::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('medicine.trash')->with('success', 'تم استعادة الدواء بنجاح.');
    }

    public function forceDelete($id)
    {
        Medicine::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('medicine.trash')->with('success', 'تم حذف الدواء نهائياً.');
    }
}
