<?php

namespace App\Http\Middleware;

use App\Models\PendaftaranAsesi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssessmentScheduleActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('masuk');
        }

        // =========================================================================
        // 1. GUARD UNTUK ASESI (SISWA / PESERTA UJI)
        // =========================================================================
        if ($user->peran === 'asesi') {
            $pendaftaranId = $request->route('pendaftaran_id') 
                ?: ($request->get('pendaftaran_id') ?: $request->input('pendaftaran_id'));

            $pendaftaran = PendaftaranAsesi::with(['skema', 'jadwal', 'asesor', 'mapa01', 'mapa02', 'rekomendasi'])
                ->where('asesi_id', $user->id)
                ->when($pendaftaranId, fn($q) => $q->where('id', $pendaftaranId))
                ->latest()
                ->first();

            if (!$pendaftaran) {
                if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Pendaftaran skema sertifikasi tidak ditemukan.'
                    ], 404);
                }

                return redirect()->route('asesi.dashboard')
                    ->with('error', 'Pendaftaran skema sertifikasi aktif tidak ditemukan.');
            }

            // Jika asesi sudah selesai dinilai oleh asesor, izinkan akses (readonly)
            if ($pendaftaran->status_pendaftaran === 'selesai' || !empty($pendaftaran->rekomendasi)) {
                return $next($request);
            }

            $isPost = $request->isMethod('POST');

            // Cek Tahap 1: FR.AK.01 harus disahkan kedua pihak
            if (!$pendaftaran->isAk01Selesai()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Formulir Persetujuan Asesmen (FR.AK.01) belum disahkan.'
                    ], 403);
                }

                if ($pendaftaran->status_ak01 === 'disetujui_asesi' || !empty($pendaftaran->tanda_tangan_asesi_ak01)) {
                    return redirect()->route('asesi.ak01', ['id' => $pendaftaran->id])
                        ->with('warning', 'Formulir FR.AK.01 telah Anda tanda tangani dan sedang menunggu persetujuan/pengesahan dari Asesor Penguji sebelum sesi ujian dapat diakses.');
                }

                return redirect()->route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $pendaftaran->id])
                    ->with('error', 'Harap tandatangani Persetujuan Asesmen (FR.AK.01) pada Tahapan Asesmen sebelum memasuki Ruang Ujian.');
            }

            // Cek Tahap 2: MAPA harus disahkan untuk pengerjaan aktif (POST)
            if ($isPost && !$pendaftaran->isMapaConfirmed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan Asesor.'
                ], 403);
            }

            // Sinkronisasi status jadwal secara real-time
            \App\Models\JadwalAsesmen::syncAllStatuses();
            if ($pendaftaran->jadwal) {
                $pendaftaran->jadwal->syncRealtimeStatus();
            }

            // Cek Tahap 3: Time-Lock Jadwal Asesmen
            if ($pendaftaran->jadwal) {
                // Skenario 1: Jadwal Dibatalkan
                if ($pendaftaran->jadwal->status_jadwal === 'dibatalkan') {
                    if ($isPost || $request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Jadwal asesmen telah dibatalkan.'], 403);
                    }
                    return redirect()->route('asesi.dashboard')->with('error', 'Jadwal asesmen telah dibatalkan oleh LSP.');
                }

                // Skenario 2: Belum Waktunya untuk aksi POST
                if ($isPost && $pendaftaran->jadwal->isBelumMulai()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $pendaftaran->assessment_time_status['pesan'] ?? 'Sesi ujian belum dibuka.'
                    ], 403);
                }

                // Skenario 3: Waktu Ujian Sudah Berakhir untuk aksi POST (Toleransi 10 Menit)
                if ($isPost && $pendaftaran->jadwal->isSudahSelesai(true, 10)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Waktu pengerjaan asesmen telah berakhir (Toleransi submit habis).'
                    ], 403);
                }
            }

            return $next($request);
        }

        // =========================================================================
        // 2. GUARD UNTUK ASESOR (DATA SCOPING & AUTHORIZATION)
        // =========================================================================
        if ($user->peran === 'asesor') {
            $pendaftaranId = $request->route('pendaftaranId') 
                ?: ($request->route('id') ?: ($request->get('pendaftaran_id') ?: $request->input('pendaftaran_id')));

            if ($pendaftaranId) {
                $pendaftaran = PendaftaranAsesi::with('jadwal')->find($pendaftaranId);

                if (!$pendaftaran) {
                    abort(404, 'Data pendaftaran tidak ditemukan.');
                }

                $isAuthorized = ($pendaftaran->asesor_id === $user->id)
                    || ($pendaftaran->jadwal && $pendaftaran->jadwal->asesor_id === $user->id);

                if (!$isAuthorized) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses asesi pada skema dan jadwal ini.'
                        ], 404);
                    }

                    abort(404, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menilai asesi pada skema/jadwal ini.');
                }
            }

            return $next($request);
        }

        // Peran Admin & Superadmin memiliki akses pengawasan penuh
        return $next($request);
    }
}
