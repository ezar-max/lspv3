<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAk03 extends Model
{
    use HasFactory;

    protected $table = 'assessment_ak03';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_FINAL = 'final';
    public const STATUS_LOCKED = 'terkunci';

    /**
     * 10 Butir Pernyataan Umpan Balik Resmi Standar BNSP FR.AK.03
     */
    public const FEEDBACK_QUESTIONS = [
        1 => 'Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen/uji kompetensi.',
        2 => 'Saya diberikan kesempatan untuk mempelajari standar kompetensi yang akan diujikan dan menilai diri sendiri terhadap pencapaiannya.',
        3 => 'Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metoda, instrumen dan sumber asesmen serta jadwal asesmen.',
        4 => 'Asesor berusaha menggali seluruh bukti pendukung yang sesuai dengan latar belakang pelatihan dan pengalaman yang saya miliki.',
        5 => 'Saya sepenuhnya diberikan kesempatan untuk mendemonstrasikan kompetensi yang saya miliki selama asesmen.',
        6 => 'Saya mendapatkan penjelasan yang memadai mengenai keputusan asesmen.',
        7 => 'Asesor memberikan umpan balik yang mendukung setelah asesmen serta tindak lanjutnya.',
        8 => 'Asesor bersama saya mempelajari semua dokumen asesmen serta menandatanganinya.',
        9 => 'Saya mendapatkan jaminan kerahasiaan hasil asesmen serta penjelasan penanganan dokumen asesmen.',
        10 => 'Asesor menggunakan keterampilan komunikasi yang efektif selama asesmen.',
    ];

    protected $fillable = [
        'pendaftaran_id',
        'asesi_id',
        'asesor_id',
        'jawaban_kuesioner',
        'catatan_lainnya',
        'tanda_tangan_asesi',
        'tanggal_ttd_asesi',
        'status',
        'version',
        'is_locked',
    ];

    protected $casts = [
        'jawaban_kuesioner' => 'array',
        'tanggal_ttd_asesi' => 'datetime',
        'version' => 'integer',
        'is_locked' => 'boolean',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function asesi()
    {
        return $this->belongsTo(Pengguna::class, 'asesi_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')
            ->where('document_type', 'FR.AK.03')
            ->orderBy('created_at', 'desc');
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_FINAL, self::STATUS_LOCKED]) || $this->is_locked;
    }
}
