<?php
include("conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $actualPassword = $_POST['actual'];
    $nuevoPassword = $_POST['nueva'];
    $usuario = $_POST['usuarioUpdate'];

    $consulta = "SELECT contrasena FROM usuarios WHERE usuario = ?";
    $stmt = mysqli_prepare($con, $consulta);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $hashedPassword = $row['contrasena'];

        if (password_verify($actualPassword, $hashedPassword)) {
            $nuevoPasswordHash = password_hash($nuevoPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE usuarios SET contrasena=? WHERE usuario=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $nuevoPasswordHash, $usuario);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(["status" => "success", "message" => "Contraseña actualizada correctamente"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error actualizando la contraseña"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "La contraseña actual es incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
