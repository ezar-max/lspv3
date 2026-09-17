<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\JadwalAsesmen;
use App\Models\Pengguna;
use App\Models\SuratTugas;
use App\Models\BeritaAcara;
use App\Models\DokumenLegalitasLsp;
use App\Models\PengaturanSistem;
use App\Models\IaPenilaian;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenAdminController extends Controller
{
    /**
     * Pusat Dokumen & Arsip Asesmen LSP (4 Tab Utama)
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'bundel');
        $pengaturan = PengaturanSistem::ambilData();

        // Data Skema & Jadwal untuk dropdown filter
        $skemaOptions = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema', 'asc')->get();
        $jadwalOptions = JadwalAsesmen::with('skema')->latest('tanggal_uji')->get();
        $asesorOptions = Pengguna::where('peran', 'asesor')->where('aktif', true)->orderBy('nama_lengkap', 'asc')->get();

        // -------------------------------------------------------------
        // TAB 1: BERKAS BUNDEL ASESMEN (PORTOFOLIO UJI)
        // -------------------------------------------------------------
        $skemaId = $request->get('skema_id');
        $jadwalId = $request->get('jadwal_id');
        $statusHasil = $request->get('hasil');
        $statusBerkas = $request->get('status_berkas');
        $kataKunci = $request->get('q');

        $queryBundel = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi',
            'jadwal.asesor',
            'dokumen',
            'rekomendasi'
        ]);

        if ($skemaId) $queryBundel->where('skema_id', $skemaId);
        if ($jadwalId) $queryBundel->where('jadwal_id', $jadwalId);
        if ($statusHasil) {
            if ($statusHasil === 'belum_dinilai') {
                $queryBundel->whereDoesntHave('rekomendasi');
            } else {
                $queryBundel->whereHas('rekomendasi', function ($q) use ($statusHasil) {
                    $q->where('keputusan', $statusHasil);
                });
            }
        }
        if ($statusBerkas) {
            $queryBundel->where('status_pendaftaran', $statusBerkas);
        }
        if ($kataKunci) {
            $queryBundel->where(function ($q) use ($kataKunci) {
                $q->where('nomor_pendaftaran', 'like', "%{$kataKunci}%")
                  ->orWhereHas('asesi', function ($sq) use ($kataKunci) {
                      $sq->where('nama_lengkap', 'like', "%{$kataKunci}%")
                         ->orWhere('email', 'like', "%{$kataKunci}%");
                  });
            });
        }

        $bundelList = $queryBundel->latest()->paginate(15, ['*'], 'bundel_page')->withQueryString();

        // -------------------------------------------------------------
        // TAB 2: SURAT TUGAS ASESOR
        // -------------------------------------------------------------
        $querySurat = SuratTugas::with(['jadwal.skema', 'asesor']);
        if ($kataKunci && $tab === 'surat-tugas') {
            $querySurat->where('nomor_surat', 'like', "%{$kataKunci}%")
                ->orWhereHas('asesor', fn($q) => $q->where('nama_lengkap', 'like', "%{$kataKunci}%"));
        }
        $suratTugasList = $querySurat->latest('tanggal_surat')->paginate(15, ['*'], 'surat_page')->withQueryString();

        // -------------------------------------------------------------
        // TAB 3: BERITA ACARA & RAPAT PLENO (FR.AK.05 & FR.AK.06)
        // -------------------------------------------------------------
        $queryBa = BeritaAcara::with(['jadwal.skema', 'jadwal.asesor', 'jadwal.pendaftaranAsesi.rekomendasi']);
        if ($kataKunci && $tab === 'berita-acara') {
            $queryBa->where('nomor_berita_acara', 'like', "%{$kataKunci}%");
        }
        $beritaAcaraList = $queryBa->latest('tanggal_pelaksanaan')->paginate(15, ['*'], 'ba_page')->withQueryString();

        // -------------------------------------------------------------
        // TAB 4: DOKUMEN LEGALITAS & LISENSI LSP
        // -------------------------------------------------------------
        $queryLegalitas = DokumenLegalitasLsp::query();
        if ($kataKunci && $tab === 'legalitas') {
            $queryLegalitas->where('nama_dokumen', 'like', "%{$kataKunci}%")
                ->orWhere('nomor_dokumen', 'like', "%{$kataKunci}%");
        }
        $legalitasList = $queryLegalitas->latest()->paginate(15, ['*'], 'legalitas_page')->withQueryString();

        // Statistik Cepat untuk Badges
        $counts = [
            'total_bundel' => PendaftaranAsesi::count(),
            'total_surat' => SuratTugas::count(),
            'total_ba' => BeritaAcara::count(),
            'total_legalitas' => DokumenLegalitasLsp::count(),
        ];

        return view('admin.dokumen.index', compact(
            'tab', 'pengaturan', 'skemaOptions', 'jadwalOptions', 'asesorOptions',
            'bundelList', 'suratTugasList', 'beritaAcaraList', 'legalitasList', 'counts',
            'skemaId', 'jadwalId', 'statusHasil', 'statusBerkas', 'kataKunci'
        ));
    }

    /**
     * Simpan / Terbitkan Surat Tugas Asesor
     */
    public function simpanSuratTugas(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'nullable|exists:jadwal_asesmen,id',
            'asesor_id' => 'required|exists:pengguna,id',
            'tanggal_surat' => 'required|date',
            'tujuan_penugasan' => 'required|string|max:255',
            'lokasi_tuk' => 'required|string|max:255',
        ]);

        $jadwal = $request->jadwal_id ? JadwalAsesmen::find($request->jadwal_id) : null;
        $tanggalSurat = $request->tanggal_surat;
        $nomorUrut = SuratTugas::whereYear('tanggal_surat', date('Y', strtotime($tanggalSurat)))->count() + 1;
        $nomorSurat = sprintf("ST-%03d/LSP-SMKN1/%s/%s", $nomorUrut, date('m', strtotime($tanggalSurat)), date('Y', strtotime($tanggalSurat)));

        if ($request->filled('nomor_surat_custom')) {
            $nomorSurat = $request->nomor_surat_custom;
        }

        $surat = SuratTugas::create([
            'jadwal_id' => $request->jadwal_id,
            'asesor_id' => $request->asesor_id,
            'nomor_surat' => $nomorSurat,
            'tanggal_surat' => $tanggalSurat,
            'tanggal_mulai' => $jadwal ? $jadwal->tanggal_uji : $tanggalSurat,
            'tanggal_selesai' => $jadwal ? $jadwal->tanggal_uji : $tanggalSurat,
            'tujuan_penugasan' => $request->tujuan_penugasan,
            'lokasi_tuk' => $request->lokasi_tuk,
            'status' => 'Diterbitkan',
            'catatan' => $request->catatan,
        ]);

        LogAktivitas::catat('Terbitkan Surat Tugas', 'Menerbitkan surat tugas nomor ' . $surat->nomor_surat);

        return redirect()->route('admin.dokumen.index', ['tab' => 'surat-tugas'])->with('sukses', 'Surat Tugas Asesor berhasil diterbitkan dengan nomor: ' . $surat->nomor_surat);
    }

    /**
     * Cetak Surat Tugas Asesor Resmi BNSP
     */
    public function cetakSuratTugas($id)
    {
        $surat = SuratTugas::with(['jadwal.skema', 'asesor'])->findOrFail($id);
        $pengaturan = PengaturanSistem::ambilData();
        $admin = auth()->user();

        return view('admin.dokumen.cetak-surat-tugas', compact('surat', 'pengaturan', 'admin'));
    }

    /**
     * Hapus Surat Tugas
     */
    public function hapusSuratTugas($id)
    {
        $surat = SuratTugas::findOrFail($id);
        $nomor = $surat->nomor_surat;
        $surat->delete();

        LogAktivitas::catat('Hapus Surat Tugas', 'Menghapus surat tugas: ' . $nomor);

        return back()->with('sukses', 'Surat tugas berhasil dihapus.');
    }

    /**
     * Generate Berita Acara & Rapat Pleno (FR.AK.05 & FR.AK.06)
     */
    public function generateBeritaAcara(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_asesmen,id',
            'tanggal_pelaksanaan' => 'required|date',
        ]);

        $jadwal = JadwalAsesmen::with(['pendaftaranAsesi.rekomendasi'])->findOrFail($request->jadwal_id);

        $totalPeserta = $jadwal->pendaftaranAsesi->count();
        $totalKompeten = $jadwal->pendaftaranAsesi->filter(function($p) {
            return $p->rekomendasi && $p->rekomendasi->keputusan === 'kompeten';
        })->count();
        $totalBelumKompeten = $jadwal->pendaftaranAsesi->filter(function($p) {
            return $p->rekomendasi && $p->rekomendasi->keputusan === 'belum_kompeten';
        })->count();

        $nomorUrut = BeritaAcara::whereYear('tanggal_pelaksanaan', date('Y', strtotime($request->tanggal_pelaksanaan)))->count() + 1;
        $nomorBA = sprintf("BA-%03d/LSP-SMKN1/%s/%s", $nomorUrut, date('m', strtotime($request->tanggal_pelaksanaan)), date('Y', strtotime($request->tanggal_pelaksanaan)));

        $ba = BeritaAcara::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                'nomor_berita_acara' => $nomorBA,
                'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
                'jumlah_peserta' => $totalPeserta,
                'jumlah_kompeten' => $totalKompeten,
                'jumlah_belum_kompeten' => $totalBelumKompeten,
                'catatan_pelaksanaan' => $request->catatan_pelaksanaan ?? "Pelaksanaan asesmen kompetensi skema {$jadwal->skema->nama_skema} di {$jadwal->nama_tuk} berjalan dengan lancar dan tertib sesuai SOP BNSP.",
            ]
        );

        LogAktivitas::catat('Generate Berita Acara', 'Membuat Berita Acara Asesmen: ' . $ba->nomor_berita_acara);

        return redirect()->route('admin.dokumen.index', ['tab' => 'berita-acara'])->with('sukses', 'Berita Acara Pelaksanaan & Pleno berhasil dibuat dengan nomor: ' . $ba->nomor_berita_acara);
    }

    /**
     * Cetak Berita Acara & Rekap Pleno Asesmen (FR.AK.05 & FR.AK.06)
     */
    public function cetakBeritaAcara($id)
    {
        $ba = BeritaAcara::with(['jadwal.skema.unitKompetensi', 'jadwal.asesor', 'jadwal.pendaftaranAsesi.asesi.profilAsesi', 'jadwal.pendaftaranAsesi.rekomendasi'])->findOrFail($id);
        $pengaturan = PengaturanSistem::ambilData();
        $admin = auth()->user();

        return view('admin.dokumen.cetak-berita-acara', compact('ba', 'pengaturan', 'admin'));
    }

    /**
     * Hapus Berita Acara
     */
    public function hapusBeritaAcara($id)
    {
        $ba = BeritaAcara::findOrFail($id);
        $nomor = $ba->nomor_berita_acara;
        $ba->delete();

        LogAktivitas::catat('Hapus Berita Acara', 'Menghapus berita acara: ' . $nomor);

        return back()->with('sukses', 'Berita acara berhasil dihapus.');
    }

    /**
     * Simpan Dokumen Legalitas & Lisensi LSP
     */
    public function simpanLegalitas(Request $request)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:100',
            'kategori' => 'required|string',
            'tanggal_terbit' => 'nullable|date',
            'masa_berlaku' => 'nullable|string|max:100',
            'status_dokumen' => 'required|string',
            'file_dokumen' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $namaFile = 'legalitas_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $filePath = 'storage/' . $file->storeAs('legalitas', $namaFile, 'public');
        }

        $dok = DokumenLegalitasLsp::create([
            'nama_dokumen' => $request->nama_dokumen,
            'nomor_dokumen' => $request->nomor_dokumen,
            'kategori' => $request->kategori,
            'tanggal_terbit' => $request->tanggal_terbit,
            'masa_berlaku' => $request->masa_berlaku,
            'status_dokumen' => $request->status_dokumen,
            'file_path' => $filePath,
            'keterangan' => $request->keterangan,
        ]);

        LogAktivitas::catat('Tambah Dokumen Legalitas', 'Menambahkan dokumen legalitas: ' . $dok->nama_dokumen);

        return redirect()->route('admin.dokumen.index', ['tab' => 'legalitas'])->with('sukses', 'Dokumen legalitas lembaga berhasil disimpan.');
    }

    /**
     * Hapus Dokumen Legalitas
     */
    public function hapusLegalitas($id)
    {
        $dok = DokumenLegalitasLsp::findOrFail($id);
        $nama = $dok->nama_dokumen;
        $dok->delete();

        LogAktivitas::catat('Hapus Dokumen Legalitas', 'Menghapus dokumen legalitas: ' . $nama);

        return back()->with('sukses', 'Dokumen legalitas berhasil dihapus.');
    }

    /**
     * Cetak Bundel Portofolio & Rekapitulasi Berkas Asesmen Lengkap
     */
    public function cetakBundelAsesmen($pendaftaranId)
    {
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'jadwal.asesor',
            'dokumen',
            'rekomendasi.asesor'
        ])->findOrFail($pendaftaranId);

        $iaRecords = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->get()->keyBy('kode_formulir');
        $pengaturan = PengaturanSistem::ambilData();

        return view('admin.dokumen.cetak-bundel', compact('pendaftaran', 'iaRecords', 'pengaturan'));
    }
}
