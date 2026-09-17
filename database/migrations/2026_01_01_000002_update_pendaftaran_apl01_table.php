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
            $table->string('tujuan_asesmen')->default('Sertifikasi')->after('skema_id');
            $table->string('tujuan_asesmen_lainnya')->nullable()->after('tujuan_asesmen');
            $table->string('kebangsaan')->default('Indonesia')->after('tujuan_asesmen_lainnya');
            $table->string('kode_pos')->nullable()->after('kebangsaan');
            $table->string('no_telp_rumah')->nullable()->after('kode_pos');
            $table->string('nama_perusahaan')->nullable()->after('no_telp_rumah');
            $table->string('jabatan_perusahaan')->nullable()->after('nama_perusahaan');
            $table->text('alamat_kantor')->nullable()->after('jabatan_perusahaan');
            $table->string('kode_pos_kantor')->nullable()->after('alamat_kantor');
            $table->string('telp_kantor')->nullable()->after('kode_pos_kantor');
            $table->string('fax_kantor')->nullable()->after('telp_kantor');
            $table->string('email_kantor')->nullable()->after('fax_kantor');
            $table->text('bukti_persyaratan_dasar')->nullable()->after('email_kantor');
            $table->text('bukti_administratif')->nullable()->after('bukti_persyaratan_dasar');
            $table->longText('tanda_tangan_asesi')->nullable()->after('bukti_administratif');
            $table->date('tanggal_ttd_asesi')->nullable()->after('tanda_tangan_asesi');
            $table->string('rekomendasi_admin_status')->nullable()->after('catatan_verifikasi'); // diterima / tidak_diterima
            $table->date('tanggal_ttd_admin')->nullable()->after('tanda_tangan_admin');
            $table->boolean('request_perbaikan')->default(false)->after('tanggal_ttd_admin');
            $table->text('catatan_request_perbaikan')->nullable()->after('request_perbaikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->dropColumn([
                'tujuan_asesmen',
                'tujuan_asesmen_lainnya',
                'kebangsaan',
                'kode_pos',
                'no_telp_rumah',
                'nama_perusahaan',
                'jabatan_perusahaan',
                'alamat_kantor',
                'kode_pos_kantor',
                'telp_kantor',
                'fax_kantor',
                'email_kantor',
                'bukti_persyaratan_dasar',
                'bukti_administratif',
                'tanda_tangan_asesi',
                'tanggal_ttd_asesi',
                'rekomendasi_admin_status',
                'tanggal_ttd_admin',
                'request_perbaikan',
                'catatan_request_perbaikan',
            ]);
        });
    }
};
