-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2026 a las 15:12:44
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
-- Base de datos: `sis_seguridad`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `modulo_afectado` varchar(80) NOT NULL,
  `tipo_operacion` enum('CREATE','RESTORE', 'INSERT','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `fecha_operacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id_intento` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `correoIntento` varchar(150) DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_intento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `intentos_login`
--

INSERT INTO `intentos_login` (`id_intento`, `id_usuario`, `correoIntento`, `ip_origen`, `exitoso`, `fecha_intento`) VALUES
(1, NULL, 'admin@sistema.com', '::1', 1, '2026-05-20 15:47:29'),
(2, NULL, 'admin@sistema.com', '::1', 1, '2026-05-20 21:21:21'),
(3, NULL, 'admin@sistema.com', '::1', 1, '2026-05-20 21:24:30'),
(4, NULL, 'admin@sistema.com', '::1', 1, '2026-05-22 13:09:09'),
(5, NULL, 'admin@sistema.com', '::1', 1, '2026-06-01 18:50:38'),
(7, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-06 15:44:02'),
(8, NULL, 'correo@correo.com', '127.0.0.1', 1, '2026-06-06 17:59:29'),
(13, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-06 18:02:09'),
(14, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-06 18:03:15'),
(16, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 08:39:31'),
(17, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 09:09:34'),
(18, NULL, 'correo@correo.com', '127.0.0.1', 1, '2026-06-07 12:05:51'),
(19, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 12:12:39'),
(20, NULL, 'repre@correo.com', '127.0.0.1', 1, '2026-06-07 12:14:28'),
(21, NULL, 'correo@correo.com', '127.0.0.1', 1, '2026-06-07 12:31:44'),
(22, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 12:53:57'),
(24, NULL, 'correo@correo.com', '127.0.0.1', 1, '2026-06-07 13:23:23'),
(25, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 13:26:10'),
(26, NULL, 'correo@correo.com', '127.0.0.1', 1, '2026-06-07 13:27:24'),
(27, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-07 13:28:05'),
(30, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 19:09:36'),
(31, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 19:15:49'),
(32, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 19:44:27'),
(33, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 20:37:33'),
(34, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 20:51:31'),
(55, 6, 'gillermo@correo.com', '127.0.0.1', 1, '2026-06-08 23:15:56'),
(56, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 23:16:23'),
(57, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 23:19:10'),
(98, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 23:47:40'),
(119, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-08 23:50:14'),
(124, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 00:14:36'),
(125, 7, 'juan@correo.com', '127.0.0.1', 1, '2026-06-09 00:15:36'),
(127, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 00:23:57'),
(148, 3, 'admin@sgrd.com', '127.0.0.1', 0, '2026-06-09 00:53:22'),
(149, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 00:53:36'),
(150, NULL, 'asd@adsd.com', '127.0.0.1', 0, '2026-06-09 01:01:59'),
(151, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 01:03:39'),
(152, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 01:06:48'),
(153, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 01:38:56'),
(154, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 01:55:30'),
(155, 3, 'ADMIN@SGRD.COM', '127.0.0.1', 1, '2026-06-09 02:08:41'),
(156, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:38:56'),
(157, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:23'),
(158, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:23'),
(159, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:24'),
(160, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:24'),
(161, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:24'),
(162, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:25'),
(163, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:25'),
(164, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:25'),
(165, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:26'),
(166, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:26'),
(167, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:26'),
(168, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:27'),
(169, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:27'),
(170, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:27'),
(171, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:28'),
(172, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:28'),
(173, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:28'),
(174, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:29'),
(175, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:29'),
(176, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:29'),
(177, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:30'),
(178, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:30'),
(179, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:30'),
(180, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:31'),
(181, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:31'),
(182, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:31'),
(183, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:32'),
(184, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:32'),
(185, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:32'),
(186, 3, 'admin@sgrd.com', '127.0.0.1', 1, '2026-06-09 08:44:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `modulo` varchar(80) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `modulo`, `accion`, `descripcion`) VALUES
(1, 'atletas', 'ver', 'Ver expedientes de atletas'),
(2, 'atletas', 'crear', 'Crear nuevo expediente'),
(3, 'atletas', 'editar', 'Editar expediente existente'),
(4, 'atletas', 'eliminar', 'Cambiar estado del atleta (baja lógica)'),
(5, 'asistencia', 'ver', 'Ver registros de asistencia'),
(6, 'asistencia', 'registrar', 'Registrar asistencia QR o manual'),
(7, 'carriles', 'ver', 'Ver asignación de carriles'),
(8, 'carriles', 'gestionar', 'Crear/editar asignaciones de carriles y horarios'),
(9, 'sesiones', 'ver', 'Ver sesiones planificadas'),
(10, 'sesiones', 'crear', 'Crear sesiones de entrenamiento'),
(11, 'sesiones', 'editar', 'Editar sesiones existentes'),
(12, 'sesiones', 'completar', 'Registrar volumen ejecutado post-sesión'),
(13, 'drills', 'ver', 'Ver catálogo de ejercicios'),
(14, 'drills', 'crear', 'Crear nuevos ejercicios'),
(15, 'drills', 'editar', 'Editar ejercicios existentes'),
(16, 'marcas', 'ver', 'Ver marcas registradas'),
(17, 'marcas', 'registrar', 'Registrar nuevas marcas'),
(18, 'antropometria', 'ver', 'Ver mediciones antropométricas'),
(19, 'antropometria', 'registrar', 'Registrar nuevas mediciones'),
(20, 'lesiones', 'ver', 'Ver historial de lesiones'),
(21, 'lesiones', 'registrar', 'Registrar nuevas lesiones'),
(22, 'lesiones', 'gestionar', 'Actualizar estado y protocolo de retorno'),
(23, 'rpe', 'ver', 'Ver registros de RPE'),
(24, 'rpe', 'registrar', 'Registrar RPE post-sesión'),
(25, 'eventos', 'ver', 'Ver calendario de eventos'),
(26, 'eventos', 'crear', 'Crear eventos'),
(27, 'eventos', 'editar', 'Editar eventos existentes'),
(28, 'carga', 'ver', 'Ver métricas de carga ACWR/TSS'),
(29, 'rankings', 'ver', 'Consultar rankings'),
(30, 'reportes', 'generar', 'Generar reportes PDF'),
(31, 'periodizacion', 'ver', 'Ver planes de periodización'),
(32, 'periodizacion', 'generar', 'Generar plan ATR automático'),
(33, 'periodizacion', 'editar', 'Editar plan de periodización'),
(34, 'ia', 'ver', 'Ver recomendaciones del motor IA'),
(35, 'ia', 'gestionar', 'CRUD de reglas del motor IA'),
(36, 'seguridad', 'usuarios', 'Gestión de usuarios del sistema'),
(37, 'seguridad', 'roles', 'Gestión de roles y permisos'),
(38, 'seguridad', 'bitacora', 'Consulta de bitácora de auditoría'),
(39, 'representantes', 'ver', 'Ver datos de representantes'),
(40, 'representantes', 'gestionar', 'Gestión de representantes legales'),
(41, 'grupos', 'ver', 'Ver grupos de entrenamiento'),
(42, 'grupos', 'gestionar', 'Crear/editar grupos de entrenamiento'),
(43, 'atletas', 'gestionar', 'Acceso al modulo de gestion de entrenadores'),
(44, 'seguridad', 'mantenimiento', 'Acceso al módulo de mantenimiento y respaldos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Administrador', 'Acceso total al sistema. Gestión de usuarios, configuración global.', 1, '2026-05-17 13:16:25'),
(2, 'Entrenador', 'Gestión de atletas asignados, sesiones, marcas, reportes.', 1, '2026-05-17 13:16:25'),
(3, 'Medico', 'Acceso a módulos médicos y antropometría. Solo lectura en datos deportivos.', 1, '2026-05-17 13:16:25'),
(4, 'Atleta', 'Solo lectura de su perfil propio y registro de su RPE.', 1, '2026-05-17 13:16:25'),
(5, 'Representante', 'Solo lectura del atleta bajo su tutela.', 1, '2026-05-17 13:16:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id_rol_permiso` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id_rol_permiso`, `id_rol`, `id_permiso`) VALUES
(173, 1, 1),
(169, 1, 2),
(170, 1, 3),
(171, 1, 4),
(168, 1, 5),
(167, 1, 6),
(176, 1, 7),
(175, 1, 8),
(208, 1, 9),
(206, 1, 10),
(207, 1, 11),
(205, 1, 12),
(179, 1, 13),
(177, 1, 14),
(178, 1, 15),
(191, 1, 16),
(190, 1, 17),
(166, 1, 18),
(165, 1, 19),
(189, 1, 20),
(188, 1, 21),
(187, 1, 22),
(200, 1, 23),
(199, 1, 24),
(182, 1, 25),
(180, 1, 26),
(181, 1, 27),
(174, 1, 28),
(195, 1, 29),
(196, 1, 30),
(194, 1, 31),
(193, 1, 32),
(192, 1, 33),
(186, 1, 34),
(185, 1, 35),
(204, 1, 36),
(203, 1, 37),
(201, 1, 38),
(198, 1, 39),
(197, 1, 40),
(184, 1, 41),
(183, 1, 42),
(172, 1, 43),
(202, 1, 44),
(70, 2, 1),
(68, 2, 2),
(69, 2, 3),
(67, 2, 5),
(66, 2, 6),
(72, 2, 7),
(95, 2, 9),
(93, 2, 10),
(94, 2, 11),
(92, 2, 12),
(75, 2, 13),
(73, 2, 14),
(74, 2, 15),
(84, 2, 16),
(83, 2, 17),
(65, 2, 18),
(64, 2, 19),
(82, 2, 20),
(81, 2, 21),
(91, 2, 23),
(90, 2, 24),
(78, 2, 25),
(76, 2, 26),
(77, 2, 27),
(71, 2, 28),
(88, 2, 29),
(89, 2, 30),
(87, 2, 31),
(86, 2, 32),
(85, 2, 33),
(80, 2, 34),
(79, 2, 41),
(153, 2, 43),
(129, 3, 1),
(128, 3, 18),
(127, 3, 19),
(132, 3, 20),
(131, 3, 21),
(130, 3, 22),
(156, 4, 1),
(161, 4, 16),
(160, 4, 17),
(155, 4, 18),
(154, 4, 19),
(159, 4, 20),
(158, 4, 21),
(163, 4, 23),
(162, 4, 24),
(157, 4, 28),
(149, 5, 1),
(150, 5, 25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_activas`
--

CREATE TABLE `sesiones_activas` (
  `id_sesion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token_jwt` varchar(500) NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `apellidos`, `correo`, `contrasena_hash`, `activo`, `bloqueado_hasta`, `intentos_fallidos`, `token_recuperacion`, `token_expiracion`, `fecha_creacion`, `fecha_modificacion`) VALUES
(3, '00000000', 'Administrador', 'Sistema', 'admin@sgrd.com', '$2y$10$26SZiPtKViEwm7tHHEVWl.Z8Y2U.gMOIpdZjYNTeoXUQtMqZH.LZu', 1, NULL, 0, NULL, NULL, '2026-06-06 15:30:31', '2026-06-09 00:53:36'),
(6, '20991265', 'Guillermo', 'Davila', 'gillermo@correo.com', '$2y$10$j4e0zqfcwoWfp107CJrJLuMPDb5apFO4y1FDPWNk0GX7E9jLmSnjO', 1, NULL, 0, NULL, NULL, '2026-06-08 21:29:30', NULL),
(7, '10101010', 'juan', 'algo', 'juan@correo.com', '$2y$10$4avpQSx.EEIFQocM.TryIOu9oJP5bg1YnotJpTUzETzPiXUcXBheS', 1, NULL, 0, NULL, NULL, '2026-06-09 00:15:08', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_roles`
--

CREATE TABLE `usuario_roles` (
  `id_usuario_rol` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_roles`
--

INSERT INTO `usuario_roles` (`id_usuario_rol`, `id_usuario`, `id_rol`, `fecha_asignacion`) VALUES
(2, 3, 1, '2026-06-06 15:30:31'),
(5, 6, 4, '2026-06-08 21:29:30'),
(6, 7, 2, '2026-06-09 00:15:08');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuario_completo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_usuario_completo` (
`id_usuario` int(11)
,`cedula` varchar(20)
,`nombres` varchar(100)
,`apellidos` varchar(100)
,`correo` varchar(150)
,`activo` tinyint(1)
,`bloqueado_hasta` datetime
,`intentos_fallidos` int(11)
,`roles` mediumtext
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuario_completo`
--
DROP TABLE IF EXISTS `v_usuario_completo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuario_completo`  AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`cedula` AS `cedula`, `u`.`nombres` AS `nombres`, `u`.`apellidos` AS `apellidos`, `u`.`correo` AS `correo`, `u`.`activo` AS `activo`, `u`.`bloqueado_hasta` AS `bloqueado_hasta`, `u`.`intentos_fallidos` AS `intentos_fallidos`, group_concat(`r`.`nombre` order by `r`.`nombre` ASC separator ', ') AS `roles` FROM ((`usuarios` `u` left join `usuario_roles` `ur` on(`u`.`id_usuario` = `ur`.`id_usuario`)) left join `roles` `r` on(`ur`.`id_rol` = `r`.`id_rol`)) GROUP BY `u`.`id_usuario` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `fk_bit_usuario` (`id_usuario`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id_intento`),
  ADD KEY `fk_il_usuario` (`id_usuario`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `modulo` (`modulo`,`accion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id_rol_permiso`),
  ADD UNIQUE KEY `id_rol` (`id_rol`,`id_permiso`),
  ADD KEY `fk_rp_permiso` (`id_permiso`);

--
-- Indices de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `fk_sa_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD PRIMARY KEY (`id_usuario_rol`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_rol`),
  ADD KEY `fk_ur_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  MODIFY `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bit_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD CONSTRAINT `fk_il_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD CONSTRAINT `fk_sa_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
