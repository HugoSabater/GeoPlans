<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoPlans | Agenda Cultural de Madrid</title>
    <!-- Favicon: Un mapa -->
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗺️</text></svg>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Estilos Personalizados */
        :root {
            --bs-primary: #0d6efd;
            /* Azul Corporativo */
            --bs-primary-rgb: 13, 110, 253;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 3em;
            /* Min-height para alinear */
        }

        /* Efecto Hover en Cards */
        .plan-card {
            transition: all 0.3s ease;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .plan-card-img-wrapper {
            overflow: hidden;
            position: relative;
        }

        .plan-card-img-wrapper img {
            transition: transform 0.5s ease;
        }

        .plan-card:hover .plan-card-img-wrapper img {
            transform: scale(1.05);
            /* Zoom sutil */
        }

        /* Paginación Activa Azul Fuerte */
        .page-item.active .page-link {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
            font-weight: bold;
        }

        .page-link {
            color: #0d6efd;
        }

        .page-link:hover {
            background-color: #e9ecef;
            color: #0a58ca;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar Azul Corporativo -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                🗺️ GeoPlans <span class="fw-light small opacity-75">| Agenda Cultural</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active fw-bold" href="/">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="/api/plans" target="_blank"><i
                                class="bi bi-code-slash"></i> API JSON</a></li>
                    <li class="nav-item"><a class="nav-link" href="/docs/index.html" target="_blank"><i
                                class="bi bi-book"></i> Docs</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container con Padding Vertical -->
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Sticky -->
            <div class="col-md-3 mb-4">
                <div class="sticky-top" style="top: 20px; z-index: 90;">

                    <!-- Filtros -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-funnel-fill"></i> Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="/">
                                <div class="mb-3">
                                    <label for="category"
                                        class="form-label small text-muted text-uppercase fw-bold">Categoría</label>
                                    <select name="category" id="category" class="form-select">
                                        <option value="">Todas</option>
                                        <option value="4" <?= ($filters['category_id'] ?? '') == '4' ? 'selected' : '' ?>>
                                            Música</option>
                                        <option value="5" <?= ($filters['category_id'] ?? '') == '5' ? 'selected' : '' ?>>
                                            Teatro</option>
                                        <option value="6" <?= ($filters['category_id'] ?? '') == '6' ? 'selected' : '' ?>>
                                            Cine</option>
                                        <option value="2" <?= ($filters['category_id'] ?? '') == '2' ? 'selected' : '' ?>>
                                            Gastronomía</option>
                                        <option value="7" <?= ($filters['category_id'] ?? '') == '7' ? 'selected' : '' ?>>
                                            Aire Libre</option>
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
                                    <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                                        <i class="bi bi-search"></i> Filtrar Eventos
                                    </button>
                                    <a href="/" class="btn btn-outline-secondary btn-sm">Limpiar búsqueda</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="card shadow-sm border-0 d-none d-md-block">
                        <div class="card-body">
                            <h6 class="card-title text-center text-muted small text-uppercase fw-bold mb-3">Distribución
                            </h6>
                            <canvas id="plansChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Planes -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0 text-dark">Explorar Madrid</h2>
                    <span class="badge bg-light text-dark border shadow-sm">
                        <?= count($plans) ?> resultados / pág
                    </span>
                </div>

                <?php if (empty($plans)): ?>
                    <div class="alert alert-info text-center py-5 shadow-sm border-0">
                        <i class="bi bi-search display-4 d-block mb-3 text-primary"></i>
                        <h4>Vaya... No hemos encontrado planes.</h4>
                        <p class="text-muted">Intenta ajustar los precios o cambiar de categoría.</p>
                        <a href="/" class="btn btn-primary mt-2">Ver todos los planes</a>
                    </div>
                <?php else: ?>
                    <!-- Grid Responsive: 1col móvil, 2col tablet, 3col escritorio -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($plans as $plan): ?>
                            <?php
                            // Lógica de color de Badge
                            $catName = $plan['category_name'] ?? 'General';
                            $badgeClass = match (true) {
                                stripos($catName, 'Música') !== false || stripos($catName, 'Concierto') !== false => 'bg-primary',
                                stripos($catName, 'Teatro') !== false || stripos($catName, 'Danza') !== false || stripos($catName, 'Musical') !== false => 'bg-danger',
                                stripos($catName, 'Arte') !== false || stripos($catName, 'Museo') !== false || stripos($catName, 'Exposición') !== false => 'bg-warning text-dark',
                                stripos($catName, 'Gastronomía') !== false || stripos($catName, 'Comer') !== false => 'bg-success',
                                default => 'bg-secondary'
                            };
                            ?>

                            <div class="col">
                                <div class="card h-100 shadow-sm border-0 plan-card overflow-hidden">
                                    <!-- Imagen con Link -->
                                    <a href="<?= htmlspecialchars($plan['url_source'] ?? '#') ?>" target="_blank"
                                        class="plan-card-img-wrapper d-block position-relative">
                                        <?php $img = !empty($plan['image_url']) ? $plan['image_url'] : "https://picsum.photos/seed/{$plan['id']}/400/250"; ?>
                                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="Imagen del evento"
                                            style="height: 200px; object-fit: cover;"
                                            onerror="this.src='https://via.placeholder.com/400x250?text=Sin+Imagen'">

                                        <!-- Badge Categoría Dinámica -->
                                        <span class="position-absolute top-0 end-0 badge <?= $badgeClass ?> m-2 shadow-sm">
                                            <?= htmlspecialchars($catName) ?>
                                        </span>
                                    </a>

                                    <div class="card-body d-flex flex-column">
                                        <!-- Título -->
                                        <h5 class="card-title fw-bold text-truncate-2 mb-2"
                                            title="<?= htmlspecialchars($plan['title']) ?>">
                                            <a href="<?= htmlspecialchars($plan['url_source']) ?>" target="_blank"
                                                class="text-decoration-none text-dark stretched-link">
                                                <?= htmlspecialchars($plan['title']) ?>
                                            </a>
                                        </h5>

                                        <!-- Detalles -->
                                        <div class="mb-3">
                                            <?php if ((float) $plan['price'] == 0): ?>
                                                <span class="badge bg-success rounded-pill px-3">GRATIS</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-secondary rounded-pill px-3"><?= number_format((float) $plan['price'], 2) ?>
                                                    €</span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="card-text text-muted small mb-3 flex-grow-1">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <?= htmlspecialchars($plan['location'] ?? 'Madrid') ?><br>
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <?= date('d/m/Y', strtotime($plan['date'])) ?>
                                        </p>

                                        <!-- Acciones (Z-index high para estar sobre stretched link) -->
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top position-relative"
                                            style="z-index: 2;">
                                            <a href="/plan/delete?id=<?= $plan['id'] ?>"
                                                class="btn btn-sm btn-outline-danger border-0"
                                                onclick="return confirm('¿Estás seguro de borrar este plan?');"
                                                title="Eliminar Plan">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">#ID
                                                <?= $plan['id'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación Numerada -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                        <nav aria-label="Navegación de planes" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php
                                // Preservar filtros en la URL
                                $params = $_GET;
                                unset($params['page']);
                                $qs = http_build_query($params);
                                $prefix = $qs ? "?{$qs}&" : "?";

                                $current = $pagination['current'];
                                $total = $pagination['total_pages'];
                                ?>

                                <!-- Anterior -->
                                <li class="page-item <?= $pagination['has_prev'] ? '' : 'disabled' ?>">
                                    <a class="page-link" href="<?= $prefix ?>page=<?= $pagination['prev'] ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <!-- Números -->
                                <?php for ($i = 1; $i <= $total; $i++): ?>
                                    <li class="page-item <?= ($i == $current) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $prefix ?>page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Siguiente -->
                                <li class="page-item <?= $pagination['has_next'] ? '' : 'disabled' ?>">
                                    <a class="page-link" href="<?= $prefix ?>page=<?= $pagination['next'] ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Config
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('plansChart');
            if (!ctx) return; // Si estamos en móvil puede estar oculto o no rendered

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

                new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(categoriesMap),
                        datasets: [{
                            data: Object.values(categoriesMap),
                            backgroundColor: [
                                '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                            title: { display: false }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
</body>

</html>