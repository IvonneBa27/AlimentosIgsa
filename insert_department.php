<?php
// Iniciar la sesión
session_start();

// Incluir el archivo de conexión
include('conexion.php');

// Validar si el usuario tiene una sesión activa
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html?error=session_expired');
    exit;
} else {
    // Recuperar datos de la sesión
    $sesi = $_SESSION['resultado'];
    $sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
    $sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna
    $sesionUsuarioId = $sesi['id']; 
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario y convertir a mayúsculas
    $nombre = !empty($_POST['nombre']) ? strtoupper($_POST['nombre']) : null;
    $empresa_id = !empty($_POST['empresa_id']) ? $_POST['empresa_id'] : null;
    $estatus_id = 1;

    // Verificar si los campos requeridos no están vacíos
    if ($nombre && $empresa_id) {
        try {
            // Crear la consulta SQL
            $sql = "INSERT INTO departamento (nombre, empresa_id, estatus_id, user_id) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if ($stmt) {
                // Vincular los parámetros
                $stmt->bind_param("siii", $nombre, $empresa_id, $estatus_id, $sesionUsuarioId);

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    // Redireccionar a la lista de departamentos
                    header("Location: adminDepartamento.php");
                    exit();
                } else {
                    echo "Error al insertar el departamento: " . $stmt->error;
                }

                // Cerrar el statement
                $stmt->close();
            } else {
                echo "Error al preparar la consulta: " . $con->error;
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            echo "Error general: " . $e->getMessage();
        }
    } else {
        echo "Todos los campos son obligatorios.";
    }

    // Cerrar la conexión
    $con->close();
}
?>
