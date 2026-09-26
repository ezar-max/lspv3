<?php

namespace App\Http\Controllers;

use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\PenilaianAsesmen;
use App\Models\RekomendasiAsesmen;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\BeritaAcara;
use App\Models\LogAktivitas;
use App\Models\SkemaSertifikasi;
use App\Models\Pengguna;
use App\Models\MasterAk01;
use App\Models\MasterAk07;
use App\Models\SchemeMasterInstrument;
use App\Notifications\SystemAlert;
use App\Services\MapaWorkflowService;
use Illuminate\Http\Request;

class AsesorController extends Controller
{
    protected MapaWorkflowService $mapaService;

    public function __construct(MapaWorkflowService $mapaService)
    {
        $this->mapaService = $mapaService;
    }

    /** Keep only MAPA matrix entries that belong to the active registration's scheme. */
    private function matriksPetaMilikSkema(PendaftaranAsesi $pendaftaran, array $matriks): array
    {
        return $this->mapaService->sanitizeMatrix($pendaftaran, $matriks);
    }

    public function dashboard()
    {
        JadwalAsesmen::syncAllStatuses();
        $asesorId = auth()->id();

        // 1. Daftar Jadwal Penugasan
        $jadwalList = JadwalAsesmen::with(['skema', 'pendaftaranAsesi.asesi'])
            ->where('asesor_id', $asesorId)
            ->latest('tanggal_uji')
            ->get();

        // 2. Daftar Seluruh Asesi Terkait
        $semuaAsesi = PendaftaranAsesi::with(['asesi', 'skema', 'jadwal', 'rekomendasi', 'mapa01', 'mapa02'])
            ->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            })
            ->latest('updated_at')
            ->get();

        $totalAsesi = $semuaAsesi->count();

        // 3. Antrean Tugas Verifikasi Mendesak (APL.02)
        $antreanVerifikasi = $semuaAsesi->filter(function($p) {
            return in_array($p->status_apl02, ['submitted', 'under_review', 'revision_requested', 'revision']);
        })->values()->take(6);

        $totalPendingApl02 = $semuaAsesi->filter(fn($p) => in_array($p->status_apl02, ['submitted', 'under_review']))->count();
        $totalPendingAk01 = 0;
        $totalRevisiApl02 = $semuaAsesi->filter(fn($p) => in_array($p->status_apl02, ['revision_requested', 'revision']))->count();
        $totalPerluMapa = $semuaAsesi->filter(fn($p) => $p->status_apl02 === 'approved' && (!$p->mapa01 || !$p->mapa02))->count();
        $totalSiapUji = $semuaAsesi->filter(fn($p) => $p->status_apl02 === 'approved' && empty($p->rekomendasi))->count();

        // 4. Rekapitulasi Penilaian & Rekomendasi Terakhir
        $totalPenilaian = RekomendasiAsesmen::where('asesor_id', $asesorId)->count();
        $rekomendasiTerbaru = RekomendasiAsesmen::with(['pendaftaran.asesi', 'pendaftaran.skema'])
            ->where('asesor_id', $asesorId)
            ->latest('tanggal_rekomendasi')
            ->take(5)
            ->get();

        $latestPendaftaran = $semuaAsesi->first();
        $pendaftaranItemHash = $latestPendaftaran ? ($latestPendaftaran->id . '_' . $latestPendaftaran->status_pendaftaran . '_' . $latestPendaftaran->status_apl02 . '_' . $latestPendaftaran->status_ak01 . '_' . $latestPendaftaran->updated_at) : '';
        $pendaftaranMax = $semuaAsesi->max('updated_at');
        $jadwalMax = $jadwalList->max('updated_at');
        $rekomMax = $rekomendasiTerbaru->max('updated_at');
        $initialHash = md5("{$pendaftaranItemHash}_{$pendaftaranMax}_{$totalAsesi}_{$jadwalMax}_{$jadwalList->count()}_{$rekomMax}_{$totalPenilaian}");

        return view('asesor.dashboard-asesor', compact(
            'jadwalList',
            'totalAsesi',
            'totalPendingApl02',
            'totalPendingAk01',
            'totalRevisiApl02',
            'totalPerluMapa',
            'totalSiapUji',
            'totalPenilaian',
            'antreanVerifikasi',
            'rekomendasiTerbaru',
            'initialHash'
        ));
    }

    public function dashboardHeartbeat(Request $request)
    {
        $asesorId = auth()->id();

        // 1. Pendaftaran asesi (timestamp max & count)
        $pendaftaranQuery = PendaftaranAsesi::where(function($q) use ($asesorId) {
            $q->where('asesor_id', $asesorId)
              ->orWhereHas('jadwal', function($j) use ($asesorId) {
                  $j->where('asesor_id', $asesorId);
              });
        });
        $pendaftaranMax = $pendaftaranQuery->max('updated_at');
        $pendaftaranCount = $pendaftaranQuery->count();

        // 2. Jadwal asesmen
        $jadwalQuery = JadwalAsesmen::where('asesor_id', $asesorId);
        $jadwalMax = $jadwalQuery->max('updated_at');
        $jadwalCount = $jadwalQuery->count();

        // 3. Rekomendasi
        $rekomQuery = RekomendasiAsesmen::where('asesor_id', $asesorId);
        $rekomMax = $rekomQuery->max('updated_at');
        $rekomCount = $rekomQuery->count();

        // Ambil info pendaftaran / asesi binaan terbaru untuk tampilan toast & hashing
        $latestPendaftaran = PendaftaranAsesi::with(['asesi', 'skema'])
            ->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            })
            ->latest('updated_at')
            ->first();

        $pendaftaranItemHash = $latestPendaftaran ? ($latestPendaftaran->id . '_' . $latestPendaftaran->status_pendaftaran . '_' . $latestPendaftaran->status_apl02 . '_' . $latestPendaftaran->status_ak01 . '_' . $latestPendaftaran->updated_at) : '';
        $currentHash = md5("{$pendaftaranItemHash}_{$pendaftaranMax}_{$pendaftaranCount}_{$jadwalMax}_{$jadwalCount}_{$rekomMax}_{$rekomCount}");
        $clientHash = $request->query('hash') ?: $request->query('last_hash');

        $changed = !empty($clientHash) && $clientHash !== $currentHash;

        return response()->json([
            'status' => 'ok',
            'changed' => $changed,
            'has_new' => $changed,
            'hash' => $currentHash,
            'current_hash' => $currentHash,
            'nama_asesi' => $latestPendaftaran?->asesi?->nama_lengkap ?? 'Asesi Binaan',
            'nama_skema' => $latestPendaftaran?->skema?->nama_skema ?? '',
            'keterangan' => 'Terdapat pembaruan data asesi atau berkas terbaru.',
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function jadwal(Request $request)
    {
        JadwalAsesmen::syncAllStatuses();
        $asesorId = auth()->id();
        $statusFilter = $request->get('status');
        $search = $request->get('q');

        $query = JadwalAsesmen::with(['skema', 'pendaftaranAsesi'])
            ->where('asesor_id', $asesorId);

        if (!empty($statusFilter)) {
            $query->where('status_jadwal', $statusFilter);
        }

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('kode_jadwal', 'like', "%{$search}%")
                    ->orWhere('nama_tuk', 'like', "%{$search}%")
                    ->orWhereHas('skema', function ($skemaQ) use ($search) {
                        $skemaQ->where('nama_skema', 'like', "%{$search}%")
                               ->orWhere('kode_skema', 'like', "%{$search}%");
                    });
            });
        }

        $jadwalList = $query->orderBy('tanggal_uji', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Statistics for summary badges/cards
        $countSemua = JadwalAsesmen::where('asesor_id', $asesorId)->count();
        $countBerlangsung = JadwalAsesmen::where('asesor_id', $asesorId)->where('status_jadwal', 'berlangsung')->count();
        $countTerjadwal = JadwalAsesmen::where('asesor_id', $asesorId)->where('status_jadwal', 'terjadwal')->count();
        $countSelesai = JadwalAsesmen::where('asesor_id', $asesorId)->where('status_jadwal', 'selesai')->count();

        return view('asesor.jadwal-asesmen', compact(
            'jadwalList',
            'countSemua',
            'countBerlangsung',
            'countTerjadwal',
            'countSelesai',
            'statusFilter',
            'search'
        ));
    }

    public function daftarPeserta(Request $request)
    {
        JadwalAsesmen::syncAllStatuses();
        $jadwalId = $request->get('jadwal_id');
        $statusFilter = $request->get('status');
        $keyword = $request->get('q');
        $asesorId = auth()->id();

        $query = PendaftaranAsesi::with(['asesi.profilAsesi', 'skema', 'jadwal', 'rekomendasi', 'asesor', 'mapa01', 'mapa02', 'ak07Adjustment'])
            ->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            });

        if ($jadwalId) {
            $query->where('jadwal_id', $jadwalId);
        }

        if ($statusFilter) {
            if ($statusFilter === 'pending_apl02') {
                $query->whereIn('status_apl02', ['submitted', 'under_review']);
            } elseif ($statusFilter === 'revisi_apl02') {
                $query->whereIn('status_apl02', ['revision_requested', 'revision']);
            } elseif ($statusFilter === 'approved_apl02') {
                $query->where('status_apl02', 'approved');
            } elseif ($statusFilter === 'pending_ak01') {
                $query->where('status_apl02', 'approved')
                      ->where(function($q) {
                          $q->whereNull('tanda_tangan_asesor_ak01')
                            ->orWhere('status_ak01', 'disetujui_asesi')
                            ->orWhere('status_ak01', 'belum');
                      });
            } elseif ($statusFilter === 'selesai') {
                $query->whereHas('rekomendasi');
            }
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nomor_pendaftaran', 'like', "%{$keyword}%")
                  ->orWhereHas('asesi', function($a) use ($keyword) {
                      $a->where('nama_lengkap', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhereHas('profilAsesi', function($pa) use ($keyword) {
                            $pa->where('nik', 'like', "%{$keyword}%")
                               ->orWhere('nama_sekolah_instansi', 'like', "%{$keyword}%");
                        });
                  });
            });
        }

        $pesertaList = $query->latest('updated_at')->paginate(10)->withQueryString();
        $jadwalOption = JadwalAsesmen::with('skema')->where('asesor_id', auth()->id())->get();

        // Calculate quick summary metrics
        $countSemua = PendaftaranAsesi::where('asesor_id', $asesorId)->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $asesorId))->count();
        $countPendingApl02 = PendaftaranAsesi::where(function($q) use ($asesorId) {
            $q->where('asesor_id', $asesorId)->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $asesorId));
        })->whereIn('status_apl02', ['submitted', 'under_review'])->count();
        $countPendingAk01 = PendaftaranAsesi::where(function($q) use ($asesorId) {
            $q->where('asesor_id', $asesorId)->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $asesorId));
        })->where('status_apl02', 'approved')->where('status_ak01', 'disetujui_asesi')->count();

        return view('asesor.daftar-peserta', compact(
            'pesertaList', 
            'jadwalOption', 
            'jadwalId', 
            'statusFilter', 
            'keyword',
            'countSemua',
            'countPendingApl02',
            'countPendingAk01'
        ));
    }

    public function inputPenilaian($pendaftaranId)
    {
        $asesorId = auth()->id();

        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'dokumen',
            'penilaian',
            'rekomendasi',
            'jawabanApl02',
            'verifikasiKukApl02',
            'buktiApl02.dokumenAsesi',
            'asesor',
            'mapa01',
            'mapa02'
        ])
        ->where(function($q) use ($asesorId) {
            $q->where('asesor_id', $asesorId)
              ->orWhereHas('jadwal', function($j) use ($asesorId) {
                  $j->where('asesor_id', $asesorId);
              });
        })
        ->findOrFail($pendaftaranId);

        // Jika status APL-02 masih 'submitted', update menjadi 'under_review' saat asesor membuka halaman
        if ($pendaftaran->status_apl02 === 'submitted') {
            $pendaftaran->update(['status_apl02' => 'under_review']);
        }

        $jawabanMap = $pendaftaran->jawabanApl02->keyBy('elemen_id');
        $verifikasiKukMap = $pendaftaran->verifikasiKukApl02->keyBy('kuk_id');
        $penilaianUnitMap = $pendaftaran->penilaian->keyBy('unit_id');
        $buktiApl02Map = $pendaftaran->buktiApl02 ? $pendaftaran->buktiApl02->groupBy('elemen_id') : collect([]);

        $isLocked = $pendaftaran->isApl02Approved();

        // Hitung total KUK, KUK terverifikasi, dan mapping unit-KUK
        $totalKukCount = 0;
        $initialVerifiedKuk = [];
        $initialCatatanKuk = [];
        $unitKukMap = [];
        $unitDecisions = [];

        if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
            foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                $unitKukMap[$unit->id] = [];
                $allKukInUnitAreK = true;
                $hasAnyKukInUnit = false;
                $hasAnyUnverifiedInUnit = false;
                $hasAnyBkInUnit = false;

                foreach ($unit->elemenKompetensi as $elemen) {
                    $elemJawaban = $jawabanMap->get($elemen->id);
                    $elemKlaim = $elemJawaban ? $elemJawaban->nilai_kompetensi : 'K';

                    foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                        $totalKukCount++;
                        $hasAnyKukInUnit = true;
                        $unitKukMap[$unit->id][] = $kuk->id;
                        $vKuk = $verifikasiKukMap->get($kuk->id);

                        if ($vKuk && $vKuk->is_verified !== null) {
                            $statusKuk = $vKuk->is_verified ? 'K' : 'BK';
                            $initialCatatanKuk[$kuk->id] = $vKuk->catatan_asesor ?? '';
                        } else {
                            // Default: belum dipilih satupun oleh asesor (null)
                            $statusKuk = null;
                            $initialCatatanKuk[$kuk->id] = '';
                        }

                        $initialVerifiedKuk[$kuk->id] = $statusKuk;
                        if ($statusKuk === 'K') {
                            // K
                        } elseif ($statusKuk === 'BK') {
                            $hasAnyBkInUnit = true;
                            $allKukInUnitAreK = false;
                        } else {
                            $hasAnyUnverifiedInUnit = true;
                            $allKukInUnitAreK = false;
                        }
                    }
                }

                // Keputusan Unit: gunakan dari database jika ada, atau computed rule BNSP
                $pUnit = $penilaianUnitMap->get($unit->id);
                if ($pUnit && in_array($pUnit->nilai_kompetensi, ['K', 'BK'])) {
                    $unitDecisions[$unit->id] = $pUnit->nilai_kompetensi;
                } else {
                    if ($hasAnyBkInUnit) {
                        $unitDecisions[$unit->id] = 'BK';
                    } elseif ($hasAnyKukInUnit && $allKukInUnitAreK && !$hasAnyUnverifiedInUnit) {
                        $unitDecisions[$unit->id] = 'K';
                    } else {
                        $unitDecisions[$unit->id] = null;
                    }
                }
            }
        }

        return view('asesor.input-penilaian', compact(
            'pendaftaran', 
            'jawabanMap', 
            'verifikasiKukMap', 
            'penilaianUnitMap', 
            'buktiApl02Map', 
            'isLocked',
            'totalKukCount',
            'initialVerifiedKuk',
            'initialCatatanKuk',
            'unitKukMap',
            'unitDecisions'
        ));
    }

    public function simpanPenilaian(Request $request, $pendaftaranId)
    {
        $asesorId = auth()->id();

        $pendaftaran = PendaftaranAsesi::with([
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'rekomendasi',
            'jawabanApl02',
            'verifikasiKukApl02'
        ])
        ->where(function($q) use ($asesorId) {
            $q->where('asesor_id', $asesorId)
              ->orWhereHas('jadwal', function($j) use ($asesorId) {
                  $j->where('asesor_id', $asesorId);
              });
        })
        ->findOrFail($pendaftaranId);

        // KUNCI: Jika sudah disetujui (Approved) atau Ditolak, tidak bisa diubah lagi!
        if ($pendaftaran->isApl02Approved()) {
            return back()->with('error', 'Penilaian & Pengesahan FR.APL.02 untuk peserta ini telah disetujui (Approved) dan dikunci.');
        }
        if ($pendaftaran->isApl02Rejected()) {
            return back()->with('error', 'Penilaian FR.APL.02 untuk peserta ini telah ditolak dan dikunci.');
        }

        $actionType = $request->input('action_type');
        $rekomendasiStatus = $request->input('rekomendasi_asesor_status');

        $isDitolak = ($actionType === 'reject' || $rekomendasiStatus === 'ditolak');
        $isMintaRevisi = !$isDitolak && ($actionType === 'revision' || $rekomendasiStatus === 'tidak_dapat_dilanjutkan');

        $rules = [
            'nilai' => 'required|array',
            'nilai.*' => 'required|in:K,BK',
            'verifikasi_kuk' => 'nullable|array',
            'catatan_kuk' => 'nullable|array',
            'rekomendasi_asesor_status' => 'required|in:dapat_dilanjutkan,tidak_dapat_dilanjutkan,ditolak',
            'tanda_tangan_asesor' => 'nullable|string',
        ];

        if ($isMintaRevisi || $isDitolak) {
            $rules['catatan_rekomendasi'] = 'required|string';
        } else {
            $rules['catatan_rekomendasi'] = 'nullable|string';
        }

        $request->validate($rules, [
            'nilai.required' => 'Keputusan seluruh Unit Kompetensi wajib dipilih (K atau BK).',
            'nilai.*.required' => 'Keputusan setiap Unit Kompetensi wajib dipilih (Kompeten atau Belum Kompeten).',
            'nilai.*.in' => 'Keputusan Unit Kompetensi harus berupa Kompeten (K) atau Belum Kompeten (BK).',
            'rekomendasi_asesor_status.required' => 'Rekomendasi asesor wajib ditentukan.',
            'rekomendasi_asesor_status.in' => 'Pilihan rekomendasi asesor tidak valid.',
            'catatan_rekomendasi.required' => $isDitolak ? 'Alasan/catatan penolakan wajib diisi.' : 'Catatan revisi wajib diisi agar asesi mengetahui bagian yang perlu diperbaiki.',
        ]);

        // VALIDASI KELENGKAPAN KEPUTUSAN SELURUH UNIT
        if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
            foreach ($pendaftaran->skema->unitKompetensi as $idxUnit => $unit) {
                $valUnit = $request->input("nilai.{$unit->id}");
                if (empty($valUnit) || !in_array($valUnit, ['K', 'BK'])) {
                    return back()->withInput()->with('error', "Keputusan untuk Unit " . ($idxUnit + 1) . " ({$unit->kode_unit}) belum dipilih. Silakan tentukan keputusan Kompeten (K) atau Belum Kompeten (BK).");
                }
            }
        }

        // Jika akan di-ACC (approve), pastikan tidak ada KUK yang belum diverifikasi atau bernilai BK
        if (!$isDitolak && !$isMintaRevisi) {
            $hasBkKuk = false;
            $hasUnverifiedKuk = false;
            $verifikasiKukInput = $request->input('verifikasi_kuk', []);

            if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
                foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                    foreach ($unit->elemenKompetensi as $elemen) {
                        foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                            $valKuk = $verifikasiKukInput[$kuk->id] ?? null;
                            if ($valKuk === null || $valKuk === '') {
                                $hasUnverifiedKuk = true;
                                break 3;
                            }
                            if ($valKuk === 'BK' || $valKuk === '0' || $valKuk === 0 || $valKuk === false) {
                                $hasBkKuk = true;
                                break 3;
                            }
                        }
                    }
                }
            }

            if ($hasUnverifiedKuk) {
                return back()->withInput()->with('error', 'Formulir FR.APL.02 tidak dapat disetujui (ACC) karena masih terdapat butir KUK yang belum diverifikasi (K/BK). Pastikan seluruh butir KUK dinilai Kompeten (K).');
            }

            if ($hasBkKuk || in_array('BK', $request->input('nilai', []))) {
                return back()->withInput()->with('error', 'Formulir FR.APL.02 tidak dapat disetujui (ACC) karena masih terdapat butir KUK yang dinilai Belum Kompeten (BK). Silakan gunakan tombol Revisi.');
            }
        }

        // VALIDASI TANDA TANGAN ASESOR (WAJIB)
        $ttdAsesor = $request->tanda_tangan_asesor ?: $pendaftaran->tanda_tangan_asesor;
        if (empty($ttdAsesor)) {
            return back()->withInput()->with('error', 'Gagal memverifikasi: Tanda Tangan Asesor Penguji wajib dibubuhkan sebelum menyimpan keputusan FR.APL.02.');
        }

        $oldStatus = $pendaftaran->status_apl02;

        \Illuminate\Support\Facades\DB::transaction(function () use ($pendaftaran, $request, $asesorId, $ttdAsesor, $isMintaRevisi, $isDitolak) {
            // 1. Simpan Keputusan Per Unit Kompetensi
            if ($request->nilai) {
                $unitIdsSkema = $pendaftaran->skema->unitKompetensi->pluck('id')->map(fn ($id) => (string) $id)->all();
                foreach ($request->nilai as $unitId => $nilaiKompetensi) {
                    if (!in_array((string) $unitId, $unitIdsSkema, true)) {
                        continue;
                    }
                    PenilaianAsesmen::updateOrCreate(
                        [
                            'pendaftaran_id' => $pendaftaran->id,
                            'unit_id' => $unitId,
                        ],
                        [
                            'asesor_id' => $asesorId,
                            'nilai_kompetensi' => $nilaiKompetensi,
                            'catatan_asesor' => $request->catatan_unit[$unitId] ?? null,
                        ]
                    );
                }
            }

            // 2. Simpan Verifikasi Per Butir KUK
            $verifikasiInput = $request->input('verifikasi_kuk', []);
            $catatanKukInput = $request->input('catatan_kuk', []);
            $existingVerifikasi = $pendaftaran->verifikasiKukApl02->keyBy('kuk_id');
            $existingJawaban = $pendaftaran->jawabanApl02->keyBy('elemen_id');

            if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
                foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                    foreach ($unit->elemenKompetensi as $elemen) {
                        $elemKlaim = $existingJawaban->get($elemen->id)?->nilai_kompetensi ?? 'K';

                        foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                            $vKukExisting = $existingVerifikasi->get($kuk->id);
                            $klaimKuk = $vKukExisting ? $vKukExisting->nilai_kompetensi : $elemKlaim;
                            $rawVal = $verifikasiInput[$kuk->id] ?? null;
                            if ($rawVal === 'K' || $rawVal === '1' || $rawVal === 1 || $rawVal === true) {
                                $isVerified = true;
                            } elseif ($rawVal === 'BK' || $rawVal === '0' || $rawVal === 0 || $rawVal === false) {
                                $isVerified = false;
                            } else {
                                $isVerified = null;
                            }
                            $catatanKuk = $catatanKukInput[$kuk->id] ?? null;

                            \App\Models\VerifikasiKukApl02::updateOrCreate(
                                [
                                    'pendaftaran_id' => $pendaftaran->id,
                                    'kuk_id' => $kuk->id,
                                ],
                                [
                                    'elemen_id' => $elemen->id,
                                    'nilai_kompetensi' => $klaimKuk,
                                    'is_verified' => $isVerified,
                                    'catatan_asesor' => $catatanKuk,
                                ]
                            );
                        }
                    }
                }
            }

            // 3. Simpan Rekomendasi & Workflow Status
            // 3. Simpan Rekomendasi & Workflow Status FR.APL.02
            // CATATAN: FR.APL.02 adalah Asesmen Mandiri / Verifikasi Berkas (Tahap 2).
            // Hasilnya adalah "Dapat Dilanjutkan" atau "Tidak Dapat Dilanjutkan" ke Uji Kompetensi.
            // Keputusan Akhir Kelulusan (Kompeten/Belum Kompeten) HANYA ditetapkan setelah Uji Kompetensi Hari H!
            if ($isDitolak) {
                // KONDISI: Asesor Menolak FR.APL.02
                $pendaftaran->update([
                    'status_apl02' => 'rejected',
                    'rekomendasi_asesor_status' => 'ditolak',
                    'catatan_peninjauan_asesor' => $request->catatan_rekomendasi,
                    'tanda_tangan_asesor' => $ttdAsesor,
                    'tanggal_ttd_asesor' => now(),
                ]);

                RekomendasiAsesmen::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id],
                    [
                        'asesor_id' => $asesorId,
                        'keputusan' => 'belum_kompeten',
                        'catatan_rekomendasi' => $request->catatan_rekomendasi,
                        'tanggal_rekomendasi' => now(),
                        'tanda_tangan_asesor' => $ttdAsesor,
                    ]
                );

                LogAktivitas::catat('Penolakan FR.APL.02', 'Asesor menolak FR.APL.02 untuk peserta #' . $pendaftaran->nomor_pendaftaran);
            } elseif ($isMintaRevisi) {
                // KONDISI: Asesor Meminta Revisi
                $pendaftaran->update([
                    'status_apl02' => 'revision',
                    'rekomendasi_asesor_status' => 'tidak_dapat_dilanjutkan',
                    'catatan_peninjauan_asesor' => $request->catatan_rekomendasi,
                    'tanda_tangan_asesor' => $ttdAsesor,
                    'tanggal_ttd_asesor' => now(),
                ]);

                RekomendasiAsesmen::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id],
                    [
                        'asesor_id' => $asesorId,
                        'keputusan' => 'belum_kompeten',
                        'catatan_rekomendasi' => $request->catatan_rekomendasi,
                        'tanggal_rekomendasi' => now(),
                        'tanda_tangan_asesor' => $ttdAsesor,
                    ]
                );

                LogAktivitas::catat('Permintaan Revisi FR.APL.02', 'Asesor meminta revisi FR.APL.02 untuk peserta #' . $pendaftaran->nomor_pendaftaran);
            } else {
                // KONDISI: Asesor Menyetujui FR.APL.02 (Approved) -> BUKA AK.01
                $pendaftaran->update([
                    'status_apl02' => 'approved',
                    'rekomendasi_asesor_status' => 'dapat_dilanjutkan',
                    'catatan_peninjauan_asesor' => $request->catatan_rekomendasi,
                    'tanda_tangan_asesor' => $ttdAsesor,
                    'tanggal_ttd_asesor' => now(),
                ]);

                RekomendasiAsesmen::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id],
                    [
                        'asesor_id' => $asesorId,
                        'keputusan' => 'kompeten',
                        'catatan_rekomendasi' => $request->catatan_rekomendasi,
                        'tanggal_rekomendasi' => now(),
                        'tanda_tangan_asesor' => $ttdAsesor,
                    ]
                );

                LogAktivitas::catat('Persetujuan FR.APL.02', 'Asesor menyetujui FR.APL.02 untuk peserta #' . $pendaftaran->nomor_pendaftaran);
            }
        });

        // 4. Trigger Persistent Laravel Notification ke Asesi
        if ($isDitolak && $oldStatus !== 'rejected') {
            $pendaftaran->asesi?->notify(new \App\Notifications\APL02Rejected($pendaftaran->id, $request->catatan_rekomendasi));
        } elseif ($isMintaRevisi && $oldStatus !== 'revision') {
            $pendaftaran->asesi?->notify(new \App\Notifications\APL02NeedsRevision($pendaftaran->id, $request->catatan_rekomendasi));
        } elseif (!$isDitolak && !$isMintaRevisi && $oldStatus !== 'approved') {
            $pendaftaran->asesi?->notify(new \App\Notifications\APL02Approved($pendaftaran->id));
        }

        if ($isDitolak) {
            return redirect()->route('asesor.daftar-peserta')
                ->with('sukses', 'Permohonan FR.APL.02 berhasil ditolak beserta alasan penolakan.');
        }

        if ($isMintaRevisi) {
            return redirect()->route('asesor.daftar-peserta')
                ->with('sukses', 'Permintaan revisi FR.APL.02 berhasil dikirimkan ke asesi beserta catatan perbaikan.');
        }

        return redirect()->route('asesor.dashboard')
            ->with('sukses', 'FR.APL.02 telah berhasil disetujui (Approved).');
    }

    /**
     * Tinjauan dan Pengesahan FR.AK.01 oleh Asesor
     */
    public function ak01Detail($pendaftaranId)
    {
        $asesorId = auth()->id();

        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'rekomendasi',
            'verifikasiKukApl02'
        ])->findOrFail($pendaftaranId);

        if ($pendaftaran->asesor_id !== $asesorId && $pendaftaran->jadwal?->asesor_id !== $asesorId) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke berkas peserta ini.');
        }

        if (!$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesor.input-penilaian', $pendaftaranId)
                ->with('error', 'Formulir FR.AK.01 belum dapat ditinjau karena FR.APL.02 belum disetujui (Approved).');
        }

        // Map Unit yang memiliki butir KUK BK
        $unitHasBkMap = [];
        $verifikasiKukMap = $pendaftaran->verifikasiKukApl02->keyBy('kuk_id');
        if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
            foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                $hasBk = false;
                foreach ($unit->elemenKompetensi as $elemen) {
                    foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                        $v = $verifikasiKukMap->get($kuk->id);
                        if ($v && !$v->is_verified) {
                            $hasBk = true;
                            break 2;
                        }
                    }
                }
                $unitHasBkMap[$unit->id] = $hasBk;
            }
        }

        return view('asesor.ak01-detail', compact('pendaftaran', 'unitHasBkMap'));
    }

    /**
     * Pengesahan / Tanda Tangan FR.AK.01 oleh Asesor
     */
    public function simpanAk01Asesor(Request $request, $pendaftaranId)
    {
        $asesorId = auth()->id();

        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);

        if ($pendaftaran->asesor_id !== $asesorId && $pendaftaran->jadwal?->asesor_id !== $asesorId) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki akses untuk mengesahkan formulir peserta ini.');
        }

        if (!$pendaftaran->isAk01Unlocked()) {
            return back()->with('error', 'Formulir FR.AK.01 belum dapat disahkan karena FR.APL.02 belum disetujui.');
        }

        $request->validate([
            'tuk_type' => 'nullable|string|in:Sewaktu,Tempat Kerja,Mandiri',
            'bukti_dikumpulkan' => 'nullable|array',
            'bukti_dikumpulkan_lainnya' => 'nullable|string',
            'tanda_tangan_asesor_ak01' => 'nullable|string',
        ]);

        $rawSignature = $request->tanda_tangan_asesor_ak01 ?: $request->tanda_tangan_asesor;
        $signaturePath = null;

        if (!empty($rawSignature) && \Illuminate\Support\Str::startsWith($rawSignature, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawSignature);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1] ?? '');

                if (!empty($imageBase64)) {
                    $filename = 'sig_asesor_ak01_' . $pendaftaran->id . '_' . \Illuminate\Support\Str::random(10) . '.' . $imageType;
                    \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $filename, $imageBase64);
                    $signaturePath = 'storage/signatures/' . $filename;
                }
            } catch (\Exception $e) {
                // Fallback
            }
        }

        if (empty($signaturePath)) {
            $signaturePath = $pendaftaran->tanda_tangan_asesor_ak01;
        }

        if (empty($signaturePath)) {
            return back()->with('error', 'Tanda tangan digital Asesor penguji wajib dibubuhkan pada formulir ini.');
        }

        $oldStatusAk01 = $pendaftaran->status_ak01;
        $isBothSigned = !empty($pendaftaran->tanda_tangan_asesi_ak01);

        \DB::transaction(function() use ($pendaftaran, $request, $signaturePath, $isBothSigned) {
            $updateData = [
                'tanda_tangan_asesor_ak01' => $signaturePath,
                'tanggal_ttd_asesor_ak01' => now(),
                'status_ak01' => $isBothSigned ? 'selesai' : 'disetujui_asesor',
            ];

            if ($request->has('tuk_type')) {
                $updateData['tuk_type'] = $request->input('tuk_type');
            }
            if ($request->has('bukti_dikumpulkan')) {
                $updateData['bukti_dikumpulkan'] = $request->input('bukti_dikumpulkan');
            }
            if ($request->has('bukti_dikumpulkan_lainnya')) {
                $updateData['bukti_dikumpulkan_lainnya'] = $request->input('bukti_dikumpulkan_lainnya');
            }

            $pendaftaran->update($updateData);
        });

        LogAktivitas::catat('Pengisian & Pengesahan FR.AK.01', 'Asesor mengisi rencana asesmen dan menandatangani FR.AK.01 untuk peserta #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke Asesi bahwa AK.01 telah diisi & disahkan Asesor
        if ($oldStatusAk01 !== 'selesai') {
            $pendaftaran->asesi?->notify(new \App\Notifications\AK01Approved($pendaftaran->id));
        }

        $pesanSukses = $isBothSigned
            ? 'Formulir FR.AK.01 Persetujuan Asesmen & Kerahasiaan berhasil disahkan oleh kedua belah pihak.'
            : 'Formulir FR.AK.01 Persetujuan Asesmen & Kerahasiaan berhasil diisi dan disahkan. Menunggu tanda tangan Asesi.';

        return redirect()->route('asesor.dashboard')
            ->with('sukses', $pesanSukses);
    }

    /**
     * Otorisasi dan ambil skema sertifikasi untuk asesor / admin
     */
    protected function authorizeSkemaAccess(int $skemaId): SkemaSertifikasi
    {
        $user = auth()->user();
        $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->findOrFail($skemaId);

        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            $accessibleSkemaIds = array_unique(array_filter(array_merge(
                [$user->skema_id],
                JadwalAsesmen::where('asesor_id', $user->id)->pluck('skema_id')->unique()->toArray()
            )));

            // Jika daftar skema asesor belum spesifik, izinkan akses ke seluruh skema aktif
            if (!empty($accessibleSkemaIds) && !in_array($skemaId, $accessibleSkemaIds, true)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki penugasan untuk skema ini.');
            }
        }

        return $skema;
    }

    /**
     * Tampilkan formulir Master FR.MAPA.01 per Skema Sertifikasi (Template Mandiri)
     */
    public function masterMapa01($skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $schemeAsesor = ($user->peran === 'asesor') 
            ? $user 
            : (Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first()
               ?? Pengguna::where('peran', 'asesor')->first());
        $effectiveAsesorId = $schemeAsesor?->id ?? $user->id;

        $mapa01 = $this->mapaService->getOrCreateMasterMapa01($skema, $effectiveAsesorId);

        // Buat representasi PendaftaranAsesi dummy in-memory agar view mapa-01 tetap kompatibel
        $pendaftaran = new PendaftaranAsesi([
            'id' => 0,
            'nomor_pendaftaran' => 'MASTER-' . $skema->kode_skema,
            'skema_id' => $skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
        ]);
        $pendaftaran->setRelation('skema', $skema);
        $pendaftaran->setRelation('asesi', new Pengguna([
            'nama_lengkap' => 'Template Master (Berlaku untuk Seluruh Asesi)',
            'nomor_telepon' => '-',
        ]));
        $pendaftaran->setRelation('asesor', $schemeAsesor ?? $user);
        $pendaftaran->setRelation('mapa01', $mapa01);

        $isMasterMode = true;

        return view('asesor.mapa-01', compact('pendaftaran', 'mapa01', 'skema', 'isMasterMode'));
    }

    /**
     * Simpan / Sahkan Master Dokumen FR.MAPA.01 per Skema
     */
    public function simpanMasterMapa01(Request $request, $skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $schemeAsesor = ($user->peran === 'asesor') 
            ? $user 
            : (Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first()
               ?? Pengguna::where('peran', 'asesor')->first());
        $effectiveAsesorId = $schemeAsesor?->id ?? $user->id;

        $isConfirm = $request->input('aksi') === 'konfirmasi';

        $this->mapaService->saveMasterMapa01(
            $skema,
            $request->all(),
            $request->input('tanda_tangan_asesor'),
            $isConfirm,
            $effectiveAsesorId
        );

        LogAktivitas::catat('Master MAPA.01', 'Menyimpan Master FR.MAPA.01 (' . ($isConfirm ? 'Disahkan' : 'Draft') . ') untuk Skema ' . $skema->kode_skema);

        // Notifikasi ke Admin & Superadmin jika formulir Master MAPA.01 Skema memerlukan validasi
        if ($user->peran === 'asesor') {
            $master01 = Mapa01::where('skema_id', $skema->id)->whereNull('pendaftaran_id')->first();
            $validatorData = $master01?->penyusun_validator_tabel['validator_1'] ?? [];
            $isAlreadyValidated = !empty($validatorData['ttd']) || ($validatorData['status_validasi'] ?? null) === 'tervalidasi';

            if (!$isAlreadyValidated) {
                $admins = Pengguna::whereIn('peran', ['admin', 'superadmin'])
                    ->where('aktif', true)
                    ->get();

                foreach ($admins as $admin) {
                    $hasPendingNotification = $admin->notifications()
                        ->where('type', SystemAlert::class)
                        ->get()
                        ->contains(function ($notification) use ($skema) {
                            return ($notification->data['metadata']['mapa01_master_skema_id'] ?? null) == $skema->id
                                && ($notification->data['metadata']['status'] ?? null) === 'menunggu_validasi'
                                && is_null($notification->read_at);
                        });

                    if (!$hasPendingNotification) {
                        $admin->notify(new SystemAlert(
                            'FR.MAPA.01 Sesuai Skema Menunggu Validasi',
                            'Asesor ' . ($user->nama_lengkap ?: 'Asesor') . ' telah menyusun dokumen Master FR.MAPA.01 untuk Skema ' . ($skema->nama_skema ?: 'Sertifikasi') . ' (' . $skema->kode_skema . '). Dokumen menunggu pengesahan & validasi Administrator LSP.',
                            route('asesor.skema.mapa-01', $skema->id),
                            'mapa_validation',
                            [
                                'mapa01_master_skema_id' => $skema->id,
                                'skema_id' => $skema->id,
                                'status' => 'menunggu_validasi',
                            ]
                        ));
                    }
                }
            }
        }

        if ($isConfirm) {
            $suksesMsg = in_array($user->peran, ['admin', 'superadmin'])
                ? 'Dokumen Master FR.MAPA.01 berhasil disahkan dan langsung tervalidasi oleh Administrator.'
                : 'Master Dokumen FR.MAPA.01 berhasil disahkan.';
            return redirect()->route('asesor.mapa', ['skema_id' => $skema->id])
                ->with('sukses', $suksesMsg);
        }

        $draftMsg = in_array($user->peran, ['admin', 'superadmin'])
            ? 'Dokumen Master FR.MAPA.01 berhasil disimpan dan langsung tervalidasi.'
            : 'Draft Master FR.MAPA.01 berhasil disimpan.';
        return redirect()->route('asesor.skema.mapa-01', $skema->id)
            ->with('sukses', $draftMsg);
    }

    /**
     * Tampilkan formulir Master Peta FR.MAPA.02 per Skema Sertifikasi (Template Mandiri)
     */
    public function masterMapa02($skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);
        $isAdmin = in_array($user->peran, ['admin', 'superadmin']);

        // Jika dibikin/dibuka di admin, gunakan data admin.
        // Jika dibuka di asesor, jangan diubah (tetap gunakan asesor).
        if ($isAdmin) {
            $effectiveAsesorId = $user->id;
            $schemeAsesor = $user;
        } else {
            $schemeAsesor = ($user->peran === 'asesor') 
                ? $user 
                : (Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first()
                   ?? Pengguna::where('peran', 'asesor')->first());
            $effectiveAsesorId = $schemeAsesor?->id ?? $user->id;
        }

        $mapa02 = $this->mapaService->getOrCreateMasterMapa02($skema, $effectiveAsesorId);

        // Jika dokumen master sudah tersimpan dan ada asesor/pembuatnya:
        if ($mapa02->exists && $mapa02->asesor) {
            if ($isAdmin) {
                // Di portal admin, selalu gunakan identitas admin yang sedang aktif
                $schemeAsesor = $user;
            } else {
                // Untuk asesor, jangan diubah (tetap gunakan asesor)
                $schemeAsesor = $mapa02->asesor;
            }
        }

        $pendaftaran = new PendaftaranAsesi([
            'id' => 0,
            'nomor_pendaftaran' => 'MASTER-' . $skema->kode_skema,
            'skema_id' => $skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
        ]);
        $pendaftaran->setRelation('skema', $skema);
        $pendaftaran->setRelation('asesi', new Pengguna([
            'nama_lengkap' => 'Template Master (Berlaku untuk Seluruh Asesi)',
            'nomor_telepon' => '-',
        ]));
        $pendaftaran->setRelation('asesor', $schemeAsesor ?? $user);
        $pendaftaran->setRelation('mapa02', $mapa02);

        $isMasterMode = true;

        return view('asesor.mapa-02', compact('pendaftaran', 'mapa02', 'skema', 'isMasterMode'));
    }

    /**
     * Simpan / Sahkan Master Peta FR.MAPA.02 per Skema
     */
    public function simpanMasterMapa02(Request $request, $skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);
        $isAdmin = in_array($user->peran, ['admin', 'superadmin']);

        // Jika dibikin di admin, ttd & id harus data admin.
        // Jika di asesor, jangan diubah (tetap asesor).
        if ($isAdmin) {
            $effectiveAsesorId = $user->id;
        } else {
            $schemeAsesor = ($user->peran === 'asesor') 
                ? $user 
                : (Pengguna::where('peran', 'asesor')->where('skema_id', $skema->id)->first()
                   ?? Pengguna::where('peran', 'asesor')->first());
            $effectiveAsesorId = $schemeAsesor?->id ?? $user->id;
        }

        $isConfirm = $request->input('aksi') === 'konfirmasi';

        $this->mapaService->saveMasterMapa02(
            $skema,
            $request->input('matriks_peta', []),
            $request->input('catatan_asesor'),
            $request->input('tanda_tangan_asesor'),
            $isConfirm,
            $effectiveAsesorId
        );

        LogAktivitas::catat('Master MAPA.02', 'Menyimpan Master Peta FR.MAPA.02 (' . ($isConfirm ? 'Disahkan' : 'Draft') . ') untuk Skema ' . $skema->kode_skema);

        if ($isConfirm) {
            return redirect()->route('asesor.mapa', ['skema_id' => $skema->id])
                ->with('sukses', 'Master Peta Instrumen FR.MAPA.02 berhasil disahkan dan aktif sebagai acuan seluruh asesi pada skema ini!');
        }

        return redirect()->route('asesor.skema.mapa-02', $skema->id)
            ->with('sukses', 'Draft Master Peta FR.MAPA.02 berhasil disimpan.');
    }

    /**
     * Tampilkan formulir Master FR.AK.01 per Skema Sertifikasi (Rencana Asesmen Mandiri)
     */
    public function masterAk01($skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $masterAk01 = MasterAk01::firstOrNew(['skema_id' => $skema->id]);
        if (!$masterAk01->exists) {
            $masterAk01->tuk_type = null;
            $masterAk01->bukti_dikumpulkan = [];
            $masterAk01->status = 'draft';
        }

        $allUnits = $skema->unitKompetensi()->with('elemenKompetensi.kriteriaUnjukKerja')->get();

        return view('asesor.ak01-skema', compact('skema', 'masterAk01', 'allUnits'));
    }

    /**
     * Simpan / Sahkan Master Dokumen FR.AK.01 per Skema
     */
    public function simpanMasterAk01(Request $request, $skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $request->validate([
            'tuk_type' => 'required|string|in:Sewaktu,Tempat Kerja,Mandiri',
            'bukti_dikumpulkan' => 'nullable|array',
            'bukti_dikumpulkan_lainnya' => 'nullable|string|max:500',
            'catatan_asesor' => 'nullable|string|max:1000',
            'tanda_tangan_asesor' => 'nullable|string',
            'sign_mode' => 'nullable|string',
        ]);

        $rawSignature = $request->input('tanda_tangan_asesor');
        $signaturePath = null;

        if (!empty($rawSignature) && \Illuminate\Support\Str::startsWith($rawSignature, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawSignature);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1] ?? '');

                if (!empty($imageBase64)) {
                    $filename = 'sig_master_ak01_' . $skema->id . '_' . \Illuminate\Support\Str::random(10) . '.' . $imageType;
                    \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $filename, $imageBase64);
                    $signaturePath = 'storage/signatures/' . $filename;
                }
            } catch (\Exception $e) {
                // Fallback
            }
        } elseif (!empty($rawSignature) && (\Illuminate\Support\Str::startsWith($rawSignature, 'storage/') || \Illuminate\Support\Str::startsWith($rawSignature, 'images/'))) {
            $signaturePath = $rawSignature;
        }

        $existingMasterAk01 = MasterAk01::where('skema_id', $skema->id)->first();
        if (empty($signaturePath)) {
            $signaturePath = $existingMasterAk01?->tanda_tangan_asesor;
        }

        $masterAk01 = MasterAk01::updateOrCreate(
            ['skema_id' => $skema->id],
            [
                'asesor_id' => $user->id,
                'tuk_type' => $request->input('tuk_type', 'Sewaktu'),
                'bukti_dikumpulkan' => $request->input('bukti_dikumpulkan', ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)', 'Tanya Jawab Lisan']),
                'bukti_dikumpulkan_lainnya' => $request->input('bukti_dikumpulkan_lainnya'),
                'catatan_asesor' => $request->input('catatan_asesor'),
                'tanda_tangan_asesor' => $signaturePath,
                'tanggal_ttd_asesor' => now(),
                'status' => 'selesai',
            ]
        );

        // Terapkan / sinkronisasikan ke seluruh pendaftaran asesi pada skema ini
        $syncedCount = $masterAk01->sinkronkanKePeserta();

        LogAktivitas::catat('Master AK.01', 'Menetapkan dan mengesahkan Master FR.AK.01 untuk Skema ' . $skema->kode_skema . ' (Diterapkan ke ' . $syncedCount . ' asesi)');

        return redirect()->route('asesor.mapa', ['skema_id' => $skema->id])
            ->with('sukses', 'Master FR.AK.01 untuk Skema ' . $skema->nama_skema . ' berhasil disimpan dan telah disinkronisasikan ke ' . $syncedCount . ' asesi terdaftar! Formulir sekarang dalam mode terkunci.');
    }

    /**
     * Tampilkan formulir konfigurasi Master FR.AK.07 per Skema
     */
    public function masterAk07($skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $masterAk07 = MasterAk07::firstOrNew(['skema_id' => $skema->id]);
        if (!$masterAk07->exists) {
            $masterAk07->potensi_asesi = null;
            $masterAk07->fase_penggunaan = null;
            $masterAk07->items_checklist = [];
            $masterAk07->acuan_pembanding_disepakati = null;
            $masterAk07->metode_disepakati = null;
            $masterAk07->instrumen_disepakati = null;
            $masterAk07->status = 'draft';
        }

        $criteriaDefinitions = \App\Models\AssessmentAk07Adjustment::CRITERIA_DEFINITIONS;
        $potensiDefinitions = \App\Models\AssessmentAk07Adjustment::POTENSI_DEFINITIONS;

        return view('asesor.ak07-skema', compact('skema', 'masterAk07', 'criteriaDefinitions', 'potensiDefinitions'));
    }

    /**
     * Simpan / Sahkan Master Dokumen FR.AK.07 per Skema
     */
    public function simpanMasterAk07(Request $request, $skemaId)
    {
        $user = auth()->user();
        $skema = $this->authorizeSkemaAccess((int) $skemaId);

        $request->validate([
            'potensi_asesi' => 'nullable|integer|between:1,5',
            'fase_penggunaan' => 'nullable|string',
            'items_checklist' => 'nullable|array',
            'acuan_pembanding_disepakati' => 'nullable|string|max:1000',
            'metode_disepakati' => 'nullable|string|max:1000',
            'instrumen_disepakati' => 'nullable|string|max:1000',
            'catatan_asesor' => 'nullable|string|max:1000',
            'tanda_tangan_asesor' => 'nullable|string',
        ]);

        $rawChecklist = $request->input('items_checklist', []);
        $formattedChecklist = [];

        foreach (\App\Models\AssessmentAk07Adjustment::CRITERIA_DEFINITIONS as $catId => $cat) {
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

        $existingMaster = MasterAk07::where('skema_id', $skema->id)->first();
        $rawSignature = $request->input('tanda_tangan_asesor');
        $signaturePath = null;

        if (!empty($rawSignature) && \Illuminate\Support\Str::startsWith($rawSignature, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawSignature);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1] ?? '');

                if (!empty($imageBase64)) {
                    $filename = 'sig_master_ak07_' . $skema->id . '_' . \Illuminate\Support\Str::random(10) . '.' . $imageType;
                    \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $filename, $imageBase64);
                    $signaturePath = 'storage/signatures/' . $filename;
                }
            } catch (\Exception $e) {
                // Fallback
            }
        } elseif (!empty($rawSignature) && (\Illuminate\Support\Str::startsWith($rawSignature, 'storage/') || \Illuminate\Support\Str::startsWith($rawSignature, 'images/'))) {
            $signaturePath = $rawSignature;
        }

        if (empty($signaturePath)) {
            $signaturePath = $existingMaster?->tanda_tangan_asesor;
        }

        $masterAk07 = MasterAk07::updateOrCreate(
            ['skema_id' => $skema->id],
            [
                'asesor_id' => $user->id,
                'potensi_asesi' => $request->filled('potensi_asesi') ? (int) $request->input('potensi_asesi') : null,
                'fase_penggunaan' => $request->input('fase_penggunaan') ?: null,
                'items_checklist' => $formattedChecklist,
                'acuan_pembanding_disepakati' => $request->input('acuan_pembanding_disepakati'),
                'metode_disepakati' => $request->input('metode_disepakati'),
                'instrumen_disepakati' => $request->input('instrumen_disepakati'),
                'catatan_asesor' => $request->input('catatan_asesor'),
                'tanda_tangan_asesor' => $signaturePath,
                'tanggal_ttd_asesor' => now(),
                'status' => 'selesai',
            ]
        );

        $syncedCount = $masterAk07->sinkronkanKePeserta();

        LogAktivitas::catat('Master AK.07', 'Menetapkan dan mengesahkan Master FR.AK.07 untuk Skema ' . $skema->kode_skema . ' (Diterapkan ke ' . $syncedCount . ' asesi)');

        return redirect()->route('asesor.mapa', ['skema_id' => $skema->id])
            ->with('sukses', 'Master FR.AK.07 untuk Skema ' . $skema->nama_skema . ' berhasil disimpan dan telah disinkronisasikan ke ' . $syncedCount . ' asesi terdaftar! Formulir sekarang dalam mode terkunci.');
    }

    public function mapa01($pendaftaranId)
    {
        $user = auth()->user();
        $asesorId = $user->id;

        $query = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'rekomendasi',
            'mapa01',
            'mapa02'
        ]);

        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            $query->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            });
        }

        $pendaftaran = $query->findOrFail($pendaftaranId);
        
        $schemeAsesor = ($user->peran === 'asesor')
            ? $user
            : ($pendaftaran->asesor 
               ?: (Pengguna::where('peran', 'asesor')->where('skema_id', $pendaftaran->skema_id)->first()
                   ?: Pengguna::where('peran', 'asesor')->first()));

        $effectiveAsesorId = ($pendaftaran->asesor && $pendaftaran->asesor->peran === 'asesor') 
            ? $pendaftaran->asesor_id 
            : ($schemeAsesor?->id ?? $asesorId);

        if ((!$pendaftaran->asesor_id || ($pendaftaran->asesor && $pendaftaran->asesor->peran !== 'asesor')) && $schemeAsesor) {
            $pendaftaran->update(['asesor_id' => $schemeAsesor->id]);
            $pendaftaran->setRelation('asesor', $schemeAsesor);
        }

        // Auto-generate / get existing MAPA 01 secara idempotent
        $mapa01 = $this->mapaService->getOrCreateMapa01($pendaftaran, $effectiveAsesorId);

        return view('asesor.mapa-01', compact('pendaftaran', 'mapa01'));
    }

    public function simpanMapa01(Request $request, $pendaftaranId)
    {
        $user = auth()->user();
        $asesorId = $user->id;

        $query = PendaftaranAsesi::with(['skema.unitKompetensi', 'mapa01']);
        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            $query->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            });
        }

        $pendaftaran = $query->findOrFail($pendaftaranId);

        $schemeAsesor = ($user->peran === 'asesor')
            ? $user
            : ($pendaftaran->asesor 
               ?: (Pengguna::where('peran', 'asesor')->where('skema_id', $pendaftaran->skema_id)->first()
                   ?: Pengguna::where('peran', 'asesor')->first()));

        $effectiveAsesorId = ($pendaftaran->asesor && $pendaftaran->asesor->peran === 'asesor') 
            ? $pendaftaran->asesor_id 
            : ($schemeAsesor?->id ?? $asesorId);

        if ((!$pendaftaran->asesor_id || ($pendaftaran->asesor && $pendaftaran->asesor->peran !== 'asesor')) && $schemeAsesor) {
            $pendaftaran->update(['asesor_id' => $schemeAsesor->id]);
            $pendaftaran->setRelation('asesor', $schemeAsesor);
        }

        $isConfirm = $request->input('aksi') === 'konfirmasi';

        $this->mapaService->saveMapa01(
            $pendaftaran,
            $request->all(),
            $request->input('tanda_tangan_asesor'),
            $isConfirm,
            $effectiveAsesorId
        );

        if ($isConfirm && $user->peran === 'asesor') {
            $mapa01 = $pendaftaran->fresh(['skema', 'mapa01']);
            $validatorData = $mapa01?->mapa01?->penyusun_validator_tabel['validator_1'] ?? [];
            $isAlreadyValidated = !empty($validatorData['ttd']) || ($validatorData['status_validasi'] ?? null) === 'tervalidasi';

            if (!$isAlreadyValidated) {
                $admins = Pengguna::whereIn('peran', ['admin', 'superadmin'])
                    ->where('aktif', true)
                    ->get();

                foreach ($admins as $admin) {
                    $hasPendingNotification = $admin->notifications()
                        ->where('type', SystemAlert::class)
                        ->get()
                        ->contains(function ($notification) use ($pendaftaran) {
                            return ($notification->data['metadata']['mapa01_pendaftaran_id'] ?? null) == $pendaftaran->id
                                && ($notification->data['metadata']['status'] ?? null) === 'menunggu_validasi';
                        });

                    if (!$hasPendingNotification) {
                        $admin->notify(new SystemAlert(
                            'FR.MAPA.01 Menunggu Validasi',
                            'Asesor ' . ($user->nama_lengkap ?: 'Asesor') . ' telah mengesahkan FR.MAPA.01 untuk peserta ' . ($pendaftaran->asesi?->nama_lengkap ?: 'Asesi') . '. Dokumen menunggu validasi Admin LSP.',
                            route('asesor.mapa-01', $pendaftaran->id),
                            'warning',
                            [
                                'mapa01_pendaftaran_id' => $pendaftaran->id,
                                'skema_id' => $pendaftaran->skema_id,
                                'status' => 'menunggu_validasi',
                            ]
                        ));
                    }
                }
            }
        }

        LogAktivitas::catat('Perencanaan Asesmen (FR.MAPA.01)', 'Menyimpan dokumen FR.MAPA.01 (' . ($isConfirm ? 'Disahkan' : 'Draft') . ') untuk pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        if ($isConfirm) {
            $suksesMsg = in_array($user->peran, ['admin', 'superadmin'])
                ? 'Dokumen FR.MAPA.01 berhasil disahkan dan langsung tervalidasi oleh Administrator.'
                : 'Dokumen FR.MAPA.01 berhasil disahkan.';
            return redirect()->route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id])
                ->with('sukses', $suksesMsg);
        }

        $draftMsg = in_array($user->peran, ['admin', 'superadmin'])
            ? 'Dokumen FR.MAPA.01 berhasil disimpan dan langsung tervalidasi.'
            : 'Draft FR.MAPA.01 berhasil disimpan.';
        return redirect()->route('asesor.mapa-01', $pendaftaran->id)
            ->with('sukses', $draftMsg);
    }

    public function mapa02($pendaftaranId)
    {
        $user = auth()->user();
        $asesorId = $user->id;

        $query = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'rekomendasi',
            'mapa01',
            'mapa02'
        ]);

        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            $query->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            });
        }

        $pendaftaran = $query->findOrFail($pendaftaranId);
        $effectiveAsesorId = $pendaftaran->asesor_id ?: $asesorId;

        // Auto-generate / get existing MAPA 02 secara idempotent dari master instrumen skema
        $mapa02 = $this->mapaService->getOrCreateMapa02($pendaftaran, $effectiveAsesorId);

        return view('asesor.mapa-02', compact('pendaftaran', 'mapa02'));
    }

    public function simpanMapa02(Request $request, $pendaftaranId)
    {
        $user = auth()->user();
        $asesorId = $user->id;

        $query = PendaftaranAsesi::with(['skema.unitKompetensi', 'mapa02']);
        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            $query->where(function($q) use ($asesorId) {
                $q->where('asesor_id', $asesorId)
                  ->orWhereHas('jadwal', function($j) use ($asesorId) {
                      $j->where('asesor_id', $asesorId);
                  });
            });
        }

        $pendaftaran = $query->findOrFail($pendaftaranId);
        $effectiveAsesorId = $pendaftaran->asesor_id ?: $asesorId;

        $isConfirm = $request->input('aksi') === 'konfirmasi';

        $this->mapaService->saveMapa02(
            $pendaftaran,
            $request->input('matriks_peta', []),
            $request->input('catatan_asesor'),
            $request->input('tanda_tangan_asesor'),
            $isConfirm,
            $effectiveAsesorId
        );

        LogAktivitas::catat('Peta Instrumen Asesmen (FR.MAPA.02)', 'Menyimpan dokumen FR.MAPA.02 (' . ($isConfirm ? 'Disahkan' : 'Draft') . ') untuk pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        if ($isConfirm) {
            $targetUrl = in_array($user->peran, ['admin', 'superadmin'])
                ? route('admin.master-muk.index', ['skema_id' => $pendaftaran->skema_id])
                : route('asesor.mapa');

            return redirect($targetUrl)
                ->with('sukses', 'Peta Instrumen Asesmen (FR.MAPA 02) berhasil disahkan dan siap digunakan untuk pelaksanaan asesmen!');
        }

        return back()->with('sukses', 'Draft Peta Instrumen Asesmen (FR.MAPA 02) berhasil disimpan.');
    }

    public function beritaAcara()
    {
        $jadwalList = JadwalAsesmen::with(['skema', 'beritaAcara'])
            ->where('asesor_id', auth()->id())
            ->get();

        $beritaAcaraList = BeritaAcara::whereHas('jadwal', function($q) {
            $q->where('asesor_id', auth()->id());
        })->with('jadwal.skema')->latest()->paginate(10);

        return view('asesor.berita-acara', compact('jadwalList', 'beritaAcaraList'));
    }

    public function simpanBeritaAcara(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_asesmen,id',
            'catatan_pelaksanaan' => 'required|string',
        ]);

        $jadwal = JadwalAsesmen::with('pendaftaranAsesi.rekomendasi')
            ->where('asesor_id', auth()->id())
            ->findOrFail($request->jadwal_id);

        $totalPeserta = $jadwal->pendaftaranAsesi->count();
        $totalKompeten = 0;
        $totalBelumKompeten = 0;
        $totalBelumDinilai = 0;

        foreach ($jadwal->pendaftaranAsesi as $p) {
            if ($p->rekomendasi?->keputusan === 'kompeten') {
                $totalKompeten++;
            } elseif ($p->rekomendasi?->keputusan === 'belum_kompeten') {
                $totalBelumKompeten++;
            } else {
                $totalBelumDinilai++;
            }
        }

        $nomorBA = 'BA-LSP/' . date('Y/m/') . sprintf('%03d', $jadwal->id);

        $ba = BeritaAcara::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                'nomor_berita_acara' => $nomorBA,
                'tanggal_pelaksanaan' => $jadwal->tanggal_uji,
                'jumlah_peserta' => $totalPeserta,
                'jumlah_kompeten' => $totalKompeten,
                'jumlah_belum_kompeten' => $totalBelumKompeten,
                'catatan_pelaksanaan' => $request->catatan_pelaksanaan,
                'tanda_tangan_asesor' => $request->tanda_tangan_asesor ?? null,
            ]
        );

        if ($request->hasFile('file_berita_acara')) {
            $file = $request->file('file_berita_acara');
            $path = $file->storeAs('berita_acara', 'BA_' . $jadwal->id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            $ba->update(['file_berita_acara' => 'storage/' . $path]);
        }

        LogAktivitas::catat('Berita Acara dibuat', 'Membuat Berita Acara #' . $nomorBA);

        return back()->with('sukses', 'Dokumen Berita Acara berhasil dibuat dan disimpan.');
    }

    /**
     * Pusat Pembuatan Formulir (FR.MAPA & FR.IA) Berbasis Skema
     */
    public function mapa(Request $request)
    {
        $asesor = auth()->user();
        $asesorId = $asesor->id;

        // Skema yang terkait dengan akun asesor ini
        $primarySkemaId = $asesor->skema_id;
        $jadwalSkemaIds = JadwalAsesmen::where('asesor_id', $asesorId)
            ->pluck('skema_id')
            ->unique()
            ->toArray();

        $accessibleSkemaIds = array_unique(array_filter(array_merge([$primarySkemaId], $jadwalSkemaIds)));

        // Ambil daftar skema yang dapat diakses (atau semua skema aktif jika asesor mengampu umum)
        if (!empty($accessibleSkemaIds)) {
            $skemaList = SkemaSertifikasi::whereIn('id', $accessibleSkemaIds)
                ->where('status_aktif', true)
                ->with(['unitKompetensi.elemenKompetensi.kriteriaUnjukKerja', 'masterInstruments.questionBanks', 'masterInstruments.productSpecifications'])
                ->orderBy('nama_skema', 'asc')
                ->get();

            // Jika daftar kosong karena skema belum aktif, fallback ke semua aktif
            if ($skemaList->isEmpty()) {
                $skemaList = SkemaSertifikasi::where('status_aktif', true)
                    ->with(['unitKompetensi.elemenKompetensi.kriteriaUnjukKerja', 'masterInstruments.questionBanks', 'masterInstruments.productSpecifications'])
                    ->orderBy('nama_skema', 'asc')
                    ->get();
            }
        } else {
            $skemaList = SkemaSertifikasi::where('status_aktif', true)
                ->with(['unitKompetensi.elemenKompetensi.kriteriaUnjukKerja', 'masterInstruments.questionBanks', 'masterInstruments.productSpecifications'])
                ->orderBy('nama_skema', 'asc')
                ->get();
        }

        if ($skemaList->isEmpty()) {
            // Tidak ada skema aktif — tampilkan state kosong tanpa auto-create
            $selectedSkema = null;
            $selectedSkemaId = 0;
        } else {
            // Tentukan skema penugasan asesor (asesor terikat ke skemanya)
            $selectedSkemaId = (int) ($primarySkemaId ?: ($request->get('skema_id') ?: ($skemaList->first()?->id ?? 0)));
            $selectedSkema = $skemaList->firstWhere('id', $selectedSkemaId) ?: $skemaList->first();
        }

        // Rekan asesor pada skema yang sama
        $rekanAsesor = collect();
        $mapa01Master = null;
        $mapa02Master = null;
        $masterAk01 = null;
        $masterAk07 = null;
        $samplePendaftaran = null;

        if ($selectedSkema) {
            $rekanAsesor = Pengguna::where('peran', 'asesor')
                ->where(function($q) use ($selectedSkema) {
                    $q->where('skema_id', $selectedSkema->id)
                      ->orWhereHas('jadwalAsesor', fn($j) => $j->where('skema_id', $selectedSkema->id));
                })
                ->get();

            $mapa01Master = Mapa01::where('skema_id', $selectedSkema->id)->latest()->first();
            $mapa02Master = Mapa02::where('skema_id', $selectedSkema->id)->latest()->first();
            $masterAk01 = MasterAk01::where('skema_id', $selectedSkema->id)->first();
            $masterAk07 = MasterAk07::where('skema_id', $selectedSkema->id)->first();
            $samplePendaftaran = PendaftaranAsesi::where('skema_id', $selectedSkema->id)->latest()->first();
        }

        return view('asesor.mapa-index', compact(
            'skemaList',
            'selectedSkema',
            'selectedSkemaId',
            'rekanAsesor',
            'mapa01Master',
            'mapa02Master',
            'masterAk01',
            'masterAk07',
            'samplePendaftaran'
        ));
    }
}

