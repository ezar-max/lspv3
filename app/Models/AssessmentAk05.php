<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAk05 extends Model
{
    use HasFactory;

    protected $table = 'assessment_ak05';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'dalam_pengisian';
    public const STATUS_REVIEW = 'review';
    public const STATUS_FINAL = 'final';
    public const STATUS_LOCKED = 'terkunci';

    protected $fillable = [
        'nomor_laporan',
        'skema_id',
        'jadwal_id',
        'asesor_id',
        'tanggal_laporan',
        'rekap_asesi',
        'total_asesi',
        'total_k',
        'total_bk',
        'aspek_positif',
        'aspek_negatif',
        'penolakan_hasil',
        'saran_perbaikan',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'status',
        'version',
    ];

    protected $casts = [
        'rekap_asesi' => 'array',
        'saran_perbaikan' => 'array',
        'tanggal_laporan' => 'date',
        'tanggal_ttd_asesor' => 'datetime',
        'total_asesi' => 'integer',
        'total_k' => 'integer',
        'total_bk' => 'integer',
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

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')
            ->where('document_type', 'FR.AK.05')
            ->orderBy('created_at', 'desc');
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_FINAL, self::STATUS_LOCKED]);
    }

    public function recalculateCounts(): void
    {
        $rekap = (array) ($this->rekap_asesi ?? []);
        $totalK = 0;
        $totalBk = 0;

        foreach ($rekap as $item) {
            $hasil = strtoupper((string) ($item['k_bk'] ?? ''));
            if ($hasil === 'K') {
                $totalK++;
            } elseif ($hasil === 'BK') {
                $totalBk++;
            }
        }

        $this->total_asesi = count($rekap);
        $this->total_k = $totalK;
        $this->total_bk = $totalBk;
    }
}
