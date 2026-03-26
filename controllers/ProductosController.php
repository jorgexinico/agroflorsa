<?php
namespace Controllers;

use MVC\Router;
use Models\Producto;
use Models\UnidadMedida;
use Models\Marca;
use Models\Categoria;

class ProductosController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);
        $productos = Producto::allConUnidad();
        $categorias = Categoria::whereArray(['activo' => 1]);
        $router->render('productos/index', [
            'titulo'   => 'Productos',
            'productos' => $productos,
            'categorias' => $categorias
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $producto = new Producto();
        $unidades = UnidadMedida::all();
        $marcas   = Marca::all('nombre');
        $categorias = Categoria::whereArray(['activo' => 1]);
        $alertas  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->nombre              = trim($_POST['nombre'] ?? '');
            $producto->sku                 = trim($_POST['sku'] ?? '') ?: null;
            $producto->precio_publico      = (float)($_POST['precio_publico'] ?? 0);
            $producto->precio_mayorista    = (float)($_POST['precio_mayorista'] ?? 0);
            $producto->marca_id            = (int)($_POST['marca_id'] ?? 0) ?: null;
            $producto->categoria_id        = (int)($_POST['categoria_id'] ?? 0) ?: null;
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
            'titulo'     => 'Nuevo Producto',
            'accion'     => 'crear',
            'producto'   => $producto,
            'unidades'   => $unidades,
            'marcas'     => $marcas,
            'categorias' => $categorias,
            'alertas'    => $alertas,
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        isRole(['admin']);
        $id       = (int)($_GET['id'] ?? 0);
        $producto = Producto::find($id);
        if (!$producto) redirectTo('/productos');

        $unidades   = UnidadMedida::all();
        $marcas     = Marca::all('nombre');
        $categorias = Categoria::whereArray(['activo' => 1]);
        $alertas  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->nombre             = trim($_POST['nombre'] ?? '');
            $producto->sku                = trim($_POST['sku'] ?? '') ?: null;
            $producto->precio_publico     = (float)($_POST['precio_publico'] ?? 0);
            $producto->precio_mayorista   = (float)($_POST['precio_mayorista'] ?? 0);
            $producto->marca_id           = (int)($_POST['marca_id'] ?? 0) ?: null;
            $producto->categoria_id       = (int)($_POST['categoria_id'] ?? 0) ?: null;
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
            'titulo'     => 'Editar Producto',
            'accion'     => 'editar',
            'producto'   => $producto,
            'unidades'   => $unidades,
            'marcas'     => $marcas,
            'categorias' => $categorias,
            'alertas'    => $alertas,
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);
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
     * Endpoint AJAX para actualizar precios y categoría directamente desde la tabla
     */
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

        $producto = Producto::find((int)$datos['id']);
        if (!$producto) {
            echo json_encode(['ok' => false, 'error' => 'Producto no encontrado']);
            return;
        }

        if (isset($datos['precio_publico'])) {
            $producto->precio_publico = (float)$datos['precio_publico'];
        }
        if (isset($datos['precio_mayorista'])) {
            $producto->precio_mayorista = (float)$datos['precio_mayorista'];
        }
        
        $skuRegenerado = false;
        if (isset($datos['categoria_id'])) {
            $viejaCat = $producto->categoria_id;
            $nuevaCat = (int)$datos['categoria_id'] ?: null;
            $producto->categoria_id = $nuevaCat;
            
            // Si la categoría cambió, debemos regenerar el SKU
            if ($viejaCat !== $nuevaCat && $producto->sku) {
                // Obtener datos relacionados para recrear el SKU
                $marcaRaw = '';
                if ($producto->marca_id) {
                    $m = Marca::find($producto->marca_id);
                    $marcaRaw = $m ? $m->nombre : '';
                }
                
                $categoriaRaw = '';
                if ($nuevaCat) {
                    $c = Categoria::find($nuevaCat);
                    $categoriaRaw = $c ? $c->nombre : '';
                }
                
                $unidadAbr = 'UND';
                if ($producto->unidad_id) {
                    $u = UnidadMedida::find($producto->unidad_id);
                    $unidadAbr = $u ? $u->abreviatura : 'UND';
                }

                $marcaClean = preg_replace('/[^A-Za-z0-9]/', '', $marcaRaw);
                $marcaPart = strtoupper(substr($marcaClean, 0, 4));
                if (strlen($marcaPart) < 4) $marcaPart = str_pad($marcaPart, 4, 'X');
                if (empty($marcaRaw)) $marcaPart = 'GENE';

                $catClean = preg_replace('/[^A-Za-z0-9]/', '', $categoriaRaw);
                $catPart = strtoupper(substr($catClean, 0, 4));
                if (strlen($catPart) < 4) $catPart = str_pad($catPart, 4, 'X');
                if (empty($categoriaRaw)) $catPart = 'XXXX';

                $nombreClean = preg_replace('/[^A-Za-z0-9]/', '', $producto->nombre);
                $nombrePart = strtoupper(substr($nombreClean, 0, 4));
                if (strlen($nombrePart) < 4) $nombrePart = str_pad($nombrePart, 4, 'X');

                $abreviaturaReal = strtoupper($unidadAbr);

                $baseSku = "{$marcaPart}-{$catPart}-{$nombrePart}-{$abreviaturaReal}";
                $skuFinal = $baseSku;
                $contadorSku = 1;
                
                // Mismo loop para evitar colisiones, pero excluyendo a esteproducto mismo 
                // ya que podría mantener el mismo nombre si solo cambia la categoría y la categoría no varió sus 4 letras iniciales.
                while (true) {
                    $existentes = Producto::where('sku', $skuFinal);
                    $colision = false;
                    foreach((array)$existentes as $ex) {
                        if ($ex->id !== $producto->id) {
                            $colision = true;
                            break;
                        }
                    }
                    if (!$colision) break;
                    
                    $contadorSku++;
                    $skuFinal = "{$baseSku}-{$contadorSku}";
                }
                
                $producto->sku = $skuFinal;
                $skuRegenerado = true;
            }
        }

        // Evitar que valide campos que no estamos modificando si el ORM se pone estricto.
        // Pero Producto::validar() solo valida nombre y unidad, que ya existen.
        $alertas = $producto->validar();
        if (empty($alertas['danger'])) {
            $resultado = $producto->actualizar();
            $respuesta = ['ok' => $resultado['resultado'] ?? true];
            if ($skuRegenerado) {
                $respuesta['nuevo_sku'] = $producto->sku;
            }
            echo json_encode($respuesta);
        } else {
            echo json_encode(['ok' => false, 'error' => implode(', ', $alertas['danger'])]);
        }
        
        exit;
    }

    /**
     * Importación masiva de productos.
     * POST recibe JSON con array de filas del Excel (parseado por SheetJS en el cliente).
     */
    public static function importar(Router $router): void {
        isAuth();
        isRole(['admin']);

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

            $marcaRaw  = $fila['marca'] ?? $fila[1] ?? '';
            $categoriaRaw = $fila['categoria'] ?? '';
            $unidadRaw = strtolower($fila['unidad'] ?? $fila[2] ?? '');
            
            // Asumimos que los precios pueden venir en columnas específicas si hay un encabezado, 
            // de lo contrario intentaremos extraerlos de índices posicionales si están.
            $precioPub = (float)($fila['precio_publico'] ?? $fila[3] ?? 0);
            $precioMay = (float)($fila['precio_mayorista'] ?? $fila[4] ?? 0);
            
            // Tipo y Vencimiento (por defecto si no vienen)
            $tipoRaw   = strtolower($fila['tipo'] ?? 'mercaderia');
            $tipo      = in_array($tipoRaw, $tiposValidos) ? $tipoRaw : 'mercaderia';
            $venc      = in_array(strtolower($fila['maneja_vencimiento'] ?? ''), ['1','si','yes','sí','true']) ? 1 : 0;

            // ─── INFERIR UNIDAD DESDE EL NOMBRE SI VIENE VACÍA ───
            if (empty($unidadRaw)) {
                $nombreLower = strtolower($nombre);
                // Buscar si alguna abreviatura o nombre de unidad está contenida en el nombre del producto
                foreach ($mapaUnidad as $k => $id_val) {
                    // Validamos que sea una palabra completa (ej. no confundir "sl" con "sl" en "sloppy")
                    // Se usan boundaries \b para asegurar que "L" o "Litro" sean palabras aparte
                    if (preg_match("/\b" . preg_quote($k, '/') . "\b/i", $nombreLower)) {
                        $unidadRaw = $k;
                        break;
                    }
                }
            }

            $unidadId  = $mapaUnidad[$unidadRaw] ?? null;

            if (!$unidadId) {
                // Asignar unidad por defecto 'UND' (o la que se llame 'Unidad') si no se encontró otra
                if (isset($mapaUnidad['und'])) {
                    $unidadId = $mapaUnidad['und'];
                } elseif (isset($mapaUnidad['unidad'])) {
                    $unidadId = $mapaUnidad['unidad'];
                } else {
                    $nuevaUni = new UnidadMedida();
                    $nuevaUni->sincronizar(['nombre' => 'Unidad', 'abreviatura' => 'UND']);
                    $resUni = $nuevaUni->crear();
                    $unidadId = $resUni['id'] ?? null;
                    if ($unidadId) {
                        $mapaUnidad['und'] = $unidadId;
                        $unidades[] = (object)['id' => $unidadId, 'abreviatura' => 'UND'];
                    }
                }
            }

            // ─── Buscar o Crear Marca ───
            $marcaId = null;
            if (!empty($marcaRaw)) {
                $marcaExiste = Marca::where('nombre', $marcaRaw);
                if ($marcaExiste) {
                    $marcaId = $marcaExiste[0]->id;
                } else {
                    $nuevaMarca = new Marca();
                    $nuevaMarca->sincronizar(['nombre' => $marcaRaw]);
                    $resMarca = $nuevaMarca->crear();
                    $marcaId = $resMarca['id'];
                }
            }

            // ─── Buscar o Crear Categoria ───
            $categoriaId = null;
            if (!empty($categoriaRaw)) {
                $catExiste = Categoria::where('nombre', $categoriaRaw);
                if ($catExiste) {
                    $categoriaId = $catExiste[0]->id;
                } else {
                    $nuevaCat = new Categoria();
                    $nuevaCat->sincronizar(['nombre' => $categoriaRaw, 'activo' => 1]);
                    $resCat = $nuevaCat->crear();
                    $categoriaId = $resCat['id'];
                }
            }

            // ─── Buscar producto existente por Nombre (o SKU si aplica) ───
            $skuExcel = $fila['sku'] ?? '';
            $p = null;
            if (!empty($skuExcel)) {
                $existe = Producto::where('sku', $skuExcel);
                if (!empty($existe)) $p = $existe[0];
            }
            if (!$p && !empty($nombre)) {
                $existe = Producto::where('nombre', $nombre);
                if (!empty($existe)) $p = $existe[0];
            }

            if ($p) {
                // ─── MODO ACTUALIZAR ───
                // Actualizar atributos si vienen valores válidos en el Excel
                if ($marcaId) $p->marca_id = $marcaId;
                if ($categoriaId) $p->categoria_id = $categoriaId;
                if ($unidadId) $p->unidad_id = $unidadId;
                
                $p->tipo = $tipo;
                $p->maneja_vencimiento = $venc;
                
                // Actualizar precios solo si son mayores a 0 en el Excel
                if ($precioPub > 0) $p->precio_publico = $precioPub;
                if ($precioMay > 0) $p->precio_mayorista = $precioMay;
                
                $alertas = $p->validar();
                if (!empty($alertas['danger'])) {
                    $errores[] = "Fila {$numFila} (Actualización): " . implode(', ', $alertas['danger']);
                    continue;
                }
                
                $p->actualizar();
                $importados++;
            } else {
                // ─── MODO CREAR NUEVO ───
                // Generación automática de SKU
                $marcaClean = preg_replace('/[^A-Za-z0-9]/', '', $marcaRaw);
                $marcaPart = strtoupper(substr($marcaClean, 0, 4));
                if (strlen($marcaPart) < 4) $marcaPart = str_pad($marcaPart, 4, 'X');
                if (empty($marcaRaw)) $marcaPart = 'GENE';

                $catClean = preg_replace('/[^A-Za-z0-9]/', '', $categoriaRaw);
                $catPart = strtoupper(substr($catClean, 0, 4));
                if (strlen($catPart) < 4) $catPart = str_pad($catPart, 4, 'X');
                if (empty($categoriaRaw)) $catPart = 'XXXX';

                $nombreClean = preg_replace('/[^A-Za-z0-9]/', '', $nombre);
                $nombrePart = strtoupper(substr($nombreClean, 0, 4));
                if (strlen($nombrePart) < 4) $nombrePart = str_pad($nombrePart, 4, 'X');

                $abreviaturaReal = 'UND';
                foreach ($unidades as $u) {
                    if ($u->id === $unidadId) {
                        $abreviaturaReal = strtoupper($u->abreviatura);
                        break;
                    }
                }

                $baseSku = "{$marcaPart}-{$catPart}-{$nombrePart}-{$abreviaturaReal}";
                $skuFinal = $baseSku;
                $contadorSku = 1;
                while (!empty(Producto::where('sku', $skuFinal))) {
                    $contadorSku++;
                    $skuFinal = "{$baseSku}-{$contadorSku}";
                }

                $p = new Producto();
                $p->nombre             = $nombre;
                $p->sku                = $skuFinal;
                $p->marca_id           = $marcaId;
                $p->categoria_id       = $categoriaId;
                $p->precio_publico     = $precioPub;
                $p->precio_mayorista   = $precioMay;
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
        }

        header('Content-Type: application/json');
        echo json_encode([
            'ok'         => true,
            'importados' => $importados,
            'errores'    => $errores,
        ]);
    }
}
