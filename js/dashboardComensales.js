document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Cambiado de 'line' a 'bar'
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: '#007bff', // Color de las barras
                borderColor: '#007bff',
                borderWidth: 1
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
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
  
    function obtenerDatos() {
        fetch('datosDashboardComensal.php')
            .then(response => response.json())
            .then(data => {
                //const labels = data.map(item => item.estatus);
                const labels = data.map(item => 'IGSA');
                const cantidades = data.map(item => item.cantidad_usuarios);
  
                myChart.data.labels = labels;
                myChart.data.datasets[0].data = cantidades;
                myChart.update();
            })
            .catch(error => console.error('Error al obtener los datos:', error));
    }
  
    obtenerDatos();
    setInterval(obtenerDatos, 5000); // Actualiza cada 5 segundos
  });
  