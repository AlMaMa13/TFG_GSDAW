<?php
// Inicio sesión y recupero los datos del usuario
session_start();

// Verifico si el usuario ha iniciado sesión; si no, le redirijo al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$perfil = $_SESSION['perfil'];
$es_scrum_master = $_SESSION['es_scrum_master'];

// Conecto con la base de datos
require 'conexion.php';

$usuario_actual = $_SESSION['username'];

// Cargo las tareas existentes según el rol del usuario 
if ($es_scrum_master) {
    // Scrum Master: ve todas las tareas de todos los usuarios
    $stmt = $pdo->query("
        SELECT t.*, u.es_scrum_master AS asignado_es_scrum_master
        FROM tareas_scrum t
        LEFT JOIN usuarios u ON u.username = t.asignado_a
        ORDER BY t.fecha_creacion DESC
    ");
    $tareas = $stmt->fetchAll();
} else {
    // Usuario normal: sólo ve sus propias tareas asignadas
    $stmt = $pdo->prepare("
        SELECT t.*, u.es_scrum_master AS asignado_es_scrum_master
        FROM tareas_scrum t
        LEFT JOIN usuarios u ON u.username = t.asignado_a
        WHERE t.asignado_a = ?
        ORDER BY t.fecha_creacion DESC
    ");
    $stmt->execute([$usuario_actual]);
    $tareas = $stmt->fetchAll();
}

$columnas = [
    'por_hacer'  => 'Por Hacer',
    'haciendo'   => 'Haciendo',
    'por_revisar' => 'Por Revisar',
    'terminada'  => 'Terminada',
];

// Inicializo un array vacío para cada columna
$tareas_por_columna = [];
foreach ($columnas as $key => $label) {
    $tareas_por_columna[$key] = [];
}

// Clasifico cada tarea en su columna según su estado actual
foreach ($tareas as $t) {
    $estado = $t['estado'];
    if (isset($tareas_por_columna[$estado])) {
        $tareas_por_columna[$estado][] = $t;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero Scrum - ByteRed</title>
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
                <a href="scrum.php" class="active">Scrum</a>
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
                <h1>Tablero Scrum</h1>
                <div class="topbar-actions">
                    <!-- Botón para crear nueva tarea (sólo visible para Scrum Master) -->
                    <?php if ($es_scrum_master): ?>
                        <a href="scrum.php?crear=1" class="btn btn-primary">+ Nueva Tarea</a>
                    <?php endif; ?>
                    <span class="topbar-date"><?php echo date('d/m/Y'); ?></span>
                </div>
            </header>

            <!-- Mensajes de éxito o error -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="content-wrapper" style="padding-bottom: 0;">
                    <div class="form-container" style="max-width:100%;">
                        <div class="alert alert-<?php echo $_GET['type'] ?? 'success'; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario de creación de nuevas tareas (sólo Scrum Master) -->
            <?php if (isset($_GET['crear']) && $es_scrum_master): ?>
                <div class="content-wrapper" style="padding-bottom: 0;">
                    <div class="form-container" style="max-width:100%;">
                        <div class="create-section">
                            <h3>Nueva tarea</h3>
                            <form action="procesar_tarea.php" method="POST" class="user-form">
                                <!-- Campo oculto para indicar la acción "create" -->
                                <input type="hidden" name="action" value="create">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="titulo">Título</label>
                                        <input type="text" id="titulo" name="titulo" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="asignado_a">Asignado a</label>
                                        <select id="asignado_a" name="asignado_a" required>
                                            <option value="">-- Seleccionar --</option>
                                            <?php
                                            // Obtengo la lista de usuarios para el selector de asignación
                                            $users = $pdo->query("SELECT username FROM usuarios ORDER BY username");
                                            foreach ($users as $u):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($u['username']); ?>">
                                                    <?php echo htmlspecialchars($u['username']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                                </div>
                                <div class="form-actions">
                                    <a href="scrum.php" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Crear tarea</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="scrum-board">
                <?php foreach ($columnas as $key => $label): ?>
                    <div class="scrum-column">
                        <div class="scrum-column-header scrum-header-<?php echo $key; ?>">
                            <h3><?php echo $label; ?></h3>
                            <span class="scrum-count"><?php echo count($tareas_por_columna[$key]); ?></span>
                        </div>
                        <div class="scrum-cards">
                            <?php if (count($tareas_por_columna[$key]) === 0): ?>
                                <div class="scrum-empty">Sin tareas</div>
                            <?php endif; ?>
                            <?php foreach ($tareas_por_columna[$key] as $tarea): ?>
                                <div class="scrum-card">
                                    <!-- Título de la tarea y botón de eliminar (sólo Scrum Master) -->
                                    <div class="scrum-card-header">
                                        <h4><?php echo htmlspecialchars($tarea['titulo']); ?></h4>
                                        <?php if ($es_scrum_master): ?>
                                            <a href="procesar_tarea.php?action=delete&id=<?php echo $tarea['id']; ?>"
                                               class="scrum-delete"
                                               onclick="return confirm('¿Eliminar esta tarea?');">&#10005;</a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($tarea['descripcion']): ?>
                                        <p><?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?></p>
                                    <?php endif; ?>
                                    <div class="scrum-card-footer">
                                        <span class="scrum-user"><?php echo htmlspecialchars($tarea['asignado_a']); ?></span>
                                        <?php if ($key !== 'terminada'): ?>
                                            <form action="procesar_tarea.php" method="POST" class="scrum-state-form">
                                                <input type="hidden" name="action" value="update_estado">
                                                <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">
                                                <select name="estado" onchange="this.form.submit()">
                                                    <?php
                                                    $estados = [
                                                        'por_hacer' => 'Por Hacer',
                                                        'haciendo' => 'Haciendo',
                                                        'por_revisar' => 'Por Revisar',
                                                        'terminada' => 'Terminada',
                                                    ];
                                                    foreach ($estados as $ek => $el):
                                                    ?>
                                                        <option value="<?php echo $ek; ?>" <?php echo $ek === $key ? 'selected' : ''; ?>>
                                                            <?php echo $el; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        <?php else: ?>
                                            <span class="scrum-done">&#10003;</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>

    </div>

</body>

</html>
