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
$totalRegistrosQuery = "SELECT COUNT(*) as total FROM comensal";
$totalRegistrosStmt = $conn->query($totalRegistrosQuery);
$totalRegistros = $totalRegistrosStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular el número total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Consulta base con paginación
$sql = "SELECT co.id, co.nombre_completo, co.a_paterno, co.a_materno, co.nombre, co.num_empleado, co.empresa, co.departamento, co.correo, co.barcode, co.barcode_path, co.imagePath, emp.nombre as nombre_empresa, co.fecha_de_alta, co.puesto, cs.status
        FROM comensal co 
        INNER JOIN catalog_status cs ON cs.status_id = co.estatus
        INNER JOIN empresa emp ON co.empresa = emp.id
        ORDER BY co.id
        LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$comensales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener las empresas para el select
$sqlEmpresas = "SELECT id, nombre FROM empresa WHERE estatus_id = 1";
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
                <h3 class="h3">COMENSALES</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createComensalModal" onclick="location.href = '#';"><i class="bi bi-person-plus"></i> Agregar Comensal</button>
                    </div>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminComensales.php';">
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

            <form id="filtro-form" class="mb-4">
                <div class="row">

                    <div class="col-md-3">
                        <label for="tipo_usuario" class="form-label">Empresa</label>
                        <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?= htmlspecialchars($empresa['id']) ?>">
                                    <?= htmlspecialchars($empresa['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="num_empleado" class="form-label">Nombre completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" placeholder="Nombre completo">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="btn-filtrar" class="btn btn-primary w-100">Filtrar</button>
                    </div>

                </div>
            </form>



            <div class="btn-group align-end">
                <!--<button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createComensalModal">Nuevo Comensal</button>-->
                &nbsp;
            </div>

            <br>

            <br>

            <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
                <table class="table table-striped ">
                    <thead class="table-dark">
                        <tr>
                            <th>Num. Empleado</th>
                            <th>Nombre Completo</th>
                            <th>Empresa</th>
                            <th>Correo</th>
                            <th>Codigo</th>
                            <th>Codigo de Usuario</th>
                            <th>Imagen de Perfil</th>
                            <th>Estatus</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($comensales)) : ?>
                            <?php foreach ($comensales as $comensal) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($comensal['num_empleado']) ?></td>
                                    <td><?= htmlspecialchars($comensal['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($comensal['nombre_empresa']) ?></td>
                                    <td><?= htmlspecialchars($comensal['correo']) ?></td>
                                    <td><?= htmlspecialchars($comensal['barcode']) ?></td>
                                    <td class="text-center align-middle">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#barcodeModal"
                                            data-barcode="<?= htmlspecialchars($comensal['barcode_path']) ?>">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal"
                                            data-image="<?= htmlspecialchars($comensal['imagePath']) ?>">
                                            <i class="fas fa-user-circle "></i>
                                        </a>
                                    </td>


                                    <!--<td><?= htmlspecialchars($comensal['status']) ?></td>-->
                                    <td>
                                        <?php if (trim($comensal['status']) === 'ACTIVO'): ?>
                                            <span class="badge bg-success">ACTIVO</span>
                                        <?php elseif (trim($comensal['status']) === 'BAJA'): ?>
                                            <span class="badge bg-danger">BAJA</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars(trim($comensal['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>


                                    <td class="actions">
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($comensal['id']) ?>"
                                            data-apaterno="<?= htmlspecialchars($comensal['a_paterno']) ?>"
                                            data-amaterno="<?= htmlspecialchars($comensal['a_materno'] ?? '') ?>"
                                            data-nombre="<?= htmlspecialchars($comensal['nombre'] ?? '') ?>"
                                            data-numempleado="<?= htmlspecialchars($comensal['num_empleado'] ?? '') ?>"
                                            data-empresa="<?= htmlspecialchars($comensal['empresa'] ?? '') ?>"
                                            data-departamento="<?= htmlspecialchars($comensal['departamento'] ?? '') ?>"
                                            data-puesto="<?= htmlspecialchars($comensal['puesto'] ?? '') ?>"
                                            data-correo="<?= htmlspecialchars($comensal['correo'] ?? '') ?>"
                                            data-barcode="<?= htmlspecialchars($comensal['barcode'] ?? '') ?>"
                                            data-image="<?= htmlspecialchars($comensal['imagePath'] ?? '') ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editComensalModal">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        &nbsp;
                                        <!-- Icono de "eliminar" que abre el modal de confirmación -->
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($comensal['id']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteComensalModal"
                                            onclick="setUserId(<?= htmlspecialchars($comensal['id']) ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>



                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">No se encontraron comensales.</td>
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
    <div class="modal fade" id="createComensalModal" tabindex="-1" aria-labelledby="createComensalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createComensalModalLabel">Crear Comensal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="insert_employee.php" method="POST" enctype="multipart/form-data">
                        <!-- Información General -->
                        <div class="card mb-3">
                            <h5 class="card-header">Información General</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="a_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" id="a_paterno" name="a_paterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="a_materno" class="form-label">Apellido Materno *</label>
                                        <input type="text" id="a_materno" name="a_materno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="nombre" class="form-label">Nombre(s) *</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="num_empleado" class="form-label">No. Empleado *</label>
                                        <input type="number" id="num_empleado" name="num_empleado" class="form-control">
                                    </div>
                                    <div class="col-8">
                                        <label for="empresa" class="form-label">Empresa *</label>
                                        <select id="empresa" name="empresa" class="form-select" required>
                                            <option value="">Seleccione una empresa</option>
                                            <?php foreach ($empresas as $empresa) : ?>
                                                <option value="<?= $empresa['id'] ?>"><?= $empresa['nombre'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-8">
                                        <label for="departamento" class="form-label">Departamento *</label>
                                        <select id="departamento" name="departamento" class="form-select" required>
                                            <option value="">Seleccione un departamento</option>
                                            <!-- Los departamentos se cargarán dinámicamente -->
                                        </select>
                                    </div>

                                    <div class="col-4">
                                        <label for="puesto" class="form-label">Puesto *</label>
                                        <input type="text" id="puesto" name="puesto" class="form-control" required>
                                    </div>
                                    <div class="col-8">
                                        <label for="correo" class="form-label">Correo Electronico *</label>
                                        <input type="text" id="correo" name="correo" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Imagen Personal -->
                        <div class="card mb-3">
                            <h5 class="card-header">Imagen Personal</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <video id="video-create" width="100%" autoplay></video>
                                        <button type="button" class="btn btn-primary mt-2" id="snap-create">Capturar Foto</button>
                                    </div>
                                    <div class="col-md-6">
                                        <canvas id="canvas-create" style="display:none;"></canvas>
                                        <img id="image-create" alt="Foto capturada" width="100%">
                                        <input type="hidden" name="photo" id="imageData-create">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tiempos de Comida -->
                        <div class="card mb-3">
                            <h5 class="card-header">Tiempos de Comida</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tiempos_comida[]" value="Desayuno" id="desayuno">
                                            <label class="form-check-label" for="desayuno">
                                                Desayuno
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tiempos_comida[]" value="Comida" id="comida">
                                            <label class="form-check-label" for="comida">
                                                Comida
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tiempos_comida[]" value="Cena" id="cena">
                                            <label class="form-check-label" for="cena">
                                                Cena
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tiempos_comida[]" value="Colación" id="colacion">
                                            <label class="form-check-label" for="colacion">
                                                Colación
                                            </label>
                                        </div>
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
    <div class="modal fade" id="editComensalModal" tabindex="-1" aria-labelledby="editComensalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editComensalModalLabel">Editar Comensal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_comensal.php" method="POST" enctype="multipart/form-data">
                        <!-- ID oculto del comensal -->
                        <input type="hidden" id="edit_id" name="id">

                        <!-- Información General -->
                        <div class="card mb-3">
                            <h5 class="card-header">Información General</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="edit_a_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" id="edit_a_paterno" name="a_paterno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_a_materno" class="form-label">Apellido Materno *</label>
                                        <input type="text" id="edit_a_materno" name="a_materno" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_nombre" class="form-label">Nombre(s) *</label>
                                        <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_num_empleado" class="form-label">No. Empleado *</label>
                                        <input type="number" id="edit_num_empleado" name="num_empleado" class="form-control" required>
                                    </div>
                                    <div class="col-8">
                                        <label for="edit_empresa" class="form-label">Empresa *</label>
                                        <select id="edit_empresa" name="empresa" class="form-select" required>
                                            <option value="">Seleccione una empresa</option>
                                            <?php foreach ($empresas as $empresa) : ?>
                                                <option value="<?= $empresa['id'] ?>"><?= $empresa['nombre'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <label for="edit_departamento" class="form-label">Departamento *</label>
                                        <select id="edit_departamento" name="departamento" class="form-select" required>
                                            <option value="">Seleccione un departamento</option>
                                            <?php foreach ($departamentos as $departamento) : ?>
                                                <option value="<?= $departamento['id'] ?>"><?= $departamento['nombre'] ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label for="edit_puesto" class="form-label">Puesto *</label>
                                        <input type="text" id="edit_puesto" name="puesto" class="form-control" required>
                                    </div>

                                    <div class="col-8">
                                        <label for="edit_correo" class="form-label">Correo Electronico *</label>
                                        <span id="email-display">********@<?= explode('@', htmlspecialchars($comensal['correo'] ?? ''))[1] ?></span>
                                        <span id="email-full" style="display: none;"></span>
                                        <i id="toggle-email" class="fas fa-eye" style="cursor: pointer;"></i> <!-- Icono de ojo -->
                                        <!-- <input type="text" id="edit_correo" name="correo" class="form-control" required>-->
                                    </div>
                                    <div class="col-8">
                                        <label for="edit_correo" class="form-label">Codigo del Empleado</label>
                                        <input type="text" id="edit_barcode" name="barcode" class="form-control" readonly required>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagen Personal -->
                        <div class="card mb-3">
                            <h5 class="card-header">Imagen Personal</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <video id="video-edit" width="100%" autoplay></video>
                                        <button type="button" class="btn btn-primary mt-2" id="snap-edit">Capturar Foto</button>
                                    </div>
                                    <div class="col-md-6">
                                        <canvas id="canvas-edit" style="display:none;"></canvas>
                                        <img id="image-edit" alt="Foto capturada" width="100%">
                                        <input type="hidden" name="photo" id="imageData-edit">
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


    <!-- Modal: Código de Barras -->
    <div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="barcodeModalLabel">Código de Barras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p id="barcodeInfo"></p>
                    <img id="barcodeImage" src="" alt="Código de Barras" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="printBarcode">Imprimir</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Imagen de Perfil -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Imagen de Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="profileImage" src="" alt="Imagen de Perfil" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para dar de baja el producto -->
    <div class="modal fade" id="deleteComensalModal" tabindex="-1" aria-labelledby="deleteComensalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteComensalModalLabel">Confirmación de Baja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas dar de baja al comensal?
                </div>
                <div class="modal-footer">
                    <form action="delete_comensal.php" method="POST">
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
        function initializeCamera(modalId, videoId, canvasId, imageId, snapId, imageDataId) {
            const modal = document.getElementById(modalId);
            const video = document.getElementById(videoId);
            const canvas = document.getElementById(canvasId);
            const image = document.getElementById(imageId);
            const snapButton = document.getElementById(snapId);
            const imageDataInput = document.getElementById(imageDataId);

            modal.addEventListener('shown.bs.modal', () => {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({
                            video: true
                        })
                        .then((stream) => {
                            video.srcObject = stream;
                        })
                        .catch((err) => {
                            console.error('Error al acceder a la cámara:', err);
                        });
                }
            });

            modal.addEventListener('hidden.bs.modal', () => {
                if (video.srcObject) {
                    const tracks = video.srcObject.getTracks();
                    tracks.forEach(track => track.stop());
                    video.srcObject = null;
                }
            });

            snapButton.addEventListener('click', () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                const dataUrl = canvas.toDataURL('image/png');
                image.src = dataUrl;
                imageDataInput.value = dataUrl;
                image.style.display = 'block';
                canvas.style.display = 'none';
            });
        }

        // Inicializa la cámara para cada modal
        initializeCamera('createComensalModal', 'video-create', 'canvas-create', 'image-create', 'snap-create', 'imageData-create');
        initializeCamera('editComensalModal', 'video-edit', 'canvas-edit', 'image-edit', 'snap-edit', 'imageData-edit');



        // Cargar departamentos dinámicamente al seleccionar una empresa
        const empresaSelect = document.getElementById('empresa');
        const departamentoSelect = document.getElementById('departamento');
        if (empresaSelect && departamentoSelect) {
            empresaSelect.addEventListener('change', function() {
                const empresaId = this.value;

                fetch(`get_departments.php?empresa_id=${empresaId}`)
                    .then(response => response.json())
                    .then(data => {
                        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
                        data.forEach(departamento => {
                            departamentoSelect.innerHTML += `<option value="${departamento.id}">${departamento.nombre}</option>`;
                        });
                    })
                    .catch(error => console.error('Error al cargar departamentos:', error));
            });
        }



        // Modal para código de barras
        const barcodeModal = document.getElementById('barcodeModal');
        if (barcodeModal) {
            barcodeModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const barcodePath = button.getAttribute('data-barcode');
                const info = button.getAttribute('data-info');
                document.getElementById('barcodeInfo').textContent = info;
                document.getElementById('barcodeImage').src = barcodePath;
            });
        }

        // Modal para imagen de perfil
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                document.getElementById('profileImage').src = imageSrc || 'default.jpg';
            });
        }


        // Función para pasar el ID del usuario al campo oculto del formulario en el modal de eliminación
        function setUserId(id) {
            document.getElementById('id').value = id;
        }


        // Capturar el evento de apertura del modal de edición
        $('#editComensalModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Elemento que activó el modal

            // Obtener los datos del comensal desde los atributos data-* del botón
            var id = button.data('id');
            var a_paterno = button.data('apaterno');
            var a_materno = button.data('amaterno');
            var nombre = button.data('nombre');
            var num_empleado = button.data('numempleado');
            var empresa = button.data('empresa');
            var departamento = button.data('departamento');
            var puesto = button.data('puesto');
            var correo = button.data('correo');
            var barcode = button.data('barcode');
            var image = button.data('image');


            // Asignar los valores a los campos del formulario en el modal
            $('#edit_id').val(id);
            $('#edit_a_paterno').val(a_paterno);
            $('#edit_a_materno').val(a_materno);
            $('#edit_nombre').val(nombre);
            $('#edit_num_empleado').val(num_empleado);
            $('#edit_empresa').val(empresa);
            $('#edit_departamento').val(departamento);
            $('#edit_puesto').val(puesto);
            $('#edit_correo').val(correo);
            $('#edit_barcode').val(barcode);


            // Si existe una imagen, mostrarla en el modal
            if (image) {
                $('#image-edit').attr('src', image);
                $('#imageData').val(image); // Asignar la imagen (base64 o URL) al campo oculto
            }
        });






        function imprimirCodigoBarrasDesdeModal() {
            // Obtener la imagen del código de barras
            const barcodeImage = document.getElementById("barcodeImage").src;

            // Crear contenido para la impresión con ajustes para la impresora térmica HKA80
            const ticketContent = `
                            <html>
                            <head>
                                <title>Impresión Código de Barras</title>
                                <style>
                                    @page { 
                                        size: 80mm auto; /* Ajuste al ancho de la HKA80 */
                                        margin: 0;
                                    }
                                    body { 
                                        text-align: center; 
                                        font-family: Arial, sans-serif; 
                                        margin: 0; 
                                        padding: 0; 
                                        width: 80mm; /* Ancho de la HKA80 */
                                        height: auto; /* Altura dinámica según contenido */
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        display: block; /* Elimina espacios extras debajo de la imagen */
                                    }
                                    img { 
                                        max-width: 90%; 
                                        height: auto;
                                    }
                                </style>
                            </head>
                            <body>
                                <img src="${barcodeImage}" alt="Código de Barras">
                            </body>
                            </html>
                            `;

            // Abrir ventana emergente para imprimir
            const printWindow = window.open('', '', 'width=400,height=300');
            printWindow.document.open();
            printWindow.document.write(ticketContent);
            printWindow.document.close();

            // Esperar un poco para cargar y luego imprimir
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }




        // Vincular la función al botón de impresión
        document.getElementById("printBarcode").addEventListener("click", imprimirCodigoBarrasDesdeModal);


        $(document).ready(function() {
            $('#btn-filtrar').click(function() {
                var empresa = $('#tipo_usuario').val(); // Corregido el ID del select
                var nombre = $('#nombre_completo').val();



                $.ajax({
                    url: 'filtrar_comensales.php', // Archivo PHP que procesará el filtro
                    type: 'POST',
                    data: {
                        empresa: empresa,
                        nombre: nombre
                    },
                    beforeSend: function() {
                        $('tbody').html('<tr><td colspan="9" class="text-center">Cargando...</td></tr>');
                    },
                    success: function(response) {
                        console.log("Respuesta recibida:", response); // Depuración
                        $('tbody').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en AJAX:", error);
                        alert('Hubo un error al cargar los datos.');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editComensalModal');
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // El botón o enlace que abrió el modal
                const correo = button.getAttribute('data-correo'); // Obtener el correo del enlace

                // Mostrar el correo oculto en el modal
                const emailDisplay = document.getElementById('email-display');
                const emailFull = document.getElementById('email-full');
                const icon = document.getElementById('toggle-email');

                // Aquí se asume que el correo tiene un formato válido, lo mostramos parcialmente
                emailDisplay.textContent = `********@${correo.split('@')[1]}`; // Mostrar solo el dominio y ocultar el resto
                emailFull.textContent = correo; // El correo completo está en emailFull

                // Funcionalidad para mostrar/ocultar el correo
                icon.addEventListener('click', function() {
                    if (emailDisplay.style.display !== 'none') {
                        emailDisplay.style.display = 'none';
                        emailFull.style.display = 'inline';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash'); // Cambiar icono de ojo abierto a cerrado
                    } else {
                        emailFull.style.display = 'none';
                        emailDisplay.style.display = 'inline';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye'); // Cambiar icono de ojo cerrado a abierto
                    }
                });
            });
        });
    </script>










    </main>
    </div>



    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/seguridad.js"></script>
    <!-- <script src="js/dashboardComensales.js"></script>-->

    <?php include 'footer.php'; ?>
</body>

</html>