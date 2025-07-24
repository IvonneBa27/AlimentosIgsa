var rangoHoraActual = '';

function obtenerFechaHoraDelServidor() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fechaHora.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var fechaHoraActual = new Date(xhr.responseText);
            document.getElementById('fechaHoraActual').value = fechaHoraActual;
            rangoHoraActual = verificarRangoHora(fechaHoraActual);
            console.log('Rango Hora Actual:', rangoHoraActual); // Para depuración
            resaltarTextarea(rangoHoraActual);
        }
    };
    xhr.send();
    setTimeout(obtenerFechaHoraDelServidor, 1000);
}
// Llamada inicial al cargar la página
window.onload = obtenerFechaHoraDelServidor;

function verificarRangoHora(fechaHora) {
    var rangos = [
        { inicio: '07:00', fin: '08:30', campo: 'Desayuno' },
        { inicio: '09:30', fin: '11:30', campo: 'Col_Matutina' },
        { inicio: '11:00', fin: '13:30', campo: 'Comida' },
        { inicio: '14:30', fin: '16:00', campo: 'Col_Vespertina' },
        { inicio: '16:30', fin: '17:30', campo: 'Cena' },
        { inicio: '20:00', fin: '21:00', campo: 'Col_Nocturna' }
    ];

    var horaActual = ('0' + fechaHora.getHours()).slice(-2) + ':' + ('0' + fechaHora.getMinutes()).slice(-2);
    console.log('Hora Actual:', horaActual); // Para depuración

    for (var i = 0; i < rangos.length; i++) {
        if (horaActual >= rangos[i].inicio && horaActual <= rangos[i].fin) {
            return rangos[i].campo;
        }
    }
    return '';
}


function resaltarTextarea(rango) {
    console.log('Rango recibido para resaltar:', rango); // Depuración

    // Primero, elimina el estilo de resaltado de todos los td
    var tds = document.querySelectorAll('td');
    tds.forEach(function (td) {
        td.style.border = ''; // Elimina el borde
    });

    // Luego, agrega el estilo de resaltado a los td correspondientes
    if (rango) {
        var tds = document.querySelectorAll('td[data-campo="' + rango + '"]');
        //console.log('TDs encontrados:', tds); // Depuración

        if (tds.length > 0) {
            // Aplica el borde superior al primer td
            tds[0].style.borderTop = '5px solid green';
            //console.log('Borde superior aplicado al primer td:', tds[0]); // Depuración

            // Aplica el borde inferior al último td
            tds[tds.length - 1].style.borderBottom = '5px solid green';
            //console.log('Borde inferior aplicado al último td:', tds[tds.length - 1]); // Depuración

            // Aplica los bordes laterales a todos los td
            tds.forEach(function (td) {
                td.style.borderLeft = '5px solid green';
                td.style.borderRight = '5px solid green';
                //console.log('Bordes laterales aplicados a:', td); // Depuración
            });
        } else {
            console.log('No se encontró td para el rango:', rango); // Depuración
        }
    }
}


function toggleEdit(checkbox) {
    var row = checkbox.closest('tr');
    var textareas = row.querySelectorAll('textarea');
    textareas.forEach(function (textarea) {
        var campo = textarea.getAttribute('data-campo');
        if (campo === rangoHoraActual) {
            textarea.disabled = !checkbox.checked;
            if (checkbox.checked) {
                textarea.classList.remove('form-control-plaintext');
                textarea.classList.add('form-control');
            } else {
                textarea.classList.remove('form-control');
                textarea.classList.add('form-control-plaintext');
                // Enviar cambios al servidor
                var idPaciente = row.querySelector('td[data-campo="idPaciente"]').innerText;
                var datos = {
                    nombre: row.querySelector('td[data-campo="nombre"]').innerText,
                    fechaNacimiento: row.querySelector('td[data-campo="fechaNacimiento"]').innerText,
                    cama: row.querySelector('td[data-campo="cama"]').innerText,
                    edad: row.querySelector('td[data-campo="edad"]').innerText,
                    diagnosticoMed: row.querySelector('td[data-campo="diagnosticoMed"]').innerText,
                    prescripcionNutri: row.querySelector('td[data-campo="prescripcionNutri"]').innerText,
                    Desayuno: row.querySelector('textarea[data-campo="Desayuno"]').value,
                    Col_Matutina: row.querySelector('textarea[data-campo="Col_Matutina"]').value,
                    Comida: row.querySelector('textarea[data-campo="Comida"]').value,
                    Col_Vespertina: row.querySelector('textarea[data-campo="Col_Vespertina"]').value,
                    Cena: row.querySelector('textarea[data-campo="Cena"]').value,
                    Col_Nocturna: row.querySelector('textarea[data-campo="Col_Nocturna"]').value,
                    observaciones: row.querySelector('textarea[data-campo="observaciones"]').value,
                    controlTamizaje: row.querySelector('textarea[data-campo="controlTamizaje"]').value,
                    vip: row.querySelector('td[data-campo="vip"]').innerText,
                    usuario: row.querySelector('td[data-campo="usuario"]').innerText,
                    fechaHoraActual: row.querySelector('td[data-campo="fechaHoraActual"]').innerText
                };
                guardarCambio(idPaciente, datos);
            }
        } else {
            textarea.disabled = true;
            textarea.classList.remove('form-control');
            textarea.classList.add('form-control-plaintext');
        }
    });
}


function guardarCambio(idPaciente, datos) {
    // Mostrar mensaje de confirmación
    var confirmar = confirm("¿Estás seguro de que deseas guardar los cambios?");

    if (confirmar) {
        datos.rangoHoraActual = rangoHoraActual; // Añadir el rango de hora actual a los datos

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'formG.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log('Cambio guardado:', datos);
            }
        };
        xhr.send('idPaciente=' + idPaciente + '&datos=' + encodeURIComponent(JSON.stringify(datos)));
    } else {
        // Recargar la página si se cancela la confirmación
        location.reload();
    }
}







function openModal() {
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
}

function submitForm() {
    var input = document.getElementById('fileInputAddPaciente');
    if (input.files.length > 0) {
        var formData = new FormData();
        formData.append('file', input.files[0]);

        fetch('importPacienteG.php', {
            method: 'POST',
            body: formData
        }).then(response => response.text())
            .then(text => {
                if (text.includes('Datos insertados correctamente.')) {
                    alert('Archivo subido exitosamente.');
                    window.location.reload();
                    //window.location.replace("http://localhost/dietas/formG.php");
                    //window.location.replace("http://10.1.7.240:8080/dietas/formG.php");
                    //window.location.replace("http://10.1.7.169/dietas/formG.php");

                } else if (text.includes('El ID ingresado para paciente ya existe')) {
                    alert(text);
                } else if (text.includes('Las siguientes camas no existen en el área de GINECOLOGÍA')) {
                    alert(text);
                } else if (text.includes('La cama ingresada ya existe en el área de GINECOLOGÍA')) {
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
                edadAnios: $('#edadAnios').val(),
                edadMeses: $('#edadMeses').val(),
                edadDias: $('#edadDias').val(),
                diagMed: $('#diagMed').val(),
                presNutri: $('#presNutri').val(),
                vip: $('#vip').val(),
                observaciones: $('#observaciones').val(),
                controlTami: $('#controlTami').val(),
                nombreUsuario: $('#nombreUsuario').val(),
                statusP: $('#statusP').val()
            };
            // Enviar los datos al servidor usando AJAX
            $.ajax({
                type: 'POST',
                url: 'addPacienteG.php',
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
                        //window.location.replace("http://localhost/dietas/formG.php");
                        //window.location.replace("http://10.1.7.240:8080/dietas/formG.php");
                        //window.location.replace("http://10.1.7.169/dietas/formG.php");
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




document.getElementById('fechaNac').addEventListener('change', function () {
    const fechaNac = new Date(this.value + 'T00:00:00');
    const hoy = new Date();

    let edadAnios = hoy.getFullYear() - fechaNac.getFullYear();
    let edadMeses = hoy.getMonth() - fechaNac.getMonth();
    let edadDias = hoy.getDate() - fechaNac.getDate();

    // Ajustar los días y meses si es necesario
    if (edadDias < 0) {
        edadMeses--;
        const ultimoDiaMesAnterior = new Date(hoy.getFullYear(), hoy.getMonth(), 0).getDate();
        edadDias += ultimoDiaMesAnterior;
    }

    if (edadMeses < 0) {
        edadAnios--;
        edadMeses += 12;
    }

    // Mostrar los resultados
    document.getElementById('edadAnios').value = edadAnios;
    document.getElementById('edadMeses').value = edadMeses;
    document.getElementById('edadDias').value = edadDias;
});







document.getElementById('configLink').addEventListener('click', function (event) {
    event.preventDefault();
    var myModal = new bootstrap.Modal(document.getElementById('myModalUpdate'));
    myModal.show();
});

document.getElementById('showPasswords').addEventListener('change', function () {
    var passwordFields = document.querySelectorAll('#actual, #nueva');
    passwordFields.forEach(function (field) {
        field.type = this.checked ? 'text' : 'password';
    }, this);
});

document.getElementById('cancelButton').addEventListener('click', function () {
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