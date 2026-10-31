<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$cmd = "\"$binlogExe\" -v --base64-output=DECODE-ROWS \"C:\\laragon\\data\\mysql-8.4\\binlog.000126\"";
$handle = popen($cmd, 'r');
$pos = 0;
while (($line = fgets($handle)) !== false) {
    if (preg_match('/# at (\d+)/', $line, $m)) {
        $pos = $m[1];
    }
    if (stripos($line, 'pendaftaran_asesi') !== false || stripos($line, 'skema_sertifikasi') !== false) {
        echo "Pos $pos: " . trim($line) . "\n";
    }
}
pclose($handle);
