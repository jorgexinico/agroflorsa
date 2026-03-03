<?php
namespace Controllers;

use MVC\Router;
use Models\Proveedor;

class ProveedoresController {

    public static function index(Router $router): void {
        isAuth();
        $proveedores = Proveedor::all('nombre');
        $router->render('proveedores/index', [
            'titulo'      => 'Proveedores',
            'proveedores' => $proveedores,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $proveedor = new Proveedor();
        $alertas   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proveedor->sincronizar($_POST);
            $alertas = $proveedor->validar();
            if (empty($alertas)) {
                $proveedor->crear();
                header('Location: /' . $_ENV['APP_NAME'] . '/proveedores?ok=1');
                exit;
            }
        }

        $router->render('proveedores/form', [
            'titulo'    => 'Nuevo Proveedor',
            'proveedor' => $proveedor,
            'alertas'   => $alertas,
            'accion'    => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        $id        = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $proveedor = Proveedor::find($id);
        $alertas   = [];

        if (!$proveedor) {
            header('Location: /' . $_ENV['APP_NAME'] . '/proveedores');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proveedor->sincronizar($_POST);
            $alertas = $proveedor->validar();
            if (empty($alertas)) {
                $proveedor->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/proveedores?ok=2');
                exit;
            }
        }

        $router->render('proveedores/form', [
            'titulo'    => 'Editar Proveedor',
            'proveedor' => $proveedor,
            'alertas'   => $alertas,
            'accion'    => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/proveedores');
            exit;
        }
        $id        = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $proveedor = Proveedor::find($id);
        if ($proveedor) {
            $proveedor->activo = 0;
            $proveedor->actualizar();
        }
        header('Location: /' . $_ENV['APP_NAME'] . '/proveedores?ok=3');
        exit;
    }
}
