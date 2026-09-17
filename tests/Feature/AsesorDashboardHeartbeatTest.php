<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesorDashboardHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Uji Coba, M.Kom.',
            'email' => 'asesor_heartbeat@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001122.2023',
        ]);
    }

    public function test_asesor_can_access_dashboard_and_has_initial_hash(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.dashboard'));

        $response->assertOk();
        $response->assertViewHas('initialHash');
        $response->assertDontSee('Auto-refresh:');
    }

    public function test_heartbeat_returns_valid_json_with_hash(): void
    {
        $response = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat'));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'changed',
            'hash',
        ]);
        $response->assertJson([
            'status' => 'ok',
            'changed' => false,
        ]);
    }

    public function test_heartbeat_detects_hash_match(): void
    {
        $res1 = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat'));
        $hash = $res1->json('hash');

        $res2 = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat', ['hash' => $hash]));
        $res2->assertOk();
        $res2->assertJson([
            'status' => 'ok',
            'changed' => false,
            'hash' => $hash,
        ]);
    }

    public function test_heartbeat_detects_data_change(): void
    {
        $oldHash = 'dummy_outdated_hash_12345';

        $response = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat', ['hash' => $oldHash]));

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'changed' => true,
        ]);
        $this->assertNotEquals($oldHash, $response->json('hash'));
    }

    public function test_guest_cannot_access_heartbeat(): void
    {
        $response = $this->get(route('asesor.dashboard.heartbeat'));

        $response->assertRedirect(route('masuk'));
    }

    public function test_asesor_dashboard_contains_auto_refresh_toast_and_clean_header(): void
    {
        $response = $this->actingAs($this->asesor)->get(route('asesor.dashboard'));

        $response->assertOk();
        $response->assertSee('Pembaruan Data Asesi Diterima!');
        $response->assertSee('newUpdateToast');
        $response->assertSee('checkUpdates()');
        $response->assertDontSee('Auto-refresh:');
    }

    public function test_cek_pembaruan_alias_returns_valid_payload_with_candidate_info(): void
    {
        $response = $this->actingAs($this->asesor)->getJson(route('asesor.cek-pembaruan'));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'changed',
            'has_new',
            'hash',
            'current_hash',
            'nama_asesi',
            'nama_skema',
        ]);
        $this->assertEquals('ok', $response->json('status'));
        $this->assertFalse($response->json('changed'));
        $this->assertFalse($response->json('has_new'));
    }

    public function test_heartbeat_detects_candidate_update_for_assigned_asesor(): void
    {
        $skema = \App\Models\SkemaSertifikasi::create([
            'kode_skema' => 'SKM-ASESOR-001',
            'nama_skema' => 'Skema Uji Asesor',
            'jenis' => 'KKNI',
        ]);

        $asesi = Pengguna::create([
            'nama_lengkap' => 'Budi Peserta',
            'email' => 'budi_peserta@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
        ]);

        $pendaftaran = \App\Models\PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-ASESOR-001',
            'tanggal_daftar' => now()->toDateString(),
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'asesor_id' => $this->asesor->id,
            'status_pendaftaran' => 'diverifikasi',
            'status_apl02' => 'submitted',
        ]);

        // First heartbeat
        $res1 = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat'));
        $currentHash = $res1->json('hash');
        $this->assertEquals('Budi Peserta', $res1->json('nama_asesi'));

        // No change
        $res2 = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat', ['hash' => $currentHash]));
        $this->assertFalse($res2->json('has_new'));
        $this->assertFalse($res2->json('changed'));

        // Candidate submits revision / updates status
        $pendaftaran->update([
            'status_apl02' => 'approved',
            'updated_at' => now()->addSeconds(10),
        ]);

        // Heartbeat with old hash now detects change
        $res3 = $this->actingAs($this->asesor)->getJson(route('asesor.dashboard.heartbeat', ['hash' => $currentHash]));
        $this->assertTrue($res3->json('has_new'));
        $this->assertTrue($res3->json('changed'));
        $this->assertEquals('Budi Peserta', $res3->json('nama_asesi'));
    }
}
