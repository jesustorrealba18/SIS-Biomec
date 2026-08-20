-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-08-2026 a las 03:36:14
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sis_natacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_carril`
--

CREATE TABLE `asignacion_carril` (
  `id_asignacion` int(11) NOT NULL,
  `id_carril` int(11) NOT NULL,
  `id_bloque_horario` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `dia_especifico` date DEFAULT NULL,
  `fecha_vigencia_inicio` date NOT NULL,
  `fecha_vigencia_fin` date DEFAULT NULL,
  `fecha_completacion` datetime DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `estado` varchar(20) DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignacion_carril`
--

INSERT INTO `asignacion_carril` (`id_asignacion`, `id_carril`, `id_bloque_horario`, `id_grupo`, `dia_especifico`, `fecha_vigencia_inicio`, `fecha_vigencia_fin`, `fecha_completacion`, `activa`, `estado`) VALUES
(1, 1, 1, 1, NULL, '2026-06-01', '2026-12-31', NULL, 0, 'activa'),
(2, 2, 1, 2, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(3, 3, 1, 3, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(4, 1, 2, 3, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(5, 2, 2, 1, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(6, 3, 2, 2, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(7, 1, 3, 2, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(8, 2, 3, 3, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(9, 3, 3, 1, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(10, 4, 4, 4, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(11, 5, 4, 5, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(12, 4, 5, 4, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(13, 5, 5, 5, NULL, '2026-06-01', '2026-12-31', NULL, 0, 'activa'),
(14, 6, 6, 4, NULL, '2026-06-01', '2026-12-31', '2026-08-13 21:58:27', 0, 'completada'),
(15, 6, 6, 5, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(16, 1, 7, 1, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(17, 2, 7, 2, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(18, 3, 7, 3, '2026-08-05', '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(19, 4, 7, 4, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(20, 5, 7, 5, NULL, '2026-06-01', '2026-12-31', NULL, 1, 'activa'),
(21, 3, 10, 3, '2026-07-28', '2026-07-28', '2026-08-28', NULL, 0, 'activa'),
(22, 3, 10, 10, '2026-07-30', '2026-07-30', '2026-08-30', NULL, 0, 'activa'),
(23, 5, 11, 12, '2026-07-22', '2026-07-20', '2026-08-30', '2026-08-12 14:09:03', 0, 'completada'),
(24, 3, 11, 12, '2026-08-10', '2026-08-05', '2026-09-05', NULL, 0, 'activa'),
(25, 2, 4, 6, '2026-08-14', '2026-08-12', '2026-09-12', NULL, 1, 'activa'),
(26, 5, 11, 6, '2026-08-15', '2026-08-10', '2026-08-11', '2026-08-13 22:00:58', 0, 'completada'),
(27, 4, 5, 7, '2026-08-20', '2026-08-14', '2026-09-14', NULL, 1, 'activa'),
(28, 4, 4, 8, '2026-09-10', '2026-08-14', '2026-09-14', NULL, 1, 'activa'),
(29, 3, 1, 13, NULL, '2026-08-18', '2026-09-18', NULL, 0, 'activa'),
(30, 5, 1, 13, NULL, '2026-08-18', '2026-09-18', NULL, 1, 'activa');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_carril`
--
ALTER TABLE `asignacion_carril`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `fk_ac_carril` (`id_carril`),
  ADD KEY `fk_ac_bloque` (`id_bloque_horario`),
  ADD KEY `fk_ac_grupo` (`id_grupo`),
  ADD KEY `idx_asignacion_estado` (`estado`),
  ADD KEY `idx_asignacion_fecha_completacion` (`fecha_completacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_carril`
--
ALTER TABLE `asignacion_carril`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_carril`
--
ALTER TABLE `asignacion_carril`
  ADD CONSTRAINT `fk_ac_bloque` FOREIGN KEY (`id_bloque_horario`) REFERENCES `bloques_horarios` (`id_bloque`),
  ADD CONSTRAINT `fk_ac_carril` FOREIGN KEY (`id_carril`) REFERENCES `carriles` (`id_carril`),
  ADD CONSTRAINT `fk_ac_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
