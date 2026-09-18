<?php
// Diagnostico de solo lectura. No imprime secretos, hashes, usuarios ni URLs privadas.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';
ini_set('display_errors', '0');
$failed = 0;
function result(bool $ok, string $message): void {
    global $failed;
    if (!$ok) $failed++;
    echo ($ok ? 'OK: ' : 'ERROR: ').$message.PHP_EOL;
}
function readSettings(string $path): array {
    if (!is_readable($path)) throw new RuntimeException('No se puede leer el archivo de configuracion requerido.');
    return Dotenv\Dotenv::parse(file_get_contents($path));
}
function connectReadOnly(array $env): PDO {
    foreach (['DB_HOST','DB_NAME','DB_USER','DB_PASS'] as $key) {
        if (!isset($env[$key])) throw new RuntimeException('Falta '.$key.'.');
    }
    return new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_NAME'].';charset=utf8mb4',
        $env['DB_USER'], $env['DB_PASS'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT=>5]);
}
$stage = 'configuracion';
try {
    $options = getopt('', ['login-dir:', 'config-only']);
    $root = dirname(__DIR__);
    $login = realpath($options['login-dir'] ?? dirname($root).'/login');
    if (!$login) throw new RuntimeException('No se encuentra Login. Usa --login-dir=/ruta/login.');
    $app = readSettings($root.'/includes/.env');
    $portal = readSettings($login.'/.env');
    foreach (['Agroflorsa'=>$app,'Login'=>$portal] as $name=>$env) {
        result(filter_var($env['SSO_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN), $name.': SSO habilitado');
    }
    foreach (['SSO_PORTAL_URL','SSO_ISSUER','SSO_CLIENT_ID','SSO_CLIENT_SECRET'] as $key) {
        result(is_string($app[$key] ?? null) && $app[$key] !== '', 'Agroflorsa: '.$key.' definido');
    }
    result(($app['SSO_CLIENT_ID'] ?? '')==='agroflorsa', 'Identificador de cliente agroflorsa');
    result(!empty($app['SSO_ISSUER']) && $app['SSO_ISSUER']===($portal['JWT_ISSUER'] ?? null), 'Emisor identico en ambas aplicaciones');
    $endpoint = $app['SSO_BACKCHANNEL_URL'] ?? $app['SSO_PORTAL_URL'] ?? '';
    $isLocal = static fn($host)=>is_string($host) && ($host==='localhost' || $host==='127.0.0.1' || str_ends_with($host,'.localhost'));
    result(parse_url($endpoint,PHP_URL_SCHEME)==='https' ||
        (parse_url($endpoint,PHP_URL_SCHEME)==='http' && $isLocal(parse_url($endpoint,PHP_URL_HOST)) && $isLocal(parse_url($app['SSO_PORTAL_URL'] ?? '',PHP_URL_HOST))),
        'Canal de canje HTTPS (HTTP solo en localhost)');
    foreach (['curl','pdo_mysql'] as $extension) result(extension_loaded($extension),'Extension PHP '.$extension);
    foreach (['Agroflorsa'=>$root,'Login'=>$login] as $name=>$directory) {
        $sessions=$directory.'/storage/sessions';
        result(is_dir($sessions) ? is_writable($sessions) : is_writable($directory.'/storage'),$name.': directorio de sesiones escribible por este usuario CLI');
    }
    result(str_contains(file_get_contents($login.'/controllers/SsoController.php'), "'response_format'"), 'Codigo de Login contiene soporte para identity');
    echo 'Huella Agroflorsa: '.hash_file('sha256',$root.'/controllers/PortalController.php').PHP_EOL;
    echo 'Huella Login: '.hash_file('sha256',$login.'/controllers/SsoController.php').PHP_EOL;
    if (!isset($options['config-only'])) {
        $stage='base_login';
        $central=connectReadOnly($portal);
        $q=$central->prepare('SELECT id,activa,callback_url,client_secret_hash FROM aplicaciones WHERE slug=?');
        $q->execute(['agroflorsa']); $registered=$q->fetch();
        result((bool)$registered,'Aplicacion registrada en Login');
        if ($registered) {
            result((int)$registered['activa']===1,'Aplicacion activa en Login');
            result(!empty($app['SSO_CLIENT_SECRET']) && password_verify($app['SSO_CLIENT_SECRET'],(string)$registered['client_secret_hash']), 'Secreto de Agroflorsa coincide con el hash de Login');
            $callback=parse_url($registered['callback_url']);
            result(($callback['scheme'] ?? '')==='https' || (($callback['scheme'] ?? '')==='http' && $isLocal($callback['host'] ?? '')), 'Callback usa transporte permitido');
            result(($callback['path'] ?? '')==='/'.trim(($app['APP_NAME'] ?? '').'/sso','/') && !isset($callback['query']) && !isset($callback['fragment']), 'Ruta de callback coincide con APP_NAME y /sso');
            $central->query('SELECT code_hash,usuario_id,aplicacion_id,expira_en,usado_en FROM codigos_sso LIMIT 0');
            result(true,'Esquema de codigos SSO disponible');
            $q=$central->prepare('SELECT u.id FROM usuarios u JOIN usuario_aplicacion ua ON ua.usuario_id=u.id WHERE ua.aplicacion_id=? AND u.activo=1');
            $q->execute([$registered['id']]); $subjects=$q->fetchAll(PDO::FETCH_COLUMN);
            result(count($subjects)>0,'Login tiene usuarios activos asignados a Agroflorsa');
            $stage='base_agroflorsa';
            $local=connectReadOnly($app);
            $local->query('SELECT portal_issuer,portal_subject FROM usuarios LIMIT 0');
            $local->query('SELECT id,sucursal_id,usuario_id,estado FROM turnos LIMIT 0');
            result(true,'Columnas de identidad y turnos disponibles');
            $duplicates=$local->query('SELECT COUNT(*) FROM (SELECT portal_issuer,portal_subject FROM usuarios WHERE portal_issuer IS NOT NULL AND portal_subject IS NOT NULL GROUP BY portal_issuer,portal_subject HAVING COUNT(*)>1) duplicates')->fetchColumn();
            result((int)$duplicates===0,'No hay identidades locales duplicadas');
            $find=$local->prepare('SELECT COUNT(*) FROM usuarios WHERE portal_issuer=? AND portal_subject=? AND activo=1');
            $missing=0;
            foreach ($subjects as $subject) {
                $find->execute([$app['SSO_ISSUER'] ?? '',(string)$subject]);
                if ((int)$find->fetchColumn()!==1) $missing++;
            }
            result($missing===0,'Usuarios asignados sin vinculo local activo y unico: '.$missing);
        }
    }
} catch (Throwable $e) {
    $detail=$e instanceof PDOException ? 'SQLSTATE='.preg_replace('/[^A-Z0-9]/','',(string)$e->getCode()).' MySQL='.(int)($e->errorInfo[1] ?? 0) : 'Revisa archivos y valores requeridos.';
    result(false,'Etapa '.$stage.'. '.$detail);
}
echo "Solo lectura: no se modificaron configuracion, usuarios ni secretos.\n";
exit($failed > 0 ? 1 : 0);
