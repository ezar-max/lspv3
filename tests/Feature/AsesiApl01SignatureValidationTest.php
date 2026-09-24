<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\PendaftaranAsesi;
use App\Models\ProfilAsesi;
use App\Models\DokumenAsesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsesiApl01SignatureValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-01',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP Test',
            'email' => 'admin@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'aktif' => true,
            'tanda_tangan' => 'data:image/png;base64,mockAdminTtd',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji Coba',
            'email' => 'asesi@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        ProfilAsesi::create([
            'pengguna_id' => $this->asesi->id,
            'nik' => '3201123456780001',
            'nama_sekolah_instansi' => 'SMKN 1 Gunungputri',
            'nomor_pendaftaran' => 'REG-2026-0001',
        ]);
    }

    public function test_asesi_cannot_submit_apl01_without_signature(): void
    {
        $fileRapor = UploadedFile::fake()->create('rapor.pdf', 500, 'application/pdf');
        $filePkl = UploadedFile::fake()->create('pkl.pdf', 500, 'application/pdf');
        $fileKtp = UploadedFile::fake()->create('ktp.pdf', 500, 'application/pdf');
        $fileFoto = UploadedFile::fake()->image('foto.jpg');

        $response = $this->actingAs($this->asesi)->post(route('asesi.formulir.simpan-apl01'), [
            'aksi' => 'ajukan',
            'nama_lengkap' => 'Asesi Uji Coba',
            'nik' => '3201123456780001',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Raya Gunungputri No. 1',
            'nomor_telepon' => '081234567890',
            'skema_id' => $this->skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'file_rapor' => $fileRapor,
            'file_pkl' => $filePkl,
            'file_ktp' => $fileKtp,
            'file_foto' => $fileFoto,
            'tanda_tangan_asesi' => '', // Kosong tanpa TTD
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'Tanda tangan digital Asesi wajib');
        });

        $this->assertDatabaseMissing('pendaftaran_asesi', [
            'asesi_id' => $this->asesi->id,
            'status_pendaftaran' => 'diajukan',
        ]);
    }

    public function test_asesi_can_submit_apl01_with_valid_signature(): void
    {
        $fileRapor = UploadedFile::fake()->create('rapor.pdf', 500, 'application/pdf');
        $filePkl = UploadedFile::fake()->create('pkl.pdf', 500, 'application/pdf');
        $fileKtp = UploadedFile::fake()->create('ktp.pdf', 500, 'application/pdf');
        $fileFoto = UploadedFile::fake()->image('foto.jpg');

        $mockTtd = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWAQMAAABf6+mHAAAABlBMVEX///8AAABVwtN+AAAAAXRSTlMAQObYZgAAACNJREFUeN7twTEBAAAAwqD1T20MH6AAAAAAAAAAAAAAAAC4GQ4sAAGlP4G0AAAAAElFTkSuQmCC';

        $response = $this->actingAs($this->asesi)->post(route('asesi.formulir.simpan-apl01'), [
            'aksi' => 'ajukan',
            'nama_lengkap' => 'Asesi Uji Coba',
            'nik' => '3201123456780001',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Raya Gunungputri No. 1',
            'nomor_telepon' => '081234567890',
            'skema_id' => $this->skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'file_rapor' => $fileRapor,
            'file_pkl' => $filePkl,
            'file_ktp' => $fileKtp,
            'file_foto' => $fileFoto,
            'tanda_tangan_asesi' => $mockTtd,
        ]);

        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('pendaftaran_asesi', [
            'asesi_id' => $this->asesi->id,
            'status_pendaftaran' => 'diajukan',
        ]);
    }

    public function test_admin_can_reject_pendaftaran_without_selecting_schedule(): void
    {
        // Pendaftaran diajukan oleh asesi
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-0001',
            'status_pendaftaran' => 'diajukan',
            'tanggal_daftar' => now()->toDateString(),
            'tanda_tangan_asesi' => 'data:image/png;base64,mockAsesiTtd',
        ]);

        // Tidak ada jadwal sama sekali untuk skema ini
        $this->assertDatabaseCount('jadwal_asesmen', 0);

        // Admin melakukan aksi tolak
        $response = $this->actingAs($this->admin)->post(route('admin.verifikasi.simpan', $pendaftaran->id), [
            'status_pendaftaran' => 'ditolak',
            'rekomendasi_admin_status' => 'tidak_diterima',
            'jadwal_id' => null, // Tidak wajib ada jadwal
            'catatan_verifikasi' => 'Berkas persyaratan tidak sesuai ketentuan.',
        ]);

        $response->assertRedirect(route('admin.verifikasi-berkas'));
        $response->assertSessionHas('sukses');

        $pendaftaran->refresh();
        $this->assertEquals('ditolak', $pendaftaran->status_pendaftaran);
        $this->assertEquals('tidak_diterima', $pendaftaran->rekomendasi_admin_status);
        $this->assertNull($pendaftaran->jadwal_id);
    }

    public function test_detail_verifikasi_view_has_formnovalidate_on_reject_button(): void
    {
        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-0001',
            'status_pendaftaran' => 'diajukan',
            'tanggal_daftar' => now()->toDateString(),
            'tanda_tangan_asesi' => 'data:image/png;base64,mockAsesiTtd',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.detail-verifikasi', $pendaftaran->id));
        $response->assertStatus(200);
        $response->assertSee('formnovalidate');
        $response->assertSee('Tolak Permohonan');
    }

    public function test_manajemen_jadwal_view_has_responsive_mobile_classes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.manajemen-jadwal'));
        $response->assertStatus(200);
        $response->assertSee('jadwal-form-grid-2');
        $response->assertSee('jadwal-form-grid-3');
    }
}
