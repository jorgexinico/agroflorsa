<div class="row">
    <form class="col-lg-6 border bg-light rounded p-4" id="formularioVcard">
        <h1>Ingreso de información</h1>
        <div class="form-text text-danger">Esta aplicación no almacena ningun tipo de información</div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre completo">
                    <label for="nombre">Nombre completo</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Correo electrónico">
                    <label for="email">Correo electrónico Personal</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="email" name="email-work" id="email-work" class="form-control" placeholder="Correo electrónico">
                    <label for="email-work">Correo electrónico Empresarial</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="tel" name="telefono" id="telefono" class="form-control" placeholder="Número telefónico">
                    <label for="telefono">Número teléfonico</label>
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