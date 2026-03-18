<?php
namespace Controllers;

use MVC\Router;
use Models\UnidadMedida;

class UnidadesController {

    public static function index(Router $router): void {
        isAuth();
        $unidades = UnidadMedida::all('nombre');
        $router->render('unidades/index', [
            'titulo'   => 'Unidades de Medida',
            'unidades' => $unidades,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $unidad  = new UnidadMedida();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $unidad->sincronizar($_POST);
            $alertas = $unidad->validar();
            if (empty($alertas)) {
                $unidad->creado_en = date('Y-m-d H:i:s');
                $unidad->crear();
                header('Location: /' . $_ENV['APP_NAME'] . '/unidades?ok=1');
                exit;
            }
        }

        $router->render('unidades/form', [
            'titulo'  => 'Nueva Unidad de Medida',
            'unidad'  => $unidad,
            'alertas' => $alertas,
            'accion'  => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        $id      = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $unidad  = UnidadMedida::find($id);
        $alertas = [];

        if (!$unidad) {
            header('Location: /' . $_ENV['APP_NAME'] . '/unidades');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $unidad->sincronizar($_POST);
            $alertas = $unidad->validar();
            if (empty($alertas)) {
                $unidad->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/unidades?ok=2');
                exit;
            }
        }

        $router->render('unidades/form', [
            'titulo'  => 'Editar Unidad',
            'unidad'  => $unidad,
            'alertas' => $alertas,
            'accion'  => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/unidades');
            exit;
        }
        $id     = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $unidad = UnidadMedida::find($id);
        if ($unidad) {
            $unidad->eliminar();
        }
        header('Location: /' . $_ENV['APP_NAME'] . '/unidades?ok=3');
        exit;
    }

    public static function actualizarInline(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        
        if (!$datos || empty($datos['id'])) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $unidad = UnidadMedida::find((int)$datos['id']);
        if (!$unidad) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $unidad->nombre = trim($datos['nombre']);
        if (isset($datos['abreviatura'])) $unidad->abreviatura = trim($datos['abreviatura']);

        $alertas = $unidad->validar();
        if (empty($alertas['danger'])) {
            $resultado = $unidad->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
