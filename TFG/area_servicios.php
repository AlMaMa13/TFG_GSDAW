<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

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
            <div class="sidebar-logo">
                <div class="logo-wrapper sidebar-logo-wrapper">
                    <img src="Logotipo-blanco2-2048x1448.png" alt="Logo">
                </div>
                <h2>ByteRed</h2>
            </div>

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
                <a href="area_servicios.php" class="active">Inicio</a>
                <a href="ticket.php">Nuevo Ticket</a>
                <a href="#">Mis Tickets</a>
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <a href="#">Auditoría</a>
                <?php endif; ?>
                <?php if ($perfil == 'sudo'): ?>
                    <a href="gestion_usuarios.php">Gestión de Usuarios</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">Cerrar Sesión</a>
            </div>
        </aside>

        <main class="main-content">

            <header class="topbar">
                <h1>Panel de Control</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <section class="modules-grid">

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

                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <div class="card">
                        <div class="card-icon card-icon-auditoria">
                            <span>&#128203;</span>
                        </div>
                        <h3>AUDITORÍA</h3>
                        <p>Consultar documentación de auditorías realizadas.</p>
                        <a href="#" class="card-btn">Acceder</a>
                    </div>
                <?php endif; ?>

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

                <!-- <div class="card card-placeholder">
                    <div class="card-icon">
                        <span>&#9881;&#65039;</span>
                    </div>
                    <h3>Más funciones próximamente</h3>
                    <p>Nuevos módulos en desarrollo.</p>
                </div> -->

            </section>

        </main>

    </div>

</body>

</html>
