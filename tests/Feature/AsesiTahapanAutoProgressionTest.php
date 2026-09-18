<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesiTahapanAutoProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesi;
    protected ProfilAsesi $profil;
    protected SkemaSertifikasi $skema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Auto Step',
            'email' => 'asesi.autostep@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->profil = ProfilAsesi::create([
            'pengguna_id' => $this->asesi->id,
            'nik' => '3201123456780009',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Merdeka No. 10',
            'nomor_telepon' => '081234567899',
            'nama_sekolah_instansi' => 'SMKN 1 Gunungputri',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-AUTO-01',
            'nama_skema' => 'Skema Auto Progression',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);
    }

    public function test_page_does_not_render_old_stepper_buttons()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-AUTO-001',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertDontSee('stepper-item');
        $response->assertDontSee('HORIZONTAL STEP INDICATOR');
    }

    public function test_page_defaults_to_step_1_when_apl01_not_approved()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-AUTO-002',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertViewHas('currentStep', 1);
        $response->assertViewHas('activeStep', 1);
    }

    public function test_page_auto_advances_to_step_2_when_apl01_is_approved_by_admin()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-AUTO-003',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tanda_tangan_admin' => 'data:image/png;base64,ttdadmin',
            'tanggal_ttd_admin' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertViewHas('currentStep', 2);
        $response->assertViewHas('activeStep', 2);
    }

    public function test_page_auto_advances_to_step_3_when_apl02_is_approved_by_asesor()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-AUTO-004',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tanda_tangan_admin' => 'data:image/png;base64,ttdadmin',
            'tanggal_ttd_admin' => now(),
            'status_apl02' => 'approved',
            'status_ak01' => 'menunggu_persetujuan',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertViewHas('currentStep', 3);
        $response->assertViewHas('activeStep', 3);
    }

    public function test_page_auto_advances_to_step_4_when_ak01_is_completed()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-AUTO-005',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tanda_tangan_admin' => 'data:image/png;base64,ttdadmin',
            'tanggal_ttd_admin' => now(),
            'status_apl02' => 'approved',
            'status_ak01' => 'disetujui_asesi',
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_ak01.png',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertViewHas('currentStep', 4);
        $response->assertViewHas('activeStep', 4);
        $response->assertSee('FR.AK.07');
        $response->assertSee('Penyesuaian yang Wajar dan Beralasan');
    }
}

