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

// Configuración para la paginación
$registrosPorPagina = 50;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Obtener el total de registros
$totalRegistrosQuery = "SELECT COUNT(*) as total from usuarios ";
$totalRegistrosStmt = $conn->query($totalRegistrosQuery);
$totalRegistros = $totalRegistrosStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular el número total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Consulta base con paginación
$sql = "SELECT u.id, u.usuario, u.nombre_completo, u.apaterno, u.amaterno, u.nombre, r.role, u.correo, cs.status
        FROM usuarios u 
        INNER JOIN catalog_status cs ON cs.status_id = u.estatus_id
        LEFT JOIN roles r ON u.rol_id = r.id
        ORDER BY u.id
        LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Obtener los roles para el select
$sqlRoles = "SELECT id, role FROM roles WHERE estatus_id = 1";
$stmtRoles = $conn->prepare($sqlRoles);
$stmtRoles->execute();
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Obtener los sitios para el select
$sqlSitios = "SELECT id, nombre FROM sitios";
$stmtSitios = $conn->prepare($sqlSitios);
$stmtSitios->execute();
$sitios = $stmtSitios->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> A D M I N I S T R A C I Ó N &nbsp; &nbsp;</title>
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
                <h3 class="h3">USUARIOS</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createUsuarioModal" onclick="location.href = '#';"><i class="bi bi-person-plus"></i> Agregar Usuario</button>
                    </div>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminUsuariosAdmin.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!--  <canvas class="my-4 w-100" id="myChart" width="900" height="200"></canvas>-->



            <div class="btn-group align-end">

                &nbsp;
            </div>

            <br>

            <br>

            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-striped ">
                    <thead class="table-dark">
                        <tr>
                            <th>Id</th>
                            <th>Nombre Completo</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Correo</th>
                            <th>Estatus</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($usuarios)) : ?>
                            <?php foreach ($usuarios as $usuario) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><?= htmlspecialchars($usuario['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                                    <td><?= htmlspecialchars($usuario['role']) ?></td>
                                    <td><?= htmlspecialchars($usuario['correo']) ?></td>



                                    <!--<td><?= htmlspecialchars($usuario['status']) ?></td>-->
                                    <td>
                                        <?php if (trim($usuario['status']) === 'ACTIVO'): ?>
                                            <span class="badge bg-success">ACTIVO</span>
                                        <?php elseif (trim($usuario['status']) === 'BAJA'): ?>
                                            <span class="badge bg-danger">BAJA</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars(trim($usuario['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>


                                    <td class="actions">
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($usuario['id']) ?>"
                                            data-apaterno="<?= htmlspecialchars($usuario['apaterno']) ?>"
                                            data-amaterno="<?= htmlspecialchars($usuario['amaterno'] ?? '') ?>"
                                            data-nombre="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                            data-usuario="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>"
                                            data-role="<?= htmlspecialchars($usuario['role'] ?? '') ?>"
                                            data-correo="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUsuarioModal">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        &nbsp;
                                        <!-- Icono de "eliminar" que abre el modal de confirmación -->
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($usuario['id']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUsuarioModal"
                                            onclick="setUserId(<?= htmlspecialchars($usuario['id']) ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>



                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">No se encontraron usuarios.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Mostrar total de registros y paginación -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Total de Registros <strong><?= $totalRegistros ?></strong> registros</p>
                <p class="mb-0">Página <strong><?= $paginaActual ?></strong> de <strong><?= $totalPaginas ?></strong></p>
            </div>


            <!-- Controles de paginación -->
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
    </div>
    </div>

    <!-- Modal Crear-->
    <div class="modal fade" id="createUsuarioModal" tabindex="-1" aria-labelledby="createUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUsuarioModalLabel">Crear Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="insert_usuarios.php" method="POST" enctype="multipart/form-data">
                        <!-- Información General -->
                        <div class="card mb-3">
                            <h5 class="card-header">Información General</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="a_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" id="apaterno" name="apaterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="a_materno" class="form-label">Apellido Materno *</label>
                                        <input type="text" id="amaterno" name="amaterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="nombre" class="form-label">Nombre(s) *</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="num_empleado" class="form-label">Usuario *</label>
                                        <input type="text" id="usuario" name="usuario" class="form-control">
                                    </div>
                                    <div class="col-4">
                                        <label for="contrasena" class="form-label">Contraseña *</label>
                                        <input type="password" id="contrasena" name="contrasena" class="form-control">
                                    </div>
                                    <div class="col-8">
                                        <label for="empresa" class="form-label">Rol *</label>
                                        <select id="rol_id" name="rol_id" class="form-select" required>
                                            <option value="">Seleccione un rol</option>
                                            <?php foreach ($roles as $rol) : ?>
                                                <option value="<?= $rol['id'] ?>"><?= $rol['role'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <label for="empresa" class="form-label">Sitio *</label>
                                        <select id="sitio_id" name="sitio_id" class="form-select" required>
                                            <option value="">Seleccione el sitio</option>
                                            <?php foreach ($sitios as $sitio) : ?>
                                                <option value="<?= $sitio['id'] ?>"><?= $sitio['nombre'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-8">
                                        <label for="correo" class="form-label">Correo Electronico *</label>
                                        <input type="text" id="correo" name="correo" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>


    <!-- Modal Editar-->
    <div class="modal fade" id="editUsuarioModal" tabindex="-1" aria-labelledby="editUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_comensal.php" method="POST" enctype="multipart/form-data">

                        <input type="hidden" id="edit_id" name="id">

                        <!-- Información General -->
                        <div class="card mb-3">
                            <h5 class="card-header">Información General</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="edit_a_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" id="edit_apaterno" name="apaterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_a_materno" class="form-label">Apellido Materno *</label>
                                        <input type="text" id="edit_amaterno" name="amaterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_nombre" class="form-label">Nombre(s) *</label>
                                        <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_num_empleado" class="form-label">Usuario *</label>
                                        <input type="number" id="edit_usuario" name="usuario" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="contrasena" class="form-label">Contraseña *</label>
                                        <input type="password" id="contrasena" name="contrasena" class="form-control">
                                    </div>

                                    <div class="col-8">
                                        <label for="edit_rol" class="form-label">Rol *</label>
                                        <select id="edit_rol" name="rol_id" class="form-select" required>
                                            <option value="">Seleccione un rol</option>
                                            <?php foreach ($roles as $rol) : ?>
                                                <option value="<?= $rol['id'] ?>"><?= $rol['role'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>




                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <!-- Modal de confirmación para dar de baja el producto -->
    <div class="modal fade" id="deleteUsuarioModal" tabindex="-1" aria-labelledby="deleteUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUsuarioModalLabel">Confirmación de Baja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas dar de baja al usuario?
                </div>
                <div class="modal-footer">
                    <form action="delete_usuarios.php" method="POST">
                        <input type="hidden" name="id" id="id">
                        <button type="submit" class="btn btn-danger">Sí, dar de baja</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Función para pasar el ID del usuario al campo oculto del formulario en el modal de eliminación
        function setUserId(id) {
            document.getElementById('id').value = id;
        }


        // Capturar el evento de apertura del modal de edición
        $('#editUsuarioModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Elemento que activó el modal

            // Obtener los datos del usuario desde los atributos data-* del botón
            var id = button.data('id');
            var apaterno = button.data('apaterno');
            var amaterno = button.data('amaterno');
            var nombre = button.data('nombre');
            var usuario = button.data('usuario');
            var rol_id = button.data('rol_id');
            var correo = button.data('correo');



            // Asignar los valores a los campos del formulario en el modal
            $('#edit_id').val(id);
            $('#edit_apaterno').val(apaterno);
            $('#edit_amaterno').val(amaterno);
            $('#edit_nombre').val(nombre);
            $('#edit_usuario').val(usuario);
            $('#edit_rol_id').val(rol_id);
            $('#edit_correo').val(correo);

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