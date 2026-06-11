<?php
// Inicio la sesión para comprobar si el usuario está autenticado
session_start();

// Verifico si el usuario ha iniciado sesión; si no, le redirijo a la página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtengo el perfil del usuario desde la sesión
$perfil = $_SESSION['perfil'];

// Compruebo que el usuario tiene permisos de ADMINISTRADOR o SUDO; si no, le redirijo a la página de área de servicios
if ($perfil !== 'administrador' && $perfil !== 'sudo') {
    header("Location: area_servicios.php");
    exit();
}

// Ruta absoluta al directorio donde se almacenan los documentos de auditoría
$ruta_docs = __DIR__ . '/documentos_auditoria';
// Array donde se guardará la información de cada documento
$documentos = [];

// Si el directorio existe, escaneo los archivos que contiene
if (is_dir($ruta_docs)) {
    // Obtengo la lista de archivos del directorio
    $archivos = scandir($ruta_docs);
    // Recorro cada archivo ignorando las entradas . y ..
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;
        $ruta_completa = $ruta_docs . '/' . $archivo;
        if (is_file($ruta_completa)) {
            // Extraigo la información del archivo: nombre, extensión, ruta, fecha de creación y tamaño
            $info = pathinfo($archivo);
            $documentos[] = [
                'nombre'       => $info['filename'],
                'extension'    => $info['extension'] ?? '',
                'archivo'      => $archivo,
                'ruta'         => 'documentos_auditoria/' . $archivo,
                'fecha_creacion' => date('d/m/Y H:i', filectime($ruta_completa)),
                'tamano'       => filesize($ruta_completa),
            ];
        }
    }
}

// Ordeno los documentos por fecha de creación de más reciente a más antiguo para mostrarlos
usort($documentos, function ($a, $b) {
    return strcmp($b['fecha_creacion'], $a['fecha_creacion']);
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - ByteRed</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="dashboard">

        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <div class="logo-wrapper sidebar-logo-wrapper">
                        <img src="Logotipo-blanco2-2048x1448.png" alt="Logo">
                    </div>
                    <h2>ByteRed</h2>
                </div>
                <label for="nav-toggle" class="nav-toggle">&#9776;</label>
            </div>
            <!-- Checkbox oculto que controla el menú responsive -->
            <input type="checkbox" id="nav-toggle" class="nav-toggle-input" autocomplete="off">
            <div class="sidebar-dropdown">

            <div class="sidebar-user">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                    <span class="user-role role-<?php echo $perfil; ?>"><?php echo $perfil; ?></span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="area_servicios.php">Inicio</a>
                <a href="ticket.php">Nuevo Ticket</a>
                <a href="mis_tickets.php">Mis Tickets</a>
                <a href="scrum.php">Scrum</a>
                <!-- Enlace a Auditoría visible solo para los roles de ADMINISTRADOR y SUDO -->
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <a href="auditoria.php" class="active">Auditoría</a>
                <?php endif; ?>
                <!-- Enlace a Gestión de Usuarios visible solo para el rol de SUDO -->
                <?php if ($perfil == 'sudo'): ?>
                    <a href="gestion_usuarios.php">Gestión de Usuarios</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">Cerrar Sesión</a>
            </div>

            </div>
        </aside>

        <main class="main-content">

            <header class="topbar">
                <h1>Auditoría</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <div class="content-wrapper" style="flex-direction: column;">

                <!-- Muestro un mensaje de alerta si se recibe por parámetro GET (ej. después de una acción) -->
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-<?php echo $_GET['type'] ?? 'success'; ?>">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <!-- Si no hay documentos, Muestro el mensaje de lista vacía -->
                <?php if (count($documentos) === 0): ?>
                    <div class="results-section" style="text-align:center;">
                        <p class="no-results">No hay documentos de auditoría disponibles.</p>
                    </div>
                <?php else: ?>
                    <div class="auditoria-lista">
                        <?php foreach ($documentos as $doc): ?>
                            <div class="card auditoria-item">
                                <div class="card-icon card-icon-auditoria">
                                    <span>&#128196;</span>
                                </div>
                                <div class="auditoria-info">
                                    <h3><?php echo htmlspecialchars($doc['nombre']); ?></h3>
                                    <p class="auditoria-meta"><?php echo $doc['fecha_creacion']; ?> &middot; <?php
                                        $tam = $doc['tamano'];
                                        if ($tam < 1024) {
                                            echo $tam . ' B';
                                        } elseif ($tam < 1048576) {
                                            echo round($tam / 1024, 1) . ' KB';
                                        } else {
                                            echo round($tam / 1048576, 1) . ' MB';
                                        }
                                    ?></p>
                                </div>
                                <div class="auditoria-actions">
                                    <a href="<?php echo $doc['ruta']; ?>" target="_blank" class="card-btn">Visualizar</a>
                                    <a href="<?php echo $doc['ruta']; ?>" download="<?php echo $doc['archivo']; ?>" class="card-btn card-btn-descargar">Descargar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </main>

    </div>

</body>

</html>
