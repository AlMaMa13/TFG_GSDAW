<?php

session_start(); // Inicio la sesión para "recordar" al usuario

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userInput = $_POST['username'];
    $passwordInput = $_POST['password'];

    try {
        // Buscamos al usuario por su nombre de usuario
        $sql = "SELECT id, username, password, perfil FROM usuarios WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userInput]);
        $usuario = $stmt->fetch();

        //Valido el usuario mediante la comparación de la contraseña con el hash de la base de datos
        
        //Si el usuario existe y la contraseña es correcta, guardo las credenciales en la sesión
        if ($usuario && password_verify($passwordInput, $usuario['password'])) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['perfil'] = $usuario['perfil'];

        // Redirijo al usuario al área de servicios con las distintas funcionalidades en función de su perfil
            header("Location: area_servicios.php");
            exit();
        } else {
            echo "Usuario o contraseña incorrectos.<br> Pruebe de nuevo. <a href='login.html'>Login</a>";
        }
    } catch (PDOException $e) {
        die("Error en el login: " . $e->getMessage());
    }
}
?>