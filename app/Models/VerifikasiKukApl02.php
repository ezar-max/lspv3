<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiKukApl02 extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_kuk_apl02';

    protected $fillable = [
        'pendaftaran_id',
        'elemen_id',
        'kuk_id',
        'nilai_kompetensi',
        'is_verified',
        'catatan_asesor',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function elemen()
    {
        return $this->belongsTo(ElemenKompetensi::class, 'elemen_id');
    }

    public function kuk()
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }
}
