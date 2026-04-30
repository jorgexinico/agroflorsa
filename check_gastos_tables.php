<?php
require 'includes/app.php';
use Model\ActiveRecord;

$db = ActiveRecord::getDB();

try {
    echo "Checking tables...\n";
    $tables = ['gastos', 'categorias_gastos'];
    foreach ($tables as $table) {
        $res = $db->query("SHOW TABLES LIKE '$table'");
        if ($res->rowCount() > 0) {
            echo "Table '$table' exists.\n";
        } else {
            echo "Table '$table' DOES NOT exist.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
