<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenAsesi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_asesi';

    protected $fillable = [
        'pendaftaran_id',
        'jenis_dokumen',
        'nama_dokumen',
        'file_path',
        'status_verifikasi',
        'catatan',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    /**
     * URL publik untuk file dokumen
     */
    public function getUrlAttribute()
    {
        $path = $this->file_path;
        if (!$path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $clean = ltrim(\Illuminate\Support\Str::replaceFirst('storage/', '', $path), '/');

        return '/storage/' . $clean;
    }

    /**
     * Cek apakah file adalah gambar
     */
    public function getIsImageAttribute()
    {
        $path = $this->file_path;
        if (!$path) return false;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
    }
}
