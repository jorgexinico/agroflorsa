<?php
namespace Controllers;

use MVC\Router;
use Models\Producto;
use Models\UnidadMedida;

class ProductosController {

    public static function index(Router $router): void {
        isAuth();
        $productos = Producto::allConUnidad();
        $router->render('productos/index', [
            'titulo'   => 'Productos',
            'productos' => $productos,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $producto = new Producto();
        $unidades = UnidadMedida::all();
        $alertas  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->nombre              = trim($_POST['nombre'] ?? '');
            $producto->sku                 = trim($_POST['sku'] ?? '') ?: null;
            $producto->unidad_id           = (int)($_POST['unidad_id'] ?? 0);
            $producto->tipo                = $_POST['tipo'] ?? 'mercaderia';
            $producto->maneja_vencimiento  = isset($_POST['maneja_vencimiento']) ? 1 : 0;
            $producto->activo              = 1;

            $alertas = $producto->validar();
            if (empty($alertas['danger'])) {
                $producto->creado_en = date('Y-m-d H:i:s');
                $producto->crear();
                redirectTo('/productos?ok=1');
            }
        }

        $router->render('productos/form', [
            'titulo'   => 'Nuevo Producto',
            'accion'   => 'crear',
            'producto' => $producto,
            'unidades' => $unidades,
            'alertas'  => $alertas,
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        $id       = (int)($_GET['id'] ?? 0);
        $producto = Producto::find($id);
        if (!$producto) redirectTo('/productos');

        $unidades = UnidadMedida::all();
        $alertas  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->nombre             = trim($_POST['nombre'] ?? '');
            $producto->sku                = trim($_POST['sku'] ?? '') ?: null;
            $producto->unidad_id          = (int)($_POST['unidad_id'] ?? 0);
            $producto->tipo               = $_POST['tipo'] ?? 'mercaderia';
            $producto->maneja_vencimiento = isset($_POST['maneja_vencimiento']) ? 1 : 0;

            $alertas = $producto->validar();
            if (empty($alertas['danger'])) {
                $producto->actualizar();
                redirectTo('/productos?ok=2');
            }
        }

        $router->render('productos/form', [
            'titulo'   => 'Editar Producto',
            'accion'   => 'editar',
            'producto' => $producto,
            'unidades' => $unidades,
            'alertas'  => $alertas,
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirectTo('/productos');

        $id       = (int)($_POST['id'] ?? 0);
        $producto = Producto::find($id);

        if ($producto) {
            $producto->activo = 0;
            $producto->actualizar();
        }
        redirectTo('/productos?ok=3');
    }

    /**
     * Importación masiva de productos.
     * POST recibe JSON con array de filas del Excel (parseado por SheetJS en el cliente).
     */
    public static function importar(Router $router): void {
        isAuth();

        // GET — mostrar vista de importación
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $unidades = UnidadMedida::all();
            $router->render('productos/importar', [
                'titulo'   => 'Importar Productos (Excel)',
                'unidades' => $unidades,
            ]);
            return;
        }

        // POST — procesar datos enviados como JSON
        $json     = file_get_contents('php://input');
        $filas    = json_decode($json, true);

        if (!is_array($filas) || empty($filas)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'No se recibieron datos válidos.']);
            return;
        }

        // Cargar mapa de unidades (abreviatura → id)
        $unidades   = UnidadMedida::all();
        $mapaUnidad = [];
        foreach ($unidades as $u) {
            $mapaUnidad[strtolower(trim($u->abreviatura))] = $u->id;
            $mapaUnidad[strtolower(trim($u->nombre))]      = $u->id;
        }

        $importados = 0;
        $errores    = [];
        $tiposValidos = ['mercaderia', 'materia_prima', 'terminado'];

        foreach ($filas as $i => $fila) {
            $fila = array_map('trim', $fila);
            $numFila = $i + 2; // +2 porque fila 1 es encabezado

            $nombre = $fila['nombre'] ?? $fila[0] ?? '';
            if (empty($nombre)) {
                $errores[] = "Fila {$numFila}: nombre vacío — omitida.";
                continue;
            }

            $sku       = $fila['sku'] ?? $fila[1] ?? '';
            $tipoRaw   = strtolower($fila['tipo'] ?? $fila[2] ?? 'mercaderia');
            $tipo      = in_array($tipoRaw, $tiposValidos) ? $tipoRaw : 'mercaderia';
            $unidadRaw = strtolower($fila['unidad'] ?? $fila[3] ?? '');
            $unidadId  = $mapaUnidad[$unidadRaw] ?? null;
            $venc      = in_array(strtolower($fila['maneja_vencimiento'] ?? $fila[4] ?? ''), ['1','si','yes','sí','true']) ? 1 : 0;

            if (!$unidadId) {
                $errores[] = "Fila {$numFila}: unidad '{$unidadRaw}' no existe — omitida.";
                continue;
            }

            $p = new Producto();
            $p->nombre             = $nombre;
            $p->sku                = $sku ?: null;
            $p->tipo               = $tipo;
            $p->unidad_id          = $unidadId;
            $p->maneja_vencimiento = $venc;
            $p->activo             = 1;

            $alertas = $p->validar();
            if (!empty($alertas['danger'])) {
                $errores[] = "Fila {$numFila}: " . implode(', ', $alertas['danger']);
                continue;
            }

            $p->creado_en = date('Y-m-d H:i:s');
            $resultado = $p->crear();
            if ($resultado) {
                $importados++;
            } else {
                $errores[] = "Fila {$numFila}: error al guardar '{$nombre}'.";
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'ok'         => true,
            'importados' => $importados,
            'errores'    => $errores,
        ]);
    }
}
