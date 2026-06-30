<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

/**
 * RecalculateBudgetSeeder
 *
 * Recalculates all budget used_amount and locked_amount columns
 * based on all existing tickets in the database, EXCLUDING ticket ID 5
 * ("Pengadaan Penambahan Kapasitas Storage BNI") as requested.
 *
 * Usage:
 *   php artisan db:seed --class=RecalculateBudgetSeeder
 */
class RecalculateBudgetSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Delete ALL tickets in the database to completely reset the system
        Ticket::query()->delete();

        // 1. Reset all used and locked amounts to 0 for all budgets
        foreach (Budget::all() as $budget) {
            $budget->update([
                'used_amount'   => 0,
                'locked_amount' => 0,
            ]);
        }

        $this->command->info("Database reset complete. All tickets deleted and budgets reset to 0.");
    }
}
