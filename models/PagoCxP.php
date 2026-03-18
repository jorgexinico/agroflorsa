<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo PagoCxP — tabla `pagos_cxp`
 */
class PagoCxP extends ActiveRecord {

    protected static $tabla      = 'pagos_cxp';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','cxp_id','fecha','metodo','monto','referencia'
    ];

    public ?int    $id         = null;
    public ?int    $cxp_id     = null;
    public ?string $fecha      = null;
    public string  $metodo     = 'efectivo';
    public float   $monto      = 0;
    public ?string $referencia = null;

    /** Lista todos los pagos de una cuenta concreta */
    public static function getByCxp(int $cxp_id): array {
        return self::fetchRaw(
            "SELECT * FROM pagos_cxp WHERE cxp_id = :cid ORDER BY fecha DESC",
            [':cid' => $cxp_id]
        );
    }
}
