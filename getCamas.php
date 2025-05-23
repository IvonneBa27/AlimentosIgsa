
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conexion.php'; // Asegúrate de que este archivo existe y no tiene errores

if (!isset($_GET['area'])) {
    echo json_encode(["error" => "Área no especificada"]);
    exit;
}

$area = $_GET['area'];

try {
    $stmt = $con->prepare("SELECT numero FROM camas WHERE area = ?");
    $stmt->bind_param("s", $area);
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
