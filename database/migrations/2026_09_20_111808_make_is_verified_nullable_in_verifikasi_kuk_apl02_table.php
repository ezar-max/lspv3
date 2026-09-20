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
        Schema::table('verifikasi_kuk_apl02', function (Blueprint $table) {
            $table->boolean('is_verified')->nullable()->default(null)->change();
        });

        // Set existing records for unfinalized pendaftaran to null so neither K nor BK is pre-selected
        \Illuminate\Support\Facades\DB::table('verifikasi_kuk_apl02')
            ->whereIn('pendaftaran_id', function ($query) {
                $query->select('id')
                    ->from('pendaftaran_asesi')
                    ->whereIn('status_apl02', ['submitted', 'under_review', 'draft']);
            })
            ->update(['is_verified' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('verifikasi_kuk_apl02')
            ->whereNull('is_verified')
            ->update(['is_verified' => false]);

        Schema::table('verifikasi_kuk_apl02', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->change();
        });
    }
};
