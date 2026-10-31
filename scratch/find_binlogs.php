<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$dir = 'C:\\laragon\\data\\mysql-8.4';
$files = glob("$dir/binlog.000*");
sort($files);
foreach (array_reverse($files) as $f) {
    $base = basename($f);
    $cmd = "\"$binlogExe\" --database=lspskielv3 \"$f\" | findstr /C:\"pendaftaran_asesi\" /C:\"skema_sertifikasi\"";
    $out = shell_exec($cmd);
    if (!empty(trim($out))) {
        echo "$base has matches!\n";
    }
}
