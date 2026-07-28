<?php

namespace App\Http\Controllers\UsersPharmacies;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
use App\Models\User;
use App\Notifications\PharmacistApprovalReviewed;
use Illuminate\Http\Request;

class PharmacistController extends Controller
{
    public function index()
    {
        $pharmacists = Pharmacist::with('users')->latest()->get();
        return view('Pharmacist.index', compact('pharmacists'));
    }

    public function create()
    {
        $users = User::where('role', 'pharmacist')->get();
        return view('Pharmacist.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'national_id' => 'required|string|max:255|unique:pharmacists,national_id',
            'syndicate_number' => 'required|string|max:255|unique:pharmacists,syndicate_number',
            'license_number' => 'required|string|max:255|unique:pharmacists,license_number',
            'graduation_university' => 'required|string|max:255',
            'graduation_year' => 'required|digits:4|integer',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'syndicate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'license_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $validated['certificate_file'] = $request->file('certificate_file')->store('pharmacists/certificates', 'public');
        $validated['syndicate_file'] = $request->file('syndicate_file')->store('pharmacists/syndicate', 'public');
        $validated['license_file'] = $request->file('license_file')->store('pharmacists/licenses', 'public');

        Pharmacist::create($validated);

        return redirect()->route('pharmacist.index')->with('success', 'تم اضافة الصيدلي بنجاح.');
    }

    // ---------------------------------------------------------------
    // Admin approval of a pharmacist's submitted credentials.
    // ---------------------------------------------------------------

    public function review($id)
    {
        $pharmacist = Pharmacist::with('users')->findOrFail($id);

        return view('Pharmacist.review', compact('pharmacist'));
    }

    public function approve(Request $request, $id)
    {
        $pharmacist = Pharmacist::with('users')->findOrFail($id);

        if ($pharmacist->status !== 'pending') {
            return redirect()->route('pharmacist.index')->with('error', 'تمت مراجعة هذا الصيدلي بالفعل.');
        }

        $pharmacist->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $pharmacist->users->notify(new PharmacistApprovalReviewed($pharmacist));

        return redirect()->route('pharmacist.index')->with('success', 'تمت الموافقة على الصيدلي بنجاح.');
    }

    public function reject(Request $request, $id)
    {
        $pharmacist = Pharmacist::with('users')->findOrFail($id);

        if ($pharmacist->status !== 'pending') {
            return redirect()->route('pharmacist.index')->with('error', 'تمت مراجعة هذا الصيدلي بالفعل.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $pharmacist->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'approved_by' => $request->user()->id,
        ]);

        $pharmacist->users->notify(new PharmacistApprovalReviewed($pharmacist));

        return redirect()->route('pharmacist.index')->with('success', 'تم رفض الصيدلي.');
    }
}
