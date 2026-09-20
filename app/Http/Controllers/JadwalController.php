<?php

namespace App\Http\Controllers;

use App\Models\JadwalAsesmen;
use App\Models\SkemaSertifikasi;
use App\Models\Pengguna;
use App\Models\LogAktivitas;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        JadwalAsesmen::syncAllStatuses();
        $jadwalList = JadwalAsesmen::with(['skema', 'asesor'])->latest('tanggal_uji')->paginate(10);
        $skemaOptions = SkemaSertifikasi::where('status_aktif', true)->orderBy('nama_skema', 'asc')->get();
        $asesorOptions = Pengguna::with('skema')->where('peran', 'asesor')->where('aktif', true)->orderBy('nama_lengkap', 'asc')->get();

        return view('admin.manajemen-jadwal', compact('jadwalList', 'skemaOptions', 'asesorOptions'));
    }

    public function simpanJadwal(Request $request)
    {
        $request->validate([
            'kode_jadwal' => 'required|string|unique:jadwal_asesmen,kode_jadwal',
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'asesor_id' => 'required|exists:pengguna,id',
            'nama_tuk' => 'required|string|max:255',
            'tanggal_uji' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'kuota' => 'required|integer|min:1|max:50',
        ], [
            'tanggal_uji.after_or_equal' => 'Tanggal uji tidak boleh di masa lampau. Pilih hari ini atau tanggal mendatang.',
            'kuota.required' => 'Kapasitas kuota asesi wajib diisi (Standar: 10 asesi/asesor/hari).',
            'kuota.max' => 'Kapasitas maksimal satu sesi per asesor disarankan tidak melebihi 50 asesi.',
        ]);

        // Validasi kesesuaian role skema asesor dengan skema yang dipilih
        $asesor = Pengguna::where('peran', 'asesor')
            ->where('id', $request->asesor_id)
            ->where('skema_id', $request->skema_id)
            ->first();

        if (!$asesor) {
            return back()->withInput()->with('error', 'Gagal membuat jadwal: Asesor yang dipilih tidak memiliki kewenangan/role untuk skema sertifikasi yang dipilih.');
        }

        $kuota = $request->input('kuota', 10);

        $jadwal = JadwalAsesmen::create([
            'kode_jadwal' => $request->kode_jadwal,
            'skema_id' => $request->skema_id,
            'asesor_id' => $request->asesor_id,
            'nama_tuk' => $request->nama_tuk,
            'tanggal_uji' => $request->tanggal_uji,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'kuota' => $kuota,
            'status_jadwal' => 'terjadwal',
        ]);

        LogAktivitas::catat('Tambah Jadwal Asesmen', 'Membuat jadwal uji #' . $jadwal->kode_jadwal);

        // Notifikasi ke Asesor yang ditugaskan
        if ($jadwal->asesor_id) {
            $asesorUser = Pengguna::find($jadwal->asesor_id);
            if ($asesorUser) {
                $skema = SkemaSertifikasi::find($jadwal->skema_id);
                $skemaNama = $skema ? $skema->nama_skema : 'Skema Sertifikasi';
                $tglFormat = \Carbon\Carbon::parse($jadwal->tanggal_uji)->translatedFormat('d F Y');
                $asesorUser->notify(new SystemAlert(
                    'Penugasan Jadwal Asesmen Baru',
                    "Anda telah ditugaskan sebagai Asesor Penguji pada jadwal baru: {$jadwal->kode_jadwal} - {$skemaNama} di {$jadwal->nama_tuk} ({$tglFormat}).",
                    route('asesor.jadwal'),
                    'jadwal',
                    [
                        'jadwal_id' => $jadwal->id,
                        'kode_jadwal' => $jadwal->kode_jadwal,
                    ]
                ));
            }
        }

        return back()->with('sukses', 'Jadwal uji kompetensi berhasil dibuat.');
    }

    public function ubahJadwal(Request $request, $id)
    {
        $jadwal = JadwalAsesmen::findOrFail($id);

        $request->validate([
            'kode_jadwal' => 'required|string|unique:jadwal_asesmen,kode_jadwal,' . $jadwal->id,
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'asesor_id' => 'required|exists:pengguna,id',
            'nama_tuk' => 'required|string|max:255',
            'tanggal_uji' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'kuota' => 'required|integer|min:1',
            'status_jadwal' => 'required|in:terjadwal,berlangsung,selesai,dibatalkan',
        ]);

        // Validasi kesesuaian role skema asesor dengan skema yang dipilih
        $asesor = Pengguna::where('peran', 'asesor')
            ->where('id', $request->asesor_id)
            ->where('skema_id', $request->skema_id)
            ->first();

        if (!$asesor) {
            return back()->withInput()->with('error', 'Gagal memperbarui jadwal: Asesor yang dipilih tidak memiliki kewenangan/role untuk skema sertifikasi yang dipilih.');
        }

        $jadwal->update([
            'kode_jadwal' => $request->kode_jadwal,
            'skema_id' => $request->skema_id,
            'asesor_id' => $request->asesor_id,
            'nama_tuk' => $request->nama_tuk,
            'tanggal_uji' => $request->tanggal_uji,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'kuota' => $request->kuota,
            'status_jadwal' => $request->status_jadwal,
        ]);

        LogAktivitas::catat('Ubah Jadwal Asesmen', 'Memperbarui jadwal uji #' . $jadwal->kode_jadwal);

        return back()->with('sukses', 'Jadwal uji kompetensi berhasil diperbarui.');
    }

    public function hapusJadwal($id)
    {
        $jadwal = JadwalAsesmen::findOrFail($id);
        $kode = $jadwal->kode_jadwal;
        $jadwal->delete();

        LogAktivitas::catat('Hapus Jadwal Asesmen', 'Menghapus jadwal uji #' . $kode);

        return back()->with('sukses', 'Jadwal uji kompetensi berhasil dihapus.');
    }
}
