<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalAsesmen extends Model
{
    use HasFactory;

    protected $table = 'jadwal_asesmen';

    protected $fillable = [
        'kode_jadwal',
        'skema_id',
        'asesor_id',
        'nama_tuk',
        'tanggal_uji',
        'waktu_mulai',
        'waktu_selesai',
        'kuota',
        'status_jadwal',
    ];

    public function skema()
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Pengguna::class, 'asesor_id');
    }

    public function pendaftaranAsesi()
    {
        return $this->hasMany(PendaftaranAsesi::class, 'jadwal_id');
    }

    public function beritaAcara()
    {
        return $this->hasOne(BeritaAcara::class, 'jadwal_id');
    }

    /**
     * Waktu Mulai dalam bentuk objek Carbon (Timezone Konsisten)
     */
    public function getWaktuMulaiCarbonAttribute(): ?\Carbon\Carbon
    {
        if (empty($this->tanggal_uji)) {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $waktu = $this->waktu_mulai ? substr($this->waktu_mulai, 0, 5) : '00:00';
        $tanggal = \Carbon\Carbon::parse($this->tanggal_uji, $tz)->format('Y-m-d');

        return \Carbon\Carbon::parse("{$tanggal} {$waktu}", $tz);
    }

    /**
     * Waktu Selesai dalam bentuk objek Carbon (Timezone Konsisten)
     */
    public function getWaktuSelesaiCarbonAttribute(): ?\Carbon\Carbon
    {
        if (empty($this->tanggal_uji)) {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $waktu = $this->waktu_selesai ? substr($this->waktu_selesai, 0, 5) : '23:59';
        $tanggal = \Carbon\Carbon::parse($this->tanggal_uji, $tz)->format('Y-m-d');

        return \Carbon\Carbon::parse("{$tanggal} {$waktu}", $tz);
    }

    /**
     * Sinkronisasi status seluruh jadwal secara real-time berdasarkan tanggal dan waktu WIB saat ini.
     * Aturan:
     * - 'dibatalkan' tetap 'dibatalkan'
     * - Waktu saat ini > waktu_selesai -> otomatis 'selesai' (waktu habis)
     * - Waktu saat ini di antara waktu_mulai dan waktu_selesai -> otomatis 'berlangsung'
     * - Waktu saat ini < waktu_mulai -> otomatis 'terjadwal'
     */
    public static function syncAllStatuses(): void
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);
        $today = $now->toDateString();
        $timeNow = $now->format('H:i:s');
        $timeGraceThreshold = $now->copy()->subMinutes(10)->format('H:i:s');

        // 1. Jadwal hari ini yang telah melewati waktu selesai + toleransi 10 menit -> otomatis 'selesai'
        static::where('status_jadwal', '!=', 'dibatalkan')
            ->where('status_jadwal', '!=', 'selesai')
            ->where('tanggal_uji', '=', $today)
            ->where('waktu_selesai', '<=', $timeGraceThreshold)
            ->update(['status_jadwal' => 'selesai']);

        // 2. Jadwal tanggal lampau (< hari ini) yang berstatus 'terjadwal' -> otomatis 'selesai'
        // (Catatan: Jadwal tanggal lampau yang diset 'berlangsung' oleh Admin LSP dipertahankan sebagai manual override)
        static::where('status_jadwal', '=', 'terjadwal')
            ->where('tanggal_uji', '<', $today)
            ->update(['status_jadwal' => 'selesai']);

        // 3. Jadwal yang sedang dalam rentang waktu pelaksanaan hari ini -> otomatis 'berlangsung'
        static::where('status_jadwal', '=', 'terjadwal')
            ->where('tanggal_uji', '=', $today)
            ->where('waktu_mulai', '<=', $timeNow)
            ->where('waktu_selesai', '>', $timeNow)
            ->update(['status_jadwal' => 'berlangsung']);
    }

    /**
     * Sinkronisasi status satu jadwal ini secara real-time.
     */
    public function syncRealtimeStatus(bool $save = true): string
    {
        if (in_array($this->status_jadwal, ['dibatalkan', 'selesai'])) {
            return $this->status_jadwal;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);
        $start = $this->waktu_mulai_carbon;
        $end = $this->waktu_selesai_carbon;

        if (!$start || !$end) {
            return $this->status_jadwal ?? 'terjadwal';
        }

        // Pertahankan manual override admin jika jadwal tanggal lampau sengaja diset 'berlangsung'
        if ($this->status_jadwal === 'berlangsung' && $now->toDateString() !== $this->tanggal_uji_carbon?->toDateString()) {
            return 'berlangsung';
        }

        $newStatus = $this->status_jadwal;
        $endWithGrace = $end->copy()->addMinutes(10);

        if ($now->isAfter($endWithGrace)) {
            $newStatus = 'selesai';
        } elseif ($this->status_jadwal === 'terjadwal' && $now->between($start, $end)) {
            $newStatus = 'berlangsung';
        }

        if ($save && $newStatus !== $this->status_jadwal && $this->exists) {
            $this->status_jadwal = $newStatus;
            $this->saveQuietly();
        }

        return $newStatus;
    }

    /**
     * Sisa detik menuju waktu selesai ujian (0 jika sudah berakhir atau belum aktif)
     */
    public function getSisaDetikUjianAttribute(): int
    {
        $end = $this->waktu_selesai_carbon;
        if (!$end) {
            return 0;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);

        if ($now->isAfter($end)) {
            return 0;
        }

        return max(0, $now->diffInSeconds($end, false));
    }

    /**
     * Detik menuju waktu mulai jika jadwal belum dibuka
     */
    public function getDetikMenujuMulaiAttribute(): int
    {
        $start = $this->waktu_mulai_carbon;
        if (!$start) {
            return 0;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);

        if ($now->isAfter($start)) {
            return 0;
        }

        return max(0, $now->diffInSeconds($start, false));
    }

    /**
     * Memeriksa apakah waktu asesmen sedang aktif
     * Prioritas:
     * 1. Status Manual Admin ('berlangsung' -> True)
     * 2. Status 'dibatalkan' / 'selesai' -> False
     * 3. Waktu Server saat ini berada dalam rentang [waktu_mulai, waktu_selesai (+ grace period jika POST)]
     */
    public function isWaktuAktif(bool $isPostRequest = false, int $gracePeriodMinutes = 10): bool
    {
        if (in_array($this->status_jadwal, ['dibatalkan', 'selesai'])) {
            return false;
        }

        // Jika diset berlangsung (misal manual admin override / toleransi), izinkan
        if ($this->status_jadwal === 'berlangsung') {
            return true;
        }

        $start = $this->waktu_mulai_carbon;
        $end = $this->waktu_selesai_carbon;

        if (!$start || !$end) {
            return true;
        }

        if ($isPostRequest) {
            $end = $end->copy()->addMinutes($gracePeriodMinutes);
        }

        $now = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'));

        return $now->between($start, $end);
    }

    /**
     * Memeriksa apakah jadwal belum dimulai
     */
    public function isBelumMulai(): bool
    {
        if ($this->status_jadwal === 'berlangsung') {
            return false;
        }

        if ($this->status_jadwal === 'dibatalkan') {
            return false;
        }

        $start = $this->waktu_mulai_carbon;
        if (!$start) {
            return false;
        }

        $now = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'));

        return $now->isBefore($start);
    }

    /**
     * Memeriksa apakah jadwal sudah berakhir
     */
    public function isSudahSelesai(bool $isPostRequest = false, int $gracePeriodMinutes = 10): bool
    {
        if ($this->status_jadwal === 'selesai') {
            return true;
        }

        if ($this->status_jadwal === 'berlangsung') {
            return false;
        }

        $end = $this->waktu_selesai_carbon;
        if (!$end) {
            return false;
        }

        if ($isPostRequest) {
            $end = $end->copy()->addMinutes($gracePeriodMinutes);
        }

        $now = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'));

        return $now->isAfter($end);
    }

    /**
     * Ringkasan status waktu jadwal asesmen lengkap
     */
    public function getTimeStatusAttribute(): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);
        $start = $this->waktu_mulai_carbon;
        $end = $this->waktu_selesai_carbon;

        $fmtMulai = $start ? $start->format('H:i') . ' WIB' : '-';
        $fmtSelesai = $end ? $end->format('H:i') . ' WIB' : '-';
        $fmtTanggal = $start ? $start->translatedFormat('d F Y') : '-';

        if ($this->status_jadwal === 'dibatalkan') {
            return [
                'status' => 'dibatalkan',
                'is_active' => false,
                'is_before' => false,
                'is_after' => false,
                'is_manual' => false,
                'pesan' => 'Jadwal asesmen ini telah dibatalkan oleh LSP.',
                'mulai_at' => $start,
                'selesai_at' => $end,
                'formatted_mulai' => $fmtMulai,
                'formatted_selesai' => $fmtSelesai,
                'formatted_tanggal' => $fmtTanggal,
            ];
        }

        if ($this->status_jadwal === 'berlangsung') {
            return [
                'status' => 'aktif',
                'is_active' => true,
                'is_before' => false,
                'is_after' => false,
                'is_manual' => true,
                'pesan' => "Sesi asesmen sedang aktif di {$this->nama_tuk} sampai pukul {$fmtSelesai}.",
                'mulai_at' => $start,
                'selesai_at' => $end,
                'formatted_mulai' => $fmtMulai,
                'formatted_selesai' => $fmtSelesai,
                'formatted_tanggal' => $fmtTanggal,
            ];
        }

        if ($this->status_jadwal === 'selesai' || ($end && $now->isAfter($end))) {
            return [
                'status' => 'selesai',
                'is_active' => false,
                'is_before' => false,
                'is_after' => true,
                'is_manual' => ($this->status_jadwal === 'selesai'),
                'pesan' => "Sesi asesmen telah berakhir pada pukul {$fmtSelesai} (Waktu Habis).",
                'mulai_at' => $start,
                'selesai_at' => $end,
                'formatted_mulai' => $fmtMulai,
                'formatted_selesai' => $fmtSelesai,
                'formatted_tanggal' => $fmtTanggal,
            ];
        }

        if ($start && $now->isBefore($start)) {
            $diffHuman = $now->diffForHumans($start, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]);
            return [
                'status' => 'belum_mulai',
                'is_active' => false,
                'is_before' => true,
                'is_after' => false,
                'is_manual' => false,
                'pesan' => "Ruang uji belum dibuka. Sesi asesmen dijadwalkan pada {$fmtTanggal} pukul {$fmtMulai} ({$diffHuman}).",
                'mulai_at' => $start,
                'selesai_at' => $end,
                'formatted_mulai' => $fmtMulai,
                'formatted_selesai' => $fmtSelesai,
                'formatted_tanggal' => $fmtTanggal,
            ];
        }

        return [
            'status' => 'aktif',
            'is_active' => true,
            'is_before' => false,
            'is_after' => false,
            'is_manual' => false,
            'pesan' => "Sesi asesmen sedang berlangsung sampai pukul {$fmtSelesai}.",
            'mulai_at' => $start,
            'selesai_at' => $end,
            'formatted_mulai' => $fmtMulai,
            'formatted_selesai' => $fmtSelesai,
            'formatted_tanggal' => $fmtTanggal,
        ];
    }
}
