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
        Schema::create('mapa_02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->nullable()->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');

            $table->json('matriks_peta')->nullable(); // Menyimpan checklist instrumen per KUK & Elemen
            $table->text('catatan_asesor')->nullable();
            $table->longText('tanda_tangan_asesor')->nullable();
            $table->timestamp('tanggal_ttd_asesor')->nullable();
            $table->string('status_mapa')->default('selesai'); // draft, selesai

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapa_02');
    }
};
