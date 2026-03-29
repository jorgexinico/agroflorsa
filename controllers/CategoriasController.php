<?php
namespace Controllers;

use MVC\Router;
use Models\Categoria;

class CategoriasController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $categorias = Categoria::whereArray(['activo' => 1]);
        $router->render('categorias/index', [
            'titulo'     => 'Categorías',
            'categorias' => $categorias,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $categoria = new Categoria();
        $alertas   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();
            if (empty($alertas['danger'])) {
                $categoria->creado_en = date('Y-m-d H:i:s');
                $categoria->activo = 1;
                $categoria->crear();
                redirectTo('/categorias?ok=1');
            }
        }

        $router->render('categorias/form', [
            'titulo'    => 'Nueva Categoría',
            'categoria' => $categoria,
            'alertas'   => $alertas,
            'accion'    => 'crear',
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        isRole(['admin']);
        $id        = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $categoria = Categoria::find($id);
        $alertas   = [];

        if (!$categoria) {
            redirectTo('/categorias');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();
            if (empty($alertas['danger'])) {
                $categoria->actualizar();
                redirectTo('/categorias?ok=2');
            }
        }

        $router->render('categorias/form', [
            'titulo'    => 'Editar Categoría',
            'categoria' => $categoria,
            'alertas'   => $alertas,
            'accion'    => 'editar',
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('/categorias');
        }
        $id        = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->activo = 0;
            $categoria->actualizar();
        }
        redirectTo('/categorias?ok=3');
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

        $categoria = Categoria::find((int)$datos['id']);
        if (!$categoria) {
            echo json_encode(['ok' => false, 'error' => 'Registro no encontrado']);
            return;
        }

        if (isset($datos['nombre'])) $categoria->nombre = trim($datos['nombre']);
        if (isset($datos['descripcion'])) $categoria->descripcion = trim($datos['descripcion']);

        $alertas = $categoria->validar();
        if (empty($alertas['danger'])) {
            $resultado = $categoria->actualizar();
            echo json_encode(['ok' => $resultado['resultado'] ?? true]);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }
}
