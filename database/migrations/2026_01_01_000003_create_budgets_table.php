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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->enum('expenditure_type', ['CAPEX', 'OPEX']);
            $table->enum('category', ['infrastruktur_utama', 'lisensi_sistem', 'layanan_pemeliharaan', 'perlengkapan_operasional']);
            $table->integer('fiscal_year');
            $table->decimal('total_limit', 15, 2);
            $table->decimal('locked_amount', 15, 2)->default(0);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->timestamps();

            // One budget record per expenditure_type + category + fiscal_year
            $table->unique(['expenditure_type', 'category', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
