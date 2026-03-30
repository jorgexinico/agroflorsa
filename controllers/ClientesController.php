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
                redirectTo('/clientes?ok=1');
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
            redirectTo('/clientes');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente->sincronizar($_POST);
            $alertas = $cliente->validar();
            if (empty($alertas)) {
                $cliente->actualizar();
                redirectTo('/clientes?ok=2');
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
            redirectTo('/clientes');
        }
        $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $cliente = Cliente::find($id);
        if ($cliente) {
            $cliente->activo = 0;
            $cliente->actualizar();
        }
        redirectTo('/clientes?ok=3');
    }

    public static function activar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('/clientes');
        }
        $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $cliente = Cliente::find($id);
        if ($cliente) {
            $cliente->activo = 1;
            $cliente->actualizar();
        }
        redirectTo('/clientes?ok=4');
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

        $cliente = Cliente::find((int)$datos['id']);
        if (!$cliente) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $cliente->nombre = trim($datos['nombre']);
        if (isset($datos['nit'])) $cliente->nit = trim($datos['nit']);
        if (isset($datos['telefono'])) $cliente->telefono = trim($datos['telefono']);

        $alertas = $cliente->validar();
        if (empty($alertas['danger'])) {
            $resultado = $cliente->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
