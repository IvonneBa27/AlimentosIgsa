<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d\TH:i:s");
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

// Consultar los datos de tipo_usuario
$queryTipoUsuario = "SELECT id, tipo_usuario FROM tipo_usuario";
$stmtTipoUsuario = $conn->query($queryTipoUsuario);
$tiposUsuario = $stmtTipoUsuario->fetchAll(PDO::FETCH_ASSOC);

// Consultar los datos de producto
$queryProducto = "SELECT id, producto FROM producto";
$stmtProducto = $conn->query($queryProducto);
$productos = $stmtProducto->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> O P E R A C I Ó N &nbsp; &nbsp; C O M E D O R </title>
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
                <h3 class="h3">COMENSAL REPORTERÍA</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'reporteGeneral.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>



             <!-- Formulario de filtros -->
        <form id="filtro-form" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="tipo_producto" class="form-label">Tipo de Producto</label>
                    <select id="tipo_producto" name="tipo_producto" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?= htmlspecialchars($producto['id']) ?>">
                                <?= htmlspecialchars($producto['producto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($tiposUsuario as $tipo): ?>
                            <option value="<?= htmlspecialchars($tipo['id']) ?>">
                                <?= htmlspecialchars($tipo['tipo_usuario']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="num_empleado" class="form-label">Número de Empleado</label>
                    <input type="text" id="num_empleado" name="num_empleado" class="form-control" placeholder="Número">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btn-filtrar" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btn-exportar" class="btn btn-success w-100">Exportar a Excel</button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btn-correo" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo">
                        Enviar por correo
                    </button>
                </div>
            </div>
        </form>

        <!-- Tabla de registros -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Usuario</th>
                        <th>Número de Empleado</th>
                        <th>Nombre Completo</th>
                        <th>Empresa</th>
                        <th>Departamento</th>
                        <th>Producto</th>
                        <th>Importe</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody id="tabla-registros">
                    <!-- Datos dinámicos -->
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <nav>
            <ul id="pagination" class="pagination">
                <!-- Paginación dinámica -->
            </ul>
        </nav>
    </div>

    <!-- Modal para Enviar por Correo -->
    <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalEnviarCorreoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnviarCorreoLabel">Enviar Reporte por Correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-enviar-correo">
                        <div class="mb-3">
                            <label for="correos" class="form-label">Correos</label>
                            <input type="text" id="correos" name="correos" class="form-control" placeholder="Ingresa correos separados por coma">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btn-enviar-correo" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function cargarDatos(pagina = 1) {
                const filtros = {
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    tipo_producto: $('#tipo_producto').val(),
                    tipo_usuario: $('#tipo_usuario').val(),
                    num_empleado: $('#num_empleado').val(),
                    pagina: pagina
                };

                $.ajax({
                    url: 'filter_reports.php',
                    type: 'POST',
                    data: filtros,
                    dataType: 'json',
                    success: function(response) {
                        $('#tabla-registros').html(response.tabla);
                        $('#pagination').html(response.paginacion);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('Ocurrió un error al cargar los datos.');
                    }
                });
            }

            $('#btn-filtrar').on('click', function() {
                cargarDatos(1);
            });

            $('#pagination').on('click', '.page-link', function(e) {
                e.preventDefault();
                const pagina = $(this).data('pagina');
                cargarDatos(pagina);
            });

            $('#btn-exportar').on('click', function() {
                window.location.href = 'exportar_excel.php?' + $('#filtro-form').serialize();
            });

            $('#btn-enviar-correo').on('click', function() {
                const correos = $('#correos').val();
                if (!correos) {
                    alert('Por favor, ingresa al menos un correo.');
                    return;
                }
                const filtros = $('#filtro-form').serialize();
                $.post('enviar_correo.php', {
                    correos,
                    filtros
                }, function(response) {
                    alert(response.mensaje || 'Correo enviado exitosamente.');
                    $('#modalEnviarCorreo').modal('hide');
                }).fail(function() {
                    alert('Error al enviar correo.');
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

    
    <?php include 'footer.php'; ?>
</body>

</html>
