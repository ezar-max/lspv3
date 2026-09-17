<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\MasterAk01;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAk01PerSkemaTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private Pengguna $asesi;
    private SkemaSertifikasi $skema;
    private UnitKompetensi $unit;
    private ElemenKompetensi $elemen;
    private KriteriaUnjukKerja $kuk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-001',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Uji AK01',
            'email' => 'asesor.ak01@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'tanda_tangan' => 'signatures/asesor_profil.png',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji AK01',
            'email' => 'asesi.ak01@lsp.test',
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
            'pernyataan_kuk' => 'Prinsip penulisan kode diimplementasikan.',
        ]);
    }

    /** 1. Halaman Hub MAPA menampilkan Card FR.AK.01 */
    public function test_asesor_sees_master_ak01_card_in_mapa_hub(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa', ['skema_id' => $this->skema->id]));

        $response->assertStatus(200);
        $response->assertSee('FR.AK.01');
        $response->assertSee(route('asesor.skema.ak-01', $this->skema->id));
        $this->assertTrue(
            str_contains($response->getContent(), 'Kelola Form') || str_contains($response->getContent(), 'Tambah Form +')
        );
    }

    /** 2. Asesor dapat membuka halaman konfigurasi Master FR.AK.01 */
    public function test_asesor_can_open_master_ak01_page(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.ak-01', $this->skema->id));

        $response->assertStatus(200);
        $response->assertSee('MASTER TEMPLATE SKEMA');
        $response->assertSee($this->skema->kode_skema);
        $response->assertSee($this->unit->judul_unit);
    }

    /** 3. Asesor dapat menyimpan & mengesahkan Master FR.AK.01 untuk skema */
    public function test_asesor_can_save_master_ak01_for_scheme(): void
    {
        $postData = [
            'tuk_type' => 'Tempat Kerja',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)'],
            'bukti_dikumpulkan_lainnya' => 'Logbook PKL Industri',
            'catatan_asesor' => 'Asesi wajib membawa laptop sendiri',
            'tanda_tangan_asesor' => 'signatures/asesor_profil.png',
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.skema.ak-01.simpan', $this->skema->id), $postData);

        $response->assertRedirect(route('asesor.mapa', ['skema_id' => $this->skema->id]));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('master_ak01', [
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tuk_type' => 'Tempat Kerja',
            'bukti_dikumpulkan_lainnya' => 'Logbook PKL Industri',
            'status' => 'selesai',
        ]);

        $master = MasterAk01::where('skema_id', $this->skema->id)->first();
        $this->assertEquals(['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)'], $master->bukti_dikumpulkan);
    }

    /** 4. Data Master FR.AK.01 tersinkronisasi otomatis ke seluruh pendaftaran asesi pada skema */
    public function test_master_ak01_automatically_syncs_to_asesi_registrations(): void
    {
        // Buat 2 pendaftaran asesi pada skema ini
        $pendaftaran1 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001',
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
        ]);

        $asesi2 = Pengguna::create([
            'nama_lengkap' => 'Asesi Kedua',
            'email' => 'asesi2@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $pendaftaran2 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-002',
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'asesi_id' => $asesi2->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
        ]);

        // Asesor menyimpan master AK.01
        $this->actingAs($this->asesor)->post(route('asesor.skema.ak-01.simpan', $this->skema->id), [
            'tuk_type' => 'Tempat Kerja',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Tanya Jawab Lisan'],
            'bukti_dikumpulkan_lainnya' => 'Portofolio Digital',
            'tanda_tangan_asesor' => 'signatures/asesor_profil.png',
        ]);

        // Refresh model pendaftaran dari DB
        $pendaftaran1->refresh();
        $pendaftaran2->refresh();

        $this->assertEquals('Tempat Kerja', $pendaftaran1->tuk_type);
        $this->assertEquals('Portofolio Digital', $pendaftaran1->bukti_dikumpulkan_lainnya);
        $this->assertEquals('signatures/asesor_profil.png', $pendaftaran1->tanda_tangan_asesor_ak01);
        $this->assertEquals('disetujui_asesor', $pendaftaran1->status_ak01);

        $this->assertEquals('Tempat Kerja', $pendaftaran2->tuk_type);
        $this->assertEquals('signatures/asesor_profil.png', $pendaftaran2->tanda_tangan_asesor_ak01);
        $this->assertEquals('disetujui_asesor', $pendaftaran2->status_ak01);
    }

    /** 5. Asesi membuka formulir FR.AK.01 dan data dari asesor terisi otomatis */
    public function test_asesi_sees_prefilled_ak01_from_asesor_and_can_sign(): void
    {
        // Buat master AK.01
        MasterAk01::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tuk_type' => 'Mandiri',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)'],
            'bukti_dikumpulkan_lainnya' => 'Sertifikat Pelatihan',
            'tanda_tangan_asesor' => 'signatures/asesor_profil.png',
            'tanggal_ttd_asesor' => now(),
            'status' => 'selesai',
        ]);

        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-003',
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
        ]);

        // Asesi mengakses halaman ak01
        $response = $this->actingAs($this->asesi)->get(route('asesi.ak01', ['id' => $pendaftaran->id]));
        $response->assertStatus(200);
        $response->assertSee('TUK Mandiri');

        // Pendaftaran otomatis sync
        $pendaftaran->refresh();
        $this->assertEquals('Mandiri', $pendaftaran->tuk_type);
        $this->assertEquals('signatures/asesor_profil.png', $pendaftaran->tanda_tangan_asesor_ak01);

        // Asesi menandatangani formulir FR.AK.01
        $signResponse = $this->actingAs($this->asesi)->post(route('asesi.ak01.sign', ['id' => $pendaftaran->id]), [
            'pendaftaran_id' => $pendaftaran->id,
            'tanda_tangan_asesi_ak01' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $pendaftaran->refresh();

        // Status AK.01 langsung menjadi 'selesai' karena asesor sudah tanda tangan via Master AK.01
        $this->assertEquals('selesai', $pendaftaran->status_ak01);
        $this->assertNotEmpty($pendaftaran->tanda_tangan_asesi_ak01);
    }
}
