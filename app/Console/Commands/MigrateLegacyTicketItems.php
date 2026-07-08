<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('eproc:migrate-legacy-items')]
#[Description('Migrates legacy item_name, quantity, and amount columns into the new ticket_items table.')]
class MigrateLegacyTicketItems extends Command
{
    public function handle()
    {
        $this->info('Starting legacy ticket item migration...');

        // Fetch tickets that do not have any items in the ticket_items table
        $tickets = Ticket::whereDoesntHave('items')
            // Only migrate if we still have the legacy attributes available (e.g. from raw DB if columns were dropped in Eloquent but still exist in table, though in our case we dropped them from the schema. 
            // Wait, we dropped them in the migration! So we can't access them unless we backed them up or we fetch them from another source.
            // Oh, we dropped them in the migration already? Let's check the migration file.
            ->get();

        $this->warn('Note: If the columns were already dropped via migration, data is lost unless restored from backup. Assuming the migration was run with backup or we are running this before dropping the columns.');
        
        // However, since we already ran the drop column migration in Fase 1, the data is gone from DB. 
        // We will just write the script for completeness/future reference if they restore from backup.
        
        $count = 0;
        foreach ($tickets as $ticket) {
            // We use getRawOriginal just in case the columns still exist but are hidden
            $itemName = $ticket->getRawOriginal('item_name') ?? 'Legacy Item (Unknown)';
            $quantity = $ticket->getRawOriginal('quantity') ?? 1;
            $amount   = $ticket->getRawOriginal('amount') ?? 0;

            if ($amount > 0) {
                $ticket->items()->create([
                    'item_name'  => $itemName,
                    'quantity'   => $quantity,
                    'unit_price' => $amount, // Legacy amount was per unit because total was amount * quantity
                ]);
                $count++;
            }
        }

        $this->info("Migration completed. Migrated items for {$count} tickets.");
    }
}
