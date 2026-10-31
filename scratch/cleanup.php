<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec('DROP DATABASE IF EXISTS test_restore');
echo "test_restore dropped\n";

@unlink(__DIR__ . '/test_restore.sql');
@unlink(__DIR__ . '/restore_test.sql');
@unlink(__DIR__ . '/main_restore.sql');
@unlink(__DIR__ . '/binlog_decoded.sql');
@unlink(__DIR__ . '/binlog_decoded_all.sql');
@unlink(__DIR__ . '/binlog_out.sql');
echo "Large scratch sql files cleaned up.\n";
