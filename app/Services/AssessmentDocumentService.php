<?php

namespace App\Services;

use App\Models\AssessmentAk02;
use App\Models\AssessmentAk03;
use App\Models\AssessmentAk05;
use App\Models\AssessmentAk06;
use App\Models\AssessmentVa;
use App\Models\DocumentAuditLog;
use App\Models\JadwalAsesmen;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\RekomendasiAsesmen;
use App\Models\SkemaSertifikasi;
use App\Notifications\SystemAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssessmentDocumentService
{
    // =========================================================================
    // 1. FR.AK.02 — REKAMAN ASESMEN KOMPETENSI
    // =========================================================================

    public function getOrCreateAk02(PendaftaranAsesi $pendaftaran): AssessmentAk02
    {
        $ak02 = $pendaftaran->ak02;
        if ($ak02) {
            return $ak02;
        }

        return DB::transaction(function () use ($pendaftaran) {
            // Load unit kompetensi skema
            $skema = $pendaftaran->skema;
            $units = $skema ? $skema->unitKompetensi : collect();

            $matriksDefault = [];
            $rekomendasiDefault = [];

            foreach ($units as $u) {
                $matriksDefault[$u->id] = [
                    'unit_id' => $u->id,
                    'kode_unit' => $u->kode_unit,
                    'judul_unit' => $u->judul_unit,
                    'methods' => [
                        'observasi_demonstrasi' => true, // default observasi
                        'portofolio' => false,
                        'pernyataan_pihak_ketiga' => false,
                        'pertanyaan_wawancara' => false,
                        'pertanyaan_lisan' => false,
                        'pertanyaan_tertulis' => true,  // default tertulis
                        'proyek_kerja' => false,
                        'lainnya' => false,
                    ],
                    'catatan' => '',
                    'referensi_bukti' => '',
                ];

                $rekomendasiDefault[$u->id] = [
                    'unit_id' => $u->id,
                    'hasil' => 'K',
                    'catatan' => '',
                ];
            }

            $dokumenTerkait = [
                'apl01' => [
                    'label' => 'FR.APL.01 Permohonan Sertifikasi Kompetensi',
                    'nomor' => $pendaftaran->nomor_pendaftaran,
                    'status' => $pendaftaran->status_pendaftaran,
                ],
                'apl02' => [
                    'label' => 'FR.APL.02 Asesmen Mandiri Peserta',
                    'status' => $pendaftaran->status_apl02 ?? 'approved',
                ],
                'ak01' => [
                    'label' => 'FR.AK.01 Persetujuan Asesmen dan Kerahasiaan',
                    'status' => $pendaftaran->status_ak01 ?? 'selesai',
                ],
            ];

            $ak02 = AssessmentAk02::create([
                'pendaftaran_id' => $pendaftaran->id,
                'skema_id' => $pendaftaran->skema_id,
                'asesor_id' => $pendaftaran->asesor_id ?? auth()->id(),
                'matriks_bukti' => $matriksDefault,
                'rekomendasi_unit' => $rekomendasiDefault,
                'dokumen_terkait' => $dokumenTerkait,
                'total_unit' => $units->count(),
                'total_k' => $units->count(),
                'total_bk' => 0,
                'keputusan_final' => 'kompeten',
                'status' => AssessmentAk02::STATUS_DRAFT,
                'version' => 1,
            ]);

            DocumentAuditLog::record(
                'FR.AK.02',
                $ak02->id,
                'created',
                null,
                AssessmentAk02::STATUS_DRAFT,
                1,
                'Inisialisasi dokumen FR.AK.02 Rekaman Asesmen Kompetensi'
            );

            return $ak02;
        });
    }

    public function saveDraftAk02(AssessmentAk02 $ak02, array $data, Pengguna $user): AssessmentAk02
    {
        if ($ak02->isFinalized() && !in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'status' => 'Dokumen FR.AK.02 telah difinalisasi dan terkunci untuk pengubahan langsung.',
            ]);
        }

        return DB::transaction(function () use ($ak02, $data, $user) {
            $prevStatus = $ak02->status;

            if (isset($data['matriks_bukti'])) {
                $ak02->matriks_bukti = $data['matriks_bukti'];
            }
            if (isset($data['rekomendasi_unit'])) {
                $ak02->rekomendasi_unit = $data['rekomendasi_unit'];
            }
            if (isset($data['tindak_lanjut'])) {
                $ak02->tindak_lanjut = $data['tindak_lanjut'];
            }
            if (isset($data['komentar_asesor'])) {
                $ak02->komentar_asesor = $data['komentar_asesor'];
            }

            $ak02->recalculateCounts();

            if ($ak02->status === AssessmentAk02::STATUS_DRAFT) {
                $ak02->status = AssessmentAk02::STATUS_IN_PROGRESS;
            }

            $ak02->save();

            DocumentAuditLog::record(
                'FR.AK.02',
                $ak02->id,
                'saved_draft',
                $prevStatus,
                $ak02->status,
                $ak02->version,
                'Pembaruan draf rekaman asesmen kompetensi'
            );

            return $ak02;
        });
    }

    public function signAsesorAk02(AssessmentAk02 $ak02, string $signature, Pengguna $user): AssessmentAk02
    {
        return DB::transaction(function () use ($ak02, $signature, $user) {
            $prevStatus = $ak02->status;

            $ak02->tanda_tangan_asesor = $signature;
            $ak02->tanggal_ttd_asesor = now();
            $ak02->status = AssessmentAk02::STATUS_DECISION_RECORDED;
            $ak02->save();

            // Sinkronkan ke tabel rekomendasi_asesmen jika belum ada
            RekomendasiAsesmen::updateOrCreate(
                ['pendaftaran_id' => $ak02->pendaftaran_id],
                [
                    'asesor_id' => $user->id,
                    'keputusan' => $ak02->keputusan_final,
                    'catatan_rekomendasi' => $ak02->komentar_asesor ?: "Peserta dinyatakan {$ak02->keputusan_final} pada seluruh unit kompetensi.",
                    'tanggal_rekomendasi' => now(),
                    'tanda_tangan_asesor' => $signature,
                ]
            );

            DocumentAuditLog::record(
                'FR.AK.02',
                $ak02->id,
                'signed_asesor',
                $prevStatus,
                $ak02->status,
                $ak02->version,
                "Asesor {$user->nama_lengkap} menandatangani keputusan {$ak02->keputusan_final}"
            );

            // Kirim notifikasi ke Asesi bahwa keputusan telah keluar dan FR.AK.03 terbuka
            $asesi = $ak02->pendaftaran->asesi;
            if ($asesi) {
                $targetUrl = \Illuminate\Support\Facades\Route::has('dokumen-asesmen.ak03.show')
                    ? route('dokumen-asesmen.ak03.show', $ak02->pendaftaran_id)
                    : '#';
                $asesi->notify(new SystemAlert(
                    'Keputusan Asesmen Tersedia',
                    "Asesor telah menetapkan keputusan asesmen pada formulir FR.AK.02. Silakan mengisi formulir umpan balik FR.AK.03.",
                    $targetUrl,
                    'success',
                    ['pendaftaran_id' => $ak02->pendaftaran_id]
                ));
            }

            return $ak02;
        });
    }

    public function signAsesiAk02(AssessmentAk02 $ak02, string $signature, Pengguna $user): AssessmentAk02
    {
        return DB::transaction(function () use ($ak02, $signature, $user) {
            $prevStatus = $ak02->status;

            $ak02->tanda_tangan_asesi = $signature;
            $ak02->tanggal_ttd_asesi = now();
            $ak02->status = AssessmentAk02::STATUS_FINAL;
            $ak02->save();

            DocumentAuditLog::record(
                'FR.AK.02',
                $ak02->id,
                'signed_asesi',
                $prevStatus,
                $ak02->status,
                $ak02->version,
                "Asesi {$user->nama_lengkap} menandatangani dan menyetujui hasil FR.AK.02"
            );

            return $ak02;
        });
    }

    public function reopenAk02(AssessmentAk02 $ak02, string $notes, Pengguna $user): AssessmentAk02
    {
        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'role' => 'Hanya Admin atau Super Admin yang memiliki wewenang membuka kembali dokumen yang telah difinalisasi.',
            ]);
        }

        return DB::transaction(function () use ($ak02, $notes, $user) {
            $prevStatus = $ak02->status;
            $newVersion = $ak02->version + 1;

            $ak02->version = $newVersion;
            $ak02->status = AssessmentAk02::STATUS_IN_PROGRESS;
            $ak02->catatan_revisi = $notes;
            $ak02->save();

            DocumentAuditLog::record(
                'FR.AK.02',
                $ak02->id,
                'reopened',
                $prevStatus,
                $ak02->status,
                $newVersion,
                "Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$notes}"
            );

            return $ak02;
        });
    }

    // =========================================================================
    // 2. FR.AK.03 — UMPAN BALIK DAN CATATAN ASESMEN
    // =========================================================================

    public function getOrCreateAk03(PendaftaranAsesi $pendaftaran): AssessmentAk03
    {
        $ak03 = $pendaftaran->ak03;
        if ($ak03) {
            return $ak03;
        }

        return DB::transaction(function () use ($pendaftaran) {
            $pertanyaanDefault = [];
            foreach (AssessmentAk03::FEEDBACK_QUESTIONS as $no => $text) {
                $pertanyaanDefault[$no] = [
                    'no' => $no,
                    'pertanyaan' => $text,
                    'jawaban' => 'ya',
                    'catatan' => '',
                ];
            }

            $ak03 = AssessmentAk03::create([
                'pendaftaran_id' => $pendaftaran->id,
                'asesi_id' => $pendaftaran->asesi_id,
                'asesor_id' => $pendaftaran->asesor_id ?? 1,
                'jawaban_kuesioner' => $pertanyaanDefault,
                'catatan_lainnya' => '',
                'status' => AssessmentAk03::STATUS_DRAFT,
                'version' => 1,
                'is_locked' => false,
            ]);

            DocumentAuditLog::record(
                'FR.AK.03',
                $ak03->id,
                'created',
                null,
                AssessmentAk03::STATUS_DRAFT,
                1,
                'Inisialisasi kuesioner umpan balik FR.AK.03'
            );

            return $ak03;
        });
    }

    public function saveAk03(
        AssessmentAk03 $ak03,
        array $jawaban,
        ?string $catatanLainnya,
        ?string $signature,
        bool $submit,
        Pengguna $user
    ): AssessmentAk03 {
        // Validasi aturan: tidak boleh diisi sebelum AK.02 memiliki keputusan asesmen
        if (!$ak03->pendaftaran->isAk03Unlocked()) {
            throw ValidationException::withMessages([
                'status' => 'Formulir FR.AK.03 terkunci rapat sampai Asesor menetapkan keputusan asesmen pada FR.AK.02.',
            ]);
        }

        if ($ak03->isSubmitted() && !in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'status' => 'Formulir umpan balik telah dikirimkan dan terkunci untuk pengubahan.',
            ]);
        }

        return DB::transaction(function () use ($ak03, $jawaban, $catatanLainnya, $signature, $submit, $user) {
            $prevStatus = $ak03->status;

            $ak03->jawaban_kuesioner = $jawaban;
            $ak03->catatan_lainnya = $catatanLainnya;

            if ($signature) {
                $ak03->tanda_tangan_asesi = $signature;
                $ak03->tanggal_ttd_asesi = now();
            }

            if ($submit) {
                $ak03->status = AssessmentAk03::STATUS_SUBMITTED;
                $ak03->is_locked = true;

                DocumentAuditLog::record(
                    'FR.AK.03',
                    $ak03->id,
                    'submitted',
                    $prevStatus,
                    $ak03->status,
                    $ak03->version,
                    "Asesi {$user->nama_lengkap} mengirimkan umpan balik FR.AK.03 (Terkunci)"
                );

                // Notifikasi ke Asesor
                $asesor = $ak03->asesor;
                if ($asesor) {
                    $targetUrl = \Illuminate\Support\Facades\Route::has('dokumen-asesmen.ak03.show')
                        ? route('dokumen-asesmen.ak03.show', $ak03->pendaftaran_id)
                        : '#';
                    $asesor->notify(new SystemAlert(
                        'Umpan Balik Asesi Dikirim',
                        "Asesi {$ak03->pendaftaran->asesi->nama_lengkap} telah melengkapi dan mengirimkan formulir umpan balik FR.AK.03.",
                        $targetUrl,
                        'info',
                        ['pendaftaran_id' => $ak03->pendaftaran_id]
                    ));
                }
            } else {
                $ak03->status = AssessmentAk03::STATUS_DRAFT;
                DocumentAuditLog::record(
                    'FR.AK.03',
                    $ak03->id,
                    'saved_draft',
                    $prevStatus,
                    $ak03->status,
                    $ak03->version,
                    'Penyimpanan draf umpan balik asesi'
                );
            }

            $ak03->save();

            return $ak03;
        });
    }

    // =========================================================================
    // 3. FR.AK.05 — LAPORAN ASESMEN
    // =========================================================================

    public function getOrCreateAk05(int $skemaId, ?int $jadwalId, Pengguna $user): AssessmentAk05
    {
        $existing = AssessmentAk05::where('skema_id', $skemaId)
            ->when($jadwalId, fn ($q) => $q->where('jadwal_id', $jadwalId))
            ->where('asesor_id', $user->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($skemaId, $jadwalId, $user) {
            $skema = SkemaSertifikasi::findOrFail($skemaId);
            $nomorLaporan = 'AK05/' . date('Y/m') . '/' . strtoupper(Str::random(6));

            $ak05 = AssessmentAk05::create([
                'nomor_laporan' => $nomorLaporan,
                'skema_id' => $skemaId,
                'jadwal_id' => $jadwalId,
                'asesor_id' => $user->id,
                'tanggal_laporan' => now(),
                'rekap_asesi' => [],
                'total_asesi' => 0,
                'total_k' => 0,
                'total_bk' => 0,
                'aspek_positif' => 'Pelaksanaan asesmen berjalan tertib dan asesi mampu mendemonstrasikan SOP secara memadai.',
                'aspek_negatif' => 'Sebagian peserta membutuhkan waktu tambahan dalam pemahaman terminologi spesifikasi teknis.',
                'penolakan_hasil' => 'Tidak ada penolakan atau banding dari seluruh asesi yang diuji.',
                'saran_perbaikan' => [
                    [
                        'pihak' => 'Asesor',
                        'tindakan' => 'Peningkatan pendalaman studi kasus pada asesmen lisan',
                        'prioritas' => 'Sedang',
                        'batas_waktu' => now()->addMonth()->format('Y-m-d'),
                    ],
                    [
                        'pihak' => 'Manajemen TUK',
                        'tindakan' => 'Pemeliharaan berkala instrumen workstation praktik',
                        'prioritas' => 'Tinggi',
                        'batas_waktu' => now()->addWeeks(2)->format('Y-m-d'),
                    ],
                ],
                'status' => AssessmentAk05::STATUS_DRAFT,
                'version' => 1,
            ]);

            $this->syncParticipantsAk05($ak05);

            DocumentAuditLog::record(
                'FR.AK.05',
                $ak05->id,
                'created',
                null,
                AssessmentAk05::STATUS_DRAFT,
                1,
                "Inisialisasi dokumen laporan asesmen FR.AK.05 ({$nomorLaporan})"
            );

            return $ak05;
        });
    }

    public function syncParticipantsAk05(AssessmentAk05 $ak05): void
    {
        $query = PendaftaranAsesi::with(['asesi', 'ak02'])
            ->where('skema_id', $ak05->skema_id);

        if ($ak05->jadwal_id) {
            $query->where('jadwal_id', $ak05->jadwal_id);
        } else {
            $query->where('asesor_id', $ak05->asesor_id);
        }

        $pendaftarans = $query->get();
        $rekap = [];
        $totalK = 0;
        $totalBk = 0;

        foreach ($pendaftarans as $p) {
            $ak02 = $p->ak02;
            $isK = $ak02 && $ak02->keputusan_final === 'kompeten';
            $k_bk = $isK ? 'K' : ($ak02 && $ak02->keputusan_final === 'belum_kompeten' ? 'BK' : '-');

            if ($k_bk === 'K') {
                $totalK++;
            } elseif ($k_bk === 'BK') {
                $totalBk++;
            }

            $rekap[] = [
                'pendaftaran_id' => $p->id,
                'nomor_pendaftaran' => $p->nomor_pendaftaran,
                'nama_asesi' => $p->asesi ? $p->asesi->nama_lengkap : 'Peserta',
                'k_bk' => $k_bk,
                'keterangan' => $isK ? 'Semua unit kompeten' : ($k_bk === 'BK' ? 'Perlu tindak lanjut asesmen' : 'Belum selesai asesmen'),
                'is_override' => false,
                'override_reason' => null,
            ];
        }

        $ak05->rekap_asesi = $rekap;
        $ak05->total_asesi = count($rekap);
        $ak05->total_k = $totalK;
        $ak05->total_bk = $totalBk;
        $ak05->save();
    }

    public function saveAk05(
        AssessmentAk05 $ak05,
        array $data,
        bool $finalize,
        ?string $signature,
        Pengguna $user
    ): AssessmentAk05 {
        if ($ak05->isFinalized() && !in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'status' => 'Laporan FR.AK.05 telah difinalisasi dan berstatus read-only.',
            ]);
        }

        return DB::transaction(function () use ($ak05, $data, $finalize, $signature, $user) {
            $prevStatus = $ak05->status;

            if (isset($data['aspek_positif'])) {
                $ak05->aspek_positif = $data['aspek_positif'];
            }
            if (isset($data['aspek_negatif'])) {
                $ak05->aspek_negatif = $data['aspek_negatif'];
            }
            if (isset($data['penolakan_hasil'])) {
                $ak05->penolakan_hasil = $data['penolakan_hasil'];
            }
            if (isset($data['saran_perbaikan'])) {
                $ak05->saran_perbaikan = $data['saran_perbaikan'];
            }
            if (isset($data['rekap_asesi'])) {
                $ak05->rekap_asesi = $data['rekap_asesi'];
                $ak05->recalculateCounts();
            }

            if ($signature) {
                $ak05->tanda_tangan_asesor = $signature;
                $ak05->tanggal_ttd_asesor = now();
            }

            if ($finalize) {
                $ak05->status = AssessmentAk05::STATUS_FINAL;
                DocumentAuditLog::record(
                    'FR.AK.05',
                    $ak05->id,
                    'finalized',
                    $prevStatus,
                    $ak05->status,
                    $ak05->version,
                    "Laporan FR.AK.05 difinalisasi oleh {$user->nama_lengkap} (Terkunci)"
                );
            } else {
                $ak05->status = AssessmentAk05::STATUS_IN_PROGRESS;
                DocumentAuditLog::record(
                    'FR.AK.05',
                    $ak05->id,
                    'saved_draft',
                    $prevStatus,
                    $ak05->status,
                    $ak05->version,
                    'Penyimpanan draf laporan asesmen FR.AK.05'
                );
            }

            $ak05->save();

            return $ak05;
        });
    }

    // =========================================================================
    // 4. FR.AK.06 — MENINJAU PROSES ASESMEN
    // =========================================================================

    public function getOrCreateAk06(
        int $skemaId,
        string $scopeType,
        ?int $jadwalId,
        ?int $pendaftaranId,
        Pengguna $user
    ): AssessmentAk06 {
        $existing = AssessmentAk06::where('skema_id', $skemaId)
            ->where('scope_type', $scopeType)
            ->when($jadwalId, fn ($q) => $q->where('jadwal_id', $jadwalId))
            ->when($pendaftaranId, fn ($q) => $q->where('pendaftaran_id', $pendaftaranId))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($skemaId, $scopeType, $jadwalId, $pendaftaranId, $user) {
            $nomorReview = 'AK06/' . date('Y/m') . '/' . strtoupper(Str::random(6));

            $prosedurDefault = [];
            foreach (AssessmentAk06::PROCEDURES as $key => $title) {
                $prosedurDefault[$key] = [
                    'aspek' => $title,
                    'valid' => 'sesuai',
                    'reliabel' => 'sesuai',
                    'fleksibel' => 'sesuai',
                    'adil' => 'sesuai',
                    'catatan' => 'Pelaksanaan aspek selaras dengan standar dan panduan mutu asesmen.',
                ];
            }

            $dimensiDefault = [
                'task_skills' => [
                    'dimensi' => 'Task Skills',
                    'bukti' => 'Hasil observasi unjuk kerja praktik & tes tertulis',
                    'instrumen' => 'FR.IA.01, FR.IA.02',
                    'catatan' => 'Asesi menunjukkan kemampuan menjalankan tugas pokok secara konsisten.',
                ],
                'task_management_skills' => [
                    'dimensi' => 'Task Management Skills',
                    'bukti' => 'Manajemen urutan kerja dan efisiensi waktu praktik',
                    'instrumen' => 'FR.IA.01, FR.IA.02',
                    'catatan' => 'Asesi mengelola alur kerja sesuai standar waktu yang ditentukan.',
                ],
                'contingency_management_skills' => [
                    'dimensi' => 'Contingency Management Skills',
                    'bukti' => 'Respons terhadap skenario troubleshooting darurat',
                    'instrumen' => 'FR.IA.03, FR.IA.07',
                    'catatan' => 'Asesi tanggap dalam penanganan kendala yang tidak terduga.',
                ],
                'job_role_environment_skills' => [
                    'dimensi' => 'Job Role / Environment Skills',
                    'bukti' => 'Penerapan K3 dan kepatuhan SOP lingkungan kerja',
                    'instrumen' => 'FR.IA.01',
                    'catatan' => 'Kepatuhan standar APD dan kerapihan area kerja terjaga.',
                ],
                'transfer_skills' => [
                    'dimensi' => 'Transfer Skills',
                    'bukti' => 'Adaptasi aplikasi alat dan metode kerja pada konteks berbeda',
                    'instrumen' => 'FR.IA.02, FR.IA.09',
                    'catatan' => 'Asesi mampu mentransfer keahlian pada varian tugas kerja sejenis.',
                ],
            ];

            $rekomendasiDefault = [
                [
                    'temuan' => 'Konsistensi pencatatan waktu observasi demonstrasi perlu ditingkatkan',
                    'rekomendasi' => 'Gunakan timestamp digital live assessment secara disiplin',
                    'penanggung_jawab' => 'Seluruh Tim Asesor',
                    'target_tanggal' => now()->addWeeks(3)->format('Y-m-d'),
                    'status' => 'Berjalan',
                ],
            ];

            $ak06 = AssessmentAk06::create([
                'nomor_review' => $nomorReview,
                'skema_id' => $skemaId,
                'jadwal_id' => $jadwalId,
                'pendaftaran_id' => $pendaftaranId,
                'asesor_id' => $user->id,
                'scope_type' => $scopeType,
                'tanggal_review' => now(),
                'prosedur_matrix' => $prosedurDefault,
                'dimensi_kompetensi' => $dimensiDefault,
                'rekomendasi_peningkatan' => $rekomendasiDefault,
                'komentar_reviewer' => 'Proses asesmen terlaksana secara profesional, objektif, dan adil.',
                'status' => AssessmentAk06::STATUS_DRAFT,
                'version' => 1,
            ]);

            DocumentAuditLog::record(
                'FR.AK.06',
                $ak06->id,
                'created',
                null,
                AssessmentAk06::STATUS_DRAFT,
                1,
                "Inisialisasi dokumen peninjauan proses asesmen FR.AK.06 ({$nomorReview})"
            );

            return $ak06;
        });
    }

    public function saveAk06(
        AssessmentAk06 $ak06,
        array $data,
        bool $finalize,
        ?string $signature,
        Pengguna $user
    ): AssessmentAk06 {
        if ($ak06->isFinalized() && !in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'status' => 'Review FR.AK.06 telah difinalisasi dan berstatus read-only.',
            ]);
        }

        return DB::transaction(function () use ($ak06, $data, $finalize, $signature, $user) {
            $prevStatus = $ak06->status;

            if (isset($data['prosedur_matrix'])) {
                $ak06->prosedur_matrix = $data['prosedur_matrix'];
            }
            if (isset($data['dimensi_kompetensi'])) {
                $ak06->dimensi_kompetensi = $data['dimensi_kompetensi'];
            }
            if (isset($data['rekomendasi_peningkatan'])) {
                $ak06->rekomendasi_peningkatan = $data['rekomendasi_peningkatan'];
            }
            if (isset($data['komentar_reviewer'])) {
                $ak06->komentar_reviewer = $data['komentar_reviewer'];
            }

            if ($signature) {
                $ak06->tanda_tangan_reviewer = $signature;
                $ak06->tanggal_ttd_reviewer = now();
            }

            if ($finalize) {
                $ak06->status = AssessmentAk06::STATUS_FINAL;
                DocumentAuditLog::record(
                    'FR.AK.06',
                    $ak06->id,
                    'finalized',
                    $prevStatus,
                    $ak06->status,
                    $ak06->version,
                    "Dokumen peninjauan FR.AK.06 difinalisasi oleh {$user->nama_lengkap}"
                );
            } else {
                $ak06->status = AssessmentAk06::STATUS_DRAFT;
                DocumentAuditLog::record(
                    'FR.AK.06',
                    $ak06->id,
                    'saved_draft',
                    $prevStatus,
                    $ak06->status,
                    $ak06->version,
                    'Penyimpanan draf peninjauan proses asesmen FR.AK.06'
                );
            }

            $ak06->save();

            return $ak06;
        });
    }

    // =========================================================================
    // 5. FR.VA — MEMBERIKAN KONTRIBUSI DALAM VALIDASI ASESMEN
    // =========================================================================

    public function createOrGetVa(int $skemaId, Pengguna $user): AssessmentVa
    {
        $existing = AssessmentVa::where('skema_id', $skemaId)
            ->where('status', '!=', AssessmentVa::STATUS_FINAL)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($skemaId, $user) {
            $skema = SkemaSertifikasi::findOrFail($skemaId);
            $nomorValidasi = 'VA/' . date('Y/m') . '/' . strtoupper(Str::random(6));

            $matriksDefault = [];
            foreach (AssessmentVa::VALIDATION_ASPECTS as $no => $aspek) {
                $matriksDefault[$no] = [
                    'no' => $no,
                    'aspek' => $aspek,
                    'vatm' => [
                        'v' => true,
                        'a' => true,
                        't' => true,
                        'm' => true,
                    ],
                    'vrfa' => [
                        'v' => true,
                        'r' => true,
                        'f' => true,
                        'a' => true,
                    ],
                    'catatan' => 'Sesuai dengan acuan pembanding dan panduan mutu LSP.',
                ];
            }

            $pesertaDefault = [
                [
                    'peran' => 'asesor_kompetensi',
                    'nama' => $user->nama_lengkap,
                    'hasil_konfirmasi' => 'Menyepakati fokus penjaminan mutu dan acuan pembanding SKKNI',
                    'tujuan' => 'Memastikan kesesuaian bukti dan keabsahan instrumen asesmen',
                ],
                [
                    'peran' => 'lead_asesor',
                    'nama' => 'Ketua Komite Teknis Uji Kompetensi',
                    'hasil_konfirmasi' => 'Konfirmasi kepatuhan regulasi BNSP dan kesiapan TUK',
                    'tujuan' => 'Penjaminan mutu pelaksanaan asesmen',
                ],
            ];

            $acuanDefault = [
                'standar_kompetensi' => true,
                'skema_sertifikasi' => true,
                'sop_ik' => true,
                'manual_instruction' => false,
                'standar_kinerja' => true,
                'lainnya' => false,
            ];

            $dokumenTerkaitDefault = [
                'perangkat_asesmen' => true,
                'peraturan_pedoman' => true,
                'bukti_asesmen' => true,
                'lainnya' => false,
            ];

            $temuanDefault = [
                [
                    'temuan' => 'Format formulir pertanyaan lisan perlu diselaraskan dengan revisi KUK terkini',
                    'kategori' => 'Perangkat Asesmen',
                    'prioritas' => 'Sedang',
                    'rekomendasi' => 'Perbarui lembar FR.IA.07 sesuai matriks SKKNI terbaru',
                    'bukti_pendukung' => 'Naskah instrumen bank soal',
                ],
            ];

            $rencanaDefault = [
                [
                    'kegiatan' => 'Revisi dan finalisasi bank soal instrumen FR.IA.07 bersama tim asesor',
                    'waktu' => now()->addWeeks(2)->format('Y-m-d'),
                    'penanggung_jawab' => 'Tim Pengembang Perangkat MUK',
                    'status' => 'Berjalan',
                ],
            ];

            $va = AssessmentVa::create([
                'nomor_validasi' => $nomorValidasi,
                'skema_id' => $skemaId,
                'lead_asesor_id' => $user->id,
                'tanggal_validasi' => now(),
                'tempat_validasi' => 'TUK Mandiri SMKN 1 Gunungputri',
                'periode_validasi' => ['setelah_asesmen'],
                'tujuan_fokus' => [
                    'penjaminan_mutu' => true,
                    'mengantisipasi_risiko' => true,
                    'memenuhi_bnsp' => true,
                    'kesesuaian_bukti' => true,
                ],
                'konteks_validasi' => [
                    'internal' => true,
                    'dengan_kolega' => true,
                ],
                'pendekatan_validasi' => [
                    'panel_asesmen' => true,
                    'mengkaji_perangkat' => true,
                    'mengkaji_bukti' => true,
                ],
                'peserta_relevan' => $pesertaDefault,
                'acuan_pembanding' => $acuanDefault,
                'dokumen_terkait' => $dokumenTerkaitDefault,
                'keterampilan_komunikasi' => [
                    'proaktif' => true,
                    'active_listening' => true,
                    'empati' => true,
                ],
                'catatan_kontribusi' => 'Memberikan telaah kritis dan konstruktif dalam penyempurnaan proses asesmen.',
                'matriks_penilaian' => $matriksDefault,
                'temuan_validasi' => $temuanDefault,
                'rekomendasi_peningkatan' => 'Menjadwalkan kegiatan validasi berkala setiap akhir gelombang sertifikasi.',
                'rencana_perbaikan' => $rencanaDefault,
                'current_step' => 1,
                'status' => AssessmentVa::STATUS_DRAFT,
                'version' => 1,
            ]);

            DocumentAuditLog::record(
                'FR.VA',
                $va->id,
                'created',
                null,
                AssessmentVa::STATUS_DRAFT,
                1,
                "Inisialisasi kegiatan validasi asesmen FR.VA ({$nomorValidasi})"
            );

            return $va;
        });
    }

    public function saveStepVa(
        AssessmentVa $va,
        array $stepData,
        int $step,
        bool $finalize,
        ?string $signature,
        Pengguna $user
    ): AssessmentVa {
        if ($va->isFinalized() && !in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan validasi FR.VA telah difinalisasi dan berstatus read-only.',
            ]);
        }

        return DB::transaction(function () use ($va, $stepData, $step, $finalize, $signature, $user) {
            $prevStatus = $va->status;

            foreach ($stepData as $key => $val) {
                if (in_array($key, $va->getFillable())) {
                    $va->{$key} = $val;
                }
            }

            $va->current_step = max(1, min(7, $step));

            if ($signature) {
                $va->tanda_tangan_lead = $signature;
                $va->tanggal_ttd_lead = now();
            }

            if ($finalize) {
                $va->status = AssessmentVa::STATUS_FINAL;
                DocumentAuditLog::record(
                    'FR.VA',
                    $va->id,
                    'finalized',
                    $prevStatus,
                    $va->status,
                    $va->version,
                    "Kegiatan validasi asesmen FR.VA difinalisasi oleh {$user->nama_lengkap}"
                );
            } else {
                $va->status = AssessmentVa::STATUS_IN_PROGRESS;
                DocumentAuditLog::record(
                    'FR.VA',
                    $va->id,
                    'saved_step_' . $step,
                    $prevStatus,
                    $va->status,
                    $va->version,
                    "Penyimpanan langkah {$step} kegiatan validasi asesmen FR.VA"
                );
            }

            $va->save();

            return $va;
        });
    }

    /**
     * Reopen finalized document of any assessment type (Admin / Superadmin only)
     */
    public function reopenDocument(string $type, int $id, string $reason, Pengguna $user)
    {
        if (!in_array($user->peran, ['admin', 'superadmin'])) {
            throw ValidationException::withMessages([
                'role' => 'Hanya Admin atau Super Admin yang memiliki wewenang membuka kembali dokumen yang telah difinalisasi.',
            ]);
        }

        $typeNormalized = strtoupper(trim($type));

        return DB::transaction(function () use ($typeNormalized, $id, $reason, $user) {
            switch ($typeNormalized) {
                case 'FR.AK.02':
                case 'AK02':
                case 'AK-02':
                    $doc = AssessmentAk02::findOrFail($id);
                    $prevStatus = $doc->status;
                    $doc->version += 1;
                    $doc->status = AssessmentAk02::STATUS_IN_PROGRESS;
                    $doc->catatan_revisi = $reason;
                    $doc->save();

                    DocumentAuditLog::record(
                        'FR.AK.02',
                        $doc->id,
                        'reopened',
                        $prevStatus,
                        $doc->status,
                        $doc->version,
                        "Perbaikan catatan referensi bukti unit 1 atas temuan tim monitoring. Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$reason}"
                    );
                    return $doc;

                case 'FR.AK.03':
                case 'AK03':
                case 'AK-03':
                    $doc = AssessmentAk03::findOrFail($id);
                    $prevStatus = $doc->status;
                    $doc->version += 1;
                    $doc->status = AssessmentAk03::STATUS_DRAFT;
                    $doc->is_locked = false;
                    $doc->save();

                    DocumentAuditLog::record(
                        'FR.AK.03',
                        $doc->id,
                        'reopened',
                        $prevStatus,
                        $doc->status,
                        $doc->version,
                        "Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$reason}"
                    );
                    return $doc;

                case 'FR.AK.05':
                case 'AK05':
                case 'AK-05':
                    $doc = AssessmentAk05::findOrFail($id);
                    $prevStatus = $doc->status;
                    $doc->version += 1;
                    $doc->status = AssessmentAk05::STATUS_IN_PROGRESS;
                    $doc->save();

                    DocumentAuditLog::record(
                        'FR.AK.05',
                        $doc->id,
                        'reopened',
                        $prevStatus,
                        $doc->status,
                        $doc->version,
                        "Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$reason}"
                    );
                    return $doc;

                case 'FR.AK.06':
                case 'AK06':
                case 'AK-06':
                    $doc = AssessmentAk06::findOrFail($id);
                    $prevStatus = $doc->status;
                    $doc->version += 1;
                    $doc->status = AssessmentAk06::STATUS_IN_PROGRESS;
                    $doc->save();

                    DocumentAuditLog::record(
                        'FR.AK.06',
                        $doc->id,
                        'reopened',
                        $prevStatus,
                        $doc->status,
                        $doc->version,
                        "Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$reason}"
                    );
                    return $doc;

                case 'FR.VA':
                case 'VA':
                    $doc = AssessmentVa::findOrFail($id);
                    $prevStatus = $doc->status;
                    $doc->version += 1;
                    $doc->status = AssessmentVa::STATUS_IN_PROGRESS;
                    $doc->save();

                    DocumentAuditLog::record(
                        'FR.VA',
                        $doc->id,
                        'reopened',
                        $prevStatus,
                        $doc->status,
                        $doc->version,
                        "Dibuka kembali oleh {$user->nama_lengkap} ({$user->peran}). Alasan: {$reason}"
                    );
                    return $doc;

                default:
                    throw ValidationException::withMessages(['document_type' => "Jenis dokumen {$typeNormalized} tidak dikenali."]);
            }
        });
    }
}

