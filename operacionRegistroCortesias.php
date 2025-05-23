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
// Obtener las empresas para el select
$sqlEmpresas = "SELECT id, nombre FROM empresa WHERE estatus_id = 1";
$stmtEmpresas = $conn->prepare($sqlEmpresas);
$stmtEmpresas->execute();
$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

// Obtener las empresas para el select
$sqlDepartamentos = "SELECT id, nombre FROM departamento WHERE estatus_id = 1";
$stmtDepartamentos = $conn->prepare($sqlDepartamentos);
$stmtDepartamentos->execute();
$departamentos = $stmtDepartamentos->fetchAll(PDO::FETCH_ASSOC);


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
                <h3 class="h2">CORTESÍAS</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'operacionRegistroAlimentos.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alerta para mensajes de error -->
            <div id="alertaError" class="alert alert-danger d-none" role="alert"></div>


        <div class="card mb-4">
                <div class="card-header bg-dark text-white">Escanear Código de Barras</div>
                <div class="card-body">
                    <form id="barcode-form">
                        <div class="form-group">
                            <label for="codigo_barras">Código de Barras</label>
                            <input type="text" id="barcodeInput" placeholder="Escanea el código de barras" class="form-control" autofocus>
                        </div>
                        <!--<button type="submit" class="btn btn-primary">Registrar</button>-->
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-header bg-dark text-white">Registros de Cortesias</div>
                <div class="card-body">
                    <table class="table table-striped" id="registros-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Codigo</th>
                                <th>Empresa</th>
                                <th>Area</th>
                                <th>Solicitante</th>
                                <th>Tipo de Comida</th>
                                <th>Cantidad</th>
                                <th>Fecha y Hora</th>
                                <th>Estatus</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>

                            <!-- Registros dinámicos se insertarán aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal para selección de datos -->
            <div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="registroModalLabel">Confirmar Registro</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form action="register_cortesias.php" method="POST" enctype="multipart/form-data">
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
                                        <?php foreach ($departamentos as $departamento) : ?>
                                            <option value="<?= $departamento['id'] ?>"><?= $departamento['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="solicitante" class="form-label">Solicitante</label>
                                    <input type="text" id="solicitante" name="solicitante" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                                </div>
                                <input type="hidden" id="codigoBarras">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmarRegistro">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal para cambiar el estatus a "BAJA" -->

            <div class="modal fade" id="deleteComensalModal" tabindex="-1" aria-labelledby="deleteComensalModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteComensalModalLabel">Confirmación de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas cancelar el servicio?
                        </div>
                        <div class="modal-footer">
                            <form action="delete_control_food.php" method="POST">
                                <input type="hidden" name="id" id="id">
                                <button type="submit" class="btn btn-danger">Sí, confirmar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Sticky Footer -->
            <?php //include 'footer.php'; 
            ?>

            <!-- Scripts -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                $(document).ready(function() {
                    let barcodeTimeout;
                    cargarRegistros();

                    // Evento cuando el usuario escanea el código de barras
                    $('#barcodeInput').on('input', function() {
                        clearTimeout(barcodeTimeout); // Limpiar timeout anterior
                        const barcode = $(this).val().trim();

                        if (barcode.length === 12) { // Ajusta la longitud si es diferente
                            console.log("Código detectado, esperando 1 segundo antes de procesar...");

                            $('#barcodeInput').prop('disabled', true); // Bloquear input temporalmente

                            barcodeTimeout = setTimeout(() => {
                                console.log("Procesando código:", barcode);

                                // Guardamos el código en el input oculto
                                $('#codigoBarras').val(barcode);

                                // Mostrar el modal de registro
                                new bootstrap.Modal(document.getElementById("registroModal")).show();

                                // Reactivar el input y limpiar su valor
                                $('#barcodeInput').prop('disabled', false).val('');

                            }, 1000);
                        }
                    });

                    // Evento cuando se hace clic en el botón "Confirmar"
                    $("#confirmarRegistro").click(function(e) {
                        e.preventDefault(); // Evita que el formulario se envíe normalmente

                        // Obtener los datos del formulario
                        var formData = {
                            barcode: $("#codigoBarras").val(),
                            empresa_id: $("#empresa").val(),
                            departamento_id: $("#departamento").val(),
                            solicitante: $("#solicitante").val(),
                            cantidad: $("#cantidad").val()
                        };

                        console.log("Enviando datos:", formData);

                        // Validar que los campos obligatorios no estén vacíos
                        if (!formData.empresa_id || !formData.departamento_id || !formData.solicitante || !formData.cantidad) {
                            alert("Por favor, complete todos los campos.");
                            return;
                        }

                        // Enviar datos con AJAX
                        $.ajax({
                            type: "POST",
                            url: "register_cortesias.php",
                            data: formData,
                            dataType: "json",
                            success: function(response) {
                                console.log("Respuesta del servidor:", response);

                                if (response.success) {
                                    alert(response.message); // Mostrar mensaje de éxito
                                    $("#registroModal").modal("hide"); // Cerrar modal
                                    $("#barcodeInput").val(""); // Limpiar input del código de barras
                                    cargarRegistros(); // Recargar tabla de registros
                                } else {
                                    alert(response.message); // Mostrar mensaje de error
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error en la petición:", xhr.responseText);
                                alert("Ocurrió un error al registrar la cortesía.");
                            }
                        });
                    });

                    // Función para cargar registros en la tabla
                    function cargarRegistros() {
                        $.ajax({
                            url: "register_cortesias.php",
                            type: "POST",
                            data: {
                                action: "get_registros_dia"
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.success) {
                                    var tbody = $("#registros-table tbody");
                                    tbody.empty();
                                    response.data.registros_dia.forEach(function(registro, index) {
                                        let estatusHTML = `<span class="badge badge-secondary">${registro.estatus}</span>`;
                                        if (registro.estatus === 'ACTIVO') estatusHTML = '<span class="badge bg-success">ACTIVO</span>';
                                        if (registro.estatus === 'CANCELADO') estatusHTML = '<span class="badge bg-danger">CANCELADO</span>';
                                        tbody.append(`
                            <tr>
                                <td>${registro.id}</td>
                                <td>${registro.barcode}</td>
                                <td>${registro.nombre_empresa}</td>
                                <td>${registro.nombre_departamento}</td>
                                <td>${registro.solicitante}</td>
                                <td>${registro.tipo_producto}</td>
                                <td>${registro.cantidad}</td>
                                <td>${registro.fecha_registro}</td>
                                <td>${estatusHTML}</td>
                                <td><button class="btn btn-danger btn-sm">Eliminar</button></td>
                            </tr>
                        `);
                                    });
                                }
                            }
                        });
                    }
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