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
        Schema::create('mapa_01', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->nullable()->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');

            // 1. Menentukan Pendekatan Asesmen
            $table->json('pendekatan_asesi')->nullable();
            $table->string('tujuan_asesmen')->nullable();
            $table->string('tujuan_asesmen_lainnya')->nullable();
            $table->string('konteks_lingkungan')->nullable(); // nyata, simulasi
            $table->string('konteks_peluang_bukti')->nullable(); // tersedia, terbatas
            $table->string('hubungan_standar_bukti')->nullable(); // senang, datar, sedih
            $table->string('hubungan_standar_aktivitas')->nullable(); // senang, datar, sedih
            $table->string('hubungan_standar_pembelajaran')->nullable(); // senang, datar, sedih
            $table->json('pelaksana_asesmen')->nullable();
            $table->json('konfirmasi_orang_relevan')->nullable();

            // 1.2 Standar Industri atau Tempat Kerja
            $table->json('standar_industri')->nullable();

            // 2. Mempersiapkan Rencana Asesmen
            $table->json('rencana_unit_matriks')->nullable();

            // 3. Mengidentifikasi Persyaratan Modifikasi dan Kontekstualisasi
            $table->string('karakteristik_kandidat_status')->default('tidak_ada'); // ada, tidak_ada
            $table->text('karakteristik_kandidat_teks')->nullable();
            $table->string('kebutuhan_kontekstualisasi_status')->default('tidak_ada'); // ada, tidak_ada
            $table->text('kebutuhan_kontekstualisasi_teks')->nullable();
            $table->string('saran_pelatihan_status')->default('tidak_ada'); // ada, tidak_ada
            $table->text('saran_pelatihan_teks')->nullable();
            $table->string('penyesuaian_perangkat_status')->default('tidak_ada'); // ada, tidak_ada
            $table->text('penyesuaian_perangkat_teks')->nullable();
            $table->string('peluang_terintegrasi_status')->default('tidak_ada'); // ada, tidak_ada
            $table->text('peluang_terintegrasi_teks')->nullable();

            // Konfirmasi Pihak Relevan Tabel
            $table->json('konfirmasi_pihak_relevan_tabel')->nullable();

            // Penyusun dan Validator
            $table->json('penyusun_validator_tabel')->nullable();

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
        Schema::dropIfExists('mapa_01');
    }
};
