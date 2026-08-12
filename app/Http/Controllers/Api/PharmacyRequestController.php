<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyAssignment;
use App\Models\PharmacyRequest;
use App\Notifications\NewPharmacyRequestSubmitted;
use App\Notifications\PharmacyRequestReviewed;
use App\Notifications\PharmacyRequestSubmittedConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PharmacyRequestController extends Controller
{
    private function newPharmacyRules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'phone' => 'required|string|max:13',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    private function blockedReason($pharmacist): ?string
    {
        if ($pharmacist->pharmacies) {
            return 'أنت مسؤول بالفعل عن صيدلية مسجلة باسمك. عليك مغادرتها أولاً.';
        }

        if (PharmacyRequest::where('pharmacist_id', $pharmacist->id)->where('status', 'pending')->exists()) {
            return 'لديك طلب صيدلية قيد المراجعة بالفعل.';
        }

        return null;
    }

    /**
     * طلب الصيدلية الخاص بالصيدلاني الحالي.
     */
    public function index(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.',
            ], 403);
        }

        $pharmacyRequest = PharmacyRequest::with(['pharmacy', 'targetPharmacy'])
            ->where('pharmacist_id', $pharmacist->id)
            ->latest()
            ->first();

        return response()->json([
            'status' => true,
            'data' => $pharmacyRequest,
        ]);
    }

    /**
     * إرسال طلب تسجيل صيدلية جديدة.
     */
    public function store(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.',
            ], 403);
        }

        if ($reason = $this->blockedReason($pharmacist)) {
            return response()->json([
                'status' => false,
                'message' => $reason,
            ], 422);
        }

        $validated = $request->validate($this->newPharmacyRules());

        $data = $validated;
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('pharmacies/logos', 'public');
        }
        if ($request->hasFile('license_document')) {
            $data['license_document'] = $request->file('license_document')->store('pharmacies/licenses', 'public');
        }
        $data['pharmacist_id'] = $pharmacist->id;
        $data['status'] = 'pending';

        $pharmacyRequest = PharmacyRequest::create($data);

        $pharmacyRequest->pharmacist->users->notify(new PharmacyRequestSubmittedConfirmation($pharmacyRequest));
        Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new NewPharmacyRequestSubmitted($pharmacyRequest)
        );

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال طلب تسجيل الصيدلية للمراجعة.',
            'data' => $pharmacyRequest,
        ], 201);
    }

    /**
     * كل طلبات الصيدليات (للمدير).
     */
    public function adminIndex(Request $request)
    {
        $requests = PharmacyRequest::with(['pharmacist.users', 'pharmacy', 'targetPharmacy'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests,
        ]);
    }

    /**
     * اعتماد طلب صيدلية (إنشاء/إعادة تعيين الصيدلية + إنشاء إسناد).
     */
    public function approve(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::with('pharmacist')->findOrFail($id);

        if ($pharmacyRequest->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'تمت مراجعة هذا الطلب بالفعل.',
            ], 422);
        }

        // الانضمام إلى صيدلية شاغرة موجودة
        if ($pharmacyRequest->target_pharmacy_id) {
            $pharmacy = Pharmacy::find($pharmacyRequest->target_pharmacy_id);

            if (! $pharmacy || $pharmacy->status === 'suspended' || $pharmacy->pharmacist_id !== null) {
                return response()->json([
                    'status' => false,
                    'message' => 'هذه الصيدلية لم تعد شاغرة.',
                ], 422);
            }

            $pharmacy->update([
                'pharmacist_id' => $pharmacyRequest->pharmacist_id,
                'is_verified' => true,
            ]);
        } else {
            // تسجيل جديد
            $validated = $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'phone' => 'required|string|max:13',
                'address' => 'required|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'opening_time' => 'nullable',
                'closing_time' => 'nullable',
                'status' => 'required|in:opne,closed,suspended',
                'is_verified' => 'nullable|boolean',
            ]);

            $pharmacy = Pharmacy::create([
                'pharmacist_id' => $pharmacyRequest->pharmacist_id,
                'name_ar' => $validated['name_ar'],
                'name_en' => $validated['name_en'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'opening_time' => $validated['opening_time'] ?? null,
                'closing_time' => $validated['closing_time'] ?? null,
                'status' => $validated['status'],
                'is_verified' => $request->boolean('is_verified', false),
            ]);
        }

        PharmacyAssignment::create([
            'pharmacist_id' => $pharmacyRequest->pharmacist_id,
            'pharmacy_id' => $pharmacy->id,
            'started_at' => now(),
        ]);

        $pharmacyRequest->update([
            'pharmacy_id' => $pharmacy->id,
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $pharmacyRequest->pharmacist->users->notify(new PharmacyRequestReviewed($pharmacyRequest));

        return response()->json([
            'status' => true,
            'message' => 'تمت الموافقة على الصيدلية.',
            'data' => $pharmacy,
        ]);
    }

    /**
     * رفض طلب الصيدلية.
     */
    public function reject(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::with('pharmacist.users')->findOrFail($id);

        if ($pharmacyRequest->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'تمت مراجعة هذا الطلب بالفعل.',
            ], 422);
        }

        $validated = $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        $pharmacyRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $pharmacyRequest->pharmacist->users->notify(new PharmacyRequestReviewed($pharmacyRequest));

        return response()->json([
            'status' => true,
            'message' => 'تم رفض الطلب.',
        ]);
    }
}