<?php
// views/ventas/comprobante.php
// Se imprime como HTML plano
$appName = $_ENV['APP_NAME'] ?? 'agroflorsa';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante - Venta #<?= $venta['id'] ?></title>
    <style>
        @page { size: letter; margin: 15mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            color: #333;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #2e7d32;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info h1 {
            color: #2e7d32;
            margin: 0 0 5px 0;
            font-size: 28px;
            text-transform: uppercase;
        }
        .company-info p { margin: 2px 0; color: #555; }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0 0 10px 0;
            color: #555;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-details table {
            float: right;
            border-collapse: collapse;
        }
        .invoice-details th {
            background-color: #f5f5f5;
            padding: 5px 10px;
            border: 1px solid #ddd;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        .invoice-details td {
            padding: 5px 10px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #d32f2f;
        }

        /* Cliente Info */
        .client-info {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .client-info table {
            width: 100%;
        }
        .client-info th {
            text-align: left;
            width: 100px;
            color: #666;
            padding-bottom: 8px;
        }
        .client-info td {
            font-weight: bold;
            padding-bottom: 8px;
        }

        /* Tabla de Productos */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #2e7d32;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            border: 1px solid #2e7d32;
        }
        .items-table td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) { background-color: #fafafa; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* Totales */
        .totals-container {
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }
        .totals-table th {
            padding: 10px;
            text-align: right;
            color: #555;
            border-bottom: 1px solid #ddd;
        }
        .totals-table td {
            padding: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
        }
        .total-row th, .total-row td {
            font-size: 20px;
            color: #2e7d32;
            border-bottom: none;
            border-top: 2px solid #2e7d32;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        /* UI Controls (No print) */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background-color: #fff; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="margin-bottom: 20px; text-align: center; background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #2e7d32; color: white; border: none; border-radius: 4px; font-weight: bold;">🖨️ Imprimir Factura</button>
    <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 4px; margin-left: 10px;">❌ Cerrar</button>
</div>

<div class="invoice-container">
    
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>AGROFLORSA</h1>
            <p><strong>Distribuidora Agrícola Flor de Mayo</strong></p>
            <p>Venta de Agroquímicos y Fertilizantes</p>
            <p>Teléfono: <?= htmlspecialchars($venta['sucursal_telefono'] ?: 'Sin asignar') ?></p>
        </div>
        <div class="invoice-details">
            <h2>COMPROBANTE DE COMPRA</h2>
            <table>
                <tr>
                    <th>FECHA</th>
                    <th>COMPROBANTE N°</th>
                </tr>
                <tr>
                    <td><?= date('d/m/Y', strtotime($venta['fecha'])) ?></td>
                    <td><?= str_pad($venta['id'], 6, '0', STR_PAD_LEFT) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Cliente -->
    <div class="client-info">
        <table>
            <tr>
                <th>CLIENTE:</th>
                <td><?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor Final') ?></td>
                <th>NIT:</th>
                <td><?= htmlspecialchars($venta['cliente_nit'] ?? 'C/F') ?></td>
            </tr>
            <tr>
                <th>DIRECCIÓN:</th>
                <td><?= htmlspecialchars($venta['cliente_direccion'] ?? 'Ciudad') ?></td>
                <th>TELÉFONO:</th>
                <td><?= htmlspecialchars($venta['cliente_telefono'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <!-- Detalles de Venta -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">CANTIDAD</th>
                <th style="width: 50%;">DESCRIPCIÓN</th>
                <th class="text-right" style="width: 20%;">PRECIO UNIT.</th>
                <th class="text-right" style="width: 20%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
            <tr>
                <td class="text-center"><?= number_format($item['cantidad'], 2) ?></td>
                <td><?= htmlspecialchars($item['producto_nombre'] . ' (' . $item['unidad_abreviatura'] . ')') ?></td>
                <td class="text-right">Q <?= number_format($item['precio_unitario'], 2) ?></td>
                <td class="text-right">Q <?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totals-container">
        <table class="totals-table">
            <tr class="total-row">
                <th>TOTAL A PAGAR:</th>
                <td>Q <?= number_format($venta['total'], 2) ?></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Tipo de Pago:</strong> <span style="text-transform: capitalize;"><?= $venta['tipo_pago'] ?></span></p>
        <?php if ($venta['observacion']): ?>
            <p><strong>Observaciones:</strong> <?= htmlspecialchars($venta['observacion']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>¡Gracias por su compra en Agroflorsa!</p>
        <p>Este documento es un comprobante interno de venta de mercadería.</p>
    </div>

</div>

</body>
</html>
