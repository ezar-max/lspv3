<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use App\Models\Pengguna;
use App\Models\IaPenilaian;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\MasterAk01;
use App\Models\SchemeMasterInstrument;
use App\Services\FrIa01Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormulirController extends Controller
{
    public function __construct(private FrIa01Service $frIa01Service)
    {
    }

    /**
     * Helper untuk mengambil data pendaftaran atau membuat mock data dari database yang ada
     */
    private function dapatkanPendaftaran($pendaftaranId = null)
    {
        $user = auth()->user();
        $requestedSkemaId = request()->filled('skema_id') 
            ? (int) request()->get('skema_id') 
            : (session()->has('active_selected_skema_id') ? (int) session('active_selected_skema_id') : null);

        $explicitPendaftaranId = ($pendaftaranId !== null && $pendaftaranId !== '') 
            ? (int) $pendaftaranId 
            : (request()->filled('pendaftaran_id') ? (int) request()->get('pendaftaran_id') : null);

        // Jika pendaftaranId = 0 secara eksplisit, ini adalah mode Blanko / Pratinjau Master Skema
        $isExplicitBlanko = ($explicitPendaftaranId === 0);

        // Helper closures untuk eager load
        $withRelations = [
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'skema.masterInstruments.questionBanks.kriteriaUnjukKerja',
            'skema.masterInstruments.productSpecifications',
            'asesor',
            'jadwal.asesor',
            'rekomendasi',
            'mapa01',
            'mapa02',
            'jawabanApl02',
            'iaPenilaian'
        ];

        $pendaftaran = null;

        // =========================================================================
        // PRIORITAS 1: JIKA ADA PENDAFTARAN_ID EKSPLISIT (> 0)
        // INI ADALAH ASESMEN NYATA UNTUK ASESI TERTENTU DARI DATABASE
        // =========================================================================
        if ($explicitPendaftaranId && $explicitPendaftaranId > 0 && !$isExplicitBlanko) {
            $query = PendaftaranAsesi::with($withRelations)->where('id', $explicitPendaftaranId);

            if ($user && $user->peran === 'asesi') {
                $query->where('asesi_id', $user->id);
            } elseif ($user && $user->peran === 'asesor') {
                $query->where(function ($q) use ($user) {
                    $q->where('asesor_id', $user->id)
                        ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                });
            }

            $pendaftaran = $query->first();

            if ($pendaftaran) {
                session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                session(['active_selected_skema_id' => $pendaftaran->skema_id]);
                $requestedSkemaId = $pendaftaran->skema_id;
            } elseif ($user && $user->peran === 'asesi') {
                $existsOther = PendaftaranAsesi::where('id', $explicitPendaftaranId)->exists();
                if ($existsOther) {
                    abort(403, 'Akses Ditolak: Anda hanya diperbolehkan mengakses berkas dan hasil ujian milik akun Anda sendiri.');
                }
                abort(404, 'Pendaftaran tidak ditemukan.');
            }
        }

        // =========================================================================
        // PRIORITAS 2: JIKA BELUM ADA PENDAFTARAN EKSPLISIT ATAU MASIH NULL
        // =========================================================================
        if (!$pendaftaran) {
            // =========================================================================
            // KASUS A: SKEMA_ID DIKETAHUI (REQUEST ATAU SESSION)
            // =========================================================================
            if ($requestedSkemaId) {
                session(['active_selected_skema_id' => $requestedSkemaId]);

                // Jika bukan mode blanko eksplisit, coba cari pendaftaran asesi di skema ini
                if (!$isExplicitBlanko) {
                    if ($user && $user->peran === 'asesi') {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)
                            ->where('asesi_id', $user->id)
                            ->where('skema_id', $requestedSkemaId)
                            ->latest()
                            ->first();
                    } elseif ($user && $user->peran === 'asesor') {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)
                            ->where('skema_id', $requestedSkemaId)
                            ->where(function ($q) use ($user) {
                                $q->where('asesor_id', $user->id)
                                    ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                            })
                            ->latest()
                            ->first();
                    } else {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)
                            ->where('skema_id', $requestedSkemaId)
                            ->latest()
                            ->first();
                    }
                }

                // Jika tetap belum ada pendaftaran asesi untuk skema ini atau diminta blanko, buka Master Blanko Skema
                if (!$pendaftaran) {
                    $pendaftaran = $this->resolveBlankoSkema($requestedSkemaId, $user);
                    session()->forget('active_asesor_pendaftaran_id');
                } else {
                    if ($pendaftaran->id > 0) {
                        session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                    }
                }
            } 
            // =========================================================================
            // KASUS B: SKEMA_ID TIDAK DIKETAHUI (GUNAKAN PENDAFTARAN AKTIF)
            // =========================================================================
            else {
                if ($user && $user->peran === 'asesi') {
                    $pendaftaran = PendaftaranAsesi::with($withRelations)
                        ->where('asesi_id', $user->id)
                        ->latest()
                        ->first();

                    if (!$pendaftaran) {
                        $pendaftaran = $this->resolveBlankoSkema($user->skema_id, $user);
                    }
                } elseif ($user && $user->peran === 'asesor') {
                    $targetId = session('active_asesor_pendaftaran_id');

                    if ($targetId) {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)
                            ->where(function ($q) use ($user) {
                                $q->where('asesor_id', $user->id)
                                    ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                            })
                            ->find($targetId);
                    }

                    if (!$pendaftaran) {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)
                            ->where(function ($q) use ($user) {
                                $q->where('asesor_id', $user->id)
                                    ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                            })
                            ->latest()
                            ->first();
                    }

                    if ($pendaftaran) {
                        session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                        session(['active_selected_skema_id' => $pendaftaran->skema_id]);
                    } else {
                        $pendaftaran = $this->resolveBlankoSkema($user->skema_id, $user);
                    }
                } else {
                    // Admin / Superadmin
                    $targetId = session('active_asesor_pendaftaran_id');

                    if ($targetId) {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)->find($targetId);
                    }

                    if (!$pendaftaran) {
                        $pendaftaran = PendaftaranAsesi::with($withRelations)->latest()->first();
                    }

                    if ($pendaftaran) {
                        session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                        session(['active_selected_skema_id' => $pendaftaran->skema_id]);
                    } else {
                        $pendaftaran = $this->resolveBlankoSkema($user?->skema_id, $user);
                    }
                }
            }
        }

        // PASTIKAN RELASI SKEMA TER-LOAD LENGKAP DENGAN UNIT, ELEMEN, DAN KUK
        if ($pendaftaran && $pendaftaran->skema) {
            $pendaftaran->skema->loadMissing([
                'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                'masterInstruments.questionBanks.kriteriaUnjukKerja',
                'masterInstruments.productSpecifications'
            ]);

            // Sinkronkan session skema aktif
            if (!empty($pendaftaran->skema_id)) {
                session(['active_selected_skema_id' => $pendaftaran->skema_id]);
            }
        }

        return $pendaftaran;
    }

    /**
     * Membuat objek dummy pendaftaran untuk pratinjau / master blanko per skema sertifikasi
     */
    protected function resolveBlankoSkema($skemaId = null, $user = null)
    {
        $skema = null;
        if ($skemaId) {
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->find($skemaId);
        }

        if (!$skema) {
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->first();
        }

        if (!$skema) {
            // Skema default in-memory berstandar BNSP lengkap dengan unit, elemen, dan KUK
            $skema = new SkemaSertifikasi([
                'id' => 1,
                'kode_skema' => 'SKM-STANDAR-BNSP',
                'nama_skema' => 'Skema Sertifikasi Kompetensi (Standar BNSP)',
                'status_aktif' => true,
            ]);

            $unit = new \App\Models\UnitKompetensi([
                'id' => 1,
                'skema_id' => 1,
                'kode_unit' => 'SKK.BNSP.001.01',
                'judul_unit' => 'Menerapkan Standar Operasional dan Prosedur Kerja Kompetensi',
            ]);

            $elemen = new \App\Models\ElemenKompetensi([
                'id' => 1,
                'unit_id' => 1,
                'nomor_elemen' => 1,
                'nama_elemen' => 'Melaksanakan Prosedur dan Keselamatan Kerja',
            ]);

            $kuk1 = new \App\Models\KriteriaUnjukKerja([
                'id' => 1,
                'elemen_id' => 1,
                'nomor_kuk' => '1.1',
                'pernyataan_kuk' => 'Prosedur kerja diidentifikasi dan diterapkan sesuai spesifikasi teknis.',
            ]);

            $kuk2 = new \App\Models\KriteriaUnjukKerja([
                'id' => 2,
                'elemen_id' => 1,
                'nomor_kuk' => '1.2',
                'pernyataan_kuk' => 'Peralatan dan perlengkapan kerja disiapkan sesuai kebutuhan standar operasional.',
            ]);

            $elemen->setRelation('kriteriaUnjukKerja', collect([$kuk1, $kuk2]));
            $unit->setRelation('elemenKompetensi', collect([$elemen]));
            $skema->setRelation('unitKompetensi', collect([$unit]));
        }

        // Hanya baca — TIDAK boleh auto-create record MAPA saat preview/blanko
        $masterMapa01 = ($skema->exists)
            ? Mapa01::where('skema_id', $skema->id)->whereNull('pendaftaran_id')->first()
            : null;

        if (!$masterMapa01) {
            $masterMapa01 = new Mapa01(['skema_id' => $skema->id ?? 1]);
        }

        $masterMapa02 = ($skema->exists)
            ? Mapa02::where('skema_id', $skema->id)->whereNull('pendaftaran_id')->first()
            : null;

        if (!$masterMapa02) {
            $masterMapa02 = new Mapa02(['skema_id' => $skema->id ?? 1]);
        }

        $dummy = new PendaftaranAsesi([
            'nomor_pendaftaran' => 'BLANKO-' . ($skema->kode_skema ?? 'BNSP'),
            'skema_id' => $skema->id,
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
            'status_pendaftaran' => 'disetujui',
        ]);
        $dummy->id = 0;
        $dummy->setRelation('skema', $skema);
        $dummy->setRelation('asesi', new Pengguna([
            'nama_lengkap' => '(Blanko / Pratinjau Master)',
            'nomor_telepon' => '-',
        ]));
        $dummy->setRelation('asesor', $user ?: new Pengguna(['nama_lengkap' => 'Asesor Penguji']));
        $dummy->setRelation('jadwal', new JadwalAsesmen([
            'nama_tuk' => 'TUK Mandiri / Sewaktu LSP',
            'tanggal_mulai' => now(),
        ]));
        $dummy->setRelation('mapa01', $masterMapa01);
        $dummy->setRelation('mapa02', $masterMapa02);
        $dummy->setRelation('jawabanApl02', collect());
        $dummy->setRelation('iaPenilaian', collect());

        $dummy->tuk_type = null;
        $dummy->bukti_dikumpulkan = [];

        if ($skema->exists) {
            $masterAk01 = MasterAk01::where('skema_id', $skema->id)->first();
            if ($masterAk01) {
                $dummy->tanda_tangan_asesor_ak01 = $masterAk01->tanda_tangan_asesor;
                $dummy->tanggal_ttd_asesor_ak01 = $masterAk01->tanggal_ttd_asesor;
            }
        }

        return $dummy;
    }

    /**
     * Gate Check: Memeriksa apakah FR.AK.01 sudah ditandatangani
     */
    private function cekGateAk01($pendaftaran)
    {
        if (!$pendaftaran) {
            return false;
        }

        // Izinkan jika user adalah admin / superadmin
        if (auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin'])) {
            return true;
        }

        // Jika user adalah asesi, cek apakah asesi sudah menandatangani FR.AK.01
        if (auth()->check() && auth()->user()->peran === 'asesi') {
            return !empty($pendaftaran->tanda_tangan_asesi_ak01) 
                || !empty($pendaftaran->tanda_tangan_asesi)
                || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']);
        }

        // Jika user adalah asesor, cek apakah asesor sudah menandatangani FR.AK.01
        if (auth()->check() && auth()->user()->peran === 'asesor') {
            return !empty($pendaftaran->tanda_tangan_asesor_ak01) 
                || !empty($pendaftaran->tanda_tangan_asesor)
                || in_array($pendaftaran->status_ak01, ['disetujui_asesor', 'selesai']);
        }

        // Cek status AK-01 umum
        return ($pendaftaran->status_ak01 === 'selesai') ||
            !empty($pendaftaran->tanda_tangan_asesi_ak01) ||
            !empty($pendaftaran->tanda_tangan_asesor_ak01);
    }

    /**
     * Index Galeri Direktori Seluruh Formulir & Ujian (Dialihkan ke portal masing-masing peran)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $skemaId = $request->get('skema_id');
        if (!$skemaId && $request->has('pendaftaran_id') && (int)$request->get('pendaftaran_id') > 0) {
            $skemaId = PendaftaranAsesi::where('id', $request->get('pendaftaran_id'))->value('skema_id');
        }
        if (!$skemaId && $user && !empty($user->skema_id)) {
            $skemaId = $user->skema_id;
        }
        if (!$skemaId) {
            $skemaId = session('active_selected_skema_id');
        }

        if ($user && $user->peran === 'asesor') {
            if ($skemaId) {
                return redirect()->route('asesor.mapa', ['skema_id' => $skemaId]);
            }
            return redirect()->route('asesor.mapa');
        }
        if ($user && $user->peran === 'asesi') {
            return redirect()->route('asesi.tahapan');
        }
        if ($skemaId) {
            return redirect()->route('admin.master-muk.index', ['skema_id' => $skemaId]);
        }
        return redirect()->route('admin.master-muk.index');
    }

    /**
     * Helper fallback redirect jika pendaftaran tidak ditemukan
     */
    protected function redirectFallback(?string $pesan = null)
    {
        $user = auth()->user();
        $skemaId = request('skema_id') ?? ($user?->skema_id) ?? session('active_selected_skema_id');
        $redirect = match ($user?->peran) {
            'asesor' => $skemaId ? redirect()->route('asesor.mapa', ['skema_id' => $skemaId]) : redirect()->route('asesor.mapa'),
            'asesi' => redirect()->route('asesi.tahapan'),
            default => $skemaId ? redirect()->route('admin.master-muk.index', ['skema_id' => $skemaId]) : redirect()->route('admin.master-muk.index')
        };

        if ($pesan) {
            return $redirect->with('info', $pesan);
        }

        return $redirect;
    }

    /* =========================================================================
       INSTRUMEN ASESMEN PRAKTIK & DEMONSTRASI (OBSERVASI LANGSUNG)
       ========================================================================= */

    public function ia01(Request $request, $pendaftaranId = null)
    {
        $user = $request->user();
        abort_unless($user, 401);
        $requestedId = $pendaftaranId ?? $request->get('pendaftaran_id');
        $isMasterMode = is_numeric($requestedId) && (int) $requestedId === 0;

        if ($isMasterMode) {
            abort_unless(in_array($user->peran, ['asesor', 'admin', 'superadmin'], true), 403, 'Master FR.IA.01 hanya tersedia untuk Asesor dan Admin.');
            $skemaId = (int) ($request->input('skema_id') ?: session('active_selected_skema_id') ?: $user->skema_id);
            abort_unless($skemaId > 0, 404, 'Skema sertifikasi untuk master FR.IA.01 tidak ditemukan.');
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->findOrFail($skemaId);
            abort_unless($this->frIa01Service->canManageMaster($user, $skema), 403, 'Anda tidak berwenang mengelola master FR.IA.01 untuk skema ini.');
            $pendaftaran = null;
        } else {
            if (is_numeric($requestedId) && (int) $requestedId > 0) {
                $pendaftaran = $this->frIa01Service->loadAuthorizedRegistration((int) $requestedId, $user);
                $skema = $pendaftaran->skema;
            } else {
                $query = PendaftaranAsesi::query()->with(['asesi.profilAsesi', 'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja', 'asesor', 'jadwal.asesor', 'mapa01', 'mapa02', 'iaPenilaian']);
                if ($user->peran === 'asesi') {
                    $query->where('asesi_id', $user->id);
                } elseif ($user->peran === 'asesor') {
                    $query->where(function ($assigned) use ($user) {
                        $assigned->where('asesor_id', $user->id)
                            ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                    });
                }
                if ($request->filled('skema_id')) {
                    $query->where('skema_id', (int) $request->input('skema_id'));
                }
                $pendaftaran = $query->latest()->first();
                if (!$pendaftaran) {
                    if (in_array($user->peran, ['asesor', 'admin', 'superadmin'], true)) {
                        $isMasterMode = true;
                        $skemaId = (int) ($request->input('skema_id') ?: session('active_selected_skema_id') ?: $user->skema_id);
                        $skema = $skemaId > 0
                            ? SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->find($skemaId)
                            : SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->first();
                        abort_unless($skema, 404, 'Skema sertifikasi untuk master FR.IA.01 tidak ditemukan.');
                        $pendaftaran = null;
                    } else {
                        return $this->redirectFallback('Silakan pilih pendaftaran asesmen FR.IA.01 terlebih dahulu.');
                    }
                } else {
                    $pendaftaran = $this->frIa01Service->loadAuthorizedRegistration((int) $pendaftaran->id, $user);
                    $skema = $pendaftaran->skema;
                }
            }
        }

        $iaRecord = $isMasterMode ? null : IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $masterInst = SchemeMasterInstrument::where('skema_id', $skema->id)
            ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_01'))
            ->first();
        $masterSnapshot = $this->frIa01Service->publishedMasterSnapshot($skema);
        $isMasterConfigured = $masterSnapshot !== null;
        $masterStandards = $isMasterMode
            ? $this->frIa01Service->masterEditorStandards($masterInst, $skema)
            : $this->frIa01Service->executionStandards($pendaftaran, $iaRecord);
        $kukIds = $this->frIa01Service->kukIdsForScheme($skema);
        $pencapaian = (array) ($savedData['pencapaian'] ?? []);
        $evaluatedKukCount = collect($kukIds)->filter(fn ($id) => in_array($pencapaian[$id] ?? null, ['K', 'BK'], true))->count();
        $isAssessmentInProgress = !$isMasterMode && $this->frIa01Service->isAssessmentInProgress($pendaftaran);
        $isAssessmentFinished = !$isMasterMode && $this->frIa01Service->isAssessmentFinished($pendaftaran);
        $canEdit = !$isMasterMode && $this->frIa01Service->canEdit($user, $pendaftaran, $iaRecord);
        $canSignAsesor = !$isMasterMode && $this->frIa01Service->canSignAsesor($user, $pendaftaran, $iaRecord);
        $canManageMaster = $this->frIa01Service->canManageMaster($user, $skema);
        $canSignAsesi = !$isMasterMode && $this->frIa01Service->canSignAsesi($user, $pendaftaran, $iaRecord);
        $isLocked = !$isMasterMode && $this->frIa01Service->isLocked($iaRecord);
        $masterVersion = data_get($masterInst?->additional_metadata, '_draft_version', '');

        return view('formulir.fr-ia-01', compact(
            'pendaftaran',
            'skema',
            'iaRecord',
            'savedData',
            'masterInst',
            'masterStandards',
            'masterVersion',
            'isMasterConfigured',
            'isMasterMode',
            'kukIds',
            'evaluatedKukCount',
            'isAssessmentInProgress',
            'isAssessmentFinished',
            'canEdit',
            'canSignAsesor',
            'canManageMaster',
            'canSignAsesi',
            'isLocked'
        ));
    }

    public function saveMasterIa01(Request $request, int $skemaId)
    {
        $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->findOrFail($skemaId);
        $publish = $request->input('action') === 'publish';
        abort_unless(in_array($request->input('action'), ['draft', 'publish'], true), 422, 'Aksi master FR.IA.01 tidak valid.');

        $this->frIa01Service->saveMaster($skema, $request->user(), $request->all(), $publish);

        return redirect()->route('formulir.ia01', ['pendaftaranId' => 0, 'skema_id' => $skema->id])
            ->with('sukses', $publish
                ? 'Master FR.IA.01 berhasil diterbitkan. Asesmen baru akan menggunakan versi ini.'
                : 'Draft master FR.IA.01 berhasil disimpan tanpa mengubah versi yang sedang diterbitkan.');
    }

    public function storeIa01Signature(Request $request, int $pendaftaranId, string $signer)
    {
        $pendaftaran = $this->frIa01Service->loadAuthorizedRegistration($pendaftaranId, $request->user());
        $record = $this->frIa01Service->sign($pendaftaran, $request->user(), $signer, $request->all());
        if ($record->status === 'completed') {
            $this->evaluasiKelengkapanAsesmen($pendaftaran);
        }

        return back()->with('sukses', $signer === 'asesi'
            ? 'Tanda tangan asesi berhasil disimpan. FR.IA.01 kini terkunci.'
            : 'Tanda tangan asesor berhasil disimpan. Formulir dapat diisi selama asesmen berlangsung.');
    }

    public function showIa01Signature(Request $request, int $pendaftaranId, string $signer)
    {
        $pendaftaran = $this->frIa01Service->loadAuthorizedRegistration($pendaftaranId, $request->user());
        $path = $this->frIa01Service->authorizedSignaturePath($pendaftaran, $request->user(), $signer);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function ia02(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.02')->first() : null;
        $masterInst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_02'))
            ->first();

        $meta = $masterInst ? ($masterInst->additional_metadata ?? []) : [];
        if (is_string($meta)) $meta = json_decode($meta, true) ?: [];

        $skemaNama = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';
        $units = $pendaftaran->skema->unitKompetensi ?? collect();
        $unitTitles = $units->pluck('judul_unit')->filter()->values();

        $saved = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        
        $dataPraktik = [
            'judul_tugas' => $saved['judul_tugas'] ?? ($meta['judul_tugas'] ?? ($masterInst?->title ?? ('Tugas Praktik Demonstrasi ' . $skemaNama))),
            'skenario' => $saved['skenario'] ?? ($meta['scenario'] ?? ($meta['skenario'] ?? '')),
            'peralatan_bahan' => $saved['peralatan_bahan'] ?? ($meta['tools_equipment'] ?? ($meta['peralatan_bahan'] ?? '')),
            'durasi_waktu' => $saved['durasi_waktu'] ?? ($meta['durasi_waktu'] ?? ($masterInst?->time_limit_minutes ? ($masterInst->time_limit_minutes . ' Menit') : '')),
            'instruksi_kerja' => $saved['instruksi_kerja'] ?? ($meta['instruksi_kerja'] ?? ($masterInst?->instructions ? explode("\n", $masterInst->instructions) : [])),
            'standar_hasil' => $saved['standar_hasil'] ?? ($meta['standar_hasil'] ?? []),
            'catatan' => $iaRecord->catatan_asesor ?? ($saved['catatan'] ?? ''),
            'kelompok_skenario' => $saved['kelompok_skenario'] ?? ($meta['kelompok_skenario'] ?? []),
        ];

        if (empty($dataPraktik['skenario']) && !empty($dataPraktik['kelompok_skenario'][1]['skenario'])) {
            $dataPraktik['skenario'] = $dataPraktik['kelompok_skenario'][1]['skenario'];
        }
        if (empty($dataPraktik['peralatan_bahan']) && !empty($dataPraktik['kelompok_skenario'][1]['peralatan'])) {
            $dataPraktik['peralatan_bahan'] = $dataPraktik['kelompok_skenario'][1]['peralatan'];
        }
        if (empty($dataPraktik['durasi_waktu']) && !empty($dataPraktik['kelompok_skenario'][1]['waktu'])) {
            $dataPraktik['durasi_waktu'] = $dataPraktik['kelompok_skenario'][1]['waktu'];
        }

        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-02', compact('pendaftaran', 'iaRecord', 'masterInst', 'dataPraktik', 'isMasterMode'));
    }

    public function ia03(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.03')->first() : null;
        $masterInst = \App\Models\SchemeMasterInstrument::with('questionBanks')
            ->where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_03'))
            ->first();
        $masterQuestions = $masterInst ? $masterInst->questionBanks : collect();
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-03', compact('pendaftaran', 'iaRecord', 'masterInst', 'masterQuestions', 'isMasterMode'));
    }

    /* =========================================================================
       INSTRUMEN ASESMEN PROYEK SINGKAT & REVIU PRODUK
       ========================================================================= */

    public function ia04a(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.04A')->first() : null;
        $masterInst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_04a'))
            ->first();
        $meta = $masterInst ? ($masterInst->additional_metadata ?? []) : [];
        if (is_string($meta)) $meta = json_decode($meta, true) ?: [];
        $saved = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $dataProyek = [
            'judul_proyek' => $saved['judul_proyek'] ?? ($meta['judul_proyek'] ?? ($masterInst?->title ?? 'Penjelasan Proyek Singkat / Kegiatan Terstruktur')),
            'skenario' => $saved['skenario'] ?? ($meta['scenario'] ?? ($meta['skenario'] ?? '')),
            'waktu_menit' => $saved['waktu_menit'] ?? ($meta['waktu_menit'] ?? ($meta['durasi_waktu'] ?? '')),
            'demonstrasi' => $saved['demonstrasi'] ?? ($meta['demonstrasi'] ?? ($meta['deliverables'] ?? '')),
            'waktu_demo' => $saved['waktu_demo'] ?? ($meta['waktu_demo'] ?? ''),
            'umpan_balik' => $saved['umpan_balik'] ?? ($meta['umpan_balik'] ?? ($iaRecord->catatan_asesor ?? '')),
            'instruksi_terstruktur' => $meta['instruksi_terstruktur'] ?? ($masterInst?->instructions ? explode("\n", $masterInst->instructions) : []),
            'durasi_waktu' => $meta['durasi_waktu'] ?? ($masterInst?->time_limit_minutes ? ($masterInst->time_limit_minutes . ' Menit') : ''),
            'peralatan_bahan' => $meta['tools_equipment'] ?? ($meta['peralatan_bahan'] ?? ''),
            'catatan' => $iaRecord->catatan_asesor ?? ($saved['catatan'] ?? ''),
        ];
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-04a', compact('pendaftaran', 'iaRecord', 'masterInst', 'dataProyek', 'isMasterMode'));
    }

    public function ia04b(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.04B')->first() : null;
        $savedData = $iaRecord?->data_jawaban ?? [];
        $masterInst = \App\Models\SchemeMasterInstrument::with('productSpecifications')
            ->where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_04b'))
            ->first();
        $masterSpecs = $masterInst ? $masterInst->productSpecifications : collect();
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-04b', compact('pendaftaran', 'iaRecord', 'masterInst', 'masterSpecs', 'savedData', 'isMasterMode'));
    }

    public function ia11(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.11')->first() : null;
        $savedData = $iaRecord?->data_jawaban ?? [];
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-11', compact('pendaftaran', 'iaRecord', 'savedData', 'isMasterMode'));
    }

    /* =========================================================================
    /* =========================================================================
       BANK SOAL MASTER STANDAR BNSP (IA.05, IA.06, IA.07)
       ========================================================================= */

    public static function getSoalIa05($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_05'))
            ->where('is_active', true);

        if ($skemaId) {
            $query->where('skema_id', $skemaId);
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    if ($q->kriteriaUnjukKerja
                        && $q->kriteriaUnjukKerja->elemenKompetensi?->unitKompetensi?->skema_id !== (int) $skemaId) {
                        continue;
                    }
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'opsi' => $q->options ?? [],
                        'kunci' => $q->correct_answer,
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                        'pembahasan' => $q->rubric_guide ?? '',
                        'gambar' => $q->image_path,
                    ];
                }
                if (!empty($list)) {
                    return $list;
                }
            }

            // Fallback ke additional_metadata jika questionBanks belum ada
            if ($inst && !empty($inst->additional_metadata['data_jawaban']) && is_array($inst->additional_metadata['data_jawaban'])) {
                return $inst->additional_metadata['data_jawaban'];
            }

        } else {
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'opsi' => $q->options ?? [],
                        'kunci' => $q->correct_answer,
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                        'pembahasan' => $q->rubric_guide ?? '',
                        'gambar' => $q->image_path,
                    ];
                }
                return $list;
            }
            if ($inst && !empty($inst->additional_metadata['data_jawaban']) && is_array($inst->additional_metadata['data_jawaban'])) {
                return $inst->additional_metadata['data_jawaban'];
            }
        }

        return [];
    }

    public static function getSoalIa06($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_06'))
            ->where('is_active', true);

        if ($skemaId) {
            $query->where('skema_id', $skemaId);
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    if ($q->kriteriaUnjukKerja
                        && $q->kriteriaUnjukKerja->elemenKompetensi?->unitKompetensi?->skema_id !== (int) $skemaId) {
                        continue;
                    }
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'kunci' => $q->correct_answer,
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                if (!empty($list)) {
                    return $list;
                }
            }

            // Fallback ke additional_metadata jika questionBanks belum ada
            if ($inst && !empty($inst->additional_metadata['data_jawaban']) && is_array($inst->additional_metadata['data_jawaban'])) {
                return $inst->additional_metadata['data_jawaban'];
            }

        } else {
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'kunci' => $q->correct_answer,
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                return $list;
            }
            if ($inst && !empty($inst->additional_metadata['data_jawaban']) && is_array($inst->additional_metadata['data_jawaban'])) {
                return $inst->additional_metadata['data_jawaban'];
            }
        }

        return [];
    }

    public static function getSoalIa07($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_07'))
            ->where('is_active', true);

        if ($skemaId) {
            $query->where('skema_id', $skemaId);
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    if ($q->kriteriaUnjukKerja
                        && $q->kriteriaUnjukKerja->elemenKompetensi?->unitKompetensi?->skema_id !== (int) $skemaId) {
                        continue;
                    }
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'kunci_rujukan' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                if (!empty($list)) {
                    return $list;
                }
            }

        } else {
            $inst = $query->first();
            if ($inst && $inst->questionBanks->count() > 0) {
                $list = [];
                foreach ($inst->questionBanks as $idx => $q) {
                    $num = $q->order ?? ($idx + 1);
                    $list[$num] = [
                        'no' => $num,
                        'pertanyaan' => $q->question_text,
                        'kunci_rujukan' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                return $list;
            }
        }

        return [];
    }

    public function ia05a(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        $iaRecord = \App\Models\IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.05A')
            ->first();
            
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa05($pendaftaran->skema_id);
        if (empty($soalList) && $iaRecord && !empty($iaRecord->data_jawaban)) {
            $soalList = $iaRecord->data_jawaban;
        }
        if (empty($soalList)) {
            $soalList = [
                1 => [
                    'no' => 1,
                    'pertanyaan' => '',
                    'opsi' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''],
                    'kunci' => ''
                ]
            ];
        }
        
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-05a', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    public function ia05b(Request $request, $pendaftaranId = null)
    {
        // Kunci Jawaban Soal PG HANYA untuk asesor / admin / superadmin (ASESI DILARANG AKSES)
        if (auth()->check() && auth()->user()->peran === 'asesi') {
            abort(403, 'Akses Ditolak: Kunci jawaban soal pilihan ganda hanya dapat diakses oleh Asesor Penguji.');
        }

        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa05($pendaftaran->skema_id);

        $iaRecord05c = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05C')->first();
        $iaRecord05b = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05B')->first();
        
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-05b', compact('pendaftaran', 'soalList', 'iaRecord05c', 'iaRecord05b', 'isMasterMode'));
    }

    public function ia05c(Request $request, $pendaftaranId = null)
    {
        // Lembar Ujian PG Asesi
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa05($pendaftaran->skema_id);

        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05C')->first();
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-05c', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    public function ia06a(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        $iaRecord = \App\Models\IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.06A')
            ->first();
            
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa06($pendaftaran->skema_id);
        if (empty($soalList) && $iaRecord && !empty($iaRecord->data_jawaban)) {
            $soalList = $iaRecord->data_jawaban;
        }
        if (empty($soalList)) {
            $soalList = [
                1 => [
                    'no' => 1,
                    'pertanyaan' => '',
                    'kunci' => '',
                    'kuk' => ''
                ]
            ];
        }
        
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-06a', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    public function ia06b(Request $request, $pendaftaranId = null)
    {
        // Kunci Jawaban Esai Khusus Asesor / Admin (ASESI DILARANG AKSES)
        if (auth()->check() && auth()->user()->peran === 'asesi') {
            abort(403, 'Akses Ditolak: Kunci jawaban soal esai hanya dapat diakses oleh Asesor Penguji.');
        }

        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa06($pendaftaran->skema_id);

        $iaRecord06c = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06C')->first();
        $iaRecord06b = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06B')->first();
        
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-06b', compact('pendaftaran', 'soalList', 'iaRecord06c', 'iaRecord06b', 'isMasterMode'));
    }

    public function ia06c(Request $request, $pendaftaranId = null)
    {
        // Lembar Jawaban & Penilaian Esai
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa06($pendaftaran->skema_id);

        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06C')->first();
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-06c', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    public function ia07a(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        $iaRecord = \App\Models\IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('kode_formulir', 'FR.IA.07A')
            ->first();
            
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa07($pendaftaran->skema_id);
        if (empty($soalList)) {
            $soalList = [
                1 => [
                    'no' => 1,
                    'pertanyaan' => '',
                    'kunci' => ''
                ]
            ];
        }
        
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-07a', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    public function ia07(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        
        // STRICT MASTER LOADING
        $soalList = self::getSoalIa07($pendaftaran->skema_id);
        
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.07')->first();
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-07', compact('pendaftaran', 'soalList', 'iaRecord', 'isMasterMode'));
    }

    /* =========================================================================
       INSTRUMEN ASESMEN PORTOFOLIO, WAWANCARA & PIHAK KETIGA
       ========================================================================= */

    public function ia08(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.08')->first();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-08', compact('pendaftaran', 'iaRecord', 'savedData', 'isMasterMode'));
    }

    public function ia09(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.09')->first();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-09', compact('pendaftaran', 'iaRecord', 'savedData', 'isMasterMode'));
    }

    public function ia10(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.10')->first();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        
        // Generate guest token jika belum ada
        if (!$iaRecord || empty($iaRecord->token_akses)) {
            $token = Str::random(40);
            if (!empty($pendaftaran->id)) {
                $iaRecord = IaPenilaian::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.10'],
                    [
                        'token_akses' => $token,
                        'token_expired_at' => now()->addDays(7),
                        'role' => 'supervisor',
                        'status' => 'draft',
                    ]
                );
            } else {
                $iaRecord = new IaPenilaian([
                    'pendaftaran_id' => 0,
                    'kode_formulir' => 'FR.IA.10',
                    'token_akses' => $token,
                    'token_expired_at' => now()->addDays(7),
                    'role' => 'supervisor',
                    'status' => 'draft',
                ]);
            }
        }

        $magicLink = route('formulir.ia10.guest', $iaRecord->token_akses);
        $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');
        return view('formulir.fr-ia-10', compact('pendaftaran', 'iaRecord', 'savedData', 'magicLink', 'isMasterMode'));
    }

    /**
     * Public Magic Link untuk Supervisor Pihak Ketiga (FR.IA.10)
     */
    public function guestIa10($token)
    {
        $iaRecord = IaPenilaian::where('token_akses', $token)->firstOrFail();
        $pendaftaran = $this->dapatkanPendaftaran($iaRecord->pendaftaran_id);
        if (!$pendaftaran) {
            abort(404, 'Data pendaftaran tidak ditemukan.');
        }
        return view('formulir.fr-ia-10-guest', compact('pendaftaran', 'iaRecord', 'token'));
    }

    public function simpanGuestIa10(Request $request, $token)
    {
        $iaRecord = IaPenilaian::where('token_akses', $token)->firstOrFail();

        $dataPayload = [
            'supervisor' => [
                'nama' => $request->nama_supervisor,
                'jabatan' => $request->jabatan,
                'tempat_kerja' => $request->tempat_kerja,
                'telepon' => $request->telepon,
            ],
            'pertanyaan_k3_performa' => [
                'q_k3' => $request->q_k3,
                'q_tim' => $request->q_tim,
                'q_kelola' => $request->q_kelola,
                'q_adaptasi' => $request->q_adaptasi,
                'q_respon' => $request->q_respon,
                'q_kontak' => $request->q_kontak,
            ],
            'wawancara' => [
                'lama_bekerja' => $request->lama_bekerja,
                'testimoni_kinerja' => $request->testimoni_kinerja,
                'catatan_tambahan' => $request->catatan_tambahan,
            ]
        ];

        $iaRecord->update([
            'data_jawaban' => $dataPayload,
            'status' => 'submitted',
            'tanda_tangan' => null,
            'tanggal_tanda_tangan' => null,
        ]);

        return back()->with('sukses', 'Konfirmasi dan verifikasi kinerja asesi telah berhasil dikirimkan ke Asesor Penguji.');
    }

    /* =========================================================================
       ACTION SIMPAN PENILAIAN / PENGERJAAN FORMULIR FR.IA
       ========================================================================= */

    public function simpanIa(Request $request, $kodeForm, $pendaftaranId)
    {
        if (strtoupper(trim($kodeForm)) === 'FR.IA.01' && (int) $pendaftaranId <= 0) {
            abort_unless(
                in_array($request->user()?->peran, ['admin', 'superadmin', 'asesor'], true),
                403,
                'Akses ditolak.'
            );

            return back()->with('error', 'Master FR.IA.01 harus disimpan sebagai draft atau diterbitkan melalui alur master khusus. Tanda tangan dan hasil penilaian hanya disimpan pada execution asesi.');
        }

        if (strtoupper(trim($kodeForm)) === 'FR.IA.01' && (int) $pendaftaranId > 0) {
            $pendaftaran = $this->frIa01Service->loadAuthorizedRegistration((int) $pendaftaranId, $request->user());
            $record = $this->frIa01Service->save($pendaftaran, $request->user(), $request->all());
            $message = $record->status === 'submitted'
                ? 'Hasil FR.IA.01 berhasil disimpan dan menunggu tanda tangan asesi setelah asesmen selesai.'
                : 'Draft Ceklis Observasi Aktivitas (FR.IA.01) berhasil disimpan.';

            return back()->with('sukses', $message);
        }

        $user = auth()->user();
        $pendaftaranIdInt = (int) $pendaftaranId;
        if ($pendaftaranIdInt > 0) {
            $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranIdInt);
            // SERVER-SIDE FINAL LOCK
            if ($pendaftaran->status_pendaftaran === 'selesai' || !empty($pendaftaran->rekomendasi)) {
                abort(403, 'Akses Ditolak: Pendaftaran ini sudah berstatus Final. Data asesmen tidak dapat diubah lagi.');
            }
        } else {
            $pendaftaran = $this->dapatkanPendaftaran(0);
            abort_unless($pendaftaran, 404, 'Data skema sertifikasi tidak ditemukan.');
        }

        $kodeForm = strtoupper(trim($kodeForm));
        $kodeFormValid = [
            'FR.IA.01', 'FR.IA.02', 'FR.IA.03', 'FR.IA.04', 'FR.IA.04A', 'FR.IA.04B',
            'FR.IA.05', 'FR.IA.05A', 'FR.IA.05B', 'FR.IA.05C',
            'FR.IA.06', 'FR.IA.06A', 'FR.IA.06B', 'FR.IA.06C',
            'FR.IA.07', 'FR.IA.08', 'FR.IA.09', 'FR.IA.10', 'FR.IA.11',
        ];
        abort_unless(in_array($kodeForm, $kodeFormValid, true), 422, 'Kode formulir IA tidak valid.');
        $role = $user ? $user->peran : 'asesor';

        if ($pendaftaran->id > 0) {
            // Validasi keamanan peran Asesi
            if ($user && $user->peran === 'asesi') {
                if ($pendaftaran->asesi_id !== $user->id) {
                    abort(403, 'Akses Ditolak: Anda tidak dapat menyimpan data untuk peserta lain.');
                }
                $allowedAsesiForms = ['FR.IA.05C', 'FR.IA.06C', 'FR.APL.01', 'FR.APL.02', 'FR.AK.01'];
                if (!in_array($kodeForm, $allowedAsesiForms)) {
                    abort(403, 'Akses Ditolak: Formulir ini hanya dapat dinilai dan disimpan oleh Asesor Penguji.');
                }

                // Kunci 1x Kirim untuk Ujian Asesi (FR.IA.05C & FR.IA.06C)
                if (in_array($kodeForm, ['FR.IA.05C', 'FR.IA.06C'])) {
                    $alreadyDone = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                        ->where('kode_formulir', $kodeForm)
                        ->whereIn('status', ['completed', 'submitted', 'evaluated'])
                        ->first();
                    if ($alreadyDone) {
                        return back()->with('error', 'Gagal mengirim: Lembar ujian ' . $kodeForm . ' ini telah dikirimkan sebelumnya dan berstatus terkunci. Jawaban hanya dapat dikirimkan 1 (satu) kali.');
                    }
                }
            }

            if ($user && $user->peran === 'asesor') {
                $isAssigned = $pendaftaran->asesor_id === $user->id
                    || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists();
                abort_unless($isAssigned, 403, 'Akses Ditolak: Anda tidak ditugaskan untuk pendaftaran ini.');
            }

            // Cek instrumen aktif (admin/superadmin dilewati; asesor dicek bila ada konfigurasi MAPA.02)
            if ($user && !in_array($user->peran, ['admin', 'superadmin'])) {
                if ($pendaftaran->hasMapa02Config() && !$pendaftaran->isInstrumenAktif($kodeForm)) {
                    abort(422, 'Instrumen ini tidak aktif pada MAPA.02 untuk pendaftaran tersebut.');
                }
            }
        } else {
            // Mode Master Blanko / Template Skema (id == 0)
            abort_unless($user, 403, 'Akses Ditolak.');
            
            if (!in_array($user->peran, ['admin', 'superadmin'], true)) {
                abort_unless($user->peran === 'asesor', 403, 'Akses Ditolak.');
                // Validasi bahwa Asesor memiliki hak pada skema ini
                $skemaId = $pendaftaran->skema_id;
                $hasHakSkema = ($user->skema_id == $skemaId)
                    || JadwalAsesmen::where('asesor_id', $user->id)->where('skema_id', $skemaId)->exists()
                    || PendaftaranAsesi::where('asesor_id', $user->id)->where('skema_id', $skemaId)->exists();
                abort_unless($hasHakSkema, 403, 'Akses Ditolak: Anda tidak ditugaskan sebagai asesor untuk skema sertifikasi ini.');
            }
        }

        $dataPayload = $request->except(['_token', 'tanda_tangan']);
        $tandaTangan = $request->tanda_tangan ?? ($user->tanda_tangan ?? null);
        $status = $request->has('simpan_draft') ? 'draft' : 'completed';
        $suksesMsg = 'Data ' . $kodeForm . ' berhasil disimpan ke sistem.';

        /* ---------------------------------------------------------------------
           1. CEKLIS OBSERVASI AKTIVITAS (FR.IA.01)
           --------------------------------------------------------------------- */
        if ($kodeForm === 'FR.IA.01') {
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('umpan_balik', $request->input('catatan', 'Seluruh instruksi kerja dan demonstrasi praktik telah diobservasi.'));
            $dataPayload = [
                'standar_industri' => $request->input('standar_industri', []),
                'pencapaian' => $request->input('pencapaian', []),
                'penilaian_lanjut' => $request->input('penilaian_lanjut', []),
                'catatan_kuk' => $request->input('catatan_kuk', []),
                'umpan_balik' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Ceklis Observasi Aktivitas (FR.IA.01) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           2. TUGAS PRAKTIK DEMONSTRASI (FR.IA.02)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.02') {
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', '');
            
            $kelompokSkenario = $request->input('kelompok_skenario', []);
            $skenario = $request->input('skenario');
            if (empty($skenario) && isset($kelompokSkenario[1]['skenario'])) {
                $skenario = $kelompokSkenario[1]['skenario'];
            }
            $peralatan = $request->input('peralatan_bahan');
            if (empty($peralatan) && isset($kelompokSkenario[1]['peralatan'])) {
                $peralatan = $kelompokSkenario[1]['peralatan'];
            }
            $durasiWaktu = $request->input('durasi_waktu');
            if (empty($durasiWaktu) && isset($kelompokSkenario[1]['waktu'])) {
                $durasiWaktu = $kelompokSkenario[1]['waktu'];
            }
            $judulTugas = $request->input('judul_tugas');
            $instruksiKerja = $request->input('instruksi_kerja');

            if (empty($kelompokSkenario) && (!empty($skenario) || !empty($peralatan) || !empty($durasiWaktu))) {
                $kelompokSkenario[1] = [
                    'skenario' => $skenario ?? '',
                    'peralatan' => $peralatan ?? '',
                    'waktu' => $durasiWaktu ?? '',
                ];
            }

            $dataPayload = [
                'catatan' => $catatan,
                'kelompok_skenario' => $kelompokSkenario,
                'skenario' => $skenario ?? '',
                'peralatan_bahan' => $peralatan ?? '',
                'durasi_waktu' => $durasiWaktu ?? '',
                'saved_at' => now()->toDateTimeString(),
            ];

            if (!empty($judulTugas)) {
                $dataPayload['judul_tugas'] = $judulTugas;
            }
            if (!empty($instruksiKerja)) {
                $dataPayload['instruksi_kerja'] = $instruksiKerja;
            }

            // TTD Asesor dan Asesi (opsional, jika disubmit form)
            if ($request->filled('ttd_asesi')) {
                $dataPayload['ttd_asesi'] = $request->input('ttd_asesi');
                $dataPayload['tgl_ttd_asesi'] = now()->toDateTimeString();
            }
            if ($request->filled('ttd_asesor')) {
                $dataPayload['ttd_asesor'] = $request->input('ttd_asesor');
            }

            $suksesMsg = 'Tugas Praktik Demonstrasi (FR.IA.02) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           3. PERTANYAAN MENDUKUNG OBSERVASI (FR.IA.03)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.03') {
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('umpan_balik', $request->input('catatan', ''));
            $kelompokSoal = $request->input('kelompok_soal', []);
            
            $flatPertanyaan = $request->input('pertanyaan', []);
            $flatRespon = $request->input('respon', []);
            $flatPencapaian = $request->input('pencapaian', []);

            if (!empty($kelompokSoal) && is_array($kelompokSoal)) {
                foreach ($kelompokSoal as $kIdx => $items) {
                    if (is_array($items)) {
                        foreach ($items as $qIdx => $val) {
                            $key = "{$kIdx}_{$qIdx}";
                            if (isset($val['pertanyaan'])) $flatPertanyaan[$key] = $val['pertanyaan'];
                            if (isset($val['tanggapan'])) $flatRespon[$key] = $val['tanggapan'];
                            if (isset($val['pencapaian'])) $flatPencapaian[$key] = $val['pencapaian'];
                        }
                    }
                }
            }

            $dataPayload = [
                'pertanyaan' => $flatPertanyaan,
                'respon' => $flatRespon,
                'pencapaian' => $flatPencapaian,
                'kelompok_soal' => $kelompokSoal,
                'umpan_balik' => $catatan,
                'rekomendasi' => $rekomendasi,
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Pertanyaan Mendukung Observasi (FR.IA.03) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           4. INSTRUKSI TERSTRUKTUR & PENILAIAN PROYEK (FR.IA.04A & FR.IA.04B)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.04A') {
            $isMasterMode = empty($pendaftaran->id) || $pendaftaran->id == 0 || !$request->filled('pendaftaran_id');

            // Validasi wajib diisi untuk asesor dan admin saat membuat / menyimpan formulir
            if (!$request->has('simpan_draft')) {
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'skenario' => 'required|string',
                    'waktu_menit' => 'required',
                    'demonstrasi' => 'required|string',
                    'waktu_demo' => 'required',
                ], [
                    'skenario.required' => 'Skenario proyek singkat / kegiatan terstruktur lainnya wajib diisi.',
                    'waktu_menit.required' => 'Waktu pengerjaan proyek singkat wajib diisi.',
                    'demonstrasi.required' => 'Hasil proyek singkat yang perlu didemonstrasikan / dipresentasikan wajib diisi.',
                    'waktu_demo.required' => 'Waktu demonstrasi / presentasi proyek wajib diisi.',
                ]);

                if ($validator->fails()) {
                    return back()->withInput()->withErrors($validator)->with('error', $validator->errors()->first());
                }
            }

            $rawWaktuMenit = $request->input('waktu_menit', '90 Menit');
            $cleanWaktuMenit = 0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*jam/i', (string)$rawWaktuMenit, $mJam)) {
                $cleanWaktuMenit = (int) round(((float)$mJam[1]) * 60);
            } elseif (preg_match('/(\d+)\s*menit/i', (string)$rawWaktuMenit, $mMenit)) {
                $cleanWaktuMenit = (int)$mMenit[1];
            } else {
                $cleanWaktuMenit = (int) preg_replace('/[^0-9]/', '', (string)$rawWaktuMenit);
            }
            $waktuMenitFinal = $cleanWaktuMenit > 0 ? $cleanWaktuMenit : 90;

            $rawWaktuDemo = $request->input('waktu_demo', '30 Menit');
            $cleanWaktuDemo = 0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*jam/i', (string)$rawWaktuDemo, $mJam)) {
                $cleanWaktuDemo = (int) round(((float)$mJam[1]) * 60);
            } elseif (preg_match('/(\d+)\s*menit/i', (string)$rawWaktuDemo, $mMenit)) {
                $cleanWaktuDemo = (int)$mMenit[1];
            } else {
                $cleanWaktuDemo = (int) preg_replace('/[^0-9]/', '', (string)$rawWaktuDemo);
            }
            $waktuDemoFinal = $cleanWaktuDemo > 0 ? $cleanWaktuDemo : 30;

            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('umpan_balik', $request->input('catatan', ''));
            $dataPayload = [
                'skenario' => $request->input('skenario'),
                'waktu_menit' => $rawWaktuMenit,
                'demonstrasi' => $request->input('demonstrasi'),
                'waktu_demo' => $rawWaktuDemo,
                'umpan_balik' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];

            // Sinkronkan ke SchemeMasterInstrument (ia_04a) pada Master Mode atau jika master instrument ada
            if ($pendaftaran->skema_id) {
                $inst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
                    ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_04a'))
                    ->first();
                if (!$inst && $isMasterMode) {
                    $inst = \App\Models\SchemeMasterInstrument::create([
                        'skema_id' => $pendaftaran->skema_id,
                        'instrument_code' => 'ia_04a',
                        'title' => 'Penjelasan Proyek Singkat (DIT)',
                        'is_active' => true,
                        'created_by' => auth()->id() ?? $pendaftaran->asesor_id,
                    ]);
                }
                if ($inst && ($isMasterMode || in_array($user?->peran, ['admin', 'superadmin'], true))) {
                    $meta = $inst->additional_metadata ?? [];
                    if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];
                    if ($request->filled('skenario')) {
                        $meta['skenario'] = $request->input('skenario');
                        $meta['scenario'] = $request->input('skenario');
                    }
                    if ($request->filled('waktu_menit')) {
                        $meta['waktu_menit'] = $waktuMenitFinal;
                        $inst->time_limit_minutes = $waktuMenitFinal;
                    }
                    if ($request->filled('demonstrasi')) {
                        $meta['demonstrasi'] = $request->input('demonstrasi');
                        $meta['deliverables'] = $request->input('demonstrasi');
                    }
                    if ($request->filled('waktu_demo')) {
                        $meta['waktu_demo'] = $waktuDemoFinal;
                    }
                    $meta['is_saved'] = true;
                    $inst->additional_metadata = $meta;
                    $inst->save();
                }
            }

            $suksesMsg = 'Penjelasan Proyek Singkat (FR.IA.04A) berhasil disimpan.';
        }
        elseif ($kodeForm === 'FR.IA.04B') {
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', '');
            $dataPayload = [
                'judul_proyek' => $request->input('judul_proyek'),
                'spesifikasi' => $request->input('spesifikasi', []),
                'rekomendasi' => $rekomendasi,
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Penilaian Proyek Singkat (FR.IA.04B) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           4.5. SIMPAN SOAL CUSTOM (FR.IA.05A & FR.IA.06A)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.05A') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', '');
            $dataPayload = $request->input('data_jawaban', []);
            $suksesMsg = 'Daftar Pertanyaan Pilihan Ganda (FR.IA.05A) berhasil disimpan ke Bank Soal.';
        }
        elseif ($kodeForm === 'FR.IA.06A') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', '');
            $dataPayload = $request->input('data_jawaban', []);
            $suksesMsg = 'Daftar Pertanyaan Esai (FR.IA.06A) berhasil disimpan ke Bank Soal.';
        }
        elseif ($kodeForm === 'FR.IA.05B') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', $request->input('catatan_asesor', ''));
            $dataPayload = [
                'rekomendasi' => $rekomendasi,
                'catatan_asesor' => $catatan,
                'evaluasi_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Evaluasi & Kunci Jawaban Pilihan Ganda (FR.IA.05B) berhasil disimpan.';
        }
        elseif ($kodeForm === 'FR.IA.06B') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', $request->input('catatan_asesor', ''));
            $dataPayload = [
                'rekomendasi' => $rekomendasi,
                'catatan_asesor' => $catatan,
                'evaluasi_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Lembar Kunci Jawaban Esai (FR.IA.06B) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           5. AUTO-GRADING UNTUK UJIAN PILIHAN GANDA (FR.IA.05C) & KUNCI ASESOR (FR.IA.05B / FR.IA.05A)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.05C') {
            $masterSoal = self::getSoalIa05($pendaftaran->skema_id);
            
            $jawabanAsesi = $request->input('jawaban', []);
            $totalSoal = count($masterSoal);

            if ($totalSoal === 0) {
                return back()->withInput()->with('error', 'Gagal mengirim: Bank soal pilihan ganda untuk skema ini belum tersedia di sistem.');
            }

            // Validasi: Seluruh butir soal PG wajib dijawab
            if ($role === 'asesi' && !$request->has('simpan_draft')) {
                $terjawabCount = 0;
                foreach ($masterSoal as $no => $item) {
                    if (!empty($jawabanAsesi[$no])) {
                        $terjawabCount++;
                    }
                }
                if ($terjawabCount < $totalSoal) {
                    return back()->withInput()->with('error', 'Gagal menyelesaikan ujian: Anda baru menjawab ' . $terjawabCount . ' dari ' . $totalSoal . ' soal. Seluruh butir soal wajib dijawab sebelum mengumpulkan.');
                }
            }

            $benar = 0;
            $salah = 0;
            $detailHasil = [];

            foreach ($masterSoal as $no => $item) {
                $kunci = strtoupper(trim($item['kunci'] ?? ''));
                $pilihan = isset($jawabanAsesi[$no]) ? strtoupper(trim($jawabanAsesi[$no])) : null;
                $isBenar = ($pilihan !== null && $pilihan === $kunci);

                if ($isBenar) {
                    $benar++;
                } else {
                    $salah++;
                }

                $detailHasil[$no] = [
                    'pertanyaan' => $item['pertanyaan'],
                    'kunci' => $kunci,
                    'pilihan_asesi' => $pilihan,
                    'is_benar' => $isBenar,
                    'kuk' => $item['kuk'] ?? '',
                    'pembahasan' => $item['pembahasan'] ?? ''
                ];
            }

            $skor = $totalSoal > 0 ? (int) round(($benar / $totalSoal) * 100) : 0;
            $rekomendasi = $skor >= 70 ? 'K' : 'BK';
            $catatan = 'Hasil auto-grading: ' . $benar . '/' . $totalSoal . ' soal benar (Skor: ' . $skor . '/100). Rekomendasi: ' . ($rekomendasi === 'K' ? 'Kompeten (Memenuhi Syarat Pengetahuan)' : 'Belum Kompeten (Perlu Remidi Pengetahuan)');

            $dataPayload = [
                'jawaban' => $jawabanAsesi,
                'skor' => $skor,
                'benar' => $benar,
                'salah' => $salah,
                'total' => $totalSoal,
                'detail_hasil' => $detailHasil,
                'waktu_selesai' => now()->toDateTimeString()
            ];

            if ($request->has('simpan_draft')) {
                $status = 'draft';
                $suksesMsg = 'Draft ujian pilihan ganda berhasil disimpan.';
            } else {
                // Simpan juga ke IA.05B untuk referensi pengesahan asesor
                IaPenilaian::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.05B'],
                    [
                        'user_id' => $pendaftaran->asesor_id ?? ($user ? $user->id : null),
                        'role' => 'asesor',
                        'data_jawaban' => $dataPayload,
                        'rekomendasi' => $rekomendasi,
                        'catatan_asesor' => $catatan,
                        'status' => 'completed',
                        'tanda_tangan' => $pendaftaran->tanda_tangan_asesor ?? null,
                        'tanggal_tanda_tangan' => now(),
                    ]
                );
                $suksesMsg = 'Ujian Pilihan Ganda (FR.IA.05C) berhasil dikumpulkan! Nilai Skor Anda: ' . $skor . '/100 (' . ($rekomendasi === 'K' ? 'Kompeten' : 'Belum Kompeten') . '). Jawaban telah dikunci.';
            }
        }

        /* ---------------------------------------------------------------------
           6. WORKFLOW UJIAN ESAI (FR.IA.06C)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.06C') {
            $existingRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06C')->first();
            
            if ($role === 'asesi') {
                $masterSoal = self::getSoalIa06($pendaftaran->skema_id);
                
                $jawabanEsai = $request->input('jawaban_esai', []);
                $totalSoal = count($masterSoal);

                if ($totalSoal === 0) {
                    return back()->withInput()->with('error', 'Gagal mengirim: Bank soal esai untuk skema ini belum tersedia di sistem.');
                }

                // Validasi: Seluruh butir soal esai wajib dijawab
                if (!$request->has('simpan_draft')) {
                    foreach ($masterSoal as $no => $item) {
                        if (!isset($jawabanEsai[$no]) || trim((string)$jawabanEsai[$no]) === '') {
                            return back()->withInput()->with('error', 'Gagal mengirim: Soal Esai No. ' . $no . ' belum Anda jawab. Seluruh butir soal esai wajib diisi sebelum mengumpulkan.');
                        }
                    }
                }

                $status = $request->has('simpan_draft') ? 'draft' : 'submitted';
                $rekomendasi = 'Menunggu Evaluasi';
                $catatan = 'Lembar jawaban esai telah dikirimkan oleh asesi pada ' . now()->format('d-m-Y H:i') . ' dan siap dievaluasi oleh asesor.';
                $dataPayload = [
                    'jawaban_esai' => $jawabanEsai,
                    'submitted_at' => now()->toDateTimeString(),
                ];
                $suksesMsg = $request->has('simpan_draft') ? 'Draft ujian esai berhasil disimpan.' : 'Jawaban Ujian Esai (FR.IA.06C) berhasil dikumpulkan dan dikunci! Status: Menunggu Koreksi oleh Asesor.';
            } else {
                $status = $request->has('simpan_draft') ? 'draft' : 'completed';
                $rekomendasi = $request->input('rekomendasi', 'K');
                $catatan = $request->input('catatan', 'Seluruh butir soal esai telah dinilai dan memenuhi kriteria unjuk kerja.');
                
                $prevJawabanEsai = $existingRecord->data_jawaban['jawaban_esai'] ?? [];
                $inputJawaban = $request->input('jawaban_esai', []);
                $finalJawaban = !empty($prevJawabanEsai) ? $prevJawabanEsai : $inputJawaban;

                $dataPayload = [
                    'jawaban_esai' => $finalJawaban,
                    'pencapaian' => $request->input('pencapaian', []),
                    'catatan_per_soal' => $request->input('catatan_per_soal', []),
                    'evaluated_at' => now()->toDateTimeString()
                ];
                $suksesMsg = 'Penilaian Lembar Ujian Esai (FR.IA.06C) berhasil disimpan!';
            }
        }

        /* ---------------------------------------------------------------------
           7. WORKFLOW PERTANYAAN LISAN / DPL (FR.IA.07)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.07') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', 'Asesi telah menyelesaikan sesi tanya jawab lisan dengan respons yang memuaskan.');
            $dataPayload = [
                'respon_lisan' => $request->input('respon_lisan', []),
                'pencapaian' => $request->input('pencapaian', []),
                'catatan_asesor' => $catatan,
                'evaluated_at' => now()->toDateTimeString()
            ];
            $suksesMsg = 'Penilaian Pertanyaan Lisan (FR.IA.07) berhasil disimpan dan dikirimkan ke Asesi untuk diverifikasi.';
        }

        /* ---------------------------------------------------------------------
           8. CEKLIS VERIFIKASI PORTOFOLIO (FR.IA.08)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.08') {
            if (!$request->has('simpan_draft') && !empty($pendaftaranId)) {
                $request->validate([
                    'dokumen_portofolio' => 'required|array|min:1',
                    'dokumen_portofolio.*.valid' => 'required|in:ya,tidak',
                    'dokumen_portofolio.*.asli' => 'required|in:ya,tidak',
                    'dokumen_portofolio.*.terkini' => 'required|in:ya,tidak',
                    'dokumen_portofolio.*.memadai' => 'required|in:ya,tidak',
                ], [
                    'dokumen_portofolio.required' => 'Daftar dokumen bukti portofolio wajib dinilai.',
                    'dokumen_portofolio.*.valid.required' => 'Aturan bukti Valid (Ya/Tidak) wajib dipilih untuk setiap portofolio.',
                    'dokumen_portofolio.*.asli.required' => 'Aturan bukti Asli (Ya/Tidak) wajib dipilih untuk setiap portofolio.',
                    'dokumen_portofolio.*.terkini.required' => 'Aturan bukti Terkini (Ya/Tidak) wajib dipilih untuk setiap portofolio.',
                    'dokumen_portofolio.*.memadai.required' => 'Aturan bukti Memadai (Ya/Tidak) wajib dipilih untuk setiap portofolio.',
                ]);
            }
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', $request->input('bukti_tambahan', ''));
            $dataPayload = [
                'dokumen_portofolio' => $request->input('dokumen_portofolio', []),
                'klarifikasi_elemen' => $request->input('klarifikasi_elemen', []),
                'bukti_tambahan' => $request->input('bukti_tambahan'),
                'rekomendasi' => $rekomendasi,
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Ceklis Verifikasi Portofolio (FR.IA.08) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           9. PERTANYAAN WAWANCARA (FR.IA.09)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.09') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $pertanyaanWawancara = $request->input('pertanyaan_wawancara', []);

            // Asesor / Admin wajib mengisi pertanyaan wawancara pada setiap elemen saat membuat/menyimpan formulir
            if ($role !== 'asesi' && !$request->has('simpan_draft')) {
                if (empty($pertanyaanWawancara) || !is_array($pertanyaanWawancara)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Semua kolom Daftar Pertanyaan Wawancara wajib diisi sebelum menyimpan formulir FR.IA.09.');
                }
                foreach ($pertanyaanWawancara as $row) {
                    if (empty(trim($row['pertanyaan'] ?? ''))) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Semua kolom Daftar Pertanyaan Wawancara wajib diisi sebelum menyimpan formulir FR.IA.09.');
                    }
                }
            }

            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', $request->input('kesimpulan', ''));
            $dataPayload = [
                'pertanyaan_wawancara' => $pertanyaanWawancara,
                'kesimpulan' => $request->input('kesimpulan'),
                'rekomendasi' => $rekomendasi,
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Pertanyaan Wawancara (FR.IA.09) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           10. VERIFIKASI PIHAK KETIGA (FR.IA.10)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.10') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', '');
            $dataPayload = [
                'supervisor' => [
                    'nama' => $request->input('nama_supervisor'),
                    'jabatan' => $request->input('jabatan'),
                    'tempat_kerja' => $request->input('tempat_kerja'),
                    'alamat' => $request->input('alamat'),
                    'telepon' => $request->input('telepon'),
                ],
                'pertanyaan_k3_performa' => [
                    'q_k3' => $request->input('q_k3'),
                    'q_tim' => $request->input('q_tim'),
                    'q_kelola' => $request->input('q_kelola'),
                    'q_adaptasi' => $request->input('q_adaptasi'),
                    'q_respon' => $request->input('q_respon'),
                    'q_kontak' => $request->input('q_kontak'),
                ],
                'wawancara' => [
                    'hubungan' => $request->input('hubungan'),
                    'lama_bekerja' => $request->input('lama_bekerja'),
                    'kedekatan' => $request->input('kedekatan'),
                    'pengalaman_teknis' => $request->input('pengalaman_teknis'),
                    'testimoni_kinerja' => $request->input('testimoni_kinerja'),
                    'kebutuhan_pelatihan' => $request->input('kebutuhan_pelatihan'),
                    'catatan_tambahan' => $request->input('catatan_tambahan'),
                ],
                'rekomendasi' => $rekomendasi,
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Verifikasi Pihak Ketiga (FR.IA.10) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           11. CEKLIS MENINJAU ASESMEN / REVIU PRODUK (FR.IA.11)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.11') {
            $status = $request->has('simpan_draft') ? 'draft' : 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $rekomendasi = $request->input('rekomendasi', $request->input('rekomendasi_crp', 'kompeten'));
            $catatan = $request->input('catatan', '');
            $dataPayload = [
                'data_teknis' => [
                    'nama_produk' => $request->input('nama_produk'),
                    'standar_industri' => $request->input('standar_industri'),
                    'dimensi_format' => $request->input('dimensi_format'),
                    'bahan_teknologi' => $request->input('bahan_teknologi'),
                    'kapasitas_ukuran' => $request->input('kapasitas_ukuran'),
                    'data_teknis' => $request->input('data_teknis_detail'),
                    'tgl_pengoperasian' => $request->input('tgl_pengoperasian'),
                    'gambar_produk' => $request->input('gambar_produk'),
                ],
                'reviu_spesifikasi' => $request->input('reviu_spesifikasi', []),
                'reviu_dimensi' => $request->input('reviu_dimensi', []),
                'rekomendasi' => $rekomendasi,
                'observasi_detail' => [
                    'kelompok_pekerjaan' => $request->input('obs_kelompok_pekerjaan'),
                    'unit' => $request->input('obs_unit'),
                    'elemen' => $request->input('obs_elemen'),
                    'kuk' => $request->input('obs_kuk'),
                ],
                'validator' => [
                    'penyusun_2_nama' => $request->input('penyusun_2_nama'),
                    'penyusun_2_met' => $request->input('penyusun_2_met'),
                    'penyusun_2_ttd' => $request->input('penyusun_2_ttd'),
                    'validator_1_nama' => $request->input('validator_1_nama'),
                    'validator_1_met' => $request->input('validator_1_met'),
                    'validator_1_ttd' => $request->input('validator_1_ttd'),
                    'validator_2_nama' => $request->input('validator_2_nama'),
                    'validator_2_met' => $request->input('validator_2_met'),
                    'validator_2_ttd' => $request->input('validator_2_ttd'),
                ],
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];
            $suksesMsg = 'Ceklis Reviu Produk (FR.IA.11) berhasil disimpan.';
        }

        /* ---------------------------------------------------------------------
           12. DEFAULT UNTUK FORMULIR LAINNYA
           --------------------------------------------------------------------- */
        else {
            $rekomendasi = $request->rekomendasi ?? ($request->rekomendasi_cvp ?? ($request->rekomendasi_crp ?? 'K'));
            $catatan = $request->catatan ?? ($request->umpan_balik ?? null);
        }

        // Sinkronkan ke SchemeMasterInstrument untuk mode master (pendaftaran_id == 0) atau sinkronisasi IA.02, IA.05A, IA.06A
        if ($pendaftaran->skema_id && ($pendaftaran->id == 0 || in_array($kodeForm, ['FR.IA.02', 'FR.IA.05A', 'FR.IA.06A'], true) || in_array($user?->peran, ['admin', 'superadmin'], true))) {
            $rawCode = strtolower(str_replace(['FR.', 'fr.', '.'], '', $kodeForm));
            $instCode = \App\Models\SchemeMasterInstrument::normalizeCode($rawCode);
            $inst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
                ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases($instCode))
                ->first();
            if (!$inst) {
                $bnspInfo = \App\Models\SchemeMasterInstrument::getBnspInfo($instCode);
                $inst = \App\Models\SchemeMasterInstrument::create([
                    'skema_id' => $pendaftaran->skema_id,
                    'instrument_code' => $instCode,
                    'title' => !empty($dataPayload['judul_tugas']) ? $dataPayload['judul_tugas'] : ($bnspInfo['full_name'] ?? $kodeForm),
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            }
            $meta = $inst->additional_metadata ?? [];
            if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];

            // FILTER PAYLOAD: Cegah Master Data Leak
            $masterPayload = [];
            
            if ($kodeForm === 'FR.IA.02') {
                if (!empty($dataPayload['judul_tugas'])) {
                    $inst->title = $dataPayload['judul_tugas'];
                }
                if (!empty($dataPayload['durasi_waktu'])) {
                    $durasiStr = (string)$dataPayload['durasi_waktu'];
                    $menit = 0;
                    if (preg_match('/(\d+(?:\.\d+)?)\s*jam/i', $durasiStr, $mJam)) {
                        $menit = (int) round(((float)$mJam[1]) * 60);
                    } elseif (preg_match('/(\d+)\s*menit/i', $durasiStr, $mMenit)) {
                        $menit = (int)$mMenit[1];
                    } elseif (preg_match('/(\d+)/', $durasiStr, $mFallback)) {
                        $menit = (int)$mFallback[1];
                    }
                    if ($menit > 0) {
                        $inst->time_limit_minutes = $menit;
                    }
                    $masterPayload['durasi_waktu'] = $dataPayload['durasi_waktu'];
                }
                if (isset($dataPayload['skenario'])) {
                    $masterPayload['scenario'] = $dataPayload['skenario'];
                    $masterPayload['skenario'] = $dataPayload['skenario'];
                }
                if (isset($dataPayload['peralatan_bahan'])) {
                    $masterPayload['tools_equipment'] = $dataPayload['peralatan_bahan'];
                    $masterPayload['peralatan_bahan'] = $dataPayload['peralatan_bahan'];
                }
                if (isset($dataPayload['kelompok_skenario'])) {
                    $masterPayload['kelompok_skenario'] = $dataPayload['kelompok_skenario'];
                }
                if (isset($dataPayload['instruksi_kerja'])) {
                    $masterPayload['instruksi_kerja'] = $dataPayload['instruksi_kerja'];
                }
            } elseif ($kodeForm === 'FR.IA.03') {
                if (isset($dataPayload['pertanyaan'])) $masterPayload['pertanyaan'] = $dataPayload['pertanyaan'];
                if (isset($dataPayload['kelompok_soal'])) $masterPayload['kelompok_soal'] = $dataPayload['kelompok_soal'];
            } elseif ($kodeForm === 'FR.IA.04A' || $kodeForm === 'FR.IA.04B') {
                // IA.04A sudah disave manual sebelumnya. IA.04B tidak memiliki input master.
            } elseif ($kodeForm === 'FR.IA.05A') {
                // Sinkronkan butir soal PG ke master_question_banks
                if (is_array($dataPayload) && !empty($dataPayload)) {
                    $order = 1;
                    $existingQIds = [];
                    foreach ($dataPayload as $item) {
                        $pertanyaan = trim($item['pertanyaan'] ?? '');
                        if (empty($pertanyaan)) {
                            continue;
                        }
                        $opsi = $item['opsi'] ?? [];
                        $kunci = strtoupper(trim($item['kunci'] ?? 'A'));
                        $kukNomor = trim($item['kuk'] ?? '');
                        
                        $kukId = null;
                        if (!empty($kukNomor)) {
                            $kukRow = \App\Models\KriteriaUnjukKerja::whereHas('elemenKompetensi.unitKompetensi', function ($q) use ($pendaftaran) {
                                $q->where('skema_id', $pendaftaran->skema_id);
                            })->where('nomor_kuk', $kukNomor)->first();
                            $kukId = $kukRow?->id;
                        }

                        $qb = \App\Models\MasterQuestionBank::where('scheme_master_instrument_id', $inst->id)
                            ->where('order', $order)
                            ->first();

                        if ($qb) {
                            $qb->update([
                                'question_type' => 'multiple_choice',
                                'question_text' => $pertanyaan,
                                'options' => $opsi,
                                'correct_answer' => $kunci,
                                'kuk_id' => $kukId,
                                'points' => 1,
                            ]);
                        } else {
                            $qb = \App\Models\MasterQuestionBank::create([
                                'scheme_master_instrument_id' => $inst->id,
                                'question_type' => 'multiple_choice',
                                'question_text' => $pertanyaan,
                                'options' => $opsi,
                                'correct_answer' => $kunci,
                                'kuk_id' => $kukId,
                                'points' => 1,
                                'order' => $order,
                            ]);
                        }
                        $existingQIds[] = $qb->id;
                        $order++;
                    }
                    if (!empty($existingQIds)) {
                        \App\Models\MasterQuestionBank::where('scheme_master_instrument_id', $inst->id)
                            ->whereNotIn('id', $existingQIds)
                            ->delete();
                    }
                }
                $masterPayload['data_jawaban'] = $dataPayload;
            } elseif ($kodeForm === 'FR.IA.06A') {
                // Sinkronkan butir soal Esai ke master_question_banks
                if (is_array($dataPayload) && !empty($dataPayload)) {
                    $order = 1;
                    $existingQIds = [];
                    foreach ($dataPayload as $item) {
                        $pertanyaan = trim($item['pertanyaan'] ?? '');
                        if (empty($pertanyaan)) {
                            continue;
                        }
                        $kunci = trim($item['kunci'] ?? ($item['kunci_referensi'] ?? ''));
                        $kukNomor = trim($item['kuk'] ?? '');
                        
                        $kukId = null;
                        if (!empty($kukNomor)) {
                            $kukRow = \App\Models\KriteriaUnjukKerja::whereHas('elemenKompetensi.unitKompetensi', function ($q) use ($pendaftaran) {
                                $q->where('skema_id', $pendaftaran->skema_id);
                            })->where('nomor_kuk', $kukNomor)->first();
                            $kukId = $kukRow?->id;
                        }

                        $qb = \App\Models\MasterQuestionBank::where('scheme_master_instrument_id', $inst->id)
                            ->where('order', $order)
                            ->first();

                        if ($qb) {
                            $qb->update([
                                'question_type' => 'essay',
                                'question_text' => $pertanyaan,
                                'correct_answer' => $kunci,
                                'kuk_id' => $kukId,
                                'points' => 10,
                            ]);
                        } else {
                            $qb = \App\Models\MasterQuestionBank::create([
                                'scheme_master_instrument_id' => $inst->id,
                                'question_type' => 'essay',
                                'question_text' => $pertanyaan,
                                'correct_answer' => $kunci,
                                'kuk_id' => $kukId,
                                'points' => 10,
                                'order' => $order,
                            ]);
                        }
                        $existingQIds[] = $qb->id;
                        $order++;
                    }
                    if (!empty($existingQIds)) {
                        \App\Models\MasterQuestionBank::where('scheme_master_instrument_id', $inst->id)
                            ->whereNotIn('id', $existingQIds)
                            ->delete();
                    }
                }
                $masterPayload['data_jawaban'] = $dataPayload;
            } elseif ($kodeForm === 'FR.IA.05C' || $kodeForm === 'FR.IA.06C') {
                // Ujian Asesi tidak untuk master
            } elseif ($kodeForm === 'FR.IA.07') {
                // Pertanyaan lisan diambil dari skema/unit, tidak ada payload tambahan
            } elseif ($kodeForm === 'FR.IA.08') {
                if (isset($dataPayload['bukti_tambahan'])) $masterPayload['bukti_tambahan'] = $dataPayload['bukti_tambahan'];
            } elseif ($kodeForm === 'FR.IA.09') {
                if (isset($dataPayload['pertanyaan_wawancara']) && is_array($dataPayload['pertanyaan_wawancara'])) {
                    $masterPayload['pertanyaan_wawancara'] = [];
                    foreach ($dataPayload['pertanyaan_wawancara'] as $key => $val) {
                        $masterPayload['pertanyaan_wawancara'][$key] = [
                            'pertanyaan' => $val['pertanyaan'] ?? ''
                        ];
                    }
                }
            } elseif ($kodeForm === 'FR.IA.10') {
                // Saat ini tidak ada input master eksplisit di form 10, jangan simpan apapun
            } elseif ($kodeForm === 'FR.IA.11') {
                // FR.IA.11 menyimpan nama validator di master
                if (isset($dataPayload['validator'])) $masterPayload['validator'] = $dataPayload['validator'];
            } else {
                $masterPayload = $dataPayload;
            }

            $meta = array_merge($meta, $masterPayload);
            $meta['is_saved'] = true;
            $meta['saved_at'] = now()->toDateTimeString();
            $inst->additional_metadata = $meta;
            $inst->save();
        }

        if ($pendaftaran->id > 0) {
            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => $kodeForm],
                [
                    'user_id' => $user ? $user->id : null,
                    'role' => $role,
                    'data_jawaban' => $dataPayload,
                    'rekomendasi' => $rekomendasi,
                    'catatan_asesor' => $catatan,
                    'status' => $status,
                    'tanda_tangan' => $tandaTangan,
                    'tanggal_tanda_tangan' => now(),
                ]
            );

            // Periksa apakah seluruh instrumen asesmen yang disepakati selesai dinilai
            $this->evaluasiKelengkapanAsesmen($pendaftaran);
        }

        return back()->with('sukses', $suksesMsg);
    }

    public function simpanTtdAsesiIa(Request $request, $kodeForm, $pendaftaranId)
    {
        $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);
        
        $iaRecord = IaPenilaian::firstOrCreate(
            ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => $kodeForm],
            [
                'user_id' => auth()->id(),
                'role' => 'asesi',
                'status' => 'draft',
                'data_jawaban' => []
            ]
        );

        $dataPayload = $iaRecord->data_jawaban ?? [];
        $dataPayload['ttd_asesi'] = $request->input('ttd_asesi') ?? auth()->user()->tanda_tangan;
        $dataPayload['tgl_ttd_asesi'] = now()->toDateTimeString();

        $iaRecord->data_jawaban = $dataPayload;
        $iaRecord->save();

        return back()->with('sukses', 'Tanda tangan Anda berhasil disimpan pada ' . $kodeForm);
    }

    /**
     * Mengecek dan memperbarui status pendaftaran menjadi completed_assessment
     */
    private function evaluasiKelengkapanAsesmen($pendaftaran)
    {
        $selesaiCount = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('status', 'completed')
            ->count();

        if ($selesaiCount >= 3) {
            $pendaftaran->update([
                'status_penilaian_ia' => 'completed_assessment'
            ]);
        }
    }

    /* =========================================================================
       FORMULIR STANDAR LAINNYA (MAPA, AK, APL)
       ========================================================================= */

    public function mapa01(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $mapa01 = $pendaftaran->mapa01;
        $skema = $pendaftaran->skema;
        $isMasterMode = ($pendaftaran->id === 0);
        return view('formulir.fr-mapa-01', compact('pendaftaran', 'mapa01', 'skema', 'isMasterMode'));
    }

    public function mapa02(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $mapa02 = $pendaftaran->mapa02;
        $skema = $pendaftaran->skema;
        $isMasterMode = ($pendaftaran->id === 0);
        return view('formulir.fr-mapa-02', compact('pendaftaran', 'mapa02', 'skema', 'isMasterMode'));
    }

    public function ak01(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }

        // STRICT BACKEND GUARD: Jika user adalah asesi, cek apakah APL.02 sudah disetujui
        if (auth()->check() && auth()->user()->peran === 'asesi' && !$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('warning', 'Formulir FR.AK.01 belum tersedia. Silakan menunggu Formulir FR.APL.02 disetujui oleh asesor.');
        }

        $pendaftaran->syncFromMasterAk01IfAvailable();
        $pendaftaran->refresh();

        return view('formulir.fr-ak-01', compact('pendaftaran'));
    }

    public function simpanAk01(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $user = auth()->user();

        if ($user && $user->peran === 'asesi') {
            abort_unless($pendaftaran->asesi_id === $user->id, 403, 'Akses Ditolak: Anda tidak dapat mengubah FR.AK.01 peserta lain.');
        }
        if ($user && $user->peran === 'asesor') {
            $isAssigned = $pendaftaran->asesor_id === $user->id
                || $pendaftaran->jadwal()->where('asesor_id', $user->id)->exists();
            abort_unless($isAssigned, 403, 'Akses Ditolak: Anda tidak ditugaskan untuk pendaftaran ini.');
        }

        // STRICT BACKEND GUARD: Jika user adalah asesi, cek apakah APL.02 sudah disetujui
        if ($user && $user->peran === 'asesi' && !$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('error', 'Formulir FR.AK.01 belum tersedia. Silakan menunggu Formulir FR.APL.02 disetujui oleh asesor.');
        }

        $request->validate([
            'tuk_type' => 'nullable|string',
            'bukti_dikumpulkan' => 'nullable|array',
            'bukti_dikumpulkan_lainnya' => 'nullable|string',
        ]);

        if ($request->has('tuk_type')) {
            $pendaftaran->tuk_type = $request->input('tuk_type');
        }
        if ($request->has('bukti_dikumpulkan')) {
            $pendaftaran->bukti_dikumpulkan = $request->input('bukti_dikumpulkan');
        }
        $pendaftaran->bukti_dikumpulkan_lainnya = $request->input('bukti_dikumpulkan_lainnya');

        if ($user && ($user->peran === 'asesor' || in_array($user->peran, ['admin', 'superadmin']))) {
            $pendaftaran->tanda_tangan_asesor_ak01 = null;
            $pendaftaran->tanggal_ttd_asesor_ak01 = null;
            $pendaftaran->status_ak01 = $pendaftaran->status_ak01 === 'selesai' ? 'selesai' : 'disetujui_asesor';
        } elseif ($user && $user->peran === 'asesi') {
            $pendaftaran->tanda_tangan_asesi_ak01 = null;
            $pendaftaran->tanggal_ttd_asesi_ak01 = null;
            $pendaftaran->status_ak01 = 'selesai';
            $pendaftaran->tanda_tangan_asesor_ak01 = null;
            $pendaftaran->tanggal_ttd_asesor_ak01 = null;
        }

        $pendaftaran->save();

        return redirect()->route('formulir.ak01', $pendaftaran->id)->with('sukses', 'Formulir FR.AK.01 Persetujuan Asesmen & Kerahasiaan berhasil disimpan.');
    }

    public function apl01(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        if (auth()->check() && auth()->user()->peran === 'asesi') {
            return redirect()->route('asesi.formulir', [
                'tab' => 'apl01',
                'pendaftaran_id' => $pendaftaran->id
            ]);
        }
        return view('formulir.fr-apl-01', compact('pendaftaran'));
    }

    public function apl02(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        if (auth()->check() && auth()->user()->peran === 'asesi') {
            return redirect()->route('asesi.formulir', [
                'tab' => 'apl02',
                'pendaftaran_id' => $pendaftaran->id
            ]);
        }
        return view('formulir.fr-apl-02', compact('pendaftaran'));
    }
}
