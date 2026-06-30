<?php
namespace Models;

use Model\ActiveRecord;

class TomaInventario extends ActiveRecord {

    protected static $tabla = 'tomas_inventario';
    protected static $idTabla = 'id';
    protected static $columnasDB = ['id', 'sucursal_id', 'usuario_id', 'fecha_inicio', 'fecha_fin', 'estado', 'observacion'];

    public function __construct(array $args = []) {
        $this->sincronizar($args);
    }

    public ?int $id = null;
    public ?int $sucursal_id = null;
    public ?int $usuario_id = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public string $estado = 'en_progreso';
    public ?string $observacion = null;

    public ?string $sucursal_nombre = null;
    public ?string $usuario_nombre = null;

    public static function getTomas(): array {
        return self::fetchRaw("
            SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre 
            FROM tomas_inventario t 
            JOIN sucursales s ON s.id = t.sucursal_id 
            JOIN usuarios u ON u.id = t.usuario_id 
            ORDER BY t.id DESC
        ");
    }

    public static function getTomasPorSucursal(int $sucursal_id): array {
        return self::fetchRaw("
            SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre 
            FROM tomas_inventario t 
            JOIN sucursales s ON s.id = t.sucursal_id 
            JOIN usuarios u ON u.id = t.usuario_id 
            WHERE t.sucursal_id = :sid
            ORDER BY t.id DESC
        ", [':sid' => $sucursal_id]);
    }
}
