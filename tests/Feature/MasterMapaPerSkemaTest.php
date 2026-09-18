<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Services\MapaWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterMapaPerSkemaTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private SkemaSertifikasi $skema;
    private UnitKompetensi $unit;
    private ElemenKompetensi $elemen;
    private KriteriaUnjukKerja $kuk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-001',
            'nama_skema' => 'Skema Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Master Test',
            'email' => 'asesor.master@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
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
    }

    /** 1. Halaman Hub MAPA dapat diakses asesor saat skema belum memiliki pendaftaran asesi */
    public function test_asesor_can_access_mapa_hub_with_no_registrations(): void
    {
        // Pastikan tidak ada pendaftaran sama sekali di DB
        $this->assertEquals(0, PendaftaranAsesi::count());

        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $response->assertStatus(200);
        $response->assertSee(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertSee(route('asesor.skema.mapa-02', $this->skema->id));
    }

    /** 2. Asesor dapat membuka dan menyimpan Master FR.MAPA.01 */
    public function test_asesor_can_open_and_save_master_mapa01(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);
        $response->assertSee('MASTER TEMPLATE SKEMA');

        // Simpan Draft Master MAPA-01
        $postResponse = $this->actingAs($this->asesor)->post(route('asesor.skema.mapa-01.simpan', $this->skema->id), [
            'aksi' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi Kejuruan RPL',
            'konteks_lingkungan' => 'simulasi',
            'konteks_peluang_bukti' => 'tersedia',
        ]);

        $postResponse->assertRedirect(route('asesor.skema.mapa-01', $this->skema->id));

        $master01 = Mapa01::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($master01);
        $this->assertEquals('Sertifikasi Kejuruan RPL', $master01->tujuan_asesmen);
        $this->assertEquals('draft', $master01->status_mapa);
    }

    /** 3. Master FR.MAPA.02 baru dibuka dalam keadaan kosong dan tidak langsung disimpan ke database */
    public function test_master_mapa02_starts_blank_and_does_not_persist_records_on_get(): void
    {
        $this->assertEquals(0, Mapa02::where('skema_id', $this->skema->id)->count());

        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-02', $this->skema->id));
        $response->assertStatus(200);

        // Tidak otomatis membuat record di DB saat baru dibuka
        $this->assertEquals(0, Mapa02::where('skema_id', $this->skema->id)->count());

        // Pastikan tidak ada instrumen yang tercentang
        $response->assertDontSee('value="1" checked');
    }

    /** 4. Asesor dapat membuka dan mengesahkan Master FR.MAPA.02 */
    public function test_asesor_can_open_and_confirm_master_mapa02(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-02', $this->skema->id));
        $response->assertStatus(200);

        // Sahkan Master MAPA-02 dengan matriks KUK aktif
        $matrix = [
            $this->unit->id => [
                $this->elemen->id => [
                    $this->kuk->id => [
                        'clo' => 1,
                        'dpt' => 1,
                    ],
                ],
            ],
        ];

        $postResponse = $this->actingAs($this->asesor)->post(route('asesor.skema.mapa-02.simpan', $this->skema->id), [
            'aksi' => 'konfirmasi',
            'matriks_peta' => $matrix,
            'catatan_asesor' => 'Matriks master terverifikasi asesor RPL.',
        ]);

        $postResponse->assertRedirect(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $master02 = Mapa02::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($master02);
        $this->assertEquals('selesai', $master02->status_mapa);
        $this->assertEquals(1, $master02->matriks_peta[$this->unit->id][$this->elemen->id][$this->kuk->id]['clo']);
    }

    /** 4. Pratinjau cetak FR.MAPA dapat diakses berbasis skema_id tanpa data pendaftaran nyata */
    public function test_print_preview_works_without_registration(): void
    {
        $response01 = $this->actingAs($this->asesor)->get(route('formulir.mapa01', ['skema_id' => $this->skema->id]));
        $response01->assertStatus(200);

        $response02 = $this->actingAs($this->asesor)->get(route('formulir.mapa02', ['skema_id' => $this->skema->id]));
        $response02->assertStatus(200);
    }

    /** 5. Pendaftaran asesi baru otomatis mewarisi isian dari Master MAPA */
    public function test_new_registration_inherits_from_master_mapa(): void
    {
        $service = app(MapaWorkflowService::class);

        // Buat dan sahkan master
        $service->saveMasterMapa01($this->skema, [
            'tujuan_asesmen' => 'Sertifikasi BNSP Unggulan',
            'konteks_lingkungan' => 'nyata',
        ], null, true, $this->asesor->id);

        $matrix = [
            $this->unit->id => [
                $this->elemen->id => [
                    $this->kuk->id => [
                        'clo' => 1,
                        'dpe' => 1,
                    ],
                ],
            ],
        ];
        $service->saveMasterMapa02($this->skema, $matrix, 'Catatan Khusus Master RPL', null, true, $this->asesor->id);

        // Ada asesi baru mendaftar
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Calon Asesi Baru',
            'email' => 'asesi.baru@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-2026-0099',
            'skema_id' => $this->skema->id,
            'asesi_id' => $asesi->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diajukan',
            'tanggal_daftar' => now(),
        ]);

        // Auto generation untuk pendaftaran asesi
        $mapa01Asesi = $service->getOrCreateMapa01($pendaftaran);
        $mapa02Asesi = $service->getOrCreateMapa02($pendaftaran);

        // Pastikan mewarisi data dari Master
        $this->assertEquals('Sertifikasi BNSP Unggulan', $mapa01Asesi->tujuan_asesmen);
        $this->assertEquals('Catatan Khusus Master RPL', $mapa02Asesi->catatan_asesor);
        $this->assertEquals(1, $mapa02Asesi->matriks_peta[$this->unit->id][$this->elemen->id][$this->kuk->id]['dpe']);
    }
}
