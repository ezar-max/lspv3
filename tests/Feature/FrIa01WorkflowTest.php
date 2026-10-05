<?php

namespace Tests\Feature;

use App\Models\IaPenilaian;
use App\Models\JadwalAsesmen;
use App\Models\AssessmentAk02;
use App\Models\AssessmentAk03;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FrIa01WorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private Pengguna $asesor;

    private Pengguna $asesi;

    private Pengguna $asesorLain;

    private PendaftaranAsesi $pendaftaran;

    private JadwalAsesmen $jadwal;

    private KriteriaUnjukKerja $kuk;

    private SchemeMasterInstrument $master;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = $this->createUser('admin', 'Admin LSP');
        $this->asesor = $this->createUser('asesor', 'Asesor Penguji');
        $this->asesi = $this->createUser('asesi', 'Asesi Uji');
        $this->asesorLain = $this->createUser('asesor', 'Asesor Lain');

        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-IA01',
            'nama_skema' => 'Skema Pengujian IA01',
            'kategori' => 'KKNI',
            'biaya' => 0,
            'status_aktif' => true,
        ]);
        $unit = UnitKompetensi::create([
            'skema_id' => $skema->id,
            'kode_unit' => 'UNIT.001',
            'judul_unit' => 'Melaksanakan pekerjaan',
            'standar_kompetensi' => 'SKKNI',
        ]);
        $elemen = $unit->elemenKompetensi()->create([
            'nomor_elemen' => 1,
            'nama_elemen' => 'Menyiapkan pekerjaan',
        ]);
        $this->kuk = $elemen->kriteriaUnjukKerja()->create([
            'nomor_kuk' => '1.1',
            'pernyataan_kuk' => 'Peralatan disiapkan sesuai prosedur.',
        ]);

        $this->pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-IA01-001',
            'asesi_id' => $this->asesi->id,
            'asesor_id' => $this->asesor->id,
            'skema_id' => $skema->id,
            'status_pendaftaran' => 'disetujui',
            'tanggal_daftar' => now(),
        ]);
        $this->jadwal = JadwalAsesmen::create([
            'kode_jadwal' => 'JAD-IA01-001',
            'skema_id' => $skema->id,
            'asesor_id' => $this->asesor->id,
            'nama_tuk' => 'TUK Uji',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => '00:00:00',
            'waktu_selesai' => '23:59:00',
            'kuota' => 10,
            'status_jadwal' => 'berlangsung',
        ]);
        $this->pendaftaran->update(['jadwal_id' => $this->jadwal->id]);

        Mapa01::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $this->asesor->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $this->asesor->id,
            'matriks_peta' => [
                $unit->id => [
                    $elemen->id => [
                        $this->kuk->id => ['clo' => 1],
                    ],
                ],
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->master = SchemeMasterInstrument::create([
            'skema_id' => $skema->id,
            'instrument_code' => 'ia_01',
            'title' => 'Ceklis Observasi Aktivitas',
            'is_active' => true,
            'created_by' => $this->asesor->id,
            'additional_metadata' => [
                'is_saved' => true,
                'lifecycle_status' => 'published',
                'published_version' => 1,
                'published_standards' => [$this->kuk->id => 'Standar acuan skema'],
                'standar_industri' => [$this->kuk->id => 'Standar acuan skema'],
            ],
        ]);
    }

    public function test_authorized_assessor_and_admin_can_open_form_but_other_assessor_cannot(): void
    {
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Ceklis Observasi Aktivitas')
            ->assertSee('UNIT.001');

        $this->actingAs($this->admin)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk();

        $this->actingAs($this->asesorLain)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertNotFound();
    }

    public function test_fr_ia01_requires_confirmed_mapa_and_active_clo_instrument(): void
    {
        $mapa02 = $this->pendaftaran->mapa02()->firstOrFail();
        $this->pendaftaran->mapa01()->firstOrFail()->update(['status_mapa' => 'draft']);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertForbidden();
        $this->pendaftaran->mapa01()->firstOrFail()->update(['status_mapa' => 'selesai']);

        $mapa02->update(['status_mapa' => 'draft']);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertForbidden();

        $mapa02->update([
            'status_mapa' => 'selesai',
            'matriks_peta' => [$this->kuk->elemenKompetensi->unit_id => [
                $this->kuk->elemen_id => [$this->kuk->id => ['clo' => 0]],
            ]],
        ]);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertForbidden();
    }

    public function test_master_draft_is_editable_and_only_a_complete_published_version_is_available_to_assessee(): void
    {
        $this->jadwal->update([
            'status_jadwal' => 'terjadwal',
            'tanggal_uji' => now()->addDay()->toDateString(),
        ]);
        $this->master->update([
            'additional_metadata' => [
                'is_saved' => true,
                'lifecycle_status' => 'draft',
                'draft_standards' => [$this->kuk->id => 'Draft standar'],
                '_draft_version' => 'master-draft-1',
            ],
        ]);

        $this->actingAs($this->asesi)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertForbidden();

        $this->withSession(['active_selected_skema_id' => $this->pendaftaran->skema_id])
            ->actingAs($this->asesor)
            ->get(route('formulir.ia01', 0))
            ->assertOk()
            ->assertSee('Master FR.IA.01 — atur standar acuan untuk KUK.')
            ->assertSee('value="draft">Simpan Draft Master', false)
            ->assertSee('value="publish">Terbitkan Master', false)
            ->assertSee('name="standar_industri['.$this->kuk->id.']" rows="2" class="input-inline-bnsp" placeholder="Standar Industri atau Tempat Kerja..." >Draft standar', false)
            ->assertSee('name="pencapaian['.$this->kuk->id.']" value="K"  style="width: 16px; height: 16px; accent-color: #059669; cursor: pointer;" disabled', false)
            ->assertDontSee('ia01-signature-canvas');

        $this->actingAs($this->asesorLain)
            ->post(route('formulir.ia01.master.save', ['skemaId' => $this->pendaftaran->skema_id]), [
                'master_version' => 'master-draft-1',
                'action' => 'draft',
                'standar_industri' => [$this->kuk->id => 'Tidak berwenang'],
            ])
            ->assertForbidden();
        $this->withSession(['active_selected_skema_id' => $this->pendaftaran->skema_id])
            ->actingAs($this->asesor)
            ->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => 0]), [
                'standar_industri' => [$this->kuk->id => 'Tidak boleh masuk generic save'],
            ])
            ->assertSessionHas('error');

        $this->actingAs($this->asesor)
            ->post(route('formulir.ia01.master.save', ['skemaId' => $this->pendaftaran->skema_id]), [
                'master_version' => 'master-draft-1',
                'action' => 'draft',
                'standar_industri' => [$this->kuk->id => 'Draft hasil edit'],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('draft', data_get($this->master->fresh()->additional_metadata, 'lifecycle_status'));
        $this->assertSame('Draft hasil edit', data_get($this->master->fresh()->additional_metadata, 'draft_standards.'.$this->kuk->id));

        $draftVersion = data_get($this->master->fresh()->additional_metadata, '_draft_version');
        $this->actingAs($this->asesor)
            ->post(route('formulir.ia01.master.save', ['skemaId' => $this->pendaftaran->skema_id]), [
                'master_version' => $draftVersion,
                'action' => 'publish',
                'standar_industri' => [],
            ])
            ->assertSessionHasErrors('standar_industri');

        $draftVersion = data_get($this->master->fresh()->additional_metadata, '_draft_version');
        $this->actingAs($this->asesor)
            ->post(route('formulir.ia01.master.save', ['skemaId' => $this->pendaftaran->skema_id]), [
                'master_version' => $draftVersion,
                'action' => 'publish',
                'standar_industri' => [$this->kuk->id => 'Standar terbit'],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $published = $this->master->fresh()->additional_metadata;
        $this->assertSame(1, $published['published_version']);
        $this->assertSame('published', $published['lifecycle_status']);
        $this->assertSame('Standar terbit', $published['published_standards'][$this->kuk->id]);
        $this->assertArrayNotHasKey('signature_asesor', $published);

        $this->actingAs($this->asesi)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Mode Pratinjau Asesi (Hanya Baca)')
            ->assertSee('Standar terbit');
    }

    public function test_assessor_cannot_sign_or_save_before_assessment_begins(): void
    {
        $this->jadwal->update([
            'status_jadwal' => 'terjadwal',
            'tanggal_uji' => now()->addDay()->toDateString(),
        ]);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Formulir belum dibuka.')
            ->assertSee('akan aktif otomatis saat jadwal asesmen berstatus berlangsung.')
            ->assertSee('Kelola Template FR.IA.01')
            ->assertDontSee('ia01-open-signature')
            ->assertDontSee('id="ia01-signature-canvas"', false)
            ->assertSee('name="standar_industri['.$this->kuk->id.']" rows="2" class="input-inline-bnsp" placeholder="Standar Industri atau Tempat Kerja..." readonly', false);

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => '',
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Tidak boleh disimpan'],
        ])->assertForbidden();

        $this->actingAs($this->asesor)->post($this->signatureUrl('asesor'), [
            'version' => '',
            'signature' => $this->signatureDataUrl(),
        ])->assertForbidden();
        $this->assertDatabaseMissing('ia_penilaian', [
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.01',
        ]);

        $this->jadwal->update([
            'status_jadwal' => 'berlangsung',
            'tanggal_uji' => now()->toDateString(),
            'waktu_mulai' => now()->subMinute()->format('H:i'),
            'waktu_selesai' => now()->addHour()->format('H:i'),
        ]);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertDontSee('Formulir belum dibuka.')
            ->assertSee('ia01-open-signature')
            ->assertSee('id="ia01-signature-canvas"', false)
            ->assertSee('name="standar_industri['.$this->kuk->id.']" rows="2" class="input-inline-bnsp" placeholder="Standar Industri atau Tempat Kerja..." readonly', false)
            ->assertSee('name="pencapaian['.$this->kuk->id.']" value="K"  style="width: 16px; height: 16px; accent-color: #059669; cursor: pointer;" required', false);

        $this->signAsesor();
        $record = $this->record();
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'pencapaian' => [$this->kuk->id => 'K'],
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_execution_signatures_and_master_snapshots_are_isolated_per_assessee(): void
    {
        $secondAsesi = $this->createUser('asesi', 'Asesi Kedua');
        $secondRegistration = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'REG-IA01-002',
            'asesi_id' => $secondAsesi->id,
            'asesor_id' => $this->asesor->id,
            'skema_id' => $this->pendaftaran->skema_id,
            'jadwal_id' => $this->jadwal->id,
            'status_pendaftaran' => 'disetujui',
            'tanggal_daftar' => now(),
        ]);
        Mapa01::create([
            'pendaftaran_id' => $secondRegistration->id,
            'skema_id' => $secondRegistration->skema_id,
            'asesor_id' => $this->asesor->id,
            'status_mapa' => 'selesai',
        ]);
        Mapa02::create([
            'pendaftaran_id' => $secondRegistration->id,
            'skema_id' => $secondRegistration->skema_id,
            'asesor_id' => $this->asesor->id,
            'matriks_peta' => [
                $this->kuk->elemenKompetensi->unit_id => [
                    $this->kuk->elemen_id => [$this->kuk->id => ['clo' => 1]],
                ],
            ],
            'status_mapa' => 'selesai',
        ]);

        $this->signAsesor();
        $this->actingAs($this->asesor)->post(route('formulir.ia01.signature.store', [
            'pendaftaranId' => $secondRegistration->id,
            'signer' => 'asesor',
        ]), [
            'version' => '',
            'signature' => $this->signatureDataUrl(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $firstRecord = $this->record();
        $secondRecord = IaPenilaian::where('pendaftaran_id', $secondRegistration->id)
            ->where('kode_formulir', 'FR.IA.01')
            ->firstOrFail();
        $firstSignature = $firstRecord->data_jawaban['signature_asesor'];
        $secondSignature = $secondRecord->data_jawaban['signature_asesor'];
        $this->assertNotSame($firstSignature['path'], $secondSignature['path']);
        $this->assertStringStartsWith('signatures/fr-ia-01/'.$this->pendaftaran->id.'/', $firstSignature['path']);
        $this->assertStringStartsWith('signatures/fr-ia-01/'.$secondRegistration->id.'/', $secondSignature['path']);
        $this->assertSame(1, $firstRecord->data_jawaban['master_snapshot']['version']);
        $this->assertSame($firstRecord->data_jawaban['master_snapshot'], $secondRecord->data_jawaban['master_snapshot']);
        $this->assertArrayNotHasKey('signature_asesor', $this->master->fresh()->additional_metadata);
        Storage::disk('local')->assertExists($firstSignature['path']);
        Storage::disk('local')->assertExists($secondSignature['path']);

        $this->actingAs($this->asesor)->post(route('formulir.ia01.master.save', [
            'skemaId' => $this->pendaftaran->skema_id,
        ]), [
            'master_version' => data_get($this->master->fresh()->additional_metadata, '_draft_version', ''),
            'action' => 'draft',
            'standar_industri' => [$this->kuk->id => 'Standar draft'],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, data_get($this->master->fresh()->additional_metadata, 'published_version'));
        $this->assertSame('Standar acuan skema', $this->record()->data_jawaban['master_snapshot']['standar_industri'][$this->kuk->id]);

        $this->actingAs($this->asesor)->post(route('formulir.ia01.master.save', [
            'skemaId' => $this->pendaftaran->skema_id,
        ]), [
            'master_version' => data_get($this->master->fresh()->additional_metadata, '_draft_version'),
            'action' => 'publish',
            'standar_industri' => [$this->kuk->id => 'Standar versi dua'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(2, data_get($this->master->fresh()->additional_metadata, 'published_version'));
        $this->assertSame('Standar acuan skema', $this->record()->data_jawaban['master_snapshot']['standar_industri'][$this->kuk->id]);
        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Standar acuan skema')
            ->assertDontSee('Standar versi dua');
    }

    public function test_assessor_can_save_and_resume_a_draft_and_foreign_kuk_ids_are_rejected(): void
    {
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => '',
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Sebelum tanda tangan'],
        ])->assertStatus(422);

        $this->signAsesor();
        $record = $this->record();
        $response = $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Standar tempat kerja'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'penilaian_lanjut' => [$this->kuk->id => 'Catatan lanjutan'],
            'umpan_balik' => 'Performa baik.',
            'rekomendasi' => 'K',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();
        $record = $this->record();
        $this->assertSame('draft', $record->status);
        $this->assertSame('Standar acuan skema', $record->data_jawaban['standar_industri'][$this->kuk->id]);
        $this->assertSame(1, $record->data_jawaban['master_snapshot']['version']);
        $this->assertSame('K', $record->data_jawaban['pencapaian'][$this->kuk->id]);
        $this->assertSame('Performa baik.', $record->data_jawaban['umpan_balik']);
        $this->assertSame('K', $record->rekomendasi);

        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Standar acuan skema')
            ->assertSee('Performa baik.');

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [999999 => 'Standar asing'],
        ])->assertSessionHasErrors('standar_industri');
    }

    public function test_admin_can_save_a_draft_without_impersonating_the_assessor(): void
    {
        $this->signAsesor();
        $record = $this->record();
        $this->actingAs($this->admin)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Standar administrasi'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Catatan draft admin.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $record = $this->record();
        $this->assertSame($this->admin->id, $record->user_id);
        $this->assertSame('admin', $record->role);
    }

    public function test_signature_must_be_nonempty_private_and_scoped_to_the_registration(): void
    {
        $recordVersion = '';
        $url = route('formulir.ia01.signature.store', [
            'pendaftaranId' => $this->pendaftaran->id,
            'signer' => 'asesor',
        ]);

        $this->actingAs($this->asesor)
            ->post($url, [
                'version' => $recordVersion,
                'signature' => '',
            ])
            ->assertSessionHasErrors('signature');

        $this->actingAs($this->asesor)->post($url, [
            'version' => $recordVersion,
            'signature' => $this->signatureDataUrl(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $record = $this->record();
        $signature = $record->data_jawaban['signature_asesor'];
        $this->assertSame($this->asesor->id, $signature['signer_user_id']);
        $this->assertSame('asesor', $signature['signer_role']);
        $this->assertSame($this->pendaftaran->id, $signature['pendaftaran_id']);
        $this->assertStringStartsWith('signatures/fr-ia-01/'.$this->pendaftaran->id.'/', $signature['path']);
        Storage::disk('local')->assertExists($signature['path']);

        $this->actingAs($this->asesorLain)
            ->get(route('formulir.ia01.signature', [
                'pendaftaranId' => $this->pendaftaran->id,
                'signer' => 'asesor',
            ]))
            ->assertNotFound();
    }

    public function test_unassigned_assessor_cannot_sign_or_finalize_another_registration(): void
    {
        $this->actingAs($this->asesorLain)->post($this->signatureUrl('asesor'), [
            'version' => '',
            'signature' => $this->signatureDataUrl(),
        ])->assertNotFound();

        $this->signAsesor();
        $record = $this->record();
        $this->actingAs($this->asesorLain)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'finalisasi' => '1',
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Tidak berwenang.',
            'rekomendasi' => 'K',
        ])->assertNotFound();

        $this->assertSame('draft', $this->record()->status);
    }

    public function test_assessor_can_finalize_and_asesi_signs_after_assessment_finishes(): void
    {
        $this->saveDraft();
        $record = $this->record();
        $payload = [
            'version' => $record->data_jawaban['_version'],
            'finalisasi' => '1',
            'standar_industri' => [$this->kuk->id => 'SOP tempat kerja'],
            'pencapaian' => [$this->kuk->id => 'BK'],
            'penilaian_lanjut' => [$this->kuk->id => 'Perlu observasi ulang'],
            'umpan_balik' => 'Perlu peningkatan pada kriteria ini.',
            'rekomendasi' => 'BK',
        ];
        $this->actingAs($this->asesor)
            ->post($this->saveUrl(), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('submitted', $this->record()->status);
        $this->actingAs($this->asesi)
            ->post($this->signatureUrl('asesi'), [
                'version' => $this->record()->data_jawaban['_version'],
                'signature' => $this->signatureDataUrl(),
            ])->assertForbidden();

        $this->jadwal->update(['status_jadwal' => 'selesai']);
        $this->actingAs($this->asesi)
            ->post($this->signatureUrl('asesi'), [
                'version' => $this->record()->data_jawaban['_version'],
                'signature' => $this->signatureDataUrl(),
            ])->assertRedirect();

        $this->assertSame('completed', $this->record()->status);

        $lockedPayload = $payload;
        $lockedPayload['version'] = $this->record()->data_jawaban['_version'];
        $lockedPayload['simpan_draft'] = '1';
        $this->actingAs($this->asesor)
            ->post($this->saveUrl(), $lockedPayload)
            ->assertForbidden();
    }

    public function test_form_can_be_finalized_with_assessor_signature_before_asesi_signs(): void
    {
        $this->signAsesor();
        $record = $this->record();

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'finalisasi' => '1',
            'standar_industri' => [$this->kuk->id => 'SOP tempat kerja'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Sesuai standar.',
            'rekomendasi' => 'K',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('submitted', $this->record()->status);
    }

    public function test_form_cannot_be_finalized_while_any_kuk_is_unassessed(): void
    {
        $this->saveDraft();
        $record = $this->record();

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'finalisasi' => '1',
            'standar_industri' => [$this->kuk->id => 'SOP tempat kerja'],
            'umpan_balik' => 'Umpan balik tersedia.',
            'rekomendasi' => 'K',
        ])->assertSessionHasErrors('pencapaian');

        $this->assertSame('draft', $this->record()->status);
    }

    public function test_asesi_can_review_used_form_results_only_after_assessment_finishes(): void
    {
        $this->saveDraft();
        $record = $this->record();
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'finalisasi' => '1',
            'standar_industri' => [$this->kuk->id => 'SOP tempat kerja'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Sesuai standar.',
            'rekomendasi' => 'K',
        ])->assertRedirect();

        $url = route('asesi.hasil-formulir', $this->pendaftaran->id);
        $this->actingAs($this->asesi)->get($url)->assertForbidden();

        $this->jadwal->update(['status_jadwal' => 'selesai']);
        IaPenilaian::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'kode_formulir' => 'FR.IA.02',
            'user_id' => $this->asesor->id,
            'role' => 'asesor',
            'data_jawaban' => ['catatan_praktik' => 'Produk praktik memenuhi instruksi.'],
            'status' => 'completed',
        ]);
        AssessmentAk02::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'skema_id' => $this->pendaftaran->skema_id,
            'asesor_id' => $this->asesor->id,
            'keputusan_final' => 'kompeten',
            'komentar_asesor' => 'Bukti asesmen telah diverifikasi.',
            'status' => 'final',
        ]);
        AssessmentAk03::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesi_id' => $this->asesi->id,
            'asesor_id' => $this->asesor->id,
            'jawaban_kuesioner' => [1 => 'Setuju'],
            'status' => 'submitted',
        ]);
        $this->actingAs($this->asesi)
            ->get($url)
            ->assertOk()
            ->assertSee('FR.IA.01')
            ->assertSee('FR.IA.02')
            ->assertSee('FR.AK.02')
            ->assertSee('FR.AK.03')
            ->assertSee('Standar acuan skema')
            ->assertSee('Produk praktik memenuhi instruksi.')
            ->assertSee('Tanda tangan asesi diperlukan')
            ->assertDontSee('signatures/fr-ia-01');

        $this->actingAs($this->asesor)->get($url)->assertRedirect();
        $asesiLain = $this->createUser('asesi', 'Asesi Lain');
        $this->actingAs($asesiLain)->get($url)->assertNotFound();
    }

    public function test_outdated_tab_cannot_overwrite_newer_draft(): void
    {
        $this->saveDraft();
        $record = $this->record();
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Versi terbaru'],
        ])->assertRedirect();

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Versi lama'],
        ])->assertStatus(409);

        $this->assertSame('Standar acuan skema', $this->record()->data_jawaban['standar_industri'][$this->kuk->id]);
    }

    private function createUser(string $role, string $name): Pengguna
    {
        static $index = 0;
        $index++;

        return Pengguna::create([
            'nama_lengkap' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).$index.'@lsp.test',
            'kata_sandi' => bcrypt('password'),
            'peran' => $role,
        ]);
    }

    private function saveUrl(): string
    {
        return route('formulir.ia.simpan', [
            'kodeForm' => 'FR.IA.01',
            'pendaftaranId' => $this->pendaftaran->id,
        ]);
    }

    private function signatureUrl(string $signer): string
    {
        return route('formulir.ia01.signature.store', [
            'pendaftaranId' => $this->pendaftaran->id,
            'signer' => $signer,
        ]);
    }

    private function record(): IaPenilaian
    {
        return IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.01')
            ->firstOrFail();
    }

    private function saveDraft(): void
    {
        $this->signAsesor();
        $record = $this->record();
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Standar awal'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Umpan balik awal.',
            'rekomendasi' => 'K',
        ])->assertSessionHasNoErrors();
    }

    private function signAsesor(): void
    {
        $record = IaPenilaian::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.01')
            ->first();
        $this->actingAs($this->asesor)->post($this->signatureUrl('asesor'), [
            'version' => $record ? $record->data_jawaban['_version'] : '',
            'signature' => $this->signatureDataUrl(),
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    private function signatureDataUrl(): string
    {
        $image = imagecreatetruecolor(120, 80);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $transparent);
        $black = imagecolorallocate($image, 20, 30, 40);
        imagesetthickness($image, 3);
        imageline($image, 10, 60, 60, 20, $black);
        imageline($image, 60, 20, 110, 55, $black);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
