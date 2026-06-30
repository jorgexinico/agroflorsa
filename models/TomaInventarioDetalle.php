<?php
namespace Models;

use Model\ActiveRecord;

class TomaInventarioDetalle extends ActiveRecord {

    protected static $tabla = 'tomas_inventario_detalle';
    protected static $idTabla = 'id';
    protected static $columnasDB = ['id', 'toma_id', 'producto_id', 'stock_sistema', 'conteo_fisico', 'diferencia', 'estado', 'fecha_conteo'];

    public function __construct(array $args = []) {
        $this->sincronizar($args);
    }

    public ?int $id = null;
    public ?int $toma_id = null;
    public ?int $producto_id = null;
    public float $stock_sistema = 0;
    public float $conteo_fisico = 0;
    public float $diferencia = 0;
    public string $estado = 'pendiente';
    public ?string $fecha_conteo = null;

    public ?string $producto_nombre = null;
    public ?string $producto_sku = null;
    public ?string $unidad_nombre = null;

    public static function getByToma(int $toma_id): array {
        return self::fetchRaw("
            SELECT d.*, p.nombre AS producto_nombre, p.sku AS producto_sku, u.nombre AS unidad_nombre 
            FROM tomas_inventario_detalle d
            JOIN productos p ON p.id = d.producto_id
            LEFT JOIN unidades_medida u ON u.id = p.unidad_id
            WHERE d.toma_id = :tid
            ORDER BY p.nombre ASC
        ", [':tid' => $toma_id]);
    }
}
