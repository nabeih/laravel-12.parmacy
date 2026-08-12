<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    /**
     * تسجيل الدخول — يعيد توكن (Bearer) يُرسل لاحقاً في Authorization.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'هذا الحساب موقوف. يرجى التواصل مع الإدارة.',
            ], 403);
        }

        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * عرض بيانات المستخدم الحالي.
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * تسجيل الخروج — إبطال التوكن الحالي.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->api_token = null;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الخروج بنجاح.',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'تعذر تسجيل الخروج.',
        ], 400);
    }

    /**
     * تسجيل مستخدم (عميل).
     */
    public function registerUser(Request $request)
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

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الحساب. أُرسل كود تحقق عبر البريد الإلكتروني.',
            'user' => $this->userPayload($user),
        ], 201);
    }

    /**
     * تسجيل صيدلاني (حسابه قيد المراجعة حتى يقرّه المدير).
     */
    public function registerPharmacist(Request $request)
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

        [$user, $pharmacist] = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'pharmacist',
                'is_active' => true,
            ]);

            $pharmacist = \App\Models\Pharmacist::create([
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

        $this->otpService->generateAndSend($user);

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء حسابك. طلبك الآن قيد مراجعة الإدارة.',
            'user' => $this->userPayload($user),
            'pharmacist_status' => 'pending',
        ], 201);
    }

    /**
     * التحقق من كود OTP المرسَل عبر البريد.
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        try {
            $this->otpService->verify($user, $validated['code']);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], method_exists($e, 'getCode') && $e->getCode() ? $e->getCode() : 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم التحقق من البريد الإلكتروني بنجاح.',
        ]);
    }

    /**
     * إعادة إرسال كود التحقق.
     */
    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $this->otpService->generateAndSend($user);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني.',
        ]);
    }

    /**
     * إخفاء الحقول الحساسة وإرفاق العلاقات الخاصة بكل دور.
     */
    private function userPayload(User $user): array
    {
        $user->makeHidden(['password', 'remember_token', 'api_token', 'email_verification_otp_hash', 'email_verification_otp_expires_at']);

        $payload = $user->toArray();

        if ($user->role === 'pharmacist') {
            $payload['pharmacist'] = $user->pharmacists;
            $payload['pharmacy'] = $user->pharmacists?->pharmacies;
        }

        return $payload;
    }
}
