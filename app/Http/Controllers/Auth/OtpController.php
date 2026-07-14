<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasVerifiedEmail()) {
                return redirect()->route('dashboard');
            }
            $email = $user->email;
        } else {
            // If no OTP email in session, redirect back to register
            if (! $request->session()->has('otp_email')) {
                return redirect()->route('register')
                    ->with('error', 'Sesi verifikasi tidak valid. Silakan daftar ulang.');
            }
            $email = $request->session()->get('otp_email');
        }

        return view('auth.verify-otp', [
            'email' => $email,
        ]);
    }

    /**
     * Handle OTP verification.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        if (auth()->check()) {
            $email = auth()->user()->email;
        } else {
            $email = $request->session()->get('otp_email');
        }

        if (! $email) {
            return redirect()->route('register')
                ->with('error', 'Sesi verifikasi tidak valid. Silakan daftar ulang.');
        }

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts("otp:{$email}", 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn("otp:{$email}");
            return back()->withErrors([
                'otp_code' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $valid = OtpVerification::verify($email, $request->otp_code);

        if (! $valid) {
            \Illuminate\Support\Facades\RateLimiter::hit("otp:{$email}", 60);
            return back()->withErrors([
                'otp_code' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::clear("otp:{$email}");

        // Stamp email_verified_at to activate account
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('register')
                ->with('error', 'Akun tidak ditemukan. Silakan daftar ulang.');
        }

        $user->update(['email_verified_at' => now()]);

        // Clear OTP session
        $request->session()->forget('otp_email');

        if (auth()->check()) {
            return redirect()->route('dashboard')
                ->with('success', 'Akun Anda berhasil diverifikasi.');
        }

        return redirect()->route('login')
            ->with('success', 'Akun Anda berhasil diverifikasi. Silakan login.');
    }

    /**
     * Resend OTP to the same email.
     */
    public function resend(Request $request): RedirectResponse
    {
        if (auth()->check()) {
            $email = auth()->user()->email;
        } else {
            $email = $request->session()->get('otp_email');
        }

        if (! $email) {
            return redirect()->route('register')
                ->with('error', 'Sesi verifikasi tidak valid.');
        }

        $otp = OtpVerification::generate($email);

        Mail::to($email)->send(new OtpMail(
            otpCode:    $otp->otp_code,
            ttlMinutes: config('eprocurement.otp_ttl_minutes', 10),
            isResend:   true,
        ));

        return back()->with('info', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
