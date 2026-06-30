<?php
require_once __DIR__ . '/includes/app.php';

$db = \Model\ActiveRecord::getDB();

$sql1 = "
CREATE TABLE IF NOT EXISTS tomas_inventario (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sucursal_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    estado ENUM('en_progreso', 'completada', 'cancelada') NOT NULL DEFAULT 'en_progreso',
    observacion VARCHAR(255) NULL,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$sql2 = "
CREATE TABLE IF NOT EXISTS tomas_inventario_detalle (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    toma_id INT NOT NULL,
    producto_id INT NOT NULL,
    stock_sistema DECIMAL(10,2) NOT NULL DEFAULT 0,
    conteo_fisico DECIMAL(10,2) NOT NULL DEFAULT 0,
    diferencia DECIMAL(10,2) NOT NULL DEFAULT 0,
    estado ENUM('pendiente', 'contado') NOT NULL DEFAULT 'pendiente',
    fecha_conteo DATETIME NULL,
    FOREIGN KEY (toma_id) REFERENCES tomas_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $db->exec($sql1);
    $db->exec($sql2);
    echo "Tablas tomas_inventario creadas correctamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
