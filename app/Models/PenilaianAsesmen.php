<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianAsesmen extends Model
{
    use HasFactory;

    protected $table = 'penilaian_asesmen';

    protected $fillable = [
        'pendaftaran_id',
        'unit_id',
        'asesor_id',
        'nilai_kompetensi',
        'bukti_pendukung',
        'catatan_asesor',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function unit()
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }
}
