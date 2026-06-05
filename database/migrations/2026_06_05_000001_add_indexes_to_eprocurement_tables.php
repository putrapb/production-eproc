<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for high-query columns in E-Procurement tables.
 *
 * Rationale:
 *  - tickets.status        → filtered heavily in scopeForRole() and index views
 *  - tickets.user_id       → every Requester query filters by user_id
 *  - approval_logs.ticket_id → N+1 guard: every ticket detail loads all its logs
 *  - approval_logs.user_id   → audit queries by actor
 *  - budgets composite     → Gate 4 findForTicket() queries type + category + year
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── tickets ───────────────────────────────────────────────
        Schema::table('tickets', function (Blueprint $table) {
            // Composite index: role-based list queries filter by status + user_id together
            $table->index('status',  'idx_tickets_status');
            $table->index('user_id', 'idx_tickets_user_id');

            // Composite index for PFA queue: status + created_at for ordered listing
            $table->index(['status', 'created_at'], 'idx_tickets_status_created');
        });

        // ─── approval_logs ─────────────────────────────────────────
        Schema::table('approval_logs', function (Blueprint $table) {
            // Each ticket detail page eager-loads all approval logs by ticket_id
            $table->index('ticket_id', 'idx_approval_logs_ticket_id');

            // Audit trail queries filter by actor (user_id)
            $table->index('user_id', 'idx_approval_logs_user_id');

            // Combined index for timeline queries sorted by created_at
            $table->index(['ticket_id', 'created_at'], 'idx_approval_logs_ticket_created');
        });

        // ─── budgets ───────────────────────────────────────────────
        Schema::table('budgets', function (Blueprint $table) {
            // Gate 4 findForTicket() always queries by all three columns together
            $table->index(
                ['expenditure_type', 'category', 'fiscal_year'],
                'idx_budgets_type_category_year'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_status');
            $table->dropIndex('idx_tickets_user_id');
            $table->dropIndex('idx_tickets_status_created');
        });

        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropIndex('idx_approval_logs_ticket_id');
            $table->dropIndex('idx_approval_logs_user_id');
            $table->dropIndex('idx_approval_logs_ticket_created');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('idx_budgets_type_category_year');
        });
    }
};
