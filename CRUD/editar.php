<?php
require_once __DIR__ . '/conexion.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Obtener datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'titulo' => 'No encontrado', 'mensaje' => 'El usuario que intentas editar no existe.'];
    header("Location: index.php");
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $rol      = trim($_POST['rol'] ?? 'Usuario');
    $estado   = trim($_POST['estado'] ?? 'Activo');

    if (empty($nombre) || empty($email)) {
        $error = "El nombre y el correo electrónico son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Introduce un formato de correo electrónico válido.";
    } else {
        // Verificar si el correo ya existe en otro usuario
        $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $checkStmt->execute([$email, $id]);
        if ($checkStmt->fetch()) {
            $error = "El correo ya se encuentra asignado a otro usuario.";
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol = ?, estado = ? WHERE id = ?");
            $stmt->execute([$nombre, $email, $telefono, $rol, $estado, $id]);
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => '¡Actualización Exitosa!', 'mensaje' => "El usuario $nombre fue modificado correctamente."];
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario #<?= $id ?> | NEXUS TI</title>
    <!-- Iconos Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Estilos del Sistema -->
    <link rel="stylesheet" href="css/sistema.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem 1rem;">

    <div class="modal-card" style="max-width: 580px; transform: none; box-shadow: var(--shadow-xl);">
        <div class="modal-header">
            <h3><i class="fas fa-user-pen" style="color: var(--primary);"></i> Editar Usuario #<?= $id ?></h3>
            <a href="index.php" class="modal-close-btn" style="text-decoration: none;"><i class="fas fa-arrow-left"></i></a>
        </div>

        <?php if (!empty($error)): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border-left: 4px solid #ef4444; padding: 1rem 1.5rem; margin: 1rem 1.75rem 0 1.75rem; border-radius: var(--radius-sm); color: #f87171; font-size: 0.88rem;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Teléfono de Contacto</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" placeholder="+52 55 1234 5678">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rol en el Sistema</label>
                        <select name="rol" class="form-control" style="padding-left: 1rem;">
                            <option value="Usuario" <?= ($usuario['rol'] ?? '') === 'Usuario' ? 'selected' : '' ?>>Usuario</option>
                            <option value="Editor" <?= ($usuario['rol'] ?? '') === 'Editor' ? 'selected' : '' ?>>Editor</option>
                            <option value="Administrador" <?= ($usuario['rol'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado de Cuenta</label>
                        <select name="estado" class="form-control" style="padding-left: 1rem;">
                            <option value="Activo" <?= ($usuario['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= ($usuario['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" name="actualizar" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>

</body>
</html>
