<?php

namespace App\Services;

use App\Models\IaPenilaian;
use App\Models\JadwalAsesmen;
use App\Models\LogAktivitas;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SchemeMasterInstrument;
use App\Models\SkemaSertifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FrIa01Service
{
    private const FORM_CODE = 'FR.IA.01';

    private const LOCKED_STATUSES = ['completed', 'evaluated', 'locked', 'selesai'];

    public function loadAuthorizedRegistration(int $id, Pengguna $user): PendaftaranAsesi
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'skema.masterInstruments',
            'asesor',
            'jadwal.asesor',
            'mapa01',
            'mapa02',
            'iaPenilaian',
        ])->findOrFail($id);

        $this->authorizeParticipant($pendaftaran, $user);
        $this->assertWorkflowReady($pendaftaran);

        return $pendaftaran;
    }

    public function authorizeParticipant(PendaftaranAsesi $pendaftaran, Pengguna $user): void
    {
        if (! in_array($user->peran, ['asesor', 'admin', 'superadmin', 'asesi'], true)) {
            abort(403, 'Role Anda tidak dapat mengakses FR.IA.01.');
        }

        if ($user->peran === 'asesor') {
            $assigned = (int) $pendaftaran->asesor_id === (int) $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists();
            abort_unless($assigned, 404, 'Pendaftaran tidak ditemukan.');
        } elseif ($user->peran === 'asesi') {
            abort_unless((int) $pendaftaran->asesi_id === (int) $user->id, 403, 'Akses ditolak untuk pendaftaran ini.');
        }
    }

    public function assertWorkflowReady(PendaftaranAsesi $pendaftaran): void
    {
        abort_unless($pendaftaran->skema_id && $pendaftaran->asesi_id, 422, 'Data pendaftaran FR.IA.01 belum lengkap.');
        abort_unless(
            ! in_array($pendaftaran->status_pendaftaran, ['ditolak', 'rejected', 'dibatalkan'], true),
            403,
            'Pendaftaran ini tidak berada pada status asesmen yang dapat menggunakan FR.IA.01.'
        );

        $mapa01 = $pendaftaran->getEffectiveMapa01();
        abort_unless($mapa01 && $mapa01->status_mapa === 'selesai', 403, 'FR.IA.01 belum dapat digunakan karena MAPA.01 belum selesai.');

        $mapa02 = $pendaftaran->getEffectiveMapa02();
        abort_unless($mapa02 && $mapa02->status_mapa === 'selesai', 403, 'FR.IA.01 belum dapat digunakan karena MAPA.02 belum dikonfirmasi.');
        abort_unless(! empty($mapa02->matriks_peta), 403, 'MAPA.02 belum memiliki matriks instrumen yang dikonfirmasi.');
        abort_unless($pendaftaran->isInstrumenAktif(self::FORM_CODE), 403, 'FR.IA.01 tidak aktif pada MAPA.02 untuk pendaftaran ini.');

        $record = $pendaftaran->relationLoaded('iaPenilaian')
            ? $pendaftaran->iaPenilaian->firstWhere('kode_formulir', self::FORM_CODE)
            : IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', self::FORM_CODE)->first();
        $hasMasterSnapshot = $this->executionSnapshot($pendaftaran, $record) !== null;
        abort_unless(
            $hasMasterSnapshot || $this->publishedMasterSnapshot($pendaftaran->skema) !== null,
            403,
            'Master FR.IA.01 belum memiliki standar lengkap yang diterbitkan.'
        );
    }

    public function isLocked(?IaPenilaian $record): bool
    {
        return $record && in_array(strtolower((string) $record->status), self::LOCKED_STATUSES, true);
    }

    public function isAssessmentInProgress(PendaftaranAsesi $pendaftaran): bool
    {
        $jadwal = $pendaftaran->jadwal;

        return $jadwal && $jadwal->syncRealtimeStatus() === 'berlangsung';
    }

    public function isAssessmentFinished(PendaftaranAsesi $pendaftaran): bool
    {
        if ($pendaftaran->status_pendaftaran === 'selesai' || $pendaftaran->rekomendasi()->exists()) {
            return true;
        }

        $jadwal = $pendaftaran->jadwal;

        return $jadwal && $jadwal->syncRealtimeStatus() === 'selesai';
    }

    private function isRegistrationClosed(PendaftaranAsesi $pendaftaran): bool
    {
        return $pendaftaran->status_pendaftaran === 'selesai'
            || $pendaftaran->rekomendasi()->exists();
    }

    public function canEdit(Pengguna $user, PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): bool
    {
        return in_array($user->peran, ['asesor', 'admin', 'superadmin'], true)
            && ! $this->isRegistrationClosed($pendaftaran)
            && ! $this->isLocked($record)
            && ($record?->status !== 'submitted')
            && $this->isAssessmentInProgress($pendaftaran)
            && ($user->peran !== 'asesor'
                || (int) $pendaftaran->asesor_id === (int) $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists());
    }

    public function canSignAsesor(Pengguna $user, PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): bool
    {
        return $user->peran === 'asesor'
            && ! $this->isRegistrationClosed($pendaftaran)
            && ! $this->isLocked($record)
            && ($record?->status !== 'submitted')
            && $this->isAssessmentInProgress($pendaftaran)
            && ((int) $pendaftaran->asesor_id === (int) $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists());
    }

    public function canManageMaster(Pengguna $user, SkemaSertifikasi $skema): bool
    {
        return in_array($user->peran, ['admin', 'superadmin'], true)
            || ($user->peran === 'asesor'
                && ((int) $user->skema_id === (int) $skema->id
                    || JadwalAsesmen::where('asesor_id', $user->id)->where('skema_id', $skema->id)->exists()));
    }

    public function saveMaster(SkemaSertifikasi $skema, Pengguna $user, array $input, bool $publish): SchemeMasterInstrument
    {
        abort_unless($this->canManageMaster($user, $skema), 403, 'Anda tidak berwenang mengelola master FR.IA.01 untuk skema ini.');

        $validated = Validator::make($input, [
            'master_version' => ['present', 'nullable', 'string', 'max:64'],
            'standar_industri' => ['nullable', 'array', 'max:500'],
            'standar_industri.*' => ['nullable', 'string', 'max:3000'],
        ])->validate();

        $kukIds = $this->kukIdsForScheme($skema);
        $standards = $this->normalizeMasterStandards((array) ($validated['standar_industri'] ?? []), $skema);
        $this->assertMasterKeysBelongToScheme((array) ($validated['standar_industri'] ?? []), $kukIds);
        if ($publish) {
            abort_if(!$kukIds, 422, 'Skema belum memiliki KUK untuk diterbitkan sebagai master FR.IA.01.');
            foreach ($kukIds as $kukId) {
                if (trim((string) ($standards[$kukId] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        'standar_industri' => 'Lengkapi standar industri atau tempat kerja untuk semua KUK sebelum menerbitkan master.',
                    ]);
                }
            }
        }

        return DB::transaction(function () use ($skema, $user, $validated, $standards, $publish) {
            $instrument = SchemeMasterInstrument::where('skema_id', $skema->id)
                ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_01'))
                ->lockForUpdate()
                ->first();
            $instrument ??= new SchemeMasterInstrument([
                'skema_id' => $skema->id,
                'instrument_code' => 'ia_01',
                'title' => 'Ceklis Observasi Aktivitas',
                'is_active' => true,
                'created_by' => $user->id,
            ]);

            $metadata = $instrument->additional_metadata ?? [];
            $draftVersion = data_get($metadata, '_draft_version');
            $submittedVersion = (string) ($validated['master_version'] ?? '');
            if ($draftVersion && !hash_equals((string) $draftVersion, $submittedVersion)) {
                abort(409, 'Master FR.IA.01 berubah di tab lain. Muat ulang halaman sebelum menyimpan.');
            }

            if (!array_key_exists('lifecycle_status', $metadata)
                && !empty($metadata['is_saved'])
                && empty($metadata['published_version'])) {
                $legacyStandards = (array) ($metadata['standar_industri'] ?? $metadata['kuk_standards'] ?? []);
                $legacyStandards['standar_elemen'] = $legacyStandards['standar_elemen'] ?? $metadata['standar_elemen'] ?? [];
                $legacyStandards['default_standard'] = $legacyStandards['default_standard'] ?? $metadata['default_standard'] ?? $metadata['metadata_default_standard'] ?? '';
                $legacyPublishedStandards = $this->normalizeMasterStandards($legacyStandards, $skema);
                if ($this->standardsCoverKuks($legacyPublishedStandards, $this->kukIdsForScheme($skema))) {
                    $metadata['published_standards'] = $legacyPublishedStandards;
                    $metadata['published_version'] = 1;
                    $metadata['published_at'] = $metadata['saved_at'] ?? now()->toIso8601String();
                }
            }

            $metadata['standar_industri'] = $standards;
            $metadata['draft_standards'] = $standards;
            unset($metadata['signature_asesor']);
            $metadata['_draft_version'] = (string) Str::uuid();
            $metadata['is_saved'] = true;
            $metadata['saved_at'] = now()->toDateTimeString();

            if ($publish) {
                $metadata['published_standards'] = $standards;
                $metadata['published_version'] = (int) ($metadata['published_version'] ?? 0) + 1;
                $metadata['published_at'] = now()->toIso8601String();
                $metadata['lifecycle_status'] = 'published';
            } else {
                $metadata['lifecycle_status'] = 'draft';
            }

            $instrument->additional_metadata = $metadata;
            $instrument->save();
            LogAktivitas::catat(
                $publish ? 'Master FR.IA.01 diterbitkan' : 'Draft master FR.IA.01 disimpan',
                "Skema ID {$skema->id}; instrumen {$instrument->id}"
            );

            return $instrument;
        });
    }

    public function publishedMasterSnapshot(SkemaSertifikasi $skema): ?array
    {
        $kukIds = $this->kukIdsForScheme($skema);
        if (!$kukIds) {
            return null;
        }

        $instrument = SchemeMasterInstrument::where('skema_id', $skema->id)
            ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_01'))
            ->where('is_active', true)
            ->first();
        if (!$instrument) {
            return null;
        }

        $metadata = $instrument->additional_metadata ?? [];
        $legacyPublished = !array_key_exists('lifecycle_status', $metadata)
            && (bool) ($metadata['is_saved'] ?? false);
        if (!$legacyPublished && empty($metadata['published_version'])) {
            return null;
        }

        $source = $legacyPublished
            ? (!empty($metadata['published_standards'])
                ? $metadata['published_standards']
                : (!empty($metadata['standar_industri']) ? $metadata['standar_industri'] : ($metadata['kuk_standards'] ?? [])))
            : ($metadata['published_standards'] ?? []);
        $source = (array) $source;
        $source['standar_elemen'] = $source['standar_elemen'] ?? $metadata['standar_elemen'] ?? [];
        $source['default_standard'] = $source['default_standard'] ?? $metadata['default_standard'] ?? $metadata['metadata_default_standard'] ?? '';
        $standards = $this->normalizeMasterStandards((array) $source, $skema);
        foreach ($kukIds as $kukId) {
            if (trim((string) ($standards[$kukId] ?? '')) === '') {
                return null;
            }
        }

        return [
            'instrument_id' => (int) $instrument->id,
            'version' => (int) ($metadata['published_version'] ?? 1),
            'published_at' => $metadata['published_at'] ?? $instrument->updated_at?->toIso8601String(),
            'standar_industri' => $standards,
        ];
    }

    public function masterEditorStandards(?SchemeMasterInstrument $instrument, SkemaSertifikasi $skema): array
    {
        $metadata = $instrument?->additional_metadata ?? [];

        return $this->normalizeMasterStandards(
            (array) ($metadata['draft_standards'] ?? $metadata['published_standards'] ?? $metadata['standar_industri'] ?? $metadata['kuk_standards'] ?? []),
            $skema
        );
    }

    public function executionStandards(PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): array
    {
        return $this->executionSnapshot($pendaftaran, $record)['standar_industri'] ?? [];
    }

    public function canSignAsesi(Pengguna $user, PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): bool
    {
        return $user->peran === 'asesi'
            && (int) $pendaftaran->asesi_id === (int) $user->id
            && $record?->status === 'submitted'
            && $this->isAssessmentFinished($pendaftaran);
    }

    public function kukIds(PendaftaranAsesi $pendaftaran): array
    {
        return $pendaftaran->skema->unitKompetensi
            ->flatMap(fn ($unit) => $unit->elemenKompetensi->flatMap(fn ($elemen) => $elemen->kriteriaUnjukKerja->pluck('id')))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function save(PendaftaranAsesi $pendaftaran, Pengguna $user, array $input): IaPenilaian
    {
        $this->authorizeParticipant($pendaftaran, $user);
        $this->assertWorkflowReady($pendaftaran);
        $this->assertPayloadSize($input);

        $record = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', self::FORM_CODE)
            ->first();
        abort_unless($this->canEdit($user, $pendaftaran, $record), 403, 'FR.IA.01 sudah terkunci atau Anda tidak berwenang mengubahnya.');
        abort_unless(
            $this->hasValidSignature($record, 'asesor', $pendaftaran),
            422,
            'Tanda tangan asesor wajib dibuat sebelum menyimpan FR.IA.01.'
        );

        $validated = Validator::make($input, [
            'version' => ['present', 'nullable', 'string', 'max:64'],
            'simpan_draft' => ['sometimes', 'in:1,true,on'],
            'finalisasi' => ['sometimes', 'in:1,true,on'],
            'standar_industri' => ['nullable', 'array', 'max:500'],
            'standar_industri.*' => ['nullable', 'string', 'max:3000'],
            'pencapaian' => ['nullable', 'array', 'max:500'],
            'pencapaian.*' => ['nullable', 'in:K,BK'],
            'penilaian_lanjut' => ['nullable', 'array', 'max:500'],
            'penilaian_lanjut.*' => ['nullable', 'string', 'max:2000'],
            'catatan_kuk' => ['nullable', 'array', 'max:500'],
            'catatan_kuk.*' => ['nullable', 'string', 'max:2000'],
            'umpan_balik' => ['nullable', 'string', 'max:3000'],
            'rekomendasi' => ['nullable', 'in:K,BK'],
        ])->validate();

        $kukIds = $this->kukIds($pendaftaran);
        $elementIds = $pendaftaran->skema->unitKompetensi
            ->flatMap(fn ($unit) => $unit->elemenKompetensi->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertMapKeysBelongToRegistration($validated, $kukIds, $elementIds);

        $pencapaian = (array) ($validated['pencapaian'] ?? []);
        $penilaianLanjut = (array) ($validated['penilaian_lanjut'] ?? []);
        $catatanKuk = (array) ($validated['catatan_kuk'] ?? []);
        $umpanBalik = trim((string) ($validated['umpan_balik'] ?? ''));
        $rekomendasi = $validated['rekomendasi'] ?? null;
        $finalize = ! isset($validated['simpan_draft']) && isset($validated['finalisasi']);

        if ($finalize) {
            foreach ($kukIds as $kukId) {
                if (! in_array($pencapaian[$kukId] ?? null, ['K', 'BK'], true)) {
                    throw ValidationException::withMessages([
                        'pencapaian' => 'Lengkapi pencapaian untuk semua KUK sebelum menyimpan formulir.',
                    ]);
                }
            }
            if (! $kukIds) {
                throw ValidationException::withMessages(['pencapaian' => 'Skema belum memiliki KUK untuk dinilai.']);
            }
            if ($umpanBalik === '') {
                throw ValidationException::withMessages(['umpan_balik' => 'Umpan balik untuk asesi wajib diisi sebelum finalisasi.']);
            }
            if (! $rekomendasi) {
                throw ValidationException::withMessages(['rekomendasi' => 'Pilih rekomendasi Kompeten atau Belum Kompeten.']);
            }
            if (! $this->hasValidSignature($record, 'asesor', $pendaftaran)) {
                throw ValidationException::withMessages([
                    'signature' => 'Tanda tangan asesor wajib tersedia sebelum formulir difinalisasi.',
                ]);
            }
        }

        $saved = DB::transaction(function () use ($pendaftaran, $user, $validated, $pencapaian, $penilaianLanjut, $catatanKuk, $umpanBalik, $rekomendasi, $finalize, $kukIds) {
            PendaftaranAsesi::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
            $current = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->where('kode_formulir', self::FORM_CODE)
                ->lockForUpdate()
                ->first();
            $this->assertVersion($current, (string) ($validated['version'] ?? ''));
            abort_unless(! $this->isLocked($current), 403, 'FR.IA.01 sudah terkunci dan tidak dapat diubah.');
            abort_unless($current?->status !== 'submitted', 403, 'FR.IA.01 sudah dikirim untuk ditandatangani asesi.');
            abort_unless(
                $this->isAssessmentInProgress($pendaftaran),
                403,
                'FR.IA.01 hanya dapat diisi saat jadwal asesmen sedang berlangsung.'
            );
            if (! $this->hasValidSignature($current, 'asesor', $pendaftaran)) {
                throw ValidationException::withMessages([
                    'signature' => 'Tanda tangan asesor wajib tersedia sebelum menyimpan FR.IA.01.',
                ]);
            }

            $data = $current?->data_jawaban ?? [];
            $snapshot = $this->executionSnapshot($pendaftaran, $current);
            abort_unless(is_array($snapshot), 403, 'Master FR.IA.01 belum diterbitkan atau tidak lagi tersedia.');
            $standar = (array) ($snapshot['standar_industri'] ?? []);
            foreach ($kukIds as $kukId) {
                if (trim((string) ($standar[$kukId] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        'standar_industri' => 'Standar master FR.IA.01 belum lengkap untuk semua KUK.',
                    ]);
                }
            }
            $data = array_merge($data, [
                'master_snapshot' => $snapshot,
                'standar_industri' => $standar,
                'pencapaian' => $pencapaian,
                'penilaian_lanjut' => $penilaianLanjut,
                'catatan_kuk' => $catatanKuk,
                'standar_industri_scope' => 'kuk',
                'umpan_balik' => $umpanBalik,
                'saved_at' => now()->toDateTimeString(),
                '_version' => (string) Str::uuid(),
            ]);

            $current ??= new IaPenilaian([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => self::FORM_CODE,
            ]);
            $current->user_id = $user->id;
            $current->role = $user->peran;
            $current->data_jawaban = $data;
            $current->catatan_asesor = $umpanBalik;
            $current->rekomendasi = $rekomendasi;
            $current->status = $finalize ? 'submitted' : 'draft';
            $current->tanda_tangan = data_get($data, 'signature_asesor.path', $current->tanda_tangan);
            $current->save();

            LogAktivitas::catat(
                $finalize ? 'FR.IA.01 difinalisasi' : 'Draft FR.IA.01 disimpan',
                "Pendaftaran ID {$pendaftaran->id}; formulir ".self::FORM_CODE
            );

            return $current;
        });

        return $saved;
    }

    public function sign(PendaftaranAsesi $pendaftaran, Pengguna $user, string $signer, array $input): IaPenilaian
    {
        $this->authorizeParticipant($pendaftaran, $user);
        $this->assertWorkflowReady($pendaftaran);

        if (! in_array($signer, ['asesor', 'asesi'], true)) {
            abort(404);
        }
        $validated = Validator::make($input, [
            'version' => ['present', 'nullable', 'string', 'max:64'],
            'signature' => ['required', 'string', 'max:500000', 'regex:/^data:image\\/png;base64,[A-Za-z0-9+\\/=]+$/'],
        ])->validate();
        $binary = $this->decodeSignature($validated['signature']);

        $record = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', self::FORM_CODE)
            ->first();
        abort_unless(
            $record || $signer === 'asesor',
            422,
            'Asesor harus menandatangani FR.IA.01 terlebih dahulu sebelum asesi dapat membubuhkan tanda tangan.'
        );
        abort_unless(! $this->isLocked($record), 403, 'FR.IA.01 sudah terkunci dan tidak menerima tanda tangan baru.');
        if ($signer === 'asesor') {
            abort_unless($this->canSignAsesor($user, $pendaftaran, $record), 403, 'Tanda tangan hanya dapat dibuat oleh asesor yang ditugaskan sebelum formulir dikirim atau dikunci.');
        } else {
            abort_unless($this->canSignAsesi($user, $pendaftaran, $record), 403, 'Asesi hanya dapat menandatangani setelah asesmen selesai dan formulir dikirim.');
        }

        $previousPath = data_get($record?->data_jawaban, 'signature_'.$signer.'.path');
        $path = 'signatures/fr-ia-01/'.$pendaftaran->id.'/'.Str::uuid().'.png';
        if (! Storage::disk('local')->put($path, $binary)) {
            throw new \RuntimeException('Tanda tangan tidak dapat disimpan ke penyimpanan privat.');
        }

        try {
            $saved = DB::transaction(function () use ($pendaftaran, $user, $signer, $validated, $record, $path) {
                PendaftaranAsesi::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
                $current = $record
                    ? IaPenilaian::whereKey($record->id)->lockForUpdate()->firstOrFail()
                    : IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                        ->where('kode_formulir', self::FORM_CODE)
                        ->lockForUpdate()
                        ->first();
                $this->assertVersion($current, (string) ($validated['version'] ?? ''));
                abort_unless(! $this->isLocked($current), 403, 'FR.IA.01 sudah terkunci dan tidak menerima tanda tangan baru.');
                if ($signer === 'asesor') {
                    abort_unless($this->canSignAsesor($user, $pendaftaran, $current), 403, 'Tanda tangan hanya dapat dibuat oleh asesor yang ditugaskan sebelum formulir dikirim atau dikunci.');
                } else {
                    abort_unless($this->canSignAsesi($user, $pendaftaran, $current), 403, 'Asesi hanya dapat menandatangani setelah asesmen selesai dan formulir dikirim.');
                }

                $data = $current?->data_jawaban ?? [];
                if (empty($data['master_snapshot'])) {
                    $snapshot = $this->executionSnapshot($pendaftaran, $current);
                    abort_unless(is_array($snapshot), 403, 'Master FR.IA.01 belum diterbitkan atau tidak lagi tersedia.');
                    $data['master_snapshot'] = $snapshot;
                    $data['standar_industri'] = $snapshot['standar_industri'];
                    $data['standar_industri_scope'] = 'kuk';
                }
                $field = 'signature_'.$signer;
                $data[$field] = [
                    'path' => $path,
                    'signer_user_id' => $user->id,
                    'signer_role' => $user->peran,
                    'pendaftaran_id' => $pendaftaran->id,
                    'document' => self::FORM_CODE,
                    'signed_at' => now()->toIso8601String(),
                    'status' => 'signed',
                ];
                $data['_version'] = (string) Str::uuid();
                $current ??= new IaPenilaian([
                    'pendaftaran_id' => $pendaftaran->id,
                    'kode_formulir' => self::FORM_CODE,
                ]);
                $current->data_jawaban = $data;
                if ($signer === 'asesor') {
                    $current->tanda_tangan = $path;
                    $current->tanggal_tanda_tangan = now();
                } else {
                    $current->status = 'completed';
                }
                $current->save();

                LogAktivitas::catat(
                    "Tanda tangan {$signer} FR.IA.01 disimpan",
                    "Pendaftaran ID {$pendaftaran->id}; formulir ".self::FORM_CODE
                );

                return $current;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if (is_string($previousPath)
            && str_starts_with($previousPath, 'signatures/fr-ia-01/'.$pendaftaran->id.'/')
            && ! str_contains($previousPath, '..')
            && $previousPath !== $path) {
            Storage::disk('local')->delete($previousPath);
        }

        return $saved;
    }

    public function authorizedSignaturePath(PendaftaranAsesi $pendaftaran, Pengguna $user, string $signer): string
    {
        $this->authorizeParticipant($pendaftaran, $user);
        $this->assertWorkflowReady($pendaftaran);

        if (! in_array($signer, ['asesor', 'asesi'], true)) {
            abort(404);
        }

        $record = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', self::FORM_CODE)
            ->first();
        $signature = data_get($record?->data_jawaban, 'signature_'.$signer);
        abort_unless($record, 404, 'Tanda tangan tidak tersedia.');
        $expectedUserIds = $signer === 'asesi'
            ? [(int) $pendaftaran->asesi_id]
            : array_filter([(int) $pendaftaran->asesor_id, (int) $pendaftaran->jadwal?->asesor_id]);

        abort_unless(
            is_array($signature)
                && (int) ($signature['pendaftaran_id'] ?? 0) === (int) $pendaftaran->id
                && ($signature['document'] ?? null) === self::FORM_CODE
                && in_array((int) ($signature['signer_user_id'] ?? 0), $expectedUserIds, true)
                && ($signature['signer_role'] ?? null) === $signer
                && is_string($signature['path'] ?? null)
                && str_starts_with($signature['path'], 'signatures/fr-ia-01/'.$pendaftaran->id.'/')
                && ! str_contains($signature['path'], '..'),
            404,
            'Tanda tangan tidak tersedia.'
        );

        $path = $signature['path'];
        abort_unless(Storage::disk('local')->exists($path), 404, 'File tanda tangan tidak tersedia.');

        return Storage::disk('local')->path($path);
    }

    private function assertMapKeysBelongToRegistration(array $data, array $kukIds, array $elementIds): void
    {
        $allowedKeys = array_fill_keys(array_map('strval', array_merge($kukIds, $elementIds)), true);
        foreach (['standar_industri', 'pencapaian', 'penilaian_lanjut', 'catatan_kuk'] as $field) {
            foreach (array_keys($data[$field] ?? []) as $key) {
                if (! isset($allowedKeys[(string) $key])) {
                    throw ValidationException::withMessages([
                        $field => 'Data penilaian berisi ID KUK/elemen yang tidak termasuk dalam skema pendaftaran ini.',
                    ]);
                }
            }
        }
    }

    private function assertMasterKeysBelongToScheme(array $standards, array $kukIds): void
    {
        $allowed = array_fill_keys(array_map('strval', $kukIds), true);
        foreach (array_keys($standards) as $key) {
            if (!isset($allowed[(string) $key])) {
                throw ValidationException::withMessages([
                    'standar_industri' => 'Data master berisi ID KUK yang tidak termasuk dalam skema ini.',
                ]);
            }
        }
    }

    private function assertPayloadSize(array $input): void
    {
        if (strlen(serialize($input)) > 4 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'formulir' => 'Ukuran data FR.IA.01 terlalu besar. Kurangi panjang catatan sebelum menyimpan.',
            ]);
        }
    }

    public function kukIdsForScheme(SkemaSertifikasi $skema): array
    {
        return $skema->unitKompetensi
            ->flatMap(fn ($unit) => $unit->elemenKompetensi->flatMap(fn ($elemen) => $elemen->kriteriaUnjukKerja->pluck('id')))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function normalizeMasterStandards(array $standards, SkemaSertifikasi $skema): array
    {
        $elementStandards = (array) ($standards['standar_elemen'] ?? []);
        $defaultStandard = $standards['default_standard'] ?? $standards['metadata_default_standard'] ?? '';
        $normalized = [];
        foreach ($skema->unitKompetensi as $unit) {
            foreach ($unit->elemenKompetensi as $elemen) {
                $elementValue = $standards[$elemen->id] ?? $elementStandards[$elemen->id] ?? null;
                foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                    $value = $standards[$kuk->id] ?? $elementValue ?? $defaultStandard;
                    $normalized[$kuk->id] = is_string($value) ? trim($value) : '';
                }
            }
        }

        return $normalized;
    }

    private function executionSnapshot(PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): ?array
    {
        $kukIds = $this->kukIds($pendaftaran);
        $savedSnapshot = data_get($record?->data_jawaban, 'master_snapshot');
        if (is_array($savedSnapshot)
            && is_array($savedSnapshot['standar_industri'] ?? null)
            && $this->standardsCoverKuks($savedSnapshot['standar_industri'], $kukIds)) {
            return $savedSnapshot;
        }

        $legacyStandards = data_get($record?->data_jawaban, 'standar_industri');
        if (is_array($legacyStandards) && $this->standardsCoverKuks($legacyStandards, $kukIds)) {
            return [
                'instrument_id' => null,
                'version' => 0,
                'published_at' => null,
                'legacy_execution_standards' => true,
                'standar_industri' => $legacyStandards,
            ];
        }

        return $this->publishedMasterSnapshot($pendaftaran->skema);
    }

    private function standardsCoverKuks(array $standards, array $kukIds): bool
    {
        if (!$kukIds) {
            return false;
        }

        foreach ($kukIds as $kukId) {
            if (!is_string($standards[$kukId] ?? null) || trim($standards[$kukId]) === '') {
                return false;
            }
        }

        return true;
    }

    private function assertVersion(?IaPenilaian $record, string $version): void
    {
        if (! $record && $version === '') {
            return;
        }

        $currentVersion = data_get($record?->data_jawaban, '_version');
        $matches = $currentVersion
            ? hash_equals((string) $currentVersion, $version)
            : $record?->updated_at?->format('Y-m-d H:i:s') === $version;

        if (! $record || $version === '' || ! $matches) {
            abort(409, 'Data FR.IA.01 berubah di tab lain. Muat ulang halaman sebelum menyimpan perubahan.');
        }
    }

    private function hasValidSignature(?IaPenilaian $record, string $signer, PendaftaranAsesi $pendaftaran): bool
    {
        if (! $record) {
            return false;
        }

        $signature = data_get($record->data_jawaban, 'signature_'.$signer);
        $expectedUserIds = $signer === 'asesi'
            ? [(int) $pendaftaran->asesi_id]
            : array_filter([(int) $pendaftaran->asesor_id, (int) $pendaftaran->jadwal?->asesor_id]);

        return is_array($signature)
            && (int) ($signature['pendaftaran_id'] ?? 0) === (int) $pendaftaran->id
            && ($signature['document'] ?? null) === self::FORM_CODE
            && in_array((int) ($signature['signer_user_id'] ?? 0), $expectedUserIds, true)
            && ($signature['signer_role'] ?? null) === $signer
            && is_string($signature['path'] ?? null)
            && str_starts_with($signature['path'], 'signatures/fr-ia-01/'.$pendaftaran->id.'/')
            && ! str_contains($signature['path'], '..')
            && Storage::disk('local')->exists($signature['path']);
    }

    private function decodeSignature(string $dataUrl): string
    {
        $binary = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')), true);
        $imageInfo = $binary === false ? false : getimagesizefromstring($binary);

        if ($binary === false || strlen($binary) > 256 * 1024 || ! $imageInfo || $imageInfo['mime'] !== 'image/png') {
            throw ValidationException::withMessages(['signature' => 'Gambar tanda tangan PNG tidak valid atau ukurannya terlalu besar.']);
        }
        if ($imageInfo[0] > 1600 || $imageInfo[1] > 600 || $imageInfo[0] < 100 || $imageInfo[1] < 40) {
            throw ValidationException::withMessages(['signature' => 'Ukuran kanvas tanda tangan tidak valid.']);
        }
        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('Ekstensi GD diperlukan untuk memvalidasi tanda tangan.');
        }

        $image = @imagecreatefromstring($binary);
        if (! $image) {
            throw ValidationException::withMessages(['signature' => 'Gambar tanda tangan tidak dapat dibaca.']);
        }

        $hasInk = false;
        for ($y = 0; $y < imagesy($image) && ! $hasInk; $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                if ($color['alpha'] < 127 && ($color['red'] < 245 || $color['green'] < 245 || $color['blue'] < 245)) {
                    $hasInk = true;
                    break;
                }
            }
        }
        imagedestroy($image);

        if (! $hasInk) {
            throw ValidationException::withMessages(['signature' => 'Tanda tangan kosong. Bubuhkan tanda tangan sebelum menyimpan.']);
        }

        return $binary;
    }
}
