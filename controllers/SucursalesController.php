<?php
namespace Controllers;

use MVC\Router;
use Models\Sucursal;

/**
 * SucursalesController — CRUD completo
 */
class SucursalesController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $sucursales = Sucursal::all('nombre');
        $router->render('sucursales/index', [
            'titulo'     => 'Sucursales',
            'sucursales' => $sucursales,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $sucursal = new Sucursal();
        $alertas  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal->sincronizar($_POST);
            $alertas = $sucursal->validar();
            if (empty($alertas)) {
                $sucursal->creado_en = date('Y-m-d H:i:s');
                $resultado = $sucursal->crear();
                if ($resultado['resultado']) {
                    redirectTo('/sucursales?ok=1');
                    exit;
                }
            }
        }

        $router->render('sucursales/form', [
            'titulo'    => 'Nueva Sucursal',
            'sucursal'  => $sucursal,
            'alertas'   => $alertas,
            'accion'    => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        isRole(['admin']);
        $id       = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $sucursal = Sucursal::find($id);
        $alertas  = [];

        if (!$sucursal) {
            redirectTo('/sucursales');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal->sincronizar($_POST);
            $alertas = $sucursal->validar();
            if (empty($alertas)) {
                $sucursal->actualizar();
                redirectTo('/sucursales?ok=2');
                exit;
            }
        }

        $router->render('sucursales/form', [
            'titulo'   => 'Editar Sucursal',
            'sucursal' => $sucursal,
            'alertas'  => $alertas,
            'accion'   => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/sucursales');
            exit;
        }
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $sucursal = Sucursal::find($id);
        if ($sucursal) {
            $sucursal->activa = 0;
            $sucursal->actualizar();
        }
        redirectTo('/sucursales?ok=3');
        exit;
    }

    public static function reactivar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/sucursales');
            exit;
        }
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $sucursal = Sucursal::find($id);
        if ($sucursal) {
            $sucursal->activa = 1;
            $sucursal->actualizar();
        }
        redirectTo('/sucursales?ok=4');
        exit;
    }

    public static function actualizarInline(Router $router): void {
        isAuth();
        isRole(['admin']); // Solo admin puede editar sucursales
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        
        if (!$datos || empty($datos['id'])) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $sucursal = Sucursal::find((int)$datos['id']);
        if (!$sucursal) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $sucursal->nombre = trim($datos['nombre']);
        if (isset($datos['direccion'])) $sucursal->direccion = trim($datos['direccion']);

        $alertas = $sucursal->validar();
        if (empty($alertas['danger'])) {
            $resultado = $sucursal->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
