<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaUnjukKerja extends Model
{
    use HasFactory;

    protected $table = 'kriteria_unjuk_kerja';

    protected $fillable = [
        'elemen_id',
        'nomor_kuk',
        'pernyataan_kuk',
    ];

    public function elemenKompetensi()
    {
        return $this->belongsTo(ElemenKompetensi::class, 'elemen_id');
    }

    public function verifikasiApl02()
    {
        return $this->hasMany(VerifikasiKukApl02::class, 'kuk_id');
    }
}
