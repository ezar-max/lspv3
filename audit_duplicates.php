<?php
// READ-ONLY Duplicate Data Audit Script
// Does NOT modify any data

echo "=== EXISTING DUPLICATE DATA AUDIT ===" . PHP_EOL;
echo "Generated: " . date('Y-m-d H:i:s') . PHP_EOL;
echo str_repeat("=", 60) . PHP_EOL;

// 1. ia_penilaian
echo PHP_EOL . "## 1. ia_penilaian (pendaftaran_id, kode_formulir)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$dupes = DB::select('
    SELECT pendaftaran_id, kode_formulir, COUNT(*) as jumlah
    FROM ia_penilaian
    GROUP BY pendaftaran_id, kode_formulir
    HAVING COUNT(*) > 1
');

if (empty($dupes)) {
    echo "NO DUPLICATES FOUND" . PHP_EOL;
} else {
    $totalAffected = 0;
    echo count($dupes) . " duplicate group(s) found:" . PHP_EOL;
    foreach ($dupes as $d) {
        echo PHP_EOL . "  KEY: pendaftaran_id={$d->pendaftaran_id}, kode_formulir={$d->kode_formulir} (count={$d->jumlah})" . PHP_EOL;
        $totalAffected += $d->jumlah;
        $rows = DB::select('
            SELECT id, user_id, role, status, rekomendasi, created_at, updated_at
            FROM ia_penilaian
            WHERE pendaftaran_id = ? AND kode_formulir = ?
            ORDER BY id
        ', [$d->pendaftaran_id, $d->kode_formulir]);
        foreach ($rows as $r) {
            echo "    id={$r->id} user_id={$r->user_id} role={$r->role} status={$r->status} rekom={$r->rekomendasi} created={$r->created_at} updated={$r->updated_at}" . PHP_EOL;
        }
    }
    echo PHP_EOL . "Total affected rows: {$totalAffected}" . PHP_EOL;
}

// 2. penilaian_asesmen
echo PHP_EOL . "## 2. penilaian_asesmen (pendaftaran_id, unit_id)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$dupes2 = DB::select('
    SELECT pendaftaran_id, unit_id, COUNT(*) as jumlah
    FROM penilaian_asesmen
    GROUP BY pendaftaran_id, unit_id
    HAVING COUNT(*) > 1
');

if (empty($dupes2)) {
    echo "NO DUPLICATES FOUND" . PHP_EOL;
} else {
    $totalAffected2 = 0;
    echo count($dupes2) . " duplicate group(s) found:" . PHP_EOL;
    foreach ($dupes2 as $d) {
        echo PHP_EOL . "  KEY: pendaftaran_id={$d->pendaftaran_id}, unit_id={$d->unit_id} (count={$d->jumlah})" . PHP_EOL;
        $totalAffected2 += $d->jumlah;
        $rows = DB::select('
            SELECT id, asesor_id, nilai_kompetensi, catatan_asesor, created_at, updated_at
            FROM penilaian_asesmen
            WHERE pendaftaran_id = ? AND unit_id = ?
            ORDER BY id
        ', [$d->pendaftaran_id, $d->unit_id]);
        foreach ($rows as $r) {
            $catatan = $r->catatan_asesor ? substr($r->catatan_asesor, 0, 50) : 'null';
            echo "    id={$r->id} asesor_id={$r->asesor_id} nilai={$r->nilai_kompetensi} catatan=\"{$catatan}\" created={$r->created_at} updated={$r->updated_at}" . PHP_EOL;
        }
    }
    echo PHP_EOL . "Total affected rows: {$totalAffected2}" . PHP_EOL;
}

// 3. dokumen_asesi
echo PHP_EOL . "## 3. dokumen_asesi (pendaftaran_id, jenis_dokumen)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$dupes3 = DB::select('
    SELECT pendaftaran_id, jenis_dokumen, COUNT(*) as jumlah
    FROM dokumen_asesi
    GROUP BY pendaftaran_id, jenis_dokumen
    HAVING COUNT(*) > 1
');

if (empty($dupes3)) {
    echo "NO DUPLICATES FOUND" . PHP_EOL;
} else {
    $totalAffected3 = 0;
    echo count($dupes3) . " duplicate group(s) found:" . PHP_EOL;
    foreach ($dupes3 as $d) {
        echo PHP_EOL . "  KEY: pendaftaran_id={$d->pendaftaran_id}, jenis_dokumen=\"{$d->jenis_dokumen}\" (count={$d->jumlah})" . PHP_EOL;
        $totalAffected3 += $d->jumlah;
        $rows = DB::select('
            SELECT id, nama_dokumen, file_path, status_verifikasi, created_at, updated_at
            FROM dokumen_asesi
            WHERE pendaftaran_id = ? AND jenis_dokumen = ?
            ORDER BY id
        ', [$d->pendaftaran_id, $d->jenis_dokumen]);
        foreach ($rows as $r) {
            $fileExists = '-';
            if ($r->file_path) {
                $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], '', $r->file_path), '/');
                $fullPath = storage_path('app/public/' . $cleanPath);
                $fileExists = file_exists($fullPath) ? 'EXISTS' : 'MISSING';
            }
            echo "    id={$r->id} file=\"{$r->file_path}\" disk={$fileExists} status={$r->status_verifikasi} created={$r->created_at} updated={$r->updated_at}" . PHP_EOL;
        }
    }
    echo PHP_EOL . "Total affected rows: {$totalAffected3}" . PHP_EOL;
}

// 4. berita_acara
echo PHP_EOL . "## 4. berita_acara (jadwal_id)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$dupes4 = DB::select('
    SELECT jadwal_id, COUNT(*) as jumlah
    FROM berita_acara
    GROUP BY jadwal_id
    HAVING COUNT(*) > 1
');

if (empty($dupes4)) {
    echo "NO DUPLICATES FOUND" . PHP_EOL;
} else {
    $totalAffected4 = 0;
    echo count($dupes4) . " duplicate group(s) found:" . PHP_EOL;
    foreach ($dupes4 as $d) {
        echo PHP_EOL . "  KEY: jadwal_id={$d->jadwal_id} (count={$d->jumlah})" . PHP_EOL;
        $totalAffected4 += $d->jumlah;
        $rows = DB::select('
            SELECT id, nomor_berita_acara, tanggal_pelaksanaan, jumlah_peserta, jumlah_kompeten, jumlah_belum_kompeten, created_at, updated_at
            FROM berita_acara
            WHERE jadwal_id = ?
            ORDER BY id
        ', [$d->jadwal_id]);
        foreach ($rows as $r) {
            echo "    id={$r->id} nomor=\"{$r->nomor_berita_acara}\" tgl={$r->tanggal_pelaksanaan} peserta={$r->jumlah_peserta} K={$r->jumlah_kompeten} BK={$r->jumlah_belum_kompeten} created={$r->created_at} updated={$r->updated_at}" . PHP_EOL;
        }
    }
    echo PHP_EOL . "Total affected rows: {$totalAffected4}" . PHP_EOL;
}

// 5. profil_asesi
echo PHP_EOL . "## 5. profil_asesi (pengguna_id)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

if (!Schema::hasTable('profil_asesi')) {
    echo "TABLE DOES NOT EXIST" . PHP_EOL;
} else {
    $dupes5 = DB::select('
        SELECT pengguna_id, COUNT(*) as jumlah
        FROM profil_asesi
        GROUP BY pengguna_id
        HAVING COUNT(*) > 1
    ');

    if (empty($dupes5)) {
        echo "NO DUPLICATES FOUND" . PHP_EOL;
    } else {
        $totalAffected5 = 0;
        echo count($dupes5) . " duplicate group(s) found:" . PHP_EOL;
        foreach ($dupes5 as $d) {
            echo PHP_EOL . "  KEY: pengguna_id={$d->pengguna_id} (count={$d->jumlah})" . PHP_EOL;
            $totalAffected5 += $d->jumlah;
            $rows = DB::select('
                SELECT id, created_at, updated_at
                FROM profil_asesi
                WHERE pengguna_id = ?
                ORDER BY id
            ', [$d->pengguna_id]);
            foreach ($rows as $r) {
                echo "    id={$r->id} created={$r->created_at} updated={$r->updated_at}" . PHP_EOL;
            }
        }
        echo PHP_EOL . "Total affected rows: {$totalAffected5}" . PHP_EOL;
    }
}

// 6. Summary stats
echo PHP_EOL . "## TABLE ROW COUNTS" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$tables = ['ia_penilaian', 'penilaian_asesmen', 'dokumen_asesi', 'berita_acara', 'profil_asesi'];
foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        $count = DB::table($t)->count();
        echo "  {$t}: {$count} rows" . PHP_EOL;
    } else {
        echo "  {$t}: TABLE NOT FOUND" . PHP_EOL;
    }
}

// 7. Orphan file audit for dokumen_asesi
echo PHP_EOL . "## 6. FILE INTEGRITY AUDIT (dokumen_asesi)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$allDocs = DB::select('SELECT id, pendaftaran_id, jenis_dokumen, file_path FROM dokumen_asesi WHERE file_path IS NOT NULL AND file_path != ""');

$active = 0; $orphan = 0; $missing = 0; $ambiguous = 0;
$missingList = [];
$orphanList = [];

foreach ($allDocs as $doc) {
    $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], '', $doc->file_path), '/');
    $fullPath = storage_path('app/public/' . $cleanPath);

    if (file_exists($fullPath)) {
        $active++;
    } else {
        $missing++;
        if (count($missingList) < 20) {
            $missingList[] = "  id={$doc->id} pend={$doc->pendaftaran_id} jenis=\"{$doc->jenis_dokumen}\" path=\"{$doc->file_path}\"";
        }
    }
}

// Check for orphan physical files in dokumen-asesi directory
$storageDir = storage_path('app/public/dokumen-asesi');
if (is_dir($storageDir)) {
    $physicalFiles = glob($storageDir . '/*');
    $dbPaths = array_map(function($d) {
        return ltrim(str_replace(['/storage/', 'storage/'], '', $d->file_path), '/');
    }, $allDocs);

    foreach ($physicalFiles as $pf) {
        $relativePath = 'dokumen-asesi/' . basename($pf);
        if (!in_array($relativePath, $dbPaths)) {
            $orphan++;
            if (count($orphanList) < 20) {
                $orphanList[] = "  {$relativePath} (" . filesize($pf) . " bytes)";
            }
        }
    }
}

echo "Active (DB path → file exists): {$active}" . PHP_EOL;
echo "Missing (DB path → file NOT exists): {$missing}" . PHP_EOL;
echo "Orphan (file exists → NOT in DB): {$orphan}" . PHP_EOL;
echo "Ambiguous: {$ambiguous}" . PHP_EOL;

if (!empty($missingList)) {
    echo PHP_EOL . "Missing files (first 20):" . PHP_EOL;
    foreach ($missingList as $m) echo $m . PHP_EOL;
}
if (!empty($orphanList)) {
    echo PHP_EOL . "Orphan files (first 20):" . PHP_EOL;
    foreach ($orphanList as $o) echo $o . PHP_EOL;
}

echo PHP_EOL . "=== AUDIT COMPLETE ===" . PHP_EOL;
