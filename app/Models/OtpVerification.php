<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    /**
     * Standalone table — no foreign keys — for fast read/write during sign-up OTP flow.
     * OTP records expire after TTL defined in config('eprocurement.otp_ttl_minutes').
     */
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Generate a new OTP record for the given email.
     * Deletes any existing OTP for that email first.
     */
    public static function generate(string $email): self
    {
        static::where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'email'      => $email,
            'otp_code'   => $code,
            'expires_at' => now()->addMinutes(config('eprocurement.otp_ttl_minutes', 10)),
        ]);
    }

    /**
     * Verify the given code for an email. Returns true if valid and not expired.
     */
    public static function verify(string $email, string $code): bool
    {
        $record = static::forEmail($email)->valid()->where('otp_code', $code)->first();

        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }
}
