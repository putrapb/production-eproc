<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Track who currently holds the "ball" (pending action)
            $table->string('pending_with_role')->nullable()->after('status')
                ->comment('role yang saat ini harus bertindak: requester|team_leader|department_head|none');
            $table->foreignId('pending_with_user_id')->nullable()->after('pending_with_role')
                ->constrained('users')->nullOnDelete()
                ->comment('user spesifik yang saat ini memegang pending action (nullable)');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['pending_with_user_id']);
            $table->dropColumn(['pending_with_role', 'pending_with_user_id']);
        });
    }
};
