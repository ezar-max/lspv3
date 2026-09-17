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
        if (!Schema::hasTable('master_ak01')) {
            Schema::create('master_ak01', function (Blueprint $table) {
                $table->id();
                $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
                $table->foreignId('asesor_id')->nullable()->constrained('pengguna')->nullOnDelete();
                $table->string('tuk_type')->default('Sewaktu');
                $table->json('bukti_dikumpulkan')->nullable();
                $table->text('bukti_dikumpulkan_lainnya')->nullable();
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
        Schema::dropIfExists('master_ak01');
    }
};
