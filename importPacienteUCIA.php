<?php
include('conexion.php');
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario'];
$sesionNombre = $sesi['nombre_completo'];
//$sesionCargo = $sesi[4];
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $inputFileName = $_FILES['file']['tmp_name'];

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


        // Validar todas las camas
        $cama1 = array_column($data, 3);
        $camaStr1 = implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($cama1), $con), $cama1));
        $camaQuery1 = "SELECT numero FROM camas WHERE numero IN ('$camaStr1') AND area = 'UCIA'";
        $checkResultCama1 = mysqli_query($con, $camaQuery1);

        $existingCamas1 = [];
        while ($row = mysqli_fetch_assoc($checkResultCama1)) {
            $existingCamas1[] = $row['numero'];
        }

        // Comparar las camas del Excel con las que sí existen en el área
        $noExisten = array_diff($cama1, $existingCamas1);

        if (!empty($noExisten)) {
            echo "Las siguientes camas no existen en el área de UCIA, valide los datos: " . implode(", ", $noExisten);
            //exit; // Opcional: detener el proceso si hay errores
        } else {

            // Validar todas las camas
            $cama = array_column($data, 3);
            $camaStr = implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($cama), $con), $cama));
            $camaQuery = "SELECT cama FROM pacientes WHERE cama IN ('$camaStr') AND area = 'UCIA' AND statusP = 'Activo'";
            $checkResultCama = mysqli_query($con, $camaQuery);

            if (mysqli_num_rows($checkResultCama) > 0) {
                $existingCamas = [];
                while ($row = mysqli_fetch_assoc($checkResultCama)) {
                    $existingCamas[] = $row['cama'];
                }
                //echo "La cama ingresada ya existe en el área de CIRUGÍA GENERAL, valide los datos" . implode(", ", $existingCamas);
                echo "La cama ingresada ya existe en el área de UCIA, valide los datos: " . implode(", ", $existingCamas);
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
                    VALUES ('', '$nombre', '$fechaNacimiento', '$idPaciente', '$cama', '$edadAnios', '$edadMeses', '$edadDias', '$diagnosticoMed', '$prescripcionNutri', 'UCIA', '$vip', '$observaciones', '$controlTamizaje', '$sesionNombre', 'Activo')";

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
        }
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
