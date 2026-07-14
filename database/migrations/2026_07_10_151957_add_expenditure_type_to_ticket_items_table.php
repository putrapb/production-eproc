<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add expenditure_type to ticket_items for per-item CAPEX/OPEX classification.
     *
     * Nullable: existing items inherit from parent ticket's expenditure_type at runtime
     * via getEffectiveExpenditureTypeAttribute(). Back-filled below for convenience.
     */
    public function up(): void
    {
        Schema::table('ticket_items', function (Blueprint $table) {
            $table->string('expenditure_type', 10)->nullable()->after('unit_price')
                ->comment('CAPEX or OPEX per-item. NULL = inherit from parent ticket.');
        });

        // Back-fill existing items using a DB-agnostic approach (works on SQLite, MySQL, PostgreSQL).
        // Each ticket_item without a classification inherits from its parent ticket.
        DB::table('ticket_items')
            ->whereNull('expenditure_type')
            ->get(['id', 'ticket_id'])
            ->each(function ($item) {
                $ticket = DB::table('tickets')
                    ->where('id', $item->ticket_id)
                    ->value('expenditure_type');

                if ($ticket) {
                    DB::table('ticket_items')
                        ->where('id', $item->id)
                        ->update(['expenditure_type' => $ticket]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ticket_items', function (Blueprint $table) {
            $table->dropColumn('expenditure_type');
        });
    }
};

