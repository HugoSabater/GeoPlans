# GeoPlans: Buscador de Planes Geolocalizados

GeoPlans es una aplicación web MVC nativa desarrollada en PHP 8.1+ que permite la agregación, gestión y visualización de planes de ocio (conciertos, teatro, cine) mediante técnicas de scraping.

El proyecto implementa una arquitectura limpia, sin frameworks full-stack, priorizando el uso de estándares PSR, inyección de dependencias y patrones de diseño.

## Requisitos del Servidor

Para desplegar este proyecto necesitas un entorno con:

- **PHP 8.1** o superior.
- **MySQL** o MariaDB.
- **Composer** (Gestor de dependencias).
- Extensión `pdo_mysql` habilitada en PHP.

## Instalación y Despliegue

Sigue estos pasos para poner en marcha el proyecto en tu máquina local:

### 1. Clonar el repositorio
```bash
git clone https://github.com/HugoSabater/GeoPlans
cd GeoPlans
```

### 2. Instalar dependencias
Descarga las librerías necesarias (Guzzle, Monolog, Symfony Components) ejecutando:
```bash
composer install
```
> **Nota:** Si estás en un entorno de desarrollo local con PHP 7.4 (ej. XAMPP antiguo), puedes usar `composer install --ignore-platform-req=php`, aunque se recomienda actualizar a PHP 8.1.

### 3. Configuración del entorno
Copia el archivo de ejemplo y configura tus credenciales de base de datos:
```bash
cp .env.example .env
```
Edita el archivo `.env` y ajusta `DB_USER`, `DB_PASS` y `DB_NAME` según tu configuración local.

### 4. Base de Datos
Importa el esquema y los datos iniciales en tu gestor de base de datos (phpMyAdmin, Workbench, CLI):
```bash
mysql -u root -p geoplans_db < database.sql
```
*Asegúrate de crear la base de datos `geoplans_db` antes de importar si no existe.*

### 5. Configuración del Servidor Web
Configura tu servidor (Apache/Nginx) para que el `DocumentRoot` apunte a la carpeta `/public`.
- **Apache:** Asegúrate de que `mod_rewrite` esté activado para que el enrutamiento funcione correctamente.
- **PHP Built-in Server (Rápido para pruebas):**
  ```bash
  php -S localhost:8000 -t public
  ```

---

## Instrucciones de Uso

### Ejecutar el Scraper
Para poblar la base de datos con nuevos planes extraídos de fuentes externas, ejecuta el script desde la terminal:
```bash
php scripts/run_scrape.php
```

### Acceder a la Web y API
- **Web**: Visita `http://localhost:8000` (o tu vhost) para ver el buscador y las estadísticas gráficas.
- **API REST**: Accede a `http://localhost:8000/api/plans` para obtener el listado de eventos en formato JSON.

### Ejecutar Tests Unitarios
Para verificar la integridad del código, ejecuta la suite de pruebas con PHPUnit:
```bash
./vendor/bin/phpunit tests
```

### Documentación del Código
La documentación técnica de clases y métodos está disponible en HTML estático.
Abre el archivo `/docs/index.html` en tu navegador para consultarla.

---

## Tecnologías y Librerías

- **Core**: PHP 8.1 Nativo, PDO, MVC Pattern.
- **Frontend**: HTML5, Bootstrap 5, Chart.js.
- **Scraping**: GuzzleHTTP, Symfony DOM Crawler y CSS Selector.
- **Logging**: Monolog.
- **Testing**: PHPUnit.
- **Docs**: phpDocumentor.
