<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'nama_lsp',
        'kode_lsp',
        'no_sk_lisensi',
        'nomor_lisensi',
        'masa_berlaku',
        'status_keaktifan',
        'email_resmi',
        'nomor_telepon',
        'alamat_lengkap',
        'logo_path',
        'tentang_lsp',
        'visi',
        'misi',
    ];

    public static function ambilData()
    {
        $pengaturan = self::first();
        if (!$pengaturan) {
            $pengaturan = self::create([
                'nama_lsp' => 'Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri',
                'kode_lsp' => 'LSP-P1-SMKN1-GUNUNGPUTRI',
                'no_sk_lisensi' => 'KEP.1215/BNSP/V/2025',
                'nomor_lisensi' => 'BNSP-LSP-2629-ID',
                'masa_berlaku' => 'Hingga 23 Mei 2030',
                'status_keaktifan' => 'Aktif',
                'email_resmi' => 'lsp.smkn1gnputri@gmail.com',
                'nomor_telepon' => '(021) 867-3310',
                'alamat_lengkap' => 'Jl. Barokah No. 6, Desa Wanaherang, Kecamatan Gunungputri, Kabupaten Bogor, Jawa Barat',
                'tentang_lsp' => 'Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri adalah lembaga pelaksana sertifikasi kompetensi kerja yang telah berlisensi resmi Badan Nasional Sertifikasi Profesi (BNSP) untuk menguji dan menjamin kompetensi keahlian peserta didik vokasi berstandar SKKNI dan industri.',
                'visi' => 'Menjadi LSP P1 terdepan dalam menghasilkan lulusan vokasi SMK Negeri 1 Gunungputri yang kompeten, berkarakter, dan berdaya saing global.',
                'misi' => 'Mengembangkan skema sertifikasi sesuai kebutuhan DUDI (Dunia Usaha & Dunia Industri) dan melaksanakan asesmen yang transparan, profesional, dan akuntabel.',
            ]);
        } else {
            // Sinkronkan data profil dan legalitas lembaga resmi
            $pengaturan->update([
                'nama_lsp' => 'Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri',
                'kode_lsp' => 'LSP-P1-SMKN1-GUNUNGPUTRI',
                'no_sk_lisensi' => 'KEP.1215/BNSP/V/2025',
                'nomor_lisensi' => 'BNSP-LSP-2629-ID',
                'masa_berlaku' => 'Hingga 23 Mei 2030',
                'status_keaktifan' => 'Aktif',
                'email_resmi' => 'lsp.smkn1gnputri@gmail.com',
                'nomor_telepon' => '(021) 867-3310',
                'alamat_lengkap' => 'Jl. Barokah No. 6, Desa Wanaherang, Kecamatan Gunungputri, Kabupaten Bogor, Jawa Barat',
            ]);
        }

        return $pengaturan;
    }
}
