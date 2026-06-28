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
 * Uses a temporary intermediate value to prevent collisions during rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Users: Role Enum Restructuring ──────────────────────────────────
        // PostgreSQL requires ALTER TYPE to modify enum values.
        // We add new values first, update data, then remove old values via column recreation.

        // Step 1: Add 'team_leader' to the role enum (department_head stays temporarily)
        DB::statement("ALTER TYPE users_role_check ADD VALUE IF NOT EXISTS 'team_leader'");

        // Step 2: Use intermediate value to safely swap roles without collision
        // division_head → department_head (division_head becomes the new decision maker dept head)
        // department_head → team_leader (old dept head becomes team leader)
        DB::statement("
            UPDATE users SET role = 'team_leader' WHERE role = 'department_head'
        ");
        DB::statement("
            UPDATE users SET role = 'department_head' WHERE role = 'division_head'
        ");

        // ── Tickets: Status Restructuring ─────────────────────────────────

        // For PostgreSQL enums in tickets, we need to handle them carefully.
        // Step 1: Add new status values
        DB::statement("ALTER TYPE tickets_status_check ADD VALUE IF NOT EXISTS 'pending_team_leader'");

        // Step 2: Swap statuses using temp intermediate
        // pending_dept_head → pending_team_leader
        DB::statement("
            UPDATE tickets SET status = 'pending_team_leader' WHERE status = 'pending_dept_head'
        ");
        // pending_div_head → pending_dept_head
        DB::statement("
            UPDATE tickets SET status = 'pending_dept_head' WHERE status = 'pending_div_head'
        ");
    }

    public function down(): void
    {
        // Reverse: team_leader → department_head, department_head → division_head
        DB::statement("
            UPDATE users SET role = 'department_head' WHERE role = 'team_leader'
        ");
        DB::statement("
            UPDATE users SET role = 'division_head' WHERE role = 'department_head'
        ");

        // Reverse tickets
        DB::statement("
            UPDATE tickets SET status = 'pending_div_head' WHERE status = 'pending_dept_head'
        ");
        DB::statement("
            UPDATE tickets SET status = 'pending_dept_head' WHERE status = 'pending_team_leader'
        ");
    }
};
