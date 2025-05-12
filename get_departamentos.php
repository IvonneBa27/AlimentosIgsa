<?php
require 'conexion.php'; // O tu archivo de conexión

if (isset($_POST['id'])) {
    $empresa_id = $_POST['empresa'];

    $sql = "SELECT id, nombre FROM departamento WHERE empresa_id = ? AND estatus_id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$empresa_id]);
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<option value="">Seleccione un departamento</option>';
    foreach ($departamentos as $dep) {
        echo '<option value="' . $dep['id'] . '">' . $dep['nombre'] . '</option>';
    }
}
?>
