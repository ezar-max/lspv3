<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAk02;
use App\Models\PendaftaranAsesi;
use App\Services\AssessmentDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssessmentAk02Controller extends Controller
{
    protected AssessmentDocumentService $documentService;

    public function __construct(AssessmentDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Otorisasi Asesor / Asesi berkas pendaftaran
     */
    protected function authorizeAccess(PendaftaranAsesi $pendaftaran, bool $writeOnly = false): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (in_array($user->peran, ['admin', 'superadmin'])) {
            return;
        }

        if ($user->peran === 'asesor') {
            $isAssigned = ($pendaftaran->asesor_id === $user->id) ||
                          ($pendaftaran->jadwal && $pendaftaran->jadwal->asesor_id === $user->id);
            if (!$isAssigned) {
                abort(403, 'Akses Ditolak: Anda bukan asesor yang ditugaskan pada berkas asesmen ini.');
            }
            return;
        }

        if ($user->peran === 'asesi') {
            if ($pendaftaran->asesi_id !== $user->id) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke berkas peserta ini.');
            }
            if ($writeOnly) {
                abort(403, 'Akses Ditolak: Asesi hanya memiliki hak membaca dan menandatangani hasil asesmen.');
            }
            return;
        }

        abort(403, 'Akses Ditolak');
    }

    /**
     * Tampilkan dan kelola formulir FR.AK.02
     */
    public function show($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi',
            'asesor',
            'jadwal',
            'ak02',
            'ak03',
        ])->findOrFail($pendaftaranId);

        $this->authorizeAccess($pendaftaran, false);

        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);

        return view('dokumen-asesmen.ak02.edit', compact('pendaftaran', 'ak02'));
    }

    /**
     * Autosave asinkron (AJAX)
     */
    public function autosave(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $this->authorizeAccess($pendaftaran, true);

        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);
        $user = auth()->user();

        $data = $request->only([
            'matriks_bukti',
            'rekomendasi_unit',
            'tindak_lanjut',
            'komentar_asesor',
        ]);

        $ak02 = $this->documentService->saveDraftAk02($ak02, $data, $user);

        return response()->json([
            'success' => true,
            'message' => 'Tersimpan otomatis',
            'saved_at' => now()->format('H:i:s'),
            'total_unit' => $ak02->total_unit,
            'total_k' => $ak02->total_k,
            'total_bk' => $ak02->total_bk,
            'keputusan_final' => $ak02->keputusan_final,
        ]);
    }

    /**
     * Simpan draf formulir FR.AK.02
     */
    public function simpan(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $this->authorizeAccess($pendaftaran, true);

        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);
        $user = auth()->user();

        $data = $request->validate([
            'matriks_bukti' => 'nullable|array',
            'rekomendasi_unit' => 'nullable|array',
            'tindak_lanjut' => 'nullable|string',
            'komentar_asesor' => 'nullable|string',
        ]);

        // Jika terdapat unit BK, validasi tindak lanjut wajib diisi
        $rekomendasi = (array) ($data['rekomendasi_unit'] ?? []);
        $hasBk = false;
        foreach ($rekomendasi as $u) {
            $hasil = is_array($u) ? ($u['hasil'] ?? null) : $u;
            if (strtoupper((string) $hasil) === 'BK') {
                $hasBk = true;
                break;
            }
        }

        if ($hasBk && empty(trim($data['tindak_lanjut'] ?? ''))) {
            throw ValidationException::withMessages([
                'tindak_lanjut' => 'Tindak lanjut yang dibutuhkan wajib diisi jika terdapat unit yang Belum Kompeten (BK).',
            ]);
        }

        $this->documentService->saveDraftAk02($ak02, $data, $user);

        return redirect()->route('dokumen-asesmen.ak02.edit', $pendaftaranId)
            ->with('sukses', 'Draf FR.AK.02 Rekaman Asesmen Kompetensi berhasil disimpan!');
    }

    /**
     * Tanda tangan keputusan oleh Asesor
     */
    public function signAsesor(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $this->authorizeAccess($pendaftaran, true);

        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);
        $user = auth()->user();

        $signature = $request->input('signature') ?: $user->tanda_tangan;
        if (empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan atau gunakan tanda tangan tersimpan pada profil Anda.');
        }

        $this->documentService->signAsesorAk02($ak02, $signature, $user);

        return redirect()->route('dokumen-asesmen.ak02.edit', $pendaftaranId)
            ->with('sukses', 'Keputusan asesmen FR.AK.02 berhasil disahkan dan ditandatangani!');
    }

    /**
     * Tanda tangan konfirmasi oleh Asesi
     */
    public function signAsesi(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $user = auth()->user();
        if ($user->id !== $pendaftaran->asesi_id && !in_array($user->peran, ['admin', 'superadmin'])) {
            abort(403, 'Hanya peserta yang bersangkutan yang dapat menandatangani lembar konfirmasi ini.');
        }

        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);

        $signature = $request->input('signature') ?: $user->tanda_tangan;
        if (empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan digital Anda terlebih dahulu.');
        }

        $this->documentService->signAsesiAk02($ak02, $signature, $user);

        return redirect()->route('dokumen-asesmen.ak02.edit', $pendaftaranId)
            ->with('sukses', 'FR.AK.02 berhasil ditandatangani dan difinalisasi.');
    }

    /**
     * Buka kembali dokumen (Reopen) khusus Admin / Superadmin
     */
    public function reopen(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);
        $user = auth()->user();

        $notes = $request->validate([
            'alasan_revisi' => 'required|string|min:5',
        ])['alasan_revisi'];

        $this->documentService->reopenAk02($ak02, $notes, $user);

        return redirect()->route('dokumen-asesmen.ak02.edit', $pendaftaranId)
            ->with('sukses', "Dokumen FR.AK.02 berhasil dibuka kembali (Revisi versi {$ak02->version}).");
    }

    /**
     * Tampilan cetak resmi A4 standar BNSP
     */
    public function cetak($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi',
            'asesor',
            'jadwal',
            'ak02',
        ])->findOrFail($pendaftaranId);

        $this->authorizeAccess($pendaftaran, false);
        $ak02 = $this->documentService->getOrCreateAk02($pendaftaran);

        return view('dokumen-asesmen.ak02.cetak', compact('pendaftaran', 'ak02'));
    }
}
