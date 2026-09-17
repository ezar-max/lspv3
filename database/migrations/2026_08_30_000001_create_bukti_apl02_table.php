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
        Schema::create('bukti_apl02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('elemen_id')->constrained('elemen_kompetensi')->onDelete('cascade');
            $table->foreignId('dokumen_id')->nullable()->constrained('dokumen_asesi')->onDelete('set null');
            $table->string('sumber')->default('upload'); // 'upload' atau 'apl01'
            $table->string('nama_file_asli')->nullable();
            $table->string('nama_file_tersimpan')->nullable();
            $table->string('file_path')->nullable(); // relative path: 'bukti_apl02/...'
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();

            $table->index(['pendaftaran_id', 'elemen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_apl02');
    }
};
