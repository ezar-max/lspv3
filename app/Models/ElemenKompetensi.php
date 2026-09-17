<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElemenKompetensi extends Model
{
    use HasFactory;

    protected $table = 'elemen_kompetensi';

    protected $fillable = [
        'unit_id',
        'nomor_elemen',
        'nama_elemen',
        'pertanyaan_elemen',
    ];

    public function unitKompetensi()
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_id');
    }

    public function kriteriaUnjukKerja()
    {
        return $this->hasMany(KriteriaUnjukKerja::class, 'elemen_id')->orderBy('nomor_kuk', 'asc');
    }

    public function jawabanApl02()
    {
        return $this->hasMany(JawabanApl02::class, 'elemen_id');
    }

    public function buktiApl02()
    {
        return $this->hasMany(BuktiApl02::class, 'elemen_id');
    }

    public function verifikasiKukApl02()
    {
        return $this->hasMany(VerifikasiKukApl02::class, 'elemen_id');
    }
}
