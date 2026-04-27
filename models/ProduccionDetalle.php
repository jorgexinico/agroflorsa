<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo ProduccionDetalle — tabla `produccion_detalle`
 */
class ProduccionDetalle extends ActiveRecord {

    protected static $tabla      = 'produccion_detalle';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','produccion_id','producto_id','lote_id','cantidad','costo_unitario','subtotal'
    ];

    public ?int   $id            = null;
    public ?int   $produccion_id = null;
    public ?int   $producto_id   = null;
    public ?int   $lote_id       = null;
    public float  $cantidad      = 0;
    public float  $costo_unitario = 0;
    public float  $subtotal      = 0;

    // Joins
    public ?string $producto_nombre    = null;
    public ?string $unidad_abreviatura = null;
    public ?string $lote_codigo        = null;

    public static function getByProduccion(int $produccion_id): array {
        return self::fetchRaw(
            "SELECT pd.*, p.nombre AS producto_nombre, u.abreviatura AS unidad_abreviatura, l.codigo_lote AS lote_codigo
             FROM produccion_detalle pd
             JOIN productos p ON p.id = pd.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             LEFT JOIN lotes l ON l.id = pd.lote_id
             WHERE pd.produccion_id = :pid",
            [':pid' => $produccion_id]
        );
    }
}
