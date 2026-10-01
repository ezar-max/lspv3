<?php

namespace Tests\Feature;

use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsesiDeadlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_asesi_tidak_deadlock_saat_submit_ujian_setelah_apl02_disetujui()
    {
        Storage::fake('public');
        
        $asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor 1',
            'email' => 'asesor1@example.com',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'asesor',
            'aktif' => true
        ]);
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi 1',
            'email' => 'asesi1@example.com',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'asesi',
            'aktif' => true
        ]);
        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Skema Test',
            'kategori' => 'KKNI',
            'sektor' => 'IT',
            'jenis' => 'Klaster',
            'aktif' => true
        ]);
        
        $jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-001',
            'nama_jadwal' => 'Jadwal Test',
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'waktu_mulai' => '00:01',
            'waktu_selesai' => '23:59',
            'tanggal_uji' => now()->toDateString(),
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK TUK',
        ]);
        
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'jadwal_id' => $jadwal->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'disetujui',
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'ttd',
            'tanda_tangan_asesor_ak01' => 'ttd',
            // MAPA
            'tanda_tangan_asesi_mapa01' => 'ttd',
            'tanda_tangan_asesor_mapa01' => 'ttd',
        ]);

        $unit = \App\Models\UnitKompetensi::create([
            'skema_id' => $skema->id,
            'kode_unit' => 'U01',
            'judul_unit' => 'Unit 1',
            'jenis_standar' => 'SKKNI'
        ]);
        $elemen = \App\Models\ElemenKompetensi::create([
            'unit_id' => $unit->id,
            'nomor_elemen' => '1',
            'nama_elemen' => 'Elemen 1'
        ]);
        $kuk = \App\Models\KriteriaUnjukKerja::create([
            'elemen_id' => $elemen->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'KUK 1'
        ]);

        // Buka Ujian Praktik
        \App\Models\Mapa01::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'status_mapa' => 'selesai'
        ]);
        \App\Models\Mapa02::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'status_mapa' => 'selesai'
        ]);

        // 1. Asesor menyetujui APL.02
        $this->actingAs($asesor);
        $responseAsesor = $this->post(route('asesor.input-penilaian.simpan', $pendaftaran->id), [
            'rekomendasi' => 'dapat_dilanjutkan',
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'catatan_rekomendasi' => 'OK',
            'tanda_tangan_asesor' => 'ttd_asesor_fake_base64_data',
            'nilai' => [$unit->id => 'K'],
            'verifikasi_kuk' => [$kuk->id => 'K']
        ]);
        
        $responseAsesor->assertSessionDoesntHaveErrors();
        $responseAsesor->assertRedirect();
        
        // Memastikan FR.APL.02 Approved
        $this->assertDatabaseHas('pendaftaran_asesi', [
            'id' => $pendaftaran->id,
            'status_apl02' => 'approved'
        ]);

        // Memastikan RekomendasiAsesmen (keputusan FR.AK.02) BELUM terbuat (untuk mencegah deadlock)
        $this->assertDatabaseMissing('rekomendasi_asesmen', [
            'pendaftaran_id' => $pendaftaran->id
        ]);

        // 2. Asesi submit IA.02 (Ujian Praktik)
        $this->actingAs($asesi);
        
        // Tambahkan draft jawaban untuk CBT (IA.05) agar validasi submitUjian tidak gagal
        \App\Models\IaPenilaian::create([
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.05',
            'status' => 'draft',
            'data_jawaban' => [
                'jawaban_pg' => [
                    // Mocking jawaban untuk index 0, 1, 2 dst untuk memastikan tidak ada soal yang kosong. 
                    // Kita asumsikan ada maksimal 5 soal yg terbuat otomatis oleh observer (jika ada).
                    0 => 'A',
                    1 => 'B',
                    2 => 'C',
                    3 => 'D',
                    4 => 'A',
                    5 => 'B'
                ]
            ]
        ]);

        $file = UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf');

        $responseAsesi = $this->post(route('asesi.ujian.submit'), [
            'pendaftaran_id' => $pendaftaran->id,
            'file_praktik' => $file,
            'catatan_praktik' => 'Ini catatan'
        ]);
        
        $responseAsesi->assertStatus(302);
        if ($responseAsesi->baseResponse instanceof \Illuminate\Http\RedirectResponse && session()->has('error')) {
            dd("Redirected with error: " . session('error'));
        }
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.02',
            'status' => 'submitted'
        ]);
    }
}
