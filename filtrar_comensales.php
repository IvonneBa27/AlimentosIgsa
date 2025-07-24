<?php
include('conexion.php');

$empresa = isset($_POST['empresa']) ? $_POST['empresa'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';

// Construcción de la consulta con filtros
$query = "SELECT co.id, co.nombre_completo, co.a_paterno, co.a_materno, co.nombre, co.num_empleado, co.empresa, co.departamento, co.correo, co.barcode, co.barcode_path, co.imagePath, emp.nombre as nombre_empresa, co.fecha_de_alta, co.puesto, cs.status, co.t_desayuno, co.t_colacion, co.t_comida, co.t_cena
        FROM comensal co 
        INNER JOIN catalog_status cs ON cs.status_id = co.estatus
        INNER JOIN empresa emp ON co.empresa = emp.id
        WHERE 1=1";

$params = [];
$types = ""; // Para bind_param

// Filtro por empresa (si se proporciona)
if (!empty($empresa)) {
    $query .= " AND co.empresa = ?";
    $params[] = $empresa;
    $types .= "s";
}

// Filtro por nombre (si se proporciona)
if (!empty($nombre)) {
    $query .= " AND co.nombre_completo LIKE ?";
    $params[] = "%$nombre%"; // Asegura que el filtro busque coincidencias parciales
    $types .= "s";
}

// Preparar y ejecutar la consulta
$stmt = $con->prepare($query);

// Solo hacer bind_param si hay parámetros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Verifica cuántos resultados devuelve la consulta
if ($result->num_rows > 0) {
    while ($comensal = $result->fetch_assoc()) {
        echo "<tr>
    <td>" . htmlspecialchars($comensal['num_empleado']) . "</td>
    <td>" . htmlspecialchars($comensal['nombre_completo']) . "</td>
    <td>" . htmlspecialchars($comensal['nombre_empresa']) . "</td>
    <td>" . htmlspecialchars($comensal['correo']) . "</td>
    <td>" . htmlspecialchars($comensal['barcode']) . "</td>

    <td class='text-center align-middle'>
        <a href='#'
            data-bs-toggle='modal'
            data-bs-target='#barcodeModal'
            data-barcode='" . htmlspecialchars($comensal['barcode_path']) . "'
            data-barcodeinfo='" . htmlspecialchars($comensal['barcode']) . "'
            data-correo='" . htmlspecialchars($comensal['correo']) . "'
            data-id='" . htmlspecialchars($comensal['id']) . "'>
            <i class='fas fa-barcode'></i>
        </a>
    </td>

    <td class='text-center align-middle'>
        <a href='#' data-bs-toggle='modal' data-bs-target='#imageModal'
            data-image='" . htmlspecialchars($comensal['imagePath']) . "'>
            <i class='fas fa-user-circle'></i>
        </a>
    </td>";

        // Estatus con badge
        if (trim($comensal['status']) === 'ACTIVO') {
            echo "<td><span class='badge bg-success'>ACTIVO</span></td>";
        } elseif (trim($comensal['status']) === 'BAJA') {
            echo "<td><span class='badge bg-danger'>BAJA</span></td>";
        } else {
            echo "<td><span class='badge bg-secondary'>" . htmlspecialchars(trim($comensal['status'])) . "</span></td>";
        }

        echo "<td class='actions'>
        <a href='#'
            data-id='" . htmlspecialchars($comensal['id']) . "'
            data-apaterno='" . htmlspecialchars($comensal['a_paterno']) . "'
            data-amaterno='" . htmlspecialchars($comensal['a_materno'] ?? '') . "'
            data-nombre='" . htmlspecialchars($comensal['nombre'] ?? '') . "'
            data-numempleado='" . htmlspecialchars($comensal['num_empleado'] ?? '') . "'
            data-empresa='" . htmlspecialchars($comensal['empresa'] ?? '') . "'
            data-departamento='" . htmlspecialchars($comensal['departamento'] ?? '') . "'
            data-puesto='" . htmlspecialchars($comensal['puesto'] ?? '') . "'
            data-correo='" . htmlspecialchars($comensal['correo'] ?? '') . "'
            data-barcode='" . htmlspecialchars($comensal['barcode'] ?? '') . "'
            data-image='" . htmlspecialchars($comensal['imagePath'] ?? '') . "'
            data-desayuno='" . htmlspecialchars($comensal['t_desayuno'] ?? '') . "'
            data-colacion='" . htmlspecialchars($comensal['t_colacion'] ?? '') . "'
            data-comida='" . htmlspecialchars($comensal['t_comida'] ?? '') . "'
            data-cena='" . htmlspecialchars($comensal['t_cena'] ?? '') . "'
            data-bs-toggle='modal'
            data-bs-target='#editComensalModal'>
            <i class='fas fa-edit'></i>
        </a>
        &nbsp;

        <a href='#'
            data-id='" . htmlspecialchars($comensal['id']) . "'
            data-bs-toggle='modal'
            data-bs-target='#deleteComensalModal'
            onclick='setUserId(" . htmlspecialchars($comensal['id']) . ")'>
            <i class='fas fa-trash-alt'></i>
        </a>";

        // Botón de alta (solo si está en baja)
        if (trim($comensal['status']) === 'BAJA') {
            echo "&nbsp;
        <a href='#'
            data-id='" . htmlspecialchars($comensal['id']) . "'
            data-bs-toggle='modal'
            data-bs-target='#altaComensalModal'
            onclick='setAltaId(" . htmlspecialchars($comensal['id']) . ")'>
            <i class='fas fa-user-alt'></i>
        </a>";
        }

        echo "</td>
</tr>";
    }
} else {
    echo '<tr><td colspan="9" class="text-center">No se encontraron comensales.</td></tr>';
}
