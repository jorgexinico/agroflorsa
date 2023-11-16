<?php
// var_dump($operaciones);
// exit;
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
        <th style="color:white">_______________________________</th>
        <th style="font-size:90%;">REPORTE DE COMISIONES REALIZADAS POR EL SERVICIO DE MUSICA MILITAR</th>
    </tr>
</table><br>

<table border="1">
    <thead>
        <tr>
            <th>NO</th>
            <th>BANDA</th>
            <th>LUGAR DE COMISION</th>
            <th>MOTIVO</th>
            <th>CANTIDAD</th>
            <th>FECHA</th>
            <th>INICIA</th>
            <th>TERMINA</th>
            <th>ESTADO</th>

        </tr>
    </thead>
    <tbody id="tabla_body">
        <?php
        if ($operaciones[0] == "") {
            ?>
            <tr>
                <td style="width:15px; text-align:center;" colspan="9">
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
                <td style="width:15px; text-align:center;">
                    <?php
                    echo $contador;
                    ?>
                </td>
                <td style="width:200px; text-align:center;">
                    <?php
                    echo $nombrebanda;
                    ?>
                </td>
                <td style="width:15px; text-align:center;">
                    <?php
                    if ($idcomision == 999) {
                        echo $civil;
                    } else {
                        echo $comisionnombre;
                    }
                    ?>
                </td>

                <td style="width:200px; text-align:center;">
                    <?php
                    echo $motivo
                        ?>
                </td>
                <td style="width:15px; text-align:center;">
                    <?php
                    echo $cantidad
                        ?>
                </td>
                <td style="width:15px; text-align:center;">
                    <?php
                    echo $fecha
                        ?>
                </td>
                <td style="width:15px; text-align:center;">
                    <?php
                    echo $horain
                        ?>
                </td>
                <td style="width:15px; text-align:center;">
                    <?php
                    echo $horafin
                        ?>
                </td>
                <td style="width:150px; text-align:center;">
                    <?php
                    if ($horaactualizada < $horain) {
                        echo 'PENDIENTE';
                    }
                    if ($horaactualizada > $horafin) {
                        echo 'FINALIZADO';
                    }
                    if ($horaactualizada > $horain && $horaactualizada < $horafin) {
                        echo 'EN CURSO';
                    }
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
        <td style="font-size:90%;">Cantidad de Comisiones Realizadas:  <?php
                    echo $total;
                    ?></td>
    </tr>
</table>
<br><br>
<table>
    <tr>
        <td style="color:white">_____________________________________________</td>
        <td style="font-size:90%;">SOLDADO Y PROGRAMADOR FIRME Y LEAL A SU NACIÓN</td>
    </tr>
</table>