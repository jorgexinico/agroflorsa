<?php
require dirname(__DIR__).'/classes/PortalIdentity.php';
$db=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE TABLE usuarios(id INTEGER PRIMARY KEY,nombre TEXT,rol TEXT,activo INTEGER,portal_issuer TEXT,portal_subject TEXT,UNIQUE(portal_issuer,portal_subject))');
$db->exec("INSERT INTO usuarios VALUES(3,'Admin','admin',1,NULL,NULL),(9,'Olga','vendedor',1,NULL,NULL)");
$r=new Classes\PortalIdentity($db);$issuer='https://login.example';
$r->link($issuer,'1',3);$r->link($issuer,'2',9);
if($r->find($issuer,'2')['id']!==9)throw new RuntimeException('Identidad');
$db->exec("UPDATE usuarios SET rol='supervisor' WHERE id=9");
if($r->find($issuer,'2')['rol']!=='supervisor')throw new RuntimeException('Rol obsoleto');
if($r->find('https://otro.example','2')!==null)throw new RuntimeException('Emisor');
$denied=false;try{$r->link($issuer,'2',3);}catch(Throwable $e){$denied=true;}if(!$denied)throw new RuntimeException('Reasignación');
$db->exec('UPDATE usuarios SET activo=0 WHERE id=9');
if($r->find($issuer,'2')!==null)throw new RuntimeException('Inactivo');
echo "Identidad central: IDs distintos, rol actualizado, emisor, reasignación e inactividad OK.\n";
