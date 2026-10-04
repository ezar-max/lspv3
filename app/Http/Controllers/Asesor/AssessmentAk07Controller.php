<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
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
        $pendaftaran = PendaftaranAsesi::with('jadwal')->findOrFail($pendaftaranId);
        $this->authorizeAsesor($pendaftaran);

        return redirect()->route('asesor.skema.ak-07', $pendaftaran->skema_id)
            ->with('info', 'FR.AK.07 diisi satu kali dan berlaku untuk seluruh asesi pada skema ini.');
    }

    /**
     * Simpan / Perbarui data checklist dan kesepakatan FR.AK.07
     */
    public function update(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with('jadwal')->findOrFail($pendaftaranId);
        $this->authorizeAsesor($pendaftaran);

        return redirect()->route('asesor.skema.ak-07', $pendaftaran->skema_id)
            ->with('info', 'FR.AK.07 disimpan bersama untuk seluruh asesi pada skema ini.');
    }

    /**
     * Tanda tangan digital Asesi untuk FR.AK.07
     */
    public function signAsesi(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with('ak07Adjustment')->findOrFail($pendaftaranId);
        $this->authorizeAsesi($pendaftaran);

        if (!$pendaftaran->syncFromMasterAk07IfAvailable()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 4])
                ->with('error', 'FR.AK.07 belum tersedia. Asesor harus mengisi dan menandatangani formulir untuk skema ini terlebih dahulu.');
        }

        $validated = $request->validate([
            'tanda_tangan_asesi' => ['required', 'string', 'max:2000000', 'regex:/^data:image\\/(png|jpeg);base64,/'],
        ]);

        $rawSignature = $validated['tanda_tangan_asesi'];
        $signaturePayload = substr($rawSignature, strpos($rawSignature, ',') + 1);
        if (base64_decode($signaturePayload, true) === false) {
            return back()->withErrors(['tanda_tangan_asesi' => 'Data tanda tangan Asesi tidak valid.']);
        }

        $signaturePath = app(\App\Services\MapaWorkflowService::class)->saveSignatureFile(
            $rawSignature,
            $pendaftaran->id,
            'ak07_asesi'
        );
        if (!$signaturePath || str_starts_with($signaturePath, 'data:image/')) {
            return back()->withErrors(['tanda_tangan_asesi' => 'Tanda tangan Asesi gagal disimpan.']);
        }

        $pendaftaran->refresh();
        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07 || empty($ak07->asesor_signature)) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 4])
                ->with('error', 'FR.AK.07 belum ditandatangani Asesor.');
        }

        $ak07->asesi_signature = $signaturePath;
        $ak07->asesi_signed_at = now('Asia/Jakarta');
        $ak07->status = 'confirmed';
        $ak07->save();

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Asesi menandatangani dan mengonfirmasi FR.AK.07 #' . $pendaftaran->nomor_pendaftaran,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 5])
            ->with('sukses', 'FR.AK.07 berhasil ditandatangani dan disetujui. Silakan lanjutkan ke pelaksanaan ujian/tes online.');
    }

    public function confirmAsesi(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with('ak07Adjustment')->findOrFail($pendaftaranId);
        $this->authorizeAsesi($pendaftaran);

        if (!$pendaftaran->syncFromMasterAk07IfAvailable()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 4])
                ->with('error', 'FR.AK.07 belum tersedia. Asesor harus mengisi dan menandatangani formulir untuk skema ini terlebih dahulu.');
        }

        $pendaftaran->refresh();
        $ak07 = $pendaftaran->ak07Adjustment;
        if (!$ak07 || empty($ak07->asesor_signature)) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 4])
                ->with('error', 'FR.AK.07 belum ditandatangani Asesor.');
        }

        if (empty($ak07->asesi_signature) || empty($ak07->asesi_signed_at)) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 4])
                ->with('error', 'Silakan bubuhkan tanda tangan pada FR.AK.07 sebelum melanjutkan.');
        }

        $ak07->status = 'confirmed';
        $ak07->save();

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Asesi mengonfirmasi Kesepakatan Penyesuaian FR.AK.07 #' . $pendaftaran->nomor_pendaftaran,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 5])
            ->with('sukses', 'Kesepakatan FR.AK.07 berhasil dikonfirmasi. Silakan lanjutkan ke pelaksanaan ujian/tes online.');
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
        $hasSignedMaster = $pendaftaran->syncFromMasterAk07IfAvailable();
        $pendaftaran->refresh();

        if (!$hasSignedMaster) {
            return redirect()->route('asesi.tahapan', [
                'pendaftaran_id' => $pendaftaran->id,
                'step' => 4,
            ])->with('info', 'FR.AK.07 belum diisi dan ditandatangani Asesor untuk skema ini.');
        }

        $ak07 = $pendaftaran->ak07Adjustment;
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
