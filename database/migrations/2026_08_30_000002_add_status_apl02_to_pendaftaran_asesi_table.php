<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->string('status_apl02')->default('draft')->after('rekomendasi_asesor_status'); // draft, submitted, under_review, revision, approved
            $table->timestamp('tanggal_submit_apl02')->nullable()->after('status_apl02');
        });

        // Sinkronisasi data awal berdasarkan rekomendasi_asesor_status dan jawaban_apl02 existing
        try {
            DB::table('pendaftaran_asesi')
                ->where('rekomendasi_asesor_status', 'dapat_dilanjutkan')
                ->update(['status_apl02' => 'approved']);

            DB::table('pendaftaran_asesi')
                ->where('rekomendasi_asesor_status', 'tidak_dapat_dilanjutkan')
                ->update(['status_apl02' => 'revision']);

            DB::table('pendaftaran_asesi')
                ->whereNull('rekomendasi_asesor_status')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('jawaban_apl02')
                          ->whereColumn('jawaban_apl02.pendaftaran_id', 'pendaftaran_asesi.id');
                })
                ->update(['status_apl02' => 'submitted', 'tanggal_submit_apl02' => now()]);
        } catch (\Throwable $e) {
            // Ignore error during initial data backfill if table is clean
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_asesi', function (Blueprint $table) {
            $table->dropColumn([
                'status_apl02',
                'tanggal_submit_apl02',
            ]);
        });
    }
};
