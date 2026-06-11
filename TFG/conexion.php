<?php
// Configuración de la conexión a la base de datos
$host = "localhost";
$database   = "u393433014_byteapp";
$user = "u393433014_administrador";
$pass = "G=*6jnZ8s"; 
$charset = "utf8mb4";

// Construcción del DSN para PDO
$dsn = "mysql:host=$host;dbname=$database;charset=$charset";

// Opciones de configuración de PDO: manejo de errores, modo de fetch y desactivación de emulación de consultas preparadas
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Esta variable $pdo es la que tengo que usar en todos los archivos
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     // En producción no tengo que mostrar el error real, pero para el TFG me ayuda a depurar
     die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>