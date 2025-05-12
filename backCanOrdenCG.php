<?php
include('conexion.php');
$ticket = $_POST['id'];
$update = "UPDATE ticket SET ticket.Visto_Bueno = 'Aplicado', ticket.Estatus = 'Resuelto', ticket.Fecha_Hora_VoBo = '$fechaHoraActual' WHERE ticket.Ticket = '$ticket'";
$query = mysqli_query($con, $update);
if ($query) {
    $return = "OrdenCancelada";
}
echo json_encode($return);
mysqli_close($con);
?>  