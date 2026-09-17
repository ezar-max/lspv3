<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\LogAktivitas;
use App\Models\PengaturanSistem;
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

        $logTerbaru = LogAktivitas::with('pengguna')->latest()->take(7)->get();

        return view('superadmin.dashboard-superadmin', compact('statistik', 'logTerbaru'));
    }

    public function manajemenPengguna(Request $request)
    {
        $peranFilter = $request->get('peran');
        $kataKunci = $request->get('q');

        $query = Pengguna::query();

        if ($peranFilter) {
            $query->where('peran', $peranFilter);
        }

        if ($kataKunci) {
            $query->where(function($q) use ($kataKunci) {
                $q->where('nama_lengkap', 'like', "%{$kataKunci}%")
                  ->orWhere('email', 'like', "%{$kataKunci}%");
            });
        }

        $penggunaList = $query->latest()->paginate(10);

        return view('superadmin.manajemen-pengguna', compact('penggunaList', 'peranFilter', 'kataKunci'));
    }

    public function simpanPengguna(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email',
            'kata_sandi' => 'required|min:6',
            'peran' => 'required|in:superadmin,admin,asesor,asesi',
            'nomor_telepon' => 'nullable|string',
        ]);

        $pengguna = Pengguna::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => $request->peran,
            'nomor_telepon' => $request->nomor_telepon,
            'aktif' => true,
        ]);

        if ($request->peran === 'asesi') {
            ProfilAsesi::create([
                'pengguna_id' => $pengguna->id,
                'nomor_pendaftaran' => 'REG-' . date('Ymd') . '-' . sprintf('%04d', $pengguna->id),
            ]);
        }

        LogAktivitas::catat('Tambah Pengguna System', 'Super Admin membuat akun baru: ' . $pengguna->nama_lengkap . ' (' . strtoupper($pengguna->peran) . ')');

        return back()->with('sukses', 'Pengguna baru berhasil ditambahkan.');
    }

    public function ubahPengguna(Request $request, $id)
    {
        $pengguna = Pengguna::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email,' . $pengguna->id,
            'peran' => 'required|in:superadmin,admin,asesor,asesi',
            'nomor_telepon' => 'nullable|string',
        ]);

        $pengguna->update([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'peran' => $request->peran,
            'nomor_telepon' => $request->nomor_telepon,
            'aktif' => $request->has('aktif'),
        ]);

        if ($request->filled('kata_sandi')) {
            $pengguna->update(['kata_sandi' => Hash::make($request->kata_sandi)]);
        }

        LogAktivitas::catat('Ubah Pengguna System', 'Super Admin memperbarui akun: ' . $pengguna->nama_lengkap);

        return back()->with('sukses', 'Data pengguna berhasil diperbarui.');
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
