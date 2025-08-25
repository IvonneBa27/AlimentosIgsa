<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d");
session_start();

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}

$sesionUsuario = $sesi['usuario'];
$sesionNombre = $sesi['nombre'];
$sesionRol = $sesi['rol_id'];

include 'db_connection.php';

// Obtener roles (excepto superadministrador id = 1)
$roles = $con->query("SELECT id, role FROM roles WHERE id != 1");

// Si se seleccionó un rol
$rolSeleccionado = isset($_GET['rol_id']) ? intval($_GET['rol_id']) : 0;

// Obtener módulos activos
$modulospermitidos = $con->query("SELECT id, modulo FROM modulos WHERE estatus_id = 1");

// Obtener permisos del rol
$permisos = [];
if ($rolSeleccionado > 0) {
    $query = $con->query("SELECT * FROM permisos WHERE rol_id = $rolSeleccionado");
    while ($row = $query->fetch_assoc()) {
        $permisos[$row['modulo_id']] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Permisos de Rol</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <main class="main-content p-4 w-100">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h3 class="h3">PERMISOS DE ROL DE USUARIO</h3>
            <div class="btn-toolbar mb-2 mb-md-0">
                <form action="">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminSeguridad.php';">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <form method="GET" action="" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="rolSelect" class="col-form-label">Rol de Usuario:</label>
                </div>
                <div class="col-auto">
                    <select class="form-select" name="rol_id" id="rolSelect" onchange="this.form.submit()" required>
                        <option value="">Seleccione un rol</option>
                        <?php while($r = $roles->fetch_assoc()): ?>
                            <option value="<?= $r['id'] ?>" <?= $rolSeleccionado == $r['id'] ? 'selected' : '' ?>>
                                <?= $r['role'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if ($rolSeleccionado > 0): ?>
        <form method="POST" action="guardar_permisos.php">
            <input type="hidden" name="rol_id" value="<?= $rolSeleccionado ?>">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Módulo</th>
                            <th>Ver</th>
                            <th>Crear</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($modulopermitido = $modulospermitidos->fetch_assoc()):
                            $perm = $permisos[$modulopermitido['id']] ?? ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($modulopermitido['modulo']) ?></td>
                                <?php foreach(['ver','crear','editar','eliminar'] as $permiso): ?>
                                    <td class="text-center">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="permisos[<?= $modulopermitido['id'] ?>][<?= $permiso ?>]"
                                               value="1"
                                               <?= $perm[$permiso] ? 'checked' : '' ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Permisos
                </button>
            </div>
        </form>
        <?php endif; ?>
    </main>
</div>

<script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>


<?php include 'footer.php'; ?>
</body>
</html>
