<?php
// Inicio la sesión y recupero los datos del usuario 
session_start();

// Verifico si el usuario ha iniciado sesión; si no, le redirijo al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Establezco la conexión con la base de datos
require 'conexion.php';

// Genero la URL para redirigir después de cada operación
$redirect = "scrum.php";
$usuario_actual = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';

    // ACCIÓN: CREAR NUEVA TAREA (sólo Scrum Master)
    if ($action === 'create') {
        // Verifico que el usuario tenga permisos de Scrum Master
        if (!$_SESSION['es_scrum_master']) {
            header("Location: $redirect?msg=" . urlencode("No tienes permiso para crear tareas.") . "&type=error");
            exit();
        }

        // Recojo y limpio los datos del formulario
        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $asignado_a  = trim($_POST['asignado_a'] ?? '');

        // Valido los campos obligatorios para poder registrar la tarea
        if (empty($titulo) || empty($asignado_a)) {
            header("Location: $redirect?msg=" . urlencode("El título y el usuario asignado son obligatorios.") . "&type=error");
            exit();
        }

        // Inserto la nueva tarea en la base de datos con estado inicial "por_hacer"
        try {
            $stmt = $pdo->prepare("INSERT INTO tareas_scrum (titulo, descripcion, asignado_a, estado, creado_por, fecha_creacion) VALUES (?, ?, ?, 'por_hacer', ?, NOW())");
            $stmt->execute([$titulo, $descripcion, $asignado_a, $usuario_actual]);
            header("Location: $redirect?msg=" . urlencode("Tarea creada y asignada a $asignado_a.") . "&type=success");
        } catch (PDOException $e) {
            header("Location: $redirect?msg=" . urlencode("Error al crear la tarea.") . "&type=error");
        }
        exit();
    }

    //  ACCIÓN: ACTUALIZAR ESTADO DE UNA TAREA 
    if ($action === 'update_estado') {
        $id     = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        // Validación: el estado debe ser uno de los valores permitidos
        $estados_validos = ['por_hacer', 'haciendo', 'por_revisar', 'terminada'];
        if (!in_array($estado, $estados_validos)) {
            header("Location: $redirect?msg=" . urlencode("Estado no válido.") . "&type=error");
            exit();
        }

        try {
            // Compruebo si la tarea existe y obtengo el usuario asignado
            $stmt = $pdo->prepare("SELECT asignado_a FROM tareas_scrum WHERE id = ?");
            $stmt->execute([$id]);
            $tarea = $stmt->fetch();

            if (!$tarea) {
                header("Location: $redirect?msg=" . urlencode("La tarea no existe.") . "&type=error");
                exit();
            }

            // Gestiono que sólo el usuario asignado o el Scrum Master pueden cambiar el estado de la tarea
            if ($tarea['asignado_a'] !== $usuario_actual && !$_SESSION['es_scrum_master']) {
                header("Location: $redirect?msg=" . urlencode("No puedes modificar una tarea que no te pertenece.") . "&type=error");
                exit();
            }

            // Actualizo el estado de la tarea en la base de datos
            $stmt = $pdo->prepare("UPDATE tareas_scrum SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            header("Location: $redirect?msg=" . urlencode("Estado actualizado.") . "&type=success");
        } catch (PDOException $e) {
            header("Location: $redirect?msg=" . urlencode("Error al actualizar.") . "&type=error");
        }
        exit();
    }
}

//  ACCIÓN: ELIMINAR TAREA VÍA GET (sólo Scrum Master) 
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['action']) && $_GET['action'] === 'delete') {
    // Verifico permisos: sólo Scrum Master puede eliminar tareas
    if (!$_SESSION['es_scrum_master']) {
        header("Location: $redirect?msg=" . urlencode("No tienes permiso para eliminar tareas.") . "&type=error");
        exit();
    }

    $id = (int) ($_GET['id'] ?? 0);
    try {
        // Elimino la tarea de la base de datos por su ID
        $stmt = $pdo->prepare("DELETE FROM tareas_scrum WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: $redirect?msg=" . urlencode("Tarea eliminada.") . "&type=success");
    } catch (PDOException $e) {
        header("Location: $redirect?msg=" . urlencode("Error al eliminar.") . "&type=error");
    }
    exit();
}

//  Si no se ejecutó ninguna acción, redirige al tablero
header("Location: $redirect");
exit();
