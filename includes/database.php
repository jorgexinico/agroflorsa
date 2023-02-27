<?php

try {
    $host = $_ENV['DB_HOST'];
    $service = $_ENV['DB_SERVICE'];
    $server = $_ENV['DB_SERVER'];
    $user = $_SESSION['auth_user'];
    $pass = $_SESSION['pass'];
    $database = $_ENV['DB_NAME'];

    $db =  new PDO("informix:host=$host; service=$service;database=$database; server=$server; protocol=onsoctcp;EnableScrollableCursors=1", "$user", "$pass");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    
} catch (PDOException $e) {
    echo json_encode([
        "detalle" => $e->getMessage(),       
        "mensaje" => "Error de conexión bd",

        "codigo" => 5,
    ]);
    header('Location: /');
    exit;
}
