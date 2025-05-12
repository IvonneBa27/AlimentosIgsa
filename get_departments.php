<?php
// Iniciar la sesión
session_start();

// Incluir el archivo de conexión
include('conexion.php');

// Validar si el usuario tiene una sesión activa
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html?error=session_expired');
    exit;
} else {
    // Recuperar datos de la sesión
    $sesi = $_SESSION['resultado'];
    $sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
    $sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna
    $sesionUsuarioId = $sesi['id']; 
}

if (isset($_GET['empresa_id'])) {
    $empresa_id = $_GET['empresa_id'];

    // Conexión a la base de datos (debe estar definida correctamente en conexion.php)
    $sql_departamentos = "SELECT id, nombre FROM departamento WHERE empresa_id = ?";
    
    // Preparar la consulta
    if ($stmt = $con->prepare($sql_departamentos)) {
        // Vincular parámetros (en este caso solo un parámetro)
        $stmt->bind_param("i", $empresa_id);
        
        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $result = $stmt->get_result();

        // Crear un arreglo para almacenar los departamentos
        $departamentos = [];
        
        // Recorrer los resultados
        while ($row = $result->fetch_assoc()) {
            $departamentos[] = $row;
        }

        // Devolver los departamentos en formato JSON
        echo json_encode($departamentos);

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
