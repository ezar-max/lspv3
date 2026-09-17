<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\Mapa01;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapa01UnfilledTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Test LSP',
            'email' => 'admin.test@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'tanda_tangan' => 'signatures/admin_test.png',
            'nomor_registrasi' => 'ADM.TEST.001',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-001',
            'nama_skema' => 'Skema Sertifikasi Uji Coba Unfilled',
            'status_aktif' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'TIK.TEST.001',
            'judul_unit' => 'Menerapkan Pengujian Sistem Informasi',
        ]);

        \App\Models\ElemenKompetensi::create([
            'unit_id' => $this->unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Melakukan Verifikasi',
        ]);
    }

    /**
     * Pastikan FR.MAPA.01 pada bagian admin / formulir TIDAK terisi otomatis saat belum diisi
     */
    public function test_admin_sees_unfilled_mapa01_when_not_yet_configured(): void
    {
        // Pastikan belum ada record mapa_01 di database
        $this->assertEquals(0, Mapa01::count());

        // 1. Akses halaman master MAPA-01 skema sebagai Admin
        $response = $this->actingAs($this->admin)->get(route('asesor.skema.mapa-01', $this->skema->id));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 2. Tombol validasi admin harus menampilkan aksi validasi baru, BUKAN status sudah tervalidasi
        $response->assertSee('Validasi &amp; Sahkan FR.MAPA.01', false);
        $response->assertDontSee('Tervalidasi Admin');
        $response->assertDontSee('Perbarui Validasi Admin');
        $response->assertSee('Validasi & TTD', false);

        // 3. Matriks bukti tidak boleh mengandung teks otomatis dummy
        $response->assertDontSee('Bukti hasil demonstrasi praktik langsung unjuk kerja dan portofolio unit ' . $this->unit->judul_unit);

        // 4. Pastikan tidak ada checkbox unit matriks yang tercentang otomatis
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][l]" value="1" checked', $content);
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][t]" value="1" checked', $content);
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][methods][]" value="CL" checked', $content);
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][methods][]" value="DPT" checked', $content);
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][methods][]" value="PW" checked', $content);

        // 5. Pastikan radio tujuan_asesmen "Sertifikasi" tidak tercentang otomatis
        $this->assertStringNotContainsString('name="tujuan_asesmen" value="Sertifikasi" checked', $content);

        // 6. Pastikan radio konteks_lingkungan tidak tercentang otomatis
        $this->assertStringNotContainsString('name="konteks_lingkungan" value="Tempat kerja simulasi" checked', $content);
        $this->assertStringNotContainsString('name="konteks_lingkungan" value="Tempat kerja nyata" checked', $content);

        // 7. Pastikan radio konteks_peluang_bukti tidak tercentang otomatis
        $this->assertStringNotContainsString('name="konteks_peluang_bukti" value="Tersedia" checked', $content);

        // 8. Pastikan radio hubungan_standar tidak tercentang otomatis (senang)
        $this->assertStringNotContainsString('name="hubungan_standar_bukti" value="senang" checked', $content);
        $this->assertStringNotContainsString('name="hubungan_standar_aktivitas" value="senang" checked', $content);
        $this->assertStringNotContainsString('name="hubungan_standar_pembelajaran" value="senang" checked', $content);

        // 9. Pastikan checkbox pendekatan_asesi[] dan pelaksana_asesmen[] tidak tercentang otomatis
        $this->assertStringNotContainsString('name="pelaksana_asesmen[]" value="Lembaga Sertifikasi" checked', $content);
        $this->assertStringNotContainsString('name="konfirmasi_orang_relevan[]" value="Manajer sertifikasi LSP" checked', $content);
        $this->assertStringNotContainsString('name="standar_industri[]" value="Standar Kompetensi:" checked', $content);
    }

    /**
     * Pastikan FR.MAPA.01 via route formulir.mapa01 juga bersih saat belum diisi
     */
    public function test_admin_sees_unfilled_formulir_mapa01_blanko(): void
    {
        $response = $this->actingAs($this->admin)->get(route('formulir.mapa01', ['skema_id' => $this->skema->id]));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Tidak boleh ada teks bukti dummy otomatis
        $response->assertDontSee('Bukti hasil demonstrasi praktik langsung unjuk kerja');

        // Checkbox matriks unit tidak boleh tercentang
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][l]" value="1" checked', $content);
        $this->assertStringNotContainsString('name="rencana_unit_matriks[' . $this->unit->id . '][methods][]" value="CL" checked', $content);

        // Radio tujuan asesmen tidak boleh tercentang
        $this->assertStringNotContainsString('name="tujuan_asesmen" value="Sertifikasi" checked', $content);
    }
}
