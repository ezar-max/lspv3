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
        Schema::table('ia_penilaian', function (Blueprint $table) {
            $table->longText('tanda_tangan_asesor')->nullable()->after('token_expired_at');
            $table->timestamp('tanggal_ttd_asesor')->nullable()->after('tanda_tangan_asesor');
            $table->longText('tanda_tangan_asesi')->nullable()->after('tanggal_ttd_asesor');
            $table->timestamp('tanggal_ttd_asesi')->nullable()->after('tanda_tangan_asesi');
            $table->foreignId('master_id')->nullable()->after('kode_formulir')->constrained('scheme_master_instruments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ia_penilaian', function (Blueprint $table) {
            $table->dropForeign(['master_id']);
            $table->dropColumn([
                'tanda_tangan_asesor',
                'tanggal_ttd_asesor',
                'tanda_tangan_asesi',
                'tanggal_ttd_asesi',
                'master_id'
            ]);
        });
    }
};
