var rangoHoraActual = '';

function obtenerFechaHoraDelServidor() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fechaHora.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log('Respuesta del servidor:', xhr.responseText); // Depuración
            var fechaHoraActual = new Date(xhr.responseText);
            console.log('Fecha y Hora Actual:', fechaHoraActual); // Depuración
            document.getElementById('fechaHoraActual').value = fechaHoraActual;
            rangoHoraActual = verificarRangoHora(fechaHoraActual);
            verificarYActualizarColumnaBloqueo(fechaHoraActual); //Aquí se llama
            //console.log('Rango Hora Actual:', rangoHoraActual); // Para depuración
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

        { inicio: '07:00', fin: '08:00', campo: 'Desayuno' },
        { inicio: '08:01', fin: '10:30', campo: 'Col_Matutina' },
        { inicio: '10:31', fin: '12:30', campo: 'Comida' },
        { inicio: '12:31', fin: '16:00', campo: 'Col_Vespertina' },
        { inicio: '16:01', fin: '17:30', campo: 'Cena' },
        { inicio: '17:31', fin: '21:00', campo: 'Col_Nocturna' }

    ];

    var horaActual = ('0' + fechaHora.getHours()).slice(-2) + ':' + ('0' + fechaHora.getMinutes()).slice(-2);
    console.log('Hora Actual:', horaActual); // Para depuración

    for (var i = 0; i < rangos.length; i++) {
        if (horaActual >= rangos[i].inicio && horaActual <= rangos[i].fin) {
            console.log('Rango encontrado:', rangos[i].campo); // Depuración
            return rangos[i].campo;
        }
    }
    console.log('No se encontró un rango correspondiente'); // Depuración
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
    // Guardar la posición del scroll antes de cualquier acción
    var contenedor = document.getElementById('contenedorScroll');
    localStorage.setItem('scrollTopContenedor', contenedor.scrollTop);
    // Mostrar mensaje de confirmación
    var confirmar = confirm("¿Estás seguro de que deseas guardar los cambios?");

    if (confirmar) {
        datos.rangoHoraActual = rangoHoraActual; // Añadir el rango de hora actual a los datos

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'formCG.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log('Cambio guardado:', datos);
                location.reload();
            }
        };
        xhr.send('idPaciente=' + idPaciente + '&datos=' + encodeURIComponent(JSON.stringify(datos)));
    } else {
        // Recargar la página si se cancela la confirmación
        location.reload();
    }
}


window.addEventListener('load', function () {
    var contenedor = document.getElementById('contenedorScroll');
    var scrollTop = localStorage.getItem('scrollTopContenedor');
    if (scrollTop !== null) {
        contenedor.scrollTop = parseInt(scrollTop);
        localStorage.removeItem('scrollTopContenedor');
    }
});

let columnaBloqueoVisible = false;

function verificarYActualizarColumnaBloqueo(fechaHoraActual) {
    const hora = fechaHoraActual.getHours();
    const minutos = fechaHoraActual.getMinutes();
    const dentroDelRango = (hora > 10 || (hora === 10 && minutos >= 10)) && hora < 21;

    const tabla = document.getElementById("miTabla");

    if (dentroDelRango && !columnaBloqueoVisible) {
        const theadRows = tabla.querySelectorAll("thead tr");
        theadRows.forEach((tr, index) => {
            const th = document.createElement("th");
            th.className = "text-center columna-bloqueo";

            if (index === 0) {
                th.innerHTML = `<span>Solicitud día siguiente</span><br><button class="btn btn-success btn-sm mt-1" onclick="enviarDatosBloqueo()">Enviar datos</button>`;
            } else {
                th.textContent = "Desayuno";
            }

            tr.appendChild(th);
        });

        const filas = tabla.querySelectorAll("tbody tr");
        filas.forEach(fila => {
            const td = document.createElement("td");
            td.className = "text-center columna-bloqueo";
            td.innerHTML = `<textarea class="form-control table-textarea" placeholder="Desayuno.." data-campo="bloqueo"></textarea>`;
            fila.appendChild(td);
        });

        columnaBloqueoVisible = true;
    }
}

function enviarDatosBloqueo() {
    const tabla = document.getElementById("miTabla");
    const filas = tabla.querySelectorAll("tbody tr");
    const datos = [];

    filas.forEach(fila => {
        const getCampo = campo => {
            const celda = fila.querySelector(`td[data-campo="${campo}"]`);
            return celda ? celda.textContent.trim() : "";
        };

        const getInputCampo = campo => {
            const celda = fila.querySelector(`td[data-campo="${campo}"]`);
            const input = celda ? celda.querySelector("input") : null;
            return input ? input.value.trim() : "";
        };

        const getTextarea = campo => {
            const textarea = fila.querySelector(`textarea[data-campo="${campo}"]`);
            return textarea ? textarea.value.trim() : "";
        };

        const registro = {
            idPaciente: getCampo("idPaciente"),
            nombre: getCampo("nombre"),
            fechaNacimiento: getCampo("fechaNacimiento"),
            cama: getCampo("cama"),
            edad: getCampo("edad"),
            edadMeses: getCampo("edadMeses"),
            edadDias: getCampo("edadDias"),
            diagnosticoMed: getCampo("diagnosticoMed"),
            prescripcionNutri: getCampo("prescripcionNutri"),
            observaciones: getCampo("observaciones"),
            controlTamizaje: getCampo("controlTamizaje"),
            vip: getCampo("vip"),
            usuario: getCampo("usuario"),
            desayuno: getTextarea("bloqueo")
        };

        if (registro.idPaciente && registro.desayuno !== "") {
            datos.push(registro);
        }
    });

    if (datos.length > 0) {
        fetch("guardar_bloqueosCG.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ registros: datos })
        })
            .then(response => response.json())
            .then(data => {
                console.log("Datos guardados:", data);
                alert("Datos enviados correctamente");

                // Recargar los datos en los textarea sin recargar la página
                cargarDatosBloqueo();


                // Eliminar columna después de enviar
                //const columnas = tabla.querySelectorAll(".columna-bloqueo");
                //columnas.forEach(col => col.remove());
                //columnaBloqueoVisible = false;11
            })
            .catch(error => {
                console.error("Error al guardar los datos:", error);
                alert("Error al enviar los datos");
            });
    } else {
        alert("No hay datos para enviar.");
    }
}




window.addEventListener("DOMContentLoaded", () => {
    fetch("guardar_bloqueosCG.php") 
        .then(response => response.json())
        .then(data => {
            const filas = document.querySelectorAll("#miTabla tbody tr");

            filas.forEach(fila => {
                const idPaciente = fila.querySelector('td[data-campo="idPaciente"]').textContent.trim();
                const desayuno = data[idPaciente];

                if (desayuno !== undefined) {
                    const textarea = fila.querySelector('textarea[data-campo="bloqueo"]');
                    if (textarea) {
                        textarea.value = desayuno;
                    }
                }
            });
        })
        .catch(error => {
            console.error("Error al cargar los datos:", error);
        });
});





function cargarDatosBloqueo() {
    fetch("guardar_bloqueosCG.php") // o "obtener_bloqueosCG.php" si lo tienes separado
        .then(response => response.json())
        .then(data => {
            const filas = document.querySelectorAll("#miTabla tbody tr");

            filas.forEach(fila => {
                const idPaciente = fila.querySelector('td[data-campo="idPaciente"]').textContent.trim();
                const desayuno = data[idPaciente];

                if (desayuno !== undefined) {
                    const textarea = fila.querySelector('textarea[data-campo="bloqueo"]');
                    if (textarea) {
                        textarea.value = desayuno;
                    }
                }
            });
        })
        .catch(error => {
            console.error("Error al cargar los datos:", error);
        });
}
window.addEventListener("DOMContentLoaded", cargarDatosBloqueo);













function openModal() {
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
}

function submitForm() {
    var input = document.getElementById('fileInputAddPaciente');
    if (input.files.length > 0) {
        var formData = new FormData();
        formData.append('file', input.files[0]);

        fetch('importPacienteCG.php', {
            method: 'POST',
            body: formData
        }).then(response => response.text())
            .then(text => {
                if (text.includes('Datos insertados correctamente.')) {
                    alert('Archivo subido exitosamente.');
                    window.location.reload();
                    //window.location.replace("http://localhost/dietas/formCG.php");
                    //window.location.replace("http://10.1.7.240:8080/dietas/formCG.php");
                    //window.location.replace("http://10.1.7.169/dietas/formCG.php");
                } else if (text.includes('El ID ingresado para paciente ya existe')) {
                    alert(text);
                } else if (text.includes('Las siguientes camas no existen en el área de CIRUGÍA GENERAL')) {
                    alert(text);
                } else if (text.includes('La cama ingresada ya existe en el área de CIRUGÍA GENERAL')) {
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
                url: 'addPacienteCG.php',
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
                        //window.location.replace("http://localhost/dietas/formCG.php");
                        //window.location.replace("http://10.1.7.241:8080/Alimentos/formCG.php");
                        //window.location.replace("http://10.1.7.169/dietas/formCG.php");
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



/*document.getElementById('fechaNac').addEventListener('change', function () {
    const fechaNac = new Date(this.value);
    const hoy = new Date();
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const mes = hoy.getMonth() - fechaNac.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }
    document.getElementById('edad').value = edad;
});*/


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