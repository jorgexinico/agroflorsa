<?php
namespace Controllers;

use MVC\Router;
use Models\Gasto;
use Models\CategoriaGasto;
use Models\Sucursal;
use Model\ActiveRecord;

class GastosController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']); // Solo admins y supervisores

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);

        $params = [':f1' => $fecha_inicio . ' 00:00:00', ':f2' => $fecha_fin . ' 23:59:59'];
        $whereSucursal = '';
        if ($sucursal_id > 0) {
            $whereSucursal = " AND g.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        $gastos = ActiveRecord::fetchRaw(
            "SELECT g.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre, c.nombre AS categoria_nombre
             FROM gastos g
             LEFT JOIN sucursales s ON s.id = g.sucursal_id
             JOIN usuarios u ON u.id = g.usuario_id
             JOIN categorias_gastos c ON c.id = g.categoria_id
             WHERE g.fecha BETWEEN :f1 AND :f2 {$whereSucursal}
             ORDER BY g.fecha DESC",
            $params
        );

        $sucursales = Sucursal::getActivas();

        $router->render('gastos/index', [
            'titulo'       => 'Gastos Operativos (Egresos)',
            'gastos'       => $gastos,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin,
            'sucursal_id'  => $sucursal_id,
            'sucursales'   => $sucursales
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']);

        $gasto = new Gasto();
        $categorias = CategoriaGasto::allCategorias();
        $sucursales = Sucursal::getActivas();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Evitar TypeError: Cannot assign string to property ?int (PHP 7.4+)
            if (empty($_POST['sucursal_id'])) $_POST['sucursal_id'] = null;
            if (empty($_POST['categoria_id'])) $_POST['categoria_id'] = null;
            if (isset($_POST['monto'])) $_POST['monto'] = (float)$_POST['monto'];

            $gasto->sincronizar($_POST);
            $gasto->usuario_id = $_SESSION['usuario_id'] ?? 1;
            
            // Validar que si no selecciona sucursal, lo guarde como nulo (gasto global)
            if (empty($gasto->sucursal_id)) {
                $gasto->sucursal_id = null;
            }

            if (empty($gasto->fecha)) {
                $gasto->fecha = date('Y-m-d H:i:s');
            } else {
                // Asegurar formato DATETIME
                if (strlen($gasto->fecha) <= 10) {
                    $gasto->fecha .= ' ' . date('H:i:s');
                }
            }

            $alertas = $gasto->validar();

            if (empty($alertas)) {
                $resultado = $gasto->crear();
                if ($resultado) {
                    redirectTo('/gastos?ok=1');
                    exit;
                } else {
                    Gasto::setAlerta('danger', 'Error al guardar el gasto');
                    $alertas = Gasto::getAlertas();
                }
            }
        }

        $router->render('gastos/crear', [
            'titulo'     => 'Registrar Nuevo Gasto',
            'gasto'      => $gasto,
            'categorias' => $categorias,
            'sucursales' => $sucursales,
            'alertas'    => $alertas
        ]);
    }

    public static function eliminar(): void {
        isAuth();
        isRole(['admin']); // Solo admin puede anular

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if ($id) {
                $gasto = Gasto::find($id);
                if ($gasto && $gasto->estado !== 'anulado') {
                    $gasto->estado = 'anulado';
                    $gasto->actualizar();
                    redirectTo('/gastos?ok=2');
                    exit;
                }
            }
            redirectTo('/gastos?err=1');
            exit;
        }
    }
}
