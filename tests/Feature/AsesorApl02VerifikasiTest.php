<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Notifications\APL02Rejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AsesorApl02VerifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected ProfilAsesi $profil;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit;
    protected ElemenKompetensi $elemen;
    protected KriteriaUnjukKerja $kuk1;
    protected KriteriaUnjukKerja $kuk2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji Uji Coba',
            'email' => 'asesor.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001 2026',
            'tanda_tangan' => 'data:image/png;base64,samplettdasesor',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Coba',
            'email' => 'asesi.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'tanda_tangan' => 'data:image/png;base64,samplettdasesi',
        ]);

        $this->profil = ProfilAsesi::create([
            'pengguna_id' => $this->asesi->id,
            'nik' => '3201123456780001',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Merdeka No. 10',
            'nomor_telepon' => '081234567890',
            'nama_sekolah_instansi' => 'SMKN 1 Gunungputri',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-02',
            'nama_skema' => 'Skema Pengujian APL 02',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'TIK.MM01.001.01',
            'judul_unit' => 'Menerapkan Prinsip Dasar Desain',
        ]);

        $this->elemen = ElemenKompetensi::create([
            'unit_id' => $this->unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Mengidentifikasi materi desain',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Materi desain diidentifikasi sesuai kebutuhan',
        ]);

        $this->kuk2 = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen->id,
            'nomor_kuk' => '1.2',
            'pernyataan_kuk' => 'Format file dipastikan sesuai standar',
        ]);
    }

    private function createPendaftaranSubmitted(): PendaftaranAsesi
    {
        return PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nomor_pendaftaran' => 'REG-APL02-001',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tanda_tangan_admin' => 'data:image/png;base64,ttdadmin',
            'status_apl02' => 'submitted',
            'tanggal_submit_apl02' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
        ]);
    }

    public function test_view_input_penilaian_has_tolak_button_and_no_standalone_minta_revisi_button()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();

        $response = $this->actingAs($this->asesor)->get(route('asesor.input-penilaian', $pendaftaran->id));

        $response->assertStatus(200);
        // Standalone "Minta Revisi" button in bottom bar should be absent
        $response->assertDontSee('Minta Revisi</button>', false);
        // Tolak button should be present
        $response->assertSee('Tolak</span>', false);
        $response->assertSee('bukaModalTolak()', false);
        $response->assertSee('modalTolak', false);
        // Dynamic main button should be present
        $response->assertSee('btn-submit-penilaian-asesor', false);
    }

    public function test_asesor_cannot_approve_if_any_kuk_is_bk()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();

        $payload = [
            'action_type' => 'approve',
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'catatan_rekomendasi' => 'Semua oke',
            'tanda_tangan_asesor' => 'data:image/png;base64,samplettdasesor',
            'verifikasi_kuk' => [
                $this->kuk1->id => 1, // K
                $this->kuk2->id => 0, // BK
            ],
            'nilai' => [
                $this->unit->id => 'K',
            ],
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.input-penilaian.simpan', $pendaftaran->id), $payload);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Belum Kompeten (BK)', session('error'));
        $pendaftaran->refresh();
        $this->assertNotEquals('approved', $pendaftaran->status_apl02);
    }

    public function test_asesor_can_request_revision_when_kuk_is_bk()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();

        $payload = [
            'action_type' => 'revision',
            'rekomendasi_asesor_status' => 'tidak_dapat_dilanjutkan',
            'catatan_rekomendasi' => 'Mohon lengkapi bukti pada KUK 1.2',
            'tanda_tangan_asesor' => 'data:image/png;base64,samplettdasesor',
            'verifikasi_kuk' => [
                $this->kuk1->id => 1,
                $this->kuk2->id => 0,
            ],
            'catatan_kuk' => [
                $this->kuk2->id => 'Bukti portofolio belum sesuai',
            ],
            'nilai' => [
                $this->unit->id => 'BK',
            ],
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.input-penilaian.simpan', $pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.daftar-peserta'));
        $response->assertSessionHas('sukses');
        $pendaftaran->refresh();
        $this->assertTrue($pendaftaran->isApl02Revision());
        $this->assertEquals('tidak_dapat_dilanjutkan', $pendaftaran->rekomendasi_asesor_status);
        $this->assertEquals('Mohon lengkapi bukti pada KUK 1.2', $pendaftaran->catatan_peninjauan_asesor);
    }

    public function test_asesor_can_reject_apl02()
    {
        Notification::fake();

        $pendaftaran = $this->createPendaftaranSubmitted();

        $payload = [
            'action_type' => 'reject',
            'rekomendasi_asesor_status' => 'ditolak',
            'catatan_rekomendasi' => 'Berkas dan portofolio tidak memenuhi persyaratan skema sertifikasi.',
            'tanda_tangan_asesor' => 'data:image/png;base64,samplettdasesor',
            'verifikasi_kuk' => [
                $this->kuk1->id => 0,
                $this->kuk2->id => 0,
            ],
            'nilai' => [
                $this->unit->id => 'BK',
            ],
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.input-penilaian.simpan', $pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.daftar-peserta'));
        $response->assertSessionHas('sukses');
        $this->assertStringContainsString('ditolak', session('sukses'));

        $pendaftaran->refresh();
        $this->assertTrue($pendaftaran->isApl02Rejected());
        $this->assertEquals('ditolak', $pendaftaran->rekomendasi_asesor_status);
        $this->assertEquals('Berkas dan portofolio tidak memenuhi persyaratan skema sertifikasi.', $pendaftaran->catatan_peninjauan_asesor);

        // Notification should be sent to Asesi
        Notification::assertSentTo($this->asesi, APL02Rejected::class);
    }

    public function test_asesi_view_shows_rejected_alert_when_apl02_rejected()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();
        $pendaftaran->update([
            'status_apl02' => 'rejected',
            'rekomendasi_asesor_status' => 'ditolak',
            'catatan_peninjauan_asesor' => 'Tidak memenuhi kualifikasi portofolio.',
            'tanggal_ttd_asesor' => now(),
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', ['step' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Status FR.APL.02: Ditolak Asesor (Tidak Dapat Diterima)');
        $response->assertSee('Tidak memenuhi kualifikasi portofolio.');
        $response->assertSee('FR.APL.02 Ditolak Asesor');
    }

    public function test_asesor_can_approve_when_all_kuk_are_k()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();

        $payload = [
            'action_type' => 'approve',
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'catatan_rekomendasi' => 'Semua bukti lengkap dan valid.',
            'tanda_tangan_asesor' => 'data:image/png;base64,samplettdasesor',
            'verifikasi_kuk' => [
                $this->kuk1->id => 1,
                $this->kuk2->id => 1,
            ],
            'nilai' => [
                $this->unit->id => 'K',
            ],
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.input-penilaian.simpan', $pendaftaran->id), $payload);

        $response->assertRedirect(route('asesor.dashboard'));
        $response->assertSessionHas('sukses');

        $pendaftaran->refresh();
        $this->assertTrue($pendaftaran->isApl02Approved());
        $this->assertEquals('dapat_dilanjutkan', $pendaftaran->rekomendasi_asesor_status);
    }

    public function test_asesi_dashboard_shows_revisi_badge_when_apl02_under_revision()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();
        $pendaftaran->update([
            'status_apl02' => 'revision',
            'rekomendasi_asesor_status' => 'tidak_dapat_dilanjutkan',
            'catatan_peninjauan_asesor' => 'Bukti belum lengkap, tolong perbaiki.',
            'tanggal_ttd_asesor' => now(),
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.dashboard'));

        $response->assertStatus(200);
        // Badge should say "Revisi"
        $response->assertSee('Revisi');
        // It must NOT say "Pendaftaran Ditolak"
        $response->assertDontSee('Pendaftaran Ditolak');
        $response->assertDontSee('Pendaftaran Tidak Dapat Dilanjutkan (Ditolak)');
        // Should show revision banner
        $response->assertSee('Permintaan Revisi FR.APL.02');
    }

    public function test_asesi_dashboard_shows_pendaftaran_ditolak_badge_when_apl02_rejected()
    {
        $pendaftaran = $this->createPendaftaranSubmitted();
        $pendaftaran->update([
            'status_apl02' => 'rejected',
            'rekomendasi_asesor_status' => 'ditolak',
            'catatan_peninjauan_asesor' => 'Tidak memenuhi kualifikasi portofolio.',
            'tanggal_ttd_asesor' => now(),
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.dashboard'));

        $response->assertStatus(200);
        // Badge should say "Pendaftaran Ditolak"
        $response->assertSee('Pendaftaran Ditolak');
        $response->assertSee('Pendaftaran Tidak Dapat Dilanjutkan (Ditolak)');
    }
}
