<?php

declare(strict_types=1);

namespace GeoPlans\Core;

/**
 * Controlador base para la gestión de vistas.
 */
abstract class Controller
{
    /**
     * Renderiza una vista desde el directorio /views.
     *
     * @param string $view Nombre del archivo (ej: 'home/index')
     * @param array $data Datos para extraer en la vista
     */
    protected function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("La vista {$view} no existe en {$viewPath}");
        }

        // Extraer variables para que estén disponibles en el scope del require
        extract($data);

        require_once $viewPath;
    }
}
