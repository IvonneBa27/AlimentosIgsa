
document.addEventListener('DOMContentLoaded', function () {
    const areaSelect = document.getElementById('areaSelect');
    const camaSelect = document.getElementById('camaSelect');

    if (areaSelect) {
        areaSelect.addEventListener('change', function () {
            const area = this.value;

            fetch('getCamas.php?area=' + encodeURIComponent(area))
                .then(response => response.json())
                .then(data => {
                    camaSelect.innerHTML = '';
                    data.forEach(cama => {
                        const option = document.createElement('option');
                        option.value = cama;
                        option.textContent = cama;
                        camaSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar camas:', error);
                });
        });
    }
});







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
                //window.location.replace("http://localhost/AlimentosIgsa/pacientes.php");
                window.location.replace("http://10.1.7.230:8080/Alimentos/pacientes.php");
            } else {
                alert(data.message); // El ID ingresado para paciente ya existe, valide los datos
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        });
});






