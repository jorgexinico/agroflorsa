<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo CuentaPorPagar — tabla `cuentas_por_pagar`
 */
class CuentaPorPagar extends ActiveRecord {

    protected static $tabla      = 'cuentas_por_pagar';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','proveedor_id','compra_id','total','pagado','saldo','estado','fecha'
    ];

    public ?int    $id           = null;
    public ?int    $proveedor_id = null;
    public ?int    $compra_id    = null;
    public float   $total        = 0;
    public float   $pagado       = 0;
    public float   $saldo        = 0;
    public string  $estado       = 'pendiente';
    public ?string $fecha        = null;

    // Joins
    public ?string $proveedor_nombre = null;
    public ?float  $compra_total     = null;
    public ?string $compra_fecha     = null;
    public ?string $sucursal_nombre  = null;

    /** Lista cuentas abiertas (pendiente o parcial), opcionalmente filtradas por proveedor */
    public static function getPendientes(int $proveedor_id = 0): array {
        $where  = "WHERE cxp.estado IN ('pendiente','parcial')";
        $params = [];
        if ($proveedor_id > 0) {
            $where .= " AND cxp.proveedor_id = :prov";
            $params[':prov'] = $proveedor_id;
        }
        return self::fetchRaw(
            "SELECT cxp.*, p.nombre AS proveedor_nombre, c.fecha AS compra_fecha,
                    c.total AS compra_total, s.nombre AS sucursal_nombre
             FROM cuentas_por_pagar cxp
             LEFT JOIN proveedores p ON p.id = cxp.proveedor_id
             JOIN compras c ON c.id = cxp.compra_id
             JOIN sucursales s ON s.id = c.sucursal_id
             $where
             ORDER BY cxp.fecha DESC",
            $params
        );
    }

    /** Devuelve la cuenta de una compra concreta (o null) */
    public static function getByCompra(int $compra_id): ?array {
        return self::fetchFirstRaw(
            "SELECT cxp.*, p.nombre AS proveedor_nombre
             FROM cuentas_por_pagar cxp
             LEFT JOIN proveedores p ON p.id = cxp.proveedor_id
             WHERE cxp.compra_id = :cid",
            [':cid' => $compra_id]
        );
    }
}
