-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-04-2026 a las 21:22:00
-- Versión del servidor: 10.4.18-MariaDB
-- Versión de PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestion_natacion`
--

-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL, -- Aquí guardarás la clave encriptada (AES-256)
  `rol` enum('Administrador','Entrenador','Atleta') NOT NULL,
  `estatus` tinyint(1) DEFAULT 1, -- 1 para activo, 0 para suspendido
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



--
-- Estructura de tabla para la tabla `atleta`
--

CREATE TABLE `atleta` (
  `id_atleta` int(11) NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fichaje_federativo` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `lateralidad` enum('Derecho','Zurdo','Ambidiestro') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `qr_acceso` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `id_representante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencia`
--

CREATE TABLE `competencia` (
  `id_competencia` int(11) NOT NULL,
  `nombre_evento` varchar(150) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `organizador` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `tipo_piscina` enum('25m','50m') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `nivel` enum('Invitacional','Estadal','Regional','Nacional','Internacional') COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `id_atleta` int(11) DEFAULT NULL,
  `id_competencia` int(11) DEFAULT NULL,
  `estilo` enum('Libre','Espalda','Pecho','Mariposa','Combinado') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `distancia` int(11) DEFAULT NULL,
  `tipo_piscina` enum('25m','50m') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `tiempo_final` time(3) DEFAULT NULL,
  `puntos_fina` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `clima_temperatura` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcial`
--

CREATE TABLE `parcial` (
  `id_parcial` int(11) NOT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `metros_parcial` int(11) DEFAULT NULL,
  `tiempo_parcial` time(3) DEFAULT NULL,
  `frecuencia_brazada` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id_ejercicio` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `estilo` enum('Mariposa','Espalda','Pecho','Libre','Combinado','Varios') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `material` set('Aletas','Paletas','Snorkel','Tabla','Pullbuoy') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `impacto_articular` enum('Bajo','Medio','Alto') COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesociclo`
--

CREATE TABLE `mesociclo` (
  `id_mesociclo` int(11) NOT NULL,
  `id_competencia` int(11) DEFAULT NULL,
  `tipo_bloque` enum('Acumulación','Transformación','Realización') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `objetivo_principal` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `volumen_objetivo` int(11) DEFAULT NULL,
  `microciclos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion_entrenamiento`
--

CREATE TABLE `sesion_entrenamiento` (
  `id_sesion` int(11) NOT NULL,
  `id_mesociclo` int(11) DEFAULT NULL,
  `id_entrenador` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `lugar` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `volumen_total` int(11) DEFAULT NULL,
  `objetivo_sesion` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_sesion`
--

CREATE TABLE `detalle_sesion` (
  `id_detalle` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_ejercicio` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `bloque` enum('Calentamiento','Principal','Afloje') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `distancia_metros` int(11) DEFAULT NULL,
  `intensidad` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `descanso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carga_entrenamiento`
--

CREATE TABLE `carga_entrenamiento` (
  `id_carga` int(11) NOT NULL,
  `id_atleta` int(11) DEFAULT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `rpe_esfuerzo` int(11) DEFAULT NULL,
  `fatiga_previa` int(11) DEFAULT NULL,
  `calidad_sueno` int(11) DEFAULT NULL,
  `dolor_muscular` tinyint(1) DEFAULT NULL,
  `comentarios_atleta` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expediente_medico`
--

CREATE TABLE `expediente_medico` (
  `id_expediente` int(11) NOT NULL,
  `id_atleta` int(11) DEFAULT NULL,
  `tipo_evento` enum('Lesión','Cirugía','Enfermedad','Alergia','Condición Congénita') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `zona_afectada` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `gravedad` enum('Leve','Moderada','Grave') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `limitaciones` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `tratamiento_actual` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_antropometricos`
--

CREATE TABLE `datos_antropometricos` (
  `id_antropometria` int(11) NOT NULL,
  `id_atleta` int(11) DEFAULT NULL,
  `fecha_medicion` date DEFAULT NULL,
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `estatura_cm` decimal(5,2) DEFAULT NULL,
  `envergadura_cm` decimal(5,2) DEFAULT NULL,
  `porcentaje_grasa` decimal(4,2) DEFAULT NULL,
  `porcentaje_musculo` decimal(4,2) DEFAULT NULL,
  `talla_pie` decimal(3,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `representante`
--

CREATE TABLE `representante` (
  `id_representante` int(11) NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `telefono_principal` varchar(20) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `telefono_emergencia` varchar(20) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `parentesco` enum('Padre','Madre','Abuelo/a','Tutor') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `direccion_residencia` text COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_atleta` int(11) DEFAULT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `estatus` enum('Presente','Ausente','Justificado','Tarde') COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `hora_check_in` time DEFAULT NULL,
  `observacion_dia` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Índices para tablas volcadas
--

-- Indices de la tabla `atleta`
ALTER TABLE `atleta`
  ADD PRIMARY KEY (`id_atleta`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `id_representante` (`id_representante`);

-- Indices de la tabla `competencia`
ALTER TABLE `competencia`
  ADD PRIMARY KEY (`id_competencia`);

-- Indices de la tabla `marca`
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD KEY `id_atleta` (`id_atleta`),
  ADD KEY `id_competencia` (`id_competencia`);

-- Indices de la tabla `parcial`
ALTER TABLE `parcial`
  ADD PRIMARY KEY (`id_parcial`),
  ADD KEY `id_marca` (`id_marca`);

-- Indices de la tabla `ejercicio`
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id_ejercicio`);

-- Indices de la tabla `mesociclo`
ALTER TABLE `mesociclo`
  ADD PRIMARY KEY (`id_mesociclo`),
  ADD KEY `id_competencia` (`id_competencia`);

-- Indices de la tabla `sesion_entrenamiento`
ALTER TABLE `sesion_entrenamiento`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `id_mesociclo` (`id_mesociclo`);

-- Indices de la tabla `detalle_sesion`
ALTER TABLE `detalle_sesion`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_sesion` (`id_sesion`),
  ADD KEY `id_ejercicio` (`id_ejercicio`);

-- Indices de la tabla `carga_entrenamiento`
ALTER TABLE `carga_entrenamiento`
  ADD PRIMARY KEY (`id_carga`),
  ADD KEY `id_atleta` (`id_atleta`),
  ADD KEY `id_sesion` (`id_sesion`);

-- Indices de la tabla `expediente_medico`
ALTER TABLE `expediente_medico`
  ADD PRIMARY KEY (`id_expediente`),
  ADD KEY `id_atleta` (`id_atleta`);

-- Indices de la tabla `datos_antropometricos`
ALTER TABLE `datos_antropometricos`
  ADD PRIMARY KEY (`id_antropometria`),
  ADD KEY `id_atleta` (`id_atleta`);

-- Indices de la tabla `representante`
ALTER TABLE `representante`
  ADD PRIMARY KEY (`id_representante`),
  ADD UNIQUE KEY `cedula` (`cedula`);

-- Indices de la tabla `asistencia`
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_atleta` (`id_atleta`),
  ADD KEY `id_sesion` (`id_sesion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

ALTER TABLE `atleta` MODIFY `id_atleta` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `competencia` MODIFY `id_competencia` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `marca` MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `parcial` MODIFY `id_parcial` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ejercicio` MODIFY `id_ejercicio` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mesociclo` MODIFY `id_mesociclo` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sesion_entrenamiento` MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `detalle_sesion` MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `carga_entrenamiento` MODIFY `id_carga` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `expediente_medico` MODIFY `id_expediente` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `datos_antropometricos` MODIFY `id_antropometria` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `representante` MODIFY `id_representante` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `asistencia` MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas (Llaves Foráneas)
--

ALTER TABLE `atleta`
  ADD CONSTRAINT `atleta_ibfk_1` FOREIGN KEY (`id_representante`) REFERENCES `representante` (`id_representante`);

ALTER TABLE `marca`
  ADD CONSTRAINT `marca_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atleta` (`id_atleta`),
  ADD CONSTRAINT `marca_ibfk_2` FOREIGN KEY (`id_competencia`) REFERENCES `competencia` (`id_competencia`);

ALTER TABLE `parcial`
  ADD CONSTRAINT `parcial_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`);

ALTER TABLE `mesociclo`
  ADD CONSTRAINT `mesociclo_ibfk_1` FOREIGN KEY (`id_competencia`) REFERENCES `competencia` (`id_competencia`);

ALTER TABLE `sesion_entrenamiento`
  ADD CONSTRAINT `sesion_entrenamiento_ibfk_1` FOREIGN KEY (`id_mesociclo`) REFERENCES `mesociclo` (`id_mesociclo`);

ALTER TABLE `detalle_sesion`
  ADD CONSTRAINT `detalle_sesion_ibfk_1` FOREIGN KEY (`id_sesion`) REFERENCES `sesion_entrenamiento` (`id_sesion`),
  ADD CONSTRAINT `detalle_sesion_ibfk_2` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicio` (`id_ejercicio`);

ALTER TABLE `carga_entrenamiento`
  ADD CONSTRAINT `carga_entrenamiento_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atleta` (`id_atleta`),
  ADD CONSTRAINT `carga_entrenamiento_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesion_entrenamiento` (`id_sesion`);

ALTER TABLE `expediente_medico`
  ADD CONSTRAINT `expediente_medico_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atleta` (`id_atleta`);

ALTER TABLE `datos_antropometricos`
  ADD CONSTRAINT `datos_antropometricos_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atleta` (`id_atleta`);

ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atleta` (`id_atleta`),
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesion_entrenamiento` (`id_sesion`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;