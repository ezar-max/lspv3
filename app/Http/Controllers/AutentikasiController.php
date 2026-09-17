<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\ProfilAsesi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AutentikasiController extends Controller
{
    public function tampilMasuk()
    {
        if (Auth::check()) {
            return $this->redirectBerdasarkanPeran(Auth::user());
        }
        return view('autentikasi.masuk');
    }

    public function prosesMasuk(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'kata_sandi' => 'required',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'kata_sandi.required' => 'Kata sandi wajib diisi.',
        ]);

        $pengguna = Pengguna::where('email', $request->email)->first();

        if ($pengguna && Hash::check($request->kata_sandi, $pengguna->kata_sandi)) {
            if (!$pengguna->aktif) {
                return back()->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.')->withInput();
            }

            Auth::login($pengguna);
            LogAktivitas::catat('Pengguna Masuk', 'Berhasil masuk ke sistem');

            return $this->redirectBerdasarkanPeran($pengguna)->with('sukses', 'Selamat datang kembali, ' . $pengguna->nama_lengkap . '!');
        }

        return back()->with('error', 'Email atau kata sandi yang Anda masukkan salah.')->withInput();
    }

    public function tampilRegistrasi()
    {
        if (Auth::check()) {
            return $this->redirectBerdasarkanPeran(Auth::user());
        }
        return view('publik.registrasi');
    }

    public function prosesRegistrasi(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email',
            'kata_sandi' => 'required|min:6|confirmed',
            'nomor_telepon' => 'required|string|max:20',
            'nik' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'nama_sekolah_instansi' => 'nullable|string|max:255',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'kata_sandi.required' => 'Kata sandi wajib diisi.',
            'kata_sandi.min' => 'Kata sandi minimal 6 karakter.',
            'kata_sandi.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'nomor_telepon.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'nik.required' => 'NIK (Nomor Induk Kependudukan) wajib 16 digit.',
            'nik.size' => 'NIK harus berjumlah tepat 16 digit angka.',
            'nik.regex' => 'NIK hanya boleh berisi 16 digit angka.',
        ]);

        $pengguna = Pengguna::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => 'asesi',
            'nomor_telepon' => $request->nomor_telepon,
            'aktif' => true,
        ]);

        ProfilAsesi::create([
            'pengguna_id' => $pengguna->id,
            'nik' => $request->nik,
            'nama_sekolah_instansi' => $request->nama_sekolah_instansi,
            'nomor_pendaftaran' => 'REG-' . date('Ymd') . '-' . sprintf('%04d', $pengguna->id),
        ]);

        Auth::login($pengguna);
        LogAktivitas::catat('Registrasi Asesi Baru', 'Pendaftaran akun asesi baru atas nama ' . $pengguna->nama_lengkap);

        return redirect()->route('asesi.dashboard')->with('sukses', 'Pendaftaran akun berhasil! Selamat datang di Portal Asesi LSP.');
    }

    public function keluar()
    {
        if (Auth::check()) {
            LogAktivitas::catat('Pengguna Keluar', 'Keluar dari sistem');
            Auth::logout();
        }
        return redirect()->route('beranda')->with('sukses', 'Anda telah berhasil keluar dari sistem.');
    }

    private function redirectBerdasarkanPeran($pengguna)
    {
        switch ($pengguna->peran) {
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'asesor':
                return redirect()->route('asesor.dashboard');
            case 'asesi':
            default:
                return redirect()->route('asesi.dashboard');
        }
    }
}
