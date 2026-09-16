<?php
/** Ejecutar en el servidor que aloja Agroflorsa y Login. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';

function settings(string $path, array $values): void {
    $text = file_get_contents($path);
    foreach ($values as $key => $value) {
        if (strpbrk((string)$value, "'\r\n") !== false) throw new RuntimeException('Valor de configuración inválido.');
        $line = $key."='".$value."'";
        $pattern = '/^'.preg_quote($key, '/').'\s*=.*$/m';
        $text = preg_match($pattern, $text) ? preg_replace_callback($pattern, fn() => $line, $text) : $text."\n".$line."\n";
    }
    if (file_put_contents($path, $text, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar '.$path);
}
function connection(array $env): PDO {
    return new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_NAME'].';charset=utf8mb4', $env['DB_USER'], $env['DB_PASS'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
function column(PDO $db, string $table, string $name, string $definition): void {
    if (!in_array($name, $db->query('SHOW COLUMNS FROM '.$table)->fetchAll(PDO::FETCH_COLUMN), true)) $db->exec("ALTER TABLE $table ADD $name $definition");
}
try {
    $options = getopt('', ['portal-user:', 'local-user:', 'login-dir:', 'apply']);
    $root = dirname(__DIR__);
    $login = realpath($options['login-dir'] ?? dirname($root).'/login');
    if (!$login || empty($options['portal-user']) || empty($options['local-user'])) throw new RuntimeException('Uso: php bin/setup-production-sso.php --portal-user=ivan --local-user=USUARIO_AGROFLORSA [--login-dir=/var/www/login] [--apply]');
    $appPath = $root.'/includes/.env';
    $loginPath = $login.'/.env';
    $appEnv = Dotenv\Dotenv::parse(file_get_contents($appPath));
    $portalEnv = Dotenv\Dotenv::parse(file_get_contents($loginPath));
    $app = connection($appEnv);
    $portal = connection($portalEnv);
    $q = $app->prepare('SELECT id,usuario,rol FROM usuarios WHERE usuario=? AND activo=1');
    $q->execute([$options['local-user']]); $local = $q->fetch();
    $q = $portal->prepare('SELECT id,usuario FROM usuarios WHERE usuario=? AND activo=1');
    $q->execute([$options['portal-user']]); $central = $q->fetch();
    if (!$local || !$central) throw new RuntimeException('Ambos usuarios deben existir y estar activos. No se crean usuarios ni roles automáticamente.');
    $issuer = 'https://login.agroflorsa.com';
    $callback = 'https://agroflorsa.com/sso';
    $configPath = $login.'/config/applications.php';
    $config = file_get_contents($configPath);
    $pattern = '~([\'\"]agroflorsa[\'\"]\s*=>\s*)[\'\"][^\'\"]*[\'\"]~';
    if (preg_match_all($pattern, $config) !== 1) throw new RuntimeException('No se reconoce la entrada agroflorsa de config/applications.php.');
    foreach ([$appPath, $loginPath, $configPath] as $path) if (!is_writable($path)) throw new RuntimeException('Sin permiso de escritura: '.$path);
    foreach (['optional_sso.sql','session_activity.sql'] as $file) if (!is_readable($login.'/database/'.$file)) throw new RuntimeException('Actualiza el código de Login: falta '.$file);
    foreach (['curl','openssl','pdo_mysql'] as $extension) if (!extension_loaded($extension)) throw new RuntimeException('Falta extensión PHP: '.$extension);
    echo 'Vínculo: '.$central['usuario'].' (Login #'.$central['id'].') -> '.$local['usuario'].' (Agroflorsa #'.$local['id'].', rol '.$local['rol'].").\n";
    if (!isset($options['apply'])) { echo "Comprobación terminada sin cambios. Añade --apply para preparar y activar SSO.\n"; exit; }

    // Respaldos de archivos privados, fuera de public/. Las migraciones son aditivas.
    $backup = $root.'/storage/sso-backup-'.date('Ymd-His').'-'.bin2hex(random_bytes(3));
    if (!mkdir($backup, 0700, true)) throw new RuntimeException('No se pudo crear el respaldo.');
    foreach ([$appPath, $loginPath, $configPath] as $i => $path) {
        if (!copy($path, $backup.'/'.$i.'.bak')) throw new RuntimeException('No se pudo respaldar '.$path);
        chmod($backup.'/'.$i.'.bak', 0600);
    }
    $keyPath = function (string $key, string $fallback) use ($portalEnv, $login): string {
        $path = $portalEnv[$key] ?? $fallback;
        return str_starts_with($path, '/') ? $path : $login.'/'.$path;
    };
    $privatePath = $keyPath('JWT_PRIVATE_KEY', 'storage/jwt-private.pem');
    $publicPath = $keyPath('JWT_PUBLIC_KEY', 'storage/jwt-public.pem');
    if (file_exists($privatePath)) {
        $key = openssl_pkey_get_private(file_get_contents($privatePath));
    } else {
        if (file_exists($publicPath)) throw new RuntimeException('Existe clave pública sin privada; restaura la privada antes de continuar.');
        $key = openssl_pkey_new(['private_key_bits'=>3072, 'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
        if (!$key || !openssl_pkey_export($key, $private)) throw new RuntimeException('No se pudo generar la clave RSA.');
        if (file_put_contents($privatePath, $private) === false) throw new RuntimeException('No se pudo guardar la clave privada.');
        chmod($privatePath, 0600);
    }
    if (!$key) throw new RuntimeException('Clave privada inválida.');
    $details = openssl_pkey_get_details($key);
    if ($details['type'] !== OPENSSL_KEYTYPE_RSA) throw new RuntimeException('La clave debe ser RSA.');
    if (file_exists($publicPath) && trim(file_get_contents($publicPath)) !== trim($details['key'])) throw new RuntimeException('Las claves pública y privada no coinciden.');
    foreach ([$publicPath, $root.'/storage/portal-public.pem'] as $path) if (file_put_contents($path, $details['key']) === false) throw new RuntimeException('No se pudo guardar la clave pública.');

    column($portal, 'usuarios', 'version_sesion', 'INT NOT NULL DEFAULT 0');
    column($portal, 'usuarios', 'es_admin', 'BOOLEAN NOT NULL DEFAULT 0');
    $portal->exec(file_get_contents($login.'/database/optional_sso.sql'));
    $portal->exec(file_get_contents($login.'/database/session_activity.sql'));
    column($portal, 'codigos_sso', 'sesion_id', 'CHAR(64) NULL');
    column($app, 'usuarios', 'portal_issuer', 'VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin NULL');
    column($app, 'usuarios', 'portal_subject', 'VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL');
    if (!in_array('uq_portal_identity', array_column($app->query('SHOW INDEX FROM usuarios')->fetchAll(), 'Key_name'), true)) $app->exec('ALTER TABLE usuarios ADD UNIQUE KEY uq_portal_identity (portal_issuer,portal_subject)');
    (new Classes\PortalIdentity($app))->link($issuer, (string)$central['id'], (int)$local['id']);
    $q = $portal->prepare('SELECT id,client_secret_hash FROM aplicaciones WHERE slug=?');
    $q->execute(['agroflorsa']); $existing = $q->fetch();
    $secret = $appEnv['SSO_CLIENT_SECRET'] ?? '';
    if (!$existing || strlen($secret)<32 || !password_verify($secret, $existing['client_secret_hash'])) $secret = bin2hex(random_bytes(32));
    $portal->beginTransaction();
    $q = $portal->prepare('INSERT INTO aplicaciones (slug,nombre,callback_url,client_secret_hash,activa) VALUES (?,?,?,?,1) ON DUPLICATE KEY UPDATE callback_url=VALUES(callback_url),client_secret_hash=VALUES(client_secret_hash),activa=1');
    $q->execute(['agroflorsa','Agroflorsa',$callback,password_hash($secret, PASSWORD_DEFAULT)]);
    $q = $portal->prepare('INSERT IGNORE INTO usuario_aplicacion (usuario_id,aplicacion_id) SELECT ?,id FROM aplicaciones WHERE slug=?');
    $q->execute([$central['id'],'agroflorsa']);
    $portal->commit();
    if (file_put_contents($configPath, preg_replace_callback($pattern, fn($m) => $m[1]."'https://agroflorsa.com/'", $config), LOCK_EX) === false) throw new RuntimeException('No se pudo actualizar el catálogo.');
    settings($appPath, ['DEBUG_MODE'=>'0','APP_NAME'=>'','SESSION_SECURE'=>'true','SSO_ENABLED'=>'true','PORTAL_URL'=>$issuer,'SSO_PORTAL_URL'=>$issuer,'SSO_BACKCHANNEL_URL'=>$issuer,'SSO_ISSUER'=>$issuer,'SSO_CLIENT_ID'=>'agroflorsa','SSO_CLIENT_SECRET'=>$secret,'SSO_PUBLIC_KEY'=>$root.'/storage/portal-public.pem']);
    $enabled = array_filter(array_map('trim', explode(',', $portalEnv['SSO_APPLICATIONS'] ?? 'agroflorsa')));
    $enabled[] = 'agroflorsa';
    settings($loginPath, ['SSO_ENABLED'=>'true','SSO_APPLICATIONS'=>implode(',',array_unique($enabled)),'JWT_ISSUER'=>$issuer,'JWT_PRIVATE_KEY'=>$privatePath,'JWT_PUBLIC_KEY'=>$publicPath,'SESSION_SECURE'=>'true']);
    echo "Configuración completada. Secretos guardados sin mostrarlos.\nRespaldo de archivos: $backup\nPrueba ahora https://agroflorsa.com en una ventana privada y entra con tu usuario de Login.\n";
} catch (Throwable $e) {
    if (isset($portal) && $portal->inTransaction()) $portal->rollBack();
    // No imprimir excepciones PDO: pueden incluir valores privados del servidor.
    fwrite(STDERR, $e instanceof PDOException ? "Falló una operación MySQL. Revisa conexión, permisos y esquema. Puede haber cambios parciales; no se borraron datos.\n" : $e->getMessage()."\n");
    exit(1);
}
