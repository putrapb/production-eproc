<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table must be created BEFORE users table (users has FK to hr_employees).
     */
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('name');
            $table->string('position');
            $table->string('division');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_employees');
    }
};
