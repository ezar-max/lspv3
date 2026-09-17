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
        Schema::table('pengaturan_sistem', function (Blueprint $table) {
            $table->string('no_sk_lisensi')->nullable()->default('KEP.1215/BNSP/V/2025')->after('kode_lsp');
            $table->string('masa_berlaku')->nullable()->default('Hingga 23 Mei 2030')->after('nomor_lisensi');
            $table->string('status_keaktifan')->nullable()->default('Aktif')->after('masa_berlaku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_sistem', function (Blueprint $table) {
            $table->dropColumn(['no_sk_lisensi', 'masa_berlaku', 'status_keaktifan']);
        });
    }
};
