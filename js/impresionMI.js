$(document).ready(function() {
    $('#formImpresora').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'datosEtiquetaMI.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log(response); 
                alert('Etiquetas generadas e impresas correctamente.');
                window.location.replace("http://10.1.7.230:8080/Alimentos/dietasMI.php");
                //window.location.replace("http://localhost/Alimentos/dietasCG.php");
            },
            error: function() {
                alert('Error al generar las etiquetas.');
            }
        });
    });
}); 




function printLabel() {
    $('#ModalImpresora').modal('show');
}

function CancelarImpresion() {
    
}

