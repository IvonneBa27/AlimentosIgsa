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
        fetch(`datosDashboardUCIA.php?fechaFiltro=${fechaFiltro}`)
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
    fechaFiltroInput.addEventListener('change', function () {
        obtenerDatos(this.value);
    });

    obtenerDatos(fechaFiltroInput.value);
    setInterval(() => obtenerDatos(fechaFiltroInput.value), 5000); // Actualiza cada 5 segundos
});
