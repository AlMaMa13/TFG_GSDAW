<?php

// Inicio sesión y recupero los datos del usuario
session_start();

//  Verifico que el usuario haya iniciado sesión; si no, le redirijo al login 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Incluyo la conexión con base de datos y cargo las librerías (PHPMailer) 
require 'conexion.php';
require 'vendor/autoload.php';

// Importo las clases necesarias de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifico que la solicitud sea POST; si no, redirijo al formulario
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ticket.php");
    exit();
}

// Obtengo y limpio los datos enviados desde el formulario
$usuario   = trim($_POST['usuario'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$urgencia  = trim($_POST['urgencia'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// Valido cada campo y acumulo errores si los hay
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

// Si hay errores, los muestro y detengo la ejecución
if (!empty($errores)) {
    echo "<h3>Error al enviar el ticket:</h3><ul>";
    foreach ($errores as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul><a href='ticket.php'>Volver al formulario</a>";
    exit();
}

// Bloque try-catch para el manejo de excepciones
try {

    // Fecha/hora actual del servidor
    $fecha = date('Y-m-d H:i:s');

    // Inserto el ticket en la base de datos (consulta preparada)
    $sql = "INSERT INTO tickets (usuario, correo_contacto, urgencia, descripcion, fecha)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario, $correo, $urgencia, $descripcion, $fecha]);

    // Obtengo el ID autogenerado del ticket insertado
    $ticket_id = $pdo->lastInsertId();

    // Configuro PHPMailer con servidor SMTP
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.ionos.es';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'practicas@bytered.es';
    $mail->Password   = 'NataliaAlex2026#**2709';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente y destinatario del correo
    $mail->setFrom('practicas@bytered.es', "Ticket #$ticket_id - $usuario");
    $mail->addAddress('practicas@bytered.es', 'Soporte ByteRed');

    // Configuro el formato HTML, charset y asunto del correo
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "[Ticket #$ticket_id] Nueva incidencia - $urgencia";

    // Cuerpo del mensaje en HTML con los datos del ticket
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

    // Versión en texto plano del mensaje (alternativa a HTML)
    $mail->AltBody = "Ticket #$ticket_id\nUsuario: $usuario\nCorreo: $correo\nUrgencia: $urgencia\nFecha: $fecha\n\nDescripción:\n$descripcion";

    // Envío el correo
    $mail->send();

    // Redirijo al formulario con mensaje de éxito
    header("Location: ticket.php?ok=1");
    exit();

// Capturo errores de PHPMailer
} catch (Exception $e) {

    echo "<h3>Ticket registrado, pero fallo al enviar el correo.</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<a href='ticket.php'>Volver al formulario</a>";
    exit();

// Capturo errores de la base de datos
} catch (PDOException $e) {

    echo "<h3>Error al guardar el ticket en la base de datos.</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='ticket.php'>Volver al formulario</a>";
    exit();
}
