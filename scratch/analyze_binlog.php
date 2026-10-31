<?php
$f = fopen(__DIR__ . '/binlog_decoded.sql', 'r');
$tables = [];
while (($line = fgets($f)) !== false) {
    if (preg_match('/### (INSERT INTO|UPDATE|DELETE FROM) `lspskielv3`\.`([^`]+)`/', $line, $m)) {
        $tables[$m[2]][$m[1]] = ($tables[$m[2]][$m[1]] ?? 0) + 1;
    }
}
fclose($f);
print_r($tables);
