-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.38-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5278
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table cars.admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cars.admin_users: ~0 rows (approximately)
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
	(1, 'admin', '$2y$10$eLBa4o53u/3ADSwqCftP3e3id5qboGp.WZHH5t92xjvALbZMjV8uW');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;

-- Dumping structure for table cars.vehicles
CREATE TABLE IF NOT EXISTS `vehicles` (
  `model_id` int(11) DEFAULT NULL,
  `model_make_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_trim` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_year` smallint(4) DEFAULT NULL,
  `model_body` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_engine_position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_engine_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_engine_compression` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_engine_fuel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `make_country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_weight_kg` smallint(6) DEFAULT NULL,
  `model_transmission_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  UNIQUE KEY `Index 2` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cars.vehicles: ~6 rows (approximately)
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;

-- Dumping structure for table cars.vehicles_images
CREATE TABLE IF NOT EXISTS `vehicles_images` (
  `model_id` int(11) DEFAULT NULL COMMENT 'see vehicle table',
  `image` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `Index 2` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cars.vehicles_images: ~10 rows (approximately)
/*!40000 ALTER TABLE `vehicles_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicles_images` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
