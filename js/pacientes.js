function openModal() {
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
}

function submitForm() {
    var input = document.getElementById('fileInputAddPaciente');
    if (input.files.length > 0) {
        var formData = new FormData();
        formData.append('file', input.files[0]);

        fetch('importPaciente.php', {
            method: 'POST',
            body: formData
        }).then(response => response.text())
            .then(text => {
                if (text.includes('Datos insertados correctamente.')) {
                    alert('Archivo subido exitosamente.');
                    //window.location.replace("http://localhost/dietas/pacientes.php");
                    window.location.replace("http://10.1.7.230:8080/Alimentos/pacientes.php");
                    //window.location.replace("http://10.1.7.169/dietas/pacientes.php");
                } else if (text.includes('El ID ingresado para paciente ya existe')) {
                    alert(text);
                } else {
                    alert('Error al subir el archivo.');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error al subir el archivo.');
            });
    } else {
        alert('Por favor, selecciona un archivo primero.');
    }
}



function clearFileInputAddPaciente() {
    var input = document.getElementById('fileInputAddPaciente');
    input.value = '';
    document.getElementById('fileNameAddPaciente').textContent = 'No se ha seleccionado ningún archivo.';
}

function updateFileNameAddPaciente() {
    var input = document.getElementById('fileInputAddPaciente');
    var fileName = input.files[0] ? input.files[0].name : 'No se ha seleccionado ningún archivo.';
    document.getElementById('fileNameAddPaciente').textContent = fileName;
}


$(document).ready(function () {
    // Cerrar el modal al presionar los botones de cerrar
    $('.close, .btn-danger').click(function () {
        $('#myModal').modal('hide');
    });
    // Limpiar el formulario cuando el modal se oculta
    $('#myModal').on('hidden.bs.modal', function () {
        document.getElementById('myFormAddPaciente').reset();
        $('#myFormAddPaciente input, #myFormAddPaciente textarea, #myFormAddPaciente select').removeClass('is-invalid is-valid');
    });
    // Obtener y validar los datos del formulario al presionar "Agregar Paciente"
    $('#addPaciente').click(function (event) {
        event.preventDefault(); // Evitar el envío del formulario por defecto
        // Validar que todos los campos requeridos estén completos
        let isValid = true;
        $('#myFormAddPaciente input, #myFormAddPaciente textarea, #myFormAddPaciente select').each(function () {
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
                nombrePaciente: $('#nombrePaciente').val(),
                fechaNac: $('#fechaNac').val(),
                idPaciente: $('#idPaciente').val(),
                cama: $('#cama').val(),
                edad: $('#edad').val(),
                diagMed: $('#diagMed').val(),
                presNutri: $('#presNutri').val(),
                area: $('#area').val(),
                vip: $('#vip').val(),
                observaciones: $('#observaciones').val(),
                controlTami: $('#controlTami').val(),
                nombreUsuario: $('#nombreUsuario').val(),
                statusP: $('#statusP').val()
            };
            // Enviar los datos al servidor usando AJAX
            $.ajax({
                type: 'POST',
                url: 'addPaciente.php',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log(response); // Agregar log para depuración
                    if (response.status === 'error') {
                        alert(response.message);
                        //window.location.replace("http://localhost/dietas/formCG.php");
                    } else {
                        alert(response.message);
                        // Cerrar el modal
                        $('#myModal').modal('hide');
                        // Redirigir a la página de pacientes
                        console.log("Redirigiendo a pacientes.php"); // Agregar log para depuración
                        window.location.reload();
                        //window.location.replace("http://localhost/dietas/pacientes.php");
                        //window.location.replace("http://10.1.7.241:8080/dietas/pacientes.php");
                        //window.location.replace("http://10.1.7.169/dietas/pacientes.php");
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