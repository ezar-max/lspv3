<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$dir = 'C:\\laragon\\data\\mysql-8.4';

for ($i = 120; $i <= 126; $i++) {
    $num = str_pad($i, 6, '0', STR_PAD_LEFT);
    $f = "$dir/binlog.$num";
    if (!file_exists($f)) continue;
    $out = shell_exec("\"$binlogExe\" -v --base64-output=DECODE-ROWS \"$f\" | findstr /C:\"INSERT INTO `lspskielv3`.`pendaftaran_asesi`\" /C:\"INSERT INTO `lspskielv3`.`skema_sertifikasi`\"");
    if (!empty(trim($out ?? ''))) {
        echo "binlog.$num has inserts:\n$out\n";
    }
}
