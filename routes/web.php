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

// Rute publik (tanpa login)

Route::get('/', fn () => redirect()->route('login'));

// Webhook post-deployment (aman dengan header rahasia)
Route::get('/api/deploy/post-update', [DeployController::class, 'postUpdate'])
    ->name('deploy.post-update')
    ->middleware(\App\Http\Middleware\VerifyDeploySecret::class);

// Verifikasi publik via QR Code
Route::get('/verify/{ticket}', [TicketController::class, 'verifyPublic'])->name('tickets.verify')->middleware('signed');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1'); // H-2: limit 5 req/menit

    // Lupa password (via OTP)
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Verifikasi OTP
Route::get('/verify-otp', [OtpController::class, 'show'])->name('verification.notice');
Route::get('/verify-otp-alias', fn () => redirect()->route('verification.notice'))->name('otp.show');
Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:5,1');
Route::post('/verify-otp/resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:1,1');

// Rute pengguna login & terverifikasi

Route::middleware(['auth', 'verified'])->group(function () {

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Bantuan & FAQ
    Route::get('/faq', fn () => view('faq.index'))->name('faq.index');

    // Profil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Pengaturan
    Route::get('/settings', fn () => view('settings.index'))->name('settings.index');

    // Daftar & detail tiket
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/export', [TicketController::class, 'export'])
        ->middleware('throttle:5,1')
        ->name('tickets.export');

    // PENTING: Rute create harus di atas rute {ticket}
    Route::middleware('role:requester')->group(function () {
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

        // Form revisi
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

        // Jalankan smart validation
        Route::post('/tickets/{ticket}/validate', [TicketController::class, 'runSmartValidation'])->name('tickets.validate');

        // Preview smart validation (AJAX)
        Route::post('/tickets/preview-validation', [TicketController::class, 'previewValidation'])->name('tickets.preview-validation');

        // Konfirmasi silang dana (saat over-budget)
        Route::post('/tickets/{ticket}/cross-fund', [TicketController::class, 'applyCrossFund'])->name('tickets.cross-fund');

        // Batalkan tiket
        Route::post('/tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    });

    // Detail dan dokumen tiket
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/document/{ticketDocument}', [TicketController::class, 'streamDocument'])->name('tickets.document');
    Route::get('/tickets/{ticket}/download-po', [PurchaseOrderController::class, 'download'])->name('tickets.download-po');
    Route::get('/tickets/{ticket}/download-audit', [TicketController::class, 'downloadAudit'])->name('tickets.download-audit');

    // Akses khusus Team Leader
    Route::middleware('role:team_leader')->group(function () {
        Route::post('/tickets/{ticket}/review', [TicketController::class, 'review'])->name('tickets.review');
        Route::post('/tickets/{ticket}/generate-form', [PurchaseOrderController::class, 'generate'])->name('tickets.generate-form');
        Route::post('/tickets/bulk-review', [TicketController::class, 'bulkReview'])->name('tickets.bulk-review');
    });

    // Akses khusus Department Head
    Route::middleware('role:department_head')->group(function () {
        Route::post('/tickets/{ticket}/decide', [TicketController::class, 'decide'])->name('tickets.decide');
        Route::post('/tickets/bulk-decide', [TicketController::class, 'bulkDecide'])->name('tickets.bulk-decide');
    });

    // Log audit
    Route::middleware('role:team_leader,department_head')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});
