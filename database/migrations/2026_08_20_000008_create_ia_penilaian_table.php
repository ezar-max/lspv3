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
        Schema::create('ia_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->string('kode_formulir', 30); // FR.IA.01, FR.IA.02, FR.IA.03, FR.IA.04A, FR.IA.04B, FR.IA.05A, FR.IA.05B, FR.IA.05C, FR.IA.06A, FR.IA.06B, FR.IA.06C, FR.IA.07, FR.IA.08, FR.IA.09, FR.IA.10, FR.IA.11
            $table->foreignId('user_id')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->string('role', 30)->default('asesor'); // asesor, asesi, supervisor
            $table->json('data_jawaban')->nullable(); // structured response payload
            $table->string('rekomendasi', 50)->nullable(); // K, BK, M, BM, dll.
            $table->text('catatan_asesor')->nullable();
            $table->string('status', 30)->default('draft'); // draft, submitted, evaluated, completed
            $table->string('token_akses', 100)->nullable()->unique(); // for guest magic link (FR.IA.10)
            $table->timestamp('token_expired_at')->nullable();
            $table->longText('tanda_tangan')->nullable();
            $table->timestamp('tanggal_tanda_tangan')->nullable();
            $table->timestamps();

            $table->index(['pendaftaran_id', 'kode_formulir']);
        });

        // Add completed_assessment status support if not present
        if (!Schema::hasColumn('pendaftaran_asesi', 'status_penilaian_ia')) {
            Schema::table('pendaftaran_asesi', function (Blueprint $table) {
                $table->string('status_penilaian_ia', 50)->default('belum_dinilai')->after('status_ak01'); // belum_dinilai, proses_asesmen, completed_assessment
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ia_penilaian');

        if (Schema::hasColumn('pendaftaran_asesi', 'status_penilaian_ia')) {
            Schema::table('pendaftaran_asesi', function (Blueprint $table) {
                $table->dropColumn('status_penilaian_ia');
            });
        }
    }
};
