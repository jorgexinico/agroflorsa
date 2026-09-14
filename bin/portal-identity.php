<?php
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__).'/includes')->safeLoad();
if(($_ENV['DB_HOST']??'')!=='base_db')throw new RuntimeException('Este comando de desarrollo solo admite base_db local.');
require dirname(__DIR__).'/includes/database.php';
$issuer=$_ENV['SSO_ISSUER']??'';
$command=$argv[1]??'';
if($command==='migrate') {
    $columns=$db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
    if(!in_array('portal_issuer',$columns,true))$db->exec('ALTER TABLE usuarios ADD portal_issuer VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin NULL');
    if(!in_array('portal_subject',$columns,true))$db->exec('ALTER TABLE usuarios ADD portal_subject VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL');
    $indexes=$db->query('SHOW INDEX FROM usuarios')->fetchAll();
    if(!in_array('uq_portal_identity',array_column($indexes,'Key_name'),true))$db->exec('ALTER TABLE usuarios ADD UNIQUE KEY uq_portal_identity (portal_issuer,portal_subject)');
    // Traslado único de datos existentes; el receptor ya no consulta este mapa.
    $map=json_decode($_ENV['SSO_USER_MAP']??'{}',true,512,JSON_THROW_ON_ERROR);
    foreach($map as $subject=>$localId)(new Classes\PortalIdentity($db))->link($issuer,(string)$subject,(int)$localId);
    echo "Columnas preparadas e identidades anteriores migradas. No se modificaron roles ni IDs locales.\n";
} elseif($command==='link') {
    $q=$db->prepare('SELECT id FROM usuarios WHERE usuario=?');$q->execute([$argv[3]??'']);$localId=$q->fetchColumn();
    if(!$localId)throw new RuntimeException('Nombre de usuario local inexistente.');
    (new Classes\PortalIdentity($db))->link($issuer,$argv[2]??'',(int)$localId);
    echo "Identidad guardada en MySQL. Se conserva el rol local.\n";
} else {throw new RuntimeException('Uso: migrate | link ID_CENTRAL USUARIO_LOCAL');}
