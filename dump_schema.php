<?php
require __DIR__ . '/includes/app.php';
$db = \Model\ActiveRecord::getDB();

echo "Tablas:\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (strpos($t, 'precio') !== false || strpos($t, 'product') !== false) {
        echo "Tabla $t:\n";
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        print_r($cols);
    }
}
