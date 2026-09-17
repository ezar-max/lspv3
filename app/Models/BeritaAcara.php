<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'berita_acara';

    protected $fillable = [
        'jadwal_id',
        'nomor_berita_acara',
        'tanggal_pelaksanaan',
        'jumlah_peserta',
        'jumlah_kompeten',
        'jumlah_belum_kompeten',
        'catatan_pelaksanaan',
        'file_berita_acara',
        'tanda_tangan_asesor',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }
}
