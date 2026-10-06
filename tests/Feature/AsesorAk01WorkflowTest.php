<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\JadwalAsesmen;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Notifications\AK01Approved;
use App\Notifications\AK01ReadyForSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsesorAk01WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesorA;
    protected Pengguna $asesorB;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit1;
    protected ElemenKompetensi $elemen1;
    protected KriteriaUnjukKerja $kuk1;
    protected JadwalAsesmen $jadwal;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);
        Storage::fake('public');

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'admin',
        ]);

        $this->asesorA = Pengguna::create([
            'nama_lengkap' => 'Asesor A Kompeten',
            'email' => 'asesorA@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001',
            'tanda_tangan' => 'signatures/profil_asesor_a.png'
        ]);

        $this->asesorB = Pengguna::create([
            'nama_lengkap' => 'Asesor B Lain',
            'email' => 'asesorB@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.002',
            'tanda_tangan' => null
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Test',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Teknisi Rekayasa Perangkat Lunak',
            'jenis_skema' => 'KKNI',
            'deskripsi' => 'Skema pengujian kompetensi software engineering',
            'aktif' => true,
        ]);

        $this->unit1 = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menulis Kode Program Sesuai Panduan',
        ]);

        $this->elemen1 = ElemenKompetensi::create([
            'unit_id' => $this->unit1->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menyiapkan lingkungan pengembangan',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen1->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Tools pemrograman disiapkan sesuai kebutuhan proyek',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'kode_jadwal' => 'JDW-2026-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '00:00',
            'waktu_selesai' => '23:59',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK Lab Komputer RPL',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-2026-001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesorA->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'terverifikasi',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)'],
        ]);
    }

    /** 1. Asesi submits AK-01 approval without a signature */
    public function test_asesi_submits_ak01_approval_without_signature(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->asesi)->post(route('asesi.ak01.simpan', $this->pendaftaran->id), [
            'tuk_type' => 'Sewaktu',
            'persetujuan_asesmen' => '1',
            'persetujuan_kerahasiaan' => '1',
        ]);

        $response->assertRedirect();
        $this->pendaftaran->refresh();

        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesi_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesi_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesor_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesor_ak01);

        Notification::assertSentTo($this->asesi, AK01Approved::class);
    }

    /** 2. Asesor can save AK-01 planning data without a signature */
    public function test_asesor_can_fill_ak01_without_signature(): void
    {
        Notification::fake();

        $this->assertEquals('belum', $this->pendaftaran->status_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesi_ak01);

        $response = $this->actingAs($this->asesorA)->post(route('asesor.ak01.simpan', $this->pendaftaran->id), [
            'tuk_type' => 'Tempat Kerja',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Tanya Jawab Lisan'],
            'bukti_dikumpulkan_lainnya' => 'Portofolio Proyek Industri',
        ]);

        $response->assertRedirect(route('asesor.dashboard'));
        $this->pendaftaran->refresh();

        $this->assertEquals('disetujui_asesor', $this->pendaftaran->status_ak01);
        $this->assertEquals('Tempat Kerja', $this->pendaftaran->tuk_type);
        $this->assertContains('Observasi Praktik Demonstrasi', $this->pendaftaran->bukti_dikumpulkan);
        $this->assertEquals('Portofolio Proyek Industri', $this->pendaftaran->bukti_dikumpulkan_lainnya);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesor_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesor_ak01);

        // Asesi submits agreement established by Asesor.
        $asesiResponse = $this->actingAs($this->asesi)->post(route('asesi.ak01.simpan', $this->pendaftaran->id), [
            'persetujuan_asesmen' => '1',
        ]);

        $asesiResponse->assertRedirect();
        $this->pendaftaran->refresh();

        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesi_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesi_ak01);
        // Preserved Asesor's settings
        $this->assertEquals('Tempat Kerja', $this->pendaftaran->tuk_type);
    }

    public function test_asesor_cannot_save_ak01_without_an_evidence_method(): void
    {
        $response = $this->actingAs($this->asesorA)->post(route('asesor.ak01.simpan', $this->pendaftaran->id), [
            'tuk_type' => 'Tempat Kerja',
        ]);

        $response->assertSessionHasErrors('bukti_dikumpulkan');
        $this->assertEquals('belum', $this->pendaftaran->fresh()->status_ak01);
    }

    /** 3. Asesor wajib membuat tanda tangan sebelum menyetujui FR.APL.02 */
    public function test_asesor_must_sign_before_approving_apl02(): void
    {
        $this->pendaftaran->update([
            'status_apl02' => 'submitted',
            'tanda_tangan_asesor' => null,
            'tanggal_ttd_asesor' => null,
        ]);

        $response = $this->actingAs($this->asesorA)->post(route('asesor.input-penilaian.simpan', $this->pendaftaran->id), [
            'action_type' => 'approve',
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'nilai' => [$this->unit1->id => 'K'],
            'verifikasi_kuk' => [$this->kuk1->id => 'K'],
            'catatan_rekomendasi' => '',
            'catatan_unit' => [$this->unit1->id => 'Unit sesuai'],
        ]);

        $response->assertSessionHas('error', 'Gagal memverifikasi: Tanda Tangan Asesor Penguji wajib dibubuhkan sebelum menyimpan keputusan FR.APL.02.');
        $this->pendaftaran->refresh();
        $this->assertNotEquals('approved', $this->pendaftaran->status_apl02);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesor);
    }

    /** 4. Unauthorized asesor cannot approve AK-01 */
    public function test_unauthorized_asesor_cannot_approve_ak01(): void
    {
        $this->pendaftaran->update([
            'status_ak01' => 'disetujui_asesi',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanggal_ttd_asesi_ak01' => now()
        ]);

        $response = $this->actingAs($this->asesorB)->post(route('asesor.ak01.simpan', $this->pendaftaran->id), [
            'tanda_tangan_asesor_ak01' => 'dummy'
        ]);

        $response->assertStatus(403);
    }

    /** 4. Asesor confirms AK-01 without a signature and notifies Asesi */
    public function test_asesor_approves_ak01_without_signature(): void
    {
        Notification::fake();

        $this->pendaftaran->update([
            'status_ak01' => 'disetujui_asesi',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanggal_ttd_asesi_ak01' => now()
        ]);

        $response = $this->actingAs($this->asesorA)->post(route('asesor.ak01.simpan', $this->pendaftaran->id), [
            'tanda_tangan_asesor_ak01' => 'signatures/profil_asesor_a.png',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi'],
        ]);

        $response->assertRedirect(route('asesor.dashboard'));
        $this->pendaftaran->refresh();

        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesor_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesor_ak01);

        Notification::assertSentTo($this->asesi, AK01Approved::class);
    }

    /** 5. Asesor ignores legacy canvas payloads */
    public function test_asesor_ignores_legacy_canvas_signature_payload(): void
    {
        Notification::fake();

        $this->pendaftaran->update([
            'status_ak01' => 'disetujui_asesi',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanggal_ttd_asesi_ak01' => now()
        ]);

        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->asesorA)->post(route('asesor.ak01.simpan', $this->pendaftaran->id), [
            'tanda_tangan_asesor_ak01' => $dummySignature,
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi'],
        ]);

        $response->assertRedirect(route('asesor.dashboard'));
        $this->pendaftaran->refresh();

        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertNull($this->pendaftaran->tanda_tangan_asesor_ak01);
        $this->assertNull($this->pendaftaran->tanggal_ttd_asesor_ak01);

        Notification::assertSentTo($this->asesi, AK01Approved::class);
    }

    /** 6. Ruang Uji is blocked when status_ak01 is disetujui_asesi (waiting for asesor) */
    public function test_ruang_uji_blocked_when_ak01_waiting_for_asesor(): void
    {
        $this->pendaftaran->update([
            'status_ak01' => 'disetujui_asesi',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanggal_ttd_asesi_ak01' => now(),
            'tanda_tangan_asesor_ak01' => null
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaran->id]));
        $response->assertRedirect(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id, 'step' => 4]));
        $response->assertSessionHas('warning');

        // Autosave blocked
        $autoSaveResp = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $autoSaveResp->assertStatus(403);
    }

    /** 7. Ruang Uji is unlocked when status_ak01 is selesai and MAPA is confirmed */
    public function test_ruang_uji_unlocked_when_ak01_selesai_and_mapa_confirmed(): void
    {
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 1]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->pendaftaran->update([
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanda_tangan_asesor_ak01' => 'signatures/profil_asesor_a.png',
            'tanggal_ttd_asesi_ak01' => now(),
            'tanggal_ttd_asesor_ak01' => now()
        ]);

        \App\Models\AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'asesor_signature' => 'sig_asesor',
            'asesi_signature' => 'sig_asesi',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaran->id]));
        $response->assertOk();

        // Autosave succeeds
        $autoSaveResp = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $autoSaveResp->assertOk();
        $autoSaveResp->assertJson(['status' => 'success']);
    }

    /** 8. AK-01 Unit Badges strictly reflect MAPA.02 checkboxes per unit */
    public function test_ak01_unit_badges_reflect_mapa02_checkboxes_dynamically(): void
    {
        $unit2 = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.002.01',
            'judul_unit' => 'Mengimplementasikan Algoritma Pemrograman',
        ]);
        $elemen2 = ElemenKompetensi::create([
            'unit_id' => $unit2->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menulis algoritma',
        ]);
        $kuk2 = KriteriaUnjukKerja::create([
            'elemen_id' => $elemen2->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Struktur data didefinisikan',
        ]);

        // Unit 1: Only Praktik (CLO) & Uji Tertulis (DPE)
        // Unit 2: Only Tugas Praktik (DPT) & Tanya Jawab Observasi (PMO)
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpe' => 1, 'dpt' => 0, 'pmo' => 0, 'dpl' => 0, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                    ]
                ],
                $unit2->id => [
                    $elemen2->id => [
                        $kuk2->id => ['clo' => 0, 'dpe' => 0, 'dpt' => 1, 'pmo' => 1, 'dpl' => 0, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $inst1 = $this->pendaftaran->getInstrumenPerUnit($this->unit1->id);
        $this->assertTrue($inst1['clo']);
        $this->assertTrue($inst1['dpe']);
        $this->assertFalse($inst1['dpt']);
        $this->assertFalse($inst1['pmo']);
        $this->assertFalse($inst1['vp']);
        $this->assertFalse($inst1['pw']);
        $this->assertFalse($inst1['crp']);

        $inst2 = $this->pendaftaran->getInstrumenPerUnit($unit2->id);
        $this->assertTrue($inst2['dpt']);
        $this->assertTrue($inst2['pmo']);
        $this->assertFalse($inst2['clo']);
        $this->assertFalse($inst2['dpe']);
        $this->assertFalse($inst2['vp']);
        $this->assertFalse($inst2['pw']);
        $this->assertFalse($inst2['crp']);

        // View as Asesi
        $asesiResp = $this->actingAs($this->asesi)->get(route('asesi.ak01', ['id' => $this->pendaftaran->id]));
        $asesiResp->assertOk();
        $asesiResp->assertSee('Observasi (IA.01)');
        $asesiResp->assertSee('Uji Tertulis (IA.05/06)');
        $asesiResp->assertSee('Tugas Praktik (IA.02)');
        $asesiResp->assertSee('Tanya Jawab (IA.03)');
        $asesiResp->assertDontSee('Portofolio (IA.08)');
        $asesiResp->assertDontSee('Wawancara (IA.09)');
        $asesiResp->assertDontSee('Reviu Produk (IA.11)');

        // View as Asesor
        $asesorResp = $this->actingAs($this->asesorA)->get(route('asesor.ak01.detail', ['pendaftaranId' => $this->pendaftaran->id]));
        $asesorResp->assertOk();
        $asesorResp->assertSee('Observasi (IA.01)');
        $asesorResp->assertSee('Uji Tertulis (IA.05/06)');
        $asesorResp->assertSee('Tugas Praktik (IA.02)');
        $asesorResp->assertSee('Tanya Jawab (IA.03)');
        $asesorResp->assertDontSee('Portofolio (IA.08)');
        $asesorResp->assertDontSee('Wawancara (IA.09)');
        $asesorResp->assertDontSee('Reviu Produk (IA.11)');
    }

    /** 9. Tahapan Asesmen view does not contain showModalSukses or completed modal popup */
    public function test_tahapan_asesmen_view_does_not_contain_success_popup(): void
    {
        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id, 'completed' => 1]));
        $response->assertOk();
        $response->assertDontSee('showModalSukses');
        $response->assertDontSee('Tahapan Asesmen Selesai!');
    }
}
