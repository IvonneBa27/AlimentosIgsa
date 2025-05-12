<?php
include('conexion.php');

$empresa = isset($_POST['empresa']) ? $_POST['empresa'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';

// Construcción de la consulta con filtros
$query = "SELECT co.id, co.nombre_completo, co.a_paterno, co.a_materno, co.nombre, co.num_empleado, co.empresa, co.departamento, co.correo, co.barcode, co.barcode_path, co.imagePath, emp.nombre as nombre_empresa, co.fecha_de_alta, co.puesto, cs.status
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
            <td class='text-center'>
                <a href='#' data-bs-toggle='modal' data-bs-target='#barcodeModal'
                    data-barcode='" . htmlspecialchars($comensal['barcode_path']) . "'>
                    <i class='fas fa-barcode'></i>
                </a>
            </td>
            <td class='text-center'>
                <a href='#' data-bs-toggle='modal' data-bs-target='#imageModal'
                    data-image='" . htmlspecialchars($comensal['imagePath']) . "'>
                    <i class='fas fa-user-circle'></i>
                </a>
            </td>
            <td>
                <span class='badge " . ($comensal['status'] === 'ACTIVO' ? 'bg-success' : 'bg-danger') . "'>" . htmlspecialchars($comensal['status']) . "</span>
            </td>
   <td class='actions'>
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
    </a>
</td>

        </tr>";
    }
} else {
    echo '<tr><td colspan="9" class="text-center">No se encontraron comensales.</td></tr>';
}
