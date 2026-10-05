<?php

namespace Tests\Feature;

use App\Models\IaPenilaian;
use App\Models\KriteriaUnjukKerja;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
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

    private KriteriaUnjukKerja $kuk;

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

    public function test_assessor_can_save_and_resume_a_draft_and_foreign_kuk_ids_are_rejected(): void
    {
        $response = $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => '',
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
        $this->assertSame('Standar tempat kerja', $record->data_jawaban['standar_industri'][$this->kuk->id]);
        $this->assertSame('K', $record->data_jawaban['pencapaian'][$this->kuk->id]);
        $this->assertSame('Performa baik.', $record->data_jawaban['umpan_balik']);
        $this->assertSame('K', $record->rekomendasi);

        $this->actingAs($this->asesor)
            ->get(route('formulir.ia01', $this->pendaftaran->id))
            ->assertOk()
            ->assertSee('Standar tempat kerja')
            ->assertSee('Performa baik.');

        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => $record->data_jawaban['_version'],
            'simpan_draft' => '1',
            'standar_industri' => [999999 => 'Standar asing'],
        ])->assertSessionHasErrors('standar_industri');
    }

    public function test_admin_can_save_a_draft_without_impersonating_the_assessor(): void
    {
        $this->actingAs($this->admin)->post($this->saveUrl(), [
            'version' => '',
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
        $this->saveDraft();
        $record = $this->record();
        $url = route('formulir.ia01.signature.store', [
            'pendaftaranId' => $this->pendaftaran->id,
            'signer' => 'asesor',
        ]);

        $this->actingAs($this->asesor)
            ->post($url, [
                'version' => $record->data_jawaban['_version'],
                'signature' => '',
            ])
            ->assertSessionHasErrors('signature');

        $this->actingAs($this->asesor)->post($url, [
            'version' => $record->data_jawaban['_version'],
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

    public function test_finalization_requires_both_signatures_and_locks_the_assessment(): void
    {
        $this->saveDraft();
        $record = $this->record();
        $this->actingAs($this->asesor)->post($this->signatureUrl('asesor'), [
            'version' => $record->data_jawaban['_version'],
            'signature' => $this->signatureDataUrl(),
        ])->assertRedirect();

        $record = $this->record();
        $this->actingAs($this->asesi)->post($this->signatureUrl('asesi'), [
            'version' => $record->data_jawaban['_version'],
            'signature' => $this->signatureDataUrl(),
        ])->assertRedirect();

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

        $this->assertSame('completed', $this->record()->status);

        $lockedPayload = $payload;
        $lockedPayload['version'] = $this->record()->data_jawaban['_version'];
        $lockedPayload['simpan_draft'] = '1';
        $this->actingAs($this->asesor)
            ->post($this->saveUrl(), $lockedPayload)
            ->assertForbidden();
    }

    public function test_form_cannot_be_finalized_without_both_signatures(): void
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
        ])->assertSessionHasErrors('signature');

        $this->assertSame('draft', $this->record()->status);
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

        $this->assertSame('Versi terbaru', $this->record()->data_jawaban['standar_industri'][$this->kuk->id]);
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
        $this->actingAs($this->asesor)->post($this->saveUrl(), [
            'version' => '',
            'simpan_draft' => '1',
            'standar_industri' => [$this->kuk->id => 'Standar awal'],
            'pencapaian' => [$this->kuk->id => 'K'],
            'umpan_balik' => 'Umpan balik awal.',
            'rekomendasi' => 'K',
        ])->assertSessionHasNoErrors();
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
