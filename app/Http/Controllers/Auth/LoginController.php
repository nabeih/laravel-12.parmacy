<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showManagerLogin(): View
    {
        return view('auth.manager-login');
    }

    public function managerLogin(Request $request): RedirectResponse
    {
        return $this->attemptLogin($request, 'admin', 'admin.dash');
    }

    public function showPharmacistLogin(): View
    {
        return view('auth.pharmacist-login');
    }

    public function pharmacistLogin(Request $request): RedirectResponse
    {
        return $this->attemptLogin($request, 'pharmacist', 'pharmacist.dashboard');
    }

    public function showUserLogin(): View
    {
        return view('auth.user-login');
    }

    public function userLogin(Request $request): RedirectResponse
    {
        return $this->attemptLogin($request, 'customer', 'user.dashboard');
    }

    protected function attemptLogin(Request $request, string $expectedRole, string $redirectRoute): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->role !== $expectedRole) {
            Auth::logout();

            return back()->withErrors(['email' => 'هذا الحساب غير مصرح له بالدخول من هذه البوابة.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'هذا الحساب موقوف. يرجى التواصل مع الإدارة.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route($redirectRoute);
    }

    public function logout(Request $request): RedirectResponse
    {
        $role = optional(Auth::user())->role;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return match ($role) {
            'admin' => redirect()->route('login.manager'),
            'pharmacist' => redirect()->route('login.pharmacist'),
            default => redirect()->route('login.user'),
        };
    }
}
