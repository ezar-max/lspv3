<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$stmt = $pdo->query("SHOW BINLOG EVENTS IN 'binlog.000126'");
$lastPos = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['Event_type'] === 'Query') {
        $info = $row['Info'] ?? '';
        if (stripos($info, 'drop') !== false || stripos($info, 'truncate') !== false || stripos($info, 'create table') !== false) {
            echo "Pos: {$row['Pos']}, Info: " . substr($info, 0, 80) . "\n";
            if ($lastPos === 0) $lastPos = $row['Pos'];
        }
    }
}
echo "First drop pos: $lastPos\n";
