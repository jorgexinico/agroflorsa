<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Compra — tabla `compras`
 */
class Compra extends ActiveRecord {

    protected static $tabla      = 'compras';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','sucursal_id','proveedor_id','fecha','total','estado','observacion'
    ];

    public ?int    $id           = null;
    public ?int    $sucursal_id  = null;
    public ?int    $proveedor_id = null;
    public ?string $fecha        = null;
    public float   $total        = 0;
    public string  $estado       = 'recibida';
    public ?string $observacion  = null;

    // Joins
    public ?string $proveedor_nombre = null;
    public ?string $sucursal_nombre  = null;

    public static function allConDetalle(int $sucursal_id): array {
        return self::fetchRaw(
            "SELECT c.*, p.nombre AS proveedor_nombre, s.nombre AS sucursal_nombre
             FROM compras c
             LEFT JOIN proveedores p ON p.id = c.proveedor_id
             JOIN sucursales s ON s.id = c.sucursal_id
             WHERE c.sucursal_id = :suc
             ORDER BY c.fecha DESC",
            [':suc' => $sucursal_id]
        );
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->sucursal_id)) self::setAlerta('danger', 'Selecciona la sucursal');
        return static::$alertas;
    }
}
