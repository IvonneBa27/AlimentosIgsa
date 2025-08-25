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
    FROM modulos
";
$stmtCount = $conn->prepare($totalRegistrosQuery);
$stmtCount->execute();
$totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);


// Obtener módulos permitidos con paginación
$sql = "
    SELECT m.id, m.modulo, m.ruta, cs.status as estatus
    FROM modulos m
        INNER JOIN catalog_status cs ON cs.status_id = m.estatus_id
    ORDER BY m.id
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($sql);
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
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-target="#createModuloModal" data-bs-toggle="modal"><i class="bi bi-person-plus"></i> Agregar Modulo</button>
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
                            <th>Estatus</th>
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
                                               <td>
                                    <?php if ($rutas['estatus'] === 'ACTIVO'): ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php elseif ($rutas['estatus'] === 'BAJA'): ?>
                                        <span class="badge bg-danger">INACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($rutas['estatus']) ?></span>
                                    <?php endif; ?>
                                </td>


                                    <td class="actions">
                                        <!-- Icono de "editar" que abre el modal de confirmación -->

                                        <a href="#"
                                            data-id="<?= htmlspecialchars($rutas['id']) ?>"
                                            data-modulo="<?= htmlspecialchars($rutas['modulo']) ?>"
                                            data-ruta="<?= htmlspecialchars($rutas['ruta']) ?>"
                                            data-toggle="modal"
                                            data-target="#editModuloModal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        &nbsp;
                                        &nbsp;
                                        <!-- Icono de "eliminar" que abre el modal de confirmación -->
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($rutas['id']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModuloModal"
                                            onclick="setUserId(<?= htmlspecialchars($rutas['id']) ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
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
            <!-- Modal para crear departamento -->
            <div class="modal fade" id="createModuloModal" tabindex="-1" aria-labelledby="createModuloModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createDepartmentModalLabel">Crear Ruta del Módulo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_modulo.php" method="POST" enctype="multipart/form-data">
                                <div class="card">
                                    <h5 class="card-header">Información General</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="empresa_id" class="control-label">Modulo*</label>
                                                    <input type="text" id="modulo" name="modulo" class="form-control" placeholder="Modulo" required>

                                                </div>
                                                <div class="form-group">
                                                    <label for="nombre" class="control-label">Ruta *</label>
                                                    <input type="text" id="ruta" name="ruta" class="form-control" placeholder="Ruta" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary mx-2">Guardar</button>
                                    <button type="button" class="btn btn-warning mx-2" data-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal para editar departamento -->
            <div class="modal fade" id="editModuloModal" tabindex="-1" aria-labelledby="editModuloModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModuloModalLabel">Editar Ruta del Modulo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="update_modulo.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" id="editModuloId">
                                <div class="card">
                                    <h5 class="card-header">Información General</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="modulo" class="control-label">Modulo*</label>
                                                    <input type="text" id="editModulo" name="modulo" class="form-control" placeholder="Modulo" required>

                                                </div>
                                                <div class="form-group">
                                                    <label for="ruta" class="control-label">Ruta *</label>
                                                    <input type="text" id="editRuta" name="ruta" class="form-control" placeholder="Ruta" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary mx-2">Actualizar</button>
                                    <button type="button" class="btn btn-warning mx-2" data-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal de confirmación para dar de baja al Modulo -->
            <div class="modal fade" id="deleteModuloModal" tabindex="-1" aria-labelledby="deleteModuloModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModuloModalLabel">Confirmación de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas dar de baja el modulo?
                        </div>
                        <div class="modal-footer">
                            <form action="delete_modulo.php" method="POST">
                                <input type="hidden" name="id" id="id">
                                <button type="submit" class="btn btn-danger">Sí, dar de baja</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


    <script>
        // Función para pasar el ID del usuario al campo oculto del formulario en el modal de eliminación
        function setUserId(id) {
            document.getElementById('id').value = id;
        }

        // Capturar el evento de apertura del modal de edición
        $('#editModuloModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Elemento que activó el modal
            var id = button.data('id'); // ID del modulo
            var modulo = button.data('modulo'); // Nombre del modulo
            var ruta = button.data('ruta'); // Nombre de la ruta



            // Asignar valores a los campos del formulario en el modal
            $('#editModuloId').val(id);
            $('#editModulo').val(modulo);
            $('#editRuta').val(ruta);


        });
    </script>






    </main>
    </div>




    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'footer.php'; ?>
</body>

</html>