-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-05-2026 a las 23:22:12
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
  `tipo_operacion` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
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
(42, 'grupos', 'gestionar', 'Crear/editar grupos de entrenamiento');

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
(8, 1, 1),
(5, 1, 2),
(6, 1, 3),
(7, 1, 4),
(4, 1, 5),
(3, 1, 6),
(11, 1, 7),
(10, 1, 8),
(42, 1, 9),
(40, 1, 10),
(41, 1, 11),
(39, 1, 12),
(14, 1, 13),
(12, 1, 14),
(13, 1, 15),
(26, 1, 16),
(25, 1, 17),
(2, 1, 18),
(1, 1, 19),
(24, 1, 20),
(23, 1, 21),
(22, 1, 22),
(35, 1, 23),
(34, 1, 24),
(17, 1, 25),
(15, 1, 26),
(16, 1, 27),
(9, 1, 28),
(30, 1, 29),
(31, 1, 30),
(29, 1, 31),
(28, 1, 32),
(27, 1, 33),
(21, 1, 34),
(20, 1, 35),
(38, 1, 36),
(37, 1, 37),
(36, 1, 38),
(33, 1, 39),
(32, 1, 40),
(19, 1, 41),
(18, 1, 42),
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
(129, 3, 1),
(128, 3, 18),
(127, 3, 19),
(132, 3, 20),
(131, 3, 21),
(130, 3, 22),
(136, 4, 1),
(142, 4, 16),
(141, 4, 17),
(135, 4, 18),
(134, 4, 19),
(140, 4, 20),
(139, 4, 21),
(144, 4, 23),
(143, 4, 24),
(138, 4, 25),
(137, 4, 28),
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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  MODIFY `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT;

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
