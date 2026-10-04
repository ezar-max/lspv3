<?php

namespace Tests\Feature;

use App\Models\AssessmentAk07Adjustment;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssessmentAk07WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesorA;
    protected Pengguna $asesorB;
    protected Pengguna $asesi;
    protected Pengguna $asesiLain;
    protected SkemaSertifikasi $skema;
    protected JadwalAsesmen $jadwal;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);
        Storage::fake('public');

        $this->asesorA = Pengguna::create([
            'nama_lengkap' => 'Asesor A Penguji',
            'email' => 'asesorA@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001',
            'tanda_tangan' => 'signatures/profil_asesor_a.png',
        ]);

        $this->asesorB = Pengguna::create([
            'nama_lengkap' => 'Asesor B Lain',
            'email' => 'asesorB@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.002',
            'tanda_tangan' => null,
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Kompetensi',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
            'tanda_tangan' => 'signatures/profil_asesi.png',
        ]);

        $this->asesiLain = Pengguna::create([
            'nama_lengkap' => 'Asesi Orang Lain',
            'email' => 'asesilain@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-01',
            'nama_skema' => 'Pemrograman Perangkat Lunak',
            'jenis_skema' => 'KKNI',
            'deskripsi' => 'Skema kejuruan RPL',
            'aktif' => true,
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'kode_jadwal' => 'JDW-2026-AK07',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'Lab Komputer RPL 1',
            'kuota' => 20,
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-2026-AK07-001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'jadwal_id' => $this->jadwal->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diterima',
            'rekomendasi_admin_status' => 'diterima',
            'status_apl02' => 'approved',
            'tanda_tangan_asesi_ak01' => 'sig_ak01.png',
            'tanda_tangan_asesor_ak01' => 'sig_asesor_ak01.png',
            'status_ak01' => 'selesai',
            'status_mapa01' => 'selesai',
            'status_mapa02' => 'selesai',
        ]);

        \App\Models\Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);

        \App\Models\Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
            'matriks_peta' => [1 => [1 => [1 => ['dpe' => 1]]]],
        ]);

        $instCbt = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia_05',
            'title' => 'Pertanyaan Tertulis Pilihan Ganda',
            'is_active' => true,
        ]);

        \App\Models\MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instCbt->id,
            'skema_id' => $this->skema->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Pertanyaan uji coba AK07?',
            'options' => ['A' => 'Opsi 1', 'B' => 'Opsi 2'],
            'correct_answer' => 'A',
            'order' => 1,
        ]);
    }

    /**
     * 1. Asesor yang ditugaskan dapat membuka halaman edit FR.AK.07
     */
    public function test_assigned_asesor_can_view_ak07_edit_page(): void
    {
        $response = $this->actingAs($this->asesorA)
            ->get(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

        $response->assertRedirect(route('asesor.skema.ak-07', $this->skema->id));
    }

    /**
     * 2. Asesor lain yang tidak ditugaskan ditolak saat mengakses FR.AK.07 (IDOR protection)
     */
    public function test_unauthorized_asesor_cannot_access_ak07(): void
    {
        $response = $this->actingAs($this->asesorB)
            ->get(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

        $response->assertStatus(403);
    }

    /**
     * 3. Asesor dapat menyimpan formulir FR.AK.07 sebagai Draft
     */
    public function test_legacy_asesor_save_route_redirects_to_shared_scheme_form(): void
    {
        $payload = [
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'pra_asesmen',
            'items_checklist' => [
                1 => [
                    'perlu' => '1',
                    'opsi' => ['penyesuaian_instruksi_tertulis', 'tambahan_waktu_literasi'],
                    'keterangan' => 'Butuh teks instruksi diperbesar',
                ],
                2 => [
                    'perlu' => '0',
                    'opsi' => [],
                    'keterangan' => '',
                ]
            ],
            'acuan_pembanding_disepakati' => 'SKKNI RPL 2026',
            'metode_disepakati' => 'Observasi Praktik Demonstrasi',
            'instrumen_disepakati' => 'FR.IA.01 & FR.IA.03',
            'catatan_asesor' => 'Diberikan waktu ekstra 15 menit',
            'aksi' => 'draft',
        ];

        $response = $this->actingAs($this->asesorA)
            ->post(route('asesor.pendaftaran.ak07.update', $this->pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.skema.ak-07', $this->skema->id));
        $response->assertSessionHas('info');
        $this->assertDatabaseMissing('assessment_ak07_adjustments', [
            'assessment_registration_id' => $this->pendaftaran->id,
        ]);
    }

    /**
     * 4. Asesor dapat mengonfirmasi FR.AK.07 dengan tanda tangan profil
     */
    public function test_asesor_cannot_save_shared_ak07_through_legacy_endpoint(): void
    {
        $payload = [
            'potensi_asesi' => 3,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => [],
            'acuan_pembanding_disepakati' => 'SKKNI RPL',
            'metode_disepakati' => 'Observasi',
            'instrumen_disepakati' => 'FR.IA.01',
            'aksi' => 'confirm',
        ];

        $response = $this->actingAs($this->asesorA)
            ->post(route('asesor.pendaftaran.ak07.update', $this->pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.skema.ak-07', $this->skema->id));

        $this->assertDatabaseMissing('assessment_ak07_adjustments', [
            'assessment_registration_id' => $this->pendaftaran->id,
        ]);
    }

    /**
     * 5. Endpoint tanda tangan asesor tidak lagi tersedia
     */
    public function test_asesor_signature_endpoint_is_removed(): void
    {
        $this->assertNull(app('router')->getRoutes()->getByName('asesor.pendaftaran.ak07.sign-asesor'));
    }

    /**
     * 6. Asesi dapat melihat detail FR.AK.07 miliknya
     */
    public function test_asesi_with_legacy_ak07_data_is_redirected_to_shared_form_stage(): void
    {
        AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->asesi)
            ->get(route('asesi.ak07', $this->pendaftaran->id));

        $response->assertRedirect(route('asesi.tahapan', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'step' => 4,
        ]));
    }

    /**
     * 7. Asesi lain tidak dapat melihat berkas FR.AK.07 yang bukan miliknya (IDOR protection)
     */
    public function test_unauthorized_asesi_cannot_view_other_asesi_ak07(): void
    {
        AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->asesiLain)
            ->get(route('asesi.ak07', $this->pendaftaran->id));

        $response->assertStatus(403);
    }

    /**
     * 8. Asesi dapat menandatangani FR.AK.07 dan mengonfirmasi kesepakatan
     */
    public function test_asesi_cannot_confirm_ak07_before_shared_master_is_signed(): void
    {
        $ak07 = AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->asesi)
            ->post(route('asesi.ak07.confirm', $this->pendaftaran->id));

        $response->assertRedirect(route('asesi.tahapan', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'step' => 4,
        ]));
        $response->assertSessionHas('error');
        $ak07->refresh();

        $this->assertNull($ak07->asesi_signature);
        $this->assertNull($ak07->asesor_signature);
        $this->assertNull($ak07->asesi_signed_at);
        $this->assertEquals('draft', $ak07->status);
    }

    /**
     * 9. Re-confirmation Rule: Perubahan data pada dokumen CONFIRMED otomatis mereset status ke DRAFT
     */
    public function test_legacy_save_route_does_not_modify_individual_ak07_record(): void
    {
        $ak07 = AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'asesor_signature' => 'sig_asesor.png',
            'asesor_signed_at' => now(),
            'asesi_signature' => 'sig_asesi.png',
            'asesi_signed_at' => now(),
            'status' => 'confirmed',
        ]);

        $payload = [
            'potensi_asesi' => 4,
            'fase_penggunaan' => 'setelah_pra_asesmen',
            'items_checklist' => [],
            'acuan_pembanding_disepakati' => 'SKKNI Revisi',
            'metode_disepakati' => 'Wawancara Tambahan',
            'instrumen_disepakati' => 'FR.IA.07',
            'aksi' => 'draft',
        ];

        $response = $this->actingAs($this->asesorA)
            ->post(route('asesor.pendaftaran.ak07.update', $this->pendaftaran->id), $payload);

        $response->assertRedirect();
        $ak07->refresh();

        $this->assertEquals('confirmed', $ak07->status);
        $this->assertEquals('sig_asesor.png', $ak07->asesor_signature);
        $this->assertEquals('sig_asesi.png', $ak07->asesi_signature);
    }

    /**
     * 10. Lembar cetak A4 FR.AK.07 dapat diakses oleh Asesor yang ditugaskan
     */
    public function test_asesor_can_view_ak07_print_template(): void
    {
        AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'status' => 'confirmed',
            'asesor_signature' => 'sig_asesor.png',
            'asesi_signature' => 'sig_asesi.png',
        ]);

        $response = $this->actingAs($this->asesorA)
            ->get(route('asesor.pendaftaran.ak07.cetak', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.AK.07');
        $response->assertSee('CEKLIS PENYESUAIAN YANG WAJAR DAN BERALASAN');
    }

    /**
     * 11. Halaman Asesor (Daftar Peserta, Live Penilaian, Hub MAPA) menampilkan tombol dan badge FR.AK.07
     */
    public function test_asesor_views_render_ak07_links_and_buttons(): void
    {
        // 1. Daftar Peserta
        $respPeserta = $this->actingAs($this->asesorA)
            ->get(route('asesor.daftar-peserta', ['jadwal_id' => $this->jadwal->id]));
        $respPeserta->assertStatus(200);
        $respPeserta->assertSee('FR.AK.07');
        $respPeserta->assertSee(route('asesor.skema.ak-07', $this->skema->id));

        // 2. Penilaian Live
        $respLive = $this->actingAs($this->asesorA)
            ->get(route('asesor.penilaian-live', ['pendaftaranId' => $this->pendaftaran->id]));
        $respLive->assertStatus(200);
        $respLive->assertSee('FR.AK.07');
        $respLive->assertSee(route('asesor.skema.ak-07', $this->skema->id));

        // 3. Hub MAPA
        $respMapa = $this->actingAs($this->asesorA)
            ->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));
        $respMapa->assertStatus(200);
        $respMapa->assertSee('FR.AK.07');
        $respMapa->assertSee('Penyesuaian Yang Wajar', false);
    }

    /**
     * 12. Setiap Asesi dapat melihat tautan dan tombol FR.AK.07 di dashboard dan membukanya langsung
     */
    public function test_asesi_views_render_ak07_links_and_buttons(): void
    {
        // 1. Dashboard Asesi
        $respDash = $this->actingAs($this->asesi)
            ->get(route('asesi.dashboard'));
        $respDash->assertStatus(200);
        $respDash->assertSee('FR.AK.07');
        $respDash->assertSee(route('asesi.ak07', ['pendaftaranId' => $this->pendaftaran->id]));

        // 2. Tanpa Master FR.AK.07 bertanda tangan, asesi dikembalikan ke tahap formulir yang kosong.
        $respDetail = $this->actingAs($this->asesi)
            ->get(route('asesi.ak07', ['pendaftaranId' => $this->pendaftaran->id]));
        $respDetail->assertRedirect(route('asesi.tahapan', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'step' => 4,
        ]));
    }

    /**
     * 13. Tombol navbar langsung ke FR.AK.07 telah dihapus dari tata-letak navbar
     */
    public function test_navbar_does_not_render_ak07_direct_link(): void
    {
        $respTahapan = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));
        $respTahapan->assertStatus(200);
        $respTahapan->assertDontSee('Penyesuaian Asesmen (FR.AK.07)');

        $respJadwal = $this->actingAs($this->asesi)->get(route('asesi.jadwal'));
        $respJadwal->assertStatus(200);
        $respJadwal->assertDontSee('Penyesuaian Asesmen (FR.AK.07)');
    }

    /**
     * 14. Setelah mengisi FR.AK.01 di tahapan formulir, asesi langsung otomatis diarahkan ke FR.AK.07 di Step 4
     */
    public function test_ak01_completion_in_tahapan_redirects_to_ak07(): void
    {
        $this->pendaftaran->update([
            'tanda_tangan_asesor_ak01' => 'signatures/asesor_signed.png',
            'status_ak01' => 'disetujui_asesor',
        ]);
        $signature = 'data:image/png;base64,' . base64_encode('asesi signature');

        // Pengiriman form AK.01 dari tahapan formulir
        $resp = $this->actingAs($this->asesi)->post(route('asesi.tahapan.ak01'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => $signature,
        ]);

        $resp->assertRedirect(route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $this->pendaftaran->id]));
        $resp->assertSessionHas('sukses');

        // Pengujian via AJAX
        $respAjax = $this->actingAs($this->asesi)->postJson(route('asesi.tahapan.ak01'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => $signature,
        ]);

        $respAjax->assertStatus(200);
        $respAjax->assertJson([
            'success' => true,
            'redirect_url' => route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $this->pendaftaran->id]),
        ]);

        // Halaman tahapan otomatis beralih dan memuat Formulir FR.AK.07
        $this->pendaftaran->refresh();
        $respTahapan = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $this->pendaftaran->id]));
        $respTahapan->assertStatus(200);
        $respTahapan->assertSee('FR.AK.07');
        $respTahapan->assertSee('FR.AK.07 Belum Diisi Asesor');
    }

    /**
     * 15. Tombol navbar langsung ke Ruang Ujian Online telah dihapus dari tata-letak navbar
     */
    public function test_navbar_does_not_render_ruang_ujian_direct_link(): void
    {
        $respTahapan = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));
        $respTahapan->assertStatus(200);
        $respTahapan->assertDontSee('Ruang Ujian Online (FR.IA)');

        $respJadwal = $this->actingAs($this->asesi)->get(route('asesi.jadwal'));
        $respJadwal->assertStatus(200);
        $respJadwal->assertDontSee('Ruang Ujian Online (FR.IA)');
    }

    /**
     * 16. Setelah menandatangani dan submit FR.AK.07, asesi langsung diarahkan ke halaman ujian/tes baru di Step 5
     */
    public function test_ak07_completion_in_tahapan_redirects_to_test_page_step_5(): void
    {
        // Pastikan AK01 selesai terlebih dahulu
        $this->pendaftaran->update([
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'signatures/dummy_asesi.png',
            'tanda_tangan_asesor_ak01' => 'signatures/profil_asesor_a.png',
        ]);

        $respSign = $this->actingAs($this->asesi)->post(route('asesi.ak07.confirm', $this->pendaftaran->id));

        $respSign->assertRedirect(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id, 'step' => 4]));
        $respSign->assertSessionHas('error');

        // Buka halaman tahapan; tanpa Master yang disahkan, peserta tetap berada di tahap 4.
        $respTahapan = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id]));
        $respTahapan->assertStatus(200);
        $respTahapan->assertSee('FR.AK.07 Belum Diisi Asesor');
    }
}
