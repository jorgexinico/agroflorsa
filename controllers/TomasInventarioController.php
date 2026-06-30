<?php
namespace Controllers;

use MVC\Router;
use Models\TomaInventario;
use Models\TomaInventarioDetalle;
use Models\Sucursal;
use Model\ActiveRecord;
use Models\Producto;

class TomasInventarioController {

    public static function index(Router $router): void {
        isAuth();
        
        $sucursal_id = $_SESSION['sucursal_id'] ?? 0;
        $rol = $_SESSION['usuario_rol'] ?? '';
        
        if ($rol === 'admin' || $rol === 'supervisor') {
            $tomas = TomaInventario::getTomas();
        } else {
            $tomas = TomaInventario::getTomasPorSucursal($sucursal_id);
        }

        $sucursales = Sucursal::getActivas();

        $router->render('inventario/tomas/index', [
            'titulo' => 'Tomas de Inventario',
            'tomas' => $tomas,
            'sucursales' => $sucursales,
            'sucursal_sesion' => $sucursal_id
        ]);
    }

    public static function crear(): void {
        isAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal_id = (int)($_POST['sucursal_id'] ?? 0);
            $observacion = trim($_POST['observacion'] ?? '');

            if (!$sucursal_id) {
                echo json_encode(['ok' => false, 'error' => 'Sucursal inválida']);
                return;
            }

            try {
                ActiveRecord::getDB()->beginTransaction();

                $toma = new TomaInventario();
                $toma->sucursal_id = $sucursal_id;
                $toma->usuario_id = (int)$_SESSION['usuario_id'];
                $toma->observacion = $observacion;
                $toma->fecha_inicio = date('Y-m-d H:i:s');

                $resultado = $toma->crear();
                $toma_id = $resultado['id'];

                // Insertar todos los productos activos de la sucursal en el detalle
                // Haremos un query INSERT SELECT para ser eficientes
                $sql_insert = "
                    INSERT INTO tomas_inventario_detalle (toma_id, producto_id, stock_sistema, conteo_fisico, diferencia, estado)
                    SELECT :tid, p.id, COALESCE(iep.cantidad, 0), 0, 0, 'pendiente'
                    FROM productos p
                    LEFT JOIN inventario_existencias_producto iep ON iep.producto_id = p.id AND iep.sucursal_id = :sid
                    WHERE p.activo = 1
                ";
                
                ActiveRecord::fetchRaw($sql_insert, [
                    ':tid' => $toma_id,
                    ':sid' => $sucursal_id
                ]);

                ActiveRecord::getDB()->commit();

                echo json_encode(['ok' => true, 'id' => $toma_id]);
            } catch (\Exception $e) {
                ActiveRecord::getDB()->rollBack();
                echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public static function conteo(Router $router): void {
        isAuth();
        
        $id = (int)($_GET['id'] ?? 0);
        $toma = TomaInventario::find($id);

        if (!$toma) {
            header('Location: /inventario/tomas');
            exit;
        }

        $sucursal = Sucursal::find($toma->sucursal_id);
        $detalles = TomaInventarioDetalle::getByToma($id);

        $marcas = \Models\Marca::all('nombre');
        $categorias = \Models\Categoria::whereArray(['activo' => 1]);
        $unidades = \Models\UnidadMedida::all();

        $router->render('inventario/tomas/conteo', [
            'titulo' => 'Conteo de Inventario - ' . $sucursal->nombre,
            'toma' => $toma,
            'detalles' => $detalles,
            'sucursal' => $sucursal,
            'marcas' => $marcas,
            'categorias' => $categorias,
            'unidades' => $unidades,
            'layout' => 'clean' // Usar layout limpio para máximo espacio
        ]);
    }

    public static function guardarDetalle(): void {
        isAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $detalle_id = (int)($data['id'] ?? 0);
            $conteo_fisico = (float)($data['conteo'] ?? 0);

            $detalle = TomaInventarioDetalle::find($detalle_id);
            if (!$detalle) {
                echo json_encode(['ok' => false, 'error' => 'Detalle no encontrado']);
                return;
            }

            // BUSCAMOS A QUÉ TOMA PERTENECE PARA SABER LA SUCURSAL
            $toma = TomaInventario::find($detalle->toma_id);
            
            // EL TRUCO DE ORO: Obtenemos el stock REAL en el milisegundo en que se confirmó el conteo
            $existencia = ActiveRecord::fetchFirstRaw(
                "SELECT cantidad FROM inventario_existencias_producto WHERE producto_id = :pid AND sucursal_id = :sid",
                [':pid' => $detalle->producto_id, ':sid' => $toma->sucursal_id]
            );
            $stockRealTime = $existencia ? (float)$existencia['cantidad'] : 0;

            $detalle->stock_sistema = $stockRealTime; // Actualizamos la foto al instante actual
            $detalle->conteo_fisico = $conteo_fisico;
            $detalle->diferencia = $conteo_fisico - $stockRealTime;
            $detalle->estado = 'contado';
            $detalle->fecha_conteo = date('Y-m-d H:i:s');
            
            $resultado = $detalle->actualizar();
            
            echo json_encode([
                'ok' => $resultado, 
                'diferencia' => $detalle->diferencia,
                'stock_sistema' => $stockRealTime,
                'fecha' => date('H:i')
            ]);
        }
    }

    public static function crearProductoRapido(): void {
        isAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $skuInput = trim($_POST['sku'] ?? '');
            $toma_id = (int)($_POST['toma_id'] ?? 0);

            if (!$nombre) {
                echo json_encode(['ok' => false, 'error' => 'Nombre requerido']);
                return;
            }

            try {
                ActiveRecord::getDB()->beginTransaction();

                // ─── Generación de SKU Automático (si no se proporciona) ───
                $skuFinal = $skuInput;
                $marcaId = (int)($_POST['marca_id'] ?? 0) ?: null;
                $categoriaId = (int)($_POST['categoria_id'] ?? 0) ?: null;
                $unidadId = (int)($_POST['unidad_id'] ?? 0);

                if (empty($skuFinal)) {
                    $marcaRaw = '';
                    if ($marcaId) {
                        $m = \Models\Marca::find($marcaId);
                        $marcaRaw = $m ? $m->nombre : '';
                    }

                    $categoriaRaw = '';
                    if ($categoriaId) {
                        $c = \Models\Categoria::find($categoriaId);
                        $categoriaRaw = $c ? $c->nombre : '';
                    }

                    $unidadAbr = 'UND';
                    if ($unidadId) {
                        $u = \Models\UnidadMedida::find($unidadId);
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

                    $nombreClean = preg_replace('/[^A-Za-z0-9]/', '', $nombre);
                    $nombrePart = strtoupper(substr($nombreClean, 0, 4));
                    if (strlen($nombrePart) < 4) $nombrePart = str_pad($nombrePart, 4, 'X');

                    $abreviaturaReal = strtoupper($unidadAbr);

                    $baseSku = "{$marcaPart}-{$catPart}-{$nombrePart}-{$abreviaturaReal}";
                    $skuFinal = $baseSku;
                    $contadorSku = 1;
                    
                    while (!empty(Producto::where('sku', $skuFinal))) {
                        $contadorSku++;
                        $skuFinal = "{$baseSku}-{$contadorSku}";
                    }
                }

                $producto = new Producto();
                $producto->sincronizar([
                    'nombre' => $nombre,
                    'sku' => $skuFinal,
                    'precio_publico' => (float)($_POST['precio_publico'] ?? 0),
                    'precio_mayorista' => (float)($_POST['precio_mayorista'] ?? 0),
                    'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
                    'marca_id' => $marcaId,
                    'categoria_id' => $categoriaId,
                    'unidad_id' => $unidadId,
                    'tipo' => $_POST['tipo'] ?? 'mercaderia',
                    'maneja_vencimiento' => isset($_POST['maneja_vencimiento']) ? 1 : 0,
                    'activo' => 1
                ]);
                
                $producto->creado_en = date('Y-m-d H:i:s');
                $resProd = $producto->crear();
                $producto_id = $resProd['id'];

                if ($toma_id > 0) {
                    $detalle = new TomaInventarioDetalle();
                    $detalle->sincronizar([
                        'toma_id' => $toma_id,
                        'producto_id' => $producto_id,
                        'stock_sistema' => 0,
                        'conteo_fisico' => 0,
                        'diferencia' => 0,
                        'estado' => 'pendiente'
                    ]);
                    $resDet = $detalle->crear();
                    $detalle_id = $resDet['id'];
                }

                ActiveRecord::getDB()->commit();

                // Recuperamos el nombre de la unidad para mandarlo al frontend
                $unidadNombre = 'Unidad';
                if ($unidadId) {
                    $u = \Models\UnidadMedida::find($unidadId);
                    if ($u) $unidadNombre = $u->nombre;
                }

                echo json_encode([
                    'ok' => true, 
                    'producto_id' => $producto_id,
                    'detalle_id' => $detalle_id ?? null,
                    'nombre' => $nombre,
                    'sku' => $skuFinal,
                    'unidad_nombre' => $unidadNombre
                ]);

            } catch (\Exception $e) {
                ActiveRecord::getDB()->rollBack();
                echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public static function finalizar(): void {
        isAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $toma_id = (int)($_POST['toma_id'] ?? 0);
            
            $toma = TomaInventario::find($toma_id);
            if (!$toma || $toma->estado !== 'en_progreso') {
                echo json_encode(['ok' => false, 'error' => 'Toma inválida o ya finalizada']);
                return;
            }

            try {
                ActiveRecord::getDB()->beginTransaction();

                $toma->estado = 'completada';
                $toma->fecha_fin = date('Y-m-d H:i:s');
                $toma->actualizar();

                // Aquí generaríamos el Ajuste de Inventario automático.
                // Por razones de seguridad, vamos a crear el encabezado del AjusteInventario
                $ajuste = new \Models\AjusteInventario();
                $ajuste->sucursal_id = $toma->sucursal_id;
                $ajuste->usuario_id = (int)$_SESSION['usuario_id'];
                $ajuste->fecha = date('Y-m-d H:i:s');
                $ajuste->motivo = 'Ajuste automático por Toma de Inventario #' . $toma->id;
                
                $resAjuste = $ajuste->crear();
                $ajuste_id = (int)$resAjuste['id'];

                $detalles = TomaInventarioDetalle::getByToma($toma_id);
                
                foreach ($detalles as $d) {
                    if ((float)$d['diferencia'] !== 0.0) {
                        // Crear detalle de ajuste
                        $detAjuste = new \Models\AjusteDetalle();
                        $detAjuste->ajuste_id = $ajuste_id;
                        $detAjuste->producto_id = (int)$d['producto_id'];
                        $detAjuste->cantidad = (float)$d['diferencia'];
                        $detAjuste->crear();

                        // En vez de hacer queries manuales, usamos la misma lógica centralizada de inventario
                        $ok = \Models\Inventario::ajustarStock($toma->sucursal_id, $d['producto_id'], $d['diferencia']);
                        
                        if ($ok) {
                            \Models\Inventario::registrarMovimiento([
                                'sucursal_id'     => $toma->sucursal_id,
                                'producto_id'     => $d['producto_id'],
                                'lote_id'         => null,
                                'tipo'            => 'ajuste',
                                'signo'           => $d['diferencia'] > 0 ? 1 : -1,
                                'cantidad'        => abs($d['diferencia']),
                                'referencia_tipo' => 'ajuste',
                                'referencia_id'   => $ajuste_id,
                                'costo_unitario'  => null
                            ]);
                        }
                    }
                }

                ActiveRecord::getDB()->commit();
                echo json_encode(['ok' => true]);
            } catch (\Exception $e) {
                ActiveRecord::getDB()->rollBack();
                echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            }
        }
    }
}
