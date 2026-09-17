<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKompetensi extends Model
{
    use HasFactory;

    protected $table = 'unit_kompetensi';

    protected $fillable = [
        'skema_id',
        'kode_unit',
        'judul_unit',
        'standar_kompetensi',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function elemenKompetensi()
    {
        return $this->hasMany(ElemenKompetensi::class, 'unit_id')->orderBy('nomor_elemen', 'asc');
    }
}
