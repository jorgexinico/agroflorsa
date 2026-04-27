<?php
// views/ventas/envio.php
// Esta vista no usa el layout principal, se imprime como HTML plano para facilidad de impresión.
$appName = $_ENV['APP_NAME'] ?? 'agroflorsa';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Envío - Venta #<?= $venta['id'] ?></title>
    <style>
        /* CSS Específico para impresión y diseño similar al JPG */
        @page { size: auto; margin: 0mm; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .envio-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 10px;
            position: relative;
        }
        /* Header */
        .h-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .h-logo img { width: 120px; }
        .h-info { text-align: right; }
        .h-info h2 { margin: 0; font-size: 20px; color: #2e7d32; }
        .h-info p { margin: 2px 0; }
        
        .title-envio {
            text-align: center;
            color: #1976d2;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        /* Bloques amarillos y rojos */
        .top-blocks {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .block-fecha {
            border: 1px solid #000;
            border-collapse: collapse;
            text-align: center;
        }
        .block-fecha th {
            background-color: #ffeb3b; /* Amarillo */
            border: 1px solid #000;
            padding: 2px 10px;
            font-size: 11px;
        }
        .block-fecha td {
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 14px;
        }
        .block-envio {
            background-color: #ffeb3b;
            color: #d32f2f; /* Rojo */
            font-weight: bold;
            font-size: 24px;
            padding: 5px 20px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
        }
        .block-numero {
            color: #d32f2f;
            font-size: 18px;
            font-weight: bold;
            padding: 5px 10px;
        }

        /* Datos del cliente */
        .cliente-info {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            line-height: 1.8;
        }
        .cliente-row { display: flex; align-items: flex-end; }
        .c-label { font-weight: bold; margin-right: 5px; }
        .c-value { flex-grow: 1; border-bottom: 1px solid #000; min-height: 20px; padding-left: 5px; }

        /* Tabla de detalle */
        .tabla-detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .tabla-detalle th {
            background-color: #ffeb3b;
            color: #d32f2f;
            border: 1px solid #000;
            padding: 5px;
            font-size: 14px;
        }
        .tabla-detalle td {
            border: 1px solid #000;
            padding: 8px 5px;
            height: 20px;
        }
        .t-cant { width: 10%; text-align: center; }
        .t-desc { width: 50%; }
        .t-pu { width: 20%; text-align: right; }
        .t-valor { width: 20%; text-align: right; font-weight: bold; }

        /* Footer */
        .footer-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-letras {
            flex-grow: 1;
            border: 1px solid #000;
            padding: 10px;
            margin-right: 20px;
            font-size: 12px;
            height: 40px;
        }
        .total-box {
            background-color: #ffeb3b;
            border: 1px solid #000;
            padding: 10px 20px;
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        /* Ocultar botón al imprimir */
        @media print {
            .no-print { display: none !important; }
            .envio-container { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="margin-bottom: 20px; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">🖨️ Imprimir</button>
    <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">❌ Cerrar</button>
</div>

<div class="envio-container">
    
    <!-- Header -->
    <div class="h-header">
        <div class="h-logo">
            <!-- Si tienes logotipo, puedes agregarlo aquí -->
            <h2>DISTRIBUIDORA AGRÍCOLA</h2>
            <h1 style="color:#2e7d32; margin:0;">FLOR DE MAYO</h1>
        </div>
        <div class="h-info">
            <p>Tel: <?= htmlspecialchars($venta['sucursal_telefono'] ?: 'Sin asignar') ?></p>
            <p>Venta de Agroquímicos y Fertilizantes</p>
        </div>
    </div>

    <!-- Bloques amarillos y Número -->
    <div class="top-blocks">
        <table class="block-fecha">
            <tr>
                <th>DIA</th>
                <th>MES</th>
                <th>AÑO</th>
            </tr>
            <tr>
                <?php $date = new DateTime($venta['fecha']); ?>
                <td><?= $date->format('d') ?></td>
                <td><?= $date->format('m') ?></td>
                <td><?= $date->format('y') ?></td>
            </tr>
        </table>

        <div class="block-envio">ENVIO</div>

        <div class="block-numero">
            Nº <?= str_pad($venta['id'], 6, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <!-- Datos del Cliente -->
    <div class="cliente-info">
        <div class="cliente-row mb-2">
            <span class="c-label">NOMBRE:</span>
            <div class="c-value"><?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor Final') ?></div>
        </div>
        <div class="cliente-row mb-2">
            <span class="c-label">DIRECCION:</span>
            <div class="c-value"><?= htmlspecialchars($venta['cliente_direccion'] ?? '') ?></div>
        </div>
        <div class="cliente-row">
            <span class="c-label">TEL:</span>
             <div class="c-value" style="width: 30%; flex-grow: 0;"><?= htmlspecialchars($venta['cliente_telefono'] ?? '') ?></div>
             <span class="c-label mt-2 px-2">NIT:</span>
             <div class="c-value" style="flex-grow: 1;"><?= htmlspecialchars($venta['cliente_nit'] ?? '') ?></div>
        </div>
    </div>

    <!-- Tabla de Detalle -->
    <table class="tabla-detalle">
        <thead>
            <tr>
                <th class="t-cant">CANT.</th>
                <th class="t-desc">D E S C R I P C I O N</th>
                <th class="t-pu">P. UNIT.</th>
                <th class="t-valor">V A L O R</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
            <tr>
                <td class="t-cant"><?= number_format($item['cantidad'], 2) ?></td>
                <td class="t-desc"><?= htmlspecialchars($item['producto_nombre'] . ' ' . $item['unidad_abreviatura']) ?></td>
                <td class="t-pu"><?= number_format($item['precio_unitario'], 2) ?></td>
                <td class="t-valor"><?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- Filas vacías para rellenar el formato -->
            <?php 
            $filasRestantes = 10 - count($detalle);
            for($i = 0; $i < $filasRestantes; $i++): 
            ?>
            <tr>
                <td class="t-cant"></td>
                <td class="t-desc"></td>
                <td class="t-pu"></td>
                <td class="t-valor"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Total -->
    <div class="footer-total">
        <div class="total-letras">
            <strong>TOTAL EN LETRAS:</strong>
            <br>
            <!-- Aquí iría una función de PHP para convertir números a letras si la tuvieras -->
        </div>
        <div class="total-box">
            TOTAL Q. <?= number_format($venta['total'], 2) ?>
        </div>
    </div>

</div>

</body>
</html>
