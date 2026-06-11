<?php
// Inicio de sesión
session_start();

//Verifico que el usuario haya iniciado sesión; si no, redirijo al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtengo el perfil del usuario desde la sesión
$perfil = $_SESSION['perfil'];

// Realizo la conexión con la base de datos
require 'conexion.php';

// Consulto los tickets del usuario actual, ordenados por fecha ascendente
$stmt = $pdo->prepare("SELECT id, fecha, urgencia, descripcion FROM tickets WHERE usuario = ? ORDER BY fecha ASC");
$stmt->execute([$_SESSION['username']]);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets - ByteRed</title>
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
                <a href="mis_tickets.php" class="active">Mis Tickets</a>
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <a href="auditoria.php">Auditoría</a>
                <?php endif; ?>
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
                <h1>Mis Tickets</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <div class="content-wrapper">

                <div class="form-container form-container-wide">

                    <!-- Muestro mensaje después de realizar la acción -->
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-<?php echo $_GET['type'] ?? 'success'; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Si hay tickets, los muestro en una tabla; si no, muestro el mensaje -->
                    <?php if (count($tickets) > 0): ?>
                        <div class="results-section">
                            <h3>Historial de tickets (<?php echo count($tickets); ?>)</h3>
                            <div class="table-responsive">
                                <table class="user-table tickets-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fecha</th>
                                            <th>Urgencia</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $t): ?>
                                            <tr>
                                                <td><?php echo $t['id']; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($t['fecha'])); ?></td>
                                                <td>
                                                    <span class="urgency urgency-<?php echo strtolower($t['urgencia']); ?>">
                                                        <?php echo $t['urgencia']; ?>
                                                    </span>
                                                </td>
                                                <td class="desc-cell"><?php echo nl2br(htmlspecialchars($t['descripcion'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="results-section">
                            <h3>Historial de tickets</h3>
                            <p class="no-results">No has enviado ningún ticket todavía.</p>
                            <div class="form-actions" style="justify-content: center;">
                                <a href="ticket.php" class="btn btn-primary">Crear primer ticket</a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="form-sidebar">
                    <div class="info-card">
                        <h4>Tus tickets</h4>
                        <p>Desde aquí puedes consultar el historial completo de incidencias que has reportado.</p>
                    </div>
                    <div class="info-card">
                        <h4>Leyenda de urgencia</h4>
                        <ul>
                            <li><span class="urgency urgency-crítica">Crítica</span> — 1 hora</li>
                            <li><span class="urgency urgency-alta">Alta</span> — 4 horas</li>
                            <li><span class="urgency urgency-media">Media</span> — 24 horas</li>
                            <li><span class="urgency urgency-baja">Baja</span> — 48 horas</li>
                        </ul>
                    </div>
                </div>

            </div>

        </main>

    </div>

</body>

</html>
