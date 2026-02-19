-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para distribuidora_gas
CREATE DATABASE IF NOT EXISTS `distribuidora_gas` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;
USE `distribuidora_gas`;

-- Volcando estructura para tabla distribuidora_gas.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telefono` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `ultimo_pedido` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `telefono` (`telefono`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.clientes: ~0 rows (aproximadamente)
REPLACE INTO `clientes` (`id`, `telefono`, `nombre`, `direccion`, `ultimo_pedido`) VALUES
	(2, '992103251', 'Prueba', 'Calle Falsa 123', NULL);

-- Volcando estructura para tabla distribuidora_gas.cuadraturas
CREATE TABLE IF NOT EXISTS `cuadraturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movil_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total_venta` decimal(10,2) DEFAULT NULL,
  `total_debitos` decimal(10,2) DEFAULT NULL,
  `total_cupones` decimal(10,2) DEFAULT NULL,
  `total_descuentos` decimal(10,2) DEFAULT NULL,
  `total_entregar` decimal(10,2) DEFAULT NULL,
  `total_entregado` decimal(10,2) DEFAULT NULL,
  `saldo` decimal(10,2) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `total_kilos` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `movil_id` (`movil_id`),
  CONSTRAINT `cuadraturas_ibfk_1` FOREIGN KEY (`movil_id`) REFERENCES `moviles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.cuadraturas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla distribuidora_gas.descuentos
CREATE TABLE IF NOT EXISTS `descuentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descuento` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.descuentos: ~0 rows (aproximadamente)
REPLACE INTO `descuentos` (`id`, `descuento`, `descripcion`) VALUES
	(5, 1000, '5 Kilos'),
	(6, 2000, '11 Kilos'),
	(7, 3000, '15 Kilos'),
	(8, 5000, '45 Kilos');

-- Volcando estructura para tabla distribuidora_gas.facturas
CREATE TABLE IF NOT EXISTS `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `carga` varchar(20) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.facturas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla distribuidora_gas.inventario
CREATE TABLE IF NOT EXISTS `inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carga` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_carga` (`carga`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.inventario: ~0 rows (aproximadamente)
REPLACE INTO `inventario` (`id`, `carga`, `cantidad`, `precio_unitario`, `precio_venta`) VALUES
	(5, '5 Kilos', 4, 10800.00, 13500.00),
	(6, '11 Kilos', 6, 20800.00, 24800.00),
	(7, '15 Kilos', 79, 18900.00, 25900.00),
	(8, '45 Kilos', 8, 82750.00, 87250.00);

-- Volcando estructura para tabla distribuidora_gas.medios_pago
CREATE TABLE IF NOT EXISTS `medios_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medio_pago` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.medios_pago: ~0 rows (aproximadamente)
REPLACE INTO `medios_pago` (`id`, `medio_pago`) VALUES
	(6, 'Efectivo'),
	(7, 'Débito'),
	(8, 'Cupón'),
	(9, 'Transferencia'),
	(10, 'No Paga');

-- Volcando estructura para tabla distribuidora_gas.moviles
CREATE TABLE IF NOT EXISTS `moviles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telefono` varchar(20) NOT NULL,
  `movil` varchar(50) NOT NULL,
  `patente` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.moviles: ~0 rows (aproximadamente)
REPLACE INTO `moviles` (`id`, `telefono`, `movil`, `patente`) VALUES
	(4, '942467687', 'Movil 1', 'PLPL-27'),
	(5, '+56988556652', 'Movil 2', 'PLVW-24');

-- Volcando estructura para tabla distribuidora_gas.pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `numero_pedido` int(11) NOT NULL,
  `telefono_cliente` varchar(20) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `direccion_cliente` text NOT NULL,
  `carga` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `medio_pago` varchar(50) NOT NULL,
  `descuento` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `movil` varchar(50) NOT NULL,
  `estado` enum('Pendiente','No Asignado','Entregado') DEFAULT 'No Asignado',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.pedidos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla distribuidora_gas.stock_moviles
CREATE TABLE IF NOT EXISTS `stock_moviles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movil_id` int(11) NOT NULL,
  `carga` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `movil_id` (`movil_id`),
  KEY `carga` (`carga`),
  CONSTRAINT `stock_moviles_ibfk_1` FOREIGN KEY (`movil_id`) REFERENCES `moviles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_moviles_ibfk_2` FOREIGN KEY (`carga`) REFERENCES `inventario` (`carga`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.stock_moviles: ~0 rows (aproximadamente)
REPLACE INTO `stock_moviles` (`id`, `movil_id`, `carga`, `cantidad`) VALUES
	(10, 4, '5 Kilos', 5),
	(11, 4, '11 Kilos', 4),
	(12, 4, '15 Kilos', 21),
	(13, 4, '45 Kilos', 2);

-- Volcando estructura para tabla distribuidora_gas.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rango` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.usuarios: ~1 rows (aproximadamente)
REPLACE INTO `usuarios` (`id`, `usuario`, `password`, `rango`) VALUES
	(1, 'admin', 'cc0971385cb0502e913ce65f3f15d2ef4c6fdf137993cd3344f27a046255c766', 'administrador');

-- Volcando estructura para tabla distribuidora_gas.ventas_local
CREATE TABLE IF NOT EXISTS `ventas_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `carga` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `medio_pago` varchar(50) NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla distribuidora_gas.ventas_local: ~0 rows (aproximadamente)
REPLACE INTO `ventas_local` (`id`, `fecha`, `carga`, `cantidad`, `precio_unitario`, `medio_pago`, `descuento`, `total`) VALUES
	(3, '2025-03-29 19:17:48', '5 Kilos', 1, 13500.00, 'Efectivo', 0.00, 13500.00);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
