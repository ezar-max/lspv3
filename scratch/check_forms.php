<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (glob(__DIR__ . '/../resources/views/formulir/fr-*.blade.php') as $file) {
    $content = file_get_contents($file);
    $name = basename($file);
    $hasSkema = strpos($content, 'skema') !== false;
    $hasUnit = strpos($content, 'unitKompetensi') !== false;
    $hasElemen = strpos($content, 'elemenKompetensi') !== false;
    $hasKuk = strpos($content, 'kriteriaUnjukKerja') !== false;
    $size = strlen($content);
    echo sprintf("%-25s | Size: %8d | Skema: %-3s | Unit: %-3s | Elemen: %-3s | KUK: %-3s\n",
        $name, $size,
        $hasSkema ? 'YES' : 'NO',
        $hasUnit ? 'YES' : 'NO',
        $hasElemen ? 'YES' : 'NO',
        $hasKuk ? 'YES' : 'NO'
    );
}
