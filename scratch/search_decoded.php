<?php
$f = fopen(__DIR__ . '/binlog_decoded_all.sql', 'r');
$found = [];
while (($line = fgets($f)) !== false) {
    if (preg_match('/Table_map: `([^`]+)`\.`([^`]+)`/', $line, $m)) {
        $found[$m[1]][$m[2]] = ($found[$m[1]][$m[2]] ?? 0) + 1;
    }
}
fclose($f);
print_r($found);
