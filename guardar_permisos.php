<?php
include("conexion.php");

$rol_id = intval($_POST['rol_id']);
$permisos = $_POST['permisos'] ?? [];

// Eliminar permisos actuales del rol
$con->query("DELETE FROM permisos WHERE rol_id = $rol_id");

// Insertar los nuevos
foreach ($permisos as $modulo_id => $acciones) {
    $ver = isset($acciones['ver']) ? 1 : 0;
    $crear = isset($acciones['crear']) ? 1 : 0;
    $editar = isset($acciones['editar']) ? 1 : 0;
    $eliminar = isset($acciones['eliminar']) ? 1 : 0;

    $stmt = $con->prepare("INSERT INTO permisos (rol_id, modulo_id, ver, crear, editar, eliminar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiii", $rol_id, $modulo_id, $ver, $crear, $editar, $eliminar);
    $stmt->execute();
}

header("Location: adminSeguridad.php?rol_id=$rol_id&success=1");
