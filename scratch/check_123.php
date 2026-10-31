<?php
$binlogExe = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqlbinlog.exe';
$cmd = "\"$binlogExe\" --start-position=413570 \"C:\\laragon\\data\\mysql-8.4\\binlog.000123\" | findstr /C:\"create table\" /C:\"seeder\" /C:\"insert\"";
$out = shell_exec($cmd);
echo substr($out ?? '', 0, 500);
