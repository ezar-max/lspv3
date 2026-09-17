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
        // 1. FR.AK.02 — Rekaman Asesmen Kompetensi
        Schema::create('assessment_ak02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade')->unique();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->json('matriks_bukti')->nullable();
            $table->json('rekomendasi_unit')->nullable();
            $table->unsignedInteger('total_unit')->default(0);
            $table->unsignedInteger('total_k')->default(0);
            $table->unsignedInteger('total_bk')->default(0);
            $table->enum('keputusan_final', ['kompeten', 'belum_kompeten'])->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->text('komentar_asesor')->nullable();
            $table->json('dokumen_terkait')->nullable();
            $table->longText('tanda_tangan_asesor')->nullable();
            $table->dateTime('tanggal_ttd_asesor')->nullable();
            $table->longText('tanda_tangan_asesi')->nullable();
            $table->dateTime('tanggal_ttd_asesi')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
        });

        // 2. FR.AK.03 — Umpan Balik dan Catatan Asesmen
        Schema::create('assessment_ak03', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade')->unique();
            $table->foreignId('asesi_id')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->json('jawaban_kuesioner')->nullable();
            $table->text('catatan_lainnya')->nullable();
            $table->longText('tanda_tangan_asesi')->nullable();
            $table->dateTime('tanggal_ttd_asesi')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        // 3. FR.AK.05 — Laporan Asesmen (Kelompok / Skema)
        Schema::create('assessment_ak05', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_laporan')->unique();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_asesmen')->onDelete('set null');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->date('tanggal_laporan');
            $table->json('rekap_asesi')->nullable();
            $table->unsignedInteger('total_asesi')->default(0);
            $table->unsignedInteger('total_k')->default(0);
            $table->unsignedInteger('total_bk')->default(0);
            $table->text('aspek_positif')->nullable();
            $table->text('aspek_negatif')->nullable();
            $table->text('penolakan_hasil')->nullable();
            $table->json('saran_perbaikan')->nullable();
            $table->longText('tanda_tangan_asesor')->nullable();
            $table->dateTime('tanggal_ttd_asesor')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 4. FR.AK.06 — Meninjau Proses Asesmen
        Schema::create('assessment_ak06', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_review')->unique();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_asesmen')->onDelete('set null');
            $table->foreignId('pendaftaran_id')->nullable()->constrained('pendaftaran_asesi')->onDelete('set null');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->enum('scope_type', ['individual', 'kelompok', 'skema'])->default('skema');
            $table->date('tanggal_review');
            $table->json('prosedur_matrix')->nullable();
            $table->json('dimensi_kompetensi')->nullable();
            $table->json('rekomendasi_peningkatan')->nullable();
            $table->text('komentar_reviewer')->nullable();
            $table->longText('tanda_tangan_reviewer')->nullable();
            $table->dateTime('tanggal_ttd_reviewer')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 5. FR.VA — Memberikan Kontribusi dalam Validasi Asesmen
        Schema::create('assessment_va', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_validasi')->unique();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('lead_asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->date('tanggal_validasi');
            $table->string('tempat_validasi')->nullable();
            $table->json('periode_validasi')->nullable();
            $table->json('tujuan_fokus')->nullable();
            $table->json('konteks_validasi')->nullable();
            $table->json('pendekatan_validasi')->nullable();
            $table->json('peserta_relevan')->nullable();
            $table->json('acuan_pembanding')->nullable();
            $table->json('dokumen_terkait')->nullable();
            $table->json('keterampilan_komunikasi')->nullable();
            $table->text('catatan_kontribusi')->nullable();
            $table->json('matriks_penilaian')->nullable();
            $table->json('temuan_validasi')->nullable();
            $table->text('rekomendasi_peningkatan')->nullable();
            $table->json('rencana_perbaikan')->nullable();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->longText('tanda_tangan_lead')->nullable();
            $table->dateTime('tanggal_ttd_lead')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 6. Audit Trail Khusus Dokumen Asesmen
        Schema::create('document_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('user_id')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->string('action', 50);
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->unsignedInteger('version')->default(1);
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_audit_logs');
        Schema::dropIfExists('assessment_va');
        Schema::dropIfExists('assessment_ak06');
        Schema::dropIfExists('assessment_ak05');
        Schema::dropIfExists('assessment_ak03');
        Schema::dropIfExists('assessment_ak02');
    }
};
