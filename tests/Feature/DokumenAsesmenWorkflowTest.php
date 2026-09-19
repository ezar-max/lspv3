<?php

namespace Tests\Feature;

use App\Models\AssessmentAk02;
use App\Models\AssessmentAk03;
use App\Models\AssessmentAk05;
use App\Models\AssessmentAk06;
use App\Models\AssessmentVa;
use App\Models\DocumentAuditLog;
use App\Models\ElemenKompetensi;
use App\Models\JadwalAsesmen;
use App\Models\KriteriaUnjukKerja;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenAsesmenWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $superadmin;
    protected Pengguna $admin;
    protected Pengguna $asesor;
    protected Pengguna $asesi1;
    protected Pengguna $asesi2;
    protected SkemaSertifikasi $skema;
    protected UnitKompetensi $unit1;
    protected UnitKompetensi $unit2;
    protected JadwalAsesmen $jadwal;
    protected PendaftaranAsesi $pendaftaran1;
    protected PendaftaranAsesi $pendaftaran2;

    protected function setUp(): void
    {
        parent::setUp();
        if (!\Illuminate\Support\Facades\Route::has('dokumen-asesmen.index')) {
            $this->markTestSkipped('Routes dokumen-asesmen dinonaktifkan sementara sesuai instruksi user.');
        }
        config(['auth.providers.users.model' => Pengguna::class]);
        Storage::fake('public');

        // 1. Users
        $this->superadmin = Pengguna::create([
            'nama_lengkap' => 'Superadmin LSP',
            'email' => 'superadmin@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'superadmin',
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'admin',
        ]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Kompeten',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.009988',
            'tanda_tangan' => 'signatures/asesor.png',
        ]);

        $this->asesi1 = Pengguna::create([
            'nama_lengkap' => 'Budi Asesi Satu',
            'email' => 'budi@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
            'nomor_registrasi' => 'REG-001',
            'tanda_tangan' => 'signatures/budi.png',
        ]);

        $this->asesi2 = Pengguna::create([
            'nama_lengkap' => 'Siti Asesi Dua',
            'email' => 'siti@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
            'nomor_registrasi' => 'REG-002',
            'tanda_tangan' => 'signatures/siti.png',
        ]);

        // 2. Skema & Units
        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-01',
            'nama_skema' => 'Pemrograman Web Madya',
            'jenis_skema' => 'KKNI',
            'deskripsi' => 'Skema keahlian web engineering SMK',
            'status_aktif' => true,
        ]);

        $this->unit1 = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.004.01',
            'judul_unit' => 'Mengimplementasikan Algoritma Pemrograman',
        ]);

        $this->unit2 = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.005.01',
            'judul_unit' => 'Membuat Dokumen Kode Program',
        ]);

        // 3. Jadwal & Pendaftaran
        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesor->id,
            'kode_jadwal' => 'JDW-RPL-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK Mandiri SMKN 1 Gunungputri',
        ]);

        $this->pendaftaran1 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG/2026/RPL/001',
            'asesi_id' => $this->asesi1->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'disetujui',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'disetujui',
            'status_ak01' => 'disetujui',
        ]);

        $this->pendaftaran2 = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG/2026/RPL/002',
            'asesi_id' => $this->asesi2->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'disetujui',
            'status_apl01' => 'disetujui',
            'status_apl02' => 'disetujui',
            'status_ak01' => 'disetujui',
        ]);
    }

    /**
     * 1. Hub Dokumen Asesmen accessible by roles
     */
    public function test_dokumen_asesmen_hub_accessible_by_roles(): void
    {
        // Unauthenticated redirected
        $this->get(route('dokumen-asesmen.index'))
            ->assertRedirect(route('masuk'));

        // Admin can access
        $this->actingAs($this->admin)
            ->get(route('dokumen-asesmen.index'))
            ->assertOk()
            ->assertSee('Pusat Manajemen Dokumen Asesmen')
            ->assertSee('FR.AK.02')
            ->assertSee('FR.AK.03');

        // Asesor can access
        $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.index'))
            ->assertOk()
            ->assertSee('Pusat Manajemen Dokumen Asesmen');

        // Asesi can access
        $this->actingAs($this->asesi1)
            ->get(route('dokumen-asesmen.index'))
            ->assertOk()
            ->assertSee('Pusat Manajemen Dokumen Asesmen');
    }

    /**
     * 2. FR.AK.02 Workflow: Auto initialization, unit matrix, K/BK calculation
     */
    public function test_ak02_workflow_auto_initialization_and_update(): void
    {
        // Asesor accesses AK.02 edit form
        $response = $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.ak02.edit', $this->pendaftaran1->id));

        $response->assertOk()
            ->assertSee('FR.AK.02')
            ->assertSee($this->unit1->kode_unit)
            ->assertSee($this->unit2->kode_unit);

        // Database record should now exist
        $ak02 = AssessmentAk02::where('pendaftaran_id', $this->pendaftaran1->id)->first();
        $this->assertNotNull($ak02);
        $this->assertEquals(2, $ak02->total_unit);

        // Update AK.02 unit records
        $matriks = [
            $this->unit1->id => [
                'unit_id' => $this->unit1->id,
                'kode_unit' => $this->unit1->kode_unit,
                'judul_unit' => $this->unit1->judul_unit,
                'methods' => ['observasi_demonstrasi' => 1, 'pertanyaan_tertulis' => 1],
                'catatan' => 'Mampu menulis kode rapi',
                'referensi_bukti' => 'Lembar observasi',
            ],
            $this->unit2->id => [
                'unit_id' => $this->unit2->id,
                'kode_unit' => $this->unit2->kode_unit,
                'judul_unit' => $this->unit2->judul_unit,
                'methods' => ['portofolio' => 1],
                'catatan' => 'Dokumentasi lengkap',
                'referensi_bukti' => 'Dokumen readme',
            ],
        ];

        $rekomendasi = [
            $this->unit1->id => ['unit_id' => $this->unit1->id, 'hasil' => 'K', 'catatan' => 'Kompeten'],
            $this->unit2->id => ['unit_id' => $this->unit2->id, 'hasil' => 'K', 'catatan' => 'Kompeten'],
        ];

        $postData = [
            'matriks_bukti' => $matriks,
            'rekomendasi_unit' => $rekomendasi,
            'keputusan_final' => 'kompeten',
            'tindak_lanjut' => null,
            'komentar_asesor' => 'Peserta memenuhi seluruh kriteria unjuk kerja.',
            'finalize' => 0,
        ];

        $updateRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak02.simpan', $this->pendaftaran1->id), $postData);

        $updateRes->assertRedirect(route('dokumen-asesmen.ak02.edit', $this->pendaftaran1->id));

        $ak02->refresh();
        $this->assertEquals(2, $ak02->total_k);
        $this->assertEquals(0, $ak02->total_bk);
        $this->assertEquals('kompeten', $ak02->keputusan_final);
    }

    /**
     * 3. FR.AK.02 BK requires tindak lanjut
     */
    public function test_ak02_bk_requires_tindak_lanjut(): void
    {
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);

        $matriks = [
            $this->unit1->id => [
                'unit_id' => $this->unit1->id,
                'kode_unit' => $this->unit1->kode_unit,
                'judul_unit' => $this->unit1->judul_unit,
                'methods' => ['observasi_demonstrasi' => 1],
                'catatan' => 'Perlu pendalaman algoritma',
                'referensi_bukti' => 'Lembar praktik',
            ],
            $this->unit2->id => [
                'unit_id' => $this->unit2->id,
                'kode_unit' => $this->unit2->kode_unit,
                'judul_unit' => $this->unit2->judul_unit,
                'methods' => ['portofolio' => 1],
                'catatan' => 'Dokumentasi ada',
                'referensi_bukti' => 'Dokumen readme',
            ],
        ];

        // Unit 1 is BK
        $rekomendasi = [
            $this->unit1->id => ['unit_id' => $this->unit1->id, 'hasil' => 'BK', 'catatan' => 'Belum kompeten'],
            $this->unit2->id => ['unit_id' => $this->unit2->id, 'hasil' => 'K', 'catatan' => 'Kompeten'],
        ];

        $postData = [
            'matriks_bukti' => $matriks,
            'rekomendasi_unit' => $rekomendasi,
            'keputusan_final' => 'belum_kompeten',
            'tindak_lanjut' => 'Wajib melakukan uji ulang demonstrasi unit 1',
            'komentar_asesor' => 'Tindak lanjut uji ulang diperlukan.',
            'finalize' => 0,
        ];

        $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak02.simpan', $this->pendaftaran1->id), $postData);

        $ak02->refresh();
        $this->assertEquals(1, $ak02->total_k);
        $this->assertEquals(1, $ak02->total_bk);
        $this->assertEquals('belum_kompeten', $ak02->keputusan_final);
        $this->assertNotEmpty($ak02->tindak_lanjut);
    }

    /**
     * 4. FR.AK.02 Dual Signatures and Finalization
     */
    public function test_ak02_dual_signatures_and_finalization(): void
    {
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);

        // Asesi signs AK.02
        $sigRes = $this->actingAs($this->asesi1)
            ->post(route('dokumen-asesmen.ak02.ttd-asesi', $this->pendaftaran1->id), [
                'signature' => 'data:image/png;base64,sample_signature_asesi',
            ]);
        $sigRes->assertRedirect();

        $ak02->refresh();
        $this->assertNotNull($ak02->tanda_tangan_asesi);
        $this->assertNotNull($ak02->tanggal_ttd_asesi);

        // Asesor finalizes
        $finalRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak02.simpan', $this->pendaftaran1->id), [
                'matriks_bukti' => $ak02->matriks_bukti,
                'rekomendasi_unit' => $ak02->rekomendasi_unit,
                'keputusan_final' => 'kompeten',
                'tindak_lanjut' => null,
                'komentar_asesor' => 'Kompeten penuh.',
                'signature' => 'data:image/png;base64,sample_signature_asesor',
                'finalize' => 1,
            ]);
        $finalRes->assertRedirect();

        $ak02->refresh();
        $this->assertEquals(AssessmentAk02::STATUS_FINAL, $ak02->status);
        $this->assertTrue($ak02->isFinalized());
        $this->assertTrue($this->pendaftaran1->isAk02Finalized());
    }

    /**
     * 5. FR.AK.03 Gatekeeper: Locked until AK.02 has decision
     */
    public function test_ak03_locked_until_ak02_has_decision(): void
    {
        // Initially, AK.02 is not created or in draft without decision
        $this->assertFalse($this->pendaftaran2->isAk03Unlocked());

        // Asesi attempts to open AK.03 form
        $res = $this->actingAs($this->asesi2)
            ->get(route('dokumen-asesmen.ak03.show', $this->pendaftaran2->id));

        $res->assertOk()
            ->assertSee('Formulir Belum Terbuka')
            ->assertSee('keputusan asesmen');

        // Attempting to post when locked should fail
        $jawaban = array_fill(1, 10, ['ya_tidak' => 'ya', 'catatan' => '']);
        $postRes = $this->actingAs($this->asesi2)
            ->post(route('dokumen-asesmen.ak03.simpan', $this->pendaftaran2->id), [
                'jawaban' => $jawaban,
                'submit' => 1,
            ]);

        $postRes->assertSessionHas('error');

        // Now, record decision on AK.02
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran2);
        $ak02->update([
            'status' => AssessmentAk02::STATUS_FINAL,
            'keputusan_final' => 'kompeten',
            'tanda_tangan_asesor' => 'signatures/asesor.png',
            'tanggal_ttd_asesor' => now(),
        ]);

        $this->pendaftaran2->refresh();
        $this->assertTrue($this->pendaftaran2->isAk03Unlocked());

        // Now asesi can open the form unlocked
        $unlockedRes = $this->actingAs($this->asesi2)
            ->get(route('dokumen-asesmen.ak03.show', $this->pendaftaran2->id));

        $unlockedRes->assertOk()
            ->assertSee('Formulir Umpan Balik Asesi')
            ->assertSee('Saya mendapatkan penjelasan yang cukup');
    }

    /**
     * 6. FR.AK.03 Submission and Lock
     */
    public function test_ak03_submission_locks_document(): void
    {
        // Setup unlocked AK.02
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);
        $ak02->update([
            'status' => AssessmentAk02::STATUS_FINAL,
            'keputusan_final' => 'kompeten',
            'tanda_tangan_asesor' => 'signatures/asesor.png',
            'tanggal_ttd_asesor' => now(),
        ]);

        // Asesi submits feedback with 10 official questions
        $jawaban = [];
        for ($i = 1; $i <= 10; $i++) {
            $jawaban[$i] = [
                'ya_tidak' => 'ya',
                'catatan' => 'Sangat jelas dan transparan',
            ];
        }

        $res = $this->actingAs($this->asesi1)
            ->post(route('dokumen-asesmen.ak03.simpan', $this->pendaftaran1->id), [
                'jawaban' => $jawaban,
                'catatan_lainnya' => 'Pelaksanaan asesmen di SMKN 1 Gunungputri sangat baik.',
                'signature' => 'data:image/png;base64,signature_asesi',
                'submit' => 1,
            ]);

        $res->assertRedirect();

        $ak03 = AssessmentAk03::where('pendaftaran_id', $this->pendaftaran1->id)->first();
        $this->assertNotNull($ak03);
        $this->assertEquals(AssessmentAk03::STATUS_SUBMITTED, $ak03->status);
        $this->assertTrue($ak03->is_locked);
        $this->assertNotNull($ak03->tanda_tangan_asesi);

        // Submitting again should be rejected because document is locked
        $resAgain = $this->actingAs($this->asesi1)
            ->post(route('dokumen-asesmen.ak03.simpan', $this->pendaftaran1->id), [
                'jawaban' => $jawaban,
                'submit' => 1,
            ]);
        $resAgain->assertSessionHas('error');
    }

    /**
     * 7. FR.AK.05 Multi-Asesi Report & Synchronization
     */
    public function test_ak05_multi_asesi_report_and_synchronization(): void
    {
        // Set AK.02 decisions for both candidates
        $ak02_1 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);
        $ak02_1->update(['status' => AssessmentAk02::STATUS_FINAL, 'keputusan_final' => 'kompeten']);

        $ak02_2 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran2);
        $ak02_2->update(['status' => AssessmentAk02::STATUS_FINAL, 'keputusan_final' => 'belum_kompeten']);

        // Asesor creates AK.05
        $createRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak05.create'), [
                'skema_id' => $this->skema->id,
                'jadwal_id' => $this->jadwal->id,
            ]);

        $ak05 = AssessmentAk05::where('skema_id', $this->skema->id)
            ->where('jadwal_id', $this->jadwal->id)
            ->first();

        $this->assertNotNull($ak05);
        $createRes->assertRedirect(route('dokumen-asesmen.ak05.edit', $ak05->id));

        // Verify synchronization loaded both participants
        $this->assertEquals(2, $ak05->total_asesi);
        $this->assertEquals(1, $ak05->total_k);
        $this->assertEquals(1, $ak05->total_bk);

        // Asesor updates notes and finalizes
        $postData = [
            'aspek_positif' => 'Demonstrasi praktik sangat disiplin.',
            'aspek_negatif' => 'Pemahaman dokumentasi perlu diperkuat.',
            'penolakan_hasil' => 'Tidak ada banding dari asesi.',
            'rekap_asesi' => $ak05->rekap_asesi,
            'saran_perbaikan' => [
                ['pihak' => 'Asesor', 'tindakan' => 'Briefing tambahan', 'prioritas' => 'Sedang', 'batas_waktu' => '2026-10-01'],
            ],
            'signature' => 'data:image/png;base64,signature_asesor',
            'finalize' => 1,
        ];

        $saveRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak05.simpan', $ak05->id), $postData);

        $saveRes->assertRedirect(route('dokumen-asesmen.ak05.edit', $ak05->id));

        $ak05->refresh();
        $this->assertEquals(AssessmentAk05::STATUS_FINAL, $ak05->status);
        $this->assertTrue($ak05->isFinalized());
    }

    /**
     * 8. FR.AK.06 Assessment Process Review
     */
    public function test_ak06_review_process_evaluation_and_finalization(): void
    {
        // Asesor creates AK.06
        $createRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak06.create'), [
                'skema_id' => $this->skema->id,
                'scope_type' => 'skema',
            ]);

        $ak06 = AssessmentAk06::where('skema_id', $this->skema->id)
            ->where('scope_type', 'skema')
            ->first();

        $this->assertNotNull($ak06);
        $createRes->assertRedirect(route('dokumen-asesmen.ak06.edit', $ak06->id));

        // Save review with matrix and dimensions
        $prosedurMatrix = $ak06->prosedur_matrix;
        $dimensi = $ak06->dimensi_kompetensi;
        $rekomendasi = [
            [
                'temuan' => 'Waktu pengujian tertulis perlu disesuaikan',
                'rekomendasi' => 'Optimasi bank soal CBT',
                'penanggung_jawab' => 'Tim Pengembang IT',
                'target_tanggal' => '2026-10-15',
                'status' => 'Berjalan',
            ]
        ];

        $postData = [
            'prosedur_matrix' => $prosedurMatrix,
            'dimensi_kompetensi' => $dimensi,
            'rekomendasi_peningkatan' => $rekomendasi,
            'komentar_reviewer' => 'Proses asesmen secara menyeluruh memenuhi regulasi BNSP.',
            'signature' => 'data:image/png;base64,signature_reviewer',
            'finalize' => 1,
        ];

        $saveRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.ak06.simpan', $ak06->id), $postData);

        $saveRes->assertRedirect(route('dokumen-asesmen.ak06.edit', $ak06->id));

        $ak06->refresh();
        $this->assertEquals(AssessmentAk06::STATUS_FINAL, $ak06->status);
        $this->assertTrue($ak06->isFinalized());
        $this->assertNotNull($ak06->tanda_tangan_reviewer);
    }

    /**
     * 9. FR.VA Independent QA Validation Activity Workflow
     */
    public function test_fr_va_quality_assurance_wizard_workflow(): void
    {
        // Asesor creates FR.VA for scheme
        $createRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.va.create'), [
                'skema_id' => $this->skema->id,
            ]);

        $va = AssessmentVa::where('skema_id', $this->skema->id)->first();
        $this->assertNotNull($va);
        $createRes->assertRedirect(route('dokumen-asesmen.va.wizard', $va->id));

        // Step 1: Save basic info
        $step1Res = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.va.save-step', $va->id), [
                'step' => 1,
                'tanggal_validasi' => now()->toDateString(),
                'tempat_validasi' => 'TUK Mandiri SMKN 1 Gunungputri',
                'periode_validasi' => ['setelah_asesmen'],
            ]);
        $step1Res->assertRedirect();

        // Step 5: Save 8-aspect matrix with VATM & VRFA via AJAX JSON
        $matriks = $va->matriks_penilaian;
        $ajaxRes = $this->actingAs($this->asesor)
            ->postJson(route('dokumen-asesmen.va.save-step', $va->id), [
                'step' => 5,
                'matriks_penilaian' => $matriks,
            ]);

        $ajaxRes->assertOk()
            ->assertJson(['success' => true]);

        // Step 7: Finalize FR.VA
        $finalizeRes = $this->actingAs($this->asesor)
            ->post(route('dokumen-asesmen.va.save-step', $va->id), [
                'step' => 7,
                'rekomendasi_peningkatan' => 'Menjadwalkan validasi berkala setiap semester.',
                'signature' => 'data:image/png;base64,signature_lead_va',
                'finalize' => 1,
            ]);
        $finalizeRes->assertRedirect();

        $va->refresh();
        $this->assertEquals(AssessmentVa::STATUS_FINAL, $va->status);
        $this->assertTrue($va->isFinalized());
        $this->assertNotNull($va->tanda_tangan_lead);
    }

    /**
     * 10. Audit Trail & Reopen Versioning
     */
    public function test_document_audit_trail_and_versioning_reopen(): void
    {
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);
        $ak02->update([
            'status' => AssessmentAk02::STATUS_FINAL,
            'keputusan_final' => 'kompeten',
            'version' => 1,
        ]);

        // Superadmin reopens finalized AK.02 document
        $reopenRes = $this->actingAs($this->superadmin)
            ->post(route('dokumen-asesmen.reopen'), [
                'document_type' => 'FR.AK.02',
                'document_id' => $ak02->id,
                'reason' => 'Perbaikan catatan referensi bukti unit 1 atas temuan tim monitoring',
            ]);

        $reopenRes->assertRedirect();

        $ak02->refresh();
        $this->assertEquals(AssessmentAk02::STATUS_IN_PROGRESS, $ak02->status);
        $this->assertEquals(2, $ak02->version);

        // Check audit log recorded reopening
        $reopenLog = DocumentAuditLog::where('document_type', 'FR.AK.02')
            ->where('document_id', $ak02->id)
            ->where('action', 'reopened')
            ->first();

        $this->assertNotNull($reopenLog);
        $this->assertEquals(2, $reopenLog->version);
        $this->assertStringContainsString('Perbaikan catatan', $reopenLog->keterangan);
    }

    /**
     * 11. Official A4 Cetak Routes Render 200 OK
     */
    public function test_cetak_a4_routes_render_successfully(): void
    {
        $ak02 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk02($this->pendaftaran1);
        $ak03 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk03($this->pendaftaran1);
        $ak05 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk05($this->skema->id, $this->jadwal->id, $this->asesor);
        $ak06 = app(\App\Services\AssessmentDocumentService::class)->getOrCreateAk06($this->skema->id, 'skema', null, null, $this->asesor);
        $va = app(\App\Services\AssessmentDocumentService::class)->createOrGetVa($this->skema->id, $this->asesor);

        $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.ak02.cetak', $this->pendaftaran1->id))
            ->assertOk()
            ->assertSee('FR.AK.02')
            ->assertSee('LSP-P1 SMKN 1 GUNUNGPUTRI');

        $this->actingAs($this->asesi1)
            ->get(route('dokumen-asesmen.ak03.cetak', $this->pendaftaran1->id))
            ->assertOk()
            ->assertSee('FR.AK.03');

        $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.ak05.cetak', $ak05->id))
            ->assertOk()
            ->assertSee('FR.AK.05');

        $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.ak06.cetak', $ak06->id))
            ->assertOk()
            ->assertSee('FR.AK.06');

        $this->actingAs($this->asesor)
            ->get(route('dokumen-asesmen.va.cetak', $va->id))
            ->assertOk()
            ->assertSee('FR.VA');
    }
}
