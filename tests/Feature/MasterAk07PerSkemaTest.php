<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\MasterAk07;
use App\Models\AssessmentAk07Adjustment;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAk07PerSkemaTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private Pengguna $asesi1;
    private Pengguna $asesi2;
    private SkemaSertifikasi $skema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TI-001',
            'nama_skema' => 'Teknologi Informasi',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Uji AK07',
            'email' => 'asesor.ak07@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'tanda_tangan' => 'signatures/asesor_profil.png',
        ]);

        $this->asesi1 = Pengguna::create([
            'nama_lengkap' => 'Asesi Satu',
            'email' => 'asesi1@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->asesi2 = Pengguna::create([
            'nama_lengkap' => 'Asesi Dua',
            'email' => 'asesi2@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);
    }

    public function test_asesor_sees_master_ak07_card_in_mapa_index()
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));
        $response->assertStatus(200);
        $response->assertSee('FR.AK.07');
        $response->assertSee('Penyesuaian Yang Wajar (Master Skema)');
        $response->assertSee('Tambah Form +');

        // Jika master sudah ada, tombol berubah menjadi Kelola Master Form
        MasterAk07::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'potensi_asesi' => 1,
            'status' => 'selesai',
        ]);
        $response2 = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));
        $response2->assertSee('Kelola Master Form');
    }

    public function test_asesor_can_open_and_save_master_ak07()
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.ak-07', $this->skema->id));
        $response->assertStatus(200);
        $response->assertSee('Master FR.AK.07');

        $payload = [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'acuan_pembanding_disepakati' => 'SKKNI TI Terbaru',
            'metode_disepakati' => 'Observasi Praktik & Portofolio',
            'instrumen_disepakati' => 'FR.IA.01, FR.IA.02',
            'catatan_asesor' => 'Disediakan tambahan waktu 15 menit',
            'items_checklist' => [
                1 => [
                    'perlu' => '1',
                    'opsi' => ['penyesuaian_instruksi_tertulis'],
                    'keterangan' => 'Menggunakan panduan bahasa yang ringkas',
                ],
            ],
            'gunakan_profil' => '1',
        ];

        $postResponse = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-07.simpan', $this->skema->id), $payload);
        $postResponse->assertRedirect(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $this->assertDatabaseHas('master_ak07', [
            'skema_id' => $this->skema->id,
            'potensi_asesi' => 2,
            'acuan_pembanding_disepakati' => 'SKKNI TI Terbaru',
            'status' => 'selesai',
        ]);
    }

    public function test_master_ak07_syncs_automatically_to_all_registered_asesi()
    {
        // Daftarkan 2 asesi pada skema ini
        $p1 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001',
            'asesi_id' => $this->asesi1->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
            'status_pendaftaran' => 'disetujui',
        ]);

        $p2 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-002',
            'asesi_id' => $this->asesi2->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
            'status_pendaftaran' => 'disetujui',
        ]);

        // Simpan master AK-07
        $master = MasterAk07::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'potensi_asesi' => 3,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => [1 => ['perlu_penyesuaian' => true]],
            'acuan_pembanding_disepakati' => 'SKKNI Standar Terbuka',
            'metode_disepakati' => 'Observasi & Tanya Jawab',
            'instrumen_disepakati' => 'FR.IA.01, FR.IA.03',
            'catatan_asesor' => 'Disinkronkan ke seluruh peserta',
            'tanda_tangan_asesor' => 'signatures/asesor_profil.png',
            'tanggal_ttd_asesor' => now(),
            'status' => 'selesai',
        ]);

        $syncedCount = $master->sinkronkanKePeserta();
        $this->assertEquals(2, $syncedCount);

        // Verifikasi asesi1 dan asesi2 memiliki ak07 dengan data tersinkron
        $this->assertDatabaseHas('assessment_ak07_adjustments', [
            'assessment_registration_id' => $p1->id,
            'potensi_asesi' => 3,
            'acuan_pembanding_disepakati' => 'SKKNI Standar Terbuka',
            'asesor_signature' => 'signatures/asesor_profil.png',
        ]);

        $this->assertDatabaseHas('assessment_ak07_adjustments', [
            'assessment_registration_id' => $p2->id,
            'potensi_asesi' => 3,
            'acuan_pembanding_disepakati' => 'SKKNI Standar Terbuka',
            'asesor_signature' => 'signatures/asesor_profil.png',
        ]);

        // Verifikasi asesi bisa melihat formulir FR.AK.07 yang telah disiapkan asesor
        $viewResponse = $this->actingAs($this->asesi1)->get(route('asesi.ak07', $p1->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('SKKNI Standar Terbuka');
    }

    public function test_all_fr_ia_forms_can_be_viewed_without_existing_scheme_in_db()
    {
        // Hapus seluruh skema di database untuk menguji kondisi 'skema belum dibuat'
        SkemaSertifikasi::query()->delete();
        $this->assertEquals(0, SkemaSertifikasi::count());

        $routes = [
            'formulir.ia01',
            'formulir.ia02',
            'formulir.ia03',
            'formulir.ia04a',
            'formulir.ia04b',
            'formulir.ia05a',
            'formulir.ia05b',
            'formulir.ia06a',
            'formulir.ia06b',
            'formulir.ia07',
            'formulir.ia08',
            'formulir.ia09',
            'formulir.ia10',
            'formulir.ia11',
        ];

        foreach ($routes as $route) {
            $resp = $this->actingAs($this->asesor)->get(route($route));
            $this->assertEquals(200, $resp->status(), "Route {$route} failed to render without scheme.");
        }

        // Test form ujian asesi (05c dan 06c)
        $resp05c = $this->actingAs($this->asesi1)->get(route('formulir.ia05c'));
        $this->assertEquals(200, $resp05c->status(), "Route formulir.ia05c failed to render without scheme.");

        $resp06c = $this->actingAs($this->asesi1)->get(route('formulir.ia06c'));
        $this->assertEquals(200, $resp06c->status(), "Route formulir.ia06c failed to render without scheme.");
    }
}
