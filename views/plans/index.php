<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">GeoPlans</a>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Filtrar Planes</h2>
                <form method="GET" class="row g-3 bg-white p-3 border rounded shadow-sm">
                    <div class="col-md-4">
                        <label class="form-label">Categoría (ID)</label>
                        <input type="number" name="category" class="form-control"
                            value="<?= htmlspecialchars((string) ($filters['category_id'] ?? '')) ?>"
                            placeholder="Ej: 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio Máximo (€)</label>
                        <input type="number" step="0.01" name="price" class="form-control"
                            value="<?= htmlspecialchars((string) ($filters['max_price'] ?? '')) ?>" placeholder="Ej: 50">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                        <a href="/" class="btn btn-secondary ms-2">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Listado de Eventos</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Ubicación</th>
                                        <th>Precio</th>
                                        <th>Fecha</th>
                                        <th>Categoría</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($plans)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No se encontraron planes.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($plans as $plan): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($plan['title']) ?></strong></td>
                                                <td><?= htmlspecialchars($plan['location']) ?></td>
                                                <td><?= number_format((float) $plan['price'], 2) ?>€</td>
                                                <td><?= date('d/m/Y H:i', strtotime($plan['date'])) ?></td>
                                                <td><span
                                                        class="badge bg-info text-dark"><?= htmlspecialchars($plan['category_name']) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Estadísticas</h5>
                        <!-- Canvas para Chart.js -->
                        <canvas id="plansChart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('plansChart').getContext('2d');

            // 1. Fetch a la API interna
            fetch('/api/plans')
                .then(response => {
                    if (!response.ok) throw new Error('Error en la red');
                    return response.json();
                })
                .then(json => {
                    if (json.status === 'success') {
                        renderChart(json.data);
                    }
                })
                .catch(error => console.error('Error cargando la API:', error));

            /**
             * Procesa los datos y genera la gráfica
             */
            function renderChart(plans) {
                // Contar planes por categoría
                const categoriesMap = {};

                plans.forEach(plan => {
                    const catName = plan.category_name || 'Sin Categoría';
                    categoriesMap[catName] = (categoriesMap[catName] || 0) + 1;
                });

                const labels = Object.keys(categoriesMap);
                const dataValues = Object.values(categoriesMap);

                new Chart(ctx, {
                    type: 'doughnut', // Gráfico circular para distribución
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Cantidad de Planes',
                            data: dataValues,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            title: {
                                display: true,
                                text: 'Distribución por Categoría'
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>