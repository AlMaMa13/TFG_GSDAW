<?php

// Inicio la sesión para recuperar al usuario
session_start(); 

// Incluyo el archivo de conexión con la base de datos
require 'conexion.php';

// Verifico si la solicitud se ha enviado mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturo los datos introducidos por el usuario en el formulario de login
    $userInput = $_POST['username'];
    $passwordInput = $_POST['password'];

    try {
        // Preparo y ejecuto la consulta SQL para buscar al usuario por su nombre de usuario
        $sql = "SELECT id, username, password, perfil, es_scrum_master FROM usuarios WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userInput]);
        $usuario = $stmt->fetch();

        // Verifico que el usuario existe y que la contraseña introducida coincide con el hash almacenado en la base de datos
        if ($usuario && password_verify($passwordInput, $usuario['password'])) {
            // Guardo los datos del usuario en variables de sesión para usarlas en toda la aplicación
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['perfil'] = $usuario['perfil'];
            $_SESSION['es_scrum_master'] = (bool) $usuario['es_scrum_master'];

            // Redirijo al usuario a la página "ÁREA DE SERVICIOS"
            header("Location: area_servicios.php");
            exit();
        } else {
            // Muestro un mensaje de error con enlace para volver al formulario de login
            echo "Usuario o contraseña incorrectos.<br> Pruebe de nuevo. <a href='login.html'>Login</a>";
        }
    } catch (PDOException $e) {
        // Detengo la ejecución y muestro el mensaje de error si falla la conexión o la consulta
        die("Error en el login: " . $e->getMessage());
    }
}
?>