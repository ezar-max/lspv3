<?php
$content = file_get_contents(__DIR__ . '/binlog_decoded_all.sql');
$utf8 = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
preg_match_all('/Table_map: `lspskielv3`\.`([^`]+)`/', $utf8, $m);
print_r(array_count_values($m[1]));
