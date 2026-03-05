<?php
namespace Controllers;

use MVC\Router;
use Models\Cliente;

class ClientesController {

    public static function index(Router $router): void {
        isAuth();
        $clientes = Cliente::all('nombre');
        $router->render('clientes/index', [
            'titulo'   => 'Clientes',
            'clientes' => $clientes,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $cliente = new Cliente();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente->sincronizar($_POST);
            $alertas = $cliente->validar();
            if (empty($alertas)) {
                $cliente->creado_en = date('Y-m-d H:i:s');
                $cliente->crear();
                header('Location: /' . $_ENV['APP_NAME'] . '/clientes?ok=1');
                exit;
            }
        }

        $router->render('clientes/form', [
            'titulo'  => 'Nuevo Cliente',
            'cliente' => $cliente,
            'alertas' => $alertas,
            'accion'  => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        $id      = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $cliente = Cliente::find($id);
        $alertas = [];

        if (!$cliente) {
            header('Location: /' . $_ENV['APP_NAME'] . '/clientes');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente->sincronizar($_POST);
            $alertas = $cliente->validar();
            if (empty($alertas)) {
                $cliente->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/clientes?ok=2');
                exit;
            }
        }

        $router->render('clientes/form', [
            'titulo'  => 'Editar Cliente',
            'cliente' => $cliente,
            'alertas' => $alertas,
            'accion'  => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/clientes');
            exit;
        }
        $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $cliente = Cliente::find($id);
        if ($cliente) {
            $cliente->activo = 0;
            $cliente->actualizar();
        }
        header('Location: /' . $_ENV['APP_NAME'] . '/clientes?ok=3');
        exit;
    }

    public static function activar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/clientes');
            exit;
        }
        $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $cliente = Cliente::find($id);
        if ($cliente) {
            $cliente->activo = 1;
            $cliente->actualizar();
        }
        header('Location: /' . $_ENV['APP_NAME'] . '/clientes?ok=4');
        exit;
    }
}
