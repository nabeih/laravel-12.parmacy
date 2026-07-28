<?php

namespace App\Http\Controllers\UsersPharmacies;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\PharmacyAssignment;
use App\Models\PharmacyRequest;
use App\Models\User;
use App\Notifications\NewPharmacyRequestSubmitted;
use App\Notifications\PharmacyRequestReviewed;
use App\Notifications\PharmacyRequestSubmittedConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // ---------------------------------------------------------------
    // Pharmacist side: propose a pharmacy (new or join-existing-vacant),
    // admin approval is what actually creates/reassigns the real Pharmacy row.
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        $pharmacyRequest = PharmacyRequest::with(['pharmacy', 'targetPharmacy'])
            ->where('pharmacist_id', $pharmacist->id)
            ->latest()
            ->first();

        $pharmacist->load('assignments.pharmacy');

        return view('PharmacyRequest.index', compact('pharmacyRequest', 'pharmacist'));
    }

    /**
     * A pharmacist is only ever licensed to be pharmacist-in-charge of one
     * pharmacy *at a time* — once they have one (or a request already
     * pending), they cannot register/join another until they leave it.
     * Returns an error message if blocked, null if clear to proceed.
     */
    private function blockedReason(Pharmacist $pharmacist): ?string
    {
        if ($pharmacist->pharmacies) {
            return 'أنت مسؤول بالفعل عن صيدلية مسجلة باسمك. عليك مغادرتها أولاً قبل تسجيل أو الانضمام إلى صيدلية أخرى.';
        }

        if (PharmacyRequest::where('pharmacist_id', $pharmacist->id)->where('status', 'pending')->exists()) {
            return 'لديك طلب صيدلية قيد المراجعة بالفعل.';
        }

        return null;
    }

    public function create(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        if ($reason = $this->blockedReason($pharmacist)) {
            return redirect()->route('pharmacy_request.index')->with('error', $reason);
        }

        $vacantPharmacies = Pharmacy::whereNull('pharmacist_id')->get();

        return view('PharmacyRequest.create', compact('vacantPharmacies'));
    }

    public function store(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        if ($reason = $this->blockedReason($pharmacist)) {
            return redirect()->route('pharmacy_request.index')->with('error', $reason);
        }

        if ($request->filled('target_pharmacy_id')) {
            $validated = $request->validate([
                'target_pharmacy_id' => 'required|exists:pharmacies,id',
            ]);

            $targetPharmacy = Pharmacy::findOrFail($validated['target_pharmacy_id']);
            if ($targetPharmacy->pharmacist_id !== null) {
                return back()->withErrors(['target_pharmacy_id' => 'هذه الصيدلية لم تعد شاغرة، اختر صيدلية أخرى.']);
            }

            $pharmacyRequest = PharmacyRequest::create([
                'pharmacist_id' => $pharmacist->id,
                'target_pharmacy_id' => $targetPharmacy->id,
                'status' => 'pending',
            ]);
        } else {
            $validated = $request->validate($this->newPharmacyRules());

            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('pharmacy-requests/logos', 'public');
            }
            if ($request->hasFile('license_document')) {
                $validated['license_document'] = $request->file('license_document')->store('pharmacy-requests/documents', 'public');
            }

            $validated['pharmacist_id'] = $pharmacist->id;
            $validated['status'] = 'pending';

            $pharmacyRequest = PharmacyRequest::create($validated);
        }

        Notification::send(User::where('role', 'admin')->get(), new NewPharmacyRequestSubmitted($pharmacyRequest));
        $request->user()->notify(new PharmacyRequestSubmittedConfirmation($pharmacyRequest));

        return redirect()->route('pharmacy_request.index')
            ->with('success', 'تم إرسال طلب الصيدلية إلى الإدارة للمراجعة.');
    }

    /**
     * The pharmacist steps down from their current pharmacy, closing the
     * open work-history entry and leaving it vacant for someone else to
     * take over (or for the admin to await a new pharmacist).
     */
    public function leave(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;
        $pharmacy = $pharmacist?->pharmacies;

        if (! $pharmacy) {
            return redirect()->route('pharmacy_request.index')->with('error', 'لا توجد صيدلية لمغادرتها.');
        }

        DB::transaction(function () use ($pharmacist, $pharmacy) {
            PharmacyAssignment::where('pharmacist_id', $pharmacist->id)
                ->where('pharmacy_id', $pharmacy->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            $pharmacy->update(['pharmacist_id' => null, 'status' => 'suspended']);
        });

        return redirect()->route('pharmacy_request.index')
            ->with('success', 'لقد غادرت الصيدلية. يمكنك الآن تسجيل صيدلية جديدة أو الانضمام إلى صيدلية شاغرة.');
    }

    // ---------------------------------------------------------------
    // Admin side: reviews/finalizes the submitted details before the
    // real Pharmacy row is created or reassigned.
    // ---------------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $status = $request->query('status');

        $requests = PharmacyRequest::with(['pharmacist.users', 'targetPharmacy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.PharmacyRequest.index', compact('requests', 'status'));
    }

    public function review($id)
    {
        $pharmacyRequest = PharmacyRequest::with(['pharmacist.users', 'targetPharmacy'])->findOrFail($id);

        return view('admin.PharmacyRequest.review', compact('pharmacyRequest'));
    }

    public function approve(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::with(['pharmacist', 'targetPharmacy'])->findOrFail($id);

        if ($pharmacyRequest->status !== 'pending') {
            return redirect()->route('admin.pharmacy_request.index')
                ->with('error', 'تمت مراجعة هذا الطلب بالفعل.');
        }

        // Defense in depth: a pharmacist can only ever be in charge of one
        // pharmacy at a time — refuse rather than hit the DB's unique-index error.
        if ($pharmacyRequest->pharmacist->pharmacies) {
            return redirect()->route('admin.pharmacy_request.index')
                ->with('error', 'هذا الصيدلي مسؤول بالفعل عن صيدلية أخرى، لا يمكن اعتماد صيدلية إضافية له.');
        }

        if ($pharmacyRequest->target_pharmacy_id) {
            // Join-existing-vacant path.
            $validated = $request->validate([
                'status' => 'required|in:opne,closed,suspended',
                'is_verified' => 'nullable|boolean',
            ]);

            $pharmacy = $pharmacyRequest->targetPharmacy;

            // Race-condition guard: someone else may have taken it since review.
            if (! $pharmacy || $pharmacy->pharmacist_id !== null) {
                return redirect()->route('admin.pharmacy_request.index')
                    ->with('error', 'هذه الصيدلية لم تعد شاغرة.');
            }

            $pharmacy->update([
                'pharmacist_id' => $pharmacyRequest->pharmacist_id,
                'status' => $validated['status'],
                'is_verified' => $request->boolean('is_verified'),
            ]);
        } else {
            // New-registration path.
            $validated = $request->validate($this->approveNewPharmacyRules());

            $pharmacy = Pharmacy::create([
                'pharmacist_id' => $pharmacyRequest->pharmacist_id,
                'name_ar' => $validated['name_ar'],
                'name_en' => $validated['name_en'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'logo' => $pharmacyRequest->logo,
                'opening_time' => $validated['opening_time'] ?? null,
                'closing_time' => $validated['closing_time'] ?? null,
                'status' => $validated['status'],
                'is_verified' => $request->boolean('is_verified'),
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

        return redirect()->route('admin.pharmacy_request.index')
            ->with('success', 'تمت الموافقة على الصيدلية.');
    }

    private function approveNewPharmacyRules(): array
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
            'status' => 'required|in:opne,closed,suspended',
            'is_verified' => 'nullable|boolean',
        ];
    }

    public function reject(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::with('pharmacist.users')->findOrFail($id);

        if ($pharmacyRequest->status !== 'pending') {
            return redirect()->route('admin.pharmacy_request.index')
                ->with('error', 'تمت مراجعة هذا الطلب بالفعل.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $pharmacyRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $pharmacyRequest->pharmacist->users->notify(new PharmacyRequestReviewed($pharmacyRequest));

        return redirect()->route('admin.pharmacy_request.index')
            ->with('success', 'تم رفض الطلب.');
    }
}
