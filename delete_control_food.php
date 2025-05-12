<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Incluir conexión a la base de datos
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID del usuario
    $id = $_POST['id'];

    // Actualizar el estado del usuario en la base de datos
    $sql = "UPDATE control_alimentos SET estatus_id = 2 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    // Ejecutar la actualización y verificar el resultado
    if ($stmt->execute()) {
        // Redirigir a la misma página después de la actualización
        header("Location: operacionRegistroAlimentos.php");
        exit();
    } else {
        echo 'Error al actualizar el estado del producto.';
    }

    // Cerrar la conexión
    $conn = null;
}
