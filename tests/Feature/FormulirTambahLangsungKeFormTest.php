<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormulirTambahLangsungKeFormTest extends TestCase
{
    use RefreshDatabase;

    protected $asesor;
    protected $admin;
    protected $skema;
    protected $unit;
    protected $elemen;
    protected $kuk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-001',
            'nama_skema' => 'Skema Pengujian Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menerapkan Prinsip Dasar Pemrograman',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $this->elemen = ElemenKompetensi::create([
            'unit_id' => $this->unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Mengidentifikasi Kebutuhan Perangkat Lunak',
        ]);

        $this->kuk = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Spesifikasi fungsional perangkat lunak diidentifikasi dengan teliti.',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji Kompetensi',
            'email' => 'asesor.penguji@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP Utama',
            'email' => 'admin.utama@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);
    }

    public function test_tambah_form_links_directly_to_form_sheet_with_contents_like_preview()
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $response->assertStatus(200);

        // Preview link and Tambah Form + link point to the same form route for FR.IA
        $expectedIa01Url = route('formulir.ia01', ['skema_id' => $this->skema->id]);
        $expectedIa02Url = route('formulir.ia02', ['skema_id' => $this->skema->id]);
        $expectedIa05Url = route('formulir.ia05a', ['skema_id' => $this->skema->id]);

        $response->assertSee($expectedIa01Url, false);
        $response->assertSee($expectedIa02Url, false);
        $response->assertSee($expectedIa05Url, false);

        // Buka lembar formulir IA.11 (memastikan judul unit kompetensi diambil dari database)
        $formIa11Resp = $this->actingAs($this->asesor)->get(route('formulir.ia11', ['skema_id' => $this->skema->id]));
        $formIa11Resp->assertStatus(200);
        $formIa11Resp->assertSee('FR.IA.11');
        $formIa11Resp->assertSee('J.620100.001.01');
        $formIa11Resp->assertSee('Menerapkan Prinsip Dasar Pemrograman');
    }


    public function test_simpan_ia_in_master_blanko_mode_saves_to_scheme_master_instrument()
    {
        // Asesor mengisi formulir master blanko (pendaftaranId = 0)
        $saveResp = $this->actingAs($this->asesor)->post(route('formulir.ia.simpan', [
            'kodeForm' => 'FR.IA.01',
            'pendaftaranId' => 0,
        ]), [
            'skema_id' => $this->skema->id,
            'standar_industri' => ['1' => 'SOP Pengembangan Software v2.0'],
            'umpan_balik' => 'Instrumen observasi siap digunakan',
        ]);

        $saveResp->assertRedirect();

        // Data tersimpan di SchemeMasterInstrument
        $instrument = SchemeMasterInstrument::where('skema_id', $this->skema->id)
            ->where('instrument_code', 'ia_01')
            ->first();

        $this->assertNotNull($instrument);
        $this->assertTrue($instrument->isConfigured());
        $this->assertEquals('SOP Pengembangan Software v2.0', $instrument->additional_metadata['standar_industri']['1']);

        // Kembali ke halaman index MAPA, tombol berubah dari "Tambah Form +" menjadi "Kelola Form" dan status "Tersedia"
        $indexResp = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Tersedia');
        $indexResp->assertSee('Kelola Form');
    }
}
