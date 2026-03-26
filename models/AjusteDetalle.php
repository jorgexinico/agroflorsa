<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo AjusteDetalle — tabla `ajuste_detalle`
 */
class AjusteDetalle extends ActiveRecord {
    protected static $tabla      = 'ajuste_detalle';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','ajuste_id','producto_id','lote_id','cantidad'];

    public ?int   $id          = null;
    public ?int   $ajuste_id   = null;
    public ?int   $producto_id = null;
    public ?int   $lote_id     = null;
    public float  $cantidad    = 0;
}
