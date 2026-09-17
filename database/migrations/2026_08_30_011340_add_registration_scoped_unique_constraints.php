<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawaban_apl02', function (Blueprint $table) {
            $table->unique(['pendaftaran_id', 'elemen_id'], 'jawaban_apl02_pendaftaran_elemen_unique');
        });

        Schema::table('rekomendasi_asesmen', function (Blueprint $table) {
            $table->unique('pendaftaran_id', 'rekomendasi_asesmen_pendaftaran_unique');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_apl02', function (Blueprint $table) {
            $table->dropUnique('jawaban_apl02_pendaftaran_elemen_unique');
        });

        Schema::table('rekomendasi_asesmen', function (Blueprint $table) {
            $table->dropUnique('rekomendasi_asesmen_pendaftaran_unique');
        });
    }
};
