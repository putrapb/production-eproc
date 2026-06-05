<?php

namespace Database\Seeders;

use App\Models\HrEmployee;
use Illuminate\Database\Seeder;

/**
 * HrEmployeeSeeder
 *
 * Seeds 4 HR employee records representing the demo users for
 * Usability Testing of the E-Procurement BNI system.
 *
 * NIP format: 10-digit numeric string.
 * Division must contain "IT Infrastructure" keyword (enforced by system config).
 * Position determines the system role via HrEmployee::deriveRole().
 */
class HrEmployeeSeeder extends Seeder
{
    /**
     * Role derivation logic (HrEmployee::deriveRole()):
     *  - "Division Head"   in position → division_head
     *  - "Department Head" in position → department_head
     *  - "Procurement" or "Fixed Assets" in position → pfa
     *  - anything else → requester
     */
    public function run(): void
    {
        $employees = [
            [
                'nip'      => '2024001001',
                'name'     => 'Muhammad Raihan Fauzan',
                'position' => 'IT Infrastructure Project Management Staff',
                // deriveRole() → requester
                'division' => 'IT Infrastructure Management',
            ],
            [
                'nip'      => '2024001002',
                'name'     => 'Bintang Mahaputra Nararya Rabbani',
                'position' => 'Procurement & Fixed Assets Staff',
                // deriveRole() → pfa
                'division' => 'IT Infrastructure Management',
            ],
            [
                'nip'      => '2024001003',
                'name'     => 'Haikal Fadhilah',
                'position' => 'Department Head IT Infrastructure',
                // deriveRole() → department_head
                'division' => 'IT Infrastructure Management',
            ],
            [
                'nip'      => '2024001004',
                'name'     => 'Putra Pertama Budianto',
                'position' => 'Division Head IT Infrastructure Management',
                // deriveRole() → division_head
                'division' => 'IT Infrastructure Management',
            ],
        ];

        foreach ($employees as $data) {
            HrEmployee::create($data);
        }
    }
}
