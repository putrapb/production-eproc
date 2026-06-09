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

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
        ]);

        $email = $request->email;

        // Generate OTP
        $otp = OtpVerification::generate($email);

        // Send OTP Mail
        Mail::to($email)->send(new OtpMail(
            otpCode:    $otp->otp_code,
            ttlMinutes: config('eprocurement.otp_ttl_minutes', 10),
        ));

        // Save email in session
        session(['reset_password_email' => $email]);

        return redirect()->route('password.reset')
            ->with('info', 'Kode OTP reset password telah dikirim ke email Anda.');
    }
}
