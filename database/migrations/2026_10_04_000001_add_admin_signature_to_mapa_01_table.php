<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapa_01', function (Blueprint $table) {
            $table->longText('tanda_tangan_admin')->nullable()->after('tanggal_ttd_asesor');
            $table->timestamp('tanggal_ttd_admin')->nullable()->after('tanda_tangan_admin');
        });
    }

    public function down(): void
    {
        Schema::table('mapa_01', function (Blueprint $table) {
            $table->dropColumn(['tanda_tangan_admin', 'tanggal_ttd_admin']);
        });
    }
};