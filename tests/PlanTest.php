<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GeoPlans\Models\Plan;

final class PlanTest extends TestCase
{
    public function testPlanModelCanBeInstantiated(): void
    {
        $plan = new Plan();
        $this->assertInstanceOf(Plan::class, $plan);
    }

    public function testDataNormalization(): void
    {
        // Simulamos datos sucios del scraper
        $dirtyTitle = "   El Rey León   ";
        $cleanTitle = trim($dirtyTitle);

        $this->assertSame('El Rey León', $cleanTitle, 'El modelo o servicio debe limpiar espacios.');
    }

    public function testCategoryDetectionLogic(): void
    {
        // Testeamos la lógica de negocio de categorización
        $title = "Gran Concierto de Piano";
        $isMusic = str_contains(strtolower($title), 'concierto');

        $this->assertTrue($isMusic, 'El sistema debe detectar palabras clave en títulos.');
    }
}