<?php

namespace Tests\Feature;

use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJadwalEditTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $asesor1;
    protected Pengguna $asesor2;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Akun Admin
        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Super Administrator LSP',
            'email' => 'admin.test@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        // 2. Buat Skema Sertifikasi
        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-001',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
            'jenis_skema' => 'KKNI',
            'status_aktif' => true,
        ]);

        // 3. Buat Dua Asesor dengan kewenangan skema terkait
        $this->asesor1 = Pengguna::create([
            'nama_lengkap' => 'Asesor Pertama, S.Kom',
            'email' => 'asesor1@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.001.001',
            'skema_id' => $this->skema->id,
            'aktif' => true,
        ]);

        $this->asesor2 = Pengguna::create([
            'nama_lengkap' => 'Asesor Pengganti, M.Kom',
            'email' => 'asesor2@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.002.002',
            'skema_id' => $this->skema->id,
            'aktif' => true,
        ]);

        // 4. Buat Asesi
        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Peserta Uji Asesi',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        // 5. Buat Jadwal Awal
        $this->jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-TEST-001',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor1->id,
            'nama_tuk' => 'Lab Komputer 1 SMKN 1',
            'tanggal_uji' => now()->addDays(2)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '12:00',
            'kuota' => 10,
            'status_jadwal' => 'terjadwal',
        ]);
    }

    /** 1. Halaman Manajemen Jadwal Admin menampilkan tombol Edit dan Modal Edit Jadwal */
    public function test_admin_can_view_edit_button_and_modal_on_manajemen_jadwal_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.manajemen-jadwal'));

        $response->assertOk();
        $response->assertSee('Aksi');
        $response->assertSee('Edit');
        $response->assertSee('bukaModalEditJadwal', false);
        $response->assertSee('modalEditJadwal');
        $response->assertSee('formEditJadwal');
        $response->assertSee('edit_kode_jadwal');
        $response->assertSee('edit_status_jadwal');
        $response->assertSee('edit_skema_id');
        $response->assertSee('edit_asesor_id');
        $response->assertSee('edit_nama_tuk');
        $response->assertSee('edit_tanggal_uji');
        $response->assertSee('edit_waktu_mulai');
        $response->assertSee('edit_waktu_selesai');
        $response->assertSee('edit_kuota');
    }

    /** 2. Admin berhasil mengubah data jadwal uji kompetensi */
    public function test_admin_can_update_assessment_schedule_successfully(): void
    {
        $newTanggal = now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.manajemen-jadwal'))
            ->post(route('admin.jadwal.ubah', $this->jadwal->id), [
                'kode_jadwal' => 'JDW-TEST-001-REV',
                'skema_id' => $this->skema->id,
                'asesor_id' => $this->asesor1->id,
                'nama_tuk' => 'Lab RPL Gedung B Lantai 2',
                'tanggal_uji' => $newTanggal,
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '15:00',
                'kuota' => 20,
                'status_jadwal' => 'berlangsung',
            ]);

        $response->assertRedirect(route('admin.manajemen-jadwal'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('jadwal_asesmen', [
            'id' => $this->jadwal->id,
            'kode_jadwal' => 'JDW-TEST-001-REV',
            'nama_tuk' => 'Lab RPL Gedung B Lantai 2',
            'tanggal_uji' => $newTanggal,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '15:00',
            'kuota' => 20,
            'status_jadwal' => 'berlangsung',
        ]);
    }

    /** 3. Mengganti asesor pada jadwal akan mensinkronkan data pendaftaran asesi & mengirim notifikasi */
    public function test_updating_asesor_on_schedule_syncs_pendaftaran_and_sends_notification(): void
    {
        // Daftarkan asesi pada jadwal ini
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-001-TEST',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor1->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diterima',
        ]);

        $this->assertEquals($this->asesor1->id, $pendaftaran->asesor_id);

        // Admin mengubah asesor dari Asesor 1 ke Asesor 2
        $response = $this->actingAs($this->admin)
            ->post(route('admin.jadwal.ubah', $this->jadwal->id), [
                'kode_jadwal' => $this->jadwal->kode_jadwal,
                'skema_id' => $this->skema->id,
                'asesor_id' => $this->asesor2->id,
                'nama_tuk' => $this->jadwal->nama_tuk,
                'tanggal_uji' => $this->jadwal->tanggal_uji,
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:00',
                'kuota' => 15,
                'status_jadwal' => 'terjadwal',
            ]);

        $response->assertSessionHas('sukses');

        // Pendaftaran asesi otomatis terbarui ke asesor pengganti
        $this->assertEquals($this->asesor2->id, $pendaftaran->fresh()->asesor_id);

        // Notifikasi terkirim ke Asesor 2
        $notif = $this->asesor2->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('Penugasan Jadwal Asesmen Diperbarui', $notif->data['title']);
    }

    /** 4. Validasi: Gagal jika memilih asesor yang bukan untuk skema tersebut */
    public function test_updating_schedule_rejects_unauthorized_asesor(): void
    {
        $skemaLain = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-LAS-002',
            'nama_skema' => 'Pengelasan Kualifikasi II',
            'status_aktif' => true,
        ]);

        $asesorLain = Pengguna::create([
            'nama_lengkap' => 'Asesor Khusus Las',
            'email' => 'asesor.las@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $skemaLain->id,
            'aktif' => true,
        ]);

        // Coba pasangkan asesor las untuk skema RPL
        $response = $this->actingAs($this->admin)
            ->post(route('admin.jadwal.ubah', $this->jadwal->id), [
                'kode_jadwal' => $this->jadwal->kode_jadwal,
                'skema_id' => $this->skema->id,
                'asesor_id' => $asesorLain->id,
                'nama_tuk' => $this->jadwal->nama_tuk,
                'tanggal_uji' => $this->jadwal->tanggal_uji,
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:00',
                'kuota' => 10,
                'status_jadwal' => 'terjadwal',
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak memiliki kewenangan/role', session('error'));
    }

    /** 5. Hak Akses: Asesi dan Asesor tidak dapat mengubah jadwal asesmen */
    public function test_non_admin_cannot_update_assessment_schedule(): void
    {
        $responseAsesi = $this->actingAs($this->asesi)
            ->post(route('admin.jadwal.ubah', $this->jadwal->id), [
                'kode_jadwal' => 'JDW-HACKED',
                'skema_id' => $this->skema->id,
                'asesor_id' => $this->asesor1->id,
                'nama_tuk' => 'TUK Palsu',
                'tanggal_uji' => now()->toDateString(),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:00',
                'kuota' => 10,
                'status_jadwal' => 'berlangsung',
            ]);

        $responseAsesi->assertRedirect(route('asesi.dashboard'));

        $responseAsesor = $this->actingAs($this->asesor1)
            ->post(route('admin.jadwal.ubah', $this->jadwal->id), [
                'kode_jadwal' => 'JDW-HACKED',
                'skema_id' => $this->skema->id,
                'asesor_id' => $this->asesor1->id,
                'nama_tuk' => 'TUK Palsu',
                'tanggal_uji' => now()->toDateString(),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:00',
                'kuota' => 10,
                'status_jadwal' => 'berlangsung',
            ]);

        $responseAsesor->assertRedirect(route('asesor.dashboard'));

        // Pastikan data jadwal tidak berubah
        $this->assertDatabaseMissing('jadwal_asesmen', [
            'kode_jadwal' => 'JDW-HACKED',
        ]);
    }
}
