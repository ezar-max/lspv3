<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec("DROP DATABASE IF EXISTS test_restore");
$pdo->exec("CREATE DATABASE test_restore");

$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$mysqlExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe';

$cmd = "\"$binlogExe\" --rewrite-db=\"lspskielv3->test_restore\" --start-position=413570 \"C:\\laragon\\data\\mysql-8.4\\binlog.000123\" \"C:\\laragon\\data\\mysql-8.4\\binlog.000124\" \"C:\\laragon\\data\\mysql-8.4\\binlog.000125\"";
$cmdEnd = "\"$binlogExe\" --rewrite-db=\"lspskielv3->test_restore\" --stop-position=290724 \"C:\\laragon\\data\\mysql-8.4\\binlog.000126\"";

echo "Extracting binlogs with rewrite-db (no --database flag)...\n";
$sql = shell_exec($cmd);
$sql .= shell_exec($cmdEnd);

// Also replace text DDL references like `use lspskielv3;` and `lspskielv3.`
$sql = str_replace('use `lspskielv3`', 'use `test_restore`', $sql);
$sql = str_replace('use lspskielv3', 'use test_restore', $sql);
$sql = str_replace('`lspskielv3`', '`test_restore`', $sql);

file_put_contents(__DIR__ . '/test_restore.sql', $sql);
echo "SQL size: " . strlen($sql) . "\n";

echo "Importing into test_restore...\n";
exec("\"$mysqlExe\" -u root --force test_restore < \"" . __DIR__ . "/test_restore.sql\"", $out, $ret);
echo "Import exit code: $ret\n";

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
