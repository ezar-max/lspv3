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
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('public');
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
            'items_checklist' => array_fill_keys(
                array_keys(AssessmentAk07Adjustment::CRITERIA_DEFINITIONS),
                ['perlu' => '0']
            ),
            'gunakan_profil' => '1',
            'tanda_tangan_asesor' => 'data:image/png;base64,' . base64_encode('tanda tangan asesor'),
        ];

        $postResponse = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-07.simpan', $this->skema->id), $payload);
        $postResponse->assertRedirect(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $master = MasterAk07::where('skema_id', $this->skema->id)->firstOrFail();
        $this->assertDatabaseHas('master_ak07', [
            'skema_id' => $this->skema->id,
            'potensi_asesi' => 2,
            'acuan_pembanding_disepakati' => 'SKKNI TI Terbaru',
            'status' => 'selesai',
        ]);
        $this->assertNotEmpty($master->tanda_tangan_asesor);
        $this->assertNotNull($master->tanggal_ttd_asesor);
        $this->assertTrue(Storage::disk('public')->exists(str_replace('storage/', '', $master->tanda_tangan_asesor)));
    }

    public function test_master_ak07_requires_a_selection_for_each_adjustment_category()
    {
        Storage::fake('public');
        $checklist = array_fill_keys(
            array_keys(AssessmentAk07Adjustment::CRITERIA_DEFINITIONS),
            ['perlu' => '0']
        );
        unset($checklist[4]);

        $response = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-07.simpan', $this->skema->id), [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => $checklist,
            'tanda_tangan_asesor' => 'data:image/png;base64,' . base64_encode('tanda tangan asesor'),
        ]);

        $response->assertSessionHasErrors('items_checklist.4.perlu');
        $this->assertDatabaseMissing('master_ak07', ['skema_id' => $this->skema->id]);
    }

    public function test_master_ak07_requires_checkbox_option_when_category_is_marked_perlu_penyesuaian()
    {
        Storage::fake('public');
        $checklist = array_fill_keys(
            array_keys(AssessmentAk07Adjustment::CRITERIA_DEFINITIONS),
            ['perlu' => '0']
        );
        // Kategori 1 dipilih "Perlu Penyesuaian" tapi tanpa memilih opsi checkbox
        $checklist[1] = [
            'perlu' => '1',
            'opsi' => [],
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-07.simpan', $this->skema->id), [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => $checklist,
            'tanda_tangan_asesor' => 'data:image/png;base64,' . base64_encode('tanda tangan asesor'),
        ]);

        $response->assertSessionHasErrors('items_checklist.1.opsi');
        $this->assertDatabaseMissing('master_ak07', ['skema_id' => $this->skema->id]);

        // Sekarang isi dengan opsi checkbox yang dipilih
        $subOptionKey = array_key_first(AssessmentAk07Adjustment::CRITERIA_DEFINITIONS[1]['sub_options']);
        $checklist[1]['opsi'] = [$subOptionKey];

        $successResponse = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-07.simpan', $this->skema->id), [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => $checklist,
            'tanda_tangan_asesor' => 'data:image/png;base64,' . base64_encode('tanda tangan asesor'),
        ]);

        $successResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('master_ak07', ['skema_id' => $this->skema->id]);
    }

    public function test_asesor_cannot_save_master_ak07_without_signature()
    {
        $payload = [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'saat_pra_asesmen',
        ];

        $response = $this->actingAs($this->asesor)
            ->post(route('asesor.skema.ak-07.simpan', $this->skema->id), $payload);

        $response->assertSessionHasErrors('tanda_tangan_asesor');
        $this->assertDatabaseMissing('master_ak07', [
            'skema_id' => $this->skema->id,
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

        $p1->update([
            'status_apl02' => 'approved',
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_ak01.png',
        ]);
        $emptyBeforeMaster = $this->actingAs($this->asesi1)
            ->get(route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $p1->id]));
        $emptyBeforeMaster->assertOk();
        $emptyBeforeMaster->assertSee('FR.AK.07 Belum Diisi Asesor');
        $emptyBeforeMaster->assertDontSee('SKKNI Standar Terbuka');

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

        // Semua asesi menerima formulir dan tanda tangan asesor yang sama.
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
        $viewResponse->assertSee('Disahkan Asesor untuk Seluruh Asesi Skema');
        $viewResponse->assertSee('x-ref="canvas"', false);
        $viewResponse->assertDontSee('Gores TTD Baru');

        $stageResponse = $this->actingAs($this->asesi1)->get(route('asesi.tahapan', [
            'step' => 4,
            'pendaftaran_id' => $p1->id,
        ]));
        $stageResponse->assertOk();
        $stageResponse->assertSee('x-ref="canvas"', false);
        $stageResponse->assertDontSee('Gores TTD Baru');
    }

    public function test_all_fr_ia_forms_can_be_viewed_without_existing_scheme_in_db()
    {
        // Hapus seluruh skema di database untuk menguji kondisi 'skema belum dibuat'
        SkemaSertifikasi::query()->delete();
        $this->assertEquals(0, SkemaSertifikasi::count());

        $routes = [
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
            $resp->assertDontSee('Penyusun (Asesor Penguji)');
            $resp->assertDontSee('Validator (Admin LSP)');
            $resp->assertDontSee('Belum ditandatangani');
        }

        $ia01Response = $this->actingAs($this->asesor)->get(route('formulir.ia01'));
        $ia01Response->assertRedirect();
        $ia01Response->assertDontSee('SKM-STANDAR-BNSP');

        // Test form ujian asesi (05c dan 06c)
        $resp05c = $this->actingAs($this->asesi1)->get(route('formulir.ia05c'));
        $this->assertEquals(200, $resp05c->status(), "Route formulir.ia05c failed to render without scheme.");

        $resp06c = $this->actingAs($this->asesi1)->get(route('formulir.ia06c'));
        $this->assertEquals(200, $resp06c->status(), "Route formulir.ia06c failed to render without scheme.");
    }
}
