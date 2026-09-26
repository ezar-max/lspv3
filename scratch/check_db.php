<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SkemaSertifikasi;

echo "--- DAFTAR SKEMA & UNIT KOMPETENSI ---\n";
foreach (SkemaSertifikasi::with(['unitKompetensi.elemenKompetensi.kriteriaUnjukKerja'])->get() as $s) {
    $totalElemen = 0;
    $totalKuk = 0;
    foreach ($s->unitKompetensi as $u) {
        $totalElemen += $u->elemenKompetensi->count();
        foreach ($u->elemenKompetensi as $e) {
            $totalKuk += $e->kriteriaUnjukKerja->count();
        }
    }
    echo sprintf("ID: %-3d | %-20s | %-40s | Units: %-2d | Elemen: %-2d | KUK: %-3d\n",
        $s->id, $s->kode_skema, substr($s->nama_skema, 0, 40),
        $s->unitKompetensi->count(), $totalElemen, $totalKuk
    );
}

echo "\n--- DAFTAR PENDAFTARAN ASESI ---\n";
foreach (\App\Models\PendaftaranAsesi::with('skema')->take(10)->get() as $p) {
    echo sprintf("Pendaftaran ID: %-3d | Asesi ID: %-3d | Skema ID: %-3d (%s)\n",
        $p->id, $p->asesi_id, $p->skema_id, $p->skema?->nama_skema ?? 'Tanpa Skema'
    );
}
