<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
// PUBLIC ROUTES — No authentication required
// ─────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('login'));

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // OTP Verification (after registration)
    Route::get('/verify-otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/verify-otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
});

// ─────────────────────────────────────────────────────────────
// AUTHENTICATED ROUTES — Requires login + email verified
// ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard — all roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile — all roles
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ─── Ticket Routes ───────────────────────────────────────

    // Ticket list & detail — all roles
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

    // IMPORTANT: /tickets/create must be registered BEFORE /tickets/{ticket}
    // to prevent Laravel from matching 'create' as a {ticket} parameter.
    Route::middleware('role:requester')->group(function () {
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

        // Edit (revision re-upload)
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

        // Smart Validation — Requester triggers this after PFA accepts the document
        Route::post('/tickets/{ticket}/validate', [TicketController::class, 'runSmartValidation'])->name('tickets.validate');

        // Cross-fund confirmation — Requester confirms silang dana when over-budget
        Route::post('/tickets/{ticket}/cross-fund', [TicketController::class, 'applyCrossFund'])->name('tickets.cross-fund');
    });

    // PO Download — Requester and PFA only
    Route::middleware('role:requester,pfa')->group(function () {
        Route::get('/tickets/{ticket}/download-po', [PurchaseOrderController::class, 'download'])->name('tickets.download-po');
    });

    // Ticket detail — all roles (AFTER /create route)
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // [PFA only] Document review + PO generation
    Route::middleware('role:pfa')->group(function () {
        Route::post('/tickets/{ticket}/review', [TicketController::class, 'review'])->name('tickets.review');
        Route::post('/tickets/{ticket}/generate-po', [PurchaseOrderController::class, 'generate'])->name('tickets.generate-po');
    });

    // [Department Head only] Forward ticket
    Route::middleware('role:department_head')->group(function () {
        Route::post('/tickets/{ticket}/forward', [TicketController::class, 'forward'])->name('tickets.forward');
    });

    // [Division Head only] Final decision
    Route::middleware('role:division_head')->group(function () {
        Route::post('/tickets/{ticket}/decide', [TicketController::class, 'decide'])->name('tickets.decide');
    });
});
