<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAk06 extends Model
{
    use HasFactory;

    protected $table = 'assessment_ak06';

    public const SCOPE_INDIVIDUAL = 'individual';
    public const SCOPE_KELOMPOK = 'kelompok';
    public const SCOPE_SKEMA = 'skema';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';
    public const STATUS_LOCKED = 'terkunci';

    public const PROCEDURES = [
        'rencana_asesmen' => 'Rencana asesmen',
        'persiapan_asesmen' => 'Persiapan asesmen',
        'implementasi_asesmen' => 'Implementasi asesmen',
        'keputusan_asesmen' => 'Keputusan asesmen',
        'umpan_balik_asesmen' => 'Umpan balik asesmen',
        'rekomendasi_peningkatan' => 'Rekomendasi untuk peningkatan',
    ];

    public const PRINCIPLES = [
        'valid' => 'Valid',
        'reliabel' => 'Reliabel',
        'fleksibel' => 'Fleksibel',
        'adil' => 'Adil',
    ];

    public const COMPETENCY_DIMENSIONS = [
        'task_skills' => 'Task Skills',
        'task_management_skills' => 'Task Management Skills',
        'contingency_management_skills' => 'Contingency Management Skills',
        'job_role_environment_skills' => 'Job Role / Environment Skills',
        'transfer_skills' => 'Transfer Skills',
    ];

    protected $fillable = [
        'nomor_review',
        'skema_id',
        'jadwal_id',
        'pendaftaran_id',
        'asesor_id',
        'scope_type',
        'tanggal_review',
        'prosedur_matrix',
        'dimensi_kompetensi',
        'rekomendasi_peningkatan',
        'komentar_reviewer',
        'tanda_tangan_reviewer',
        'tanggal_ttd_reviewer',
        'status',
        'version',
    ];

    protected $casts = [
        'prosedur_matrix' => 'array',
        'dimensi_kompetensi' => 'array',
        'rekomendasi_peningkatan' => 'array',
        'tanggal_review' => 'date',
        'tanggal_ttd_reviewer' => 'datetime',
        'version' => 'integer',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')
            ->where('document_type', 'FR.AK.06')
            ->orderBy('created_at', 'desc');
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_FINAL, self::STATUS_LOCKED]);
    }
}
