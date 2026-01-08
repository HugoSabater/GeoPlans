# 🌍 GeoPlans | Cultural Aggregator Engine

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC-orange?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**GeoPlans** es una plataforma de agregación de eventos culturales de alto rendimiento diseñada bajo una arquitectura **MVC Nativa (Sin Frameworks)**. El sistema implementa un motor de *Web Scraping* resiliente capaz de consolidar datos heterogéneos (TeatroMadrid, etc.), normalizarlos y servirlos a través de una Interfaz Reactiva y una API RESTful JSON.

---

## 🚀 Características Técnicas

### 🏗️ Arquitectura & Backend
- **Core Nativo PHP 8.2:** Implementación estricta (`strict_types=1`) sin dependencia de frameworks como Laravel o Symfony, demostrando dominio del lenguaje.
- **Patrón MVC Estricto:** Separación total de responsabilidades (Front Controller, Router, Controllers, Models, Views).
- **Inyección de Dependencias:** Uso de `vlucas/phpdotenv` para gestión de entornos y `monolog/monolog` para observabilidad.
- **Base de Datos:** MySQL con capa de abstracción PDO y sentencias preparadas para prevenir inyección SQL.

### 🕷️ Motor de Adquisición (Scraping)
- **Extracción Inteligente:** Uso de `GuzzleHttp` y `Symfony DOMCrawler` para parsing HTML avanzado.
- **Lógica Difusa:** Categorización automática de eventos basada en análisis semántico del título (NLP básico).
- **Mantenimiento Autónomo:** Script `maintenance.php` diseñado para ejecución CRON, encargado de la limpieza de eventos caducados y descubrimiento de nuevos items (Paginación automática).

### 🎨 Frontend & UX
- **Diseño Atómico:** Componentes visuales modulares con Bootstrap 5.
- **Feedback Visual:** Sistema de etiquetas (Badges) dinámicos basados en la categoría del evento.
- **Performance:** Carga diferida de imágenes y paginación optimizada (Grid 3x3).

---

## 🛠️ Requisitos del Sistema

- **PHP:** 8.1 o superior (Probado en 8.2).
- **Extensiones:** `pdo_mysql`, `mbstring`, `curl`, `dom`.
- **Base de Datos:** MySQL 5.7 / 8.0 o MariaDB.
- **Gestor de Dependencias:** Composer.

---

## 📦 Instalación y Despliegue

### 1. Clonar el repositorio
```bash
git clone https://github.com/HugoSabater/GeoPlans.git
cd GeoPlans
```

### 2. Instalar dependencias
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configurar Entorno
```bash
cp .env.example .env
# Editar .env con tus credenciales de base de datos
```

### 4. Base de Datos (Seed Inicial)
Importar el archivo `database.sql` incluido en la raíz. Este archivo contiene la estructura DDL completa y un dataset semilla dinámico generado mediante scraping en tiempo real. Incluye eventos actuales de Madrid (Teatro, Musicales, etc.) con sus metadatos e imágenes validadas.

### 5. Arrancar Servidor (Modo Desarrollo)
```bash
php -S localhost:8000 -t public
```

---

## 🤖 Automatización y Scripts
El sistema incluye herramientas CLI para mantenimiento:

| Comando | Descripción |
|---------|-------------|
| `php scripts/maintenance.php` | **Modo Producción**: Elimina eventos pasados y scrapea nuevas páginas. |
| `php scripts/refresh_all.php` | **Modo Reset**: Trunca la base de datos y regenera todo desde cero. |

---

## 🔌 Documentación API
El sistema expone un endpoint público para consumo de terceros:

### `GET /api/plans`
**Response**: JSON con la lista de eventos activos.

**Estructura:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "El Rey León",
      "category": "Musicales",
      "date": "2026-02-20",
      "image_url": "https://..."
    }
  ]
}
```
Para documentación técnica detallada del código, consultar `/docs/index.html`.

---

## 🧪 Testing
Ejecutar la suite de pruebas unitarias:
```bash
./vendor/bin/phpunit tests
```

---

**Autor**: Hugo Sabater  
**Licencia**: MIT