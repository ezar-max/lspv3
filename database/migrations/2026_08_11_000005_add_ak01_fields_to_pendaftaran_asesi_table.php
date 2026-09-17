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
        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->string('tuk_type')->nullable()->default('Sewaktu')->after('catatan_peninjauan_asesor');
            $table->json('bukti_dikumpulkan')->nullable()->after('tuk_type');
            $table->string('bukti_dikumpulkan_lainnya')->nullable()->after('bukti_dikumpulkan');
            $table->longText('tanda_tangan_asesi_ak01')->nullable()->after('bukti_dikumpulkan_lainnya');
            $table->timestamp('tanggal_ttd_asesi_ak01')->nullable()->after('tanda_tangan_asesi_ak01');
            $table->longText('tanda_tangan_asesor_ak01')->nullable()->after('tanggal_ttd_asesi_ak01');
            $table->timestamp('tanggal_ttd_asesor_ak01')->nullable()->after('tanda_tangan_asesor_ak01');
            $table->string('status_ak01')->default('belum')->after('tanggal_ttd_asesor_ak01'); // belum, disetujui_asesi, selesai
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->dropColumn([
                'tuk_type',
                'bukti_dikumpulkan',
                'bukti_dikumpulkan_lainnya',
                'tanda_tangan_asesi_ak01',
                'tanggal_ttd_asesi_ak01',
                'tanda_tangan_asesor_ak01',
                'tanggal_ttd_asesor_ak01',
                'status_ak01',
            ]);
        });
    }
};
