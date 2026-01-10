-- 1. Crear y usar la base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS `geoplans`;
USE `geoplans`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Tabla Categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar categorías base
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Música'),
(2, 'Teatro'),
(3, 'Cine'),
(4, 'Gastronomía'),
(5, 'Aire Libre'),
(6, 'Arte/Museo'),
(7, 'Varios');

-- 3. Tabla Plans
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `date` datetime NOT NULL,
  `url_source` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_plan_category` (`category_id`),
  CONSTRAINT `fk_plan_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;