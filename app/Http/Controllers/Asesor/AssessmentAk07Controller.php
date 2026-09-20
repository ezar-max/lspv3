<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAssessmentAk07Request;
use App\Models\AssessmentAk07Adjustment;
use App\Models\LogAktivitas;
use App\Models\PendaftaranAsesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssessmentAk07Controller extends Controller
{
    /**
     * Otorisasi Asesor penilai berkas pendaftaran (Mencegah IDOR)
     */
    protected function authorizeAsesor(PendaftaranAsesi $pendaftaran): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (in_array($user->peran, ['admin', 'superadmin'])) {
            return;
        }

        $asesorId = $user->id;
        $isAssigned = ($pendaftaran->asesor_id === $asesorId) || 
                      ($pendaftaran->jadwal && $pendaftaran->jadwal->asesor_id === $asesorId);

        if (!$isAssigned) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang pada berkas asesmen peserta ini.');
        }
    }

    /**
     * Otorisasi Asesi pemilik berkas
     */
    protected function authorizeAsesi(PendaftaranAsesi $pendaftaran): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (in_array($user->peran, ['admin', 'superadmin'])) {
            return;
        }

        if ($pendaftaran->asesi_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke berkas pendaftaran ini.');
        }
    }

    /**
     * Tampilkan formulir FR.AK.07 untuk ditinjau & diisi Asesor
     */
    public function edit($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'mapa01',
            'ak07Adjustment',
        ])->findOrFail($pendaftaranId);

        $this->authorizeAsesor($pendaftaran);

        // Pengalihan sistem terpusat: Formulir FR.AK.07 kini dikelola 1 form master per-skema untuk seluruh asesi
        if ($pendaftaran->skema_id) {
            return redirect()->route('asesor.skema.ak-07', $pendaftaran->skema_id)
                ->with('info', 'Formulir FR.AK.07 kini dikelola terpusat per-skema (Master FR.AK.07) untuk seluruh asesi.');
        }

        // Sinkronisasi otomatis dari Master FR.AK.07 Skema jika tersedia
        $pendaftaran->syncFromMasterAk07IfAvailable();
        $pendaftaran->refresh();

        // Ambil atau buat instance model penyesuaian AK.07
        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07) {
            $defaultPotensi = $this->resolveDefaultPotensi($pendaftaran);
            
            $ak07 = new AssessmentAk07Adjustment([
                'assessment_registration_id' => $pendaftaran->id,
                'potensi_asesi' => null,
                'fase_penggunaan' => null,
                'items_checklist' => [],
                'status' => 'draft',
            ]);
        }

        $criteriaDefinitions = AssessmentAk07Adjustment::CRITERIA_DEFINITIONS;
        $potensiDefinitions = AssessmentAk07Adjustment::POTENSI_DEFINITIONS;

        return view('asesor.ak07.edit', compact(
            'pendaftaran',
            'ak07',
            'criteriaDefinitions',
            'potensiDefinitions'
        ));
    }

    /**
     * Simpan / Perbarui data checklist dan kesepakatan FR.AK.07
     */
    public function update(UpdateAssessmentAk07Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with(['ak07Adjustment', 'jadwal'])->findOrFail($pendaftaranId);
        $this->authorizeAsesor($pendaftaran);

        $ak07 = AssessmentAk07Adjustment::firstOrNew([
            'assessment_registration_id' => $pendaftaran->id,
        ]);

        $isPreviouslyConfirmed = ($ak07->status === 'confirmed');
        $isConfirmAction = in_array($request->input('aksi'), ['confirm', 'konfirmasi']);

        // Format items checklist dari form submission
        $rawChecklist = $request->input('items_checklist', []);
        $formattedChecklist = [];

        foreach (AssessmentAk07Adjustment::CRITERIA_DEFINITIONS as $catId => $cat) {
            $catInput = $rawChecklist[$catId] ?? [];
            $perluVal = $catInput['perlu'] ?? null;
            $isPerlu = null;

            if ($perluVal !== null && $perluVal !== '') {
                $isPerlu = filter_var($perluVal, FILTER_VALIDATE_BOOLEAN) || 
                           ($perluVal === '1') || 
                           ($perluVal === 1) ||
                           ($perluVal === 'ya');
            }

            $opsiDipilih = is_array($catInput['opsi'] ?? null) ? array_values($catInput['opsi']) : [];
            $keterangan = trim($catInput['keterangan'] ?? '');

            $formattedChecklist[$catId] = [
                'perlu_penyesuaian' => $isPerlu,
                'opsi_dipilih' => $isPerlu === true ? $opsiDipilih : [],
                'keterangan' => $isPerlu === true ? $keterangan : '',
            ];
        }

        // Simpan atribut utama
        $ak07->potensi_asesi = $request->filled('potensi_asesi') ? (int) $request->input('potensi_asesi') : null;
        $ak07->fase_penggunaan = $request->input('fase_penggunaan') ?: null;
        $ak07->items_checklist = $formattedChecklist;
        $ak07->acuan_pembanding_disepakati = $request->input('acuan_pembanding_disepakati');
        $ak07->metode_disepakati = $request->input('metode_disepakati');
        $ak07->instrumen_disepakati = $request->input('instrumen_disepakati');
        $ak07->catatan_asesor = $request->input('catatan_asesor');

        // Tanda tangan Asesor
        $rawSignature = $request->input('tanda_tangan_asesor');
        if (!empty($rawSignature)) {
            $savedSignaturePath = $this->processSignatureImage($rawSignature, 'asesor', $pendaftaran->id);
            if ($savedSignaturePath) {
                $ak07->asesor_signature = $savedSignaturePath;
                $ak07->asesor_signed_at = now();
            }
        }

        // STATE MACHINE & RE-CONFIRMATION RULE:
        // Jika sebelumnya sudah confirmed lalu asesor mengubah data (bukan konfirmasi ulang eksplisit),
        // reset status kembali ke draft & batalkan pengesahan untuk penandatanganan ulang.
        if ($isPreviouslyConfirmed && !$isConfirmAction) {
            $ak07->status = 'draft';
            $ak07->asesor_signature = null;
            $ak07->asesor_signed_at = null;
            $ak07->asesi_signature = null;
            $ak07->asesi_signed_at = null;
        } elseif ($isConfirmAction) {
            if (empty($ak07->asesor_signature)) {
                $profileTtd = auth()->user()->tanda_tangan;
                if (!empty($profileTtd)) {
                    $ak07->asesor_signature = $profileTtd;
                    $ak07->asesor_signed_at = now();
                }
            }
            $ak07->status = 'confirmed';
        }

        $ak07->save();

        // Jika asesor memilih untuk menerapkan konfigurasi ini ke seluruh asesi skema
        if ($request->boolean('terapkan_semua_asesi')) {
            $master = \App\Models\MasterAk07::updateOrCreate(
                ['skema_id' => $pendaftaran->skema_id],
                [
                    'asesor_id' => auth()->id(),
                    'potensi_asesi' => $ak07->potensi_asesi,
                    'fase_penggunaan' => $ak07->fase_penggunaan,
                    'items_checklist' => $ak07->items_checklist,
                    'acuan_pembanding_disepakati' => $ak07->acuan_pembanding_disepakati,
                    'metode_disepakati' => $ak07->metode_disepakati,
                    'instrumen_disepakati' => $ak07->instrumen_disepakati,
                    'catatan_asesor' => $ak07->catatan_asesor,
                    'tanda_tangan_asesor' => $ak07->asesor_signature,
                    'tanggal_ttd_asesor' => $ak07->asesor_signed_at,
                    'status' => $ak07->status,
                ]
            );
            $master->sinkronkanKePeserta();
        }

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Memperbarui Formulir FR.AK.07 Penyesuaian yang Wajar untuk Pendaftaran #' . $pendaftaran->nomor_pendaftaran . ' (' . strtoupper($ak07->status) . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $message = $ak07->isConfirmed()
            ? 'Dokumen FR.AK.07 berhasil disahkan (CONFIRMED).'
            : 'Draft FR.AK.07 berhasil disimpan.';

        return redirect()->route('asesor.pendaftaran.ak07.edit', $pendaftaran->id)
            ->with('sukses', $message);
    }

    /**
     * Tanda tangan digital Asesor untuk FR.AK.07
     */
    public function signAsesor(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with('ak07Adjustment')->findOrFail($pendaftaranId);
        $this->authorizeAsesor($pendaftaran);

        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07) {
            return back()->with('error', 'Formulir FR.AK.07 belum diisi.');
        }

        $rawSignature = $request->input('tanda_tangan_asesor');
        $signaturePath = $this->processSignatureImage($rawSignature, 'asesor', $pendaftaran->id);

        if (!$signaturePath && !empty(auth()->user()->tanda_tangan)) {
            $signaturePath = auth()->user()->tanda_tangan;
        }

        if (!$signaturePath) {
            return back()->with('error', 'Tanda tangan asesor tidak valid atau belum tersedia.');
        }

        $ak07->asesor_signature = $signaturePath;
        $ak07->asesor_signed_at = now();

        if (!empty($ak07->asesi_signature)) {
            $ak07->status = 'confirmed';
        }

        $ak07->save();

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Menandatangani Formulir FR.AK.07 untuk Peserta #' . $pendaftaran->nomor_pendaftaran,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('sukses', 'Tanda tangan Asesor pada FR.AK.07 berhasil disimpan.');
    }

    /**
     * Tanda tangan digital Asesi untuk FR.AK.07
     */
    public function signAsesi(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with('ak07Adjustment')->findOrFail($pendaftaranId);
        $this->authorizeAsesi($pendaftaran);

        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07) {
            $defaultChecklist = AssessmentAk07Adjustment::defaultChecklistItems();
            $asesorSig = $pendaftaran->tanda_tangan_asesor_ak01 ?? $pendaftaran->asesor?->tanda_tangan;
            $namaSkema = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';
            $defaultPotensi = $this->resolveDefaultPotensi($pendaftaran);

            $ak07 = AssessmentAk07Adjustment::create([
                'assessment_registration_id' => $pendaftaran->id,
                'potensi_asesi' => $defaultPotensi,
                'fase_penggunaan' => 'saat_pra_asesmen',
                'items_checklist' => $defaultChecklist,
                'status' => 'draft',
                'acuan_pembanding_disepakati' => "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$namaSkema}",
                'metode_disepakati' => 'Observasi Demonstrasi & Wawancara',
                'instrumen_disepakati' => 'FR.IA.01, FR.IA.03',
                'catatan_asesor' => 'Seluruh proses asesmen disepakati dapat dilaksanakan dengan penyesuaian yang wajar sesuai kesepakatan bersama.',
                'asesor_signature' => $asesorSig,
                'asesor_signed_at' => $asesorSig ? now() : null,
            ]);
        }

        $rawSignature = $request->input('tanda_tangan_asesi');
        $signaturePath = $this->processSignatureImage($rawSignature, 'asesi', $pendaftaran->id);

        if (!$signaturePath && !empty(auth()->user()->tanda_tangan)) {
            $signaturePath = auth()->user()->tanda_tangan;
        }

        if (!$signaturePath) {
            return back()->with('error', 'Tanda tangan asesi tidak valid.');
        }

        $ak07->asesi_signature = $signaturePath;
        $ak07->asesi_signed_at = now();

        if (!empty($ak07->asesor_signature)) {
            $ak07->status = 'confirmed';
        }

        $ak07->save();

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Asesi menandatangani Kesepakatan Penyesuaian FR.AK.07 #' . $pendaftaran->nomor_pendaftaran,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 5])
            ->with('sukses', 'Tanda tangan Anda pada Kesepakatan FR.AK.07 berhasil disimpan. Silakan lanjutkan ke pelaksanaan ujian/tes online.');
    }

    /**
     * Tampilan detail & penandatanganan FR.AK.07 untuk Asesi
     */
    public function asesiDetail(Request $request, $pendaftaranId = null)
    {
        $user = auth()->user();

        if ($pendaftaranId) {
            $pendaftaran = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'skema.unitKompetensi',
                'asesor',
                'jadwal',
                'ak07Adjustment'
            ])->findOrFail($pendaftaranId);
        } else {
            $pendaftaran = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'skema.unitKompetensi',
                'asesor',
                'jadwal',
                'ak07Adjustment'
            ])->where('asesi_id', $user->id)
              ->whereNotIn('status_pendaftaran', ['ditolak'])
              ->latest()
              ->firstOrFail();
        }

        $this->authorizeAsesi($pendaftaran);

        // Sinkronkan data dari Master FR.AK.07 jika ada
        $pendaftaran->syncFromMasterAk07IfAvailable();
        $pendaftaran->refresh();

        $ak07 = $pendaftaran->ak07Adjustment;
        $defaultChecklist = AssessmentAk07Adjustment::defaultChecklistItems();
        $asesorSig = $pendaftaran->tanda_tangan_asesor_ak01 ?? $pendaftaran->asesor?->tanda_tangan;
        $namaSkema = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';

        if (!$ak07) {
            $defaultPotensi = $this->resolveDefaultPotensi($pendaftaran);
            $ak07 = AssessmentAk07Adjustment::create([
                'assessment_registration_id' => $pendaftaran->id,
                'potensi_asesi' => $defaultPotensi,
                'fase_penggunaan' => 'saat_pra_asesmen',
                'items_checklist' => $defaultChecklist,
                'status' => 'draft',
                'acuan_pembanding_disepakati' => "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$namaSkema}",
                'metode_disepakati' => 'Observasi Demonstrasi & Wawancara',
                'instrumen_disepakati' => 'FR.IA.01, FR.IA.03',
                'catatan_asesor' => 'Seluruh proses asesmen disepakati dapat dilaksanakan dengan penyesuaian yang wajar sesuai kesepakatan bersama.',
                'asesor_signature' => $asesorSig,
                'asesor_signed_at' => $asesorSig ? now() : null,
            ]);
        } elseif (empty($ak07->items_checklist)) {
            $ak07->update([
                'items_checklist' => $defaultChecklist,
                'acuan_pembanding_disepakati' => $ak07->acuan_pembanding_disepakati ?? "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$namaSkema}",
                'metode_disepakati' => $ak07->metode_disepakati ?? 'Observasi Demonstrasi & Wawancara',
                'instrumen_disepakati' => $ak07->instrumen_disepakati ?? 'FR.IA.01, FR.IA.03',
                'catatan_asesor' => $ak07->catatan_asesor ?? 'Seluruh proses asesmen disepakati dapat dilaksanakan dengan penyesuaian yang wajar sesuai kesepakatan bersama.',
                'asesor_signature' => $ak07->asesor_signature ?? $asesorSig,
                'asesor_signed_at' => $ak07->asesor_signed_at ?? ($asesorSig ? now() : null),
            ]);
        }

        $criteriaDefinitions = AssessmentAk07Adjustment::CRITERIA_DEFINITIONS;
        $potensiDefinitions = AssessmentAk07Adjustment::POTENSI_DEFINITIONS;

        return view('asesi.ak07-detail', compact(
            'pendaftaran',
            'ak07',
            'criteriaDefinitions',
            'potensiDefinitions'
        ));
    }

    /**
     * Format Cetak Dokumen A4 / PDF Standar Blangko BNSP FR.AK.07
     */
    public function cetak($pendaftaranId)
    {
        $user = auth()->user();
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'ak07Adjustment',
        ])->findOrFail($pendaftaranId);

        // Otorisasi: asesor terkait, asesi pemilik, atau admin
        if ($user->peran === 'asesor') {
            $this->authorizeAsesor($pendaftaran);
        } elseif ($user->peran === 'asesi') {
            $this->authorizeAsesi($pendaftaran);
        } elseif (!in_array($user->peran, ['admin', 'superadmin'])) {
            abort(403);
        }

        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07) {
            $pendaftaran->syncFromMasterAk07IfAvailable();
            $pendaftaran->refresh();
            $ak07 = $pendaftaran->ak07Adjustment;
        }

        if (!$ak07) {
            abort(404, 'Formulir FR.AK.07 belum dibuat.');
        }

        $criteriaDefinitions = AssessmentAk07Adjustment::CRITERIA_DEFINITIONS;
        $potensiDefinitions = AssessmentAk07Adjustment::POTENSI_DEFINITIONS;

        return view('asesor.ak07.cetak', compact(
            'pendaftaran',
            'ak07',
            'criteriaDefinitions',
            'potensiDefinitions'
        ));
    }

    /**
     * Helper pemrosesan berkas / base64 tanda tangan digital
     */
    protected function processSignatureImage(?string $rawSignature, string $role, int $pendaftaranId): ?string
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
                    $filename = 'sig_' . $role . '_ak07_' . $pendaftaranId . '_' . Str::random(8) . '.' . $imageType;
                    Storage::disk('public')->put('signatures/' . $filename, $imageBase64);
                    return 'storage/signatures/' . $filename;
                }
            } catch (\Exception $e) {
                // Fallback bila decode gagal
            }
        } elseif (Str::startsWith($rawSignature, 'signatures/') || Str::startsWith($rawSignature, 'storage/')) {
            return $rawSignature;
        }

        return null;
    }

    /**
     * Resolusi nilai potensi awal asesi dari FR.MAPA.01
     */
    protected function resolveDefaultPotensi(PendaftaranAsesi $pendaftaran): int
    {
        $mapa01 = $pendaftaran->mapa01;
        if (!$mapa01 || empty($mapa01->pendekatan_asesi)) {
            return 1;
        }

        $pendekatan = (array) $mapa01->pendekatan_asesi;
        $firstVal = strtolower($pendekatan[0] ?? '');

        if (str_contains($firstVal, 'mampu telusur')) {
            return str_contains($firstVal, 'industri') ? 3 : 1;
        } elseif (str_contains($firstVal, 'belum berbasis kompetensi')) {
            return str_contains($firstVal, 'industri') ? 4 : 2;
        } elseif (str_contains($firstVal, 'mandiri') || str_contains($firstVal, 'otodidak')) {
            return 5;
        }

        return 1;
    }
}
