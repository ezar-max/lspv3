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
        $requestedSkemaId = request()->get('skema_id');

        // 0. JIKA SKEMA_ID DIMINTA EKSPLISIT (PRATINJAU MASTER BLANKO) TANPA PENDAFTARAN_ID SPESIFIK
        if ($requestedSkemaId && !$pendaftaranId && !request()->has('pendaftaran_id')) {
            $dummy = $this->resolveBlankoSkema($requestedSkemaId, $user);
            if ($dummy) {
                return $dummy;
            }
        }

        // 1. JIKA USER ADALAH ASESI: HANYA BOLEH AKSES PENDAFTARAN MILIK AKUNNYA SENDIRI
        if ($user && $user->peran === 'asesi') {
            $userId = $user->id;
            if ($pendaftaranId) {
                $pendaftaran = PendaftaranAsesi::with([
                    'asesi.profilAsesi',
                    'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                    'asesor',
                    'jadwal',
                    'rekomendasi',
                    'mapa01',
                    'mapa02',
                    'jawabanApl02',
                    'iaPenilaian'
                ])->where('id', $pendaftaranId)->where('asesi_id', $userId)->first();

                if (!$pendaftaran) {
                    // Cek jika ID tersebut milik peserta lain -> larang akses!
                    $existsOther = PendaftaranAsesi::where('id', $pendaftaranId)->exists();
                    if ($existsOther) {
                        abort(403, 'Akses Ditolak: Anda hanya diperbolehkan mengakses berkas dan hasil ujian milik akun Anda sendiri.');
                    }
                    abort(404, 'Pendaftaran tidak ditemukan.');
                } else {
                    return $pendaftaran;
                }
            }

            // Ambil pendaftaran aktif/terbaru milik akun asesi ini
            $pendaftaran = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                'asesor',
                'jadwal',
                'rekomendasi',
                'mapa01',
                'mapa02',
                'jawabanApl02',
                'iaPenilaian'
            ])->where('asesi_id', $userId)->latest()->first();

            if ($pendaftaran) {
                return $pendaftaran;
            }

            return $this->resolveBlankoSkema($requestedSkemaId ?? $user->skema_id, $user);
        }

        // UNTUK ASESOR & ADMIN: Gunakan session jika pendaftaran_id tidak ada di URL
        if (!$pendaftaranId) {
            $pendaftaranId = request()->get('pendaftaran_id') ?? session('active_asesor_pendaftaran_id');
        }

        // 2. JIKA USER ADALAH ASESOR
        if ($user && $user->peran === 'asesor') {
            if ($pendaftaranId) {
                $pendaftaran = PendaftaranAsesi::with([
                    'asesi.profilAsesi',
                    'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                    'asesor',
                    'jadwal',
                    'rekomendasi',
                    'mapa01',
                    'mapa02',
                    'jawabanApl02',
                    'iaPenilaian'
                ])->where(function ($q) use ($user) {
                    $q->where('asesor_id', $user->id)
                        ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                })->find($pendaftaranId);

                if ($pendaftaran) {
                    session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                    return $pendaftaran;
                }

                $dummy = $this->resolveBlankoSkema($requestedSkemaId ?? $user->skema_id, $user);
                if ($dummy) {
                    return $dummy;
                }

                abort(403, 'Akses Ditolak: Anda tidak ditugaskan untuk pendaftaran ini.');
            }

            $pendaftaran = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                'asesor',
                'jadwal',
                'rekomendasi',
                'mapa01',
                'mapa02',
                'jawabanApl02',
                'iaPenilaian'
            ])->where(function ($q) use ($user) {
                $q->where('asesor_id', $user->id)
                    ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
            })->latest()->first();

            if ($pendaftaran) {
                session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                return $pendaftaran;
            }

            return $this->resolveBlankoSkema($requestedSkemaId ?? $user->skema_id, $user);
        }

        // 3. ADMIN / SUPERADMIN / FALLBACK
        if ($pendaftaranId) {
            $pendaftaran = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                'asesor',
                'jadwal',
                'rekomendasi',
                'mapa01',
                'mapa02',
                'jawabanApl02',
                'iaPenilaian'
            ])->find($pendaftaranId);

            if ($pendaftaran) {
                session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
                return $pendaftaran;
            }

            return $this->resolveBlankoSkema($requestedSkemaId ?? $user?->skema_id, $user);
        }

        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'rekomendasi',
            'mapa01',
            'mapa02',
            'jawabanApl02',
            'iaPenilaian'
        ])->latest()->first();

        if ($pendaftaran) {
            session(['active_asesor_pendaftaran_id' => $pendaftaran->id]);
            return $pendaftaran;
        }

        return $this->resolveBlankoSkema($requestedSkemaId ?? $user?->skema_id, $user);
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
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first();
        return view('formulir.fr-ia-01', compact('pendaftaran', 'iaRecord'));
    }

    public function ia02(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        return view('formulir.fr-ia-02', compact('pendaftaran'));
    }

    public function ia03(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.03')->first();
        return view('formulir.fr-ia-03', compact('pendaftaran', 'iaRecord'));
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
        return view('formulir.fr-ia-04a', compact('pendaftaran'));
    }

    public function ia04b(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.04B')->first();
        return view('formulir.fr-ia-04b', compact('pendaftaran', 'iaRecord'));
    }

    public function ia11(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.11')->first();
        return view('formulir.fr-ia-11', compact('pendaftaran', 'iaRecord'));
    }

    /* =========================================================================
    /* =========================================================================
       BANK SOAL MASTER STANDAR BNSP (IA.05, IA.06, IA.07)
       ========================================================================= */

    public static function getSoalIa05($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('instrument_code', 'ia05')
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
                if (!empty($list)) {
                    return $list;
                }
            }
        }

        $defaultSample = [
            1 => [
                'no' => 1,
                'pertanyaan' => 'Dalam penerapan standar operasional prosedur di tempat kerja, langkah awal yang harus dipastikan sebelum memulai pengoperasian peralatan adalah...',
                'opsi' => [
                    'a' => 'Memeriksa kelengkapan alat pelindung diri (APD) dan kesiapan alat',
                    'b' => 'Langsung menyalakan saklar daya utama tanpa pengawasan',
                    'c' => 'Mengabaikan petunjuk instruksi kerja',
                    'd' => 'Menyerahkan tugas kepada operator lain tanpa koordinasi',
                ],
                'kunci' => 'a',
                'kuk' => 'KUK 1.1 - Instruksi kerja dan K3 diidentifikasi',
                'pembahasan' => 'Pemeriksaan APD dan kesiapan alat merupakan prosedur K3 standar sebelum operasional.',
                'gambar' => null,
            ],
        ];

        return $defaultSample;
    }

    public static function getSoalIa06($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('instrument_code', 'ia06')
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
                if (!empty($list)) {
                    return $list;
                }
            }
        }

        return [
            1 => [
                'no' => 1,
                'pertanyaan' => 'Jelaskan tahapan prosedur keselamatan dan kesehatan kerja (K3) yang wajib diterapkan sebelum memulai pekerjaan!',
                'kunci_referensi' => '1. Identifikasi potensi bahaya di area kerja; 2. Gunakan APD sesuai standar; 3. Periksa kondisi peralatan dan lingkungan kerja.',
                'kuk' => 'KUK 1.1 - Prosedur K3 diidentifikasi',
            ],
        ];
    }

    public static function getSoalIa07($skemaId = null)
    {
        $query = \App\Models\SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('instrument_code', 'ia07')
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
                if (!empty($list)) {
                    return $list;
                }
            }
        }

        return [
            1 => [
                'no' => 1,
                'pertanyaan' => 'Bagaimana tindakan yang Anda lakukan jika terjadi kondisi darurat atau penyimpangan prosedur operasional saat bekerja?',
                'kunci_rujukan' => 'Menghentikan proses kerja sementara, melapor kepada penanggung jawab, dan mengikuti SOP tanggap darurat.',
                'kuk' => 'KUK 1.1 - Prosedur penanganan darurat diterapkan',
            ],
        ];
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
        return view('formulir.fr-ia-08', compact('pendaftaran', 'iaRecord'));
    }

    public function ia09(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.09')->first();
        return view('formulir.fr-ia-09', compact('pendaftaran', 'iaRecord'));
    }

    public function ia10(Request $request, $pendaftaranId = null)
    {
        $pendaftaran = $this->dapatkanPendaftaran($pendaftaranId ?: $request->get('pendaftaran_id'));
        if (!$pendaftaran) {
            return $this->redirectFallback('Silakan pilih atau daftarkan skema sertifikasi terlebih dahulu.');
        }
        $iaRecord = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.10')->first();
        
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
        return view('formulir.fr-ia-10', compact('pendaftaran', 'iaRecord', 'magicLink'));
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

        abort_unless($pendaftaran->isInstrumenAktif($kodeForm), 422,
            'Instrumen ini tidak aktif pada MAPA.02 untuk pendaftaran tersebut.');

        $dataPayload = $request->except(['_token', 'tanda_tangan']);
        $tandaTangan = $request->tanda_tangan ?? ($user->tanda_tangan ?? null);
        $status = 'completed';
        $suksesMsg = 'Data ' . $kodeForm . ' berhasil disimpan ke sistem.';

        /* ---------------------------------------------------------------------
           1. AUTO-GRADING UNTUK UJIAN PILIHAN GANDA (FR.IA.05C)
           --------------------------------------------------------------------- */
        if ($kodeForm === 'FR.IA.05C') {
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
                $kunci = strtoupper(trim($item['kunci']));
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
           2. WORKFLOW UJIAN ESAI (FR.IA.06C)
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
                    if (!isset($jawabanEsai[$no]) || trim($jawabanEsai[$no]) === '') {
                        return back()->withInput()->with('error', 'Gagal mengirim: Soal Esai No. ' . $no . ' belum Anda jawab. Seluruh butir soal esai wajib diisi sebelum mengumpulkan.');
                    }
                }

                // Asesi mengumpulkan lembar jawaban -> status = submitted
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
                // Asesor melakukan penilaian / grading -> status = completed
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
           3. WORKFLOW PERTANYAAN LISAN / DPL (FR.IA.07)
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
           4. DEFAULT UNTUK FORMULIR LAINNYA
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

    /**
     * Action Simpan Tanda Tangan & Verifikasi Hasil Penilaian oleh Asesi
     */
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

        $tandaTangan = $request->tanda_tangan ?? ($user->tanda_tangan ?? ($pendaftaran->tanda_tangan_asesi ?? null));

        if (!$tandaTangan) {
            $tandaTangan = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="28" fill="%231e3a8a">' . urlencode($user->nama_lengkap) . '</text></svg>';
        }

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
            if (empty($rawTtd)) {
                $rawTtd = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><text x="10" y="38" font-family="Brush Script MT, cursive, sans-serif" font-size="28" fill="%231e3a8a">' . urlencode($user->nama_lengkap) . '</text></svg>';
            }
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
