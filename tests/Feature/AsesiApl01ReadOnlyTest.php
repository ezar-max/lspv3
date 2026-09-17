<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesiApl01ReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesi;
    protected ProfilAsesi $profil;
    protected SkemaSertifikasi $skema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Coba',
            'email' => 'asesi.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
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
            'kode_skema' => 'SKM-TEST-01',
            'nama_skema' => 'Skema Pengujian Readonly',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);
    }

    public function test_apl01_is_editable_when_draft()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-001',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertSee('Simpan Draf');
        $response->assertDontSee('Terkunci (Read-Only)');
        $response->assertSee('input_file_ktp');
    }

    public function test_apl01_is_readonly_when_approved_by_admin()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-002',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tujuan_asesmen' => 'Sertifikasi',
            'tanda_tangan_admin' => 'data:image/png;base64,samplettd',
            'tanggal_ttd_admin' => now(),
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan'));

        $response->assertStatus(200);
        $response->assertSee('Disetujui Admin (ACC) &bull; Read-Only', false);
        $response->assertSee('Terkunci (Read-Only)');
        $response->assertSee('Lanjut ke Formulir FR.APL.02');
        $response->assertDontSee('Simpan Draf');
        $response->assertDontSee('input_file_ktp');
    }

    public function test_post_to_apl01_is_blocked_when_already_approved_by_admin()
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-003',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'tujuan_asesmen' => 'Sertifikasi',
            'tanda_tangan_admin' => 'data:image/png;base64,samplettd',
            'tanggal_ttd_admin' => now(),
        ]);

        $response = $this->actingAs($this->asesi)->post(route('asesi.tahapan.apl01'), [
            'pendaftaran_id' => $pendaftaran->id,
            'nama_lengkap' => 'Nama Baru Diubah',
            'nik' => '3201999999990001',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2004-02-02',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Alamat Baru Diubah',
            'nomor_telepon' => '089999999999',
            'skema_id' => $this->skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'aksi' => 'simpan_lanjut',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');

        // Pastikan nama lengkap dan NIK profil tidak berubah
        $this->profil->refresh();
        $this->assertEquals('3201123456780001', $this->profil->nik);
        $this->assertEquals('Bogor', $this->profil->tempat_lahir);
    }
}
