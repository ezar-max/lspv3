<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemeMasterInstrument extends Model
{
    use HasFactory;

    protected $table = 'scheme_master_instruments';

    protected $fillable = [
        'skema_id',
        'unit_kompetensi_id',
        'instrument_code',
        'title',
        'instructions',
        'time_limit_minutes',
        'is_active',
        'additional_metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_metadata' => 'array',
        'time_limit_minutes' => 'integer',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function unitKompetensi()
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_kompetensi_id');
    }

    public function questionBanks()
    {
        return $this->hasMany(MasterQuestionBank::class, 'scheme_master_instrument_id')->orderBy('order', 'asc');
    }

    public function productSpecifications()
    {
        return $this->hasMany(MasterProductSpecification::class, 'scheme_master_instrument_id')->orderBy('order', 'asc');
    }

    /**
     * Kamus Acuan Baku Penamaan Formulir Instrumen BNSP (FR.IA)
     */
    public const BNSP_INSTRUMENT_MAP = [
        'ia_01' => [
            'code' => 'FR.IA.01',
            'name' => 'Ceklis Observasi Aktivitas',
            'full_name' => 'Ceklis Observasi Aktivitas Praktik',
            'badge' => 'Observasi',
            'category' => 'praktik',
            'color' => 'blue',
            'badge_classes' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot_color' => 'bg-blue-500',
            'icon' => 'fa-clipboard-check',
        ],
        'ia_02' => [
            'code' => 'FR.IA.02',
            'name' => 'Tugas Praktik Demonstrasi',
            'full_name' => 'Tugas Praktik Demonstrasi & Skenario Masalah',
            'badge' => 'Praktik',
            'category' => 'praktik',
            'color' => 'sky',
            'badge_classes' => 'bg-sky-50 text-sky-700 border-sky-200',
            'dot_color' => 'bg-sky-500',
            'icon' => 'fa-person-chalkboard',
        ],
        'ia_03' => [
            'code' => 'FR.IA.03',
            'name' => 'Pertanyaan Pendukung Observasi',
            'full_name' => 'Pertanyaan untuk Mendukung Observasi',
            'badge' => 'Tanya Jawab',
            'category' => 'wawancara',
            'color' => 'amber',
            'badge_classes' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot_color' => 'bg-amber-500',
            'icon' => 'fa-clipboard-question',
        ],
        'ia_05' => [
            'code' => 'FR.IA.05',
            'name' => 'Pertanyaan Tertulis PG',
            'full_name' => 'DPT - Pertanyaan Tertulis Pilihan Ganda (CBT)',
            'badge' => 'CBT / PG',
            'category' => 'tertulis',
            'color' => 'emerald',
            'badge_classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot_color' => 'bg-emerald-500',
            'icon' => 'fa-list-check',
        ],
        'ia_06' => [
            'code' => 'FR.IA.06',
            'name' => 'Pertanyaan Tertulis Esai',
            'full_name' => 'DPT - Pertanyaan Tertulis Esai & Studi Kasus',
            'badge' => 'Esai',
            'category' => 'tertulis',
            'color' => 'purple',
            'badge_classes' => 'bg-purple-50 text-purple-700 border-purple-200',
            'dot_color' => 'bg-purple-500',
            'icon' => 'fa-pen-nib',
        ],
        'ia_07' => [
            'code' => 'FR.IA.07',
            'name' => 'Daftar Pertanyaan Lisan',
            'full_name' => 'DPL - Daftar Pertanyaan Lisan & Kunci Rujukan Asesor',
            'badge' => 'Lisan',
            'category' => 'wawancara',
            'color' => 'orange',
            'badge_classes' => 'bg-orange-50 text-orange-700 border-orange-200',
            'dot_color' => 'bg-orange-500',
            'icon' => 'fa-comments',
        ],
        'ia_04a' => [
            'code' => 'FR.IA.04A',
            'name' => 'Penjelasan Proyek Singkat (TOR)',
            'full_name' => 'Penjelasan Proyek Singkat & Terms of Reference (TOR)',
            'badge' => 'Proyek',
            'category' => 'praktik',
            'color' => 'indigo',
            'badge_classes' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'dot_color' => 'bg-indigo-500',
            'icon' => 'fa-diagram-project',
        ],
        'ia_11' => [
            'code' => 'FR.IA.11',
            'name' => 'Ceklis Spesifikasi Mutu Produk',
            'full_name' => 'Ceklis Standar Mutu dan Spesifikasi Produk Asesi',
            'badge' => 'Mutu Produk',
            'category' => 'praktik',
            'color' => 'teal',
            'badge_classes' => 'bg-teal-50 text-teal-700 border-teal-200',
            'dot_color' => 'bg-teal-500',
            'icon' => 'fa-cube',
        ],
    ];

    /**
     * Normalisasi kode instrumen ke format kunci baku (misal: ia01 -> ia_01, FR.IA.05 -> ia_05)
     */
    public static function normalizeCode(?string $code): string
    {
        $c = strtolower(trim(str_replace(['.', '-', ' '], '', $code ?? '')));
        
        if (str_starts_with($c, 'fr')) {
            $c = substr($c, 2);
        }

        return match ($c) {
            'ia01', 'ia_01', 'cl', 'clo', 'observasi' => 'ia_01',
            'ia02', 'ia_02', 'tpd', 'praktik' => 'ia_02',
            'ia03', 'ia_03', 'pmo', 'pertanyaanobservasi' => 'ia_03',
            'ia05', 'ia_05', 'dptpg', 'cbt', 'pilihanganda' => 'ia_05',
            'ia06', 'ia_06', 'dptesai', 'esai' => 'ia_06',
            'ia07', 'ia_07', 'dpl', 'lisan' => 'ia_07',
            'ia04a', 'ia_04a', 'ia04', 'ia_04', 'tor', 'proyek' => 'ia_04a',
            'ia11', 'ia_11', 'crp', 'mutu', 'produk' => 'ia_11',
            default => str_starts_with($c, 'ia') ? (str_contains($c, '_') ? $c : substr_replace($c, '_', 2, 0)) : 'ia_05'
        };
    }

    /**
     * Dapatkan daftar seluruh alias variasi kode instrumen (ia_05, ia05, FR.IA.05, fr.ia.05, IA.05, dll)
     */
    public static function getCodeAliases(?string $code): array
    {
        $normalized = self::normalizeCode($code);
        $clean = str_replace('_', '', $normalized); // e.g. ia05
        $suffix = substr($clean, 2);
        $upperDot = 'FR.IA.' . strtoupper($suffix);
        $lowerDot = 'fr.ia.' . strtolower($suffix);
        $shortUpperDot = 'IA.' . strtoupper($suffix);
        $shortLowerDot = 'ia.' . strtolower($suffix);

        return array_values(array_unique([
            $normalized,
            $clean,
            strtoupper($clean),
            $upperDot,
            $lowerDot,
            $shortUpperDot,
            $shortLowerDot,
        ]));
    }

    /**
     * Ambil metadata BNSP baku untuk kode tertentu
     */
    public static function getBnspInfo(?string $code): array
    {
        $normalized = self::normalizeCode($code);
        return self::BNSP_INSTRUMENT_MAP[$normalized] ?? [
            'code' => strtoupper($code ?? 'FR.IA'),
            'name' => 'Instrumen Asesmen',
            'full_name' => 'Instrumen Uji Kompetensi BNSP',
            'badge' => 'Instrumen',
            'category' => 'standar',
            'color' => 'slate',
            'badge_classes' => 'bg-slate-50 text-slate-700 border-slate-200',
            'dot_color' => 'bg-slate-500',
            'icon' => 'fa-file-lines',
        ];
    }

    /**
     * Accessor metadata BNSP instance saat ini
     */
    public function getMukInfoAttribute(): array
    {
        return self::getBnspInfo($this->instrument_code);
    }

    /**
     * Accessor total butir soal / parameter
     */
    public function getSoalCountAttribute(): int
    {
        if ($this->instrument_code === 'ia11') {
            return $this->product_specifications_count ?? $this->productSpecifications()->count();
        }

        return $this->question_banks_count ?? $this->questionBanks()->count();
    }

    /**
     * Scope filter berdasarkan kode instrumen
     */
    public function scopeCode($query, $code)
    {
        return $query->where('instrument_code', $code);
    }
}
