<?php
include("conexion.php"); // Asegúrate de que este archivo define $conn
date_default_timezone_set("America/Mexico_City");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar que los datos existen
    if (isset($_POST["idPaciente"], $_POST["comida"], $_POST["seleccionado"])) {
        $idPaciente = intval($_POST["idPaciente"]);
        $comida = $_POST["comida"];
        $seleccionado = intval($_POST["seleccionado"]);
        $fecha = date("Y-m-d");

        // Consulta SQL para insertar o actualizar
        $sql = "INSERT INTO estadoCheckbox (idPaciente, comida, seleccionado, fecha)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE seleccionado = ?";
        $stmt = $con->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isisi", $idPaciente, $comida, $seleccionado, $fecha, $seleccionado);
            if ($stmt->execute()) {
                echo "Guardado correctamente";
            } else {
                echo "Error al ejecutar la consulta";
            }
            $stmt->close();
        } else {
            echo "Error al preparar la consulta";
        }
    } else {
        echo "Datos incompletos";
    }

    $con->close();
} else {
    echo "Método no permitido";
}
?>
