<?php

namespace Tests\Feature;

use App\Models\JadwalAsesmen;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\Skema;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicInstrumentNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesi;
    protected Pengguna $asesor;
    protected SkemaSertifikasi $skema;
    protected JadwalAsesmen $jadwal;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-001',
            'nama_skema' => 'Skema Dynamic Test Kejuruan',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'U.001',
            'judul_unit' => 'Unit Kompetensi Dasar Uji',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $elemen = $unit->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Elemen Pertama',
            'pertanyaan_elemen' => 'Apakah kompeten pada elemen ini?',
        ]);

        $elemen->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'KUK Pertama Teruji',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Dinamis',
            'email' => 'asesi.dinamis@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'nomor_registrasi' => 'REG-ASESI-001',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji Dinamis',
            'email' => 'asesor.dinamis@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001',
            'tanda_tangan' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'kode_jadwal' => 'JDW-DYN-01',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'nama_tuk' => 'TUK Dynamic Center',
            'status_jadwal' => 'berlangsung',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-DYN-001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'jadwal_id' => $this->jadwal->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'ttd_asesi_ak01.png',
            'tanda_tangan_asesor_ak01' => 'ttd_asesor_ak01.png',
        ]);

        // Sahkan MAPA.01 & MAPA.02
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_mapa' => 'selesai',
        ]);

        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_mapa' => 'selesai',
            'matriks_pemetaan' => [
                $unit->id => [
                    $elemen->id => [
                        ['clo' => true, 'dpe' => true, 'dpt' => true]
                    ]
                ]
            ],
        ]);

        \App\Models\AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'status' => 'confirmed',
            'asesi_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'asesi_signed_at' => now(),
        ]);
    }

    public function test_skema_has_instrumen_and_aliases()
    {
        // Default vocational fallback
        $this->assertTrue($this->skema->hasInstrumen('FR.IA.01'));
        $this->assertTrue($this->skema->hasInstrumen('ia01'));
        $this->assertTrue($this->skema->hasInstrumen('FR.IA.02'));
        $this->assertTrue($this->skema->hasInstrumen('FR.IA.05'));
        $this->assertTrue($this->skema->hasInstrumen('FR.IA.06'));

        // Check alias model Skema
        $skemaAlias = Skema::find($this->skema->id);
        $this->assertNotNull($skemaAlias);
        $this->assertTrue($skemaAlias->hasInstrumen('FR.IA.01'));

        // Register custom instrument
        SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia04a',
            'title' => 'Penugasan Proyek',
            'is_active' => true,
        ]);

        $this->skema->refresh();
        $this->assertTrue($this->skema->hasInstrumen('FR.IA.04A'));
        $this->assertTrue($this->skema->hasInstrumen('ia04a'));
    }

    public function test_asesi_ruang_uji_displays_dynamic_tabs()
    {
        $response = $this->actingAs($this->asesi)
            ->get(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaran->id]));

        $response->assertStatus(200);
        $response->assertSee('FR.IA.05 (Ujian Teori CBT PG)');
        $response->assertSee('FR.IA.06 (Ujian Tertulis Esai)');
        $response->assertSee('FR.IA.02 (Tugas Praktik Demonstrasi)');
    }

    public function test_asesor_penilaian_live_displays_dynamic_tabs_and_final_rekap()
    {
        $response = $this->actingAs($this->asesor)
            ->get(route('asesor.penilaian-live', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.IA.01 (Ceklis Observasi)');
        $response->assertSee('FR.AK.02');
        $response->assertSee('Gunakan TTD Profil');
    }

    public function test_asesor_can_save_penilaian_live_with_profile_signature()
    {
        $response = $this->actingAs($this->asesor)
            ->post(route('asesor.penilaian-live.simpan', $this->pendaftaran->id), [
                'tab_action' => 'rekap_final',
                'keputusan' => 'kompeten',
                'catatan_rekomendasi' => 'Asesi sangat kompeten dalam praktik dan teori.',
                'mode_ttd' => 'profil',
                'penilaian_kuk' => [1 => 'K'],
                'catatan_observasi' => 'Observasi langsung memuaskan.',
            ]);

        $response->assertRedirect(route('asesor.penilaian-live', ['pendaftaranId' => $this->pendaftaran->id, 'tab' => 'rekap']));

        $this->assertDatabaseHas('rekomendasi_asesmen', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'keputusan' => 'kompeten',
        ]);

        $this->pendaftaran->refresh();
        $this->assertEquals('selesai', $this->pendaftaran->status_pendaftaran);
        $this->assertEquals('dapat_dilanjutkan', $this->pendaftaran->rekomendasi_asesor_status);
    }

    public function test_asesi_ruang_uji_route_redirects_to_tahapan_step_5()
    {
        $response = $this->actingAs($this->asesi)
            ->get(route('asesi.ruang-uji', ['pendaftaran_id' => $this->pendaftaran->id]));

        $response->assertRedirect(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaran->id]));
    }
}
