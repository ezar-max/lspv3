<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\PendaftaranAsesi;
use App\Models\Mapa01;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapa01AutoValidationTest extends TestCase
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
            'nama_lengkap' => 'Admin LSP Utama',
            'email' => 'admin.utama@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'tanda_tangan' => 'signatures/admin_utama.png',
            'nomor_registrasi' => 'ADM.001.2026',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji Kompeten',
            'email' => 'asesor.kompeten@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'tanda_tangan' => 'signatures/asesor_kompeten.png',
            'nomor_registrasi' => 'MET.000.999999 2026',
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
    }

    /**
     * 1. Admin yang membuka formulir MAPA-01 tidak perlu menggambar TTD asesor
     * (tombol kanvas modal TTD asesor tidak muncul, digantikan status otomatis terverifikasi).
     */
    public function test_admin_sees_auto_verified_asesor_and_no_canvas_button_in_mapa01(): void
    {
        $response = $this->actingAs($this->admin)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);

        // Tidak boleh ada tombol buka modal canvas untuk tanda tangan asesor bagi admin
        $response->assertDontSee("bukaModal('modalCanvasTtd')", false);
        $response->assertDontSee('Bubuhkan Tanda Tangan');

        // Harus menampilkan badge bahwa TTD asesor terverifikasi otomatis
        $response->assertSee('Terverifikasi Otomatis');

        // Harus menampilkan tombol Sahkan & Validasi FR.MAPA.01
        $response->assertSee('Sahkan &amp; Validasi FR.MAPA.01', false);
    }

    /**
     * 2. Admin menyimpan Master MAPA-01 langsung terisi TTD asesor dan langsung tervalidasi.
     */
    public function test_admin_saving_master_mapa01_auto_populates_asesor_signature_and_validates_directly(): void
    {
        $response = $this->actingAs($this->admin)->post(route('asesor.skema.mapa-01.simpan', $this->skema->id), [
            'aksi' => 'konfirmasi',
            'tujuan_asesmen' => 'Sertifikasi',
            'konteks_lingkungan' => 'Tempat kerja simulasi',
        ]);

        $response->assertRedirect(route('asesor.skema.mapa-02', $this->skema->id));

        $master = Mapa01::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($master);

        // TTD Asesor terisi otomatis
        $this->assertNotEmpty($master->tanda_tangan_asesor);
        $this->assertNotNull($master->tanggal_ttd_asesor);

        // Baris Validator Admin langsung terisi dan statusnya tervalidasi
        $tabel = $master->penyusun_validator_tabel;
        $this->assertIsArray($tabel);
        $this->assertEquals('tervalidasi', $tabel['validator_1']['status_validasi'] ?? null);
        $this->assertEquals($this->admin->nama_lengkap, $tabel['validator_1']['nama'] ?? null);
        $this->assertNotEmpty($tabel['validator_1']['ttd'] ?? null);
    }

    /**
     * 3. Admin menyimpan MAPA-01 peserta langsung tervalidasi dan memperbarui pendaftaran asesi.
     */
    public function test_admin_saving_candidate_mapa01_auto_validates_and_updates_pendaftaran(): void
    {
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Peserta Uji Satu',
            'email' => 'peserta1@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-TEST-001',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diterima',
            'tujuan_asesmen' => 'Sertifikasi',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('asesor.mapa-01.simpan', $pendaftaran->id), [
            'aksi' => 'konfirmasi',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $response->assertRedirect(route('asesor.mapa-02', $pendaftaran->id));

        $mapa01 = Mapa01::where('pendaftaran_id', $pendaftaran->id)->first();
        $this->assertNotNull($mapa01);

        // TTD Asesor otomatis terisi
        $this->assertNotEmpty($mapa01->tanda_tangan_asesor);

        // Validator langsung tervalidasi
        $tabel = $mapa01->penyusun_validator_tabel;
        $this->assertEquals('tervalidasi', $tabel['validator_1']['status_validasi'] ?? null);
        $this->assertEquals($this->admin->nama_lengkap, $tabel['validator_1']['nama'] ?? null);

        // PendaftaranAsesi juga langsung terupdate tanda tangan admin
        $pendaftaranFresh = $pendaftaran->fresh();
        $this->assertNotEmpty($pendaftaranFresh->tanda_tangan_admin);
        $this->assertNotNull($pendaftaranFresh->tanggal_ttd_admin);
    }

    /**
     * 4. Asesor tanpa tanda tangan profil tetap melihat tombol manual canvas dan tidak otomatis divalidasi admin.
     */
    public function test_asesor_still_sees_canvas_button_and_status_waiting_for_admin(): void
    {
        $asesorPolos = Pengguna::create([
            'nama_lengkap' => 'Asesor Baru Tanpa TTD',
            'email' => 'asesor.polos@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'tanda_tangan' => null,
            'nomor_registrasi' => 'MET.000.888888 2026',
        ]);

        $response = $this->actingAs($asesorPolos)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);

        // Asesor melihat tombol modal canvas untuk membubuhkan tanda tangannya
        $response->assertSee("bukaModal('modalCanvasTtd')", false);
        $response->assertSee('Bubuhkan Tanda Tangan');

        // Saat asesor menyimpan draft, status validator tidak otomatis menjadi tervalidasi
        $this->actingAs($asesorPolos)->post(route('asesor.skema.mapa-01.simpan', $this->skema->id), [
            'aksi' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $master = Mapa01::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($master);
        $tabel = $master->penyusun_validator_tabel;
        $this->assertNotEquals('tervalidasi', $tabel['validator_1']['status_validasi'] ?? null);
    }

    /**
     * 5. Jika FR.MAPA.01 skema sudah divalidasi, halaman detail verifikasi APL menampilkan tervalidasi
     * dan formulir peserta tidak dapat / tidak perlu divalidasi ulang.
     */
    public function test_candidate_inherits_scheme_validation_and_cannot_be_revalidated(): void
    {
        // 1. Admin memvalidasi Master MAPA 01 untuk skema
        $this->actingAs($this->admin)->post(route('admin.mapa-01.validasi', $this->skema->id), [
            'skema_id' => $this->skema->id,
            'validator_nama' => $this->admin->nama_lengkap,
            'validator_nomor_met' => $this->admin->nomor_registrasi,
        ]);

        $master = Mapa01::where('skema_id', $this->skema->id)->whereNull('pendaftaran_id')->first();
        $this->assertNotNull($master);
        $this->assertEquals('tervalidasi', $master->penyusun_validator_tabel['validator_1']['status_validasi'] ?? null);

        // 2. Ada pendaftaran peserta pada skema tersebut
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Peserta Uji Dua',
            'email' => 'peserta2@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-TEST-002',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diajukan',
            'tujuan_asesmen' => 'Sertifikasi',
            'tanggal_daftar' => now(),
        ]);

        // 3. Di halaman detail verifikasi berkas APL (ACC APL), status MAPA 01 langsung "✓ Tervalidasi Admin"
        $resDetail = $this->actingAs($this->admin)->get(route('admin.detail-verifikasi', $pendaftaran->id));
        $resDetail->assertStatus(200);
        $resDetail->assertSee('✓ Tervalidasi Admin');
        $resDetail->assertDontSee('Menunggu Validasi Admin');
        $resDetail->assertSee('Lihat Dokumen FR.MAPA.01');
        $resDetail->assertDontSee('Validasi FR.MAPA.01 &rarr;', false);

        // 4. Di formulir peserta, tombol "Validasi & Sahkan" tidak muncul lagi, digantikan status tervalidasi via master skema
        $resForm = $this->actingAs($this->admin)->get(route('asesor.mapa-01', $pendaftaran->id));
        $resForm->assertStatus(200);
        $resForm->assertSee('Tervalidasi Resmi (Master Skema)');
        $resForm->assertDontSee('Validasi &amp; Sahkan FR.MAPA.01', false);

        // 5. Mencoba memvalidasi ulang peserta yang skema-nya sudah tervalidasi tidak diizinkan / dinformasikan sudah valid
        $resReval = $this->actingAs($this->admin)->post(route('admin.mapa-01.validasi', $pendaftaran->id), []);
        $resReval->assertSessionHas('info', 'Dokumen FR.MAPA.01 untuk skema sertifikasi ini sudah berstatus tervalidasi melalui Master Skema.');
    }
}
