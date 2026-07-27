<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Concerns\ScopesToPharmacy;
use App\Http\Controllers\Controller;
use App\Models\Active_Ingredient;
use App\Models\Dosage_Form;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Medicine_Category;
use App\Models\MedicineRequest;
use App\Models\User;
use App\Notifications\MedicineRequestReviewed;
use App\Notifications\MedicineRequestSubmittedConfirmation;
use App\Notifications\NewMedicineRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MedicineRequestController extends Controller
{
    use ScopesToPharmacy;

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

    private function catalogOptions(): array
    {
        return [
            'manufacturers' => Manufacturer::where('is_active', 1)->get(),
            'categories' => Medicine_Category::where('is_active', 1)->get(),
            'active_ingredients' => Active_Ingredient::where('is_active', 1)->get(),
            'dosage_forms' => Dosage_Form::where('is_active', 1)->get(),
        ];
    }

    private function syncIngredients($model, Request $request): void
    {
        foreach ($request->active_ingredient as $index => $activeIngredientId) {
            $model->activeIngredients()->attach($activeIngredientId, [
                'strength_value' => $request->strength_value[$index],
                'strength_unit' => $request->strength_unit[$index],
            ]);
        }
    }

    // ---------------------------------------------------------------
    // Pharmacist side: submit a proposal, never touches Medicine itself.
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $pharmacy = $this->currentPharmacy($request);

        $requests = MedicineRequest::with('medicine')
            ->where('pharmacy_id', $pharmacy->id)
            ->latest()
            ->get();

        return view('MedicineRequest.index', compact('requests'));
    }

    public function create()
    {
        return view('MedicineRequest.create', $this->catalogOptions());
    }

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

        $medicineRequest->load('pharmacy');
        Notification::send(User::where('role', 'admin')->get(), new NewMedicineRequestSubmitted($medicineRequest));
        $request->user()->notify(new MedicineRequestSubmittedConfirmation($medicineRequest));

        return redirect()->route('medicine_request.index')
            ->with('success', 'تم إرسال طلب الدواء إلى الإدارة للمراجعة.');
    }

    // ---------------------------------------------------------------
    // Admin side: the catalog stays admin-owned — approving writes the
    // admin's final (possibly corrected) values, not a blind copy.
    // ---------------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $status = $request->query('status');

        $requests = MedicineRequest::with(['pharmacy', 'requestedBy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.MedicineRequest.index', compact('requests', 'status'));
    }

    public function review($id)
    {
        $medicineRequest = MedicineRequest::with('activeIngredients')->findOrFail($id);

        return view('admin.MedicineRequest.review', array_merge(
            $this->catalogOptions(),
            compact('medicineRequest')
        ));
    }

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

        $medicineRequest->requestedBy->notify(new MedicineRequestReviewed($medicineRequest));

        return redirect()->route('admin.medicine_request.index')
            ->with('success', 'تمت الموافقة على الطلب وإضافة الدواء إلى الكتالوج.');
    }

    public function reject(Request $request, $id)
    {
        $medicineRequest = MedicineRequest::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $medicineRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $medicineRequest->requestedBy->notify(new MedicineRequestReviewed($medicineRequest));

        return redirect()->route('admin.medicine_request.index')
            ->with('success', 'تم رفض الطلب.');
    }
}
