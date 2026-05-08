<?php
namespace Controllers;

use MVC\Router;
use Models\Producto;
use Models\Sucursal;

class PreciosController {

    public static function index(Router $router): void {
        isAuth();
        
        $productos = Producto::fetchRaw(
            "SELECT p.id, p.nombre, p.sku, p.precio_publico, p.precio_mayorista, 
                    u.abreviatura AS unidad, m.nombre AS marca
             FROM productos p
             JOIN unidades_medida u ON u.id = p.unidad_id
             LEFT JOIN marcas m ON m.id = p.marca_id
             WHERE p.activo = 1
             ORDER BY p.nombre ASC"
        );

        $sucursales = Sucursal::getActivas();

        $stocks = Producto::fetchRaw(
            "SELECT producto_id, sucursal_id, cantidad 
             FROM inventario_existencias_producto"
        );
        
        $stockMap = [];
        foreach($stocks as $s) {
            $stockMap[$s['producto_id']][$s['sucursal_id']] = (float)$s['cantidad'];
        }

        $router->render('precios/index', [
            'titulo'     => 'Consulta de Precios',
            'productos'  => $productos,
            'sucursales' => $sucursales,
            'stockMap'   => $stockMap
        ]);
    }
}
