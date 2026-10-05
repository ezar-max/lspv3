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
        'master_id',
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
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'tanda_tangan_asesi',
        'tanggal_ttd_asesi',
    ];

    protected $casts = [
        'data_jawaban' => 'array',
        'token_expired_at' => 'datetime',
        'tanggal_tanda_tangan' => 'datetime',
        'tanggal_ttd_asesor' => 'datetime',
        'tanggal_ttd_asesi' => 'datetime',
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
