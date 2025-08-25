/*$(document).ready(function() {
    $('#formImpresora').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'datosEtiquetaVIP.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log(response); 
                alert('Etiquetas generadas e impresas correctamente.');
                window.location.replace("http://10.1.7.230:8080/Alimentos/dietasVIP.php");
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
    
}*/


function CancelarImpresion() {
    // Cierra el modal (por si no se cierra automáticamente)
    const modal = bootstrap.Modal.getInstance(document.getElementById('ModalImpresora'));
    if (modal) modal.hide();

    // Limpia las selecciones del select múltiple
    const select = document.getElementById('selectPacientes');
    if (select) {
        for (let option of select.options) {
            option.selected = false;
        }

        // Si estás usando Select2, también resetea visualmente
        if ($(select).hasClass("select2-hidden-accessible")) {
            $(select).val(null).trigger("change");
        }
    }

    // También puedes limpiar otros campos si es necesario
    document.getElementById('selectTipo').selectedIndex = 0;
    document.getElementById('selectPacientes').selectedIndex = 0;
    document.getElementById('formImpresora').reset();
}



function printLabel() {
    // Abre el modal
    const modal = new bootstrap.Modal(document.getElementById('ModalImpresora'));
    modal.show();
}

