<?php
include 'conexion.php';
session_start(); // Iniciar sesión

// Verificar que se haya enviado el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar los datos del formulario
    $Usuario = isset($_POST['user']) ? trim($_POST['user']) : '';
    $Contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

    // Verificar que los campos no estén vacíos
    if (empty($Usuario) || empty($Contrasena)) {
        header("Location: index.html?error=campos_vacios");
        exit();
    }

    // Verificar conexión
    if (!$con) {
        die("Conexión fallida: " . mysqli_connect_error());
    }

    // Preparar la consulta para evitar inyección SQL
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "s", $Usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $res2 = mysqli_fetch_assoc($result);

    // Verificar si el usuario existe y si la contraseña es correcta
    if ($res2 && password_verify($Contrasena, $res2['contrasena'])) {
        $_SESSION['resultado'] = $res2; // Guardar datos en sesión
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: index.html?error=credenciales_invalidas");
        exit();
    }

    // Cerrar la conexión
    mysqli_stmt_close($stmt);
    mysqli_close($con);
} else {
    // Si se intenta acceder directamente sin POST
    header("Location: index.html?error=acceso_no_valido");
    exit();
}
?>
