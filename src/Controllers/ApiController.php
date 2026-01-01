<?php

declare(strict_types=1);

namespace GeoPlans\Controllers;

use GeoPlans\Models\Plan;
use Throwable;

/**
 * Controlador para la API REST de GeoPlans.
 */
class ApiController
{
    private Plan $planModel;

    public function __construct()
    {
        $this->planModel = new Plan();
    }

    /**
     * Endpoint: GET /api/plans
     * Devuelve el listado de planes en formato JSON.
     */
    public function getPlans(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $plans = $this->planModel->getAll();
            
            echo json_encode([
                'status' => 'success',
                'count'  => count($plans),
                'data'   => $plans
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener los datos de la API.'
            ]);
        }
    }
}