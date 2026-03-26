<?php
// Try local connection if base_db fails
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_USER'] = 'root'; // common default
$_ENV['DB_PASS'] = '';     // common default
$_ENV['DB_NAME'] = 'agroflorsa'; // from conversation history/user info

require 'includes/app.php';
$db = \Model\ActiveRecord::getDB();
$res = $db->query('SHOW TABLES');
while($row = $res->fetch(PDO::FETCH_NUM)) {
    $table = $row[0];
    echo "\nTable: $table\n";
    $res2 = $db->query("DESCRIBE `$table` ");
    while($row2 = $res2->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row2['Field']} ({$row2['Type']})\n";
    }
}
