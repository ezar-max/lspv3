<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BuktiApl02 extends Model
{
    use HasFactory;

    protected $table = 'bukti_apl02';

    protected $fillable = [
        'pendaftaran_id',
        'elemen_id',
        'dokumen_id',
        'sumber',
        'nama_file_asli',
        'nama_file_tersimpan',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAsesi::class, 'pendaftaran_id');
    }

    public function elemen()
    {
        return $this->belongsTo(ElemenKompetensi::class, 'elemen_id');
    }

    public function dokumenAsesi()
    {
        return $this->belongsTo(DokumenAsesi::class, 'dokumen_id');
    }

    /**
     * URL publik untuk file bukti (baik dari upload langsung maupun referensi APL.01)
     */
    public function getUrlAttribute()
    {
        $path = ($this->sumber === 'apl01' && $this->dokumenAsesi) 
            ? $this->dokumenAsesi->file_path 
            : $this->file_path;

        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $clean = ltrim(Str::replaceFirst('storage/', '', $path), '/');

        return '/storage/' . $clean;
    }

    /**
     * Nama tampilan dokumen
     */
    public function getNamaTampilAttribute()
    {
        if ($this->sumber === 'apl01' && $this->dokumenAsesi) {
            return $this->dokumenAsesi->jenis_dokumen . ' (' . ($this->dokumenAsesi->nama_dokumen ?: 'Dokumen APL.01') . ')';
        }
        return $this->nama_file_asli ?: ($this->nama_file_tersimpan ?: 'Dokumen Bukti');
    }

    /**
     * Format ukuran file
     */
    public function getFileSizeFormattedAttribute()
    {
        if ($this->sumber === 'apl01') {
            return 'Berkas APL.01';
        }
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        } elseif ($bytes > 0) {
            return $bytes . ' B';
        }
        return '-';
    }

    /**
     * Cek apakah file adalah gambar
     */
    public function getIsImageAttribute()
    {
        $url = $this->url;
        if (!$url) return false;
        return (bool) preg_match('/\.(jpeg|jpg|png|webp|jfif|gif|bmp|avif)($|\?)/i', $url) || Str::startsWith($this->mime_type ?? '', 'image/');
    }

    /**
     * Cek apakah file adalah PDF
     */
    public function getIsPdfAttribute()
    {
        $url = $this->url;
        if (!$url) return false;
        return (bool) preg_match('/\.pdf($|\?)/i', $url) || ($this->mime_type === 'application/pdf');
    }
}
