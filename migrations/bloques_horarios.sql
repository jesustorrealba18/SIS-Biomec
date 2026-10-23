-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-10-2026 a las 04:51:20
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
-- Estructura de tabla para la tabla `bloques_horarios`
--

CREATE TABLE `bloques_horarios` (
  `id_bloque` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bloques_horarios`
--

INSERT INTO `bloques_horarios` (`id_bloque`, `dia_semana`, `hora_inicio`, `hora_fin`, `estado`) VALUES
(1, 'Lunes', '07:00:00', '09:00:00', 'Activo'),
(2, 'Martes', '07:00:00', '09:00:00', 'Activo'),
(3, 'Miercoles', '07:00:00', '09:00:00', 'Activo'),
(4, 'Jueves', '07:00:00', '09:00:00', 'Activo'),
(5, 'Viernes', '07:00:00', '09:00:00', 'Activo'),
(6, 'Sabado', '09:00:00', '11:00:00', 'Activo'),
(7, 'Lunes', '16:00:00', '18:00:00', 'Activo'),
(8, 'Martes', '16:00:00', '18:00:00', 'Activo'),
(9, 'Miercoles', '16:00:00', '18:00:00', 'Activo'),
(10, 'Jueves', '16:00:00', '18:00:00', 'Activo'),
(11, 'Viernes', '16:00:00', '18:00:00', 'Activo'),
(12, 'Miercoles', '14:16:00', '15:50:00', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bloques_horarios`
--
ALTER TABLE `bloques_horarios`
  ADD PRIMARY KEY (`id_bloque`),
  ADD UNIQUE KEY `dia_semana` (`dia_semana`,`hora_inicio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bloques_horarios`
--
ALTER TABLE `bloques_horarios`
  MODIFY `id_bloque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
