<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Active_Ingredient;
use App\Models\Dosage_Form;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Medicine_Category;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * قائمة الأدوية (بحث/تصفية) — متاح للجميع.
     */
    public function medicines(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $medicines = Medicine::query()
            ->with(['manufacturer', 'category', 'dosageForm', 'activeIngredients'])
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('brand_name_ar', 'like', "%{$q}%")
                        ->orWhere('brand_name_en', 'like', "%{$q}%")
                        ->orWhereHas('manufacturer', fn ($m) => $m->where('name_ar', 'like', "%{$q}%")->orWhere('name_en', 'like', "%{$q}%"))
                        ->orWhereHas('category', fn ($c) => $c->where('name_ar', 'like', "%{$q}%")->orWhere('name_en', 'like', "%{$q}%"));
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => $medicines,
        ]);
    }

    /**
     * عرض دواء واحد.
     */
    public function showMedicine(Medicine $medicine)
    {
        $medicine->load(['manufacturer', 'category', 'dosageForm', 'activeIngredients']);

        return response()->json([
            'status' => true,
            'data' => $medicine,
        ]);
    }

    /**
     * قائمة التصنيفات.
     */
    public function categories()
    {
        return response()->json([
            'status' => true,
            'data' => Medicine_Category::where('is_active', true)->latest()->get(),
        ]);
    }

    /**
     * قائمة أشكال الجرعات.
     */
    public function dosageForms()
    {
        return response()->json([
            'status' => true,
            'data' => Dosage_Form::where('is_active', true)->latest()->get(),
        ]);
    }

    /**
     * قائمة الشركات المصنّعة.
     */
    public function manufacturers()
    {
        return response()->json([
            'status' => true,
            'data' => Manufacturer::where('is_active', true)->latest()->get(),
        ]);
    }

    /**
     * قائمة المواد الفعّالة.
     */
    public function activeIngredients()
    {
        return response()->json([
            'status' => true,
            'data' => Active_Ingredient::where('is_active', true)->latest()->get(),
        ]);
    }

    // ==================== إدارة المدير ====================

    /**
     * كل الأدوية (شامل غير النشط) للمدير.
     */
    public function adminMedicines(Request $request)
    {
        $medicines = Medicine::query()
            ->with(['manufacturer', 'category', 'dosageForm', 'activeIngredients'])
            ->when($request->query('q'), fn ($query, $q) => $query->where('brand_name_en', 'like', "%{$q}%")->orWhere('brand_name_ar', 'like', "%{$q}%"))
            ->latest()
            ->paginate($request->query('per_page', 15));

        return response()->json(['status' => true, 'data' => $medicines]);
    }

    /**
     * إنشاء دواء جديد.
     */
    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'brand_name_en' => 'required|string|max:255',
            'brand_name_ar' => 'required|string|max:255',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'category_id' => 'required|exists:medicine_categories,id',
            'dosage_form_id' => 'required|exists:dosage_forms,id',
            'reference_price' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:medicines,barcode',
            'requires_prescription' => 'nullable|boolean',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'active_ingredient' => 'nullable|array',
            'active_ingredient.*' => 'exists:active_ingredients,id',
            'strength_value' => 'nullable|array',
            'strength_value.*' => 'numeric|min:0',
            'strength_unit' => 'nullable|array',
            'strength_unit.*' => 'string|max:20',
        ]);

        $medicine = Medicine::create([
            'brand_name_en' => $validated['brand_name_en'],
            'brand_name_ar' => $validated['brand_name_ar'],
            'manufacturer_id' => $validated['manufacturer_id'],
            'category_id' => $validated['category_id'],
            'dosage_form_id' => $validated['dosage_form_id'],
            'reference_price' => $validated['reference_price'],
            'barcode' => $validated['barcode'] ?? null,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'description_en' => $validated['description_en'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncActiveIngredients($medicine, $request);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة الدواء بنجاح.',
            'data' => $medicine->load(['manufacturer', 'category', 'dosageForm', 'activeIngredients']),
        ], 201);
    }

    /**
     * تحديث دواء.
     */
    public function updateMedicine(Request $request, Medicine $medicine)
    {
        $request->validate([
            'brand_name_en' => 'sometimes|required|string|max:255',
            'brand_name_ar' => 'sometimes|required|string|max:255',
            'manufacturer_id' => 'sometimes|required|exists:manufacturers,id',
            'category_id' => 'sometimes|required|exists:medicine_categories,id',
            'dosage_form_id' => 'sometimes|required|exists:dosage_forms,id',
            'reference_price' => 'sometimes|required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:medicines,barcode,' . $medicine->id,
            'requires_prescription' => 'nullable|boolean',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'active_ingredient' => 'nullable|array',
            'active_ingredient.*' => 'exists:active_ingredients,id',
            'strength_value' => 'nullable|array',
            'strength_value.*' => 'numeric|min:0',
            'strength_unit' => 'nullable|array',
            'strength_unit.*' => 'string|max:20',
        ]);

        $medicine->update($request->only([
            'brand_name_en', 'brand_name_ar', 'manufacturer_id', 'category_id',
            'dosage_form_id', 'reference_price', 'barcode', 'description_en',
            'description_ar', 'notes', 'requires_prescription', 'is_active',
        ]));

        $this->syncActiveIngredients($medicine, $request, true);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الدواء بنجاح.',
            'data' => $medicine->fresh(['manufacturer', 'category', 'dosageForm', 'activeIngredients']),
        ]);
    }

    /**
     * حذف دواء (soft delete).
     */
    public function destroyMedicine(Medicine $medicine)
    {
        $medicine->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الدواء بنجاح.',
        ]);
    }

    /**
     * ربط المواد الفعّالة مع التركيز والوحدة.
     */
    private function syncActiveIngredients(Medicine $medicine, Request $request, bool $sync = false): void
    {
        if (! $request->filled('active_ingredient')) {
            return;
        }

        $pivot = [];
        foreach ($request->active_ingredient as $index => $ingredientId) {
            $pivot[$ingredientId] = [
                'strength_value' => $request->strength_value[$index] ?? null,
                'strength_unit' => $request->strength_unit[$index] ?? null,
            ];
        }

        if ($sync) {
            $medicine->activeIngredients()->sync($pivot);
        } else {
            $medicine->activeIngredients()->attach($pivot);
        }
    }

    // ======= إدارة التصنيفات =======
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $category = Medicine_Category::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'تمت إضافة التصنيف.', 'data' => $category], 201);
    }

    public function updateCategory(Request $request, Medicine_Category $category)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update($validated);

        return response()->json(['status' => true, 'message' => 'تم تحديث التصنيف.', 'data' => $category]);
    }

    public function destroyCategory(Medicine_Category $category)
    {
        $category->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف التصنيف.']);
    }

    // ======= إدارة أشكال الجرعات =======
    public function storeDosageForm(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $dosageForm = Dosage_Form::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'تمت إضافة الشكل.', 'data' => $dosageForm], 201);
    }

    public function updateDosageForm(Request $request, Dosage_Form $dosageForm)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $dosageForm->update($validated);

        return response()->json(['status' => true, 'message' => 'تم تحديث الشكل.', 'data' => $dosageForm]);
    }

    public function destroyDosageForm(Dosage_Form $dosageForm)
    {
        $dosageForm->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الشكل.']);
    }

    // ======= إدارة الشركات المصنّعة =======
    public function storeManufacturer(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $manufacturer = Manufacturer::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'تمت إضافة الشركة.', 'data' => $manufacturer], 201);
    }

    public function updateManufacturer(Request $request, Manufacturer $manufacturer)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $manufacturer->update($validated);

        return response()->json(['status' => true, 'message' => 'تم تحديث الشركة.', 'data' => $manufacturer]);
    }

    public function destroyManufacturer(Manufacturer $manufacturer)
    {
        $manufacturer->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الشركة.']);
    }

    // ======= إدارة المواد الفعّالة =======
    public function storeActiveIngredient(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $ingredient = Active_Ingredient::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'تمت إضافة المادة الفعّالة.', 'data' => $ingredient], 201);
    }

    public function updateActiveIngredient(Request $request, Active_Ingredient $activeIngredient)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $activeIngredient->update($validated);

        return response()->json(['status' => true, 'message' => 'تم تحديث المادة الفعّالة.', 'data' => $activeIngredient]);
    }

    public function destroyActiveIngredient(Active_Ingredient $activeIngredient)
    {
        $activeIngredient->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف المادة الفعّالة.']);
    }
}
