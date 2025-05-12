$(document).ready(function () {
    $('.btn.btn-primary').click(function () {
        var id = $(this).data('id');
        $.ajax({
            url: 'notas.php',
            type: 'post',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                console.log(response);
                const notasData = response;
                const modalContent1 = document.getElementById('modal-content1');

                notasData.forEach((nota) => {
                    const notaInp = document.createElement('textarea');
                    notaInp.id = 'miTextarea';
                    notaInp.classList.add('form-control');
                    notaInp.classList.add('fs-5');

                    notaInp.textContent = 'Creado: ' + nota.Fecha_Creacion + '     ' +  'Creado por: ' + nota.Creada_por  + '\n' + 'Nota: ' + nota.Nota;
                    modalContent1.appendChild(notaInp);

                });

                $('#myModal').modal('show');

            }
        });
    });
});


function CerrarModal() {
    $('#myModal').modal('hide');

    // Elimina los divs existentes dentro del modal-content1
    const modalContent1 = document.getElementById('modal-content1');
    while (modalContent1.firstChild) {
        modalContent1.removeChild(modalContent1.firstChild);
    }
}


