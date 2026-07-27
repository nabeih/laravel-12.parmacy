<?php

namespace App\Http\Controllers\UsersPharmacies;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacyRequest;
use App\Models\User;
use App\Notifications\NewPharmacyRequestSubmitted;
use App\Notifications\PharmacyRequestReviewed;
use App\Notifications\PharmacyRequestSubmittedConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PharmacyRequestController extends Controller
{
    private function validationRules(): array
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
    // Pharmacist side: propose a pharmacy, admin approval is what
    // actually creates the real Pharmacy row.
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        $pharmacyRequest = PharmacyRequest::with('pharmacy')->where('pharmacist_id', $pharmacist->id)->latest()->first();

        return view('PharmacyRequest.index', compact('pharmacyRequest', 'pharmacist'));
    }

    public function create(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        if ($pharmacist->pharmacies || PharmacyRequest::where('pharmacist_id', $pharmacist->id)->where('status', 'pending')->exists()) {
            return redirect()->route('pharmacy_request.index');
        }

        return view('PharmacyRequest.create');
    }

    public function store(Request $request)
    {
        $pharmacist = $request->user()->pharmacists;

        if (! $pharmacist || $pharmacist->status !== 'approved') {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك كصيدلي أولاً قبل تسجيل صيدلية.');
        }

        if ($pharmacist->pharmacies || PharmacyRequest::where('pharmacist_id', $pharmacist->id)->where('status', 'pending')->exists()) {
            return redirect()->route('pharmacy_request.index');
        }

        $validated = $request->validate($this->validationRules());

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('pharmacy-requests/logos', 'public');
        }
        if ($request->hasFile('license_document')) {
            $validated['license_document'] = $request->file('license_document')->store('pharmacy-requests/documents', 'public');
        }

        $validated['pharmacist_id'] = $pharmacist->id;
        $validated['status'] = 'pending';

        $pharmacyRequest = PharmacyRequest::create($validated);

        Notification::send(User::where('role', 'admin')->get(), new NewPharmacyRequestSubmitted($pharmacyRequest));
        $request->user()->notify(new PharmacyRequestSubmittedConfirmation($pharmacyRequest));

        return redirect()->route('pharmacy_request.index')
            ->with('success', 'تم إرسال طلب تسجيل الصيدلية إلى الإدارة للمراجعة.');
    }

    // ---------------------------------------------------------------
    // Admin side: reviews/finalizes the submitted details before the
    // real Pharmacy row is created.
    // ---------------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $status = $request->query('status');

        $requests = PharmacyRequest::with('pharmacist.users')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.PharmacyRequest.index', compact('requests', 'status'));
    }

    public function review($id)
    {
        $pharmacyRequest = PharmacyRequest::with('pharmacist.users')->findOrFail($id);

        return view('admin.PharmacyRequest.review', compact('pharmacyRequest'));
    }

    public function approve(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::findOrFail($id);

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
            'logo' => $pharmacyRequest->logo,
            'opening_time' => $validated['opening_time'] ?? null,
            'closing_time' => $validated['closing_time'] ?? null,
            'status' => $validated['status'],
            'is_verified' => $request->boolean('is_verified'),
        ]);

        $pharmacyRequest->update([
            'pharmacy_id' => $pharmacy->id,
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $pharmacyRequest->pharmacist->users->notify(new PharmacyRequestReviewed($pharmacyRequest));

        return redirect()->route('admin.pharmacy_request.index')
            ->with('success', 'تمت الموافقة على الصيدلية وإضافتها إلى النظام.');
    }

    public function reject(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::with('pharmacist.users')->findOrFail($id);

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
