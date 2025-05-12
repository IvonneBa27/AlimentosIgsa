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

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $producto = !empty($_POST['producto']) ? strtoupper($_POST['producto']) : null;
  

    if ($id && $producto) {
        try {
            // Crear y ejecutar la consulta SQL para actualizar el producto
            $sql = "UPDATE producto SET producto = ? WHERE id = ?";
            $stmt = $con->prepare($sql);
    
            // Verificar si la consulta fue preparada correctamente
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $conn->error);
            }
    
            // Enlazar parámetros
            $stmt->bind_param("si", $producto, $id); // s: string, d: double, i: integer
    
            // Ejecutar la consulta
            if ($stmt->execute()) {
                $_SESSION['message'] = "Producto actualizado correctamente.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error al actualizar el producto.";
                $_SESSION['message_type'] = "danger";
            }
    
            // Cerrar el statement
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Todos los campos son obligatorios.";
        $_SESSION['message_type'] = "warning";
    }
    
    // Redirigir al usuario después de completar la operación
    header("Location: adminProducto.php");
    exit();
    
}
?>
