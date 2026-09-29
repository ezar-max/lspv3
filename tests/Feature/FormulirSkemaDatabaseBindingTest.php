<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormulirSkemaDatabaseBindingTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $asesor;
    protected $skemaA;
    protected $skemaB;
    protected $unitA;
    protected $unitB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Penguji LSP',
            'email' => 'admin.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        $this->skemaA = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-TEK-001',
            'nama_skema' => 'Skema Rekayasa Perangkat Lunak',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $this->unitA = UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menulis Kode Clean Architecture',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $elemenA = $this->unitA->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menerapkan Pola SOLID',
        ]);

        $elemenA->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Prinsip single responsibility diterapkan pada setiap class.',
        ]);

        $this->unitA2 = UnitKompetensi::create([
            'skema_id' => $this->skemaA->id,
            'kode_unit' => 'J.620100.002.01',
            'judul_unit' => 'Melakukan Pengujian Unit Testing',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $elemenA2 = $this->unitA2->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menjalankan Test Suite',
        ]);

        $elemenA2->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Test case dijalankan dan diverifikasi.',
        ]);

        $this->skemaB = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-NET-002',
            'nama_skema' => 'Skema Jaringan Komputer',
            'kategori' => 'KKNI',
            'biaya' => 600000,
            'status_aktif' => true,
        ]);

        $this->unitB = UnitKompetensi::create([
            'skema_id' => $this->skemaB->id,
            'kode_unit' => 'J.611000.002.01',
            'judul_unit' => 'Mengonfigurasi Routing BGP',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $elemenB = $this->unitB->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Mengonfigurasi Autonomous System',
        ]);

        $elemenB->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Nomor AS dan peering BGP dikonfigurasi sesuai topologi.',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Kompetensi LSP',
            'email' => 'asesor.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaA->id,
        ]);
    }

    public function test_form_ia01_binds_dynamically_to_requested_skema()
    {
        $this->actingAs($this->admin);

        // Buka FR.IA.01 untuk Skema A
        $responseA = $this->get(route('formulir.ia01', ['skema_id' => $this->skemaA->id]));
        $responseA->assertOk();
        $responseA->assertSee($this->skemaA->nama_skema);
        $responseA->assertSee($this->skemaA->kode_skema);
        $responseA->assertSee($this->unitA->kode_unit);
        $responseA->assertSee($this->unitA->judul_unit);
        $responseA->assertDontSee($this->unitB->kode_unit);

        // Buka FR.IA.01 untuk Skema B
        $responseB = $this->get(route('formulir.ia01', ['skema_id' => $this->skemaB->id]));
        $responseB->assertOk();
        $responseB->assertSee($this->skemaB->nama_skema);
        $responseB->assertSee($this->skemaB->kode_skema);
        $responseB->assertSee($this->unitB->kode_unit);
        $responseB->assertSee($this->unitB->judul_unit);
        $responseB->assertDontSee($this->unitA->kode_unit);
    }

    public function test_form_ia02_and_ia03_bind_dynamically_to_database_skema()
    {
        $this->actingAs($this->admin);

        // FR.IA.02
        $responseIa02 = $this->get(route('formulir.ia02', ['skema_id' => $this->skemaA->id]));
        $responseIa02->assertOk();
        $responseIa02->assertSee($this->skemaA->nama_skema);
        $responseIa02->assertSee($this->unitA->judul_unit);

        // FR.IA.03
        $responseIa03 = $this->get(route('formulir.ia03', ['skema_id' => $this->skemaB->id]));
        $responseIa03->assertOk();
        $responseIa03->assertSee($this->skemaB->nama_skema);
        $responseIa03->assertSee($this->unitB->kode_unit);
    }

    public function test_form_ia05_and_ia06_and_ia07_generate_soal_from_active_skema_kuk()
    {
        $this->actingAs($this->admin);

        // FR.IA.05A (Pilihan Ganda Dinamis dari KUK Skema A)
        $responseIa05 = $this->get(route('formulir.ia05a', ['skema_id' => $this->skemaA->id]));
        $responseIa05->assertOk();
        $responseIa05->assertSee($this->skemaA->nama_skema);
        $responseIa05->assertSee($this->unitA->judul_unit);

        // FR.IA.06A (Esai Dinamis dari Elemen Skema B)
        $responseIa06 = $this->get(route('formulir.ia06a', ['skema_id' => $this->skemaB->id]));
        $responseIa06->assertOk();
        $responseIa06->assertSee($this->skemaB->nama_skema);
        $responseIa06->assertSee($this->unitB->kode_unit);

        // FR.IA.07 (Pertanyaan Lisan Dinamis dari KUK Skema A)
        $responseIa07 = $this->get(route('formulir.ia07', ['skema_id' => $this->skemaA->id]));
        $responseIa07->assertOk();
        $responseIa07->assertSee($this->skemaA->nama_skema);
        $responseIa07->assertSee($this->unitA->kode_unit);
    }

    public function test_form_ia08_and_ia09_and_ia10_bind_dynamically_to_active_skema()
    {
        $this->actingAs($this->admin);

        // FR.IA.08
        $responseIa08 = $this->get(route('formulir.ia08', ['skema_id' => $this->skemaA->id]));
        $responseIa08->assertOk();
        $responseIa08->assertSee($this->skemaA->nama_skema);
        $responseIa08->assertSee($this->unitA->kode_unit);

        // FR.IA.09
        $responseIa09 = $this->get(route('formulir.ia09', ['skema_id' => $this->skemaB->id]));
        $responseIa09->assertOk();
        $responseIa09->assertSee($this->skemaB->nama_skema);
        $responseIa09->assertSee($this->unitB->kode_unit);

        // FR.IA.10
        $responseIa10 = $this->get(route('formulir.ia10', ['skema_id' => $this->skemaA->id]));
        $responseIa10->assertOk();
        $responseIa10->assertSee($this->skemaA->nama_skema);
        $responseIa10->assertSee($this->skemaA->kode_skema);
    }

    public function test_action_bar_kembali_button_does_not_contain_history_back()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('formulir.ia01', ['skema_id' => $this->skemaA->id]));
        $response->assertOk();
        $response->assertDontSee('window.history.back()');
    }

    public function test_all_forms_display_all_units_not_just_one()
    {
        $this->actingAs($this->admin);

        $routesToTest = [
            'formulir.ia01',
            'formulir.ia02',
            'formulir.ia03',
            'formulir.ia04a',
            'formulir.ia04b',
            'formulir.ia05a',
            'formulir.ia05b',
            'formulir.ia06a',
            'formulir.ia06b',
            'formulir.ia06c',
            'formulir.ia07',
            'formulir.ia08',
            'formulir.ia09',
            'formulir.ia10',
            'formulir.ia11',
            'formulir.ak01',
        ];

        foreach ($routesToTest as $routeName) {
            $response = $this->get(route($routeName, ['skema_id' => $this->skemaA->id]));
            $response->assertOk();
            // Kedua unit harus tampil di setiap form!
            $response->assertSee($this->unitA->kode_unit);
            $response->assertSee($this->unitA2->kode_unit);
            $response->assertDontSee('Dst..');
        }

        // Pastikan juga Master MUK IA.02 dan IA.03 menampilkan semua unit dan tidak ada Dst..
        $instrumentIa02 = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia02',
            'title' => 'Master FR.IA.02',
            'created_by' => $this->admin->id,
        ]);
        $masterMukIa02 = $this->get(route('admin.master-muk.manage', $instrumentIa02->id));
        $masterMukIa02->assertOk();
        $masterMukIa02->assertSee($this->unitA->kode_unit);
        $masterMukIa02->assertSee($this->unitA2->kode_unit);
        $masterMukIa02->assertDontSee('Dst..');

        $instrumentIa03 = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia03',
            'title' => 'Master FR.IA.03',
            'created_by' => $this->admin->id,
        ]);
        $masterMukIa03 = $this->get(route('admin.master-muk.manage', $instrumentIa03->id));
        $masterMukIa03->assertOk();
        $masterMukIa03->assertSee($this->unitA->kode_unit);
        $masterMukIa03->assertSee($this->unitA2->kode_unit);
        $masterMukIa03->assertDontSee('Dst..');
    }

    public function test_real_assessment_binds_asesi_and_asesor_from_database()
    {
        $asesi = Pengguna::create([
            'nama_lengkap' => 'Ahmad Dahlan Asesi',
            'email' => 'ahmad.asesi@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $asesorPenilai = Pengguna::create([
            'nama_lengkap' => 'Dr. Budi Hartono, M.T.',
            'email' => 'budi.asesor@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.789101.2023',
        ]);

        $jadwal = \App\Models\JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-2026-TEK',
            'skema_id' => $this->skemaA->id,
            'asesor_id' => $asesorPenilai->id,
            'nama_tuk' => 'TUK Lab Komputasi A',
            'tanggal_uji' => '2026-10-15',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '16:00:00',
            'kuota' => 20,
            'status_jadwal' => 'berlangsung',
        ]);

        $pendaftaran = \App\Models\PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-AHMAD-2026',
            'asesi_id' => $asesi->id,
            'asesor_id' => $asesorPenilai->id,
            'jadwal_id' => $jadwal->id,
            'skema_id' => $this->skemaA->id,
            'status_pendaftaran' => 'disetujui',
            'tanggal_daftar' => now(),
        ]);

        // 1. Asesor membuka form FR.IA.01 untuk asesi ini
        $responseAsesor = $this->actingAs($asesorPenilai)
            ->get(route('formulir.ia01', $pendaftaran->id));

        $responseAsesor->assertOk();
        // Harus menampilkan nama asesi asli dari DB
        $responseAsesor->assertSee('Ahmad Dahlan Asesi');
        // Harus menampilkan nama asesor asli dari DB
        $responseAsesor->assertSee('Dr. Budi Hartono, M.T.');
        // Harus menampilkan No. Reg asesor asli dari DB
        $responseAsesor->assertSee('MET.000.789101.2023');
        // Harus menampilkan nama TUK asli dari DB
        $responseAsesor->assertSee('TUK Lab Komputasi A');
        // Harus menampilkan tanggal asesmen dari jadwal
        $responseAsesor->assertSee('15-10-2026');
        // Tidak boleh menampilkan teks pratinjau master blanko
        $responseAsesor->assertDontSee('(Blanko / Pratinjau Master)');

        // 2. Asesi membuka form FR.IA.01 miliknya sendiri
        $responseAsesi = $this->actingAs($asesi)
            ->get(route('formulir.ia01', $pendaftaran->id));

        $responseAsesi->assertOk();
        $responseAsesi->assertSee('Ahmad Dahlan Asesi');
        $responseAsesi->assertSee('Dr. Budi Hartono, M.T.');
        $responseAsesi->assertSee('MET.000.789101.2023');
        $responseAsesi->assertDontSee('(Blanko / Pratinjau Master)');

        // 3. Uji FR.IA.02, FR.IA.03, FR.IA.07 juga menampilkan Asesi dan Asesor real dari database
        foreach (['formulir.ia02', 'formulir.ia03', 'formulir.ia07'] as $route) {
            $resp = $this->actingAs($asesorPenilai)->get(route($route, $pendaftaran->id));
            $resp->assertOk();
            $resp->assertSee('Ahmad Dahlan Asesi');
            $resp->assertSee('Dr. Budi Hartono, M.T.');
            $resp->assertSee('MET.000.789101.2023');
            $resp->assertSee('TUK Lab Komputasi A');
            $resp->assertSee('15-10-2026');
            $resp->assertDontSee('(Blanko / Pratinjau Master)');
        }

        // 4. Uji Tabel PENYUSUN DAN VALIDATOR:
        // - Mengambil data validator dari asesor penguji saat asesmen
        // - Tidak ada input ketik (Penyusun 2..., Validator 1..., Validator 2...)
        // - Masing-masing hanya menampilkan 1 baris
        $responseAsesor->assertSee('PENYUSUN');
        $responseAsesor->assertSee('VALIDATOR');
        $responseAsesor->assertDontSee('Penyusun 2...');
        $responseAsesor->assertDontSee('Validator 1...');
        $responseAsesor->assertDontSee('Validator 2...');
    }

    public function test_penyusun_dan_validator_binds_creator_and_admin_validator()
    {
        $asesorPenyusunA = Pengguna::create([
            'nama_lengkap' => 'Ir. Hendra Wijaya (Asesor Pembuat Form Skema A)',
            'email' => 'hendra.penyusun@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaA->id,
            'nomor_registrasi' => 'MET.PENYUSUN.001.2025',
        ]);

        $this->admin->update([
            'nama_lengkap' => 'Hj. Siti Aminah, S.E. (Admin LSP Validator)',
            'nomor_registrasi' => 'REG.ADM.LSP.888',
        ]);

        $instrument = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $this->skemaA->id,
            'instrument_code' => 'ia01',
            'title' => 'Ceklis Observasi',
            'created_by' => $asesorPenyusunA->id,
            'is_active' => true,
        ]);

        $jadwal = \App\Models\JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-TEST-VAL',
            'skema_id' => $this->skemaA->id,
            'asesor_id' => $asesorPenyusunA->id,
            'nama_tuk' => 'TUK Utama',
            'tanggal_uji' => '2026-11-20',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '16:00:00',
            'kuota' => 10,
        ]);

        $asesi = Pengguna::create([
            'nama_lengkap' => 'Budi Santoso Asesi',
            'email' => 'budi.santoso@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $pendaftaran = \App\Models\PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-BUDI-VAL',
            'asesi_id' => $asesi->id,
            'asesor_id' => $asesorPenyusunA->id,
            'jadwal_id' => $jadwal->id,
            'skema_id' => $this->skemaA->id,
            'status_pendaftaran' => 'disetujui',
            'tanggal_daftar' => now(),
        ]);

        $resp = $this->actingAs($asesorPenyusunA)->get(route('formulir.ia01', $pendaftaran->id));
        $resp->assertOk();
        // Penyusun harus nama akun asesor pembuat form skema tersebut
        $resp->assertSee('Ir. Hendra Wijaya (Asesor Pembuat Form Skema A)');
        $resp->assertSee('MET.PENYUSUN.001.2025');
        // Validator harus akun admin biasa LSP dari database
        $resp->assertSee('Hj. Siti Aminah, S.E. (Admin LSP Validator)');
        $resp->assertSee('REG.ADM.LSP.888');
        // Tidak ada input untuk ketik
        $resp->assertDontSee('Penyusun 2...');
        $resp->assertDontSee('Validator 1...');
        $resp->assertDontSee('Validator 2...');

        // Uji untuk Skema B: jika ada Asesor B pemegang Skema B, penyusun otomatis mengikuti Asesor B
        $asesorPenyusunB = Pengguna::create([
            'nama_lengkap' => 'Dewi Lestari, S.T. (Asesor Skema B)',
            'email' => 'dewi.asesor@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaB->id,
            'nomor_registrasi' => 'MET.ASESOR.B.2026',
        ]);

        $pendaftaranB = \App\Models\PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-ASESI-B',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skemaB->id,
            'status_pendaftaran' => 'disetujui',
            'tanggal_daftar' => now(),
        ]);

        $respB = $this->actingAs($asesorPenyusunB)->get(route('formulir.ia01', $pendaftaranB->id));
        $respB->assertOk();
        $respB->assertSee('Dewi Lestari, S.T. (Asesor Skema B)');
        $respB->assertSee('MET.ASESOR.B.2026');
        $respB->assertSee('Hj. Siti Aminah, S.E. (Admin LSP Validator)');
    }

    public function test_action_bar_kembali_button_returns_to_mapa_for_asesor_and_never_daftar_peserta()
    {
        $this->actingAs($this->asesor);

        $routesToTest = [
            'formulir.ia01',
            'formulir.ia02',
            'formulir.ia03',
            'formulir.ia04a',
            'formulir.ia04b',
            'formulir.ia05a',
            'formulir.ia05b',
            'formulir.ia05c',
            'formulir.ia06a',
            'formulir.ia06b',
            'formulir.ia06c',
            'formulir.ia07',
            'formulir.ia08',
            'formulir.ia09',
            'formulir.ia10',
            'formulir.ia11',
            'formulir.ak01',
        ];

        $expectedBackUrl = route('asesor.mapa', ['skema_id' => $this->skemaA->id]);
        $pesertaPenilaianUrl = route('asesor.daftar-peserta');

        foreach ($routesToTest as $routeName) {
            $response = $this->get(route($routeName, ['skema_id' => $this->skemaA->id]));
            $response->assertOk();
            $response->assertSee($expectedBackUrl);
            $response->assertDontSee($pesertaPenilaianUrl);
        }

        // Test direct access to formulir.index redirects to asesor.mapa
        $indexResponse = $this->get(route('formulir.index', ['skema_id' => $this->skemaA->id]));
        $indexResponse->assertRedirect($expectedBackUrl);
    }

    public function test_action_bar_kembali_button_returns_to_master_muk_for_admin()
    {
        $this->actingAs($this->admin);

        $routesToTest = [
            'formulir.ia01',
            'formulir.ia02',
            'formulir.ia03',
            'formulir.ia04a',
            'formulir.ia04b',
            'formulir.ia05a',
            'formulir.ia05b',
            'formulir.ia05c',
            'formulir.ia06a',
            'formulir.ia06b',
            'formulir.ia06c',
            'formulir.ia07',
            'formulir.ia08',
            'formulir.ia09',
            'formulir.ia10',
            'formulir.ia11',
            'formulir.ak01',
        ];

        $expectedBackUrl = route('admin.master-muk.index', ['skema_id' => $this->skemaA->id]);

        foreach ($routesToTest as $routeName) {
            $response = $this->get(route($routeName, ['skema_id' => $this->skemaA->id]));
            $response->assertOk();
            $response->assertSee($expectedBackUrl);
            $response->assertDontSee(route('asesor.daftar-peserta'));
        }

        // Test direct access to formulir.index redirects to admin.master-muk.index
        $indexResponse = $this->get(route('formulir.index', ['skema_id' => $this->skemaA->id]));
        $indexResponse->assertRedirect($expectedBackUrl);
    }
}
