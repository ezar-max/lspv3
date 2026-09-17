<?php

namespace Tests\Feature;

use App\Models\BeritaPengumuman;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully_for_guest(): void
    {
        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Pemrogram Junior (Junior Developer)',
            'kategori' => 'Teknologi Informasi',
            'deskripsi' => 'Skema keahlian software engineering',
            'status_aktif' => true,
        ]);

        UnitKompetensi::create([
            'skema_id' => $skema->id,
            'kode_unit' => 'J.620100.004.01',
            'judul_unit' => 'Menggunakan Struktur Data',
            'jenis_standar' => 'SKKNI',
        ]);

        $admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP',
            'email' => 'admin.berita@example.com',
            'kata_sandi' => bcrypt('password123'),
            'peran' => 'admin',
            'aktif' => true,
        ]);

        BeritaPengumuman::create([
            'penulis_id' => $admin->id,
            'judul' => 'Pengumuman Jadwal Uji Kompetensi',
            'slug' => 'pengumuman-jadwal-uji-kompetensi',
            'kategori' => 'pengumuman',
            'konten' => 'Jadwal uji kompetensi periode ini telah dibuka.',
            'dipublikasikan' => true,
            'tanggal_publikasi' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('landing');
        $response->assertSee('LSP SMKN 1 Gunungputri');
        $response->assertSee('BNSP-LSP-2629-ID');
        $response->assertSee('Pemrogram Junior');
        $response->assertSee('Pengumuman Jadwal Uji Kompetensi');
        $response->assertSee(route('masuk'));
        $response->assertSee(route('daftar'));
    }

    public function test_landing_page_renders_for_authenticated_users(): void
    {
        $roles = ['asesi', 'asesor', 'admin', 'superadmin'];

        foreach ($roles as $peran) {
            $user = Pengguna::create([
                'nama_lengkap' => 'User ' . ucfirst($peran),
                'email' => "user.{$peran}@example.com",
                'kata_sandi' => bcrypt('password123'),
                'peran' => $peran,
                'aktif' => true,
            ]);

            $response = $this->actingAs($user)->get('/');
            $response->assertStatus(200);
            $response->assertSee('Buka Dasbor');
            $response->assertSee(route($peran . '.dasbor'));
        }
    }

    public function test_api_skema_units_returns_valid_json(): void
    {
        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-PPLG-01',
            'nama_skema' => 'Pemrograman Berorientasi Objek',
            'kategori' => 'PPLG',
            'deskripsi' => 'Pengembangan software OOP',
            'status_aktif' => true,
        ]);

        UnitKompetensi::create([
            'skema_id' => $skema->id,
            'kode_unit' => 'J.620100.005.02',
            'judul_unit' => 'Menerapkan Pemrograman Berorientasi Objek',
            'jenis_standar' => 'SKKNI',
        ]);

        $response = $this->getJson("/api/skema/{$skema->id}/units");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'skema' => 'Pemrograman Berorientasi Objek',
        ]);
        $response->assertJsonFragment([
            'kode_unit' => 'J.620100.005.02',
        ]);
    }

    public function test_route_daftar_is_accessible(): void
    {
        $response = $this->get('/daftar');
        $response->assertStatus(200);
    }

    public function test_navbar_buttons_hidden_on_masuk_and_daftar_pages(): void
    {
        $resMasuk = $this->get('/masuk');
        $resMasuk->assertStatus(200);
        $resMasuk->assertDontSee('class="nav-aksi"', false);

        $resMasuk->assertSee('id="menuNavigasi"', false);
        $resMasuk->assertSee('Beranda');
        $resMasuk->assertSee('Skema');
        $resMasuk->assertSee('Berita &amp; Pengumuman', false);
        $resMasuk->assertDontSee('Profil LSP');

        $resDaftar = $this->get('/daftar');
        $resDaftar->assertStatus(200);
        $resDaftar->assertDontSee('class="nav-aksi"', false);
        $resDaftar->assertSee('id="menuNavigasi"', false);
        $resDaftar->assertSee('Beranda');
        $resDaftar->assertSee('Skema');
        $resDaftar->assertSee('Berita &amp; Pengumuman', false);
        $resDaftar->assertDontSee('Profil LSP');

        $resRegistrasi = $this->get('/registrasi');
        $resRegistrasi->assertStatus(200);
        $resRegistrasi->assertDontSee('class="nav-aksi"', false);

        $resBeranda = $this->get('/');
        $resBeranda->assertStatus(200);
        $resBeranda->assertSee('class="nav-aksi"', false);
        $resBeranda->assertSee('id="menuNavigasi"', false);
        $resBeranda->assertSee('Beranda');
        $resBeranda->assertSee('Skema');
        $resBeranda->assertSee('Berita &amp; Pengumuman', false);
    }
}
