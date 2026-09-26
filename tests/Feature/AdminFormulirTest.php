<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\PendaftaranAsesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormulirTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected SkemaSertifikasi $skemaA;
    protected SkemaSertifikasi $skemaB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP Utama',
            'email' => 'admin.utama@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        $this->skemaA = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-A',
            'nama_skema' => 'Skema Rekayasa Perangkat Lunak',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $this->skemaB = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-B',
            'nama_skema' => 'Skema Teknik Jaringan Komputer',
            'kategori' => 'KKNI',
            'biaya' => 600000,
            'status_aktif' => true,
        ]);

        $unit = UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'TIK.001',
            'judul_unit' => 'Membuat Kode Program',
            'standar_kompetensi' => 'SKKNI',
        ]);
        $elemen = $unit->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menulis Sintaks',
            'pertanyaan_elemen' => 'Bisa menulis sintaks?',
        ]);
        $elemen->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Sintaks ditulis sesuai standar',
        ]);
    }

    public function test_admin_can_access_pusat_pembuatan_formulir()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.master-muk.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Pembuatan Formulir');
        $response->assertSee('Pilih Skema yang Mau Dibuatkan Formulir:');
        $response->assertSee('FR.MAPA.01');
        $response->assertSee('FR.MAPA.02');
        $response->assertSee('FR.IA.01');
        $response->assertSee('FR.IA.11');
        $response->assertSee('Skema Rekayasa Perangkat Lunak');
    }

    public function test_admin_can_switch_schemes_freely()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.master-muk.index', [
            'skema_id' => $this->skemaB->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Skema Teknik Jaringan Komputer');
    }

    public function test_asesor_view_locks_to_assigned_scheme_without_dropdown()
    {
        $asesorA = Pengguna::create([
            'nama_lengkap' => 'Asesor Skema A',
            'email' => 'asesor.a@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaA->id,
        ]);

        $response = $this->actingAs($asesorA)->get(route('asesor.mapa'));

        $response->assertStatus(200);
        $response->assertSee('Skema Penugasan Anda');
        $response->assertSee('Skema Rekayasa Perangkat Lunak');
        // Asesor must NOT see the dropdown to change scheme
        $response->assertDontSee('Pilih Skema yang Mau Dibuatkan Formulir:');
        $response->assertDontSee('admin_skema_id');
    }

    public function test_form_created_by_admin_on_scheme_a_appears_for_asesor_with_scheme_a()
    {
        $asesorA = Pengguna::create([
            'nama_lengkap' => 'Asesor Skema A',
            'email' => 'asesor.a@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaA->id,
        ]);

        // Admin creates master instrument on Skema A
        $instrument = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia05',
            'title' => 'Uji Teori Pilihan Ganda RPL',
            'instructions' => 'Petunjuk pengerjaan soal pilihan ganda',
            'is_active' => true,
        ]);

        // Admin adds 3 question items to the question bank
        for ($i = 1; $i <= 3; $i++) {
            $instrument->questionBanks()->create([
                'question_text' => "Soal nomor {$i} tentang programming",
                'question_type' => 'multiple_choice',
                'options' => ['A' => 'Jawaban A', 'B' => 'Jawaban B'],
                'correct_answer' => 'A',
                'weight' => 10,
                'order' => $i,
            ]);
        }

        // Asesor assigned to Skema A visits their form page
        $response = $this->actingAs($asesorA)->get(route('asesor.mapa'));

        $response->assertStatus(200);
        // The badge shows 3 questions available
        $response->assertSee('3 Butir Soal');
        $response->assertSee('Kelola Form');
    }

    public function test_old_bank_instrumen_view_file_does_not_exist()
    {
        $this->assertFalse(file_exists(resource_path('views/admin/master-muk/index.blade.php')));
    }

    public function test_asesor_or_admin_can_manage_fr_ia_11_spec_with_time_tolerance()
    {
        $instrument = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia11',
            'title' => 'Ceklis Mutu Produk RPL',
            'is_active' => true,
        ]);

        // 1. Store spec with time tolerance
        $response = $this->actingAs($this->admin)->post(route('admin.master-muk.spec.store'), [
            'scheme_master_instrument_id' => $instrument->id,
            'spec_name' => 'Waktu Respon Halaman Dashboard',
            'standard_tolerance' => '00:02:30',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('master_product_specifications', [
            'scheme_master_instrument_id' => $instrument->id,
            'spec_name' => 'Waktu Respon Halaman Dashboard',
            'standard_tolerance' => '00:02:30',
        ]);

        $spec = \App\Models\MasterProductSpecification::where('scheme_master_instrument_id', $instrument->id)->first();

        // 2. Manage page renders time input and action buttons
        $manageResp = $this->actingAs($this->admin)->get(route('admin.master-muk.manage', $instrument->id));
        $manageResp->assertStatus(200);
        $manageResp->assertSee('type="time"', false);
        $manageResp->assertSee('00:02:30');
        $manageResp->assertSee('bukaModalEditSpec', false);

        // 3. Update spec with new time
        $updateResp = $this->actingAs($this->admin)->post(route('admin.master-muk.spec.update', $spec->id), [
            'spec_name' => 'Waktu Respon Halaman Dashboard Terkoreksi',
            'standard_tolerance' => '00:01:45',
        ]);
        $updateResp->assertRedirect();

        $this->assertDatabaseHas('master_product_specifications', [
            'id' => $spec->id,
            'spec_name' => 'Waktu Respon Halaman Dashboard Terkoreksi',
            'standard_tolerance' => '00:01:45',
        ]);

        // 4. Destroy spec
        $deleteResp = $this->actingAs($this->admin)->delete(route('admin.master-muk.spec.destroy', $spec->id));
        $deleteResp->assertRedirect();

        $this->assertDatabaseMissing('master_product_specifications', [
            'id' => $spec->id,
        ]);
    }

    public function test_fr_ia_01_matches_bnsp_structure_and_can_be_saved()
    {
        $unit = \App\Models\UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Mengembangkan Antarmuka Web',
        ]);

        $elem = \App\Models\ElemenKompetensi::create([
            'unit_id' => $unit->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Merancang Tampilan Antarmuka',
        ]);

        $kuk = \App\Models\KriteriaUnjukKerja::create([
            'elemen_id' => $elem->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Rancangan tata letak dibuat sesuai panduan desain',
        ]);

        $instrument = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia01',
            'title' => 'Ceklis Observasi Aktivitas Praktik',
            'is_active' => true,
        ]);

        // 1. Visit manage page for IA.01
        $response = $this->actingAs($this->admin)->get(route('admin.master-muk.manage', $instrument->id));
        $response->assertStatus(200);
        $response->assertSee('FR.IA.01. CL - CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA ATAU TEMPAT KERJA SIMULASI');
        $response->assertSee('PANDUAN BAGI ASESOR');
        $response->assertSee('Kelompok Pekerjaan 1');
        $response->assertSee('Standar Industri atau Tempat Kerja');
        $response->assertSee('Umpan Balik untuk asesi:');

        // 2. Save metadata from the form
        $saveResp = $this->actingAs($this->admin)->post(route('admin.master-muk.update-metadata', $instrument->id), [
            'metadata_default_standard' => 'SOP Pengembangan Web LSP',
            'time_limit_minutes' => 90,
            'metadata_standar_elemen' => [
                $elem->id => 'SOP UI/UX & SKKNI 2023',
            ],
            'metadata_umpan_balik' => 'Kandidat menunjukkan performa demonstrasi yang sangat baik.',
        ]);

        $saveResp->assertRedirect();
        $saveResp->assertSessionHas('sukses');

        $instrument->refresh();
        $this->assertEquals('SOP Pengembangan Web LSP', $instrument->additional_metadata['default_standard']);
        $this->assertEquals(90, $instrument->time_limit_minutes);
        $this->assertEquals('SOP UI/UX & SKKNI 2023', $instrument->additional_metadata['standar_elemen'][$elem->id]);
        $this->assertEquals('Kandidat menunjukkan performa demonstrasi yang sangat baik.', $instrument->additional_metadata['umpan_balik']);
    }

    public function test_fr_ia_02_matches_bnsp_structure_and_can_be_saved()
    {
        $unit = \App\Models\UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'J.620100.004.01',
            'judul_unit' => 'Menggunakan Struktur Data',
        ]);

        $instrument = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia02',
            'title' => 'Tugas Praktik Demonstrasi',
            'is_active' => true,
        ]);

        // 1. Visit manage page for IA.02
        $response = $this->actingAs($this->admin)->get(route('admin.master-muk.manage', $instrument->id));
        $response->assertStatus(200);
        $response->assertSee('FR.IA.02.');
        $response->assertSee('TPD - TUGAS PRAKTIK DEMONSTRASI');
        $response->assertSee('A. Petunjuk');
        $response->assertSee('B. Skenario Tugas Praktik Demonstrasi');
        $response->assertSee('Kelompok');
        $response->assertSee('Pekerjaan 1');
        $response->assertSee('Dst..');
        $response->assertSee('Skenario Tugas Praktik Demonstrasi:');
        $response->assertSee('Perlengkapan dan Peralatan :');
        $response->assertSee('Durasi Waktu :');
        $response->assertSee('PENYUSUN DAN VALIDATOR');

        // 2. Save metadata
        $saveResp = $this->actingAs($this->admin)->post(route('admin.master-muk.update-metadata', $instrument->id), [
            'time_limit_minutes' => 180,
            'metadata_scenario' => 'Demonstrasikan pembuatan modul autentikasi dan basis data.',
            'metadata_tools' => "1. PC / Laptop\n2. PHP & MySQL\n3. VS Code",
            'metadata_kelompok_skenario' => [
                1 => [
                    'skenario' => 'Demonstrasikan pembuatan modul autentikasi dan basis data.',
                    'peralatan' => "1. PC / Laptop\n2. PHP & MySQL\n3. VS Code",
                    'waktu' => '180 Menit',
                ]
            ],
            'metadata_penyusun_validator' => [
                'validator_1_nama' => 'Master Asesor Validator',
                'validator_1_met' => 'MET.000.999999.2023',
                'validator_1_ttd' => 'VALID-2026',
            ]
        ]);

        $saveResp->assertRedirect();
        $saveResp->assertSessionHas('sukses');

        $instrument->refresh();
        $this->assertEquals(180, $instrument->time_limit_minutes);
        $this->assertEquals('Demonstrasikan pembuatan modul autentikasi dan basis data.', $instrument->additional_metadata['scenario']);
        $this->assertEquals('Master Asesor Validator', $instrument->additional_metadata['penyusun_validator']['validator_1_nama']);
    }

    public function test_fr_ia_03_matches_bnsp_structure_and_can_be_saved()
    {
        $unit = \App\Models\UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menulis Kode Clean Architecture',
        ]);

        // 1. Visit FR.IA.03
        $response = $this->actingAs($this->admin)->get(route('formulir.ia03', ['skema_id' => $this->skemaA->id]));
        $response->assertStatus(200);
        $response->assertSee('FR.IA.03.');
        $response->assertSee('PERTANYAAN UNTUK MENDUKUNG OBSERVASI');
        $response->assertSee('PANDUAN BAGI ASESOR');
        $response->assertSee('Kelompok');
        $response->assertSee('Pekerjaan 1');
        $response->assertSee('Kelompok');
        $response->assertSee('Pekerjaan 2');
        $response->assertSee('Kelompok');
        $response->assertSee('Pekerjaan 3');
        $response->assertSee('Pencapaian');
        $response->assertSee('Tanggapan:');
        $response->assertSee('Umpan balik untuk asesi:');
        $response->assertSee('Diadaptasi dari template yang disediakan di Departemen Pendidikan dan Pelatihan, Australia');

        // 2. Test saving form
        $pendaftaran = \App\Models\PendaftaranAsesi::create([
            'skema_id' => $this->skemaA->id,
            'asesi_id' => $this->admin->id,
            'nomor_pendaftaran' => 'REG-IA03-TEST-001',
            'status' => 'terdaftar',
            'tanggal_daftar' => now()->toDateString(),
        ]);

        $saveResp = $this->actingAs($this->admin)->post(route('formulir.ia.simpan', [
            'kodeForm' => 'FR.IA.03',
            'pendaftaranId' => $pendaftaran->id,
        ]), [
            'kelompok_soal' => [
                1 => [
                    1 => [
                        'pertanyaan' => 'Bagaimana prosedur K3 diterapkan sebelum demonstrasi?',
                        'tanggapan' => 'Asesi memeriksa APD dan instalasi keselamatan.',
                        'pencapaian' => 'Ya',
                    ]
                ]
            ],
            'umpan_balik' => 'Penjelasan asesi sangat memuaskan.',
        ]);

        $saveResp->assertRedirect();
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.03',
        ]);
    }
}



