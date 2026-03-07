<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo PagoCxC — tabla `pagos_cxc`
 * Registra cada abono aplicado a una cuenta por cobrar.
 *
 * DDL requerido (ejecutar una sola vez):
 *   CREATE TABLE pagos_cxc (
 *     id          BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 *     cxc_id      BIGINT NOT NULL,
 *     fecha       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *     metodo      ENUM('efectivo','transferencia','cheque','otro') NOT NULL DEFAULT 'efectivo',
 *     monto       DECIMAL(12,2) NOT NULL,
 *     referencia  VARCHAR(120) NULL,
 *     CONSTRAINT fk_pagocxc_cxc FOREIGN KEY (cxc_id) REFERENCES cuentas_por_cobrar(id)
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class PagoCxC extends ActiveRecord {

    protected static $tabla      = 'pagos_cxc';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','cxc_id','fecha','metodo','monto','referencia'];

    public ?int    $id         = null;
    public ?int    $cxc_id     = null;
    public ?string $fecha      = null;
    public string  $metodo     = 'efectivo';
    public float   $monto      = 0;
    public ?string $referencia = null;

    public static function getByCxC(int $cxc_id): array {
        return self::fetchRaw(
            "SELECT * FROM pagos_cxc WHERE cxc_id = :cid ORDER BY fecha DESC",
            [':cid' => $cxc_id]
        );
    }

    public static function getTotalPagado(int $cxc_id): float {
        $row = self::fetchFirstRaw(
            "SELECT COALESCE(SUM(monto),0) AS total FROM pagos_cxc WHERE cxc_id = :cid",
            [':cid' => $cxc_id]
        );
        return (float)($row['total'] ?? 0);
    }
}
