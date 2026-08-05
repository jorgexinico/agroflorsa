<?php
// views/auth/login.php
?>
<style>
    /* Premium Login Redesign */
    body.ag-auth-body {
        margin: 0 !important;
        padding: 0 !important;
        height: 100vh !important;
        width: 100vw !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        /* Capa oscura superpuesta y la imagen flor de mayo de fondo */
        background-image: 
            linear-gradient(135deg, rgba(31, 41, 55, 0.5) 0%, rgba(17, 24, 39, 0.8) 100%),
            url('<?= $base ?>/images/flor%20de%20mayo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
        background-repeat: no-repeat !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    }

    .premium-login-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        width: 90%;
        max-width: 420px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        animation: cardFloatUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
        transform: translateY(30px);
        box-sizing: border-box;
    }

    @keyframes cardFloatUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .premium-login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .premium-login-header i {
        font-size: 3.5rem;
        /* Gradiente agrícola (tonos verdes) */
        background: linear-gradient(135deg, #22c55e, #15803d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
        margin-bottom: 0.5rem;
        animation: pulseIcon 2.5s infinite ease-in-out;
    }

    @keyframes pulseIcon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .premium-login-header h2 {
        font-weight: 800;
        color: #111827;
        margin: 0;
        font-size: 1.8rem;
        letter-spacing: -0.025em;
    }

    .premium-login-header p {
        color: #4b5563;
        font-size: 0.95rem;
        margin-top: 0.5rem;
    }

    .premium-form-group {
        margin-bottom: 1.5rem;
    }

    .premium-form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .premium-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .premium-input-wrapper i {
        position: absolute;
        left: 1.25rem;
        color: #9ca3af;
        font-size: 1.2rem;
        transition: color 0.3s ease;
        z-index: 2;
    }

    .premium-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3.25rem;
        border: 2px solid #e5e7eb;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.95);
        font-size: 1rem;
        color: #1f2937;
        transition: all 0.3s ease;
        outline: none;
        box-sizing: border-box;
    }

    .premium-input:focus {
        border-color: #16a34a;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
    }

    .premium-input:focus + i, .premium-input-wrapper:focus-within i {
        color: #16a34a;
    }

    .premium-btn {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #22c55e, #15803d);
        color: white;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .premium-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(22, 163, 74, 0.5);
    }

    .premium-btn:active {
        transform: translateY(1px);
    }

    .premium-alert {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        color: #991b1b;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .premium-alert i {
        font-size: 1.2rem;
    }
</style>

<div class="premium-login-card">
  <div class="premium-login-header">
    <i class="bi bi-flower3"></i>
    <h2>Agroflorsa</h2>
    <p>Sistema de control administrativo</p>
  </div>

  <?php if (!empty($alertas)): foreach ($alertas as $tipo => $msgs): foreach ($msgs as $msg): ?>
  <div class="premium-alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span><?= s($msg) ?></span>
  </div>
  <?php endforeach; endforeach; endif; ?>

  <form method="POST" action="">
    <div class="premium-form-group">
      <label for="usuario" class="premium-form-label">Usuario</label>
      <div class="premium-input-wrapper">
        <i class="bi bi-person"></i>
        <input type="text" name="usuario" id="usuario"
               class="premium-input" placeholder="Ingresa tu usuario"
               autocomplete="username" autofocus required>
      </div>
    </div>
    
    <div class="premium-form-group">
      <label for="password" class="premium-form-label">Contraseña</label>
      <div class="premium-input-wrapper">
        <i class="bi bi-lock"></i>
        <input type="password" name="password" id="password"
               class="premium-input" placeholder="••••••••"
               autocomplete="current-password" required>
      </div>
    </div>
    
    <button type="submit" class="premium-btn">
      <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
    </button>
  </form>

  <p class="text-center mt-4 mb-0" style="color: #6b7280; font-size: 0.85rem; text-align: center; font-weight: 500;">
    Agroflorsa &copy; <?= date('Y') ?>
  </p>
</div>
