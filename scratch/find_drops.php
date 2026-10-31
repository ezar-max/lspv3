<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$dir = 'C:\\laragon\\data\\mysql-8.4';
$files = glob("$dir/binlog.0001*");
sort($files);
foreach ($files as $f) {
    $base = basename($f);
    $out = shell_exec("\"$binlogExe\" \"$f\" | findstr /C:\"DROP TABLE\"");
    if (!empty(trim($out ?? ''))) {
        echo "$base has DROP TABLE: " . substr(trim($out), 0, 80) . "\n";
    }
}
