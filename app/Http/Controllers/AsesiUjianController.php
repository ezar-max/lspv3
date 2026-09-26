<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranAsesi;
use App\Models\IaPenilaian;
use App\Models\SchemeMasterInstrument;
use App\Models\MasterQuestionBank;
use App\Models\DokumenAsesi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AsesiUjianController extends Controller
{
    /** A question tagged to a KUK may only be used when that KUK belongs to the selected scheme. */
    private static function questionMilikSkema(MasterQuestionBank $question, int $skemaId): bool
    {
        $kuk = $question->kriteriaUnjukKerja;
        return !$kuk || (int) $kuk->elemenKompetensi?->unitKompetensi?->skema_id === $skemaId;
    }

    /**
     * Memeriksa status aksesibilitas sesi ujian hari H
     */
    private function cekAksesSesiUjian($pendaftaran)
    {
        if (!$pendaftaran) {
            return [
                'bisa_akses' => false,
                'is_readonly' => true,
                'status' => 'tidak_ditemukan',
                'pesan' => 'Pendaftaran sertifikasi tidak ditemukan.'
            ];
        }

        $timeStatus = $pendaftaran->assessment_time_status;

        return [
            'bisa_akses' => $timeStatus['can_access'],
            'is_readonly' => $timeStatus['is_readonly'],
            'status' => $timeStatus['status'],
            'pesan' => $timeStatus['pesan'],
            'formatted_mulai' => $timeStatus['formatted_mulai'] ?? '-',
            'formatted_selesai' => $timeStatus['formatted_selesai'] ?? '-',
            'formatted_tanggal' => $timeStatus['formatted_tanggal'] ?? '-',
        ];
    }

    /**
     * Mengambil atau men-generate bank soal CBT (FR.IA.05) untuk skema terkait
     */
    public static function getDaftarSoalCbt($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_05'))
            ->where('is_active', true)
            ->first();

        if ($inst && $inst->questionBanks->count() > 0) {
            $list = [];
            foreach ($inst->questionBanks as $idx => $q) {
                if (!self::questionMilikSkema($q, (int) $skema->id)) {
                    continue;
                }
                $num = $q->order ?? ($idx + 1);
                $list[$num] = [
                    'no' => $num,
                    'id' => $q->id,
                    'pertanyaan' => $q->question_text,
                    'opsi' => $q->options ?? [],
                    'kunci' => $q->correct_answer,
                    'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kriteria Unjuk Kerja BNSP',
                    'gambar' => $q->image_path,
                ];
            }
            return $list;
        }

        return [];
    }

    /**
     * Mengambil atau men-generate bank soal Esai (FR.IA.06)
     */
    public static function getDaftarSoalEsai($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_06'))
            ->where('is_active', true)
            ->first();

        if ($inst && $inst->questionBanks->count() > 0) {
            $list = [];
            foreach ($inst->questionBanks as $idx => $q) {
                if (!self::questionMilikSkema($q, (int) $skema->id)) {
                    continue;
                }
                $num = $q->order ?? ($idx + 1);
                $list[$num] = [
                    'no' => $num,
                    'id' => $q->id,
                    'pertanyaan' => $q->question_text,
                    'kunci_referensi' => $q->correct_answer,
                    'kunci' => $q->correct_answer,
                    'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kriteria Unjuk Kerja BNSP',
                ];
            }
            return $list;
        }

        return [];
    }

    /**
     * Mengambil panduan & skenario tugas praktik demonstrasi (FR.IA.02) dari database
     */
    public static function getPanduanPraktikIa02($skema, $pendaftaran = null)
    {
        if (!$skema && !$pendaftaran) return [];
        $skemaId = $skema?->id ?? ($pendaftaran?->skema_id ?? null);

        // 1. Cek dari formulir penilaian spesifik pendaftaran jika ada
        if ($pendaftaran && $pendaftaran->id) {
            $ia02 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->whereIn('kode_formulir', ['FR.IA.02', 'ia02', 'ia_02'])
                ->first();

            if ($ia02 && !empty($ia02->data_jawaban)) {
                $dj = $ia02->data_jawaban;
                if (!empty($dj['skenario']) || !empty($dj['judul_tugas']) || !empty($dj['instruksi_kerja'])) {
                    return [
                        'judul_tugas' => $dj['judul_tugas'] ?? 'Tugas Praktik Demonstrasi',
                        'waktu_menit' => (int) preg_replace('/[^0-9]/', '', (string)($dj['durasi_waktu'] ?? '120')) ?: 120,
                        'skema_nama' => $skema?->nama_skema ?? $pendaftaran->skema?->nama_skema ?? 'Skema Sertifikasi',
                        'skema_kode' => $skema?->kode_skema ?? $pendaftaran->skema?->kode_skema ?? '',
                        'skenario' => $dj['skenario'] ?? null,
                        'instruksi_kerja' => is_array($dj['instruksi_kerja'] ?? null) ? $dj['instruksi_kerja'] : array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($dj['instruksi_kerja'] ?? '')))),
                        'peralatan_bahan' => is_array($dj['peralatan_bahan'] ?? null) ? $dj['peralatan_bahan'] : array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($dj['peralatan_bahan'] ?? '')))),
                        'standar_hasil' => $dj['standar_hasil'] ?? [],
                    ];
                }
            }
        }

        // 2. Cek dari master instrumen skema di database
        if ($skemaId) {
            $inst = SchemeMasterInstrument::where('skema_id', $skemaId)
                ->whereIn('instrument_code', SchemeMasterInstrument::getCodeAliases('ia_02'))
                ->where('is_active', true)
                ->first();

            if ($inst && !empty($inst->additional_metadata)) {
                $meta = is_array($inst->additional_metadata) ? $inst->additional_metadata : json_decode($inst->additional_metadata, true);
                if (!empty($meta['skenario']) || !empty($meta['judul_tugas']) || !empty($meta['instruksi_kerja'])) {
                    return [
                        'judul_tugas' => $meta['judul_tugas'] ?? $inst->title ?? 'Tugas Praktik Demonstrasi',
                        'waktu_menit' => $inst->time_limit_minutes ?? 120,
                        'skema_nama' => $skema?->nama_skema ?? 'Skema Sertifikasi',
                        'skema_kode' => $skema?->kode_skema ?? '',
                        'skenario' => $meta['skenario'] ?? null,
                        'instruksi_kerja' => $meta['instruksi_kerja'] ?? ($inst->instructions ? array_filter(array_map('trim', explode("\n", $inst->instructions))) : []),
                        'peralatan_bahan' => $meta['peralatan_bahan'] ?? [],
                        'standar_hasil' => $meta['standar_hasil'] ?? [],
                    ];
                }
            }
        }

        // 3. Jika belum dikonfigurasi di database, kembalikan data kosong
        return [
            'judul_tugas' => null,
            'waktu_menit' => null,
            'skema_nama' => $skema?->nama_skema ?? '',
            'skema_kode' => $skema?->kode_skema ?? '',
            'skenario' => null,
            'instruksi_kerja' => [],
            'peralatan_bahan' => [],
            'standar_hasil' => [],
        ];
    }

    /**
     * Mengambil daftar pertanyaan lisan / wawancara (FR.IA.03 / FR.IA.07) dari database
     */
    public static function getDaftarPertanyaanLisan($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', array_merge(
                SchemeMasterInstrument::getCodeAliases('ia_03'),
                SchemeMasterInstrument::getCodeAliases('ia_07')
            ))
            ->where('is_active', true)
            ->first();

        if ($inst && $inst->questionBanks->count() > 0) {
            $list = [];
            foreach ($inst->questionBanks as $idx => $q) {
                if (!self::questionMilikSkema($q, (int) $skema->id)) {
                    continue;
                }
                $num = $q->order ?? ($idx + 1);
                $list[$num] = [
                    'no' => $num,
                    'id' => $q->id,
                    'tanya' => $q->question_text,
                    'kunci' => $q->correct_answer ?: $q->rubric_guide,
                    'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kriteria Unjuk Kerja BNSP',
                ];
            }
            return $list;
        }

        return [];
    }

    public function index(Request $request)
    {
        $params = ['step' => 5];
        if ($request->has('pendaftaran_id')) {
            $params['pendaftaran_id'] = $request->get('pendaftaran_id');
        }
        if ($request->has('tab')) {
            $params['tab'] = $request->get('tab');
        }
        if ($request->has('submitted')) {
            $params['submitted'] = $request->get('submitted');
        }

        return redirect()->route('asesi.tahapan', $params);
    }

    /**
     * AJAX Real-time Auto-Save Jawaban CBT & Esai
     */
    public function autosave(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->peran !== 'asesi') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $pendaftaranId = $request->input('pendaftaran_id');
        $pendaftaran = PendaftaranAsesi::where('id', $pendaftaranId)->where('asesi_id', $user->id)->first();

        if (!$pendaftaran) {
            return response()->json(['status' => 'error', 'message' => 'Pendaftaran tidak valid'], 404);
        }

        if ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima') {
            return response()->json(['status' => 'error', 'message' => 'Pendaftaran sertifikasi ini telah Ditolak. Sesi ujian tidak dapat diikuti.'], 403);
        }

        if (!$pendaftaran->isMapaConfirmed()) {
            return response()->json(['status' => 'error', 'message' => 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor.'], 403);
        }

        $isAk01Selesai = ($pendaftaran->status_ak01 === 'selesai') || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && !empty($pendaftaran->tanda_tangan_asesor_ak01));
        if (!$isAk01Selesai && $pendaftaran->status_pendaftaran !== 'selesai') {
            return response()->json(['status' => 'error', 'message' => 'Formulir FR.AK.01 belum disahkan oleh kedua belah pihak.'], 403);
        }

        if ($pendaftaran->jadwal) {
            $pendaftaran->jadwal->syncRealtimeStatus();
            if ($pendaftaran->jadwal->status_jadwal === 'dibatalkan') {
                return response()->json(['status' => 'error', 'message' => 'Jadwal asesmen telah dibatalkan.'], 403);
            }
            if ($pendaftaran->jadwal->isBelumMulai()) {
                return response()->json(['status' => 'error', 'message' => 'Sesi asesmen belum dimulai sesuai jadwal. Jawaban belum dapat disimpan.'], 403);
            }
            if ($pendaftaran->jadwal->isSudahSelesai(true, 10)) {
                return response()->json(['status' => 'error', 'message' => 'Waktu pengerjaan asesmen telah berakhir.'], 403);
            }
        }

        // GUARD KETAT: Jika ujian telah dikumpulkan / disubmit, jawaban tidak dapat diubah lagi
        $isSubmitted = ($pendaftaran->status_pendaftaran === 'selesai')
            || !empty($pendaftaran->rekomendasi)
            || IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->whereIn('kode_formulir', ['FR.IA.05', 'FR.IA.06', 'FR.IA.02'])
                ->where('status', 'submitted')
                ->exists();

        if ($isSubmitted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ujian telah dikumpulkan dan tidak dapat diisi atau diubah lagi.'
            ], 403);
        }

        $tipe = $request->input('tipe');

        if ($tipe === 'cbt') {
            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.05',
            ]);

            $payload = $record->data_jawaban ?? [
                'jawaban_pg' => [],
                'total_soal' => count(self::getDaftarSoalCbt($pendaftaran->skema)),
            ];

            if ($request->has('jawaban_pg') && is_array($request->input('jawaban_pg'))) {
                foreach ($request->input('jawaban_pg') as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $payload['jawaban_pg'][$k] = $v;
                    }
                }
            } else {
                $no = $request->input('no');
                $jawaban = $request->input('jawaban');
                if ($no !== null) {
                    $payload['jawaban_pg'][$no] = $jawaban;
                }
            }

            $payload['last_updated_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = $record->status ?: 'draft';
            $record->save();

            return response()->json([
                'status' => 'success',
                'tipe' => 'cbt',
                'no' => $request->input('no'),
                'jawaban' => $request->input('jawaban'),
                'total_terjawab' => count($payload['jawaban_pg'] ?? []),
                'saved_at' => now()->format('H:i:s')
            ]);
        }

        if ($tipe === 'esai') {
            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.06',
            ]);

            $payload = $record->data_jawaban ?? [
                'jawaban_esai' => [],
            ];

            if ($request->has('jawaban_esai') && is_array($request->input('jawaban_esai'))) {
                foreach ($request->input('jawaban_esai') as $k => $v) {
                    $payload['jawaban_esai'][$k] = $v;
                }
            } else {
                $no = $request->input('no');
                $jawaban = $request->input('jawaban');
                if ($no !== null) {
                    $payload['jawaban_esai'][$no] = $jawaban;
                }
            }

            $payload['last_updated_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = $record->status ?: 'draft';
            $record->save();

            return response()->json([
                'status' => 'success',
                'tipe' => 'esai',
                'no' => $request->input('no'),
                'total_terjawab' => count(array_filter($payload['jawaban_esai'] ?? [], fn($v) => !empty(trim($v ?? '')))),
                'saved_at' => now()->format('H:i:s')
            ]);
        }

        if ($tipe === 'praktik_catatan') {
            $catatan = $request->input('catatan');

            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.02',
            ]);

            $payload = $record->data_jawaban ?? [];
            $payload['catatan_praktik'] = $catatan;
            $payload['last_updated_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = $record->status ?: 'draft';
            $record->save();

            return response()->json([
                'status' => 'success',
                'tipe' => 'praktik',
                'saved_at' => now()->format('H:i:s')
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Tipe autosave tidak dikenali'], 400);
    }

    /**
     * Upload File Hasil / Laporan Praktik Demonstrasi (FR.IA.02)
     */
    public function uploadIa02(Request $request)
    {
        $user = auth()->user();
        $pendaftaranId = $request->input('pendaftaran_id');
        $pendaftaran = PendaftaranAsesi::where('id', $pendaftaranId)->where('asesi_id', $user->id)->firstOrFail();

        if ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima') {
            return redirect()->back()->with('error', 'Pendaftaran sertifikasi ini telah Ditolak. Berkas ujian tidak dapat diunggah.');
        }

        if (!$pendaftaran->isMapaConfirmed()) {
            return redirect()->back()->with('error', 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor. Ruang ujian belum tersedia.');
        }

        $isAk01Selesai = ($pendaftaran->status_ak01 === 'selesai') || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && !empty($pendaftaran->tanda_tangan_asesor_ak01));
        if (!$isAk01Selesai && $pendaftaran->status_pendaftaran !== 'selesai') {
            return redirect()->back()->with('error', 'Formulir FR.AK.01 belum disahkan oleh kedua belah pihak.');
        }

        if ($pendaftaran->jadwal) {
            $pendaftaran->jadwal->syncRealtimeStatus();
            if ($pendaftaran->jadwal->status_jadwal === 'dibatalkan') {
                return redirect()->back()->with('error', 'Jadwal asesmen telah dibatalkan.');
            }
            if ($pendaftaran->jadwal->isBelumMulai()) {
                return redirect()->back()->with('error', 'Sesi ujian belum dibuka.');
            }
            if ($pendaftaran->jadwal->isSudahSelesai(true, 10)) {
                return redirect()->back()->with('error', 'Waktu pelaksanaan asesmen telah berakhir.');
            }
        }

        // Guard: Jika ujian sudah dikumpulkan, berkas praktik tidak dapat diubah
        $isSubmitted = ($pendaftaran->status_pendaftaran === 'selesai')
            || !empty($pendaftaran->rekomendasi)
            || IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->whereIn('kode_formulir', ['FR.IA.05', 'FR.IA.06', 'FR.IA.02'])
                ->where('status', 'submitted')
                ->exists();

        if ($isSubmitted) {
            return redirect()->back()->with('error', 'Ujian telah dikumpulkan dan berkas praktik tidak dapat diubah lagi.');
        }

        $request->validate([
            'file_praktik' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,zip,rar,doc,docx|max:10240',
            'catatan_praktik' => 'nullable|string',
        ]);

        if ($request->hasFile('file_praktik')) {
            $file = $request->file('file_praktik');
            $namaFile = 'Laporan_Praktik_IA02_' . $pendaftaran->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('dokumen-asesi', $namaFile, 'public');
            $filePath = '/storage/' . $path;

            DokumenAsesi::updateOrCreate(
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jenis_dokumen' => 'Hasil Proyek / Laporan Praktik FR.IA.02',
                ],
                [
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'ukuran_file' => $file->getSize(),
                    'tipe_file' => $file->getClientMimeType(),
                ]
            );

            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.02',
            ]);

            $payload = $record->data_jawaban ?? [];
            $payload['file_laporan'] = $filePath;
            $payload['nama_dokumen'] = $file->getClientOriginalName();
            $payload['catatan_praktik'] = $request->input('catatan_praktik', $payload['catatan_praktik'] ?? '');
            $payload['uploaded_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = 'submitted';
            $record->save();
        }

        return redirect()->back()->with('sukses', 'Berkas laporan / dokumentasi praktik FR.IA.02 berhasil diunggah!');
    }

    /**
     * Submit & Kumpulkan Ujian Final oleh Asesi
     */
    public function submitUjian(Request $request)
    {
        $user = auth()->user();
        $pendaftaranId = $request->input('pendaftaran_id');
        $pendaftaran = PendaftaranAsesi::with('skema')->where('id', $pendaftaranId)->where('asesi_id', $user->id)->firstOrFail();

        if ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima') {
            return redirect()->back()->with('error', 'Pendaftaran sertifikasi ini telah Ditolak.');
        }

        if (!$pendaftaran->isMapaConfirmed()) {
            return redirect()->back()->with('error', 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor. Ruang ujian belum tersedia.');
        }

        $isAk01Selesai = ($pendaftaran->status_ak01 === 'selesai') || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && !empty($pendaftaran->tanda_tangan_asesor_ak01));
        if (!$isAk01Selesai && $pendaftaran->status_pendaftaran !== 'selesai') {
            return redirect()->back()->with('error', 'Formulir FR.AK.01 belum disahkan oleh kedua belah pihak.');
        }

        if ($pendaftaran->jadwal) {
            $pendaftaran->jadwal->syncRealtimeStatus();
            if ($pendaftaran->jadwal->status_jadwal === 'dibatalkan') {
                return redirect()->back()->with('error', 'Jadwal asesmen telah dibatalkan.');
            }
            if ($pendaftaran->jadwal->isBelumMulai()) {
                return redirect()->back()->with('error', 'Sesi ujian belum dibuka.');
            }
            if ($pendaftaran->jadwal->isSudahSelesai(true, 10)) {
                return redirect()->back()->with('error', 'Waktu pelaksanaan asesmen telah berakhir.');
            }
        }

        // Guard: Jika ujian telah dikumpulkan sebelumnya, tidak dapat dikumpulkan ulang
        $isSubmitted = ($pendaftaran->status_pendaftaran === 'selesai')
            || !empty($pendaftaran->rekomendasi)
            || IaPenilaian::where('pendaftaran_id', $pendaftaran->id)
                ->whereIn('kode_formulir', ['FR.IA.05', 'FR.IA.06', 'FR.IA.02'])
                ->where('status', 'submitted')
                ->exists();

        if ($isSubmitted) {
            return redirect()->route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $pendaftaran->id])
                ->with('info', 'Ujian Anda telah dikumpulkan sebelumnya dan lembar jawaban telah dikunci.');
        }

        // 1. Evaluasi & Submit CBT (FR.IA.05)
        $soalCbt = self::getDaftarSoalCbt($pendaftaran->skema);
        $recordIa05 = IaPenilaian::firstOrNew([
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.05',
        ]);

        $payloadCbt = $recordIa05->data_jawaban ?? ['jawaban_pg' => []];
        $jawabanPg = $payloadCbt['jawaban_pg'] ?? [];

        // Validasi: seluruh butir soal CBT wajib dijawab sebelum dikirim
        $total = count($soalCbt);
        if ($total > 0) {
            $unansweredList = [];
            foreach ($soalCbt as $no => $item) {
                if (!isset($jawabanPg[$no]) || trim((string)$jawabanPg[$no]) === '') {
                    $unansweredList[] = $no;
                }
            }
            if (count($unansweredList) > 0) {
                $countUnanswered = count($unansweredList);
                $contohNomor = implode(', ', array_slice($unansweredList, 0, 5)) . (count($unansweredList) > 5 ? ' dll' : '');
                return redirect()->back()->with('error', "Ujian belum dapat dikumpulkan: Masih terdapat {$countUnanswered} butir soal pilihan ganda yang belum Anda jawab (Nomor: {$contohNomor}). Seluruh soal wajib dijawab sebelum mengumpulkan ujian.");
            }
        }

        $benar = 0;
        $total = count($soalCbt);
        foreach ($soalCbt as $no => $item) {
            $jawabAsesi = $jawabanPg[$no] ?? null;
            if ($jawabAsesi && strtoupper($jawabAsesi) === strtoupper($item['kunci'] ?? 'A')) {
                $benar++;
            }
        }

        $skor = ($total > 0) ? round(($benar / $total) * 100, 1) : 0;
        $payloadCbt['skor_cbt'] = $skor;
        $payloadCbt['jumlah_benar'] = $benar;
        $payloadCbt['total_soal'] = $total;
        $payloadCbt['submitted_at'] = now()->toDateTimeString();
        $payloadCbt['ttd_asesi'] = $user->tanda_tangan ?? $pendaftaran->tanda_tangan_asesi;

        $recordIa05->user_id = $user->id;
        $recordIa05->role = 'asesi';
        $recordIa05->data_jawaban = $payloadCbt;
        $recordIa05->status = 'submitted';
        $recordIa05->rekomendasi = ($skor >= 75) ? 'K' : 'BK';
        $recordIa05->save();

        // 2. Submit Esai (FR.IA.06)
        $recordIa06 = IaPenilaian::firstOrNew([
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.06',
        ]);

        $payloadEsai = $recordIa06->data_jawaban ?? ['jawaban_esai' => []];
        $payloadEsai['submitted_at'] = now()->toDateTimeString();
        $payloadEsai['ttd_asesi'] = $user->tanda_tangan ?? $pendaftaran->tanda_tangan_asesi;

        $recordIa06->user_id = $user->id;
        $recordIa06->role = 'asesi';
        $recordIa06->data_jawaban = $payloadEsai;
        $recordIa06->status = 'submitted';
        $recordIa06->save();

        // 3. Submit Praktik (FR.IA.02)
        $recordIa02 = IaPenilaian::firstOrNew([
            'pendaftaran_id' => $pendaftaran->id,
            'kode_formulir' => 'FR.IA.02',
        ]);
        $payloadPraktik = $recordIa02->data_jawaban ?? [];
        $payloadPraktik['submitted_at'] = now()->toDateTimeString();
        $recordIa02->user_id = $user->id;
        $recordIa02->role = 'asesi';
        $recordIa02->data_jawaban = $payloadPraktik;
        $recordIa02->status = 'submitted';
        $recordIa02->save();

        LogAktivitas::catat('Ujian Online Selesai', "Asesi {$user->nama_lengkap} telah mengumpulkan seluruh lembar ujian FR.IA pada pendaftaran #{$pendaftaran->nomor_pendaftaran}");

        return redirect()->route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $pendaftaran->id, 'submitted' => 1])
            ->with('sukses', 'Ujian asesmen berhasil dikumpulkan! Seluruh jawaban Anda telah tersimpan dan siap dievaluasi oleh Asesor Penguji.');
    }

    /**
     * Endpoint API Heartbeat untuk Asesi: Memeriksa status sesi ujian aktif
     */
    public function statusSesiLive(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->peran !== 'asesi') {
            return response()->json(['has_active_exam' => false], 403);
        }

        $requestedPendaftaranId = $request->input('pendaftaran_id');

        // Jika ada spesifik pendaftaran_id yang diminta (misal dari ruang-uji)
        if ($requestedPendaftaranId) {
            $p = PendaftaranAsesi::with(['skema', 'asesor', 'jadwal'])
                ->where('id', $requestedPendaftaranId)
                ->where('asesi_id', $user->id)
                ->first();

            if (!$p) {
                return response()->json(['has_active_exam' => false]);
            }

            $jadwal = $p->jadwal;
            $statusJadwal = $jadwal ? $jadwal->syncRealtimeStatus() : 'terjadwal';
            $isSubmitted = IaPenilaian::where('pendaftaran_id', $p->id)
                ->whereIn('kode_formulir', ['FR.IA.05', 'FR.IA.06', 'FR.IA.02'])
                ->where('status', 'submitted')
                ->exists() || ($p->status_pendaftaran === 'selesai') || !empty($p->rekomendasi);

            $isRuangUjiOpen = $p->isRuangUjiOpen();
            $sisaDetik = $jadwal ? $jadwal->sisa_detik_ujian : 0;

            return response()->json([
                'has_active_exam' => ($statusJadwal === 'berlangsung' || $isRuangUjiOpen) && !$isSubmitted,
                'status_jadwal' => $statusJadwal,
                'can_access' => $isRuangUjiOpen,
                'is_submitted' => $isSubmitted,
                'is_readonly' => ($p->assessment_time_status['is_readonly'] ?? false) || $isSubmitted,
                'sisa_detik' => $sisaDetik,
                'skema_nama' => $p->skema->nama_skema ?? 'Skema Sertifikasi',
                'asesor_nama' => $p->asesor->nama_lengkap ?? ($jadwal->asesor->nama_lengkap ?? 'Asesor Penguji'),
                'nama_tuk' => $jadwal->nama_tuk ?? 'TUK LSP',
                'ruang_uji_url' => route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $p->id]),
            ]);
        }

        // Global check: cari pendaftaran aktif asesi yang jadwalnya sedang berlangsung
        $listPendaftaran = PendaftaranAsesi::with(['skema', 'asesor', 'jadwal'])
            ->where('asesi_id', $user->id)
            ->whereNotIn('status_pendaftaran', ['selesai', 'ditolak'])
            ->whereDoesntHave('rekomendasi')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($listPendaftaran as $p) {
            $isSubmitted = IaPenilaian::where('pendaftaran_id', $p->id)
                ->whereIn('kode_formulir', ['FR.IA.05', 'FR.IA.06', 'FR.IA.02'])
                ->where('status', 'submitted')
                ->exists() || ($p->status_pendaftaran === 'selesai') || !empty($p->rekomendasi);

            if ($isSubmitted) {
                continue;
            }

            $jadwal = $p->jadwal;
            if (!$jadwal) {
                continue;
            }

            $statusJadwal = $jadwal->syncRealtimeStatus();
            $isAktif = ($statusJadwal === 'berlangsung') || $jadwal->isWaktuAktif();

            if ($isAktif && $p->isRuangUjiOpen()) {
                return response()->json([
                    'has_active_exam' => true,
                    'pendaftaran_id' => $p->id,
                    'status_jadwal' => $statusJadwal,
                    'skema_nama' => $p->skema->nama_skema ?? 'Skema Sertifikasi',
                    'skema_kode' => $p->skema->kode_skema ?? 'SKEMA',
                    'asesor_nama' => $p->asesor->nama_lengkap ?? ($jadwal->asesor->nama_lengkap ?? 'Asesor Penguji'),
                    'nama_tuk' => $jadwal->nama_tuk ?? 'TUK LSP',
                    'ruang_uji_url' => route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $p->id]),
                    'sisa_detik' => $jadwal->sisa_detik_ujian,
                ]);
            }
        }

        return response()->json([
            'has_active_exam' => false,
        ]);
    }
}
