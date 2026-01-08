-- GeoPlans Database Schema (CORREGIDO Y FINAL)
-- Compatible con PHP 8.2 y Scraper V2

-- 1. CREACIÓN DE LA BASE DE DATOS (Solución al Error #1046)
CREATE DATABASE IF NOT EXISTS `geoplans`;
USE `geoplans`;

-- 2. CONFIGURACIÓN DE LA BASE DE DATOS
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------
-- Table: categories
-- -------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertamos las categorías base
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Música'),
(2, 'Teatro'),
(3, 'Cine'),
(4, 'Gastronomía'),
(5, 'Aire Libre'),
(6, 'Arte/Museo'),
(7, 'Varios');

-- -------------------------------------------------
-- Table: plans
-- -------------------------------------------------
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `date` datetime NOT NULL,
  `url_source` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL, -- IMPORTANTE: Columna necesaria para tu versión actual
  `category_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_plan_category` (`category_id`),
  CONSTRAINT `fk_plan_category` FOREIGN_KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: No insertamos datos falsos antiguos. 
-- Ejecuta el script de scraping para llenar esto con datos reales.

SET FOREIGN_KEY_CHECKS = 1;