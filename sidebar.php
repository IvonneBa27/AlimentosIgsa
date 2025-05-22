<?php
// sidebar_dashboard.php

include("conexion.php");
date_default_timezone_set("America/Mexico_City");

// 1) Sesión
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['resultado'])) {
  header('Location: index.html');
  exit;
}
$sesi          = $_SESSION['resultado'];
$sesionUsuario = $sesi['usuario'];
$sesionNombre  = $sesi['nombre_completo'];
$sesionRol     = $sesi['rol_id'];
$sesionSitio     = $sesi['sitio_id'];

// 2) Traer módulos permitidos
$modulos_permitidos = [];
$sql = "
  SELECT m.id, m.modulo, m.ruta
    FROM permisos p
    INNER JOIN modulos m ON p.modulo_id = m.id
   WHERE p.rol_id = ?
   ORDER BY m.modulo
";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $sesionRol);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $modulos_permitidos[] = $row;
}
$stmt->close();

// 3) Agrupar por uno o dos niveles
$grupos = [];
foreach ($modulos_permitidos as $modulo) {
  $partes = explode(' - ', $modulo['modulo']);
  if (count($partes) === 1) {
    $nivel1 = trim($partes[0]);
    $grupos[$nivel1][] = [
      'nombre' => $nivel1,
      'ruta'   => $modulo['ruta']
    ];
  } elseif (count($partes) === 2) {
    $nivel1 = trim($partes[0]);
    $nombre = trim($partes[1]);
    $grupos[$nivel1][] = [
      'nombre' => $nombre,
      'ruta'   => $modulo['ruta']
    ];
  } else {
    // tres o más, tomamos solo los dos primeros niveles
    $nivel1 = trim($partes[0]);
    $nivel2 = trim($partes[1]);
    $clave  = "{$nivel1} - {$nivel2}";
    $nombre = trim($partes[2]);
    $grupos[$clave][] = [
      'nombre' => $nombre,
      'ruta'   => $modulo['ruta']
    ];
  }
}



$nombreSitio = '';

// Consulta con PDO
$sqlSitio = "SELECT nombre FROM sitios WHERE id = :id";
$stmt = $conn->prepare($sqlSitio);
$stmt->bindParam(':id', $sesionSitio, PDO::PARAM_INT);
$stmt->execute();

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $nombreSitio = $row['nombre'];
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Operación Alimentos</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
    }

    .container-flex {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* SIDEBAR */
    .sidebar {
      width: 280px;
      background: #f8f9fa;
      border-right: 1px solid #dee2e6;
      padding: 1rem;
      overflow-y: auto;
    }

    .sidebar .user-info {
      margin-bottom: 1.5rem;
      padding-bottom: .5rem;
      border-bottom: 1px solid #dee2e6;
    }

    .sidebar .user-info h5 {
      margin: 0;
      font-size: 1.1rem;
    }

    .sidebar .user-info small {
      color: #6c757d;
    }

    .group-title {
      margin-top: 1rem;
      margin-bottom: .3rem;
      font-weight: 600;
      text-transform: uppercase;
      font-size: .85rem;
      color: #343a40;
    }

    .nav-link {
      color: #495057;
      padding: .4rem 0;
      display: block;
      transition: .2s;
    }

    .nav-link:hover {
      background: #e9ecef;
      border-radius: 4px;
      text-decoration: none;
    }

    /* scrollbar */
    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: #adb5bd;
      border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: #f8f9fa;
    }

    /* MAIN CONTENT */
    .main-content {
      flex: 1;
      padding: 2rem;
      overflow-y: auto;
    }

    .hover-bg:hover {
      background-color: #f8f9fa;
      text-decoration: none;
    }

    .nav-link.active,
    .nav-link:hover {
      font-weight: 500;
      color: #0d6efd !important;
    }
  </style>
</head>

<body>

  <div class="container-flex">

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
      <!-- Logo + Usuario -->
      <div class="text-center border-bottom pb-3 mb-3">
        <img src="img/IGSA_Medical.png" alt="IGSA Medical" width="60" height="60" class="mb-2">
        <div class="fw-semibold fs-5">IGSA Medical</div>
        <div class="text-muted small"><?= htmlspecialchars($sesionNombre) ?></div>
        <div class="text-muted small mb-2">Usuario: <?= htmlspecialchars($sesionUsuario) ?></div>
        <div class="text-muted small">Sitio: <?= htmlspecialchars($nombreSitio) ?></div>
        <a href="index.html" class="nav-link text-danger p-0">
          <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
        </a>
      </div>

      <!-- Accesos fijos -->
      <ul class="nav flex-column mb-3">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="bi bi-house me-2"></i> Inicio
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-toggle="modal" data-target="#myModalUpdate">
            <i class="bi bi-gear me-2"></i> Configuración
          </a>
        </li>

      </ul>



      <ul class="nav flex-column px-2">
        <?php foreach ($grupos as $grupo => $modulos): ?>
          <!-- Título del grupo -->
          <li class="nav-item mb-1 px-3 text-uppercase fw-bold small text-secondary border-bottom pb-1">
            <?= htmlspecialchars($grupo) ?>
          </li>

          <!-- Módulos del grupo -->
          <?php foreach ($modulos as $modulo): ?>
            <li class="nav-item">
              <a href="<?= htmlspecialchars($modulo['ruta']) ?>" class="nav-link d-flex align-items-center gap-2 px-3 py-1 rounded text-dark hover-bg">
                <i class="bi bi-dot text-primary small"></i>
                <span class="small"><?= htmlspecialchars($modulo['nombre']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </ul>



    </aside>

  </div>

  <!-- Modal para actualización de contraseña -->
  <div class="modal fade" id="myModalUpdate" tabindex="-1" aria-labelledby="myModalUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content shadow-lg rounded-4">

        <!-- Encabezado -->
        <div class="modal-header bg-primary text-white rounded-top">
          <h5 class="modal-title" id="myModalUpdateLabel">🔒 Actualización de Contraseña</h5>
          <!-- Botón X -->
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>

        </div>

        <!-- Cuerpo del modal -->
        <div class="modal-body px-4 py-3">
          <form id="myFormUpdate" class="row g-3 needs-validation" novalidate>

            <input type="hidden" name="usuarioUpdate" value="<?= $sesionUsuario ?>">

            <div class="col-12">
              <label for="actual" class="form-label fw-semibold">Contraseña actual</label>
              <input type="password" class="form-control form-control-lg" id="actual" name="actual" required>
            </div>

            <div class="col-12">
              <label for="nueva" class="form-label fw-semibold">Nueva contraseña</label>
              <input type="password" class="form-control form-control-lg" id="nueva" name="nueva" required>
            </div>

            <div class="col-12">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="showPasswords">
                <label class="form-check-label" for="showPasswords">Mostrar contraseñas</label>
              </div>
            </div>
          </form>
        </div>

        <!-- Pie del modal -->
        <div class="modal-footer d-flex justify-content-between px-4 py-3">
          <button type="submit" class="btn btn-success btn-lg px-4" form="myFormUpdate">Actualizar</button>
          <!-- Botón Cancelar -->
          <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">Cancelar</button>
        </div>

      </div>
    </div>
  </div>




  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery (requerido por Bootstrap) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS (versión 4 o compatible con tu HTML) -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Mostrar/ocultar contraseñas
    document.getElementById('showPasswords').addEventListener('change', function() {
      const actual = document.getElementById('actual');
      const nueva = document.getElementById('nueva');
      const type = this.checked ? 'text' : 'password';
      actual.type = type;
      nueva.type = type;
    });

    // Enviar formulario con AJAX
    $("#myFormUpdate").on("submit", function(e) {
      e.preventDefault();

      $.ajax({
        url: "updatePassword.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
          if (response.status === "success") {
            alert("✅ " + response.message);
            $("#myModalUpdate").modal("hide");

            // Espera 1.5 segundos antes de cerrar sesión
            setTimeout(function() {
              window.location.href = "dashboard.php"; // o login.php
            }, 1500);

          } else {
            alert("❌ " + response.message);
          }
        },
        error: function() {
          alert("⚠️ Error en la conexión con el servidor");
        }
      });
    });
  </script>




</body>



</html>