<?php
include('conexion.php');
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi[1];
$sesionNombre = $sesi[3];
$sesionCargo = $sesi[4];
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $inputFileName = $_FILES['file']['tmp_name'];

    // Verificar la conexión a la base de datos
    if (!$con) {
        die("Conexión fallida: " . mysqli_connect_error());
    } else {
        //echo "Conexión exitosa a la base de datos.<br>";
    }

    // Leer el archivo Excel
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Omitir la primera fila (encabezados)
    array_shift($data);

    // Validar todos los idPaciente antes de insertar
    $idPacientes = array_column($data, 2);
    $idPacientesStr = implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($idPacientes), $con), $idPacientes));
    $checkQuery = "SELECT idPaciente FROM pacientes WHERE idPaciente IN ('$idPacientesStr')";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $existingIds = [];
        while ($row = mysqli_fetch_assoc($checkResult)) {
            $existingIds[] = $row['idPaciente'];
        }
        echo "El ID ingresado para paciente ya existe, valide los datos: " . implode(", ", $existingIds);
    } else {
        // Insertar datos en la base de datos
        foreach ($data as $row) {
            // Asegúrate de que la fila tiene el número correcto de columnas
            if (count($row) >= 10) {
                $nombre = mysqli_real_escape_string($con, $row[0]);
                $fechaNacimiento = mysqli_real_escape_string($con, $row[1]);
                $idPaciente = mysqli_real_escape_string($con, $row[2]);
                $cama = mysqli_real_escape_string($con, $row[3]);
                //$edad = mysqli_real_escape_string($con, $row[4]);
                $diagnosticoMed = mysqli_real_escape_string($con, $row[4]);
                $prescripcionNutri = mysqli_real_escape_string($con, $row[5]);
                $area = mysqli_real_escape_string($con, $row[6]);
                $vip = mysqli_real_escape_string($con, $row[7]);
                $observaciones = mysqli_real_escape_string($con, $row[8]);
                $controlTamizaje = mysqli_real_escape_string($con, $row[9]);
               
                // Convertir la fecha al formato YYYY-MM-DD
                $fechaNacimiento = date('Y-m-d', strtotime($fechaNacimiento));

                // Calcular la edad en años, meses y días
                $fechaNac = new DateTime($fechaNacimiento);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNac);

                $edadAnios = $edad->y;
                $edadMeses = $edad->m;
                $edadDias = $edad->d;

                // Imprimir la edad calculada para depuración
                //echo "Edad: $edadAnios años, $edadMeses meses, $edadDias días<br>";

                $sql = "INSERT INTO pacientes (id, nombre, fechaNacimiento, idPaciente, cama, edad, edadMeses, edadDias, diagnosticoMed, prescripcionNutri, area, vip, observaciones, controlTamizaje, creadoPor, statusP) 
                    VALUES ('', '$nombre', '$fechaNacimiento', '$idPaciente', '$cama', '$edadAnios', '$edadMeses', '$edadDias', '$diagnosticoMed', '$prescripcionNutri', 'UCIA', '$vip', '$observaciones', '$controlTamizaje', '$sesionNombre', 'Alta')";

                // Imprimir la consulta SQL para depuración
                echo "Consulta SQL: $sql<br>";

                $query = mysqli_query($con, $sql);

                if (!$query) {
                    echo "Error en la consulta: " . mysqli_error($con) . "<br>";
                } else {
                    echo "Datos insertados correctamente.<br>";
                }
            } else {
                echo "Fila con datos insuficientes: " . implode(", ", $row) . "<br>";
            }
        }

        echo "Proceso de importación completado.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
