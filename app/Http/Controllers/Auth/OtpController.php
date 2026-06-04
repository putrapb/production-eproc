<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // If no OTP email in session, redirect back to register
        if (! $request->session()->has('otp_email')) {
            return redirect()->route('register')
                ->with('error', 'Sesi verifikasi tidak valid. Silakan daftar ulang.');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('otp_email'),
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

        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('register')
                ->with('error', 'Sesi verifikasi tidak valid. Silakan daftar ulang.');
        }

        $valid = OtpVerification::verify($email, $request->otp_code);

        if (! $valid) {
            return back()->withErrors([
                'otp_code' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
            ]);
        }

        // Stamp email_verified_at to activate account
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('register')
                ->with('error', 'Akun tidak ditemukan. Silakan daftar ulang.');
        }

        $user->update(['email_verified_at' => now()]);

        // Clear OTP session
        $request->session()->forget('otp_email');

        return redirect()->route('login')
            ->with('success', 'Akun Anda berhasil diverifikasi. Silakan login.');
    }

    /**
     * Resend OTP to the same email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('register')
                ->with('error', 'Sesi verifikasi tidak valid.');
        }

        $otp = OtpVerification::generate($email);

        \Illuminate\Support\Facades\Mail::raw(
            "Kode OTP verifikasi akun E-Procurement BNI Anda: {$otp->otp_code}\n\nKode ini berlaku selama " . config('eprocurement.otp_ttl_minutes', 10) . " menit.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('[E-Procurement BNI] Kode Verifikasi OTP — Kirim Ulang');
            }
        );

        return back()->with('info', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
