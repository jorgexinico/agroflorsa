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
}
