<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsesi;
use App\Models\PendaftaranAsesi;
use App\Models\DokumenAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\ElemenKompetensi;
use App\Models\JawabanApl02;
use App\Models\BuktiApl02;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AsesiTahapanController extends Controller
{
    /**
     * IDs pada request tidak boleh memperluas struktur kompetensi milik skema
     * pendaftaran aktif. Ini mencegah data APL.02 lintas-skema disisipkan lewat
     * request yang dimodifikasi.
     */
    private function validasiStrukturApl02(PendaftaranAsesi $pendaftaran, array $elemenIds = [], array $kukIds = []): void
    {
        $skema = $pendaftaran->skema()->with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->firstOrFail();
        $elemenValid = $skema->unitKompetensi->flatMap->elemenKompetensi->pluck('id')->map(fn ($id) => (string) $id)->all();
        $kukValid = $skema->unitKompetensi->flatMap->elemenKompetensi->flatMap->kriteriaUnjukKerja->pluck('id')->map(fn ($id) => (string) $id)->all();

        abort_unless(empty(array_diff(array_map('strval', $elemenIds), $elemenValid)), 422, 'Elemen kompetensi tidak valid untuk skema pendaftaran ini.');
        abort_unless(empty(array_diff(array_map('strval', $kukIds), $kukValid)), 422, 'KUK tidak valid untuk skema pendaftaran ini.');
    }

    public function index(Request $request)
    {
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);

        $semuaPendaftaran = PendaftaranAsesi::with([
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'dokumen',
            'jadwal.asesor',
            'asesor',
            'rekomendasi.asesor',
            'jawabanApl02',
            'buktiApl02.dokumenAsesi',
        ])->where('asesi_id', $pengguna->id)->latest()->get();

        $pendaftaranId = $request->get('pendaftaran_id');
        $pendaftaran = null;

        if ($pendaftaranId) {
            $pendaftaran = $semuaPendaftaran->firstWhere('id', $pendaftaranId);
            if (!$pendaftaran) {
                abort(PendaftaranAsesi::whereKey($pendaftaranId)->exists() ? 403 : 404,
                    'Pendaftaran yang dipilih tidak dapat diakses.');
            }
        }
        if (!$pendaftaran) {
            $pendaftaran = $semuaPendaftaran->first(function ($p) {
                return $p->status_pendaftaran !== 'ditolak' && $p->rekomendasi_admin_status !== 'tidak_diterima';
            });
        }

        $pendaftaranTerakhirDitolak = $semuaPendaftaran->first(function ($p) {
            return $p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima';
        });

        if ($pendaftaran && ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima')) {
            $pendaftaranTerakhirDitolak = $pendaftaran;
            $pendaftaran = null;
        }

        $skemaList = SkemaSertifikasi::where('status_aktif', true)
            ->with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')
            ->get();

        $ditolakSkemaIds = $semuaPendaftaran->filter(function ($p) {
            return $p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima';
        })->pluck('skema_id')->unique()->toArray();

        $runningSkemaIds = $semuaPendaftaran->filter(function ($p) {
            return !in_array($p->status_pendaftaran, ['ditolak']) && $p->rekomendasi_admin_status !== 'tidak_diterima';
        })->pluck('skema_id')->unique()->toArray();

        $isDraft = false;
        $isDiajukan = false;
        $isDitolakAdmin = false;
        $isAccAdmin = false;
        $isApl02Selesai = false;
        $isDitolakAsesor = false;
        $isAccAsesor = false;
        $isAk01Selesai = false;
        $statusApl02 = 'draft';
        $isApl02Approved = false;
        $isApl02Revision = false;
        $isApl02Rejected = false;
        $isApl02Submitted = false;
        $isApl02UnderReview = false;
        $isApl02Draft = true;

        if ($pendaftaran) {
            $isDraft = in_array($pendaftaran->status_pendaftaran, ['draft', 'revisi']);
            $isDiajukan = ($pendaftaran->status_pendaftaran === 'diajukan');
            $isDitolakAdmin = ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima');
            $isAccAdmin = $pendaftaran->isApprovedByAdmin();
            $isApl02Selesai = ($pendaftaran->jawabanApl02 && $pendaftaran->jawabanApl02->count() > 0);

            $isApl02Approved = $pendaftaran->isApl02Approved();
            $isApl02Revision = $pendaftaran->isApl02Revision();
            $isApl02Submitted = $pendaftaran->isApl02Submitted();
            $isApl02UnderReview = $pendaftaran->isApl02UnderReview();
            $isApl02Draft = $pendaftaran->isApl02Draft();
            $isApl02Rejected = $pendaftaran->isApl02Rejected();

            if ($isApl02Approved) {
                $statusApl02 = 'approved';
            } elseif ($isApl02Revision) {
                $statusApl02 = 'revision';
            } elseif ($isApl02Rejected) {
                $statusApl02 = 'rejected';
            } elseif ($isApl02UnderReview) {
                $statusApl02 = 'under_review';
            } elseif ($isApl02Submitted) {
                $statusApl02 = 'submitted';
            } else {
                $statusApl02 = 'draft';
            }

            $isDitolakAsesor = $isApl02Rejected;
            $isAccAsesor = $isApl02Approved;
            $pendaftaran->syncFromMasterAk01IfAvailable();
            $isAk01Selesai = (!empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']));
        }

        $currentStep = 1;
        if ($isAccAdmin && !$isApl02Approved) {
            $currentStep = 2;
        } elseif ($isApl02Approved) {
            $currentStep = 3;
        }

        $requestedStep = (int) $request->get('step', $currentStep);
        // STRICT BACKEND GUARD: AK.01 (Step 3) HANYA boleh dibuka jika FR.APL.02 sudah disetujui (Approved) oleh Asesor
        if ($requestedStep === 3 && (!$pendaftaran || !$pendaftaran->isAk01Unlocked())) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran?->id, 'step' => 2])
                ->with('warning', 'Formulir FR.AK.01 belum tersedia. Silakan menunggu Formulir FR.APL.02 disetujui oleh asesor.');
        }

        if ($requestedStep >= 1 && $requestedStep <= 3) {
            $currentStep = $requestedStep;
        }

        $draftData = [
            'nama_lengkap' => $pengguna->nama_lengkap,
            'nik' => $profil->nik,
            'tempat_lahir' => $profil->tempat_lahir,
            'tanggal_lahir' => $profil->tanggal_lahir,
            'jenis_kelamin' => $profil->jenis_kelamin ?? 'Laki-laki',
            'kebangsaan' => $pendaftaran->kebangsaan ?? 'Indonesia',
            'pendidikan_terakhir' => $profil->pendidikan_terakhir,
            'alamat' => $profil->alamat,
            'kode_pos' => $pendaftaran->kode_pos ?? null,
            'no_telp_rumah' => $pendaftaran->no_telp_rumah ?? null,
            'nomor_telepon' => $pengguna->nomor_telepon,
            'nama_sekolah_instansi' => $pendaftaran->nama_perusahaan ?? $profil->nama_sekolah_instansi,
            'pekerjaan' => $pendaftaran->jabatan_perusahaan ?? $profil->pekerjaan,
            'alamat_kantor' => $pendaftaran->alamat_kantor ?? null,
            'kode_pos_kantor' => $pendaftaran->kode_pos_kantor ?? null,
            'telp_kantor' => $pendaftaran->telp_kantor ?? null,
            'fax_kantor' => $pendaftaran->fax_kantor ?? null,
            'email_kantor' => $pendaftaran->email_kantor ?? null,
            'skema_id' => $pendaftaran ? $pendaftaran->skema_id : null,
            'tujuan_asesmen' => $pendaftaran ? $pendaftaran->tujuan_asesmen : 'Sertifikasi',
            'bukti_persyaratan_dasar' => $pendaftaran ? ($pendaftaran->bukti_persyaratan_dasar ?? []) : [],
            'bukti_administratif' => $pendaftaran ? ($pendaftaran->bukti_administratif ?? []) : [],
            'tanda_tangan_asesi' => $pendaftaran ? ($pendaftaran->tanda_tangan_asesi ?? $pengguna->tanda_tangan) : $pengguna->tanda_tangan,
        ];

        $jawabanMap = collect();
        if ($pendaftaran && $pendaftaran->jawabanApl02) {
            $jawabanMap = $pendaftaran->jawabanApl02->keyBy('elemen_id');
        }

        $buktiApl02Map = collect();
        if ($pendaftaran && $pendaftaran->buktiApl02) {
            $buktiApl02Map = $pendaftaran->buktiApl02->groupBy('elemen_id');
        }

        $verifikasiKukMap = collect();
        if ($pendaftaran && $pendaftaran->verifikasiKukApl02) {
            $verifikasiKukMap = $pendaftaran->verifikasiKukApl02->keyBy('kuk_id');
        }

        $penilaianUnitMap = collect();
        if ($pendaftaran && $pendaftaran->penilaian) {
            $penilaianUnitMap = $pendaftaran->penilaian->keyBy('unit_id');
        }

        $progressPersen = 0;
        if ($isDraft && !$isDiajukan) $progressPersen = 0;
        elseif ($isDiajukan && !$isAccAdmin) $progressPersen = 25;
        elseif ($isAccAdmin && ($statusApl02 === 'draft' || $statusApl02 === 'revision')) $progressPersen = 40;
        elseif ($statusApl02 === 'submitted' || $statusApl02 === 'under_review') $progressPersen = 60;
        elseif ($isApl02Approved && !$isAk01Selesai) $progressPersen = 80;
        elseif ($isAk01Selesai) $progressPersen = 100;

        $dokumenList = $pendaftaran ? $pendaftaran->dokumen : collect();
        $dokumenTeknis = collect($dokumenList)->filter(function ($dok) {
            $jenis = strtolower($dok->jenis_dokumen ?? '');
            return !str_contains($jenis, 'ktp') && !str_contains($jenis, 'pasfoto') && !str_contains($jenis, 'foto');
        });

        $totalElemen = ($pendaftaran && $pendaftaran->skema) 
            ? $pendaftaran->skema->unitKompetensi->sum(fn($u) => $u->elemenKompetensi->count()) 
            : 0;

        return view('asesi.tahapan.index', compact(
            'pengguna',
            'profil',
            'semuaPendaftaran',
            'pendaftaran',
            'pendaftaranTerakhirDitolak',
            'skemaList',
            'ditolakSkemaIds',
            'runningSkemaIds',
            'currentStep',
            'isDraft',
            'isDiajukan',
            'isDitolakAdmin',
            'isAccAdmin',
            'isApl02Selesai',
            'isDitolakAsesor',
            'isAccAsesor',
            'isAk01Selesai',
            'statusApl02',
            'isApl02Approved',
            'isApl02Revision',
            'isApl02Rejected',
            'isApl02Submitted',
            'isApl02UnderReview',
            'isApl02Draft',
            'draftData',
            'jawabanMap',
            'buktiApl02Map',
            'verifikasiKukMap',
            'penilaianUnitMap',
            'dokumenList',
            'dokumenTeknis',
            'progressPersen',
            'totalElemen'
        ));
    }

    public function storeApl01(Request $request)
    {
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string',
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'tujuan_asesmen' => 'required|string',
            'file_rapor' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'file_pkl' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'file_ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'file_foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'tanda_tangan_asesi' => 'nullable|string',
        ], [
            'skema_id.required' => 'Silakan pilih skema sertifikasi.',
            'file_rapor.max' => 'Ukuran file maksimal 5MB.',
            'file_pkl.max' => 'Ukuran file maksimal 5MB.',
            'file_ktp.max' => 'Ukuran file maksimal 5MB.',
            'file_foto.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $isDitolakSkema = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('skema_id', $request->skema_id)
            ->where(function ($q) {
                $q->where('status_pendaftaran', 'ditolak')
                    ->orWhere('rekomendasi_admin_status', 'tidak_diterima');
            })->exists();

        if ($isDitolakSkema) {
            return back()->with('error', 'Skema sertifikasi ini telah Ditolak untuk akun Anda dan tidak dapat dipilih kembali.');
        }

        $pendaftaranId = $request->input('pendaftaran_id');
        $pendaftaran = null;

        if ($pendaftaranId) {
            $pendaftaran = PendaftaranAsesi::with('dokumen')->where('asesi_id', $pengguna->id)->find($pendaftaranId);
        }
        if (!$pendaftaran) {
            $pendaftaran = PendaftaranAsesi::with('dokumen')
                ->where('asesi_id', $pengguna->id)
                ->whereIn('status_pendaftaran', ['draft', 'revisi'])
                ->first();
        }

        // GUARD: Jika formulir FR.APL.01 telah disetujui (ACC) oleh Admin LSP, formulir bersifat Read-Only
        if ($pendaftaran && $pendaftaran->isApprovedByAdmin()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('info', 'Formulir FR.APL.01 Anda telah disetujui (ACC) oleh Admin LSP dan berstatus Read-Only sehingga tidak dapat diubah.');
        }

        $pengguna->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_telepon' => $request->nomor_telepon,
        ]);

        $profil->update([
            'nik' => $request->nik,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'pendidikan_terakhir' => $request->pendidikan_terakhir ?? $profil->pendidikan_terakhir,
            'pekerjaan' => $request->pekerjaan ?? $profil->pekerjaan,
            'nama_sekolah_instansi' => $request->nama_sekolah_instansi ?? $profil->nama_sekolah_instansi,
        ]);

        if (!$pendaftaran || $pendaftaran->skema_id != $request->skema_id) {
            $sudahAda = PendaftaranAsesi::where('asesi_id', $pengguna->id)
                ->where('skema_id', $request->skema_id)
                ->whereNotIn('status_pendaftaran', ['draft', 'revisi', 'ditolak'])
                ->exists();
            if ($sudahAda) {
                return back()->with('error', 'Anda sudah memiliki pendaftaran aktif pada skema ini.');
            }
        }

        $isAjukan = ($request->input('aksi') === 'ajukan' || $request->input('aksi') === 'simpan_lanjut');

        $dokumenRaporAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')->count() > 0;
        $dokumenPklAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Portofolio Sertifikat/Karya')->count() > 0;
        $dokumenKtpAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'KTP / Kartu Pelajar')->count() > 0;
        $dokumenFotoAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Pasfoto 3x4 Background Merah')->count() > 0;

        if ($isAjukan) {
            if (!$request->hasFile('file_rapor') && !$dokumenRaporAda) {
                return back()->with('error', 'Silakan unggah berkas Foto Copy Rapor / Ijazah terlebih dahulu.');
            }
            if (!$request->hasFile('file_pkl') && !$dokumenPklAda) {
                return back()->with('error', 'Silakan unggah berkas Foto Copy Sertifikat PKL / Pelatihan terlebih dahulu.');
            }
            if (!$request->hasFile('file_ktp') && !$dokumenKtpAda) {
                return back()->with('error', 'Silakan unggah berkas KTP / Kartu Pelajar terlebih dahulu.');
            }
            if (!$request->hasFile('file_foto') && !$dokumenFotoAda) {
                return back()->with('error', 'Silakan unggah Pasfoto 3x4 (Background Merah) terlebih dahulu.');
            }
            if (!$request->filled('tanda_tangan_asesi') && empty($pendaftaran?->tanda_tangan_asesi) && empty($pengguna->tanda_tangan)) {
                return back()->with('error', 'Silakan buat Tanda Tangan Digital pada formulir permohonan.');
            }
        }

        $nomorPendaftaran = $pendaftaran ? $pendaftaran->nomor_pendaftaran : ('APL01-' . date('Ymd') . '-' . rand(1000, 9999));
        $statusPendaftaran = $isAjukan ? 'diajukan' : ($pendaftaran ? $pendaftaran->status_pendaftaran : 'draft');
        $ttd = $request->tanda_tangan_asesi ?: ($pendaftaran?->tanda_tangan_asesi ?: $pengguna->tanda_tangan);

        $dataPendaftaran = [
            'nomor_pendaftaran' => $nomorPendaftaran,
            'asesi_id' => $pengguna->id,
            'skema_id' => $request->skema_id,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => $request->tujuan_asesmen,
            'tujuan_asesmen_lainnya' => $request->tujuan_asesmen_lainnya ?? null,
            'kebangsaan' => $request->kebangsaan ?? 'Indonesia',
            'kode_pos' => $request->kode_pos ?? null,
            'no_telp_rumah' => $request->no_telp_rumah ?? null,
            'nama_perusahaan' => $request->nama_sekolah_instansi ?? null,
            'jabatan_perusahaan' => $request->pekerjaan ?? null,
            'alamat_kantor' => $request->alamat_kantor ?? null,
            'kode_pos_kantor' => $request->kode_pos_kantor ?? null,
            'telp_kantor' => $request->telp_kantor ?? null,
            'fax_kantor' => $request->fax_kantor ?? null,
            'email_kantor' => $request->email_kantor ?? null,
            'bukti_persyaratan_dasar' => $request->bukti_dasar ?? ($pendaftaran->bukti_persyaratan_dasar ?? []),
            'bukti_administratif' => $request->bukti_admin ?? ($pendaftaran->bukti_administratif ?? []),
            'tanda_tangan_asesi' => $ttd,
            'tanggal_ttd_asesi' => $ttd ? now() : null,
            'status_pendaftaran' => $statusPendaftaran,
            'request_perbaikan' => false,
            'catatan_request_perbaikan' => null,
        ];

        if ($pendaftaran) {
            $pendaftaran->update($dataPendaftaran);
        } else {
            $pendaftaran = PendaftaranAsesi::create($dataPendaftaran);
        }

        $uploads = [
            'file_rapor' => 'Ijazah / Rapor Terakhir',
            'file_pkl' => 'Portofolio Sertifikat/Karya',
            'file_ktp' => 'KTP / Kartu Pelajar',
            'file_foto' => 'Pasfoto 3x4 Background Merah',
        ];

        foreach ($uploads as $field => $jenisDokumen) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $ext = strtolower($file->getClientOriginalExtension());
                $namaFile = \Illuminate\Support\Str::slug($jenisDokumen, '_') . '_' . $pengguna->id . '_' . time() . '.' . $ext;
                $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');

                DokumenAsesi::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id, 'jenis_dokumen' => $jenisDokumen],
                    [
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'status_verifikasi' => 'menunggu',
                        'catatan' => null,
                    ]
                );
            }
        }

        LogAktivitas::catat('Pengisian FR.APL.01', ($isAjukan ? 'Mengajukan' : 'Menyimpan draft') . ' pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke seluruh Admin & Superadmin saat asesi mengajukan permohonan ACC FR.APL.01
        if ($isAjukan) {
            $admins = Pengguna::whereIn('peran', ['admin', 'superadmin'])->where('aktif', true)->get();
            $skema = SkemaSertifikasi::find($request->skema_id);
            $skemaNama = $skema ? $skema->nama_skema : 'Skema Sertifikasi';
            foreach ($admins as $admin) {
                $admin->notify(new SystemAlert(
                    'Permohonan ACC FR.APL.01: ' . $pengguna->nama_lengkap,
                    "Asesi {$pengguna->nama_lengkap} telah mengajukan berkas formulir FR.APL.01 ({$skemaNama}) dan meminta verifikasi (ACC) permohonan.",
                    route('admin.detail-verifikasi', $pendaftaran->id),
                    'apl01_submission',
                    [
                        'pendaftaran_id' => $pendaftaran->id,
                        'asesi_id' => $pengguna->id,
                        'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
                    ]
                ));
            }
        }

        $nextStep = $isAjukan ? 2 : 1;
        return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => $nextStep])
            ->with('sukses', $isAjukan
                ? 'Formulir FR.APL.01 berhasil diajukan! Anda dapat melanjutkan ke asesmen mandiri FR.APL.02.'
                : 'Draft FR.APL.01 berhasil disimpan.');
    }

    public function storeApl02(Request $request)
    {
        $pengguna = auth()->user();

        $pendaftaranId = $request->input('pendaftaran_id');
        if ($pendaftaranId) {
            $pendaftaran = PendaftaranAsesi::with('skema.unitKompetensi.elemenKompetensi')->where('asesi_id', $pengguna->id)->findOrFail($pendaftaranId);
        } else {
            $pendaftaran = PendaftaranAsesi::with('skema.unitKompetensi.elemenKompetensi')->where('asesi_id', $pengguna->id)->latest()->firstOrFail();
        }

        $isDitolak = ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima');
        if ($isDitolak) {
            return redirect()->route('asesi.tahapan', ['step' => 1])->with('error', 'Pendaftaran telah Ditolak. Silakan daftar skema lainnya.');
        }

        // KUNCI: Formulir FR.APL.02 HANYA dapat diisi jika berkas FR.APL.01 sudah di-ACC / diverifikasi oleh Admin LSP
        $isAccAdmin = $pendaftaran->isApprovedByAdmin();
        if (!$isAccAdmin) {
            return redirect()->route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id])
                ->with('error', 'Formulir FR.APL.02 Asesmen Mandiri belum dapat diisi karena berkas permohonan FR.APL.01 Anda belum disetujui / diverifikasi (ACC) oleh Admin LSP.');
        }

        // KUNCI: Cek apakah formulir FR.APL.02 sedang dikunci (Approved, Rejected, atau Menunggu Pemeriksaan)
        if ($pendaftaran->isApl02Approved()) {
            return redirect()->route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id])
                ->with('error', 'Formulir FR.APL.02 telah disetujui oleh Asesor dan dikunci.');
        }

        if ($pendaftaran->isApl02Rejected()) {
            return redirect()->route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id])
                ->with('error', 'Formulir FR.APL.02 telah ditolak oleh Asesor dan tidak dapat diajukan kembali.');
        }

        if ($pendaftaran->isApl02Submitted() || $pendaftaran->isApl02UnderReview()) {
            return redirect()->route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id])
                ->with('error', 'Formulir FR.APL.02 sedang dalam proses pemeriksaan oleh Asesor.');
        }

        $this->validasiStrukturApl02(
            $pendaftaran,
            array_keys($request->input('penilaian', [])),
            array_keys($request->input('penilaian_kuk', []))
        );

        $request->validate([
            'penilaian' => 'required|array',
            'penilaian.*' => 'required|in:K,BK',
            'bukti_foto.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'tanda_tangan_asesi' => 'nullable|string',
        ], [
            'penilaian.required' => 'Silakan beri penilaian (K atau BK) pada seluruh elemen kompetensi.',
            'penilaian.*.required' => 'Penilaian K atau BK wajib dipilih untuk setiap elemen kompetensi.',
            'penilaian.*.in' => 'Pilihan penilaian harus berupa Kompeten (K) atau Belum Kompeten (BK).',
        ]);

        // Cek kelengkapan seluruh elemen kompetensi skema (tidak boleh ada elemen yang terlewat)
        if ($pendaftaran->skema) {
            $allElemenIds = $pendaftaran->skema->unitKompetensi->flatMap->elemenKompetensi->pluck('id')->toArray();
            $submittedElemenIds = array_keys($request->penilaian ?? []);
            $diff = array_diff($allElemenIds, $submittedElemenIds);
            if (!empty($diff)) {
                return redirect()->route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id])
                    ->withInput()
                    ->with('error', 'Masih ada data yang wajib diisi. Silakan lengkapi penilaian seluruh butir KUK terlebih dahulu.');
            }
        }

        $isWasRevision = $pendaftaran->isApl02Revision();
        $ttdAsesi = $request->tanda_tangan_asesi ?: ($pendaftaran->tanda_tangan_asesi ?: $pengguna->tanda_tangan);
        $pendaftaran->update([
            'status_apl02' => 'submitted',
            'tanggal_submit_apl02' => now(),
            'rekomendasi_asesor_status' => null, // Reset rekomendasi jika perbaikan/submit ulang
            'tanda_tangan_asesi' => $ttdAsesi,
            'tanggal_ttd_asesi' => now(),
        ]);

        foreach ($request->penilaian as $elemenId => $nilai) {
            // Handle any direct file upload in standard POST fallback
            if ($request->hasFile("bukti_foto.{$elemenId}")) {
                $files = is_array($request->file("bukti_foto.{$elemenId}")) 
                    ? $request->file("bukti_foto.{$elemenId}") 
                    : [$request->file("bukti_foto.{$elemenId}")];

                foreach ($files as $file) {
                    $namaAsli = $file->getClientOriginalName();
                    $namaFileUnik = 'bukti_apl02_' . $pendaftaran->id . '_elem_' . $elemenId . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs('bukti_apl02', $file, $namaFileUnik);

                    BuktiApl02::create([
                        'pendaftaran_id' => $pendaftaran->id,
                        'elemen_id' => $elemenId,
                        'dokumen_id' => null,
                        'sumber' => 'upload',
                        'nama_file_asli' => $namaAsli,
                        'nama_file_tersimpan' => $namaFileUnik,
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Sync fallback bukti_relevan
            $firstBukti = BuktiApl02::where('pendaftaran_id', $pendaftaran->id)
                ->where('elemen_id', $elemenId)
                ->first();
            $fallbackPath = $firstBukti ? ($firstBukti->sumber === 'apl01' ? ($firstBukti->dokumenAsesi->file_path ?? null) : $firstBukti->file_path) : ($request->input("bukti_relevan.{$elemenId}") ?? null);

            JawabanApl02::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemenId],
                [
                    'nilai_kompetensi' => $nilai,
                    'bukti_relevan' => $fallbackPath,
                ]
            );
        }

        // Simpan klaim mandiri per butir KUK ke tabel verifikasi_kuk_apl02 (klaim asesi)
        if ($request->has('penilaian_kuk') && is_array($request->penilaian_kuk)) {
            foreach ($request->penilaian_kuk as $kukId => $nilaiKuk) {
                $kukModel = \App\Models\KriteriaUnjukKerja::find($kukId);
                if ($kukModel) {
                    \App\Models\VerifikasiKukApl02::updateOrCreate(
                        ['pendaftaran_id' => $pendaftaran->id, 'kuk_id' => $kukId],
                        [
                            'elemen_id' => $kukModel->elemen_id,
                            'nilai_kompetensi' => $nilaiKuk,
                            'is_verified' => false, // Reset verifikasi ke false untuk diperiksa ulang oleh asesor
                        ]
                    );
                }
            }
        }

        if ($isWasRevision) {
            LogAktivitas::catat('Pengajuan Ulang FR.APL.02', 'Asesi mengajukan ulang FR.APL.02 #' . $pendaftaran->nomor_pendaftaran . ' setelah perbaikan revisi.');
            $pesanSukses = 'Formulir FR.APL.02 berhasil diajukan ulang ke Asesor setelah perbaikan. Mohon tunggu pemeriksaan ulang dari Asesor.';
        } else {
            LogAktivitas::catat('Pengajuan FR.APL.02', 'Asesi mengirim Asesmen Mandiri #' . $pendaftaran->nomor_pendaftaran . ' untuk diperiksa asesor.');
            $pesanSukses = 'Formulir FR.APL.02 berhasil dikirim untuk diperiksa oleh Asesor. Mohon tunggu hasil pemeriksaan asesor sebelum melanjutkan ke FR.AK.01.';
        }

        return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
            ->with('sukses', $pesanSukses);
    }

    /**
     * AJAX: Upload bukti pendukung baru untuk elemen kompetensi pada FR.APL.02
     */
    public function uploadBuktiApl02(Request $request)
    {
        $pengguna = auth()->user();

        $request->validate([
            'pendaftaran_id' => 'required|integer',
            'elemen_id' => 'required|integer',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'file.required' => 'File bukti wajib dipilih.',
            'file.mimes' => 'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.',
            'file.max' => 'Ukuran file terlalu besar. Maksimal 10 MB.',
        ]);

        $pendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)->findOrFail($request->pendaftaran_id);

        // Guard izin ubah bukti berdasarkan status APL.02
        if (!$pendaftaran->isApl02Draft() && !$pendaftaran->isApl02Revision()) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti FR.APL.02 tidak dapat diubah karena formulir sudah dikirim atau telah disetujui.',
            ], 403);
        }

        $elemen = ElemenKompetensi::findOrFail($request->elemen_id);

        $unitSkemaIds = $pendaftaran->skema ? $pendaftaran->skema->unitKompetensi->pluck('id')->toArray() : [];
        if (!in_array($elemen->unit_id, $unitSkemaIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Elemen kompetensi tidak valid untuk skema ini.',
            ], 422);
        }

        $file = $request->file('file');
        $namaAsli = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $namaFileUnik = 'bukti_apl02_' . $pendaftaran->id . '_elem_' . $elemen->id . '_' . time() . '_' . Str::random(8) . '.' . $extension;

        // Simpan via disk public: storage/app/public/bukti_apl02/...
        $path = Storage::disk('public')->putFileAs('bukti_apl02', $file, $namaFileUnik);

        $bukti = BuktiApl02::create([
            'pendaftaran_id' => $pendaftaran->id,
            'elemen_id' => $elemen->id,
            'dokumen_id' => null,
            'sumber' => 'upload',
            'nama_file_asli' => $namaAsli,
            'nama_file_tersimpan' => $namaFileUnik,
            'file_path' => $path, // Menyimpan relative path 'bukti_apl02/namafile.ext'
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        // Sync fallback jawaban_apl02.bukti_relevan
        JawabanApl02::updateOrCreate(
            ['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemen->id],
            ['bukti_relevan' => $path]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bukti berhasil diupload.',
            'data' => [
                'id' => $bukti->id,
                'elemen_id' => $bukti->elemen_id,
                'nama' => $bukti->nama_tampil,
                'ukuran' => $bukti->file_size_formatted,
                'url' => $bukti->url,
                'is_image' => $bukti->is_image,
                'is_pdf' => $bukti->is_pdf,
                'sumber' => 'upload',
            ],
        ]);
    }

    /**
     * AJAX: Tautkan berkas dokumen APL.01 yang sudah ada sebagai bukti elemen APL.02
     */
    public function pilihBuktiApl01(Request $request)
    {
        $pengguna = auth()->user();

        $request->validate([
            'pendaftaran_id' => 'required|integer',
            'elemen_id' => 'required|integer',
            'dokumen_id' => 'required|integer',
        ], [
            'dokumen_id.required' => 'Pilih salah satu dokumen APL.01.',
        ]);

        $pendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)->findOrFail($request->pendaftaran_id);

        // Guard izin ubah bukti berdasarkan status APL.02
        if (!$pendaftaran->isApl02Draft() && !$pendaftaran->isApl02Revision()) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti FR.APL.02 tidak dapat diubah karena formulir sudah dikirim atau telah disetujui.',
            ], 403);
        }

        $elemen = ElemenKompetensi::findOrFail($request->elemen_id);
        $this->validasiStrukturApl02($pendaftaran, [$elemen->id]);
        $dokumen = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)->findOrFail($request->dokumen_id);

        // Cek apakah dokumen ini sudah ditautkan pada elemen ini
        $existing = BuktiApl02::where('pendaftaran_id', $pendaftaran->id)
            ->where('elemen_id', $elemen->id)
            ->where('dokumen_id', $dokumen->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen APL.01 ini sudah terlampir pada elemen ini.',
            ], 422);
        }

        $bukti = BuktiApl02::create([
            'pendaftaran_id' => $pendaftaran->id,
            'elemen_id' => $elemen->id,
            'dokumen_id' => $dokumen->id,
            'sumber' => 'apl01',
            'nama_file_asli' => $dokumen->nama_dokumen ?: $dokumen->jenis_dokumen,
            'nama_file_tersimpan' => null,
            'file_path' => null,
            'mime_type' => null,
            'file_size' => 0,
        ]);

        // Sync fallback jawaban_apl02.bukti_relevan
        JawabanApl02::updateOrCreate(
            ['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemen->id],
            ['bukti_relevan' => $dokumen->file_path]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bukti dari APL.01 berhasil ditautkan.',
            'data' => [
                'id' => $bukti->id,
                'elemen_id' => $bukti->elemen_id,
                'nama' => $bukti->nama_tampil,
                'ukuran' => $bukti->file_size_formatted,
                'url' => $bukti->url,
                'is_image' => $bukti->is_image,
                'is_pdf' => $bukti->is_pdf,
                'sumber' => 'apl01',
            ],
        ]);
    }

    /**
     * AJAX: Hapus bukti pendukung APL.02
     */
    public function hapusBuktiApl02($id)
    {
        $pengguna = auth()->user();
        $bukti = BuktiApl02::with(['pendaftaran', 'dokumenAsesi'])->findOrFail($id);

        // Otorisasi ketat: pastikan pendaftaran milik asesi yang sedang login
        if (!$bukti->pendaftaran || $bukti->pendaftaran->asesi_id !== $pengguna->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus bukti ini.',
            ], 403);
        }

        // Guard izin ubah bukti berdasarkan status APL.02
        if (!$bukti->pendaftaran->isApl02Draft() && !$bukti->pendaftaran->isApl02Revision()) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti FR.APL.02 tidak dapat diubah karena formulir sudah dikirim atau telah disetujui.',
            ], 403);
        }

        // Jika sumber = upload, hapus file fisik dari disk
        if ($bukti->sumber === 'upload' && $bukti->file_path) {
            if (Storage::disk('public')->exists($bukti->file_path)) {
                Storage::disk('public')->delete($bukti->file_path);
            }
        }
        // Jika sumber = apl01, JANGAN hapus file fisik dokumen APL.01

        $elemenId = $bukti->elemen_id;
        $pendaftaranId = $bukti->pendaftaran_id;
        $bukti->delete();

        // Update fallback jawaban_apl02.bukti_relevan jika masih ada sisa bukti lain
        $sisaBukti = BuktiApl02::where('pendaftaran_id', $pendaftaranId)
            ->where('elemen_id', $elemenId)
            ->first();

        $fallbackPath = $sisaBukti ? ($sisaBukti->sumber === 'apl01' ? ($sisaBukti->dokumenAsesi->file_path ?? null) : $sisaBukti->file_path) : null;
        JawabanApl02::where('pendaftaran_id', $pendaftaranId)
            ->where('elemen_id', $elemenId)
            ->update(['bukti_relevan' => $fallbackPath]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti berhasil dihapus.',
            'elemen_id' => $elemenId,
        ]);
    }

    public function storeAk01(Request $request)
    {
        $pengguna = auth()->user();
        $pendaftaranId = $request->pendaftaran_id ?: $request->route('id');
        $pendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)->findOrFail($pendaftaranId);

        $isDitolak = ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima');
        if ($isDitolak) {
            return redirect()->route('asesi.tahapan', ['step' => 1])->with('error', 'Pendaftaran telah Ditolak.');
        }

        // STRICT BACKEND GUARD: AK.01 HANYA boleh disimpan/ditandatangani jika FR.APL.02 sudah disetujui (Approved) oleh Asesor
        if (!$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('error', 'FR.APL.02 harus disetujui Asesor terlebih dahulu sebelum FR.AK.01 dapat diisi.');
        }

        // Sinkronkan data persetujuan asesmen dari Master FR.AK.01 Skema jika ada
        $pendaftaran->syncFromMasterAk01IfAvailable();

        $request->validate([
            'tuk_type' => 'nullable|string|in:Sewaktu,Tempat Kerja,Mandiri',
            'bukti_dikumpulkan' => 'nullable|array',
            'bukti_dikumpulkan_lainnya' => 'nullable|string',
            'tanda_tangan_asesi_ak01' => 'nullable|string',
        ]);

        $rawTtd = $request->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi ?: $pengguna->tanda_tangan));

        if (empty($rawTtd)) {
            return back()->withInput()->with('error', 'Silakan bubuhkan tanda tangan digital Anda terlebih dahulu sebelum mengirim formulir.');
        }

        $ttdPath = $rawTtd;

        // Validasi & Simpan Signature Base64 ke Storage Fisik jika berupa DataURL
        if (is_string($rawTtd) && \Illuminate\Support\Str::startsWith($rawTtd, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawTtd);
                if (count($imageParts) === 2) {
                    $imageTypeAux = explode('image/', $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageBase64 = base64_decode($imageParts[1]);

                    $fileName = 'signatures/ak01_asesi_' . $pendaftaran->id . '_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $imageType;
                    Storage::disk('public')->put($fileName, $imageBase64);
                    $ttdPath = 'storage/' . $fileName;

                    // Sync ke tanda tangan profil asesi
                    $pengguna->update(['tanda_tangan' => $ttdPath]);
                }
            } catch (\Exception $e) {
                \Log::warning('Gagal menyimpan signature ke file, fallback ke string: ' . $e->getMessage());
            }
        }

        $oldAk01Status = $pendaftaran->status_ak01;

        \Illuminate\Support\Facades\DB::transaction(function () use ($pendaftaran, $request, $ttdPath) {
            $asesor = $pendaftaran->asesor ?: $pendaftaran->jadwal?->asesor;
            $asesorTtd = $pendaftaran->tanda_tangan_asesor_ak01 ?: ($asesor?->tanda_tangan ?: 'signatures/verified_asesor_auto.png');

            $pendaftaran->update([
                'tuk_type' => $request->tuk_type ?: ($pendaftaran->tuk_type ?? 'Sewaktu'),
                'bukti_dikumpulkan' => $request->bukti_dikumpulkan ?: ($pendaftaran->bukti_dikumpulkan ?? ['Uji Praktik / Observasi Demonstrasi', 'Uji Tertulis (CBT)', 'Tanya Jawab Lisan']),
                'bukti_dikumpulkan_lainnya' => $request->has('bukti_dikumpulkan_lainnya') ? $request->bukti_dikumpulkan_lainnya : $pendaftaran->bukti_dikumpulkan_lainnya,
                'tanda_tangan_asesi_ak01' => $ttdPath,
                'tanggal_ttd_asesi_ak01' => now(),
                'status_ak01' => 'selesai',
                'tanda_tangan_asesor_ak01' => $asesorTtd,
                'tanggal_ttd_asesor_ak01' => $pendaftaran->tanggal_ttd_asesor_ak01 ?: now(),
            ]);

            LogAktivitas::catat('Pengisian FR.AK.01', 'Asesi menandatangani Persetujuan Asesmen #' . $pendaftaran->nomor_pendaftaran . ' (ACC Otomatis)');
        });

        // Trigger Notification ke Asesi & Asesor
        if ($oldAk01Status !== 'selesai') {
            $pendaftaran->asesi?->notify(new \App\Notifications\AK01Approved($pendaftaran->id));
            $asesorUser = $pendaftaran->asesor ?: $pendaftaran->jadwal?->asesor;
            if ($asesorUser) {
                $asesorUser->notify(new \App\Notifications\AK01ReadyForSignature($pendaftaran->id, $pendaftaran->asesi?->nama_lengkap ?? 'Asesi'));
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan menunggu sesi asesmen Anda dimulai sesuai jadwal.',
                'redirect_url' => route('asesi.dashboard', ['pendaftaran_id' => $pendaftaran->id]),
            ]);
        }

        return redirect()->route('asesi.dashboard', ['pendaftaran_id' => $pendaftaran->id])
            ->with('notif_ak01_selesai', true)
            ->with('sukses', 'Formulir FR.AK.01 Persetujuan Asesmen & Kerahasiaan telah berhasil ditandatangani. Silakan menunggu sesi asesmen Anda dimulai sesuai jadwal.');
    }
}
