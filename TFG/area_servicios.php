<?php
// ÁREA DE SERVICIOS: Panel principal de la aplicación

// Inicio o reanudo la sesión del usuario
session_start();

// Si el usuario no ha iniciado sesión, lo redirijo al formulario de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtengo el perfil del usuario desde la sesión para controlar permisos y mostrar sólo aquellas secciones a las que tenga acceso
$perfil = $_SESSION['perfil'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Servicios - ByteRed</title>
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

            <!-- Checkbox oculto que controla el despliegue del menú en responsive -->
            <input type="checkbox" id="nav-toggle" class="nav-toggle-input" autocomplete="off">
            
            <div class="sidebar-dropdown">

                <div class="sidebar-user">
                    <div class="user-avatar">
                        <!-- Muestro la inicial del nombre de usuario en mayúscula -->
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <!-- Muestro el nombre del usuario y su rol -->
                        <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                        <span class="user-role role-<?php echo $perfil; ?>"><?php echo $perfil; ?></span>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <a href="area_servicios.php" class="active">Inicio</a>
                    <a href="ticket.php">Nuevo Ticket</a>
                    <a href="mis_tickets.php">Mis Tickets</a>
                    <a href="scrum.php">Scrum</a>
                    <!-- Enlace a Auditoría visible solo para los roles de ADMINISTRADOR y SUDO -->
                    <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                        <a href="auditoria.php">Auditoría</a>
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
                <h1>Panel de Control</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <section class="modules-grid">

                <!-- TICKETS: Visible para todos los roles -->
                <?php if ($perfil == 'empleado' || $perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <div class="card">
                        <div class="card-icon card-icon-tickets">
                            <span>&#128196;</span>
                        </div>
                        <h3>TICKETS</h3>
                        <p>Solicitar asistencia técnica y realizar seguimiento de incidencias.</p>
                        <a href="ticket.php" class="card-btn">Acceder</a>
                    </div>
                <?php endif; ?>

                <!-- SCRUM: Visible para todos los roles -->
                <div class="card">
                    <div class="card-icon card-icon-scrum">
                        <span>&#128200;</span>
                    </div>
                    <h3>SCRUM</h3>
                    <p>Tablero de tareas con gestión de estados: por hacer, haciendo, por revisar, terminada.</p>
                    <a href="scrum.php" class="card-btn">Acceder</a>
                </div>

                <!-- ADUITORÍAS: Visible solo para los roles de ADMINISTRADOR y SUDO -->
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <div class="card">
                        <div class="card-icon card-icon-auditoria">
                            <span>&#128203;</span>
                        </div>
                        <h3>AUDITORÍA</h3>
                        <p>Consultar documentación de auditorías realizadas.</p>
                        <a href="auditoria.php" class="card-btn">Acceder</a>
                    </div>
                <?php endif; ?>

                <!-- GESTIÓN DE USUARIOS: Visible solo para el rol de SUDO -->
                <?php if ($perfil == 'sudo'): ?>
                    <div class="card card-highlight">
                        <div class="card-icon card-icon-users">
                            <span>&#128101;</span>
                        </div>
                        <h3>GESTIÓN USUARIOS</h3>
                        <p>Crear, modificar y eliminar cuentas de usuario.</p>
                        <a href="gestion_usuarios.php" class="card-btn card-btn-warning">Acceder</a>
                    </div>
                <?php endif; ?>

            </section>

        </main>

    </div>

</body>

</html>
