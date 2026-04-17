<?php
require_once __DIR__ . '/includes/app.php';
use Model\ActiveRecord;

try {
    $db = ActiveRecord::getDB();
    $sql = "ALTER TABLE productos ADD COLUMN precio_compra DECIMAL(10,2) DEFAULT 0.00 AFTER precio_mayorista";
    $db->query($sql);
    echo "Columna 'precio_compra' agregada exitosamente a la tabla 'productos'.\n";
} catch (\Exception $e) {
    echo "Error o la columna ya existe: " . $e->getMessage() . "\n";
}
