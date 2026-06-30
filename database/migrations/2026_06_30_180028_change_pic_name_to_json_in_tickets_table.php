<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tickets ALTER COLUMN pic_name TYPE json USING (CASE WHEN pic_name IS NULL THEN NULL ELSE json_build_array(pic_name) END)');
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('pic_name');
            });
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('pic_name')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tickets ALTER COLUMN pic_name TYPE varchar(255) USING (pic_name->>0)');
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('pic_name');
            });
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('pic_name', 255)->nullable()->after('description');
            });
        }
    }
};
