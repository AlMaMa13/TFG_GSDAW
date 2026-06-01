<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== 'sudo') {
    header("Location: login.html");
    exit();
}

$perfil = $_SESSION['perfil'];
$buscar = trim($_GET['buscar'] ?? '');
$editar = $_GET['editar'] ?? null;
$usuario_editar = null;

require 'conexion.php';

if ($editar) {
    $stmt = $pdo->prepare("SELECT id, username, email, perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$editar]);
    $usuario_editar = $stmt->fetch();
}

$resultados = [];
if ($buscar !== '') {
    $stmt = $pdo->prepare("SELECT id, username, email, perfil FROM usuarios WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC");
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
                <a href="area_servicios.php">Inicio</a>
                <a href="ticket.php">Nuevo Ticket</a>
                <a href="#">Mis Tickets</a>
                <?php if ($perfil == 'administrador' || $perfil == 'sudo'): ?>
                    <a href="#">Auditoría</a>
                <?php endif; ?>
                <?php if ($perfil == 'sudo'): ?>
                    <a href="gestion_usuarios.php" class="active">Gestión de Usuarios</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">Cerrar Sesión</a>
            </div>
        </aside>

        <main class="main-content">

            <header class="topbar">
                <h1>Gestión de Usuarios</h1>
                <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
            </header>

            <div class="content-wrapper">

                <div class="form-container">

                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-<?php echo $_GET['type'] ?? 'success'; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Buscador -->
                    <div class="search-section">
                        <form method="GET" class="search-form">
                            <input type="text" name="buscar" placeholder="Buscar por nombre de usuario o correo electrónico..."
                                   value="<?php echo htmlspecialchars($buscar); ?>">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <?php if ($buscar !== ''): ?>
                                <a href="gestion_usuarios.php" class="btn btn-secondary">Limpiar</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <?php if ($buscar !== ''): ?>
                        <div class="results-section">
                            <h3>Resultados de búsqueda</h3>
                            <?php if (count($resultados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="user-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Perfil</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($resultados as $u): ?>
                                                <tr>
                                                    <td><?php echo $u['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                    <td><span class="user-role role-<?php echo $u['perfil']; ?>"><?php echo $u['perfil']; ?></span></td>
                                                    <td class="actions-cell">
                                                        <a href="gestion_usuarios.php?editar=<?php echo $u['id']; ?>&buscar=<?php echo urlencode($buscar); ?>" class="btn-action btn-edit">Editar</a>
                                                        <a href="procesar_usuario.php?action=delete&id=<?php echo $u['id']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar al usuario «<?php echo htmlspecialchars(addslashes($u['username'])); ?>»? Esta acción no se puede deshacer.');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="no-results">No se encontraron usuarios con ese criterio.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de edición -->
                    <?php if ($usuario_editar): ?>
                        <div class="edit-section">
                            <h3>Editar usuario: <?php echo htmlspecialchars($usuario_editar['username']); ?></h3>
                            <form action="procesar_usuario.php" method="POST" class="user-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">

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

                                <div class="form-actions">
                                    <a href="gestion_usuarios.php?buscar=<?php echo urlencode($buscar); ?>" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de creación -->
                    <div class="create-section">
                        <h3>Crear nuevo usuario</h3>
                        <form action="procesar_usuario.php" method="POST" class="user-form">
                            <input type="hidden" name="action" value="create">

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

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Crear usuario</button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Panel lateral informativo -->
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
