<?php

namespace App\Http\Controllers;

use App\Models\ProfilAsesi;
use App\Models\PendaftaranAsesi;
use App\Models\DokumenAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use App\Models\JawabanApl02;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;

class AsesiController extends Controller
{
    public function dashboard(Request $request)
    {
        JadwalAsesmen::syncAllStatuses();
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);

        $semuaPendaftaran = PendaftaranAsesi::with(['skema', 'rekomendasi', 'asesor'])
            ->where('asesi_id', $pengguna->id)
            ->latest()
            ->get();

        $selectedPendaftaranId = $request->get('pendaftaran_id');
        if ($selectedPendaftaranId) {
            $pendaftaranTerakhir = PendaftaranAsesi::with(['skema.unitKompetensi', 'jadwal.asesor', 'dokumen', 'rekomendasi', 'asesor', 'jawabanApl02', 'iaPenilaian', 'ak07Adjustment'])
                ->where('asesi_id', $pengguna->id)
                ->find($selectedPendaftaranId);
        } else {
            $pendaftaranTerakhir = PendaftaranAsesi::with(['skema.unitKompetensi', 'jadwal.asesor', 'dokumen', 'rekomendasi', 'asesor', 'jawabanApl02', 'iaPenilaian', 'ak07Adjustment'])
                ->where('asesi_id', $pengguna->id)
                ->latest()
                ->first();
        }

        $jadwalList = collect();
        if ($pendaftaranTerakhir && $pendaftaranTerakhir->skema_id) {
            $jadwalList = JadwalAsesmen::with('asesor')
                ->where('skema_id', $pendaftaranTerakhir->skema_id)
                ->where('status_jadwal', '!=', 'selesai')
                ->where('status_jadwal', '!=', 'dibatalkan')
                ->get()
                ->map(function($j) {
                    $terisi = PendaftaranAsesi::where('jadwal_id', $j->id)->count();
                    $j->terisi = $terisi;
                    $j->sisa_kuota = max(0, $j->kuota - $terisi);
                    $j->is_penuh = $terisi >= $j->kuota;
                    return $j;
                });
        }

        $asesorList = \App\Models\Pengguna::where('peran', 'asesor')->where('aktif', true)->get();

        return view('asesi.dashboard', compact('pengguna', 'profil', 'pendaftaranTerakhir', 'semuaPendaftaran', 'asesorList', 'jadwalList'));
    }

    public function profil()
    {
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);
        
        $pendaftaranAktif = PendaftaranAsesi::with(['skema.unitKompetensi', 'dokumen', 'rekomendasi'])
            ->where('asesi_id', $pengguna->id)
            ->latest()
            ->first();

        return view('asesi.kelola-profil', compact('pengguna', 'profil', 'pendaftaranAktif'));
    }

    public function simpanProfil(Request $request)
    {
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'nik' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'nama_sekolah_instansi' => 'nullable|string|max:255',
            'tanda_tangan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $pengguna->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_telepon' => $request->nomor_telepon,
        ]);

        if ($request->hasFile('tanda_tangan')) {
            $file = $request->file('tanda_tangan');
            $namaFile = 'ttd_asesi_' . $pengguna->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('tanda_tangan', $namaFile, 'public');
            $pengguna->update(['tanda_tangan' => 'storage/' . $path]);
        }

        $profil->update([
            'nik' => $request->nik,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'pekerjaan' => $request->pekerjaan,
            'nama_sekolah_instansi' => $request->nama_sekolah_instansi,
        ]);

        LogAktivitas::catat('Memperbarui Profil', 'Asesi memperbarui biodata diri');

        return back()->with('sukses', 'Profil biodata berhasil diperbarui.');
    }

    public function requestPerbaikanBiodata(Request $request, $id)
    {
        $pendaftaran = PendaftaranAsesi::where('asesi_id', auth()->id())->findOrFail($id);

        $request->validate([
            'catatan_request_perbaikan' => 'required|string|max:500',
        ], [
            'catatan_request_perbaikan.required' => 'Silakan jelaskan bagian data yang perlu diperbaiki.',
        ]);

        $pendaftaran->update([
            'request_perbaikan' => true,
            'catatan_request_perbaikan' => $request->catatan_request_perbaikan,
        ]);

        LogAktivitas::catat('Request Perbaikan Biodata Pendaftaran', 'Asesi mengajukan request perbaikan data pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke Admin & Superadmin
        $admins = Pengguna::whereIn('peran', ['admin', 'superadmin'])->where('aktif', true)->get();
        $userNama = auth()->user()->nama_lengkap ?? 'Asesi';
        foreach ($admins as $admin) {
            $admin->notify(new SystemAlert(
                'Permohonan Perbaikan Berkas / Data: ' . $userNama,
                "Asesi {$userNama} mengajukan perbaikan data pendaftaran #{$pendaftaran->nomor_pendaftaran}: {$request->catatan_request_perbaikan}",
                route('admin.detail-verifikasi', $pendaftaran->id),
                'revision',
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'asesi_id' => auth()->id(),
                ]
            ));
        }

        return back()->with('sukses', 'Permohonan perbaikan biodata pendaftaran berhasil dikirim ke Admin LSP.');
    }

    // =========================================================================
    // 1 HALAMAN TERPADU FORMULIR ASESMEN (FR.APL.01, FR.APL.02, FR.AK.01)
    // =========================================================================
    public function formulirTerpadu(Request $request)
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
            'penilaian.unit'
        ])->where('asesi_id', $pengguna->id)->latest()->get();

        // Ambil daftar ID skema yang pernah ditolak untuk akun ini
        $ditolakSkemaIds = $semuaPendaftaran->filter(function($p) {
            return $p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima';
        })->pluck('skema_id')->unique()->toArray();

        // Ambil daftar ID skema yang sedang aktif berjalan
        $runningSkemaIds = $semuaPendaftaran->filter(function($p) {
            return !in_array($p->status_pendaftaran, ['ditolak']) && $p->rekomendasi_admin_status !== 'tidak_diterima';
        })->pluck('skema_id')->unique()->toArray();

        $isModeBaru = ($request->get('baru') == '1' || $request->get('action') === 'daftar_baru');
        $pendaftaranId = $request->get('pendaftaran_id');
        $pendaftaran = null;

        if (!$isModeBaru) {
            if ($pendaftaranId) {
                $pendaftaran = $semuaPendaftaran->firstWhere('id', $pendaftaranId);
            }
            if (!$pendaftaran) {
                // Utamakan pendaftaran yang sedang aktif (bukan ditolak)
                $pendaftaran = $semuaPendaftaran->first(function($p) {
                    return $p->status_pendaftaran !== 'ditolak' && $p->rekomendasi_admin_status !== 'tidak_diterima';
                });
            }
        }

        // Simpan referensi pendaftaran terakhir yang ditolak untuk notifikasi alert
        $pendaftaranTerakhirDitolak = $semuaPendaftaran->first(function($p) {
            return $p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima';
        });

        // JIKA PENDAFTARAN DITOLAK: Otomatis reset formulir APL-01 menjadi form baru yang bersih
        if ($pendaftaran && ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima')) {
            $pendaftaranTerakhirDitolak = $pendaftaran;
            $pendaftaran = null;
            $isModeBaru = true;
        }

        // Cek agar notifikasi penolakan HANYA muncul 1x saja dan tidak muncul lagi saat refresh
        $tampilkanNotifDitolak = false;
        if ($pendaftaranTerakhirDitolak) {
            $sessionKey = 'notif_ditolak_shown_' . $pendaftaranTerakhirDitolak->id;
            if (!session()->has($sessionKey)) {
                $tampilkanNotifDitolak = true;
                session([$sessionKey => true]);
            }
        }

        $skemaList = SkemaSertifikasi::where('status_aktif', true)
            ->with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')
            ->get();

        // Status gating & persetujuan untuk pendaftaran terpilih
        $isDraft = false;
        $isDiajukan = false;
        $isDitolakAdmin = false;
        $isAccAdmin = false;
        $isApl02Selesai = false;
        $isDitolakAsesor = false;
        $isAccAsesor = false;
        $isAk01Selesai = false;
        $isTotalSelesai = false;

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
            $isApl02Rejected = $pendaftaran->isApl02Rejected();
            $isApl02Submitted = $pendaftaran->isApl02Submitted();
            $isApl02UnderReview = $pendaftaran->isApl02UnderReview();
            $isApl02Draft = $pendaftaran->isApl02Draft();

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
            $isAk01Selesai = (!empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']));
            $isTotalSelesai = $isAk01Selesai && ($pendaftaran->rekomendasi != null);
        }

        // SMART AUTO-TAB DECISION
        $tabAktif = $request->get('tab');
        if (!$tabAktif || !in_array($tabAktif, ['apl01', 'apl02', 'ak01'])) {
            if ($isModeBaru || !$pendaftaran || $isDraft || $isDiajukan || $isDitolakAdmin) {
                $tabAktif = 'apl01';
            } elseif ($isAccAdmin && !$isApl02Approved) {
                $tabAktif = 'apl02';
            } elseif ($isApl02Approved && !$isAk01Selesai) {
                $tabAktif = 'ak01';
            } else {
                $tabAktif = 'ak01';
            }
        }

        // STRICT BACKEND GUARD: jika tab ak01 diminta padahal APL-02 belum approved, tolak
        if ($tabAktif === 'ak01' && (!$pendaftaran || !$pendaftaran->isAk01Unlocked())) {
            $tabAktif = 'apl02';
        }

        // Draft data untuk Form APL-01 (otomatis reset skema_id jika mode baru atau ditolak)
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
            'skema_id' => $pendaftaran ? $pendaftaran->skema_id : (old('skema_id') ?? null),
            'tujuan_asesmen' => $pendaftaran ? $pendaftaran->tujuan_asesmen : 'Sertifikasi',
            'tujuan_asesmen_lainnya' => $pendaftaran ? $pendaftaran->tujuan_asesmen_lainnya : null,
            'bukti_persyaratan_dasar' => $pendaftaran ? ($pendaftaran->bukti_persyaratan_dasar ?? []) : [],
            'bukti_administratif' => $pendaftaran ? ($pendaftaran->bukti_administratif ?? []) : [],
            'tanda_tangan_asesi' => $pendaftaran ? ($pendaftaran->tanda_tangan_asesi ?? $pengguna->tanda_tangan) : $pengguna->tanda_tangan,
        ];

        // Jawaban APL-02 map & Bukti APL-02 map
        $jawabanMap = collect();
        if ($pendaftaran && $pendaftaran->jawabanApl02) {
            $jawabanMap = $pendaftaran->jawabanApl02->keyBy('elemen_id');
        }

        $buktiApl02Map = collect();
        if ($pendaftaran && $pendaftaran->buktiApl02) {
            $buktiApl02Map = $pendaftaran->buktiApl02->groupBy('elemen_id');
        }

        $dokumenList = $pendaftaran ? $pendaftaran->dokumen : collect();
        $dokumenTeknis = collect($dokumenList)->filter(function ($dok) {
            $jenis = strtolower($dok->jenis_dokumen ?? '');
            return !str_contains($jenis, 'ktp') && !str_contains($jenis, 'pasfoto') && !str_contains($jenis, 'foto');
        });

        return view('asesi.formulir-terpadu', compact(
            'pengguna',
            'profil',
            'semuaPendaftaran',
            'pendaftaran',
            'pendaftaranTerakhirDitolak',
            'tampilkanNotifDitolak',
            'isModeBaru',
            'skemaList',
            'ditolakSkemaIds',
            'runningSkemaIds',
            'isDraft',
            'isDiajukan',
            'isDitolakAdmin',
            'isAccAdmin',
            'isApl02Selesai',
            'isDitolakAsesor',
            'isAccAsesor',
            'isAk01Selesai',
            'isTotalSelesai',
            'statusApl02',
            'isApl02Approved',
            'isApl02Revision',
            'isApl02Submitted',
            'isApl02UnderReview',
            'isApl02Draft',
            'tabAktif',
            'draftData',
            'jawabanMap',
            'buktiApl02Map',
            'dokumenList',
            'dokumenTeknis'
        ));
    }

    public function simpanApl01Lengkap(Request $request)
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
            'file_rapor.max' => 'Ukuran file Rapor maksimal 5MB.',
            'file_pkl.max' => 'Ukuran file PKL maksimal 5MB.',
            'file_ktp.max' => 'Ukuran file KTP maksimal 5MB.',
            'file_foto.max' => 'Ukuran file Pasfoto maksimal 5MB.',
        ]);

        // Cek jika skema yang dipilih pernah ditolak untuk akun asesi ini
        $isDitolakSkema = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('skema_id', $request->skema_id)
            ->where(function($q) {
                $q->where('status_pendaftaran', 'ditolak')
                  ->orWhere('rekomendasi_admin_status', 'tidak_diterima');
            })
            ->exists();

        if ($isDitolakSkema) {
            return back()->with('error', 'Skema sertifikasi ini telah Ditolak untuk akun Anda dan tidak dapat dipilih kembali. Silakan pilih skema sertifikasi lainnya.');
        }

        // Simpan Profil
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

        // Cek jika mendaftar skema baru atau mengubah skema
        if (!$pendaftaran || $pendaftaran->skema_id != $request->skema_id) {
            $sudahAda = PendaftaranAsesi::where('asesi_id', $pengguna->id)
                ->where('skema_id', $request->skema_id)
                ->whereNotIn('status_pendaftaran', ['draft', 'revisi', 'ditolak'])
                ->exists();

            if ($sudahAda) {
                return back()->with('error', 'Anda sudah memiliki pendaftaran aktif pada skema ini. Setiap akun hanya diperbolehkan mendaftar 1 kali per skema sertifikasi.');
            }
        }

        $isAjukan = ($request->input('aksi') === 'ajukan');

        // Validasi kelengkapan berkas jika diajukan
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

        // Upload berkas
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
                    [
                        'pendaftaran_id' => $pendaftaran->id,
                        'jenis_dokumen' => $jenisDokumen,
                    ],
                    [
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'status_verifikasi' => 'menunggu',
                        'catatan' => null,
                    ]
                );
            }
        }

        session()->forget('apl01_draft');

        $pesan = $isAjukan
            ? 'Formulir FR.APL.01 berhasil diajukan! Admin LSP akan memverifikasi berkas persyaratan Anda.'
            : 'Draft Formulir FR.APL.01 berhasil disimpan.';

        LogAktivitas::catat('Pengisian FR.APL.01', ($isAjukan ? 'Mengajukan' : 'Menyimpan draft') . ' pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke seluruh Admin & Superadmin saat asesi mengajukan FR.APL.01
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

        return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl01'])->with('sukses', $pesan);
    }

    public function buatPendaftaranSkemaBaru(Request $request)
    {
        $pengguna = auth()->user();
        
        $request->validate([
            'skema_id' => 'required|exists:skema_sertifikasi,id',
        ]);

        $isDitolakSkema = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('skema_id', $request->skema_id)
            ->where(function($q) {
                $q->where('status_pendaftaran', 'ditolak')
                  ->orWhere('rekomendasi_admin_status', 'tidak_diterima');
            })
            ->exists();

        if ($isDitolakSkema) {
            return back()->with('error', 'Skema sertifikasi ini telah Ditolak untuk akun Anda dan tidak dapat dipilih kembali. Silakan pilih skema sertifikasi lainnya.');
        }

        $sudahAda = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('skema_id', $request->skema_id)
            ->whereNotIn('status_pendaftaran', ['ditolak'])
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Anda sudah memiliki pendaftaran aktif untuk skema ini.');
        }

        $pendaftaran = PendaftaranAsesi::create([
            'nomor_pendaftaran' => 'APL01-' . date('Ymd') . '-' . rand(1000, 9999),
            'asesi_id' => $pengguna->id,
            'skema_id' => $request->skema_id,
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'draft',
            'tujuan_asesmen' => 'Sertifikasi',
            'kebangsaan' => 'Indonesia',
        ]);

        LogAktivitas::catat('Mendaftar Skema Baru', 'Membuat pendaftaran baru untuk skema ID: ' . $request->skema_id);

        return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl01'])
            ->with('sukses', 'Pendaftaran skema baru berhasil dibuat. Silakan lengkapi Formulir FR.APL.01.');
    }

    // MAIN PENDAFTARAN ROUTE DENGAN SMART REDIRECTION KE FORMULIR TERPADU
    public function pendaftaran()
    {
        return redirect()->route('asesi.formulir', ['tab' => 'apl01']);
    }

    // HELPER: AMBIL DRAFT DATA DARI SESSION ATAU DATABASE DRAFT
    private function getOrPopulateDraft()
    {
        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);
        $draft = session('apl01_draft', []);

        // Pre-fill profile defaults
        $draft['nama_lengkap'] = $draft['nama_lengkap'] ?? $pengguna->nama_lengkap;
        $draft['nomor_telepon'] = $draft['nomor_telepon'] ?? $pengguna->nomor_telepon;
        $draft['nik'] = $draft['nik'] ?? $profil->nik;
        $draft['tempat_lahir'] = $draft['tempat_lahir'] ?? $profil->tempat_lahir;
        $draft['tanggal_lahir'] = $draft['tanggal_lahir'] ?? $profil->tanggal_lahir;
        $draft['jenis_kelamin'] = $draft['jenis_kelamin'] ?? $profil->jenis_kelamin;
        $draft['alamat'] = $draft['alamat'] ?? $profil->alamat;
        $draft['pendidikan_terakhir'] = $draft['pendidikan_terakhir'] ?? $profil->pendidikan_terakhir;
        $draft['pekerjaan'] = $draft['pekerjaan'] ?? $profil->pekerjaan;
        $draft['nama_sekolah_instansi'] = $draft['nama_sekolah_instansi'] ?? $profil->nama_sekolah_instansi;

        // Auto load existing database draft if session draft is missing skema_id
        if (empty($draft['skema_id'])) {
            $pendaftaranDraft = PendaftaranAsesi::where('asesi_id', $pengguna->id)
                ->whereIn('status_pendaftaran', ['draft', 'revisi'])
                ->latest()
                ->first();

            if ($pendaftaranDraft) {
                $draft['skema_id'] = $pendaftaranDraft->skema_id;
                $draft['tujuan_asesmen'] = $pendaftaranDraft->tujuan_asesmen;
                $draft['tujuan_asesmen_lainnya'] = $pendaftaranDraft->tujuan_asesmen_lainnya;
                $draft['bukti_persyaratan_dasar'] = $pendaftaranDraft->bukti_persyaratan_dasar ?? [];
                $draft['tanda_tangan_asesi'] = $pendaftaranDraft->tanda_tangan_asesi;
            }
        }

        session(['apl01_draft' => $draft]);
        return $draft;
    }

    // HALAMAN BAGIAN 1: DATA PEMOHON
    public function pendaftaranBagian1()
    {
        $pengguna = auth()->user();
        $pendaftaranAktif = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->whereNotIn('status_pendaftaran', ['ditolak'])
            ->latest()
            ->first();
        if ($pendaftaranAktif && $pendaftaranAktif->isApprovedByAdmin()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaranAktif->id, 'step' => 1])
                ->with('info', 'Formulir FR.APL.01 Anda telah disetujui (ACC) oleh Admin LSP dan berstatus Read-Only.');
        }

        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);
        $draftData = $this->getOrPopulateDraft();

        return view('asesi.pendaftaran.bagian1', compact('pengguna', 'profil', 'draftData'));
    }

    public function simpanBagian1(Request $request)
    {
        $pengguna = auth()->user();
        $pendaftaranAktif = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->whereNotIn('status_pendaftaran', ['ditolak'])
            ->latest()
            ->first();
        if ($pendaftaranAktif && $pendaftaranAktif->isApprovedByAdmin()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaranAktif->id, 'step' => 1])
                ->with('info', 'Formulir FR.APL.01 Anda telah disetujui (ACC) oleh Admin LSP dan berstatus Read-Only.');
        }
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string',
        ]);

        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);

        // Save profile immediately to database so draft is never lost
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

        $draft = session('apl01_draft', []);
        $draft = array_merge($draft, $request->except(['_token']));
        session(['apl01_draft' => $draft]);

        return redirect()->route('asesi.pendaftaran.bagian2')->with('sukses', 'Bagian 1 berhasil disimpan. Silakan pilih skema pada Bagian 2.');
    }

    // HALAMAN BAGIAN 2: DATA SERTIFIKASI
    public function pendaftaranBagian2()
    {
        $pengguna = auth()->user();
        $skemaList = SkemaSertifikasi::where('status_aktif', true)->with('unitKompetensi')->get();
        $draftData = $this->getOrPopulateDraft();

        if (empty($draftData['nik'])) {
            return redirect()->route('asesi.pendaftaran.bagian1')->with('info', 'Silakan lengkapi Bagian 1 terlebih dahulu.');
        }

        $registeredSkemaIds = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->whereNotIn('status_pendaftaran', ['draft', 'revisi'])
            ->pluck('skema_id')
            ->toArray();

        return view('asesi.pendaftaran.bagian2', compact('skemaList', 'draftData', 'registeredSkemaIds'));
    }

    public function simpanBagian2(Request $request)
    {
        $request->validate([
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'tujuan_asesmen' => 'required|string',
        ], [
            'skema_id.required' => 'Silakan pilih skema sertifikasi.',
        ]);

        $pengguna = auth()->user();

        // PEMBATASAN: SETIAP ASESI HANYA BISA DAFTAR 1 KALI PER SKEMA
        $pendaftaranSubmitted = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('skema_id', $request->skema_id)
            ->whereNotIn('status_pendaftaran', ['draft', 'revisi'])
            ->exists();

        if ($pendaftaranSubmitted) {
            return back()->with('error', 'Anda sudah pernah mendaftar pada skema ini. Setiap akun hanya diperbolehkan mendaftar 1 kali per skema sertifikasi.');
        }

        $draft = session('apl01_draft', []);
        $draft = array_merge($draft, $request->except(['_token']));
        session(['apl01_draft' => $draft]);

        // Auto-save draft record to PendaftaranAsesi database
        $pendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->whereIn('status_pendaftaran', ['draft', 'revisi'])
            ->first();

        if (!$pendaftaran) {
            $pendaftaran = PendaftaranAsesi::create([
                'asesi_id' => $pengguna->id,
                'status_pendaftaran' => 'draft',
                'nomor_pendaftaran' => 'APL01-' . date('Ymd') . '-' . rand(1000, 9999),
                'skema_id' => $request->skema_id,
                'tanggal_daftar' => now(),
                'tujuan_asesmen' => $request->tujuan_asesmen,
            ]);
        }

        $pendaftaran->update([
            'skema_id' => $request->skema_id,
            'tujuan_asesmen' => $request->tujuan_asesmen,
            'tujuan_asesmen_lainnya' => $request->tujuan_asesmen_lainnya ?? null,
        ]);

        return redirect()->route('asesi.pendaftaran.bagian31')->with('sukses', 'Bagian 2 berhasil disimpan. Lanjutkan ke Bukti Persyaratan Dasar (Bagian 3.1).');
    }

    public function pilihAsesor(Request $request, $id)
    {
        $pendaftaran = PendaftaranAsesi::where('asesi_id', auth()->id())->findOrFail($id);

        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_asesmen,id',
        ], [
            'jadwal_id.required' => 'Silakan pilih Jadwal Asesmen & Asesor Penguji.',
            'jadwal_id.exists' => 'Jadwal yang dipilih tidak valid.',
        ]);

        $jadwal = JadwalAsesmen::with('asesor')
            ->where('skema_id', $pendaftaran->skema_id)
            ->findOrFail($request->jadwal_id);

        // CEK KUOTA JADWAL
        $terisi = PendaftaranAsesi::where('jadwal_id', $jadwal->id)->count();
        if ($terisi >= $jadwal->kuota && $pendaftaran->jadwal_id != $jadwal->id) {
            return back()->with('error', 'Maaf, kuota untuk jadwal dan Asesor ini sudah penuh (' . $terisi . '/' . $jadwal->kuota . '). Silakan pilih jadwal/Asesor lainnya.');
        }

        $pendaftaran->update([
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $jadwal->asesor_id,
        ]);

        $namaAsesor = $jadwal->asesor->nama_lengkap ?? 'Asesor Resmi LSP';

        LogAktivitas::catat('Memilih Jadwal & Asesor', 'Asesi memilih ' . $namaAsesor . ' (' . $jadwal->nama_tuk . ') untuk pendaftaran #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke Asesor terkait penugasan asesi baru pada jadwal
        if ($jadwal->asesor) {
            $asesiUser = auth()->user();
            $skemaNama = $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi';
            $jadwal->asesor->notify(new SystemAlert(
                'Penugasan Asesi Baru',
                "Asesi baru mendaftar pada sesi jadwal Anda: {$asesiUser->nama_lengkap} ({$skemaNama}) pada jadwal {$jadwal->kode_jadwal} ({$jadwal->nama_tuk}).",
                route('asesor.daftar-peserta', ['jadwal_id' => $jadwal->id]),
                'penugasan',
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jadwal_id' => $jadwal->id,
                    'asesi_id' => $asesiUser->id,
                    'kode_jadwal' => $jadwal->kode_jadwal,
                ]
            ));
        }

        return back()->with('sukses', 'Jadwal Asesmen (' . $jadwal->nama_tuk . ') & Asesor Penguji (' . $namaAsesor . ') berhasil dipilih. Silakan lanjutkan ke pengisian APL-02.');
    }

    // HALAMAN BAGIAN 3.1: SYARAT DASAR
    public function pendaftaranBagian31()
    {
        $pengguna = auth()->user();
        $draftData = $this->getOrPopulateDraft();

        if (empty($draftData['skema_id'])) {
            return redirect()->route('asesi.pendaftaran.bagian2')->with('info', 'Silakan pilih skema sertifikasi pada Bagian 2 terlebih dahulu.');
        }

        $pendaftaranAktif = PendaftaranAsesi::with('dokumen')
            ->where('asesi_id', $pengguna->id)
            ->whereIn('status_pendaftaran', ['draft', 'revisi'])
            ->first();

        return view('asesi.pendaftaran.bagian31', compact('draftData', 'pendaftaranAktif'));
    }

    public function simpanBagian31(Request $request)
    {
        $request->validate([
            'file_rapor' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'file_pkl' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ], [
            'file_rapor.mimes' => 'File Foto Copy Rapor harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',
            'file_rapor.max' => 'Ukuran file Rapor maksimal 5MB.',
            'file_pkl.mimes' => 'File Sertifikat PKL harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',
            'file_pkl.max' => 'Ukuran file Sertifikat PKL maksimal 5MB.',
        ]);

        $pengguna = auth()->user();
        $draft = session('apl01_draft', []);
        $draft['bukti_persyaratan_dasar'] = $request->bukti_dasar ?? [];
        session(['apl01_draft' => $draft]);

        $pendaftaran = PendaftaranAsesi::with('dokumen')
            ->where('asesi_id', $pengguna->id)
            ->whereIn('status_pendaftaran', ['draft', 'revisi'])
            ->first();

        // Validasi: Wajib unggah file Rapor & PKL sebelum lanjut ke Bagian 3.2
        $dokumenRaporAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')->count() > 0;
        $dokumenPklAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Portofolio Sertifikat/Karya')->count() > 0;

        if (!$request->hasFile('file_rapor') && !$dokumenRaporAda) {
            return back()->with('error', 'Silakan unggah file Foto Copy Rapor/Ijazah terlebih dahulu sebelum melanjutkan ke Bagian 3.2!');
        }

        if (!$request->hasFile('file_pkl') && !$dokumenPklAda) {
            return back()->with('error', 'Silakan unggah file Foto Copy Sertifikat PKL/Pelatihan terlebih dahulu sebelum melanjutkan ke Bagian 3.2!');
        }

        if ($pendaftaran) {
            $pendaftaran->update([
                'bukti_persyaratan_dasar' => $request->bukti_dasar ?? [],
            ]);

            // Handle file uploads in Bagian 3.1 (Ganti file lama jika merevisi)
            if ($request->hasFile('file_rapor')) {
                $file = $request->file('file_rapor');
                $ext = strtolower($file->getClientOriginalExtension());
                $namaFile = 'rapor_' . auth()->id() . '_' . time() . '.' . $ext;
                $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');
                
                DokumenAsesi::updateOrCreate(
                    [
                        'pendaftaran_id' => $pendaftaran->id,
                        'jenis_dokumen' => 'Ijazah / Rapor Terakhir',
                    ],
                    [
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'status_verifikasi' => 'menunggu',
                        'catatan' => null,
                    ]
                );
            }

            if ($request->hasFile('file_pkl')) {
                $file = $request->file('file_pkl');
                $ext = strtolower($file->getClientOriginalExtension());
                $namaFile = 'pkl_' . auth()->id() . '_' . time() . '.' . $ext;
                $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');
                
                DokumenAsesi::updateOrCreate(
                    [
                        'pendaftaran_id' => $pendaftaran->id,
                        'jenis_dokumen' => 'Portofolio Sertifikat/Karya',
                    ],
                    [
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'status_verifikasi' => 'menunggu',
                        'catatan' => null,
                    ]
                );
            }
        }

        return redirect()->route('asesi.pendaftaran.bagian32')->with('sukses', 'Bagian 3.1 & berkas persyaratan dasar berhasil disimpan. Lanjutkan ke Bagian 3.2.');
    }

    // HALAMAN BAGIAN 3.2: BUKTI ADMIN & TTD
    public function pendaftaranBagian32()
    {
        $pengguna = auth()->user();
        $draftData = $this->getOrPopulateDraft();

        if (empty($draftData['skema_id'])) {
            return redirect()->route('asesi.pendaftaran.bagian2')->with('info', 'Silakan pilih skema sertifikasi terlebih dahulu.');
        }

        $pendaftaranAktif = PendaftaranAsesi::with('dokumen')
            ->where('asesi_id', $pengguna->id)
            ->whereIn('status_pendaftaran', ['draft', 'revisi'])
            ->first();

        return view('asesi.pendaftaran.bagian32', compact('pengguna', 'draftData', 'pendaftaranAktif'));
    }

    public function simpanBagian32(Request $request)
    {
        $request->validate([
            'file_ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'file_foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'tanda_tangan_asesi' => 'required|string',
        ], [
            'file_ktp.mimes' => 'File KTP/Kartu Pelajar harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',
            'file_ktp.max' => 'Ukuran file KTP maksimal 5MB.',
            'file_foto.mimes' => 'Pasfoto 3x4 harus berformat JPG, JPEG, PNG, atau WEBP.',
            'file_foto.max' => 'Ukuran Pasfoto 3x4 maksimal 5MB.',
            'tanda_tangan_asesi.required' => 'Silakan buat Tanda Tangan Canvas Digital terlebih dahulu.',
        ]);

        $pengguna = auth()->user();
        $profil = ProfilAsesi::firstOrCreate(['pengguna_id' => $pengguna->id]);
        $draft = session('apl01_draft', []);

        $pendaftaran = PendaftaranAsesi::with('dokumen')
            ->where('asesi_id', $pengguna->id)
            ->whereIn('status_pendaftaran', ['draft', 'revisi'])
            ->first();

        // Validasi: Wajib unggah file KTP & Pasfoto 3x4 sebelum menyelesaikan pendaftaran
        $dokumenKtpAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'KTP / Kartu Pelajar')->count() > 0;
        $dokumenFotoAda = $pendaftaran && $pendaftaran->dokumen->where('jenis_dokumen', 'Pasfoto 3x4 Background Merah')->count() > 0;

        if (!$request->hasFile('file_ktp') && !$dokumenKtpAda) {
            return back()->with('error', 'Silakan unggah file KTP / Kartu Pelajar terlebih dahulu sebelum menyelesaikan permohonan pendaftaran!');
        }

        if (!$request->hasFile('file_foto') && !$dokumenFotoAda) {
            return back()->with('error', 'Silakan unggah file Pasfoto 3x4 (Background Merah) terlebih dahulu sebelum menyelesaikan permohonan pendaftaran!');
        }

        // Synchronize updated profile info
        $pengguna->update([
            'nama_lengkap' => $draft['nama_lengkap'] ?? $pengguna->nama_lengkap,
            'nomor_telepon' => $draft['nomor_telepon'] ?? $pengguna->nomor_telepon,
        ]);

        $profil->update([
            'nik' => $draft['nik'] ?? $profil->nik,
            'tempat_lahir' => $draft['tempat_lahir'] ?? $profil->tempat_lahir,
            'tanggal_lahir' => $draft['tanggal_lahir'] ?? $profil->tanggal_lahir,
            'jenis_kelamin' => $draft['jenis_kelamin'] ?? $profil->jenis_kelamin,
            'alamat' => $draft['alamat'] ?? $profil->alamat,
            'pendidikan_terakhir' => $draft['pendidikan_terakhir'] ?? $profil->pendidikan_terakhir,
            'pekerjaan' => $draft['pekerjaan'] ?? $profil->pekerjaan,
            'nama_sekolah_instansi' => $draft['nama_sekolah_instansi'] ?? $profil->nama_sekolah_instansi,
        ]);

        $nomorPendaftaran = $pendaftaran ? $pendaftaran->nomor_pendaftaran : ('APL01-' . date('Ymd') . '-' . rand(1000, 9999));

        $dataPendaftaran = [
            'nomor_pendaftaran' => $nomorPendaftaran,
            'asesi_id' => $pengguna->id,
            'skema_id' => $draft['skema_id'],
            'jadwal_id' => $draft['jadwal_id'] ?? null,
            'tanggal_daftar' => now(),
            'tujuan_asesmen' => $draft['tujuan_asesmen'] ?? 'Sertifikasi',
            'tujuan_asesmen_lainnya' => $draft['tujuan_asesmen_lainnya'] ?? null,
            'kebangsaan' => $draft['kebangsaan'] ?? 'Indonesia',
            'kode_pos' => $draft['kode_pos'] ?? null,
            'no_telp_rumah' => $draft['no_telp_rumah'] ?? null,
            'nama_perusahaan' => $draft['nama_sekolah_instansi'] ?? null,
            'jabatan_perusahaan' => $draft['pekerjaan'] ?? null,
            'alamat_kantor' => $draft['alamat_kantor'] ?? null,
            'kode_pos_kantor' => $draft['kode_pos_kantor'] ?? null,
            'telp_kantor' => $draft['telp_kantor'] ?? null,
            'fax_kantor' => $draft['fax_kantor'] ?? null,
            'email_kantor' => $draft['email_kantor'] ?? null,
            'bukti_persyaratan_dasar' => $draft['bukti_persyaratan_dasar'] ?? [],
            'bukti_administratif' => $request->bukti_admin ?? [],
            'tanda_tangan_asesi' => $request->tanda_tangan_asesi,
            'tanggal_ttd_asesi' => now(),
            'status_pendaftaran' => 'diajukan',
            'request_perbaikan' => false,
            'catatan_request_perbaikan' => null,
            'catatan_verifikasi' => null,
        ];

        if ($pendaftaran) {
            $pendaftaran->update($dataPendaftaran);
        } else {
            $pendaftaran = PendaftaranAsesi::create($dataPendaftaran);
        }

        // Handle file uploads in Bagian 3.2 (Ganti file lama jika merevisi)
        if ($request->hasFile('file_ktp')) {
            $file = $request->file('file_ktp');
            $namaFile = 'ktp_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');
            
            DokumenAsesi::updateOrCreate(
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jenis_dokumen' => 'KTP / Kartu Pelajar',
                ],
                [
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => 'storage/' . $path,
                    'status_verifikasi' => 'menunggu',
                    'catatan' => null,
                ]
            );
        }

        if ($request->hasFile('file_foto')) {
            $file = $request->file('file_foto');
            $namaFile = 'pasfoto_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');
            
            DokumenAsesi::updateOrCreate(
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jenis_dokumen' => 'Pasfoto 3x4 Background Merah',
                ],
                [
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => 'storage/' . $path,
                    'status_verifikasi' => 'menunggu',
                    'catatan' => null,
                ]
            );
        }

        // Clear session draft
        session()->forget('apl01_draft');

        LogAktivitas::catat('Form FR.APL.01 & Berkas Diselesai', 'Menyelesaikan Formulir FR.APL.01 #' . $pendaftaran->nomor_pendaftaran);

        // Notifikasi ke seluruh Admin & Superadmin bahwa permohonan FR.APL.01 diajukan untuk diverifikasi (ACC)
        $admins = Pengguna::whereIn('peran', ['admin', 'superadmin'])->where('aktif', true)->get();
        $skemaNama = $pendaftaran->skema ? $pendaftaran->skema->nama_skema : 'Skema Sertifikasi';
        foreach ($admins as $admin) {
            $admin->notify(new SystemAlert(
                'Permohonan ACC FR.APL.01: ' . $pengguna->nama_lengkap,
                "Asesi {$pengguna->nama_lengkap} telah menyelesaikan dan mengajukan permohonan formulir FR.APL.01 ({$skemaNama}) untuk diverifikasi (ACC).",
                route('admin.detail-verifikasi', $pendaftaran->id),
                'apl01_submission',
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'asesi_id' => $pengguna->id,
                    'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
                ]
            ));
        }

        return redirect()->route('asesi.dashboard')
            ->with('sukses', 'Formulir FR.APL.01 dan perbaikan berkas persyaratan berhasil diselesaikan dan diajukan ulang ke Admin LSP!');
    }

    public function uploadDokumen($id)
    {
        $pendaftaran = PendaftaranAsesi::with(['skema.unitKompetensi', 'dokumen', 'jadwal'])
            ->where('asesi_id', auth()->id())
            ->findOrFail($id);

        return view('asesi.upload-dokumen', compact('pendaftaran'));
    }

    public function simpanDokumen(Request $request, $id)
    {
        $pendaftaran = PendaftaranAsesi::where('asesi_id', auth()->id())->findOrFail($id);

        $request->validate([
            'jenis_dokumen' => 'required|string',
            'file_dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5048',
        ], [
            'file_dokumen.required' => 'Pilih file yang akan diunggah.',
            'file_dokumen.max' => 'Ukuran file maksimal 5MB.',
        ]);

        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $jenisDokumenSlug = \Illuminate\Support\Str::slug($request->jenis_dokumen, '_');
            $namaFile = $jenisDokumenSlug . '_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('dokumen_asesi', $namaFile, 'public');

            DokumenAsesi::create([
                'pendaftaran_id' => $pendaftaran->id,
                'jenis_dokumen' => $request->jenis_dokumen,
                'nama_dokumen' => $file->getClientOriginalName(),
                'file_path' => 'storage/' . $path,
                'status_verifikasi' => 'menunggu',
            ]);

            LogAktivitas::catat('Unggah Dokumen', 'Unggah berkas ' . $request->jenis_dokumen);
        }

        return back()->with('sukses', 'Dokumen berhasil diunggah.');
    }

    public function ajukanPendaftaran($id)
    {
        $pendaftaran = PendaftaranAsesi::with('dokumen')->where('asesi_id', auth()->id())->findOrFail($id);

        if ($pendaftaran->dokumen->count() === 0) {
            return back()->with('error', 'Permohonan tidak dapat diajukan! Anda belum mengunggah dokumen persyaratan apapun. Silakan lengkapi unggah berkas Anda terlebih dahulu.');
        }

        $pendaftaran->update(['status_pendaftaran' => 'diajukan']);

        LogAktivitas::catat('Pengajuan Berkas Pendaftaran', 'Mengajukan berkas pendaftaran #' . $pendaftaran->nomor_pendaftaran . ' untuk diverifikasi Admin');

        return redirect()->route('asesi.dashboard')->with('sukses', 'Pendaftaran FR.APL.01 berhasil diajukan! Tim Admin LSP akan melakukan verifikasi berkas Anda.');
    }

    public function jadwal()
    {
        JadwalAsesmen::syncAllStatuses();
        $pendaftaranList = PendaftaranAsesi::with(['skema', 'jadwal.asesor'])
            ->where('asesi_id', auth()->id())
            ->get();

        return view('asesi.jadwal-asesi', compact('pendaftaranList'));
    }

    public function hasilNilai()
    {
        $pendaftaranList = PendaftaranAsesi::with(['skema', 'jadwal', 'penilaian.unit', 'rekomendasi.asesor'])
            ->where('asesi_id', auth()->id())
            ->get();

        return view('asesi.hasil-nilai', compact('pendaftaranList'));
    }

    public function apl02(Request $request)
    {
        return redirect()->route('asesi.formulir', [
            'tab' => 'apl02',
            'pendaftaran_id' => $request->get('pendaftaran_id'),
        ]);
    }

    public function simpanApl02(Request $request)
    {
        $pengguna = auth()->user();
        
        $pendaftaranId = $request->input('pendaftaran_id');
        if ($pendaftaranId) {
            $pendaftaran = PendaftaranAsesi::with('skema.unitKompetensi.elemenKompetensi')->where('asesi_id', $pengguna->id)->findOrFail($pendaftaranId);
        } else {
            $pendaftaran = PendaftaranAsesi::with('skema.unitKompetensi.elemenKompetensi')->where('asesi_id', $pengguna->id)
                ->latest()
                ->firstOrFail();
        }

        // KUNCI: Cek apakah pendaftaran ditolak oleh Admin
        $isDitolak = ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima');
        if ($isDitolak) {
            return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl01'])
                ->with('error', 'Pendaftaran untuk skema ini telah Ditolak. Anda tidak dapat melanjutkan pengisian formulir. Silakan mendaftar skema sertifikasi lainnya.');
        }

        // KUNCI 1: Gabisa isi kalau Form APL 1 belum di-ACC Admin
        $isAccAdmin = $pendaftaran->isApprovedByAdmin();
        if (!$isAccAdmin) {
            return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl01'])
                ->with('error', 'Formulir FR.APL.02 Asesmen Mandiri belum dapat diisi karena Formulir FR.APL.01 untuk pendaftaran skema ini belum disetujui / diverifikasi oleh Admin LSP.');
        }

        // KUNCI 2: Gabisa isi/edit kalau APL-02 sudah Approved atau sedang Under Review / Submitted
        if ($pendaftaran->isApl02Approved()) {
            return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl02'])
                ->with('error', 'Formulir FR.APL.02 telah disetujui oleh Asesor dan dikunci.');
        }

        if ($pendaftaran->isApl02Submitted() || $pendaftaran->isApl02UnderReview()) {
            return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl02'])
                ->with('error', 'Formulir FR.APL.02 sedang dalam proses pemeriksaan oleh Asesor.');
        }

        $skemaApl02 = $pendaftaran->skema()->with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->firstOrFail();
        $elemenValid = $skemaApl02->unitKompetensi->flatMap->elemenKompetensi->pluck('id')->map(fn ($id) => (string) $id)->all();
        $kukValid = $skemaApl02->unitKompetensi->flatMap->elemenKompetensi->flatMap->kriteriaUnjukKerja->pluck('id')->map(fn ($id) => (string) $id)->all();
        abort_unless(empty(array_diff(array_map('strval', array_keys($request->input('penilaian', []))), $elemenValid)), 422, 'Elemen kompetensi tidak valid untuk skema pendaftaran ini.');
        abort_unless(empty(array_diff(array_map('strval', array_keys($request->input('penilaian_kuk', []))), $kukValid)), 422, 'KUK tidak valid untuk skema pendaftaran ini.');

        $request->validate([
            'penilaian' => 'required|array',
            'penilaian.*' => 'required|in:K,BK',
            'bukti_foto.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'tanda_tangan_asesi' => 'nullable|string',
        ], [
            'penilaian.required' => 'Silakan beri penilaian (K atau BK) pada seluruh elemen kompetensi.',
            'penilaian.*.required' => 'Penilaian K atau BK wajib dipilih untuk setiap elemen kompetensi.',
            'bukti_foto.*.max' => 'Ukuran file foto bukti maksimal 10MB.',
        ]);

        // Cek kelengkapan seluruh elemen
        if ($pendaftaran->skema) {
            $allElemenIds = $pendaftaran->skema->unitKompetensi->flatMap->elemenKompetensi->pluck('id')->toArray();
            $submittedElemenIds = array_keys($request->penilaian ?? []);
            $diff = array_diff($allElemenIds, $submittedElemenIds);
            if (!empty($diff)) {
                return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl02'])
                    ->withInput()
                    ->with('error', 'Masih ada data yang wajib diisi. Silakan lengkapi penilaian seluruh butir KUK terlebih dahulu.');
            }
        }

        $ttdAsesi = $request->tanda_tangan_asesi ?: ($pendaftaran->tanda_tangan_asesi ?: $pengguna->tanda_tangan);
        $pendaftaran->update([
            'status_apl02' => 'submitted',
            'tanggal_submit_apl02' => now(),
            'rekomendasi_asesor_status' => null,
            'tanda_tangan_asesi' => $ttdAsesi,
            'tanggal_ttd_asesi' => now(),
        ]);

        foreach ($request->penilaian as $elemenId => $nilai) {
            $buktiPath = null;

            if ($request->hasFile("bukti_foto.{$elemenId}")) {
                $file = $request->file("bukti_foto.{$elemenId}");
                $namaFile = 'bukti_apl02_' . $pendaftaran->id . '_elem_' . $elemenId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('bukti_apl02', $namaFile, 'public');
                $buktiPath = 'storage/' . $path;
            }

            JawabanApl02::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemenId],
                [
                    'nilai_kompetensi' => $nilai,
                    'bukti_relevan' => $buktiPath,
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
                            'is_verified' => false,
                        ]
                    );
                }
            }
        }

        LogAktivitas::catat('Pengajuan FR.APL.02', 'Asesi mengirim Formulir Asesmen Mandiri FR.APL.02 #' . $pendaftaran->nomor_pendaftaran . ' untuk diperiksa asesor.');

        return redirect()->route('asesi.formulir', ['pendaftaran_id' => $pendaftaran->id, 'tab' => 'apl02'])
            ->with('sukses', 'Formulir FR.APL.02 berhasil dikirim untuk diperiksa oleh Asesor. Mohon tunggu proses verifikasi sebelum melanjutkan ke FR.AK.01.');
    }

    public function ak01(Request $request)
    {
        return $this->ak01Detail($request->get('pendaftaran_id'), $request);
    }

    /**
     * Halaman Utama Formulir FR.AK.01 (Persetujuan Asesmen & Kerahasiaan) dengan Direct URL Protection
     */
    public function ak01Detail($id = null, Request $request = null)
    {
        $pengguna = auth()->user();
        $pendaftaran = $id 
            ? PendaftaranAsesi::where('asesi_id', $pengguna->id)->find($id)
            : PendaftaranAsesi::where('asesi_id', $pengguna->id)->latest()->first();

        if (!$pendaftaran) {
            return redirect()->route('asesi.dashboard')
                ->with('warning', 'Belum ada pendaftaran skema aktif.');
        }

        // STRICT BACKEND GUARD: AK.01 HANYA boleh dibuka jika status_apl02 = approved
        if (!$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('error', 'FR.APL.02 harus disetujui Asesor terlebih dahulu sebelum FR.AK.01 dapat diisi.');
        }

        $pendaftaran->load([
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'skema.masterAk01',
            'asesor',
            'jadwal',
            'verifikasiKukApl02',
            'penilaian'
        ]);

        $pendaftaran->syncFromMasterAk01IfAvailable();

        // Auto-ACC jika asesi sudah menandatangani FR.AK.01 namun status masih menunggu asesor
        if ($pendaftaran->status_ak01 === 'disetujui_asesi' || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && $pendaftaran->status_ak01 !== 'selesai')) {
            $asesor = $pendaftaran->asesor ?: $pendaftaran->jadwal?->asesor;
            $asesorTtd = $pendaftaran->tanda_tangan_asesor_ak01 ?: ($asesor?->tanda_tangan ?: 'signatures/verified_asesor_auto.png');
            $pendaftaran->update([
                'status_ak01' => 'selesai',
                'tanda_tangan_asesor_ak01' => $asesorTtd,
                'tanggal_ttd_asesor_ak01' => $pendaftaran->tanggal_ttd_asesor_ak01 ?: now(),
            ]);
            $pendaftaran->refresh();
        }

        // Cek Unit mana saja yang memiliki butir KUK berstatus BK pada APL.02
        $verifikasiKukMap = $pendaftaran->verifikasiKukApl02->keyBy('kuk_id');
        $unitHasBkMap = [];
        if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
            foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                $hasBk = false;
                foreach ($unit->elemenKompetensi as $elemen) {
                    foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                        $v = $verifikasiKukMap->get($kuk->id);
                        if ($v && (!$v->is_verified || $v->nilai_kompetensi === 'BK')) {
                            $hasBk = true;
                            break 2;
                        }
                    }
                }
                $unitHasBkMap[$unit->id] = $hasBk;
            }
        }

        $semuaPendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)
            ->where('status_pendaftaran', '!=', 'ditolak')
            ->with('skema')
            ->get();

        return view('asesi.ak01', compact('pendaftaran', 'unitHasBkMap', 'semuaPendaftaran'));
    }

    public function simpanAk01(Request $request, $id)
    {
        $pengguna = auth()->user();
        $pendaftaran = PendaftaranAsesi::where('asesi_id', $pengguna->id)->findOrFail($id);

        // KUNCI: Cek apakah pendaftaran ditolak
        $isDitolak = ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima');
        if ($isDitolak) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 1])
                ->with('error', 'Pendaftaran untuk skema ini telah Ditolak. Anda tidak dapat melanjutkan pengisian formulir.');
        }

        // STRICT BACKEND GUARD: Form APL-02 harus berstatus Approved
        if (!$pendaftaran->isAk01Unlocked()) {
            return redirect()->route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id, 'step' => 2])
                ->with('error', 'FR.APL.02 harus disetujui Asesor terlebih dahulu sebelum FR.AK.01 dapat diisi.');
        }

        $request->validate([
            'tuk_type' => 'nullable|string|in:Sewaktu,Tempat Kerja,Mandiri',
            'bukti_dikumpulkan' => 'nullable|array',
            'bukti_dikumpulkan_lainnya' => 'nullable|string',
            'tanda_tangan_asesi_ak01' => 'nullable|string',
        ]);

        $rawTtd = $request->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi ?: $pengguna->tanda_tangan));

        if (empty($rawTtd)) {
            return back()->withInput()->with('error', 'Silakan bubuhkan tanda tangan digital Anda terlebih dahulu.');
        }

        $ttdPath = $rawTtd;

        // Simpan Signature Base64 ke Storage jika berupa DataURL
        if (is_string($rawTtd) && \Illuminate\Support\Str::startsWith($rawTtd, 'data:image')) {
            try {
                $imageParts = explode(';base64,', $rawTtd);
                if (count($imageParts) === 2) {
                    $imageTypeAux = explode('image/', $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageBase64 = base64_decode($imageParts[1]);

                    $fileName = 'signatures/ak01_asesi_' . $pendaftaran->id . '_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $imageType;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                    $ttdPath = 'storage/' . $fileName;

                    $pengguna->update(['tanda_tangan' => $ttdPath]);
                }
            } catch (\Exception $e) {
                \Log::warning('Gagal simpan signature file: ' . $e->getMessage());
            }
        }

        $oldAk01Status = $pendaftaran->status_ak01;

        \Illuminate\Support\Facades\DB::transaction(function () use ($pendaftaran, $request, $ttdPath) {
            $asesor = $pendaftaran->asesor ?: $pendaftaran->jadwal?->asesor;
            $asesorTtd = $pendaftaran->tanda_tangan_asesor_ak01 ?: ($asesor?->tanda_tangan ?: 'signatures/verified_asesor_auto.png');

            $pendaftaran->update([
                'tuk_type' => $request->has('tuk_type') ? $request->tuk_type : $pendaftaran->tuk_type,
                'bukti_dikumpulkan' => $request->has('bukti_dikumpulkan') ? $request->bukti_dikumpulkan : $pendaftaran->bukti_dikumpulkan,
                'bukti_dikumpulkan_lainnya' => $request->has('bukti_dikumpulkan_lainnya') ? $request->bukti_dikumpulkan_lainnya : $pendaftaran->bukti_dikumpulkan_lainnya,
                'tanda_tangan_asesi_ak01' => $ttdPath,
                'tanggal_ttd_asesi_ak01' => now(),
                'status_ak01' => 'selesai',
                'tanda_tangan_asesor_ak01' => $asesorTtd,
                'tanggal_ttd_asesor_ak01' => $pendaftaran->tanggal_ttd_asesor_ak01 ?: now(),
            ]);

            LogAktivitas::catat('Pengisian FR.AK.01', 'Asesi menyetujui dan menandatangani Persetujuan Asesmen FR.AK.01 #' . $pendaftaran->nomor_pendaftaran . ' (ACC Otomatis)');
        });

        // Trigger Notification ke Asesi (Hanya saat pertama kali / cegah duplikasi)
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

    /**
     * Dokumen & Bukti Asesmen (Portofolio Persyaratan & Berkas)
     */
    public function dokumenBukti(Request $request)
    {
        $pengguna = auth()->user();
        $pendaftaranAktif = PendaftaranAsesi::with(['skema.unitKompetensi', 'dokumen', 'jadwal'])
            ->where('asesi_id', $pengguna->id)
            ->latest()
            ->first();

        if ($pendaftaranAktif) {
            return view('asesi.upload-dokumen', ['pendaftaran' => $pendaftaranAktif]);
        }

        return redirect()->route('asesi.formulir')->with('info', 'Silakan daftarkan atau lengkapi formulir pendaftaran skema terlebih dahulu.');
    }
}
