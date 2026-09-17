<?php

namespace App\Http\Controllers;

use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class SkemaController extends Controller
{
    public function index()
    {
        $skemaList = SkemaSertifikasi::with(['unitKompetensi.elemenKompetensi.kriteriaUnjukKerja'])
            ->withCount('unitKompetensi')
            ->latest()
            ->paginate(15);
        return view('admin.manajemen-skema', compact('skemaList'));
    }

    public function simpanSkema(Request $request)
    {
        $request->validate([
            'kode_skema' => 'required|string|unique:skema_sertifikasi,kode_skema',
            'nama_skema' => 'required|string|max:255',
            'kategori' => 'required|string',
            'biaya' => 'nullable|numeric',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $namaFile = 'skema_' . time() . '.' . $file->getClientOriginalExtension();
            $gambarPath = 'storage/' . $file->storeAs('skema', $namaFile, 'public');
        }

        $skema = SkemaSertifikasi::create([
            'kode_skema' => $request->kode_skema,
            'nama_skema' => $request->nama_skema,
            'kategori' => $request->kategori,
            'biaya' => $request->biaya ?? 0,
            'deskripsi' => $request->deskripsi,
            'status_aktif' => $request->has('status_aktif'),
            'gambar' => $gambarPath,
        ]);

        // Simpan unit kompetensi sekaligus jika ada
        if ($request->has('unit_kode') && is_array($request->unit_kode)) {
            foreach ($request->unit_kode as $idx => $kodeUnit) {
                if (!empty($kodeUnit) && !empty($request->unit_judul[$idx] ?? null)) {
                    UnitKompetensi::create([
                        'skema_id' => $skema->id,
                        'kode_unit' => $kodeUnit,
                        'judul_unit' => $request->unit_judul[$idx],
                        'standar_kompetensi' => $request->unit_standar[$idx] ?? 'SKKNI',
                    ]);
                }
            }
        }

        LogAktivitas::catat('Tambah Skema Sertifikasi', 'Menambahkan skema baru: ' . $skema->nama_skema);

        return back()->with('sukses', 'Skema sertifikasi dan unit kompetensi berhasil ditambahkan.');
    }

    public function ubahSkema(Request $request, $id)
    {
        $skema = SkemaSertifikasi::findOrFail($id);

        $request->validate([
            'kode_skema' => 'required|string|unique:skema_sertifikasi,kode_skema,' . $skema->id,
            'nama_skema' => 'required|string|max:255',
            'kategori' => 'required|string',
            'biaya' => 'nullable|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $namaFile = 'skema_' . time() . '.' . $file->getClientOriginalExtension();
            $skema->gambar = 'storage/' . $file->storeAs('skema', $namaFile, 'public');
        }

        $skema->update([
            'kode_skema' => $request->kode_skema,
            'nama_skema' => $request->nama_skema,
            'kategori' => $request->kategori,
            'biaya' => $request->biaya ?? 0,
            'deskripsi' => $request->deskripsi,
            'status_aktif' => $request->has('status_aktif'),
        ]);

        LogAktivitas::catat('Ubah Skema Sertifikasi', 'Memperbarui skema: ' . $skema->nama_skema);

        return back()->with('sukses', 'Skema sertifikasi berhasil diperbarui.');
    }

    public function hapusSkema($id)
    {
        $skema = SkemaSertifikasi::findOrFail($id);
        $namaSkema = $skema->nama_skema;
        $skema->delete();

        LogAktivitas::catat('Hapus Skema Sertifikasi', 'Menghapus skema: ' . $namaSkema);

        return back()->with('sukses', 'Skema sertifikasi berhasil dihapus.');
    }

    public function simpanUnit(Request $request, $skemaId)
    {
        $skema = SkemaSertifikasi::findOrFail($skemaId);

        $request->validate([
            'kode_unit' => 'required|string',
            'judul_unit' => 'required|string',
            'standar_kompetensi' => 'required|string',
        ]);

        UnitKompetensi::create([
            'skema_id' => $skema->id,
            'kode_unit' => $request->kode_unit,
            'judul_unit' => $request->judul_unit,
            'standar_kompetensi' => $request->standar_kompetensi,
        ]);

        LogAktivitas::catat('Tambah Unit Kompetensi', 'Menambah unit kompetensi pada skema ' . $skema->nama_skema);

        return back()->with('sukses', 'Unit kompetensi berhasil ditambahkan.');
    }

    public function hapusUnit($id)
    {
        $unit = UnitKompetensi::findOrFail($id);
        $unit->delete();

        return back()->with('sukses', 'Unit kompetensi berhasil dihapus.');
    }

    // MANAJEMEN ELEMEN & KUK
    public function simpanElemen(Request $request, $unitId)
    {
        $unit = UnitKompetensi::findOrFail($unitId);

        $request->validate([
            'nomor_elemen' => 'required|integer',
            'nama_elemen' => 'required|string',
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_id' => $unit->id,
            'nomor_elemen' => $request->nomor_elemen,
            'nama_elemen' => $request->nama_elemen,
            'pertanyaan_elemen' => $request->pertanyaan_elemen ?? null,
        ]);

        // Simpan KUK sekaligus jika diisi
        if ($request->has('kuk_nomor') && is_array($request->kuk_nomor)) {
            foreach ($request->kuk_nomor as $idx => $nomorKuk) {
                if (!empty($nomorKuk) && !empty($request->kuk_pernyataan[$idx] ?? null)) {
                    KriteriaUnjukKerja::create([
                        'elemen_id' => $elemen->id,
                        'nomor_kuk' => $nomorKuk,
                        'pernyataan_kuk' => $request->kuk_pernyataan[$idx],
                    ]);
                }
            }
        }

        LogAktivitas::catat('Tambah Elemen Kompetensi', 'Menambah Elemen #' . $elemen->nomor_elemen . ' pada Unit ' . $unit->kode_unit);

        return back()->with('sukses', 'Elemen kompetensi dan KUK berhasil ditambahkan.');
    }

    public function ubahElemen(Request $request, $id)
    {
        $elemen = ElemenKompetensi::findOrFail($id);

        $request->validate([
            'nomor_elemen' => 'required|integer',
            'nama_elemen' => 'required|string',
        ]);

        $elemen->update([
            'nomor_elemen' => $request->nomor_elemen,
            'nama_elemen' => $request->nama_elemen,
            'pertanyaan_elemen' => $request->pertanyaan_elemen ?? null,
        ]);

        LogAktivitas::catat('Ubah Elemen Kompetensi', 'Memperbarui Elemen #' . $elemen->nomor_elemen);

        return back()->with('sukses', 'Elemen kompetensi berhasil diperbarui.');
    }

    public function hapusElemen($id)
    {
        $elemen = ElemenKompetensi::findOrFail($id);
        $elemen->delete();

        return back()->with('sukses', 'Elemen kompetensi berhasil dihapus.');
    }

    public function simpanKuk(Request $request, $elemenId)
    {
        $elemen = ElemenKompetensi::findOrFail($elemenId);

        $request->validate([
            'nomor_kuk' => 'required|string',
            'pernyataan_kuk' => 'required|string',
        ]);

        KriteriaUnjukKerja::create([
            'elemen_id' => $elemen->id,
            'nomor_kuk' => $request->nomor_kuk,
            'pernyataan_kuk' => $request->pernyataan_kuk,
        ]);

        LogAktivitas::catat('Tambah KUK', 'Menambah KUK ' . $request->nomor_kuk . ' pada Elemen #' . $elemen->nomor_elemen);

        return back()->with('sukses', 'Kriteria Unjuk Kerja (KUK) berhasil ditambahkan.');
    }

    public function hapusKuk($id)
    {
        $kuk = KriteriaUnjukKerja::findOrFail($id);
        $kuk->delete();

        return back()->with('sukses', 'Kriteria Unjuk Kerja (KUK) berhasil dihapus.');
    }
}
