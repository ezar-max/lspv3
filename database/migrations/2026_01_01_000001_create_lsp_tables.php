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
        // 1. Tabel Pengguna (User)
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('kata_sandi');
            $table->enum('peran', ['superadmin', 'admin', 'asesor', 'asesi'])->default('asesi');
            $table->string('nomor_telepon')->nullable();
            $table->string('foto_profil')->nullable();
            $table->longText('tanda_tangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel Profil Asesi
        Schema::create('profil_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->onDelete('cascade');
            $table->string('nik', 16)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nama_sekolah_instansi')->nullable();
            $table->string('nomor_pendaftaran')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Skema Sertifikasi
        Schema::create('skema_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_skema')->unique();
            $table->string('nama_skema');
            $table->string('kategori')->default('Teknologi Informasi');
            $table->text('deskripsi')->nullable();
            $table->decimal('biaya', 12, 2)->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->string('gambar')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Unit Kompetensi
        Schema::create('unit_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->string('kode_unit');
            $table->string('judul_unit');
            $table->string('standar_kompetensi')->default('SKKNI');
            $table->timestamps();
        });

        // 5. Tabel Jadwal Asesmen
        Schema::create('jadwal_asesmen', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jadwal')->unique();
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->string('nama_tuk');
            $table->date('tanggal_uji');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->integer('kuota')->default(20);
            $table->enum('status_jadwal', ['terjadwal', 'berlangsung', 'selesai', 'dibatalkan'])->default('terjadwal');
            $table->timestamps();
        });

        // 6. Tabel Pendaftaran Asesi
        Schema::create('pendaftaran_asesi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran')->unique();
            $table->foreignId('asesi_id')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('skema_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_asesmen')->onDelete('set null');
            $table->date('tanggal_daftar');
            $table->string('status_pendaftaran')->default('draft');
            $table->text('catatan_verifikasi')->nullable();
            $table->longText('tanda_tangan_admin')->nullable();
            $table->timestamps();
        });

        // 7. Tabel Dokumen Asesi
        Schema::create('dokumen_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->string('jenis_dokumen'); // KTP, Ijazah, Pasfoto, APL01, APL02, Portofolio
            $table->string('nama_dokumen');
            $table->string('file_path');
            $table->enum('status_verifikasi', ['menunggu', 'valid', 'tidak_valid'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 8. Tabel Penilaian Asesmen
        Schema::create('penilaian_asesmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('unit_kompetensi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->enum('nilai_kompetensi', ['K', 'BK'])->default('BK'); // K = Kompeten, BK = Belum Kompeten
            $table->text('bukti_pendukung')->nullable();
            $table->text('catatan_asesor')->nullable();
            $table->timestamps();
        });

        // 9. Tabel Rekomendasi Asesmen
        Schema::create('rekomendasi_asesmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_asesi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('pengguna')->onDelete('cascade');
            $table->enum('keputusan', ['kompeten', 'belum_kompeten'])->default('belum_kompeten');
            $table->text('catatan_rekomendasi')->nullable();
            $table->date('tanggal_rekomendasi')->nullable();
            $table->longText('tanda_tangan_asesor')->nullable();
            $table->timestamps();
        });

        // 10. Tabel Berita Acara
        Schema::create('berita_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal_asesmen')->onDelete('cascade');
            $table->string('nomor_berita_acara')->unique();
            $table->date('tanggal_pelaksanaan');
            $table->integer('jumlah_peserta')->default(0);
            $table->integer('jumlah_kompeten')->default(0);
            $table->integer('jumlah_belum_kompeten')->default(0);
            $table->text('catatan_pelaksanaan')->nullable();
            $table->string('file_berita_acara')->nullable();
            $table->longText('tanda_tangan_asesor')->nullable();
            $table->timestamps();
        });

        // 11. Tabel Berita & Pengumuman
        Schema::create('berita_pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->enum('kategori', ['berita', 'pengumuman', 'panduan'])->default('berita');
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            $table->string('gambar')->nullable();
            $table->foreignId('penulis_id')->constrained('pengguna')->onDelete('cascade');
            $table->boolean('dipublikasikan')->default(true);
            $table->date('tanggal_publikasi');
            $table->timestamps();
        });

        // 12. Tabel Log Aktivitas
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->string('aktivitas');
            $table->text('deskripsi')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 13. Tabel Pengaturan Sistem
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lsp')->default('LSP SMK Keahlian');
            $table->string('kode_lsp')->default('LSP-P1-SMK');
            $table->string('nomor_lisensi')->default('BNSP-LSP-1234-ID');
            $table->string('email_resmi')->default('info@lsp.sch.id');
            $table->string('nomor_telepon')->default('(021) 7890-1234');
            $table->text('alamat_lengkap')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('tentang_lsp')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('berita_pengumuman');
        Schema::dropIfExists('berita_acara');
        Schema::dropIfExists('rekomendasi_asesmen');
        Schema::dropIfExists('penilaian_asesmen');
        Schema::dropIfExists('dokumen_asesi');
        Schema::dropIfExists('pendaftaran_asesi');
        Schema::dropIfExists('jadwal_asesmen');
        Schema::dropIfExists('unit_kompetensi');
        Schema::dropIfExists('skema_sertifikasi');
        Schema::dropIfExists('profil_asesi');
        Schema::dropIfExists('pengguna');
    }
};
