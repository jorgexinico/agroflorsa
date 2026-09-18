<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre TEXT, rol TEXT, activo INTEGER, portal_issuer TEXT, portal_subject TEXT, UNIQUE(portal_issuer,portal_subject))');
$db->exec("INSERT INTO usuarios VALUES (17,'Operador','vendedor',1,NULL,NULL),(28,'Otro','admin',1,NULL,NULL)");
$identity = new Classes\PortalIdentity($db);
$issuer = 'https://login.agroflorsa.com';
$identity->link($issuer, '3', 17);
$identity->link($issuer, '3', 17);
$user = $identity->find($issuer, '3');
if ((int)$user['id'] !== 17 || $user['rol'] !== 'vendedor') throw new RuntimeException('Se alteró el usuario o rol local.');
foreach ([['4',17], ['3',28]] as [$subject,$id]) {
    $rejected = false;
    try { $identity->link($issuer, $subject, $id); } catch (Throwable $e) { $rejected = true; }
    if (!$rejected) throw new RuntimeException('Se permitió reasignar una identidad existente.');
}
if ($identity->find('https://otro.example', '3') !== null) throw new RuntimeException('Se aceptó otro emisor.');
$db->exec('UPDATE usuarios SET activo=0 WHERE id=17');
if ($identity->find($issuer, '3') !== null) throw new RuntimeException('Se aceptó un usuario inactivo.');
// Una instalación sin índice único no debe elegir arbitrariamente un usuario.
$db->exec('DROP TABLE usuarios');
$db->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre TEXT, rol TEXT, activo INTEGER, portal_issuer TEXT, portal_subject TEXT)');
$insert=$db->prepare('INSERT INTO usuarios VALUES (?,?,?,?,?,?)');
$insert->execute([17,'Uno','vendedor',1,$issuer,'3']);
$insert->execute([28,'Dos','admin',1,$issuer,'3']);
$rejected=false;
try { $identity->find($issuer,'3'); } catch (RuntimeException $e) { $rejected=true; }
if (!$rejected) throw new RuntimeException('Se eligió una identidad duplicada.');
echo "OK: IDs distintos, rol conservado, repetición segura, conflictos, duplicados y usuario inactivo denegados.\n";
