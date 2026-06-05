<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 *
 * Master seeder that orchestrates all seeder classes in the correct order.
 *
 * Execution order (dependency-aware):
 *  1. HrEmployeeSeeder — must run first (Users reference hr_employees)
 *  2. UserSeeder       — requires hr_employees to exist (linked by NIP)
 *  3. BudgetSeeder     — independent, no FK constraints
 *
 * Demo credentials (all passwords: "password"):
 * ┌──────────────────────┬─────────────────────────┬──────────────────┐
 * │ Role                 │ Email                   │ Nama             │
 * ├──────────────────────┼─────────────────────────┼──────────────────┤
 * │ Requester (PM Staff) │ raihan@bni.co.id        │ Raihan Ardiansyah│
 * │ PFA (Procurement)    │ bintang@bni.co.id       │ Bintang Permana  │
 * │ Department Head      │ haikal@bni.co.id        │ Haikal Fadhilah  │
 * │ Division Head        │ putra@bni.co.id         │ Putra Bagas P.   │
 * └──────────────────────┴─────────────────────────┴──────────────────┘
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HrEmployeeSeeder::class, // 1. HR records (must be first)
            UserSeeder::class,       // 2. User accounts (references HR)
            BudgetSeeder::class,     // 3. Budget pagu anggaran
        ]);
    }
}
