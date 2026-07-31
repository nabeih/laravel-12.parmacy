<?php

namespace App\Services;

use App\Exceptions\OtpException;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OtpService
{
    private const OTP_LENGTH = 6;

    private const OTP_VALIDITY_MINUTES = 10;

    /**
     * Generate a fresh OTP for the given user, persist its hash, and email it.
     */
    public function generateAndSend(User $user): void
    {
        $plainOtp = $this->generateOtp();

        $user->forceFill([
            'email_verification_otp_hash' => Hash::make($plainOtp),
            'email_verification_otp_expires_at' => Carbon::now()->addMinutes(self::OTP_VALIDITY_MINUTES),
        ])->save();

        $this->sendMail($user, $plainOtp);
    }

    /**
     * Validate the given OTP for the user and mark the email as verified.
     *
     * @throws OtpException
     */
    public function verify(User $user, string $otp): void
    {
        if ($user->email_verified_at !== null) {
            throw new OtpException('This email address is already verified.', 409);
        }

        if (! $user->email_verification_otp_hash || ! $user->email_verification_otp_expires_at) {
            throw new OtpException('No verification code was requested for this account. Please request a new one.', 404);
        }

        if (Carbon::now()->greaterThan($user->email_verification_otp_expires_at)) {
            throw new OtpException('This verification code has expired. Please request a new one.', 410);
        }

        if (! Hash::check($otp, $user->email_verification_otp_hash)) {
            throw new OtpException('The verification code you entered is incorrect.', 422);
        }

        $user->forceFill([
            'email_verified_at' => Carbon::now(),
            'email_verification_otp_hash' => null,
            'email_verification_otp_expires_at' => null,
        ])->save();
    }

    private function generateOtp(): string
    {
        return (string) random_int(
            (int) str_pad('1', self::OTP_LENGTH, '0'),
            (int) str_pad('', self::OTP_LENGTH, '9')
        );
    }

    private function sendMail(User $user, string $otp): void
    {
        try {
            Mail::to($user->email)->send(
                new OtpVerificationMail($user, $otp, self::OTP_VALIDITY_MINUTES)
            );
        } catch (Throwable $e) {
            Log::error('Failed to send OTP verification email.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
