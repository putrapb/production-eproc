<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Before dropping columns, migrate existing data to ticket_items
        // so no historical data is lost.
        $tickets = DB::table('tickets')
            ->whereNotNull('item_name')
            ->get(['id', 'item_name', 'quantity', 'amount']);

        foreach ($tickets as $ticket) {
            DB::table('ticket_items')->insert([
                'ticket_id'  => $ticket->id,
                'item_name'  => $ticket->item_name ?? 'Item Pengadaan',
                'quantity'   => $ticket->quantity ?? 1,
                'unit_price' => ($ticket->quantity > 0)
                    ? round($ticket->amount / $ticket->quantity, 2)
                    : $ticket->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Now drop the old columns
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'quantity']);
        });
    }

    public function down(): void
    {
        // Add the columns back
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('title');
            $table->unsignedInteger('quantity')->default(1)->after('item_name');
        });

        // Restore data from ticket_items back to tickets
        $ticketItems = DB::table('ticket_items')->get();
        foreach ($ticketItems as $item) {
            DB::table('tickets')
                ->where('id', $item->ticket_id)
                ->update([
                    'item_name' => $item->item_name,
                    'quantity'  => $item->quantity,
                ]);
        }
    }
};
