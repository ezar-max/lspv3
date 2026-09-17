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
        $query->each(function (PendaftaranAsesi $p) use (&$count) {
            $dataToUpdate = [];

            // Hanya berikan default TUK dan Bukti jika peserta belum mengisi / status masih 'belum'
            if ($p->status_ak01 === 'belum' || empty($p->tuk_type)) {
                $dataToUpdate['tuk_type'] = $this->tuk_type ?? $p->tuk_type ?? 'Sewaktu';
            }
            if ($p->status_ak01 === 'belum' || empty($p->bukti_dikumpulkan)) {
                $dataToUpdate['bukti_dikumpulkan'] = $this->bukti_dikumpulkan ?? $p->bukti_dikumpulkan ?? ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)', 'Tanya Jawab Lisan'];
            }
            if ($p->status_ak01 === 'belum' || empty($p->bukti_dikumpulkan_lainnya)) {
                $dataToUpdate['bukti_dikumpulkan_lainnya'] = $this->bukti_dikumpulkan_lainnya ?? $p->bukti_dikumpulkan_lainnya;
            }

            // Tanda tangan asesor jika belum diisi
            if (!empty($this->tanda_tangan_asesor) && empty($p->tanda_tangan_asesor_ak01)) {
                $dataToUpdate['tanda_tangan_asesor_ak01'] = $this->tanda_tangan_asesor;
                $dataToUpdate['tanggal_ttd_asesor_ak01'] = $this->tanggal_ttd_asesor ?? now();
            }

            // Update status ak01
            if (!empty($p->tanda_tangan_asesi_ak01) && (!empty($dataToUpdate['tanda_tangan_asesor_ak01']) || !empty($p->tanda_tangan_asesor_ak01))) {
                $dataToUpdate['status_ak01'] = 'selesai';
            } elseif ($p->status_ak01 === 'belum' && (!empty($dataToUpdate['tanda_tangan_asesor_ak01']) || !empty($p->tanda_tangan_asesor_ak01))) {
                $dataToUpdate['status_ak01'] = 'disetujui_asesor';
            }

            if (!empty($dataToUpdate)) {
                $p->update($dataToUpdate);
            }
            $count++;
        });

        return $count;
    }
}
