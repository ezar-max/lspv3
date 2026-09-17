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
        // 1. Tabel Elemen Kompetensi
        Schema::create('elemen_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_kompetensi')->onDelete('cascade');
            $table->integer('nomor_elemen')->default(1);
            $table->text('nama_elemen');
            $table->text('pertanyaan_elemen')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Kriteria Unjuk Kerja (KUK)
        Schema::create('kriteria_unjuk_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemen_id')->constrained('elemen_kompetensi')->onDelete('cascade');
            $table->string('nomor_kuk'); // misal: "1.1", "1.2", "2.1"
            $table->text('pernyataan_kuk');
            $table->timestamps();
        });

        // 3. Tabel Jawaban Asesmen Mandiri (FR.APL.02)
        Schema::create('jawaban_apl02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('elemen_id')->constrained('elemen_kompetensi')->onDelete('cascade');
            $table->enum('nilai_kompetensi', ['K', 'BK'])->default('K'); // K = Kompeten, BK = Belum Kompeten
            $table->string('bukti_relevan')->nullable(); // Path file/foto bukti pendukung
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_apl02');
        Schema::dropIfExists('kriteria_unjuk_kerja');
        Schema::dropIfExists('elemen_kompetensi');
    }
};
