  $(document).ready(function () {
    $('#idPaciente, #nombrePaciente').on('keyup', function () {
      let id = $('#idPaciente').val();
      let nombre = $('#nombrePaciente').val();

      $.ajax({
        url: 'buscarPacientes.php',
        method: 'POST',
        data: { id: id, nombre: nombre },
        success: function (data) {
          $('#tablaPacientes').html(data);
        }
      });
    });
  });

