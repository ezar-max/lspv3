<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAk02 extends Model
{
    use HasFactory;

    protected $table = 'assessment_ak02';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'dalam_pengisian';
    public const STATUS_COMPLETED = 'assessment_completed';
    public const STATUS_DECISION_RECORDED = 'decision_recorded';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_FINAL = 'final';
    public const STATUS_LOCKED = 'terkunci';

    public const EVIDENCE_METHODS = [
        'observasi_demonstrasi' => 'Observasi Demonstrasi',
        'portofolio' => 'Portofolio',
        'pernyataan_pihak_ketiga' => 'Pernyataan Pihak Ketiga',
        'pertanyaan_wawancara' => 'Pertanyaan Wawancara',
        'pertanyaan_lisan' => 'Pertanyaan Lisan',
        'pertanyaan_tertulis' => 'Pertanyaan Tertulis',
        'proyek_kerja' => 'Proyek Kerja',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'pendaftaran_id',
        'skema_id',
        'asesor_id',
        'matriks_bukti',
        'rekomendasi_unit',
        'total_unit',
        'total_k',
        'total_bk',
        'keputusan_final',
        'tindak_lanjut',
        'komentar_asesor',
        'dokumen_terkait',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'tanda_tangan_asesi',
        'tanggal_ttd_asesi',
        'status',
        'version',
        'catatan_revisi',
    ];

    protected $casts = [
        'matriks_bukti' => 'array',
        'rekomendasi_unit' => 'array',
        'dokumen_terkait' => 'array',
        'tanggal_ttd_asesor' => 'datetime',
        'tanggal_ttd_asesi' => 'datetime',
        'total_unit' => 'integer',
        'total_k' => 'integer',
        'total_bk' => 'integer',
        'version' => 'integer',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')
            ->where('document_type', 'FR.AK.02')
            ->orderBy('created_at', 'desc');
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_FINAL, self::STATUS_LOCKED]);
    }

    public function isDecisionCompleted(): bool
    {
        return !empty($this->keputusan_final) && in_array($this->status, [
            self::STATUS_DECISION_RECORDED,
            self::STATUS_SIGNED,
            self::STATUS_FINAL,
            self::STATUS_LOCKED,
        ]);
    }

    /**
     * Hitung total unit, K, BK secara otomatis dari array rekomendasi_unit
     */
    public function recalculateCounts(): void
    {
        $rekomendasi = (array) ($this->rekomendasi_unit ?? []);
        $totalK = 0;
        $totalBk = 0;

        foreach ($rekomendasi as $unitId => $item) {
            $hasil = is_array($item) ? ($item['hasil'] ?? null) : $item;
            if (strtoupper((string) $hasil) === 'K') {
                $totalK++;
            } elseif (strtoupper((string) $hasil) === 'BK') {
                $totalBk++;
            }
        }

        $totalUnit = count($rekomendasi);
        $this->total_unit = $totalUnit;
        $this->total_k = $totalK;
        $this->total_bk = $totalBk;

        if ($totalUnit > 0) {
            $this->keputusan_final = ($totalBk === 0 && $totalK === $totalUnit) ? 'kompeten' : 'belum_kompeten';
        }
    }
}
