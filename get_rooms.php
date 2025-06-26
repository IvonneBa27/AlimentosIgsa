<?php
header('Content-Type: application/json; charset=utf-8');
include 'db_connection.php';

$empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
if ($empresa_id < 1) {
    echo '[]';
    exit;
}

$sql = "
  SELECT id, nombre 
  FROM salas_conferencias 
  WHERE estatus_id = 1 
  ORDER BY nombre
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':eid', $empresa_id, PDO::PARAM_INT);
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
