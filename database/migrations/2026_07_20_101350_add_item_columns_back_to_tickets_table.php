<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('title');
            $table->integer('quantity')->nullable()->after('item_name');
        });

        // Restore data from ticket_items back to tickets
        $ticketItems = \Illuminate\Support\Facades\DB::table('ticket_items')->get();
        foreach ($ticketItems as $item) {
            \Illuminate\Support\Facades\DB::table('tickets')
                ->where('id', $item->ticket_id)
                ->update([
                    'item_name' => $item->item_name,
                    'quantity'  => $item->quantity,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'quantity']);
        });
    }
};
