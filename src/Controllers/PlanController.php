<?php

declare(strict_types=1);

namespace GeoPlans\Controllers;

use GeoPlans\Core\Controller;
use GeoPlans\Models\Plan;

/**
 * Controlador para gestionar la visualización de planes.
 */
class PlanController extends Controller
{
    private Plan $planModel;

    public function __construct()
    {
        $this->planModel = new Plan();
    }

    /**
     * Acción principal: Listado y filtrado.
     */
    /**
     * Acción principal: Listado y filtrado con paginación.
     */
    public function index(): void
    {
        // 1. Configuración de Paginación
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 9; // Planes por página (3x3 grid)
        $offset = ($page - 1) * $limit;

        // 2. Capturar filtros
        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'max_price' => $_GET['price'] ?? null
        ];

        // 3. Obtener datos y conteo
        if (!empty($filters['category_id']) || !empty($filters['max_price'])) {
            // Añadir paginación a filtros
            $filters['limit'] = $limit;
            $filters['offset'] = $offset;

            $plans = $this->planModel->getByFilter($filters);
            $total = $this->planModel->countByFilter($filters);
        } else {
            $plans = $this->planModel->getAll($limit, $offset);
            $total = $this->planModel->countAll();
        }

        // 4. Calcular datos de paginación
        $totalPages = (int) ceil($total / $limit);

        // Renderizar vista
        $this->render('plans/index', [
            'plans' => $plans,
            'filters' => $filters,
            'title' => 'Explorar Planes - GeoPlans',
            'pagination' => [
                'current' => $page,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
                'next' => $page + 1,
                'prev' => $page - 1
            ]
        ]);
    }

    /**
     * Elimina un plan.
     */
    public function delete(): void
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->planModel->delete((int) $id);
        }
        // Redirigir al listado
        header('Location: /');
        exit;
    }
}