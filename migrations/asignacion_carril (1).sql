-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-10-2026 a las 04:50:31
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
(1, 1, 12, 5, NULL, '2026-10-22', '2026-10-23', NULL, 1, 'activa');

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
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
