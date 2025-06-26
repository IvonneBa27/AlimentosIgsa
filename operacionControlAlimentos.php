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





</head>

<div>
  <div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main-content">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h3 class="h3">CONTROL DE ALIMENTOS</h3>
        <div class="btn-toolbar mb-2 mb-md-0">
          <!--<div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Punto de Venta</button>
          </div>-->

          <form id="filterForm" method="GET" action="#">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">Fecha Inicio</button>
              <input type="date" class="btn btn-sm btn-outline-secondary" name="fechaInicio" id="fechaInicio" value="<?= htmlspecialchars($fechaInicio) ?>" required>
              <button type="button" class="btn btn-sm btn-outline-secondary">Fecha Fin</button>
              <input type="date" class="btn btn-sm btn-outline-secondary" name="fechaFin" id="fechaFin" value="<?= htmlspecialchars($fechaFin) ?>" required>
              <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </div>
          </form>

          <form action="">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'operacionControlAlimentos.php';">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                  <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                </svg>
              </button>
            </div>
          </form>
        </div>
      </div>



      <div id="content" class="container mt-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center">Gráfica de Productos</h5>
            <br>

            <div id="chart-container">
              <canvas id="myChart"></canvas>
            </div>

            <!-- Tabla con totales -->

            <table class="table table-striped text-center">
              <thead class="table-dark">
                <thead>
                  <tr>
                    <th colspan="3">CONSUMO DE ALIMENTOS &nbsp; <span id="fechaRango"></span></th>
                  </tr>
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                  </tr>
                </thead>
              <tbody id="tablaTotales">
                <!-- Contenido generado dinámicamente -->
              </tbody>
              <tfoot>
                <tr>
                  <td><strong>TOTAL</strong></td>
                  <td id="totalCantidad"></td>
                  <td>100%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <script>
        // Suponiendo que `fechaInicio` y `fechaFin` son valores obtenidos del servidor o seleccionados por el usuario
        const fechaInicio = "<?= $fechaInicio ?>".split(' ')[0]; // Tomar solo la fecha
        const fechaFin = "<?= $fechaFin ?>".split(' ')[0]; // Tomar solo la fecha

        // Función para formatear la fecha en día, mes y año
        function formatearFecha(fecha) {
          const opciones = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          };
          return new Date(fecha).toLocaleDateString('es-ES', opciones);
        }

        // Actualizar el rango de fechas en la tabla
        document.getElementById('fechaRango').textContent = `${formatearFecha(fechaInicio)} AL ${formatearFecha(fechaFin)}`;

        // Datos generados en el backend
        const productos = <?= $productosJSON ?>;
        const totales = <?= $totalesJSON ?>;
        const colores = <?= $coloresJSON ?>;

        // Calcular el total general
        const totalRegistros = totales.reduce((sum, value) => sum + value, 0);

        // Crear filas de la tabla
        const tablaTotales = document.getElementById('tablaTotales');
        productos.forEach((producto, index) => {
          const porcentaje = ((totales[index] / totalRegistros) * 100).toFixed(2); // Calcular porcentaje
          const fila = `
                <tr>
                    <td>${producto}</td>
                    <td>${totales[index]}</td>
                    <td>${porcentaje}%</td>
                </tr>
            `;
          tablaTotales.innerHTML += fila;
        });

        // Actualizar el total en la tabla
        document.getElementById('totalCantidad').textContent = totalRegistros;

        // Inicializar gráfico de pastel con Chart.js
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: productos,
            datasets: [{
              data: totales,
              backgroundColor: colores,
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'top',
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const total = context.raw;
                    const porcentaje = ((total / totalRegistros) * 100).toFixed(2);
                    return `${context.label}: ${total} (${porcentaje}%)`;
                  }
                }
              }
            }
          }
        });

        // Cambiar entre tema claro y oscuro
        document.getElementById('toggleTheme').addEventListener('click', function() {
          const body = document.body;
          const icon = this.querySelector('i');

          if (body.classList.contains('bg-light')) {
            body.classList.replace('bg-light', 'bg-dark');
            body.classList.replace('text-dark', 'text-light');
            icon.classList.replace('fa-moon', 'fa-sun');
          } else {
            body.classList.replace('bg-dark', 'bg-light');
            body.classList.replace('text-light', 'text-dark');
            icon.classList.replace('fa-sun', 'fa-moon');
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