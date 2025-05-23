<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // Consulta preparada para evitar inyecciones
        $stmt = $con->prepare("UPDATE control_puntoventa SET estatus = 3 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: operacionPuntoConsumo.php?mensaje=cancelado");
            exit;
        } else {
            echo "Error al cancelar: " . $stmt->error;
        }

        $stmt->close();
        $con->close();
    } else {
        echo "ID no recibido.";
    }
}
?>
