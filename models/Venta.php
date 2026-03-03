<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Venta — tabla `ventas`
 */
class Venta extends ActiveRecord {

    protected static $tabla      = 'ventas';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','sucursal_id','turno_id','cliente_id','fecha','tipo_pago','total','estado','observacion'
    ];

    public ?int    $id           = null;
    public ?int    $sucursal_id  = null;
    public ?int    $turno_id     = null;
    public ?int    $cliente_id   = null;
    public ?string $fecha        = null;
    public string  $tipo_pago    = 'contado';
    public float   $total        = 0;
    public string  $estado       = 'emitida';
    public ?string $observacion  = null;

    // Joins
    public ?string $cliente_nombre  = null;
    public ?string $sucursal_nombre = null;

    public static function getByTurno(int $turno_id): array {
        return self::fetchRaw(
            "SELECT v.*, c.nombre AS cliente_nombre
             FROM ventas v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE v.turno_id = :tid AND v.estado != 'anulada'
             ORDER BY v.fecha DESC",
            [':tid' => $turno_id]
        );
    }

    public static function getBySucursa(int $sucursal_id, string $fecha = ''): array {
        $where = "WHERE v.sucursal_id = :suc AND v.estado != 'anulada'";
        $params = [':suc' => $sucursal_id];
        if ($fecha) {
            $where  .= " AND DATE(v.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }
        return self::fetchRaw(
            "SELECT v.*, c.nombre AS cliente_nombre
             FROM ventas v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             $where ORDER BY v.fecha DESC",
            $params
        );
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->turno_id))   self::setAlerta('danger', 'No hay turno abierto');
        return static::$alertas;
    }
}
