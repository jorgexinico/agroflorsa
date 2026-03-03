<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo VentaDetalle — tabla `venta_detalle`
 */
class VentaDetalle extends ActiveRecord {

    protected static $tabla      = 'venta_detalle';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','venta_id','producto_id','lote_id','cantidad','precio_unitario','costo_unitario','subtotal'
    ];

    public ?int   $id             = null;
    public ?int   $venta_id       = null;
    public ?int   $producto_id    = null;
    public ?int   $lote_id        = null;
    public float  $cantidad       = 0;
    public float  $precio_unitario = 0;
    public ?float $costo_unitario  = null;
    public float  $subtotal       = 0;

    // Joins
    public ?string $producto_nombre    = null;
    public ?string $unidad_abreviatura = null;

    public static function getByVenta(int $venta_id): array {
        return self::fetchRaw(
            "SELECT vd.*, p.nombre AS producto_nombre, u.abreviatura AS unidad_abreviatura
             FROM venta_detalle vd
             JOIN productos p ON p.id = vd.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE vd.venta_id = :vid",
            [':vid' => $venta_id]
        );
    }
}
