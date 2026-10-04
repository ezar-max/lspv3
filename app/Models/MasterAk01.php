<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAk01 extends Model
{
    use HasFactory;

    protected $table = 'master_ak01';

    protected $fillable = [
        'skema_id',
        'asesor_id',
        'tuk_type',
        'bukti_dikumpulkan',
        'bukti_dikumpulkan_lainnya',
        'catatan_asesor',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'status',
    ];

    protected $casts = [
        'bukti_dikumpulkan' => 'array',
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
     * Terapkan konfigurasi Master AK.01 ini ke seluruh pendaftaran asesi pada skema ini
     */
    public function sinkronkanKePeserta(?int $specificPendaftaranId = null): int
    {
        $query = PendaftaranAsesi::where('skema_id', $this->skema_id);

        if ($specificPendaftaranId) {
            $query->where('id', $specificPendaftaranId);
        }

        $count = 0;
        if ($this->status !== 'selesai' || empty($this->tanda_tangan_asesor)) {
            return $count;
        }

        $masterBukti = (array) ($this->bukti_dikumpulkan ?? []);
        $query->each(function (PendaftaranAsesi $pendaftaran) use (&$count, $masterBukti) {
            $pendaftaranBukti = (array) ($pendaftaran->bukti_dikumpulkan ?? []);
            $masterSignedAt = $this->tanggal_ttd_asesor?->format('Y-m-d H:i:s');
            $pendaftaranSignedAt = $pendaftaran->tanggal_ttd_asesor_ak01?->format('Y-m-d H:i:s');
            $configurationChanged = $pendaftaran->tuk_type !== $this->tuk_type
                || $pendaftaranBukti !== $masterBukti
                || $pendaftaran->bukti_dikumpulkan_lainnya !== $this->bukti_dikumpulkan_lainnya
                || $pendaftaran->tanda_tangan_asesor_ak01 !== $this->tanda_tangan_asesor
                || $pendaftaranSignedAt !== $masterSignedAt;

            $dataToUpdate = [
                'tuk_type' => $this->tuk_type,
                'bukti_dikumpulkan' => $masterBukti,
                'bukti_dikumpulkan_lainnya' => $this->bukti_dikumpulkan_lainnya,
                'tanda_tangan_asesor_ak01' => $this->tanda_tangan_asesor,
                'tanggal_ttd_asesor_ak01' => $this->tanggal_ttd_asesor,
            ];

            if ($configurationChanged) {
                $dataToUpdate['tanda_tangan_asesi_ak01'] = null;
                $dataToUpdate['tanggal_ttd_asesi_ak01'] = null;
                $dataToUpdate['status_ak01'] = 'disetujui_asesor';
            } elseif ($pendaftaran->status_ak01 === 'belum') {
                $dataToUpdate['status_ak01'] = 'disetujui_asesor';
            }

            $pendaftaran->update($dataToUpdate);
            $count++;
        });

        return $count;
    }
}
