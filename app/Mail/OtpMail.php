<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OtpMail — Mailable for OTP verification email.
 *
 * Implements ShouldQueue so Laravel will push this to the queue driver
 * configured in config/queue.php (default: 'sync' in testing = immediate).
 *
 * In production: set QUEUE_CONNECTION=database or redis.
 * In testing/dev: QUEUE_CONNECTION=sync runs it synchronously, so test cases
 *                 continue to pass without any queue worker running.
 */
class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param string $otpCode   The 6-digit OTP code to send.
     * @param int    $ttlMinutes OTP validity in minutes (from config).
     * @param bool   $isResend   Whether this is a resend (affects subject line).
     */
    public function __construct(
        public readonly string $otpCode,
        public readonly int    $ttlMinutes,
        public readonly bool   $isResend = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isResend
            ? '[E-Procurement BNI] Kode Verifikasi OTP — Kirim Ulang'
            : '[E-Procurement BNI] Kode Verifikasi OTP';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }

    public function attachments(): array
    {
        return [];
    }
}
