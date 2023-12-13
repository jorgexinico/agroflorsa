<?php
// var_dump($fechaReporte);
// exit;

date_default_timezone_set("America/Caracas");
setlocale(LC_TIME, 'es_VE.UTF-8', 'esp');

$dia = date("m");
$Nombre_año = date("Y");


if ($dia == 1) {
    $meshoy = 'ENERO';
}
if ($dia == 2) {
    $meshoy = 'FEBRERO';
}
if ($dia == 3) {
    $meshoy = 'MARZO';
}
if ($dia == 4) {
    $meshoy = 'ABRIL';
}
if ($dia == 5) {
    $meshoy = 'MAYO';
}
if ($dia == 6) {
    $meshoy = 'JUNIO';
}
if ($dia == 7) {
    $meshoy = 'JULIO';
}
if ($dia == 8) {
    $meshoy = 'AGOSTO';
}
if ($dia == 9) {
    $meshoy = 'SEPTIEMBRE';
}
if ($dia == 10) {
    $meshoy = 'OCTUBRE';
}
if ($dia == 11) {
    $meshoy = 'NOVIEMBRE';
}
if ($dia == 12) {
    $meshoy = 'DICIEMBRE';
}
?>


<table>
    <tr>
        <td>REPÚBLICA DE GUATEMALA</td>
        <td style="color:white">____________________________________________________________________________</td>
        <td>EJÉRCITO DE GUATEMALA</td>
    </tr>
</table>
<br><br>
<table>
    <tr>
        <th style="color:white">________________</th>
        <th style="font-size:90%;">REPORTE DE COMISIONES REALIZADAS EN EL MES DE <?php echo $meshoy ?> POR EL SERVICIO DE MUSICA MILITAR</th>
    </tr>
</table><br>


<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    #tabla_info {
        width: 80%;
        margin: 0 auto;
        border-collapse: collapse;
    }

    th.th_banda,
    td.th_stilo {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        font-size: 12px;
    }

    /* th {
        background-color: #f2f2f2;
    } */
</style>





<table class="tabla_info" id="tabla_info">
    <thead>
        <tr>
            <th class="th_banda">NO</th>
            <th class="th_banda">BANDA</th>
            <th class="th_banda">LUGAR DE COMISION</th>
            <th class="th_banda">MOTIVO</th>
            <th class="th_banda">CANTIDAD</th>
            <th class="th_banda">FECHA</th>
            <th class="th_banda">INICIA</th>
            <th class="th_banda">TERMINA</th>
            <!-- <th>ESTADO</th> -->

        </tr>
    </thead>
    <tbody id="tabla_body">
        <?php
        if ($operaciones[0] == "") {
            ?>
            <tr>
                <td class="th_stilo" style="width:15px; text-align:center;" colspan="9">
                    <?php
                    echo "NO SE REPORTAN REGISTROS";
                    ?>
                </td>

            </tr>
            <?php
        }
        $key = 0;
        foreach ($operaciones as $key => $value) {

            $total = $key + 1;

            $contador = $value['contador'];
            $nombrebanda = $value['nombrebanda'];
            $idcomision = $value['idcomision'];
            $comisionnombre = $value['comisionnombre'];
            $civil = $value['civil'];
            $motivo = $value['motivo'];
            $cantidad = $value['cantidad'];
            $fecha = $value['fecha'];
            $horain = $value['horain'];
            $horafin = $value['horafin'];
            $horaactualizada = $value['horaactualizada'];

            ?>

            <tr>
                <td  class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    echo $contador;
                    ?>
                </td>
                <td class="th_stilo" style="width:200px; text-align:center;">
                    <?php
                    echo $nombrebanda;
                    ?>
                </td>
                <td class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    if ($idcomision == 999) {
                        echo $civil;
                    } else {
                        echo $comisionnombre;
                    }
                    ?>
                </td>

                <td class="th_stilo" style="width:200px; text-align:center;">
                    <?php
                    echo $motivo
                        ?>
                </td>
                <td class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    echo $cantidad
                        ?>
                </td>
                <td class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    echo $fecha
                        ?>
                </td>
                <td class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    echo $horain
                        ?>
                </td>
                <td class="th_stilo" style="width:15px; text-align:center;">
                    <?php
                    echo $horafin
                        ?>
                </td>





            </tr>

            <?php

        }
        ?>


    </tbody>

</table><br>


<table>
    <tr>
        <td style="color:white">__________________________________________________</td>
        <td style="font-size:90%;">Cantidad de Comisiones Realizadas:
            <?php
            echo $total;
            ?>
        </td>
    </tr>
</table>
<br><br>
<table>
    <tr>
        <td style="color:white">_____________________________________________</td>
        <td style="font-size:90%;">SOLDADO Y PROGRAMADOR FIRME Y LEAL A SU NACIÓN</td>
    </tr>
</table>