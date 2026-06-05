<?php

namespace Database\Seeders;

use App\Models\Budget;
use Illuminate\Database\Seeder;

/**
 * BudgetSeeder
 *
 * Seeds realistic large-scale corporate budget (pagu anggaran) data for
 * BNI IT Infrastructure Management, Kantor Pejompongan.
 *
 * Total pagu ~Rp 540 Miliar distributed across 4 corporate asset classes:
 *
 * ┌──────────────────────────┬──────────┬───────────────────────┐
 * │ Kategori                 │ Type     │ Total Limit           │
 * ├──────────────────────────┼──────────┼───────────────────────┤
 * │ infrastruktur_utama      │ CAPEX    │ Rp 250.000.000.000    │
 * │ lisensi_sistem           │ CAPEX    │ Rp 100.000.000.000    │
 * │ layanan_pemeliharaan     │ OPEX     │ Rp 150.000.000.000    │
 * │ perlengkapan_operasional │ OPEX     │ Rp  40.000.000.000    │
 * ├──────────────────────────┼──────────┼───────────────────────┤
 * │ TOTAL                    │          │ Rp 540.000.000.000    │
 * └──────────────────────────┴──────────┴───────────────────────┘
 *
 * All amounts use decimal(15,2) precision as defined in the migration.
 */
class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $fiscalYear = now()->year;

        $budgets = [
            // ── CAPEX ─────────────────────────────────────────────────────────────
            [
                'expenditure_type' => 'CAPEX',
                'category'         => 'infrastruktur_utama',
                'total_limit'      => 250_000_000_000.00, // Rp 250 Miliar
                // Server, network equipment, storage, data center infrastructure
            ],
            [
                'expenditure_type' => 'CAPEX',
                'category'         => 'lisensi_sistem',
                'total_limit'      => 100_000_000_000.00, // Rp 100 Miliar
                // Enterprise software licenses, ERP, core banking integrations
            ],
            [
                'expenditure_type' => 'CAPEX',
                'category'         => 'layanan_pemeliharaan',
                'total_limit'      => 0.00, // CAPEX maintenance — not allocated this cycle
            ],
            [
                'expenditure_type' => 'CAPEX',
                'category'         => 'perlengkapan_operasional',
                'total_limit'      => 0.00, // CAPEX supplies — not allocated this cycle
            ],

            // ── OPEX ──────────────────────────────────────────────────────────────
            [
                'expenditure_type' => 'OPEX',
                'category'         => 'infrastruktur_utama',
                'total_limit'      => 0.00, // OPEX infra — not allocated (CAPEX-only class)
            ],
            [
                'expenditure_type' => 'OPEX',
                'category'         => 'lisensi_sistem',
                'total_limit'      => 0.00, // OPEX license — not allocated (CAPEX-only class)
            ],
            [
                'expenditure_type' => 'OPEX',
                'category'         => 'layanan_pemeliharaan',
                'total_limit'      => 150_000_000_000.00, // Rp 150 Miliar
                // Managed services, outsourcing, AMC, cloud subscriptions
            ],
            [
                'expenditure_type' => 'OPEX',
                'category'         => 'perlengkapan_operasional',
                'total_limit'      => 40_000_000_000.00, // Rp 40 Miliar
                // Operational supplies, stationery, minor accessories
            ],
        ];

        foreach ($budgets as $budget) {
            Budget::create([
                'expenditure_type' => $budget['expenditure_type'],
                'category'         => $budget['category'],
                'fiscal_year'      => $fiscalYear,
                'total_limit'      => $budget['total_limit'], // decimal(15,2)
                'locked_amount'    => 0.00,
                'used_amount'      => 0.00,
            ]);
        }
    }
}
