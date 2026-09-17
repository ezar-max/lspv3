<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardPendaftaranAutoRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_cek_pendaftaran_terbaru_detects_new_submission(): void
    {
        $admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP Test',
            'email' => 'admintest@lsp.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'aktif' => true,
        ]);

        $asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Baru Test',
            'email' => 'asesitest@lsp.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Teknik Komputer',
            'aktif' => true,
        ]);

        $p1 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diajukan',
        ]);

        // When last_id is equal to current latest id -> has_new is false
        $response = $this->actingAs($admin)->getJson(route('admin.cek-pendaftaran-terbaru', ['last_id' => $p1->id]));
        $response->assertOk();
        $response->assertJson([
            'latest_id' => $p1->id,
            'has_new' => false,
        ]);

        // When a new registration is submitted by an asesi
        $p2 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-002',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diajukan',
        ]);

        // Now checking with previous last_id -> has_new is true!
        $response2 = $this->actingAs($admin)->getJson(route('admin.cek-pendaftaran-terbaru', ['last_id' => $p1->id]));
        $response2->assertOk();
        $response2->assertJson([
            'latest_id' => $p2->id,
            'has_new' => true,
            'nama_asesi' => 'Asesi Baru Test',
        ]);
    }
}
