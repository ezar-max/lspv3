<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranAsesi extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_asesi';

    protected $fillable = [
        'nomor_pendaftaran',
        'asesi_id',
        'skema_id',
        'asesor_id',
        'jadwal_id',
        'tanggal_daftar',
        'tujuan_asesmen',
        'tujuan_asesmen_lainnya',
        'kebangsaan',
        'kode_pos',
        'no_telp_rumah',
        'nama_perusahaan',
        'jabatan_perusahaan',
        'alamat_kantor',
        'kode_pos_kantor',
        'telp_kantor',
        'fax_kantor',
        'email_kantor',
        'bukti_persyaratan_dasar',
        'bukti_administratif',
        'tanda_tangan_asesi',
        'tanggal_ttd_asesi',
        'status_pendaftaran',
        'rekomendasi_admin_status',
        'catatan_verifikasi',
        'tanda_tangan_admin',
        'tanggal_ttd_admin',
        'rekomendasi_asesor_status',
        'status_apl02',
        'tanggal_submit_apl02',
        'catatan_peninjauan_asesor',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'request_perbaikan',
        'catatan_request_perbaikan',
        'tuk_type',
        'bukti_dikumpulkan',
        'bukti_dikumpulkan_lainnya',
        'tanda_tangan_asesi_ak01',
        'tanggal_ttd_asesi_ak01',
        'tanda_tangan_asesor_ak01',
        'tanggal_ttd_asesor_ak01',
        'status_ak01',
    ];

    protected $casts = [
        'bukti_persyaratan_dasar' => 'array',
        'bukti_administratif' => 'array',
        'bukti_dikumpulkan' => 'array',
        'tanggal_ttd_asesi' => 'date',
        'tanggal_ttd_admin' => 'date',
        'tanggal_ttd_asesor' => 'date',
        'tanggal_submit_apl02' => 'datetime',
        'tanggal_ttd_asesi_ak01' => 'datetime',
        'tanggal_ttd_asesor_ak01' => 'datetime',
        'request_perbaikan' => 'boolean',
    ];

    public function isApprovedByAdmin(): bool
    {
        return in_array($this->status_pendaftaran, ['diverifikasi', 'diterima', 'selesai'])
            || $this->rekomendasi_admin_status === 'diterima'
            || !empty($this->tanda_tangan_admin);
    }

    public function isAccAdmin(): bool
    {
        return $this->isApprovedByAdmin();
    }

    public function isApl02Approved(): bool
    {
        return $this->status_apl02 === 'approved';
    }

    public function isApl02Revision(): bool
    {
        return in_array($this->status_apl02, ['revision', 'revision_requested', 'revisi'])
            || $this->rekomendasi_asesor_status === 'tidak_dapat_dilanjutkan';
    }

    public function isApl02Rejected(): bool
    {
        return in_array($this->status_apl02, ['rejected', 'ditolak'])
            || $this->rekomendasi_asesor_status === 'ditolak';
    }

    public function isApl02Submitted(): bool
    {
        return in_array($this->status_apl02, ['submitted', 'apl02_submitted', 'waiting_reverification']);
    }

    public function isApl02UnderReview(): bool
    {
        return $this->status_apl02 === 'under_review';
    }

    public function isApl02Draft(): bool
    {
        return empty($this->status_apl02) || $this->status_apl02 === 'draft';
    }

    public function isAk01Unlocked(): bool
    {
        return $this->status_apl02 === 'approved'
            || in_array($this->status_ak01, ['disetujui_asesor', 'disetujui_asesi', 'selesai'])
            || !empty($this->tanda_tangan_asesor_ak01);
    }

    public function getEffectiveMapa01()
    {
        $m01 = $this->relationLoaded('mapa01') ? $this->mapa01 : $this->mapa01()->first();
        if (!$m01 && $this->skema_id) {
            $m01 = Mapa01::where('skema_id', $this->skema_id)->whereNull('pendaftaran_id')->latest()->first();
        }
        return $m01;
    }

    public function getEffectiveMapa02()
    {
        $m02 = $this->relationLoaded('mapa02') ? $this->mapa02 : $this->mapa02()->first();
        if (!$m02 && $this->skema_id) {
            $m02 = Mapa02::where('skema_id', $this->skema_id)->whereNull('pendaftaran_id')->latest()->first();
        }
        return $m02;
    }

    public function isMapaConfirmed(): bool
    {
        $m01 = $this->getEffectiveMapa01();
        $m02 = $this->getEffectiveMapa02();

        return ($m01 && $m01->status_mapa === 'selesai') && ($m02 && $m02->status_mapa === 'selesai');
    }

    public function getMapaConfirmedAttribute(): bool
    {
        return $this->isMapaConfirmed();
    }

    public function asesi()
    {
        return $this->belongsTo(Pengguna::class, 'asesi_id');
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'asesi_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function jadwalAsesmen()
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenAsesi::class, 'pendaftaran_id');
    }

    public function penilaian()
    {
        return $this->hasMany(PenilaianAsesmen::class, 'pendaftaran_id');
    }

    public function rekomendasi()
    {
        return $this->hasOne(RekomendasiAsesmen::class, 'pendaftaran_id');
    }

    public function jawabanApl02()
    {
        return $this->hasMany(JawabanApl02::class, 'pendaftaran_id');
    }

    public function verifikasiKukApl02()
    {
        return $this->hasMany(VerifikasiKukApl02::class, 'pendaftaran_id');
    }

    public function buktiApl02()
    {
        return $this->hasMany(BuktiApl02::class, 'pendaftaran_id');
    }

    public function mapa01()
    {
        return $this->hasOne(Mapa01::class, 'pendaftaran_id');
    }

    public function mapa02()
    {
        return $this->hasOne(Mapa02::class, 'pendaftaran_id');
    }

    public function iaPenilaian()
    {
        return $this->hasMany(IaPenilaian::class, 'pendaftaran_id');
    }

    public function ak07Adjustment()
    {
        return $this->hasOne(AssessmentAk07Adjustment::class, 'assessment_registration_id');
    }

    public function ak07()
    {
        return $this->hasOne(AssessmentAk07Adjustment::class, 'assessment_registration_id');
    }

    public function ak02()
    {
        return $this->hasOne(AssessmentAk02::class, 'pendaftaran_id');
    }

    public function ak03()
    {
        return $this->hasOne(AssessmentAk03::class, 'pendaftaran_id');
    }

    public function isAk02Finalized(): bool
    {
        $ak02 = $this->ak02()->first();
        return $ak02 && $ak02->isFinalized();
    }

    public function isAk03Unlocked(): bool
    {
        $ak02 = $this->ak02()->first();
        return $ak02 && $ak02->isDecisionCompleted();
    }

    /**
     * Memeriksa apakah Formulir FR.AK.01 telah disahkan oleh kedua belah pihak
     */
    public function isAk01Selesai(): bool
    {
        if ($this->status_ak01 === 'selesai') {
            return true;
        }

        return !empty($this->tanda_tangan_asesi_ak01) && !empty($this->tanda_tangan_asesor_ak01);
    }

    /**
     * Evaluasi Time-Lock & Kesiapan Ruang Uji (AK.01 + MAPA + Jadwal Waktu)
     */
    public function getAssessmentTimeStatusAttribute(): array
    {
        // 1. Jika asesmen sudah selesai dinilai asesor
        if ($this->status_pendaftaran === 'selesai' || !empty($this->rekomendasi)) {
            $keputusan = strtoupper($this->rekomendasi->keputusan ?? 'SELESAI');
            return [
                'status' => 'selesai_dinilai',
                'can_access' => true,
                'is_readonly' => true,
                'pesan' => "Sesi asesmen telah selesai dilaksanakan. Keputusan: {$keputusan}.",
                'formatted_mulai' => $this->jadwal?->time_status['formatted_mulai'] ?? '-',
                'formatted_selesai' => $this->jadwal?->time_status['formatted_selesai'] ?? '-',
                'formatted_tanggal' => $this->jadwal?->time_status['formatted_tanggal'] ?? '-',
            ];
        }

        // 2. Cek MAPA
        if (!$this->isMapaConfirmed()) {
            return [
                'status' => 'mapa_pending',
                'can_access' => false,
                'is_readonly' => true,
                'pesan' => 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor. Ruang ujian belum tersedia.',
                'formatted_mulai' => $this->jadwal?->time_status['formatted_mulai'] ?? '-',
                'formatted_selesai' => $this->jadwal?->time_status['formatted_selesai'] ?? '-',
                'formatted_tanggal' => $this->jadwal?->time_status['formatted_tanggal'] ?? '-',
            ];
        }

        // 3. Cek AK.01
        if (!$this->isAk01Selesai()) {
            if ($this->status_ak01 === 'disetujui_asesi' || !empty($this->tanda_tangan_asesi_ak01)) {
                return [
                    'status' => 'ak01_menunggu_asesor',
                    'can_access' => false,
                    'is_readonly' => true,
                    'pesan' => 'Formulir FR.AK.01 telah Anda tandatangani dan menunggu pengesahan Asesor.',
                    'formatted_mulai' => $this->jadwal?->time_status['formatted_mulai'] ?? '-',
                    'formatted_selesai' => $this->jadwal?->time_status['formatted_selesai'] ?? '-',
                    'formatted_tanggal' => $this->jadwal?->time_status['formatted_tanggal'] ?? '-',
                ];
            }

            return [
                'status' => 'ak01_pending',
                'can_access' => false,
                'is_readonly' => true,
                'pesan' => 'Formulir Persetujuan Asesmen (FR.AK.01) belum disahkan oleh kedua belah pihak.',
                'formatted_mulai' => $this->jadwal?->time_status['formatted_mulai'] ?? '-',
                'formatted_selesai' => $this->jadwal?->time_status['formatted_selesai'] ?? '-',
                'formatted_tanggal' => $this->jadwal?->time_status['formatted_tanggal'] ?? '-',
            ];
        }

        // 4. Cek Jadwal Asesmen
        if (!$this->jadwal) {
            return [
                'status' => 'aktif',
                'can_access' => true,
                'is_readonly' => false,
                'pesan' => 'Ruang ujian aktif.',
                'formatted_mulai' => '-',
                'formatted_selesai' => '-',
                'formatted_tanggal' => '-',
            ];
        }

        $jadwalStatus = $this->jadwal->time_status;

        if ($jadwalStatus['status'] === 'dibatalkan') {
            return [
                'status' => 'dibatalkan',
                'can_access' => false,
                'is_readonly' => true,
                'pesan' => $jadwalStatus['pesan'],
                'formatted_mulai' => $jadwalStatus['formatted_mulai'],
                'formatted_selesai' => $jadwalStatus['formatted_selesai'],
                'formatted_tanggal' => $jadwalStatus['formatted_tanggal'],
            ];
        }

        if ($jadwalStatus['status'] === 'belum_mulai') {
            return [
                'status' => 'belum_mulai',
                'can_access' => false,
                'is_readonly' => true,
                'pesan' => $jadwalStatus['pesan'],
                'formatted_mulai' => $jadwalStatus['formatted_mulai'],
                'formatted_selesai' => $jadwalStatus['formatted_selesai'],
                'formatted_tanggal' => $jadwalStatus['formatted_tanggal'],
            ];
        }

        if ($jadwalStatus['status'] === 'selesai') {
            return [
                'status' => 'selesai',
                'can_access' => true,
                'is_readonly' => true,
                'pesan' => $jadwalStatus['pesan'],
                'formatted_mulai' => $jadwalStatus['formatted_mulai'],
                'formatted_selesai' => $jadwalStatus['formatted_selesai'],
                'formatted_tanggal' => $jadwalStatus['formatted_tanggal'],
            ];
        }

        return [
            'status' => 'aktif',
            'can_access' => true,
            'is_readonly' => false,
            'pesan' => $jadwalStatus['pesan'],
            'formatted_mulai' => $jadwalStatus['formatted_mulai'],
            'formatted_selesai' => $jadwalStatus['formatted_selesai'],
            'formatted_tanggal' => $jadwalStatus['formatted_tanggal'],
        ];
    }

    /**
     * Memeriksa apakah ruang uji dapat diakses untuk pengerjaan aktif
     */
    public function isRuangUjiOpen(bool $isPostRequest = false, int $gracePeriodMinutes = 10): bool
    {
        if ($this->status_pendaftaran === 'selesai' || !empty($this->rekomendasi)) {
            return true;
        }

        if (!$this->isMapaConfirmed() || !$this->isAk01Selesai()) {
            return false;
        }

        if (!$this->jadwal) {
            return true;
        }

        return $this->jadwal->isWaktuAktif($isPostRequest, $gracePeriodMinutes);
    }

    /**
     * Mengambil alasan teks kenapa ruang uji terkunci
     */
    public function getRuangUjiLockReason(): ?string
    {
        $status = $this->assessment_time_status;
        if ($status['can_access'] && !$status['is_readonly']) {
            return null;
        }

        return $status['pesan'] ?? 'Ruang uji saat ini tidak dapat diakses.';
    }

    /**
     * Mengambil daftar instrumen FR.IA yang aktif berdasarkan kesepakatan bukti pada FR.AK.01 dan FR.MAPA
     */
    public function getInstrumenAktifAttribute()
    {
        // Jika merupakan blanko / master pratinjau (misal skema belum dibuat atau belum ada pendaftaran), aktifkan semua FR.IA
        if ($this->id === 0 || str_starts_with((string)$this->nomor_pendaftaran, 'BLANKO')) {
            $allForms = [
                'FR.IA.01', 'FR.IA.02', 'FR.IA.03',
                'FR.IA.04', 'FR.IA.04A', 'FR.IA.04B',
                'FR.IA.05', 'FR.IA.05A', 'FR.IA.05B', 'FR.IA.05C',
                'FR.IA.06', 'FR.IA.06A', 'FR.IA.06B', 'FR.IA.06C',
                'FR.IA.07', 'FR.IA.08', 'FR.IA.09', 'FR.IA.10', 'FR.IA.11'
            ];
            return [
                'has_l' => true,
                'has_tl' => true,
                'has_t_tertulis' => true,
                'has_t_lisan' => true,
                'has_proyek' => true,
                'has_ia01' => true,
                'has_ia02' => true,
                'has_ia03' => true,
                'has_ia04' => true,
                'has_ia05' => true,
                'has_ia06' => true,
                'has_ia07' => true,
                'has_ia08' => true,
                'has_ia09' => true,
                'has_ia10' => true,
                'has_ia11' => true,
                'daftar_kode' => $allForms,
            ];
        }

        $bukti = (array) ($this->bukti_dikumpulkan ?? []);
        if (empty($bukti)) {
            $masterAk01 = MasterAk01::where('skema_id', $this->skema_id)->first();
            if ($masterAk01 && !empty($masterAk01->bukti_dikumpulkan)) {
                $bukti = (array) $masterAk01->bukti_dikumpulkan;
            }
        }
        $buktiStr = implode(' ', $bukti);
        $hasAk01Config = !empty($bukti);

        // Periksa data perencanaan peta instrumen MAPA-02 jika ada
        $mapa02 = $this->relationLoaded('mapa02') ? $this->mapa02 : $this->mapa02()->first();
        $mapa02 = $this->getEffectiveMapa02();
        $matriksPeta = $mapa02 ? ($mapa02->matriks_peta ?? []) : [];
        $hasMapa02Config = !empty($matriksPeta);

        $mapaHasClo = false;
        $mapaHasDpt = false;
        $mapaHasPmo = false;
        $mapaHasDpe = false;
        $mapaHasDpl = false;
        $mapaHasVp  = false;
        $mapaHasPw  = false;
        $mapaHasCrp = false;

        if ($hasMapa02Config && is_array($matriksPeta)) {
            foreach ($matriksPeta as $unitData) {
                if (is_array($unitData)) {
                    foreach ($unitData as $elemData) {
                        if (is_array($elemData)) {
                            foreach ($elemData as $item) {
                                if (is_array($item)) {
                                    if (!empty($item['clo'])) $mapaHasClo = true;
                                    if (!empty($item['dpt'])) $mapaHasDpt = true;
                                    if (!empty($item['pmo'])) $mapaHasPmo = true;
                                    if (!empty($item['dpe'])) $mapaHasDpe = true;
                                    if (!empty($item['dpl'])) $mapaHasDpl = true;
                                    if (!empty($item['vp']))  $mapaHasVp  = true;
                                    if (!empty($item['pw']))  $mapaHasPw  = true;
                                    if (!empty($item['crp'])) $mapaHasCrp = true;
                                }
                            }
                        }
                    }
                }
            }
        }


        // Cek apakah skema memiliki SchemeMasterInstrument aktif (template soal tersedia)
        $hasMasterInstruments = $this->skema
            ? $this->skema->masterInstruments()->where('is_active', true)->exists()
            : false;

        // =====================================================================
        // PRIORITAS PENENTUAN INSTRUMEN AKTIF:
        //   1. MAPA.02 (jika sudah dikonfigurasi asesor) → PRIORITAS UTAMA
        //      - scheme_master_instruments hanya sebagai filter ketersediaan soal
        //   2. scheme_master_instruments saja (jika belum ada MAPA.02)
        //      - dipakai saat asesor belum buat MAPA.02 tapi sudah upload instrumen
        //   3. bukti_dikumpulkan (AK.01) string matching → FALLBACK terakhir
        // =====================================================================

        if ($hasMapa02Config) {
            // --- PRIORITAS 1: MAPA.02 flags menentukan instrumen aktif ---
            // scheme_master_instruments dipakai sebagai filter tambahan:
            // instrumen aktif jika MAPA.02 centang DAN soal tersedia di sistem.
            // Jika tidak ada masterInstruments → percaya 100% ke MAPA.02 flags.

            $hasIa01 = $mapaHasClo && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.01') : true);
            $hasIa02 = $mapaHasDpt && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.02') : true);
            $hasIa03 = $mapaHasPmo && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.03') : true);
            // FR.IA.04A/B tidak punya flag khusus di MAPA.02 — aktif hanya jika AK.01 eksplisit
            $hasIa04 = Str::contains($buktiStr, ['Kegiatan Terstruktur', 'Proyek', 'TOR']);
            $hasIa05 = $mapaHasDpe && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.05') : true);
            $hasIa06 = $mapaHasDpe && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.06') : true);
            $hasIa07 = $mapaHasDpl && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.07') : true);
            $hasIa08 = $mapaHasVp  && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.08') : true);
            $hasIa09 = $mapaHasPw  && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.09') : true);
            // FR.IA.10 (Pihak Ketiga) selalu manual / magic-link, tidak dikontrol MAPA.02
            $hasIa10 = Str::contains($buktiStr, ['Portofolio', 'Pihak Ketiga', 'Verifikasi Pihak Ketiga']);
            $hasIa11 = $mapaHasCrp && ($hasMasterInstruments ? $this->skema->hasInstrumen('FR.IA.11') : true);

        } elseif ($hasMasterInstruments) {
            // --- PRIORITAS 2: scheme_master_instruments (MAPA.02 belum ada) ---
            $hasIa01 = $this->skema->hasInstrumen('FR.IA.01');
            $hasIa02 = $this->skema->hasInstrumen('FR.IA.02');
            $hasIa03 = $this->skema->hasInstrumen('FR.IA.03');
            $hasIa04 = $this->skema->hasInstrumen('FR.IA.04A') || $this->skema->hasInstrumen('FR.IA.04');
            $hasIa05 = $this->skema->hasInstrumen('FR.IA.05');
            $hasIa06 = $this->skema->hasInstrumen('FR.IA.06');
            $hasIa07 = $this->skema->hasInstrumen('FR.IA.07');
            $hasIa08 = $this->skema->hasInstrumen('FR.IA.08');
            $hasIa09 = $this->skema->hasInstrumen('FR.IA.09');
            $hasIa10 = $this->skema->hasInstrumen('FR.IA.10');
            $hasIa11 = $this->skema->hasInstrumen('FR.IA.11');

        } else {
            // --- PRIORITAS 3: Fallback ke bukti_dikumpulkan (AK.01) string matching ---
            $hasIa01 = Str::contains($buktiStr, ['Observasi Langsung', 'L : ', 'Praktik'])
                || $mapaHasClo
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.01') : true));
            $hasIa02 = Str::contains($buktiStr, ['Observasi Langsung', 'L : ', 'Praktik', 'Tugas Praktik'])
                || $mapaHasDpt || $mapaHasClo
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.02') : true));
            $hasIa03 = Str::contains($buktiStr, ['Observasi Langsung', 'L : ', 'Praktik', 'PMO'])
                || $mapaHasPmo
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.03') : true));
            $hasIa04 = Str::contains($buktiStr, ['Kegiatan Terstruktur', 'Proyek', 'TOR'])
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.04A') : false));
            $hasTTertulis = Str::contains($buktiStr, ['Tertulis', 'Soal Esai', 'Pilihan Ganda', 'T : Tes Tertulis'])
                || $mapaHasDpe
                || (!$hasAk01Config && ($this->skema ? ($this->skema->hasInstrumen('FR.IA.05') || $this->skema->hasInstrumen('FR.IA.06')) : true));
            $hasTLisan = Str::contains($buktiStr, ['Lisan', 'Hasil Pertanyaan Lisan', 'T : Tes Lisan'])
                || $mapaHasDpl
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.07') : false));
            $hasIa05 = $hasTTertulis;
            $hasIa06 = $hasTTertulis;
            $hasIa07 = $hasTLisan;
            $hasIa08 = Str::contains($buktiStr, ['Portofolio', 'TL : ', 'Verifikasi']) || $mapaHasVp;
            $hasIa09 = Str::contains($buktiStr, ['Wawancara', 'Portofolio', 'Hasil Pertanyaan Wawancara']) || $mapaHasPw;
            $hasIa10 = Str::contains($buktiStr, ['Portofolio', 'Pihak Ketiga', 'Verifikasi']);
            $hasIa11 = Str::contains($buktiStr, ['Reviu Produk', 'Produk', 'Hasil Reviu Produk'])
                || $mapaHasCrp
                || (!$hasAk01Config && ($this->skema ? $this->skema->hasInstrumen('FR.IA.11') : false));
        }

        $hasL          = $hasIa01 || $hasIa02 || $hasIa03;
        $hasTL         = $hasIa08 || $hasIa09 || $hasIa10;
        $hasTTertulis  = $hasIa05 || $hasIa06;
        $hasTLisan     = $hasIa07;
        $hasProyek     = $hasIa04 || $hasIa11;

        $aktif = [];
        if ($hasIa01) $aktif[] = 'FR.IA.01';
        if ($hasIa02) $aktif[] = 'FR.IA.02';
        if ($hasIa03) $aktif[] = 'FR.IA.03';
        if ($hasIa04) {
            $aktif[] = 'FR.IA.04';
            $aktif[] = 'FR.IA.04A';
            $aktif[] = 'FR.IA.04B';
        }
        if ($hasIa05) {
            $aktif[] = 'FR.IA.05';
            $aktif[] = 'FR.IA.05A';
            $aktif[] = 'FR.IA.05B';
            $aktif[] = 'FR.IA.05C';
        }
        if ($hasIa06) {
            $aktif[] = 'FR.IA.06';
            $aktif[] = 'FR.IA.06A';
            $aktif[] = 'FR.IA.06B';
            $aktif[] = 'FR.IA.06C';
        }
        if ($hasIa07) $aktif[] = 'FR.IA.07';
        if ($hasIa08) $aktif[] = 'FR.IA.08';
        if ($hasIa09) $aktif[] = 'FR.IA.09';
        if ($hasIa10) $aktif[] = 'FR.IA.10';
        if ($hasIa11) $aktif[] = 'FR.IA.11';

        return [
            'has_l'         => $hasL,
            'has_tl'        => $hasTL,
            'has_t_tertulis'=> $hasTTertulis,
            'has_t_lisan'   => $hasTLisan,
            'has_proyek'    => $hasProyek,
            'has_ia01'      => $hasIa01,
            'has_ia02'      => $hasIa02,
            'has_ia03'      => $hasIa03,
            'has_ia04'      => $hasIa04,
            'has_ia05'      => $hasIa05,
            'has_ia06'      => $hasIa06,
            'has_ia07'      => $hasIa07,
            'has_ia08'      => $hasIa08,
            'has_ia09'      => $hasIa09,
            'has_ia10'      => $hasIa10,
            'has_ia11'      => $hasIa11,
            'daftar_kode'   => array_unique($aktif),
        ];
    }


    public function hasMapa02Config(): bool
    {
        $mapa02 = $this->getEffectiveMapa02();
        return !empty($mapa02?->matriks_peta);
    }

    public function isInstrumenAktif($kodeForm)
    {
        $info = $this->instrumen_aktif;
        $cleanKode = strtoupper(trim($kodeForm));
        
        return in_array($cleanKode, $info['daftar_kode']);
    }

    /**
     * Dapatkan daftar formulir asesmen yang relevan untuk dikerjakan Asesi
     */
    public function getInstrumenAsesi(): array
    {
        $allDefinitions = [
            'cbt' => [
                'kode' => 'FR.IA.05',
                'key' => 'ia05',
                'tab' => 'cbt',
                'judul' => 'Ujian Teori CBT Pilihan Ganda',
                'label' => 'FR.IA.05 (Ujian Teori CBT PG)',
                'icon' => 'fa-list-check',
                'badge' => 'CBT PG',
            ],
            'esai' => [
                'kode' => 'FR.IA.06',
                'key' => 'ia06',
                'tab' => 'esai',
                'judul' => 'Ujian Tertulis Esai & Kasus',
                'label' => 'FR.IA.06 (Ujian Tertulis Esai)',
                'icon' => 'fa-pen-to-square',
                'badge' => 'Esai',
            ],
            'praktik' => [
                'kode' => 'FR.IA.02',
                'key' => 'ia02',
                'tab' => 'praktik',
                'judul' => 'Tugas Praktik Demonstrasi',
                'label' => 'FR.IA.02 (Tugas Praktik Demonstrasi)',
                'icon' => 'fa-screwdriver-wrench',
                'badge' => 'Praktik',
            ],
            'proyek' => [
                'kode' => 'FR.IA.04A',
                'key' => 'ia04a',
                'tab' => 'proyek',
                'judul' => 'Penugasan Proyek Singkat / TOR',
                'label' => 'FR.IA.04A (Proyek / TOR)',
                'icon' => 'fa-diagram-project',
                'badge' => 'Proyek',
            ],
        ];

        $result = [];
        if ($this->isInstrumenAktif('FR.IA.05')) {
            $result['cbt'] = $allDefinitions['cbt'];
        }
        if ($this->isInstrumenAktif('FR.IA.06')) {
            $result['esai'] = $allDefinitions['esai'];
        }
        if ($this->isInstrumenAktif('FR.IA.02')) {
            $result['praktik'] = $allDefinitions['praktik'];
        }
        if ($this->isInstrumenAktif('FR.IA.04A') || $this->isInstrumenAktif('FR.IA.04')) {
            $result['proyek'] = $allDefinitions['proyek'];
        }

        // Fallback jika belum terkonfigurasi di MAPA / AK-01 tapi skema memiliki instrumen
        if (empty($result) && $this->skema) {
            if ($this->skema->hasInstrumen('FR.IA.05')) $result['cbt'] = $allDefinitions['cbt'];
            if ($this->skema->hasInstrumen('FR.IA.06')) $result['esai'] = $allDefinitions['esai'];
            if ($this->skema->hasInstrumen('FR.IA.02')) $result['praktik'] = $allDefinitions['praktik'];
            if ($this->skema->hasInstrumen('FR.IA.04A')) $result['proyek'] = $allDefinitions['proyek'];
        }

        return $result;
    }

    /**
     * Dapatkan status instrumen aktif khusus untuk suatu Unit Kompetensi berdasarkan FR.MAPA.02
     */
    public function getInstrumenPerUnit($unitId): array
    {
        $mapa02 = $this->relationLoaded('mapa02') ? $this->mapa02 : $this->mapa02()->first();
        $mapa02 = $this->getEffectiveMapa02();
        $unitMatriks = $mapa02?->matriks_peta[$unitId] ?? [];

        $flags = [
            'clo' => false, // IA.01 Observasi
            'dpt' => false, // IA.02 Tugas Praktik
            'pmo' => false, // IA.03 Pertanyaan Observasi / Lisan
            'dpe' => false, // IA.05/06 Uji Tertulis PG & Esai
            'dpl' => false, // IA.07 Tanya Jawab Lisan
            'vp'  => false, // IA.08 Portofolio
            'pw'  => false, // IA.09 Wawancara
            'crp' => false, // IA.11 Reviu Produk
        ];

        $hasExplicitMapaData = false;

        if (is_array($unitMatriks) && count($unitMatriks) > 0) {
            foreach ($unitMatriks as $elemData) {
                if (is_array($elemData)) {
                    foreach ($elemData as $item) {
                        if (is_array($item)) {
                            if (isset($item['clo']) || isset($item['dpt']) || isset($item['pmo']) || isset($item['dpe']) || isset($item['dpl']) || isset($item['vp']) || isset($item['pw']) || isset($item['crp'])) {
                                $hasExplicitMapaData = true;
                            }
                            if (!empty($item['clo']) && (int)$item['clo'] === 1) $flags['clo'] = true;
                            if (!empty($item['dpt']) && (int)$item['dpt'] === 1) $flags['dpt'] = true;
                            if (!empty($item['pmo']) && (int)$item['pmo'] === 1) $flags['pmo'] = true;
                            if (!empty($item['dpe']) && (int)$item['dpe'] === 1) $flags['dpe'] = true;
                            if (!empty($item['dpl']) && (int)$item['dpl'] === 1) $flags['dpl'] = true;
                            if (!empty($item['vp'])  && (int)$item['vp'] === 1)  $flags['vp']  = true;
                            if (!empty($item['pw'])  && (int)$item['pw'] === 1)  $flags['pw']  = true;
                            if (!empty($item['crp']) && (int)$item['crp'] === 1) $flags['crp'] = true;
                        }
                    }
                }
            }
        }

        // Fallback HANYA jika unit ini sama sekali belum pernah diset/diisi pada MAPA.02
        if (!$hasExplicitMapaData && !array_filter($flags)) {
            $skema = $this->relationLoaded('skema') ? $this->skema : $this->skema()->first();
            if ($skema) {
                if ($skema->hasInstrumen('FR.IA.01')) $flags['clo'] = true;
                if ($skema->hasInstrumen('FR.IA.02')) $flags['dpt'] = true;
                if ($skema->hasInstrumen('FR.IA.03')) $flags['pmo'] = true;
                if ($skema->hasInstrumen('FR.IA.05') || $skema->hasInstrumen('FR.IA.06')) $flags['dpe'] = true;
                if ($skema->hasInstrumen('FR.IA.07')) $flags['dpl'] = true;
                if ($skema->hasInstrumen('FR.IA.08')) $flags['vp']  = true;
                if ($skema->hasInstrumen('FR.IA.09')) $flags['pw']  = true;
                if ($skema->hasInstrumen('FR.IA.11')) $flags['crp'] = true;
            }

            if (!array_filter($flags)) {
                $flags['clo'] = true;
                $flags['dpt'] = true;
            }
        }

        return $flags;
    }

    /**
     * Pastikan data formulir FR.AK.01 tersinkronisasi dari Master FR.AK.01 Skema jika ada
     */
    public function syncFromMasterAk01IfAvailable(): bool
    {
        $skema = $this->relationLoaded('skema') ? $this->skema : $this->skema()->with('masterAk01')->first();
        $masterAk01 = $skema?->masterAk01;

        if (!$masterAk01) {
            return false;
        }

        $needsUpdate = false;
        $dataToUpdate = [];

        // Default TUK jika asesi belum memilih atau status masih 'belum'
        if (!empty($masterAk01->tuk_type) && (empty($this->tuk_type) || $this->status_ak01 === 'belum')) {
            if ($this->tuk_type !== $masterAk01->tuk_type) {
                $this->tuk_type = $masterAk01->tuk_type;
                $dataToUpdate['tuk_type'] = $masterAk01->tuk_type;
                $needsUpdate = true;
            }
        }

        // Default metode bukti jika asesi belum memilih atau status masih 'belum'
        if (!empty($masterAk01->bukti_dikumpulkan) && (empty($this->bukti_dikumpulkan) || $this->status_ak01 === 'belum')) {
            if ($this->bukti_dikumpulkan != $masterAk01->bukti_dikumpulkan) {
                $this->bukti_dikumpulkan = $masterAk01->bukti_dikumpulkan;
                $dataToUpdate['bukti_dikumpulkan'] = $masterAk01->bukti_dikumpulkan;
                $needsUpdate = true;
            }
        }

        if (!empty($masterAk01->bukti_dikumpulkan_lainnya) && (empty($this->bukti_dikumpulkan_lainnya) || $this->status_ak01 === 'belum')) {
            if ($this->bukti_dikumpulkan_lainnya !== $masterAk01->bukti_dikumpulkan_lainnya) {
                $this->bukti_dikumpulkan_lainnya = $masterAk01->bukti_dikumpulkan_lainnya;
                $dataToUpdate['bukti_dikumpulkan_lainnya'] = $masterAk01->bukti_dikumpulkan_lainnya;
                $needsUpdate = true;
            }
        }

        if (!empty($masterAk01->tanda_tangan_asesor)) {
            if (empty($this->tanda_tangan_asesor_ak01) || $this->tanda_tangan_asesor_ak01 !== $masterAk01->tanda_tangan_asesor) {
                $this->tanda_tangan_asesor_ak01 = $masterAk01->tanda_tangan_asesor;
                $this->tanggal_ttd_asesor_ak01 = $masterAk01->tanggal_ttd_asesor ?? now();
                $dataToUpdate['tanda_tangan_asesor_ak01'] = $masterAk01->tanda_tangan_asesor;
                $dataToUpdate['tanggal_ttd_asesor_ak01'] = $this->tanggal_ttd_asesor_ak01;

                if ($this->status_ak01 === 'belum') {
                    $this->status_ak01 = 'disetujui_asesor';
                    $dataToUpdate['status_ak01'] = 'disetujui_asesor';
                } elseif (!empty($this->tanda_tangan_asesi_ak01)) {
                    $this->status_ak01 = 'selesai';
                    $dataToUpdate['status_ak01'] = 'selesai';
                }
                $needsUpdate = true;
            }
        }

        if ($needsUpdate && $this->exists) {
            $this->update($dataToUpdate);
        }

        return $needsUpdate;
    }

    /**
     * Pastikan data formulir FR.AK.07 tersinkronisasi dari Master FR.AK.07 Skema jika ada
     */
    public function syncFromMasterAk07IfAvailable(): bool
    {
        $skema = $this->relationLoaded('skema') ? $this->skema : $this->skema()->with('masterAk07')->first();
        $masterAk07 = $skema?->masterAk07;

        if (!$masterAk07) {
            return false;
        }

        $ak07 = $this->relationLoaded('ak07Adjustment') ? $this->ak07Adjustment : $this->ak07Adjustment()->first();
        $isNew = false;
        if (!$ak07) {
            $ak07 = new \App\Models\AssessmentAk07Adjustment([
                'assessment_registration_id' => $this->id,
            ]);
            $isNew = true;
        }

        $needsUpdate = false;

        // Jika baru atau status masih draft, sinkronkan checklist & pengaturan
        if ($isNew || $ak07->status !== 'confirmed') {
            if (!empty($masterAk07->potensi_asesi) && ($isNew || empty($ak07->potensi_asesi))) {
                $ak07->potensi_asesi = $masterAk07->potensi_asesi;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->fase_penggunaan)) {
                $ak07->fase_penggunaan = $masterAk07->fase_penggunaan;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->items_checklist)) {
                $ak07->items_checklist = $masterAk07->items_checklist;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->acuan_pembanding_disepakati)) {
                $ak07->acuan_pembanding_disepakati = $masterAk07->acuan_pembanding_disepakati;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->metode_disepakati)) {
                $ak07->metode_disepakati = $masterAk07->metode_disepakati;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->instrumen_disepakati)) {
                $ak07->instrumen_disepakati = $masterAk07->instrumen_disepakati;
                $needsUpdate = true;
            }
            if (!empty($masterAk07->catatan_asesor)) {
                $ak07->catatan_asesor = $masterAk07->catatan_asesor;
                $needsUpdate = true;
            }
        }

        // Tanda tangan asesor diselaraskan jika belum ada di ak07
        if (!empty($masterAk07->tanda_tangan_asesor) && empty($ak07->asesor_signature)) {
            $ak07->asesor_signature = $masterAk07->tanda_tangan_asesor;
            $ak07->asesor_signed_at = $masterAk07->tanggal_ttd_asesor ?? now();
            $needsUpdate = true;
        }

        if ($needsUpdate || $isNew) {
            if (!empty($ak07->asesor_signature) && !empty($ak07->asesi_signature)) {
                $ak07->status = 'confirmed';
            } else {
                $ak07->status = 'draft';
            }
            $ak07->save();
            return true;
        }

        return false;
    }
}
