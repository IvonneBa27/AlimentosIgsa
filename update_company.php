<?php

include('conexion.php');
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = strtoupper(trim($_POST['nombre']));

    // Validar que los datos no estén vacíos
    if (!empty($id) && !empty($nombre)) {
        try {
            $sql = "UPDATE empresa SET nombre = ? WHERE id = ?";
            $stmt = $con->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if ($stmt) {
                // Enlazar los parámetros
                $stmt->bind_param("si", $nombre, $id);

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Empresa actualizada con éxito.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error al actualizar la empresa: " . $stmt->error;
                    $_SESSION['message_type'] = "danger";
                }

                // Cerrar el statement
                $stmt->close();
            } else {
                $_SESSION['message'] = "Error al preparar la consulta: " . $con->error;
                $_SESSION['message_type'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Error general: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Todos los campos son obligatorios.";
        $_SESSION['message_type'] = "warning";
    }
    header('Location:adminEmpresa.php');
    exit();
}
?>
