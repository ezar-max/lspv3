<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\LogAktivitas;
use App\Models\PengaturanSistem;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $statistik = [
            'superadmin' => Pengguna::where('peran', 'superadmin')->count(),
            'admin' => Pengguna::where('peran', 'admin')->count(),
            'asesor' => Pengguna::where('peran', 'asesor')->count(),
            'asesi' => Pengguna::where('peran', 'asesi')->count(),
        ];

        $totalSkema = SkemaSertifikasi::count();
        $totalPendaftaran = \App\Models\PendaftaranAsesi::count();
        $totalPending = \App\Models\PendaftaranAsesi::where('status_pendaftaran', 'diajukan')->count();

        $logTerbaru = LogAktivitas::with('pengguna')->latest()->take(7)->get();

        return view('superadmin.dashboard-superadmin', compact('statistik', 'logTerbaru', 'totalSkema', 'totalPendaftaran', 'totalPending'));
    }

    public function manajemenPengguna(Request $request)
    {
        $peranFilter = $request->get('peran');
        $kataKunci = $request->get('q');

        $query = Pengguna::with(['profilAsesi', 'skema', 'pendaftaranAsesi.skema']);

        if ($peranFilter) {
            $query->where('peran', $peranFilter);
        }

        if ($kataKunci) {
            $query->where(function($q) use ($kataKunci) {
                $q->where('nama_lengkap', 'like', "%{$kataKunci}%")
                  ->orWhere('email', 'like', "%{$kataKunci}%")
                  ->orWhere('nomor_telepon', 'like', "%{$kataKunci}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$kataKunci}%")
                  ->orWhereHas('profilAsesi', function($sq) use ($kataKunci) {
                      $sq->where('nik', 'like', "%{$kataKunci}%")
                         ->orWhere('nama_sekolah_instansi', 'like', "%{$kataKunci}%")
                         ->orWhere('pendidikan_terakhir', 'like', "%{$kataKunci}%")
                         ->orWhere('pekerjaan', 'like', "%{$kataKunci}%");
                  });
            });
        }

        $penggunaList = $query->latest()->paginate(10)->appends($request->query());
        $daftarSkema = SkemaSertifikasi::orderBy('nama_skema', 'asc')->get();

        $statistik = [
            'total' => Pengguna::count(),
            'superadmin' => Pengguna::where('peran', 'superadmin')->count(),
            'admin' => Pengguna::where('peran', 'admin')->count(),
            'asesor' => Pengguna::where('peran', 'asesor')->count(),
            'asesi' => Pengguna::where('peran', 'asesi')->count(),
        ];

        return view('superadmin.manajemen-pengguna', compact('penggunaList', 'peranFilter', 'kataKunci', 'daftarSkema', 'statistik'));
    }

    public function simpanPengguna(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email',
            'kata_sandi' => 'required|min:6',
            'peran' => 'required|in:superadmin,admin,asesor,asesi',
            'nomor_telepon' => 'nullable|string|max:50',
            'nomor_registrasi' => 'nullable|string|max:100',
            'skema_id' => 'nullable|exists:skema_sertifikasi,id',
            'nik' => 'nullable|string|max:20',
            'nama_sekolah_instansi' => 'nullable|string|max:255',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
        ]);

        $pengguna = Pengguna::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => $request->peran,
            'nomor_telepon' => $request->nomor_telepon,
            'nomor_registrasi' => $request->peran === 'asesor' ? $request->nomor_registrasi : null,
            'skema_id' => $request->peran === 'asesor' ? $request->skema_id : null,
            'aktif' => true,
        ]);

        if ($request->peran === 'asesi') {
            ProfilAsesi::create([
                'pengguna_id' => $pengguna->id,
                'nik' => $request->nik,
                'nama_sekolah_instansi' => $request->nama_sekolah_instansi,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'pekerjaan' => $request->pekerjaan,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'nomor_pendaftaran' => 'REG-' . date('Ymd') . '-' . sprintf('%04d', $pengguna->id),
            ]);
        }

        LogAktivitas::catat('Tambah Pengguna System', 'Super Admin membuat akun baru: ' . $pengguna->nama_lengkap . ' (' . strtoupper($pengguna->peran) . ')');

        return back()->with('sukses', 'Pengguna baru ' . $pengguna->nama_lengkap . ' (' . strtoupper($pengguna->peran) . ') berhasil ditambahkan.');
    }

    public function ubahPengguna(Request $request, $id)
    {
        $pengguna = Pengguna::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email,' . $pengguna->id,
            'peran' => 'required|in:superadmin,admin,asesor,asesi',
            'nomor_telepon' => 'nullable|string|max:50',
            'kata_sandi' => 'nullable|min:6',
            'nomor_registrasi' => 'nullable|string|max:100',
            'skema_id' => 'nullable|exists:skema_sertifikasi,id',
            'nik' => 'nullable|string|max:20',
            'nama_sekolah_instansi' => 'nullable|string|max:255',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'peran' => $request->peran,
            'nomor_telepon' => $request->nomor_telepon,
            'nomor_registrasi' => $request->peran === 'asesor' ? $request->nomor_registrasi : null,
            'skema_id' => $request->peran === 'asesor' ? $request->skema_id : null,
            'aktif' => $request->has('aktif') ? (bool)$request->aktif : false,
        ];

        if ($request->filled('kata_sandi')) {
            $data['kata_sandi'] = Hash::make($request->kata_sandi);
        }

        $pengguna->update($data);

        // Update Profil Asesi jika peran asesi
        if ($request->peran === 'asesi') {
            ProfilAsesi::updateOrCreate(
                ['pengguna_id' => $pengguna->id],
                [
                    'nik' => $request->nik,
                    'nama_sekolah_instansi' => $request->nama_sekolah_instansi,
                    'pendidikan_terakhir' => $request->pendidikan_terakhir,
                    'pekerjaan' => $request->pekerjaan,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'alamat' => $request->alamat,
                ]
            );
        }

        LogAktivitas::catat('Ubah Pengguna System', 'Super Admin memperbarui data akun: ' . $pengguna->nama_lengkap . ' (' . strtoupper($pengguna->peran) . ')');

        return back()->with('sukses', 'Data akun ' . $pengguna->nama_lengkap . ' berhasil diperbarui.');
    }

    public function hapusPengguna($id)
    {
        $pengguna = Pengguna::findOrFail($id);

        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $pengguna->nama_lengkap;
        $pengguna->delete();

        LogAktivitas::catat('Hapus Pengguna System', 'Super Admin menghapus akun: ' . $nama);

        return back()->with('sukses', 'Pengguna berhasil dihapus.');
    }

    public function logAktivitas()
    {
        $logList = LogAktivitas::with('pengguna')->latest()->paginate(20);
        return view('superadmin.log-aktivitas', compact('logList'));
    }

    public function pengaturanSistem()
    {
        $pengaturan = PengaturanSistem::ambilData();
        return view('superadmin.pengaturan-sistem', compact('pengaturan'));
    }

    public function simpanPengaturanSistem(Request $request)
    {
        $pengaturan = PengaturanSistem::ambilData();

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

        LogAktivitas::catat('Ubah Pengaturan Sistem', 'Super Admin memperbarui konfigurasi global LSP');

        return back()->with('sukses', 'Pengaturan sistem global berhasil disimpan.');
    }
}
