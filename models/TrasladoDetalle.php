<?php
namespace Models;

use Model\ActiveRecord;

class TrasladoDetalle extends ActiveRecord {
    protected static $tabla      = 'traslado_detalle';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','traslado_id','producto_id','lote_id','cantidad','costo_unitario'];

    public ?int   $id             = null;
    public ?int   $traslado_id    = null;
    public ?int   $producto_id    = null;
    public ?int   $lote_id        = null;
    public float  $cantidad       = 0;
    public ?float $costo_unitario = null;

    public function __construct($args = []) {
        $this->id             = $args['id']             ?? null;
        $this->traslado_id    = $args['traslado_id']    ?? null;
        $this->producto_id    = $args['producto_id']    ?? null;
        $this->lote_id        = $args['lote_id']        ?? null;
        $this->cantidad       = $args['cantidad']       ?? 0;
        $this->costo_unitario = $args['costo_unitario'] ?? null;
    }

    public static function getByTraslado(int $id): array {
        return self::fetchRaw(
            "SELECT td.*, p.nombre AS producto_nombre, p.sku, u.abreviatura AS unidad 
             FROM traslado_detalle td
             JOIN productos p ON p.id = td.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE td.traslado_id = :id",
            [':id' => $id]
        );
    }
}
