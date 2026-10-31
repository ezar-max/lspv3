<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$allSkemas = \App\Models\SkemaSertifikasi::all();
echo "Total Skemas: " . $allSkemas->count() . "\n";
foreach ($allSkemas as $sk) {
    $masters = \App\Models\SchemeMasterInstrument::where('skema_id', $sk->id)->get();
    echo "- Skema {$sk->id} ({$sk->kode_skema}): " . $masters->pluck('instrument_code')->implode(', ') . "\n";
}
