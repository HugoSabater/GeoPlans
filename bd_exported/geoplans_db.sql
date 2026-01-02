-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 02-01-2026 a las 18:34:31
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `geoplans_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Cultura', '2026-01-01 17:02:30'),
(2, 'Gastronomía', '2026-01-01 17:02:30'),
(3, 'Deportes', '2026-01-01 17:02:30'),
(4, 'Música', '2026-01-01 17:02:30'),
(5, 'Teatro', '2026-01-02 00:00:36'),
(6, 'Cine', '2026-01-02 00:00:36'),
(7, 'Aire Libre', '2026-01-02 00:00:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plans`
--

DROP TABLE IF EXISTS `plans`;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int UNSIGNED NOT NULL,
  `image_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `date` datetime NOT NULL,
  `url_source` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plan_date` (`date`),
  KEY `idx_plan_price` (`price`),
  KEY `fk_plan_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plans`
--

INSERT INTO `plans` (`id`, `category_id`, `image_url`, `title`, `description`, `location`, `price`, `date`, `url_source`, `created_at`) VALUES
(1, 5, 'https://teatromadrid.com/wp-content/uploads/2025/01/TEATRO_MADRID-los-miserables-CARTEL-400x575.jpg', 'Los miserables, el musical', 'Los miserables, el musical - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/los-miserables-el-musical', '2026-01-02 00:26:52'),
(2, 5, 'https://teatromadrid.com/wp-content/uploads/2024/11/TEATRO_MADRID-wicked_el-musical-CARTEL-4-400x575.jpg', 'Wicked, el musical', 'Wicked, el musical - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/wicked-el-musical', '2026-01-02 00:26:52'),
(3, 5, 'https://teatromadrid.com/wp-content/uploads/2023/01/TEATRO_MADRID-The_Book-of-Mormon-CARTEL-2-1-400x575.jpg', 'The Book of Mormon, el musical', 'The Book of Mormon, el musical - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/the-book-of-mormon-el-musical', '2026-01-02 00:26:52'),
(4, 7, 'https://teatromadrid.com/wp-content/uploads/2019/02/TEATRO_MADRID-la_funcion-que-sale-mal-CARTEL-400x575.jpg', 'La función que sale mal', 'La función que sale mal - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/la-funcion-que-sale-mal', '2026-01-02 00:26:52'),
(5, 5, 'https://teatromadrid.com/wp-content/uploads/2025/03/TEATRO-MADRID-Cenicienta-el-musical-CARTEL.png', 'Cenicienta, el musical', 'Cenicienta, el musical - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/cenicienta-el-musical', '2026-01-02 00:26:52'),
(6, 7, 'https://teatromadrid.com/wp-content/uploads/2024/07/TEATRO-MADRID-WAH_nueva_temporada_2024_2025-cartel.jpg', 'WAH Madrid', 'WAH Madrid - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/wah-madrid', '2026-01-02 00:26:52'),
(7, 7, 'https://teatromadrid.com/wp-content/uploads/2023/07/TEATRO_MADRID-Atrapadas-en-la-ofi-CARTEL.jpg', 'Atrapadas en la ofi', 'Atrapadas en la ofi - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/atrapadas-en-la-ofi', '2026-01-02 00:26:52'),
(8, 5, 'https://teatromadrid.com/wp-content/uploads/2019/02/TEATRO-MADRID-Los-pilares-de-la-tierra-segunda-temporada-CARTEL.jpg', 'Los Pilares de la Tierra. El Musical', 'Los Pilares de la Tierra. El Musical - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/los-pilares-de-la-tierra-el-musical', '2026-01-02 00:26:52'),
(9, 7, 'https://teatromadrid.com/wp-content/uploads/2025/07/TEATRO-MADRID-NO-ME-TOQUES-EL-CUENTO-cartel.jpg', 'No me toques el cuento', 'No me toques el cuento - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://www.teatromadrid.com/cartelera', '2026-01-02 00:26:52'),
(10, 5, 'https://teatromadrid.com/wp-content/uploads/2025/05/TEATRO_MADRID-Houdini_un-musical-magico-CARTEL-1-400x575.jpg', 'HOUDINI, un musical mágico', 'HOUDINI, un musical mágico - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/houdini-un-musical-magico', '2026-01-02 00:26:52'),
(11, 7, 'https://teatromadrid.com/wp-content/uploads/2025/08/TEATRO-MADRID-El-Rey-Leon-TEATRO-LOPE-DE-VEGA.png', 'El Rey León', 'El Rey León - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/el-rey-leon', '2026-01-02 00:26:52'),
(12, 7, 'https://teatromadrid.com/wp-content/uploads/2025/06/TEATRO-MADRID-Un-dios-salvaje-cartel-1.jpg', 'Un Dios Salvaje', 'Un Dios Salvaje - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/un-dios-salvaje', '2026-01-02 00:26:52'),
(13, 7, 'https://teatromadrid.com/wp-content/uploads/2025/09/TEATRO-MADRID-corta-el-cable-rojo-cartel.jpg', 'Corta el cable rojo', 'Corta el cable rojo - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/corta-cable-rojo', '2026-01-02 00:26:52'),
(14, 5, 'https://teatromadrid.com/wp-content/uploads/2022/09/TEATRO-MADRID-La_hora_y_media_del_club_de_la_comedia_Principe_Gran_Via-400x575.jpg', 'La Hora y Media de El Club de la Comedia', 'La Hora y Media de El Club de la Comedia - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/la-hora-y-media-de-el-club-de-la-comedia', '2026-01-02 00:26:52'),
(15, 7, 'https://teatromadrid.com/wp-content/uploads/2023/07/TEATRO-MADRID-que_dios-nos-pille-confesados-CARTEL-400x575.jpg', 'Josema Yuste: Que Dios nos pille confesados', 'Josema Yuste: Que Dios nos pille confesados - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:49', 'https://teatromadrid.com/espectaculo/josema-yuste-que-dios-nos-pille-confesados', '2026-01-02 00:26:52'),
(16, 7, 'https://teatromadrid.com/wp-content/uploads/2025/07/TEATRO-MADRID-NO-ME-TOQUES-EL-CUENTO-cartel.jpg', 'No me toques el cuento', 'No me toques el cuento - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:51', 'https://www.teatromadrid.com/cartelera/pagina/2', '2026-01-02 00:26:52'),
(17, 7, 'https://teatromadrid.com/wp-content/uploads/2025/07/TEATRO-MADRID-NO-ME-TOQUES-EL-CUENTO-cartel.jpg', 'No me toques el cuento', 'No me toques el cuento - Entradas y más info en la web oficial.', 'Madrid', 0.00, '2026-01-02 00:26:52', 'https://www.teatromadrid.com/cartelera/pagina/3', '2026-01-02 00:26:52');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `plans`
--
ALTER TABLE `plans`
  ADD CONSTRAINT `fk_plan_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
