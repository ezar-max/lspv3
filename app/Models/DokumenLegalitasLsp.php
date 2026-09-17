<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenLegalitasLsp extends Model
{
    use HasFactory;

    protected $table = 'dokumen_legalitas_lsp';

    protected $fillable = [
        'nama_dokumen',
        'nomor_dokumen',
        'kategori',
        'tanggal_terbit',
        'masa_berlaku',
        'status_dokumen',
        'file_path',
        'keterangan',
    ];
}
