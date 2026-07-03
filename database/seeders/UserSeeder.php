<?php

namespace Database\Seeders;

use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 *
 * Seeds 3 system user accounts for the new 3-role system.
 * Accounts are pre-verified (email_verified_at = now()) so they can
 * log in immediately without going through the OTP flow during demos.
 *
 * Credentials:
 *  - Email format: {firstname}@bna.co.id
 *  - Password: "password" (all users — for demo purposes only)
 *
 * Roles:
 *  - requester      : Creates procurement tickets + uploads supporting docs
 *  - team_leader    : Reviews documents + generates procurement form
 *  - department_head: Final approve/decline decision
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ── Requester ───────────────────────────────────────────────
            [
                'nip'   => '2024001001',
                'email' => 'raihan@bna.co.id',
                'role'  => 'requester',
            ],
            // ── Team Leader (Document checker + Form generator) ─────────
            [
                'nip'   => '2024001003',
                'email' => 'haikal@bna.co.id',
                'role'  => 'team_leader',
            ],
            // ── Department Head (Final decision maker) ───────────────────
            [
                'nip'   => '2024001004',
                'email' => 'putra@bna.co.id',
                'role'  => 'department_head',
            ],
        ];

        foreach ($accounts as $account) {
            $hr = HrEmployee::where('nip', $account['nip'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'hr_employee_id'    => $hr->id,
                    'name'              => $hr->name,
                    'email'             => $account['email'],
                    'password'          => Hash::make('password'),
                    'role'              => $account['role'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
