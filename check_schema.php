<?php
require 'includes/app.php';
use Model\ActiveRecord;

$db = ActiveRecord::getDB();
$res = $db->query("SHOW COLUMNS FROM traslados LIKE 'estado'");
print_r($res->fetch(PDO::FETCH_ASSOC));
