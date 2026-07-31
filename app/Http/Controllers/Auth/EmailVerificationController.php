<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function page(Request $request): View
    {
        return view('auth.verify-email', ['email' => $request->query('email', '')]);
    }

    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 404);
        }

        try {
            $this->otpService->verify($user, $request->validated('otp'));
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 404);
        }

        if ($user->email_verified_at !== null) {
            return response()->json([
                'message' => 'This email address is already verified.',
            ], 409);
        }

        $this->otpService->generateAndSend($user);

        return response()->json([
            'message' => 'A new verification code has been sent to your email.',
        ]);
    }
}
