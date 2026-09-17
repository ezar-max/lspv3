<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaPengumuman extends Model
{
    use HasFactory;

    protected $table = 'berita_pengumuman';

    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'ringkasan',
        'konten',
        'gambar',
        'penulis_id',
        'dipublikasikan',
        'tanggal_publikasi',
    ];

    protected $casts = [
        'tanggal_publikasi' => 'datetime',
        'dipublikasikan' => 'boolean',
    ];

    public function penulis()
    {
        return $this->belongsTo(Pengguna::class, 'penulis_id');
    }
}
