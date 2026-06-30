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
        // 0. Delete the "Pengadaan Penambahan Kapasitas Storage BNI" ticket so it is cleared completely
        Ticket::where('id', 5)
            ->orWhere('title', 'Pengadaan Penambahan Kapasitas Storage BNI')
            ->delete();

        // 1. Reset all used and locked amounts to 0
        foreach (Budget::all() as $budget) {
            $budget->update([
                'used_amount'   => 0,
                'locked_amount' => 0,
            ]);
        }

        // 2. Fetch all tickets except ID 5 ("Pengadaan Penambahan Kapasitas Storage BNI")
        // and also exclude any ticket with that specific title to be robust.
        $tickets = Ticket::where('id', '!=', 5)
            ->where('title', '!=', 'Pengadaan Penambahan Kapasitas Storage BNI')
            ->get();

        $count = 0;
        foreach ($tickets as $ticket) {
            $year = $ticket->created_at ? $ticket->created_at->year : now()->year;

            if ($ticket->isApproved() || $ticket->isFormGenerated()) {
                $budget = Budget::findForTicket($ticket->expenditure_type, $ticket->category, $year);
                if ($budget) {
                    $budget->increment('used_amount', $ticket->total_amount);
                    $count++;
                }
            } elseif ($ticket->status === 'pending_dept_head' && $ticket->is_cross_fund) {
                $budget = Budget::findForTicket($ticket->expenditure_type, $ticket->category, $year);
                if ($budget) {
                    $budget->increment('locked_amount', $ticket->total_amount);
                    $count++;
                }
            }
        }

        $this->command->info("Recalculation complete. Processed {$count} tickets and updated budgets.");
    }
}
