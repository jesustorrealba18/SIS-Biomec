-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2026 a las 03:34:54
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
-- Estructura de tabla para la tabla `entrenador`
--

CREATE TABLE `entrenador` (
  `id_entrenador` int(11) NOT NULL,
  `cedula` varchar(11) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F') DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(12) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `curriculum_pdf` varchar(255) DEFAULT NULL,
  `curriculum_nombre_original` varchar(255) DEFAULT NULL,
  `foto` varchar(100) NOT NULL DEFAULT '',
  `id_usuario` int(3) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entrenador`
--

INSERT INTO `entrenador` (`id_entrenador`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `correo`, `telefono`, `direccion`, `curriculum_pdf`, `curriculum_nombre_original`, `foto`, `id_usuario`, `estado`) VALUES
(3, 'V-08591799', 'JOSEFINA', 'Pirolo', '1969-07-12', 'F', 'josefinavarro@gmail.com', '04120560145', 'Carrera 5 Entre Calles 6 Y 7 Tamaca', 'CV_V08591799_1792639048.pdf', 'San Mateo camisas.pdf', '', 0, 'Activo'),
(4, '28591974', 'jesus', 'david', '2000-10-10', 'M', 'ejemplo@correo.com', '04122222323', 'santa isabel', NULL, NULL, '', 0, 'Activo'),
(5, '30948948', 'Heliana', 'Garcia', '2000-04-12', 'F', 'eliana@gmail.com', '04239394784', 'Calle 6 con Carrera 7B', NULL, NULL, '', 0, 'Activo'),
(6, '30768900', 'Pedro Antonio', 'Jimenez Suarez', '2000-02-23', 'M', 'pedrojimenezs@gmail.com', '04124686397', 'Calle 5 San Francisco', NULL, NULL, '', 0, 'Activo'),
(7, '23564790', 'Rosa Valentina', 'Suarez ', '1998-03-15', 'F', 'delarosa@gmail.com', '04124758865', 'Santa Rosa ', NULL, NULL, '', 0, 'Activo'),
(8, '23574648', 'Piu Antonio', 'Siu', '2000-08-05', 'M', 'correo@gmail.com', '04124402959', 'Calle 5 con carrera 7 centro', NULL, NULL, '', 0, 'Activo'),
(10, '30759776', 'Veronica', 'Villamizar', '2004-04-10', 'F', 'vero.cvv10@gmail.com', '04165501950', 'San Francisco', NULL, NULL, '', 0, 'Activo'),
(11, '34634373', 'Hola', 'Peres', '2000-09-18', 'F', 'ejemplo@gmail.com', '06373437343', 'jjjxj', NULL, NULL, '', NULL, 'Inactivo'),
(12, '30759778', 'Veronica', 'Villamizar', '2000-02-10', 'F', 'vero.cvv10@gmail.com', '04124727433', 'Calle 10', NULL, NULL, '', NULL, 'Activo'),
(13, '22222222', 'wwwww', 'wwww', '0002-02-22', 'M', 'ddeliana@gmail.com', '99999999999', 'dddddd', NULL, NULL, '', NULL, 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  ADD PRIMARY KEY (`id_entrenador`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  MODIFY `id_entrenador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
