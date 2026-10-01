<?php

namespace Tests\Feature;

use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AsesorPenilaianMassalTest extends TestCase
{
    use RefreshDatabase;

    public function test_asesor_dapat_mengoreksi_peserta_yang_ditugaskan()
    {
        // 1. Setup Data
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
        
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'disetujui'
        ]);

        // 2. Simulasi Request
        $this->actingAs($asesor);
        
        $response = $this->post(route('asesor.koreksi-teori.simpan'), [
            'jadwal_id' => null,
            'penilaian_esai' => [
                $pendaftaran->id => ['K', 'K']
            ],
            'catatan_esai' => [
                $pendaftaran->id => 'Test Catatan'
            ]
        ]);

        // 3. Assertions
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.06B',
            'rekomendasi' => 'K'
        ]);
    }

    public function test_asesor_tidak_dapat_mengoreksi_peserta_yang_bukan_tugasnya()
    {
        // 1. Setup Data
        $asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor 1',
            'email' => 'asesor2@example.com',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'asesor',
            'aktif' => true
        ]);
        $asesorLain = Pengguna::create([
            'nama_lengkap' => 'Asesor Lain',
            'email' => 'asesorlain@example.com',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'asesor',
            'aktif' => true
        ]);
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi 2',
            'email' => 'asesi2@example.com',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'asesi',
            'aktif' => true
        ]);
        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-002',
            'nama_skema' => 'Skema Test 2',
            'kategori' => 'KKNI',
            'sektor' => 'IT',
            'jenis' => 'Klaster',
            'aktif' => true
        ]);
        
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-002',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesorLain->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'disetujui'
        ]);

        // 2. Simulasi Request
        $this->actingAs($asesor); // Login sebagai asesor (bukan asesorLain)
        
        $response = $this->post(route('asesor.koreksi-teori.simpan'), [
            'jadwal_id' => null,
            'penilaian_esai' => [
                $pendaftaran->id => ['K', 'K']
            ],
            'catatan_esai' => [
                $pendaftaran->id => 'Test Catatan Hacked'
            ]
        ]);

        // 3. Assertions
        $response->assertSessionHas('sukses'); // Endpoint memproses tanpa error 403 untuk mencegah kebocoran data
        $this->assertDatabaseMissing('ia_penilaian', [
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.06B',
        ]);
    }
}
