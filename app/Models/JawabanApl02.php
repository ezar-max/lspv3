<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanApl02 extends Model
{
    use HasFactory;

    protected $table = 'jawaban_apl02';

    protected $fillable = [
        'pendaftaran_id',
        'elemen_id',
        'nilai_kompetensi',
        'bukti_relevan',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function elemen()
    {
        return $this->belongsTo(ElemenKompetensi::class, 'elemen_id');
    }

    /**
     * URL publik untuk bukti relevan
     */
    public function getUrlBuktiAttribute()
    {
        $path = $this->bukti_relevan;
        if (!$path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $clean = ltrim(\Illuminate\Support\Str::replaceFirst('storage/', '', $path), '/');

        return '/storage/' . $clean;
    }
}
