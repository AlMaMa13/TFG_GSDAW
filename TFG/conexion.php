<?php
$host = "localhost";
$database   = "soporte_bd";
$user = "root";
$pass = ""; 
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$database;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Esta variable $pdo es la que tengo que usar en todos los archivos
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En producción no tengo que mostrar el error real, pero para el TFG me ayuda a depurar
     die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>