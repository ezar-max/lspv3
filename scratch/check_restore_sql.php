<?php
$f = fopen(__DIR__ . '/restore_test.sql', 'r');
$found = [];
while (($line = fgets($f)) !== false) {
    if (stripos($line, 'pendaftaran_asesi') !== false || stripos($line, 'skema_sertifikasi') !== false || stripos($line, 'pengguna') !== false) {
        $found[] = substr(trim($line), 0, 100);
        if (count($found) > 30) break;
    }
}
fclose($f);
print_r($found);
