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
        Schema::table('master_ak07', function (Blueprint $table) {
            $table->unsignedTinyInteger('potensi_asesi')->nullable()->default(null)->change();
            $table->string('fase_penggunaan')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ak07', function (Blueprint $table) {
            $table->unsignedTinyInteger('potensi_asesi')->default(1)->change();
            $table->string('fase_penggunaan')->default('saat_pra_asesmen')->change();
        });
    }
};
