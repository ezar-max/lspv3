<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilAsesi extends Model
{
    use HasFactory;

    protected $table = 'profil_asesi';

    protected $fillable = [
        'pengguna_id',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'pendidikan_terakhir',
        'pekerjaan',
        'nama_sekolah_instansi',
        'nomor_pendaftaran',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
