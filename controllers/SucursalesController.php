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
                $resultado = $sucursal->crear();
                if ($resultado['resultado']) {
                    header('Location: /' . $_ENV['APP_NAME'] . '/sucursales?ok=1');
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
            header('Location: /' . $_ENV['APP_NAME'] . '/sucursales');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal->sincronizar($_POST);
            $alertas = $sucursal->validar();
            if (empty($alertas)) {
                $sucursal->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/sucursales?ok=2');
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
        header('Location: /' . $_ENV['APP_NAME'] . '/sucursales?ok=3');
        exit;
    }
}
