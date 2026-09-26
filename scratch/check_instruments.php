<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SchemeMasterInstrument;

echo "--- MASTER INSTRUMENTS IN DATABASE ---\n";
foreach (SchemeMasterInstrument::with(['skema', 'questionBanks', 'productSpecifications'])->get() as $inst) {
    echo sprintf("ID: %-3d | Skema ID: %-3d (%-15s) | Code: %-8s | Title: %-30s | Soal: %-2d | Spec: %-2d\n",
        $inst->id,
        $inst->skema_id,
        $inst->skema?->kode_skema ?? 'N/A',
        $inst->instrument_code,
        substr($inst->title, 0, 30),
        $inst->questionBanks->count(),
        $inst->productSpecifications->count()
    );
}
