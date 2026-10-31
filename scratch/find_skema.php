<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$dir = 'C:\\laragon\\data\\mysql-8.4';

foreach (['binlog.000123', 'binlog.000122', 'binlog.000120', 'binlog.000115', 'binlog.000100'] as $b) {
    $f = "$dir/$b";
    if (!file_exists($f)) continue;
    $out = shell_exec("\"$binlogExe\" -v --base64-output=DECODE-ROWS \"$f\" | findstr /C:\"skema_sertifikasi\" /C:\"pengguna\"");
    echo "$b: " . substr(trim($out ?? ''), 0, 100) . "\n";
}
