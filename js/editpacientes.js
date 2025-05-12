document.getElementById('btnActualizar').addEventListener('click', function (event) {
    event.preventDefault(); // Evita el envío por defecto del formulario

    // Recoge los datos del formulario
    const formData = new FormData(document.getElementById('miFormulario'));

    // Envía los datos usando fetch
    fetch('actPaciente.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Muestra el mensaje según la respuesta
            if (data.status === 'success') {
                alert(data.message); // Paciente actualizado exitosamente
                window.location.replace("http://localhost/Alimentos/pacientes.php");
               // window.location.replace("http://10.1.7.230:8080/Alimentos/pacientes.php");
            } else {
                alert(data.message); // El ID ingresado para paciente ya existe, valide los datos
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        });
});