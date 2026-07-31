<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
use App\Models\User;
use App\Notifications\NewPharmacistRegistered;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function showUserForm(): View
    {
        return view('auth.user-register');
    }

    public function registerUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->otpService->generateAndSend($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'مرحباً بك! تم إنشاء حسابك بنجاح.');
    }

    public function showPharmacistForm(): View
    {
        return view('auth.pharmacist-register');
    }

    public function registerPharmacist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'national_id' => 'required|string|max:255|unique:pharmacists,national_id',
            'syndicate_number' => 'required|string|max:255|unique:pharmacists,syndicate_number',
            'license_number' => 'required|string|max:255|unique:pharmacists,license_number',
            'graduation_university' => 'required|string|max:255',
            'graduation_year' => 'required|digits:4|integer',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'syndicate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'license_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        [$user, $pharmacist] = DB::transaction(function () use ($request, $validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'pharmacist',
                'is_active' => true,
            ]);

            $pharmacist = Pharmacist::create([
                'user_id' => $user->id,
                'national_id' => $validated['national_id'],
                'syndicate_number' => $validated['syndicate_number'],
                'license_number' => $validated['license_number'],
                'graduation_university' => $validated['graduation_university'],
                'graduation_year' => $validated['graduation_year'],
                'certificate_file' => $request->file('certificate_file')->store('pharmacists/certificates', 'public'),
                'syndicate_file' => $request->file('syndicate_file')->store('pharmacists/syndicate', 'public'),
                'license_file' => $request->file('license_file')->store('pharmacists/licenses', 'public'),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
            ]);

            return [$user, $pharmacist];
        });

        $pharmacist->setRelation('users', $user);
        Notification::send(User::where('role', 'admin')->get(), new NewPharmacistRegistered($pharmacist));

        $this->otpService->generateAndSend($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pharmacist.dashboard')->with('success', 'تم إنشاء حسابك بنجاح. طلبك الآن قيد المراجعة من قبل الإدارة.');
    }
}
