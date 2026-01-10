# 🌍 GeoPlans | Cultural Aggregator Engine

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC-orange?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success?style=for-the-badge)

</div>

**GeoPlans** es una plataforma de agregación de eventos de alto rendimiento. Diseñada bajo una arquitectura **MVC Nativa (Zero-Framework)**, implementa un motor de ingestión de datos autónomo que centraliza la oferta cultural de Madrid en tiempo real.

El sistema destaca por su **Scraping Resiliente**, capaz de filtrar ruido publicitario y categorizar eventos mediante lógica difusa, sirviendo los datos a través de una Interfaz Web Reactiva y una API RESTful de baja latencia.

---

## 🚀 Características Técnicas

### 🏗️ Arquitectura Backend (Core)

- **PHP 8.2 Estricto:** Código tipado (`declare(strict_types=1)`) para máxima robustez y rendimiento.
- **Custom MVC:** Framework propio ligero. Sin la sobrecarga (bloatware) de Laravel/Symfony.
- **Inyección de Dependencias:** Gestión profesional con Composer.
- **Seguridad:** Sentencias preparadas (PDO) y gestión de entornos con `.env`.

### 🕷️ Motor de Datos (Data Engine)

- **Extracción Inteligente:** Crawler basado en `Guzzle` + `Symfony DOMCrawler`.
- **Sanitización Automática:** Algoritmo que descarta banners y "falsos eventos" (filtro de calidad).
- **Categorización Semántica:** Detecta si un evento es "Teatro" o "Música" analizando palabras clave en el título.

---

## 🛠️ Requisitos del Sistema

- **PHP:** 8.1 o superior (Recomendado 8.2).
- **Extensiones:** `pdo_mysql`, `mbstring`, `curl`, `dom`.
- **Base de Datos:** MySQL 8.0 / MariaDB.
- **Gestor de Dependencias:** Composer.

---

## 📦 Instalación y Despliegue

Sigue estos pasos para levantar el entorno en local:

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
# Edita .env con tus credenciales de MySQL (DB_HOST, DB_NAME=geoplans, etc.)
```

### 4. Base de Datos

Importar el archivo `database.sql` incluido en la raíz para generar la estructura de la base de datos.

### 5. (Opcional) Regenerar Datos en Tiempo Real

Para borrar los datos semilla y descargar eventos frescos de hoy:

```bash
php scripts/refresh_all.php
```

### 6. Arrancar Servidor

```bash
php -S localhost:8000 -t public
```

Accede a: `http://localhost:8000`

---

## 🔌 Documentación API

GeoPlans expone sus datos para terceros:

### `GET /api/plans`

- **Respuesta:** JSON con eventos activos.
- **Ejemplo:**

```json
{
  "status": "success",
  "total": 17,
  "data": [
    {
      "id": 104,
      "title": "El Rey León",
      "category": "Musicales",
      "date": "2026-02-20 20:00:00",
      "image_url": "https://..."
    }
  ]
}
```

---

## 🤖 AI & Developer Context

### Project Architecture
- **Language:** PHP 8.2 (Strict Types enabled).
- **Framework:** None (Custom Native MVC).
- **Database:** MySQL / MariaDB using PDO.
- **Dependencies:** Managed via Composer (Guzzle, Monolog, DOMCrawler).

### ⚠️ Critical Environment Requirements
This project strictly requires **PHP 8.2+**. 
Running it on PHP 7.4 will cause immediate fatal errors due to syntax incompatibility (`match` expressions, typed properties).

### 🛠️ Setup Instructions for AI Agents
If the user asks to run the project, guide them through these steps:

1. **Environment Check:** - Verify `php -v` is >= 8.2.
   - If the system PHP is old, suggest using a portable PHP 8.2 folder (e.g., `C:\php82\php.exe`).

2. **Configuration (`php.ini`):**
   - Ensure `extension_dir` is set to an absolute path.
   - Enable extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`.

3. **Dependency Installation:**
   - Run: `composer install --no-dev --optimize-autoloader`.
   - *Note:* If system PHP is old, instruct to run: `<path_to_php82> composer.phar install`.

4. **Database:**
   - Import `database.sql`.
   - Rename `.env.example` to `.env` and set `DB_HOST=127.0.0.1` (avoid `localhost` on Windows to prevent loopback issues).

5. **Server Start:**
   - Do NOT use XAMPP/Apache config unless necessary.
   - Use the built-in server: `php -S localhost:8000 -t public`.

### 🐛 Known Troubleshooting
- **Error:** `could not find driver`
  - **Fix:** The `php.ini` is not loading the `ext` folder correctly. Use absolute path in `extension_dir`.
- **Error:** `500 Internal Server Error`
  - **Fix:** Check `.env` credentials and ensure logs folder has write permissions.

---

## 👤 Autor

**Hugo Sabater**

---

## 📝 Licencia

MIT