<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\PendaftaranAsesi;
use App\Models\Mapa01;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Mapa01KonfirmasiOrangRelevanTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesor;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP SMKN 1',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'tanda_tangan' => 'signatures/admin_smkn1.png',
            'nomor_registrasi' => 'ADM.001.2026',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji 1',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'tanda_tangan' => 'signatures/asesor1.png',
            'nomor_registrasi' => 'MET.000.123456 2026',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TKJ-001',
            'nama_skema' => 'Teknik Komputer dan Jaringan',
            'status_aktif' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.611000.001.01',
            'judul_unit' => 'Memasang Perangkat Jaringan',
        ]);
    }

    /**
     * Test tampilan tabel konfirmasi orang yang relevan:
     * Menyajikan sinkronisasi, tidak ada badge statis '• Sesuai', dan baris dapat diaktifkan/dicoret.
     */
    public function test_view_renders_konfirmasi_tabel_with_proper_labels_and_no_fake_badge(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);

        // Tidak boleh ada badge hijau palsu "• Sesuai" pada tabel konfirmasi
        $response->assertDontSee('<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Sesuai', false);

        // Harus menampilkan tabel dengan id tabel-konfirmasi-relevan
        $response->assertSee('id="tabel-konfirmasi-relevan"', false);
        $response->assertSee('Orang yang Relevan (*Coret jika tidak perlu)');
        $response->assertSee('Gunakan Data Manajer/Admin LSP');
        $response->assertSee('Hari Ini');

        // Pastikan 4 peran utama terdefinisi
        $response->assertSee('Manajer sertifikasi LSP');
        $response->assertSee('Master Asesor / Master Trainer / Lead Asesor Kompetensi');
        $response->assertSee('Manajer pelatihan Lembaga Training terakreditasi / terdaftar');
        $response->assertSee('Manajer atau supervisor di tempat kerja');
    }

    /**
     * Test penyimpanan konfirmasi orang yang relevan dan tabelnya tersinkronisasi dua arah.
     */
    public function test_saving_mapa01_synchronizes_konfirmasi_orang_relevan_and_tabel(): void
    {
        $payload = [
            'aksi' => 'draft',
            'konfirmasi_orang_relevan' => ['Manajer sertifikasi LSP'],
            'konfirmasi_pihak_relevan_tabel' => [
                'manajer_lsp' => [
                    'relevan' => 1,
                    'nama' => 'Drs. Budi Santoso, M.Pd.',
                    'ttd_tanggal' => '18/09/2026',
                ],
                'lead_asesor' => [
                    'relevan' => 0,
                    'nama' => '',
                    'ttd_tanggal' => '',
                ],
            ],
            'rencana_unit_matriks' => [
                $this->unit->id => [
                    'l' => 1,
                    'tl' => 0,
                    't' => 1,
                    'methods' => ['CL', 'DPT'],
                    'bukti' => 'Bukti demonstrasi',
                ],
            ],
            'penyusun_validator_tabel' => [
                'penyusun_1' => [
                    'nama' => $this->asesor->nama_lengkap,
                    'nomor_met' => $this->asesor->nomor_registrasi,
                    'ttd' => $this->asesor->tanda_tangan,
                    'ttd_tanggal' => date('d/m/Y'),
                ],
            ],
        ];

        $response = $this->actingAs($this->asesor)->post(
            route('asesor.skema.mapa-01.simpan', $this->skema->id),
            $payload
        );

        $response->assertSessionHasNoErrors();

        $mapa = Mapa01::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($mapa);

        // Pastikan konfirmasi_orang_relevan tersimpan
        $this->assertContains('Manajer sertifikasi LSP', $mapa->konfirmasi_orang_relevan);

        // Pastikan konfirmasi_pihak_relevan_tabel tersimpan dengan struktur normal
        $tabel = $mapa->konfirmasi_pihak_relevan_tabel;
        $this->assertIsArray($tabel);
        $this->assertEquals(1, $tabel['manajer_lsp']['relevan']);
        $this->assertEquals('Drs. Budi Santoso, M.Pd.', $tabel['manajer_lsp']['nama']);
        $this->assertEquals('18/09/2026', $tabel['manajer_lsp']['ttd_tanggal']);

        $this->assertEquals(0, $tabel['lead_asesor']['relevan']);
    }
}

