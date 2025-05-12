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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = strtoupper(trim($_POST['nombre']));

    // Verificar que los datos no estén vacíos
    if (!empty($id) && !empty($nombre)) {
        try {
            // Crear la consulta SQL
            $sql = "UPDATE salas_conferencias SET nombre = ? WHERE id = ?";
            $stmt = $con->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if ($stmt) {
                // Vincular los parámetros
                $stmt->bind_param("si", $nombre, $id); // "s" para string, "i" para integer

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Sala de conferencia actualizado exitosamente.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Error al actualizar la sala de conferencia: ' . $stmt->error;
                    $_SESSION['message_type'] = 'danger';
                }

                // Cerrar el statement
                $stmt->close();
            } else {
                $_SESSION['message'] = 'Error al preparar la consulta: ' . $con->error;
                $_SESSION['message_type'] = 'danger';
            }
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error en la base de datos: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Todos los campos son obligatorios.';
        $_SESSION['message_type'] = 'warning';
    }
}

// Redirigir a la página de administración
header('Location: adminSalas.php');
exit();
?>
