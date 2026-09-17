<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAk05;
use App\Models\JadwalAsesmen;
use App\Models\SkemaSertifikasi;
use App\Services\AssessmentDocumentService;
use Illuminate\Http\Request;

class AssessmentAk05Controller extends Controller
{
    protected AssessmentDocumentService $documentService;

    public function __construct(AssessmentDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Daftar laporan asesmen / selector pembuatan laporan baru
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $laporans = AssessmentAk05::with(['skema', 'jadwal', 'asesor'])
            ->when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $skemas = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema')->get();
        $jadwals = JadwalAsesmen::with('skema')
            ->when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->orderBy('tanggal_uji', 'desc')
            ->get();

        return view('dokumen-asesmen.ak05.index', compact('laporans', 'skemas', 'jadwals'));
    }

    /**
     * Buat laporan baru atau buka laporan yang sudah ada untuk skema/jadwal terkait
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'jadwal_id' => 'nullable|exists:jadwal_asesmen,id',
        ]);

        $skemaId = (int) $request->input('skema_id');
        $jadwalId = $request->input('jadwal_id') ? (int) $request->input('jadwal_id') : null;

        $ak05 = $this->documentService->getOrCreateAk05($skemaId, $jadwalId, $user);

        return redirect()->route('dokumen-asesmen.ak05.edit', $ak05->id);
    }

    /**
     * Tampilkan dan kelola formulir laporan asesmen FR.AK.05
     */
    public function edit($id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $ak05 = AssessmentAk05::with(['skema.unitKompetensi', 'jadwal', 'asesor'])->findOrFail($id);

        if ($user->peran === 'asesor' && $ak05->asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda bukan pembuat laporan asesmen ini.');
        }

        return view('dokumen-asesmen.ak05.edit', compact('ak05'));
    }

    /**
     * Sinkronkan ulang data peserta dengan FR.AK.02
     */
    public function sync($id)
    {
        $user = auth()->user();
        $ak05 = AssessmentAk05::findOrFail($id);

        if ($user->peran === 'asesor' && $ak05->asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak');
        }

        $this->documentService->syncParticipantsAk05($ak05);

        return redirect()->route('dokumen-asesmen.ak05.edit', $id)
            ->with('sukses', 'Daftar hasil asesi berhasil disinkronkan langsung dari seluruh dokumen FR.AK.02!');
    }

    /**
     * Simpan pembaruan atau finalisasi FR.AK.05
     */
    public function simpan(Request $request, $id)
    {
        $user = auth()->user();
        $ak05 = AssessmentAk05::findOrFail($id);

        if ($user->peran === 'asesor' && $ak05->asesor_id !== $user->id) {
            abort(403, 'Akses Ditolak');
        }

        $data = $request->validate([
            'aspek_positif' => 'nullable|string',
            'aspek_negatif' => 'nullable|string',
            'penolakan_hasil' => 'nullable|string',
            'saran_perbaikan' => 'nullable|array',
            'rekap_asesi' => 'nullable|array',
            'finalize' => 'nullable|boolean',
        ]);

        $isFinalize = (bool) ($request->input('finalize', 0));
        $signature = $request->input('signature') ?: $user->tanda_tangan;

        if ($isFinalize && empty($signature)) {
            return back()->with('error', 'Silakan bubuhkan tanda tangan asesor untuk memfinalisasi laporan asesmen.');
        }

        $this->documentService->saveAk05($ak05, $data, $isFinalize, $signature, $user);

        $msg = $isFinalize
            ? 'Laporan asesmen FR.AK.05 telah difinalisasi dan dikunci.'
            : 'Draf laporan FR.AK.05 berhasil disimpan.';

        return redirect()->route('dokumen-asesmen.ak05.edit', $id)->with('sukses', $msg);
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

        $ak05 = AssessmentAk05::with(['skema', 'jadwal', 'asesor'])->findOrFail($id);

        return view('dokumen-asesmen.ak05.cetak', compact('ak05'));
    }
}
