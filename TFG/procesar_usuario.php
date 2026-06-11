<?php
// Inicio sesión y recupero los datos del usuario
session_start();

// Verifico la autenticación y los permisos: sólo los usuarios con rol de SUDO pueden ejecutar estas acciones
if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== 'sudo') {
    // Redirijo al login si no hay sesión activa o el perfil no es SUDO
    header("Location: login.html");
    exit();
}

// Incluyo el archivo de conexión con la base de datos
require 'conexion.php';

// Genero la URL para redirigir después de cada operación
$redirect = "gestion_usuarios.php";

// Procesamiento de peticiones POST (creación y actualización de usuarios)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Obtengo la acción a realizar desde el campo oculto del formulario
    $action = $_POST['action'] ?? '';

    // ACCIÓN: CREAR un nuevo usuario
    if ($action === 'create') {
        // Recojo y limpio los datos del formulario de creación
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'empleado';
        $es_scrum_master = isset($_POST['es_scrum_master']) ? 1 : 0;

        // Valido todos los campos obligatorios que deben estar rellenos
        if (empty($username) || empty($email) || empty($password)) {
            header("Location: $redirect?msg=" . urlencode("Todos los campos son obligatorios.") . "&type=error");
            exit();
        }

        // Hash de la contraseña usando BCRYPT para almacenamiento seguro
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);

        // Inserto el nuevo usuario en la base de datos mediante una consulta preparada
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, email, perfil, id_empresa, es_scrum_master) VALUES (?, ?, ?, ?, 1, ?)");
            $stmt->execute([$username, $pass_hash, $email, $perfil, $es_scrum_master]);
            header("Location: $redirect?msg=" . urlencode("Usuario " . $username .  " creado correctamente.") . "&type=success");
        } catch (PDOException $e) {
            // Capturo error de duplicado (username o email ya existente) o error genérico
            $msg = str_contains($e->getMessage(), 'Duplicate') ? "El nombre de usuario o el email ya existen." : "Error al crear usuario.";
            header("Location: $redirect?msg=" . urlencode($msg) . "&type=error");
        }
        exit();
    }

    // ACCIÓN: ACTUALIZAR un usuario existente
    if ($action === 'update') {
        // Recojo y tipo los datos del formulario de edición
        $id       = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'empleado';
        $es_scrum_master = isset($_POST['es_scrum_master']) ? 1 : 0;

        // Valido el ID, que debe ser positivo y los campos obligatorios no pueden estar vacíos
        if ($id <= 0 || empty($username) || empty($email)) {
            header("Location: $redirect?msg=" . urlencode("Datos incompletos.") . "&type=error");
            exit();
        }

        try {
            // Si se proporcionó una nueva contraseña, se incluye en la actualización
            if (!empty($password)) {
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, email = ?, perfil = ?, password = ?, es_scrum_master = ? WHERE id = ?");
                $stmt->execute([$username, $email, $perfil, $pass_hash, $es_scrum_master, $id]);
            } else {
                // Si no hay nueva contraseña, se actualizan el resto de campos sin modificar la contraseña actual
                $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, email = ?, perfil = ?, es_scrum_master = ? WHERE id = ?");
                $stmt->execute([$username, $email, $perfil, $es_scrum_master, $id]);
            }
            header("Location: $redirect?msg=" . urlencode("Usuario " . $username . " actualizado correctamente.") . "&type=success");
        } catch (PDOException $e) {
            // Capturo error de duplicado o error genérico en la actualización
            $msg = str_contains($e->getMessage(), 'Duplicate') ? "El nombre de usuario o el email ya existen." : "Error al actualizar usuario.";
            header("Location: $redirect?msg=" . urlencode($msg) . "&type=error");
        }
        exit();
    }
}

// ACCIÓN: ELIMINAR un usuario mediante petición GET
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    // Valido el ID del usuario a eliminar
    if ($id <= 0) {
        header("Location: $redirect?msg=" . urlencode("ID de usuario inválido.") . "&type=error");
        exit();
    }

    // Prevengo que el usuario no pueda eliminarse a sí mismo
    if ($id == $_SESSION['user_id']) {
        header("Location: $redirect?msg=" . urlencode("No puedes eliminar tu propia cuenta.") . "&type=error");
        exit();
    }

    // Ejecuto la eliminación en la base de datos
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        // Verifico si realmente se eliminó alguna fila
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

// Redirijo por defecto si no se cumplió ninguna de las condiciones anteriores
header("Location: $redirect");
exit();
