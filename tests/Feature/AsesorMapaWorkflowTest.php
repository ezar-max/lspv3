<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\JadwalAsesmen;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Services\MapaWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AsesorMapaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $asesorA;
    private Pengguna $asesorB;
    private Pengguna $admin;
    private Pengguna $asesi;
    private SkemaSertifikasi $skema;
    private UnitKompetensi $unit1;
    private ElemenKompetensi $elemen1;
    private KriteriaUnjukKerja $kuk1;
    private JadwalAsesmen $jadwal;
    private PendaftaranAsesi $pendaftaran;
    private MapaWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);
        Storage::fake('public');

        $this->service = app(MapaWorkflowService::class);

        $this->asesorA = Pengguna::create([
            'nama_lengkap' => 'Asesor Pertama, M.Kom.',
            'email' => 'asesorA@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.001122.2023',
            'tanda_tangan' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $this->asesorB = Pengguna::create([
            'nama_lengkap' => 'Asesor Kedua, S.T.',
            'email' => 'asesorB@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
            'nomor_registrasi' => 'MET.000.003344.2023',
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin LSP',
            'email' => 'admin@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'admin',
            'aktif' => true,
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Kandidat Asesi',
            'email' => 'asesi@example.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
        ]);

        $this->skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-RPL-001',
            'nama_skema' => 'Rekayasa Perangkat Lunak',
        ]);

        $this->unit1 = UnitKompetensi::create([
            'skema_id' => $this->skema->id,
            'kode_unit' => 'J.620100.004.01',
            'judul_unit' => 'Mengimplementasikan Algoritma Pemrograman',
        ]);

        $this->elemen1 = ElemenKompetensi::create([
            'unit_id' => $this->unit1->id,
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menentukan tipe data dan struktur kontrol program',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'elemen_id' => $this->elemen1->id,
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Tipe data dan variabel diidentifikasi sesuai spesifikasi logika program.',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'kode_jadwal' => 'JDW-2026-001',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '00:00',
            'waktu_selesai' => '23:59',
            'status_jadwal' => 'berlangsung',
            'nama_tuk' => 'TUK Lab Komputer RPL',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-2026-001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesorA->id,
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'terverifikasi',
            'status_apl02' => 'submitted',
        ]);
    }

    /** 1. Asesor dapat melihat MAPA miliknya */
    public function test_asesor_can_view_their_assigned_mapa(): void
    {
        $response01 = $this->actingAs($this->asesorA)->get(route('asesor.mapa-01', $this->pendaftaran->id));
        $response01->assertOk();

        $response02 = $this->actingAs($this->asesorA)->get(route('asesor.mapa-02', $this->pendaftaran->id));
        $response02->assertOk();
    }

    /** 2. Asesor lain tidak dapat melihat/edit MAPA kandidat milik asesor lain */
    public function test_other_asesor_cannot_view_or_edit_unauthorized_mapa(): void
    {
        $this->actingAs($this->asesorB)
            ->get(route('asesor.mapa-01', $this->pendaftaran->id))
            ->assertNotFound();

        $this->actingAs($this->asesorB)
            ->get(route('asesor.mapa-02', $this->pendaftaran->id))
            ->assertNotFound();

        $this->actingAs($this->asesorB)
            ->post(route('asesor.mapa-02.simpan', $this->pendaftaran->id), [
                'aksi' => 'draft',
                'matriks_peta' => [],
            ])
            ->assertNotFound();
    }

    /** 3. MAPA auto-generation berjalan saat diakses */
    public function test_mapa_auto_generation_creates_records_on_demand(): void
    {
        $this->assertDatabaseMissing('mapa_01', ['pendaftaran_id' => $this->pendaftaran->id]);
        $this->assertDatabaseMissing('mapa_02', ['pendaftaran_id' => $this->pendaftaran->id]);

        $m01 = $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);
        $m02 = $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);

        $this->assertNotNull($m01);
        $this->assertNotNull($m02);
        $this->assertEquals('draft', $m01->status_mapa);
        $this->assertEquals('draft', $m02->status_mapa);
        $this->assertDatabaseHas('mapa_01', ['pendaftaran_id' => $this->pendaftaran->id]);
        $this->assertDatabaseHas('mapa_02', ['pendaftaran_id' => $this->pendaftaran->id]);
    }

    /** 4. Auto-generation idempotent (tidak membuat duplicate & tidak menimpa editan) */
    public function test_auto_generation_is_idempotent_and_does_not_overwrite_existing_edits(): void
    {
        $m02_first = $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);
        $m02_first->update(['catatan_asesor' => 'Catatan Khusus yang diedit oleh asesor']);

        $m02_second = $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);

        $this->assertEquals($m02_first->id, $m02_second->id);
        $this->assertEquals('Catatan Khusus yang diedit oleh asesor', $m02_second->catatan_asesor);
        $this->assertEquals(1, Mapa02::where('pendaftaran_id', $this->pendaftaran->id)->count());
    }

    /** 5. Master instrument menjadi default mapping saat inisialisasi */
    public function test_master_instrument_defines_default_mapping(): void
    {
        // Konfigurasikan SchemeMasterInstrument: hanya CBT (ia05) dan Portofolio (ia08)
        SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia05',
            'title' => 'Master Uji Tulis CBT',
            'is_active' => true,
        ]);
        SchemeMasterInstrument::create([
            'skema_id' => $this->skema->id,
            'instrument_code' => 'ia08',
            'title' => 'Master Verifikasi Portofolio',
            'is_active' => true,
        ]);

        $matrix = $this->service->generateDefaultMatrix($this->skema);

        $kukMatrix = $matrix[$this->unit1->id][$this->elemen1->id][$this->kuk1->id];
        $this->assertEquals(1, $kukMatrix['clo']); // CLO baseline
        $this->assertEquals(1, $kukMatrix['dpe']); // from ia05
        $this->assertEquals(1, $kukMatrix['vp']);  // from ia08
        $this->assertEquals(0, $kukMatrix['dpt']); // not in master
        $this->assertEquals(0, $kukMatrix['pw']);  // not in master
    }

    /** 6. Save draft berhasil */
    public function test_save_draft_persists_draft_status(): void
    {
        $matrix = [
            $this->unit1->id => [
                $this->elemen1->id => [
                    $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 0]
                ]
            ]
        ];

        $response = $this->actingAs($this->asesorA)->post(route('asesor.mapa-02.simpan', $this->pendaftaran->id), [
            'aksi' => 'draft',
            'matriks_peta' => $matrix,
            'catatan_asesor' => 'Masih dalam proses penyusunan rencana.',
        ]);

        $response->assertSessionHas('sukses');

        $mapa02 = Mapa02::where('pendaftaran_id', $this->pendaftaran->id)->first();
        $this->assertNotNull($mapa02);
        $this->assertEquals('draft', $mapa02->status_mapa);
        $this->assertEquals('Masih dalam proses penyusunan rencana.', $mapa02->catatan_asesor);
    }

    /** 7. Konfirmasi Mapa 1 memberi tahu admin dan tidak menggandakan notifikasi */
    public function test_confirm_mapa01_notifies_admin_once_until_validated(): void
    {
        $payload = [
            'aksi' => 'konfirmasi',
            'tujuan_asesmen' => 'Sertifikasi',
            'pendekatan_asesi' => ['Mandiri'],
            'tanda_tangan_asesor' => $this->asesorA->tanda_tangan,
        ];

        $this->actingAs($this->asesorA)
            ->post(route('asesor.mapa-01.simpan', $this->pendaftaran->id), $payload)
            ->assertRedirect(route('asesor.mapa-02', $this->pendaftaran->id));

        $this->assertEquals(1, $this->admin->notifications()->count());

        $notification = $this->admin->notifications()->first();
        $this->assertSame('FR.MAPA.01 Menunggu Validasi', $notification->data['title']);
        $this->assertSame($this->pendaftaran->id, $notification->data['metadata']['mapa01_pendaftaran_id']);
        $this->assertSame('menunggu_validasi', $notification->data['metadata']['status']);
        $this->assertStringContainsString('/asesor/mapa-01/' . $this->pendaftaran->id, $notification->data['url']);

        $this->actingAs($this->asesorA)
            ->post(route('asesor.mapa-01.simpan', $this->pendaftaran->id), $payload)
            ->assertRedirect(route('asesor.mapa-02', $this->pendaftaran->id));

        $this->assertEquals(1, $this->admin->notifications()->count());
    }

    /** 8. Confirm berhasil & status menjadi selesai */
    public function test_confirm_persists_confirmed_status(): void
    {
        $matrix = [
            $this->unit1->id => [
                $this->elemen1->id => [
                    $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 1]
                ]
            ]
        ];

        $response = $this->actingAs($this->asesorA)->post(route('asesor.mapa-02.simpan', $this->pendaftaran->id), [
            'aksi' => 'konfirmasi',
            'matriks_peta' => $matrix,
            'catatan_asesor' => 'Rencana asesmen disetujui.',
            'tanda_tangan_asesor' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertRedirect(route('asesor.mapa'));

        $mapa02 = Mapa02::where('pendaftaran_id', $this->pendaftaran->id)->first();
        $this->assertNotNull($mapa02);
        $this->assertEquals('selesai', $mapa02->status_mapa);
        $this->assertNotNull($mapa02->tanggal_ttd_asesor);
    }

    /** 9. Confirm tanpa instrumen valid ditolak dengan pesan error */
    public function test_confirm_without_active_instruments_fails_validation(): void
    {
        // Matrix kosong tanpa instrumen aktif
        $matrixEmpty = [
            $this->unit1->id => [
                $this->elemen1->id => [
                    $this->kuk1->id => ['clo' => 0, 'dpt' => 0, 'dpe' => 0, 'pmo' => 0, 'dpl' => 0, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                ]
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->service->saveMapa02(
            $this->pendaftaran,
            $matrixEmpty,
            'Catatan',
            null,
            true, // isConfirm = true
            $this->asesorA->id
        );
    }

    /** 10. Signature disimpan sebagai file pada storage */
    public function test_signature_saved_as_file_on_storage(): void
    {
        $rawBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $savedPath = $this->service->saveSignatureFile($rawBase64, $this->pendaftaran->id, 'mapa02_test');

        $this->assertNotNull($savedPath);
        $this->assertStringStartsWith('storage/signatures/', $savedPath);

        $relativePath = str_replace('storage/', '', $savedPath);
        Storage::disk('public')->assertExists($relativePath);
    }

    /** 10. MAPA confirmed membuka workflow berikutnya */
    public function test_is_mapa_confirmed_method_and_accessor(): void
    {
        $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);
        $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);

        $this->assertFalse($this->pendaftaran->isMapaConfirmed());
        $this->assertFalse($this->pendaftaran->mapa_confirmed);

        // Sahkan MAPA 01 dan MAPA 02
        Mapa01::where('pendaftaran_id', $this->pendaftaran->id)->update(['status_mapa' => 'selesai']);
        Mapa02::where('pendaftaran_id', $this->pendaftaran->id)->update(['status_mapa' => 'selesai']);

        $this->pendaftaran->refresh();

        $this->assertTrue($this->pendaftaran->isMapaConfirmed());
        $this->assertTrue($this->pendaftaran->mapa_confirmed);
    }

    /** 11. MAPA belum confirmed memblok / terdeteksi false */
    public function test_is_mapa_confirmed_returns_false_when_draft_or_missing(): void
    {
        $this->assertFalse($this->pendaftaran->isMapaConfirmed());

        // Hanya MAPA 01 selesai, MAPA 02 masih draft
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [],
            'status_mapa' => 'draft',
        ]);

        $this->pendaftaran->refresh();
        $this->assertFalse($this->pendaftaran->isMapaConfirmed());
    }

    /** 12. FR.AK.01 membaca mapping MAPA */
    public function test_fr_ak_01_reflects_mapa_instruments(): void
    {
        $matrix = [
            $this->unit1->id => [
                $this->elemen1->id => [
                    $this->kuk1->id => ['clo' => 1, 'dpt' => 0, 'dpe' => 1, 'vp' => 1]
                ]
            ]
        ];

        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => $matrix,
            'status_mapa' => 'selesai',
        ]);

        $this->pendaftaran->refresh();
        $instrumenAktif = $this->pendaftaran->instrumen_aktif;

        $this->assertTrue($instrumenAktif['has_ia01']);
        $this->assertTrue($instrumenAktif['has_ia05']);
        $this->assertTrue($instrumenAktif['has_ia08']);
    }

    /** 13. FR.IA hanya menampilkan instrumen aktif */
    public function test_fr_ia_only_activates_selected_instruments(): void
    {
        $matrix = [
            $this->unit1->id => [
                $this->elemen1->id => [
                    $this->kuk1->id => ['clo' => 1, 'dpt' => 0, 'dpe' => 1, 'pmo' => 0, 'dpl' => 0, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                ]
            ]
        ];

        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => $matrix,
            'status_mapa' => 'selesai',
        ]);

        $this->pendaftaran->refresh();

        $this->assertTrue($this->pendaftaran->isInstrumenAktif('FR.IA.01'));
        $this->assertTrue($this->pendaftaran->isInstrumenAktif('FR.IA.05'));
        $this->assertFalse($this->pendaftaran->isInstrumenAktif('FR.IA.08'));
        $this->assertFalse($this->pendaftaran->isInstrumenAktif('FR.IA.09'));
        $this->assertFalse($this->pendaftaran->isInstrumenAktif('FR.IA.11'));
    }

    /** 14. Tidak ada duplicate MAPA untuk satu pendaftaran */
    public function test_no_duplicate_mapa_records(): void
    {
        $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);
        $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);
        $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);
        $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);

        $this->assertEquals(1, Mapa01::where('pendaftaran_id', $this->pendaftaran->id)->count());
        $this->assertEquals(1, Mapa02::where('pendaftaran_id', $this->pendaftaran->id)->count());
    }

    /** 15. Transaction rollback jika terjadi kegagalan sistem */
    public function test_transaction_rollback_on_failure(): void
    {
        try {
            $this->service->saveMapa02(
                $this->pendaftaran,
                [], // Matriks kosong yang akan gagal validasi
                'Catatan rollback test',
                null,
                true, // confirm = true akan throw ValidationException
                $this->asesorA->id
            );
        } catch (\Throwable $e) {
            // Expected validation exception
        }

        $this->assertDatabaseMissing('mapa_02', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'catatan_asesor' => 'Catatan rollback test',
            'status_mapa' => 'selesai',
        ]);
    }

    /** 16. APL.02 approval allowed even when MAPA is not confirmed */
    public function test_apl02_approval_allowed_even_when_mapa_not_confirmed(): void
    {
        // Pastikan MAPA belum selesai
        $this->assertFalse($this->pendaftaran->isMapaConfirmed());

        $response = $this->actingAs($this->asesorA)->post(route('asesor.input-penilaian.simpan', $this->pendaftaran->id), [
            'nilai' => [$this->unit1->id => 'K'],
            'verifikasi_kuk' => [$this->kuk1->id => 'K'],
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'tanda_tangan_asesor' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertSessionHas('sukses');
        $response->assertRedirect(route('asesor.dashboard'));

        $this->pendaftaran->refresh();
        $this->assertEquals('approved', $this->pendaftaran->status_apl02);
    }

    /** 17. APL.02 approval allowed when MAPA is confirmed */
    public function test_apl02_approval_allowed_when_mapa_confirmed(): void
    {
        // Sahkan MAPA 01 dan MAPA 02
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 1]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->pendaftaran->refresh();
        $this->assertTrue($this->pendaftaran->isMapaConfirmed());

        $response = $this->actingAs($this->asesorA)->post(route('asesor.input-penilaian.simpan', $this->pendaftaran->id), [
            'nilai' => [$this->unit1->id => 'K'],
            'verifikasi_kuk' => [$this->kuk1->id => 'K'],
            'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            'tanda_tangan_asesor' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertSessionHas('sukses');
        $response->assertRedirect(route('asesor.dashboard'));

        $this->pendaftaran->refresh();
        $this->assertEquals('approved', $this->pendaftaran->status_apl02);
    }

    /** 18. Asesi exam access blocked when MAPA is not confirmed */
    public function test_asesi_exam_access_blocked_when_mapa_not_confirmed(): void
    {
        $this->assertFalse($this->pendaftaran->isMapaConfirmed());

        // Setujui AK01 oleh kedua pihak agar melewati gate AK-01 dan menguji khusus gate MAPA
        $this->pendaftaran->update([
            'tanda_tangan_asesi_ak01' => 'dummy_ttd',
            'tanda_tangan_asesor_ak01' => 'dummy_asesor_ttd',
            'status_ak01' => 'selesai'
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.ujian', ['pendaftaran_id' => $this->pendaftaran->id]));
        $response->assertOk();
        $response->assertSee('Rencana asesmen (FR.MAPA.01 &amp; FR.MAPA.02) belum disahkan asesor', false);

        // Autosave juga ditolak 403
        $autoSaveResp = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $autoSaveResp->assertStatus(403);
    }

    /** 19. Asesi exam access allowed when MAPA is confirmed */
    public function test_asesi_exam_access_allowed_when_mapa_confirmed(): void
    {
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 1]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->pendaftaran->update([
            'tanda_tangan_asesi_ak01' => 'dummy_ttd',
            'tanda_tangan_asesor_ak01' => 'dummy_asesor_ttd',
            'status_ak01' => 'selesai'
        ]);

        $response = $this->actingAs($this->asesi)->get(route('asesi.ujian', ['pendaftaran_id' => $this->pendaftaran->id]));
        $response->assertOk();
        $response->assertDontSee('Rencana asesmen (FR.MAPA.01 &amp; FR.MAPA.02) belum disahkan asesor', false);

        // Autosave berhasil
        $autoSaveResp = $this->actingAs($this->asesi)->postJson(route('asesi.ujian.autosave'), [
            'pendaftaran_id' => $this->pendaftaran->id,
            'tipe' => 'cbt',
            'no' => 1,
            'jawaban' => 'A'
        ]);
        $autoSaveResp->assertOk();
        $autoSaveResp->assertJson(['status' => 'success']);
    }

    /** 20. Live assessment rejects inactive instrument */
    public function test_live_assessment_rejects_inactive_instrument(): void
    {
        // MAPA hanya mengaktifkan Observasi (CLO), TIDAK mengaktifkan Lisan (DPL)
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpt' => 0, 'pmo' => 0, 'dpe' => 0, 'dpl' => 0, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->actingAs($this->asesorA)->post(route('asesor.penilaian-live.simpan', $this->pendaftaran->id), [
            'keputusan' => 'kompeten',
            'penilaian_kuk' => [$this->kuk1->id => 'K'],
            'respon_lisan' => [1 => 'Jawaban lisan ilegal'],
            'penilaian_lisan' => [1 => 'K'],
        ]);

        // Rekor FR.IA.01 tersimpan karena aktif
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.01',
        ]);

        // Rekor FR.IA.07 TIDAK tersimpan karena tidak aktif di MAPA.02
        $this->assertDatabaseMissing('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.07',
        ]);
    }

    /** 21. Live assessment accepts active instrument */
    public function test_live_assessment_accepts_active_instrument(): void
    {
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'matriks_peta' => [
                $this->unit1->id => [
                    $this->elemen1->id => [
                        $this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'pmo' => 0, 'dpe' => 0, 'dpl' => 1, 'vp' => 0, 'pw' => 0, 'crp' => 0]
                    ]
                ]
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->actingAs($this->asesorA)->post(route('asesor.penilaian-live.simpan', $this->pendaftaran->id), [
            'keputusan' => 'kompeten',
            'penilaian_kuk' => [$this->kuk1->id => 'K'],
            'respon_lisan' => [1 => 'Jawaban lisan valid'],
            'penilaian_lisan' => [1 => 'K'],
        ]);

        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.01',
        ]);
        $this->assertDatabaseHas('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.07',
        ]);
    }

    /** 22. MAPA state transition and reconfirmation */
    public function test_mapa_state_transition_and_reconfirmation(): void
    {
        // 1. Inisialisasi awal -> status draft
        $m02 = $this->service->getOrCreateMapa02($this->pendaftaran, $this->asesorA->id);
        $this->assertEquals('draft', $m02->status_mapa);

        // 2. Konfirmasi & Sahkan -> status selesai
        $m02_confirmed = $this->service->saveMapa02(
            $this->pendaftaran,
            [$this->unit1->id => [$this->elemen1->id => [$this->kuk1->id => ['clo' => 1, 'dpt' => 1, 'dpe' => 1]]]],
            'Catatan confirmed',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true, // isConfirm = true
            $this->asesorA->id
        );
        $this->assertEquals('selesai', $m02_confirmed->status_mapa);

        // 3. Asesor mengedit & menyimpan sebagai DRAFT -> status kembali draft
        $m02_edited = $this->service->saveMapa02(
            $this->pendaftaran,
            [$this->unit1->id => [$this->elemen1->id => [$this->kuk1->id => ['clo' => 1, 'dpt' => 0, 'dpe' => 1]]]],
            'Catatan diedit menjadi draft',
            null,
            false, // isConfirm = false
            $this->asesorA->id
        );
        $this->assertEquals('draft', $m02_edited->status_mapa);
        $this->assertFalse($this->pendaftaran->isMapaConfirmed());

        // 4. Asesor melakukan konfirmasi ulang -> status kembali selesai
        $m02_reconfirmed = $this->service->saveMapa02(
            $this->pendaftaran,
            [$this->unit1->id => [$this->elemen1->id => [$this->kuk1->id => ['clo' => 1, 'dpt' => 0, 'dpe' => 1]]]],
            'Catatan disahkan kembali',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true, // isConfirm = true
            $this->asesorA->id
        );
        $this->assertEquals('selesai', $m02_reconfirmed->status_mapa);
    }

    /** 23. Database unique constraint on mapa tables */
    public function test_database_unique_constraint_on_mapa_tables(): void
    {
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'draft',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Membuat duplikat record kedua dengan pendaftaran_id yang sama harus ditolak oleh database constraint
        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->skema->id,
            'asesor_id' => $this->asesorA->id,
            'status_mapa' => 'selesai',
        ]);
    }

    /** 24. Existing MAPA data is not overwritten */
    public function test_existing_mapa_data_is_not_overwritten(): void
    {
        $m01 = $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);
        $m01->update(['tujuan_asesmen' => 'Tujuan Khusus Sertifikasi Portofolio']);

        $m01_fetched = $this->service->getOrCreateMapa01($this->pendaftaran, $this->asesorA->id);

        $this->assertEquals('Tujuan Khusus Sertifikasi Portofolio', $m01_fetched->tujuan_asesmen);
    }

    /** 25. Other asesor cannot pass MAPA gate or access assigned candidate */
    public function test_other_asesor_cannot_pass_mapa_gate(): void
    {
        $this->actingAs($this->asesorB)
            ->post(route('asesor.input-penilaian.simpan', $this->pendaftaran->id), [
                'nilai' => [$this->unit1->id => 'K'],
                'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
            ])
            ->assertNotFound();

        $this->actingAs($this->asesorB)
            ->post(route('asesor.penilaian-live.simpan', $this->pendaftaran->id), [
                'keputusan' => 'kompeten',
            ])
            ->assertNotFound();
    }
}
