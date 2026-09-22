<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\JadwalAsesmen;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\MasterQuestionBank;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentScheduleTimeLockTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesorLas;
    protected Pengguna $asesorRpl;
    protected Pengguna $asesiLas;
    protected Pengguna $asesiRpl;

    protected SkemaSertifikasi $skemaLas;
    protected SkemaSertifikasi $skemaRpl;

    protected JadwalAsesmen $jadwalLas;
    protected JadwalAsesmen $jadwalRpl;

    protected PendaftaranAsesi $pendaftaranLas;
    protected PendaftaranAsesi $pendaftaranRpl;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Asesor Pengelasan & Asesor RPL
        $this->asesorLas = Pengguna::create([
            'nama_lengkap' => 'Bambang Las, ST (Asesor Pengelasan)',
            'email' => 'asesor.las@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001.LAS',
            'tanda_tangan' => 'signatures/asesor_las.png'
        ]);

        $this->asesorRpl = Pengguna::create([
            'nama_lengkap' => 'Rina RPL, M.Kom (Asesor RPL)',
            'email' => 'asesor.rpl@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.002.RPL',
            'tanda_tangan' => 'signatures/asesor_rpl.png'
        ]);

        // 2. Asesi Pengelasan & Asesi RPL
        $this->asesiLas = Pengguna::create([
            'nama_lengkap' => 'Ahmad Las (Siswa Pengelasan)',
            'email' => 'ahmad.las@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        $this->asesiRpl = Pengguna::create([
            'nama_lengkap' => 'Budi RPL (Siswa RPL)',
            'email' => 'budi.rpl@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
        ]);

        // 3. Master Skema Pengelasan & RPL
        $this->skemaLas = SkemaSertifikasi::create([
            'kode_skema' => 'KKNI-II-LAS-001',
            'nama_skema' => 'Pengelasan Kualifikasi II',
            'jenis_skema' => 'KKNI',
            'aktif' => true,
        ]);

        $this->skemaRpl = SkemaSertifikasi::create([
            'kode_skema' => 'KKNI-II-RPL-001',
            'nama_skema' => 'Rekayasa Perangkat Lunak Kualifikasi II',
            'jenis_skema' => 'KKNI',
            'aktif' => true,
        ]);

        // 4. Units, Elements & Master Instruments
        $unitLas = UnitKompetensi::create([
            'skema_id' => $this->skemaLas->id,
            'kode_unit' => 'C.24LAS01.001.1',
            'judul_unit' => 'Melaksanakan K3LH di Bengkel Las',
        ]);
        $elemenLas = ElemenKompetensi::create([
            'unit_id' => $unitLas->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menyiapkan APD Las',
        ]);
        $kukLas = KriteriaUnjukKerja::create([
            'elemen_id' => $elemenLas->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Topeng las dan apron dipakai sesuai SOP',
        ]);

        $instrumenLas = SchemeMasterInstrument::create([
            'skema_id' => $this->skemaLas->id,
            'instrument_code' => 'FR.IA.05',
            'title' => 'Pertanyaan Tertulis Pilihan Ganda (Las)',
            'is_active' => true,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instrumenLas->id,
            'kuk_id' => $kukLas->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Fungsi utama filter shade 10-12 pada helm las SMAW adalah?',
            'options' => [
                'A' => 'Melindungi mata dari radiasi UV dan inframerah',
                'B' => 'Menahan panas saja',
                'C' => 'Mencegah debu',
                'D' => 'Sebagai aksesoris kerja',
            ],
            'correct_answer' => 'A',
            'order' => 1
        ]);

        $unitRpl = UnitKompetensi::create([
            'skema_id' => $this->skemaRpl->id,
            'kode_unit' => 'J.620100.001.01',
            'judul_unit' => 'Menulis Kode Program Bersih',
        ]);
        $elemenRpl = ElemenKompetensi::create([
            'unit_id' => $unitRpl->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menerapkan Standar Coding',
        ]);
        $kukRpl = KriteriaUnjukKerja::create([
            'elemen_id' => $elemenRpl->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Struktur folder dan naming convention diterapkan',
        ]);

        $instrumenRpl = SchemeMasterInstrument::create([
            'skema_id' => $this->skemaRpl->id,
            'instrument_code' => 'FR.IA.05',
            'title' => 'Pertanyaan Tertulis Pilihan Ganda (RPL)',
            'is_active' => true,
        ]);

        MasterQuestionBank::create([
            'scheme_master_instrument_id' => $instrumenRpl->id,
            'kuk_id' => $kukRpl->id,
            'question_type' => 'multiple_choice',
            'question_text' => 'Konsep OOP yang membungkus data dan fungsi adalah?',
            'options' => [
                'A' => 'Enkapsulasi',
                'B' => 'Polimorfisme',
                'C' => 'Inheritansi',
                'D' => 'Abstraksi',
            ],
            'correct_answer' => 'A',
            'order' => 1
        ]);

        // 5. Active Schedules
        $this->jadwalLas = JadwalAsesmen::create([
            'skema_id' => $this->skemaLas->id,
            'asesor_id' => $this->asesorLas->id,
            'kode_jadwal' => 'JDW-LAS-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '12:00',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK Bengkel Las SMKN 1 Gunungputri',
        ]);

        $this->jadwalRpl = JadwalAsesmen::create([
            'skema_id' => $this->skemaRpl->id,
            'asesor_id' => $this->asesorRpl->id,
            'kode_jadwal' => 'JDW-RPL-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '13:00',
            'waktu_selesai' => '17:00',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK Lab RPL SMKN 1 Gunungputri',
        ]);

        // 6. Registrations
        $this->pendaftaranLas = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-LAS-001',
            'asesi_id' => $this->asesiLas->id,
            'skema_id' => $this->skemaLas->id,
            'jadwal_id' => $this->jadwalLas->id,
            'asesor_id' => $this->asesorLas->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diterima',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'approved',
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_las.png',
            'tanda_tangan_asesor_ak01' => 'signatures/asesor_las.png',
            'tanggal_ttd_asesi_ak01' => now(),
            'tanggal_ttd_asesor_ak01' => now()
        ]);

        $this->pendaftaranRpl = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-RPL-001',
            'asesi_id' => $this->asesiRpl->id,
            'skema_id' => $this->skemaRpl->id,
            'jadwal_id' => $this->jadwalRpl->id,
            'asesor_id' => $this->asesorRpl->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'diterima',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'approved',
            'status_ak01' => 'selesai',
            'tanda_tangan_asesi_ak01' => 'signatures/asesi_rpl.png',
            'tanda_tangan_asesor_ak01' => 'signatures/asesor_rpl.png',
            'tanggal_ttd_asesi_ak01' => now(),
            'tanggal_ttd_asesor_ak01' => now()
        ]);

        // MAPA Confirmation for both
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'skema_id' => $this->skemaLas->id,
            'asesor_id' => $this->asesorLas->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'skema_id' => $this->skemaLas->id,
            'asesor_id' => $this->asesorLas->id,
            'matriks_peta' => [$unitLas->id => [$elemenLas->id => [$kukLas->id => ['dpt' => 1]]]],
            'status_mapa' => 'selesai',
        ]);

        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaranRpl->id,
            'skema_id' => $this->skemaRpl->id,
            'asesor_id' => $this->asesorRpl->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaranRpl->id,
            'skema_id' => $this->skemaRpl->id,
            'asesor_id' => $this->asesorRpl->id,
            'matriks_peta' => [$unitRpl->id => [$elemenRpl->id => [$kukRpl->id => ['dpt' => 1]]]],
            'status_mapa' => 'selesai',
        ]);

        \App\Models\AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaranLas->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'status' => 'confirmed',
            'asesi_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'asesi_signed_at' => now(),
        ]);

        \App\Models\AssessmentAk07Adjustment::create([
            'assessment_registration_id' => $this->pendaftaranRpl->id,
            'potensi_asesi' => 1,
            'fase_penggunaan' => 'saat_pra_asesmen',
            'status' => 'confirmed',
            'asesi_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'asesi_signed_at' => now(),
        ]);
    }

    /** 1. Multi-Skema Isolation: Asesor Las only sees Welding candidates */
    public function test_asesor_scoping_isolation_in_participant_list(): void
    {
        $responseLas = $this->actingAs($this->asesorLas)->get(route('asesor.penilaian'));
        $responseLas->assertOk();
        $responseLas->assertSee('Ahmad Las (Siswa Pengelasan)');
        $responseLas->assertDontSee('Budi RPL (Siswa RPL)');

        $responseRpl = $this->actingAs($this->asesorRpl)->get(route('asesor.penilaian'));
        $responseRpl->assertOk();
        $responseRpl->assertSee('Budi RPL (Siswa RPL)');
        $responseRpl->assertDontSee('Ahmad Las (Siswa Pengelasan)');
    }

    /** 2. Multi-Skema Isolation: Asesor Las cannot access or grade RPL candidate */
    public function test_cross_asesor_assessment_access_is_strictly_forbidden(): void
    {
        // Asesor Las tries to open Live Assessment of RPL candidate -> 404
        $this->actingAs($this->asesorLas)
            ->get(route('asesor.penilaian-live', $this->pendaftaranRpl->id))
            ->assertNotFound();

        // Asesor Las tries to submit grade for RPL candidate -> 404
        $this->actingAs($this->asesorLas)
            ->post(route('asesor.penilaian-live.simpan', $this->pendaftaranRpl->id), [
                'keputusan' => 'kompeten',
                'metode_ttd' => 'profil'
            ])
            ->assertNotFound();
    }

    /** 3. Question Bank Isolation: Asesi Las only sees Welding questions in Ruang Uji */
    public function test_question_bank_isolation_for_asesi(): void
    {
        $respLas = $this->actingAs($this->asesiLas)->followingRedirects()->get(route('asesi.ujian', ['pendaftaran_id' => $this->pendaftaranLas->id]));
        $respLas->assertOk();
        $respLas->assertSee('Fungsi utama filter shade 10-12 pada helm las SMAW adalah?');
        $respLas->assertDontSee('Konsep OOP yang membungkus data dan fungsi adalah?');

        $respRpl = $this->actingAs($this->asesiRpl)->followingRedirects()->get(route('asesi.ujian', ['pendaftaran_id' => $this->pendaftaranRpl->id]));
        $respRpl->assertOk();
        $respRpl->assertSee('Konsep OOP yang membungkus data dan fungsi adalah?');
        $respRpl->assertDontSee('Fungsi utama filter shade 10-12 pada helm las SMAW adalah?');
    }

    /** 4. Time-Lock: Pre-schedule gate blocks POST and indicates opening time */
    public function test_time_lock_pre_schedule_blocks_submission(): void
    {
        // Set schedule to tomorrow
        $this->jadwalLas->update([
            'tanggal_uji' => now()->addDay()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '12:00',
            'status_jadwal' => 'terjadwal'
        ]);

        $this->assertTrue($this->jadwalLas->isBelumMulai());

        // Autosave is blocked
        $response = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $response->assertStatus(403);
        $response->assertJsonFragment(['status' => 'error']);
    }

    /** 5. Time-Lock: Manual Admin Override (status_jadwal === 'berlangsung') allows access */
    public function test_manual_admin_override_allows_access(): void
    {
        // Schedule is yesterday, but Admin LSP set status to 'berlangsung'
        $this->jadwalLas->update([
            'tanggal_uji' => now()->subDays(2)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status_jadwal' => 'berlangsung'
        ]);

        $this->assertTrue($this->jadwalLas->isWaktuAktif());

        $response = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $response->assertOk();
        $response->assertJson(['status' => 'success']);
    }

    /** 6. Time-Lock: Grace Period (10 minutes) allows submission right after schedule end time */
    public function test_grace_period_allows_submission_within_10_minutes(): void
    {
        $now = Carbon::create(2026, 9, 18, 14, 0, 0, config('app.timezone', 'Asia/Jakarta'));
        Carbon::setTestNow($now);

        try {
            // Suppose schedule ended 4 minutes ago today
            $this->jadwalLas->update([
                'tanggal_uji' => $now->toDateString(),
                'waktu_mulai' => $now->copy()->subHours(2)->format('H:i'),
                'waktu_selesai' => $now->copy()->subMinutes(4)->format('H:i'),
                'status_jadwal' => 'terjadwal'
            ]);

            // Within grace period (4 min <= 10 min), isSudahSelesai(true, 10) is false
            $this->assertFalse($this->jadwalLas->isSudahSelesai(true, 10));

            // Autosave succeeds under grace period
            $response = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
                'pendaftaran_id' => $this->pendaftaranLas->id,
                'tipe' => 'cbt',
                'no' => 1,
                'jawaban' => 'A'
            ]);
            $response->assertOk();
        } finally {
            Carbon::setTestNow(null);
        }
    }

    /** 7. Time-Lock: Submission after grace period expires is rejected */
    public function test_submission_after_grace_period_is_rejected(): void
    {
        $now = Carbon::create(2026, 9, 18, 14, 0, 0, config('app.timezone', 'Asia/Jakarta'));
        Carbon::setTestNow($now);

        try {
            // Suppose schedule ended 30 minutes ago today
            $this->jadwalLas->update([
                'tanggal_uji' => $now->toDateString(),
                'waktu_mulai' => $now->copy()->subHours(3)->format('H:i'),
                'waktu_selesai' => $now->copy()->subMinutes(30)->format('H:i'),
                'status_jadwal' => 'terjadwal'
            ]);

            // After grace period, isSudahSelesai(true, 10) is true
            $this->assertTrue($this->jadwalLas->isSudahSelesai(true, 10));

            // Autosave blocked with 403
            $response = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
                'pendaftaran_id' => $this->pendaftaranLas->id,
                'tipe' => 'cbt',
                'no' => 1,
                'jawaban' => 'A'
            ]);
            $response->assertStatus(403);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    /** 8. Time-Lock: Cancelled schedule is rejected */
    public function test_cancelled_schedule_is_rejected(): void
    {
        $this->jadwalLas->update([
            'status_jadwal' => 'dibatalkan'
        ]);

        $response = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $response->assertStatus(403);
    }

    /** 9. Live Assessment Started indicator rendered on Asesi and Asesor views */
    public function test_live_assessment_started_indicator_rendered_on_views(): void
    {
        $this->jadwalLas->update([
            'status_jadwal' => 'berlangsung',
            'waktu_selesai' => '23:59',
        ]);

        $this->assertTrue($this->pendaftaranLas->isRuangUjiOpen());

        // 1. Asesi Dashboard
        $asesiDashResp = $this->actingAs($this->asesiLas)->get(route('asesi.dashboard'));
        $asesiDashResp->assertOk();
        $asesiDashResp->assertSee('Sesi Asesmen Telah Dimulai!');

        // 2. Asesi Ruang Uji
        $asesiUjianResp = $this->actingAs($this->asesiLas)->followingRedirects()->get(route('asesi.ujian', ['pendaftaran_id' => $this->pendaftaranLas->id]));
        $asesiUjianResp->assertOk();
        $asesiUjianResp->assertSee('Sesi Ujian Aktif');

        // 3. Asesi Tahapan
        $asesiTahapanResp = $this->actingAs($this->asesiLas)->get(route('asesi.tahapan', ['pendaftaran_id' => $this->pendaftaranLas->id]));
        $asesiTahapanResp->assertOk();
        $asesiTahapanResp->assertSee('Sesi Asesmen Telah Dimulai!');

        // 4. Asesor Dashboard
        $asesorDashResp = $this->actingAs($this->asesorLas)->get(route('asesor.dashboard'));
        $asesorDashResp->assertOk();
        $asesorDashResp->assertSee('Live Dimulai');

        // 5. Asesor Daftar Peserta
        $asesorPesertaResp = $this->actingAs($this->asesorLas)->get(route('asesor.daftar-peserta', ['jadwal_id' => $this->jadwalLas->id]));
        $asesorPesertaResp->assertOk();
        $asesorPesertaResp->assertSee('Ujian Dimulai');

        // 6. Asesor Penilaian Live
        $asesorLiveResp = $this->actingAs($this->asesorLas)->get(route('asesor.penilaian-live', ['pendaftaranId' => $this->pendaftaranLas->id]));
        $asesorLiveResp->assertOk();
        $asesorLiveResp->assertSee('Asesmen Dimulai (Sesi Aktif)');
    }

    /** 10. Jadwal yang belum dimulai (termasuk lintas tengah malam: 23:00 - 00:00) tidak boleh berstatus selesai */
    public function test_unstarted_schedule_with_cross_midnight_or_future_time_is_terjadwal_not_selesai(): void
    {
        $now = Carbon::create(2026, 9, 18, 20, 0, 0, config('app.timezone', 'Asia/Jakarta'));
        Carbon::setTestNow($now);

        try {
            // Buat jadwal malam ini 20:30 - 00:00 WIB (belum mulai pada jam 20:00)
            $jadwalMalam = JadwalAsesmen::create([
                'kode_jadwal' => 'JDW-MIDNIGHT-01',
                'skema_id' => $this->skemaLas->id,
                'asesor_id' => $this->asesorLas->id,
                'nama_tuk' => 'Bengkel Pengelasan Malam',
                'tanggal_uji' => $now->toDateString(),
                'waktu_mulai' => $now->copy()->addMinutes(30)->format('H:i'),
                'waktu_selesai' => '00:00',
                'kuota' => 10,
                'status_jadwal' => 'selesai', // disimulasikan sebelumnya salah status 'selesai'
            ]);

            // Verifikasi waktu selesai carbon adalah hari berikutnya (lintas hari)
            $this->assertTrue($jadwalMalam->waktu_selesai_carbon->isAfter($jadwalMalam->waktu_mulai_carbon));

            // Karena waktu mulai belum tiba, isBelumMulai() harus true dan isSudahSelesai() harus false
            $this->assertTrue($jadwalMalam->isBelumMulai());
            $this->assertFalse($jadwalMalam->isSudahSelesai());

            // Jalankan sinkronisasi status
            JadwalAsesmen::syncAllStatuses();

            // Status di database wajib terkoreksi menjadi 'terjadwal', BUKAN 'selesai'
            $jadwalMalamFresh = $jadwalMalam->fresh();
            $this->assertEquals('terjadwal', $jadwalMalamFresh->status_jadwal);
            $this->assertEquals('belum_mulai', $jadwalMalamFresh->time_status['status']);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    /** 11. Halaman Manajemen Jadwal Admin menampilkan lencana 'Terjadwal' untuk sesi yang belum dimulai */
    public function test_admin_schedule_management_view_shows_terjadwal_for_unstarted_session(): void
    {
        $admin = Pengguna::create([
            'nama_lengkap' => 'Admin Penguji LSP',
            'email' => 'admin.jadwal@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'admin',
        ]);

        $now = Carbon::now(config('app.timezone', 'Asia/Jakarta'));
        $futureTime = $now->copy()->addHours(2);

        $jadwalFuture = JadwalAsesmen::create([
            'kode_jadwal' => 'JDW-FUTURE-99',
            'skema_id' => $this->skemaLas->id,
            'asesor_id' => $this->asesorLas->id,
            'nama_tuk' => 'Bengkel Pengelasan',
            'tanggal_uji' => $futureTime->toDateString(),
            'waktu_mulai' => $futureTime->format('H:i'),
            'waktu_selesai' => $futureTime->copy()->addHours(3)->format('H:i'),
            'kuota' => 10,
            'status_jadwal' => 'selesai', // Status awal salah
        ]);

        $response = $this->actingAs($admin)->get(route('admin.manajemen-jadwal'));
        $response->assertOk();

        // Harus menampilkan badge Terjadwal dan status di DB sudah menjadi 'terjadwal'
        $response->assertSee('Terjadwal');
        $this->assertEquals('terjadwal', $jadwalFuture->fresh()->status_jadwal);
    }

    /** 12. Asesmen hanya bisa diakses, diisi, dan dikerjakan saat jadwal telah dimulai */
    public function test_asesmen_hanya_bisa_diisi_dan_dikerjakan_saat_jadwal_dimulai(): void
    {
        // 1. KONDISI: Jadwal Belum Dimulai (Besok)
        $this->jadwalLas->update([
            'tanggal_uji' => now()->addDays(1)->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '12:00',
            'status_jadwal' => 'terjadwal',
        ]);

        $this->assertTrue($this->jadwalLas->isBelumMulai());

        // Buka halaman tahapan formulir step 5
        $respBelumMulai = $this->actingAs($this->asesiLas)
            ->get(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaranLas->id]));

        $respBelumMulai->assertOk();
        // Memastikan layar tunggu jadwal muncul dengan countdown dan rincian jadwal
        $respBelumMulai->assertSee('Menunggu Waktu Pelaksanaan Asesmen');
        $respBelumMulai->assertSee('Asesmen Dimulai Dalam:');
        $respBelumMulai->assertSee('Menunggu Jadwal Mulai');
        // Memastikan soal ujian TIDAK bocor dan TIDAK bisa diisi/dikerjakan
        $respBelumMulai->assertDontSee('Fungsi utama filter shade 10-12 pada helm las SMAW adalah?');
        $respBelumMulai->assertDontSee('FR.IA.05 (Ujian Teori CBT PG)');

        // Percobaan pengiriman autosave diblokir dengan 403 Forbidden
        $postBelumMulai = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $postBelumMulai->assertStatus(403);
        $postBelumMulai->assertJsonFragment([
            'status' => 'error'
        ]);

        // 2. KONDISI: Jadwal Telah Dimulai (Status 'berlangsung')
        $this->jadwalLas->update([
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '07:00',
            'waktu_selesai' => '17:00',
            'status_jadwal' => 'berlangsung',
        ]);

        $this->assertTrue($this->jadwalLas->isWaktuAktif());

        // Buka kembali halaman tahapan formulir step 5
        $respSudahMulai = $this->actingAs($this->asesiLas)
            ->get(route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $this->pendaftaranLas->id]));

        $respSudahMulai->assertOk();
        // Memastikan lencana aktif muncul dan layar tunggu menghilang
        $respSudahMulai->assertSee('Sesi Ujian Aktif');
        $respSudahMulai->assertDontSee('Menunggu Waktu Pelaksanaan Asesmen');
        // Lembar soal terbuka dan siap diisi
        $respSudahMulai->assertSee('FR.IA.05 (Ujian Teori CBT PG)');
        $respSudahMulai->assertSee('Fungsi utama filter shade 10-12 pada helm las SMAW adalah?');

        // Autosave berhasil disimpan
        $postSudahMulai = $this->actingAs($this->asesiLas)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaranLas->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $postSudahMulai->assertOk();
        $postSudahMulai->assertJsonFragment(['status' => 'success']);
    }
}

