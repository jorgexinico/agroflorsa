<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo CuentaPorCobrar — tabla `cuentas_por_cobrar`
 */
class CuentaPorCobrar extends ActiveRecord {

    protected static $tabla      = 'cuentas_por_cobrar';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','cliente_id','venta_id','fecha','total','pagado','saldo','estado'
    ];

    public ?int    $id         = null;
    public ?int    $cliente_id = null;
    public ?int    $venta_id   = null;
    public ?string $fecha      = null;
    public float   $total      = 0;
    public float   $pagado     = 0;
    public float   $saldo      = 0;
    public string  $estado     = 'pendiente';

    // Joins
    public ?string $cliente_nombre  = null;
    public ?string $sucursal_nombre = null;

    /** Lista cuentas abiertas (pendiente o parcial), opcionalmente filtradas por cliente */
    public static function getPendientes(int $cliente_id = 0): array {
        $where  = "WHERE cxc.estado IN ('pendiente','parcial')";
        $params = [];
        if ($cliente_id > 0) {
            $where .= " AND cxc.cliente_id = :cli";
            $params[':cli'] = $cliente_id;
        }
        return self::fetchRaw(
            "SELECT cxc.*, c.nombre AS cliente_nombre, v.fecha AS venta_fecha,
                    s.nombre AS sucursal_nombre
             FROM cuentas_por_cobrar cxc
             JOIN clientes c ON c.id = cxc.cliente_id
             JOIN ventas v   ON v.id = cxc.venta_id
             JOIN sucursales s ON s.id = v.sucursal_id
             $where
             ORDER BY cxc.fecha DESC",
            $params
        );
    }

    /** Devuelve la cuenta de una venta concreta (o null) */
    public static function getByVenta(int $venta_id): ?array {
        return self::fetchFirstRaw(
            "SELECT cxc.*, c.nombre AS cliente_nombre
             FROM cuentas_por_cobrar cxc
             JOIN clientes c ON c.id = cxc.cliente_id
             WHERE cxc.venta_id = :vid",
            [':vid' => $venta_id]
        );
    }
}
