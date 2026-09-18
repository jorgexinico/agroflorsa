<?php
// Pruebas aisladas: HTTP simulado, SQLite en memoria y claves efímeras de prueba.
namespace Classes {
    function curl_init($url) { $GLOBALS['requests'][]=$url; return new \stdClass(); }
    function curl_setopt_array($curl,$options) { $GLOBALS['requestOptions']=$options; return true; }
    function curl_exec($curl) { return $GLOBALS['response']; }
    function curl_getinfo($curl,$option) { return 200; }
    function curl_errno($curl) { return 0; }
    function curl_close($curl) {}
    function header($value) { $GLOBALS['redirect']=$value; }
}
namespace {
    if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
    require dirname(__DIR__).'/vendor/autoload.php';
    function check($condition,$message) { if (!$condition) throw new \RuntimeException($message); }
    function url($path='') { return '/'.ltrim($path,'/'); }
    function redirectTo($path) { $GLOBALS['redirect']=$path; }
    $mode=$argv[1] ?? 'jwt';
    $_ENV=['SSO_PORTAL_URL'=>'https://login.example','SSO_BACKCHANNEL_URL'=>'https://login.example',
        'SSO_ISSUER'=>'https://login.example','SSO_CLIENT_ID'=>'agroflorsa','SSO_CLIENT_SECRET'=>'test-only'];
    $GLOBALS['requests']=[];
    if ($mode==='logout') {
        session_start();
        $_SESSION=['usuario_id'=>17];
        $_ENV['SSO_ENABLED']='true';
        register_shutdown_function(function () {
            check($_SESSION===[] && session_status()===PHP_SESSION_NONE,'Logout debe destruir la sesión');
            check($GLOBALS['requests']===[],'Logout no debe consultar Login');
            echo "OK logout\n";
        });
        \Controllers\AuthController::logout(new \MVC\Router());
    }
    if ($mode==='start') {
        $_SESSION=[];
        register_shutdown_function(function () {
            check(preg_match('/^[a-f0-9]{64}$/',$_SESSION['portal_state'] ?? '')===1,'State inválido');
            check(str_contains($GLOBALS['redirect'],'/authorize?client_id=agroflorsa&state='.$_SESSION['portal_state']),'Redirección inválida');
            echo "OK start\n";
        });
        \Classes\SsoClient::start();
    }
    $db=new \PDO('sqlite::memory:',null,null,[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]);
    $db->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY,nombre TEXT,rol TEXT,activo INTEGER,portal_issuer TEXT,portal_subject TEXT)');
    $db->exec("INSERT INTO usuarios VALUES (17,'Operador','vendedor',1,'https://login.example','3')");
    $db->exec('CREATE TABLE turnos (id INTEGER,usuario_id INTEGER,sucursal_id INTEGER,estado TEXT)');
    $db->exec("INSERT INTO turnos VALUES (8,17,4,'abierto')");
    if (in_array($mode,['get','post','local','inactive','missing'],true)) {
        $_SESSION=in_array($mode,['get','post'],true) ? ['portal_state'=>'pending'] : ['usuario_id'=>17,'usuario_rol'=>'admin','portal_expires'=>1,'portal_refresh'=>'obsolete'];
        if ($mode==='inactive') $db->exec('UPDATE usuarios SET activo=0');
        if ($mode==='missing') $db->exec('DELETE FROM usuarios');
        $_SERVER=['REQUEST_URI'=>'/ventas','REQUEST_METHOD'=>$mode==='post'?'POST':'GET'];
        register_shutdown_function(function () use ($mode) {
            check($GLOBALS['requests']===[],'Hubo contacto HTTP con Login');
            if ($mode==='post') check(http_response_code()===401,'POST debe ser 401');
            if (in_array($mode,['inactive','missing'],true)) check(http_response_code()===403 && $_SESSION===[],'Debe denegar usuario local');
            if ($mode==='get') check(!isset($_SESSION['usuario_id']) && $_SESSION['portal_state']==='pending','Debe conservar state');
            if ($mode==='local') check($_SESSION['usuario_rol']==='vendedor' && !isset($_SESSION['portal_expires'],$_SESSION['portal_refresh']),'No debe depender del JWT vencido');
            echo "\nOK $mode\n";
        });
        require dirname(__DIR__).'/includes/sso-session.php';
        exit;
    }
    $private=null;
    $claims=['iss'=>'https://login.example','aud'=>'agroflorsa','sub'=>'3'];
    function prepareToken($claims,$private) {
        $_SESSION=['portal_state'=>str_repeat('a',64),'portal_state_time'=>time(),'usuario_id'=>999,'turno_id'=>999];
        $_GET=['state'=>str_repeat('a',64),'code'=>str_repeat('b',64)];
        $GLOBALS['response']=json_encode(['identity'=>$claims]);
    }
    prepareToken($claims,$private);
    $decoded=\Classes\SsoClient::consume();
    check($decoded->sub==='3' && !isset($_SESSION['portal_state']),'Debe aceptar identidad sin JWT y consumir state');
    parse_str($GLOBALS['requestOptions'][CURLOPT_POSTFIELDS],$sent);
    check($sent===['client_id'=>'agroflorsa','client_secret'=>'test-only','code'=>str_repeat('b',64),'response_format'=>'identity'],'Contrato POST incorrecto');
    $bad=[['iss'=>'wrong'],['aud'=>'wrong'],['sub'=>3],['sub'=>'name'],['sub'=>'0'],['sub'=>null]];
    foreach ($bad as $change) {
        prepareToken(array_replace($claims,$change),$private);
        $rejected=false;
        try { \Classes\SsoClient::consume(); } catch (\Throwable $e) { $rejected=true; }
        check($rejected,'Identidad inválida aceptada: '.json_encode($change));
    }
    foreach (['state','expired-state','code','signature','http'] as $case) {
        prepareToken($claims,$private);
        if ($case==='state') $_GET['state']='wrong';
        if ($case==='expired-state') $_SESSION['portal_state_time']=time()-601;
        if ($case==='code') $_GET['code']='wrong';
        if ($case==='signature') $GLOBALS['response']=json_encode(['access_token'=>'not-accepted']);
        if ($case==='http') $_ENV['SSO_BACKCHANNEL_URL']='http://login.example';
        $rejected=false;
        try { \Classes\SsoClient::consume(); } catch (\Throwable $e) { $rejected=true; }
        check($rejected && !isset($_SESSION['portal_state']),'Debe rechazar '.$case);
    }
    session_start();
    $_ENV['SSO_BACKCHANNEL_URL']='https://login.example';
    prepareToken($claims,$private);
    $previous=session_id();
    \Controllers\PortalController::callback();
    check(session_id()!==$previous,'Debe regenerar sesión');
    check($_SESSION['usuario_id']===17 && $_SESSION['usuario_rol']==='vendedor' && $_SESSION['turno_id']===8 && $_SESSION['sucursal_id']===4,'Identidad/turno local incorrectos');
    check($GLOBALS['redirect']==='/dashboard' && !isset($_SESSION['portal_refresh'],$_SESSION['portal_expires']),'Callback incorrecto');
    foreach (['unlinked','inactive'] as $case) {
        prepareToken($claims,$private);
        $db->exec($case==='unlinked' ? "UPDATE usuarios SET portal_subject='4'" : "UPDATE usuarios SET portal_subject='3',activo=0");
        ob_start(); \Controllers\PortalController::callback(); ob_end_clean();
        check(http_response_code()===403 && $_SESSION===[],'Debe denegar '.$case);
    }
    session_destroy();
    check(count(array_filter($GLOBALS['requests'],fn($url)=>!str_ends_with($url,'/token')))===0,'Endpoint inesperado');
    echo "OK identidad sin JWT, state, contrato token, callback, sesión, turno y denegaciones\n";
}
