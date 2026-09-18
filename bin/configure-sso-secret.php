<?php
// Configura únicamente el secreto de Agroflorsa; nunca imprime credenciales.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';
ini_set('display_errors', '0');
$handle = null;
$db = null;
$original = null;
$written = false;
try {
    $options = getopt('', ['apply', 'login-dir:']);
    $root = dirname(__DIR__);
    $login = realpath($options['login-dir'] ?? dirname($root).'/login');
    if (!$login || !is_readable($login.'/.env')) throw new RuntimeException('No se puede leer el .env de Login.');
    $portal = Dotenv\Dotenv::parse(file_get_contents($login.'/.env'));
    $path = $root.'/includes/.env';
    $handle = fopen($path, isset($options['apply']) ? 'r+' : 'r');
    if (!$handle || !flock($handle, LOCK_EX)) throw new RuntimeException('No se pudo abrir/bloquear includes/.env.');
    $original = stream_get_contents($handle);
    $app = Dotenv\Dotenv::parse($original);
    $db = new PDO('mysql:host='.$portal['DB_HOST'].';port='.($portal['DB_PORT'] ?? '3306').';dbname='.$portal['DB_NAME'].';charset=utf8mb4',
        $portal['DB_USER'], $portal['DB_PASS'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
    $db->beginTransaction();
    $q = $db->prepare('SELECT id,client_secret_hash FROM aplicaciones WHERE slug=? FOR UPDATE');
    $q->execute(['agroflorsa']);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new RuntimeException('No existe la aplicación agroflorsa en Login.');
    $secret = $app['SSO_CLIENT_SECRET'] ?? '';
    $existingHash = (string)($row['client_secret_hash'] ?? '');
    if ($existingHash !== '' && password_verify($secret, $existingHash)) {
        $db->rollBack();
        echo "OK: el secreto de Agroflorsa ya coincide con Login. No se modificó nada.\n";
    } else {
        if ($existingHash !== '') throw new RuntimeException('Login ya tiene otro secreto registrado. No se reemplazó: requiere revisar la configuración existente.');
        if (!isset($options['apply'])) {
            $db->rollBack();
            echo "Login no tiene secreto. Ejecuta este comando con --apply para configurarlo.\n";
        } else {
            if (strlen($secret)<32 || preg_match('/REEMPLAZAR|SECRETO_REAL|REDACTED|[<>]/i', $secret)) $secret = bin2hex(random_bytes(32));
            // Formato dotenv seguro; conservar otros valores, incluyendo la BD.
            $encoded = str_replace(['\\','"','$'], ['\\\\','\\"','\\$'], $secret);
            $line = 'SSO_CLIENT_SECRET="'.$encoded.'"';
            $updated = preg_replace('/^[\t ]*(?:export[\t ]+)?SSO_CLIENT_SECRET[\t ]*=.*(?:\r?\n|$)/m', '', $original);
            $updated = rtrim($updated, "\r\n")."\n".$line."\n";
            $parsed = Dotenv\Dotenv::parse($updated);
            if (($parsed['SSO_CLIENT_SECRET'] ?? null) !== $secret) throw new RuntimeException('No se pudo codificar el secreto sin alterar su valor.');
            $backupDir = $root.'/storage';
            if (!is_dir($backupDir) && !mkdir($backupDir,0700,true)) throw new RuntimeException('No se pudo crear storage.');
            $backup = $backupDir.'/sso-env-backup-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
            $previousMask = umask(0077);
            $saved = file_put_contents($backup, $original, LOCK_EX);
            umask($previousMask);
            if ($saved !== strlen($original)) throw new RuntimeException('No se pudo guardar el respaldo privado.');
            $hash = password_hash($secret, PASSWORD_DEFAULT);
            $q = $db->prepare('UPDATE aplicaciones SET client_secret_hash=? WHERE id=?');
            $q->execute([$hash, $row['id']]);
            rewind($handle);
            $written = true;
            if (fwrite($handle, $updated)!==strlen($updated) || !ftruncate($handle,strlen($updated)) || !fflush($handle)) throw new RuntimeException('No se pudo guardar includes/.env.');
            $db->commit();
            $written = false;
            echo "OK: secreto guardado en Agroflorsa y hash registrado en Login.\nRespaldo privado: $backup\n";
        }
    }
} catch (Throwable $e) {
    if ($db instanceof PDO && $db->inTransaction()) $db->rollBack();
    if ($written && is_resource($handle) && is_string($original)) {
        rewind($handle);
        $restored = fwrite($handle,$original)===strlen($original) && ftruncate($handle,strlen($original)) && fflush($handle);
        if (!$restored) fwrite(STDERR,"No se pudo restaurar el archivo; recupera el respaldo privado en storage.\n");
    }
    fwrite(STDERR, "No se completó la configuración. ".($e instanceof PDOException ? 'Revisa conexión y permisos de MySQL.' : ($e instanceof RuntimeException ? $e->getMessage() : 'Revisa permisos y formato de los archivos .env.'))."\n");
    exit(1);
} finally {
    if (is_resource($handle)) { flock($handle,LOCK_UN); fclose($handle); }
}
