<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\PendaftaranAsesi;
use App\Models\Mapa01;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapa01ValidatorDatabaseAccountTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Drs. H. Bambang Hermanto, M.Pd.',
            'email' => 'admin.bambang@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'nomor_registrasi' => 'REG.ADM.2026.0099',
            'aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Fredi Wahyu ezar',
            'email' => 'fredi.asesor@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.092673723627.1092',
            'aktif' => true,
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Peserta Uji Satu',
            'email' => 'peserta1@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-001',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
            'status_aktif' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'TIK.RPL.001',
            'judul_unit' => 'Mengembangkan Antarmuka Web',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nomor_pendaftaran' => 'REG-2026-0001',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'disetujui',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'disetujui',
        ]);
    }

    /**
     * Test asesor melihat nama validator dan nomor registrasi terisi otomatis dari akun admin di database.
     */
    public function test_asesor_sees_admin_validator_name_and_reg_number_prefilled_from_database(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.mapa-01', $this->pendaftaran->id));
        $response->assertStatus(200);

        // Nilai input validator harus mengambil nama dan nomor_registrasi dari akun admin di database
        $response->assertSee('value="Drs. H. Bambang Hermanto, M.Pd."', false);
        $response->assertSee('value="REG.ADM.2026.0099"', false);
    }

    /**
     * Test master MAPA-01 skema juga mengambil nama validator dan nomor registrasi dari akun admin di database.
     */
    public function test_master_mapa01_sees_admin_validator_prefilled_from_database(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);

        $response->assertSee('value="Drs. H. Bambang Hermanto, M.Pd."', false);
        $response->assertSee('value="REG.ADM.2026.0099"', false);
    }

    /**
     * Test modal validasi admin juga terisi otomatis dari akun admin.
     */
    public function test_admin_validation_modal_prefills_with_admin_database_account(): void
    {
        $response = $this->actingAs($this->admin)->get(route('asesor.mapa-01', $this->pendaftaran->id));
        $response->assertStatus(200);

        $response->assertSee('value="Drs. H. Bambang Hermanto, M.Pd."', false);
        $response->assertSee('value="REG.ADM.2026.0099"', false);
    }

    /**
     * Test asesor menyimpan MAPA-01 tetap menyimpan identitas validator dari database akun admin.
     */
    public function test_asesor_saving_mapa01_persists_admin_validator_from_database(): void
    {
        $payload = [
            'pendekatan_asesi' => ['Hasil pelatihan dan / atau pendidikan:'],
            'tujuan_asesmen' => 'Sertifikasi',
            'penyusun_validator_tabel' => [
                'penyusun_1' => [
                    'nama' => 'Fredi Wahyu ezar',
                    'nomor_met' => 'MET.092673723627.1092',
                ],
                'validator_1' => [
                    'nama' => '', // kosong, harus otomatis diambil dari database akun admin
                    'nomor_met' => '',
                ]
            ],
            'is_confirm' => '0',
        ];

        $response = $this->actingAs($this->asesor)->post(route('asesor.mapa-01.simpan', $this->pendaftaran->id), $payload);
        $response->assertStatus(302);

        $mapa01 = Mapa01::where('pendaftaran_id', $this->pendaftaran->id)->first();
        $this->assertNotNull($mapa01);
        $this->assertEquals('Drs. H. Bambang Hermanto, M.Pd.', $mapa01->penyusun_validator_tabel['validator_1']['nama']);
        $this->assertEquals('REG.ADM.2026.0099', $mapa01->penyusun_validator_tabel['validator_1']['nomor_met']);
    }
}

