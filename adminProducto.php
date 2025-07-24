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

// Consulta base para obtener los empleados y sus áreas
$sql = "SELECT pro.id, pro.producto, pro.barcode, pro.costo, pro.barcode_path, cs.status
        FROM producto pro
          INNER JOIN catalog_status cs ON cs.status_id = pro.estatus
        ORDER BY pro.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h3 class="h3">SERVICIOS</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createProductModal" onclick="location.href = '#';"><i class="bi bi-person-plus"></i> Agregar Producto</button>
                    </div>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'adminProducto.php';">
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
                            <th>Servicio</th>
                            <th>Código</th>
                            <th>Código del producto</th>
                            <th>Estatus</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productos)) : ?>
                            <?php foreach ($productos as $producto) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($producto['id']) ?></td>
                                    <td><?= htmlspecialchars($producto['producto']) ?></td>
                                    <td><?= htmlspecialchars($producto['barcode']) ?></td>
                                    <td class="text-center align-middle">
                                        <!-- Icono de imagen que abre el modal al hacer clic -->
                                        <a href="#" data-toggle="modal" data-target="#barcodeModal"
                                            data-barcode="<?= htmlspecialchars($producto['barcode_path']) ?>">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($producto['status'] === 'ACTIVO'): ?>
                                            <span class="badge bg-success">ACTIVO</span>
                                        <?php elseif ($producto['status'] === 'BAJA'): ?>
                                            <span class="badge bg-danger">BAJA</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($producto['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <!-- Icono de "editar" que abre el modal de confirmación -->
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($producto['id']) ?>"
                                            data-producto="<?= htmlspecialchars($producto['producto']) ?>"
                                            data-costo="<?= htmlspecialchars($producto['costo']) ?>"
                                            data-toggle="modal"
                                            data-target="#editProductModal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        &nbsp;
                                        &nbsp;
                                        <!-- Icono de "eliminar" que abre el modal de confirmación -->
                                        <a href="#"
                                            data-id="<?= htmlspecialchars($producto['id']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteProductModal"
                                            onclick="setUserId(<?= htmlspecialchars($producto['id']) ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">No se encontraron productos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal para crear producto -->
            <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createProductModalLabel">Crear Producto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_product.php" method="POST" enctype="multipart/form-data">
                                <!-- Información General -->
                                <div class="card">
                                    <h5 class="card-header">Información General</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="producto" class="control-label">Producto *</label>
                                                    <input type="text" id="producto" name="producto" class="form-control" placeholder="Nombre del producto" required style="text-transform:uppercase;">
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

            <!-- Modal para mostrar la imagen del código de barras y la información -->
            <div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="barcodeModalLabel">Código de Barras</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <!-- Información del producto -->
                            <p id="productInfo"></p>
                            <!-- Imagen del código de barras -->
                            <img id="barcodeImage" src="" alt="Código de Barras" class="img-fluid">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="printBarcode()">Imprimir</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal para editar producto -->
            <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProductModalLabel">Editar Producto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editProductForm" action="update_product.php" method="POST">
                                <input type="hidden" name="id" id="editProductId">
                                <div class="card">
                                    <h5 class="card-header">Información General</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editProducto" class="control-label">Producto *</label>
                                                    <input type="text" id="editProducto" name="producto" class="form-control" placeholder="Nombre del producto" required style="text-transform:uppercase;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary mx-2">Guardar Cambios</button>
                                    <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal de confirmación para dar de baja el producto -->
            <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteProductModalLabel">Confirmación de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas dar de baja el producto?
                        </div>
                        <div class="modal-footer">
                            <form action="delete_product.php" method="POST">
                                <input type="hidden" name="id" id="id">
                                <button type="submit" class="btn btn-danger">Sí, dar de baja</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    // Captura el evento de apertura del modal para cambiar la imagen según el producto
    $('#barcodeModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Elemento que activó el modal
        var barcodePath = button.data('barcode'); // Obtiene la ruta de la imagen del atributo data-barcode
        var modal = $(this);
        modal.find('#barcodeImage').attr('src', barcodePath); // Cambia la ruta de la imagen en el modal
    });


    // Función para pasar el ID del usuario al campo oculto del formulario en el modal de eliminación
    function setUserId(id) {
        document.getElementById('id').value = id;
    }


    // Función para cargar la imagen y detalles del producto en el modal
    function openBarcodeModal(barcodeSrc, productInfo) {
        // Configurar la fuente de la imagen del código de barras y los detalles del producto
        document.getElementById('barcodeImage').src = barcodeSrc;
        document.getElementById('productInfo').innerText = productInfo;

        // Mostrar el modal
        $('#barcodeModal').modal('show');
    }

    // Función para imprimir solo el contenido del código de barras y la información
    function printBarcode() {
        const printContent = `
            <div style="text-align: center;">
                <h3>${document.getElementById('productInfo').innerText}</h3>
                ${document.getElementById('barcodeImage').outerHTML}
            </div>
        `;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Imprimir Código de Barras</title>');
        printWindow.document.write('<style>body { text-align: center; font-family: Arial, sans-serif; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.print();
    }


    // Capturar el evento de apertura del modal de edición
    $('#editProductModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Elemento que activó el modal
        var id = button.data('id'); // ID del producto
        var producto = button.data('producto'); // Nombre del producto

        // Asignar valores a los campos del formulario en el modal
        $('#editProductId').val(id);
        $('#editProducto').val(producto);
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