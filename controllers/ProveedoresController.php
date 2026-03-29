<?php
namespace Controllers;

use MVC\Router;
use Models\Proveedor;

class ProveedoresController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $proveedores = Proveedor::all('nombre');
        $router->render('proveedores/index', [
            'titulo'      => 'Proveedores',
            'proveedores' => $proveedores,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $proveedor = new Proveedor();
        $alertas   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proveedor->sincronizar($_POST);
            $alertas = $proveedor->validar();
            if (empty($alertas)) {
                $proveedor->creado_en = date('Y-m-d H:i:s');
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
        isRole(['admin']);
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
        isRole(['admin']);
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
        redirectTo('/proveedores?ok=3');
    }

    public static function activar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/proveedores');
            exit;
        }
        $id        = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $proveedor = Proveedor::find($id);
        if ($proveedor) {
            $proveedor->activo = 1;
            $proveedor->actualizar();
        }
        redirectTo('/proveedores?ok=4');
    }

    public static function actualizarInline(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        
        if (!$datos || empty($datos['id'])) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $proveedor = Proveedor::find((int)$datos['id']);
        if (!$proveedor) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $proveedor->nombre = trim($datos['nombre']);
        if (isset($datos['nit'])) $proveedor->nit = trim($datos['nit']);
        if (isset($datos['telefono'])) $proveedor->telefono = trim($datos['telefono']);

        $alertas = $proveedor->validar();
        if (empty($alertas['danger'])) {
            $resultado = $proveedor->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
