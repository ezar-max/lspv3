<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAk02;
use App\Models\AssessmentAk03;
use App\Models\AssessmentAk05;
use App\Models\AssessmentAk06;
use App\Models\AssessmentVa;
use App\Models\DocumentAuditLog;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;

class DokumenAsesmenController extends Controller
{
    /**
     * Halaman Pusat Manajemen Dokumen Asesmen (/dokumen-asesmen)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        $jenisDokumen = $request->input('jenis_dokumen', 'semua');
        $skemaId = $request->input('skema_id');
        $statusFilter = $request->input('status');
        $cari = $request->input('cari');

        $skemas = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema')->get();
        $asesors = Pengguna::where('peran', 'asesor')->where('aktif', true)->orderBy('nama_lengkap')->get();

        // 1. Query AK.02
        $ak02Query = AssessmentAk02::with(['pendaftaran.asesi', 'skema', 'asesor']);
        if ($user->peran === 'asesi') {
            $ak02Query->whereHas('pendaftaran', fn ($q) => $q->where('asesi_id', $user->id));
        } elseif ($user->peran === 'asesor') {
            $ak02Query->where('asesor_id', $user->id);
        }
        if ($skemaId) {
            $ak02Query->where('skema_id', $skemaId);
        }
        if ($statusFilter) {
            $ak02Query->where('status', $statusFilter);
        }
        if ($cari) {
            $ak02Query->whereHas('pendaftaran.asesi', fn ($q) => $q->where('nama_lengkap', 'like', "%{$cari}%"))
                ->orWhereHas('pendaftaran', fn ($q) => $q->where('nomor_pendaftaran', 'like', "%{$cari}%"));
        }
        $ak02List = $ak02Query->get();

        // 2. Query AK.03
        $ak03Query = AssessmentAk03::with(['pendaftaran.asesi', 'pendaftaran.skema', 'asesor']);
        if ($user->peran === 'asesi') {
            $ak03Query->where('asesi_id', $user->id);
        } elseif ($user->peran === 'asesor') {
            $ak03Query->where('asesor_id', $user->id);
        }
        if ($skemaId) {
            $ak03Query->whereHas('pendaftaran', fn ($q) => $q->where('skema_id', $skemaId));
        }
        if ($statusFilter) {
            $ak03Query->where('status', $statusFilter);
        }
        if ($cari) {
            $ak03Query->whereHas('pendaftaran.asesi', fn ($q) => $q->where('nama_lengkap', 'like', "%{$cari}%"));
        }
        $ak03List = $ak03Query->get();

        // 3. Query AK.05 (Hanya Asesor, Admin, Superadmin)
        $ak05List = collect();
        if ($user->peran !== 'asesi') {
            $ak05Query = AssessmentAk05::with(['skema', 'jadwal', 'asesor']);
            if ($user->peran === 'asesor') {
                $ak05Query->where('asesor_id', $user->id);
            }
            if ($skemaId) {
                $ak05Query->where('skema_id', $skemaId);
            }
            if ($statusFilter) {
                $ak05Query->where('status', $statusFilter);
            }
            if ($cari) {
                $ak05Query->where('nomor_laporan', 'like', "%{$cari}%");
            }
            $ak05List = $ak05Query->get();
        }

        // 4. Query AK.06 (Hanya Asesor, Admin, Superadmin)
        $ak06List = collect();
        if ($user->peran !== 'asesi') {
            $ak06Query = AssessmentAk06::with(['skema', 'jadwal', 'pendaftaran.asesi', 'asesor']);
            if ($user->peran === 'asesor') {
                $ak06Query->where('asesor_id', $user->id);
            }
            if ($skemaId) {
                $ak06Query->where('skema_id', $skemaId);
            }
            if ($statusFilter) {
                $ak06Query->where('status', $statusFilter);
            }
            if ($cari) {
                $ak06Query->where('nomor_review', 'like', "%{$cari}%");
            }
            $ak06List = $ak06Query->get();
        }

        // 5. Query FR.VA (Hanya Asesor, Admin, Superadmin)
        $vaList = collect();
        if ($user->peran !== 'asesi') {
            $vaQuery = AssessmentVa::with(['skema', 'leadAsesor']);
            if ($user->peran === 'asesor') {
                $vaQuery->where('lead_asesor_id', $user->id);
            }
            if ($skemaId) {
                $vaQuery->where('skema_id', $skemaId);
            }
            if ($statusFilter) {
                $vaQuery->where('status', $statusFilter);
            }
            if ($cari) {
                $vaQuery->where('nomor_validasi', 'like', "%{$cari}%");
            }
            $vaList = $vaQuery->get();
        }

        // Susun item dokumen ke dalam list seragam
        $items = collect();

        if (in_array($jenisDokumen, ['semua', 'ak02'])) {
            foreach ($ak02List as $row) {
                $items->push([
                    'id' => $row->id,
                    'reference_id' => $row->pendaftaran_id,
                    'jenis_kode' => 'FR.AK.02',
                    'jenis_label' => 'Rekaman Asesmen',
                    'nomor_dokumen' => $row->pendaftaran ? $row->pendaftaran->nomor_pendaftaran : "AK02-#{$row->id}",
                    'skema' => $row->skema ? $row->skema->nama_skema : '-',
                    'subjek' => $row->pendaftaran && $row->pendaftaran->asesi ? $row->pendaftaran->asesi->nama_lengkap : 'Asesi',
                    'asesor' => $row->asesor ? $row->asesor->nama_lengkap : '-',
                    'tanggal' => $row->updated_at ? $row->updated_at->format('d/m/Y') : '-',
                    'status' => $row->status,
                    'version' => $row->version,
                    'view_url' => route('dokumen-asesmen.ak02.edit', $row->pendaftaran_id),
                    'edit_url' => in_array($user->peran, ['asesor', 'admin', 'superadmin']) ? route('dokumen-asesmen.ak02.edit', $row->pendaftaran_id) : null,
                    'print_url' => route('dokumen-asesmen.ak02.cetak', $row->pendaftaran_id),
                    'raw_model' => $row,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (in_array($jenisDokumen, ['semua', 'ak03'])) {
            foreach ($ak03List as $row) {
                $items->push([
                    'id' => $row->id,
                    'reference_id' => $row->pendaftaran_id,
                    'jenis_kode' => 'FR.AK.03',
                    'jenis_label' => 'Umpan Balik Asesi',
                    'nomor_dokumen' => $row->pendaftaran ? $row->pendaftaran->nomor_pendaftaran : "AK03-#{$row->id}",
                    'skema' => $row->pendaftaran && $row->pendaftaran->skema ? $row->pendaftaran->skema->nama_skema : '-',
                    'subjek' => $row->asesi ? $row->asesi->nama_lengkap : 'Asesi',
                    'asesor' => $row->asesor ? $row->asesor->nama_lengkap : '-',
                    'tanggal' => $row->updated_at ? $row->updated_at->format('d/m/Y') : '-',
                    'status' => $row->status,
                    'version' => $row->version,
                    'view_url' => route('dokumen-asesmen.ak03.show', $row->pendaftaran_id),
                    'edit_url' => $user->peran === 'asesi' && !$row->is_locked ? route('dokumen-asesmen.ak03.show', $row->pendaftaran_id) : null,
                    'print_url' => route('dokumen-asesmen.ak03.cetak', $row->pendaftaran_id),
                    'raw_model' => $row,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (in_array($jenisDokumen, ['semua', 'ak05'])) {
            foreach ($ak05List as $row) {
                $items->push([
                    'id' => $row->id,
                    'reference_id' => $row->id,
                    'jenis_kode' => 'FR.AK.05',
                    'jenis_label' => 'Laporan Asesmen',
                    'nomor_dokumen' => $row->nomor_laporan,
                    'skema' => $row->skema ? $row->skema->nama_skema : '-',
                    'subjek' => "Kelompok ({$row->total_asesi} Asesi)",
                    'asesor' => $row->asesor ? $row->asesor->nama_lengkap : '-',
                    'tanggal' => $row->tanggal_laporan ? $row->tanggal_laporan->format('d/m/Y') : '-',
                    'status' => $row->status,
                    'version' => $row->version,
                    'view_url' => route('dokumen-asesmen.ak05.edit', $row->id),
                    'edit_url' => in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$row->isFinalized() ? route('dokumen-asesmen.ak05.edit', $row->id) : null,
                    'print_url' => route('dokumen-asesmen.ak05.cetak', $row->id),
                    'raw_model' => $row,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (in_array($jenisDokumen, ['semua', 'ak06'])) {
            foreach ($ak06List as $row) {
                $subjek = match ($row->scope_type) {
                    'individual' => $row->pendaftaran && $row->pendaftaran->asesi ? $row->pendaftaran->asesi->nama_lengkap : 'Individual',
                    'kelompok' => 'Kelompok Peserta',
                    default => 'Skema Sertifikasi',
                };

                $items->push([
                    'id' => $row->id,
                    'reference_id' => $row->id,
                    'jenis_kode' => 'FR.AK.06',
                    'jenis_label' => 'Review Proses Asesmen',
                    'nomor_dokumen' => $row->nomor_review,
                    'skema' => $row->skema ? $row->skema->nama_skema : '-',
                    'subjek' => $subjek,
                    'asesor' => $row->asesor ? $row->asesor->nama_lengkap : '-',
                    'tanggal' => $row->tanggal_review ? $row->tanggal_review->format('d/m/Y') : '-',
                    'status' => $row->status,
                    'version' => $row->version,
                    'view_url' => route('dokumen-asesmen.ak06.edit', $row->id),
                    'edit_url' => in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$row->isFinalized() ? route('dokumen-asesmen.ak06.edit', $row->id) : null,
                    'print_url' => route('dokumen-asesmen.ak06.cetak', $row->id),
                    'raw_model' => $row,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (in_array($jenisDokumen, ['semua', 'va'])) {
            foreach ($vaList as $row) {
                $items->push([
                    'id' => $row->id,
                    'reference_id' => $row->id,
                    'jenis_kode' => 'FR.VA',
                    'jenis_label' => 'Validasi Asesmen',
                    'nomor_dokumen' => $row->nomor_validasi,
                    'skema' => $row->skema ? $row->skema->nama_skema : '-',
                    'subjek' => 'Tim Penjaminan Mutu Validasi',
                    'asesor' => $row->leadAsesor ? $row->leadAsesor->nama_lengkap : '-',
                    'tanggal' => $row->tanggal_validasi ? $row->tanggal_validasi->format('d/m/Y') : '-',
                    'status' => $row->status,
                    'version' => $row->version,
                    'view_url' => route('dokumen-asesmen.va.wizard', $row->id),
                    'edit_url' => in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$row->isFinalized() ? route('dokumen-asesmen.va.wizard', $row->id) : null,
                    'print_url' => route('dokumen-asesmen.va.cetak', $row->id),
                    'raw_model' => $row,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        // Urutkan berdasarkan updated_at terbaru
        $items = $items->sortByDesc('updated_at')->values();

        // Hitung statistik dokumen
        $stats = [
            'total' => $items->count(),
            'draft' => $items->whereIn('status', ['draft', 'dalam_pengisian'])->count(),
            'dalam_proses' => $items->whereIn('status', ['assessment_completed', 'review'])->count(),
            'menunggu_review' => $items->whereIn('status', ['decision_recorded', 'submitted'])->count(),
            'selesai' => $items->whereIn('status', ['final', 'terkunci', 'signed'])->count(),
            'perlu_revisi' => $items->whereIn('status', ['perlu_revisi', 'revisi'])->count(),
        ];

        return view('dokumen-asesmen.index', compact(
            'items',
            'stats',
            'skemas',
            'asesors',
            'jenisDokumen',
            'skemaId',
            'statusFilter',
            'cari'
        ));
    }

    /**
     * Dapatkan data jejak audit suatu dokumen untuk modal / slideover
     */
    public function auditTrail(Request $request, string $type, int $id)
    {
        $logs = DocumentAuditLog::with('user')
            ->where('document_type', strtoupper($type))
            ->where('document_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'type' => strtoupper($type),
            'id' => $id,
            'logs' => $logs,
        ]);
    }

    /**
     * Buka kembali dokumen yang telah difinalisasi (Khusus Admin / Super Admin)
     */
    public function reopen(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['admin', 'superadmin'])) {
            abort(403, 'Hanya Admin atau Super Admin yang berwenang membuka kembali dokumen.');
        }

        $data = $request->validate([
            'document_type' => 'required|string',
            'document_id' => 'required|integer',
            'reason' => 'required|string|min:5',
        ]);

        app(\App\Services\AssessmentDocumentService::class)->reopenDocument(
            $data['document_type'],
            $data['document_id'],
            $data['reason'],
            $user
        );

        return back()->with('sukses', "Dokumen {$data['document_type']} berhasil dibuka kembali untuk revisi (Versi baru dibuat).");
    }
}

