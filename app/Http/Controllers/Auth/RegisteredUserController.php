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
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Flow:
     *  1. Validate NIP exists in hr_employees and belongs to allowed division
     *  2. Derive role from position
     *  3. Create user (unverified)
     *  4. Send OTP to corporate email
     *  5. Redirect to OTP verification page
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip'      => ['required', 'string', 'max:20'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email', 'ends_with:@bni.co.id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Step 1: Validate NIP against HR database
        $hrEmployee = HrEmployee::where('nip', $request->nip)->first();

        if (! $hrEmployee) {
            return back()->withErrors([
                'nip' => 'NIP tidak ditemukan dalam database karyawan.',
            ])->withInput();
        }

        // Step 2: Validate division
        $allowedDivision = config('eprocurement.allowed_division_keyword', 'IT Infrastructure Management');
        if (! str_contains($hrEmployee->division, $allowedDivision)) {
            return back()->withErrors([
                'nip' => 'NIP Anda tidak termasuk dalam Departemen IT Infrastructure Management.',
            ])->withInput();
        }

        // Step 3: Check that this HR record doesn't already have an account
        if ($hrEmployee->user()->exists()) {
            return back()->withErrors([
                'nip' => 'NIP ini sudah terdaftar dalam sistem. Silakan login.',
            ])->withInput();
        }

        // Step 4: Derive role from position
        $role = $hrEmployee->deriveRole();

        // Step 5: Create user (email_verified_at = null until OTP verified)
        User::create([
            'hr_employee_id'   => $hrEmployee->id,
            'name'             => $hrEmployee->name,
            'email'            => strtolower($request->email),
            'password'         => $request->password, // auto-hashed via cast
            'role'             => $role,
            'email_verified_at' => null,
        ]);

        // Step 6: Generate and send OTP via queued Mailable
        $otp = OtpVerification::generate(strtolower($request->email));

        Mail::to($request->email)->send(new OtpMail(
            otpCode:    $otp->otp_code,
            ttlMinutes: config('eprocurement.otp_ttl_minutes', 10),
            isResend:   false,
        ));

        // Step 7: Redirect to OTP verification, passing email in session
        $request->session()->put('otp_email', strtolower($request->email));

        return redirect()->route('otp.show')
            ->with('info', 'Kode OTP telah dikirim ke email korporat Anda.');
    }
}
