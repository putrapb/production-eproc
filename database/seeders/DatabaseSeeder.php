<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeds:
     *  1. HR Employees — 4 records representing one employee per role
     *  2. Users — 4 system accounts (1 per role), all verified
     *  3. Budgets — all expenditure_type × category combinations for current fiscal year
     */
    public function run(): void
    {
        // ─── 1. HR Employees ─────────────────────────────────────────
        $hrRequester = HrEmployee::create([
            'nip'      => '1001001001',
            'name'     => 'Budi Santoso',
            'position' => 'Staff IT Infrastructure Project Management',
            'division' => 'IT Infrastructure Management',
        ]);

        $hrPfa = HrEmployee::create([
            'nip'      => '1001001002',
            'name'     => 'Siti Rahayu',
            'position' => 'Staff Procurement & Fixed Assets',
            'division' => 'IT Infrastructure Management',
        ]);

        $hrDeptHead = HrEmployee::create([
            'nip'      => '1001001003',
            'name'     => 'Andi Wijaya',
            'position' => 'Department Head IT Infrastructure',
            'division' => 'IT Infrastructure Management',
        ]);

        $hrDivHead = HrEmployee::create([
            'nip'      => '1001001004',
            'name'     => 'Drs. Hendra Kusuma',
            'position' => 'Division Head IT Infrastructure Management',
            'division' => 'IT Infrastructure Management',
        ]);

        // ─── 2. System Users ─────────────────────────────────────────
        User::create([
            'hr_employee_id'    => $hrRequester->id,
            'name'              => $hrRequester->name,
            'email'             => 'requester@bni.co.id',
            'password'          => Hash::make('password'),
            'role'              => 'requester',
            'email_verified_at' => now(),
        ]);

        User::create([
            'hr_employee_id'    => $hrPfa->id,
            'name'              => $hrPfa->name,
            'email'             => 'pfa@bni.co.id',
            'password'          => Hash::make('password'),
            'role'              => 'pfa',
            'email_verified_at' => now(),
        ]);

        User::create([
            'hr_employee_id'    => $hrDeptHead->id,
            'name'              => $hrDeptHead->name,
            'email'             => 'depthead@bni.co.id',
            'password'          => Hash::make('password'),
            'role'              => 'department_head',
            'email_verified_at' => now(),
        ]);

        User::create([
            'hr_employee_id'    => $hrDivHead->id,
            'name'              => $hrDivHead->name,
            'email'             => 'divhead@bni.co.id',
            'password'          => Hash::make('password'),
            'role'              => 'division_head',
            'email_verified_at' => now(),
        ]);

        // ─── 3. Budget Records ────────────────────────────────────────
        // All 10 combinations: 2 types × 5 categories for fiscal year 2026
        $year       = now()->year;
        $categories = ['hardware', 'software', 'services', 'office_supplies', 'others'];
        $types      = ['CAPEX', 'OPEX'];

        $budgetLimits = [
            'CAPEX' => [
                'hardware'       => 5_000_000_000.00,
                'software'       => 2_000_000_000.00,
                'services'       => 1_000_000_000.00,
                'office_supplies' => 500_000_000.00,
                'others'         => 500_000_000.00,
            ],
            'OPEX' => [
                'hardware'       => 3_000_000_000.00,
                'software'       => 1_500_000_000.00,
                'services'       => 2_000_000_000.00,
                'office_supplies' => 800_000_000.00,
                'others'         => 700_000_000.00,
            ],
        ];

        foreach ($types as $type) {
            foreach ($categories as $category) {
                Budget::create([
                    'expenditure_type' => $type,
                    'category'         => $category,
                    'fiscal_year'      => $year,
                    'total_limit'      => $budgetLimits[$type][$category],
                    'locked_amount'    => 0.00,
                    'used_amount'      => 0.00,
                ]);
            }
        }
    }
}
