
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                lineTension: 0,
                backgroundColor: 'transparent',
                borderColor: '#007bff',
                borderWidth: 4,
                pointBackgroundColor: '#007bff'
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    boxPadding: 3
                }
            }
        }
    });

    function obtenerDatos(fechaFiltro) {
        fetch(`datosDashboardG.php?fechaFiltro=${fechaFiltro}`)
            .then(response => response.json())
            .then(data => {
                const labels = data.map(item => item.nombre_dia);
                const cantidades = data.map(item => item.cantidad);

                myChart.data.labels = labels;
                myChart.data.datasets[0].data = cantidades;
                myChart.update();
            })
            .catch(error => console.error('Error al obtener los datos:', error));
    }

    const fechaFiltroInput = document.getElementById('fechaFiltro');
    fechaFiltroInput.addEventListener('change', function() {
        obtenerDatos(this.value);
    });

    obtenerDatos(fechaFiltroInput.value);
    setInterval(() => obtenerDatos(fechaFiltroInput.value), 5000); // Actualiza cada 5 segundos
});








document.getElementById('configLink').addEventListener('click', function(event) {
    event.preventDefault();
    var myModal = new bootstrap.Modal(document.getElementById('myModalUpdate'));
    myModal.show();
});

document.getElementById('showPasswords').addEventListener('change', function() {
    var passwordFields = document.querySelectorAll('#actual, #nueva');
    passwordFields.forEach(function(field) {
        field.type = this.checked ? 'text' : 'password';
    }, this);
});

document.getElementById('cancelButton').addEventListener('click', function() {
    // Limpiar los campos de entrada
    document.getElementById('myFormUpdate').reset();
    // Cerrar el modal
    $('#myModalUpdate').modal('hide');
});


$(document).ready(function () {
    // Cerrar el modal al presionar los botones de cerrar
    $('.close, .btn-danger').click(function () {
        $('#myModalUpdate').modal('hide');
    });

    // Limpiar el formulario cuando el modal se oculta
    $('#myModalUpdate').on('hidden.bs.modal', function () {
        document.getElementById('myFormUpdate').reset();
        $('#myFormUpdate input').removeClass('is-invalid is-valid');
    });

    // Obtener y validar los datos del formulario al presionar "Actualizar"
    $('#update').click(function (event) {
        event.preventDefault(); // Evitar el envío del formulario por defecto
        // Validar que todos los campos requeridos estén completos
        let isValid = true;
        $('#myFormUpdate input').each(function () {
            if ($(this).prop('required') && $(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid').removeClass('is-valid');
            } else {
                $(this).addClass('is-valid').removeClass('is-invalid');
            }
        });

        if (isValid) {
            // Obtener los datos del formulario
            let formData = {
                actual: $('#actual').val(),
                nueva: $('#nueva').val(),
                usuarioUpdate: $('#usuarioUpdate').val()
            };
            // Enviar los datos al servidor usando AJAX
            $.ajax({
                type: 'POST',
                url: 'updatePassword.php',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log(response); // Agregar log para depuración
                    if (response.status === 'error') {
                        alert(response.message);
                    } else {
                        alert(response.message);
                        // Cerrar el modal
                        $('#myModalUpdate').modal('hide');
                        // Redirigir a la página de confirmación o recargar la página
                        window.location.reload();
                    }
                },
                error: function () {
                    alert('Error al enviar los datos.');
                }
            });
        } else {
            alert('Por favor, completa todos los campos requeridos.');
        }
    });
});
    
  
