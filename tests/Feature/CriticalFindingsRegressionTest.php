<?php

namespace Tests\Feature;

use App\Models\DokumenAsesi;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\Pengumuman;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CriticalFindingsRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesiA;
    protected Pengguna $asesiB;
    protected Pengguna $asesor;
    protected SkemaSertifikasi $skema;
    protected PendaftaranAsesi $pendaftaranA;
    protected PendaftaranAsesi $pendaftaranB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Test',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Test',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.123',
            'aktif' => true,
        ]);

        $this->asesiA = Pengguna::create([
            'nama_lengkap' => 'Asesi A',
            'email' => 'asesi.a@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        $this->asesiB = Pengguna::create([
            'nama_lengkap' => 'Asesi B',
            'email' => 'asesi.b@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Skema Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'kode_jadwal' => 'JDW-RPL-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'Lab RPL',
            'kuota' => 20,
        ]);

        $this->pendaftaranA = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-A01',
            'asesi_id' => $this->asesiA->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'jadwal_id' => $jadwal->id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
            'status_pendaftaran' => 'disetujui',
        ]);

        $this->pendaftaranB = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-B02',
            'asesi_id' => $this->asesiB->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'jadwal_id' => $jadwal->id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
            'status_pendaftaran' => 'disetujui',
        ]);
    }

    // =========================================================================
    // ITEM B: PUBLIC STORAGE ROUTE SECURITY
    // =========================================================================

    public function test_public_assets_in_storage_are_accessible_by_anyone(): void
    {
        // Buat file publik simulasi pada folder berita
        $storageDir = storage_path('app/public/berita');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir . '/test_public_image.txt';
        file_put_contents($testFile, 'PUBLIC CONTENT');

        try {
            $response = $this->get('/storage/berita/test_public_image.txt');
            $response->assertStatus(200);
            $this->assertEquals('PUBLIC CONTENT', file_get_contents($response->getFile()->getPathname()));
        } finally {
            if (file_exists($testFile)) {
                unlink($testFile);
            }
        }
    }

    public function test_protected_document_cannot_be_accessed_without_authentication(): void
    {
        $storageDir = storage_path('app/public/dokumen_asesi');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir . '/ktp_secret.pdf';
        file_put_contents($testFile, 'SENSITIVE KTP');

        try {
            $response = $this->get('/storage/dokumen_asesi/ktp_secret.pdf');
            $response->assertStatus(403);
        } finally {
            if (file_exists($testFile)) {
                unlink($testFile);
            }
        }
    }

    public function test_user_a_cannot_access_document_of_user_b(): void
    {
        $storageDir = storage_path('app/public/dokumen_asesi');
        File::ensureDirectoryExists($storageDir);
        $filename = 'ktp_asesi_b_' . time() . '.pdf';
        $testFile = $storageDir . '/' . $filename;
        file_put_contents($testFile, 'KTP ASESI B DATA');

        $dokumenB = DokumenAsesi::create([
            'pendaftaran_id' => $this->pendaftaranB->id,
            'jenis_dokumen' => 'KTP',
            'nama_dokumen' => 'KTP Asesi B',
            'file_path' => 'dokumen_asesi/' . $filename,
            'status_verifikasi' => 'valid',
        ]);

        try {
            // Asesi B (pemilik) dapat mengakses
            $resOwner = $this->actingAs($this->asesiB)->get('/storage/dokumen_asesi/' . $filename);
            $resOwner->assertStatus(200);

            // Asesi A (peserta lain) DITOLAK (403)
            $resOther = $this->actingAs($this->asesiA)->get('/storage/dokumen_asesi/' . $filename);
            $resOther->assertStatus(403);

            // Admin dapat mengakses
            $resAdmin = $this->actingAs($this->admin)->get('/storage/dokumen_asesi/' . $filename);
            $resAdmin->assertStatus(200);
        } finally {
            if (file_exists($testFile)) {
                unlink($testFile);
            }
        }
    }

    public function test_path_traversal_attempts_are_blocked(): void
    {
        // Traversal attempt to escape storage/app/public
        $response = $this->get('/storage/../../routes/web.php');
        $response->assertStatus(404);

        $responseDot = $this->get('/storage/....//....//routes/web.php');
        $responseDot->assertStatus(404);
    }

    // =========================================================================
    // ITEM C: NEWS ROUTE
    // =========================================================================

    public function test_news_routes_work_with_canonical_publik_prefix(): void
    {
        $berita = \App\Models\BeritaPengumuman::create([
            'judul' => 'Uji Regresi Berita LSP',
            'slug' => 'uji-regresi-berita-lsp',
            'konten' => 'Konten pengumuman regresi resmi.',
            'penulis_id' => $this->admin->id,
            'status' => 'dipublikasikan',
            'tanggal_publikasi' => now(),
        ]);

        $resList = $this->get(route('publik.berita'));
        $resList->assertStatus(200);
        $resList->assertSee('Uji Regresi Berita LSP');

        $resDetail = $this->get(route('publik.berita.detail', $berita->slug));
        $resDetail->assertStatus(200);
        $resDetail->assertSee('Uji Regresi Berita LSP');

        // Pastikan landing page merender link publik.berita.detail
        $resLanding = $this->get('/');
        $resLanding->assertStatus(200);
        $resLanding->assertSee(route('publik.berita.detail', $berita->slug));
    }

    // =========================================================================
    // ITEM D: ADMIN DOKUMEN BUNDEL CETAK ROUTE
    // =========================================================================

    public function test_admin_dokumen_bundel_cetak_route_is_accessible_by_admin(): void
    {
        // Admin dapat mengakses cetak bundel portofolio
        $response = $this->actingAs($this->admin)->get(route('admin.dokumen.bundel.cetak', $this->pendaftaranA->id));
        $response->assertStatus(200);
        $response->assertSee($this->pendaftaranA->nomor_pendaftaran);

        // Guest ditolak/dialihkan ke login
        $this->post(route('keluar'));
        $resGuest = $this->get(route('admin.dokumen.bundel.cetak', $this->pendaftaranA->id));
        $resGuest->assertRedirect(route('masuk'));

        // Asesi ditolak oleh middleware peran dan dialihkan ke dashboard asesi
        $resAsesi = $this->actingAs($this->asesiA)->get(route('admin.dokumen.bundel.cetak', $this->pendaftaranA->id));
        $resAsesi->assertRedirect(route('asesi.dashboard'));
    }

    // =========================================================================
    // ITEM E: ADMIN PENGATURAN SISTEM ROUTE MAPPING
    // =========================================================================

    public function test_admin_pengaturan_sistem_maps_to_pengaturan_view_not_manajemen_asesor(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.pengaturan-sistem'));
        $response->assertStatus(200);
        
        // Memastikan yang dirender adalah halaman pengaturan sistem, bukan daftar asesor
        $response->assertSee('Pengaturan Global Sistem');
        $response->assertDontSee('Pusat Registrasi Asesor Baru');
    }
}
