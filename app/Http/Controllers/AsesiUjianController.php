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

        // 1. Cek apakah ada master instrumen di database
        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', ['ia05', 'FR.IA.05', 'fr.ia.05', 'IA.05', 'ia.05'])
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

        // 2. Fallback cerdas berdasarkan Unit Kompetensi & Elemen SKKNI skema aktif
        $fallbackList = [];
        $no = 1;
        
        $units = $skema->unitKompetensi()->with('elemenKompetensi.kriteriaUnjukKerja')->get();

        foreach ($units as $unit) {
            foreach ($unit->elemenKompetensi as $elemen) {
                $kukList = $elemen->kriteriaUnjukKerja;
                $kukUtama = $kukList->first();

                // Buat butir soal objektif per elemen
                $fallbackList[$no] = [
                    'no' => $no,
                    'id' => 'gen_' . $no,
                    'unit_kode' => $unit->kode_unit,
                    'unit_judul' => $unit->judul_unit,
                    'elemen_nama' => $elemen->nama_elemen,
                    'kuk' => $kukUtama ? "KUK {$kukUtama->nomor_kuk} - {$kukUtama->pernyataan_kuk}" : "Elemen: {$elemen->nama_elemen}",
                    'pertanyaan' => "Dalam pelaksanaan unit '{$unit->judul_unit}', pada saat melakukan tahap {$elemen->nama_elemen}, tindakan yang paling tepat sesuai Standar Operasional Prosedur (SOP) dan K3 adalah...",
                    'opsi' => [
                        'A' => "Melakukan pemeriksaan parameter awal, memakai APD lengkap, dan memastikan lingkungan kerja aman sebelum memulai.",
                        'B' => "Langsung mengoperasikan peralatan tanpa memeriksa buku manual atau petunjuk kerja.",
                        'C' => "Mengabaikan penggunaan alat pelindung diri (APD) jika pekerjaan hanya berlangsung singkat.",
                        'D' => "Menyerahkan seluruh persiapan teknis kepada asisten tanpa pengecekan ulang kalibrasi alat.",
                        'E' => "Mengubah spesifikasi teknis benda kerja tanpa persetujuan penanggung jawab TUK."
                    ],
                    'kunci' => 'A',
                    'gambar' => null,
                ];
                $no++;

                if ($no > 25) break 2;
            }
        }

        if (empty($fallbackList)) {
            for ($i = 1; $i <= 10; $i++) {
                $fallbackList[$i] = [
                    'no' => $i,
                    'id' => 'gen_' . $i,
                    'kuk' => 'Standar Kompetensi Kejuruan Terpadu BNSP',
                    'pertanyaan' => "Pertanyaan Standar Uji Teori Kejuruan No. {$i}: Dalam menerapkan prosedur keselamatan dan kesehatan kerja (K3) serta kualitas hasil kerja pada skema {$skema->nama_skema}, langkah utama yang wajib dilakukan adalah...",
                    'opsi' => [
                        'A' => "Menerapkan SOP kerja yang terstandarisasi, melakukan inspeksi alat, dan mencatat log hasil kerja secara berkala.",
                        'B' => "Bekerja secara mandiri tanpa mematuhi pedoman gambar kerja atau instruksi teknis.",
                        'C' => "Menunda pelaporan kerusakan peralatan hingga seluruh proses pengujian berakhir.",
                        'D' => "Menggunakan peralatan kerja tidak sesuai peruntukan fungsi aslinya.",
                        'E' => "Menonaktifkan sistem proteksi pengaman mesin untuk mempercepat waktu produksi."
                    ],
                    'kunci' => 'A',
                    'gambar' => null,
                ];
            }
        }

        return $fallbackList;
    }

    /**
     * Mengambil atau men-generate bank soal Esai (FR.IA.06)
     */
    public static function getDaftarSoalEsai($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', ['ia06', 'FR.IA.06', 'fr.ia.06', 'IA.06', 'ia.06'])
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
                    'kuk' => $q->kriteriaUnjukKerja ? "KUK {$q->kriteriaUnjukKerja->nomor_kuk} - {$q->kriteriaUnjukKerja->pernyataan_kuk}" : 'Standar Kriteria Unjuk Kerja BNSP',
                ];
            }
            return $list;
        }

        return [
            1 => [
                'no' => 1,
                'kuk' => 'Penerapan K3 dan Persiapan Alat/Bahan',
                'pertanyaan' => "Jelaskan langkah-langkah persiapan kerja dan penerapan K3 (Keselamatan dan Kesehatan Kerja) yang wajib Anda lakukan sebelum mengoperasikan peralatan pada skema sertifikasi '{$skema->nama_skema}'!",
                'kunci_referensi' => "Pemeriksaan APD lengkap, pengecekan kondisi fisik mesin/alat kerja, pembersihan area kerja, dan verifikasi ketersediaan material sesuai lembar kerja."
            ],
            2 => [
                'no' => 2,
                'kuk' => 'Prosedur Teknis & Penanganan Kendala (Troubleshooting)',
                'pertanyaan' => "Apabila saat proses pengerjaan benda kerja/tugas terjadi penyimpangan toleransi ukuran atau ketidaksesuaian hasil kerja, sebutkan dan jelaskan tindakan korektif (troubleshooting) yang harus Anda ambil sesuai SOP!",
                'kunci_referensi' => "Menghentikan proses, mengidentifikasi penyebab deviasi (kalibrasi/tool/setting parameter), berkonsultasi dengan asesor/supervisor, dan melakukan penyesuaian setting secara terukur."
            ],
            3 => [
                'no' => 3,
                'kuk' => 'Pengujian Mutu & Pelaporan Hasil Kerja',
                'pertanyaan' => "Uraikan bagaimana cara Anda melakukan pemeriksaan mutu akhir (quality inspection) terhadap hasil kerja demonstrasi praktik serta bagaimana Anda menyusun laporannya!",
                'kunci_referensi' => "Menggunakan alat ukur presisi terkalibrasi, membandingkan hasil riil dengan lembar spesifikasi gambar kerja, serta mencatat hasil verifikasi pada formulir laporan kerja."
            ]
        ];
    }

    /**
     * Mengambil panduan penugasan praktik (FR.IA.02)
     */
    public static function getPanduanPraktikIa02($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::where('skema_id', $skema->id)
            ->whereIn('instrument_code', ['ia02', 'FR.IA.02', 'fr.ia.02', 'IA.02', 'ia.02'])
            ->where('is_active', true)
            ->first();

        if ($inst && !empty($inst->additional_metadata)) {
            $meta = is_array($inst->additional_metadata) ? $inst->additional_metadata : json_decode($inst->additional_metadata, true);
            return [
                'judul_tugas' => $meta['judul_tugas'] ?? $inst->title ?? 'Tugas Praktik Demonstrasi',
                'waktu_menit' => $inst->time_limit_minutes ?? 120,
                'skema_nama' => $skema->nama_skema,
                'skema_kode' => $skema->kode_skema,
                'skenario' => $meta['skenario'] ?? null,
                'instruksi_kerja' => $meta['instruksi_kerja'] ?? ($inst->instructions ? explode("\n", $inst->instructions) : []),
                'peralatan_bahan' => $meta['peralatan_bahan'] ?? [],
                'standar_hasil' => $meta['standar_hasil'] ?? [],
            ];
        }

        return [
            'judul_tugas' => 'Tugas Praktik Demonstrasi Kerja di Bengkel / Lab TUK',
            'waktu_menit' => 120,
            'skema_nama' => $skema->nama_skema ?? 'Skema Kejuruan BNSP',
            'skema_kode' => $skema->kode_skema ?? 'BNSP-SKEMA',
            'skenario' => 'Laksanakan penugasan praktik kerja sesuai SOP dan gambar kerja standar.',
            'instruksi_kerja' => [
                'Periksa kelengkapan alat pelindung diri (APD) dan kenakan secara benar sebelum memasuki area kerja.',
                'Pelajari gambar kerja / spesifikasi teknis dan SOP demonstrasi yang telah disiapkan oleh Asesor di TUK.',
                'Lakukan pemeriksaan kelaikan peralatan, bahan baku, dan lakukan kalibrasi alat ukur sebelum digunakan.',
                'Laksanakan tugas praktik kerja secara mandiri, aman, dan efisien dengan mematuhi batas toleransi ukuran yang ditentukan.',
                'Lakukan inspeksi mandiri hasil kerja dan buat dokumentasi foto/laporan ringkas hasil demonstrasi.',
                'Bersihkan area kerja (5R/5S) dan serahkan benda kerja/laporan hasil praktik kepada Asesor Penguji.'
            ],
            'peralatan_bahan' => [
                'Alat Pelindung Diri (Kacamata safety/kedok las, masker, sarung tangan, sepatu safety, wearpack/apron).',
                'Mesin / perangkat kerja utama sesuai unit kompetensi kejuruan di TUK.',
                'Alat ukur presisi (Jangka sorong / welding gauge / multimeter / instrumen uji terkait).',
                'Material / bahan uji praktik dan lembar gambar kerja terstandar.'
            ],
            'standar_hasil' => [
                'Benda kerja sesuai ukuran toleransi standar spesifikasi.',
                'Bebas dari cacat kritis yang membahayakan fungsi struktur.',
                'Laporan kerja atau lembar verifikasi terisi lengkap dan tertib 5R.'
            ]
        ];
    }

    /**
     * Mengambil daftar pertanyaan lisan / wawancara (FR.IA.03 / FR.IA.07)
     */
    public static function getDaftarPertanyaanLisan($skema)
    {
        if (!$skema) return [];

        $inst = SchemeMasterInstrument::with(['questionBanks.kriteriaUnjukKerja'])
            ->where('skema_id', $skema->id)
            ->whereIn('instrument_code', ['ia03', 'ia07', 'FR.IA.03', 'FR.IA.07', 'fr.ia.03', 'fr.ia.07', 'IA.03', 'IA.07'])
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

        return [
            1 => [
                'no' => 1,
                'tanya' => 'Sebutkan faktor risiko K3 utama pada unit kerja yang Anda demonstrasikan dan jelaskan tindakan mitigasi yang wajib diambil!',
                'kunci' => 'Asesi harus dapat menyebutkan bahaya listrik/api/mekanik, penggunaan APD wajib, dan prosedur darurat.',
                'kuk' => 'Standar Keselamatan Kerja & Prosedur K3',
            ],
            2 => [
                'no' => 2,
                'tanya' => 'Bagaimana prosedur Anda memastikan peralatan kerja terkalibrasi dan siap pakai sebelum digunakan?',
                'kunci' => 'Melakukan inspeksi visual, verifikasi batas masa berlaku kalibrasi alat ukur, dan uji fungsional awal tanpa beban.',
                'kuk' => 'Pemeriksaan Kelaikan & Kalibrasi Alat',
            ],
            3 => [
                'no' => 3,
                'tanya' => 'Jika terjadi penyimpangan toleransi pada hasil pengerjaan, apa langkah korektif sistematis yang Anda tempuh?',
                'kunci' => 'Mengidentifikasi akar penyebab deviasi, melakukan penyesuaian parameter, dan melapor kepada supervisor/asesor.',
                'kuk' => 'Penanganan Deviasi Mutu & Troubleshooting',
            ]
        ];
    }

    /**
     * Halaman Utama Ruang Ujian Terpadu Asesi (FR.IA)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->peran !== 'asesi') {
            abort(403, 'Akses Ditolak: Halaman ini khusus untuk peserta uji (Asesi).');
        }

        $pendaftaranId = $request->get('pendaftaran_id');
        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'dokumen',
            'iaPenilaian'
        ])
        ->where('asesi_id', $user->id)
        ->when($pendaftaranId, fn($q) => $q->where('id', $pendaftaranId))
        ->latest()
        ->first();

        if (!$pendaftaran) {
            if ($pendaftaranId) {
                abort(PendaftaranAsesi::whereKey($pendaftaranId)->exists() ? 403 : 404,
                    'Pendaftaran yang dipilih tidak dapat diakses.');
            }
            return redirect()->route('asesi.dashboard')
                ->with('error', 'Anda belum memiliki pendaftaran skema sertifikasi yang aktif.');
        }

        $isAk01Selesai = ($pendaftaran->status_ak01 === 'selesai') || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && !empty($pendaftaran->tanda_tangan_asesor_ak01));

        if (!$isAk01Selesai && $pendaftaran->status_pendaftaran !== 'selesai') {
            if ($pendaftaran->status_ak01 === 'disetujui_asesi' || !empty($pendaftaran->tanda_tangan_asesi_ak01)) {
                return redirect()->route('asesi.ak01', ['id' => $pendaftaran->id])
                    ->with('warning', 'Formulir FR.AK.01 telah Anda tandatangani dan sedang menunggu persetujuan/pengesahan dari Asesor Penguji sebelum memasuki Ruang Ujian.');
            }
            return redirect()->route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $pendaftaran->id])
                ->with('error', 'Harap tandatangani Persetujuan Asesmen (FR.AK.01) pada Tahapan Asesmen sebelum memasuki Ruang Ujian.');
        }

        if ($pendaftaran->jadwal) {
            $pendaftaran->jadwal->syncRealtimeStatus();
        }

        $statusSesi = $this->cekAksesSesiUjian($pendaftaran);
        $sisaDetik = $pendaftaran->jadwal ? $pendaftaran->jadwal->sisa_detik_ujian : 5400;
        $detikMenujuMulai = $pendaftaran->jadwal ? $pendaftaran->jadwal->detik_menuju_mulai : 0;

        $instrumenAsesi = $pendaftaran->getInstrumenAsesi();
        $hasCbt = isset($instrumenAsesi['cbt']);
        $hasEsai = isset($instrumenAsesi['esai']);
        $hasPraktik = isset($instrumenAsesi['praktik']);

        $soalCbt = [];
        $recordIa05 = null;
        $savedJawabanPg = [];
        if ($hasCbt) {
            $soalCbt = self::getDaftarSoalCbt($pendaftaran->skema);
            $recordIa05 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05')->first();
            $savedJawabanPg = $recordIa05 ? ($recordIa05->data_jawaban['jawaban_pg'] ?? []) : [];
        }

        $soalEsai = [];
        $recordIa06 = null;
        $savedJawabanEsai = [];
        if ($hasEsai) {
            $soalEsai = self::getDaftarSoalEsai($pendaftaran->skema);
            $recordIa06 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06')->first();
            $savedJawabanEsai = $recordIa06 ? ($recordIa06->data_jawaban['jawaban_esai'] ?? []) : [];
        }

        $panduanPraktik = [];
        $recordIa02 = null;
        $savedPraktik = [];
        $dokumenPraktik = null;
        if ($hasPraktik) {
            $panduanPraktik = self::getPanduanPraktikIa02($pendaftaran->skema);
            $recordIa02 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.02')->first();
            $savedPraktik = $recordIa02 ? ($recordIa02->data_jawaban ?? []) : [];
            $dokumenPraktik = $pendaftaran->dokumen ? $pendaftaran->dokumen->where('jenis_dokumen', 'Hasil Proyek / Laporan Praktik FR.IA.02')->first() : null;
        }

        $isSubmitted = ($recordIa05 && $recordIa05->status === 'submitted') 
            || ($pendaftaran->status_pendaftaran === 'selesai');

        $availableTabs = array_keys($instrumenAsesi);
        $requestedTab = $request->get('tab');
        if ($requestedTab && in_array($requestedTab, $availableTabs)) {
            $defaultTab = $requestedTab;
        } elseif (!empty($availableTabs)) {
            $defaultTab = $availableTabs[0];
        } else {
            $defaultTab = 'praktik';
        }

        return view('asesi.ruang-uji', compact(
            'pendaftaran',
            'statusSesi',
            'sisaDetik',
            'detikMenujuMulai',
            'instrumenAsesi',
            'defaultTab',
            'soalCbt',
            'soalEsai',
            'panduanPraktik',
            'savedJawabanPg',
            'savedJawabanEsai',
            'savedPraktik',
            'isSubmitted',
            'dokumenPraktik'
        ));
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

        if (!$pendaftaran->isMapaConfirmed()) {
            return response()->json(['status' => 'error', 'message' => 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor.'], 403);
        }

        $isAk01Selesai = ($pendaftaran->status_ak01 === 'selesai') || (!empty($pendaftaran->tanda_tangan_asesi_ak01) && !empty($pendaftaran->tanda_tangan_asesor_ak01));
        if (!$isAk01Selesai && $pendaftaran->status_pendaftaran !== 'selesai') {
            return response()->json(['status' => 'error', 'message' => 'Formulir FR.AK.01 belum disahkan oleh kedua belah pihak.'], 403);
        }

        $tipe = $request->input('tipe');

        if ($tipe === 'cbt') {
            $no = $request->input('no');
            $jawaban = $request->input('jawaban');

            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.05',
            ]);

            $payload = $record->data_jawaban ?? [
                'jawaban_pg' => [],
                'total_soal' => count(self::getDaftarSoalCbt($pendaftaran->skema)),
            ];

            $payload['jawaban_pg'][$no] = $jawaban;
            $payload['last_updated_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = $record->status ?: 'draft';
            $record->save();

            return response()->json([
                'status' => 'success',
                'tipe' => 'cbt',
                'no' => $no,
                'jawaban' => $jawaban,
                'total_terjawab' => count($payload['jawaban_pg']),
                'saved_at' => now()->format('H:i:s')
            ]);
        }

        if ($tipe === 'esai') {
            $no = $request->input('no');
            $jawaban = $request->input('jawaban');

            $record = IaPenilaian::firstOrNew([
                'pendaftaran_id' => $pendaftaran->id,
                'kode_formulir' => 'FR.IA.06',
            ]);

            $payload = $record->data_jawaban ?? [
                'jawaban_esai' => [],
            ];

            $payload['jawaban_esai'][$no] = $jawaban;
            $payload['last_updated_at'] = now()->toDateTimeString();

            $record->user_id = $user->id;
            $record->role = 'asesi';
            $record->data_jawaban = $payload;
            $record->status = $record->status ?: 'draft';
            $record->save();

            return response()->json([
                'status' => 'success',
                'tipe' => 'esai',
                'no' => $no,
                'total_terjawab' => count(array_filter($payload['jawaban_esai'], fn($v) => !empty(trim($v)))),
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

        return redirect()->route('asesi.ujian', ['pendaftaran_id' => $pendaftaran->id, 'submitted' => 1])
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
                ->where('kode_formulir', 'FR.IA.05')
                ->where('status', 'submitted')
                ->exists();

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
                'ruang_uji_url' => route('asesi.ruang-uji', ['pendaftaran_id' => $p->id]),
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
                ->where('kode_formulir', 'FR.IA.05')
                ->where('status', 'submitted')
                ->exists();

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
                    'ruang_uji_url' => route('asesi.ruang-uji', ['pendaftaran_id' => $p->id]),
                    'sisa_detik' => $jadwal->sisa_detik_ujian,
                ]);
            }
        }

        return response()->json([
            'has_active_exam' => false,
        ]);
    }
}
