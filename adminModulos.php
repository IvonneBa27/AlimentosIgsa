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
$sesionRol = $sesi['rol_id']; // Asegúrate que esté en la sesión

include 'db_connection.php';

// Configuración para la paginación
$registrosPorPagina = 20;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Contar solo módulos permitidos para el rol
$totalRegistrosQuery = "
    SELECT COUNT(*) as total 
    FROM permisos p
    INNER JOIN modulos m ON p.modulo_id = m.id
    WHERE p.rol_id = :rol_id
";
$stmtCount = $conn->prepare($totalRegistrosQuery);
$stmtCount->bindValue(':rol_id', $sesionRol, PDO::PARAM_INT);
$stmtCount->execute();
$totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Obtener módulos permitidos con paginación
$sql = "
    SELECT m.id, m.modulo, m.ruta
    FROM permisos p
    INNER JOIN modulos m ON p.modulo_id = m.id
    WHERE p.rol_id = :rol_id
    ORDER BY m.id
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':rol_id', $sesionRol, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$rutasmodulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administración de Módulos</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <main class="main-content p-4 w-100">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h3 class="h3">CONFIGURACIÓN DE MODULOS</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-target="#createModuleModal" data-bs-toggle="modal"><i class="bi bi-person-plus"></i> Agregar Modulo</button>
                    </div>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminModulos.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Id</th>
                            <th>Módulo</th>
                            <th>Ruta</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rutasmodulos)) : ?>
                            <?php foreach ($rutasmodulos as $rutas) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($rutas['id']) ?></td>
                                    <td><?= htmlspecialchars($rutas['modulo']) ?></td>
                                    <td><?= htmlspecialchars($rutas['ruta']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3">No se encontraron los modulos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between">
                <p>Total de registros: <strong><?= $totalRegistros ?></strong></p>
                <p>Página <strong><?= $paginaActual ?></strong> de <strong><?= $totalPaginas ?></strong></p>
            </div>

            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $paginaActual - 1 ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <li class="page-item <?= ($paginaActual == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $paginaActual + 1 ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </main>
    </div>

    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'footer.php'; ?>
</body>

</html>