<?php

use App\Models\OtpVerification;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('forgot password screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('forgot password request requires a registered email', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'unregistered@bni.co.id',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('forgot password request generates OTP and redirects to reset password page', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'test@bni.co.id',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'test@bni.co.id',
    ]);

    $response->assertRedirect(route('password.reset'));
    $this->assertDatabaseHas('otp_verifications', [
        'email' => 'test@bni.co.id',
    ]);

    Mail::assertQueued(OtpMail::class, function ($mail) {
        return $mail->hasTo('test@bni.co.id');
    });

    $response->assertSessionHas('reset_password_email', 'test@bni.co.id');
});

test('reset password screen requires active session', function () {
    $response = $this->get('/reset-password');

    $response->assertRedirect(route('password.request'));
});

test('reset password screen can be rendered with active session', function () {
    $response = $this->withSession(['reset_password_email' => 'test@bni.co.id'])
        ->get('/reset-password');

    $response->assertStatus(200);
});

test('user cannot reset password with invalid OTP', function () {
    $user = User::factory()->create([
        'email' => 'test@bni.co.id',
        'password' => Hash::make('oldpassword'),
    ]);

    OtpVerification::create([
        'email' => 'test@bni.co.id',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->withSession(['reset_password_email' => 'test@bni.co.id'])
        ->post('/reset-password', [
            'otp_code' => '654321', // Wrong OTP
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertSessionHasErrors(['otp_code']);
    expect(Hash::check('oldpassword', $user->fresh()->password))->toBeTrue();
});

test('user can reset password with valid OTP', function () {
    $user = User::factory()->create([
        'email' => 'test@bni.co.id',
        'password' => Hash::make('oldpassword'),
    ]);

    OtpVerification::create([
        'email' => 'test@bni.co.id',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->withSession(['reset_password_email' => 'test@bni.co.id'])
        ->post('/reset-password', [
            'otp_code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success');
    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});
