<?php
// Procesa los datos recibidos
$data = json_decode(file_get_contents('php://input'), true);

// Incluye tu archivo de conexión a la base de datos (asumiendo que 'conexion.php' contiene los detalles de la conexión)
include('conexion.php');

// Variable para almacenar los resultados de la inserción
$response = [];

foreach ($data as $registro) {
    $fecha = $registro['fechaH'];
    $area = $registro['area'];
    $idPaciente = $registro['id'];
    $nombrePaciente = $registro['nombre'];
    $fechaNacimiento = $registro['fechaNacimiento'];
    $cama = $registro['cama'];
    $edad = $registro['edad'];
    $diagnostico = $registro['diagnostico'];
    $prescripcion = $registro['prescripcion'];
    $desayuno = $registro['desayuno'];
    $colacionMatutina = $registro['colacionMatutina'];
    $comida = $registro['comida'];
    $colacionVespertina = $registro['colacionVespertina'];
    $cena = $registro['cena'];
    $colacionNocturna = $registro['colacionNocturna'];
    $observaciones = $registro['observaciones'];
    $controlTamizaje = $registro['controlTamizaje'];
    $usuario = $registro['usuario'];

    // Inserta los datos en la base de datos
    $add = "INSERT INTO dietas VALUES ('', '$nombrePaciente', '$fechaNacimiento', '$idPaciente', '$cama', '$edad', '$diagnostico', '$prescripcion', 
    '$desayuno', '$colacionMatutina','$comida', '$colacionVespertina', '$cena', '$colacionNocturna', '$observaciones', '$controlTamizaje', '$area', '$fecha', '$usuario')";
    $query = mysqli_query($con, $add);

    if ($query) {
        // Datos insertados correctamente
        $response[] = ['message' => '¡Orden solicitada!'];
    } else {
        // Error al insertar datos
        $response[] = ['error' => 'Error al insertar registro'];
    }
}

// Envía una respuesta JSON con los resultados
echo json_encode($response);
