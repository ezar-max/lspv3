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
}
