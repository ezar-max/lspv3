<?php

namespace App\Http\Controllers;

use App\Models\BeritaPengumuman;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengumumanController extends Controller
{
    public function index()
    {
        $beritaList = BeritaPengumuman::with('penulis')->latest('tanggal_publikasi')->paginate(10);
        return view('admin.manajemen-pengumuman', compact('beritaList'));
    }

    public function simpanPengumuman(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:berita,pengumuman,panduan',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $namaFile = 'berita_' . time() . '.' . $file->getClientOriginalExtension();
            $gambarPath = 'storage/' . $file->storeAs('berita', $namaFile, 'public');
        }

        $slug = Str::slug($request->judul) . '-' . rand(100, 999);

        $berita = BeritaPengumuman::create([
            'judul' => $request->judul,
            'slug' => $slug,
            'kategori' => $request->kategori,
            'ringkasan' => $request->ringkasan,
            'konten' => $request->konten,
            'gambar' => $gambarPath,
            'penulis_id' => auth()->id(),
            'dipublikasikan' => $request->has('dipublikasikan'),
            'tanggal_publikasi' => now(),
        ]);

        LogAktivitas::catat('Tambah Berita/Pengumuman', 'Memublikasikan artikel: ' . $berita->judul);

        return back()->with('sukses', 'Berita / Pengumuman berhasil dipublikasikan.');
    }

    public function ubahPengumuman(Request $request, $id)
    {
        $berita = BeritaPengumuman::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:berita,pengumuman,panduan',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $namaFile = 'berita_' . time() . '.' . $file->getClientOriginalExtension();
            $berita->gambar = 'storage/' . $file->storeAs('berita', $namaFile, 'public');
        }

        $berita->update([
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'ringkasan' => $request->ringkasan,
            'konten' => $request->konten,
            'dipublikasikan' => $request->has('dipublikasikan'),
        ]);

        LogAktivitas::catat('Ubah Berita/Pengumuman', 'Memperbarui artikel: ' . $berita->judul);

        return back()->with('sukses', 'Berita / Pengumuman berhasil diperbarui.');
    }

    public function hapusPengumuman($id)
    {
        $berita = BeritaPengumuman::findOrFail($id);
        $judul = $berita->judul;
        $berita->delete();

        LogAktivitas::catat('Hapus Berita/Pengumuman', 'Menghapus artikel: ' . $judul);

        return back()->with('sukses', 'Berita / Pengumuman berhasil dihapus.');
    }
}
