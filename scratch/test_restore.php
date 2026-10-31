<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec("DROP DATABASE IF EXISTS test_restore");
$pdo->exec("CREATE DATABASE test_restore");

$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$mysqlExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe';

$cmd1 = "\"$binlogExe\" --start-position=413570 \"C:\\laragon\\data\\mysql-8.4\\binlog.000123\"";
$cmd2 = "\"$binlogExe\" \"C:\\laragon\\data\\mysql-8.4\\binlog.000124\"";
$cmd3 = "\"$binlogExe\" \"C:\\laragon\\data\\mysql-8.4\\binlog.000125\"";
$cmd4 = "\"$binlogExe\" --stop-position=290724 \"C:\\laragon\\data\\mysql-8.4\\binlog.000126\"";

echo "Extracting binlogs...\n";
$sql = shell_exec("$cmd1");
$sql .= shell_exec("$cmd2");
$sql .= shell_exec("$cmd3");
$sql .= shell_exec("$cmd4");

echo "Total SQL size: " . strlen($sql) . " bytes\n";
$sql = str_replace('`lspskielv3`', '`test_restore`', $sql);
$sql = str_replace('use `lspskielv3`', 'use `test_restore`', $sql);
$sql = str_replace('use lspskielv3', 'use test_restore', $sql);
$sql = preg_replace('/DROP TABLE (?!IF EXISTS)/i', 'DROP TABLE IF EXISTS ', $sql);

file_put_contents(__DIR__ . '/restore_test.sql', $sql);
echo "Importing into test_restore...\n";
exec("\"$mysqlExe\" -u root --force test_restore < \"" . __DIR__ . "/restore_test.sql\"", $out, $ret);
echo "Import return code: $ret\n";

$pdo2 = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test_restore', 'root', '');
$skemaCount = $pdo2->query("SELECT count(*) FROM skema_sertifikasi")->fetchColumn();
$pendaftaranCount = $pdo2->query("SELECT count(*) FROM pendaftaran_asesi")->fetchColumn();
$userCount = $pdo2->query("SELECT count(*) FROM pengguna")->fetchColumn();
$instCount = $pdo2->query("SELECT count(*) FROM scheme_master_instruments")->fetchColumn();

echo "test_restore results:\n";
echo "Skema count: $skemaCount\n";
echo "Pendaftaran count: $pendaftaranCount\n";
echo "User count: $userCount\n";
echo "Scheme master instruments count: $instCount\n";

$p19 = $pdo2->query("SELECT * FROM pendaftaran_asesi WHERE id = 19")->fetch(PDO::FETCH_ASSOC);
echo "Pendaftaran 19 in test_restore: " . ($p19 ? "Found! (status: {$p19['status_pendaftaran']})" : "Not found") . "\n";

$skemas = $pdo2->query("SELECT id, kode_skema, nama_skema FROM skema_sertifikasi")->fetchAll(PDO::FETCH_ASSOC);
foreach ($skemas as $sk) {
    echo "  Skema {$sk['id']}: {$sk['kode_skema']} - {$sk['nama_skema']}\n";
}
$insts = $pdo2->query("SELECT id, skema_id, instrument_code, title FROM scheme_master_instruments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($insts as $in) {
    echo "  Instrument {$in['id']}: Skema {$in['skema_id']} - {$in['instrument_code']} - {$in['title']}\n";
}
