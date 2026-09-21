<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutentikasiController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\AsesiController;
use App\Http\Controllers\AsesorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SkemaController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\FormulirController;
use App\Http\Controllers\AsesiTahapanController;

/*
|--------------------------------------------------------------------------
| 1. HALAMAN PUBLIK & AUTENTIKASI
|--------------------------------------------------------------------------
*/
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::get('/profil', [PublikController::class, 'profilLsp'])->name('profil-lsp');
Route::get('/skema', [PublikController::class, 'daftarSkema'])->name('publik.skema');
Route::get('/skema/{id}', [PublikController::class, 'detailSkema'])->name('publik.skema.detail');
Route::get('/berita', [PublikController::class, 'daftarBerita'])->name('publik.berita');
Route::get('/berita/{slug}', [PublikController::class, 'detailBerita'])->name('publik.berita.detail');
Route::get('/api/skema/{id}/units', [PublikController::class, 'apiSkemaUnits'])->name('api.skema.units');
Route::get('/kontak', [PublikController::class, 'kontak'])->name('kontak');

Route::get('/masuk', [AutentikasiController::class, 'tampilMasuk'])->name('masuk');
Route::post('/masuk', [AutentikasiController::class, 'prosesMasuk'])->name('masuk.proses');
Route::get('/registrasi', [AutentikasiController::class, 'tampilRegistrasi'])->name('registrasi');
Route::get('/daftar', [AutentikasiController::class, 'tampilRegistrasi'])->name('daftar');
Route::post('/registrasi', [AutentikasiController::class, 'prosesRegistrasi'])->name('registrasi.proses');
Route::post('/keluar', [AutentikasiController::class, 'keluar'])->name('keluar');

/*
|--------------------------------------------------------------------------
| 2. ROLE: ASESI (SISWA / PESERTA UJI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'peran:asesi'])->prefix('asesi')->as('asesi.')->group(function () {
    Route::get('/dashboard', [AsesiController::class, 'dashboard'])->name('dashboard');
    Route::get('/dasbor', [AsesiController::class, 'dashboard'])->name('dasbor');
    Route::get('/profil', [AsesiController::class, 'profil'])->name('profil');
    Route::post('/profil', [AsesiController::class, 'simpanProfil'])->name('profil.simpan');
    Route::post('/request-perbaikan/{id}', [AsesiController::class, 'requestPerbaikanBiodata'])->name('request-perbaikan');
    
    // =========================================================================
    // MULTI-STEP WIZARD TAHAPAN ASESMEN TERPADU (FR.APL.01, FR.APL.02, FR.AK.01)
    // =========================================================================
    Route::get('/tahapan', [AsesiTahapanController::class, 'index'])->name('tahapan');
    Route::post('/tahapan/apl01', [AsesiTahapanController::class, 'storeApl01'])->name('tahapan.apl01');
    Route::post('/tahapan/apl02', [AsesiTahapanController::class, 'storeApl02'])->name('tahapan.apl02');
    Route::post('/tahapan/apl02/upload-bukti', [AsesiTahapanController::class, 'uploadBuktiApl02'])->name('apl02.upload_bukti');
    Route::post('/tahapan/apl02/pilih-bukti-apl01', [AsesiTahapanController::class, 'pilihBuktiApl01'])->name('apl02.pilih_bukti_apl01');
    Route::delete('/tahapan/apl02/hapus-bukti/{id}', [AsesiTahapanController::class, 'hapusBuktiApl02'])->name('apl02.hapus_bukti');
    Route::post('/tahapan/ak01', [AsesiTahapanController::class, 'storeAk01'])->name('tahapan.ak01');
    
    // 1 HALAMAN TERPADU FORMULIR ASESMEN (APL-01, APL-02, AK-01) - ALIAS & KOMPATIBILITAS
    Route::get('/formulir', [AsesiTahapanController::class, 'index'])->name('formulir');
    Route::get('/biodata', [AsesiTahapanController::class, 'index'])->name('biodata');
    Route::post('/formulir/apl-01', [AsesiTahapanController::class, 'storeApl01'])->name('formulir.simpan-apl01');
    Route::post('/formulir/skema-baru', [AsesiController::class, 'buatPendaftaranSkemaBaru'])->name('formulir.skema-baru');
    
    // RUTE ALIAS & KOMPATIBILITAS
    Route::get('/pendaftaran', [AsesiController::class, 'pendaftaran'])->name('pendaftaran');
    Route::get('/pendaftaran/bagian-1', [AsesiController::class, 'pendaftaranBagian1'])->name('pendaftaran.bagian1');
    Route::post('/pendaftaran/bagian-1', [AsesiController::class, 'simpanBagian1'])->name('pendaftaran.simpan1');
    Route::get('/pendaftaran/bagian-2', [AsesiController::class, 'pendaftaranBagian2'])->name('pendaftaran.bagian2');
    Route::post('/pendaftaran/bagian-2', [AsesiController::class, 'simpanBagian2'])->name('pendaftaran.simpan2');
    Route::get('/pendaftaran/bagian-31', [AsesiController::class, 'pendaftaranBagian31'])->name('pendaftaran.bagian31');
    Route::post('/pendaftaran/bagian-31', [AsesiController::class, 'simpanBagian31'])->name('pendaftaran.simpan31');
    Route::get('/pendaftaran/bagian-32', [AsesiController::class, 'pendaftaranBagian32'])->name('pendaftaran.bagian32');
    Route::post('/pendaftaran/bagian-32', [AsesiController::class, 'simpanBagian32'])->name('pendaftaran.simpan32');
    Route::get('/pendaftaran/{id}/upload', [AsesiController::class, 'uploadDokumen'])->name('upload-dokumen');
    Route::post('/pendaftaran/{id}/upload', [AsesiController::class, 'simpanDokumen'])->name('upload-dokumen.simpan');
    Route::post('/pendaftaran/{id}/ajukan', [AsesiController::class, 'ajukanPendaftaran'])->name('ajukan');
    
    Route::get('/jadwal', [AsesiController::class, 'jadwal'])->name('jadwal');
    Route::post('/pilih-asesor/{id}', [AsesiController::class, 'pilihAsesor'])->name('pilih-asesor');
    Route::get('/apl-02', [AsesiController::class, 'apl02'])->name('apl02');
    Route::post('/apl-02', [AsesiController::class, 'simpanApl02'])->name('apl02.simpan');
    Route::get('/ak-01/{id?}', [AsesiController::class, 'ak01Detail'])->name('ak01');
    Route::post('/ak-01/{id}', [AsesiController::class, 'simpanAk01'])->name('ak01.simpan');
    Route::post('/ak-01/{id}/sign', [AsesiTahapanController::class, 'storeAk01'])->name('ak01.sign');
    
    // FR.AK.07 (Penyesuaian yang Wajar) untuk Asesi
    Route::get('/ak-07/{pendaftaranId?}', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'asesiDetail'])->name('ak07');
    Route::post('/ak-07/{pendaftaranId}/sign-asesi', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'signAsesi'])->name('ak07.sign-asesi');
    
    // RUANG UJIAN ONLINE HARI H (FR.IA)
    Route::get('/ujian/status-live', [\App\Http\Controllers\AsesiUjianController::class, 'statusSesiLive'])->name('ujian.status-live');

    Route::middleware(['jadwal.aktif'])->group(function () {
        Route::get('/ujian', [\App\Http\Controllers\AsesiUjianController::class, 'index'])->name('ujian');
        Route::get('/ruang-uji', [\App\Http\Controllers\AsesiUjianController::class, 'index'])->name('ruang-uji');
        Route::post('/ujian/autosave', [\App\Http\Controllers\AsesiUjianController::class, 'autosave'])->name('ujian.autosave');
        Route::post('/ujian/upload-ia02', [\App\Http\Controllers\AsesiUjianController::class, 'uploadIa02'])->name('ujian.upload-ia02');
        Route::post('/ujian/submit', [\App\Http\Controllers\AsesiUjianController::class, 'submitUjian'])->name('ujian.submit');
    });

    Route::get('/hasil', [AsesiController::class, 'hasilNilai'])->name('hasil');
    Route::get('/hasil-nilai', [AsesiController::class, 'hasilNilai'])->name('hasil-nilai');
    Route::get('/dokumen', [AsesiController::class, 'dokumenBukti'])->name('dokumen');
});

/*
|--------------------------------------------------------------------------
| 3. ROLE: ASESOR (PENGUJI / PENILAI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'peran:asesor'])->prefix('asesor')->as('asesor.')->group(function () {
    Route::get('/dashboard', [AsesorController::class, 'dashboard'])->name('dashboard');
    Route::get('/dasbor', [AsesorController::class, 'dashboard'])->name('dasbor');
    Route::get('/dashboard/heartbeat', [AsesorController::class, 'dashboardHeartbeat'])->name('dashboard.heartbeat');
    Route::get('/api/cek-pembaruan', [AsesorController::class, 'dashboardHeartbeat'])->name('cek-pembaruan');
    Route::get('/jadwal', [AsesorController::class, 'jadwal'])->name('jadwal');
    Route::post('/jadwal/{jadwalId}/toggle-status', [\App\Http\Controllers\AsesorPenilaianController::class, 'toggleStatusJadwal'])->name('jadwal.toggle-status');
    Route::post('/jadwal/{jadwalId}/perpanjang-waktu', [\App\Http\Controllers\AsesorPenilaianController::class, 'perpanjangWaktuJadwal'])->name('jadwal.perpanjang-waktu');
    Route::get('/mapa', [AsesorController::class, 'mapa'])->name('mapa');
    Route::get('/formulir', [AsesorController::class, 'mapa'])->name('formulir');
    Route::get('/penilaian', [AsesorController::class, 'daftarPeserta'])->name('penilaian');
    Route::get('/peserta', [AsesorController::class, 'daftarPeserta'])->name('daftar-peserta');
    
    // LEMBAR PENILAIAN LIVE HARI H (FR.IA.01, IA.07, IA.06B, AK.02, AK.03)
    Route::middleware(['jadwal.aktif'])->group(function () {
        Route::get('/penilaian-live/{pendaftaranId}', [\App\Http\Controllers\AsesorPenilaianController::class, 'index'])->name('penilaian-live');
        Route::post('/penilaian-live/{pendaftaranId}', [\App\Http\Controllers\AsesorPenilaianController::class, 'simpanPenilaianLive'])->name('penilaian-live.simpan');
    });

    // REKAP & KOREKSI UJIAN TEORI TERPUSAT SELURUH ASESI (FR.IA.05 & FR.IA.06)
    Route::get('/koreksi-teori', [\App\Http\Controllers\AsesorPenilaianController::class, 'koreksiTeoriMassal'])->name('koreksi-teori');
    Route::post('/koreksi-teori/simpan', [\App\Http\Controllers\AsesorPenilaianController::class, 'simpanKoreksiTeoriMassal'])->name('koreksi-teori.simpan');


    // Verifikasi APL-02 & Penilaian Awal
    Route::get('/penilaian/{pendaftaranId}', [AsesorController::class, 'inputPenilaian'])->name('input-penilaian');
    Route::post('/penilaian/{pendaftaranId}', [AsesorController::class, 'simpanPenilaian'])->name('input-penilaian.simpan');
    
    // Tinjauan & Pengesahan FR.AK.01 oleh Asesor
    Route::get('/ak-01/{pendaftaranId}', [AsesorController::class, 'ak01Detail'])->name('ak01.detail');
    Route::post('/ak-01/{pendaftaranId}/sign', [AsesorController::class, 'simpanAk01Asesor'])->name('ak01.simpan');
    
    // FR.AK.07: Ceklis Penyesuaian yang Wajar dan Beralasan
    Route::get('/pendaftaran/{pendaftaranId}/ak-07', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'edit'])->name('pendaftaran.ak07.edit');
    Route::post('/pendaftaran/{pendaftaranId}/ak-07', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'update'])->name('pendaftaran.ak07.update');
    Route::post('/pendaftaran/{pendaftaranId}/ak-07/sign-asesor', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'signAsesor'])->name('pendaftaran.ak07.sign-asesor');
    Route::get('/pendaftaran/{pendaftaranId}/ak-07/cetak', [\App\Http\Controllers\Asesor\AssessmentAk07Controller::class, 'cetak'])->name('pendaftaran.ak07.cetak');
    
    Route::get('/berita-acara', [AsesorController::class, 'beritaAcara'])->name('berita-acara');
    Route::post('/berita-acara', [AsesorController::class, 'simpanBeritaAcara'])->name('berita-acara.simpan');
});

// Formulir MAPA 01 & 02 (Akses Bersama: Asesor, Admin, Superadmin)
Route::middleware(['auth', 'peran:asesor,admin,superadmin'])->prefix('asesor')->as('asesor.')->group(function () {
    // Master MAPA per Skema Sertifikasi (Template Mandiri)
    Route::get('/skema/{skemaId}/mapa-01', [AsesorController::class, 'masterMapa01'])->name('skema.mapa-01');
    Route::post('/skema/{skemaId}/mapa-01', [AsesorController::class, 'simpanMasterMapa01'])->name('skema.mapa-01.simpan');
    Route::get('/skema/{skemaId}/mapa-02', [AsesorController::class, 'masterMapa02'])->name('skema.mapa-02');
    Route::post('/skema/{skemaId}/mapa-02', [AsesorController::class, 'simpanMasterMapa02'])->name('skema.mapa-02.simpan');

    // Master FR.AK.01 per Skema Sertifikasi (Rencana Asesmen Mandiri)
    Route::get('/skema/{skemaId}/ak-01', [AsesorController::class, 'masterAk01'])->name('skema.ak-01');
    Route::post('/skema/{skemaId}/ak-01', [AsesorController::class, 'simpanMasterAk01'])->name('skema.ak-01.simpan');

    // Master FR.AK.07 per Skema Sertifikasi (Penyesuaian Yang Wajar Mandiri)
    Route::get('/skema/{skemaId}/ak-07', [AsesorController::class, 'masterAk07'])->name('skema.ak-07');
    Route::post('/skema/{skemaId}/ak-07', [AsesorController::class, 'simpanMasterAk07'])->name('skema.ak-07.simpan');

    // MAPA per Peserta (Asesi Terdaftar)
    Route::get('/mapa-01/{pendaftaranId}', [AsesorController::class, 'mapa01'])->name('mapa-01');
    Route::post('/mapa-01/{pendaftaranId}', [AsesorController::class, 'simpanMapa01'])->name('mapa-01.simpan');
    Route::get('/mapa-02/{pendaftaranId}', [AsesorController::class, 'mapa02'])->name('mapa-02');
    Route::post('/mapa-02/{pendaftaranId}', [AsesorController::class, 'simpanMapa02'])->name('mapa-02.simpan');
});

/*
|--------------------------------------------------------------------------
| 4. ROLE: ADMIN (ADMINISTRATOR)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'peran:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dasbor', [AdminController::class, 'dashboard'])->name('dasbor');
    Route::get('/api/cek-pendaftaran-terbaru', [AdminController::class, 'cekPendaftaranTerbaru'])->name('cek-pendaftaran-terbaru');
    
    Route::get('/verifikasi', [AdminController::class, 'verifikasiBerkas'])->name('verifikasi-berkas');
    Route::get('/verifikasi/{id}', [AdminController::class, 'detailVerifikasi'])->name('detail-verifikasi');
    Route::post('/verifikasi/{id}', [AdminController::class, 'simpanVerifikasi'])->name('verifikasi.simpan');
    
    Route::get('/asesi', [AdminController::class, 'manajemenAsesi'])->name('manajemen-asesi');
    Route::get('/asesi/{id}', [AdminController::class, 'detailAsesi'])->name('detail-asesi');
    Route::post('/ttd-admin/{id}', [AdminController::class, 'simpanTtdAdmin'])->name('simpan-ttd');
    Route::post('/mapa-01/{id}/validasi', [AdminController::class, 'validasiMapa01'])->name('mapa-01.validasi');
    
    Route::get('/skema', [SkemaController::class, 'index'])->name('manajemen-skema');
    Route::post('/skema', [SkemaController::class, 'simpanSkema'])->name('skema.simpan');
    Route::post('/skema/{id}/ubah', [SkemaController::class, 'ubahSkema'])->name('skema.ubah');
    Route::delete('/skema/{id}', [SkemaController::class, 'hapusSkema'])->name('skema.hapus');
    Route::post('/skema/{skemaId}/unit', [SkemaController::class, 'simpanUnit'])->name('unit.simpan');
    Route::delete('/unit/{id}', [SkemaController::class, 'hapusUnit'])->name('unit.hapus');
    
    // Rute Elemen Kompetensi & KUK
    Route::post('/unit/{unitId}/elemen', [SkemaController::class, 'simpanElemen'])->name('elemen.simpan');
    Route::post('/elemen/{id}/ubah', [SkemaController::class, 'ubahElemen'])->name('elemen.ubah');
    Route::delete('/elemen/{id}', [SkemaController::class, 'hapusElemen'])->name('elemen.hapus');
    Route::post('/elemen/{elemenId}/kuk', [SkemaController::class, 'simpanKuk'])->name('kuk.simpan');
    Route::delete('/kuk/{id}', [SkemaController::class, 'hapusKuk'])->name('kuk.hapus');
    
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('manajemen-jadwal');
    Route::post('/jadwal', [JadwalController::class, 'simpanJadwal'])->name('jadwal.simpan');
    Route::post('/jadwal/{id}/ubah', [JadwalController::class, 'ubahJadwal'])->name('jadwal.ubah');
    Route::delete('/jadwal/{id}', [JadwalController::class, 'hapusJadwal'])->name('jadwal.hapus');
    
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('manajemen-pengumuman');
    Route::post('/pengumuman', [PengumumanController::class, 'simpanPengumuman'])->name('pengumuman.simpan');
    Route::post('/pengumuman/{id}/ubah', [PengumumanController::class, 'ubahPengumuman'])->name('pengumuman.ubah');
    Route::delete('/pengumuman/{id}', [PengumumanController::class, 'hapusPengumuman'])->name('pengumuman.hapus');
    Route::get('/laporan', [AdminController::class, 'laporanKelulusan'])->name('laporan-kelulusan');
    
    // Manajemen Data & Akun Asesor
    Route::get('/asesor', [AdminController::class, 'manajemenAsesor'])->name('manajemen-asesor');
    Route::post('/asesor', [AdminController::class, 'simpanAsesor'])->name('asesor.simpan');
    Route::post('/asesor/{id}/ubah', [AdminController::class, 'ubahAsesor'])->name('asesor.ubah');
    Route::delete('/asesor/{id}', [AdminController::class, 'hapusAsesor'])->name('asesor.hapus');

    // Pengaturan Sistem (Alias/Legacy)
    Route::get('/pengaturan', [AdminController::class, 'manajemenAsesor'])->name('pengaturan-sistem');
    Route::get('/pengaturan', [AdminController::class, 'pengaturanSistem'])->name('pengaturan-sistem');
    Route::post('/pengaturan', [AdminController::class, 'simpanPengaturanSistem'])->name('pengaturan-sistem.simpan');

    // ==========================================
    // PUSAT DOKUMEN, ARSIP, & LEGALITAS LSP
    // ==========================================
    Route::prefix('dokumen')->as('dokumen.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DokumenAdminController::class, 'index'])->name('index');
        
        // Surat Tugas Asesor
        Route::post('/surat-tugas', [\App\Http\Controllers\DokumenAdminController::class, 'simpanSuratTugas'])->name('surat-tugas.simpan');
        Route::get('/surat-tugas/{id}/cetak', [\App\Http\Controllers\DokumenAdminController::class, 'cetakSuratTugas'])->name('surat-tugas.cetak');
        Route::delete('/surat-tugas/{id}', [\App\Http\Controllers\DokumenAdminController::class, 'hapusSuratTugas'])->name('surat-tugas.hapus');

        // Berita Acara & Rapat Pleno (FR.AK.05 & FR.AK.06)
        Route::post('/berita-acara/generate', [\App\Http\Controllers\DokumenAdminController::class, 'generateBeritaAcara'])->name('berita-acara.generate');
        Route::get('/berita-acara/{id}/cetak', [\App\Http\Controllers\DokumenAdminController::class, 'cetakBeritaAcara'])->name('berita-acara.cetak');
        Route::delete('/berita-acara/{id}', [\App\Http\Controllers\DokumenAdminController::class, 'hapusBeritaAcara'])->name('berita-acara.hapus');

        // Dokumen Legalitas & Lisensi LSP
        Route::post('/legalitas', [\App\Http\Controllers\DokumenAdminController::class, 'simpanLegalitas'])->name('legalitas.simpan');
        Route::delete('/legalitas/{id}', [\App\Http\Controllers\DokumenAdminController::class, 'hapusLegalitas'])->name('legalitas.hapus');

        // Bundel Asesmen & Rekap Portofolio Lengkap
        Route::get('/bundel/{pendaftaranId}/cetak', [\App\Http\Controllers\DokumenAdminController::class, 'cetakBundelAsesmen'])->name('bundel.cetak');
    });
});

/*
|--------------------------------------------------------------------------
| MODUL MASTER PERANGKAT MUK & BANK SOAL FR.IA
| Akses Terpadu: Admin, Asesor, & Superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'peran:admin,asesor,superadmin'])->prefix('admin/master-muk')->as('admin.master-muk.')->group(function () {
    Route::get('/', [\App\Http\Controllers\MasterInstrumentController::class, 'index'])->name('index');
    Route::get('/tambah', [\App\Http\Controllers\MasterInstrumentController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\MasterInstrumentController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [\App\Http\Controllers\MasterInstrumentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\MasterInstrumentController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\MasterInstrumentController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/kelola', [\App\Http\Controllers\MasterInstrumentController::class, 'manage'])->name('manage');
    Route::post('/{id}/metadata', [\App\Http\Controllers\MasterInstrumentController::class, 'updateMetadata'])->name('update-metadata');
    Route::post('/{id}/clone', [\App\Http\Controllers\MasterInstrumentController::class, 'cloneInstrument'])->name('clone');

    Route::match(['get', 'post', 'delete'], '/soal/bulk-delete', [\App\Http\Controllers\QuestionBankController::class, 'bulkDelete'])->name('soal.bulk-delete');
    Route::post('/soal/simpan', [\App\Http\Controllers\QuestionBankController::class, 'store'])->name('soal.store');
    Route::post('/soal/{id}/ubah', [\App\Http\Controllers\QuestionBankController::class, 'update'])->whereNumber('id')->name('soal.update');
    Route::delete('/soal/{id}', [\App\Http\Controllers\QuestionBankController::class, 'destroy'])->whereNumber('id')->name('soal.destroy');
    Route::post('/{id}/generate-auto', [\App\Http\Controllers\QuestionBankController::class, 'generateAuto'])->whereNumber('id')->name('generate-auto');
    Route::post('/spec/simpan', [\App\Http\Controllers\QuestionBankController::class, 'storeSpec'])->name('spec.store');
    Route::delete('/spec/{id}', [\App\Http\Controllers\QuestionBankController::class, 'destroySpec'])->name('spec.destroy');
    Route::get('/{instrumentId}/export', [\App\Http\Controllers\QuestionBankController::class, 'export'])->name('export');
});

/*
|--------------------------------------------------------------------------
| 5. ROLE: SUPER ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'peran:superadmin'])->prefix('superadmin')->as('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dasbor', [SuperAdminController::class, 'dashboard'])->name('dasbor');
    
    Route::get('/pengguna', [SuperAdminController::class, 'manajemenPengguna'])->name('manajemen-pengguna');
    Route::post('/pengguna', [SuperAdminController::class, 'simpanPengguna'])->name('pengguna.simpan');
    Route::post('/pengguna/{id}/ubah', [SuperAdminController::class, 'ubahPengguna'])->name('pengguna.ubah');
    Route::delete('/pengguna/{id}', [SuperAdminController::class, 'hapusPengguna'])->name('pengguna.hapus');
    
    Route::get('/log', [SuperAdminController::class, 'logAktivitas'])->name('log-aktivitas');
    
    Route::get('/pengaturan', [SuperAdminController::class, 'pengaturanSistem'])->name('pengaturan-sistem');
    Route::post('/pengaturan', [SuperAdminController::class, 'simpanPengaturanSistem'])->name('pengaturan-sistem.simpan');
});

/*
|--------------------------------------------------------------------------
| 6. MODUL BERKAS & FORMULIR STANDAR BNSP (PREVIEW & ARSIP)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('formulir')->as('formulir.')->group(function () {
    Route::get('/', [FormulirController::class, 'index'])->name('index');
    Route::get('/ia-01/{pendaftaranId?}', [FormulirController::class, 'ia01'])->name('ia01');
    Route::get('/ia-02/{pendaftaranId?}', [FormulirController::class, 'ia02'])->name('ia02');
    Route::get('/ia-03/{pendaftaranId?}', [FormulirController::class, 'ia03'])->name('ia03');
    Route::get('/ia-04a/{pendaftaranId?}', [FormulirController::class, 'ia04a'])->name('ia04a');
    Route::get('/ia-04b/{pendaftaranId?}', [FormulirController::class, 'ia04b'])->name('ia04b');
    Route::get('/ia-05a/{pendaftaranId?}', [FormulirController::class, 'ia05a'])->name('ia05a');
    Route::get('/ia-05b/{pendaftaranId?}', [FormulirController::class, 'ia05b'])->name('ia05b');
    Route::get('/ia-05c/{pendaftaranId?}', [FormulirController::class, 'ia05c'])->name('ia05c');
    Route::get('/ia-06a/{pendaftaranId?}', [FormulirController::class, 'ia06a'])->name('ia06a');
    Route::get('/ia-06b/{pendaftaranId?}', [FormulirController::class, 'ia06b'])->name('ia06b');
    Route::get('/ia-06c/{pendaftaranId?}', [FormulirController::class, 'ia06c'])->name('ia06c');
    Route::get('/ia-07/{pendaftaranId?}', [FormulirController::class, 'ia07'])->name('ia07');
    Route::get('/ia-08/{pendaftaranId?}', [FormulirController::class, 'ia08'])->name('ia08');
    Route::get('/ia-09/{pendaftaranId?}', [FormulirController::class, 'ia09'])->name('ia09');
    Route::get('/ia-10/{pendaftaranId?}', [FormulirController::class, 'ia10'])->name('ia10');
    Route::get('/ia-11/{pendaftaranId?}', [FormulirController::class, 'ia11'])->name('ia11');
    
    // Helper Navigasi Generic Antar Formulir IA
    Route::get('/ia/nav/{kodeForm}/{pendaftaranId?}', function ($kodeForm, $pendaftaranId = null) {
        $map = [
            'FR.IA.01' => 'formulir.ia01',
            'FR.IA.02' => 'formulir.ia02',
            'FR.IA.03' => 'formulir.ia03',
            'FR.IA.04A' => 'formulir.ia04a',
            'FR.IA.04B' => 'formulir.ia04b',
            'FR.IA.05A' => 'formulir.ia05a',
            'FR.IA.05B' => 'formulir.ia05b',
            'FR.IA.05C' => 'formulir.ia05c',
            'FR.IA.06A' => 'formulir.ia06a',
            'FR.IA.06B' => 'formulir.ia06b',
            'FR.IA.06C' => 'formulir.ia06c',
            'FR.IA.07' => 'formulir.ia07',
            'FR.IA.08' => 'formulir.ia08',
            'FR.IA.09' => 'formulir.ia09',
            'FR.IA.10' => 'formulir.ia10',
            'FR.IA.11' => 'formulir.ia11',
        ];
        $clean = strtoupper(trim($kodeForm));
        $targetRoute = $map[$clean] ?? 'formulir.index';
        return redirect()->route($targetRoute, ['pendaftaranId' => $pendaftaranId]);
    })->name('ia.index');
    
    // Action Simpan Penilaian & Ujian
    Route::post('/simpan/{kodeForm}/{pendaftaranId}', [FormulirController::class, 'simpanIa'])->name('ia.simpan');
    Route::post('/simpan-ttd-asesi/{kodeForm}/{pendaftaranId}', [FormulirController::class, 'simpanTtdAsesiIa'])->name('ia.simpan-ttd-asesi');

    Route::get('/mapa-01/{pendaftaranId?}', [FormulirController::class, 'mapa01'])->name('mapa01');
    Route::get('/mapa-02/{pendaftaranId?}', [FormulirController::class, 'mapa02'])->name('mapa02');
    Route::get('/ak-01/{pendaftaranId?}', [FormulirController::class, 'ak01'])->name('ak01');
    Route::post('/ak-01/simpan/{pendaftaranId}', [FormulirController::class, 'simpanAk01'])->name('ak01.simpan');
    Route::get('/ak-02/{pendaftaranId?}', function ($pendaftaranId = null) {
        return redirect()->route('dokumen-asesmen.ak02.show', ['pendaftaranId' => $pendaftaranId ?: 0]);
    })->name('ak02');

    Route::get('/apl-01/{pendaftaranId?}', [FormulirController::class, 'apl01'])->name('apl01');
    Route::get('/apl-02/{pendaftaranId?}', [FormulirController::class, 'apl02'])->name('apl02');
});

// Portal Publik Magic Link untuk Supervisor / Pihak Ketiga (FR.IA.10)
Route::get('/verifikasi-pihak-ketiga/{token}', [FormulirController::class, 'guestIa10'])->name('formulir.ia10.guest');
Route::post('/verifikasi-pihak-ketiga/{token}', [FormulirController::class, 'simpanGuestIa10'])->name('formulir.ia10.guest.simpan');

/*
|--------------------------------------------------------------------------
| 7. GLOBAL NOTIFICATION SYSTEM
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/notifikasi', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::match(['get', 'post'], '/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| 8. MODUL INTEGRASI DOKUMEN ASESMEN RESMI BNSP
| FR.AK.02, FR.AK.03, FR.AK.05, FR.AK.06, FR.VA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('dokumen-asesmen')->as('dokumen-asesmen.')->group(function () {
    // Pusat Dokumen Asesmen Hub
    Route::get('/', [\App\Http\Controllers\DokumenAsesmenController::class, 'index'])->name('index');
    Route::get('/audit-trail/{type}/{id}', [\App\Http\Controllers\DokumenAsesmenController::class, 'auditTrail'])->name('audit-trail');
    Route::post('/reopen', [\App\Http\Controllers\DokumenAsesmenController::class, 'reopen'])->name('reopen');

    // FR.AK.02 — Rekaman Asesmen Kompetensi
    Route::prefix('ak-02')->as('ak02.')->group(function () {
        Route::get('/{pendaftaranId}', [\App\Http\Controllers\AssessmentAk02Controller::class, 'show'])->name('edit');
        Route::get('/{pendaftaranId}/show', [\App\Http\Controllers\AssessmentAk02Controller::class, 'show'])->name('show');
        Route::post('/{pendaftaranId}/autosave', [\App\Http\Controllers\AssessmentAk02Controller::class, 'autosave'])->name('autosave');
        Route::post('/{pendaftaranId}/simpan', [\App\Http\Controllers\AssessmentAk02Controller::class, 'simpan'])->name('simpan');
        Route::post('/{pendaftaranId}/sign-asesor', [\App\Http\Controllers\AssessmentAk02Controller::class, 'signAsesor'])->name('sign-asesor');
        Route::post('/{pendaftaranId}/sign-asesi', [\App\Http\Controllers\AssessmentAk02Controller::class, 'signAsesi'])->name('sign-asesi');
        Route::post('/{pendaftaranId}/ttd-asesi', [\App\Http\Controllers\AssessmentAk02Controller::class, 'signAsesi'])->name('ttd-asesi');
        Route::post('/{pendaftaranId}/reopen', [\App\Http\Controllers\AssessmentAk02Controller::class, 'reopen'])->name('reopen');
        Route::get('/{pendaftaranId}/cetak', [\App\Http\Controllers\AssessmentAk02Controller::class, 'cetak'])->name('cetak');
    });

    // FR.AK.03 — Umpan Balik dan Catatan Asesmen
    Route::prefix('ak-03')->as('ak03.')->group(function () {
        Route::get('/{pendaftaranId}', [\App\Http\Controllers\AssessmentAk03Controller::class, 'show'])->name('show');
        Route::post('/{pendaftaranId}/simpan', [\App\Http\Controllers\AssessmentAk03Controller::class, 'simpan'])->name('simpan');
        Route::get('/{pendaftaranId}/cetak', [\App\Http\Controllers\AssessmentAk03Controller::class, 'cetak'])->name('cetak');
    });

    // FR.AK.05 — Laporan Asesmen
    Route::prefix('ak-05')->as('ak05.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AssessmentAk05Controller::class, 'index'])->name('index');
        Route::post('/buat', [\App\Http\Controllers\AssessmentAk05Controller::class, 'create'])->name('create');
        Route::get('/{id}/edit', [\App\Http\Controllers\AssessmentAk05Controller::class, 'edit'])->name('edit');
        Route::post('/{id}/sync', [\App\Http\Controllers\AssessmentAk05Controller::class, 'sync'])->name('sync');
        Route::post('/{id}/simpan', [\App\Http\Controllers\AssessmentAk05Controller::class, 'simpan'])->name('simpan');
        Route::get('/{id}/cetak', [\App\Http\Controllers\AssessmentAk05Controller::class, 'cetak'])->name('cetak');
    });

    // FR.AK.06 — Meninjau Proses Asesmen
    Route::prefix('ak-06')->as('ak06.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AssessmentAk06Controller::class, 'index'])->name('index');
        Route::post('/buat', [\App\Http\Controllers\AssessmentAk06Controller::class, 'create'])->name('create');
        Route::get('/{id}/edit', [\App\Http\Controllers\AssessmentAk06Controller::class, 'edit'])->name('edit');
        Route::post('/{id}/simpan', [\App\Http\Controllers\AssessmentAk06Controller::class, 'simpan'])->name('simpan');
        Route::get('/{id}/cetak', [\App\Http\Controllers\AssessmentAk06Controller::class, 'cetak'])->name('cetak');
    });

    // FR.VA — Memberikan Kontribusi dalam Validasi Asesmen
    Route::prefix('va')->as('va.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AssessmentVaController::class, 'index'])->name('index');
        Route::post('/buat', [\App\Http\Controllers\AssessmentVaController::class, 'create'])->name('create');
        Route::get('/{id}/wizard', [\App\Http\Controllers\AssessmentVaController::class, 'wizard'])->name('wizard');
        Route::post('/{id}/step', [\App\Http\Controllers\AssessmentVaController::class, 'saveStep'])->name('save-step');
        Route::get('/{id}/cetak', [\App\Http\Controllers\AssessmentVaController::class, 'cetak'])->name('cetak');
    });
});

/*
|--------------------------------------------------------------------------
| 9. FALLBACK ROUTE: PUBLIC STORAGE ACCESS (PROTECTION FOR WINDOWS/DEV)
| 9. SECURED STORAGE ACCESS (PROTECTED FALLBACK FOR WINDOWS/DEV)
|--------------------------------------------------------------------------
| Memastikan seluruh berkas bukti, tanda tangan, dan dokumen asesi di storage
| dapat diakses secara langsung jika web server tidak mengikuti symlink/junction.
| Menyajikan berkas publik & terproteksi dengan validasi traversal dan otorisasi dokumen.
*/
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.fallback');
Route::get('/storage/{path}', [\App\Http\Controllers\StorageFileController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.fallback');
