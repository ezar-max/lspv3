<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec("DROP DATABASE IF EXISTS lspskielv3");
$pdo->exec("CREATE DATABASE lspskielv3");

$mysqldump = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';
$mysql = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe';

echo "Dumping test_restore to lspskielv3...\n";
exec("\"$mysqldump\" -u root test_restore > \"" . __DIR__ . "/main_restore.sql\"");
exec("\"$mysql\" -u root lspskielv3 < \"" . __DIR__ . "/main_restore.sql\"");

$pdoMain = new PDO('mysql:host=127.0.0.1;port=3306;dbname=lspskielv3', 'root', '');
$pCount = $pdoMain->query("SELECT count(*) FROM pendaftaran_asesi")->fetchColumn();
$sCount = $pdoMain->query("SELECT count(*) FROM skema_sertifikasi")->fetchColumn();
$uCount = $pdoMain->query("SELECT count(*) FROM pengguna")->fetchColumn();
$iCount = $pdoMain->query("SELECT count(*) FROM scheme_master_instruments")->fetchColumn();

echo "lspskielv3 restored:\n";
echo "Pendaftaran: $pCount, Skema: $sCount, Pengguna: $uCount, Instruments: $iCount\n";
