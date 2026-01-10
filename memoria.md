# Memoria Técnica: GeoPlans

**Autor:** Hugo Sabater
**Asignatura:** Desarrollo Web en Entorno Servidor
**Fecha:** Enero 2026

---

## 1. Análisis Funcional

**GeoPlans** es una plataforma web diseñada para agregar, categorizar y visualizar eventos culturales en la ciudad de Madrid. El sistema resuelve la fragmentación de la información de ocio centralizando datos de diversas fuentes en una única interfaz unificada.

### Funcionalidades Principales:
1.  **Motor de Adquisición (Scraping):** Un servicio automatizado extrae información (título, fecha, precio, imagen) de portales web reales como *TeatroMadrid*. Utiliza lógica difusa para detectar categorías basándose en palabras clave del título.
2.  **Visualización Web:** Interfaz pública responsive (basada en Bootstrap) que muestra los eventos en una rejilla paginada, con indicativos visuales por categoría.
3.  **API REST:** Endpoint público (`/api/plans`) que expone los eventos activos en formato JSON para el consumo de terceros o aplicaciones móviles.
4.  **Gestión de Ciclo de Vida:** Scripts de mantenimiento (`maintenance.php`) para limpiar eventos caducados y renovar la base de datos automáticamente.

---

## 2. Diagrama Entidad-Relación (E-R)

El sistema utiliza una base de datos relacional MySQL normalizada con dos entidades principales:



**Descripción del Modelo:**
* **Categories (1):** Tabla maestra que almacena los tipos de eventos (Teatro, Música, Cine...).
* **Plans (N):** Tabla transaccional que almacena los eventos.
* **Relación:** Una Categoría puede tener muchos Planes (1:N), pero un Plan pertenece a una sola Categoría. Se implementa integridad referencial con `ON DELETE CASCADE`.

---

## 3. Decisiones Técnicas y Arquitectura

### 3.1. Arquitectura MVC Nativa
Se ha optado por **no utilizar frameworks Full-Stack** (como Laravel) para cumplir con los requisitos académicos y demostrar dominio del lenguaje PHP. Se ha implementado una arquitectura MVC propia:
* **Router:** Enrutador personalizado que despacha peticiones basándose en la URI y el Método HTTP.
* **Controllers:** Separan la lógica de negocio de la presentación.
* **Views:** Archivos PHP limpios que solo muestran datos.

### 3.2. Stack Tecnológico
* **PHP 8.2:** Uso de características modernas como Tipado Estricto (`declare(strict_types=1)`), Tipos de Retorno y Propiedades Tipadas.
* **MySQL + PDO:** Capa de abstracción de datos segura contra Inyección SQL mediante sentencias preparadas.
* **Composer:** Gestión profesional de dependencias.

### 3.3. Librerías Externas (Justificación)
* **GuzzleHttp:** Para realizar peticiones HTTP robustas al scrapear.
* **Symfony DOMCrawler:** Para el parseo eficiente del HTML (mucho más estable que usar Regex).
* **PHP DotEnv:** Para la gestión de variables de entorno y evitar subir credenciales al repositorio.
* **Monolog:** Para mantener un registro de errores (logs) profesional.

---

## 4. Conclusión
GeoPlans cumple con los estándares de la industria (PSR-4, PSR-12) y ofrece una solución escalable y mantenible, superando los requisitos de un simple ejercicio académico al incorporar datos reales y automatización.