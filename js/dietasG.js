function obtenerFechaHoraDelServidor() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fechaHora.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var fechaHoraActual = new Date(xhr.responseText);
            document.getElementById('fechaHoraActual').value = fechaHoraActual;
        }
    };
    xhr.send();
    setTimeout(obtenerFechaHoraDelServidor, 1000);
}
// Llamada inicial al cargar la página
window.onload = obtenerFechaHoraDelServidor;

document.querySelectorAll('#openModal').forEach(function(element) {
    element.addEventListener('click', function () {
        var idPaciente = this.getAttribute('data-id');
        document.getElementById('idPacienteModal').value = idPaciente;
        var myModal = new bootstrap.Modal(document.getElementById('Modalnota'));
        myModal.show();
    });
});

function CancelarNotas() {
    $('#Modalnota').modal('hide');
    $("#usuNotas").val('');
    $("#idPacienteModal").val('');
    $("#addnota").val('');
}

function guardarNota() {
    var idPaciente = document.getElementById('idPacienteModal').value;
    var nota = document.getElementById('addnota').value;
    var usuario = document.getElementById('usuNotas').value;
    var fechaHora = document.getElementById('fechaHoraActual').value;

    // Enviar los datos a PHP usando AJAX
    $.ajax({
        url: 'addNota.php',
        type: 'POST',
        data: {
            idPaciente: idPaciente,
            nota: nota,
            usuario: usuario,
            fechaHora: fechaHora
        },
        success: function(response) {
            // Manejar la respuesta del servidor
            alert('Nota guardada exitosamente');
            $('#Modalnota').modal('hide');
            $("#addnota").val('');
            location.reload();
        },
        error: function() {
            alert('Error al guardar la nota');
        }
    });
}
