<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ticket.php");
    exit();
}

$usuario   = trim($_POST['usuario'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$urgencia  = trim($_POST['urgencia'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

$errores = [];

if (empty($usuario)) {
    $errores[] = "El nombre de usuario es obligatorio.";
}
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Debes proporcionar un correo electrónico válido.";
}
if (!in_array($urgencia, ['Baja', 'Media', 'Alta', 'Crítica'])) {
    $errores[] = "Selecciona un nivel de urgencia válido.";
}
if (empty($descripcion)) {
    $errores[] = "La descripción de la incidencia no puede estar vacía.";
}

if (!empty($errores)) {
    echo "<h3>Error al enviar el ticket:</h3><ul>";
    foreach ($errores as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul><a href='ticket.php'>Volver al formulario</a>";
    exit();
}

try {

    $fecha = date('Y-m-d H:i:s');

    $sql = "INSERT INTO tickets (usuario, correo_contacto, urgencia, descripcion, fecha)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario, $correo, $urgencia, $descripcion, $fecha]);

    $ticket_id = $pdo->lastInsertId();

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = '';       // Configurar servidor SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = '';       // Correo remitente
    $mail->Password   = '';       // Contraseña o app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($correo, "Ticket #$ticket_id - $usuario");
    $mail->addAddress('soporte@bytered.com', 'Soporte ByteRed');

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "[Ticket #$ticket_id] Nueva incidencia - $urgencia";

    $mail->Body = "
        <h2>Nuevo ticket de soporte</h2>
        <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
            <tr><td><strong>ID Ticket</strong></td><td>$ticket_id</td></tr>
            <tr><td><strong>Usuario</strong></td><td>" . htmlspecialchars($usuario) . "</td></tr>
            <tr><td><strong>Correo</strong></td><td>" . htmlspecialchars($correo) . "</td></tr>
            <tr><td><strong>Urgencia</strong></td><td>" . htmlspecialchars($urgencia) . "</td></tr>
            <tr><td><strong>Fecha</strong></td><td>$fecha</td></tr>
            <tr><td><strong>Descripción</strong></td><td>" . nl2br(htmlspecialchars($descripcion)) . "</td></tr>
        </table>
    ";

    $mail->AltBody = "Ticket #$ticket_id\nUsuario: $usuario\nCorreo: $correo\nUrgencia: $urgencia\nFecha: $fecha\n\nDescripción:\n$descripcion";

    $mail->send();

    header("Location: ticket.php?ok=1");
    exit();
} catch (Exception $e) {

    echo "<h3>Ticket registrado, pero fallo al enviar el correo.</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<a href='ticket.php'>Volver al formulario</a>";
    exit();
} catch (PDOException $e) {

    echo "<h3>Error al guardar el ticket en la base de datos.</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='ticket.php'>Volver al formulario</a>";
    exit();
}
