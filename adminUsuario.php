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

// Consulta para obtener los tipos de usuario y sus estatus
$sql = "SELECT tu.id, tu.tipo_usuario, cs.status
        FROM tipo_usuario tu
        INNER JOIN catalog_status cs ON cs.status_id = tu.status_id
        ORDER BY tu.id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$tipo_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<div>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h3 class="h3">TIPO DE USUARIO</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-target="#createUserModal" data-bs-toggle="modal"><i class="bi bi-person-plus"></i> Agregar Usuario</button>
                    </div>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminUsuario.php';">
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
                            <th>Usuario</th>
                            <th>Estatus</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tipo_usuarios)) : ?>
                            <?php foreach ($tipo_usuarios as $tipo_usuario) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipo_usuario['id']) ?></td>
                                    <td><?= htmlspecialchars($tipo_usuario['tipo_usuario']) ?></td>
                                    <td>
                                        <?php if ($tipo_usuario['status'] === 'ACTIVO'): ?>
                                            <span class="badge bg-success">ACTIVO</span>
                                        <?php elseif ($tipo_usuario['status'] === 'BAJA'): ?>
                                            <span class="badge bg-danger">BAJA</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($tipo_usuario['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Icono de "editar" -->
                                        <a href="#"
                                            class="text-primary"
                                            data-id="<?= htmlspecialchars($tipo_usuario['id']) ?>"
                                            data-tipo_usuario="<?= htmlspecialchars($tipo_usuario['tipo_usuario']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        &nbsp;&nbsp;
                                        <!-- Icono de "eliminar" -->
                                        <a href="#"
                                            class="text-primary"
                                            data-id="<?= htmlspecialchars($tipo_usuario['id']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal"
                                            onclick="setUserId(<?= htmlspecialchars($tipo_usuario['id']) ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">No se encontraron usuarios.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
            </div>

            <!-- Modal para crear un nuevo usuario -->
            <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createUserModalLabel">Crear Nuevo Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="insert_type_user.php" method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="tipo_usuario" class="form-label">Tipo de Usuario *</label>
                                    <input type="text" id="tipo_usuario" name="tipo_usuario" class="form-control" placeholder="Tipo de Usuario" required style="text-transform:uppercase;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para editar usuario -->
            <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="update_type_user.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="editUserId">
                                <div class="mb-3">
                                    <label for="editTipoUsuario" class="form-label">Tipo de Usuario *</label>
                                    <input type="text" id="editTipoUsuario" name="tipo_usuario" class="form-control" placeholder="Tipo de Usuario" required style="text-transform:uppercase;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal de confirmación para eliminar usuario -->
            <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteUserModalLabel">Confirmación de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="delete_user_type.php" method="POST">
                            <div class="modal-body">
                                <p>¿Estás seguro de que deseas dar de baja este tipo de usuario?</p>
                                <input type="hidden" name="id" id="deleteUserId">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger">Sí, dar de baja</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <!-- Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Scripts para Modales -->
            <script>
                // Función para pasar el ID del usuario al formulario de eliminación
                function setUserId(id) {
                    document.getElementById('deleteUserId').value = id;
                }

                // Capturar evento al abrir el modal de edición
                document.getElementById('editUserModal').addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var tipoUsuario = button.getAttribute('data-tipo_usuario');

                    // Asignar valores al formulario
                    document.getElementById('editUserId').value = id;
                    document.getElementById('editTipoUsuario').value = tipoUsuario;
                });
            </script>









    </main>

            </div>
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/usuarios.js"></script>
    <script src="js/seguridad.js"></script>

    
    <?php include 'footer.php'; ?>
</body>

</html>
