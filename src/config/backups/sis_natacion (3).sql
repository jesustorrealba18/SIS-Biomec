-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-07-2026 a las 01:25:00
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
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignacion_carril`
--

INSERT INTO `asignacion_carril` (`id_asignacion`, `id_carril`, `id_bloque_horario`, `id_grupo`, `dia_especifico`, `fecha_vigencia_inicio`, `fecha_vigencia_fin`, `activa`) VALUES
(1, 2, 3, 6, '2026-06-15', '2026-06-15', '2026-07-30', 0),
(5, 1, 3, 2, '2026-06-15', '2026-06-15', '2026-06-29', 1),
(6, 3, 1, 19, '2026-07-02', '2026-07-02', '2026-07-31', 1),
(7, 2, 5, 19, '2026-07-02', '2026-07-02', '2026-07-25', 1),
(8, 5, 6, 3, '2026-07-02', '2026-07-03', '2026-08-01', 1),
(9, 3, 1, 6, '2026-07-04', '2026-07-04', '2026-08-20', 0),
(10, 3, 5, 19, '2026-07-04', '2026-07-16', '2026-08-29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_asignacion_carril` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Entrenador que validó',
  `fecha` date NOT NULL,
  `hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('QR','Manual') NOT NULL DEFAULT 'QR',
  `estado` enum('Presente','Ausente','Justificado','Tardanza') NOT NULL DEFAULT 'Presente',
  `justificacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atletas`
--

CREATE TABLE `atletas` (
  `id_atleta` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fecha_registro_club` date DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Retirado','Transferido') NOT NULL DEFAULT 'Activo',
  `id_categoria` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atletas`
--

INSERT INTO `atletas` (`id_atleta`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `sexo`, `direccion`, `telefono`, `correo`, `foto`, `fecha_registro_club`, `estado`, `id_categoria`, `id_usuario`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'V-30759776', 'Veronica', 'Villamizar', '2026-06-05', 'F', 'San Francisco', '5619387', 'vero.cvv10@gmail.com', NULL, '2026-06-05', 'Activo', 6, 1, '2026-06-05 15:46:26', NULL),
(2, '87654321', 'Patricio ', 'De los Angeles', '2006-05-17', 'M', 'Casa Blanca', '041225555', 'correoprueba@gmail.com', NULL, '2026-06-18', 'Activo', 3, NULL, '2026-06-30 23:26:27', NULL),
(3, '776572929', 'Perez', 'Antonio', '2004-06-16', 'M', 'Casa piso', '03535363', 'valorcoreeo@gmail.com', NULL, '2026-06-23', 'Activo', 6, NULL, '2026-06-26 23:48:39', NULL),
(4, '39393393', 'Pies Santiago', 'De lobo', '2016-07-22', 'M', 'Casa pegada al piso', '03303033044', 'correoa@gmail.com', NULL, '2026-07-01', 'Activo', 3, NULL, '2026-07-04 14:53:46', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atleta_datos_medicos`
--

CREATE TABLE `atleta_datos_medicos` (
  `id_datos_medicos` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `grupo_sanguineo` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `condiciones_previas` text DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(100) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia_parentesco` varchar(50) DEFAULT NULL,
  `seguro_medico` varchar(100) DEFAULT NULL,
  `numero_feveda` varchar(50) DEFAULT NULL,
  `club_procedencia` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atleta_datos_medicos`
--

INSERT INTO `atleta_datos_medicos` (`id_datos_medicos`, `id_atleta`, `grupo_sanguineo`, `alergias`, `condiciones_previas`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `contacto_emergencia_parentesco`, `seguro_medico`, `numero_feveda`, `club_procedencia`) VALUES
(1, 1, 'O+', 'Ninguna', 'Ninguna', 'Andri Vargas', '04555555', 'Madre', 'Seguro la paz', 'FEV-0999', 'Ninguno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atleta_representante`
--

CREATE TABLE `atleta_representante` (
  `id_atleta_rep` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_representante` int(11) NOT NULL,
  `autorizacion_medica` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_aut_medica` date DEFAULT NULL,
  `autorizacion_imagen` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_aut_imagen` date DEFAULT NULL,
  `recibe_notificaciones` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloques_horarios`
--

CREATE TABLE `bloques_horarios` (
  `id_bloque` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bloques_horarios`
--

INSERT INTO `bloques_horarios` (`id_bloque`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 'Martes', '02:00:00', '05:00:00'),
(3, 'Viernes', '06:17:00', '16:17:00'),
(5, 'Lunes', '16:38:00', '20:38:00'),
(6, 'Domingo', '05:30:00', '10:00:00'),
(7, 'Lunes', '11:00:00', '16:00:00'),
(8, 'Martes', '14:44:00', '20:44:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carga_diaria`
--

CREATE TABLE `carga_diaria` (
  `id_carga` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tss` decimal(8,2) DEFAULT NULL,
  `trimp` decimal(8,2) DEFAULT NULL,
  `srpe_total` int(11) DEFAULT NULL,
  `volumen_total_m` int(11) DEFAULT NULL,
  `carga_aguda_7d` decimal(8,2) DEFAULT NULL,
  `carga_cronica_28d` decimal(8,2) DEFAULT NULL,
  `acwr` decimal(5,2) DEFAULT NULL,
  `monotonia_semanal` decimal(5,2) DEFAULT NULL,
  `strain_semanal` decimal(8,2) DEFAULT NULL,
  `semaforo_acwr` enum('Verde','Amarillo','Rojo') DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carriles`
--

CREATE TABLE `carriles` (
  `id_carril` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `capacidad_maxima` int(11) NOT NULL DEFAULT 6,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carriles`
--

INSERT INTO `carriles` (`id_carril`, `numero`, `capacidad_maxima`, `activo`) VALUES
(1, 1, 6, 1),
(2, 2, 6, 1),
(3, 3, 6, 1),
(4, 4, 6, 1),
(5, 5, 6, 1),
(6, 6, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_feveda`
--

CREATE TABLE `categorias_feveda` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `edad_minima` int(11) NOT NULL,
  `edad_maxima` int(11) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_feveda`
--

INSERT INTO `categorias_feveda` (`id_categoria`, `nombre`, `edad_minima`, `edad_maxima`, `activa`) VALUES
(1, 'Pre-Infantil', 8, 10, 1),
(2, 'Infantil A', 11, 12, 1),
(3, 'Infantil B', 13, 14, 1),
(4, 'Juvenil A', 15, 16, 1),
(5, 'Juvenil B', 17, 18, 1),
(6, 'Maxima', 19, 99, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `drills`
--

CREATE TABLE `drills` (
  `id_drill` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado','Multi') NOT NULL,
  `categoria` enum('Tecnico','Fuerza','Velocidad','Coordinacion','Resistencia') NOT NULL,
  `enfoque_tecnico` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `metraje_sugerido` varchar(50) NOT NULL DEFAULT '0',
  `dificultad` enum('Basico','Intermedio','Avanzado') NOT NULL DEFAULT 'Basico',
  `material_requerido` enum('Ninguno','Pullboy','Aletas','Tabla','Paddle','Resistente','Pullboy_Aletas','Multiple') NOT NULL DEFAULT 'Ninguno',
  `personalizado` tinyint(1) NOT NULL DEFAULT 0,
  `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'En plan_seguridad',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `drills`
--

INSERT INTO `drills` (`id_drill`, `nombre`, `estilo`, `categoria`, `enfoque_tecnico`, `descripcion`, `instrucciones`, `metraje_sugerido`, `dificultad`, `material_requerido`, `personalizado`, `id_usuario_creador`, `activo`, `fecha_creacion`) VALUES
(6, 'Patadas rapidas', 'Espalda', 'Velocidad', 'Hacer la patada rapida', 'Nadar dando patadas largas y rapidas', 'Hacer dos patadas y descansar 2 segundos', '2', 'Basico', 'Ninguno', 1, 1, 1, '2026-06-25 11:29:00'),
(7, 'Nado sincronico', 'Braza', 'Fuerza', 'nadar', 'habia una vez', 'Nadar  ', '34', 'Basico', 'Ninguno', 1, 1, 1, '2026-06-25 12:11:00'),
(8, 'Nadar libre', 'Mariposa', 'Tecnico', 'mejor tecnica', 'nadar para competencia nacional', 'nada para ', '8X5M', 'Avanzado', 'Ninguno', 1, 1, 1, '0000-00-00 00:00:00'),
(10, 'Patada especial', 'Multi', 'Tecnico', 'Mejorar las patadas', 'Ejercicio para nadar grandemente', 'Hacer 5 patadas rapidas, descansar 5 segundos y continuar', '4', 'Basico', 'Ninguno', 0, 1, 1, '2026-06-27 15:00:00'),
(11, 'Brazos y patada', 'Multi', 'Coordinacion', 'Mejorar las brazadas y las patadas', 'Este ejercicio es para mejorar la coordinacion de los movimientos ', 'Realizar 5 veces movimientos de patadas y brazadas ', '504 M', 'Intermedio', 'Multiple', 0, 1, 1, '2026-06-27 15:52:00'),
(12, 'Ejercicio de nado', 'Combinado', 'Velocidad', 'Calentar los brazos y piernas', 'Comenzar a nadar con los brazos y piernas lentamente', 'Hacer dos patadas y una brazada cada dos minutos', '39X6M', 'Intermedio', 'Ninguno', 0, 1, 1, '2026-07-04 14:47:00');

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
  `foto` varchar(100) NOT NULL,
  `id_usuario` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entrenador`
--

INSERT INTO `entrenador` (`id_entrenador`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `correo`, `telefono`, `direccion`, `foto`, `id_usuario`) VALUES
(1, '99999999', 'Administrador', 'Sistema', '2000-01-01', 'M', 'admin@sisnatacion.com', '04124445567', 'Prueba para el sistema\r\n', 'default.png', 1),
(3, '30759776', 'Veronica', 'Villamizar', '2000-06-04', 'M', 'vero.cvv10@gmail.com', '06666666660', 'hin uhu hhnnnnnnnnhh', '', 1),
(5, '3587975', 'Victor', 'Lopez', '2010-06-01', 'M', 'correo@gmail.com', '0666666666', 'Entrenador ', '', 1),
(6, '20879863', 'Antonie Samuel', 'Gonzales Peña', '2026-06-14', 'M', 'antonio@gmail.com', '04235556566', 'Casa principal\r\n', '', 1),
(11, '23987947', 'Gail', 'Antonie', '2000-07-04', 'M', 'correo@gmail.com', '04736373839', 'Calle en la casa', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenador_asignacion`
--

CREATE TABLE `entrenador_asignacion` (
  `id_asignacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'ID del entrenador en plan_seguridad',
  `id_atleta` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `sede` varchar(200) DEFAULT NULL,
  `tipo` enum('Regional','Nacional','Internacional','Selectivo','Control') NOT NULL DEFAULT 'Control',
  `nivel` enum('A','B','C') DEFAULT NULL,
  `organizador` varchar(200) DEFAULT NULL,
  `estado` enum('Planificado','Inscrito','En Progreso','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificado',
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id_evento`, `nombre`, `fecha_inicio`, `fecha_fin`, `sede`, `tipo`, `nivel`, `organizador`, `estado`, `observaciones`, `fecha_creacion`) VALUES
(1, 'Pisicina Grande', '2026-06-10', '2026-06-20', 'UPTAEB', 'Regional', 'B', 'FEVEDA', 'Cancelado', 'DIVERSIOM', '2026-06-10 22:36:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento_inscripcion`
--

CREATE TABLE `evento_inscripcion` (
  `id_inscripcion` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha_inscripcion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evento_inscripcion`
--

INSERT INTO `evento_inscripcion` (`id_inscripcion`, `id_evento`, `id_atleta`, `fecha_inscripcion`) VALUES
(1, 1, 1, '2026-06-10 22:36:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factores_conversion`
--

CREATE TABLE `factores_conversion` (
  `id_factor` int(11) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia_m` int(11) NOT NULL,
  `direccion` enum('25_a_50','50_a_25') NOT NULL,
  `factor` decimal(6,4) NOT NULL,
  `fuente` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `factores_conversion`
--

INSERT INTO `factores_conversion` (`id_factor`, `estilo`, `distancia_m`, `direccion`, `factor`, `fuente`) VALUES
(1, 'Libre', 50, '25_a_50', 1.0000, 'World Aquatics'),
(2, 'Libre', 100, '25_a_50', 1.0300, 'World Aquatics'),
(3, 'Libre', 200, '25_a_50', 1.0600, 'World Aquatics'),
(4, 'Libre', 400, '25_a_50', 1.0800, 'World Aquatics'),
(5, 'Libre', 800, '25_a_50', 1.1000, 'World Aquatics'),
(6, 'Libre', 1500, '25_a_50', 1.1200, 'World Aquatics'),
(7, 'Espalda', 50, '25_a_50', 1.0000, 'World Aquatics'),
(8, 'Espalda', 100, '25_a_50', 1.0300, 'World Aquatics'),
(9, 'Espalda', 200, '25_a_50', 1.0600, 'World Aquatics'),
(10, 'Braza', 50, '25_a_50', 1.0000, 'World Aquatics'),
(11, 'Braza', 100, '25_a_50', 1.0400, 'World Aquatics'),
(12, 'Braza', 200, '25_a_50', 1.0800, 'World Aquatics'),
(13, 'Mariposa', 50, '25_a_50', 1.0000, 'World Aquatics'),
(14, 'Mariposa', 100, '25_a_50', 1.0400, 'World Aquatics'),
(15, 'Mariposa', 200, '25_a_50', 1.0800, 'World Aquatics'),
(16, 'Combinado', 100, '25_a_50', 1.0300, 'World Aquatics'),
(17, 'Combinado', 200, '25_a_50', 1.0600, 'World Aquatics'),
(18, 'Combinado', 400, '25_a_50', 1.0900, 'World Aquatics'),
(19, 'Libre', 50, '50_a_25', 1.0000, 'World Aquatics'),
(20, 'Libre', 100, '50_a_25', 0.9709, 'World Aquatics'),
(21, 'Libre', 200, '50_a_25', 0.9434, 'World Aquatics'),
(22, 'Libre', 400, '50_a_25', 0.9259, 'World Aquatics'),
(23, 'Libre', 800, '50_a_25', 0.9091, 'World Aquatics'),
(24, 'Libre', 1500, '50_a_25', 0.8929, 'World Aquatics'),
(25, 'Espalda', 50, '50_a_25', 1.0000, 'World Aquatics'),
(26, 'Espalda', 100, '50_a_25', 0.9709, 'World Aquatics'),
(27, 'Espalda', 200, '50_a_25', 0.9434, 'World Aquatics'),
(28, 'Braza', 50, '50_a_25', 1.0000, 'World Aquatics'),
(29, 'Braza', 100, '50_a_25', 0.9615, 'World Aquatics'),
(30, 'Braza', 200, '50_a_25', 0.9259, 'World Aquatics'),
(31, 'Mariposa', 50, '50_a_25', 1.0000, 'World Aquatics'),
(32, 'Mariposa', 100, '50_a_25', 0.9615, 'World Aquatics'),
(33, 'Mariposa', 200, '50_a_25', 0.9259, 'World Aquatics'),
(34, 'Combinado', 100, '50_a_25', 0.9709, 'World Aquatics'),
(35, 'Combinado', 200, '50_a_25', 0.9434, 'World Aquatics'),
(36, 'Combinado', 400, '50_a_25', 0.9174, 'World Aquatics');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fases_periodizacion`
--

CREATE TABLE `fases_periodizacion` (
  `id_fase` int(11) NOT NULL,
  `id_macrociclo` int(11) NOT NULL,
  `nombre_fase` enum('Acumulacion','Transmutacion','Realizacion','Deload') NOT NULL,
  `semana_inicio` int(11) NOT NULL,
  `semana_fin` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `porcentaje_volumen` decimal(5,2) DEFAULT NULL,
  `rango_intensidad` varchar(50) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fases_periodizacion`
--

INSERT INTO `fases_periodizacion` (`id_fase`, `id_macrociclo`, `nombre_fase`, `semana_inicio`, `semana_fin`, `fecha_inicio`, `fecha_fin`, `porcentaje_volumen`, `rango_intensidad`, `color`) VALUES
(1, 1, 'Acumulacion', 6, 7, '2026-06-02', '2026-06-18', 89.00, '66', '99');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_entrenamiento`
--

CREATE TABLE `grupos_entrenamiento` (
  `id_grupo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_entrenador` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos_entrenamiento`
--

INSERT INTO `grupos_entrenamiento` (`id_grupo`, `nombre`, `descripcion`, `id_entrenador`, `activo`) VALUES
(1, 'Pruba', 'esto es solo una prueba', 3, 0),
(2, 'prueba 2 ', 'Habia una vez', 5, 1),
(3, 'equipo dinamita', 'grupo de nado', 3, 1),
(4, 'Equipo juvenil A', 'Jovenes de 18 años ', 5, 1),
(5, 'Equipo infatil', 'Equipo de niños pequeño', 5, 1),
(6, 'Equipo Profesional', 'Este es un equipo muy profesional', 3, 1),
(18, 'Equipo 33', 'jjejeje', 1, 1),
(19, 'Equipo juvenil', 'para nadar en intermedio', 6, 0),
(20, 'Equipo dos', 'Equipo medio infatil', 11, 1),
(21, 'jjjjj', 'nnnn', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_atleta`
--

CREATE TABLE `grupo_atleta` (
  `id_grupo_atleta` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupo_atleta`
--

INSERT INTO `grupo_atleta` (`id_grupo_atleta`, `id_grupo`, `id_atleta`, `fecha_asignacion`) VALUES
(1, 6, 1, '2026-06-11'),
(4, 20, 4, '2026-07-04'),
(5, 20, 2, '2026-07-05'),
(6, 20, 3, '2026-07-05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_entrenador`
--

CREATE TABLE `grupo_entrenador` (
  `id_grupo_entrenador` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_entrenador` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lesiones`
--

CREATE TABLE `lesiones` (
  `id_lesion` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `zona_anatomica` enum('Hombro','Rodilla','Espalda','Codo','Tobillo','Cervical','Lumbar','Muslo','Gemelo','Pie','Otra') NOT NULL,
  `lado` enum('Izquierdo','Derecho','Bilateral') DEFAULT NULL,
  `tipo` enum('Sobreuso','Aguda','Recidiva') NOT NULL,
  `nivel_molestia` int(11) NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_estimada_recup` date DEFAULT NULL,
  `estado` enum('Activa','EnRehabilitacion','Recuperada','Cronica') NOT NULL DEFAULT 'Activa',
  `profesional` varchar(200) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `macrociclos`
--

CREATE TABLE `macrociclos` (
  `id_macrociclo` int(11) NOT NULL,
  `id_temporada` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `id_evento_objetivo` int(11) DEFAULT NULL,
  `estado` enum('Planificado','En Progreso','Finalizado') NOT NULL DEFAULT 'Planificado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `macrociclos`
--

INSERT INTO `macrociclos` (`id_macrociclo`, `id_temporada`, `id_grupo`, `nombre`, `fecha_inicio`, `fecha_fin`, `id_evento_objetivo`, `estado`) VALUES
(1, 1, 1, 'iiiii', '2026-06-10', '2026-06-17', 1, 'Planificado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_evento` int(11) DEFAULT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia_m` int(11) NOT NULL,
  `tipo_piscina` enum('25m','50m') NOT NULL DEFAULT '50m',
  `tiempo_final_seg` decimal(8,2) NOT NULL,
  `tiempo_reaccion_seg` decimal(5,2) DEFAULT NULL,
  `tiempo_viraje_seg` decimal(5,2) DEFAULT NULL,
  `nivel_evento` enum('Regional','Nacional','Internacional','Control') NOT NULL DEFAULT 'Control',
  `es_pb` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marca`, `id_atleta`, `id_sesion`, `id_evento`, `estilo`, `distancia_m`, `tipo_piscina`, `tiempo_final_seg`, `tiempo_reaccion_seg`, `tiempo_viraje_seg`, `nivel_evento`, `es_pb`, `fecha`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 1, 1, 'Libre', 50, '25m', 0.00, NULL, NULL, 'Control', 0, '0000-00-00', NULL, '2026-06-15 13:27:53'),
(2, 1, 1, 1, 'Libre', 80, '50m', 2.00, NULL, NULL, 'Control', 0, '2026-06-15', NULL, '2026-06-15 13:28:54'),
(3, 1, 1, NULL, 'Libre', 100, '50m', 2.00, NULL, NULL, 'Control', 0, '2026-06-15', NULL, '2026-06-15 13:31:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas_splits`
--

CREATE TABLE `marcas_splits` (
  `id_split` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `parcial_numero` int(11) NOT NULL,
  `distancia_parcial_m` int(11) NOT NULL DEFAULT 50,
  `tiempo_parcial_seg` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas_swolf`
--

CREATE TABLE `marcas_swolf` (
  `id_swolf` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `num_brazadas` int(11) NOT NULL,
  `swolf` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mediciones_antropometricas`
--

CREATE TABLE `mediciones_antropometricas` (
  `id_medicion` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `talla_cm` decimal(5,1) DEFAULT NULL,
  `envergadura_cm` decimal(5,1) DEFAULT NULL,
  `perimetro_abdominal_cm` decimal(5,1) DEFAULT NULL,
  `imc` decimal(4,1) DEFAULT NULL,
  `porcentaje_grasa` decimal(4,1) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesociclos`
--

CREATE TABLE `mesociclos` (
  `id_mesociclo` int(11) NOT NULL,
  `id_macrociclo` int(11) NOT NULL,
  `id_fase` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `semana_inicio` int(11) DEFAULT NULL,
  `semana_fin` int(11) DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `volumen_objetivo_m` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mesociclos`
--

INSERT INTO `mesociclos` (`id_mesociclo`, `id_macrociclo`, `id_fase`, `nombre`, `semana_inicio`, `semana_fin`, `objetivo`, `volumen_objetivo_m`) VALUES
(2, 1, 1, 'jjjjjj', 8, 0, 'ijnn', 88);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metas_competitivas`
--

CREATE TABLE `metas_competitivas` (
  `id_meta` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia` int(11) NOT NULL,
  `marca_objetivo_seg` decimal(8,2) DEFAULT NULL,
  `pb_actual_seg` decimal(8,2) DEFAULT NULL,
  `diferencia_pct` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `microciclos`
--

CREATE TABLE `microciclos` (
  `id_microciclo` int(11) NOT NULL,
  `id_mesociclo` int(11) NOT NULL,
  `numero_semana` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `volumen_planificado_m` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `microciclos`
--

INSERT INTO `microciclos` (`id_microciclo`, `id_mesociclo`, `numero_semana`, `fecha_inicio`, `fecha_fin`, `volumen_planificado_m`) VALUES
(3, 2, 7, '2026-06-01', '2026-06-18', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocolo_retorno`
--

CREATE TABLE `protocolo_retorno` (
  `id_paso` int(11) NOT NULL,
  `id_lesion` int(11) NOT NULL,
  `descripcion_paso` varchar(255) NOT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_completado` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_rpe`
--

CREATE TABLE `registro_rpe` (
  `id_rpe` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `rpe` int(11) NOT NULL,
  `horas_sueno` decimal(3,1) DEFAULT NULL,
  `calidad_sueno` int(11) DEFAULT NULL,
  `sensacion_muscular` int(11) DEFAULT NULL,
  `estres_percibido` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `metros_nadados` int(11) DEFAULT NULL,
  `duracion_minutos` int(11) DEFAULT NULL,
  `srpe` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglas_ia`
--

CREATE TABLE `reglas_ia` (
  `id_regla` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `antecedente` text NOT NULL COMMENT 'JSON con condiciones',
  `consecuente` text NOT NULL COMMENT 'Texto de recomendación',
  `prioridad` int(11) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'En plan_seguridad',
  `descripcion_explicativa` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reglas_ia`
--

INSERT INTO `reglas_ia` (`id_regla`, `codigo`, `nombre`, `antecedente`, `consecuente`, `prioridad`, `activa`, `id_usuario_creador`, `descripcion_explicativa`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'R-001', 'Sobreentrenamiento por ACWR alto y RPE', '{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.5},{\"campo\":\"rpe_promedio_3\",\"operador\":\">\",\"valor\":8}],\"logica\":\"AND\"}', 'Riesgo de sobreentrenamiento. Reducir volumen 30%. Evaluar descanso 24h.', 10, 1, NULL, 'ACWR mayor a 1.5 combinado con RPE promedio mayor a 8 en las últimas 3 sesiones.', '2026-05-17 16:39:25', NULL),
(2, 'R-002', 'Alto riesgo de re-lesión', '{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.5},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":true}],\"logica\":\"AND\"}', 'Alto riesgo de re-lesión. Suspender carga completa. Derivar a médico.', 9, 1, NULL, 'ACWR elevado con lesión activa presente.', '2026-05-17 16:39:25', NULL),
(3, 'R-003', 'Fatiga por privación de sueño', '{\"condiciones\":[{\"campo\":\"rpe_promedio_3\",\"operador\":\">\",\"valor\":7},{\"campo\":\"horas_sueno_promedio\",\"operador\":\"<\",\"valor\":6},{\"campo\":\"sesiones_consecutivas\",\"operador\":\">=\",\"valor\":3}],\"logica\":\"AND\"}', 'Fatiga acumulada por privación de sueño. Programar sesión de recuperación activa.', 8, 1, NULL, 'RPE elevado con promedio de sueño menor a 6 horas en 3+ sesiones.', '2026-05-17 16:39:25', NULL),
(4, 'R-004', 'Carga elevada en fase de Acumulación', '{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Acumulacion\"},{\"campo\":\"srpe_promedio_semanal\",\"operador\":\">\",\"valor\":600}],\"logica\":\"AND\"}', 'Carga subjetiva elevada para fase de acumulación. Reducir intensidad de series principales.', 7, 1, NULL, 'En fase de acumulación el sRPE semanal no debería exceder el umbral.', '2026-05-17 16:39:25', NULL),
(5, 'R-005', 'Exceso de volumen en Tapering', '{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_volumen\",\"operador\":\">\",\"valor\":10}],\"logica\":\"AND\"}', 'Exceso de volumen en taper. Ajustar a planificación original de realización.', 8, 1, NULL, 'Volumen ejecutado no debería superar el planificado en más de 10% en taper.', '2026-05-17 16:39:25', NULL),
(6, 'R-006', 'Transmutación efectiva', '{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Transmutacion\"},{\"campo\":\"mejora_marca_fase\",\"operador\":\">\",\"valor\":3}],\"logica\":\"AND\"}', 'Transmutación efectiva. Mantener intensidad y progresión actual.', 5, 1, NULL, 'Mejora mayor al 3% en marcas de control durante transmutación.', '2026-05-17 16:39:25', NULL),
(7, 'R-007', 'Estirón de crecimiento', '{\"condiciones\":[{\"campo\":\"crecimiento_estatura_6m\",\"operador\":\">\",\"valor\":3},{\"campo\":\"categoria\",\"operador\":\"IN\",\"valor\":[\"Pre-Infantil\",\"Infantil A\",\"Infantil B\",\"Juvenil A\"]}],\"logica\":\"AND\"}', 'Estirón de crecimiento detectado. Monitorear técnica y reducir carga en hombros.', 6, 1, NULL, 'Crecimiento mayor a 3cm en 6 meses en categorías infantil/juvenil.', '2026-05-17 16:39:25', NULL),
(8, 'R-008', 'Readiness óptimo', '{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">=\",\"valor\":0.8},{\"campo\":\"acwr\",\"operador\":\"<=\",\"valor\":1.3},{\"campo\":\"rpe_promedio_3\",\"operador\":\">=\",\"valor\":5},{\"campo\":\"rpe_promedio_3\",\"operador\":\"<=\",\"valor\":7},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":false}],\"logica\":\"AND\"}', 'Readiness óptimo. Mantener carga planificada.', 1, 1, NULL, 'ACWR en rango óptimo (0.8-1.3), RPE moderado (5-7) y sin lesiones activas.', '2026-05-17 16:39:25', NULL),
(9, 'R-009', 'Desviación significativa del plan', '{\"condiciones\":[{\"campo\":\"sesiones_desviacion\",\"operador\":\">=\",\"valor\":3},{\"campo\":\"desviacion_volumen\",\"operador\":\"<\",\"valor\":-20}],\"logica\":\"AND\"}', 'Desviación significativa del plan en 3+ sesiones. Evaluar causas y reajustar.', 6, 1, NULL, 'Volumen 20% o más por debajo del planificado en 3+ sesiones consecutivas.', '2026-05-17 16:39:25', NULL),
(10, 'R-010', 'Tapering efectivo', '{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_marca_control\",\"operador\":\"<=\",\"valor\":2}],\"logica\":\"AND\"}', 'Tapering efectivo. Mantener plan de competición.', 4, 1, NULL, 'Marca en control dentro del 2% del PB en fase de realización.', '2026-05-17 16:39:25', NULL),
(11, 'R-011', 'Tapering inefectivo', '{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_marca_control\",\"operador\":\">\",\"valor\":3}],\"logica\":\"AND\"}', 'Tapering inefectivo. Revisar volumen y distribución de la semana de realización.', 7, 1, NULL, 'Marca en control peor en más de 3% respecto al PB en taper.', '2026-05-17 16:39:25', NULL),
(12, 'R-012', 'Desacondicionamiento por baja carga', '{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\"<\",\"valor\":0.8}],\"logica\":\"AND\"}', 'Atleta sub-entrenado. Incrementar progresivamente la carga.', 6, 1, NULL, 'ACWR menor a 0.8 indica carga aguda muy por debajo de la crónica.', '2026-05-17 16:39:25', NULL),
(13, 'R-013', 'Alerta por asistencia irregular', '{\"condiciones\":[{\"campo\":\"inasistencias_consecutivas\",\"operador\":\">=\",\"valor\":3},{\"campo\":\"inasistencias_justificadas\",\"operador\":\"=\",\"valor\":false}],\"logica\":\"AND\"}', '3+ inasistencias consecutivas sin justificación. Contactar al representante.', 5, 1, NULL, 'Atleta con 3 o más inasistencias consecutivas sin justificar.', '2026-05-17 16:39:25', NULL),
(14, 'R-014', 'Variación de peso relevante', '{\"condiciones\":[{\"campo\":\"variacion_peso_30d\",\"operador\":\">\",\"valor\":5}],\"logica\":\"AND\"}', 'Variación de peso mayor al 5% en 30 días. Evaluar con médico.', 7, 1, NULL, 'Cambio significativo de peso corporal en período corto.', '2026-05-17 16:39:25', NULL),
(15, 'R-015', 'Riesgo en competición próxima', '{\"condiciones\":[{\"campo\":\"dias_para_competencia\",\"operador\":\"<=\",\"valor\":7},{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.3},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":true}],\"logica\":\"AND\"}', 'Competencia próxima (≤7 días) con carga elevada y lesión activa. Reevaluar participación.', 10, 1, NULL, 'Condiciones de riesgo a menos de 7 días de competencia.', '2026-05-17 16:39:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglas_log`
--

CREATE TABLE `reglas_log` (
  `id_log` int(11) NOT NULL,
  `id_regla` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `fecha_disparo` datetime NOT NULL DEFAULT current_timestamp(),
  `valores_hechos` text DEFAULT NULL,
  `recomendacion_generada` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `representantes`
--

CREATE TABLE `representantes` (
  `id_representante` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `parentesco` enum('Padre','Madre','Tutor','Otro') NOT NULL,
  `telefono_principal` varchar(20) DEFAULT NULL,
  `telefono_secundario` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `representantes`
--

INSERT INTO `representantes` (`id_representante`, `cedula`, `nombres`, `apellidos`, `parentesco`, `telefono_principal`, `telefono_secundario`, `correo`, `direccion`, `id_usuario`, `fecha_creacion`) VALUES
(1, '3033333', 'wdddc f', 'dd', 'Madre', '8888888877', '8888888889', 'hheeh@gmail.com', 'ddddd', 1, '2026-05-21 19:05:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `series_sesion`
--

CREATE TABLE `series_sesion` (
  `id_serie` int(11) NOT NULL,
  `id_sesion` int(11) NOT NULL,
  `id_drill` int(11) DEFAULT NULL,
  `orden_ejecucion` int(11) NOT NULL,
  `bloque` enum('Calentamiento','Principal','VueltaCalma') NOT NULL DEFAULT 'Principal',
  `ejercicio_descripcion` varchar(255) DEFAULT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `distancia_m` int(11) DEFAULT NULL,
  `descanso_seg` int(11) DEFAULT NULL,
  `zona_intensidad` enum('Z1','Z2','Z3','Z4','Z5') DEFAULT NULL,
  `ritmo_objetivo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `series_sesion`
--

INSERT INTO `series_sesion` (`id_serie`, `id_sesion`, `id_drill`, `orden_ejecucion`, `bloque`, `ejercicio_descripcion`, `repeticiones`, `distancia_m`, `descanso_seg`, `zona_intensidad`, `ritmo_objetivo`) VALUES
(1, 1, NULL, 2, 'Principal', 'Juegso', 23, 44, 32, 'Z1', '33'),
(6, 11, NULL, 1, 'Calentamiento', NULL, 1, 50, 15, 'Z1', '99'),
(7, 10, NULL, 1, 'Calentamiento', NULL, 1, 50, 15, 'Z1', '23'),
(8, 10, NULL, 2, 'Principal', 'solar ', 1, 25, 15, 'Z4', '88'),
(11, 12, 10, 1, 'Principal', NULL, 1, 25, 15, 'Z1', '12'),
(12, 12, 7, 2, 'Calentamiento', NULL, 1, 50, 15, 'Z1', '23'),
(13, 9, NULL, 1, 'Calentamiento', NULL, 1, 50, 15, 'Z1', 'wee');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id_sesion` int(11) NOT NULL,
  `id_microciclo` int(11) DEFAULT NULL,
  `id_grupo` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_sesion` enum('Tecnica','Resistencia','Velocidad','Recuperacion','Fuerza','Flexibilidad','Competencia') NOT NULL,
  `id_fase_actual` int(11) DEFAULT NULL,
  `calentamiento` text DEFAULT NULL,
  `vuelta_calma` text DEFAULT NULL,
  `volumen_planificado` int(11) DEFAULT NULL,
  `volumen_ejecutado` int(11) DEFAULT NULL,
  `duracion_minutos` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('Planificada','Completada','Parcial','Cancelada') NOT NULL DEFAULT 'Planificada',
  `id_entrenador` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`id_sesion`, `id_microciclo`, `id_grupo`, `fecha`, `tipo_sesion`, `id_fase_actual`, `calentamiento`, `vuelta_calma`, `volumen_planificado`, `volumen_ejecutado`, `duracion_minutos`, `observaciones`, `estado`, `id_entrenador`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 3, 1, '2026-06-11', 'Recuperacion', 1, 'jjjj', '34', 99, 99, 88, 'nada', 'Completada', NULL, '2026-06-11 00:16:10', '2026-06-12 01:55:08'),
(5, NULL, 1, '2026-06-12', 'Tecnica', NULL, NULL, NULL, 1000, NULL, NULL, NULL, 'Cancelada', 3, '2026-06-12 02:00:14', '2026-06-12 02:37:04'),
(9, NULL, 5, '2026-07-02', 'Tecnica', 0, 'lNasa', 'nndnd', 50, NULL, 33, 'Para bailar', 'Planificada', 1, '2026-06-12 02:25:51', '2026-07-02 14:11:13'),
(10, NULL, 5, '2026-06-12', 'Tecnica', 0, '200 metros de nado libre', '23 calma', 75, NULL, 89, 'Para competir', 'Planificada', 1, '2026-06-12 02:46:37', '2026-06-12 10:47:32'),
(11, NULL, 3, '2026-06-12', 'Tecnica', 0, 'hxhhd', 'jdjj', 50, 50, 66, 'hhh', 'Completada', 1, '2026-06-12 10:18:40', '2026-06-26 23:53:04'),
(12, NULL, 6, '2026-07-02', 'Recuperacion', 0, '200 m', '100 m', 75, NULL, 45, 'jjs', 'Cancelada', 1, '2026-07-02 13:54:32', '2026-07-02 14:12:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporadas`
--

CREATE TABLE `temporadas` (
  `id_temporada` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `temporadas`
--

INSERT INTO `temporadas` (`id_temporada`, `nombre`, `fecha_inicio`, `fecha_fin`, `activa`) VALUES
(1, 'juego', '2026-06-03', '2026-06-10', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiempos_corte_evento`
--

CREATE TABLE `tiempos_corte_evento` (
  `id_tiempo_corte` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia` int(11) NOT NULL,
  `tiempo_corte_segundos` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_asistencia_resumen`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_asistencia_resumen` (
`id_atleta` int(11)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`total_registros` bigint(21)
,`presentes` decimal(22,0)
,`ausentes` decimal(22,0)
,`tardanzas` decimal(22,0)
,`porcentaje_asistencia` decimal(27,1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_atleta_info`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_atleta_info` (
`id_atleta` int(11)
,`cedula` varchar(20)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`fecha_nacimiento` date
,`edad_actual` bigint(21)
,`sexo` enum('M','F')
,`estado` enum('Activo','Inactivo','Retirado','Transferido')
,`categoria` varchar(50)
,`grupo_sanguineo` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`alergias` text
,`numero_feveda` varchar(50)
,`fecha_registro_club` date
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_carga_semanal`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_carga_semanal` (
`id_atleta` int(11)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`fecha` date
,`srpe_total` int(11)
,`volumen_total_m` int(11)
,`carga_aguda_7d` decimal(8,2)
,`carga_cronica_28d` decimal(8,2)
,`acwr` decimal(5,2)
,`semaforo_acwr` enum('Verde','Amarillo','Rojo')
,`monotonia_semanal` decimal(5,2)
,`strain_semanal` decimal(8,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_lesion_activa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_lesion_activa` (
`id_atleta` int(11)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`zona_anatomica` enum('Hombro','Rodilla','Espalda','Codo','Tobillo','Cervical','Lumbar','Muslo','Gemelo','Pie','Otra')
,`tipo` enum('Sobreuso','Aguda','Recidiva')
,`nivel_molestia` int(11)
,`estado` enum('Activa','EnRehabilitacion','Recuperada','Cronica')
,`fecha_inicio` date
,`fecha_estimada_recup` date
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ranking`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ranking` (
`id_marca` int(11)
,`id_atleta` int(11)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado')
,`distancia_m` int(11)
,`tipo_piscina` enum('25m','50m')
,`tiempo_final_seg` decimal(8,2)
,`fecha` date
,`categoria` varchar(50)
,`sexo` enum('M','F')
,`posicion_ranking` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_asistencia_resumen`
--
DROP TABLE IF EXISTS `v_asistencia_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_asistencia_resumen`  AS SELECT `asis`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, count(0) AS `total_registros`, sum(case when `asis`.`estado` = 'Presente' then 1 else 0 end) AS `presentes`, sum(case when `asis`.`estado` in ('Ausente','Justificado') then 1 else 0 end) AS `ausentes`, sum(case when `asis`.`estado` = 'Tardanza' then 1 else 0 end) AS `tardanzas`, round(sum(case when `asis`.`estado` in ('Presente','Tardanza') then 1 else 0 end) / count(0) * 100,1) AS `porcentaje_asistencia` FROM (`asistencia` `asis` join `atletas` `a` on(`asis`.`id_atleta` = `a`.`id_atleta`)) WHERE `asis`.`fecha` >= curdate() - interval 30 day GROUP BY `asis`.`id_atleta`, `a`.`nombres`, `a`.`apellidos` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_atleta_info`
--
DROP TABLE IF EXISTS `v_atleta_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_atleta_info`  AS SELECT `a`.`id_atleta` AS `id_atleta`, `a`.`cedula` AS `cedula`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `a`.`fecha_nacimiento` AS `fecha_nacimiento`, timestampdiff(YEAR,`a`.`fecha_nacimiento`,curdate()) AS `edad_actual`, `a`.`sexo` AS `sexo`, `a`.`estado` AS `estado`, `c`.`nombre` AS `categoria`, `dm`.`grupo_sanguineo` AS `grupo_sanguineo`, `dm`.`alergias` AS `alergias`, `dm`.`numero_feveda` AS `numero_feveda`, `a`.`fecha_registro_club` AS `fecha_registro_club` FROM ((`atletas` `a` join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) left join `atleta_datos_medicos` `dm` on(`a`.`id_atleta` = `dm`.`id_atleta`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_carga_semanal`
--
DROP TABLE IF EXISTS `v_carga_semanal`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_carga_semanal`  AS SELECT `cd`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `cd`.`fecha` AS `fecha`, `cd`.`srpe_total` AS `srpe_total`, `cd`.`volumen_total_m` AS `volumen_total_m`, `cd`.`carga_aguda_7d` AS `carga_aguda_7d`, `cd`.`carga_cronica_28d` AS `carga_cronica_28d`, `cd`.`acwr` AS `acwr`, `cd`.`semaforo_acwr` AS `semaforo_acwr`, `cd`.`monotonia_semanal` AS `monotonia_semanal`, `cd`.`strain_semanal` AS `strain_semanal` FROM (`carga_diaria` `cd` join `atletas` `a` on(`cd`.`id_atleta` = `a`.`id_atleta`)) WHERE `cd`.`fecha` >= curdate() - interval 30 day ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_lesion_activa`
--
DROP TABLE IF EXISTS `v_lesion_activa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_lesion_activa`  AS SELECT `l`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `l`.`zona_anatomica` AS `zona_anatomica`, `l`.`tipo` AS `tipo`, `l`.`nivel_molestia` AS `nivel_molestia`, `l`.`estado` AS `estado`, `l`.`fecha_inicio` AS `fecha_inicio`, `l`.`fecha_estimada_recup` AS `fecha_estimada_recup` FROM (`lesiones` `l` join `atletas` `a` on(`l`.`id_atleta` = `a`.`id_atleta`)) WHERE `l`.`estado` in ('Activa','EnRehabilitacion') ORDER BY `l`.`fecha_inicio` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ranking`
--
DROP TABLE IF EXISTS `v_ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ranking`  AS SELECT `m`.`id_marca` AS `id_marca`, `m`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `m`.`estilo` AS `estilo`, `m`.`distancia_m` AS `distancia_m`, `m`.`tipo_piscina` AS `tipo_piscina`, `m`.`tiempo_final_seg` AS `tiempo_final_seg`, `m`.`fecha` AS `fecha`, `c`.`nombre` AS `categoria`, `a`.`sexo` AS `sexo`, row_number() over ( partition by `m`.`estilo`,`m`.`distancia_m`,`a`.`id_categoria`,`a`.`sexo` order by `m`.`tiempo_final_seg`) AS `posicion_ranking` FROM ((`marcas` `m` join `atletas` `a` on(`m`.`id_atleta` = `a`.`id_atleta`)) join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) WHERE `m`.`es_pb` = 1 ;

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
  ADD KEY `fk_ac_grupo` (`id_grupo`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`,`id_sesion`),
  ADD KEY `fk_as_sesion` (`id_sesion`),
  ADD KEY `fk_as_carril` (`id_asignacion_carril`);

--
-- Indices de la tabla `atletas`
--
ALTER TABLE `atletas`
  ADD PRIMARY KEY (`id_atleta`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `fk_at_cat` (`id_categoria`);

--
-- Indices de la tabla `atleta_datos_medicos`
--
ALTER TABLE `atleta_datos_medicos`
  ADD PRIMARY KEY (`id_datos_medicos`),
  ADD UNIQUE KEY `id_atleta` (`id_atleta`);

--
-- Indices de la tabla `atleta_representante`
--
ALTER TABLE `atleta_representante`
  ADD PRIMARY KEY (`id_atleta_rep`),
  ADD UNIQUE KEY `id_atleta` (`id_atleta`,`id_representante`),
  ADD KEY `fk_ar_repre` (`id_representante`);

--
-- Indices de la tabla `bloques_horarios`
--
ALTER TABLE `bloques_horarios`
  ADD PRIMARY KEY (`id_bloque`),
  ADD UNIQUE KEY `dia_semana` (`dia_semana`,`hora_inicio`);

--
-- Indices de la tabla `carga_diaria`
--
ALTER TABLE `carga_diaria`
  ADD PRIMARY KEY (`id_carga`),
  ADD UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`);

--
-- Indices de la tabla `carriles`
--
ALTER TABLE `carriles`
  ADD PRIMARY KEY (`id_carril`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Indices de la tabla `categorias_feveda`
--
ALTER TABLE `categorias_feveda`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `drills`
--
ALTER TABLE `drills`
  ADD PRIMARY KEY (`id_drill`);

--
-- Indices de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  ADD PRIMARY KEY (`id_entrenador`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `entrenador_asignacion`
--
ALTER TABLE `entrenador_asignacion`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_atleta`),
  ADD KEY `fk_ea_atleta` (`id_atleta`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`);

--
-- Indices de la tabla `evento_inscripcion`
--
ALTER TABLE `evento_inscripcion`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `id_evento` (`id_evento`,`id_atleta`),
  ADD KEY `fk_ei_atleta` (`id_atleta`);

--
-- Indices de la tabla `factores_conversion`
--
ALTER TABLE `factores_conversion`
  ADD PRIMARY KEY (`id_factor`),
  ADD UNIQUE KEY `estilo` (`estilo`,`distancia_m`,`direccion`);

--
-- Indices de la tabla `fases_periodizacion`
--
ALTER TABLE `fases_periodizacion`
  ADD PRIMARY KEY (`id_fase`),
  ADD KEY `fk_fp_macro` (`id_macrociclo`);

--
-- Indices de la tabla `grupos_entrenamiento`
--
ALTER TABLE `grupos_entrenamiento`
  ADD PRIMARY KEY (`id_grupo`),
  ADD KEY `fk_grupos_entrenador` (`id_entrenador`);

--
-- Indices de la tabla `grupo_atleta`
--
ALTER TABLE `grupo_atleta`
  ADD PRIMARY KEY (`id_grupo_atleta`),
  ADD UNIQUE KEY `id_grupo` (`id_grupo`,`id_atleta`),
  ADD KEY `fk_ga_atleta` (`id_atleta`);

--
-- Indices de la tabla `grupo_entrenador`
--
ALTER TABLE `grupo_entrenador`
  ADD PRIMARY KEY (`id_grupo_entrenador`),
  ADD KEY `fk_historial_grupo` (`id_grupo`),
  ADD KEY `fk_historial_entrenador` (`id_entrenador`);

--
-- Indices de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD PRIMARY KEY (`id_lesion`),
  ADD KEY `fk_le_atleta` (`id_atleta`);

--
-- Indices de la tabla `macrociclos`
--
ALTER TABLE `macrociclos`
  ADD PRIMARY KEY (`id_macrociclo`),
  ADD KEY `fk_mc_temp` (`id_temporada`),
  ADD KEY `fk_mc_grupo` (`id_grupo`),
  ADD KEY `fk_mc_evento` (`id_evento_objetivo`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`),
  ADD KEY `fk_mk_atleta` (`id_atleta`),
  ADD KEY `fk_mk_sesion` (`id_sesion`),
  ADD KEY `fk_mk_evento` (`id_evento`);

--
-- Indices de la tabla `marcas_splits`
--
ALTER TABLE `marcas_splits`
  ADD PRIMARY KEY (`id_split`),
  ADD UNIQUE KEY `id_marca` (`id_marca`,`parcial_numero`);

--
-- Indices de la tabla `marcas_swolf`
--
ALTER TABLE `marcas_swolf`
  ADD PRIMARY KEY (`id_swolf`),
  ADD UNIQUE KEY `id_marca` (`id_marca`);

--
-- Indices de la tabla `mediciones_antropometricas`
--
ALTER TABLE `mediciones_antropometricas`
  ADD PRIMARY KEY (`id_medicion`),
  ADD UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`);

--
-- Indices de la tabla `mesociclos`
--
ALTER TABLE `mesociclos`
  ADD PRIMARY KEY (`id_mesociclo`),
  ADD KEY `fk_me_macro` (`id_macrociclo`),
  ADD KEY `fk_me_fase` (`id_fase`);

--
-- Indices de la tabla `metas_competitivas`
--
ALTER TABLE `metas_competitivas`
  ADD PRIMARY KEY (`id_meta`),
  ADD UNIQUE KEY `id_evento` (`id_evento`,`id_atleta`,`estilo`,`distancia`),
  ADD KEY `fk_mce_atleta` (`id_atleta`);

--
-- Indices de la tabla `microciclos`
--
ALTER TABLE `microciclos`
  ADD PRIMARY KEY (`id_microciclo`),
  ADD KEY `fk_mi_meso` (`id_mesociclo`);

--
-- Indices de la tabla `protocolo_retorno`
--
ALTER TABLE `protocolo_retorno`
  ADD PRIMARY KEY (`id_paso`),
  ADD UNIQUE KEY `id_lesion` (`id_lesion`,`descripcion_paso`);

--
-- Indices de la tabla `registro_rpe`
--
ALTER TABLE `registro_rpe`
  ADD PRIMARY KEY (`id_rpe`),
  ADD KEY `fk_rpe_atleta` (`id_atleta`),
  ADD KEY `fk_rpe_sesion` (`id_sesion`);

--
-- Indices de la tabla `reglas_ia`
--
ALTER TABLE `reglas_ia`
  ADD PRIMARY KEY (`id_regla`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `reglas_log`
--
ALTER TABLE `reglas_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_rl_regla` (`id_regla`),
  ADD KEY `fk_rl_atleta` (`id_atleta`),
  ADD KEY `fk_rl_sesion` (`id_sesion`);

--
-- Indices de la tabla `representantes`
--
ALTER TABLE `representantes`
  ADD PRIMARY KEY (`id_representante`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `series_sesion`
--
ALTER TABLE `series_sesion`
  ADD PRIMARY KEY (`id_serie`),
  ADD KEY `fk_ss_sesion` (`id_sesion`),
  ADD KEY `fk_ss_drill` (`id_drill`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `fk_se_micro` (`id_microciclo`),
  ADD KEY `fk_se_grupo` (`id_grupo`),
  ADD KEY `fk_se_fase` (`id_fase_actual`),
  ADD KEY `fk_sesiones_entrenador` (`id_entrenador`);

--
-- Indices de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  ADD PRIMARY KEY (`id_temporada`);

--
-- Indices de la tabla `tiempos_corte_evento`
--
ALTER TABLE `tiempos_corte_evento`
  ADD PRIMARY KEY (`id_tiempo_corte`),
  ADD UNIQUE KEY `id_evento` (`id_evento`,`id_categoria`,`estilo`,`distancia`),
  ADD KEY `fk_tce_categoria` (`id_categoria`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_carril`
--
ALTER TABLE `asignacion_carril`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atletas`
--
ALTER TABLE `atletas`
  MODIFY `id_atleta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `atleta_datos_medicos`
--
ALTER TABLE `atleta_datos_medicos`
  MODIFY `id_datos_medicos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `atleta_representante`
--
ALTER TABLE `atleta_representante`
  MODIFY `id_atleta_rep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloques_horarios`
--
ALTER TABLE `bloques_horarios`
  MODIFY `id_bloque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `carga_diaria`
--
ALTER TABLE `carga_diaria`
  MODIFY `id_carga` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carriles`
--
ALTER TABLE `carriles`
  MODIFY `id_carril` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categorias_feveda`
--
ALTER TABLE `categorias_feveda`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `drills`
--
ALTER TABLE `drills`
  MODIFY `id_drill` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  MODIFY `id_entrenador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `entrenador_asignacion`
--
ALTER TABLE `entrenador_asignacion`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `evento_inscripcion`
--
ALTER TABLE `evento_inscripcion`
  MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `factores_conversion`
--
ALTER TABLE `factores_conversion`
  MODIFY `id_factor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `fases_periodizacion`
--
ALTER TABLE `fases_periodizacion`
  MODIFY `id_fase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `grupos_entrenamiento`
--
ALTER TABLE `grupos_entrenamiento`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `grupo_atleta`
--
ALTER TABLE `grupo_atleta`
  MODIFY `id_grupo_atleta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `grupo_entrenador`
--
ALTER TABLE `grupo_entrenador`
  MODIFY `id_grupo_entrenador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  MODIFY `id_lesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `macrociclos`
--
ALTER TABLE `macrociclos`
  MODIFY `id_macrociclo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `marcas_splits`
--
ALTER TABLE `marcas_splits`
  MODIFY `id_split` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marcas_swolf`
--
ALTER TABLE `marcas_swolf`
  MODIFY `id_swolf` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mediciones_antropometricas`
--
ALTER TABLE `mediciones_antropometricas`
  MODIFY `id_medicion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesociclos`
--
ALTER TABLE `mesociclos`
  MODIFY `id_mesociclo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `metas_competitivas`
--
ALTER TABLE `metas_competitivas`
  MODIFY `id_meta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `microciclos`
--
ALTER TABLE `microciclos`
  MODIFY `id_microciclo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `protocolo_retorno`
--
ALTER TABLE `protocolo_retorno`
  MODIFY `id_paso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_rpe`
--
ALTER TABLE `registro_rpe`
  MODIFY `id_rpe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reglas_ia`
--
ALTER TABLE `reglas_ia`
  MODIFY `id_regla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `reglas_log`
--
ALTER TABLE `reglas_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `representantes`
--
ALTER TABLE `representantes`
  MODIFY `id_representante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `series_sesion`
--
ALTER TABLE `series_sesion`
  MODIFY `id_serie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  MODIFY `id_temporada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tiempos_corte_evento`
--
ALTER TABLE `tiempos_corte_evento`
  MODIFY `id_tiempo_corte` int(11) NOT NULL AUTO_INCREMENT;

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

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_as_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`),
  ADD CONSTRAINT `fk_as_carril` FOREIGN KEY (`id_asignacion_carril`) REFERENCES `asignacion_carril` (`id_asignacion`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_as_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL;

--
-- Filtros para la tabla `atletas`
--
ALTER TABLE `atletas`
  ADD CONSTRAINT `fk_at_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_feveda` (`id_categoria`);

--
-- Filtros para la tabla `atleta_datos_medicos`
--
ALTER TABLE `atleta_datos_medicos`
  ADD CONSTRAINT `fk_dm_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `atleta_representante`
--
ALTER TABLE `atleta_representante`
  ADD CONSTRAINT `fk_ar_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ar_repre` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carga_diaria`
--
ALTER TABLE `carga_diaria`
  ADD CONSTRAINT `fk_cd_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `entrenador_asignacion`
--
ALTER TABLE `entrenador_asignacion`
  ADD CONSTRAINT `fk_ea_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evento_inscripcion`
--
ALTER TABLE `evento_inscripcion`
  ADD CONSTRAINT `fk_ei_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ei_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fases_periodizacion`
--
ALTER TABLE `fases_periodizacion`
  ADD CONSTRAINT `fk_fp_macro` FOREIGN KEY (`id_macrociclo`) REFERENCES `macrociclos` (`id_macrociclo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupos_entrenamiento`
--
ALTER TABLE `grupos_entrenamiento`
  ADD CONSTRAINT `fk_grupos_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `grupo_atleta`
--
ALTER TABLE `grupo_atleta`
  ADD CONSTRAINT `fk_ga_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ga_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupo_entrenador`
--
ALTER TABLE `grupo_entrenador`
  ADD CONSTRAINT `fk_historial_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD CONSTRAINT `fk_le_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `macrociclos`
--
ALTER TABLE `macrociclos`
  ADD CONSTRAINT `fk_mc_evento` FOREIGN KEY (`id_evento_objetivo`) REFERENCES `eventos` (`id_evento`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mc_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`),
  ADD CONSTRAINT `fk_mc_temp` FOREIGN KEY (`id_temporada`) REFERENCES `temporadas` (`id_temporada`);

--
-- Filtros para la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD CONSTRAINT `fk_mk_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mk_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mk_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL;

--
-- Filtros para la tabla `marcas_splits`
--
ALTER TABLE `marcas_splits`
  ADD CONSTRAINT `fk_ms_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE;

--
-- Filtros para la tabla `marcas_swolf`
--
ALTER TABLE `marcas_swolf`
  ADD CONSTRAINT `fk_sw_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mediciones_antropometricas`
--
ALTER TABLE `mediciones_antropometricas`
  ADD CONSTRAINT `fk_ma_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mesociclos`
--
ALTER TABLE `mesociclos`
  ADD CONSTRAINT `fk_me_fase` FOREIGN KEY (`id_fase`) REFERENCES `fases_periodizacion` (`id_fase`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_me_macro` FOREIGN KEY (`id_macrociclo`) REFERENCES `macrociclos` (`id_macrociclo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `metas_competitivas`
--
ALTER TABLE `metas_competitivas`
  ADD CONSTRAINT `fk_mce_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mce_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE;

--
-- Filtros para la tabla `microciclos`
--
ALTER TABLE `microciclos`
  ADD CONSTRAINT `fk_mi_meso` FOREIGN KEY (`id_mesociclo`) REFERENCES `mesociclos` (`id_mesociclo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `protocolo_retorno`
--
ALTER TABLE `protocolo_retorno`
  ADD CONSTRAINT `fk_pr_lesion` FOREIGN KEY (`id_lesion`) REFERENCES `lesiones` (`id_lesion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_rpe`
--
ALTER TABLE `registro_rpe`
  ADD CONSTRAINT `fk_rpe_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpe_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reglas_log`
--
ALTER TABLE `reglas_log`
  ADD CONSTRAINT `fk_rl_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rl_regla` FOREIGN KEY (`id_regla`) REFERENCES `reglas_ia` (`id_regla`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rl_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL;

--
-- Filtros para la tabla `series_sesion`
--
ALTER TABLE `series_sesion`
  ADD CONSTRAINT `fk_ss_drill` FOREIGN KEY (`id_drill`) REFERENCES `drills` (`id_drill`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ss_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `fk_se_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`),
  ADD CONSTRAINT `fk_se_micro` FOREIGN KEY (`id_microciclo`) REFERENCES `microciclos` (`id_microciclo`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sesiones_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `tiempos_corte_evento`
--
ALTER TABLE `tiempos_corte_evento`
  ADD CONSTRAINT `fk_tce_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_feveda` (`id_categoria`),
  ADD CONSTRAINT `fk_tce_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
