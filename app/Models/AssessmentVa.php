<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentVa extends Model
{
    use HasFactory;

    protected $table = 'assessment_va';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'dalam_pengisian';
    public const STATUS_FINAL = 'final';
    public const STATUS_LOCKED = 'terkunci';

    public const VALIDATION_ASPECTS = [
        1 => 'Rencana Asesmen',
        2 => 'Interpretasi Standar Kompetensi',
        3 => 'Interpretasi Acuan Pembanding lainnya',
        4 => 'Proses Asesmen',
        5 => 'Penyeleksian dan Penerapan Metode Asesmen',
        6 => 'Penyeleksian dan Penerapan Perangkat Asesmen',
        7 => 'Bukti-bukti yang Dikumpulkan',
        8 => 'Pengambilan Keputusan',
    ];

    public const EVIDENCE_RULES = [
        'V' => 'Valid',
        'A' => 'Asli',
        'T' => 'Terkini',
        'M' => 'Memadai',
    ];

    public const ASSESSMENT_PRINCIPLES = [
        'V' => 'Valid',
        'R' => 'Reliabel',
        'F' => 'Fleksibel',
        'A' => 'Adil',
    ];

    public const PARTICIPANT_ROLES = [
        'asesor_kompetensi' => 'Asesor Kompetensi (Wajib)',
        'lead_asesor' => 'Lead Asesor / Ketua TUK',
        'manager_supervisor' => 'Manager / Supervisor',
        'tenaga_ahli' => 'Tenaga Ahli di bidangnya',
        'koordinator_pelatihan' => 'Koordinator Pelatihan',
        'asosiasi_industri' => 'Anggota Asosiasi Industri / Profesi',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'nomor_validasi',
        'skema_id',
        'lead_asesor_id',
        'tanggal_validasi',
        'tempat_validasi',
        'periode_validasi',
        'tujuan_fokus',
        'konteks_validasi',
        'pendekatan_validasi',
        'peserta_relevan',
        'acuan_pembanding',
        'dokumen_terkait',
        'keterampilan_komunikasi',
        'catatan_kontribusi',
        'matriks_penilaian',
        'temuan_validasi',
        'rekomendasi_peningkatan',
        'rencana_perbaikan',
        'current_step',
        'tanda_tangan_lead',
        'tanggal_ttd_lead',
        'status',
        'version',
    ];

    protected $casts = [
        'periode_validasi' => 'array',
        'tujuan_fokus' => 'array',
        'konteks_validasi' => 'array',
        'pendekatan_validasi' => 'array',
        'peserta_relevan' => 'array',
        'acuan_pembanding' => 'array',
        'dokumen_terkait' => 'array',
        'keterampilan_komunikasi' => 'array',
        'matriks_penilaian' => 'array',
        'temuan_validasi' => 'array',
        'rencana_perbaikan' => 'array',
        'tanggal_validasi' => 'date',
        'tanggal_ttd_lead' => 'datetime',
        'current_step' => 'integer',
        'version' => 'integer',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function leadAsesor()
    {
        return $this->belongsTo(Pengguna::class, 'lead_asesor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')
            ->where('document_type', 'FR.VA')
            ->orderBy('created_at', 'desc');
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_FINAL, self::STATUS_LOCKED]);
    }
}
