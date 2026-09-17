<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\ProfilAsesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccApl01ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesi;
    protected Pengguna $asesor;
    protected SkemaSertifikasi $skema;
    protected PendaftaranAsesi $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-01',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
            'kategori' => 'KKNI',
            'biaya' => 500000,
            'status_aktif' => true,
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Administrator LSP',
            'email' => 'admin@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'aktif' => true,
            'tanda_tangan' => 'data:image/png;base64,mockAdminSignature',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Kompetensi RPL',
            'email' => 'asesor.rpl@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'aktif' => true,
            'tanda_tangan' => 'data:image/png;base64,mockAsesorSignature',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Calon Asesi Baru',
            'email' => 'asesi.baru@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);

        ProfilAsesi::create([
            'pengguna_id' => $this->asesi->id,
            'nik' => '3201123456780001',
            'nama_sekolah_instansi' => 'SMKN 1 Gunungputri',
            'nomor_pendaftaran' => 'REG-2026-0001',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-0001',
            'status_pendaftaran' => 'diajukan',
            'tanggal_daftar' => now()->toDateString(),
            'tanda_tangan_asesi' => 'data:image/png;base64,mockAsesiSignature',
        ]);
    }

    public function test_detail_verifikasi_view_displays_no_schedule_alert_when_no_schedules_exist(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.detail-verifikasi', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Sesi Jadwal dengan Asesor untuk Skema Ini');
        $response->assertSee(route('admin.manajemen-jadwal'));
    }

    public function test_detail_verifikasi_view_displays_all_schedules_full_alert_when_quotas_are_exceeded(): void
    {
        $jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-FULL-01',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_uji' => now()->addDays(5)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'nama_tuk' => 'Lab Komputer 1',
            'kuota' => 1,
            'status_jadwal' => 'terjadwal',
        ]);

        // Create an existing registration occupying this schedule
        $otherAsesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Lain',
            'email' => 'asesi.lain@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        PendaftaranAsesi::create([
            'asesi_id' => $otherAsesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-0002',
            'status_pendaftaran' => 'diverifikasi',
            'tanggal_daftar' => now()->toDateString(),
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $this->asesor->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.detail-verifikasi', $this->pendaftaran->id));

        $response->assertStatus(200);
        $response->assertSee('Seluruh Sesi Jadwal Sudah Penuh');
    }

    public function test_admin_cannot_acc_apl01_when_no_active_schedules_exist(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.verifikasi.simpan', $this->pendaftaran->id), [
            'status_pendaftaran' => 'diverifikasi',
            'tanda_tangan_admin_base64' => 'data:image/png;base64,validSignature',
        ]);

        $response->assertSessionHas('warning');
        $response->assertSessionHas('warning', function ($msg) {
            return str_contains($msg, 'Belum ada sesi jadwal uji aktif');
        });

        $this->pendaftaran->refresh();
        $this->assertEquals('diajukan', $this->pendaftaran->status_pendaftaran);
    }

    public function test_admin_cannot_acc_apl01_when_all_schedules_are_full(): void
    {
        $jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-FULL-02',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_uji' => now()->addDays(3)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'nama_tuk' => 'Lab Komputer 2',
            'kuota' => 1,
            'status_jadwal' => 'terjadwal',
        ]);

        $otherAsesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Kuota Penuh',
            'email' => 'asesi.penuh@example.com',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        PendaftaranAsesi::create([
            'asesi_id' => $otherAsesi->id,
            'skema_id' => $this->skema->id,
            'nomor_pendaftaran' => 'REG-2026-0003',
            'status_pendaftaran' => 'diverifikasi',
            'tanggal_daftar' => now()->toDateString(),
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $this->asesor->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.verifikasi.simpan', $this->pendaftaran->id), [
            'status_pendaftaran' => 'diverifikasi',
            'jadwal_id' => $jadwal->id,
            'tanda_tangan_admin_base64' => 'data:image/png;base64,validSignature',
        ]);

        $response->assertSessionHas('warning');
        $response->assertSessionHas('warning', function ($msg) {
            return str_contains($msg, 'penuh');
        });

        $this->pendaftaran->refresh();
        $this->assertEquals('diajukan', $this->pendaftaran->status_pendaftaran);
    }

    public function test_admin_can_acc_apl01_when_schedule_has_available_quota(): void
    {
        $jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-AVAIL-01',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_uji' => now()->addDays(4)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'nama_tuk' => 'Lab Komputer Utama',
            'kuota' => 10,
            'status_jadwal' => 'terjadwal',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.verifikasi.simpan', $this->pendaftaran->id), [
            'status_pendaftaran' => 'diverifikasi',
            'jadwal_id' => $jadwal->id,
            'tanda_tangan_admin_base64' => 'data:image/png;base64,validSignature',
            'catatan_verifikasi' => 'Berkas lengkap dan terverifikasi.',
        ]);

        $response->assertRedirect(route('admin.verifikasi-berkas'));
        $response->assertSessionHas('sukses');

        $this->pendaftaran->refresh();
        $this->assertEquals('diverifikasi', $this->pendaftaran->status_pendaftaran);
        $this->assertEquals('diterima', $this->pendaftaran->rekomendasi_admin_status);
        $this->assertEquals($jadwal->id, $this->pendaftaran->jadwal_id);
        $this->assertEquals($this->asesor->id, $this->pendaftaran->asesor_id);
    }
}
