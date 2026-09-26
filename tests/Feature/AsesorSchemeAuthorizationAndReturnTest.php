<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\SchemeMasterInstrument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesorSchemeAuthorizationAndReturnTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesor;
    protected SkemaSertifikasi $skemaAssigned;
    protected SkemaSertifikasi $skemaUnassigned;
    protected SchemeMasterInstrument $instrumentAssigned;
    protected SchemeMasterInstrument $instrumentUnassigned;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat skema dengan nama yang secara alfabetis lebih dulu daripada skema assigned
        // Ini mereplikasi skenario bug di mana Skema Unassigned (misal: Measuring Operator) muncul duluan
        $this->skemaUnassigned = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-ELN-001',
            'nama_skema' => 'AAA Skema Yang Tidak Ditugaskan',
            'status_aktif' => true,
        ]);

        $this->skemaAssigned = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-LAS-001',
            'nama_skema' => 'ZZZ Skema Penugasan Asesor',
            'status_aktif' => true,
        ]);

        // Buat unit kompetensi untuk masing-masing skema
        UnitKompetensi::create([
            'skema_id' => $this->skemaUnassigned->id,
            'kode_unit' => 'ELN.001',
            'judul_unit' => 'Unit Elektronika',
            'jenis_standar' => 'SKKNI',
        ]);

        UnitKompetensi::create([
            'skema_id' => $this->skemaAssigned->id,
            'kode_unit' => 'LAS.001',
            'judul_unit' => 'Unit Pengelasan',
            'jenis_standar' => 'SKKNI',
        ]);

        // Buat akun Asesor yang ditugaskan ke skemaAssigned
        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Fredi Asesor',
            'email' => 'asesor.fredi@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skemaAssigned->id,
        ]);

        // Buat master instrumen pada masing-masing skema
        $this->instrumentAssigned = SchemeMasterInstrument::create([
            'skema_id' => $this->skemaAssigned->id,
            'instrument_code' => 'ia11',
            'title' => 'Formulir IA.11 Pengelasan',
            'is_active' => true,
        ]);

        $this->instrumentUnassigned = SchemeMasterInstrument::create([
            'skema_id' => $this->skemaUnassigned->id,
            'instrument_code' => 'ia11',
            'title' => 'Formulir IA.11 Elektronika',
            'is_active' => true,
        ]);
    }

    public function test_asesor_accessing_admin_master_muk_defaults_to_assigned_scheme(): void
    {
        // Akses halaman Formulir tanpa parameter skema_id
        $response = $this->actingAs($this->asesor)->get(route('admin.master-muk.index'));

        $response->assertStatus(200);
        // Harus mengunci ke skema penugasan Asesor, bukan skema alfabetis pertama
        $response->assertSee('ZZZ Skema Penugasan Asesor');
        $response->assertSee('SKM-LAS-001');
        $response->assertDontSee('AAA Skema Yang Tidak Ditugaskan');
    }

    public function test_asesor_kembali_button_in_manage_page_includes_skema_id(): void
    {
        $response = $this->actingAs($this->asesor)
            ->get(route('admin.master-muk.manage', $this->instrumentAssigned->id));

        $response->assertStatus(200);
        // Tombol kembali harus membawa parameter skema_id agar pilihan skema tidak reset
        $expectedUrl = route('asesor.mapa', ['skema_id' => $this->skemaAssigned->id]);
        $response->assertSee($expectedUrl);
    }

    public function test_asesor_cannot_manage_instrument_from_unassigned_scheme(): void
    {
        // Asesor mencoba mengakses instrumen dari skema yang tidak ditugaskan
        $response = $this->actingAs($this->asesor)
            ->get(route('admin.master-muk.manage', $this->instrumentUnassigned->id));

        $response->assertRedirect(route('admin.master-muk.index'));
        $response->assertSessionHas('error');
    }

    public function test_asesor_cannot_create_instrument_for_unassigned_scheme(): void
    {
        $response = $this->actingAs($this->asesor)
            ->get(route('admin.master-muk.create', [
                'skema_id' => $this->skemaUnassigned->id,
                'code' => 'ia01',
            ]));

        $response->assertRedirect(route('admin.master-muk.index'));
        $response->assertSessionHas('error');
    }

    public function test_asesor_scheme_mapa_access_authorization(): void
    {
        // 1. Skema yang ditugaskan -> 200 OK
        $assignedResponse = $this->actingAs($this->asesor)
            ->get(route('asesor.skema.mapa-01', $this->skemaAssigned->id));
        $assignedResponse->assertStatus(200);

        // 2. Skema yang tidak ditugaskan -> 403 Forbidden
        $unassignedResponse = $this->actingAs($this->asesor)
            ->get(route('asesor.skema.mapa-01', $this->skemaUnassigned->id));
        $unassignedResponse->assertStatus(403);
    }
}
