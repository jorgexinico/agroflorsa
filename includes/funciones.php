<?php

/**
 * funciones.php — Helpers globales de Agroflorsa
 */

// ── DEBUG ─────────────────────────────────────────────
function debuguear($variable) {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// ── SALIDA SEGURA HTML ────────────────────────────────
function s($html): string {
    return htmlspecialchars((string)$html, ENT_QUOTES, 'UTF-8');
}

// ── FORMATO MONEDA ────────────────────────────────────
function formatMoney(float $valor, string $simbolo = 'Q'): string {
    return $simbolo . ' ' . number_format($valor, 2);
}

// ── ASSETS ───────────────────────────────────────────
/**
 * Devuelve la URL pública de un asset compilado.
 */
function asset(string $ruta): string {
    $version = '1.0.4'; // forzar recarga
    $appName = trim($_ENV['APP_NAME'] ?? '', '/');
    
    // Si estamos en entorno XAMPP local (con APP_NAME seteado), inyectamos /public/
    if (!empty($appName)) {
        return '/' . $appName . '/public/' . ltrim($ruta, '/') . '?v=' . $version;
    }
    
    // Producción: APP_NAME viene vacío, url() ya funciona perfecto.
    return url($ruta) . '?v=' . $version;
}

// ── REDIRECCIÓN ──────────────────────────────────────
/**
 * Genera una URL absoluta al recurso indicado.
 */
function url(string $path = ''): string {
    $base = trim($_ENV['APP_NAME'] ?? '', '/');
    return ($base ? '/' . $base : '') . '/' . ltrim($path, '/');
}

function redirectTo(string $ruta): void {
    header('Location: ' . url($ruta));
    exit;
}

// ── AUTENTICACIÓN ─────────────────────────────────────
/**
 * Verifica que el usuario esté autenticado.
 * No llama session_start() porque ya se hizo en app.php.
 */
function isAuth(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . url('/login'));
        exit;
    }
}

/**
 * Verifica que el usuario tenga alguno de los roles indicados.
 * @param string[] $roles  Ej: ['admin','supervisor']
 */
function isRole(array $roles): void {
    if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], $roles)) {
        header('Location: ' . url('/dashboard?err=403'));
        exit;
    }
}

function isNotAuth(): void {
    if (isset($_SESSION['usuario_id'])) {
        redirectTo('/dashboard');
    }
}

// ── API HELPERS ──────────────────────────────────────
function isAuthApi(): void {
    getHeadersApi();
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(["mensaje" => "No está autenticado", "codigo" => 4]);
        exit;
    }
}

function getHeadersApi(): void {
    header("Content-type: application/json; charset=utf-8");
}

// ── MENSAJES FLASH EN URL ─────────────────────────────
function mensajeOk(int $code): string {
    $mensajes = [
        1 => 'Registro creado exitosamente.',
        2 => 'Registro actualizado exitosamente.',
        3 => 'Registro eliminado.',
    ];
    return $mensajes[$code] ?? 'Operación exitosa.';
}