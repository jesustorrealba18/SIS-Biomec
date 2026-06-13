-- ============================================================
-- BD: sis_seguridad
-- Generado: 2026-06-12
-- 
-- Separada de sis_natacion para modularidad.
-- Contiene: usuarios, roles, permisos, bitacora, sesiones JWT.
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sis_seguridad` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sis_seguridad`;

-- --------------------------------------------------------
-- Tabla: usuarios
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: roles
-- --------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: permisos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(80) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `modulo` (`modulo`,`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: usuario_roles
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuario_roles`;
CREATE TABLE `usuario_roles` (
  `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario_rol`),
  UNIQUE KEY `id_usuario` (`id_usuario`,`id_rol`),
  KEY `fk_ur_rol` (`id_rol`),
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: rol_permisos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `rol_permisos`;
CREATE TABLE `rol_permisos` (
  `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  PRIMARY KEY (`id_rol_permiso`),
  UNIQUE KEY `id_rol` (`id_rol`,`id_permiso`),
  KEY `fk_rp_permiso` (`id_permiso`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: bitacora
-- --------------------------------------------------------

DROP TABLE IF EXISTS `bitacora`;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `modulo_afectado` varchar(80) NOT NULL,
  `tipo_operacion` enum('CREATE','RESTORE','INSERT','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `fecha_operacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bitacora`),
  KEY `fk_bit_usuario` (`id_usuario`),
  CONSTRAINT `fk_bit_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: intentos_login
-- --------------------------------------------------------

DROP TABLE IF EXISTS `intentos_login`;
CREATE TABLE `intentos_login` (
  `id_intento` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `correoIntento` varchar(150) DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_intento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_intento`),
  KEY `fk_il_usuario` (`id_usuario`),
  CONSTRAINT `fk_il_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: sesiones_activas (JWT)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sesiones_activas`;
CREATE TABLE `sesiones_activas` (
  `id_sesion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token_jwt` varchar(500) NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sesion`),
  KEY `fk_sa_usuario` (`id_usuario`),
  CONSTRAINT `fk_sa_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- DATOS DE PRODUCCION
-- ============================================================

-- --------------------------------------------------------
-- Datos: roles
-- --------------------------------------------------------

LOCK TABLES `roles` WRITE;
ALTER TABLE `roles` DISABLE KEYS;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema. Gestión de usuarios, configuración global.',1,'2026-05-17 13:16:25'),(2,'Entrenador','Gestión de atletas asignados, sesiones, marcas, reportes.',1,'2026-05-17 13:16:25'),(3,'Medico','Acceso a módulos médicos y antropometría. Solo lectura en datos deportivos.',1,'2026-05-17 13:16:25'),(4,'Atleta','Solo lectura de su perfil propio y registro de su RPE.',1,'2026-05-17 13:16:25'),(5,'Representante','Solo lectura del atleta bajo su tutela.',1,'2026-05-17 13:16:25');
ALTER TABLE `roles` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: usuarios
-- --------------------------------------------------------

LOCK TABLES `usuarios` WRITE;
ALTER TABLE `usuarios` DISABLE KEYS;
INSERT INTO `usuarios` VALUES (3,'00000000','Administrador','Sistema','admin@sgrd.com','$2y$10$26SZiPtKViEwm7tHHEVWl.Z8Y2U.gMOIpdZjYNTeoXUQtMqZH.LZu',1,NULL,0,NULL,NULL,'2026-06-06 15:30:31','2026-06-10 11:52:05'),(4,'28591974','Jesús','Regalado','correo@correo.com','$2y$10$5Cd91bE/v5btQEGF5XfH4usk0MzjoJ09cmnETL1A0zNXV7jRE6TTm',1,NULL,0,NULL,NULL,'2026-06-06 17:58:37','2026-06-06 18:03:42'),(5,'25854831','Jose Miguel','Pirolo Narro','josemiguel.pirolo@gmail.com','$2y$10$YhNg4TYimPC8qRTv8YTcLOAmICRO6BGOVLCPglZdUkZKlC6N5mElK',1,NULL,0,NULL,NULL,'2026-06-08 23:57:30',NULL),(6,'28425405','jose','pirolo','joseantoniopirolo@sgrd.com','$2y$10$62ynRLsgfM0WBCinzTla7eaBT3y/dazo.rVqcOLQeH6rM3rTnXil2',1,NULL,0,NULL,NULL,'2026-06-10 13:39:04',NULL);
ALTER TABLE `usuarios` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: usuario_roles
-- --------------------------------------------------------

LOCK TABLES `usuario_roles` WRITE;
ALTER TABLE `usuario_roles` DISABLE KEYS;
INSERT INTO `usuario_roles` VALUES (2,3,1,'2026-06-06 15:30:31'),(3,4,4,'2026-06-06 17:58:37'),(4,5,2,'2026-06-08 23:57:30'),(5,6,3,'2026-06-10 13:39:04');
ALTER TABLE `usuario_roles` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: permisos (sin permisos de IA eliminados)
-- --------------------------------------------------------

LOCK TABLES `permisos` WRITE;
ALTER TABLE `permisos` DISABLE KEYS;
INSERT INTO `permisos` VALUES (1,'atletas','ver','Ver expedientes de atletas'),(2,'atletas','crear','Crear nuevo expediente'),(3,'atletas','editar','Editar expediente existente'),(4,'atletas','eliminar','Cambiar estado del atleta (baja lógica)'),(5,'asistencia','ver','Ver registros de asistencia'),(6,'asistencia','registrar','Registrar asistencia QR o manual'),(7,'carriles','ver','Ver asignación de carriles'),(8,'carriles','gestionar','Crear/editar asignaciones de carriles y horarios'),(9,'sesiones','ver','Ver sesiones planificadas'),(10,'sesiones','crear','Crear sesiones de entrenamiento'),(11,'sesiones','editar','Editar sesiones existentes'),(12,'sesiones','completar','Registrar volumen ejecutado post-sesión'),(13,'drills','ver','Ver catálogo de ejercicios'),(14,'drills','crear','Crear nuevos ejercicios'),(15,'drills','editar','Editar ejercicios existentes'),(16,'marcas','ver','Ver marcas registradas'),(17,'marcas','registrar','Registrar nuevas marcas'),(18,'antropometria','ver','Ver mediciones antropométricas'),(19,'antropometria','registrar','Registrar nuevas mediciones'),(20,'lesiones','ver','Ver historial de lesiones'),(21,'lesiones','registrar','Registrar nuevas lesiones'),(22,'lesiones','editar','Actualizar estado y protocolo de retorno'),(23,'rpe','ver','Ver registros de RPE'),(24,'rpe','registrar','Registrar RPE post-sesión'),(25,'eventos','ver','Ver calendario de eventos'),(26,'eventos','crear','Crear eventos'),(27,'eventos','editar','Editar eventos existentes'),(28,'carga','ver','Ver métricas de carga ACWR/TSS'),(29,'rankings','ver','Consultar rankings'),(30,'reportes','generar','Generar reportes PDF'),(31,'periodizacion','ver','Ver planes de periodización'),(32,'periodizacion','generar','Generar plan ATR automático'),(33,'periodizacion','editar','Editar plan de periodización'),(34,'entrenadores','ver','Ver información de entrenadores'),(35,'seguridad','usuarios','Gestión de usuarios del sistema'),(36,'seguridad','roles','Gestión de roles y permisos'),(37,'seguridad','bitacora','Consulta de bitácora del sistema'),(38,'seguridad','backup','Realizar backups y restauraciones'),(39,'seguridad','login','Iniciar sesión'),(40,'seguridad','logout','Cerrar sesión'),(41,'metas','ver','Ver metas competitivas'),(42,'metas','gestionar','Crear/editar metas competitivas'),(43,'atletas','ver_propio','Atleta: ver su propio expediente'),(44,'atletas','rpe_propio','Atleta: registrar su propio RPE'),(45,'representantes','ver_hijos','Representante: ver expedientes de sus atletas'),(46,'representantes','asistencia_hijos','Representante: ver asistencia de sus atletas'),(47,'representantes','rpe_hijos','Representante: ver RPE de sus atletas'),(48,'ia','ver','Ver recomendaciones de la API IA');
ALTER TABLE `permisos` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: rol_permisos (sin refs a permisos de IA eliminados)
-- --------------------------------------------------------

LOCK TABLES `rol_permisos` WRITE;
ALTER TABLE `rol_permisos` DISABLE KEYS;
INSERT INTO `rol_permisos` VALUES (8,1,1),(5,1,2),(6,1,3),(7,1,4),(4,1,5),(3,1,6),(11,1,7),(10,1,8),(42,1,9),(40,1,10),(41,1,11),(39,1,12),(14,1,13),(12,1,14),(13,1,15),(26,1,16),(25,1,17),(2,1,18),(1,1,19),(24,1,20),(23,1,21),(22,1,22),(35,1,23),(34,1,24),(17,1,25),(15,1,26),(16,1,27),(9,1,28),(30,1,29),(31,1,30),(29,1,31),(28,1,32),(27,1,33),(21,1,34),(38,1,35),(37,1,36),(36,1,37),(33,1,38),(32,1,39),(19,1,41),(18,1,42),(152,1,43),(159,1,48),(70,2,1),(68,2,2),(69,2,3),(67,2,5),(66,2,6),(72,2,7),(95,2,9),(93,2,10),(94,2,11),(92,2,12),(75,2,13),(73,2,14),(74,2,15),(84,2,16),(83,2,17),(65,2,18),(64,2,19),(82,2,20),(81,2,21),(91,2,23),(90,2,24),(78,2,25),(76,2,26),(77,2,27),(71,2,28),(88,2,29),(89,2,30),(87,2,31),(86,2,32),(85,2,33),(80,2,34),(79,2,41),(153,2,43),(129,3,1),(128,3,18),(127,3,19),(132,3,20),(131,3,21),(130,3,22),(156,3,23),(157,3,24),(154,3,43),(155,3,44),(158,3,47),(136,4,1),(142,4,16),(141,4,17),(135,4,18),(134,4,19),(140,4,20),(139,4,21),(144,4,23),(143,4,24),(138,4,25),(137,4,28),(149,5,1),(150,5,25);
ALTER TABLE `rol_permisos` ENABLE KEYS;
UNLOCK TABLES;

-- ============================================================
-- VISTAS
-- ============================================================

DROP TABLE IF EXISTS `v_usuario_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuario_completo` AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`cedula` AS `cedula`, `u`.`nombres` AS `nombres`, `u`.`apellidos` AS `apellidos`, `u`.`correo` AS `correo`, `u`.`activo` AS `activo`, `u`.`bloqueado_hasta` AS `bloqueado_hasta`, `u`.`intentos_fallidos` AS `intentos_fallidos`, group_concat(`r`.`nombre` order by `r`.`nombre` ASC separator ', ') AS `roles` FROM ((`usuarios` `u` left join `usuario_roles` `ur` on(`u`.`id_usuario` = `ur`.`id_usuario`)) left join `roles` `r` on(`ur`.`id_rol` = `r`.`id_rol`)) GROUP BY `u`.`id_usuario`;

-- ============================================================
-- LIMPIEZA FINAL
-- ============================================================

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12
