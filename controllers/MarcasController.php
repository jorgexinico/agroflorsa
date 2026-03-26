<?php
namespace Controllers;

use MVC\Router;
use Models\Marca;

class MarcasController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $marcas = Marca::all('nombre');
        $router->render('marcas/index', [
            'titulo' => 'Marcas',
            'marcas' => $marcas,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $marca   = new Marca();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $marca->sincronizar($_POST);
            $alertas = $marca->validar();
            if (empty($alertas['danger'])) {
                $marca->crear();
                header('Location: /' . $_ENV['APP_NAME'] . '/marcas?ok=1');
                exit;
            }
        }

        $router->render('marcas/form', [
            'titulo'  => 'Nueva Marca',
            'marca'   => $marca,
            'alertas' => $alertas,
            'accion'  => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        isRole(['admin']);
        $id      = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $marca   = Marca::find($id);
        $alertas = [];

        if (!$marca) {
            header('Location: /' . $_ENV['APP_NAME'] . '/marcas');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $marca->sincronizar($_POST);
            $alertas = $marca->validar();
            if (empty($alertas['danger'])) {
                $marca->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/marcas?ok=2');
                exit;
            }
        }

        $router->render('marcas/form', [
            'titulo'  => 'Editar Marca',
            'marca'   => $marca,
            'alertas' => $alertas,
            'accion'  => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/marcas');
            exit;
        }
        $id    = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $marca = Marca::find($id);
        if ($marca) {
            $marca->eliminar();
        }
        header('Location: /' . $_ENV['APP_NAME'] . '/marcas?ok=3');
        exit;
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

        $marca = Marca::find((int)$datos['id']);
        if (!$marca) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $marca->nombre = trim($datos['nombre']);

        $alertas = $marca->validar();
        if (empty($alertas['danger'])) {
            $resultado = $marca->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
