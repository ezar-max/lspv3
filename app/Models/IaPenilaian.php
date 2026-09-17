<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IaPenilaian extends Model
{
    use HasFactory;

    protected $table = 'ia_penilaian';

    protected $fillable = [
        'pendaftaran_id',
        'kode_formulir',
        'user_id',
        'role',
        'data_jawaban',
        'rekomendasi',
        'catatan_asesor',
        'status',
        'token_akses',
        'token_expired_at',
        'tanda_tangan',
        'tanggal_tanda_tangan',
    ];

    protected $casts = [
        'data_jawaban' => 'array',
        'token_expired_at' => 'datetime',
        'tanggal_tanda_tangan' => 'datetime',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }
}
