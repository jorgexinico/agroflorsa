<?php
namespace Controllers;

use MVC\Router;
use Models\CuentaPorCobrar;
use Models\PagoCxC;
use Models\Cliente;
use Model\ActiveRecord;

/**
 * CuentasPorCobrarController — Gestión de cartera de crédito
 */
class CuentasPorCobrarController {

    /** Lista todas las cuentas pendientes o parciales */
    public static function index(Router $router): void {
        isAuth();
        $cliente_id = (int)($_GET['cliente_id'] ?? 0);
        $clientes   = Cliente::fetchRaw(
            "SELECT id, nombre FROM clientes WHERE activo = 1 ORDER BY nombre"
        );
        $cuentas    = CuentaPorCobrar::getPendientes($cliente_id);

        // Totales de la vista
        $totalSaldo   = array_sum(array_column($cuentas, 'saldo'));
        $totalGeneral = array_sum(array_column($cuentas, 'total'));

        $router->render('cuentas_cobrar/index', [
            'titulo'       => 'Cuentas por Cobrar',
            'cuentas'      => $cuentas,
            'clientes'     => $clientes,
            'cliente_id'   => $cliente_id,
            'totalSaldo'   => $totalSaldo,
            'totalGeneral' => $totalGeneral,
        ]);
    }

    /** Detalle de una cuenta con historial de abonos */
    public static function detalle(Router $router): void {
        isAuth();
        $id     = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $cuenta = CuentaPorCobrar::fetchFirstRaw(
            "SELECT cxc.*, c.nombre AS cliente_nombre, v.fecha AS venta_fecha,
                    v.tipo_pago, s.nombre AS sucursal_nombre
             FROM cuentas_por_cobrar cxc
             JOIN clientes   c ON c.id = cxc.cliente_id
             JOIN ventas     v ON v.id = cxc.venta_id
             JOIN sucursales s ON s.id = v.sucursal_id
             WHERE cxc.id = :id",
            [':id' => $id]
        );

        if (!$cuenta) {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar');
            exit;
        }

        $abonos = PagoCxC::getByCxC($id);

        $router->render('cuentas_cobrar/detalle', [
            'titulo'  => 'Cuenta por Cobrar #' . $id,
            'cuenta'  => $cuenta,
            'abonos'  => $abonos,
        ]);
    }

    /** Registrar un abono (parcial o total) */
    public static function abonar(Router $router): void {
        isAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar');
            exit;
        }

        $cxc_id     = filter_var($_POST['cxc_id'] ?? 0, FILTER_VALIDATE_INT);
        $monto      = (float)($_POST['monto']      ?? 0);
        $metodo     = $_POST['metodo']     ?? 'efectivo';
        $referencia = htmlspecialchars(trim($_POST['referencia'] ?? ''));

        $cuenta = CuentaPorCobrar::find($cxc_id);

        if (!$cuenta || $cuenta->estado === 'pagada' || $cuenta->estado === 'anulada') {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar?err=invalid');
            exit;
        }

        if ($monto <= 0 || $monto > (float)$cuenta->saldo) {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar/detalle?id=' . $cxc_id . '&err=monto');
            exit;
        }

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            // Registrar el abono
            $pago             = new PagoCxC();
            $pago->cxc_id     = $cxc_id;
            $pago->fecha      = date('Y-m-d H:i:s');
            $pago->metodo     = $metodo;
            $pago->monto      = $monto;
            $pago->referencia = $referencia ?: null;
            $pago->crear();

            // Recalcular pagado/saldo/estado en la cuenta
            $nuevoPagado = round((float)$cuenta->pagado + $monto, 2);
            $nuevoSaldo  = round((float)$cuenta->total  - $nuevoPagado, 2);
            $nuevoEstado = $nuevoSaldo <= 0 ? 'pagada' : 'parcial';

            ActiveRecord::ejecutar(
                "UPDATE cuentas_por_cobrar
                 SET pagado = :pagado, saldo = :saldo, estado = :estado
                 WHERE id = :id",
                [
                    ':pagado' => $nuevoPagado,
                    ':saldo'  => max(0, $nuevoSaldo),
                    ':estado' => $nuevoEstado,
                    ':id'     => $cxc_id,
                ]
            );

            $db->commit();
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar/detalle?id=' . $cxc_id . '&ok=1');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-cobrar/detalle?id=' . $cxc_id . '&err=db');
            exit;
        }
    }
}
