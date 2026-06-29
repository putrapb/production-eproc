<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Role & Status Restructuring Migration
 *
 * Changes:
 *  Roles (users table):
 *   - 'division_head'   → 'department_head'  (now the decision maker)
 *   - 'department_head' → 'team_leader'       (now forwarder/reviewer)
 *
 *  Statuses (tickets table):
 *   - 'pending_div_head'  → 'pending_dept_head'    (awaiting new decision maker)
 *   - 'pending_dept_head' → 'pending_team_leader'  (awaiting new forwarder)
 *
 * NOTE: Role/status columns are stored as varchar with check constraints in Laravel,
 *       NOT as PostgreSQL native enum types. So we only need to UPDATE data rows.
 *       The constraint is dropped and re-added to include the new values.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Users: Role Restructuring ──────────────────────────────────
        // Step 1: Remove existing role check constraint (if any) then re-add
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        // Step 2: Use intermediate temp value to safely swap roles without collision
        // department_head → team_leader (old dept head becomes forwarder)
        DB::statement("UPDATE users SET role = '_temp_team_leader' WHERE role = 'department_head'");
        // division_head → department_head (old div head becomes decision maker)
        DB::statement("UPDATE users SET role = 'department_head' WHERE role = 'division_head'");
        // _temp_team_leader → team_leader
        DB::statement("UPDATE users SET role = 'team_leader' WHERE role = '_temp_team_leader'");

        // Step 3: Re-add constraint with new valid role values
        DB::statement("
            ALTER TABLE users ADD CONSTRAINT users_role_check
            CHECK (role IN ('requester', 'pfa', 'team_leader', 'department_head'))
        ");

        // ── Tickets: Status Restructuring ─────────────────────────────────
        // Step 1: Remove existing status check constraint (if any)
        DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_status_check");

        // Step 2: Swap statuses using temp intermediate to avoid collisions
        DB::statement("UPDATE tickets SET status = '_temp_pending_team_leader' WHERE status = 'pending_dept_head'");
        DB::statement("UPDATE tickets SET status = 'pending_dept_head' WHERE status = 'pending_div_head'");
        DB::statement("UPDATE tickets SET status = 'pending_team_leader' WHERE status = '_temp_pending_team_leader'");

        // Step 3: Re-add constraint with new valid status values
        DB::statement("
            ALTER TABLE tickets ADD CONSTRAINT tickets_status_check
            CHECK (status IN (
                'pending_review', 'revision', 'need_to_validate',
                'pending_team_leader', 'pending_dept_head',
                'approved', 'declined', 'po_generated'
            ))
        ");
    }

    public function down(): void
    {
        // Reverse roles: team_leader → department_head, department_head → division_head
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("UPDATE users SET role = '_temp_dept_head' WHERE role = 'team_leader'");
        DB::statement("UPDATE users SET role = 'division_head' WHERE role = 'department_head'");
        DB::statement("UPDATE users SET role = 'department_head' WHERE role = '_temp_dept_head'");
        DB::statement("
            ALTER TABLE users ADD CONSTRAINT users_role_check
            CHECK (role IN ('requester', 'pfa', 'department_head', 'division_head'))
        ");

        // Reverse statuses
        DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_status_check");
        DB::statement("UPDATE tickets SET status = '_temp_pending_dept_head' WHERE status = 'pending_team_leader'");
        DB::statement("UPDATE tickets SET status = 'pending_div_head' WHERE status = 'pending_dept_head'");
        DB::statement("UPDATE tickets SET status = 'pending_dept_head' WHERE status = '_temp_pending_dept_head'");
        DB::statement("
            ALTER TABLE tickets ADD CONSTRAINT tickets_status_check
            CHECK (status IN (
                'pending_review', 'revision', 'need_to_validate',
                'pending_dept_head', 'pending_div_head',
                'approved', 'declined', 'po_generated'
            ))
        ");
    }
};
