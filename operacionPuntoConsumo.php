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
$sqlTipoPagos = "SELECT id, nombre FROM tipo_pago WHERE estatus = 1";
$stmtTipoPagos = $conn->prepare($sqlTipoPagos);
$stmtTipoPagos->execute();
$tipoPagos = $stmtTipoPagos->fetchAll(PDO::FETCH_ASSOC);





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
                <h3 class="h2">PUNTO DE VENTA</h3>
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
                <div class="card-header bg-dark text-white">Punto de Venta</div>
                <div class="card-body">
                    <table class="table table-striped" id="registros-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th>Costo</th>
                                <th>Cantidad</th>
                                <th>Cantidad</th>
                                <th>Forma de pago</th>
                                <th>Folio</th>
                                <th>Fecha y Hora</th>
                                 <th>Sitio</th>
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
                            <form action="register_punto.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_pago" class="form-label">Forma de Pago *</label>
                                    <select id="tipo_pago" name="tipo_pago" class="form-select" required>
                                        <option value="">Seleccione un tipo de pago</option>
                                        <?php foreach ($tipoPagos as $tipoPago) : ?>
                                            <option value="<?= $tipoPago['id'] ?>"><?= $tipoPago['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
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
            <!-- Modal para editar el folio -->
            <div class="modal fade" id="modal-editar-folio" tabindex="-1" aria-labelledby="modalEditarFolioLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="form-editar-folio">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEditarFolioLabel">Folio</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="editar-folio-id">

                                <div class="mb-3">
                                    <label for="editar-folio-folio" class="form-label">Folio</label>
                                    <input type="text" class="form-control" id="folio" name="folio" id="editar-folio-folio" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary" id="guardar-folio">Guardar cambios</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Modal para cambiar el estatus a "BAJA" -->

            <!-- Modal para cambiar el estatus a "BAJA" -->
            <div class="modal fade" id="deleteComensalModal" tabindex="-1" aria-labelledby="deleteComensalModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteComensalModalLabel">Cancelación del servicio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas cancelar el servicio?
                        </div>
                        <div class="modal-footer">
                            <form action="delete_control_puntoventa.php" method="POST">
                                <input type="hidden" name="id" id="delete-comensal-id">
                                <button type="submit" class="btn btn-danger">Sí, confirmar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, volver</button>
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

                    // Cargar registros al iniciar
                    cargarRegistros();

                    // Evento: escaneo de código de barras
                    $('#barcodeInput').on('input', function() {
                        clearTimeout(barcodeTimeout);
                        const barcode = $(this).val().trim();

                        if (barcode.length === 12) {
                            console.log("Código detectado, esperando 1 segundo...");

                            $('#barcodeInput').prop('disabled', true);

                            barcodeTimeout = setTimeout(() => {
                                console.log("Procesando código:", barcode);
                                $('#codigoBarras').val(barcode);
                                new bootstrap.Modal(document.getElementById("registroModal")).show();
                                $('#barcodeInput').prop('disabled', false).val('');
                            }, 1000);
                        }
                    });

                    // Evento: confirmar registro
                    $("#confirmarRegistro").click(function(e) {
                        e.preventDefault();

                        const formData = {
                            barcode: $("#codigoBarras").val(),
                            tipo_pago: $("#tipo_pago").val(),
                            cantidad: $("#cantidad").val()
                        };

                        if (!formData.tipo_pago || !formData.cantidad) {
                            alert("Por favor, complete todos los campos.");
                            return;
                        }

                        $.ajax({
                            type: "POST",
                            url: "register_punto.php",
                            data: formData,
                            dataType: "json",
                            success: function(response) {
                                console.log("Respuesta del servidor:", response);
                                if (response.success) {
                                    alert(response.message);
                                    $("#registroModal").modal("hide");
                                    $("#barcodeInput").val('');
                                    cargarRegistros();
                                } else {
                                    alert(response.message);
                                }
                            },
                            error: function(xhr) {
                                console.error("Error:", xhr.responseText);
                                alert("Ocurrió un error al registrar la venta.");
                            }
                        });
                    });

                    // Evento: guardar folio desde el modal
                    $("#guardar-folio").on("click", function(e) {
                        e.preventDefault();

                        const id = $("#modal-editar-folio input[name='id']").val();
                        const folio = $("#modal-editar-folio input[name='folio']").val().trim();

                        if (folio === "") {
                            alert("El folio no puede estar vacío.");
                            return;
                        }

                        console.log("Actualizando folio:", {
                            id,
                            folio
                        });

                        $.ajax({
                            url: "register_punto.php",
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: "actualizar_folio",
                                id: id,
                                folio: folio
                            },
                            success: function(response) {
                                console.log("Actualizando folio:", {
                                    id,
                                    folio
                                });

                                console.log("Respuesta de actualizar_folio:", response);
                                if (response.success) {
                                    alert(response.message);
                                    $("#modal-editar-folio").modal("hide");
                                    cargarRegistros();
                                } else {
                                    alert("Error: " + response.message);
                                }
                            },
                            error: function(xhr) {
                                console.error("Error en AJAX:", xhr.responseText);
                                alert("Hubo un error al intentar actualizar el folio.");
                            }
                        });
                    });

                    //Cancelacion de producto.
                    $(document).on('click', '.btn-cancelar-servicio', function() {
                        var id = $(this).data('id');
                        $('#delete-comensal-id').val(id);
                    });

                    // Función: cargar registros en la tabla
                    function cargarRegistros() {
                        $.ajax({
                            url: "register_punto.php",
                            type: "POST",
                            data: {
                                action: "get_registros_dia"
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.success) {
                                    const tbody = $("#registros-table tbody");
                                    tbody.empty();

                                    response.data.registros_dia.forEach(function(registro) {
                                        // Etiqueta de estatus
                                        let estatusHTML = `<span class="badge bg-secondary">${registro.estatus}</span>`;
                                        if (registro.estatus === 'ACTIVO') estatusHTML = '<span class="badge bg-success">ACTIVO</span>';
                                        if (registro.estatus === 'CANCELADO') estatusHTML = '<span class="badge bg-danger">CANCELADO</span>';

                                        // Tipo de pago con ícono si aplica
                                        let tipoPagoHTML = registro.nombre_tipo_pago;
                                        const esTarjeta = ['TARJETA DE CRÉDITO', 'TARJETA DE DÉBITO'].includes(registro.nombre_tipo_pago);
                                        if (esTarjeta) {
                                            tipoPagoHTML += ` <i class="bi bi-pencil-square text-primary editar-folio" style="cursor:pointer;" 
                                data-id="${registro.id}" data-folio="${registro.folio ?? ''}"></i>`;
                                        }

                                        // Folio (solo si no es null)
                                        let folioHTML = registro.folio !== null ? registro.folio : '';

                                        // Agregar fila
                                        tbody.append(`
                            <tr>
                                <td>${registro.id}</td>
                                <td>${registro.barcode}</td>
                                <td>${registro.nombre_consumo}</td>
                                <td>${registro.costo}</td>
                                <td>${registro.cantidad}</td>
                                <td>${registro.total}</td>
                                <td>${tipoPagoHTML}</td>
                                <td>${folioHTML}</td>
                                <td>${registro.fecha_registro}</td>
                                <td>${registro.sitio}</td>
                                <td>${estatusHTML}</td>
                               <td>
                                    <button 
                                        class="btn btn-dark btn-sm btn-cancelar-servicio" 
                                        data-id="${registro.id}" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteComensalModal">
                                        Cancelar
                                    </button>
                                </td>

                            </tr>
                        `);
                                    });

                                    // Evento para abrir el modal al hacer clic en ícono de editar folio
                                    $(".editar-folio").on("click", function() {
                                        const id = $(this).data("id");
                                        const folio = $(this).data("folio");

                                        $("#modal-editar-folio input[name='id']").val(id);
                                        $("#modal-editar-folio input[name='folio']").val(folio);
                                        $("#modal-editar-folio").modal("show");
                                    });
                                } else {
                                    console.warn("No se pudieron cargar los registros:", response.message);
                                }
                            },
                            error: function(xhr) {
                                console.error("Error al cargar registros:", xhr.responseText);
                            }
                        });
                    }
                });
            </script>








        </main>

    </div>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>
    </body>

</html>