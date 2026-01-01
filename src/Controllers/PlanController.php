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
    public function index(): void
    {
        // Capturar filtros de la URL (GET)
        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'max_price' => $_GET['price'] ?? null
        ];

        // Obtener datos filtrados o todos
        $plans = (!empty($filters['category_id']) || !empty($filters['max_price']))
            ? $this->planModel->getByFilter($filters)
            : $this->planModel->getAll();

        // Renderizar vista
        $this->render('plans/index', [
            'plans' => $plans,
            'filters' => $filters,
            'title' => 'Explorar Planes - GeoPlans'
        ]);
    }
}