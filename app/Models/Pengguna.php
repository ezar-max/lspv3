<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama_lengkap',
        'email',
        'kata_sandi',
        'peran',
        'skema_id',
        'nomor_registrasi',
        'nomor_telepon',
        'foto_profil',
        'tanda_tangan',
        'aktif',
    ];

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    public function getAuthPasswordName()
    {
        return 'kata_sandi';
    }

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function profilAsesi()
    {
        return $this->hasOne(ProfilAsesi::class, 'pengguna_id');
    }

    public function pendaftaranAsesi()
    {
        return $this->hasMany(PendaftaranAsesi::class, 'asesi_id');
    }

    public function pendaftaranAsesor()
    {
        return $this->hasMany(PendaftaranAsesi::class, 'asesor_id');
    }

    public function jadwalAsesor()
    {
        return $this->hasMany(JadwalAsesmen::class, 'asesor_id');
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'pengguna_id');
    }

    public function assessmentAk02()
    {
        return $this->hasMany(AssessmentAk02::class, 'asesor_id');
    }

    public function assessmentAk05()
    {
        return $this->hasMany(AssessmentAk05::class, 'asesor_id');
    }

    public function assessmentAk06()
    {
        return $this->hasMany(AssessmentAk06::class, 'asesor_id');
    }

    public function assessmentVa()
    {
        return $this->hasMany(AssessmentVa::class, 'lead_asesor_id');
    }

    public function getMorphClass()
    {
        return 'App\Models\Pengguna';
    }
}

