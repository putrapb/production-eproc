<?php

namespace Database\Seeders;

use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 *
 * Seeds 4 system user accounts, one per role.
 * Accounts are pre-verified (email_verified_at = now()) so they can
 * log in immediately without going through the OTP flow during demos.
 *
 * Credentials:
 *  - Email format: {firstname}@bni.co.id
 *  - Password: "password" (all users — for demo purposes only)
 *
 * Role is determined by the linked HrEmployee's deriveRole(), but we
 * also set it explicitly here for clarity and test safety.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ── Requester ───────────────────────────────────────────────
            [
                'nip'   => '2024001001',
                'email' => 'raihan@bni.co.id',
                'role'  => 'requester',
            ],
            // ── PFA (Procurement & Fixed Assets) ────────────────────────
            [
                'nip'   => '2024001002',
                'email' => 'bintang@bni.co.id',
                'role'  => 'pfa',
            ],
            // ── Department Head ─────────────────────────────────────────
            [
                'nip'   => '2024001003',
                'email' => 'haikal@bni.co.id',
                'role'  => 'department_head',
            ],
            // ── Division Head ───────────────────────────────────────────
            [
                'nip'   => '2024001004',
                'email' => 'putra@bni.co.id',
                'role'  => 'division_head',
            ],
        ];

        foreach ($accounts as $account) {
            $hr = HrEmployee::where('nip', $account['nip'])->firstOrFail();

            User::create([
                'hr_employee_id'    => $hr->id,
                'name'              => $hr->name,
                'email'             => $account['email'],
                'password'          => Hash::make('password'),
                'role'              => $account['role'],
                'email_verified_at' => now(), // pre-verified for demo
            ]);
        }
    }
}
