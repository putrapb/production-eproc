<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user()->load('hrEmployee'),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.min'                       => 'Password baru minimal 8 karakter.',
            'password.confirmed'                 => 'Konfirmasi password tidak sesuai.',
        ]);

        $request->user()->update([
            'password' => $request->password, // auto-hashed via model cast
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
