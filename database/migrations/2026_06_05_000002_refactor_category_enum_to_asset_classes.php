<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor ticket and budget category ENUMs from 5 legacy generic categories
 * to 4 standardized corporate asset classes.
 *
 * NOTE: The base create_tickets_table and create_budgets_table migrations have
 * already been updated to use the new 4 categories. This migration exists solely
 * to handle PRODUCTION databases that were created before this refactor and
 * may still contain old category data (hardware, software, services, etc.).
 *
 * For fresh installs (migrate:fresh / RefreshDatabase in tests):
 *   - Tables are created with new categories from the start → this migration is a no-op.
 *
 * For existing production databases (PostgreSQL/Supabase):
 *   - Data migration: old values → new values
 *   - CHECK constraint update
 *
 * Driver-aware:
 *   - SQLite   : data migration only (no constraint alteration needed — base migration
 *                already has new values; existing data will be empty on fresh install)
 *   - PostgreSQL: drop CHECK → migrate data → add new CHECK
 *   - MySQL    : migrate data → MODIFY COLUMN ENUM
 */
return new class extends Migration
{
    private const NEW_CATEGORIES = [
        'infrastruktur_utama',
        'lisensi_sistem',
        'layanan_pemeliharaan',
        'perlengkapan_operasional',
    ];

    private const OLD_CATEGORIES = [
        'hardware',
        'software',
        'services',
        'office_supplies',
        'others',
    ];

    private const OLD_TO_NEW = [
        'hardware'        => 'infrastruktur_utama',
        'software'        => 'lisensi_sistem',
        'services'        => 'layanan_pemeliharaan',
        'office_supplies' => 'perlengkapan_operasional',
        'others'          => 'perlengkapan_operasional',
    ];

    private const NEW_TO_OLD = [
        'infrastruktur_utama'      => 'hardware',
        'lisensi_sistem'           => 'software',
        'layanan_pemeliharaan'     => 'services',
        'perlengkapan_operasional' => 'office_supplies',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        match ($driver) {
            'pgsql'  => $this->upPostgres(),
            'mysql'  => $this->upMysql(),
            default  => $this->migrateData(self::OLD_TO_NEW), // SQLite: safe no-op if data already new
        };
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        match ($driver) {
            'pgsql'  => $this->downPostgres(),
            'mysql'  => $this->downMysql(),
            default  => $this->migrateData(self::NEW_TO_OLD),
        };
    }

    // ──────────────────────────────────────────────
    // PostgreSQL (production / Supabase)
    // ──────────────────────────────────────────────

    private function upPostgres(): void
    {
        // Step 1: Drop existing CHECK constraints
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_category_check');
        DB::statement('ALTER TABLE budgets DROP CONSTRAINT IF EXISTS budgets_category_check');

        // Step 2: Migrate any existing old-format data
        $this->migrateData(self::OLD_TO_NEW);

        // Step 3: Add updated CHECK constraints
        $newIn = "'" . implode("','", self::NEW_CATEGORIES) . "'";
        DB::statement("ALTER TABLE tickets ADD CONSTRAINT tickets_category_check CHECK (category IN ({$newIn}))");
        DB::statement("ALTER TABLE budgets ADD CONSTRAINT budgets_category_check CHECK (category IN ({$newIn}))");
    }

    private function downPostgres(): void
    {
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_category_check');
        DB::statement('ALTER TABLE budgets DROP CONSTRAINT IF EXISTS budgets_category_check');

        $this->migrateData(self::NEW_TO_OLD);

        $oldIn = "'" . implode("','", self::OLD_CATEGORIES) . "'";
        DB::statement("ALTER TABLE tickets ADD CONSTRAINT tickets_category_check CHECK (category IN ({$oldIn}))");
        DB::statement("ALTER TABLE budgets ADD CONSTRAINT budgets_category_check CHECK (category IN ({$oldIn}))");
    }

    // ──────────────────────────────────────────────
    // MySQL
    // ──────────────────────────────────────────────

    private function upMysql(): void
    {
        $this->migrateData(self::OLD_TO_NEW);

        $enumDef = "ENUM('" . implode("','", self::NEW_CATEGORIES) . "') NOT NULL";
        DB::statement("ALTER TABLE tickets MODIFY COLUMN category {$enumDef}");
        DB::statement("ALTER TABLE budgets MODIFY COLUMN category {$enumDef}");
    }

    private function downMysql(): void
    {
        $this->migrateData(self::NEW_TO_OLD);

        $enumDef = "ENUM('" . implode("','", self::OLD_CATEGORIES) . "') NOT NULL";
        DB::statement("ALTER TABLE tickets MODIFY COLUMN category {$enumDef}");
        DB::statement("ALTER TABLE budgets MODIFY COLUMN category {$enumDef}");
    }

    // ──────────────────────────────────────────────
    // Shared: row-level data migration
    // ──────────────────────────────────────────────

    private function migrateData(array $map): void
    {
        foreach ($map as $from => $to) {
            DB::table('tickets')->where('category', $from)->update(['category' => $to]);
            DB::table('budgets')->where('category', $from)->update(['category' => $to]);
        }
    }
};
