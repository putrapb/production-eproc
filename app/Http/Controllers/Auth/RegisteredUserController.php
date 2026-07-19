<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\HrEmployee;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form registrasi
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran user baru (cek NIP & kirim OTP)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip'      => ['required', 'string', 'max:20'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email', 'ends_with:@bna.co.id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Error generik + sleep konstan untuk mencegah NIP enumeration & timing attack
        $genericNipError = 'NIP atau data Anda tidak valid, tidak terdaftar, atau tidak memiliki akses ke sistem ini.';

        // Validasi NIP di database HR
        $hrEmployee = HrEmployee::where('nip', $request->nip)->first();

        if (! $hrEmployee) {
            usleep(random_int(100000, 300000)); // 100-300ms constant-time delay
            return back()->withErrors(['nip' => $genericNipError])->withInput();
        }

        // Cek divisi yang diizinkan
        $allowedDivision = config('eprocurement.allowed_division_keyword', 'IT Infrastructure Management');
        if (! str_contains($hrEmployee->division, $allowedDivision)) {
            usleep(random_int(100000, 300000));
            return back()->withErrors(['nip' => $genericNipError])->withInput();
        }

        // Pastikan NIP belum digunakan untuk registrasi
        if ($hrEmployee->user()->exists()) {
            usleep(random_int(100000, 300000));
            return back()->withErrors(['nip' => $genericNipError])->withInput();
        }

        // Tentukan role dari jabatan
        $role = $hrEmployee->deriveRole();

        // Buat akun user (status belum terverifikasi)
        User::create([
            'hr_employee_id'   => $hrEmployee->id,
            'name'             => $hrEmployee->name,
            'email'            => strtolower($request->email),
            'password'         => $request->password, // auto-hashed via cast
            'role'             => $role,
            'email_verified_at' => null,
        ]);

        // Generate & kirim OTP via email
        $otp = OtpVerification::generate(strtolower($request->email));

        Mail::to($request->email)->send(new OtpMail(
            otpCode:    $otp->otp_code,
            ttlMinutes: config('eprocurement.otp_ttl_minutes', 10),
            isResend:   false,
        ));

        // Redirect ke verifikasi OTP
        $request->session()->put('otp_email', strtolower($request->email));

        return redirect()->route('otp.show')
            ->with('info', 'Kode OTP telah dikirim ke email korporat Anda.');
    }
}
