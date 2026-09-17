<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan duplikasi jika ada pada mapa_01 (mempertahankan record id terbesar/terbaru)
        if (Schema::hasTable('mapa_01')) {
            $duplicates01 = DB::table('mapa_01')
                ->select('pendaftaran_id', DB::raw('COUNT(*) as total'))
                ->groupBy('pendaftaran_id')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicates01 as $dup) {
                $keepId = DB::table('mapa_01')
                    ->where('pendaftaran_id', $dup->pendaftaran_id)
                    ->orderByDesc('id')
                    ->value('id');

                DB::table('mapa_01')
                    ->where('pendaftaran_id', $dup->pendaftaran_id)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }

            Schema::table('mapa_01', function (Blueprint $table) {
                $table->unique('pendaftaran_id', 'mapa_01_pendaftaran_id_unique');
            });
        }

        // 2. Bersihkan duplikasi jika ada pada mapa_02 (mempertahankan record id terbesar/terbaru)
        if (Schema::hasTable('mapa_02')) {
            $duplicates02 = DB::table('mapa_02')
                ->select('pendaftaran_id', DB::raw('COUNT(*) as total'))
                ->groupBy('pendaftaran_id')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicates02 as $dup) {
                $keepId = DB::table('mapa_02')
                    ->where('pendaftaran_id', $dup->pendaftaran_id)
                    ->orderByDesc('id')
                    ->value('id');

                DB::table('mapa_02')
                    ->where('pendaftaran_id', $dup->pendaftaran_id)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }

            Schema::table('mapa_02', function (Blueprint $table) {
                $table->unique('pendaftaran_id', 'mapa_02_pendaftaran_id_unique');
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
                $table->dropUnique('mapa_01_pendaftaran_id_unique');
            });
        }

        if (Schema::hasTable('mapa_02')) {
            Schema::table('mapa_02', function (Blueprint $table) {
                $table->dropUnique('mapa_02_pendaftaran_id_unique');
            });
        }
    }
};
