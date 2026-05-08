<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-primary bg-gradient text-white p-4 text-center">
            <h2 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i>Consulta rápida de precios</h2>
            <p class="opacity-75 mb-0 mt-1">Busque cualquier producto para ver sus precios actuales al público y mayorista.</p>
        </div>
    </div>
</div>

<div class="row g-3 align-items-center mb-3">
    <div class="col-md-8">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-0"><i class="bi bi-filter text-muted"></i></span>
            <input type="text" id="buscador-precios" class="form-control border-0 py-2 py-md-3" placeholder="Filtrar por nombre, marca o SKU..." autofocus>
            <button class="btn btn-white bg-white border-0 text-primary fs-5" type="button" id="btn-voice-search" title="Búsqueda por voz">
                <i class="bi bi-mic-fill"></i>
            </button>
        </div>
    </div>
    <div class="col-md-4 text-md-end">
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 fs-6">
            <i class="bi bi-box-seam me-2 text-primary"></i>Total: <span id="total-cont" class="fw-bold"><?= count($productos) ?></span> productos
        </span>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-md-4" id="contenedor-precios">
    <?php foreach ($productos as $p): ?>
    <div class="col p-card" data-search="<?= s(strtolower($p['nombre'] . ' ' . $p['sku'] . ' ' . $p['marca'])) ?>">
        <div class="card h-100 border-0 shadow-sm hover-up transition-all overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-light text-muted fw-normal px-2 py-1"><i class="bi bi-upc-scan me-1"></i><?= s($p['sku'] ?: 'SIN SKU') ?></span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><?= s($p['unidad']) ?></span>
                </div>
                <h6 class="card-title fw-bold mb-1 text-dark text-truncate" style="font-size: 1.1rem;"><?= s($p['nombre']) ?></h6>
                <p class="text-muted small mb-3"><i class="bi bi-tag-fill me-1"></i><?= s($p['marca'] ?: 'Sin Marca') ?></p>
                
                <div class="mb-3">
                    <p class="small text-muted fw-bold mb-2 border-bottom pb-1">Existencias por Sucursal:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($sucursales as $suc): ?>
                            <?php $qty = $stockMap[$p['id']][$suc->id] ?? 0; ?>
                            <div class="badge <?= $qty > 0 ? 'bg-primary' : 'bg-secondary' ?> bg-opacity-10 <?= $qty > 0 ? 'text-primary' : 'text-secondary' ?> border <?= $qty > 0 ? 'border-primary' : 'border-secondary' ?> border-opacity-25 px-2 py-1">
                                <i class="bi bi-shop me-1"></i><?= s($suc->nombre) ?>: <span class="fw-bold"><?= $qty ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="row g-2 mt-auto">
                    <div class="col-12 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100 add-to-cart" 
                                data-id="<?= $p['id'] ?>" 
                                data-nombre="<?= s($p['nombre'] . ' (' . $p['unidad'] . ')') ?>">
                            <i class="bi bi-cart-plus me-1"></i>Agregar al carrito
                        </button>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-center border border-primary border-opacity-10">
                            <span class="text-primary smaller d-block mb-0 fw-semibold">Público</span>
                            <span class="fw-bold text-primary">Q<?= number_format($p['precio_publico'], 2) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-info bg-opacity-10 rounded-3 text-center border border-info border-opacity-10">
                            <span class="text-info smaller d-block mb-0 fw-semibold">Mayorista</span>
                            <span class="fw-bold text-info">Q<?= number_format($p['precio_mayorista'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Flotante del Carrito -->
<div id="cart-float" class="position-fixed bottom-0 start-50 translate-middle-x w-100 px-3 pb-3 shadow-lg d-none" style="z-index: 1050; max-width: 500px;">
    <div class="card border-0 bg-dark text-white rounded-pill px-3 py-2 shadow">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="bi bi-cart4 fs-4"></i>
                    <span id="cart-count-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        0
                    </span>
                </div>
                <div class="d-none d-sm-block ms-2">
                    <div class="fw-bold" style="font-size: 0.9rem; line-height: 1;">Vender</div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="<?= $base ?>/ventas/nueva" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                    Cobrar <i class="bi bi-arrow-right-short ms-1"></i>
                </a>
                <button id="clear-cart" class="btn btn-sm btn-outline-light rounded-circle p-1 border-0 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Vaciar carrito">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="no-results" class="text-center py-5 d-none">
    <i class="bi bi-search fs-1 text-muted opacity-25 d-block mb-3"></i>
    <p class="text-muted">No se encontraron productos que coincidan con su búsqueda.</p>
</div>

<style>
.hover-up:hover {
    transform: translateY(-8px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.155)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
.smaller { font-size: 0.7rem; }
@keyframes pulse-mic {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
.pulse-anim {
    animation: pulse-mic 1s infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador-precios');
    const cards = document.querySelectorAll('.p-card');
    const contenedor = document.getElementById('contenedor-precios');
    const noResults = document.getElementById('no-results');
    const totalCont = document.getElementById('total-cont');
    const cartFloat = document.getElementById('cart-float');
    const cartCountBadge = document.getElementById('cart-count-badge');
    const clearCartBtn = document.getElementById('clear-cart');

    // ── GESTIÓN DE CARRITO (localStorage) ──
    let cart = JSON.parse(localStorage.getItem('agro_cart') || '[]');

    function updateCartUI() {
        if (cart.length > 0) {
            cartFloat.classList.remove('d-none');
            cartCountBadge.textContent = cart.length;
        } else {
            cartFloat.classList.add('d-none');
        }
        localStorage.setItem('agro_cart', JSON.stringify(cart));
    }

    // Inicializar UI
    updateCartUI();

    // Agregar al carrito
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            
            // Evitar duplicados si se desea
            if (!cart.some(item => item.id === id)) {
                cart.push({ id, nombre });
                updateCartUI();
                
                // Feedback visual
                this.innerHTML = '<i class="bi bi-check2"></i> Agregado';
                this.classList.replace('btn-outline-primary', 'btn-success');
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Agregar al carrito';
                    this.classList.replace('btn-success', 'btn-outline-primary');
                }, 1500);
            }
        });
    });

    // Vaciar carrito
    clearCartBtn.addEventListener('click', () => {
        cart = [];
        updateCartUI();
    });

    // Función auxiliar para quitar acentos
    const removeAccents = (str) => {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };

    // ── FILTRO ──
    buscador?.addEventListener('input', function() {
        const query = removeAccents(this.value.toLowerCase().trim());
        let count = 0;

        cards.forEach(card => {
            const searchText = removeAccents(card.dataset.search);
            if (searchText.includes(query)) {
                card.style.display = '';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        totalCont.textContent = count;
        if (count === 0) {
            contenedor.classList.remove('row');
            contenedor.classList.add('d-none');
            noResults.classList.remove('d-none');
        } else {
            contenedor.classList.add('row');
            contenedor.classList.remove('d-none');
            noResults.classList.add('d-none');
        }
    });

    // ── BÚSQUEDA POR VOZ ──
    const btnVoice = document.getElementById('btn-voice-search');
    if (btnVoice && ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = 'es-ES';
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onstart = function() {
            btnVoice.innerHTML = '<i class="bi bi-mic-fill text-danger pulse-anim"></i>';
            buscador.placeholder = 'Escuchando...';
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            buscador.value = transcript;
            // Disparar evento input para filtrar
            buscador.dispatchEvent(new Event('input'));
        };

        recognition.onerror = function(event) {
            console.error("Error de reconocimiento de voz:", event.error);
            buscador.placeholder = 'Filtrar por nombre, marca o SKU...';
        };

        recognition.onend = function() {
            btnVoice.innerHTML = '<i class="bi bi-mic-fill"></i>';
            if (!buscador.value) {
                buscador.placeholder = 'Filtrar por nombre, marca o SKU...';
            }
        };

        btnVoice.addEventListener('click', () => {
            recognition.start();
        });
    } else if (btnVoice) {
        btnVoice.style.display = 'none';
    }
});
</script>
