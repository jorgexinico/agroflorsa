<?php
namespace Controllers;

use MVC\Router;
use Models\CuentaPorPagar;
use Models\PagoCxP;
use Models\Proveedor;
use Model\ActiveRecord;

class CuentasPorPagarController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $proveedor_id = filter_var($_GET['proveedor_id'] ?? 0, FILTER_VALIDATE_INT);
        $cuentas      = CuentaPorPagar::getPendientes($proveedor_id);
        $proveedores  = Proveedor::getActivos();

        $router->render('cuentas_pagar/index', [
            'titulo'       => 'Cuentas por Pagar (CxP)',
            'cuentas'      => $cuentas,
            'proveedores'  => $proveedores,
            'proveedor_id' => $proveedor_id,
        ]);
    }

    public static function detalle(Router $router): void {
        isAuth();
        isRole(['admin']);
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $cxp = CuentaPorPagar::fetchFirstRaw(
            "SELECT cxp.*, p.nombre AS proveedor_nombre, c.fecha AS compra_fecha, s.nombre AS sucursal_nombre
             FROM cuentas_por_pagar cxp
             LEFT JOIN proveedores p ON p.id = cxp.proveedor_id
             JOIN compras c ON c.id = cxp.compra_id
             JOIN sucursales s ON s.id = c.sucursal_id
             WHERE cxp.id = :id",
            [':id' => $id]
        );

        if (!$cxp) {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-pagar');
            exit;
        }

        $pagos = PagoCxP::getByCxp($id);

        $router->render('cuentas_pagar/detalle', [
            'titulo' => 'Detalle CxP #' . $id,
            'cxp'    => $cxp,
            'pagos'  => $pagos,
        ]);
    }

    public static function abonar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-pagar');
            exit;
        }

        $cxp_id = filter_var($_POST['cxp_id'] ?? 0, FILTER_VALIDATE_INT);
        $monto  = (float)($_POST['monto'] ?? 0);
        $metodo = $_POST['metodo'] ?? 'efectivo';
        $ref    = htmlspecialchars(trim($_POST['referencia'] ?? ''));

        $cxp = CuentaPorPagar::find($cxp_id);

        if (!$cxp || $monto <= 0 || $monto > $cxp->saldo) {
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-pagar/detalle?id=' . $cxp_id . '&err=monto_invalido');
            exit;
        }

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            // Guardar pago
            $pago = new PagoCxP();
            $pago->cxp_id     = $cxp_id;
            $pago->metodo     = $metodo;
            $pago->monto      = $monto;
            $pago->referencia = $ref;
            $pago->fecha      = date('Y-m-d H:i:s');
            $pago->crear();

            // Actualizar saldos
            $nuevoPagado = $cxp->pagado + $monto;
            $nuevoSaldo  = $cxp->total - $nuevoPagado;
            $nuevoEstado = $nuevoSaldo <= 0.01 ? 'pagada' : 'parcial';

            ActiveRecord::ejecutar(
                "UPDATE cuentas_por_pagar
                 SET pagado = :pagado, saldo = :saldo, estado = :estado
                 WHERE id = :id",
                [
                    ':pagado' => $nuevoPagado,
                    ':saldo'  => $nuevoSaldo,
                    ':estado' => $nuevoEstado,
                    ':id'     => $cxp_id
                ]
            );

            $db->commit();
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-pagar/detalle?id=' . $cxp_id . '&ok=abono');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            header('Location: /' . $_ENV['APP_NAME'] . '/cuentas-pagar/detalle?id=' . $cxp_id . '&err=db');
            exit;
        }
    }
}
