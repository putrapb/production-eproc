<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('reset_password_email')) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi reset password tidak ditemukan atau sudah kadaluarsa. Silakan masukkan email kembali.');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get('reset_password_email'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('reset_password_email');

        if (! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi reset password tidak ditemukan atau sudah kadaluarsa. Silakan masukkan email kembali.');
        }

        // Verify OTP
        $valid = OtpVerification::verify($email, $request->otp_code);

        if (! $valid) {
            return back()->withErrors([
                'otp_code' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Akun tidak ditemukan. Silakan masukkan email kembali.');
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session
        $request->session()->forget('reset_password_email');

        return redirect()->route('login')
            ->with('success', 'Password Anda berhasil diperbarui. Silakan masuk dengan password baru.');
    }
}
