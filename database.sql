-- ==============================================================
-- Script de Base de Datos: crud_db
-- Proyecto: Sistema de Gestión y Control (Integración de TI)
-- Compatible con MySQL / MariaDB (XAMPP)
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `crud_db` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `crud_db`;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `rol` VARCHAR(50) DEFAULT 'Usuario',
  `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo iniciales (solo se insertan si la tabla está vacía)
INSERT INTO `usuarios` (`nombre`, `email`, `telefono`, `rol`, `estado`) 
SELECT * FROM (
    SELECT 'Carlos Mendoza', 'carlos.mendoza@empresa.com', '+52 55 1234 5678', 'Administrador', 'Activo'
) AS tmp
WHERE NOT EXISTS (SELECT id FROM `usuarios` WHERE `email` = 'carlos.mendoza@empresa.com') LIMIT 1;

INSERT INTO `usuarios` (`nombre`, `email`, `telefono`, `rol`, `estado`) 
SELECT * FROM (
    SELECT 'Ana Sofía Valenzuela', 'ana.valenzuela@empresa.com', '+52 55 8765 4321', 'Editor', 'Activo'
) AS tmp
WHERE NOT EXISTS (SELECT id FROM `usuarios` WHERE `email` = 'ana.valenzuela@empresa.com') LIMIT 1;

INSERT INTO `usuarios` (`nombre`, `email`, `telefono`, `rol`, `estado`) 
SELECT * FROM (
    SELECT 'Jorge Ramírez', 'jorge.ramirez@empresa.com', '+52 33 2233 4455', 'Usuario', 'Activo'
) AS tmp
WHERE NOT EXISTS (SELECT id FROM `usuarios` WHERE `email` = 'jorge.ramirez@empresa.com') LIMIT 1;

INSERT INTO `usuarios` (`nombre`, `email`, `telefono`, `rol`, `estado`) 
SELECT * FROM (
    SELECT 'Laura Gómez', 'laura.gomez@empresa.com', '+52 81 9988 7766', 'Usuario', 'Inactivo'
) AS tmp
WHERE NOT EXISTS (SELECT id FROM `usuarios` WHERE `email` = 'laura.gomez@empresa.com') LIMIT 1;
