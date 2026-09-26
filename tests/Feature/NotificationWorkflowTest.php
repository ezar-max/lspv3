<?php

namespace Tests\Feature;

use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\SkemaSertifikasi;
use App\Notifications\SystemAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;
    protected Pengguna $superadmin;
    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected SkemaSertifikasi $skema;
    protected JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);
        Storage::fake('public');

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-NOTIF-01',
            'nama_skema' => 'Teknik Komputer & Jaringan',
            'jenis_skema' => 'KKNI',
            'jurusan' => 'TKJ',
            'deskripsi' => 'Skema Sertifikasi Pengujian Notifikasi',
            'status_aktif' => true,
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Penguji Notif',
            'email' => 'admin.notif@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
            'aktif' => true,
            'tanda_tangan' => 'signatures/admin_test.png',
        ]);

        $this->superadmin = Pengguna::create([
            'nama_lengkap' => 'Superadmin Penguji',
            'email' => 'superadmin.notif@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'superadmin',
            'aktif' => true,
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Kompeten Notif',
            'email' => 'asesor.notif@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'skema_id' => $this->skema->id,
            'nomor_registrasi' => 'MET.999.001',
            'aktif' => true,
            'tanda_tangan' => 'signatures/asesor_test.png',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Siswa Notif',
            'email' => 'asesi.notif@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
            'tanda_tangan' => 'signatures/asesi_test.png',
        ]);

        ProfilAsesi::create([
            'pengguna_id' => $this->asesi->id,
            'nik' => '3201010101010001',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2006-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Pengujian No. 123',
            'nomor_telepon' => '08123456789',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-NOTIF-01',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nama_tuk' => 'Lab Komputer 1',
            'tanggal_uji' => now()->addDays(2)->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'kuota' => 20,
            'status_jadwal' => 'terjadwal',
        ]);
    }

    /** 1. Halaman Notifikasi dapat diakses dan menampilkan data untuk semua role */
    public function test_notification_page_accessible_and_filters_work(): void
    {
        // Berikan notifikasi ke asesi
        $this->asesi->notify(new SystemAlert(
            'Pemberitahuan Uji Coba',
            'Ini adalah notifikasi uji coba untuk asesi.',
            route('asesi.dashboard'),
            'info'
        ));

        $response = $this->actingAs($this->asesi)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('Notifikasi', false);
        $response->assertSee('Pemberitahuan Uji Coba');
        $response->assertSee('Total Notifikasi');

        // Uji filter unread
        $responseUnread = $this->actingAs($this->asesi)->get(route('notifications.index', ['filter' => 'unread']));
        $responseUnread->assertOk();
        $responseUnread->assertSee('Pemberitahuan Uji Coba');

        // Uji untuk role Asesor juga
        $responseAsesor = $this->actingAs($this->asesor)->get(route('notifications.index'));
        $responseAsesor->assertOk();
        $responseAsesor->assertSee('Notifikasi', false);

        // Uji untuk role Admin juga
        $responseAdmin = $this->actingAs($this->admin)->get(route('notifications.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Notifikasi', false);
    }

    /** 2. Menandai notifikasi sebagai dibaca dan redirect ke target URL */
    public function test_mark_notification_as_read(): void
    {
        $this->asesi->notify(new SystemAlert(
            'Formulir Tersedia',
            'Silakan lanjutkan pengisian formulir.',
            route('asesi.dashboard'),
            'approved'
        ));

        $notif = $this->asesi->notifications()->first();
        $this->assertNull($notif->read_at);

        // Akses via POST read
        $response = $this->actingAs($this->asesi)->post(route('notifications.read', $notif->id));
        $response->assertRedirect(route('asesi.dashboard'));

        $notif->refresh();
        $this->assertNotNull($notif->read_at);
    }

    /** 3. Menandai seluruh notifikasi sebagai dibaca */
    public function test_mark_all_notifications_as_read(): void
    {
        $this->admin->notify(new SystemAlert('Notif 1', 'Pesan 1'));
        $this->admin->notify(new SystemAlert('Notif 2', 'Pesan 2'));

        $this->assertEquals(2, $this->admin->unreadNotifications()->count());

        $response = $this->actingAs($this->admin)->post(route('notifications.read_all'));
        $response->assertSessionHas('sukses');

        $this->assertEquals(0, $this->admin->unreadNotifications()->count());
    }

    /** 4. Menghapus notifikasi spesifik */
    public function test_delete_notification(): void
    {
        $this->asesi->notify(new SystemAlert('Notif Hapus', 'Pesan'));
        $notif = $this->asesi->notifications()->first();

        $response = $this->actingAs($this->asesi)->delete(route('notifications.destroy', $notif->id));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
    }

    /** 5. ROLE ADMIN: Asesi mengajukan FR.APL.01 memicu notifikasi ke Admin & Superadmin */
    public function test_asesi_submitting_apl01_notifies_admins_for_acc(): void
    {
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'APL01-20260906-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => 'Sertifikasi',
            'status_pendaftaran' => 'draft',
        ]);

        $fakeRapor = UploadedFile::fake()->create('rapor.pdf', 200, 'application/pdf');
        $fakePkl = UploadedFile::fake()->create('pkl.pdf', 200, 'application/pdf');
        $fakeKtp = UploadedFile::fake()->create('ktp.pdf', 200, 'application/pdf');
        $fakeFoto = UploadedFile::fake()->create('pasfoto.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->asesi)->post(route('asesi.tahapan.apl01'), [
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $this->skema->id,
            'nama_lengkap' => 'Asesi Siswa Notif',
            'nik' => '3201010101010001',
            'tempat_lahir' => 'Bogor',
            'tanggal_lahir' => '2006-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Pengujian No. 123',
            'nomor_telepon' => '08123456789',
            'tujuan_asesmen' => 'Sertifikasi',
            'nama_sekolah_instansi' => 'SMKN 1 Gunungputri',
            'pekerjaan' => 'Pelajar',
            'aksi' => 'ajukan',
            'tanda_tangan_asesi' => 'data:image/png;base64,sample_signature',
            'file_rapor' => $fakeRapor,
            'file_pkl' => $fakePkl,
            'file_ktp' => $fakeKtp,
            'file_foto' => $fakeFoto,
        ]);

        $response->assertSessionHas('sukses');

        // Periksa bahwa Admin menerima notifikasi permintaan ACC
        $adminNotifs = $this->admin->notifications;
        $this->assertTrue($adminNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Permohonan ACC FR.APL.01')
                && str_contains($n->data['message'], 'Asesi Siswa Notif');
        }));

        // Periksa bahwa Superadmin juga menerima notifikasi
        $superadminNotifs = $this->superadmin->notifications;
        $this->assertTrue($superadminNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Permohonan ACC FR.APL.01');
        }));
    }

    /** 6. ROLE ASESI & ASESOR: Admin meng-ACC berkas pendaftaran memicu notifikasi ke Asesi dan Asesor */
    public function test_admin_approving_apl01_notifies_asesi_and_asesor(): void
    {
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'APL01-20260906-0002',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diajukan',
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.verifikasi.simpan', $pendaftaran->id), [
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'jadwal_id' => $this->jadwal->id,
            'catatan_verifikasi' => 'Berkas valid dan lengkap.',
        ]);

        $response->assertRedirect(route('admin.verifikasi-berkas'));
        $response->assertSessionHas('sukses');

        // Asesi menerima notifikasi bahwa berkas di-ACC dan lanjut ke FR.APL.02
        $asesiNotifs = $this->asesi->notifications;
        $this->assertTrue($asesiNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Berkas Pendaftaran Disetujui (ACC)')
                && str_contains($n->data['message'], 'FR.APL.02');
        }));

        // Asesor menerima notifikasi penugasan asesi baru pada jadwal terkait
        $asesorNotifs = $this->asesor->notifications;
        $this->assertTrue($asesorNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Penugasan Asesi Baru')
                && str_contains($n->data['message'], 'Asesi Siswa Notif')
                && str_contains($n->data['message'], 'JDW-NOTIF-01');
        }));
    }

    /** 7. ROLE ASESOR: Asesi memilih jadwal asesor memicu notifikasi penugasan ke Asesor */
    public function test_asesi_choosing_asesor_notifies_asesor(): void
    {
        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'APL01-20260906-0003',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'diajukan',
        ]);

        $response = $this->actingAs($this->asesi)->post(route('asesi.pilih-asesor', $pendaftaran->id), [
            'jadwal_id' => $this->jadwal->id,
        ]);

        $response->assertSessionHas('sukses');

        $asesorNotifs = $this->asesor->notifications;
        $this->assertTrue($asesorNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Penugasan Asesi Baru')
                && str_contains($n->data['message'], 'JDW-NOTIF-01');
        }));
    }

    /** 8. ROLE ASESOR: Admin membuat jadwal asesmen baru memicu notifikasi ke Asesor terkait */
    public function test_admin_creating_schedule_notifies_asesor(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.simpan'), [
            'kode_jadwal' => 'JDW-NOTIF-BARU-99',
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'nama_tuk' => 'Lab Jaringan 2',
            'tanggal_uji' => now()->addDays(5)->format('Y-m-d'),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '17:00',
            'kuota' => 15,
        ]);

        $response->assertSessionHas('sukses');

        $asesorNotifs = $this->asesor->notifications;
        $this->assertTrue($asesorNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'Penugasan Jadwal Asesmen Baru')
                && str_contains($n->data['message'], 'JDW-NOTIF-BARU-99');
        }));
    }

    /** 9. ROLE ADMIN: Asesor menyusun Master FR.MAPA.01 memicu notifikasi validasi ke Admin & Superadmin */
    public function test_asesor_saving_master_mapa01_notifies_admins_for_validation(): void
    {
        $response = $this->actingAs($this->asesor)->post(route('asesor.skema.mapa-01.simpan', $this->skema->id), [
            'aksi' => 'konfirmasi',
            'tujuan_asesmen' => 'Sertifikasi Kejuruan',
            'konteks_lingkungan' => 'Tempat kerja simulasi',
            'konteks_peluang_bukti' => 'Tersedia',
        ]);

        $response->assertSessionHas('sukses');

        // Admin menerima notifikasi bahwa FR.MAPA.01 sesuai skema perlu divalidasi
        $adminNotifs = $this->admin->notifications;
        $this->assertTrue($adminNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'FR.MAPA.01 Sesuai Skema Menunggu Validasi')
                && str_contains($n->data['message'], $this->skema->nama_skema);
        }));

        // Superadmin juga menerima notifikasi
        $superadminNotifs = $this->superadmin->notifications;
        $this->assertTrue($superadminNotifs->contains(function ($n) {
            return str_contains($n->data['title'], 'FR.MAPA.01 Sesuai Skema Menunggu Validasi')
                && str_contains($n->data['message'], $this->skema->kode_skema);
        }));
    }

    /** 10. ROLE ADMIN: Halaman notifikasi menampilkan pemberitahuan form MAPA 01 sesuai skema yang perlu divalidasi */
    public function test_admin_notification_page_displays_mapa01_scheme_pending_validation(): void
    {
        // Buat Master MAPA.01 sesuai skema dalam keadaan belum divalidasi
        \App\Models\Mapa01::create([
            'skema_id' => $this->skema->id,
            'pendaftaran_id' => null,
            'asesor_id' => $this->asesor->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'penyusun_validator_tabel' => [
                'penyusun_1' => [
                    'nama' => $this->asesor->nama_lengkap,
                    'nomor_met' => 'MET.999.001',
                    'ttd' => 'signatures/asesor_test.png',
                    'ttd_tanggal' => now()->format('d/m/Y'),
                ],
                'validator_1' => [
                    'nama' => '',
                    'nomor_met' => '',
                    'ttd' => null,
                    'status_validasi' => null,
                ]
            ],
            'status_mapa' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('FR.MAPA.01 Sesuai Skema Menunggu Validasi');
        $response->assertSee($this->skema->nama_skema);
        $response->assertSee('Dokumen FR.MAPA.01 Sesuai Skema Perlu Divalidasi');
        $response->assertSee(route('asesor.skema.mapa-01', $this->skema->id));
    }

    /** 11. ROLE ADMIN: Admin memvalidasi Master FR.MAPA.01 menandai notifikasi sebagai dibaca */
    public function test_admin_validating_master_mapa01_marks_notification_as_read(): void
    {
        // 1. Asesor simpan master MAPA 01
        $this->actingAs($this->asesor)->post(route('asesor.skema.mapa-01.simpan', $this->skema->id), [
            'aksi' => 'konfirmasi',
            'tujuan_asesmen' => 'Sertifikasi',
        ]);

        $notif = $this->admin->unreadNotifications()->first();
        $this->assertNotNull($notif);
        $this->assertNull($notif->read_at);

        // 2. Admin memvalidasi Master MAPA 01
        $response = $this->actingAs($this->admin)->post(route('admin.mapa-01.validasi', $this->skema->id), [
            'skema_id' => $this->skema->id,
            'validator_nama' => $this->admin->nama_lengkap,
            'validator_nomor_met' => 'ADM.001.2026',
        ]);

        $response->assertSessionHas('sukses');

        $notif->refresh();
        $this->assertNotNull($notif->read_at);
    }
}
