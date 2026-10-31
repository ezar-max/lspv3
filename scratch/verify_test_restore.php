<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test_restore', 'root', '');
$tables = ['pendaftaran_asesi', 'skema_sertifikasi', 'unit_kompetensi', 'elemen_kompetensi', 'kriteria_unjuk_kerja', 'pengguna', 'profil_asesi', 'mapa_01', 'mapa_02', 'jadwal_asesmen', 'scheme_master_instruments'];
foreach ($tables as $t) {
    echo "$t: " . $pdo->query("SELECT count(*) FROM `$t`")->fetchColumn() . "\n";
}

$p19 = $pdo->query("SELECT * FROM pendaftaran_asesi WHERE id = 19")->fetch(PDO::FETCH_ASSOC);
echo "Pendaftaran 19:\n";
print_r($p19);

$users = $pdo->query("SELECT id, nama_lengkap, email, peran FROM pengguna")->fetchAll(PDO::FETCH_ASSOC);
echo "Users:\n";
print_r($users);
