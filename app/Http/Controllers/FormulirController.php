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
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormulirController extends Controller
{
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
            'jadwal',
            'rekomendasi',
            'mapa01',
            'mapa02',
            'jawabanApl02',
            'iaPenilaian'
        ];

        $pendaftaran = null;

        // =========================================================================
        // KASUS A: SKEMA_ID DIKETAHUI SECARA EKSPLISIT (FORM WAJIB MENGIKUTI SKEMA INI)
        // =========================================================================
        if ($requestedSkemaId) {
            session(['active_selected_skema_id' => $requestedSkemaId]);

            // Jika bukan mode blanko eksplisit, coba cari pendaftaran asesi di skema ini
            if (!$isExplicitBlanko) {
                // 1. Jika ada explicit pendaftaran_id (> 0), cek kecocokan dengan skema
                if ($explicitPendaftaranId && $explicitPendaftaranId > 0) {
                    $query = PendaftaranAsesi::with($withRelations)
                        ->where('id', $explicitPendaftaranId)
                        ->where('skema_id', $requestedSkemaId);

                    if ($user && $user->peran === 'asesi') {
                        $query->where('asesi_id', $user->id);
                    } elseif ($user && $user->peran === 'asesor') {
                        $query->where(function ($q) use ($user) {
                            $q->where('asesor_id', $user->id)
                                ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                        });
                    }

                    $pendaftaran = $query->first();
                }

                // 2. Jika belum ditemukan, cari pendaftaran asesi yang ada di skema ini
                if (!$pendaftaran) {
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
            }

            // 3. Jika tetap belum ada pendaftaran asesi untuk skema ini atau diminta blanko, buka Master Blanko Skema
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
                if ($explicitPendaftaranId && $explicitPendaftaranId > 0) {
                    $pendaftaran = PendaftaranAsesi::with($withRelations)
                        ->where('id', $explicitPendaftaranId)
                        ->where('asesi_id', $user->id)
                        ->first();

                    if (!$pendaftaran) {
                        $existsOther = PendaftaranAsesi::where('id', $explicitPendaftaranId)->exists();
                        if ($existsOther) {
                            abort(403, 'Akses Ditolak: Anda hanya diperbolehkan mengakses berkas dan hasil ujian milik akun Anda sendiri.');
                        }
                        abort(404, 'Pendaftaran tidak ditemukan.');
                    }
                } else {
                    $pendaftaran = PendaftaranAsesi::with($withRelations)
                        ->where('asesi_id', $user->id)
                        ->latest()
                        ->first();

                    if (!$pendaftaran) {
                        $pendaftaran = $this->resolveBlankoSkema($user->skema_id, $user);
                    }
                }
            } elseif ($user && $user->peran === 'asesor') {
                $targetId = ($explicitPendaftaranId && $explicitPendaftaranId > 0) 
                    ? $explicitPendaftaranId 
                    : session('active_asesor_pendaftaran_id');

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
                $targetId = ($explicitPendaftaranId && $explicitPendaftaranId > 0) 
                    ? $explicitPendaftaranId 
                    : session('active_asesor_pendaftaran_id');

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
        if ($user && $user->peran === 'asesor') {
            return redirect()->route('asesor.daftar-peserta');
        }
        if ($user && $user->peran === 'asesi') {
            return redirect()->route('asesi.tahapan');
        }
        return redirect()->route('admin.dokumen.index');
    }

    /**
     * Helper fallback redirect jika pendaftaran tidak ditemukan
     */
    protected function redirectFallback(?string $pesan = null)
    {
        $user = auth()->user();
        $redirect = match ($user?->peran) {
            'asesor' => redirect()->route('asesor.daftar-peserta'),
            'asesi' => redirect()->route('asesi.tahapan'),
            default => redirect()->route('admin.dokumen.index')
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
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first() : null;
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $masterInst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_01'))
            ->first();
        return view('formulir.fr-ia-01', compact('pendaftaran', 'iaRecord', 'savedData', 'masterInst'));
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

        // Default Skenario dinamis dari database skema aktif
        $defaultScenario = "Anda ditugaskan untuk mendemonstrasikan tugas praktik kerja pada skema {$skemaNama}, mencakup unit kompetensi: "
            . ($unitTitles->isNotEmpty() ? $unitTitles->map(fn($t, $i) => ($i + 1) . '. ' . $t)->implode('; ') : 'sesuai unit kompetensi yang dipersyaratkan')
            . " dengan mengacu kepada Standar Operasional Prosedur (SOP), Instruksi Kerja (WI), dan Kriteria Unjuk Kerja (KUK) yang berlaku.";

        // Default Perlengkapan & Peralatan dinamis dari database skema aktif
        $defaultTools = "Peralatan kerja, mesin/alat uji, instrumen, bahan kerja, serta Alat Pelindung Diri (APD) standar yang dipersyaratkan untuk pelaksanaan demonstrasi unit kompetensi pada skema {$skemaNama}.";

        $saved = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        $dataPraktik = [
            'judul_tugas' => $saved['judul_tugas'] ?? ($meta['judul_tugas'] ?? ($masterInst?->title ?? ('Tugas Praktik Demonstrasi ' . $skemaNama))),
            'skenario' => $saved['skenario'] ?? ($meta['scenario'] ?? ($meta['skenario'] ?? $defaultScenario)),
            'peralatan_bahan' => $saved['peralatan_bahan'] ?? ($meta['tools_equipment'] ?? ($meta['peralatan_bahan'] ?? $defaultTools)),
            'durasi_waktu' => $saved['durasi_waktu'] ?? ($meta['durasi_waktu'] ?? (($masterInst?->time_limit_minutes ?? 120) . ' Menit')),
            'instruksi_kerja' => $saved['instruksi_kerja'] ?? ($meta['instruksi_kerja'] ?? ($masterInst?->instructions ? explode("\n", $masterInst->instructions) : [])),
            'standar_hasil' => $saved['standar_hasil'] ?? ($meta['standar_hasil'] ?? []),
            'catatan' => $saved['catatan'] ?? ($iaRecord->catatan_asesor ?? ''),
        ];

        return view('formulir.fr-ia-02', compact('pendaftaran', 'iaRecord', 'masterInst', 'dataPraktik'));
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
        return view('formulir.fr-ia-03', compact('pendaftaran', 'iaRecord', 'masterInst', 'masterQuestions'));
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
            'skenario' => $saved['skenario'] ?? ($meta['skenario'] ?? 'Laksanakan proyek singkat sesuai batasan waktu dan spesifikasi teknis kerja.'),
            'instruksi_terstruktur' => $saved['instruksi_terstruktur'] ?? ($meta['instruksi_terstruktur'] ?? ($masterInst?->instructions ? explode("\n", $masterInst->instructions) : [])),
            'durasi_waktu' => $saved['durasi_waktu'] ?? ($meta['durasi_waktu'] ?? (($masterInst?->time_limit_minutes ?? 180) . ' Menit (3 Jam)')),
            'peralatan_bahan' => $saved['peralatan_bahan'] ?? ($meta['peralatan_bahan'] ?? 'Perangkat dan bahan proyek yang relevan.'),
        ];
        return view('formulir.fr-ia-04a', compact('pendaftaran', 'iaRecord', 'masterInst', 'dataProyek'));
    }

    public function ia04b(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.04B')->first() : null;
        $masterInst = \App\Models\SchemeMasterInstrument::with('productSpecifications')
            ->where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_04b'))
            ->first();
        $masterSpecs = $masterInst ? $masterInst->productSpecifications : collect();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        return view('formulir.fr-ia-04b', compact('pendaftaran', 'iaRecord', 'masterInst', 'masterSpecs', 'savedData'));
    }

    public function ia11(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = $pendaftaran->id ? IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.11')->first() : null;
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        return view('formulir.fr-ia-11', compact('pendaftaran', 'iaRecord', 'savedData'));
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
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                        'pembahasan' => $q->rubric_guide ?? '',
                        'gambar' => $q->image_path,
                    ];
                }
                if (!empty($list)) {
                    return $list;
                }
            }

            // FALLBACK DINAMIS: Jika belum ada soal kustom di MUK, ambil butir soal dari KUK Skema
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->find($skemaId);
            if ($skema && $skema->unitKompetensi->count() > 0) {
                $list = [];
                $qNum = 1;
                foreach ($skema->unitKompetensi as $u) {
                    foreach ($u->elemenKompetensi as $e) {
                        foreach ($e->kriteriaUnjukKerja as $k) {
                            $list[$qNum] = [
                                'no' => $qNum,
                                'pertanyaan' => "Terkait standar kerja pada unit \"{$u->judul_unit}\", tindakan yang tepat dalam memenuhi kriteria: \"{$k->pernyataan_kuk}\" adalah...",
                                'opsi' => [
                                    'A' => "Melaksanakan prosedur kerja sesuai petunjuk teknis operasional dan standar K3 yang berlaku",
                                    'B' => "Menunda pelaksanaan prosedur keselamatan kerja hingga pekerjaan selesai",
                                    'C' => "Melakukan tindakan tanpa mengacu pada instruksi kerja yang sah",
                                    'D' => "Mengabaikan spesifikasi teknis peralatan yang digunakan"
                                ],
                                'kunci' => 'A',
                                'kuk' => "KUK {$k->nomor_kuk} - {$k->pernyataan_kuk} ({$u->kode_unit})",
                                'pembahasan' => "Sesuai standar kompetensi kerja BNSP, langkah kerja pada KUK {$k->nomor_kuk} harus diterapkan secara cermat sesuai SOP kejuruan.",
                                'gambar' => null,
                            ];
                            $qNum++;
                            if ($qNum > 20) break 3;
                        }
                    }
                }
                if (!empty($list)) return $list;
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
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                        'pembahasan' => $q->rubric_guide ?? '',
                        'gambar' => $q->image_path,
                    ];
                }
                return $list;
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
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                if (!empty($list)) {
                    return $list;
                }
            }

            // FALLBACK DINAMIS ESAI DARI ELEMEN SKEMA
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->find($skemaId);
            if ($skema && $skema->unitKompetensi->count() > 0) {
                $list = [];
                $qNum = 1;
                foreach ($skema->unitKompetensi as $u) {
                    foreach ($u->elemenKompetensi as $e) {
                        $list[$qNum] = [
                            'no' => $qNum,
                            'pertanyaan' => "Uraikan langkah kerja sistematis, peralatan kerja yang diperlukan, serta prosedur keselamatan kerja saat Anda melaksanakan: \"{$e->nama_elemen}\" pada unit \"{$u->judul_unit}\" ({$u->kode_unit})!",
                            'kunci_referensi' => "Asesi wajib menguraikan: 1. Persiapan alat, bahan dan SOP; 2. Urutan pelaksanaan teknis; 3. Penerapan standar keselamatan kerja; 4. Verifikasi mutu hasil kerja.",
                            'kuk' => "Elemen {$e->nomor_elemen}: {$e->nama_elemen} ({$u->kode_unit})",
                        ];
                        $qNum++;
                        if ($qNum > 10) break 2;
                    }
                }
                if (!empty($list)) return $list;
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
                        'kunci_referensi' => $q->correct_answer,
                        'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kompetensi Kejuruan',
                    ];
                }
                return $list;
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

            // FALLBACK DINAMIS PERTANYAAN LISAN DARI KUK SKEMA
            $skema = SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->find($skemaId);
            if ($skema && $skema->unitKompetensi->count() > 0) {
                $list = [];
                $qNum = 1;
                foreach ($skema->unitKompetensi as $u) {
                    foreach ($u->elemenKompetensi as $e) {
                        foreach ($e->kriteriaUnjukKerja as $k) {
                            $list[$qNum] = [
                                'no' => $qNum,
                                'pertanyaan' => "Bagaimanakah Anda memastikan dan memverifikasi bahwa kriteria unjuk kerja: \"{$k->pernyataan_kuk}\" terpenuhi secara konsisten saat bekerja?",
                                'kunci_rujukan' => "Asesi mampu menjelaskan parameter pengukuran, urutan verifikasi, serta penanganan kendala teknis sesuai SOP kejuruan.",
                                'kuk' => "KUK {$k->nomor_kuk} - {$k->pernyataan_kuk} ({$u->kode_unit})",
                            ];
                            $qNum++;
                            if ($qNum > 15) break 3;
                        }
                    }
                }
                if (!empty($list)) return $list;
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
        $soalList = self::getSoalIa05($pendaftaran->skema_id);
        return view('formulir.fr-ia-05a', compact('pendaftaran', 'soalList'));
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
        $soalList = self::getSoalIa05($pendaftaran->skema_id);
        $iaRecord05c = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05C')->first();
        $iaRecord05b = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05B')->first();
        
        return view('formulir.fr-ia-05b', compact('pendaftaran', 'soalList', 'iaRecord05c', 'iaRecord05b'));
    }

    public function ia05c(Request $request, $pendaftaranId = null)
    {
        // Lembar Ujian PG Asesi
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $soalList = self::getSoalIa05($pendaftaran->skema_id);
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05C')->first();
        return view('formulir.fr-ia-05c', compact('pendaftaran', 'soalList', 'iaRecord'));
    }

    public function ia06a(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $soalList = self::getSoalIa06($pendaftaran->skema_id);
        return view('formulir.fr-ia-06a', compact('pendaftaran', 'soalList'));
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
        $soalList = self::getSoalIa06($pendaftaran->skema_id);
        $iaRecord06c = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06C')->first();
        $iaRecord06b = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06B')->first();
        
        return view('formulir.fr-ia-06b', compact('pendaftaran', 'soalList', 'iaRecord06c', 'iaRecord06b'));
    }

    public function ia06c(Request $request, $pendaftaranId = null)
    {
        // Lembar Jawaban & Penilaian Esai
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $soalList = self::getSoalIa06($pendaftaran->skema_id);
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06C')->first();
        return view('formulir.fr-ia-06c', compact('pendaftaran', 'soalList', 'iaRecord'));
    }

    public function ia07(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $soalList = self::getSoalIa07($pendaftaran->skema_id);
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.07')->first();
        return view('formulir.fr-ia-07', compact('pendaftaran', 'soalList', 'iaRecord'));
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
        return view('formulir.fr-ia-08', compact('pendaftaran', 'iaRecord', 'savedData'));
    }

    public function ia09(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.09')->first();
        $savedData = $iaRecord ? ($iaRecord->data_jawaban ?? []) : [];
        return view('formulir.fr-ia-09', compact('pendaftaran', 'iaRecord', 'savedData'));
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
        return view('formulir.fr-ia-10', compact('pendaftaran', 'iaRecord', 'savedData', 'magicLink'));
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
            'tanda_tangan' => $request->tanda_tangan_supervisor,
            'tanggal_tanda_tangan' => now(),
        ]);

        return back()->with('sukses', 'Konfirmasi dan verifikasi kinerja asesi telah berhasil dikirimkan ke Asesor Penguji.');
    }

    /* =========================================================================
       ACTION SIMPAN PENILAIAN / PENGERJAAN FORMULIR FR.IA
       ========================================================================= */

    public function simpanIa(Request $request, $kodeForm, $pendaftaranId)
    {
        $user = auth()->user();
        $pendaftaran = PendaftaranAsesi::findOrFail($pendaftaranId);
        $kodeForm = strtoupper(trim($kodeForm));
        $kodeFormValid = [
            'FR.IA.01', 'FR.IA.02', 'FR.IA.03', 'FR.IA.04', 'FR.IA.04A', 'FR.IA.04B',
            'FR.IA.05', 'FR.IA.05A', 'FR.IA.05B', 'FR.IA.05C',
            'FR.IA.06', 'FR.IA.06A', 'FR.IA.06B', 'FR.IA.06C',
            'FR.IA.07', 'FR.IA.08', 'FR.IA.09', 'FR.IA.10', 'FR.IA.11',
        ];
        abort_unless(in_array($kodeForm, $kodeFormValid, true), 422, 'Kode formulir IA tidak valid.');
        $role = $user ? $user->peran : 'asesor';

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

        $dataPayload = $request->except(['_token', 'tanda_tangan']);
        $tandaTangan = $request->tanda_tangan ?? ($user->tanda_tangan ?? null);
        $status = 'completed';
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
            $dataPayload = [
                'judul_tugas' => $request->input('judul_tugas'),
                'skenario' => $request->input('skenario'),
                'peralatan_bahan' => $request->input('peralatan_bahan'),
                'durasi_waktu' => $request->input('durasi_waktu'),
                'instruksi_kerja' => $request->input('instruksi_kerja'),
                'kelompok_skenario' => $request->input('kelompok_skenario', []),
                'penyusun_validator' => $request->input('penyusun_validator', []),
                'catatan' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];

            // Sinkronkan ke SchemeMasterInstrument agar Asesi di Ruang Uji / Tahap 5 langsung mendapatkannya
            if ($pendaftaran->skema_id) {
                $inst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
                    ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_02'))
                    ->first();
                if (!$inst) {
                    $inst = \App\Models\SchemeMasterInstrument::create([
                        'skema_id' => $pendaftaran->skema_id,
                        'instrument_code' => 'ia_02',
                        'title' => $request->input('judul_tugas') ?: 'Tugas Praktik Demonstrasi',
                        'is_active' => true,
                    ]);
                }
                $meta = $inst->additional_metadata ?? [];
                if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];

                if ($request->filled('judul_tugas')) $meta['judul_tugas'] = $request->input('judul_tugas');
                if ($request->filled('skenario')) $meta['skenario'] = $request->input('skenario');
                if ($request->filled('peralatan_bahan')) {
                    $meta['peralatan_bahan'] = is_array($request->peralatan_bahan) 
                        ? $request->peralatan_bahan 
                        : array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$request->peralatan_bahan)));
                }
                if ($request->filled('instruksi_kerja')) {
                    $meta['instruksi_kerja'] = is_array($request->instruksi_kerja)
                        ? $request->instruksi_kerja
                        : array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$request->instruksi_kerja)));
                }
                if ($request->filled('durasi_waktu')) {
                    $numMinutes = (int) preg_replace('/[^0-9]/', '', (string)$request->input('durasi_waktu'));
                    if ($numMinutes > 0) $inst->time_limit_minutes = $numMinutes;
                    $meta['durasi_waktu'] = $request->input('durasi_waktu');
                }
                $inst->additional_metadata = $meta;
                if ($request->filled('judul_tugas')) $inst->title = $request->input('judul_tugas');
                $inst->save();
            }

            $suksesMsg = 'Tugas Praktik Demonstrasi (FR.IA.02) berhasil disimpan dan disinkronkan ke bank instrumen skema.';
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
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('umpan_balik', $request->input('catatan', ''));
            $dataPayload = [
                'skenario' => $request->input('skenario'),
                'waktu_menit' => $request->input('waktu_menit', 90),
                'demonstrasi' => $request->input('demonstrasi'),
                'waktu_demo' => $request->input('waktu_demo', 30),
                'umpan_balik' => $catatan,
                'saved_at' => now()->toDateTimeString(),
            ];

            // Sinkronkan ke SchemeMasterInstrument (ia_04a)
            if ($pendaftaran->skema_id) {
                $inst = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
                    ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_04a'))
                    ->first();
                if (!$inst) {
                    $inst = \App\Models\SchemeMasterInstrument::create([
                        'skema_id' => $pendaftaran->skema_id,
                        'instrument_code' => 'ia_04a',
                        'title' => 'Penjelasan Proyek Singkat (DIT)',
                        'is_active' => true,
                    ]);
                }
                $meta = $inst->additional_metadata ?? [];
                if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];
                if ($request->filled('skenario')) $meta['skenario'] = $request->input('skenario');
                if ($request->filled('waktu_menit')) $meta['waktu_menit'] = (int) preg_replace('/[^0-9]/', '', (string)$request->input('waktu_menit'));
                if ($request->filled('demonstrasi')) $meta['demonstrasi'] = $request->input('demonstrasi');
                if ($request->filled('waktu_demo')) $meta['waktu_demo'] = (int) preg_replace('/[^0-9]/', '', (string)$request->input('waktu_demo'));
                $inst->additional_metadata = $meta;
                $inst->save();
            }

            $suksesMsg = 'Penjelasan Proyek Singkat (FR.IA.04A) berhasil disimpan dan disinkronkan ke bank instrumen skema.';
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
            if ($role === 'asesi') {
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
                    'kuk' => $item['kuk'],
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
                foreach ($masterSoal as $no => $item) {
                    if (!isset($jawabanEsai[$no]) || trim((string)$jawabanEsai[$no]) === '') {
                        return back()->withInput()->with('error', 'Gagal mengirim: Soal Esai No. ' . $no . ' belum Anda jawab. Seluruh butir soal esai wajib diisi sebelum mengumpulkan.');
                    }
                }

                $status = 'submitted';
                $rekomendasi = 'Menunggu Evaluasi';
                $catatan = 'Lembar jawaban esai telah dikirimkan oleh asesi pada ' . now()->format('d-m-Y H:i') . ' dan siap dievaluasi oleh asesor.';
                $dataPayload = [
                    'jawaban_esai' => $jawabanEsai,
                    'submitted_at' => now()->toDateTimeString(),
                    'ttd_asesi' => $user->tanda_tangan ?? ($pendaftaran->tanda_tangan_asesi ?? null),
                    'tgl_ttd_asesi' => now()->format('d-m-Y H:i')
                ];
                $suksesMsg = 'Jawaban Ujian Esai (FR.IA.06C) berhasil dikumpulkan dan dikunci! Status: Menunggu Koreksi oleh Asesor.';
            } else {
                $status = 'completed';
                $rekomendasi = $request->input('rekomendasi', 'K');
                $catatan = $request->input('catatan', 'Seluruh butir soal esai telah dinilai dan memenuhi kriteria unjuk kerja.');
                
                $prevJawabanEsai = $existingRecord->data_jawaban['jawaban_esai'] ?? [];
                $inputJawaban = $request->input('jawaban_esai', []);
                $finalJawaban = !empty($prevJawabanEsai) ? $prevJawabanEsai : $inputJawaban;

                $dataPayload = [
                    'jawaban_esai' => $finalJawaban,
                    'pencapaian' => $request->input('pencapaian', []),
                    'catatan_per_soal' => $request->input('catatan_per_soal', []),
                    'ttd_asesi' => $existingRecord->data_jawaban['ttd_asesi'] ?? ($pendaftaran->tanda_tangan_asesi ?? null),
                    'tgl_ttd_asesi' => $existingRecord->data_jawaban['tgl_ttd_asesi'] ?? null,
                    'evaluated_at' => now()->toDateTimeString()
                ];
                $suksesMsg = 'Penilaian Lembar Ujian Esai (FR.IA.06C) berhasil disimpan!';
            }
        }

        /* ---------------------------------------------------------------------
           7. WORKFLOW PERTANYAAN LISAN / DPL (FR.IA.07)
           --------------------------------------------------------------------- */
        elseif ($kodeForm === 'FR.IA.07') {
            $status = 'completed';
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
            $status = 'completed';
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
            $status = 'completed';
            $rekomendasi = $request->input('rekomendasi', 'K');
            $catatan = $request->input('catatan', $request->input('kesimpulan', ''));
            $dataPayload = [
                'pertanyaan_wawancara' => $request->input('pertanyaan_wawancara', []),
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
            $status = 'completed';
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
            $status = 'completed';
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

        return back()->with('sukses', $suksesMsg);
    }

    public function simpanTtdAsesiIa(Request $request, $kodeForm, $pendaftaranId)
    {
        $user = auth()->user();
        if (!$user || $user->peran !== 'asesi') {
            abort(403, 'Akses Ditolak: Hanya asesi yang dapat menandatangani verifikasi hasil ini.');
        }

        $pendaftaran = PendaftaranAsesi::where('id', $pendaftaranId)->where('asesi_id', $user->id)->firstOrFail();
        $kodeForm = strtoupper(trim($kodeForm));
        abort_unless($pendaftaran->isInstrumenAktif($kodeForm), 422,
            'Instrumen ini tidak aktif pada MAPA.02 untuk pendaftaran tersebut.');

        if ($pendaftaran->hasMapa02Config()) {
            abort_unless($pendaftaran->isInstrumenAktif($kodeForm), 422,
                'Instrumen ini tidak aktif pada MAPA.02 untuk pendaftaran tersebut.');
        }

        $tandaTangan = $request->tanda_tangan ?? ($user->tanda_tangan ?? ($pendaftaran->tanda_tangan_asesi ?? null));

        $iaRecord = IaPenilaian::firstOrNew([
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => $kodeForm,
        ]);

        $jawaban = $iaRecord->data_jawaban ?? [];
        $jawaban['ttd_asesi'] = $tandaTangan;
        $jawaban['tgl_ttd_asesi'] = now()->format('d-m-Y H:i');

        $iaRecord->data_jawaban = $jawaban;
        $iaRecord->save();

        if (empty($pendaftaran->tanda_tangan_asesi)) {
            $pendaftaran->update(['tanda_tangan_asesi' => $tandaTangan]);
        }

        return back()->with('sukses', 'Formulir ' . $kodeForm . ' berhasil diverifikasi dan ditandatangani secara digital.');
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
            $rawTtd = $request->tanda_tangan_asesor_ak01 ?: ($user->tanda_tangan ?? $pendaftaran->tanda_tangan_asesor_ak01);
            $pendaftaran->tanda_tangan_asesor_ak01 = $rawTtd;
            $pendaftaran->tanggal_ttd_asesor_ak01 = now();
            $pendaftaran->status_ak01 = !empty($pendaftaran->tanda_tangan_asesi_ak01) ? 'selesai' : 'disetujui_asesor';
        } elseif ($user && $user->peran === 'asesi') {
            $rawTtd = $request->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi_ak01 ?: ($user->tanda_tangan ?? $pendaftaran->tanda_tangan_asesi));
            if (!empty($rawTtd) && \Illuminate\Support\Str::startsWith($rawTtd, 'data:image') && !\Illuminate\Support\Str::startsWith($rawTtd, 'data:image/svg+xml')) {
                try {
                    $imageParts = explode(';base64,', $rawTtd);
                    if (count($imageParts) === 2) {
                        $imageTypeAux = explode('image/', $imageParts[0]);
                        $imageType = $imageTypeAux[1] ?? 'png';
                        $imageBase64 = base64_decode($imageParts[1]);
                        $fileName = 'signatures/ak01_asesi_' . $pendaftaran->id . '_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $imageType;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                        $rawTtd = 'storage/' . $fileName;
                        $user->update(['tanda_tangan' => $rawTtd]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Gagal simpan signature: ' . $e->getMessage());
                }
            }
            $asesor = $pendaftaran->asesor ?: $pendaftaran->jadwal?->asesor;
            $asesorTtd = $pendaftaran->tanda_tangan_asesor_ak01 ?: ($asesor?->tanda_tangan ?: 'signatures/verified_asesor_auto.png');

            $pendaftaran->tanda_tangan_asesi_ak01 = $rawTtd;
            $pendaftaran->tanggal_ttd_asesi_ak01 = now();
            $pendaftaran->status_ak01 = 'selesai';
            if (empty($pendaftaran->tanda_tangan_asesor_ak01)) {
                $pendaftaran->tanda_tangan_asesor_ak01 = $asesorTtd;
                $pendaftaran->tanggal_ttd_asesor_ak01 = now();
            }
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
