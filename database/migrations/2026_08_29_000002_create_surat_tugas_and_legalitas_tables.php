<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Surat Tugas Asesor
        if (!Schema::hasTable('surat_tugas')) {
            Schema::create('surat_tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_asesmen')->onDelete('set null');
                $table->foreignId('asesor_id')->nullable()->constrained('pengguna')->onDelete('set null');
                $table->string('nomor_surat')->unique();
                $table->date('tanggal_surat');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->string('tujuan_penugasan')->default('Pelaksanaan Uji Kompetensi / Asesmen');
                $table->string('lokasi_tuk')->nullable();
                $table->string('status')->default('Diterbitkan');
                $table->text('catatan')->nullable();
                $table->string('file_surat_tugas')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Dokumen Legalitas & Lisensi LSP
        if (!Schema::hasTable('dokumen_legalitas_lsp')) {
            Schema::create('dokumen_legalitas_lsp', function (Blueprint $table) {
                $table->id();
                $table->string('nama_dokumen');
                $table->string('nomor_dokumen')->nullable();
                $table->string('kategori')->default('Lisensi BNSP');
                $table->date('tanggal_terbit')->nullable();
                $table->string('masa_berlaku')->nullable();
                $table->string('status_dokumen')->default('Aktif');
                $table->string('file_path')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tugas');
        Schema::dropIfExists('dokumen_legalitas_lsp');
    }
};
