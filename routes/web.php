<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DeployController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
// PUBLIC ROUTES — No authentication required
// ─────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('login'));

// Post-deployment webhook (called by GitHub Actions CI/CD)
Route::get('/api/deploy/post-update', [DeployController::class, 'postUpdate'])->name('deploy.post-update');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Forgot Password & Reset Password (OTP-based)
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// OTP Verification (accessible to both guests and logged-in unverified users)
Route::get('/verify-otp', [OtpController::class, 'show'])->name('verification.notice');
Route::get('/verify-otp-alias', fn () => redirect()->route('verification.notice'))->name('otp.show');
Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:5,1');
Route::post('/verify-otp/resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:1,1');

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

        // Cancel/drop ticket — Requester cancels/declines ticket when in need_to_validate status
        Route::post('/tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    });

    // Ticket detail and documents — all authenticated roles (AFTER /create route)
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/document', [TicketController::class, 'streamDocument'])->name('tickets.document');
    Route::get('/tickets/{ticket}/download-po', [PurchaseOrderController::class, 'download'])->name('tickets.download-po');

    // [PFA only] Document review + PO generation
    Route::middleware('role:pfa')->group(function () {
        Route::post('/tickets/{ticket}/review', [TicketController::class, 'review'])->name('tickets.review');
        Route::post('/tickets/{ticket}/generate-po', [PurchaseOrderController::class, 'generate'])->name('tickets.generate-po');
    });

    // [Team Leader only] Forward ticket + Bulk Forward
    Route::middleware('role:team_leader')->group(function () {
        Route::post('/tickets/{ticket}/forward', [TicketController::class, 'forward'])->name('tickets.forward');
        Route::post('/tickets/bulk-forward', [TicketController::class, 'bulkForward'])->name('tickets.bulk-forward');
    });

    // [Department Head only] Final decision + Bulk Decide
    Route::middleware('role:department_head')->group(function () {
        Route::post('/tickets/{ticket}/decide', [TicketController::class, 'decide'])->name('tickets.decide');
        Route::post('/tickets/bulk-decide', [TicketController::class, 'bulkDecide'])->name('tickets.bulk-decide');
    });

    // [PFA & Department Head only] Audit Logs
    Route::middleware('role:pfa,department_head')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // ─── Notification Routes ─────────────────────────────────
    // All authenticated roles can receive and read notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});
