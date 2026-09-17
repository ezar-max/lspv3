<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAk03;
use App\Models\PendaftaranAsesi;
use App\Services\AssessmentDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssessmentAk03Controller extends Controller
{
    protected AssessmentDocumentService $documentService;

    public function __construct(AssessmentDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Otorisasi Asesi / Asesor berkas pendaftaran
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

        if ($user->peran === 'asesi') {
            if ($pendaftaran->asesi_id !== $user->id) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke berkas peserta ini.');
            }
            return;
        }

        if ($user->peran === 'asesor') {
            $isAssigned = ($pendaftaran->asesor_id === $user->id) ||
                          ($pendaftaran->jadwal && $pendaftaran->jadwal->asesor_id === $user->id);
            if (!$isAssigned) {
                abort(403, 'Akses Ditolak: Anda bukan asesor yang ditugaskan pada berkas ini.');
            }
            if ($writeOnly) {
                abort(403, 'Akses Ditolak: Formulir FR.AK.03 hanya boleh diisi oleh Asesi.');
            }
            return;
        }

        abort(403, 'Akses Ditolak');
    }

    /**
     * Tampilkan lembar FR.AK.03
     */
    public function show($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema',
            'asesor',
            'jadwal',
            'ak02',
            'ak03',
        ])->findOrFail($pendaftaranId);

        $this->authorizeAccess($pendaftaran, false);

        $ak03 = $this->documentService->getOrCreateAk03($pendaftaran);
        $isUnlocked = $pendaftaran->isAk03Unlocked();

        return view('dokumen-asesmen.ak03.form', compact('pendaftaran', 'ak03', 'isUnlocked'));
    }

    /**
     * Simpan umpan balik oleh Asesi
     */
    public function simpan(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $this->authorizeAccess($pendaftaran, true);

        $user = auth()->user();
        $ak03 = $this->documentService->getOrCreateAk03($pendaftaran);

        if (!$pendaftaran->isAk03Unlocked()) {
            return back()->with('error', 'Formulir belum terbuka. Menunggu penetapan keputusan asesmen pada FR.AK.02.');
        }

        if ($ak03->isSubmitted() && !in_array($user->peran, ['admin', 'superadmin'])) {
            return back()->with('error', 'Formulir umpan balik telah dikirimkan dan terkunci untuk pengubahan.');
        }

        $data = $request->validate([
            'jawaban' => 'required|array',
            'catatan_lainnya' => 'nullable|string',
            'submit' => 'nullable|boolean',
        ]);

        $isSubmit = (bool) ($request->input('submit', 0));
        $signature = $request->input('signature') ?: $user->tanda_tangan;

        if ($isSubmit && empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan asesi sebelum mengirimkan umpan balik.');
        }

        // Susun kembali format jawaban sesuai 10 butir standar BNSP
        $jawabanForm = [];
        foreach (AssessmentAk03::FEEDBACK_QUESTIONS as $no => $pertanyaan) {
            $val = $data['jawaban'][$no] ?? [];
            $pilihan = strtolower($val['jawaban'] ?? 'ya');
            $komentar = trim($val['catatan'] ?? '');

            if ($isSubmit && $pilihan === 'tidak' && empty($komentar)) {
                throw ValidationException::withMessages([
                    "jawaban.{$no}.catatan" => "Silakan berikan catatan / komentar untuk butir nomor {$no} karena memilih 'Tidak'.",
                ]);
            }

            $jawabanForm[$no] = [
                'no' => $no,
                'pertanyaan' => $pertanyaan,
                'jawaban' => $pilihan === 'tidak' ? 'tidak' : 'ya',
                'catatan' => $komentar,
            ];
        }

        $this->documentService->saveAk03(
            $ak03,
            $jawabanForm,
            $data['catatan_lainnya'] ?? '',
            $signature,
            $isSubmit,
            $user
        );

        $msg = $isSubmit
            ? 'Umpan balik telah dikirimkan dan lembar FR.AK.03 telah dikunci.'
            : 'Draf umpan balik berhasil disimpan.';

        return redirect()->route('dokumen-asesmen.ak03.show', $pendaftaranId)->with('sukses', $msg);
    }

    /**
     * Cetak lembar resmi A4 standar BNSP
     */
    public function cetak($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema',
            'asesor',
            'jadwal',
            'ak02',
            'ak03',
        ])->findOrFail($pendaftaranId);

        $this->authorizeAccess($pendaftaran, false);
        $ak03 = $this->documentService->getOrCreateAk03($pendaftaran);

        return view('dokumen-asesmen.ak03.cetak', compact('pendaftaran', 'ak03'));
    }
}
