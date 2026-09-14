<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/includes/app.php';
echo json_encode($db->query('SELECT id,usuario,nombre,rol,activo FROM usuarios')->fetchAll(),JSON_UNESCAPED_UNICODE),PHP_EOL;
