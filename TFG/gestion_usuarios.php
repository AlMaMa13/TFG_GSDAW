<?php
// Inicio de sesión: se reanuda la sesión existente para acceder a los datos del usuario autenticado
session_start();

// Verifico la autenticación y los permisos: sólo los usuarios con el rol de SUDO pueden acceder a esta página
if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== 'sudo') {
    // Redirijo al login si no hay sesión activa o el perfil no es SUDO
    header("Location: login.html");
    exit();
}

// Variables de apoyo: perfil del usuario logueado, término de búsqueda, ID de usuario a editar, y datos del usuario a editar
$perfil = $_SESSION['perfil'];
$buscar = trim($_GET['buscar'] ?? '');
$editar = $_GET['editar'] ?? null;
$usuario_editar = null;

// Inclusión del archivo de conexión a la base de datos
require 'conexion.php';

// Si se solicita editar un usuario (parámetro 'editar' en la URL), se obtienen sus datos actuales
if ($editar) {
    $stmt = $pdo->prepare("SELECT id, username, email, perfil, es_scrum_master FROM usuarios WHERE id = ?");
    $stmt->execute([$editar]);
    $usuario_editar = $stmt->fetch();
}

// Array que almacenará los resultados de búsqueda
$resultados = [];
// Si el usuario ha introducido un término de búsqueda, se ejecuta la consulta correspondiente
if ($buscar !== '') {
    $stmt = $pdo->prepare("SELECT id, username, email, perfil, es_scrum_master FROM usuarios WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC");
    $like = "%$buscar%";
    $stmt->execute([$like, $like]);
    $resultados = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - ByteRed</title>
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
            <!-- Checkbox oculto que controla el menú desplegable en dispositivos móviles -->
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

            <!-- Menú de navegación con enlaces a las secciones del sistema -->
            <nav class="sidebar-nav">
                <a href="area_servicios.php">Inicio</a>
                <a href="ticket.php">Nuevo Ticket</a>
                <a href="mis_tickets.php">Mis Tickets</a>
                <a href="scrum.php">Scrum</a>
                <!-- Enlace a Auditoría visible solo para los roles de ADMINISTRADOR y SUDO -->
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <a href="auditoria.php">Auditoría</a>
                <?php endif; ?>
                <!-- Enlace a Gestión de Usuarios visible solo para el rol de SUDO -->
                <?php if ($perfil == 'sudo'): ?>
                    <a href="gestion_usuarios.php" class="active">Gestión de Usuarios</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">Cerrar Sesión</a>
            </div>

            </div>
        </aside>

        <main class="main-content">

            <header class="topbar">
                <h1>Gestión de Usuarios</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <div class="content-wrapper">

                <div class="form-container form-container-wide">

                    <!-- Mensajes de retroalimentación: muestro alertas de éxito o error tras una operación -->
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-<?php echo $_GET['type'] ?? 'success'; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Buscador: formulario para filtrar usuarios por nombre o correo electrónico -->
                    <div class="search-section">
                        <form method="GET" class="search-form">
                            <input type="text" name="buscar" placeholder="Buscar por nombre de usuario o correo electrónico..."
                                   value="<?php echo htmlspecialchars($buscar); ?>">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <!-- Botón para limpiar la búsqueda y volver al estado inicial -->
                            <?php if ($buscar !== ''): ?>
                                <a href="gestion_usuarios.php" class="btn btn-secondary">Limpiar</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Resultados de búsqueda: se muestran sólo si hay un término de búsqueda -->
                    <?php if ($buscar !== ''): ?>
                        <div class="results-section">
                            <h3>Resultados de búsqueda</h3>
                            <!-- Si hay resultados, se muestran en una tabla -->
                            <?php if (count($resultados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="user-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Perfil</th>
                                                <th>Scrum</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Recorre cada usuario encontrado y muestra sus datos con botones de acción -->
                                            <?php foreach ($resultados as $u): ?>
                                                <tr>
                                                    <td><?php echo $u['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                    <td><span class="user-role role-<?php echo $u['perfil']; ?>"><?php echo $u['perfil']; ?></span></td>
                                                    <td><?php echo $u['es_scrum_master'] ? 'Sí' : 'No'; ?></td>
                                                    <td class="actions-cell">
                                                        <!-- Enlace para editar: mantiene el término de búsqueda actual -->
                                                        <a href="gestion_usuarios.php?editar=<?php echo $u['id']; ?>&buscar=<?php echo urlencode($buscar); ?>" class="btn-action btn-edit">Editar</a>
                                                        <!-- Enlace para eliminar con confirmación: redirige a procesar_usuario.php -->
                                                        <a href="procesar_usuario.php?action=delete&id=<?php echo $u['id']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar al usuario «<?php echo htmlspecialchars(addslashes($u['username'])); ?>»? Esta acción no se puede deshacer.');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <!-- Mensaje cuando no hay resultados -->
                            <?php else: ?>
                                <p class="no-results">No se encontraron usuarios con ese criterio.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de edición: se muestra sólo si se ha seleccionado un usuario para editar -->
                    <?php if ($usuario_editar): ?>
                        <div class="edit-section">
                            <h3>Editar usuario: <?php echo htmlspecialchars($usuario_editar['username']); ?></h3>
                            <form action="procesar_usuario.php" method="POST" class="user-form">
                                <!-- Campos ocultos: acción 'update' e ID del usuario a modificar -->
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">

                                <!-- Nombre de usuario y correo electrónico -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_username">Nombre de usuario</label>
                                        <input type="text" id="edit_username" name="username"
                                               value="<?php echo htmlspecialchars($usuario_editar['username']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_email">Correo electrónico</label>
                                        <input type="email" id="edit_email" name="email"
                                               value="<?php echo htmlspecialchars($usuario_editar['email']); ?>" required>
                                    </div>
                                </div>

                                <!-- Contraseña (opcional) y perfil -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_password">Nueva contraseña <small>(dejar en blanco para mantener la actual)</small></label>
                                        <input type="password" id="edit_password" name="password" placeholder="••••••••">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_perfil">Perfil</label>
                                        <select id="edit_perfil" name="perfil" required>
                                            <option value="empleado" <?php echo $usuario_editar['perfil'] == 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                                            <option value="administrador" <?php echo $usuario_editar['perfil'] == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                            <option value="sudo" <?php echo $usuario_editar['perfil'] == 'sudo' ? 'selected' : ''; ?>>SUDO</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Checkbox para indicar si el usuario es Scrum Master -->
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="es_scrum_master" value="1" <?php echo $usuario_editar['es_scrum_master'] ? 'checked' : ''; ?>>
                                        Scrum Master
                                    </label>
                                </div>

                                <!-- Botones de acción: cancelar (vuelve a la búsqueda actual) y guardar cambios -->
                                <div class="form-actions">
                                    <a href="gestion_usuarios.php?buscar=<?php echo urlencode($buscar); ?>" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de creación: siempre visible para añadir nuevos usuarios al sistema -->
                    <div class="create-section">
                        <h3>Crear nuevo usuario</h3>
                        <form action="procesar_usuario.php" method="POST" class="user-form">
                            <!-- Campo oculto que indica la acción 'create' -->
                            <input type="hidden" name="action" value="create">

                            <!-- Fila: nombre de usuario y correo electrónico -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_username">Nombre de usuario</label>
                                    <input type="text" id="new_username" name="username" placeholder="Ej: jperez" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_email">Correo electrónico</label>
                                    <input type="email" id="new_email" name="email" placeholder="ejemplo@correo.com" required>
                                </div>
                            </div>

                            <!-- Fila: contraseña y selección de perfil -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">Contraseña</label>
                                    <input type="password" id="new_password" name="password" placeholder="••••••••" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_perfil">Perfil</label>
                                    <select id="new_perfil" name="perfil" required>
                                        <option value="empleado">Empleado</option>
                                        <option value="administrador">Administrador</option>
                                        <option value="sudo">SUDO</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Checkbox para marcar al nuevo usuario como Scrum Master -->
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="es_scrum_master" value="1">
                                    Scrum Master
                                </label>
                            </div>

                            <!-- Botón para enviar el formulario de creación -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Crear usuario</button>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="form-sidebar">
                    <div class="info-card">
                        <h4>Gestión de usuarios</h4>
                        <p>Desde aquí puedes administrar todas las cuentas del sistema. Estas acciones están reservadas al perfil SUDO.</p>
                    </div>
                    <div class="info-card">
                        <h4>Perfiles disponibles</h4>
                        <ul>
                            <li><span class="user-role role-empleado">Empleado</span> — Acceso básico a tickets.</li>
                            <li><span class="user-role role-administrador">Administrador</span> — Tickets + Auditoría.</li>
                            <li><span class="user-role role-sudo">SUDO</span> — Acceso completo a todas las funciones.</li>
                            <li><span class="user-role role-scrum-master">Scrum Master</span> — Compatible con cualquier perfil.</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <h4>Importante</h4>
                        <p>Al eliminar un usuario se pierde todo su historial de tickets y no se puede recuperar.</p>
                    </div>
                </div>

            </div>

        </main>

    </div>

</body>

</html>
