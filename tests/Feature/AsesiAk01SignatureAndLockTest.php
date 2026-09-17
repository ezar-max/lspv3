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

class AsesiAk01SignatureAndLockTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesor;
    private Pengguna $asesi;
    private SkemaSertifikasi $skema;
    private PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-002',
            'nama_skema' => 'Rekayasa Perangkat Lunak Lanjutan',
            'status_aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji',
            'email' => 'asesor.penguji@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'tanda_tangan' => 'signatures/asesor_profil.png',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Peserta Uji',
            'email' => 'asesi.peserta@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'tanda_tangan' => 'signatures/asesi_profil_tersimpan.png',
        ]);

        $unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.002.01',
            'judul_unit' => 'Menerapkan Algoritma Pemrograman',
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_id' => $unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menentukan Struktur Data',
        ]);

        KriteriaUnjukKerja::create([
            'elemen_id' => $elemen->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Struktur data diidentifikasi sesuai spesifikasi',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nomor_pendaftaran' => 'REG-AK01-001',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diterima',
            'status_apl02' => 'approved',
            'status_ak01' => 'belum',
            'tuk_type' => 'Sewaktu',
            'bukti_dikumpulkan' => ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)'],
            'bukti_dikumpulkan_lainnya' => 'Catatan Tambahan Asesor',
        ]);
    }

    /** 1. Halaman AK.01 menampilkan form read-only (tidak dapat diisi/diubah asesi) */
    public function test_ak01_page_renders_readonly_form_fields_for_asesi(): void
    {
        $response = $this->actingAs($this->asesi)->get(route('asesi.ak01', ['id' => $this->pendaftaran->id]));

        $response->assertStatus(200);

        // Memastikan terdapat indikator Ditetapkan Asesor / LSP
        $response->assertSee('Ditetapkan Asesor / LSP');

        // Memastikan select TUK disabled dan checkbox bukti disabled
        $content = $response->getContent();
        $this->assertStringContainsString('name="tuk_type"', $content);
        $this->assertStringContainsString('disabled', $content);
    }

    /** 2. Tanda tangan asesi otomatis terisi dari profil/pendaftaran dan dapat langsung disetujui */
    public function test_ak01_autofills_existing_signature_from_profile_and_allows_direct_submission(): void
    {
        $response = $this->actingAs($this->asesi)->get(route('asesi.ak01', ['id' => $this->pendaftaran->id]));

        $response->assertStatus(200);

        // Memastikan tanda tangan yang tersimpan di profil otomatis diisi
        $response->assertSee('Otomatis Terisi');
        $response->assertSee('signatures/asesi_profil_tersimpan.png');
        $response->assertSee('Ubah Tanda Tangan');

        // Asesi menyetujui menggunakan tanda tangan yang otomatis terisi
        $submitResponse = $this->actingAs($this->asesi)->post(route('asesi.ak01.sign', ['id' => $this->pendaftaran->id]), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_profil_tersimpan.png',
        ]);

        $submitResponse->assertRedirect(route('asesi.dashboard', ['pendaftaran_id' => $this->pendaftaran->id]));

        $this->pendaftaran->refresh();
        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertEquals('signatures/asesi_profil_tersimpan.png', $this->pendaftaran->tanda_tangan_asesi_ak01);
        $this->assertNotNull($this->pendaftaran->tanggal_ttd_asesi_ak01);
    }

    /** 3. Asesi dapat mengedit tanda tangannya dengan tanda tangan baru */
    public function test_ak01_allows_asesi_to_edit_signature_with_new_canvas_data(): void
    {
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $submitResponse = $this->actingAs($this->asesi)->post(route('asesi.ak01.sign', ['id' => $this->pendaftaran->id]), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => $dummySignature,
        ]);

        $submitResponse->assertRedirect(route('asesi.dashboard', ['pendaftaran_id' => $this->pendaftaran->id]));

        $this->pendaftaran->refresh();
        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertStringStartsWith('storage/signatures/ak01_asesi_', $this->pendaftaran->tanda_tangan_asesi_ak01);
    }

    /** 4. Klausul persetujuan ditampilkan dan tombol submit aktif untuk mengirim formulir */
    public function test_ak01_page_renders_agreement_clause_and_active_submit_button(): void
    {
        $response = $this->actingAs($this->asesi)->get(route('asesi.ak01', ['id' => $this->pendaftaran->id]));

        $response->assertStatus(200);

        // Memastikan klausul persetujuan ditampilkan
        $response->assertSee('Saya telah membaca, memahami, dan menyetujui seluruh klausul persetujuan serta komitmen kerahasiaan');
        $content = $response->getContent();
        $this->assertStringContainsString('x-model="agreedToClause"', $content);

        // Memastikan tombol submit formulir ditampilkan
        $response->assertSee('Kirim & Setujui FR.AK.01', false);
    }

    /** 5. Jika asesor sudah tanda tangan lebih dulu, saat asesi mengirim AK.01 status menjadi selesai */
    public function test_ak01_submission_completes_flow_when_asesor_already_signed(): void
    {
        $this->pendaftaran->update([
            'tanda_tangan_asesor_ak01' => 'signatures/asesor_signed.png',
            'tanggal_ttd_asesor_ak01' => now(),
            'status_ak01' => 'disetujui_asesor',
        ]);

        $submitResponse = $this->actingAs($this->asesi)->post(route('asesi.ak01.sign', ['id' => $this->pendaftaran->id]), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_profil_tersimpan.png',
        ]);

        $submitResponse->assertRedirect(route('asesi.dashboard', ['pendaftaran_id' => $this->pendaftaran->id]));

        $this->pendaftaran->refresh();
        $this->assertEquals('selesai', $this->pendaftaran->status_ak01);
        $this->assertEquals('signatures/asesi_profil_tersimpan.png', $this->pendaftaran->tanda_tangan_asesi_ak01);
        $this->assertNotNull($this->pendaftaran->tanggal_ttd_asesi_ak01);
    }
}
