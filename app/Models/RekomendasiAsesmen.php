<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiAsesmen extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_asesmen';

    protected $fillable = [
        'pendaftaran_id',
        'asesor_id',
        'keputusan',
        'catatan_rekomendasi',
        'tanggal_rekomendasi',
        'tanda_tangan_asesor',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }
}
