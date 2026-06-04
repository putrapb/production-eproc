<?php

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('OTP verification page requires otp_email in session', function () {
    $this->get('/verify-otp')->assertRedirect(route('register'));
});

test('valid OTP verifies the account and stamps email_verified_at', function () {
    $user = User::factory()->unverified()->create(['email' => 'user@bni.co.id']);

    $otp = OtpVerification::create([
        'email'      => 'user@bni.co.id',
        'otp_code'   => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->withSession(['otp_email' => 'user@bni.co.id'])
        ->post('/verify-otp', ['otp_code' => '123456']);

    $response->assertRedirect(route('login'));
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('wrong OTP code returns validation error', function () {
    User::factory()->unverified()->create(['email' => 'user@bni.co.id']);

    OtpVerification::create([
        'email'      => 'user@bni.co.id',
        'otp_code'   => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->withSession(['otp_email' => 'user@bni.co.id'])
        ->post('/verify-otp', ['otp_code' => '999999']);

    $response->assertSessionHasErrors('otp_code');
});

test('expired OTP returns validation error', function () {
    User::factory()->unverified()->create(['email' => 'user@bni.co.id']);

    OtpVerification::create([
        'email'      => 'user@bni.co.id',
        'otp_code'   => '123456',
        'expires_at' => now()->subMinutes(1), // Already expired
    ]);

    $response = $this->withSession(['otp_email' => 'user@bni.co.id'])
        ->post('/verify-otp', ['otp_code' => '123456']);

    $response->assertSessionHasErrors('otp_code');
});
