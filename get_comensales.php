<?php
include 'db_connection.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'conexion.php'; // Incluye tu conexión PDO

header('Content-Type: application/json'); // Indica que la respuesta será JSON

// Validar que se envíe el parámetro departamento por GET
if (isset($_GET['departamento'])) {
    $departamento_id = $_GET['departamento'];

    try {
        // Consulta solo los campos necesarios para la lista de comensales
        $sql = "SELECT id, barcode, nombre_completo, correo FROM comensal WHERE departamento = ? AND estatus = 1 ORDER BY nombre_completo";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departamento_id]);
        $comensales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "comensales" => $comensales
        ]);
    } catch (Exception $e) {
        // En caso de error en la consulta
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error en la consulta: " . $e->getMessage()
        ]);
    }
} else {
    // Si falta el parámetro departamento
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta el parámetro 'departamento'"
    ]);
}
