<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $otp,
        public readonly int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' - Email Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp-verification',
            with: [
                'name' => $this->user->name,
                'otp' => $this->otp,
                'expiresInMinutes' => $this->expiresInMinutes,
                'appName' => config('app.name'),
            ],
        );
    }
}
