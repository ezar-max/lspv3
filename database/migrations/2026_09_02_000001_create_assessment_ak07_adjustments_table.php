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
        Schema::create('assessment_ak07_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_registration_id')
                ->unique()
                ->constrained('pendaftaran_asesi')
                ->cascadeOnDelete();
            
            // Potensi Asesi: Opsi 1 s.d. 5 sesuai kategori kandidat MAPA.01
            $table->unsignedTinyInteger('potensi_asesi')->nullable();
            
            // Fase Penggunaan
            $table->enum('fase_penggunaan', ['pra_asesmen', 'saat_pra_asesmen', 'setelah_pra_asesmen'])->nullable();
            
            // Matriks 8 Kategori Kebutuhan Penyesuaian (JSON)
            $table->json('items_checklist')->nullable();
            
            // Rekomendasi Kesepakatan Penyesuaian
            $table->text('acuan_pembanding_disepakati')->nullable();
            $table->text('metode_disepakati')->nullable();
            $table->text('instrumen_disepakati')->nullable();
            $table->text('catatan_asesor')->nullable();
            
            // Tanda Tangan & Konfirmasi Asesor
            $table->string('asesor_signature')->nullable();
            $table->timestamp('asesor_signed_at')->nullable();
            
            // Tanda Tangan & Konfirmasi Asesi
            $table->string('asesi_signature')->nullable();
            $table->timestamp('asesi_signed_at')->nullable();
            
            // Status Siklus Dokumen
            $table->enum('status', ['draft', 'confirmed'])->default('draft');
            
            $table->timestamps();
            
            // Indexing untuk query pencarian & filter
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_ak07_adjustments');
    }
};
