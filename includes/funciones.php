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
 * El document root de Docker es /var/www/html/public/,
 * así que los assets en public/build/ se sirven en /build/.
 * En local con XAMPP donde el proyecto está en htdocs/agroflorsa/,
 * cambia esta función a: '/' . $_ENV['APP_NAME'] . '/public/' . ltrim($ruta, '/')
 */
function asset(string $ruta): string {
    return '/' . ltrim($ruta, '/');
}

// ── REDIRECCIÓN ──────────────────────────────────────
function redirectTo(string $ruta): void {
    header('Location: /' . $_ENV['APP_NAME'] . $ruta);
    exit;
}

// ── AUTENTICACIÓN ─────────────────────────────────────
/**
 * Verifica que el usuario esté autenticado.
 * No llama session_start() porque ya se hizo en app.php.
 */
function isAuth(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /' . $_ENV['APP_NAME'] . '/login');
        exit;
    }
}

/**
 * Verifica que el usuario tenga alguno de los roles indicados.
 * @param string[] $roles  Ej: ['admin','supervisor']
 */
function isRole(array $roles): void {
    if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], $roles)) {
        header('Location: /' . $_ENV['APP_NAME'] . '/dashboard?err=403');
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