<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkemaSertifikasi extends Model
{
    use HasFactory;

    protected $table = 'skema_sertifikasi';

    protected $fillable = [
        'kode_skema',
        'nama_skema',
        'kategori',
        'deskripsi',
        'biaya',
        'status_aktif',
        'gambar',
    ];

    protected $appends = [
        'active_instrumens',
        'bidang_keahlian',
        'jumlah_unit',
    ];

    public function getBidangKeahlianAttribute(): string
    {
        return $this->kategori ?? 'Vokasi';
    }

    public function getJumlahUnitAttribute(): int
    {
        return $this->unit_kompetensi_count ?? ($this->relationLoaded('unitKompetensi') ? $this->unitKompetensi->count() : $this->unitKompetensi()->count());
    }


    public function unitKompetensi()
    {
        return $this->hasMany(UnitKompetensi::class, 'skema_id');
    }

    public function jadwalAsesmen()
    {
        return $this->hasMany(JadwalAsesmen::class, 'skema_id');
    }

    public function pendaftaranAsesi()
    {
        return $this->hasMany(PendaftaranAsesi::class, 'skema_id');
    }

    public function masterInstruments()
    {
        return $this->hasMany(SchemeMasterInstrument::class, 'skema_id');
    }

    public function masterAk01()
    {
        return $this->hasOne(MasterAk01::class, 'skema_id');
    }

    public function masterAk07()
    {
        return $this->hasOne(MasterAk07::class, 'skema_id');
    }

    /**
     * Normalisasi kode instrumen ke format canonical internal (contoh: 'FR.IA.01' -> 'ia01')
     */
    public static function normalizeInstrumentCode(string $code): string
    {
        $c = strtolower(trim(str_replace(['.', '-', '_', ' ', 'fr', 'FR'], '', $code)));
        
        return match ($c) {
            'ia01', 'clo', 'observasi', 'ceklisobservasi' => 'ia01',
            'ia02', 'dpt', 'praktik', 'tugaspraktik', 'demonstrasi' => 'ia02',
            'ia03', 'ia07', 'pmo', 'dpl', 'lisan', 'pertanyaanlisan', 'pertanyaanpendukung' => 'ia03',
            'ia04a', 'proyek', 'tor' => 'ia04a',
            'ia05', 'cbt', 'pg', 'pilihanganda', 'teori' => 'ia05',
            'ia06', 'esai', 'kasus', 'tertulisesai' => 'ia06',
            'ia11', 'crp', 'mutu', 'produk', 'ceklismutu' => 'ia11',
            default => $c,
        };
    }

    /**
     * Cek apakah skema memiliki instrumen tertentu yang aktif
     * Mendukung format: 'FR.IA.01', 'ia01', 'IA.01', 'FR.IA.02', 'FR.IA.05', dst.
     */
    public function hasInstrumen(string $kode): bool
    {
        $target = self::normalizeInstrumentCode($kode);

        $activeMasters = $this->relationLoaded('masterInstruments')
            ? $this->masterInstruments->where('is_active', true)
            : $this->masterInstruments()->where('is_active', true)->get();

        if ($activeMasters->isNotEmpty()) {
            foreach ($activeMasters as $m) {
                $code = self::normalizeInstrumentCode($m->instrument_code);
                if ($code === $target) {
                    return true;
                }
                // Khusus lisan: ia03 dan ia07 saling mendukung
                if (($target === 'ia03' || $target === 'ia07') && in_array($code, ['ia03', 'ia07'])) {
                    return true;
                }
            }
            return false;
        }

        // Fallback jika belum dikonfigurasi di master: standar vokasi BNSP mencakup IA.01, IA.02, IA.03, IA.05, IA.06
        return in_array($target, ['ia01', 'ia02', 'ia03', 'ia05', 'ia06']);
    }

    /**
     * Mengambil daftar seluruh instrumen aktif dalam format metadata terstruktur
     */
    public function getActiveInstrumensAttribute(): array
    {
        $allDefinitions = [
            'ia01' => [
                'kode' => 'FR.IA.01',
                'key' => 'ia01',
                'judul' => 'Ceklis Observasi Aktivitas Praktik',
                'label' => 'FR.IA.01 (Ceklis Observasi)',
                'icon' => 'fa-clipboard-check',
                'target' => 'asesor',
                'badge' => 'Observasi',
            ],
            'ia02' => [
                'kode' => 'FR.IA.02',
                'key' => 'ia02',
                'judul' => 'Tugas Praktik Demonstrasi',
                'label' => 'FR.IA.02 (Tugas Praktik Demonstrasi)',
                'icon' => 'fa-screwdriver-wrench',
                'target' => 'semua',
                'badge' => 'Praktik',
            ],
            'ia03' => [
                'kode' => 'FR.IA.03',
                'key' => 'ia03',
                'judul' => 'Pertanyaan Pendukung Observasi / Lisan',
                'label' => 'FR.IA.03 (Pertanyaan Lisan)',
                'icon' => 'fa-comments',
                'target' => 'asesor',
                'badge' => 'Lisan',
            ],
            'ia05' => [
                'kode' => 'FR.IA.05',
                'key' => 'ia05',
                'judul' => 'Ujian Teori CBT Pilihan Ganda',
                'label' => 'FR.IA.05 (Ujian Teori CBT PG)',
                'icon' => 'fa-list-check',
                'target' => 'semua',
                'badge' => 'CBT PG',
            ],
            'ia06' => [
                'kode' => 'FR.IA.06',
                'key' => 'ia06',
                'judul' => 'Ujian Tertulis Esai & Kasus',
                'label' => 'FR.IA.06 (Ujian Tertulis Esai)',
                'icon' => 'fa-pen-to-square',
                'target' => 'semua',
                'badge' => 'Esai',
            ],
            'ia04a' => [
                'kode' => 'FR.IA.04A',
                'key' => 'ia04a',
                'judul' => 'Penugasan Proyek Singkat / TOR',
                'label' => 'FR.IA.04A (Proyek / TOR)',
                'icon' => 'fa-diagram-project',
                'target' => 'semua',
                'badge' => 'Proyek',
            ],
            'ia11' => [
                'kode' => 'FR.IA.11',
                'key' => 'ia11',
                'judul' => 'Ceklis Standar Mutu Produk',
                'label' => 'FR.IA.11 (Ceklis Mutu Produk)',
                'icon' => 'fa-circle-check',
                'target' => 'asesor',
                'badge' => 'Produk',
            ],
        ];

        $result = [];
        foreach ($allDefinitions as $key => $meta) {
            if ($this->hasInstrumen($key)) {
                $result[$key] = $meta;
            }
        }

        return $result;
    }

    /**
     * Dapatkan daftar instrumen yang relevan untuk portal Asesi
     */
    public function getInstrumenAsesi(): array
    {
        return array_filter($this->active_instrumens, function ($item) {
            return in_array($item['target'], ['asesi', 'semua']);
        });
    }

    /**
     * Dapatkan daftar instrumen yang relevan untuk portal Asesor
     */
    public function getInstrumenAsesor(): array
    {
        return $this->active_instrumens;
    }
}
