$(document).ready(function () {
    $('.btn.btn-danger').click(function () {
        Swal.fire({
            title: "¿Deseas eliminar este usuario?",
            text: "¡Valida antes de realizar la accion!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Si, aplicar!"
        }).then((result) => {
            if (result.isConfirmed) {
                var id = $(this).data('id');
                $.ajax({
                    url: 'backCanOrdenCG.php',
                    type: 'post',
                    data: { id: id },
                    dataType: 'json',
                    success: function (response) {
                        if (response === "OrdenCancelada") {
                            Swal.fire({
                                position: "top-center",
                                icon: "success",
                                title: "Orden Cancelada",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(resultado => {
                                window.location.reload();
                                //window.location.replace("http://10.1.7.240:8080/dietas/adminUsuario.php");
                                //window.location.replace("http://10.1.7.169/dietas/adminUsuario.php");
                                //window.location.replace("http://localhost/dietas/adminUsuario.php");

                                
                            });
                        }
                    }
                });
            }
        });
    });
});