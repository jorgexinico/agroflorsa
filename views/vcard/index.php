<div class="row">
    <form class="col-lg-6 border bg-light rounded p-4" id="formularioVcard">
        <h1>Ingreso de información</h1>
        <div class="form-text text-danger">Esta aplicación no almacena ningun tipo de información</div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre completo">
                    <label for="nombre">Nombre</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Apellido completo">
                    <label for="apellido">Apellido</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="organizacion" id="organizacion" class="form-control" placeholder="Organización">
                    <label for="organizacion">Organización</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="puesto" id="puesto" class="form-control" placeholder="Puesto">
                    <label for="puesto">Puesto</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Dirección">
                    <label for="direccion">Dirección</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="correo" name="correo" id="correo" class="form-control" placeholder="Correo electrónico">
                    <label for="email">Correo electrónico Personal</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="email" name="correo_empresa" id="correo_empresa" class="form-control" placeholder="Correo electrónico empresarial">
                    <label for="correo_empresa">Correo electrónico Empresarial</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="tel" name="telefono_personal" id="telefono_personal" class="form-control" placeholder="Número telefónico">
                    <label for="telefono_personal">Número teléfonico</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="tel" name="telefono_trabajo" id="telefono_trabajo" class="form-control" placeholder="Número telefónico">
                    <label for="telefono_trabajo">Número teléfonico Empresarial</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button class="btn btn-primary w-100">Generar Vcard</button>
            </div>
        </div>
    </form>
    <div class="col-lg-6">
        <iframe id="iframeQR" frameborder="0" class="w-100 h-100">

        </iframe>
    </div>
    <script src="<?= asset('./build/js/vcard/index.js') ?>"></script>
</div>