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
        if (!Schema::hasTable('master_ak07')) {
            Schema::create('master_ak07', function (Blueprint $table) {
                $table->id();
                $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
                $table->foreignId('asesor_id')->nullable()->constrained('pengguna')->nullOnDelete();
                $table->unsignedTinyInteger('potensi_asesi')->default(1);
                $table->string('fase_penggunaan')->default('saat_pra_asesmen');
                $table->json('items_checklist')->nullable();
                $table->text('acuan_pembanding_disepakati')->nullable();
                $table->text('metode_disepakati')->nullable();
                $table->text('instrumen_disepakati')->nullable();
                $table->text('catatan_asesor')->nullable();
                $table->longText('tanda_tangan_asesor')->nullable();
                $table->timestamp('tanggal_ttd_asesor')->nullable();
                $table->string('status')->default('selesai'); // draft, selesai
                $table->timestamps();

                $table->unique('skema_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ak07');
    }
};
