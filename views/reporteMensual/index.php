<?php


date_default_timezone_set("America/Caracas");
setlocale(LC_TIME, 'es_VE.UTF-8', 'esp');

$dia = date("m");
$Nombre_año = date("Y");


if ($dia == 1) {
    $meshoy = 'Enero';
}
if ($dia == 2) {
    $meshoy = 'Febrero';
}
if ($dia == 3) {
    $meshoy = 'Marzo';
}
if ($dia == 4) {
    $meshoy = 'Abril';
}
if ($dia == 5) {
    $meshoy = 'Mayo';
}
if ($dia == 6) {
    $meshoy = 'Junio';
}
if ($dia == 7) {
    $meshoy = 'Julio';
}
if ($dia == 8) {
    $meshoy = 'Agosto';
}
if ($dia == 9) {
    $meshoy = 'Septiembre';
}
if ($dia == 10) {
    $meshoy = 'Octubre';
}
if ($dia == 11) {
    $meshoy = 'Noviembre';
}
if ($dia == 12) {
    $meshoy = 'Diciembre';
}
?>

<div class="row text-center">
    <div class="col">
        <h3>Reporte de las Comisiones realizadas el mes de
            <?php echo $meshoy . "&nbsp;de&nbsp;" . $Nombre_año ?>
        </h3>
    </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <form id="formFormulario" class="col-10 border p-2 mt-2 bg-light" enctype="multipart/form-data">
            <div class="col-12">
                <div class="row mb-3">
                    <div class="col-lg-4">

                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-center">
                            <a type=" button" class="btn btn-success m-1" id="imprimir">Imprimir Reporte
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                                    <path
                                        d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <a type=" button" class="btn btn-info m-1" data-bs-toggle="modal"
                                data-bs-target="#reporteanterior">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-stopwatch" viewBox="0 0 16 16">
                                    <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5V5.6z" />
                                    <path
                                        d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64a.715.715 0 0 1 .012-.013l.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354a.512.512 0 0 1-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5zM8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3z" />
                                </svg> Reporte Mes
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                                    <path
                                        d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="row justify-content-center" id="divTabla">
        <div class="col-lg-10">
            <table id="FormularioTabla" class="table table-bordered table-hover w-100 small">
                <thead>
                    <tr class="align-middle text-center small">
                        <th>NO.</th>
                        <th>BANDA</th>
                        <th>DESTINO</th>
                        <th>MOTIVO</th>
                        <th>CANT. PER.</th>
                        <th>FECHA</th>
                        <th>INICIO</th>
                        <th>FINALIZO</th>
                    </tr>
                </thead>
                <tbody class="align-middle text-center small">

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="entradafab" name="modalPersonal" tabindex="-1" role="dialog"
    aria-labelledby="infoModalLabel" aria-text="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">REGISTRE COMISION</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formIngresoComision" class="col-12 border p-2 mt-2 bg-light" enctype="multipart/form-data">

                    <b>Mostrar contenido civil?</b>
                    <input type="checkbox" name="checkcivil" id="checkcivil" value="1" />

                    <input type="hidden" name="id" id="id" class="form-control bg-white" ?>

                    <?php
                    date_default_timezone_set('America/Guatemala');
                    $fechadeldia = date("Y-m-d");
                    ?>

                    <h1></h1>
                    <input type="hidden" name="fecha" id="fecha" class="form-control bg-white"
                        value="<?php echo $fechadeldia ?>" placeholder="Y-M-D">

                    <?php
                    date_default_timezone_set('America/Guatemala');
                    $horaforma = date("H:i");
                    ?>
                    <input type="hidden" name="horavalidar" id="horavalidar" class="form-control bg-white"
                        value="<?php echo $horaforma ?>" placeholder="H:i">
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <label for="banda">BANDA </label>
                            <select name="banda" id="banda" class="form-control">
                                <option value="">Seleccione...</option>
                                <?php foreach ($banda as $banda): ?>
                                    <option value="<?= $banda['id'] ?>">
                                        <?= $banda['banda'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label for="comision">COMISION </label>
                            <div id='divComisionMilitar'>
                                <select name="comisionMilitar" id="comisionMilitar" class="form-control" style="">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($comando as $comando): ?>
                                        <option value="<?= $comando['dep_llave'] ?>">
                                            <?= $comando['dep_desc_md'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div id='divComisionCivil' style="display: none;">
                                <input type="text" name="comision1Civil" id="comision1Civil" maxlgth="50" minlength="3"
                                    class="form-control"
                                    onKeyUp="document.getElementById(this.id).value=document.getElementById(this.id).value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <label for="actividad">ACTIVIDAD </label>
                            <select name="actividad" id="actividad" class="form-control" style="display: block;">
                                <option value="">Seleccione...</option>
                                <?php foreach ($actividad as $actividad): ?>
                                    <option value="<?= $actividad['id'] ?>">
                                        <?= $actividad['actividad'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label for="cantidad">CANT. PERSONAL</label>
                            <input type="text" name="cantidad" id="cantidad" value="" maxlength="9"
                                onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"
                                class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <label for="cantidad">HORA DE INICIO</label>
                            <input type="time" name="horain" id="horain" value="" maxlength="9" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label for="cantidad">HORA FINALIZA</label>
                            <input type="time" name="horafin" id="horafin" value="" maxlength="9" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <br>
                            <input type="submit" name="submit" class="btn btn-warning form-control bg-success"
                                id="guardar_nuevo" value="GUARDAR" data-bs-dismiss="modal" disabled></input>
                        </div>
                        <div class="col-lg-6">
                            <br>
                            <button type="button" class="btn btn-secondary form-control bg-danger"
                                data-bs-dismiss="modal" id="cerrar">CERRAR</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
        </form>
    </div>
</div>


<div class="modal fade" id="reporteanterior" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">

                <h4 class="modal-title">Ingrese Mes a Buscar</h4>

                <div class="modal-body">

                    <form class="col-12 border  bg-light" enctype="multipart/form-data" id="ReporteBusqueda">
                        <div class="row mb-3">


                            <div class="col-lg-6">

                                
                                <select id="mesSelector"  aria-label="Default select example" class="form-select form-select-lg mb-3 text-center">
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div class="col-lg-6">

                                <a class="btn bg-info form-control " id="ReporteDia">IMPRIMIR</a>

                            </div>
                        </div>
                    </form>

                    <div class="col-lg-12">
                        <button type="button" class="btn btn-secondary form-control bg-danger" data-bs-dismiss="modal"
                            id="cerrar">CERRAR</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="build/js/reporteMensual/index.js"></script>