<?php

namespace App\Http\Controllers;

use App\Models\SkemaSertifikasi;
use App\Models\BeritaPengumuman;
use App\Models\PengaturanSistem;
use App\Models\Pengguna;
use App\Models\PendaftaranAsesi;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function beranda()
    {
        $pengaturan = PengaturanSistem::ambilData();
        $daftarSkema = SkemaSertifikasi::where('status_aktif', true)
            ->withCount('unitKompetensi')
            ->get();
        $daftarBerita = BeritaPengumuman::where('dipublikasikan', true)
            ->latest('tanggal_publikasi')
            ->take(6)
            ->get();

        $statistik = [
            'total_skema' => SkemaSertifikasi::where('status_aktif', true)->count(),
            'total_asesi' => Pengguna::where('peran', 'asesi')->count(),
            'total_asesor' => Pengguna::where('peran', 'asesor')->count(),
            'total_sertifikat' => PendaftaranAsesi::where('status_pendaftaran', 'diverifikasi')->count(),
        ];

        return view('landing', compact('daftarSkema', 'daftarBerita', 'pengaturan', 'statistik'));
    }

    public function apiSkemaUnits($id)
    {
        $skema = SkemaSertifikasi::with('unitKompetensi')->findOrFail($id);
        return response()->json([
            'success' => true,
            'skema' => $skema->nama_skema,
            'units' => $skema->unitKompetensi,
        ]);
    }

    public function profilLsp()
    {
        $pengaturan = PengaturanSistem::ambilData();
        $skemaList = SkemaSertifikasi::where('status_aktif', true)->withCount('unitKompetensi')->get();
        $asesorList = Pengguna::where('peran', 'asesor')->where('aktif', true)->get();
        $tukList = \App\Models\JadwalAsesmen::select('nama_tuk')->distinct()->whereNotNull('nama_tuk')->get();

        $statistik = [
            'total_tuk' => $tukList->count(),
            'total_skema' => $skemaList->count(),
            'total_asesor' => $asesorList->count(),
        ];

        return view('publik.profil-lsp', compact('pengaturan', 'skemaList', 'asesorList', 'tukList', 'statistik'));
    }

    public function daftarSkema(Request $request)
    {
        $kataKunci = $request->get('q');
        $kategori = $request->get('kategori');

        $query = SkemaSertifikasi::where('status_aktif', true);

        if ($kataKunci) {
            $query->where(function($q) use ($kataKunci) {
                $q->where('nama_skema', 'like', "%{$kataKunci}%")
                  ->orWhere('kode_skema', 'like', "%{$kataKunci}%")
                  ->orWhere('deskripsi', 'like', "%{$kataKunci}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $skemaList = $query->withCount('unitKompetensi')->paginate(9);
        $kategoriList = SkemaSertifikasi::select('kategori')->distinct()->pluck('kategori');

        return view('publik.skema-daftar', compact('skemaList', 'kategoriList', 'kataKunci', 'kategori'));
    }

    public function detailSkema($id)
    {
        $skema = SkemaSertifikasi::with('unitKompetensi')->findOrFail($id);
        return view('publik.skema-detail', compact('skema'));
    }

    public function daftarBerita(Request $request)
    {
        $kategori = $request->get('kategori');
        $kataKunci = $request->get('q');

        $query = BeritaPengumuman::where('dipublikasikan', true)->with('penulis');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($kataKunci) {
            $query->where('judul', 'like', "%{$kataKunci}%");
        }

        $beritaList = $query->latest('tanggal_publikasi')->paginate(6);

        return view('publik.berita-daftar', compact('beritaList', 'kategori', 'kataKunci'));
    }

    public function detailBerita($slug)
    {
        $berita = BeritaPengumuman::with('penulis')->where('slug', $slug)->firstOrFail();
        $beritaTerkait = BeritaPengumuman::where('dipublikasikan', true)
            ->where('id', '!=', $berita->id)
            ->take(3)
            ->get();

        return view('publik.berita-detail', compact('berita', 'beritaTerkait'));
    }

    public function kontak()
    {
        $pengaturan = PengaturanSistem::ambilData();
        return view('publik.kontak', compact('pengaturan'));
    }
}
