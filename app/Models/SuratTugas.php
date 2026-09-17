<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratTugas extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'jadwal_id',
        'asesor_id',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'tujuan_penugasan',
        'lokasi_tuk',
        'status',
        'catatan',
        'file_surat_tugas',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }
}
