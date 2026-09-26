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
        }
    }
}
