<?php

// Inicio o reanudo la sesión del usuario y recupero sus datos
session_start();

// Si el usuario no ha iniciado sesión, le redirijo al formulario de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtengo el perfil del usuario desde la sesión para controlar permisos
$perfil = $_SESSION['perfil'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket - ByteRed</title>
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
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                        <span class="user-role role-<?php echo $perfil; ?>"><?php echo $perfil; ?></span>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <a href="area_servicios.php">Inicio</a>
                    <a href="ticket.php" class="active">Nuevo Ticket</a>
                    <a href="mis_tickets.php">Mis Tickets</a>
                    <a href="scrum.php">Scrum</a>
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
                <h1>Nuevo Ticket de Soporte</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <div class="content-wrapper">

                <!-- Mensaje de éxito que Se muestra si el ticket se ha enviado correctamente -->
                <?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
                    <div class="alert alert-success">
                        &#9989; Ticket enviado correctamente. Recibirás respuesta al correo indicado.
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <!-- Los datos se envían a procesar_ticket.php mediante POST -->
                    <form action="procesar_ticket.php" method="POST" class="ticket-form">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="usuario">Nombre de usuario</label>
                                <!-- Campo de sólo lectura con el nombre del usuario autenticado -->
                                <input type="text" id="usuario" name="usuario"
                                       value="<?php echo $_SESSION['username']; ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="correo">Correo de contacto</label>
                                <input type="email" id="correo" name="correo"
                                       placeholder="tu@correo.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="urgencia">Nivel de urgencia</label>
                            <select id="urgencia" name="urgencia" required>
                                <option value="">-- Selecciona un nivel --</option>
                                <option value="Baja">Baja</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                                <option value="Crítica">Crítica</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción de la incidencia</label>
                            <textarea id="descripcion" name="descripcion" rows="6"
                                      placeholder="Describe el problema con el mayor detalle posible..."
                                      required></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="area_servicios.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Enviar Ticket</button>
                        </div>

                    </form>
                </div>

                <div class="form-sidebar">
                    <div class="info-card">
                        <h4>¿Cómo funciona?</h4>
                        <p>Al enviar este formulario, tu incidencia quedará registrada en el sistema y se notificará al equipo de soporte por correo electrónico.</p>
                    </div>
                    <div class="info-card">
                        <h4>Tiempo de respuesta</h4>
                        <ul>
                            <li><strong>Crítica</strong> — 1 hora</li>
                            <li><strong>Alta</strong> — 4 horas</li>
                            <li><strong>Media</strong> — 24 horas</li>
                            <li><strong>Baja</strong> — 48 horas</li>
                        </ul>
                    </div>
                </div>

            </div>

        </main>

    </div>

</body>

</html>
