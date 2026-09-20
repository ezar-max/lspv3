<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Mapa02ResponsiveViewTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private Pengguna $admin;
    private Pengguna $asesi;
    private SkemaSertifikasi $skema;
    private UnitKompetensi $unit;
    private ElemenKompetensi $elemen;
    private KriteriaUnjukKerja $kuk;
    private PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RESP-001',
            'nama_skema' => 'Skema Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Responsive Test',
            'email' => 'asesor.resp@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'nomor_registrasi' => 'MET.000.123456',
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Responsive Test',
            'email' => 'admin.resp@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Responsive Test',
            'email' => 'asesi.resp@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menulis Kode dengan Prinsip Terstruktur',
        ]);

        $this->elemen = ElemenKompetensi::create([
            'unit_id' => $this->unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menerapkan Gaya Penulisan Kode',
        ]);

        $this->kuk = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Prinsip penulisan kode diimplementasikan sesuai pedoman.',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nomor_pendaftaran' => 'REG-RESP-001',
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'tanggal_daftar' => now(),
        ]);
    }

    public function test_asesor_can_view_mapa02_with_responsive_elements()
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa-02', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.MAPA.02');
        $response->assertSee('mapa02-container');
        $response->assertSee('mapa02-table-wrapper');
        $response->assertSee('mapa02-table');
        $response->assertSee('mapa02-th-elemen');
        $response->assertSee('mapa02-th-kuk');
        $response->assertSee('mapa02-th-ia');
        $response->assertSee('mapa02-td-elemen');
        $response->assertSee('mapa02-td-kuk');
        $response->assertSee('mapa02-td-ia');
        $response->assertSee('mapa02-cb');
        $response->assertSee('mapa02-legend');
        $response->assertSee('mapa02-quick-actions');
        $response->assertSee('mapa02-meta-grid');
        $response->assertSee('Geser tabel ke kanan untuk instrumen');
    }

    public function test_admin_can_view_mapa02_with_responsive_elements()
    {
        $response = $this->actingAs($this->admin)->get(route('asesor.mapa-02', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('FR.MAPA.02');
        $response->assertSee('mapa02-table-wrapper');
        $response->assertSee('mapa02-table');
        $response->assertSee('mapa02-th-ia');
        $response->assertSee('mapa02-td-ia');
        $response->assertSee('mapa02-cb');
    }

    public function test_master_mapa02_has_responsive_elements()
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-02', $this->skema->id));

        $response->assertStatus(200);
        $response->assertSee('FR.MAPA.02');
        $response->assertSee('mapa02-table-wrapper');
        $response->assertSee('mapa02-table');
    }
}
