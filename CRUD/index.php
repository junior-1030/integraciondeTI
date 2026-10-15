<?php
require_once __DIR__ . '/conexion.php';

// Manejo de mensajes flash en sesión
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// -------------------------------------------------------------
// 1. CONTROLADOR DE ACCIONES (POST)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conexion_exitosa) {
    $accion = $_POST['accion'] ?? '';

    // CREAR USUARIO
    if ($accion === 'crear') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rol      = trim($_POST['rol'] ?? 'Usuario');
        $estado   = trim($_POST['estado'] ?? 'Activo');

        if (empty($nombre) || empty($email)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Campos requeridos', 'mensaje' => 'El nombre y correo son obligatorios.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Correo inválido', 'mensaje' => 'Por favor introduce una dirección de correo válida.'];
        } else {
            // Verificar si el correo ya existe
            $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Correo duplicado', 'mensaje' => "El correo '$email' ya se encuentra registrado."];
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, rol, estado) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, $telefono, $rol, $estado]);
                $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => '¡Usuario Registrado!', 'mensaje' => "Se ha agregado a $nombre correctamente."];
            }
        }
        header("Location: index.php");
        exit;
    }

    // EDITAR USUARIO
    if ($accion === 'editar') {
        $id       = (int)($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rol      = trim($_POST['rol'] ?? 'Usuario');
        $estado   = trim($_POST['estado'] ?? 'Activo');

        if ($id <= 0 || empty($nombre) || empty($email)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Error al actualizar', 'mensaje' => 'Datos incompletos o inválidos.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Correo inválido', 'mensaje' => 'Por favor introduce un correo válido.'];
        } else {
            // Validar que no pertenezca a otro usuario
            $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetch()) {
                $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'Correo en uso', 'mensaje' => 'Este correo ya pertenece a otro usuario registrado.'];
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol = ?, estado = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, $telefono, $rol, $estado, $id]);
                $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => '¡Usuario Actualizado!', 'mensaje' => "Los datos de $nombre se actualizaron con éxito."];
            }
        }
        header("Location: index.php");
        exit;
    }

    // ELIMINAR USUARIO (VÍA POST SEGURO)
    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Usuario Eliminado', 'mensaje' => 'El registro ha sido eliminado del sistema.'];
        }
        header("Location: index.php");
        exit;
    }
}

// Compatibilidad hacia atrás para eliminación por GET (ej. enlaces directos del curso)
if (isset($_GET['borrar']) && $conexion_exitosa) {
    $id = (int)$_GET['borrar'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Usuario Eliminado', 'mensaje' => 'El registro fue eliminado correctamente.'];
    }
    header("Location: index.php");
    exit;
}

// -------------------------------------------------------------
// 2. CONSULTAS DE DATOS Y MÉTRICAS
// -------------------------------------------------------------
$usuarios = [];
$total_usuarios = 0;
$total_activos = 0;
$total_inactivos = 0;
$total_admins = 0;

if ($conexion_exitosa) {
    try {
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
        $usuarios = $stmt->fetchAll();
        $total_usuarios = count($usuarios);

        foreach ($usuarios as $u) {
            if (($u['estado'] ?? 'Activo') === 'Activo') $total_activos++;
            else $total_inactivos++;
            
            if (($u['rol'] ?? 'Usuario') === 'Administrador') $total_admins++;
        }
    } catch (\PDOException $e) {
        $error_conexion = "Error al leer usuarios: " . $e->getMessage();
    }
}

// Helper para colores de avatar dinámicos
function generarGradienteAvatar($nombre) {
    $gradientes = [
        'linear-gradient(135deg, #6366f1, #3b82f6)',
        'linear-gradient(135deg, #10b981, #059669)',
        'linear-gradient(135deg, #f59e0b, #d97706)',
        'linear-gradient(135deg, #ec4899, #be185d)',
        'linear-gradient(135deg, #8b5cf6, #6d28d9)',
        'linear-gradient(135deg, #06b6d4, #0891b2)'
    ];
    $indice = abs(crc32($nombre)) % count($gradientes);
    return $gradientes[$indice];
}

// Helper para iniciales
function obtenerIniciales($nombre) {
    $partes = explode(' ', trim($nombre));
    $iniciales = '';
    if (isset($partes[0][0])) $iniciales .= strtoupper($partes[0][0]);
    if (isset($partes[1][0])) $iniciales .= strtoupper($partes[1][0]);
    return $iniciales ?: 'U';
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS TI | Sistema de Gestión y Administración</title>
    <!-- Iconos Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Estilos del Sistema -->
    <link rel="stylesheet" href="css/sistema.css">
</head>
<body>

<div class="app-wrapper">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="brand-info">
                <h1>NEXUS TI</h1>
                <span>Sistema Integral</span>
            </div>
        </div>

        <div class="sidebar-status">
            <div class="pulse-dot"></div>
            <div class="sidebar-status-text">
                <?= $conexion_exitosa ? 'MySQL: crud_db Conectado' : 'Sin conexión BD' ?>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Navegación Principal</div>
            <a href="index.php" class="nav-link active">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard & Usuarios</span>
            </a>
            <a href="#" class="nav-link" onclick="openModal('modalNuevo'); return false;">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Usuario</span>
            </a>

            <div class="nav-section-title">Integración de TI</div>
            <a href="../Proyecto%20GitHub/index.html" class="nav-link" target="_blank">
                <i class="fas fa-globe"></i>
                <span>Ver Sitio Web Público</span>
            </a>
            <a href="http://localhost/phpmyadmin" class="nav-link" target="_blank">
                <i class="fas fa-database"></i>
                <span>phpMyAdmin</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">AD</div>
                <div class="user-meta">
                    <h4>Admin XAMPP</h4>
                    <p>Super Administrador</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ================= CONTENIDO PRINCIPAL ================= -->
    <div class="main-content">

        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-toggle" id="mobileToggle" title="Abrir Menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title-box">
                    <h2>Panel de Control & Usuarios</h2>
                    <p><i class="far fa-calendar-alt"></i> <?= date('d/m/Y') ?> &bull; Sistema de Gestión de Recursos de TI</p>
                </div>
            </div>

            <div class="header-right">
                <button class="theme-toggle-btn" id="themeToggleBtn" title="Cambiar Tema (Modo Oscuro / Claro)">
                    <i class="fas fa-sun"></i>
                </button>
                <button class="btn btn-primary" onclick="openModal('modalNuevo')">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Usuario</span>
                </button>
            </div>
        </header>

        <!-- Body -->
        <main class="content-body">

            <!-- Alerta de Conexión si falló la BD -->
            <?php if (!$conexion_exitosa): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; color: #f87171;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;"><i class="fas fa-exclamation-triangle"></i> Error de conexión con MySQL</h3>
                <p><?= htmlspecialchars($error_conexion) ?></p>
                <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #e2e8f0;">Asegúrate de tener iniciado MySQL en tu panel de control XAMPP y haber importado el archivo <code>database.sql</code>.</p>
            </div>
            <?php endif; ?>

            <!-- ================= STATS / KPIS ================= -->
            <section class="stats-grid">
                <div class="stat-card" style="--card-accent: #6366f1;">
                    <div class="stat-info">
                        <p>Total Usuarios</p>
                        <h3><?= $total_usuarios ?></h3>
                        <span><i class="fas fa-users"></i> Registrados en sistema</span>
                    </div>
                    <div class="stat-icon" style="--icon-bg: rgba(99, 102, 241, 0.12); --icon-color: #6366f1;">
                        <i class="fas fa-users-gear"></i>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #10b981;">
                    <div class="stat-info">
                        <p>Usuarios Activos</p>
                        <h3><?= $total_activos ?></h3>
                        <span style="color: #10b981;"><i class="fas fa-check-circle"></i> <?= $total_usuarios > 0 ? round(($total_activos / $total_usuarios) * 100) : 0 ?>% Operativos</span>
                    </div>
                    <div class="stat-icon" style="--icon-bg: rgba(16, 185, 129, 0.12); --icon-color: #10b981;">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #f59e0b;">
                    <div class="stat-info">
                        <p>Administradores</p>
                        <h3><?= $total_admins ?></h3>
                        <span style="color: #f59e0b;"><i class="fas fa-shield-alt"></i> Control total</span>
                    </div>
                    <div class="stat-icon" style="--icon-bg: rgba(245, 158, 11, 0.12); --icon-color: #f59e0b;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #0ea5e9;">
                    <div class="stat-info">
                        <p>Estado del Motor</p>
                        <h3 style="font-size: 1.5rem; padding-top: 0.2rem;">Online</h3>
                        <span style="color: #0ea5e9;"><i class="fas fa-server"></i> Apache + PHP <?= PHP_VERSION ?></span>
                    </div>
                    <div class="stat-icon" style="--icon-bg: rgba(14, 165, 233, 0.12); --icon-color: #0ea5e9;">
                        <i class="fas fa-network-wired"></i>
                    </div>
                </div>
            </section>

            <!-- ================= PANEL PRINCIPAL / TABLA CRUD ================= -->
            <div class="data-panel">
                
                <!-- Encabezado del Panel -->
                <div class="panel-header">
                    <div class="panel-title-area">
                        <h3>Directorio de Usuarios</h3>
                        <p>Administra, busca, edita y da de alta cuentas en la base de datos.</p>
                    </div>
                    <div class="panel-actions">
                        <button class="btn btn-secondary btn-sm" id="exportCsvBtn" title="Descargar como CSV">
                            <i class="fas fa-file-csv"></i>
                            <span>Exportar CSV</span>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="openModal('modalNuevo')">
                            <i class="fas fa-plus"></i>
                            <span>Agregar Usuario</span>
                        </button>
                    </div>
                </div>

                <!-- Barra de Búsqueda y Filtros -->
                <div class="toolbar-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchUsers" class="search-input" placeholder="Buscar por nombre, correo o teléfono...">
                    </div>

                    <div class="filters-group">
                        <select id="filterRole" class="filter-select">
                            <option value="">Todos los Roles</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Editor">Editor</option>
                            <option value="Usuario">Usuario</option>
                        </select>

                        <select id="filterStatus" class="filter-select">
                            <option value="">Todos los Estados</option>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Datos -->
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Usuario / Identidad</th>
                                <th>Correo Electrónico</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (empty($usuarios)): ?>
                            <tr class="empty-row">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <h4>No hay usuarios registrados</h4>
                                        <p>Comienza agregando un nuevo usuario con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): 
                                    $id = $u['id'];
                                    $nombre = $u['nombre'];
                                    $email = $u['email'];
                                    $telefono = $u['telefono'] ?? '';
                                    $rol = $u['rol'] ?? 'Usuario';
                                    $estado = $u['estado'] ?? 'Activo';
                                    $fecha = !empty($u['fecha_registro']) ? date('d/m/Y', strtotime($u['fecha_registro'])) : 'Reciente';
                                    $gradiente = generarGradienteAvatar($nombre);
                                    $iniciales = obtenerIniciales($nombre);
                                ?>
                                <tr data-id="<?= $id ?>" 
                                    data-nombre="<?= htmlspecialchars($nombre) ?>" 
                                    data-email="<?= htmlspecialchars($email) ?>" 
                                    data-telefono="<?= htmlspecialchars($telefono) ?>" 
                                    data-rol="<?= htmlspecialchars($rol) ?>" 
                                    data-estado="<?= htmlspecialchars($estado) ?>"
                                    data-fecha="<?= htmlspecialchars($fecha) ?>">
                                    <td>
                                        <div class="user-info-cell">
                                            <div class="user-avatar-tag" style="background: <?= $gradiente ?>;">
                                                <?= $iniciales ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?= htmlspecialchars($nombre) ?></h4>
                                                <span class="user-id-badge">ID: #<?= $id ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($email) ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                                            <i class="far fa-envelope" style="color: var(--text-dim);"></i>
                                            <?= htmlspecialchars($email) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= !empty($telefono) ? htmlspecialchars($telefono) : '<span style="color: var(--text-dim); font-size: 0.8rem;">No registrado</span>' ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-role-<?= strtolower($rol) ?>">
                                            <?= htmlspecialchars($rol) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?= strtolower($estado) ?>">
                                            <span class="status-dot"></span>
                                            <?= htmlspecialchars($estado) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--text-muted);"><i class="far fa-clock"></i> <?= $fecha ?></span>
                                    </td>
                                    <td>
                                        <div class="actions-cell" style="justify-content: flex-end;">
                                            <button class="btn btn-sm btn-icon-only action-btn-edit btn-open-edit" 
                                                    title="Editar Usuario"
                                                    data-id="<?= $id ?>"
                                                    data-nombre="<?= htmlspecialchars($nombre) ?>"
                                                    data-email="<?= htmlspecialchars($email) ?>"
                                                    data-telefono="<?= htmlspecialchars($telefono) ?>"
                                                    data-rol="<?= htmlspecialchars($rol) ?>"
                                                    data-estado="<?= htmlspecialchars($estado) ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon-only action-btn-delete btn-open-delete" 
                                                    title="Eliminar Usuario"
                                                    data-id="<?= $id ?>"
                                                    data-nombre="<?= htmlspecialchars($nombre) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Fila mostrada cuando el buscador no encuentra resultados -->
                            <tr id="noResultsRow" style="display: none;">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <h4>Sin resultados coincidentes</h4>
                                        <p>No se encontraron usuarios que coincidan con los filtros de búsqueda.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer de la Tabla -->
                <div class="table-footer">
                    <div>
                        Mostrando <strong id="visibleCount"><?= $total_usuarios ?></strong> de <strong><?= $total_usuarios ?></strong> registros
                    </div>
                    <div style="font-size: 0.8rem;">
                        <i class="fas fa-lock" style="color: var(--success);"></i> Transacciones seguras con PDO Prepared Statements
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<!-- ================= MODAL: NUEVO USUARIO ================= -->
<div class="modal-backdrop" id="modalNuevo">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="color: var(--primary);"></i> Registrar Nuevo Usuario</h3>
            <button class="modal-close-btn" onclick="closeModal('modalNuevo')">&times;</button>
        </div>
        <form method="POST" action="index.php">
            <input type="hidden" name="accion" value="crear">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Carlos Mendoza" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="carlos.mendoza@empresa.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Teléfono de Contacto</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="telefono" class="form-control" placeholder="+52 55 1234 5678">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rol en el Sistema</label>
                        <select name="rol" class="form-control" style="padding-left: 1rem;">
                            <option value="Usuario">Usuario</option>
                            <option value="Editor">Editor</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control" style="padding-left: 1rem;">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalNuevo')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR USUARIO ================= -->
<div class="modal-backdrop" id="modalEditar">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit" style="color: var(--primary);"></i> Editar Datos de Usuario</h3>
            <button class="modal-close-btn" onclick="closeModal('modalEditar')">&times;</button>
        </div>
        <form method="POST" action="index.php">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Teléfono de Contacto</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="telefono" id="edit_telefono" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rol en el Sistema</label>
                        <select name="rol" id="edit_rol" class="form-control" style="padding-left: 1rem;">
                            <option value="Usuario">Usuario</option>
                            <option value="Editor">Editor</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" id="edit_estado" class="form-control" style="padding-left: 1rem;">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CONFIRMAR ELIMINACIÓN ================= -->
<div class="modal-backdrop" id="modalEliminar">
    <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <h3 style="color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> ¿Eliminar Registro?</h3>
            <button class="modal-close-btn" onclick="closeModal('modalEliminar')">&times;</button>
        </div>
        <form method="POST" action="index.php">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" id="delete_id">
            <div class="modal-body" style="padding-top: 1rem;">
                <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.5;">
                    ¿Estás seguro de que deseas eliminar permanentemente a <strong id="delete_user_name" style="color: var(--text-main);"></strong>? Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEliminar')">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Sí, Eliminar</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= TOAST NOTIFICATIONS ================= -->
<?php if ($flash): ?>
<div class="toast-container">
    <div class="toast toast-<?= $flash['tipo'] === 'success' ? 'success' : 'danger' ?>">
        <div class="toast-icon">
            <i class="fas <?= $flash['tipo'] === 'success' ? 'fa-check' : 'fa-exclamation' ?>"></i>
        </div>
        <div class="toast-content">
            <h4><?= htmlspecialchars($flash['titulo']) ?></h4>
            <p><?= htmlspecialchars($flash['mensaje']) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script Interactivo -->
<script src="js/sistema.js"></script>

</body>
</html>
