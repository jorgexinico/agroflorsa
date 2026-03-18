<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Lote — tabla `lotes`
 */
class Lote extends ActiveRecord {

    protected static $tabla      = 'lotes';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','producto_id','codigo_lote','fecha_ingreso','fecha_vencimiento','costo_unitario','creado_en'
    ];

    public ?int    $id                = null;
    public ?int    $producto_id       = null;
    public ?string $codigo_lote       = null;
    public ?string $fecha_ingreso     = null;
    public ?string $fecha_vencimiento = null;
    public float   $costo_unitario    = 0;
    public ?string $creado_en         = null;

}
