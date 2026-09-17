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
        if (!Schema::hasTable('verifikasi_kuk_apl02')) {
            Schema::create('verifikasi_kuk_apl02', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
                $table->foreignId('elemen_id')->constrained('elemen_kompetensi')->onDelete('cascade');
                $table->foreignId('kuk_id')->constrained('kriteria_unjuk_kerja')->onDelete('cascade');
                $table->enum('nilai_kompetensi', ['K', 'BK'])->default('K'); // Klaim mandiri asesi
                $table->boolean('is_verified')->default(false); // Status verifikasi / konfirmasi asesor
                $table->text('catatan_asesor')->nullable(); // Catatan per butir KUK jika ada
                $table->timestamps();

                $table->unique(['pendaftaran_id', 'kuk_id'], 'pendaftaran_kuk_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_kuk_apl02');
    }
};
