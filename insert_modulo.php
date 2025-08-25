<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html?error=session_expired');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
    $sesionUsuarioId = $sesi['id'];
    $sesionRolId = $sesi['rol_id']; // Asegúrate que el rol_id esté en sesión
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $modulo = !empty($_POST['modulo']) ? $_POST['modulo'] : null;
    $ruta = !empty($_POST['ruta']) ? $_POST['ruta'] : null;
    $estatus_id = 1;

    if ($modulo && $ruta) {
        try {
            // Insertar nuevo módulo
            $sql = "INSERT INTO modulos (modulo, ruta, estatus_id, user_id) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ssii", $modulo, $ruta, $estatus_id, $sesionUsuarioId);
                if ($stmt->execute()) {
                    // Obtener el id generado del módulo insertado
                    $nuevoModuloId = $con->insert_id;
                    $stmt->close();

                    // Insertar permisos con todos los permisos en 1 para el rol del usuario actual
                    $sqlPermisos = "INSERT INTO permisos (rol_id, modulo_id, ver, crear, editar, eliminar) VALUES (?, ?, 1, 1, 1, 1)";
                    $stmtPermisos = $con->prepare($sqlPermisos);
                    if ($stmtPermisos) {
                        $stmtPermisos->bind_param("ii", $sesionRolId, $nuevoModuloId);
                        $stmtPermisos->execute();
                        $stmtPermisos->close();

                        // Redireccionar después de todo OK
                        header("Location: adminModulos.php");
                        exit();
                    } else {
                        echo "Error al preparar la consulta de permisos: " . $con->error;
                    }
                } else {
                    echo "Error al insertar el módulo: " . $stmt->error;
                }
            } else {
                echo "Error al preparar la consulta de módulos: " . $con->error;
            }
        } catch (Exception $e) {
            echo "Error general: " . $e->getMessage();
        }
    } else {
        echo "Todos los campos son obligatorios.";
    }

    $con->close();
}
?>
