<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAk07 extends Model
{
    use HasFactory;

    protected $table = 'master_ak07';

    protected $fillable = [
        'skema_id',
        'asesor_id',
        'potensi_asesi',
        'fase_penggunaan',
        'items_checklist',
        'acuan_pembanding_disepakati',
        'metode_disepakati',
        'instrumen_disepakati',
        'catatan_asesor',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'status',
    ];

    protected $casts = [
        'items_checklist' => 'array',
        'potensi_asesi' => 'integer',
        'tanggal_ttd_asesor' => 'datetime',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    /**
     * Terapkan / sinkronisasikan konfigurasi Master FR.AK.07 ini ke seluruh pendaftaran asesi pada skema ini
     */
    public function sinkronkanKePeserta(?int $specificPendaftaranId = null): int
    {
        $query = PendaftaranAsesi::where('skema_id', $this->skema_id);

        if ($specificPendaftaranId) {
            $query->where('id', $specificPendaftaranId);
        }

        $count = 0;
        $query->each(function (PendaftaranAsesi $p) use (&$count) {
            $ak07 = AssessmentAk07Adjustment::firstOrNew([
                'assessment_registration_id' => $p->id,
            ]);

            // Selaraskan data penyesuaian dari master jika belum dikonfirmasi atau masih draft
            if (!$ak07->exists || $ak07->status === 'draft') {
                $ak07->potensi_asesi = $this->potensi_asesi;
                $ak07->fase_penggunaan = $this->fase_penggunaan;
                $ak07->items_checklist = $this->items_checklist ?? [];
                $ak07->acuan_pembanding_disepakati = $this->acuan_pembanding_disepakati;
                $ak07->metode_disepakati = $this->metode_disepakati;
                $ak07->instrumen_disepakati = $this->instrumen_disepakati;
                $ak07->catatan_asesor = $this->catatan_asesor;
            }

            // Tanda tangan asesor diselaraskan dari master
            if (!empty($this->tanda_tangan_asesor) && (empty($ak07->asesor_signature) || $ak07->asesor_signature !== $this->tanda_tangan_asesor)) {
                $ak07->asesor_signature = $this->tanda_tangan_asesor;
                $ak07->asesor_signed_at = $this->tanggal_ttd_asesor ?? now();
            }

            // Tentukan status akhir
            if (!empty($ak07->asesi_signature) && !empty($ak07->asesor_signature)) {
                $ak07->status = 'confirmed';
            } else {
                $ak07->status = 'draft';
            }

            $ak07->save();
            $count++;
        });

        return $count;
    }
}
