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
        Schema::create('scheme_master_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('unit_kompetensi_id')->nullable()->constrained('unit_kompetensi')->onDelete('set null');
            $table->string('instrument_code', 30); // ia02, ia03, ia04a, ia05, ia06, ia07, ia11
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->integer('time_limit_minutes')->nullable()->default(60);
            $table->boolean('is_active')->default(true);
            $table->json('additional_metadata')->nullable(); // Untuk skenario praktik, TOR tugas, daftar alat & bahan TUK
            $table->timestamps();

            $table->index(['skema_id', 'instrument_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_master_instruments');
    }
};
