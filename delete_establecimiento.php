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
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID del usuario
    $id = $_POST['id'];
    try {
        // Actualizar el estado del usuario en la base de datos
        $sql = "UPDATE establecimientos SET estatus_id = 2 WHERE id = ?";
        $stmt = $con->prepare($sql);

        if ($stmt) {
            // Enlazar el parámetro (i: entero)
            $stmt->bind_param('i', $id);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Redirigir a la página deseada con un mensaje de éxito
                header("Location: adminTiendas.php?msg=updated");
                exit();
            } else {
                // Manejo de error si la consulta no se ejecutó correctamente
                echo 'Error al actualizar el estado del establecimiento: ' . $stmt->error;
            }

            // Cerrar el statement
            $stmt->close();
        } else {
            echo 'Error al preparar la consulta: ' . $con->error;
        }
    } catch (Exception $e) {
        // Manejo de excepciones para errores generales
        echo 'Error general: ' . $e->getMessage();
    }
}

// Cerrar la conexión
$con->close();
