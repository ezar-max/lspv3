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
     * Tanggal Uji dalam bentuk objek Carbon
     */
    public function getTanggalUjiCarbonAttribute(): ?\Carbon\Carbon
    {
        if (empty($this->tanggal_uji)) {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        return \Carbon\Carbon::parse($this->tanggal_uji, $tz);
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

        $carbonSelesai = \Carbon\Carbon::parse("{$tanggal} {$waktu}", $tz);

        // Jika waktu selesai <= waktu mulai (misal lintas hari/tengah malam: 23:00 - 00:00 atau 22:00 - 02:00),
        // maka waktu selesai berada di hari berikutnya (+1 hari)
        $carbonMulai = $this->waktu_mulai_carbon;
        if ($carbonMulai && $carbonSelesai->lessThanOrEqualTo($carbonMulai)) {
            $carbonSelesai->addDay();
        }

        return $carbonSelesai;
    }

    /**
     * Lokasi penyimpanan status override manual admin agar tidak tertimpa sinkronisasi otomatis.
     */
    protected static function manualOverrideFilePath(): string
    {
        return storage_path('app/jadwal_manual_overrides.json');
    }

    public static function getManualStatusOverrides(): array
    {
        $path = static::manualOverrideFilePath();
        if (!is_file($path)) {
            return [];
        }
        $data = json_decode((string) @file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    public static function hasManualStatusOverride($id): bool
    {
        if (!$id) {
            return false;
        }
        $overrides = static::getManualStatusOverrides();
        return isset($overrides[(string) $id]);
    }

    public static function setManualStatusOverride($id, string $status): void
    {
        if (!$id) {
            return;
        }
        $overrides = static::getManualStatusOverrides();
        $overrides[(string) $id] = [
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ];
        $path = static::manualOverrideFilePath();
        @file_put_contents($path, json_encode($overrides, JSON_PRETTY_PRINT));
    }

    public static function clearManualStatusOverride($id): void
    {
        if (!$id) {
            return;
        }
        $overrides = static::getManualStatusOverrides();
        if (isset($overrides[(string) $id])) {
            unset($overrides[(string) $id]);
            $path = static::manualOverrideFilePath();
            @file_put_contents($path, json_encode($overrides, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Sinkronisasi status seluruh jadwal secara real-time berdasarkan tanggal dan waktu WIB saat ini.
     * Aturan:
     * - Jadwal yang baru diubah manual oleh Admin dipertahankan status pilihannya
     * - 'dibatalkan' tetap 'dibatalkan'
     * - Waktu saat ini > waktu_selesai (+ grace period) -> otomatis 'selesai' (waktu habis)
     * - Waktu saat ini di antara waktu_mulai dan waktu_selesai -> otomatis 'berlangsung'
     * - Waktu saat ini < waktu_mulai -> otomatis 'terjadwal' (tidak boleh 'selesai' jika belum mulai)
     */
    public static function syncAllStatuses(): void
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = \Carbon\Carbon::now($tz);
        $yesterday = $now->copy()->subDay()->toDateString();
        $manualOverrideIds = array_map('intval', array_keys(static::getManualStatusOverrides()));

        // 1. Jadwal tanggal lampau (< kemarin) yang berstatus 'terjadwal' (dan bukan manual override) -> otomatis 'selesai'
        $pastQuery = static::where('status_jadwal', '=', 'terjadwal')
            ->where('tanggal_uji', '<', $yesterday);
        if (!empty($manualOverrideIds)) {
            $pastQuery->whereNotIn('id', $manualOverrideIds);
        }
        $pastQuery->update(['status_jadwal' => 'selesai']);

        // 2. Sinkronkan seluruh jadwal yang aktif/relevan (kemarin, hari ini, masa depan, atau yang belum selesai)
        $kandidatJadwal = static::where('status_jadwal', '!=', 'dibatalkan')
            ->where(function ($q) use ($yesterday) {
                $q->where('tanggal_uji', '>=', $yesterday)
                  ->orWhere('status_jadwal', '!=', 'selesai');
            })
            ->get();

        foreach ($kandidatJadwal as $jadwal) {
            $jadwal->syncRealtimeStatus(true);
        }
    }

    /**
     * Sinkronisasi status satu jadwal ini secara real-time.
     */
    public function syncRealtimeStatus(bool $save = true): string
    {
        if ($this->status_jadwal === 'dibatalkan') {
            return 'dibatalkan';
        }

        // Pertahankan status yang telah diatur secara manual oleh Admin/Super Admin
        if ($this->id && static::hasManualStatusOverride($this->id)) {
            return $this->status_jadwal ?? 'terjadwal';
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

        if ($now->isBefore($start)) {
            // Jadwal belum dimulai: jika statusnya tidak sengaja 'selesai', wajib dikoreksi menjadi 'terjadwal'
            if ($this->status_jadwal === 'selesai' || empty($this->status_jadwal)) {
                $newStatus = 'terjadwal';
            }
        } elseif ($now->isAfter($endWithGrace)) {
            // Waktu telah melewati batas toleransi selesai (10 menit) -> otomatis 'selesai'
            $newStatus = 'selesai';
        } elseif ($this->status_jadwal === 'terjadwal' && $now->between($start, $endWithGrace)) {
            // Masuk dalam rentang pelaksanaan -> otomatis 'berlangsung'
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

        if ($this->status_jadwal === 'terjadwal' && $this->id && static::hasManualStatusOverride($this->id)) {
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

        if ($this->status_jadwal === 'dibatalkan' || $this->status_jadwal === 'selesai') {
            return false;
        }

        if ($this->status_jadwal === 'terjadwal' && $this->id && static::hasManualStatusOverride($this->id)) {
            return true;
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
        if ($this->status_jadwal === 'berlangsung') {
            return false;
        }

        if ($this->status_jadwal === 'terjadwal' && $this->id && static::hasManualStatusOverride($this->id)) {
            return false;
        }

        if ($this->status_jadwal === 'selesai' && $this->id && static::hasManualStatusOverride($this->id)) {
            return true;
        }

        $start = $this->waktu_mulai_carbon;
        $now = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'));

        // Jika waktu mulai masih di masa depan, jadwal belum dimulai, bukan selesai
        if ($start && $now->isBefore($start)) {
            return false;
        }

        if ($this->status_jadwal === 'selesai') {
            return true;
        }

        $end = $this->waktu_selesai_carbon;
        if (!$end) {
            return false;
        }

        if ($isPostRequest) {
            $end = $end->copy()->addMinutes($gracePeriodMinutes);
        }

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

        if ($this->status_jadwal === 'terjadwal' && $this->id && static::hasManualStatusOverride($this->id)) {
            return [
                'status' => 'belum_mulai',
                'is_active' => false,
                'is_before' => true,
                'is_after' => false,
                'is_manual' => true,
                'pesan' => "Ruang uji belum dibuka (Status: Terjadwal). Sesi asesmen dijadwalkan pada {$fmtTanggal} pukul {$fmtMulai}.",
                'mulai_at' => $start,
                'selesai_at' => $end,
                'formatted_mulai' => $fmtMulai,
                'formatted_selesai' => $fmtSelesai,
                'formatted_tanggal' => $fmtTanggal,
            ];
        }

        // Prioritaskan cek waktu belum dimulai sebelum cek status selesai
        if ($start && $now->isBefore($start) && $this->status_jadwal !== 'selesai') {
            $diffHuman = $start->diffForHumans($now, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]);
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
