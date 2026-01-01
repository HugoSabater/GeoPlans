<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">📍 GeoPlans</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="/">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="/api/plans" target="_blank">API JSON</a></li>
                    <li class="nav-item"><a class="nav-link" href="/docs/index.html" target="_blank">Docs</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 mb-4 position-sticky" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-sliders"></i> Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="/">
                            <div class="mb-3">
                                <label for="category"
                                    class="form-label small text-muted text-uppercase fw-bold">Categoría</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="1" <?= ($filters['category_id'] ?? '') == '1' ? 'selected' : '' ?>>
                                        Música</option>
                                    <option value="2" <?= ($filters['category_id'] ?? '') == '2' ? 'selected' : '' ?>>
                                        Teatro</option>
                                    <option value="3" <?= ($filters['category_id'] ?? '') == '3' ? 'selected' : '' ?>>Cine
                                    </option>
                                    <option value="4" <?= ($filters['category_id'] ?? '') == '4' ? 'selected' : '' ?>>
                                        Gastronomía</option>
                                    <option value="5" <?= ($filters['category_id'] ?? '') == '5' ? 'selected' : '' ?>>Aire
                                        Libre</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="price" class="form-label small text-muted text-uppercase fw-bold">Precio
                                    Máximo</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" name="price" id="price" class="form-control"
                                        value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>"
                                        placeholder="Ej: 50">
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                                <a href="/" class="btn btn-outline-secondary btn-sm">Limpiar búsqueda</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas (Mini) -->
                <div class="card shadow-sm border-0 d-none d-md-block">
                    <div class="card-body">
                        <h6 class="card-title text-center text-muted small text-uppercase fw-bold mb-3">Estadísticas
                        </h6>
                        <canvas id="plansChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Listado de Planes -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Explorar Eventos</h2>
                    <span class="text-muted small">Mostrando <?= count($plans) ?> resultados</span>
                </div>

                <?php if (empty($plans)): ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-search display-4 d-block mb-3"></i>
                        <h4>No hemos encontrado planes</h4>
                        <p class="text-muted">Intenta ajustar los filtros de búsqueda.</p>
                        <a href="/" class="btn btn-primary mt-2">Ver todos los planes</a>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($plans as $plan): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0 plan-card">
                                    <!-- Imagen Inteligente -->
                                    <div class="position-relative">
                                        <img src="https://picsum.photos/seed/<?= $plan['id'] ?>/400/250" class="card-img-top"
                                            alt="Imagen del evento" style="height: 200px; object-fit: cover;">
                                        <span class="position-absolute top-0 end-0 badge bg-primary m-2 shadow-sm">
                                            <?= htmlspecialchars($plan['category_name'] ?? 'General') ?>
                                        </span>
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-truncate-2 mb-2"
                                            title="<?= htmlspecialchars($plan['title']) ?>">
                                            <?= htmlspecialchars($plan['title']) ?>
                                        </h5>

                                        <div class="mb-3">
                                            <?php if ((float) $plan['price'] == 0): ?>
                                                <span class="badge bg-success rounded-pill">Gratis</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-secondary rounded-pill"><?= number_format((float) $plan['price'], 2) ?>
                                                    €</span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="card-text text-muted small mb-3 flex-grow-1">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <?= htmlspecialchars($plan['location'] ?? 'Online') ?><br>
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <?= date('d/m/Y', strtotime($plan['date'])) ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="btn-group">
                                                <?php if (!empty($plan['url_source'])): ?>
                                                    <a href="<?= htmlspecialchars($plan['url_source']) ?>" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" title="Ver Fuente">
                                                        <i class="bi bi-link-45deg"></i> Ver
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/plan/delete?id=<?= $plan['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('¿Estás seguro de borrar este plan?');"
                                                    title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                            <small class="text-muted">#<?= $plan['id'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                        <nav aria-label="Navegación de planes" class="mt-5">
                            <ul class="pagination pagination-sm justify-content-center">
                                <?php
                                $params = $_GET;
                                unset($params['page']);
                                $qs = http_build_query($params);
                                $prefix = $qs ? "?{$qs}&" : "?";
                                ?>

                                <li class="page-item <?= $pagination['has_prev'] ? '' : 'disabled' ?>">
                                    <a class="page-link" href="<?= $prefix ?>page=<?= $pagination['prev'] ?>">
                                        <i class="bi bi-chevron-left"></i> Anterior
                                    </a>
                                </li>

                                <li class="page-item disabled">
                                    <span class="page-link text-muted">
                                        <?= $pagination['current'] ?> / <?= $pagination['total_pages'] ?>
                                    </span>
                                </li>

                                <li class="page-item <?= $pagination['has_next'] ? '' : 'disabled' ?>">
                                    <a class="page-link" href="<?= $prefix ?>page=<?= $pagination['next'] ?>">
                                        Siguiente <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('plansChart').getContext('2d');

            fetch('/api/plans')
                .then(response => response.json())
                .then(json => {
                    if (json.status === 'success') {
                        renderChart(json.data);
                    }
                })
                .catch(error => console.error('Error API:', error));

            function renderChart(plans) {
                const categoriesMap = {};
                plans.forEach(plan => {
                    const catName = plan.category_name || 'Sin Categoría';
                    categoriesMap[catName] = (categoriesMap[catName] || 0) + 1;
                });

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(categoriesMap),
                        datasets: [{
                            data: Object.values(categoriesMap),
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                            title: { display: false }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
</body>

</html>