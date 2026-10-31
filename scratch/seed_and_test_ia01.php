<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$skemas = \App\Models\SkemaSertifikasi::with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->get();
foreach ($skemas as $skema) {
    $existing = \App\Models\SchemeMasterInstrument::where('skema_id', $skema->id)
        ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_01'))
        ->first();

    $standards = [];
    foreach ($skema->unitKompetensi as $unit) {
        foreach ($unit->elemenKompetensi as $elemen) {
            foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                $standards[$kuk->id] = 'SOP dan standar industri/tempat kerja yang berlaku untuk ' . $kuk->pernyataan_kuk;
            }
        }
    }

    if (!$existing) {
        $inst = \App\Models\SchemeMasterInstrument::create([
            'skema_id' => $skema->id,
            'instrument_code' => 'ia_01',
            'title' => 'Ceklis Observasi Aktivitas',
            'is_active' => true,
            'additional_metadata' => [
                'is_saved' => true,
                'lifecycle_status' => 'published',
                'published_version' => 1,
                'published_at' => now()->toIso8601String(),
                'default_standard' => 'Standar Industri / Tempat Kerja yang berlaku',
                'published_standards' => $standards,
                'standar_industri' => $standards,
            ],
        ]);
        echo "Created published ia_01 for Skema {$skema->id} ({$skema->kode_skema})\n";
    } else {
        $meta = $existing->additional_metadata ?? [];
        if (empty($meta['published_version']) || ($meta['lifecycle_status'] ?? '') !== 'published') {
            $meta['is_saved'] = true;
            $meta['lifecycle_status'] = 'published';
            $meta['published_version'] = 1;
            $meta['published_at'] = now()->toIso8601String();
            $meta['default_standard'] = 'Standar Industri / Tempat Kerja yang berlaku';
            $meta['published_standards'] = $standards;
            $meta['standar_industri'] = $standards;
            $existing->additional_metadata = $meta;
            $existing->is_active = true;
            $existing->save();
            echo "Updated ia_01 for Skema {$skema->id} ({$skema->kode_skema}) to published\n";
        } else {
            echo "Skema {$skema->id} already has published ia_01\n";
        }
    }
}

// Now test loadAuthorizedRegistration for asesi 76 and pendaftaran 19
$service = app(\App\Services\FrIa01Service::class);
$asesi = \App\Models\Pengguna::find(76);
$asesor = \App\Models\Pengguna::find(77);

echo "\nTesting loadAuthorizedRegistration for Asesi 76 on Pendaftaran 19:\n";
try {
    $p = $service->loadAuthorizedRegistration(19, $asesi);
    echo "SUCCESS! Loaded Pendaftaran {$p->id} for Asesi {$asesi->nama_lengkap}\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTesting loadAuthorizedRegistration for Asesor 77 on Pendaftaran 19:\n";
try {
    $p = $service->loadAuthorizedRegistration(19, $asesor);
    echo "SUCCESS! Loaded Pendaftaran {$p->id} for Asesor {$asesor->nama_lengkap}\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
