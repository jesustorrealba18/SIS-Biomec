-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-09-2026 a las 03:53:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
  `tipo_operacion` enum('CREATE','RESTORE','INSERT','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `fecha_operacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `id_usuario`, `modulo_afectado`, `tipo_operacion`, `id_registro_afectado`, `campo_modificado`, `valor_anterior`, `valor_nuevo`, `ip_origen`, `navegador`, `fecha_operacion`) VALUES
(1, 1, 'Mantenimiento', 'EXPORT', NULL, 'Base de Datos', NULL, 'Backup generado: SGRD_Backup_2026-09-16_03-53-37.sql', '::1', NULL, '2026-09-15 21:53:38');

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
(1, 1, 'admin@sgrd.com', '::1', 1, '2026-09-10 01:52:49'),
(2, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 17:08:53'),
(3, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 17:32:08'),
(4, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 18:27:47'),
(5, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 19:10:47'),
(6, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 19:30:29'),
(7, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 19:46:59'),
(8, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 19:57:02'),
(9, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 20:12:49'),
(10, 1, 'admin@sgrd.com', '::1', 1, '2026-09-15 21:51:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `icono` varchar(50) DEFAULT 'fa-bell',
  `color` varchar(20) DEFAULT 'indigo',
  `enlace_url` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(22, 'lesiones', 'editar', 'Actualizar estado y protocolo de retorno'),
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
(34, 'entrenadores', 'ver', 'Ver información de entrenadores'),
(35, 'seguridad', 'usuarios', 'Gestión de usuarios del sistema'),
(36, 'seguridad', 'roles', 'Gestión de roles y permisos'),
(37, 'seguridad', 'bitacora', 'Consulta de bitácora del sistema'),
(38, 'seguridad', 'backup', 'Realizar backups y restauraciones'),
(39, 'seguridad', 'login', 'Iniciar sesión'),
(40, 'seguridad', 'logout', 'Cerrar sesión'),
(41, 'metas', 'ver', 'Ver metas competitivas'),
(42, 'metas', 'gestionar', 'Crear/editar metas competitivas'),
(43, 'atletas', 'ver_propio', 'Atleta: ver su propio expediente'),
(44, 'atletas', 'rpe_propio', 'Atleta: registrar su propio RPE'),
(45, 'representantes', 'ver_hijos', 'Representante: ver expedientes de sus atletas'),
(46, 'representantes', 'asistencia_hijos', 'Representante: ver asistencia de sus atletas'),
(47, 'representantes', 'rpe_hijos', 'Representante: ver RPE de sus atletas'),
(49, 'seguridad', 'mantenimiento', 'Acceso al módulo de mantenimiento y respaldos'),
(50, 'testFisico', 'ver', 'Ver modulo de tests fisicos'),
(51, 'testFisico', 'registrar', 'Registrar, editar y eliminar tests fisicos'),
(52, 'lesiones', 'eliminar', 'Eliminar lesion (baja logica)'),
(53, 'lesiones', 'eliminardb', 'Eliminar lesion de la base de datos'),
(54, 'lesiones', 'reactivar', 'Reactivar lesion eliminada'),
(55, 'rpe', 'eliminar', 'Anular registro RPE'),
(56, 'carga_bienestar', 'registrar', 'Registrar carga de bienestar'),
(57, 'carga_bienestar', 'anular', 'Anular carga de bienestar'),
(58, 'normalizacion', 'ver', 'Ver modulo de normalizacion'),
(59, 'normalizacion', 'registrar', 'Registrar normalizacion'),
(60, 'normalizacion', 'editar', 'Editar normalizacion'),
(61, 'normalizacion', 'eliminar', 'Eliminar normalizacion'),
(62, 'normalizacion', 'anular', 'Anular normalizacion'),
(63, 'normalizacion_tiempos', 'registrar', 'Registrar normalizacion de tiempos'),
(64, 'observacionesTecnicas', 'ver', 'Ver modulo de observaciones tecnicas'),
(65, 'observacionesTecnicas', 'registrar', 'Registrar observacion tecnica'),
(66, 'representantes', 'ver', 'Ver modulo de representantes'),
(67, 'representantes', 'gestionar', 'Gestionar representantes'),
(68, 'temporadas', 'ver', 'Ver modulo de temporadas'),
(69, 'temporadas', 'registrar', 'Registrar temporada'),
(70, 'mi_perfil', 'ver', 'Ver y editar perfil propio'),
(71, 'sesiones', 'eliminar', 'Eliminar sesion de entrenamiento'),
(72, 'atletas', 'gestionar', 'Gestionar atletas (crear, editar, eliminar)'),
(73, 'marcas', 'editar', 'puede editar'),
(74, 'marcas', 'eliminar', 'se puede eliminar'),
(75, 'marcas', 'restaurar', 'se puede restaurar'),
(76, 'grupo', 'ver', 'grupo de entrenamientos'),
(77, 'antropometria', 'eliminar', 'Permite anular (soft delete) mediciones antropométricas'),
(78, 'antropometria', 'reactivar', 'Permite reactivar mediciones antropométricas'),
(79, 'carga_bienestar', 'eliminar', 'eliminar carga de bienestar'),
(80, 'carga_bienestar', 'reactivar', 'reactivar carga de bienestar'),
(81, 'carga_bienestar', 'eliminardb', 'eliminar permanente carga de bienestar'),
(82, 'rpe', 'reactivar', 'reactivar registro RPE'),
(83, 'rpe', 'editar', 'editar RPE post-sesión'),
(84, 'reportes', 'ver', 'visualizar moduo reportes PDF'),
(85, 'sistemaExperto', 'ver', 'Acceso al modulo del Sistema Experto (recomendaciones y alertas)'),
(87, 'antropometria', 'eliminardb', 'Eliminado Fisico de medicion antropometrica'),
(88, 'asignacion', 'ver', 'ver asignacion de carriles'),
(89, 'asignacion', 'gestionar', 'crear, editar y eliminar'),
(90, 'horario', 'ver', 'ver horarios'),
(91, 'horario', 'gestionar', 'crear, editar, eliminar');

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
(528, 1, 1),
(524, 1, 2),
(525, 1, 3),
(526, 1, 4),
(523, 1, 5),
(522, 1, 6),
(533, 1, 7),
(532, 1, 8),
(588, 1, 9),
(585, 1, 10),
(586, 1, 11),
(584, 1, 12),
(536, 1, 13),
(534, 1, 14),
(535, 1, 15),
(552, 1, 16),
(550, 1, 17),
(521, 1, 18),
(520, 1, 19),
(547, 1, 20),
(546, 1, 21),
(542, 1, 22),
(576, 1, 23),
(575, 1, 24),
(540, 1, 25),
(538, 1, 26),
(539, 1, 27),
(529, 1, 28),
(567, 1, 29),
(568, 1, 30),
(566, 1, 31),
(565, 1, 32),
(564, 1, 33),
(537, 1, 34),
(583, 1, 35),
(582, 1, 36),
(578, 1, 37),
(577, 1, 38),
(579, 1, 39),
(580, 1, 40),
(554, 1, 41),
(553, 1, 42),
(581, 1, 49),
(593, 1, 50),
(592, 1, 51),
(543, 1, 52),
(544, 1, 53),
(545, 1, 54),
(573, 1, 55),
(531, 1, 56),
(530, 1, 57),
(560, 1, 58),
(559, 1, 59),
(557, 1, 60),
(558, 1, 61),
(556, 1, 62),
(561, 1, 63),
(563, 1, 64),
(562, 1, 65),
(571, 1, 66),
(570, 1, 67),
(591, 1, 68),
(590, 1, 69),
(555, 1, 70),
(587, 1, 71),
(527, 1, 72),
(548, 1, 73),
(549, 1, 74),
(551, 1, 75),
(541, 1, 76),
(517, 1, 77),
(519, 1, 78),
(574, 1, 82),
(572, 1, 83),
(569, 1, 84),
(589, 1, 85),
(518, 1, 87),
(594, 1, 88),
(595, 1, 89),
(596, 1, 90),
(597, 1, 91),
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
(251, 2, 50),
(252, 2, 51),
(257, 2, 52),
(261, 2, 53),
(265, 2, 54),
(269, 2, 55),
(273, 2, 56),
(275, 2, 57),
(277, 2, 58),
(279, 2, 59),
(281, 2, 60),
(283, 2, 61),
(285, 2, 62),
(287, 2, 63),
(289, 2, 64),
(291, 2, 65),
(295, 2, 68),
(297, 2, 69),
(299, 2, 70),
(304, 2, 71),
(255, 2, 72),
(310, 2, 73),
(309, 2, 74),
(308, 2, 75),
(439, 2, 85),
(129, 3, 1),
(314, 3, 16),
(128, 3, 18),
(127, 3, 19),
(132, 3, 20),
(131, 3, 21),
(130, 3, 22),
(156, 3, 23),
(157, 3, 24),
(154, 3, 43),
(155, 3, 44),
(158, 3, 47),
(253, 3, 50),
(258, 3, 52),
(262, 3, 53),
(266, 3, 54),
(270, 3, 55),
(300, 3, 70),
(318, 3, 77),
(320, 3, 78),
(348, 4, 1),
(352, 4, 16),
(346, 4, 18),
(354, 4, 23),
(351, 4, 25),
(350, 4, 28),
(349, 4, 43),
(347, 4, 44),
(353, 4, 70),
(356, 5, 1),
(358, 5, 16),
(355, 5, 18),
(357, 5, 25),
(362, 5, 45),
(360, 5, 46),
(361, 5, 47),
(359, 5, 70);

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
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Guarda ajustes de UI del usuario como {"tema":"dark", "crono":"live"}' CHECK (json_valid(`preferencias`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `apellidos`, `correo`, `contrasena_hash`, `activo`, `bloqueado_hasta`, `intentos_fallidos`, `token_recuperacion`, `token_expiracion`, `fecha_creacion`, `fecha_modificacion`, `preferencias`) VALUES
(1, 'V-00000001', 'Administrador', 'Sistema', 'admin@sgrd.com', '$2y$10$JRFTfiOWeKn30Nn1DpiniO.kt90cMi4Mm.mUUk.BBjFy8bZnvr/gq', 1, NULL, 0, NULL, NULL, '2025-01-15 10:00:00', '2026-09-15 21:14:09', '{\"crono_mode\": \"manual\", \"tema\": \"dark\"}');

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
(1, 1, 1, '2026-09-10 01:42:46');

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
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notif_usuario` (`id_usuario`);

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
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=598;

--
-- AUTO_INCREMENT de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  MODIFY `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

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
