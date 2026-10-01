<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$asesor = App\Models\Pengguna::where('peran', 'asesor')->first();
if ($asesor) {
    echo "ASESOR:" . PHP_EOL;
    echo "  id: {$asesor->id}" . PHP_EOL;
    echo "  nama: {$asesor->nama_lengkap}" . PHP_EOL;
    echo "  tanda_tangan: " . ($asesor->tanda_tangan ?: 'NULL') . PHP_EOL;
    echo "  tanda_tangan length: " . strlen($asesor->tanda_tangan ?? '') . PHP_EOL;
    echo "  contains svg: " . (Str::contains($asesor->tanda_tangan ?? '', 'svg') ? 'YES' : 'NO') . PHP_EOL;
    echo "  starts with data: " . (Str::startsWith($asesor->tanda_tangan ?? '', 'data:') ? 'YES' : 'NO') . PHP_EOL;
    echo "  starts with storage: " . (Str::startsWith($asesor->tanda_tangan ?? '', 'storage/') ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "NO ASESOR FOUND" . PHP_EOL;
}

echo PHP_EOL;

$admins = App\Models\Pengguna::whereIn('peran', ['admin', 'superadmin'])->get();
echo "ADMINS:" . PHP_EOL;
foreach ($admins as $ad) {
    echo "  id: {$ad->id} nama: {$ad->nama_lengkap}" . PHP_EOL;
    echo "    tanda_tangan: " . ($ad->tanda_tangan ?: 'NULL') . PHP_EOL;
    echo "    contains svg: " . (Str::contains($ad->tanda_tangan ?? '', 'svg') ? 'YES' : 'NO') . PHP_EOL;
}

echo PHP_EOL;

// Check MAPA01 records
$mapa01s = App\Models\Mapa01::all();
echo "MAPA01 RECORDS: " . $mapa01s->count() . PHP_EOL;
foreach ($mapa01s as $m) {
    echo "  id: {$m->id} pendaftaran_id: {$m->pendaftaran_id}" . PHP_EOL;
    echo "    tanda_tangan_asesor: " . ($m->tanda_tangan_asesor ?: 'NULL') . PHP_EOL;
    echo "    contains svg: " . (Str::contains($m->tanda_tangan_asesor ?? '', 'svg') ? 'YES' : 'NO') . PHP_EOL;
    $penyusun = $m->penyusun_validator;
    if (is_array($penyusun) || is_object($penyusun)) {
        $penyusun = json_encode($penyusun);
    }
    echo "    penyusun_validator (first 500): " . substr($penyusun ?? 'NULL', 0, 500) . PHP_EOL;
}
