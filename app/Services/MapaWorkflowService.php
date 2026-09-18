<?php

namespace App\Services;

use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\SkemaSertifikasi;
use App\Models\SchemeMasterInstrument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MapaWorkflowService
{
    /**
     * Whitelist kunci instrumen yang didukung pada MAPA.02
     */
    public const VALID_INSTRUMENT_KEYS = ['clo', 'dpt', 'pmo', 'dpe', 'dpl', 'vp', 'pw', 'crp'];

    /**
     * Pemetaan kode instrumen master / umum ke internal key MAPA.02
     */
    public static function normalizeInstrumentCode(string $code): string
    {
        $c = strtolower(trim(str_replace(['.', '-', '_', ' '], '', $code)));

        return match ($c) {
            'ia01', 'clo', 'observasi', 'ceklisobservasi' => 'clo',
            'ia02', 'dpt', 'praktik', 'tugaspraktik', 'demonstrasi' => 'dpt',
            'ia03', 'pmo', 'pertanyaanobservasi', 'pertanyaanpendukung' => 'pmo',
            'ia05', 'ia06', 'dpe', 'ujitulis', 'cbt', 'esai', 'pilihanganda' => 'dpe',
            'ia07', 'dpl', 'pertanyaanlisan', 'lisan' => 'dpl',
            'ia08', 'vp', 'portofolio', 'verifikasiportofolio' => 'vp',
            'ia09', 'pw', 'wawancara', 'pertanyaanwawancara' => 'pw',
            'ia11', 'crp', 'reviuproduk', 'produk' => 'crp',
            default => in_array($c, self::VALID_INSTRUMENT_KEYS, true) ? $c : 'clo',
        };
    }

    /**
     * Dapatkan daftar key instrumen default yang aktif untuk skema sertifikasi berdasarkan tabel master
     */
    public function getActiveInstrumentKeysForScheme(SkemaSertifikasi $skema): array
    {
        $masters = SchemeMasterInstrument::where('skema_id', $skema->id)
            ->where('is_active', true)
            ->get();

        if ($masters->isEmpty()) {
            // Standar default BNSP jika master belum dikonfigurasi spesifik: CLO, DPT, DPE
            return ['clo', 'dpt', 'dpe'];
        }

        $activeKeys = [];
        foreach ($masters as $m) {
            $key = self::normalizeInstrumentCode($m->instrument_code);
            $activeKeys[] = $key;
        }

        // Observasi Praktik (CLO) selalu aktif sebagai baseline standar sertifikasi kejuruan/vokasi
        if (!in_array('clo', $activeKeys, true)) {
            $activeKeys[] = 'clo';
        }

        return array_values(array_unique($activeKeys));
    }

    /**
     * Generate default matriks peta instrumen MAPA.02 dari struktur Unit -> Elemen -> KUK Master Skema
     */
    public function generateDefaultMatrix(SkemaSertifikasi $skema): array
    {
        $activeKeys = $this->getActiveInstrumentKeysForScheme($skema);
        $matrix = [];

        $skema->loadMissing('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja');

        foreach ($skema->unitKompetensi as $unit) {
            foreach ($unit->elemenKompetensi as $elem) {
                $kuks = $elem->kriteriaUnjukKerja;
                if ($kuks->isNotEmpty()) {
                    foreach ($kuks as $kuk) {
                        $matrix[$unit->id][$elem->id][$kuk->id] = $this->buildInstrumentCheckboxArray($activeKeys);
                    }
                } else {
                    $matrix[$unit->id][$elem->id]['elem_only'] = $this->buildInstrumentCheckboxArray($activeKeys);
                }
            }
        }

        return $matrix;
    }

    /**
     * Helper membuat array checkbox nilai boolean 1/0
     */
    private function buildInstrumentCheckboxArray(array $activeKeys): array
    {
        $res = [];
        foreach (self::VALID_INSTRUMENT_KEYS as $k) {
            $res[$k] = in_array($k, $activeKeys, true) ? 1 : 0;
        }
        return $res;
    }

    /**
     * Normalisasi dan sinkronisasi data konfirmasi pihak relevan
     */
    private function normalizeKonfirmasiData(array $data): array
    {
        $konfirmasiOrang = (array) ($data['konfirmasi_orang_relevan'] ?? []);
        $konfirmasiTabel = (array) ($data['konfirmasi_pihak_relevan_tabel'] ?? []);

        $roleMap = [
            'manajer_lsp' => 'Manajer sertifikasi LSP',
            'lead_asesor' => 'Master Asesor / Master Trainer / Lead Asesor Kompetensi',
            'manajer_pelatihan' => 'Manajer pelatihan Lembaga Training terakreditasi / terdaftar',
            'supervisor' => 'Manajer atau supervisor di tempat kerja',
        ];

        $normalizedTabel = [];

        foreach ($roleMap as $key => $label) {
            $isRelevan = false;
            foreach ($konfirmasiOrang as $saved) {
                if (trim(strtolower($saved)) === trim(strtolower($label))) {
                    $isRelevan = true;
                    break;
                }
                if ($key === 'manajer_pelatihan' && str_contains(strtolower($saved), 'pelatihan')) {
                    $isRelevan = true;
                    break;
                }
                if ($key === 'supervisor' && str_contains(strtolower($saved), 'supervisor')) {
                    $isRelevan = true;
                    break;
                }
            }

            if (!$isRelevan && !empty($konfirmasiTabel[$key]['relevan'])) {
                $isRelevan = true;
                $konfirmasiOrang[] = $label;
            }

            $normalizedTabel[$key] = [
                'relevan' => $isRelevan ? 1 : 0,
                'nama' => $konfirmasiTabel[$key]['nama'] ?? '',
                'ttd_tanggal' => $konfirmasiTabel[$key]['ttd_tanggal'] ?? '',
            ];
        }

        return [
            'konfirmasi_orang_relevan' => array_values(array_unique($konfirmasiOrang)),
            'konfirmasi_pihak_relevan_tabel' => $normalizedTabel,
        ];
    }

    /**
     * Inisialisasi otomatis / ambil dokumen FR.MAPA.01 secara idempotent
     */
    public function getOrCreateMapa01(PendaftaranAsesi $pendaftaran, ?int $asesorId = null): Mapa01
    {
        $asesorId = $asesorId ?: ($pendaftaran->asesor_id ?: ($pendaftaran->jadwal?->asesor_id ?: auth()->id()));

        $pendaftaran->loadMissing('skema.unitKompetensi');

        $existing = $pendaftaran->relationLoaded('mapa01') ? $pendaftaran->mapa01 : Mapa01::where('pendaftaran_id', $pendaftaran->id)->first();
        if (!$existing) {
            $existing = Mapa01::where('pendaftaran_id', $pendaftaran->id)->first();
        }

        // Jika sudah ada Master Template Skema, gunakan sebagai baseline
        $master = Mapa01::where('skema_id', $pendaftaran->skema_id)->whereNull('pendaftaran_id')->first();
        $masterValidator = $master?->penyusun_validator_tabel['validator_1'] ?? [];
        $isMasterValidated = !empty($masterValidator['ttd']) || (($masterValidator['status_validasi'] ?? '') === 'tervalidasi');

        if ($existing) {
            // Jika master skema sudah tervalidasi tapi data validator peserta belum tersinkronisasi:
            if ($isMasterValidated) {
                $needsUpdate = false;
                $exTable = $existing->penyusun_validator_tabel ?: [];
                if (empty($exTable['validator_1']['ttd']) || ($exTable['validator_1']['status_validasi'] ?? '') !== 'tervalidasi') {
                    $exTable['validator_1'] = $masterValidator;
                    if (!empty($master->penyusun_validator_tabel['penyusun_1']) && empty($exTable['penyusun_1']['ttd'])) {
                        $exTable['penyusun_1'] = $master->penyusun_validator_tabel['penyusun_1'];
                    }
                    $existing->penyusun_validator_tabel = $exTable;
                    $needsUpdate = true;
                }
                if (empty($existing->tanda_tangan_asesor) && !empty($master->tanda_tangan_asesor)) {
                    $existing->tanda_tangan_asesor = $master->tanda_tangan_asesor;
                    $existing->tanggal_ttd_asesor = $master->tanggal_ttd_asesor;
                    $needsUpdate = true;
                }
                if ($existing->status_mapa === 'draft' && $master->status_mapa === 'selesai') {
                    $existing->status_mapa = 'selesai';
                    $needsUpdate = true;
                }
                if ($needsUpdate) {
                    $existing->save();
                }

                $masterTtdAdmin = $masterValidator['ttd'] ?? null;
                if ($masterTtdAdmin && empty($pendaftaran->tanda_tangan_admin)) {
                    $pendaftaran->update([
                        'tanda_tangan_admin' => $masterTtdAdmin,
                        'tanggal_ttd_admin' => now(),
                    ]);
                }
            }
            return $existing;
        }

        // Buat default unit matriks jika ada unit kompetensi
        $defaultMatriks = [];
        if ($master && !empty($master->rencana_unit_matriks)) {
            $defaultMatriks = $master->rencana_unit_matriks;
        } else {
            foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                $defaultMatriks[$unit->id] = [
                    'l' => 1,
                    'tl' => 0,
                    't' => 1,
                    'methods' => ['CL', 'DPT', 'PW'],
                    'bukti' => 'Bukti hasil demonstrasi praktik langsung unjuk kerja dan portofolio unit ' . $unit->judul_unit,
                ];
            }
        }

        // Buat record default awal dengan meng-clone dari master jika ada
        return Mapa01::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $pendaftaran->skema_id,
            'asesor_id' => $asesorId,
            'pendekatan_asesi' => $master ? ($master->pendekatan_asesi ?: []) : ['Hasil pelatihan dan / atau pendidikan:'],
            'tujuan_asesmen' => $pendaftaran->tujuan_asesmen ?: ($master?->tujuan_asesmen ?: 'Sertifikasi'),
            'tujuan_asesmen_lainnya' => $master?->tujuan_asesmen_lainnya,
            'konteks_lingkungan' => $master?->konteks_lingkungan ?: 'Tempat kerja simulasi',
            'konteks_peluang_bukti' => $master?->konteks_peluang_bukti ?: 'Tersedia',
            'hubungan_standar_bukti' => $master?->hubungan_standar_bukti ?: 'senang',
            'hubungan_standar_aktivitas' => $master?->hubungan_standar_aktivitas ?: 'senang',
            'hubungan_standar_pembelajaran' => $master?->hubungan_standar_pembelajaran ?: 'senang',
            'pelaksana_asesmen' => $master ? ($master->pelaksana_asesmen ?: ['Lembaga Sertifikasi']) : ['Lembaga Sertifikasi'],
            'konfirmasi_orang_relevan' => $master ? ($master->konfirmasi_orang_relevan ?: ['Manajer sertifikasi LSP']) : ['Manajer sertifikasi LSP'],
            'konfirmasi_pihak_relevan_tabel' => $master?->konfirmasi_pihak_relevan_tabel,
            'penyusun_validator_tabel' => $master?->penyusun_validator_tabel,
            'tanda_tangan_asesor' => $master?->tanda_tangan_asesor,
            'tanggal_ttd_asesor' => $master?->tanggal_ttd_asesor,
            'standar_industri' => $master ? ($master->standar_industri ?: ['Standar Kompetensi:']) : ['Standar Kompetensi:'],
            'rencana_unit_matriks' => $defaultMatriks,
            'karakteristik_kandidat_status' => $master?->karakteristik_kandidat_status ?: 'tidak_ada',
            'karakteristik_kandidat_teks' => $master?->karakteristik_kandidat_teks,
            'kebutuhan_kontekstualisasi_status' => $master?->kebutuhan_kontekstualisasi_status ?: 'tidak_ada',
            'kebutuhan_kontekstualisasi_teks' => $master?->kebutuhan_kontekstualisasi_teks,
            'saran_pelatihan_status' => $master?->saran_pelatihan_status ?: 'tidak_ada',
            'saran_pelatihan_teks' => $master?->saran_pelatihan_teks,
            'penyesuaian_perangkat_status' => $master?->penyesuaian_perangkat_status ?: 'tidak_ada',
            'penyesuaian_perangkat_teks' => $master?->penyesuaian_perangkat_teks,
            'peluang_terintegrasi_status' => $master?->peluang_terintegrasi_status ?: 'tidak_ada',
            'peluang_terintegrasi_teks' => $master?->peluang_terintegrasi_teks,
            'status_mapa' => ($isMasterValidated && $master?->status_mapa === 'selesai') ? 'selesai' : 'draft',
        ]);
    }

    /**
     * Dapatkan atau buat Master FR.MAPA.01 untuk Skema Sertifikasi
     */
    public function getOrCreateMasterMapa01(SkemaSertifikasi $skema, ?int $asesorId = null): Mapa01
    {
        $asesorId = $asesorId ?: auth()->id();
        $skema->loadMissing('unitKompetensi');

        $existing = Mapa01::where('skema_id', $skema->id)->whereNull('pendaftaran_id')->first();
        if ($existing) {
            return $existing;
        }

        return new Mapa01([
            'pendaftaran_id' => null,
            'skema_id' => $skema->id,
            'asesor_id' => $asesorId,
            'pendekatan_asesi' => [],
            'tujuan_asesmen' => null,
            'konteks_lingkungan' => null,
            'konteks_peluang_bukti' => null,
            'hubungan_standar_bukti' => null,
            'hubungan_standar_aktivitas' => null,
            'hubungan_standar_pembelajaran' => null,
            'pelaksana_asesmen' => [],
            'konfirmasi_orang_relevan' => [],
            'standar_industri' => [],
            'rencana_unit_matriks' => [],
            'karakteristik_kandidat_status' => 'tidak_ada',
            'kebutuhan_kontekstualisasi_status' => 'tidak_ada',
            'saran_pelatihan_status' => 'tidak_ada',
            'penyesuaian_perangkat_status' => 'tidak_ada',
            'peluang_terintegrasi_status' => 'tidak_ada',
            'status_mapa' => 'draft',
        ]);
    }

    /**
     * Inisialisasi otomatis / ambil dokumen FR.MAPA.02 secara idempotent
     */
    public function getOrCreateMapa02(PendaftaranAsesi $pendaftaran, ?int $asesorId = null): Mapa02
    {
        $asesorId = $asesorId ?: ($pendaftaran->asesor_id ?: ($pendaftaran->jadwal?->asesor_id ?: auth()->id()));

        $pendaftaran->loadMissing('skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja');

        if ($pendaftaran->mapa02) {
            return $pendaftaran->mapa02;
        }

        $existing = Mapa02::where('pendaftaran_id', $pendaftaran->id)->first();
        if ($existing) {
            return $existing;
        }

        // Cek master MAPA.02 skema untuk duplikasi matriks peta yang telah diselaraskan
        $master = Mapa02::where('skema_id', $pendaftaran->skema_id)->whereNull('pendaftaran_id')->first();
        $defaultMatrix = ($master && !empty($master->matriks_peta)) ? $master->matriks_peta : [];
        $defaultCatatan = $master?->catatan_asesor ?: null;

        return Mapa02::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $pendaftaran->skema_id,
            'asesor_id' => $asesorId,
            'matriks_peta' => $defaultMatrix,
            'catatan_asesor' => $defaultCatatan,
            'status_mapa' => 'draft',
        ]);
    }

    /**
     * Dapatkan atau buat Master FR.MAPA.02 untuk Skema Sertifikasi
     */
    public function getOrCreateMasterMapa02(SkemaSertifikasi $skema, ?int $asesorId = null): Mapa02
    {
        $asesorId = $asesorId ?: auth()->id();
        $skema->loadMissing('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja');

        $existing = Mapa02::where('skema_id', $skema->id)->whereNull('pendaftaran_id')->first();
        if ($existing) {
            return $existing;
        }

        return new Mapa02([
            'pendaftaran_id' => null,
            'skema_id' => $skema->id,
            'asesor_id' => $asesorId,
            'matriks_peta' => [],
            'catatan_asesor' => null,
            'status_mapa' => 'draft',
        ]);
    }

    /**
     * Sanitasi dan validasi matriks peta skema
     */
    public function sanitizeMatrixForScheme(SkemaSertifikasi $skema, array $inputMatrix): array
    {
        $skema->loadMissing('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja');
        $clean = [];

        foreach ($skema->unitKompetensi as $unit) {
            foreach ($unit->elemenKompetensi as $elemen) {
                $kukValid = $elemen->kriteriaUnjukKerja->pluck('id')->map(fn ($id) => (string) $id)->all();

                foreach (($inputMatrix[$unit->id][$elemen->id] ?? []) as $kukId => $nilai) {
                    if ($kukId !== 'elem_only' && !in_array((string) $kukId, $kukValid, true)) {
                        continue;
                    }

                    $cleanRow = [];
                    foreach (self::VALID_INSTRUMENT_KEYS as $k) {
                        $cleanRow[$k] = !empty($nilai[$k]) ? 1 : 0;
                    }

                    $clean[$unit->id][$elemen->id][$kukId] = $cleanRow;
                }
            }
        }

        return $clean;
    }

    /**
     * Sanitasi dan validasi matriks peta agar hanya berisi unit, elemen, KUK milik skema yang sah
     */
    public function sanitizeMatrix(PendaftaranAsesi $pendaftaran, array $inputMatrix): array
    {
        $skema = $pendaftaran->skema ?: SkemaSertifikasi::findOrFail($pendaftaran->skema_id);
        return $this->sanitizeMatrixForScheme($skema, $inputMatrix);
    }

    /**
     * Validasi kelayakan sebelum konfirmasi & pengesahan MAPA.02 skema
     */
    public function validateMatrixForConfirmationScheme(SkemaSertifikasi $skema, array $sanitizedMatrix): void
    {
        $skema->loadMissing('unitKompetensi');
        $errors = [];

        foreach ($skema->unitKompetensi as $unit) {
            $unitHasActiveInstrument = false;
            $unitData = $sanitizedMatrix[$unit->id] ?? [];

            foreach ($unitData as $elemData) {
                if (is_array($elemData)) {
                    foreach ($elemData as $item) {
                        if (is_array($item)) {
                            foreach ($item as $val) {
                                if (!empty($val)) {
                                    $unitHasActiveInstrument = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }

            if (!$unitHasActiveInstrument) {
                $errors['matriks_peta'][] = "Unit Kompetensi '{$unit->kode_unit} - {$unit->judul_unit}' belum memiliki minimal satu instrumen asesmen yang aktif.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validasi kelayakan sebelum konfirmasi & pengesahan MAPA.02
     * Setiap unit kompetensi wajib memiliki minimal 1 instrumen aktif
     */
    public function validateMatrixForConfirmation(PendaftaranAsesi $pendaftaran, array $sanitizedMatrix): void
    {
        $skema = $pendaftaran->skema ?: SkemaSertifikasi::findOrFail($pendaftaran->skema_id);
        $this->validateMatrixForConfirmationScheme($skema, $sanitizedMatrix);
    }

    /**
     * Simpan file tanda tangan digital dari raw base64 string
     */
    public function saveSignatureFile(?string $rawSignature, ?int $pendaftaranId = null, string $prefix = 'mapa02'): ?string
    {
        if (empty($rawSignature)) {
            return null;
        }

        if (Str::startsWith($rawSignature, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawSignature);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1] ?? '');

                if (!empty($imageBase64)) {
                    $idPart = $pendaftaranId ? (string) $pendaftaranId : 'master';
                    $filename = "sig_{$prefix}_{$idPart}_" . time() . '_' . Str::random(8) . '.' . $imageType;
                    Storage::disk('public')->put('signatures/' . $filename, $imageBase64);
                    return 'storage/signatures/' . $filename;
                }
            } catch (\Throwable $e) {
                // Fallback jika decode error
            }
        }

        return $rawSignature;
    }

    /**
     * Simpan / Sahkan Dokumen FR.MAPA.01
     */
    public function saveMapa01(PendaftaranAsesi $pendaftaran, array $data, ?string $rawSignature, bool $isConfirm, int $asesorId): Mapa01
    {
        return DB::transaction(function () use ($pendaftaran, $data, $rawSignature, $isConfirm, $asesorId) {
            $isAdminUser = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
            $adminTtds = Pengguna::whereIn('peran', ['admin', 'superadmin'])->pluck('tanda_tangan')->filter()->toArray();
            if ($rawSignature && in_array($rawSignature, $adminTtds, true)) {
                $rawSignature = null;
            }

            $ttdPath = $this->saveSignatureFile($rawSignature, $pendaftaran->id, 'mapa01');
            if (empty($ttdPath) && ($isConfirm || $isAdminUser)) {
                if (auth()->check() && auth()->user()->peran === 'asesor') {
                    $ttdPath = auth()->user()->tanda_tangan;
                } else {
                    $asesorUser = Pengguna::find($asesorId) ?: $pendaftaran->asesor;
                    $ttdPath = $asesorUser?->tanda_tangan ?: $pendaftaran->tanda_tangan_asesor;
                }
            }

            if (($isConfirm || $isAdminUser) && empty($ttdPath)) {
                $asesorUser = (auth()->check() && auth()->user()->peran === 'asesor') ? auth()->user() : (Pengguna::find($asesorId) ?: $pendaftaran->asesor);
                $nama = $asesorUser ? $asesorUser->nama_lengkap : 'Asesor Penguji';
                $svgSig = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="26" fill="%231e3a8a">' . urlencode($nama) . '</text></svg>';
                $ttdPath = $this->saveSignatureFile($svgSig, $pendaftaran->id, 'mapa01') ?: $svgSig;
            }

            $penyusunValidator = $data['penyusun_validator_tabel'] ?? [];

            // Pastikan baris 1 (Penyusun) adalah Asesor Penguji, BUKAN Admin LSP
            $asesorModel = Pengguna::find($asesorId) ?: ($pendaftaran->asesor ?: Pengguna::where('peran', 'asesor')->where('skema_id', $pendaftaran->skema_id)->first());
            $pNama = $penyusunValidator['penyusun_1']['nama'] ?? '';
            if (empty($pNama) || stripos($pNama, 'admin') !== false) {
                $penyusunValidator['penyusun_1']['nama'] = $asesorModel?->nama_lengkap ?? 'Asesor Penguji';
                $penyusunValidator['penyusun_1']['nomor_met'] = $asesorModel?->nomor_registrasi ?? 'MET.000.001222 2026';
            }
            if (!empty($penyusunValidator['penyusun_1']['ttd']) && in_array($penyusunValidator['penyusun_1']['ttd'], $adminTtds, true)) {
                $penyusunValidator['penyusun_1']['ttd'] = $ttdPath ?: ($asesorModel?->tanda_tangan ?? null);
            }
            if (empty($penyusunValidator['penyusun_1']['ttd']) && !empty($ttdPath)) {
                $penyusunValidator['penyusun_1']['ttd'] = $ttdPath;
            }
            if (empty($penyusunValidator['penyusun_1']['ttd_tanggal'])) {
                $penyusunValidator['penyusun_1']['ttd_tanggal'] = now()->format('d/m/Y');
            }

            // Jika Admin yang menyimpan/membuat: Otomatis lengkapi TTD asesor & langsung tervalidasi
            if ($isAdminUser) {
                if (empty($penyusunValidator['penyusun_1']['ttd'])) {
                    $penyusunValidator['penyusun_1']['ttd'] = $ttdPath;
                }

                $adminUser = auth()->user();
                $adminTtd = $adminUser->tanda_tangan;
                if (empty($adminTtd) && !empty($data['tanda_tangan_validator'])) {
                    $adminTtd = $data['tanda_tangan_validator'];
                }
                if (empty($adminTtd)) {
                    $adminName = $adminUser->nama_lengkap ?: 'Administrator LSP';
                    $adminSvg = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="24" fill="%23065f46">' . urlencode($adminName) . '</text></svg>';
                    $adminTtd = $this->saveSignatureFile($adminSvg, $pendaftaran->id, 'mapa01_val') ?: $adminSvg;
                } else {
                    $savedAdminTtd = $this->saveSignatureFile($adminTtd, $pendaftaran->id, 'mapa01_val');
                    if ($savedAdminTtd) {
                        $adminTtd = $savedAdminTtd;
                    }
                }

                $penyusunValidator['validator_1']['nama'] = !empty($penyusunValidator['validator_1']['nama']) ? $penyusunValidator['validator_1']['nama'] : ($adminUser->nama_lengkap ?: 'Administrator LSP SMKN 1 Gunungputri');
                $penyusunValidator['validator_1']['nomor_met'] = !empty($penyusunValidator['validator_1']['nomor_met']) ? $penyusunValidator['validator_1']['nomor_met'] : ($adminUser->nomor_registrasi ?: 'NIP/REG.ADM.LSP.001');
                $penyusunValidator['validator_1']['ttd'] = $adminTtd;
                $penyusunValidator['validator_1']['ttd_tanggal'] = now()->format('d/m/Y');
                $penyusunValidator['validator_1']['status_validasi'] = 'tervalidasi';

                $pendaftaran->update([
                    'tanda_tangan_admin' => $adminTtd,
                    'tanggal_ttd_admin' => now(),
                ]);
            } else {
                if (!empty($data['tanda_tangan_validator'])) {
                    $valTtd = $this->saveSignatureFile($data['tanda_tangan_validator'], $pendaftaran->id, 'mapa01_val');
                    if ($valTtd) {
                        $penyusunValidator['validator_1']['ttd'] = $valTtd;
                        $penyusunValidator['validator_1']['ttd_tanggal'] = now()->format('d/m/Y');
                        $penyusunValidator['validator_1']['status_validasi'] = 'tervalidasi';
                    }
                } elseif (!empty($penyusunValidator['validator_1']['ttd'])) {
                    $valTtd = $this->saveSignatureFile($penyusunValidator['validator_1']['ttd'], $pendaftaran->id, 'mapa01_val');
                    if ($valTtd) {
                        $penyusunValidator['validator_1']['ttd'] = $valTtd;
                    }
                } elseif (!empty($pendaftaran->tanda_tangan_admin)) {
                    $penyusunValidator['validator_1']['ttd'] = $pendaftaran->tanda_tangan_admin;
                    $penyusunValidator['validator_1']['ttd_tanggal'] = $pendaftaran->tanggal_ttd_admin ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_admin)->format('d/m/Y') : date('d/m/Y');
                    $penyusunValidator['validator_1']['nama'] = $penyusunValidator['validator_1']['nama'] ?? 'Admin LSP SMKN 1 Gunungputri';
                    $penyusunValidator['validator_1']['nomor_met'] = $penyusunValidator['validator_1']['nomor_met'] ?? 'NIP/REG.ADM.LSP.001';
                }
            }

            $normalizedKonfirmasi = $this->normalizeKonfirmasiData($data);

            $mapa01 = Mapa01::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'skema_id' => $pendaftaran->skema_id,
                    'asesor_id' => $asesorId,
                    'pendekatan_asesi' => $data['pendekatan_asesi'] ?? [],
                    'tujuan_asesmen' => $data['tujuan_asesmen'] ?? $pendaftaran->tujuan_asesmen,
                    'tujuan_asesmen_lainnya' => $data['tujuan_asesmen_lainnya'] ?? null,
                    'konteks_lingkungan' => $data['konteks_lingkungan'] ?? null,
                    'konteks_peluang_bukti' => $data['konteks_peluang_bukti'] ?? null,
                    'hubungan_standar_bukti' => $data['hubungan_standar_bukti'] ?? null,
                    'hubungan_standar_aktivitas' => $data['hubungan_standar_aktivitas'] ?? null,
                    'hubungan_standar_pembelajaran' => $data['hubungan_standar_pembelajaran'] ?? null,
                    'pelaksana_asesmen' => $data['pelaksana_asesmen'] ?? [],
                    'konfirmasi_orang_relevan' => $normalizedKonfirmasi['konfirmasi_orang_relevan'],
                    'standar_industri' => $data['standar_industri'] ?? [],
                    'rencana_unit_matriks' => $data['rencana_unit_matriks'] ?? [],
                    'karakteristik_kandidat_status' => $data['karakteristik_kandidat_status'] ?? 'tidak_ada',
                    'karakteristik_kandidat_teks' => $data['karakteristik_kandidat_teks'] ?? null,
                    'kebutuhan_kontekstualisasi_status' => $data['kebutuhan_kontekstualisasi_status'] ?? 'tidak_ada',
                    'kebutuhan_kontekstualisasi_teks' => $data['kebutuhan_kontekstualisasi_teks'] ?? null,
                    'saran_pelatihan_status' => $data['saran_pelatihan_status'] ?? 'tidak_ada',
                    'saran_pelatihan_teks' => $data['saran_pelatihan_teks'] ?? null,
                    'penyesuaian_perangkat_status' => $data['penyesuaian_perangkat_status'] ?? 'tidak_ada',
                    'penyesuaian_perangkat_teks' => $data['penyesuaian_perangkat_teks'] ?? null,
                    'peluang_terintegrasi_status' => $data['peluang_terintegrasi_status'] ?? 'tidak_ada',
                    'peluang_terintegrasi_teks' => $data['peluang_terintegrasi_teks'] ?? null,
                    'konfirmasi_pihak_relevan_tabel' => $normalizedKonfirmasi['konfirmasi_pihak_relevan_tabel'],
                    'penyusun_validator_tabel' => $penyusunValidator,
                    'tanda_tangan_asesor' => $ttdPath ?: ($pendaftaran->mapa01?->tanda_tangan_asesor ?? null),
                    'tanggal_ttd_asesor' => ($isConfirm || $isAdminUser) ? now() : ($pendaftaran->mapa01?->tanggal_ttd_asesor ?? null),
                    'status_mapa' => $isConfirm ? 'selesai' : 'draft',
                ]
            );

            return $mapa01;
        });
    }

    /**
     * Simpan / Sahkan Dokumen FR.MAPA.02
     */
    public function saveMapa02(PendaftaranAsesi $pendaftaran, array $rawMatrix, ?string $catatan, ?string $rawSignature, bool $isConfirm, int $asesorId): Mapa02
    {
        $sanitizedMatrix = $this->sanitizeMatrix($pendaftaran, $rawMatrix);

        if ($isConfirm) {
            $this->validateMatrixForConfirmation($pendaftaran, $sanitizedMatrix);
        }

        return DB::transaction(function () use ($pendaftaran, $sanitizedMatrix, $catatan, $rawSignature, $isConfirm, $asesorId) {
            $ttdPath = $this->saveSignatureFile($rawSignature, $pendaftaran->id, 'mapa02');
            if (empty($ttdPath) && $isConfirm) {
                $ttdPath = auth()->user()?->tanda_tangan ?: $pendaftaran->tanda_tangan_asesor;
            }

            if ($isConfirm && empty($ttdPath)) {
                $user = auth()->user() ?: $pendaftaran->asesor;
                $nama = $user ? $user->nama_lengkap : 'Asesor Penguji';
                $svgSig = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="26" fill="%231e3a8a">' . urlencode($nama) . '</text></svg>';
                $ttdPath = $this->saveSignatureFile($svgSig, $pendaftaran->id, 'mapa02') ?: $svgSig;
            }

            $mapa02 = Mapa02::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'skema_id' => $pendaftaran->skema_id,
                    'asesor_id' => $asesorId,
                    'matriks_peta' => $sanitizedMatrix,
                    'catatan_asesor' => $catatan,
                    'tanda_tangan_asesor' => $ttdPath ?: ($pendaftaran->mapa02?->tanda_tangan_asesor ?? null),
                    'tanggal_ttd_asesor' => $isConfirm ? now() : ($pendaftaran->mapa02?->tanggal_ttd_asesor ?? null),
                    'status_mapa' => $isConfirm ? 'selesai' : 'draft',
                ]
            );

            return $mapa02;
        });
    }

    /**
     * Simpan / Sahkan Dokumen Master FR.MAPA.01 untuk Skema Sertifikasi
     */
    public function saveMasterMapa01(SkemaSertifikasi $skema, array $data, ?string $rawSignature, bool $isConfirm, int $asesorId): Mapa01
    {
        return DB::transaction(function () use ($skema, $data, $rawSignature, $isConfirm, $asesorId) {
            $isAdminUser = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
            $adminTtds = Pengguna::whereIn('peran', ['admin', 'superadmin'])->pluck('tanda_tangan')->filter()->toArray();
            if ($rawSignature && in_array($rawSignature, $adminTtds, true)) {
                $rawSignature = null;
            }

            $ttdPath = $this->saveSignatureFile($rawSignature, null, 'mapa01_master_' . $skema->id);
            if (empty($ttdPath) && ($isConfirm || $isAdminUser)) {
                if (auth()->check() && auth()->user()->peran === 'asesor') {
                    $ttdPath = auth()->user()->tanda_tangan;
                } else {
                    $asesorUser = Pengguna::find($asesorId) ?: Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first();
                    $ttdPath = $asesorUser?->tanda_tangan;
                }
            }

            if (($isConfirm || $isAdminUser) && empty($ttdPath)) {
                $asesorUser = (auth()->check() && auth()->user()->peran === 'asesor') ? auth()->user() : (Pengguna::find($asesorId) ?: Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first());
                $nama = $asesorUser ? $asesorUser->nama_lengkap : 'Asesor Penguji';
                $svgSig = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="26" fill="%231e3a8a">' . urlencode($nama) . '</text></svg>';
                $ttdPath = $this->saveSignatureFile($svgSig, null, 'mapa01_master_' . $skema->id) ?: $svgSig;
            }

            $penyusunValidator = $data['penyusun_validator_tabel'] ?? [];
            $asesorModel = Pengguna::find($asesorId) ?: Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first();
            $pNama = $penyusunValidator['penyusun_1']['nama'] ?? '';
            if (empty($pNama) || stripos($pNama, 'admin') !== false) {
                $penyusunValidator['penyusun_1']['nama'] = $asesorModel?->nama_lengkap ?? 'Asesor Penguji';
                $penyusunValidator['penyusun_1']['nomor_met'] = $asesorModel?->nomor_registrasi ?? 'MET.000.001222 2026';
            }
            if (!empty($penyusunValidator['penyusun_1']['ttd']) && in_array($penyusunValidator['penyusun_1']['ttd'], $adminTtds, true)) {
                $penyusunValidator['penyusun_1']['ttd'] = $ttdPath ?: ($asesorModel?->tanda_tangan ?? null);
            }
            if (empty($penyusunValidator['penyusun_1']['ttd']) && !empty($ttdPath)) {
                $penyusunValidator['penyusun_1']['ttd'] = $ttdPath;
            }
            if (empty($penyusunValidator['penyusun_1']['ttd_tanggal'])) {
                $penyusunValidator['penyusun_1']['ttd_tanggal'] = now()->format('d/m/Y');
            }

            // Jika Admin yang menyimpan/membuat: Otomatis lengkapi TTD asesor & langsung tervalidasi
            if ($isAdminUser) {
                if (empty($penyusunValidator['penyusun_1']['ttd'])) {
                    $penyusunValidator['penyusun_1']['ttd'] = $ttdPath;
                }

                $adminUser = auth()->user();
                $adminTtd = $adminUser->tanda_tangan;
                if (empty($adminTtd) && !empty($data['tanda_tangan_validator'])) {
                    $adminTtd = $data['tanda_tangan_validator'];
                }
                if (empty($adminTtd)) {
                    $adminName = $adminUser->nama_lengkap ?: 'Administrator LSP';
                    $adminSvg = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="24" fill="%23065f46">' . urlencode($adminName) . '</text></svg>';
                    $adminTtd = $this->saveSignatureFile($adminSvg, null, 'mapa01_master_val_' . $skema->id) ?: $adminSvg;
                } else {
                    $savedAdminTtd = $this->saveSignatureFile($adminTtd, null, 'mapa01_master_val_' . $skema->id);
                    if ($savedAdminTtd) {
                        $adminTtd = $savedAdminTtd;
                    }
                }

                $penyusunValidator['validator_1']['nama'] = !empty($penyusunValidator['validator_1']['nama']) ? $penyusunValidator['validator_1']['nama'] : ($adminUser->nama_lengkap ?: 'Administrator LSP SMKN 1 Gunungputri');
                $penyusunValidator['validator_1']['nomor_met'] = !empty($penyusunValidator['validator_1']['nomor_met']) ? $penyusunValidator['validator_1']['nomor_met'] : ($adminUser->nomor_registrasi ?: 'NIP/REG.ADM.LSP.001');
                $penyusunValidator['validator_1']['ttd'] = $adminTtd;
                $penyusunValidator['validator_1']['ttd_tanggal'] = now()->format('d/m/Y');
                $penyusunValidator['validator_1']['status_validasi'] = 'tervalidasi';
            } else {
                if (!empty($data['tanda_tangan_validator'])) {
                    $valTtd = $this->saveSignatureFile($data['tanda_tangan_validator'], null, 'mapa01_master_val_' . $skema->id);
                    if ($valTtd) {
                        $penyusunValidator['validator_1']['ttd'] = $valTtd;
                        $penyusunValidator['validator_1']['ttd_tanggal'] = now()->format('d/m/Y');
                        $penyusunValidator['validator_1']['status_validasi'] = 'tervalidasi';
                    }
                } elseif (!empty($penyusunValidator['validator_1']['ttd'])) {
                    $valTtd = $this->saveSignatureFile($penyusunValidator['validator_1']['ttd'], null, 'mapa01_master_val_' . $skema->id);
                    if ($valTtd) {
                        $penyusunValidator['validator_1']['ttd'] = $valTtd;
                    }
                }
            }

            $normalizedKonfirmasi = $this->normalizeKonfirmasiData($data);

            $mapa01 = Mapa01::updateOrCreate(
                [
                    'skema_id' => $skema->id,
                    'pendaftaran_id' => null,
                ],
                [
                    'asesor_id' => $asesorId,
                    'pendekatan_asesi' => $data['pendekatan_asesi'] ?? [],
                    'tujuan_asesmen' => $data['tujuan_asesmen'] ?? null,
                    'tujuan_asesmen_lainnya' => $data['tujuan_asesmen_lainnya'] ?? null,
                    'konteks_lingkungan' => $data['konteks_lingkungan'] ?? null,
                    'konteks_peluang_bukti' => $data['konteks_peluang_bukti'] ?? null,
                    'hubungan_standar_bukti' => $data['hubungan_standar_bukti'] ?? null,
                    'hubungan_standar_aktivitas' => $data['hubungan_standar_aktivitas'] ?? null,
                    'hubungan_standar_pembelajaran' => $data['hubungan_standar_pembelajaran'] ?? null,
                    'pelaksana_asesmen' => $data['pelaksana_asesmen'] ?? [],
                    'konfirmasi_orang_relevan' => $normalizedKonfirmasi['konfirmasi_orang_relevan'],
                    'standar_industri' => $data['standar_industri'] ?? [],
                    'rencana_unit_matriks' => $data['rencana_unit_matriks'] ?? [],
                    'karakteristik_kandidat_status' => $data['karakteristik_kandidat_status'] ?? 'tidak_ada',
                    'karakteristik_kandidat_teks' => $data['karakteristik_kandidat_teks'] ?? null,
                    'kebutuhan_kontekstualisasi_status' => $data['kebutuhan_kontekstualisasi_status'] ?? 'tidak_ada',
                    'kebutuhan_kontekstualisasi_teks' => $data['kebutuhan_kontekstualisasi_teks'] ?? null,
                    'saran_pelatihan_status' => $data['saran_pelatihan_status'] ?? 'tidak_ada',
                    'saran_pelatihan_teks' => $data['saran_pelatihan_teks'] ?? null,
                    'penyesuaian_perangkat_status' => $data['penyesuaian_perangkat_status'] ?? 'tidak_ada',
                    'penyesuaian_perangkat_teks' => $data['penyesuaian_perangkat_teks'] ?? null,
                    'peluang_terintegrasi_status' => $data['peluang_terintegrasi_status'] ?? 'tidak_ada',
                    'peluang_terintegrasi_teks' => $data['peluang_terintegrasi_teks'] ?? null,
                    'konfirmasi_pihak_relevan_tabel' => $normalizedKonfirmasi['konfirmasi_pihak_relevan_tabel'],
                    'penyusun_validator_tabel' => $penyusunValidator,
                    'tanda_tangan_asesor' => $ttdPath,
                    'tanggal_ttd_asesor' => ($isConfirm || $isAdminUser) ? now() : null,
                    'status_mapa' => $isConfirm ? 'selesai' : 'draft',
                ]
            );

            if (($penyusunValidator['validator_1']['status_validasi'] ?? '') === 'tervalidasi' || !empty($penyusunValidator['validator_1']['ttd'])) {
                Mapa01::where('skema_id', $skema->id)
                    ->whereNotNull('pendaftaran_id')
                    ->update([
                        'penyusun_validator_tabel' => $penyusunValidator,
                        'tanda_tangan_asesor' => $ttdPath,
                        'tanggal_ttd_asesor' => $mapa01->tanggal_ttd_asesor,
                        'status_mapa' => 'selesai',
                    ]);

                $valTtd = $penyusunValidator['validator_1']['ttd'] ?? null;
                if ($valTtd) {
                    PendaftaranAsesi::where('skema_id', $skema->id)
                        ->whereNull('tanda_tangan_admin')
                        ->update([
                            'tanda_tangan_admin' => $valTtd,
                            'tanggal_ttd_admin' => now(),
                        ]);
                }
            }

            return $mapa01;
        });
    }

    /**
     * Simpan / Sahkan Dokumen Master FR.MAPA.02 untuk Skema Sertifikasi
     */
    public function saveMasterMapa02(SkemaSertifikasi $skema, array $rawMatrix, ?string $catatan, ?string $rawSignature, bool $isConfirm, int $asesorId): Mapa02
    {
        $sanitizedMatrix = $this->sanitizeMatrixForScheme($skema, $rawMatrix);

        if ($isConfirm) {
            $this->validateMatrixForConfirmationScheme($skema, $sanitizedMatrix);
        }

        return DB::transaction(function () use ($skema, $sanitizedMatrix, $catatan, $rawSignature, $isConfirm, $asesorId) {
            $adminTtds = Pengguna::whereIn('peran', ['admin', 'superadmin'])->pluck('tanda_tangan')->filter()->toArray();
            if ($rawSignature && in_array($rawSignature, $adminTtds, true)) {
                $rawSignature = null;
            }

            $ttdPath = $this->saveSignatureFile($rawSignature, null, 'mapa02_master_' . $skema->id);
            if (empty($ttdPath) && $isConfirm) {
                if (auth()->check() && auth()->user()->peran === 'asesor') {
                    $ttdPath = auth()->user()->tanda_tangan;
                } else {
                    $asesorUser = Pengguna::find($asesorId) ?: Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first();
                    $ttdPath = $asesorUser?->tanda_tangan;
                }
            }

            if ($isConfirm && empty($ttdPath)) {
                $asesorUser = (auth()->check() && auth()->user()->peran === 'asesor') ? auth()->user() : (Pengguna::find($asesorId) ?: Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first());
                $nama = $asesorUser ? $asesorUser->nama_lengkap : 'Asesor Penguji';
                $svgSig = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="26" fill="%231e3a8a">' . urlencode($nama) . '</text></svg>';
                $ttdPath = $this->saveSignatureFile($svgSig, null, 'mapa02_master_' . $skema->id) ?: $svgSig;
            }

            $mapa02 = Mapa02::updateOrCreate(
                [
                    'skema_id' => $skema->id,
                    'pendaftaran_id' => null,
                ],
                [
                    'asesor_id' => $asesorId,
                    'matriks_peta' => $sanitizedMatrix,
                    'catatan_asesor' => $catatan,
                    'tanda_tangan_asesor' => $ttdPath,
                    'tanggal_ttd_asesor' => $isConfirm ? now() : null,
                    'status_mapa' => $isConfirm ? 'selesai' : 'draft',
                ]
            );

            return $mapa02;
        });
    }

    /**
     * Periksa apakah perencanaan asesmen (MAPA.01 & MAPA.02) telah selesai dikonfirmasi
     */
    public function isMapaConfirmed(PendaftaranAsesi $pendaftaran): bool
    {
        $m01 = $pendaftaran->relationLoaded('mapa01') ? $pendaftaran->mapa01 : $pendaftaran->mapa01()->first();
        $m02 = $pendaftaran->relationLoaded('mapa02') ? $pendaftaran->mapa02 : $pendaftaran->mapa02()->first();

        return ($m01 && $m01->status_mapa === 'selesai') && ($m02 && $m02->status_mapa === 'selesai');
    }
}
