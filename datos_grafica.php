<?php
include 'db_connection.php';

// Obtener fechas desde el frontend
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

// Validar fechas; si no se proporcionan, usar el mes y año actuales
if (!$fechaInicio || !$fechaFin) {
    $mes = date('m'); // Mes actual
    $anio = date('Y'); // Año actual
    $fechaInicio = "$anio-$mes-01 00:00:00"; // Primer día del mes actual a las 00:00:00
    $fechaFin = date('Y-m-t') . " 23:59:59"; // Último día del mes actual a las 23:59:59
} else {
    // Asegurar que las fechas incluyan el rango completo del día
    $fechaInicio .= ' 00:00:00'; // Comienzo del día
    $fechaFin .= ' 23:59:59';    // Fin del día
}

// Consulta para obtener el total de cada producto en el rango de fechas
$sql = "SELECT 
            p.producto AS nombre_producto, 
            COUNT(ca.tipo_producto) AS total
        FROM 
            control_alimentos ca
        INNER JOIN 
            producto p 
        ON 
            ca.tipo_producto = p.id
        WHERE 
            ca.fecha_registro BETWEEN :fechaInicio AND :fechaFin
        GROUP BY 
            p.producto
        ORDER BY 
            total DESC";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':fechaInicio', $fechaInicio);
$stmt->bindValue(':fechaFin', $fechaFin);
$stmt->execute();

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica
$productos = [];
$totales = [];
$colores = [];

// Generar datos y colores aleatorios
foreach ($resultados as $fila) {
    $productos[] = $fila['nombre_producto']; // Nombre del producto
    $totales[] = $fila['total'];            // Total de cada producto
    $colores[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); // Color aleatorio
}

// Convertir datos a JSON para el frontend
$productosJSON = json_encode($productos);
$totalesJSON = json_encode($totales);
$coloresJSON = json_encode($colores);
