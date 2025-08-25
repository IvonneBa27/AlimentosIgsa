<?php
session_start();
include('conexion.php');

// Verificar sesión
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html?error=session_expired');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
    $sesionUsuario = $sesi['usuario'];
    $sesionNombre = $sesi['nombre'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $modulo = trim($_POST['modulo']);
    $ruta = trim($_POST['ruta']);

    
    // Debug temporal
   //  echo "ID: $id<br>Modulo: $modulo<br>Ruta: $ruta<br>"; exit;

    if (!empty($id) && !empty($modulo )) { // Antes decía $nombre
        try {
            $sql = "UPDATE modulos SET modulo = ?, ruta = ? WHERE id = ?";
            $stmt = $con->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ssi", $modulo, $ruta, $id); // Se usaba $nombre, debe ser $modulo

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Modulo actualizado exitosamente.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Error al actualizar el modulo: ' . $stmt->error;
                    $_SESSION['message_type'] = 'danger';
                }

                $stmt->close();
            } else {
                $_SESSION['message'] = 'Error al preparar la consulta: ' . $con->error;
                $_SESSION['message_type'] = 'danger';
            }
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error en la base de datos: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Todos los campos son obligatorios.';
        $_SESSION['message_type'] = 'warning';
    }
}

header('Location: adminModulos.php');
exit();
