<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ([76 => 'Asesi', 77 => 'Asesor'] as $userId => $role) {
    $user = \App\Models\Pengguna::find($userId);
    $request = \Illuminate\Http\Request::create('/formulir/ia-01/19?skema_id=19&pendaftaran_id=19', 'GET');
    $request->setUserResolver(fn() => $user);
    \Illuminate\Support\Facades\Auth::login($user);

    $session = $app->make('session')->driver();
    $session->start();
    $session->put('_token', 'test');
    $session->put($app->make('auth')->guard()->getName(), $user->id);
    $request->setLaravelSession($session);

    $httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $httpKernel->handle($request);
    echo "=== Testing $role (ID: $userId) ===\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    echo "HTML Length: " . strlen($content) . "\n";
    echo "Contains 'FR.IA.01': " . (str_contains($content, 'FR.IA.01') ? 'YES' : 'NO') . "\n";
    echo "Contains '403': " . (str_contains($content, '403') ? 'YES' : 'NO') . "\n";
    echo "Contains 'Ceklis Observasi': " . (str_contains($content, 'Ceklis Observasi') ? 'YES' : 'NO') . "\n";
}

// Also test Asesi Dashboard
$user = \App\Models\Pengguna::find(76);
$request = \Illuminate\Http\Request::create('/asesi/dashboard', 'GET');
$request->setUserResolver(fn() => $user);
\Illuminate\Support\Facades\Auth::login($user);
$session = $app->make('session')->driver();
$session->start();
$session->put('_token', 'test');
$session->put($app->make('auth')->guard()->getName(), $user->id);
$request->setLaravelSession($session);
$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);
echo "\n=== Testing Asesi Dashboard ===\n";
echo "Status Code: " . $response->getStatusCode() . "\n";
$content = $response->getContent();
echo "Contains 'Lihat FR.IA.01': " . (str_contains($content, 'Lihat FR.IA.01') ? 'YES' : 'NO') . "\n";
