<?php
include('conexion.php'); // Asegúrate que $con esté definido ahí
session_start();

// Verifica sesión activa
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
}

$sesi = $_SESSION['resultado'];
$sesionUsuario = $sesi['usuario'];
$sesionNombre = $sesi['nombre'];
$sesionUsuarioId = $sesi['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos
    $apaterno = strtoupper(trim($_POST['apaterno']));
    $amaterno = strtoupper(trim($_POST['amaterno']));
    $nombre = strtoupper(trim($_POST['nombre']));
    $nombre_completo = "$apaterno $amaterno $nombre";
    $usuario = trim($_POST['usuario']);
    $contrasena_raw = trim($_POST['contrasena']);
    $correo = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $rol_id = intval($_POST['rol_id']);
        $sitio_id = intval($_POST['sitio_id']);

    // Seguridad
    $contrasena = password_hash($contrasena_raw, PASSWORD_DEFAULT); // Encriptar
    $fecha_registro = date("Y-m-d H:i:s");
    $estatus_id = 1;

    // Insertar en la base de datos
    $sql = "INSERT INTO usuarios (rol_id, usuario, contrasena, apaterno, amaterno, nombre, nombre_completo, correo, fecha_registro, estatus_id, sitio_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la consulta: " . $con->error);
    }

    $stmt->bind_param(
        "issssssssii",
        $rol_id,
        $usuario,
        $contrasena,
        $apaterno,
        $amaterno,
        $nombre,
        $nombre_completo,
        $correo,
        $fecha_registro,
        $estatus_id,
        $sitio_id
    );

    if ($stmt->execute()) {
        header("Location:adminUsuariosAdmin.php");
    } else {
        echo "❌ Error al registrar: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
} else {
    echo "Método no permitido.";
}
?>
