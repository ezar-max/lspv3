<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa01 extends Model
{
    use HasFactory;

    protected $table = 'mapa_01';

    protected $fillable = [
        'pendaftaran_id',
        'skema_id',
        'asesor_id',
        'pendekatan_asesi',
        'tujuan_asesmen',
        'tujuan_asesmen_lainnya',
        'konteks_lingkungan',
        'konteks_peluang_bukti',
        'hubungan_standar_bukti',
        'hubungan_standar_aktivitas',
        'hubungan_standar_pembelajaran',
        'pelaksana_asesmen',
        'konfirmasi_orang_relevan',
        'standar_industri',
        'rencana_unit_matriks',
        'karakteristik_kandidat_status',
        'karakteristik_kandidat_teks',
        'kebutuhan_kontekstualisasi_status',
        'kebutuhan_kontekstualisasi_teks',
        'saran_pelatihan_status',
        'saran_pelatihan_teks',
        'penyesuaian_perangkat_status',
        'penyesuaian_perangkat_teks',
        'peluang_terintegrasi_status',
        'peluang_terintegrasi_teks',
        'konfirmasi_pihak_relevan_tabel',
        'penyusun_validator_tabel',
        'tanda_tangan_asesor',
        'tanggal_ttd_asesor',
        'status_mapa',
    ];

    protected $casts = [
        'pendekatan_asesi' => 'array',
        'pelaksana_asesmen' => 'array',
        'konfirmasi_orang_relevan' => 'array',
        'standar_industri' => 'array',
        'rencana_unit_matriks' => 'array',
        'konfirmasi_pihak_relevan_tabel' => 'array',
        'penyusun_validator_tabel' => 'array',
        'tanggal_ttd_asesor' => 'datetime',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }
}
