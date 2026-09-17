<?php

namespace App\Http\Controllers;

use App\Models\AssessmentVa;
use App\Models\SkemaSertifikasi;
use App\Services\AssessmentDocumentService;
use Illuminate\Http\Request;

class AssessmentVaController extends Controller
{
    protected AssessmentDocumentService $documentService;

    public function __construct(AssessmentDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Daftar kegiatan validasi asesmen
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $validations = AssessmentVa::with(['skema', 'leadAsesor'])
            ->when($user->peran === 'asesor', fn ($q) => $q->where('lead_asesor_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $skemas = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema')->get();

        return view('dokumen-asesmen.va.index', compact('validations', 'skemas'));
    }

    /**
     * Buat kegiatan validasi baru
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'skema_id' => 'required|exists:skema_sertifikasi,id',
        ]);

        $skemaId = (int) $request->input('skema_id');
        $va = $this->documentService->createOrGetVa($skemaId, $user);

        return redirect()->route('dokumen-asesmen.va.wizard', $va->id);
    }

    /**
     * Wizard 7 Langkah FR.VA
     */
    public function wizard($id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $va = AssessmentVa::with(['skema', 'leadAsesor'])->findOrFail($id);

        if ($user->peran === 'asesor' && $va->lead_asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda bukan tim lead validasi pada dokumen ini.');
        }

        return view('dokumen-asesmen.va.wizard', compact('va'));
    }

    /**
     * Simpan langkah wizard / finalisasi FR.VA
     */
    public function saveStep(Request $request, $id)
    {
        $user = auth()->user();
        $va = AssessmentVa::findOrFail($id);

        if ($user->peran === 'asesor' && $va->lead_asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak');
        }

        $step = (int) $request->input('step', 1);
        $finalize = (bool) $request->input('finalize', false);
        $signature = $request->input('signature') ?: $user->tanda_tangan;

        if ($finalize && empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan ketua / lead asesor untuk memfinalisasi kegiatan validasi.');
        }

        $stepData = $request->except(['_token', 'step', 'finalize', 'signature']);

        $this->documentService->saveStepVa($va, $stepData, $step, $finalize, $signature, $user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Langkah ' . $step . ' berhasil disimpan',
                'saved_at' => now()->format('H:i:s'),
                'is_finalized' => $va->isFinalized(),
            ]);
        }

        $msg = $finalize
            ? 'Kegiatan validasi asesmen FR.VA berhasil difinalisasi dan dikunci.'
            : "Langkah {$step} berhasil disimpan.";

        return redirect()->route('dokumen-asesmen.va.wizard', $id)->with('sukses', $msg);
    }

    /**
     * Cetak lembar resmi A4 standar BNSP
     */
    public function cetak($id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $va = AssessmentVa::with(['skema', 'leadAsesor'])->findOrFail($id);

        return view('dokumen-asesmen.va.cetak', compact('va'));
    }
}
