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
        ]);
    }

    /**
     * 1. Asesor yang ditugaskan dapat membuka halaman edit FR.AK.07
     */
    public function test_assigned_asesor_can_view_ak07_edit_page(): void
    {
        $response = $this->actingAs($this->asesorA)
            ->get(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.AK.07');
        $response->assertSee('Ceklis Penyesuaian yang Wajar dan Beralasan');
        $response->assertSee($this->asesi->nama_lengkap);
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
    public function test_asesor_can_save_ak07_as_draft(): void
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

        $response->assertRedirect(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('assessment_ak07_adjustments', [
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 2,
            'fase_penggunaan' => 'pra_asesmen',
            'status' => 'draft',
        ]);

        $ak07 = AssessmentAk07Adjustment::where('assessment_registration_id', $this->pendaftaran->id)->first();
        $this->assertNotNull($ak07);
        $this->assertTrue($ak07->items_checklist[1]['perlu_penyesuaian']);
        $this->assertFalse($ak07->items_checklist[2]['perlu_penyesuaian']);
    }

    /**
     * 4. Asesor dapat mengonfirmasi FR.AK.07 dengan tanda tangan profil
     */
    public function test_asesor_can_confirm_ak07_with_profile_signature(): void
    {
        $payload = [
            'potensi_asesi' => 3,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'items_checklist' => [],
            'acuan_pembanding_disepakati' => 'SKKNI RPL',
            'metode_disepakati' => 'Observasi',
            'instrumen_disepakati' => 'FR.IA.01',
            'tanda_tangan_asesor' => $this->asesorA->tanda_tangan,
            'aksi' => 'confirm',
        ];

        $response = $this->actingAs($this->asesorA)
            ->post(route('asesor.pendaftaran.ak07.update', $this->pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

        $this->assertDatabaseHas('assessment_ak07_adjustments', [
            'assessment_registration_id' => $this->pendaftaran->id,
            'status' => 'confirmed',
            'asesor_signature' => $this->asesorA->tanda_tangan,
        ]);
    }

    /**
     * 5. Asesor dapat menandatangani FR.AK.07 dengan canvas base64 image
     */
    public function test_asesor_can_sign_ak07_with_canvas_signature(): void
    {
        $ak07 = AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'status' => 'draft',
        ]);

        $dummyCanvas = 'data:image/png;base64,' . base64_encode('dummy_png_bytes');

        $response = $this->actingAs($this->asesorA)
            ->post(route('asesor.pendaftaran.ak07.sign-asesor', $this->pendaftaran->id), [
                'tanda_tangan_asesor' => $dummyCanvas,
            ]);

        $response->assertRedirect();
        $ak07->refresh();

        $this->assertNotNull($ak07->asesor_signature);
        $this->assertNotNull($ak07->asesor_signed_at);
    }

    /**
     * 6. Asesi dapat melihat detail FR.AK.07 miliknya
     */
    public function test_asesi_can_view_ak07_detail(): void
    {
        AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->asesi)
            ->get(route('asesi.ak07', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.AK.07');
        $response->assertSee('Ceklis Penyesuaian yang Wajar dan Beralasan');
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
    public function test_asesi_can_sign_ak07_and_lock_confirmed(): void
    {
        $ak07 = AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'potensi_asesi' => 1,
            'asesor_signature' => 'signatures/profil_asesor_a.png',
            'asesor_signed_at' => now(),
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->asesi)
            ->post(route('asesi.ak07.sign-asesi', $this->pendaftaran->id), [
                'tanda_tangan_asesi' => $this->asesi->tanda_tangan,
            ]);

        $response->assertRedirect();
        $ak07->refresh();

        $this->assertEquals('signatures/profil_asesi.png', $ak07->asesi_signature);
        $this->assertNotNull($ak07->asesi_signed_at);
        $this->assertEquals('confirmed', $ak07->status);
    }

    /**
     * 9. Re-confirmation Rule: Perubahan data pada dokumen CONFIRMED otomatis mereset status ke DRAFT
     */
    public function test_modifying_confirmed_ak07_resets_status_to_draft(): void
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

        // Status wajib kembali ke draft dan signature dibatalkan
        $this->assertEquals('draft', $ak07->status);
        $this->assertNull($ak07->asesor_signature);
        $this->assertNull($ak07->asesi_signature);
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
        $respPeserta->assertSee(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

        // 2. Penilaian Live
        $respLive = $this->actingAs($this->asesorA)
            ->get(route('asesor.penilaian-live', ['pendaftaranId' => $this->pendaftaran->id]));
        $respLive->assertStatus(200);
        $respLive->assertSee('FR.AK.07');
        $respLive->assertSee(route('asesor.pendaftaran.ak07.edit', $this->pendaftaran->id));

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

        // 2. Asesi membuka detail FR.AK.07 langsung bahkan jika belum diisi asesor (auto-initialize)
        $respDetail = $this->actingAs($this->asesi)
            ->get(route('asesi.ak07', ['pendaftaranId' => $this->pendaftaran->id]));
        $respDetail->assertStatus(200);
        $respDetail->assertSee('FR.AK.07');
        $respDetail->assertSee('Ceklis Penyesuaian yang Wajar dan Beralasan');
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
        // Pengiriman form AK.01 dari tahapan formulir
        $resp = $this->actingAs($this->asesi)->post(route('asesi.tahapan.ak01'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_profil_tersimpan.png',
        ]);

        $resp->assertRedirect(route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $this->pendaftaran->id]));
        $resp->assertSessionHas('sukses');

        // Pengujian via AJAX
        $respAjax = $this->actingAs($this->asesi)->postJson(route('asesi.tahapan.ak01'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_profil_tersimpan.png',
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
        $respTahapan->assertSee('Penyesuaian yang Wajar dan Beralasan');
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

        $respSign = $this->actingAs($this->asesi)->post(route('asesi.ak07.sign-asesi', $this->pendaftaran->id), [
            'tanda_tangan_asesi' => 'signatures/asesi_sign_ak07.png',
        ]);

        $respSign->assertRedirect(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id, 'step' => 5]));
        $respSign->assertSessionHas('sukses');

        // Buka halaman tahapan tanpa parameter step: otomatis berada di Step 5
        $respTahapan = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaran->id]));
        $respTahapan->assertStatus(200);
        $respTahapan->assertSee('FR.IA.05 (Ujian Teori CBT PG)');
        $respTahapan->assertSee('Palet Nomor Soal');
    }
}
