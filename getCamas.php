<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conexion.php';

if (!isset($_GET['area'])) {
    echo json_encode(["error" => "Área no especificada"]);
    exit;
}

$area = $_GET['area'];

try {
    $stmt = $con->prepare("
        SELECT numero 
        FROM camas 
        WHERE area = ? 
        AND numero NOT IN (
            SELECT cama 
            FROM pacientes 
            WHERE area = ? 
            AND statusP = 'Activo'
        )
    ");
    $stmt->bind_param("ss", $area, $area);
    $stmt->execute();
    $result = $stmt->get_result();

    $camas = [];
    while ($row = $result->fetch_assoc()) {
        $camas[] = $row['numero'];
    }

    echo json_encode($camas);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
