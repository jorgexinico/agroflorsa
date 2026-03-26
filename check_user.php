<?php
require 'includes/app.php';
use Models\Usuario;

$daniel = Usuario::where('usuario', 'daniel');
if ($daniel) {
    echo "ID: " . $daniel[0]->id . "\n";
    echo "Nombre: " . $daniel[0]->nombre . "\n";
    echo "Usuario: " . $daniel[0]->usuario . "\n";
    echo "Rol: " . $daniel[0]->rol . "\n";
} else {
    echo "Usuario 'daniel' no encontrado.\n";
}
