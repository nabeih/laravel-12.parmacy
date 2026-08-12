<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineRequest;
use App\Notifications\MedicineRequestReviewed;
use App\Notifications\MedicineRequestSubmittedConfirmation;
use App\Notifications\NewMedicineRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MedicineRequestController extends Controller
{
    private function currentPharmacy(Request $request)
    {
        return $request->user()->pharmacists->pharmacies;
    }

    private function validationRules(): array
    {
        return [
            'brand_name_en' => 'required|string|max:255',
            'brand_name_ar' => 'required|string|max:255',
            'manufacturer' => 'required|exists:manufacturers,id',
            'category' => 'required|exists:medicine_categories,id',
            'dosage_form' => 'required|exists:dosage_forms,id',
            'reference_price' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:medicines,barcode',
            'requires_prescription' => 'nullable|boolean',
            'description_en' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'active_ingredient' => 'required|array|min:1',
            'active_ingredient.*' => 'required|exists:active_ingredients,id',
            'strength_value' => 'required|array',
            'strength_value.*' => 'required|numeric|min:0',
            'strength_unit' => 'required|array',
            'strength_unit.*' => 'required|string|max:20',
        ];
    }

    private function syncIngredients($model, Request $request): void
    {
        foreach ($request->active_ingredient as $index => $ingredientId) {
            $model->activeIngredients()->attach($ingredientId, [
                'strength_value' => $request->strength_value[$index],
                'strength_unit' => $request->strength_unit[$index],
            ]);
        }
    }

    /**
     * طلبات الأدوية التي أرسلها الصيدلاني (صيدليته).
     */
    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $requests = MedicineRequest::with(['medicine', 'activeIngredients'])
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests,
        ]);
    }

    /**
     * إرسال اقتراح دواء جديد إلى الإدارة.
     */
    public function store(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $request->validate($this->validationRules());

        $medicineRequest = DB::transaction(function () use ($request, $pharmacy) {
            $medicineRequest = MedicineRequest::create([
                'pharmacy_id' => $pharmacy->id,
                'requested_by' => $request->user()->id,
                'brand_name_en' => $request->brand_name_en,
                'brand_name_ar' => $request->brand_name_ar,
                'manufacturer_id' => $request->manufacturer,
                'category_id' => $request->category,
                'dosage_form_id' => $request->dosage_form,
                'reference_price' => $request->reference_price,
                'barcode' => $request->barcode,
                'requires_prescription' => $request->boolean('requires_prescription'),
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            $this->syncIngredients($medicineRequest, $request);

            return $medicineRequest;
        });

        Notification::send(\App\Models\User::where('role', 'admin')->get(), new NewMedicineRequestSubmitted($medicineRequest));
        $request->user()->notify(new MedicineRequestSubmittedConfirmation($medicineRequest));

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال طلب الدواء إلى الإدارة للمراجعة.',
            'data' => $medicineRequest->load('activeIngredients'),
        ], 201);
    }
/**
     * كل طلبات الأدوية (للمدير) مع تصفية اختيارية حسب الحالة.
     */
    public function adminIndex(Request $request)
    {
        $requests = MedicineRequest::with(['pharmacy', 'requestedBy', 'activeIngredients'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests,
        ]);
    }

    /**
     * اعتماد الطلب — إنشاء الدواء الفعلي في الكتالوج بقيم المدير.
     */
    public function approve(Request $request, $id)
    {
        $medicineRequest = MedicineRequest::findOrFail($id);
        $request->validate($this->validationRules());

        DB::transaction(function () use ($request, $medicineRequest) {
            $medicine = Medicine::create([
                'brand_name_en' => $request->brand_name_en,
                'brand_name_ar' => $request->brand_name_ar,
                'manufacturer_id' => $request->manufacturer,
                'category_id' => $request->category,
                'dosage_form_id' => $request->dosage_form,
                'reference_price' => $request->reference_price,
                'barcode' => $request->barcode,
                'requires_prescription' => $request->boolean('requires_prescription'),
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                'notes' => $request->notes,
                'is_active' => true,
            ]);

            $this->syncIngredients($medicine, $request);

            $medicineRequest->update([
                'medicine_id' => $medicine->id,
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        $medicineRequest->requestedBy?->notify(new MedicineRequestReviewed($medicineRequest));

        return response()->json([
            'status' => true,
            'message' => 'تمت الموافقة على الطلب وإضافة الدواء إلى الكتالوج.',
        ]);
    }

    /**
     * رفض الطلب مع (اختياري) ملاحظة إدارية.
     */
    public function reject(Request $request, $id)
    {
        $medicineRequest = MedicineRequest::findOrFail($id);
        $validated = $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        $medicineRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $medicineRequest->requestedBy?->notify(new MedicineRequestReviewed($medicineRequest));

        return response()->json([
            'status' => true,
            'message' => 'تم رفض الطلب.',
        ]);
    }
}