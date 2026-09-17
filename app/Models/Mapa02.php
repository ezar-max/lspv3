<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa02 extends Model
{
    use HasFactory;

    protected $table = 'mapa_02';

    protected $fillable = [
        'pendaftaran_id',
        'skema_id',
        'asesor_id',
        'matriks_peta',
        'catatan_asesor',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'status_mapa',
    ];

    protected $casts = [
        'matriks_peta' => 'array',
        'tanggal_ttd_asesor' => 'datetime',
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
}
