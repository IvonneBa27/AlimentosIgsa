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
    $tipo_usuario = !empty($_POST['tipo_usuario']) ? strtoupper($_POST['tipo_usuario']) : null;

    // Verificar si $tipo_usuario no está vacío
    if ($tipo_usuario) {
        try {
            // Crear la consulta SQL
            $sql = "INSERT INTO tipo_usuario (tipo_usuario, status_id, user_id) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if ($stmt) {
                // Estatus inicial (puede cambiar según tus requisitos)
                $status_id = 1;

                // Vincular parámetros
                $stmt->bind_param("sii", $tipo_usuario, $status_id, $sesionUsuarioId); // "s" para string, "i" para integer

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    // Redireccionar a la lista de tipos de usuarios
                    header("Location: adminUsuario.php");
                    exit();
                } else {
                    echo "Error al ejecutar la consulta: " . $stmt->error;
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
        echo "El campo tipo de usuario es obligatorio.";
    }

    // Cerrar la conexión
    $conn->close();
}
?>
