<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo CompraDetalle — tabla `compra_detalle`
 */
class CompraDetalle extends ActiveRecord {

    protected static $tabla      = 'compra_detalle';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','compra_id','producto_id','lote_id','cantidad','costo_unitario','subtotal','fecha_vencimiento'
    ];

    public ?int   $id            = null;
    public ?int   $compra_id     = null;
    public ?int   $producto_id   = null;
    public ?int   $lote_id       = null;
    public float  $cantidad      = 0;
    public float  $costo_unitario = 0;
    public float  $subtotal      = 0;
    public ?string $fecha_vencimiento = null;

    // Joins
    public ?string $producto_nombre    = null;
    public ?string $unidad_abreviatura = null;

    public static function getByCompra(int $compra_id): array {
        return self::fetchRaw(
            "SELECT cd.*, p.nombre AS producto_nombre, u.abreviatura AS unidad_abreviatura
             FROM compra_detalle cd
             JOIN productos p ON p.id = cd.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE cd.compra_id = :cid",
            [':cid' => $compra_id]
        );
    }
}
