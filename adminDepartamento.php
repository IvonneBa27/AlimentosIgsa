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
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna




include 'db_connection.php';

// Consulta para obtener los departamentos con sus empresas y estatus
$sql = "SELECT dep.id, dep.nombre, cs.status, emp.nombre as empresa
        FROM departamento dep
        INNER JOIN catalog_status cs ON cs.status_id = dep.estatus_id
        INNER JOIN empresa emp ON dep.empresa_id = emp.id
        ORDER BY dep.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener todas las empresas para el select
$sqlEmpresas = "SELECT id, nombre FROM empresa";
$stmtEmpresas = $conn->prepare($sqlEmpresas);
$stmtEmpresas->execute();
$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> A D M I N I S T R A C I Ó N &nbsp; &nbsp; C O M E D O R </title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="node_modules/sweetalert2/dist/sweetalert2.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">





</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h3 class="h3">DEPARTAMENTOS</h3>
            <div class="btn-toolbar mb-2 mb-md-0">

                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createDepartmentModal" onclick="location.href = '#';"><i class="bi bi-person-plus"></i> Agregar Departamento</button>
                </div>

                <form action="">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminDepartamento.php';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>







        <div class="table-responsive" style="max-height: 830px; overflow-y: auto;">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Departamento</th>
                        <th>Empresa</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departamentos)) : ?>
                        <?php foreach ($departamentos as $departamento) : ?>
                            <tr>
                                <td><?= htmlspecialchars($departamento['id']) ?></td>
                                <td><?= htmlspecialchars($departamento['nombre']) ?></td>
                                <td><?= htmlspecialchars($departamento['empresa']) ?></td>
                                <td>
                                    <?php if ($departamento['status'] === 'ACTIVO'): ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php elseif ($departamento['status'] === 'BAJA'): ?>
                                        <span class="badge bg-danger">BAJA</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($departamento['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <!-- Icono de "editar" que abre el modal de confirmación -->

                                    <a href="#"
                                        data-id="<?= htmlspecialchars($departamento['id']) ?>"
                                        data-nombre="<?= htmlspecialchars($departamento['nombre']) ?>"
                                        data-toggle="modal"
                                        data-target="#editDepartmentModal">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    &nbsp;
                                    &nbsp;
                                    <!-- Icono de "eliminar" que abre el modal de confirmación -->
                                    <a href="#"
                                        data-id="<?= htmlspecialchars($departamento['id']) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteDepartmentModal"
                                        onclick="setUserId(<?= htmlspecialchars($departamento['id']) ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No se encontraron registros.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para crear departamento -->
        <div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-labelledby="createDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDepartmentModalLabel">Crear Departamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="insert_department.php" method="POST" enctype="multipart/form-data">
                            <div class="card">
                                <h5 class="card-header">Información General</h5>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="empresa_id" class="control-label">Empresa *</label>
                                                <select id="empresa_id" name="empresa_id" class="form-control" required>
                                                    <option value="">Seleccione una empresa</option>
                                                    <?php foreach ($empresas as $empresa): ?>
                                                        <option value="<?= htmlspecialchars($empresa['id']) ?>"><?= htmlspecialchars($empresa['nombre']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="nombre" class="control-label">Departamento *</label>
                                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre del departamento" required style="text-transform:uppercase;">
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
        <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDepartmentModalLabel">Editar Departamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="update_department.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="editDepartmentId">
                            <div class="card">
                                <h5 class="card-header">Información General</h5>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="editNombre" class="control-label">Departamento *</label>
                                                <input type="text" id="editNombre" name="nombre" class="form-control" placeholder="Nombre del departamento" required style="text-transform:uppercase;">
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



        <!-- Modal de confirmación para dar de baja al departamento -->
        <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDepartmentModalLabel">Confirmación de Baja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas dar de baja el departamento?
                    </div>
                    <div class="modal-footer">
                        <form action="delete_department.php" method="POST">
                            <input type="hidden" name="id" id="id">
                            <button type="submit" class="btn btn-danger">Sí, dar de baja</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


    <script>
        // Función para pasar el ID del usuario al campo oculto del formulario en el modal de eliminación
        function setUserId(id) {
            document.getElementById('id').value = id;
        }

        // Capturar el evento de apertura del modal de edición
        $('#editDepartmentModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Elemento que activó el modal
            var id = button.data('id'); // ID del departamento
            var nombre = button.data('nombre'); // Nombre del departamento


            // Asignar valores a los campos del formulario en el modal
            $('#editDepartmentId').val(id);
            $('#editNombre').val(nombre);

        });
    </script>







    </main>
    </div>
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>
</body>

</html>