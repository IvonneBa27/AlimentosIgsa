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
    $estatus_id = 1;
    // Verificar si $nombre no está vacío
    if ($nombre) {
        try {
            // Crear la consulta SQL para insertar el nombre
            $sql = "INSERT INTO empresa (nombre, estatus_id, user_id) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if ($stmt) {
                // Vincular parámetros y ejecutar la consulta
                $stmt->bind_param("sii", $nombre, $estatus_id, $sesionUsuarioId); // "s" indica que es un string
                if ($stmt->execute()) {
                    // Redireccionar a la lista de empresas si se ejecuta correctamente
                    header("Location: adminEmpresa.php");
                    exit();
                } else {
                    // Manejo de errores en la ejecución
                    echo "Error al ejecutar la consulta: " . $stmt->error;
                }

                // Cerrar el statement
                $stmt->close();
            } else {
                echo "Error al preparar la consulta: " . $con->error;
            }
        } catch (Exception $e) {
            // Manejo de excepciones
            echo "Error general: " . $e->getMessage();
        }
    } else {
        echo "El campo nombre es obligatorio.";
    }

    // Cerrar la conexión
    $con->close();
}
?>
