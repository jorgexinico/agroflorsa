<?php

try {
    $host     = $_ENV['DB_HOST'];
    $port     = $_ENV['DB_PORT'] ?? '3306';
    $user     = $_ENV['DB_USER'];
    $pass     = $_ENV['DB_PASS'];
    $database = $_ENV['DB_NAME'];

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo json_encode([
        "detalle" => $e->getMessage(),
        "mensaje" => "Error de conexión bd",
        "codigo"  => 5,
    ]);
    header('Location: /');
    exit;
}
