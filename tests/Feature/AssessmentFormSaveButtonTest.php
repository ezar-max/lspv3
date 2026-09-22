<?php

namespace Tests\Feature;

use App\Models\IaPenilaian;
use App\Models\JadwalAsesmen;
use App\Models\MasterQuestionBank;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentFormSaveButtonTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected JadwalAsesmen $jadwal;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji Ujian',
            'email' => 'asesor.penguji@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.777.UJI',
            'tanda_tangan' => 'signatures/asesor_uji.png'
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Peserta Ujian',
            'email' => 'asesi.peserta@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-SAVE-01',
            'nama_skema' => 'Skema Uji Simpan Jawaban Form',
            'jenis_skema' => 'KKNI',
            'status' => 'aktif',
        ]);

        $unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'TIK.TEST.001.01',
            'judul_unit' => 'Unit Uji Form Jawaban',
            'standar_kompetensi' => 'SKKNI'
        ]);

        // Instrumen CBT
        $instCbt = SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia_05',
            'title' => 'Pertanyaan Tertulis Pilihan Ganda',
            'is_active' => true,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instCbt->id,
            'skema_id' => $this->skema->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Apa kegunaan tombol simpan pada CBT?',
            'options' => ['A' => 'Menghapus jawaban', 'B' => 'Menyimpan jawaban ke database', 'C' => 'Membatalkan ujian', 'D' => 'Keluar aplikasi'],
            'correct_answer' => 'B',
            'order' => 1,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instCbt->id,
            'skema_id' => $this->skema->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Berapa jumlah opsi jawaban yang benar pada butir ini?',
            'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'],
            'correct_answer' => 'A',
            'order' => 2,
        ]);

        // Instrumen Esai
        $instEsai = SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia_06',
            'title' => 'Pertanyaan Tertulis Esai',
            'is_active' => true,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instEsai->id,
            'skema_id' => $this->skema->id,
            'question_type' => 'essay',
            'question_text' => 'Jelaskan prosedur pengujian sistem yang baik!',
            'correct_answer' => 'Pengujian unit, integrasi, dan end-to-end.',
            'order' => 1,
        ]);

        // Instrumen Praktik
        SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia_02',
            'title' => 'Tugas Praktik Demonstrasi',
            'is_active' => true,
            'additional_metadata' => [
                'judul_tugas' => 'Praktik Uji Simpan Form',
                'skenario' => 'Lakukan pengujian form asesmen.',
                'instruksi_kerja' => ['Buka aplikasi', 'Klik simpan'],
                'peralatan_bahan' => ['Komputer', 'Web Browser']
            ]
        ]);

        // Jadwal aktif saat ini
        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'kode_jadwal' => 'JDW-TEST-SAVE-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => now()->subHour()->format('H:i'),
            'waktu_selesai' => now()->addHours(2)->format('H:i'),
            'status_jadwal' => 'berlangsung',
            'kuota' => 25,
            'nama_tuk' => 'TUK Mandiri Lab Komputer',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-TEST-SAVE-001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diterima',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'disetujui',
            'status_ak01' => 'selesai',
            'status_mapa01' => 'selesai',
            'status_mapa02' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_ak01.png',
            'tanda_tangan_asesor_ak01' => 'signatures/asesor_ak01.png',
            'tanggal_ttd_asesi_ak01' => now(),
            'tanggal_ttd_asesor_ak01' => now(),
        ]);

        \App\Models\Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'status_mapa' => 'selesai',
            'tanda_tangan_asesor' => 'signatures/asesor_mapa01.png'
        ]);

        \App\Models\Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'matriks_peta' => [$unit->id => [1 => [1 => ['dpe' => 1, 'dpt' => 1]]]],
            'status_mapa' => 'selesai',
            'tanda_tangan_asesor' => 'signatures/asesor_mapa02.png'
        ]);

        \App\Models\AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaran->id,
            'status' => 'confirmed',
            'asesor_signature' => 'signatures/asesor_ak07.png',
            'asesor_signed_at' => now(),
            'asesi_signature' => 'signatures/asesi_ak07.png',
            'asesi_signed_at' => now(),
        ]);
    }

    public function test_simpan_jawaban_cbt_per_nomor_berhasil()
    {
        $response = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'B'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tipe' => 'cbt',
                'no' => 1,
                'jawaban' => 'B',
                'total_terjawab' => 1
            ]);

        $saved = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.05')
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals('B', $saved->data_jawaban['jawaban_pg'][1]);
    }

    public function test_simpan_seluruh_jawaban_cbt_sekaligus_berhasil()
    {
        $response = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'jawaban_pg' => [
                1 => 'B',
                2 => 'A'
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tipe' => 'cbt',
                'total_terjawab' => 2
            ]);

        $saved = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.05')
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals('B', $saved->data_jawaban['jawaban_pg'][1]);
        $this->assertEquals('A', $saved->data_jawaban['jawaban_pg'][2]);
    }

    public function test_simpan_jawaban_esai_per_nomor_berhasil()
    {
        $response = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'esai',
            'no' => 1,
            'jawaban' => 'Prosedur pengujian mencakup verifikasi fungsional dan non fungsional.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tipe' => 'esai',
                'no' => 1,
                'total_terjawab' => 1
            ]);

        $saved = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.06')
            ->first();

        $this->assertNotNull($saved);
        $this->assertStringContainsString('verifikasi fungsional', $saved->data_jawaban['jawaban_esai'][1]);
    }

    public function test_simpan_seluruh_jawaban_esai_sekaligus_berhasil()
    {
        $response = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'esai',
            'jawaban_esai' => [
                1 => 'Jawaban esai nomor 1 tersimpan lengkap.',
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tipe' => 'esai',
                'total_terjawab' => 1
            ]);

        $saved = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.06')
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals('Jawaban esai nomor 1 tersimpan lengkap.', $saved->data_jawaban['jawaban_esai'][1]);
    }

    public function test_simpan_catatan_praktik_berhasil()
    {
        $response = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'praktik_catatan',
            'catatan' => 'Pelaksanaan demonstrasi telah diselesaikan sesuai standar KUK.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tipe' => 'praktik'
            ]);

        $saved = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.02')
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals('Pelaksanaan demonstrasi telah diselesaikan sesuai standar KUK.', $saved->data_jawaban['catatan_praktik']);
    }

    public function test_tombol_simpan_terender_di_tampilan_tahapan_formulir_asesmen()
    {
        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'step' => 5
        ]));

        $response->assertStatus(200);

        // Memastikan tombol simpan dan method-methodnya ada di Blade
        $response->assertSee('simpanJawabanPg(currentPgNo', false);
        $response->assertSee('simpanSemuaCbt()', false);
        $response->assertSee('Simpan Seluruh Jawaban CBT', false);
        $response->assertSee('simpanJawabanEsai(', false);
        $response->assertSee('simpanSemuaEsai()', false);
        $response->assertSee('Simpan Seluruh Jawaban Esai', false);
        $response->assertSee('simpanCatatanPraktikManual()', false);
        $response->assertSee('Simpan Catatan Praktik', false);
    }

    public function test_autosave_cbt_dan_esai_ditolak_jika_ujian_sudah_dikumpulkan()
    {
        // Tandai ujian sebagai sudah disubmit
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'user_id' => $this->asesi->id,
            'kode_formulir' => 'FR.IA.05',
            'role' => 'asesi',
            'status' => 'submitted',
            'data_jawaban' => ['jawaban_pg' => [1 => 'B', 2 => 'A']]
        ]);

        // Coba autosave CBT nomor 1
        $resCbt = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'C'
        ]);

        $resCbt->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ujian telah dikumpulkan dan tidak dapat diisi atau diubah lagi.'
            ]);

        // Coba autosave esai
        $resEsai = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'esai',
            'no' => 1,
            'jawaban' => 'Ubah jawaban esai setelah dikumpulkan'
        ]);

        $resEsai->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ujian telah dikumpulkan dan tidak dapat diisi atau diubah lagi.'
            ]);

        // Coba autosave catatan praktik
        $resPraktik = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'praktik_catatan',
            'catatan' => 'Ubah catatan praktik'
        ]);

        $resPraktik->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ujian telah dikumpulkan dan tidak dapat diisi atau diubah lagi.'
            ]);
    }

    public function test_upload_ia02_ditolak_jika_ujian_sudah_dikumpulkan()
    {
        // Tandai ujian sebagai sudah disubmit
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'user_id' => $this->asesi->id,
            'kode_formulir' => 'FR.IA.05',
            'role' => 'asesi',
            'status' => 'submitted',
            'data_jawaban' => ['jawaban_pg' => [1 => 'B']]
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->asesi)->post(route('asesi.ujian.upload-ia02'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'file_praktik' => $file,
            'catatan_praktik' => 'Coba upload file lagi'
        ]);

        $response->assertSessionHas('error', 'Ujian telah dikumpulkan dan berkas praktik tidak dapat diubah lagi.');
    }

    public function test_submit_ulang_ujian_ditolak_jika_sudah_dikumpulkan()
    {
        // Tandai ujian sebagai sudah disubmit
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'user_id' => $this->asesi->id,
            'kode_formulir' => 'FR.IA.05',
            'role' => 'asesi',
            'status' => 'submitted',
            'data_jawaban' => ['jawaban_pg' => [1 => 'B']]
        ]);

        $response = $this->actingAs($this->asesi)->post(route('asesi.ujian.submit'), [
            'pendaftaran_id' => $this->pendaftaran->id
        ]);

        $response->assertRedirect(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaran->id]))
            ->assertSessionHas('info', 'Ujian Anda telah dikumpulkan sebelumnya dan lembar jawaban telah dikunci.');
    }

    public function test_tampilan_terkunci_dan_readonly_setelah_ujian_dikumpulkan()
    {
        // Tandai ujian sebagai sudah disubmit
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'user_id' => $this->asesi->id,
            'kode_formulir' => 'FR.IA.05',
            'role' => 'asesi',
            'status' => 'submitted',
            'data_jawaban' => ['jawaban_pg' => [1 => 'B', 2 => 'A']]
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.tahapan', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'step' => 5
        ]));

        $response->assertStatus(200);

        // Memastikan banner notifikasi terkunci muncul
        $response->assertSee('Ujian Telah Dikumpulkan & Terkunci', false);
        $response->assertSee('Jawaban Terkunci', false);
        $response->assertSee('isReadonly: true', false);
        $response->assertSee('isSubmitted: true', false);

        // Tombol simpan dan kumpulkan tidak boleh aktif
        $response->assertDontSee('Simpan Seluruh Jawaban CBT', false);
        $response->assertDontSee('Simpan Seluruh Jawaban Esai', false);
        $response->assertDontSee('Kumpulkan Jawaban', false);
    }
}
