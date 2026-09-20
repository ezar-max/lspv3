<?php

namespace App\Http\Controllers;

use App\Models\Mapa01;
use App\Models\PendaftaranAsesi;
use App\Models\DokumenAsesi;
use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use App\Models\LogAktivitas;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        JadwalAsesmen::syncAllStatuses();

        $totalAsesi = Pengguna::where('peran', 'asesi')->count();
        $totalPendingVerifikasi = PendaftaranAsesi::where('status_pendaftaran', 'diajukan')->count();
        $totalTerverifikasi = PendaftaranAsesi::where('status_pendaftaran', 'diverifikasi')->count();
        $totalSkema = SkemaSertifikasi::count();

        $pendaftaranTerbaru = PendaftaranAsesi::with(['asesi.profilAsesi', 'skema', 'dokumen'])
            ->latest()
            ->take(6)
            ->get();

        $latestPendaftaranItem = PendaftaranAsesi::latest('updated_at')->first();
        $latestHash = $latestPendaftaranItem ? md5($latestPendaftaranItem->id . '_' . $latestPendaftaranItem->status_pendaftaran . '_' . $latestPendaftaranItem->updated_at) : '';
        $latestPendaftaranId = PendaftaranAsesi::max('id') ?? 0;

        return view('admin.dashboard-admin', compact(
            'totalAsesi', 'totalPendingVerifikasi', 'totalTerverifikasi', 'totalSkema', 'pendaftaranTerbaru', 'latestHash', 'latestPendaftaranId'
        ));
    }

    public function verifikasiBerkas(Request $request)
    {
        JadwalAsesmen::syncAllStatuses();

        $status = $request->get('status', 'diajukan');

        $pendaftaranList = PendaftaranAsesi::with(['asesi.profilAsesi', 'skema', 'dokumen', 'jadwal'])
            ->when($status !== 'semua', function($q) use ($status) {
                $q->where('status_pendaftaran', $status);
            })
            ->latest()
            ->paginate(10);

        return view('admin.verifikasi-berkas', compact('pendaftaranList', 'status'));
    }

    public function detailVerifikasi($id)
    {
        JadwalAsesmen::syncAllStatuses();

        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi', 
            'skema.unitKompetensi.elemenKompetensi', 
            'dokumen', 
            'jadwal.asesor', 
            'asesor',
            'jawabanApl02'
        ])->findOrFail($id);

        // Hitung nomor urut pendaftaran asesi pada skema ini
        $nomorUrutPendaftaran = PendaftaranAsesi::where('skema_id', $pendaftaran->skema_id)
            ->where('created_at', '<=', $pendaftaran->created_at)
            ->count();
        $totalPendaftarSkema = PendaftaranAsesi::where('skema_id', $pendaftaran->skema_id)->count();

        // Ambil daftar jadwal aktif untuk skema ini & pastikan role skema asesor sesuai
        $jadwalList = JadwalAsesmen::with('asesor')
            ->where('skema_id', $pendaftaran->skema_id)
            ->where('status_jadwal', '!=', 'selesai')
            ->where('status_jadwal', '!=', 'dibatalkan')
            ->whereHas('asesor', function($a) use ($pendaftaran) {
                $a->where('skema_id', $pendaftaran->skema_id);
            })
            ->orderBy('tanggal_uji', 'asc')
            ->get()
            ->map(function($j) use ($pendaftaran) {
                $terisi = PendaftaranAsesi::where('jadwal_id', $j->id)->count();
                $j->terisi = $terisi;
                $j->sisa_kuota = max(0, $j->kuota - $terisi);
                $j->is_penuh = ($terisi >= $j->kuota) && ($pendaftaran->jadwal_id != $j->id);
                return $j;
            });

        $asesorList = Pengguna::where('peran', 'asesor')
            ->where('aktif', true)
            ->where('skema_id', $pendaftaran->skema_id)
            ->get();

        $semuaJadwalPenuh = $jadwalList->isNotEmpty() && $jadwalList->every(fn($j) => $j->is_penuh);

        return view('admin.detail-verifikasi', compact('pendaftaran', 'jadwalList', 'asesorList', 'nomorUrutPendaftaran', 'totalPendaftarSkema', 'semuaJadwalPenuh'));
    }

    public function simpanVerifikasi(Request $request, $id)
    {
        JadwalAsesmen::syncAllStatuses();

        $pendaftaran = PendaftaranAsesi::findOrFail($id);

        $request->validate([
            'status_pendaftaran' => 'required|in:diverifikasi,ditolak,draft,revisi',
            'rekomendasi_admin_status' => 'nullable|in:diterima,tidak_diterima',
            'jadwal_id' => 'nullable|exists:jadwal_asesmen,id',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        $ttdAdmin = $request->tanda_tangan_admin_base64 ?: auth()->user()->tanda_tangan;

        if ($request->tanda_tangan_admin_base64) {
            auth()->user()->update(['tanda_tangan' => $request->tanda_tangan_admin_base64]);
        }

        $isRevisi = in_array($request->status_pendaftaran, ['draft', 'revisi']);
        $isDitolak = ($request->status_pendaftaran === 'ditolak' || $request->rekomendasi_admin_status === 'tidak_diterima');

        if ($isRevisi) {
            $catatanVerifikasi = trim($request->catatan_verifikasi ?? '');

            // Update status dokumen individual jika ada dan kumpulkan catatan yang ditulis admin
            $catatanDokumenList = [];
            if ($request->has('verifikasi_dokumen')) {
                foreach ($request->verifikasi_dokumen as $dokumenId => $statusDok) {
                    $catatanDok = trim($request->catatan_dokumen[$dokumenId] ?? '');
                    DokumenAsesi::where('id', $dokumenId)->update([
                        'status_verifikasi' => $statusDok,
                        'catatan' => $catatanDok ?: null,
                    ]);

                    if ($statusDok === 'tidak_valid' && !empty($catatanDok)) {
                        $catatanDokumenList[] = $catatanDok;
                    }
                }
            }

            // Utamakan persis isi teks yang ditulis admin tanpa template buatan
            if (empty($catatanVerifikasi)) {
                if (count($catatanDokumenList) > 0) {
                    $catatanVerifikasi = implode("\n", $catatanDokumenList);
                } else {
                    $catatanVerifikasi = 'Mohon periksa dan unggah ulang berkas persyaratan yang diperlukan.';
                }
            }

            $pendaftaran->update([
                'status_pendaftaran' => 'revisi',
                'rekomendasi_admin_status' => null,
                'jadwal_id' => null,
                'asesor_id' => null,
                'catatan_verifikasi' => $catatanVerifikasi,
                'request_perbaikan' => false,
                'catatan_request_perbaikan' => null,
            ]);

            LogAktivitas::catat('Verifikasi Berkas - Perlu Revisi', 'Admin mengembalikan status pendaftaran #' . $pendaftaran->nomor_pendaftaran . ' ke status REVISI untuk diperbaiki oleh Asesi');

            // Notifikasi ke Asesi untuk perbaikan berkas
            $asesi = $pendaftaran->asesi;
            if ($asesi) {
                $catatan = $catatanVerifikasi ? " Catatan perbaikan: {$catatanVerifikasi}" : '';
                $asesi->notify(new SystemAlert(
                    'Berkas Pendaftaran Memerlukan Perbaikan',
                    "Berkas permohonan pendaftaran FR.APL.01 Anda dikembalikan oleh Admin LSP untuk diperbaiki.{$catatan}",
                    route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 1]),
                    'revision',
                    ['pendaftaran_id' => $pendaftaran->id, 'status' => 'revisi']
                ));
            }

            return redirect()->route('admin.verifikasi-berkas')->with('sukses', 'Status pendaftaran berhasil dikembalikan sebagai REVISI ke Asesi beserta catatan perbaikan dokumen.');
        }

        if ($isDitolak) {
            $pendaftaran->update([
                'status_pendaftaran' => 'ditolak',
                'rekomendasi_admin_status' => 'tidak_diterima',
                'jadwal_id' => null,
                'asesor_id' => null,
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'tanda_tangan_admin' => $ttdAdmin,
                'tanggal_ttd_admin' => now(),
                'request_perbaikan' => false,
            ]);

            // Update status dokumen individual jika ada
            if ($request->has('verifikasi_dokumen')) {
                foreach ($request->verifikasi_dokumen as $dokumenId => $statusDok) {
                    DokumenAsesi::where('id', $dokumenId)->update([
                        'status_verifikasi' => $statusDok,
                        'catatan' => $request->catatan_dokumen[$dokumenId] ?? null,
                    ]);
                }
            }

            LogAktivitas::catat('Verifikasi Berkas - Ditolak', 'Admin menolak berkas pendaftaran #' . $pendaftaran->nomor_pendaftaran . ' (Status: TIDAK DITERIMA)');

            // Notifikasi ke Asesi bahwa permohonan ditolak
            $asesi = $pendaftaran->asesi;
            if ($asesi) {
                $catatan = $request->catatan_verifikasi ? " Catatan: {$request->catatan_verifikasi}" : '';
                $asesi->notify(new SystemAlert(
                    'Permohonan Pendaftaran Tidak Diterima',
                    "Mohon maaf, permohonan pendaftaran sertifikasi Anda dinyatakan belum memenuhi syarat oleh Admin LSP.{$catatan}",
                    route('asesi.dashboard'),
                    'danger',
                    ['pendaftaran_id' => $pendaftaran->id, 'status' => 'ditolak']
                ));
            }

            return redirect()->route('admin.verifikasi-berkas')->with('sukses', 'Status pendaftaran berhasil disimpan sebagai DITOLAK (Tidak Diterima).');
        }

        // =========================================================================
        // VALIDASI KETAT SAAT ACC / DIVERIFIKASI (DITERIMA)
        // =========================================================================

        // 1. Validasi Tanda Tangan Admin
        if (empty($ttdAdmin)) {
            return back()->withInput()->with('error', 'Gagal memverifikasi: Tanda Tangan Admin LSP wajib dibubuhkan/digambar sebelum menyetujui (ACC) permohonan.');
        }

        // 2. Validasi Ketersediaan Jadwal Uji & Asesor Penguji
        $jadwalAktifList = JadwalAsesmen::with('asesor')
            ->where('skema_id', $pendaftaran->skema_id)
            ->where('status_jadwal', '!=', 'selesai')
            ->where('status_jadwal', '!=', 'dibatalkan')
            ->whereHas('asesor', function($a) use ($pendaftaran) {
                $a->where('skema_id', $pendaftaran->skema_id);
            })
            ->get();

        if ($jadwalAktifList->isEmpty()) {
            return back()->withInput()->with('warning', 'Gagal menyetujui (ACC): Belum ada sesi jadwal uji aktif dengan asesor penguji untuk skema sertifikasi ini. Silakan buat jadwal baru terlebih dahulu di menu Manajemen Jadwal.');
        }

        // Cek apakah seluruh jadwal aktif untuk skema ini sudah penuh kuotanya
        $adaJadwalTersedia = $jadwalAktifList->contains(function($j) use ($pendaftaran) {
            $terisi = PendaftaranAsesi::where('jadwal_id', $j->id)->where('id', '!=', $pendaftaran->id)->count();
            return $terisi < $j->kuota;
        });

        if (!$adaJadwalTersedia && !$pendaftaran->jadwal_id) {
            return back()->withInput()->with('warning', 'Gagal menyetujui (ACC): Seluruh sesi jadwal uji untuk skema sertifikasi ini sudah penuh kuotanya. Silakan tambah kuota atau buat sesi jadwal baru terlebih dahulu di menu Manajemen Jadwal.');
        }

        $jadwalId = $request->jadwal_id ?: $pendaftaran->jadwal_id;
        if (empty($jadwalId)) {
            return back()->withInput()->with('warning', 'Gagal menyetujui (ACC): Sesi Jadwal Uji & Asesor Penguji wajib dipilih sebelum menyetujui (ACC) permohonan.');
        }

        $jadwalSelected = JadwalAsesmen::find($jadwalId);
        if (!$jadwalSelected) {
            return back()->withInput()->with('error', 'Gagal memverifikasi: Jadwal Asesmen yang dipilih tidak valid atau sudah tidak tersedia.');
        }

        // Validasi kuota jadwal yang dipilih
        $terisiJadwal = PendaftaranAsesi::where('jadwal_id', $jadwalId)->where('id', '!=', $pendaftaran->id)->count();
        if ($terisiJadwal >= $jadwalSelected->kuota && $pendaftaran->jadwal_id != $jadwalId) {
            return back()->withInput()->with('warning', 'Gagal menyetujui (ACC): Kuota sesi jadwal yang dipilih sudah penuh (' . $terisiJadwal . '/' . $jadwalSelected->kuota . '). Silakan pilih sesi jadwal lain atau tambah kuota.');
        }

        $asesorId = $jadwalSelected->asesor_id ?: $pendaftaran->asesor_id;
        if (empty($asesorId)) {
            return back()->withInput()->with('error', 'Gagal memverifikasi: Jadwal yang dipilih belum memiliki Asesor Penguji yang ditugaskan.');
        }

        // Validasi ketat bahwa asesor yang ditugaskan memiliki skema_id yang sama dengan formulir APL.01
        $asesorValid = Pengguna::where('peran', 'asesor')
            ->where('id', $asesorId)
            ->where('skema_id', $pendaftaran->skema_id)
            ->exists();

        if (!$asesorValid) {
            return back()->withInput()->with('error', 'Gagal memverifikasi: Asesor Penguji pada jadwal yang dipilih tidak memiliki role untuk skema sertifikasi yang didaftarkan.');
        }

        // 3. Validasi Kelayakan Dokumen Persyaratan (Jika ada berkas yang diunggah)
        if ($request->has('verifikasi_dokumen')) {
            foreach ($request->verifikasi_dokumen as $dokumenId => $statusDok) {
                if ($statusDok === 'menunggu') {
                    return back()->withInput()->with('error', 'Gagal memverifikasi: Masih ada dokumen persyaratan yang berstatus "Menunggu". Harap periksa dan tentukan kelayakannya (Valid) sebelum menyetujui.');
                }
                if ($statusDok === 'tidak_valid') {
                    return back()->withInput()->with('error', 'Gagal memverifikasi: Terdapat dokumen yang "Tidak Valid". Silakan gunakan status REVISI atau DITOLAK jika berkas persyaratan tidak memenuhi syarat.');
                }
            }
        }

        $pendaftaran->update([
            'status_pendaftaran' => 'diverifikasi',
            'rekomendasi_admin_status' => 'diterima',
            'jadwal_id' => $jadwalId,
            'asesor_id' => $asesorId,
            'catatan_verifikasi' => $request->catatan_verifikasi, // Catatan verifikasi bersifat opsional
            'tanda_tangan_admin' => $ttdAdmin,
            'tanggal_ttd_admin' => now(),
            'request_perbaikan' => false,
        ]);

        // Update status dokumen individual jika ada
        if ($request->has('verifikasi_dokumen')) {
            foreach ($request->verifikasi_dokumen as $dokumenId => $statusDok) {
                DokumenAsesi::where('id', $dokumenId)->update([
                    'status_verifikasi' => $statusDok,
                    'catatan' => $request->catatan_dokumen[$dokumenId] ?? null,
                ]);
            }
        }

        // Sinkronisasi tanda tangan dan validasi Admin ke dokumen FR.MAPA.01 jika sudah dibuat
        $mapa01 = \App\Models\Mapa01::where('pendaftaran_id', $pendaftaran->id)->first();
        if ($mapa01) {
            $existingTable = $mapa01->penyusun_validator_tabel ?: [];
            $existingTable['validator_1'] = [
                'nama' => auth()->user()->nama_lengkap ?? 'Administrator LSP SMKN 1 Gunungputri',
                'nomor_met' => auth()->user()->nomor_registrasi ?? 'REG.LSP.001.2026',
                'ttd' => $ttdAdmin,
                'ttd_tanggal' => now()->format('d/m/Y'),
                'status_validasi' => 'tervalidasi',
            ];
            $mapa01->update(['penyusun_validator_tabel' => $existingTable]);
        }

        LogAktivitas::catat('Verifikasi Berkas Asesi & Penugasan Asesor', 'Admin memverifikasi status pendaftaran #' . $pendaftaran->nomor_pendaftaran . ' dan menugaskan Asesor ID #' . $asesorId);

        // 1. NOTIFIKASI KE ASESI: Berkas disetujui (ACC), silakan lanjutkan isi FR.APL.02
        $asesi = $pendaftaran->asesi;
        if ($asesi) {
            $skemaNama = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';
            $asesi->notify(new SystemAlert(
                'Berkas Pendaftaran Disetujui (ACC)',
                "Selamat! Berkas permohonan pendaftaran FR.APL.01 Anda ({$skemaNama}) telah disetujui (ACC) oleh Admin LSP. Silakan lanjutkan mengisi Formulir Asesmen Mandiri (FR.APL.02).",
                route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2]),
                'approved',
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'tahap' => 'apl02',
                    'status' => 'diverifikasi'
                ]
            ));
        }

        // 2. NOTIFIKASI KE ASESOR: Ada penugasan asesi baru pada jadwal tertentu
        $asesor = Pengguna::find($asesorId);
        if ($asesor) {
            $asesiNama = $asesi ? $asesi->nama_lengkap : 'Asesi Baru';
            $skemaNama = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';
            $kodeJadwal = $jadwalSelected->kode_jadwal ?? '-';
            $tuk = $jadwalSelected->nama_tuk ?? 'TUK';
            $asesor->notify(new SystemAlert(
                'Penugasan Asesi Baru',
                "Anda telah ditugaskan untuk menguji asesi {$asesiNama} ({$skemaNama}) pada jadwal {$kodeJadwal} ({$tuk}).",
                route('asesor.daftar-peserta', ['jadwal_id' => $jadwalSelected->id]),
                'penugasan',
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jadwal_id' => $jadwalSelected->id,
                    'asesi_id' => $pendaftaran->asesi_id,
                    'kode_jadwal' => $kodeJadwal
                ]
            ));
        }

        return redirect()->route('admin.verifikasi-berkas')->with('sukses', 'Status verifikasi pendaftaran (DIVERIFIKASI / DITERIMA) dan Penugasan Asesor berhasil disimpan.');
    }

    public function manajemenAsesi(Request $request)
    {
        $tab = $request->get('tab', 'data-asesi');
        $kataKunci = $request->get('q');
        $statusVerifikasi = $request->get('status', 'diajukan');

        $asesiList = Pengguna::where('peran', 'asesi')
            ->with(['profilAsesi', 'pendaftaranAsesi.skema'])
            ->when($kataKunci, function($q) use ($kataKunci) {
                $q->where('nama_lengkap', 'like', "%{$kataKunci}%")
                  ->orWhere('email', 'like', "%{$kataKunci}%");
            })
            ->latest()
            ->paginate(10, ['*'], 'asesi_page');

        $pendaftaranList = PendaftaranAsesi::with(['asesi.profilAsesi', 'skema', 'dokumen', 'jadwal'])
            ->when($statusVerifikasi !== 'semua' && $statusVerifikasi, function($q) use ($statusVerifikasi) {
                $q->where('status_pendaftaran', $statusVerifikasi);
            })
            ->when($kataKunci, function($q) use ($kataKunci) {
                $q->whereHas('asesi', function($sq) use ($kataKunci) {
                    $sq->where('nama_lengkap', 'like', "%{$kataKunci}%")
                       ->orWhere('email', 'like', "%{$kataKunci}%");
                })->orWhere('nomor_pendaftaran', 'like', "%{$kataKunci}%");
            })
            ->latest()
            ->paginate(10, ['*'], 'verifikasi_page');

        $totalPendingVerifikasi = PendaftaranAsesi::where('status_pendaftaran', 'diajukan')->count();
        $totalTerverifikasi = PendaftaranAsesi::where('status_pendaftaran', 'diverifikasi')->count();
        $totalAsesi = Pengguna::where('peran', 'asesi')->count();

        return view('admin.manajemen-asesi', compact(
            'asesiList', 'pendaftaranList', 'kataKunci', 'tab', 'statusVerifikasi',
            'totalPendingVerifikasi', 'totalTerverifikasi', 'totalAsesi'
        ));
    }

    public function detailAsesi($id)
    {
        $asesi = Pengguna::where('peran', 'asesi')->with([
            'profilAsesi', 
            'pendaftaranAsesi.skema.unitKompetensi', 
            'pendaftaranAsesi.jadwal.asesor',
            'pendaftaranAsesi.dokumen',
            'pendaftaranAsesi.rekomendasi'
        ])->findOrFail($id);

        return view('admin.detail-asesi', compact('asesi'));
    }

    public function simpanTtdAdmin(Request $request, $id)
    {
        $admin = auth()->user();

        $request->validate([
            'tanda_tangan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('tanda_tangan')) {
            $file = $request->file('tanda_tangan');
            $namaFile = 'ttd_admin_' . $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('tanda_tangan', $namaFile, 'public');
            $admin->update(['tanda_tangan' => 'storage/' . $path]);
        }

        return back()->with('sukses', 'Tanda Tangan Digital Admin berhasil diunggah & disimpan.');
    }

    public function laporanKelulusan(Request $request)
    {
        $tab = $request->get('tab', 'kelulusan');
        $skemaId = $request->get('skema_id');
        $status = $request->get('status');
        $kataKunci = $request->get('q');

        // 1. Laporan Kelulusan (Peserta yang sudah ada rekomendasi)
        $queryKelulusan = PendaftaranAsesi::with(['asesi.profilAsesi', 'skema', 'jadwal.asesor', 'rekomendasi'])
            ->whereHas('rekomendasi');
        if ($skemaId) $queryKelulusan->where('skema_id', $skemaId);
        if ($status) {
            $queryKelulusan->whereHas('rekomendasi', function($q) use ($status) {
                $q->where('keputusan', $status);
            });
        }
        $laporanKelulusan = $queryKelulusan->latest()->paginate(15, ['*'], 'kelulusan_page');

        // 2. Laporan Asesi Terdaftar
        $queryAsesi = Pengguna::where('peran', 'asesi')
            ->with(['profilAsesi', 'pendaftaranAsesi.skema', 'pendaftaranAsesi.rekomendasi']);
        if ($kataKunci) {
            $queryAsesi->where(function($q) use ($kataKunci) {
                $q->where('nama_lengkap', 'like', "%{$kataKunci}%")
                  ->orWhere('email', 'like', "%{$kataKunci}%");
            });
        }
        $laporanAsesi = $queryAsesi->latest()->paginate(15, ['*'], 'asesi_page');

        // 3. Laporan Asesmen & Jadwal Uji
        $queryAsesmen = JadwalAsesmen::with(['skema', 'asesor', 'pendaftaranAsesi']);
        if ($skemaId) $queryAsesmen->where('skema_id', $skemaId);
        $laporanAsesmen = $queryAsesmen->latest('tanggal_uji')->paginate(15, ['*'], 'asesmen_page');

        // 4. Rekapitulasi Statistik
        $rekapitulasi = [
            'total_asesi' => Pengguna::where('peran', 'asesi')->count(),
            'total_pendaftaran' => PendaftaranAsesi::count(),
            'total_diverifikasi' => PendaftaranAsesi::where('status_pendaftaran', 'diverifikasi')->count(),
            'total_kompeten' => \App\Models\RekomendasiAsesmen::where('keputusan', 'kompeten')->count(),
            'total_belum_kompeten' => \App\Models\RekomendasiAsesmen::where('keputusan', 'belum_kompeten')->count(),
            'total_skema' => SkemaSertifikasi::count(),
            'total_jadwal' => JadwalAsesmen::count(),
            'skema_stats' => SkemaSertifikasi::withCount(['pendaftaranAsesi', 'unitKompetensi'])->get(),
        ];

        $skemaOptions = SkemaSertifikasi::all();

        return view('admin.laporan-kelulusan', compact(
            'tab', 'laporanKelulusan', 'laporanAsesi', 'laporanAsesmen', 'rekapitulasi',
            'skemaOptions', 'skemaId', 'status', 'kataKunci'
        ));
    }

    public function pengaturanSistem()
    {
        $pengaturan = \App\Models\PengaturanSistem::ambilData();
        return view('superadmin.pengaturan-sistem', compact('pengaturan'));
    }

    public function simpanPengaturanSistem(Request $request)
    {
        $pengaturan = \App\Models\PengaturanSistem::ambilData();

        $request->validate([
            'nama_lsp' => 'required|string|max:255',
            'kode_lsp' => 'required|string|max:100',
            'no_sk_lisensi' => 'nullable|string|max:100',
            'nomor_lisensi' => 'required|string|max:100',
            'masa_berlaku' => 'nullable|string|max:100',
            'status_keaktifan' => 'nullable|string|max:50',
            'email_resmi' => 'required|email',
            'nomor_telepon' => 'required|string',
            'alamat_lengkap' => 'required|string',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $namaFile = 'logo_lsp_' . time() . '.' . $file->getClientOriginalExtension();
            $pengaturan->logo_path = 'storage/' . $file->storeAs('pengaturan', $namaFile, 'public');
        }

        $pengaturan->update([
            'nama_lsp' => $request->nama_lsp,
            'kode_lsp' => $request->kode_lsp,
            'no_sk_lisensi' => $request->no_sk_lisensi,
            'nomor_lisensi' => $request->nomor_lisensi,
            'masa_berlaku' => $request->masa_berlaku,
            'status_keaktifan' => $request->status_keaktifan ?? 'Aktif',
            'email_resmi' => $request->email_resmi,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat_lengkap' => $request->alamat_lengkap,
            'tentang_lsp' => $request->tentang_lsp,
            'visi' => $request->visi,
            'misi' => $request->misi,
        ]);

        LogAktivitas::catat('Ubah Pengaturan Sistem', 'Admin memperbarui konfigurasi profil LSP');

        return back()->with('sukses', 'Pengaturan profil LSP berhasil disimpan.');
    }

    public function manajemenAsesor(Request $request)
    {
        $kataKunci = $request->get('q');

        $query = Pengguna::with('skema')->where('peran', 'asesor');

        if ($kataKunci) {
            $query->where(function($q) use ($kataKunci) {
                $q->where('nama_lengkap', 'like', "%{$kataKunci}%")
                  ->orWhere('email', 'like', "%{$kataKunci}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$kataKunci}%")
                  ->orWhere('nomor_telepon', 'like', "%{$kataKunci}%")
                  ->orWhereHas('skema', function($sq) use ($kataKunci) {
                      $sq->where('nama_skema', 'like', "%{$kataKunci}%")
                         ->orWhere('kode_skema', 'like', "%{$kataKunci}%");
                  });
            });
        }

        $asesorList = $query->latest()->paginate(10);
        $skemaList = SkemaSertifikasi::orderBy('nama_skema', 'asc')->get();

        $totalAsesor = Pengguna::where('peran', 'asesor')->count();
        $totalAktif = Pengguna::where('peran', 'asesor')->where('aktif', true)->count();
        $totalNonaktif = Pengguna::where('peran', 'asesor')->where('aktif', false)->count();

        return view('admin.manajemen-asesor', compact(
            'asesorList', 'skemaList', 'totalAsesor', 'totalAktif', 'totalNonaktif', 'kataKunci'
        ));
    }

    public function simpanAsesor(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email',
            'kata_sandi' => 'required|min:6',
            'skema_id' => 'nullable|exists:skema_sertifikasi,id',
            'nomor_registrasi' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:50',
        ]);

        $asesor = Pengguna::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => 'asesor',
            'skema_id' => $request->skema_id,
            'nomor_registrasi' => $request->nomor_registrasi,
            'nomor_telepon' => $request->nomor_telepon,
            'aktif' => true,
        ]);

        LogAktivitas::catat('Tambah Akun Asesor', 'Admin membuat akun asesor baru: ' . $asesor->nama_lengkap . ' (' . $asesor->email . ')');

        return back()->with('sukses', 'Akun Asesor baru (' . $asesor->nama_lengkap . ') berhasil dibuat.');
    }

    public function ubahAsesor(Request $request, $id)
    {
        $asesor = Pengguna::where('peran', 'asesor')->findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email,' . $asesor->id,
            'kata_sandi' => 'nullable|min:6',
            'skema_id' => 'nullable|exists:skema_sertifikasi,id',
            'nomor_registrasi' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:50',
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'skema_id' => $request->skema_id,
            'nomor_registrasi' => $request->nomor_registrasi,
            'nomor_telepon' => $request->nomor_telepon,
            'aktif' => $request->has('aktif'),
        ];

        if ($request->filled('kata_sandi')) {
            $data['kata_sandi'] = Hash::make($request->kata_sandi);
        }

        $asesor->update($data);

        LogAktivitas::catat('Ubah Data Asesor', 'Admin memperbarui akun asesor: ' . $asesor->nama_lengkap);

        return back()->with('sukses', 'Data Asesor ' . $asesor->nama_lengkap . ' berhasil diperbarui.');
    }

    public function hapusAsesor($id)
    {
        $asesor = Pengguna::where('peran', 'asesor')->findOrFail($id);
        $nama = $asesor->nama_lengkap;
        $asesor->delete();

        LogAktivitas::catat('Hapus Akun Asesor', 'Admin menghapus akun asesor: ' . $nama);

        return back()->with('sukses', 'Akun Asesor ' . $nama . ' berhasil dihapus.');
    }

    /**
     * Endpoint polling ringan untuk mendeteksi pengajuan pendaftaran APL terbaru
     */
    public function cekPendaftaranTerbaru(Request $request)
    {
        $lastId = (int) $request->get('last_id', 0);
        $lastHash = (string) $request->get('last_hash', '');

        $latest = PendaftaranAsesi::with(['asesi', 'skema'])->latest('updated_at')->first();
        $latestId = PendaftaranAsesi::max('id') ?? 0;
        $currentHash = $latest ? md5($latest->id . '_' . $latest->status_pendaftaran . '_' . $latest->updated_at) : '';

        $hasNew = false;
        if (!empty($lastHash)) {
            $hasNew = ($currentHash !== $lastHash);
        } elseif ($lastId > 0) {
            $hasNew = ($latestId > $lastId);
        }

        return response()->json([
            'latest_id' => $latestId,
            'current_hash' => $currentHash,
            'has_new' => $hasNew,
            'nama_asesi' => $latest?->asesi?->nama_lengkap ?? 'Asesi',
            'nama_skema' => $latest?->skema?->nama_skema ?? 'Skema',
        ]);
    }

    /**
     * Validasi Dokumen FR.MAPA.01 oleh Admin LSP
     */
    public function validasiMapa01(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            abort(403, 'Hanya Administrator atau Super Admin yang dapat memvalidasi dokumen FR.MAPA.01.');
        }

        $request->validate([
            'validator_nama' => 'required|string|max:255',
            'validator_nomor_met' => 'required|string|max:100',
            'catatan_validasi' => 'nullable|string|max:1000',
            'tanda_tangan_admin_base64' => 'nullable|string',
        ], [
            'validator_nama.required' => 'Nama Validator (Admin LSP) wajib diisi.',
            'validator_nomor_met.required' => 'Nomor Registrasi / NIP Validator wajib diisi.',
        ]);

        $mapa01 = null;

        // A. Jika eksplisit validasi Master Skema
        if ($request->boolean('is_master_mode') || ($request->filled('skema_id') && ($id == $request->skema_id || empty($id)))) {
            $skemaId = $request->skema_id ?: $id;
            $mapa01 = Mapa01::where('skema_id', $skemaId)->whereNull('pendaftaran_id')->first();
            if (!$mapa01) {
                $skema = SkemaSertifikasi::find($skemaId);
                if ($skema) {
                    $mapa01 = app(\App\Services\MapaWorkflowService::class)->getOrCreateMasterMapa01($skema, $user->id);
                }
            }
        }

        // B. Jika ada pendaftaran_id di request
        if (!$mapa01 && $request->filled('pendaftaran_id')) {
            $pendaftaran = PendaftaranAsesi::find($request->pendaftaran_id);
            if ($pendaftaran) {
                $mapa01 = app(\App\Services\MapaWorkflowService::class)->getOrCreateMapa01($pendaftaran, $user->id);
            }
        }

        // C. Jika bukan master mode dan $id cocok dengan ID PendaftaranAsesi
        if (!$mapa01 && !$request->boolean('is_master_mode')) {
            $pendaftaran = PendaftaranAsesi::find($id);
            if ($pendaftaran) {
                $mapa01 = app(\App\Services\MapaWorkflowService::class)->getOrCreateMapa01($pendaftaran, $user->id);
            }
        }

        // D. Jika belum ditemukan, cek apakah $id adalah primary key dari Mapa01
        if (!$mapa01) {
            $mapa01 = Mapa01::find($id);
        }

        // E. Cek jika $id adalah pendaftaran_id pada Mapa01
        if (!$mapa01) {
            $mapa01 = Mapa01::where('pendaftaran_id', $id)->first();
        }

        // F. Fallback Master Skema jika ada skema_id
        if (!$mapa01 && $request->filled('skema_id')) {
            $mapa01 = Mapa01::where('skema_id', $request->skema_id)->whereNull('pendaftaran_id')->first();
            if (!$mapa01) {
                $skema = SkemaSertifikasi::find($request->skema_id);
                if ($skema) {
                    $mapa01 = app(\App\Services\MapaWorkflowService::class)->getOrCreateMasterMapa01($skema, $user->id);
                }
            }
        }

        if (!$mapa01) {
            return back()->with('error', 'Dokumen FR.MAPA.01 tidak ditemukan.');
        }

        // Cek jika master MAPA 01 untuk skema ini sudah tervalidasi
        $masterSudahValid = Mapa01::where('skema_id', $mapa01->skema_id)
            ->whereNull('pendaftaran_id')
            ->where(function($q) {
                $q->where('penyusun_validator_tabel->validator_1->status_validasi', 'tervalidasi')
                  ->orWhereNotNull('penyusun_validator_tabel->validator_1->ttd');
            })
            ->first();

        if ($mapa01->pendaftaran_id && $masterSudahValid) {
            $mapa01->penyusun_validator_tabel = $masterSudahValid->penyusun_validator_tabel;
            $mapa01->tanda_tangan_asesor = $mapa01->tanda_tangan_asesor ?: $masterSudahValid->tanda_tangan_asesor;
            $mapa01->tanggal_ttd_asesor = $mapa01->tanggal_ttd_asesor ?: $masterSudahValid->tanggal_ttd_asesor;
            $mapa01->status_mapa = 'selesai';
            $mapa01->save();

            $valMasterTtd = $masterSudahValid->penyusun_validator_tabel['validator_1']['ttd'] ?? ($user->tanda_tangan ?? null);
            PendaftaranAsesi::where('id', $mapa01->pendaftaran_id)->update([
                'tanda_tangan_admin' => $valMasterTtd,
                'tanggal_ttd_admin' => now(),
            ]);

            return back()->with('info', 'Dokumen FR.MAPA.01 untuk skema sertifikasi ini sudah berstatus tervalidasi melalui Master Skema.');
        }

        $ttdAdmin = $request->tanda_tangan_admin_base64 ?: $user->tanda_tangan;
        if ($request->tanda_tangan_admin_base64) {
            $user->update(['tanda_tangan' => $request->tanda_tangan_admin_base64]);
        }

        if (empty($ttdAdmin)) {
            $adminName = $user->nama_lengkap ?: 'Administrator LSP';
            $adminSvg = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="24" fill="%23065f46">' . urlencode($adminName) . '</text></svg>';
            $targetId = $mapa01->pendaftaran_id;
            $prefix = $targetId ? 'mapa01_val' : ('mapa01_master_val_' . $mapa01->skema_id);
            $ttdAdmin = app(\App\Services\MapaWorkflowService::class)->saveSignatureFile($adminSvg, $targetId, $prefix) ?: $adminSvg;
        }

        $existingTable = $mapa01->penyusun_validator_tabel ?: [];
        $existingTable['validator_1'] = [
            'nama' => $request->validator_nama ?: ($user->nama_lengkap ?: 'Administrator LSP SMKN 1 Gunungputri'),
            'nomor_met' => $request->validator_nomor_met ?: ($user->nomor_registrasi ?: 'NIP/REG.ADM.LSP.001'),
            'ttd' => $ttdAdmin,
            'ttd_tanggal' => now()->format('d/m/Y'),
            'status_validasi' => 'tervalidasi',
            'catatan' => $request->catatan_validasi ?: null,
        ];

        // Pastikan juga jika asesor belum bertanda tangan, diisi secara otomatis
        if (empty($mapa01->tanda_tangan_asesor) || empty($existingTable['penyusun_1']['ttd'])) {
            $asesorModel = $mapa01->asesor ?: Pengguna::where('peran', 'asesor')->where('skema_id', $mapa01->skema_id)->first();
            $asesorTtd = $asesorModel?->tanda_tangan;
            if (empty($asesorTtd)) {
                $namaAsesor = $asesorModel?->nama_lengkap ?: ($existingTable['penyusun_1']['nama'] ?? 'Asesor Penguji');
                $svgSig = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="26" fill="%231e3a8a">' . urlencode($namaAsesor) . '</text></svg>';
                $targetId = $mapa01->pendaftaran_id;
                $prefix = $targetId ? 'mapa01' : ('mapa01_master_' . $mapa01->skema_id);
                $asesorTtd = app(\App\Services\MapaWorkflowService::class)->saveSignatureFile($svgSig, $targetId, $prefix) ?: $svgSig;
            }
            $mapa01->tanda_tangan_asesor = $asesorTtd;
            $mapa01->tanggal_ttd_asesor = $mapa01->tanggal_ttd_asesor ?: now();
            $existingTable['penyusun_1']['nama'] = $existingTable['penyusun_1']['nama'] ?? ($asesorModel?->nama_lengkap ?? 'Asesor Penguji');
            $existingTable['penyusun_1']['nomor_met'] = $existingTable['penyusun_1']['nomor_met'] ?? ($asesorModel?->nomor_registrasi ?? 'MET.000.001222 2026');
            $existingTable['penyusun_1']['ttd'] = $asesorTtd;
            $existingTable['penyusun_1']['ttd_tanggal'] = $existingTable['penyusun_1']['ttd_tanggal'] ?? now()->format('d/m/Y');
        }

        $mapa01->penyusun_validator_tabel = $existingTable;
        $mapa01->status_mapa = 'selesai';
        $mapa01->save();

        if ($mapa01->pendaftaran_id) {
            PendaftaranAsesi::where('id', $mapa01->pendaftaran_id)->update([
                'tanda_tangan_admin' => $ttdAdmin,
                'tanggal_ttd_admin' => now(),
            ]);
        } else {
            // Master Skema: Sinkronkan ke seluruh pendaftaran asesi pada skema ini
            Mapa01::where('skema_id', $mapa01->skema_id)
                ->whereNotNull('pendaftaran_id')
                ->update([
                    'penyusun_validator_tabel' => $existingTable,
                    'tanda_tangan_asesor' => $mapa01->tanda_tangan_asesor,
                    'tanggal_ttd_asesor' => $mapa01->tanggal_ttd_asesor,
                    'status_mapa' => 'selesai',
                ]);

            PendaftaranAsesi::where('skema_id', $mapa01->skema_id)->update([
                'tanda_tangan_admin' => $ttdAdmin,
                'tanggal_ttd_admin' => now(),
            ]);
        }

        $skemaNama = $mapa01->skema?->nama_skema ?? 'Skema Sertifikasi';
        $targetInfo = $mapa01->pendaftaran_id ? "Pendaftaran #{$mapa01->pendaftaran?->nomor_pendaftaran}" : "Master Skema {$skemaNama}";
        LogAktivitas::catat('Validasi FR.MAPA.01', "Admin {$user->nama_lengkap} memvalidasi dokumen FR.MAPA.01 untuk {$targetInfo}");

        $matriksUnits = (array) ($mapa01->rencana_unit_matriks ?? []);
        $totalUnits = $mapa01->skema?->unitKompetensi?->count() ?? 0;
        $isMatrixIncomplete = ($totalUnits > 0 && count($matriksUnits) < $totalUnits);
        $isPendekatanEmpty = empty($mapa01->pendekatan_asesi);

        if ($isMatrixIncomplete || $isPendekatanEmpty) {
            return back()->with('sukses', 'Dokumen FR.MAPA.01 berhasil divalidasi dan disahkan oleh Administrator LSP!')
                         ->with('warning', 'Pemberitahuan Validasi: Terdapat komponen formulir (pendekatan kandidat atau unit kompetensi) yang belum terisi maksimal oleh Asesor.');
        }

        return back()->with('sukses', 'Dokumen FR.MAPA.01 berhasil divalidasi dan disahkan oleh Administrator LSP!');
    }
}

