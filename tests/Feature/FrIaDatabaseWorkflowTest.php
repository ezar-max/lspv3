<?php

namespace Tests\Feature;

use App\Models\IaPenilaian;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\MasterQuestionBank;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrIaDatabaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Penguji',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.123456.2023',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEST-01',
            'nama_skema' => 'Skema Agribisnis Ternak Unggas',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $unit = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'AGR.001',
            'judul_unit' => 'Manajemen Pakan Unggas',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $elemen = $unit->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menyiapkan Ransum',
            'pertanyaan_elemen' => 'Apakah mampu menyiapkan ransum?',
        ]);

        $elemen->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Pakan disiapkan sesuai takaran nutrisi',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'asesor_id' => $this->asesor->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-TEST',
            'status' => 'asesmen_selesai',
            'tuk_type' => 'Sewaktu',
            'tanggal_daftar' => now(),
        ]);
    }

    /**
     * Test: Saving FR.IA.02 by Asesor stores data in candidate's IaPenilaian AND SchemeMasterInstrument.
     */
    public function test_asesor_can_save_fr_ia_02_and_it_syncs_to_scheme_master()
    {
        $payload = [
            'judul_tugas' => 'Praktik Formulasi Pakan Mandiri',
            'durasi_waktu' => '120 Menit',
            'skenario' => 'Peserta diminta meracik ransum pakan ayam petelur fase layer.',
            'peralatan_bahan' => 'Timbangan analitik, Jagung giling, Dedak padi, Konsentrat',
            'instruksi_kerja' => "1. Timbang bahan sesuai formulasi.\n2. Lakukan homogenisasi.\n3. Catat hasil racikan.",
        ];

        $response = $this->actingAs($this->asesor)
            ->post(route('formulir.ia.simpan', [
                'kodeForm' => 'FR.IA.02',
                'pendaftaranId' => $this->pendaftaran->id,
            ]), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 1. Check IaPenilaian
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.02',
        ]);

        $iaRecord = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.02')
            ->first();

        $this->assertEquals('Praktik Formulasi Pakan Mandiri', $iaRecord->data_jawaban['judul_tugas']);
        $this->assertEquals('120 Menit', $iaRecord->data_jawaban['durasi_waktu']);

        // 2. Check SchemeMasterInstrument sync
        $master = SchemeMasterInstrument::where('skema_id', $this->skema->id)
            ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_02'))
            ->first();

        $this->assertNotNull($master);
        $this->assertEquals('Praktik Formulasi Pakan Mandiri', $master->title);
        $this->assertEquals(120, $master->time_limit_minutes);
        $this->assertEquals('Peserta diminta meracik ransum pakan ayam petelur fase layer.', $master->additional_metadata['skenario']);
    }

    /**
     * Test: Asesi views FR.IA.02 and receives the database saved content instead of hardcoded IT mock.
     */
    public function test_asesi_receives_database_saved_fr_ia_02_data()
    {
        // Pre-save FR.IA.02 in DB
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.02',
            'user_id' => $this->asesor->id,
            'role' => 'asesor',
            'status' => 'completed',
            'data_jawaban' => [
                'judul_tugas' => 'Uji Mutu Fisik Bahan Pakan',
                'durasi_waktu' => '90 Menit',
                'skenario' => 'Uji organoleptik pakan ternak unggas.',
                'peralatan_bahan' => 'Cawan petri, Sampel jagung, Kaca pembesar',
                'instruksi_kerja' => 'Periksa kadar air dan bau jamur.',
            ],
        ]);

        $response = $this->actingAs($this->asesi)
            ->get(route('formulir.ia02', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('Uji Mutu Fisik Bahan Pakan');
        $response->assertSee('90 Menit');
        $response->assertSee('Uji organoleptik pakan ternak unggas.');
        $response->assertSee('Cawan petri, Sampel jagung, Kaca pembesar');
        // Ensure no dummy IT text is rendered
        $response->assertDontSee('Komputer/Laptop dengan spesifikasi minimum');
    }

    /**
     * Test: When no questions exist in DB for CBT and Esai, empty state is returned (no fake questions).
     */
    public function test_cbt_and_esai_strictly_load_from_database_and_return_empty_when_not_configured()
    {
        $cbtQuestions = \App\Http\Controllers\FormulirController::getSoalIa05($this->skema->id);
        $esaiQuestions = \App\Http\Controllers\FormulirController::getSoalIa06($this->skema->id);
        $lisanQuestions = \App\Http\Controllers\FormulirController::getSoalIa07($this->skema->id);

        $this->assertIsArray($cbtQuestions);
        $this->assertEmpty($cbtQuestions);

        $this->assertIsArray($esaiQuestions);
        $this->assertEmpty($esaiQuestions);

        $this->assertIsArray($lisanQuestions);
        $this->assertEmpty($lisanQuestions);
    }

    /**
     * Test: When questions exist in DB for Scheme, they are properly retrieved.
     */
    public function test_cbt_and_esai_return_questions_when_seeded_in_scheme_master()
    {
        $inst = SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'FR.IA.05',
            'title' => 'Ujian Pilihan Ganda Pakan',
            'is_active' => true,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $inst->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Berapa kadar protein kasar standar pada ransum layer?',
            'options' => ['A' => '10%', 'B' => '17%', 'C' => '25%', 'D' => '30%'],
            'correct_answer' => 'B',
            'order' => 1,
        ]);

        $cbtQuestions = \App\Http\Controllers\FormulirController::getSoalIa05($this->skema->id);
        $this->assertCount(1, $cbtQuestions);
        $this->assertEquals('Berapa kadar protein kasar standar pada ransum layer?', $cbtQuestions[1]['pertanyaan']);
        $this->assertEquals('B', $cbtQuestions[1]['kunci']);
    }

    /**
     * Test: Asesor can save FR.IA.10 (Verifikasi Pihak Ketiga) and FR.IA.11 (Reviu Produk) to DB.
     */
    public function test_asesor_can_save_fr_ia_10_and_fr_ia_11()
    {
        // 1. Save FR.IA.10
        $response10 = $this->actingAs($this->asesor)
            ->post(route('formulir.ia.simpan', [
                'kodeForm' => 'FR.IA.10',
                'pendaftaranId' => $this->pendaftaran->id,
            ]), [
                'nama_supervisor' => 'Ir. Hendra Pratama',
                'tempat_kerja' => 'PT Farm Unggas Jaya',
                'alamat' => 'Jl. Peternakan No. 12',
                'telepon' => '08123456789',
                'q_k3' => '1',
                'q_tim' => '1',
                'q_kelola' => '1',
                'q_adaptasi' => '1',
                'q_respon' => '1',
                'q_kontak' => '1',
                'hubungan' => 'Kepala Divisi Feedmill',
                'lama_bekerja' => '2 Tahun',
                'testimoni_kinerja' => 'Kinerja asesi sangat baik dan konsisten.',
                'rekomendasi' => 'K',
            ]);

        $response10->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.10',
            'rekomendasi' => 'K',
        ]);

        // 2. Save FR.IA.11
        $response11 = $this->actingAs($this->asesor)
            ->post(route('formulir.ia.simpan', [
                'kodeForm' => 'FR.IA.11',
                'pendaftaranId' => $this->pendaftaran->id,
            ]), [
                'nama_produk' => 'Formula Konsentrat Unggas Super',
                'standar_industri' => 'SNI Pakan Ternak 2024',
                'dimensi_format' => 'Bentuk crumble kemasan 50 kg',
                'rekomendasi_crp' => 'kompeten',
                'catatan' => 'Hasil uji produk memenuhi semua standar mutu.',
            ]);

        $response11->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.11',
            'rekomendasi' => 'kompeten',
        ]);
    }
}

