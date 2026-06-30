<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Role & Status Refactor v2 — 3-Role System
 *
 * Changes:
 *  Roles (users table):
 *   - 'pfa' → 'team_leader'  (document checker + form generator now TL)
 *   - Constraint updated: remove 'pfa' from valid roles
 *
 *  Statuses (tickets table):
 *   - 'pending_team_leader' → 'pending_dept_head'  (TL no longer forwards; smart val goes directly to DH)
 *   - 'po_generated'        → 'form_generated'      (output is a procurement form, not a PO)
 *   - Constraint updated: remove 'pending_team_leader', rename 'po_generated' → 'form_generated'
 *
 * NOTE: Columns are varchar + CHECK constraints (not native PostgreSQL enum).
 *       We DROP the constraint, UPDATE data, then re-ADD the constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';

        // ── Users: Remove pfa role ─────────────────────────────────────
        if ($isPgsql) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        }

        // Migrate all pfa users → team_leader
        DB::statement("UPDATE users SET role = 'team_leader' WHERE role = 'pfa'");

        // Re-add constraint without 'pfa'
        if ($isPgsql) {
            DB::statement("
                ALTER TABLE users ADD CONSTRAINT users_role_check
                CHECK (role IN ('requester', 'team_leader', 'department_head'))
            ");
        }

        // ── Tickets: Status Refactor ───────────────────────────────────
        if ($isPgsql) {
            DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_status_check");
        }

        // 1. Tickets stuck in pending_team_leader → advance to pending_dept_head
        //    (TL no longer forwards; they go directly to DH after smart validation)
        DB::statement("UPDATE tickets SET status = 'pending_dept_head' WHERE status = 'pending_team_leader'");

        // 2. Rename po_generated → form_generated
        DB::statement("UPDATE tickets SET status = 'form_generated' WHERE status = 'po_generated'");

        // Re-add constraint with new valid values
        if ($isPgsql) {
            DB::statement("
                ALTER TABLE tickets ADD CONSTRAINT tickets_status_check
                CHECK (status IN (
                    'pending_review', 'revision', 'need_to_validate',
                    'pending_dept_head',
                    'approved', 'declined', 'form_generated'
                ))
            ");
        }
    }

    public function down(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';

        // ── Reverse status changes ─────────────────────────────────────
        if ($isPgsql) {
            DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_status_check");
        }

        DB::statement("UPDATE tickets SET status = 'po_generated' WHERE status = 'form_generated'");

        if ($isPgsql) {
            DB::statement("
                ALTER TABLE tickets ADD CONSTRAINT tickets_status_check
                CHECK (status IN (
                    'pending_review', 'revision', 'need_to_validate',
                    'pending_team_leader', 'pending_dept_head',
                    'approved', 'declined', 'po_generated'
                ))
            ");
        }

        // ── Reverse role changes ───────────────────────────────────────
        if ($isPgsql) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        }

        if ($isPgsql) {
            DB::statement("
                ALTER TABLE users ADD CONSTRAINT users_role_check
                CHECK (role IN ('requester', 'pfa', 'team_leader', 'department_head'))
            ");
        }
    }
};
