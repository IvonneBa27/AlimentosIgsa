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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.1.0/qz-tray.js"></script>






</head>

<div>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">



            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h3 class="h3">REGISTRO DE COMENSAL</h3>
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

            <!-- Spinner de carga -->
            <div id="spinner" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">Escanear Código de Barras</div>
                <div class="card-body">
                    <form id="barcode-form">
                        <div class="form-group">
                            <label for="codigo_barras">Código de Barras</label>
                            <input type="text" id="barcodeInput" placeholder="Escanea el código de barras" class="form-control" autocomplete="off" autofocus>
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
                                <th>Número de Empleado</th>
                                <th>Nombre del Comensal</th>
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
                                <input type="hidden" name="id" id="delete-comensal-id">
                                <button type="submit" class="btn btn-danger">Sí, confirmar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para mostrar información -->
            <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="infoModalLabel">Información del Registro</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Contenido dinámico -->
                            <p><strong>Número de empleado:</strong> <span id="numEmpleado"></span></p>
                            <p><strong>Nombre:</strong> <span id="nombreCompleto"></span></p>
                            <p><strong>Empresa:</strong> <span id="empresa"></span></p>
                            <p><strong>Descripción:</strong> <span id="tipoProducto"></span></p>
                            <p><strong>Cantidad:</strong> <span id="cantidad"></span></p>
                            <p><strong>Mensaje:</strong> <span id="mensaje"></span></p>
                            <img id="imagePath" class="d-none" alt="Imagen de perfil" style="max-width: 100%; height: auto;">
                            <p><strong>Fecha Registro:</strong> <span id="fechaRegistro"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                //Cancelacion de producto.
                $(document).on('click', '.btn-cancelar-servicio', function() {
                    var id = $(this).data('id');
                    $('#delete-comensal-id').val(id);
                });


                $(document).ready(function() {
                    function cargarRegistros() {
                        $('#spinner').show();
                        $.post('register_food.php', {
                            action: 'get_registros_dia'
                        }, function(response) {
                            $('#spinner').hide();
                            const res = JSON.parse(response);
                            if (res.success) {
                                const tbody = $('#registros-table tbody');
                                tbody.empty();
                                res.data.registros_dia.forEach((registro) => {
                                    let estatusHTML = `<span class="badge bg-secondary">${registro.estatus}</span>`;
                                    if (registro.estatus === 'ACTIVO') estatusHTML = '<span class="badge bg-success">ACTIVO</span>';
                                    if (registro.estatus === 'CANCELADO') estatusHTML = '<span class="badge bg-danger">CANCELADO</span>';

                                    tbody.append(`
                                    <tr>
                                        <td>${registro.id}</td>
                                        <td>${registro.num_empleado}</td>
                                        <td>${registro.nombre_completo}</td>
                                        <td>${registro.tipo_producto}</td>
                                        <td>${registro.cantidad}</td>
                                        <td>${registro.fecha_registro}</td>
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
                            } else {
                                mostrarError(res.message);
                            }
                        });
                    }

                    function mostrarError(mensaje) {
                        const alertaError = $('#alertaError');
                        alertaError.text(mensaje).removeClass('d-none');
                        setTimeout(() => alertaError.addClass('d-none'), 5000);
                    }

                    function procesarCodigoDeBarras(barcode) {
                        if (!barcode.trim() || barcode.length !== 12) {
                            mostrarError('Código de barras inválido.');
                            $('#barcodeInput').val(''); // Limpiar input
                            return;
                        }

                        $('#spinner').show();
                        fetch("register_food.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: new URLSearchParams({
                                    barcode
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                $('#spinner').hide();
                                $('#barcodeInput').val(''); // Limpiar input

                                if (data.success) {
                                    mostrarModalConDatos(data.data);

                                    // Retrasar la actualización de la tabla para que el usuario pueda ver el mensaje
                                    setTimeout(() => {
                                        cargarRegistros();
                                    }, 3000); // Espera 3 segundos antes de actualizar los registros
                                } else {
                                    mostrarError(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                mostrarError('Ocurrió un error al procesar la solicitud.');
                                $('#spinner').hide();
                                $('#barcodeInput').val(''); // Limpiar input
                                $('#barcodeInput').val(''); // Limpiar input
                            });
                    }

                    function mostrarModalConDatos(data) {
                        $('#numEmpleado').text(data.num_empleado || "N/A");
                        $('#nombreCompleto').text(data.nombre_completo || "N/A");
                        $('#empresa').text(data.empresa || "N/A");
                        $('#tipoProducto').text(data.tipo_producto || "N/A");
                        $('#cantidad').text(data.cantidad || "N/A");
                        $('#fechaRegistro').text(data.fecha_registro || "N/A");

                        if (data.imagePath) {
                            $('#imagePath').attr('src', data.imagePath).removeClass('d-none');
                        } else {
                            $('#imagePath').addClass('d-none');
                        }

                        const infoModal = new bootstrap.Modal(document.getElementById("infoModal"));
                        infoModal.show();

                        setTimeout(() => {
                            infoModal.hide();
                            imprimirTicket(data);

                            // Después de imprimir, restablecer el foco en el input
                            setTimeout(() => {
                                $('#barcodeInput').focus();
                            }, 100); // Ajusta el tiempo si es necesario
                        }, 2000);
                    }

                    function imprimirTicket(data) {//HKA80
                        const ticketContent = `
<<<<<<< HEAD
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Ticket</title>
                                    <style>
                                        html, body {
                                            margin: 0 !important;
                                            padding: 0 !important;
                                            font-family: Arial, sans-serif;
                                            font-size: 13px;
                                            width: 80mm;
                                            text-align: center;
                                            line-height: 1.2;
                                        }

                                        @media print {
                                            @page {
                                                size: 80mm auto;
                                                margin: 0;
                                            }
                                            html, body {
                                                margin: 0 !important;
                                                padding: 0 !important;
                                            }
                                        }

                                        .ticket {
                                            margin: 0;
                                            padding: 0;
                                        }

                                        p {
                                            margin: 0;
                                            padding: 2px 0;
                                            font-size: 13px;
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class="ticket">
                                        <p><strong>Número de empleado:</strong> ${data.num_empleado}</p>
                                        <p><strong>Nombre:</strong> ${data.nombre_completo}</p>
                                        <p><strong>Empresa:</strong> ${data.empresa}</p>
                                        <p><strong>Descripción:</strong> ${data.tipo_producto}</p>
                                        <p><strong>Cantidad:</strong> ${data.cantidad}</p>
                                        <p><strong>Fecha de registro:</strong> ${data.fecha_registro}</p>
                                    </div>
                                </body>
                                </html>
                            `;
=======
                                              <!DOCTYPE html>
                                              <html>
                                              <head>
                                                  <title>Ticket</title>
                                                  <style>
                                                      html, body {
                                                          margin: 0 !important;
                                                          padding: 0 !important;
                                                          font-family: Arial, sans-serif;
                                                          font-size: 13px;
                                                          width: 80mm;
                                                          text-align: center;
                                                          line-height: 1.2;
                                                      }

                                                      @media print {
                                                          @page {
                                                              size: 80mm auto;
                                                              margin: 0;
                                                          }
                                                          html, body {
                                                              margin: 0 !important;
                                                              padding: 0 !important;
                                                          }
                                                      }

                                                      .ticket {
                                                          margin: 0;
                                                          padding: 0;
                                                      }

                                                      p {
                                                          margin: 0;
                                                          padding: 2px 0;
                                                          font-size: 13px;
                                                      }
                                                  </style>
                                              </head>
                                              <body>
                                                  <div class="ticket">
                                                      <p><strong>Número de empleado:</strong> ${data.num_empleado}</p>
                                                      <p><strong>Nombre:</strong> ${data.nombre_completo}</p>
                                                      <p><strong>Empresa:</strong> ${data.empresa}</p>
                                                      <p><strong>Descripción:</strong> ${data.tipo_producto}</p>
                                                      <p><strong>Cantidad:</strong> ${data.cantidad}</p>
                                                      <p><strong>Fecha de registro:</strong> ${data.fecha_registro}</p>
                                                  </div>
                                              </body>
                                              </html>
                                          `;
>>>>>>> dev_IvonneAlimentos_120525

                        const printWindow = window.open('', 'PRINT', 'width=400,height=600');
                        printWindow.document.write(ticketContent);
                        printWindow.document.close();

                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.focus();
                                printWindow.print();
                                printWindow.close();
                            }, 500);
                        };
                    }

                    /*function imprimirTicket(data) {
                        const ticketHTML = `
                            Número de empleado: ${data.num_empleado}\n
                            Nombre: ${data.nombre_completo}\n
                            Empresa: ${data.empresa}\n
                            Descripción: ${data.tipo_producto}\n
                            Cantidad: ${data.cantidad}\n
                            Fecha de registro: ${data.fecha_registro}\n
                            -------------------------------
                        `;

                        // Esperar a que QZ esté listo
                        qz.websocket.connect().then(() => {
                            return qz.printers.find(); // Usa la impresora predeterminada
                        }).then(printer => {
                            const config = qz.configs.create(printer); // Configuración de impresión
                            const dataToPrint = [{
                                type: 'raw',
                                format: 'plain',
                                data: ticketHTML
                            }];
                            return qz.print(config, dataToPrint);
                        }).then(() => {
                            console.log("Impresión enviada correctamente");
                            qz.websocket.disconnect();
                        }).catch(err => {
                            console.error("Error al imprimir:", err);
                            qz.websocket.disconnect();
                        });
                    }*/





                    let barcodeTimeout;

                    $('#barcodeInput').on('input', function() {
                        clearTimeout(barcodeTimeout); // Limpiar timeout anterior
                        const barcode = $(this).val().trim();

                        if (barcode.length === 12) {
                            console.log("Código detectado, esperando 2 segundos antes de procesar...");

                            // Deshabilitar input temporalmente para evitar múltiples envíos rápidos
                            $('#barcodeInput').prop('disabled', true);

                            barcodeTimeout = setTimeout(() => {
                                console.log("Procesando código:", barcode);
                                procesarCodigoDeBarras(barcode);

                                // Volver a habilitar el input después de procesar
                                $('#barcodeInput').prop('disabled', false);
                                $('#barcodeInput').val(''); // Limpiar input después de procesar
                            }, 1000); // Esperar 2 segundos antes de procesar
                        }
                    });


                    // Cargar registros al inicio
                    cargarRegistros();
                });
            </script>











        </main>

    </div>
<<<<<<< HEAD
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.1.0/qz-tray.js"></script>
=======

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
>>>>>>> dev_IvonneAlimentos_120525
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>
    </body>

</html>