<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo PagoVenta — tabla `pagos_venta`
 */
class PagoVenta extends ActiveRecord {

    protected static $tabla      = 'pagos_venta';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','venta_id','fecha','metodo','monto','referencia'];

    public ?int    $id         = null;
    public ?int    $venta_id   = null;
    public ?string $fecha      = null;
    public string  $metodo     = 'efectivo';
    public float   $monto      = 0;
    public ?string $referencia = null;

    public static function getByVenta(int $venta_id): array {
        return self::fetchRaw(
            "SELECT * FROM pagos_venta WHERE venta_id = :vid",
            [':vid' => $venta_id]
        );
    }

    public static function getTotalPagado(int $venta_id): float {
        $row = self::fetchFirstRaw(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM pagos_venta WHERE venta_id = :vid",
            [':vid' => $venta_id]
        );
        return (float)($row['total'] ?? 0);
    }
}
