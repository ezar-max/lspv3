<?php

namespace Tests\Feature;

use App\Models\MasterAk01;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesiTahapanAk01SchemeDataTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private Pengguna $asesi;
    private SkemaSertifikasi $skema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-PJ-001',
            'nama_skema' => 'Pemrogram Junior (Junior Coder)',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Fredi Wahyu Ezar',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'tanda_tangan' => 'signatures/asesor_sig.png',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Tester Asesi',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);
    }

    /** 1. Tahapan Step 3 otomatis mengambil data metode uji dan TUK dari Master AK.01 skema di database */
    public function test_asesi_tahapan_step3_loads_form_data_from_scheme_master_ak01(): void
    {
        // Setup Master AK.01 skema di database
        MasterAk01::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tuk_type' => 'Sewaktu',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Tanya Jawab Lisan'],
            'bukti_dikumpulkan_lainnya' => null,
            'tanda_tangan_asesor' => 'signatures/asesor_sig.png',
            'tanggal_ttd_asesor' => now(),
            'status' => 'selesai',
        ]);

        // Pendaftaran asesi yang baru selesai APL.02, bukti_dikumpulkan masih null di database
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-TEST-001',
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
            'bukti_dikumpulkan' => null,
            'tuk_type' => null,
        ]);

        // Asesi membuka tahapan step=3
        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', [
            'step' => 3,
            'pendaftaran_id' => $pendaftaran->id,
        ]));

        $response->assertStatus(200);

        // Checkbox Observasi Praktik Demonstrasi Kerja harus tercentang (checked)
        $response->assertSee('value="Observasi Praktik Demonstrasi"', false);
        $response->assertSee('checked', false);

        // Checkbox Tanya Jawab Lisan harus tercentang (checked)
        $response->assertSee('value="Tanya Jawab Lisan"', false);

        // Checkbox Uji Tertulis Online TIDAK boleh tercentang karena tidak ada di Master AK.01 skema ini
        $this->assertStringNotContainsString('value="Uji Tertulis (CBT)"' . "\n" . '                                               checked', $response->getContent());

        // Verifikasi pendaftaran otomatis tersinkronisasi di database
        $pendaftaran->refresh();
        $this->assertEquals('Sewaktu', $pendaftaran->tuk_type);
        $this->assertEquals(['Observasi Praktik Demonstrasi', 'Tanya Jawab Lisan'], $pendaftaran->bukti_dikumpulkan);
        $this->assertEquals('signatures/asesor_sig.png', $pendaftaran->tanda_tangan_asesor_ak01);
    }

    /** 2. Jika Master AK.01 belum dibuat, tahapan step 3 mengambil default metode sesuai instrumen skema di database */
    public function test_asesi_tahapan_step3_fallbacks_to_scheme_instruments_when_master_ak01_not_created(): void
    {
        // Skema tanpa MasterAk01
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-TEST-002',
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
            'bukti_dikumpulkan' => null,
            'tuk_type' => null,
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', [
            'step' => 3,
            'pendaftaran_id' => $pendaftaran->id,
        ]));

        $response->assertStatus(200);

        // Data tidak boleh kosong, otomatis terisi dari instrumen default skema
        $pendaftaran->refresh();
        $this->assertNotEmpty($pendaftaran->bukti_dikumpulkan);
        $this->assertEquals('Sewaktu', $pendaftaran->tuk_type);
    }
}
