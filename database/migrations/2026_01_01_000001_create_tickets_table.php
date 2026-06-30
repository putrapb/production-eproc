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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('item_name');
            $table->enum('category', ['infrastruktur_utama', 'lisensi_sistem', 'layanan_pemeliharaan', 'perlengkapan_operasional']);
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('vendor_name');
            $table->decimal('amount', 15, 2);
            $table->enum('expenditure_type', ['CAPEX', 'OPEX'])->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_po_path')->nullable();
            $table->string('status')->default('pending_review');
            $table->boolean('is_cross_fund')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
