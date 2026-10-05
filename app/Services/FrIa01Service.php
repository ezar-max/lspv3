<?php

namespace App\Services;

use App\Models\IaPenilaian;
use App\Models\LogAktivitas;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FrIa01Service
{
    private const FORM_CODE = 'FR.IA.01';

    private const LOCKED_STATUSES = ['completed', 'submitted', 'evaluated', 'locked', 'selesai'];

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
    }

    public function isLocked(?IaPenilaian $record): bool
    {
        return $record && in_array(strtolower((string) $record->status), self::LOCKED_STATUSES, true);
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
            && ($user->peran !== 'asesor'
                || (int) $pendaftaran->asesor_id === (int) $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists());
    }

    public function canSignAsesor(Pengguna $user, PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): bool
    {
        return $user->peran === 'asesor'
            && ! $this->isRegistrationClosed($pendaftaran)
            && ! $this->isLocked($record)
            && ((int) $pendaftaran->asesor_id === (int) $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists());
    }

    public function canSignAsesi(Pengguna $user, PendaftaranAsesi $pendaftaran, ?IaPenilaian $record): bool
    {
        return $user->peran === 'asesi'
            && (int) $pendaftaran->asesi_id === (int) $user->id
            && ! $this->isRegistrationClosed($pendaftaran)
            && ! $this->isLocked($record);
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
        $standar = $this->normalizeStandar((array) ($validated['standar_industri'] ?? []), $pendaftaran);
        $penilaianLanjut = (array) ($validated['penilaian_lanjut'] ?? []);
        $catatanKuk = (array) ($validated['catatan_kuk'] ?? []);
        $umpanBalik = trim((string) ($validated['umpan_balik'] ?? ''));
        $rekomendasi = $validated['rekomendasi'] ?? null;
        $finalize = ! isset($validated['simpan_draft']) && isset($validated['finalisasi']);

        if ($finalize) {
            $incomplete = [];
            foreach ($kukIds as $kukId) {
                if (! in_array($pencapaian[$kukId] ?? null, ['K', 'BK'], true)) {
                    $incomplete[] = (string) $kukId;
                }
                if (trim((string) ($standar[$kukId] ?? '')) === '') {
                    $incomplete[] = 'standar:'.$kukId;
                }
            }

            if ($incomplete) {
                throw ValidationException::withMessages([
                    'pencapaian' => 'Lengkapi pencapaian dan standar industri untuk semua KUK sebelum menyimpan formulir.',
                ]);
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
            if (! $this->hasValidSignature($record, 'asesor', $pendaftaran)
                || ! $this->hasValidSignature($record, 'asesi', $pendaftaran)) {
                throw ValidationException::withMessages([
                    'signature' => 'Tanda tangan asesor dan asesi wajib tersedia sebelum formulir difinalisasi.',
                ]);
            }
        }

        $saved = DB::transaction(function () use ($pendaftaran, $user, $validated, $standar, $pencapaian, $penilaianLanjut, $catatanKuk, $umpanBalik, $rekomendasi, $finalize) {
            PendaftaranAsesi::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
            $current = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->where('kode_formulir', self::FORM_CODE)
                ->lockForUpdate()
                ->first();
            $this->assertVersion($current, (string) ($validated['version'] ?? ''));
            abort_unless(! $this->isLocked($current), 403, 'FR.IA.01 sudah terkunci dan tidak dapat diubah.');
            if ($finalize && (! $this->hasValidSignature($current, 'asesor', $pendaftaran)
                || ! $this->hasValidSignature($current, 'asesi', $pendaftaran))) {
                throw ValidationException::withMessages([
                    'signature' => 'Tanda tangan asesor dan asesi wajib tersedia sebelum formulir difinalisasi.',
                ]);
            }

            $data = $current?->data_jawaban ?? [];
            $data = array_merge($data, [
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
            $current->status = $finalize ? 'completed' : 'draft';
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
        if ($signer === 'asesor') {
            abort_unless($this->canSignAsesor($user, $pendaftaran, null), 403, 'Hanya asesor yang ditugaskan yang dapat menandatangani formulir ini.');
        } else {
            abort_unless($this->canSignAsesi($user, $pendaftaran, null), 403, 'Tanda tangan hanya dapat diberikan oleh asesi pada pendaftarannya sendiri.');
        }

        $validated = Validator::make($input, [
            'version' => ['required', 'string', 'max:64'],
            'signature' => ['required', 'string', 'max:500000', 'regex:/^data:image\\/png;base64,[A-Za-z0-9+\\/=]+$/'],
        ])->validate();
        $binary = $this->decodeSignature($validated['signature']);

        $record = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', self::FORM_CODE)
            ->first();
        abort_unless($record, 422, 'Simpan draft FR.IA.01 terlebih dahulu sebelum memberikan tanda tangan.');
        abort_unless(! $this->isLocked($record), 403, 'FR.IA.01 sudah terkunci dan tidak menerima tanda tangan baru.');

        $previousPath = data_get($record->data_jawaban, 'signature_'.$signer.'.path');
        $path = 'signatures/fr-ia-01/'.$pendaftaran->id.'/'.Str::uuid().'.png';
        if (! Storage::disk('local')->put($path, $binary)) {
            throw new \RuntimeException('Tanda tangan tidak dapat disimpan ke penyimpanan privat.');
        }

        try {
            $saved = DB::transaction(function () use ($pendaftaran, $user, $signer, $validated, $record, $path) {
                PendaftaranAsesi::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
                $current = IaPenilaian::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $this->assertVersion($current, $validated['version']);
                abort_unless(! $this->isLocked($current), 403, 'FR.IA.01 sudah terkunci dan tidak menerima tanda tangan baru.');

                $data = $current->data_jawaban ?? [];
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
                $current->data_jawaban = $data;
                if ($signer === 'asesor') {
                    $current->tanda_tangan = $path;
                    $current->tanggal_tanda_tangan = now();
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
            ->firstOrFail();
        $signature = data_get($record->data_jawaban, 'signature_'.$signer);
        $expectedUserId = $signer === 'asesi'
            ? (int) $pendaftaran->asesi_id
            : (int) ($pendaftaran->asesor_id ?: $pendaftaran->jadwal?->asesor_id);

        abort_unless(
            is_array($signature)
                && (int) ($signature['pendaftaran_id'] ?? 0) === (int) $pendaftaran->id
                && ($signature['document'] ?? null) === self::FORM_CODE
                && (int) ($signature['signer_user_id'] ?? 0) === $expectedUserId
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

    private function assertPayloadSize(array $input): void
    {
        if (strlen(serialize($input)) > 4 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'formulir' => 'Ukuran data FR.IA.01 terlalu besar. Kurangi panjang catatan sebelum menyimpan.',
            ]);
        }
    }

    private function normalizeStandar(array $standar, PendaftaranAsesi $pendaftaran): array
    {
        $normalized = [];
        foreach ($pendaftaran->skema->unitKompetensi as $unit) {
            foreach ($unit->elemenKompetensi as $elemen) {
                foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                    $value = $standar[$kuk->id] ?? $standar[$elemen->id] ?? '';
                    $normalized[$kuk->id] = is_string($value) ? trim($value) : '';
                }
            }
        }

        return $normalized;
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
        $expectedUserId = $signer === 'asesi'
            ? (int) $pendaftaran->asesi_id
            : (int) ($pendaftaran->asesor_id ?: $pendaftaran->jadwal?->asesor_id);

        return is_array($signature)
            && (int) ($signature['pendaftaran_id'] ?? 0) === (int) $pendaftaran->id
            && ($signature['document'] ?? null) === self::FORM_CODE
            && (int) ($signature['signer_user_id'] ?? 0) === $expectedUserId
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
