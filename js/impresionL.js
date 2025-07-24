$(document).ready(function() {
    $('#formImpresora').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'datosEtiquetaL.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log(response); 
                alert('Etiquetas generadas e impresas correctamente.');
                window.location.replace("http://10.1.7.230:8080/Alimentos/dietasL.php");
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



