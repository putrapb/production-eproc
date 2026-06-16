<?php

use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('user can register with valid NIP from allowed division', function () {
    HrEmployee::factory()->requester()->create(['nip' => '9900000001']);

    $response = $this->post('/register', [
        'nip'                   => '9900000001',
        'email'                 => 'test@bni.co.id',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('otp.show'));
    expect(User::where('email', 'test@bni.co.id')->exists())->toBeTrue();
    expect(User::where('email', 'test@bni.co.id')->first()->email_verified_at)->toBeNull();
});

test('registration is rejected when NIP is not found in HR database', function () {
    $response = $this->post('/register', [
        'nip'                   => '0000000000',
        'email'                 => 'ghost@bni.co.id',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('nip');
    expect(User::where('email', 'ghost@bni.co.id')->exists())->toBeFalse();
});

test('registration is rejected when NIP belongs to outside division', function () {
    HrEmployee::factory()->outsideDivision()->create(['nip' => '9900000002']);

    $response = $this->post('/register', [
        'nip'                   => '9900000002',
        'email'                 => 'finance@bni.co.id',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('nip');
    expect(User::where('email', 'finance@bni.co.id')->exists())->toBeFalse();
});

test('registration is rejected if NIP already has an account', function () {
    $hr = HrEmployee::factory()->requester()->create(['nip' => '9900000003']);
    User::factory()->create(['hr_employee_id' => $hr->id]);

    $response = $this->post('/register', [
        'nip'                   => '9900000003',
        'email'                 => 'newaccount@bni.co.id',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('nip');
});

test('registration is rejected if email is not from corporate domain', function () {
    HrEmployee::factory()->requester()->create(['nip' => '9900000004']);

    $response = $this->post('/register', [
        'nip'                   => '9900000004',
        'email'                 => 'bademail@gmail.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(User::where('email', 'bademail@gmail.com')->exists())->toBeFalse();
});
