<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAk06;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use App\Services\AssessmentDocumentService;
use Illuminate\Http\Request;

class AssessmentAk06Controller extends Controller
{
    protected AssessmentDocumentService $documentService;

    public function __construct(AssessmentDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Daftar tinjauan proses asesmen
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $reviews = AssessmentAk06::with(['skema', 'jadwal', 'pendaftaran.asesi', 'asesor'])
            ->when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $skemas = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema')->get();
        $jadwals = JadwalAsesmen::with('skema')
            ->when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->orderBy('tanggal_uji', 'desc')
            ->get();

        return view('dokumen-asesmen.ak06.index', compact('reviews', 'skemas', 'jadwals'));
    }

    /**
     * Buat tinjauan baru
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'scope_type' => 'required|in:individual,kelompok,skema',
            'jadwal_id' => 'nullable|exists:jadwal_asesmen,id',
            'pendaftaran_id' => 'nullable|exists:pendaftaran_asesi,id',
        ]);

        $skemaId = (int) $request->input('skema_id');
        $scopeType = $request->input('scope_type', 'skema');
        $jadwalId = $request->input('jadwal_id') ? (int) $request->input('jadwal_id') : null;
        $pendaftaranId = $request->input('pendaftaran_id') ? (int) $request->input('pendaftaran_id') : null;

        $ak06 = $this->documentService->getOrCreateAk06(
            $skemaId,
            $scopeType,
            $jadwalId,
            $pendaftaranId,
            $user
        );

        return redirect()->route('dokumen-asesmen.ak06.edit', $ak06->id);
    }

    /**
     * Tampilkan dan kelola formulir FR.AK.06
     */
    public function edit($id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $ak06 = AssessmentAk06::with(['skema', 'jadwal', 'pendaftaran.asesi', 'asesor'])->findOrFail($id);

        if ($user->peran === 'asesor' && $ak06->asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda bukan peninjau pada dokumen ini.');
        }

        return view('dokumen-asesmen.ak06.edit', compact('ak06'));
    }

    /**
     * Simpan pembaruan atau finalisasi review FR.AK.06
     */
    public function simpan(Request $request, $id)
    {
        $user = auth()->user();
        $ak06 = AssessmentAk06::findOrFail($id);

        if ($user->peran === 'asesor' && $ak06->asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak');
        }

        $data = $request->validate([
            'prosedur_matrix' => 'nullable|array',
            'dimensi_kompetensi' => 'nullable|array',
            'rekomendasi_peningkatan' => 'nullable|array',
            'komentar_reviewer' => 'nullable|string',
            'finalize' => 'nullable|boolean',
        ]);

        $isFinalize = (bool) ($request->input('finalize', 0));
        $signature = $request->input('signature') ?: $user->tanda_tangan;

        if ($isFinalize && empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan reviewer sebelum memfinalisasi dokumen peninjauan.');
        }

        $this->documentService->saveAk06($ak06, $data, $isFinalize, $signature, $user);

        $msg = $isFinalize
            ? 'Dokumen peninjauan FR.AK.06 telah difinalisasi dan dikunci.'
            : 'Draf FR.AK.06 berhasil disimpan.';

        return redirect()->route('dokumen-asesmen.ak06.edit', $id)->with('sukses', $msg);
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

        $ak06 = AssessmentAk06::with(['skema', 'jadwal', 'pendaftaran.asesi', 'asesor'])->findOrFail($id);

        return view('dokumen-asesmen.ak06.cetak', compact('ak06'));
    }
}
