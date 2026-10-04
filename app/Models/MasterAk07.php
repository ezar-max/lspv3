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
        if ($this->status !== 'selesai' || empty($this->tanda_tangan_asesor)) {
            return 0;
        }

        $query = PendaftaranAsesi::where('skema_id', $this->skema_id);

        if ($specificPendaftaranId) {
            $query->where('id', $specificPendaftaranId);
        }

        $count = 0;
        $query->each(function (PendaftaranAsesi $p) use (&$count) {
            $ak07 = AssessmentAk07Adjustment::firstOrNew([
                'assessment_registration_id' => $p->id,
            ]);

            $masterData = [
                'potensi_asesi' => $this->potensi_asesi,
                'fase_penggunaan' => $this->fase_penggunaan,
                'items_checklist' => $this->items_checklist ?? [],
                'acuan_pembanding_disepakati' => $this->acuan_pembanding_disepakati,
                'metode_disepakati' => $this->metode_disepakati,
                'instrumen_disepakati' => $this->instrumen_disepakati,
                'catatan_asesor' => $this->catatan_asesor,
                'asesor_signature' => $this->tanda_tangan_asesor,
                'asesor_signed_at' => $this->tanggal_ttd_asesor,
            ];
            $masterChanged = !$ak07->exists;
            foreach ($masterData as $attribute => $value) {
                if ($ak07->getAttribute($attribute) != $value) {
                    $masterChanged = true;
                    break;
                }
            }

            $ak07->fill($masterData);
            if ($masterChanged) {
                $ak07->asesi_signature = null;
                $ak07->asesi_signed_at = null;
                $ak07->status = 'draft';
            }

            $ak07->save();
            $count++;
        });

        return $count;
    }
}
