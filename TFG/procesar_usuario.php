<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== 'sudo') {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

$redirect = "gestion_usuarios.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

//A la variable $action le doy el valor en el html y será con lo que trabaje a continuación
    $action = $_POST['action'] ?? '';

    //CREAR USUARIOS NUEVOS
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'empleado';

        //Compruebo que todos los campos necesarios hayan sido recibidos, y si no mando un mensaje para que el usuario los rellene correctamente
        if (empty($username) || empty($email) || empty($password)) {
            header("Location: $redirect?msg=" . urlencode("Todos los campos son obligatorios.") . "&type=error");
            exit();
        }

        $pass_hash = password_hash($password, PASSWORD_BCRYPT);

        /*Realizo la inserción en base de datos utilizando una consulta preparada a la que doy los valores del formulario
        En caso de error, devuelvo el mensaje correspondiente al error*/
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, email, perfil, id_empresa) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $pass_hash, $email, $perfil]);
            header("Location: $redirect?msg=" . urlencode("Usuario " . $username .  " creado correctamente.") . "&type=success");
        } catch (PDOException $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? "El nombre de usuario o el email ya existen." : "Error al crear usuario.";
            header("Location: $redirect?msg=" . urlencode($msg) . "&type=error");
        }
        exit();
    }

    // UPDATE
    if ($action === 'update') {
        $id       = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'empleado';

        if ($id <= 0 || empty($username) || empty($email)) {
            header("Location: $redirect?msg=" . urlencode("Datos incompletos.") . "&type=error");
            exit();
        }

        try {
            if (!empty($password)) {
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, email = ?, perfil = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $perfil, $pass_hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, email = ?, perfil = ? WHERE id = ?");
                $stmt->execute([$username, $email, $perfil, $id]);
            }
            header("Location: $redirect?msg=" . urlencode("Usuario " . $username . " actualizado correctamente.") . "&type=success");
        } catch (PDOException $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? "El nombre de usuario o el email ya existen." : "Error al actualizar usuario.";
            header("Location: $redirect?msg=" . urlencode($msg) . "&type=error");
        }
        exit();
    }
}

// DELETE via GET
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $redirect?msg=" . urlencode("ID de usuario inválido.") . "&type=error");
        exit();
    }

    if ($id == $_SESSION['user_id']) {
        header("Location: $redirect?msg=" . urlencode("No puedes eliminar tu propia cuenta.") . "&type=error");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            header("Location: $redirect?msg=" . urlencode("Usuario eliminado correctamente.") . "&type=success");
        } else {
            header("Location: $redirect?msg=" . urlencode("El usuario no existe.") . "&type=error");
        }
    } catch (PDOException $e) {
        header("Location: $redirect?msg=" . urlencode("Error al eliminar usuario.") . "&type=error");
    }
    exit();
}

header("Location: $redirect");
exit();
