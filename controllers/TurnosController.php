<?php
namespace Controllers;

use MVC\Router;
use Models\Turno;
use Models\Sucursal;
use Models\Venta;
use Model\ActiveRecord;

class TurnosController {

    public static function index(Router $router): void {
        isAuth();
        $rol     = $_SESSION['usuario_rol'] ?? '';
        $esAdmin = in_array($rol, ['admin', 'supervisor']);

        if ($esAdmin) {
            // Admin/supervisor: todos los turnos
            $turnos = Turno::fetchRaw(
                "SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
                 FROM turnos t
                 JOIN sucursales s ON s.id = t.sucursal_id
                 JOIN usuarios u ON u.id = t.usuario_id
                 ORDER BY t.abierto_en DESC
                 LIMIT 100"
            );
        } else {
            // Vendedor: solo sus propios turnos
            $turnos = Turno::fetchRaw(
                "SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
                 FROM turnos t
                 JOIN sucursales s ON s.id = t.sucursal_id
                 JOIN usuarios u ON u.id = t.usuario_id
                 WHERE t.usuario_id = :uid
                 ORDER BY t.abierto_en DESC
                 LIMIT 50",
                [':uid' => $_SESSION['usuario_id']]
            );
        }

        $turnoActivo = Turno::fetchFirstRaw(
            "SELECT t.*, s.nombre AS sucursal_nombre
             FROM turnos t JOIN sucursales s ON s.id = t.sucursal_id
             WHERE t.usuario_id = :uid AND t.estado = 'abierto'
             LIMIT 1",
            [':uid' => $_SESSION['usuario_id']]
        );

        $router->render('turnos/index', [
            'titulo'      => 'Turnos',
            'turnos'      => $turnos,
            'turnoActivo' => $turnoActivo,
            'esAdmin'     => $esAdmin,
        ]);
    }

    public static function abrir(Router $router): void {
        isAuth();
        $sucursales = Sucursal::getActivas();
        $alertas    = [];

        // Verificar si ya tiene turno abierto
        $yaAbierto = Turno::fetchFirstRaw(
            "SELECT id, sucursal_id FROM turnos WHERE usuario_id = :uid AND estado = 'abierto' LIMIT 1",
            [':uid' => $_SESSION['usuario_id']]
        );
        if ($yaAbierto) {
            // Reconectar automáticamente si ya tiene un turno abierto
            $_SESSION['turno_id']    = (int)$yaAbierto['id'];
            $_SESSION['sucursal_id'] = (int)$yaAbierto['sucursal_id'];
            header('Location: /' . $_ENV['APP_NAME'] . '/dashboard?welcome_back=1');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal_id   = (int)$_POST['sucursal_id'];
            $monto_inicial = (float)$_POST['monto_inicial'];

            if (!$sucursal_id) {
                Turno::setAlerta('danger', 'Selecciona la sucursal');
                $alertas = Turno::getAlertas();
            } else {
                $turno                = new Turno();
                $turno->sucursal_id   = $sucursal_id;
                $turno->usuario_id    = $_SESSION['usuario_id'];
                $turno->monto_inicial = $monto_inicial;
                $turno->estado        = 'abierto';
                $turno->abierto_en    = date('Y-m-d H:i:s');

                $resultado = $turno->crear();
                $turno_id  = (int)$resultado['id'];

                // Guardar turno activo en sesión
                $_SESSION['turno_id']    = $turno_id;
                $_SESSION['sucursal_id'] = $sucursal_id;

                header('Location: /' . $_ENV['APP_NAME'] . '/turnos/detalle?id=' . $turno_id);
                exit;
            }
        }

        $router->render('turnos/abrir', [
            'titulo'     => 'Abrir Turno',
            'sucursales' => $sucursales,
            'alertas'    => $alertas,
        ]);
    }

    public static function detalle(Router $router): void {
        isAuth();
        $id    = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $turno = Turno::fetchFirstRaw(
            "SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
             FROM turnos t
             JOIN sucursales s ON s.id = t.sucursal_id
             JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.id = :id",
            [':id' => $id]
        );

        if (!$turno) {
            header('Location: /' . $_ENV['APP_NAME'] . '/turnos');
            exit;
        }

        $ventas   = Venta::getByTurno($id);
        
        // Obtener detalles de todas las ventas del turno para el acordeón
        $todosLosDetalles = ActiveRecord::fetchRaw(
            "SELECT vd.*, p.nombre AS producto_nombre, u.abreviatura AS unidad
             FROM venta_detalle vd
             JOIN ventas v ON v.id = vd.venta_id
             JOIN productos p ON p.id = vd.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE v.turno_id = :tid",
            [':tid' => $id]
        );

        // Agrupar detalles por venta_id
        $detallesPorVenta = [];
        foreach ($todosLosDetalles as $det) {
            $detallesPorVenta[$det['venta_id']][] = $det;
        }

        $totales  = Turno::calcularTotales($id);

        $router->render('turnos/detalle', [
            'titulo'           => 'Detalle del Turno',
            'turno'            => $turno,
            'ventas'           => $ventas,
            'detallesPorVenta' => $detallesPorVenta,
            'totales'          => $totales,
        ]);
    }

    public static function cerrar(Router $router): void {
        isAuth();
        $id    = filter_var($_POST['id'] ?? $_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $turno = Turno::find($id);

        if (!$turno || $turno->estado !== 'abierto' || $turno->usuario_id != $_SESSION['usuario_id']) {
            header('Location: /' . $_ENV['APP_NAME'] . '/turnos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto_entregado = (float)$_POST['monto_entregado'];
            $nota            = htmlspecialchars(trim($_POST['nota'] ?? ''));
            $totales         = Turno::calcularTotales($id);
            $monto_esperado  = (float)$turno->monto_inicial + $totales['total_efectivo'];

            Turno::ejecutar(
                "UPDATE turnos SET
                    estado           = 'cerrado',
                    cerrado_en       = :ahora,
                    monto_esperado   = :esperado,
                    monto_entregado  = :entregado,
                    diferencia       = :diff,
                    nota             = :nota
                 WHERE id = :id",
                [
                    ':ahora'     => date('Y-m-d H:i:s'),
                    ':esperado'  => round($monto_esperado, 2),
                    ':entregado' => round($monto_entregado, 2),
                    ':diff'      => round($monto_entregado - $monto_esperado, 2),
                    ':nota'      => $nota,
                    ':id'        => $id,
                ]
            );

            // Limpiar sesión de turno
            unset($_SESSION['turno_id'], $_SESSION['sucursal_id']);

            header('Location: /' . $_ENV['APP_NAME'] . '/turnos/reporte?id=' . $id);
            exit;
        }

        $totales = Turno::calcularTotales($id);
        $router->render('turnos/cerrar', [
            'titulo'  => 'Cerrar Turno',
            'turno'   => $turno,
            'totales' => $totales,
        ]);
    }

    public static function reporte(Router $router): void {
        isAuth();
        $id    = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $turno = Turno::fetchFirstRaw(
            "SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
             FROM turnos t
             JOIN sucursales s ON s.id = t.sucursal_id
             JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.id = :id",
            [':id' => $id]
        );

        if (!$turno) {
            header('Location: /' . $_ENV['APP_NAME'] . '/turnos');
            exit;
        }

        $ventas  = Venta::getByTurno($id);
        $totales = Turno::calcularTotales($id);

        $router->render('turnos/reporte', [
            'titulo'  => 'Reporte de Turno',
            'turno'   => $turno,
            'ventas'  => $ventas,
            'totales' => $totales,
        ]);
    }
}
