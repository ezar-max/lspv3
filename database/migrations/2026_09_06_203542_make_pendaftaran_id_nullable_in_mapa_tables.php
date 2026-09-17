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
        if (Schema::hasTable('mapa_01')) {
            Schema::table('mapa_01', function (Blueprint $table) {
                $table->unsignedBigInteger('pendaftaran_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('mapa_02')) {
            Schema::table('mapa_02', function (Blueprint $table) {
                $table->unsignedBigInteger('pendaftaran_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mapa_01')) {
            Schema::table('mapa_01', function (Blueprint $table) {
                $table->unsignedBigInteger('pendaftaran_id')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('mapa_02')) {
            Schema::table('mapa_02', function (Blueprint $table) {
                $table->unsignedBigInteger('pendaftaran_id')->nullable(false)->change();
            });
        }
    }
};
