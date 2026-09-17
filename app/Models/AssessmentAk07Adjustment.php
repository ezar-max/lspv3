<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAk07Adjustment extends Model
{
    use HasFactory;

    protected $table = 'assessment_ak07_adjustments';

    protected $fillable = [
        'assessment_registration_id',
        'potensi_asesi',
        'fase_penggunaan',
        'items_checklist',
        'acuan_pembanding_disepakati',
        'metode_disepakati',
        'instrumen_disepakati',
        'catatan_asesor',
        'asesor_signature',
        'asesor_signed_at',
        'asesi_signature',
        'asesi_signed_at',
        'status',
    ];

    protected $casts = [
        'items_checklist' => 'array',
        'potensi_asesi' => 'integer',
        'asesor_signed_at' => 'datetime',
        'asesi_signed_at' => 'datetime',
    ];

    /**
     * Definisi Baku 8 Kategori Standar BNSP FR.AK.07
     */
    public const CRITERIA_DEFINITIONS = [
        1 => [
            'id' => 1,
            'title' => 'Keterbatasan bahasa, literasi, numerasi',
            'key' => 'bahasa_literasi',
            'icon' => 'fa-language',
            'sub_options' => [
                'penyesuaian_instruksi_tertulis' => 'Penyesuaian teks instruksi kerja/soal dengan bahasa yang sederhana & komunikatif',
                'bantuan_komunikasi_lisan' => 'Pemberian klarifikasi dan bimbingan verbal secara lisan / dwibahasa',
                'tambahan_waktu_literasi' => 'Pemberian alokasi waktu ekstra untuk membaca dan memahami butir dokumen/tugas'
            ]
        ],
        2 => [
            'id' => 2,
            'title' => 'Dukungan pembaca, penerjemah, pelayan, penulis',
            'key' => 'dukungan_personel',
            'icon' => 'fa-hands-asl-interpreting',
            'sub_options' => [
                'pembaca_soal' => 'Penyediaan petugas pembaca teks instrumen asesmen independen',
                'juru_bahasa_isyarat' => 'Penyediaan juru bahasa isyarat / penerjemah bahasa khusus tersertifikasi',
                'penulis_respons' => 'Penyediaan asisten penulis / transcriber untuk mencatat respons asesi'
            ]
        ],
        3 => [
            'id' => 3,
            'title' => 'Penggunaan teknologi adaptif / peralatan khusus',
            'key' => 'teknologi_adaptif',
            'icon' => 'fa-laptop-code',
            'sub_options' => [
                'screen_reader' => 'Pemanfaatan software pembaca layar komputer (Screen Reader / Text-to-Speech)',
                'keyboard_adaptif' => 'Penggunaan perangkat keyboard khusus / alat bantu input alternatif ergonomis',
                'screen_magnifier' => 'Pemanfaatan perangkat lunak pembesar tampilan layar monitor (Screen Magnifier)'
            ]
        ],
        4 => [
            'id' => 4,
            'title' => 'Fleksibilitas waktu asesmen (keletihan / kondisi pengobatan)',
            'key' => 'fleksibilitas_waktu',
            'icon' => 'fa-clock-rotate-left',
            'sub_options' => [
                'istirahat_terjadwal' => 'Penyediaan jeda istirahat berkala / intermiten selama proses asesmen',
                'perpanjangan_durasi' => 'Penambahan alokasi total durasi waktu pengerjaan tugas praktik atau tes tertulis',
                'sesi_bertahap' => 'Pembagian pelaksanaan asesmen ke dalam beberapa sesi terpisah / multi-hari'
            ]
        ],
        5 => [
            'id' => 5,
            'title' => 'Format bahan asesmen khusus (braille, audio/video, cetak besar)',
            'key' => 'format_khusus',
            'icon' => 'fa-braille',
            'sub_options' => [
                'huruf_braille' => 'Pencetakan materi petunjuk dan lembar tugas dalam format huruf Braille',
                'media_audio_video' => 'Penyediaan format panduan instruksional dalam bentuk rekaman audio / video',
                'cetak_large_print' => 'Pencetakan lembar materi dan naskah dengan font ukuran besar (Large Print)'
            ]
        ],
        6 => [
            'id' => 6,
            'title' => 'Penyesuaian lingkungan fisik tempat asesmen',
            'key' => 'lingkungan_fisik',
            'icon' => 'fa-wheelchair',
            'sub_options' => [
                'akses_kursi_roda' => 'Penyediaan jalur landai (ramp) & tata ruang TUK yang ramah kursi roda',
                'pencahayaan_khusus' => 'Pengaturan tingkat pencahayaan ruangan yang memadai / tidak menyilaukan',
                'ruang_tenang' => 'Penyediaan ruang asesmen khusus yang tenang, hening, dan bebas distraksi/kebisingan'
            ]
        ],
        7 => [
            'id' => 7,
            'title' => 'Pertimbangan usia / gender',
            'key' => 'usia_gender',
            'icon' => 'fa-person-half-dress',
            'sub_options' => [
                'kenyamanan_komunikasi' => 'Pendekatan komunikasi dan fasilitasi empatik sesuai kelompok usia asesi',
                'penyesuaian_ergonomi_fisik' => 'Penyesuaian ergonomi fasilitas kerja dan beban fisik aktivitas praktik'
            ]
        ],
        8 => [
            'id' => 8,
            'title' => 'Pertimbangan budaya / tradisi / agama',
            'key' => 'budaya_agama',
            'icon' => 'fa-mosque',
            'sub_options' => [
                'penyesuaian_waktu_ibadah' => 'Penjadwalan sesi asesmen yang menghormati waktu ibadah / hari keagamaan',
                'atribut_khusus' => 'Kebebasan penggunaan atribut pakaian, hijab, atau simbol tradisi/keagamaan yang relevan',
                'preferensi_interaksi' => 'Penghormatan terhadap norma adat, tata krama, dan norma komunikasi asesi'
            ]
        ],
    ];

    /**
     * Definisi Baku 5 Potensi Asesi dari FR.MAPA.01
     */
    public const POTENSI_DEFINITIONS = [
        1 => 'Hasil pelatihan dan/atau pendidikan, kurikulum dan fasilitas praktik mampu telusur terhadap standar kompetensi',
        2 => 'Hasil pelatihan dan/atau pendidikan, kurikulum belum berbasis kompetensi',
        3 => 'Pekerja berpengalaman, industri/tempat kerja operasionalnya mampu telusur standar kompetensi',
        4 => 'Pekerja berpengalaman, industri/tempat kerja operasionalnya belum berbasis kompetensi',
        5 => 'Pelatihan / belajar mandiri atau otodidak',
    ];

    /**
     * Relasi ke data Pendaftaran Asesi
     */
    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'assessment_registration_id');
    }

    public function registration()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'assessment_registration_id');
    }

    /**
     * Scope Status
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Helper apakah formulir sudah memiliki tanda tangan lengkap kedua pihak
     */
    public function isFullySigned(): bool
    {
        return !empty($this->asesor_signature) && !empty($this->asesi_signature);
    }
}
