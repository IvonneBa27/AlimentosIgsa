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

include 'db_connection.php';

// Consulta base
$sql = "SELECT pc.id, pc.consumo, pc.costo, pc.barcode, pc.barcode_path, cs.status, es.nombre as tienda
        FROM punto_consumo pc
        INNER JOIN catalog_status cs ON cs.status_id = pc.estatus
        INNER JOIN establecimientos es ON es.id = pc.establecimiento_id
        ORDER BY pc.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$consumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlEstablecimientos = "SELECT id, nombre FROM establecimientos WHERE estatus_id = 1";
$stmtEstablecimientos = $conn->prepare($sqlEstablecimientos);
$stmtEstablecimientos->execute();
$establecimientos = $stmtEstablecimientos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>A D M I N I S T R A C I Ó N &nbsp; C O M E D O R</title>

  <!-- Bootstrap 5 (CDN para descartar problemas de rutas) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="node_modules/sweetalert2/dist/sweetalert2.min.css">
  <script src="js/color-modes.js"></script>
  <style>
    /* Por si algún contenedor crea conflictos de stacking, refuerzo z-index del modal */
    .modal { z-index: 1065; }
    .modal-backdrop { z-index: 1060; }
  </style>
</head>
<body class="bg-light">

<div class="d-flex">
  <?php include 'sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <main class="main-content container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
      <h3 class="h3">CONSUMO</h3>
      <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
          <button type="button" class="btn btn-sm btn-outline-secondary"
                  data-bs-toggle="modal" data-bs-target="#createConsumoModal">
            <i class="bi bi-person-plus"></i> Agregar Consumo
          </button>
        </div>
        <div class="btn-group me-2">
          <button type="button" class="btn btn-sm btn-outline-secondary"
                  onclick="location.href='adminPuntoConsumo.php';" title="Recargar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                 fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
              <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="table-responsive" style="max-height:830px; overflow-y:auto;">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Id</th>
            <th>Consumo</th>
            <th>Costo</th>
            <th>Código</th>
            <th>Código del consumo</th>
            <th>Establecimiento</th>
            <th>Estatus</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($consumos)) : ?>
            <?php foreach ($consumos as $consumo) : ?>
              <tr>
                <td><?= htmlspecialchars($consumo['id']) ?></td>
                <td><?= htmlspecialchars($consumo['consumo']) ?></td>
                <td><?= htmlspecialchars($consumo['costo']) ?></td>
                <td><?= htmlspecialchars($consumo['barcode']) ?></td>
                <td class="text-center">
                  <a href="javascript:void(0)" role="button"
                     data-bs-toggle="modal" data-bs-target="#barcodeModal"
                     data-barcode="<?= htmlspecialchars($consumo['barcode_path']) ?>"
                     data-producto="<?= htmlspecialchars($consumo['consumo']) ?>">
                    <i class="fas fa-barcode"></i>
                  </a>
                </td>
                <td><?= htmlspecialchars($consumo['tienda']) ?></td>
                <td>
                  <?php if ($consumo['status'] === 'ACTIVO'): ?>
                    <span class="badge bg-success">ACTIVO</span>
                  <?php elseif ($consumo['status'] === 'BAJA'): ?>
                    <span class="badge bg-danger">BAJA</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($consumo['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="javascript:void(0)" role="button"
                     data-bs-toggle="modal" data-bs-target="#editConsumoModal"
                     data-id="<?= htmlspecialchars($consumo['id']) ?>"
                     data-producto="<?= htmlspecialchars($consumo['consumo']) ?>">
                    <i class="fas fa-edit"></i>
                  </a>
                  &nbsp;&nbsp;
                  <a href="javascript:void(0)" role="button"
                     data-bs-toggle="modal" data-bs-target="#deleteConsumoModal"
                     data-id="<?= htmlspecialchars($consumo['id']) ?>">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="8">No se encontraron los productos.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- ===================== MODALES (como hijos directos de BODY) ===================== -->

<!-- Modal: Crear Consumo -->
<div class="modal fade" id="createConsumoModal" tabindex="-1" aria-labelledby="createConsumoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createConsumoModalLabel">Crear Consumo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form action="insert_pconsumo.php" method="POST" enctype="multipart/form-data">
          <div class="card">
            <h5 class="card-header">Información General</h5>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="consumo" class="form-label">Consumo *</label>
                  <input type="text" id="consumo" name="consumo" class="form-control" placeholder="Nombre del producto" required style="text-transform:uppercase;">
                </div>
                <div class="col-md-6">
                  <label for="costo" class="form-label">Costo *</label>
                  <input type="number" id="costo" name="costo" class="form-control" placeholder="Costo del producto" required>
                </div>
                <div class="col-8">
                  <label for="establecimiento_id" class="form-label">Establecimiento *</label>
                  <select id="establecimiento_id" name="establecimiento_id" class="form-select" required>
                    <option value="">Seleccione un establecimiento</option>
                    <?php foreach ($establecimientos as $establecimiento) : ?>
                      <option value="<?= $establecimiento['id'] ?>"><?= $establecimiento['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary mx-2">Guardar</button>
            <button type="button" class="btn btn-warning mx-2" data-bs-dismiss="modal">Cancelar</button>
          </div>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <p id="productInfo" class="mb-2"></p>
        <img id="barcodeImage" src="" alt="Código de Barras" class="img-fluid">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnPrintBarcode">Imprimir</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar Consumo -->
<div class="modal fade" id="editConsumoModal" tabindex="-1" aria-labelledby="editConsumoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editConsumoModalLabel">Editar Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="editProductForm" action="update_product.php" method="POST">
          <input type="hidden" name="id" id="editConsumoId">
          <div class="card">
            <h5 class="card-header">Información General</h5>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="editConsumo" class="form-label">Producto *</label>
                  <input type="text" id="editConsumo" name="producto" class="form-control" placeholder="Nombre del producto" required style="text-transform:uppercase;">
                </div>
              </div>
            </div>
          </div>
          <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary mx-2">Guardar Cambios</button>
            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Baja Consumo -->
<div class="modal fade" id="deleteConsumoModal" tabindex="-1" aria-labelledby="deleteConsumoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConsumoModalLabel">Confirmación de Baja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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

<!-- ===================== SCRIPTS ===================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/sidebars.js"></script>
<script src="js/seguridad.js"></script>
<script>
  // Delegación: preparar datos al abrir modales
  document.getElementById('barcodeModal').addEventListener('show.bs.modal', function (ev) {
    const trigger = ev.relatedTarget;
    if (!trigger) return;
    this.querySelector('#barcodeImage').src = trigger.getAttribute('data-barcode') || '';
    this.querySelector('#productInfo').innerText = trigger.getAttribute('data-producto') || '';
  });

  document.getElementById('editConsumoModal').addEventListener('show.bs.modal', function (ev) {
    const trigger = ev.relatedTarget;
    if (!trigger) return;
    document.getElementById('editConsumoId').value = trigger.getAttribute('data-id') || '';
    document.getElementById('editConsumo').value = trigger.getAttribute('data-producto') || '';
  });

  document.getElementById('deleteConsumoModal').addEventListener('show.bs.modal', function (ev) {
    const trigger = ev.relatedTarget;
    if (!trigger) return;
    document.getElementById('id').value = trigger.getAttribute('data-id') || '';
  });

  // Imprimir código de barras
  document.getElementById('btnPrintBarcode').addEventListener('click', function () {
    const info = document.getElementById('productInfo').innerText;
    const img = document.getElementById('barcodeImage').outerHTML;
    const win = window.open('', '', 'height=600,width=800');
    win.document.write('<html><head><title>Imprimir Código de Barras</title>');
    win.document.write('<style>body{font-family:Arial,sans-serif;text-align:center}</style>');
    win.document.write('</head><body>');
    win.document.write('<h3>' + (info || '') + '</h3>' + img);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
  });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
