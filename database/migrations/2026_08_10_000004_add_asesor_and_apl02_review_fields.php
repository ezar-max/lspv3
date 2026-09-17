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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('nomor_registrasi')->nullable()->after('peran');
        });

        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->foreignId('asesor_id')->nullable()->after('skema_id')->constrained('pengguna')->onDelete('set null');
            $table->string('rekomendasi_asesor_status')->nullable()->after('catatan_verifikasi'); // dapat_dilanjutkan / tidak_dapat_dilanjutkan
            $table->text('catatan_peninjauan_asesor')->nullable()->after('rekomendasi_asesor_status');
            $table->longText('tanda_tangan_asesor')->nullable()->after('tanda_tangan_admin');
            $table->date('tanggal_ttd_asesor')->nullable()->after('tanda_tangan_asesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('nomor_registrasi');
        });

        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->dropForeign(['asesor_id']);
            $table->dropColumn([
                'asesor_id',
                'rekomendasi_asesor_status',
                'catatan_peninjauan_asesor',
                'tanda_tangan_asesor',
                'tanggal_ttd_asesor',
            ]);
        });
    }
};
