-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sis_natacion
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Current Database: `sis_natacion`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sis_natacion` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sis_natacion`;

--
-- Table structure for table `asignacion_carril`
--

DROP TABLE IF EXISTS `asignacion_carril`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignacion_carril` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_carril` int(11) NOT NULL,
  `id_bloque_horario` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `dia_especifico` date DEFAULT NULL,
  `fecha_vigencia_inicio` date NOT NULL,
  `fecha_vigencia_fin` date DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_asignacion`),
  KEY `fk_ac_carril` (`id_carril`),
  KEY `fk_ac_bloque` (`id_bloque_horario`),
  KEY `fk_ac_grupo` (`id_grupo`),
  CONSTRAINT `fk_ac_bloque` FOREIGN KEY (`id_bloque_horario`) REFERENCES `bloques_horarios` (`id_bloque`),
  CONSTRAINT `fk_ac_carril` FOREIGN KEY (`id_carril`) REFERENCES `carriles` (`id_carril`),
  CONSTRAINT `fk_ac_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacion_carril`
--

LOCK TABLES `asignacion_carril` WRITE;
/*!40000 ALTER TABLE `asignacion_carril` DISABLE KEYS */;
/*!40000 ALTER TABLE `asignacion_carril` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_asignacion_carril` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Entrenador que validó',
  `fecha` date NOT NULL,
  `hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('QR','Manual') NOT NULL DEFAULT 'QR',
  `estado` enum('Presente','Ausente','Justificado','Tardanza') NOT NULL DEFAULT 'Presente',
  `justificacion` text DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`,`id_sesion`),
  KEY `fk_as_sesion` (`id_sesion`),
  KEY `fk_as_carril` (`id_asignacion_carril`),
  CONSTRAINT `fk_as_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`),
  CONSTRAINT `fk_as_carril` FOREIGN KEY (`id_asignacion_carril`) REFERENCES `asignacion_carril` (`id_asignacion`) ON DELETE SET NULL,
  CONSTRAINT `fk_as_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (53,5,1,NULL,NULL,'2026-06-13','2026-06-13 02:04:31','QR','Presente','Validación Biométrica QR');
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atleta_datos_medicos`
--

DROP TABLE IF EXISTS `atleta_datos_medicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atleta_datos_medicos` (
  `id_datos_medicos` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `grupo_sanguineo` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `condiciones_previas` text DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(100) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia_parentesco` varchar(50) DEFAULT NULL,
  `seguro_medico` varchar(100) DEFAULT NULL,
  `numero_feveda` varchar(50) DEFAULT NULL,
  `club_procedencia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_datos_medicos`),
  UNIQUE KEY `id_atleta` (`id_atleta`),
  CONSTRAINT `fk_dm_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_datos_medicos`
--

LOCK TABLES `atleta_datos_medicos` WRITE;
/*!40000 ALTER TABLE `atleta_datos_medicos` DISABLE KEYS */;
INSERT INTO `atleta_datos_medicos` VALUES (1,5,'A+','ninguna','Asma','jose gregorio','04120560145','Padre','Miranda','FED-00123','maximo viloria');
/*!40000 ALTER TABLE `atleta_datos_medicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atleta_representante`
--

DROP TABLE IF EXISTS `atleta_representante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atleta_representante` (
  `id_atleta_rep` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `id_representante` int(11) NOT NULL,
  `autorizacion_medica` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_aut_medica` date DEFAULT NULL,
  `autorizacion_imagen` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_aut_imagen` date DEFAULT NULL,
  PRIMARY KEY (`id_atleta_rep`),
  UNIQUE KEY `id_atleta` (`id_atleta`,`id_representante`),
  KEY `fk_ar_repre` (`id_representante`),
  CONSTRAINT `fk_ar_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_repre` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_representante`
--

LOCK TABLES `atleta_representante` WRITE;
/*!40000 ALTER TABLE `atleta_representante` DISABLE KEYS */;
INSERT INTO `atleta_representante` VALUES (46,3,2,1,'2026-06-24',0,NULL),(47,5,4,1,'2026-06-24',1,'2026-06-24'),(54,2,1,1,'2026-06-24',1,'2026-06-24'),(55,1,1,0,NULL,1,'2026-06-24'),(60,4,5,1,'2026-06-24',1,'2026-06-24');
/*!40000 ALTER TABLE `atleta_representante` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_fecha_autorizacion_insert
BEFORE INSERT ON atleta_representante
FOR EACH ROW
BEGIN
    -- Si autoriza la parte médica, estampamos la fecha del servidor
    IF NEW.autorizacion_medica = 1 THEN
        SET NEW.fecha_aut_medica = CURDATE();
    ELSE
        SET NEW.fecha_aut_medica = NULL;
    END IF;

    -- Si autoriza la imagen, estampamos la fecha del servidor
    IF NEW.autorizacion_imagen = 1 THEN
        SET NEW.fecha_aut_imagen = CURDATE();
    ELSE
        SET NEW.fecha_aut_imagen = NULL;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_fecha_autorizacion_update
BEFORE UPDATE ON atleta_representante
FOR EACH ROW
BEGIN
    -- Lógica para la Autorización Médica
    IF NEW.autorizacion_medica = 1 AND OLD.autorizacion_medica = 0 THEN
        -- Si antes no tenía permiso y ahora sí, estampamos la fecha de hoy
        SET NEW.fecha_aut_medica = CURDATE();
    ELSEIF NEW.autorizacion_medica = 0 THEN
        -- Si revoca el permiso, anulamos la fecha
        SET NEW.fecha_aut_medica = NULL;
    ELSE
        -- Si ya tenía el permiso (1) y sigue en (1), mantenemos la fecha histórica original
        SET NEW.fecha_aut_medica = OLD.fecha_aut_medica;
    END IF;

    -- Lógica para la Autorización de Imagen/Fotos
    IF NEW.autorizacion_imagen = 1 AND OLD.autorizacion_imagen = 0 THEN
        SET NEW.fecha_aut_imagen = CURDATE();
    ELSEIF NEW.autorizacion_imagen = 0 THEN
        SET NEW.fecha_aut_imagen = NULL;
    ELSE
        SET NEW.fecha_aut_imagen = OLD.fecha_aut_imagen;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `atletas`
--

DROP TABLE IF EXISTS `atletas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atletas` (
  `id_atleta` int(11) NOT NULL AUTO_INCREMENT,
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
  `token_asistencia` varchar(64) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_atleta`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `token_asistencia` (`token_asistencia`),
  KEY `fk_at_cat` (`id_categoria`),
  CONSTRAINT `fk_at_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_feveda` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atletas`
--

LOCK TABLES `atletas` WRITE;
/*!40000 ALTER TABLE `atletas` DISABLE KEYS */;
INSERT INTO `atletas` VALUES (1,'25854831','Jose Miguel','Pirolo Navarro','2016-05-10','M','Tamaca, Las Delicias','04120560145','josemiguel.pirolo@gmail.com',NULL,'2025-05-14','Activo',NULL,2,NULL,'2026-05-20 15:56:31',NULL),(2,'28425405','Jose Antonio','Pirolo Navarro','2017-05-24','M','Tamaca Las Delicias','04121112323','jose@gmsil.com',NULL,'2024-05-01','Activo',NULL,2,NULL,'2026-05-20 17:49:29',NULL),(3,'31536131','Joselin Paola','Pirolo Navarro','2017-05-10','M','Tamaca, Las Delicias','04120560145','jose@gmail.com',NULL,'2025-05-07','Activo',NULL,2,NULL,'2026-05-20 17:50:59',NULL),(4,'32296296','Francelys Adriana','Camacho Rivero','2017-05-01','F','MEtropolis','04121112323','jose@gmail.com',NULL,'2026-05-04','Activo',NULL,3,NULL,'2026-05-20 22:37:47',NULL),(5,'V-35000125','Juana','Ramones','2010-05-11','F','Carrera 5 Entre Calles 6 Y 7 Tamaca','04120560145','juana.ramones@sgrd.com',NULL,'2026-05-13','Activo','e3ee4d9cc05085156b84c15cd644e168',1,7,'2026-06-12 12:42:00','2026-06-12 12:46:58');
/*!40000 ALTER TABLE `atletas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bloques_horarios`
--

DROP TABLE IF EXISTS `bloques_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bloques_horarios` (
  `id_bloque` int(11) NOT NULL AUTO_INCREMENT,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id_bloque`),
  UNIQUE KEY `dia_semana` (`dia_semana`,`hora_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloques_horarios`
--

LOCK TABLES `bloques_horarios` WRITE;
/*!40000 ALTER TABLE `bloques_horarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `bloques_horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carga_diaria`
--

DROP TABLE IF EXISTS `carga_diaria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carga_diaria` (
  `id_carga` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_carga`),
  UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`),
  CONSTRAINT `fk_cd_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carga_diaria`
--

LOCK TABLES `carga_diaria` WRITE;
/*!40000 ALTER TABLE `carga_diaria` DISABLE KEYS */;
/*!40000 ALTER TABLE `carga_diaria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carriles`
--

DROP TABLE IF EXISTS `carriles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carriles` (
  `id_carril` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `capacidad_maxima` int(11) NOT NULL DEFAULT 6,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_carril`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carriles`
--

LOCK TABLES `carriles` WRITE;
/*!40000 ALTER TABLE `carriles` DISABLE KEYS */;
INSERT INTO `carriles` VALUES (1,1,6,1),(2,2,6,1),(3,3,6,1),(4,4,6,1),(5,5,6,1),(6,6,6,1);
/*!40000 ALTER TABLE `carriles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_feveda`
--

DROP TABLE IF EXISTS `categorias_feveda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_feveda` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `edad_minima` int(11) NOT NULL,
  `edad_maxima` int(11) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_feveda`
--

LOCK TABLES `categorias_feveda` WRITE;
/*!40000 ALTER TABLE `categorias_feveda` DISABLE KEYS */;
INSERT INTO `categorias_feveda` VALUES (1,'Pre-Infantil',8,10,1),(2,'Infantil A',11,12,1),(3,'Infantil B',13,14,1),(4,'Juvenil A',15,16,1),(5,'Juvenil B',17,18,1),(6,'Maxima',19,99,1);
/*!40000 ALTER TABLE `categorias_feveda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drills`
--

DROP TABLE IF EXISTS `drills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drills` (
  `id_drill` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado','Multi') NOT NULL,
  `categoria` enum('Tecnico','Fuerza','Velocidad','Coordinacion','Resistencia') NOT NULL,
  `enfoque_tecnico` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `metraje_sugerido` int(11) DEFAULT NULL,
  `dificultad` enum('Basico','Intermedio','Avanzado') NOT NULL DEFAULT 'Basico',
  `material_requerido` enum('Ninguno','Pullboy','Aletas','Tabla','Paddle','Resistente','Pullboy_Aletas','Multiple') NOT NULL DEFAULT 'Ninguno',
  `personalizado` tinyint(1) NOT NULL DEFAULT 0,
  `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'En plan_seguridad',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_drill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drills`
--

LOCK TABLES `drills` WRITE;
/*!40000 ALTER TABLE `drills` DISABLE KEYS */;
/*!40000 ALTER TABLE `drills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entrenador`
--

DROP TABLE IF EXISTS `entrenador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entrenador` (
  `id_entrenador` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(11) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F') DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(12) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `id_usuario` int(3) NOT NULL,
  PRIMARY KEY (`id_entrenador`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador`
--

LOCK TABLES `entrenador` WRITE;
/*!40000 ALTER TABLE `entrenador` DISABLE KEYS */;
INSERT INTO `entrenador` VALUES (3,'85917000','JOSEFINA','Pirolo','1969-07-12','M','joaw@gmail.com','04120560145','Carrera 5 Entre Calles 6 Y 7 Tamaca','',3),(4,'30088183','jose','pirolo','2003-05-15','M','joseantoniopirolo@gmail.com','04120560145','Tamaca, Las Delicias\r\nCarrera 5 Entre Calles 6y 7','',3);
/*!40000 ALTER TABLE `entrenador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entrenador_asignacion`
--

DROP TABLE IF EXISTS `entrenador_asignacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entrenador_asignacion` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL COMMENT 'ID del entrenador en plan_seguridad',
  `id_atleta` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  PRIMARY KEY (`id_asignacion`),
  UNIQUE KEY `id_usuario` (`id_usuario`,`id_atleta`),
  KEY `fk_ea_atleta` (`id_atleta`),
  CONSTRAINT `fk_ea_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador_asignacion`
--

LOCK TABLES `entrenador_asignacion` WRITE;
/*!40000 ALTER TABLE `entrenador_asignacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `entrenador_asignacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evento_inscripcion`
--

DROP TABLE IF EXISTS `evento_inscripcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evento_inscripcion` (
  `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_evento` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha_inscripcion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_inscripcion`),
  UNIQUE KEY `id_evento` (`id_evento`,`id_atleta`),
  KEY `fk_ei_atleta` (`id_atleta`),
  CONSTRAINT `fk_ei_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_ei_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento_inscripcion`
--

LOCK TABLES `evento_inscripcion` WRITE;
/*!40000 ALTER TABLE `evento_inscripcion` DISABLE KEYS */;
INSERT INTO `evento_inscripcion` VALUES (1,1,1,'2026-06-16 20:11:19'),(2,1,2,'2026-06-16 20:11:19');
/*!40000 ALTER TABLE `evento_inscripcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `sede` varchar(200) DEFAULT NULL,
  `tipo` enum('Regional','Nacional','Internacional','Selectivo','Control') NOT NULL DEFAULT 'Control',
  `nivel` enum('A','B','C') DEFAULT NULL,
  `organizador` varchar(200) DEFAULT NULL,
  `estado` enum('Planificado','Inscrito','En Progreso','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificado',
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,'Control Interno Junio 2026','2026-06-15','2026-06-19','Piscina Olímpica de Barquisimeto','Control','C','Club Biomec','Finalizado','Control de tiempos de mitad de temporada','2026-05-20 10:00:00'),(2,'Selectivo Regional Centro-Occidente','2026-07-20','2026-07-22','Complejo Acuático de Barquisimeto','Selectivo','A','FEVEDA Regional','Planificado','Clasificatorio para nacionales','2026-05-20 10:05:00'),(3,'Campeonato Nacional Infantil','2026-09-10','2026-09-14','Centro Acuático Internacional de Maiquetía','Nacional','A','FEVEDA','Planificado','Nacional para categorías infantil','2026-05-25 08:00:00'),(4,'Torneo Regional Noviembre','2026-11-05','2026-11-07','Piscina Olímpica de Mérida','Regional','B','Comité Regional','Planificado','Torneo de cierre de temporada regional','2026-05-25 08:10:00'),(5,'Competencia Internacional Copa del Caribe','2027-02-10','2027-02-15','Centro Acuático de Santo Domingo','Internacional','A','CCCAN','Planificado','Selección nacional categoría máxima','2026-05-30 12:00:00');
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `factores_conversion`
--

DROP TABLE IF EXISTS `factores_conversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factores_conversion` (
  `id_factor` int(11) NOT NULL AUTO_INCREMENT,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia_m` int(11) NOT NULL,
  `direccion` enum('25_a_50','50_a_25') NOT NULL,
  `factor` decimal(6,4) NOT NULL,
  `fuente` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_factor`),
  UNIQUE KEY `estilo` (`estilo`,`distancia_m`,`direccion`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factores_conversion`
--

LOCK TABLES `factores_conversion` WRITE;
/*!40000 ALTER TABLE `factores_conversion` DISABLE KEYS */;
INSERT INTO `factores_conversion` VALUES (1,'Libre',50,'25_a_50',1.0000,'World Aquatics'),(2,'Libre',100,'25_a_50',1.0300,'World Aquatics'),(3,'Libre',200,'25_a_50',1.0600,'World Aquatics'),(4,'Libre',400,'25_a_50',1.0800,'World Aquatics'),(5,'Libre',800,'25_a_50',1.1000,'World Aquatics'),(6,'Libre',1500,'25_a_50',1.1200,'World Aquatics'),(7,'Espalda',50,'25_a_50',1.0000,'World Aquatics'),(8,'Espalda',100,'25_a_50',1.0300,'World Aquatics'),(9,'Espalda',200,'25_a_50',1.0600,'World Aquatics'),(10,'Braza',50,'25_a_50',1.0000,'World Aquatics'),(11,'Braza',100,'25_a_50',1.0400,'World Aquatics'),(12,'Braza',200,'25_a_50',1.0800,'World Aquatics'),(13,'Mariposa',50,'25_a_50',1.0000,'World Aquatics'),(14,'Mariposa',100,'25_a_50',1.0400,'World Aquatics'),(15,'Mariposa',200,'25_a_50',1.0800,'World Aquatics'),(16,'Combinado',100,'25_a_50',1.0300,'World Aquatics'),(17,'Combinado',200,'25_a_50',1.0600,'World Aquatics'),(18,'Combinado',400,'25_a_50',1.0900,'World Aquatics'),(19,'Libre',50,'50_a_25',1.0000,'World Aquatics'),(20,'Libre',100,'50_a_25',0.9709,'World Aquatics'),(21,'Libre',200,'50_a_25',0.9434,'World Aquatics'),(22,'Libre',400,'50_a_25',0.9259,'World Aquatics'),(23,'Libre',800,'50_a_25',0.9091,'World Aquatics'),(24,'Libre',1500,'50_a_25',0.8929,'World Aquatics'),(25,'Espalda',50,'50_a_25',1.0000,'World Aquatics'),(26,'Espalda',100,'50_a_25',0.9709,'World Aquatics'),(27,'Espalda',200,'50_a_25',0.9434,'World Aquatics'),(28,'Braza',50,'50_a_25',1.0000,'World Aquatics'),(29,'Braza',100,'50_a_25',0.9615,'World Aquatics'),(30,'Braza',200,'50_a_25',0.9259,'World Aquatics'),(31,'Mariposa',50,'50_a_25',1.0000,'World Aquatics'),(32,'Mariposa',100,'50_a_25',0.9615,'World Aquatics'),(33,'Mariposa',200,'50_a_25',0.9259,'World Aquatics'),(34,'Combinado',100,'50_a_25',0.9709,'World Aquatics'),(35,'Combinado',200,'50_a_25',0.9434,'World Aquatics'),(36,'Combinado',400,'50_a_25',0.9174,'World Aquatics');
/*!40000 ALTER TABLE `factores_conversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fases_periodizacion`
--

DROP TABLE IF EXISTS `fases_periodizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fases_periodizacion` (
  `id_fase` int(11) NOT NULL AUTO_INCREMENT,
  `id_macrociclo` int(11) NOT NULL,
  `nombre_fase` enum('Acumulacion','Transmutacion','Realizacion','Deload') NOT NULL,
  `semana_inicio` int(11) NOT NULL,
  `semana_fin` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `porcentaje_volumen` decimal(5,2) DEFAULT NULL,
  `rango_intensidad` varchar(50) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_fase`),
  KEY `fk_fp_macro` (`id_macrociclo`),
  CONSTRAINT `fk_fp_macro` FOREIGN KEY (`id_macrociclo`) REFERENCES `macrociclos` (`id_macrociclo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_periodizacion`
--

LOCK TABLES `fases_periodizacion` WRITE;
/*!40000 ALTER TABLE `fases_periodizacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `fases_periodizacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_atleta`
--

DROP TABLE IF EXISTS `grupo_atleta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo_atleta` (
  `id_grupo_atleta` int(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  PRIMARY KEY (`id_grupo_atleta`),
  UNIQUE KEY `id_grupo` (`id_grupo`,`id_atleta`),
  KEY `fk_ga_atleta` (`id_atleta`),
  CONSTRAINT `fk_ga_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_ga_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_atleta`
--

LOCK TABLES `grupo_atleta` WRITE;
/*!40000 ALTER TABLE `grupo_atleta` DISABLE KEYS */;
INSERT INTO `grupo_atleta` VALUES (1,1,5,'2026-06-12'),(2,1,4,'2026-06-12');
/*!40000 ALTER TABLE `grupo_atleta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos_entrenamiento`
--

DROP TABLE IF EXISTS `grupos_entrenamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupos_entrenamiento` (
  `id_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Entrenador responsable en plan_seguridad',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_entrenamiento`
--

LOCK TABLES `grupos_entrenamiento` WRITE;
/*!40000 ALTER TABLE `grupos_entrenamiento` DISABLE KEYS */;
INSERT INTO `grupos_entrenamiento` VALUES (1,'Furia criolla','jsjsjsjsjsj',1,1);
/*!40000 ALTER TABLE `grupos_entrenamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesiones`
--

DROP TABLE IF EXISTS `lesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesiones` (
  `id_lesion` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `motivo_eliminacion` text DEFAULT NULL,
  PRIMARY KEY (`id_lesion`),
  KEY `fk_le_atleta` (`id_atleta`),
  CONSTRAINT `fk_le_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesiones`
--

LOCK TABLES `lesiones` WRITE;
/*!40000 ALTER TABLE `lesiones` DISABLE KEYS */;
INSERT INTO `lesiones` VALUES (2,3,'Pie','Izquierdo','Sobreuso',8,'pie dolorido','hielo','2026-06-11','2026-06-12','EnRehabilitacion','jose antonio','sdgsdsdf','2026-06-11 07:57:52','2026-06-11 13:33:35',1,NULL),(3,2,'Hombro','Izquierdo','Sobreuso',3,'contractura','hielo','2026-06-12','2026-06-14','Activa','jose antonio','adfasd','2026-06-12 01:05:51',NULL,1,NULL),(4,2,'Codo','Derecho','Sobreuso',4,'dolor de codo','hielo','2026-06-10','2026-06-12','Activa','josea ntonio','sfasd','2026-06-12 02:14:28',NULL,1,NULL);
/*!40000 ALTER TABLE `lesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `macrociclos`
--

DROP TABLE IF EXISTS `macrociclos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `macrociclos` (
  `id_macrociclo` int(11) NOT NULL AUTO_INCREMENT,
  `id_temporada` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `id_evento_objetivo` int(11) DEFAULT NULL,
  `estado` enum('Planificado','En Progreso','Finalizado') NOT NULL DEFAULT 'Planificado',
  PRIMARY KEY (`id_macrociclo`),
  KEY `fk_mc_temp` (`id_temporada`),
  KEY `fk_mc_grupo` (`id_grupo`),
  KEY `fk_mc_evento` (`id_evento_objetivo`),
  CONSTRAINT `fk_mc_evento` FOREIGN KEY (`id_evento_objetivo`) REFERENCES `eventos` (`id_evento`) ON DELETE SET NULL,
  CONSTRAINT `fk_mc_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`),
  CONSTRAINT `fk_mc_temp` FOREIGN KEY (`id_temporada`) REFERENCES `temporadas` (`id_temporada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `macrociclos`
--

LOCK TABLES `macrociclos` WRITE;
/*!40000 ALTER TABLE `macrociclos` DISABLE KEYS */;
/*!40000 ALTER TABLE `macrociclos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_evento` int(11) DEFAULT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia_m` int(11) NOT NULL,
  `tipo_piscina` enum('25m','50m') NOT NULL DEFAULT '50m',
  `tiempo_final_seg` decimal(8,2) NOT NULL,
  `tiempo_reaccion_seg` decimal(5,2) DEFAULT NULL,
  `tiempo_viraje_seg` decimal(5,2) DEFAULT NULL,
  `es_pb` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `motivo_eliminacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_marca`),
  KEY `fk_mk_atleta` (`id_atleta`),
  KEY `fk_mk_sesion` (`id_sesion`),
  KEY `fk_mk_evento` (`id_evento`),
  CONSTRAINT `fk_mk_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_mk_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE SET NULL,
  CONSTRAINT `fk_mk_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,1,NULL,NULL,'Libre',50,'50m',65.00,NULL,NULL,1,'2026-05-21','','2026-05-21 18:22:27','Activo',NULL),(2,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,1,'2026-05-21','presento quejas','2026-05-21 18:31:46','Activo',NULL),(3,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,0,'2026-05-21','calambre','2026-05-21 20:11:20','Activo',NULL),(4,1,NULL,NULL,'Libre',100,'50m',240.00,NULL,NULL,1,'2026-05-11','','2026-05-21 20:13:17','Activo',NULL),(5,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,0,'2026-05-12','','2026-05-21 20:16:21','Activo',NULL),(6,1,NULL,NULL,'Espalda',50,'50m',120.00,NULL,NULL,1,'2026-05-14','','2026-05-23 11:57:30','Activo',NULL),(7,4,NULL,NULL,'Espalda',100,'50m',120.00,0.50,0.60,1,'2026-05-18','Hola','2026-05-31 01:00:43','Activo',NULL),(8,3,NULL,NULL,'Espalda',50,'50m',180.00,0.70,0.50,1,'2026-05-18','nada','2026-05-31 09:27:44','Activo',NULL),(9,3,NULL,NULL,'Braza',50,'50m',180.00,40.00,50.00,1,'2026-05-19','nada','2026-05-31 09:48:55','Activo',NULL),(10,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,1,'2026-06-08','Probando','2026-06-08 23:24:00','Activo',NULL),(11,1,NULL,NULL,'Libre',100,'50m',90.00,12.33,10.00,1,'2026-06-15','Buen rendimiento eee','2026-06-12 12:34:33','Activo',NULL),(12,3,NULL,NULL,'Libre',50,'50m',120.00,5.00,6.00,1,'2026-06-16','comentarios dsfsd','2026-06-16 02:36:25','Activo',NULL),(13,5,1,NULL,'Libre',50,'50m',120.00,2.00,3.00,1,'2026-06-13','hola','2026-06-17 12:47:47','Activo',NULL),(14,1,NULL,1,'Braza',100,'25m',180.00,2.50,5.00,1,'2026-06-16','prueba de registro','2026-06-17 14:54:58','Activo',NULL);
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas_splits`
--

DROP TABLE IF EXISTS `marcas_splits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcas_splits` (
  `id_split` int(11) NOT NULL AUTO_INCREMENT,
  `id_marca` int(11) NOT NULL,
  `parcial_numero` int(11) NOT NULL,
  `distancia_parcial_m` int(11) NOT NULL DEFAULT 50,
  `tiempo_parcial_seg` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id_split`),
  UNIQUE KEY `id_marca` (`id_marca`,`parcial_numero`),
  CONSTRAINT `fk_ms_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_splits`
--

LOCK TABLES `marcas_splits` WRITE;
/*!40000 ALTER TABLE `marcas_splits` DISABLE KEYS */;
INSERT INTO `marcas_splits` VALUES (1,1,1,25,32.50),(2,1,2,50,32.50),(3,2,1,25,28.00),(4,2,2,50,32.00),(5,3,1,25,48.00),(6,3,2,50,72.00),(7,4,1,25,60.00),(8,4,2,50,60.00),(9,4,3,75,60.00),(10,4,4,100,60.00),(11,5,1,25,60.00),(12,5,2,50,60.00),(13,6,1,25,60.00),(14,6,2,50,60.00),(15,9,1,25,130.00),(16,9,2,50,50.00),(23,10,1,25,25.00),(24,10,2,50,35.00),(25,11,1,25,25.00),(26,11,2,50,30.00),(27,11,3,75,27.00),(28,11,4,100,8.00),(31,12,1,25,45.00),(32,12,2,50,75.00),(47,13,1,25,60.00),(48,13,2,50,60.00),(53,14,1,25,48.00),(54,14,2,50,52.00),(55,14,3,75,40.00),(56,14,4,100,40.00);
/*!40000 ALTER TABLE `marcas_splits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas_swolf`
--

DROP TABLE IF EXISTS `marcas_swolf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcas_swolf` (
  `id_swolf` int(11) NOT NULL AUTO_INCREMENT,
  `id_marca` int(11) NOT NULL,
  `num_brazadas` int(11) NOT NULL,
  `swolf` int(11) NOT NULL,
  PRIMARY KEY (`id_swolf`),
  UNIQUE KEY `id_marca` (`id_marca`),
  CONSTRAINT `fk_sw_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_swolf`
--

LOCK TABLES `marcas_swolf` WRITE;
/*!40000 ALTER TABLE `marcas_swolf` DISABLE KEYS */;
INSERT INTO `marcas_swolf` VALUES (1,2,32,92),(2,3,43,163),(3,4,111,231),(4,5,45,165),(5,6,44,164),(6,9,80,260),(9,10,15,75),(10,11,30,75),(12,12,15,135),(19,13,15,135),(21,14,16,61);
/*!40000 ALTER TABLE `marcas_swolf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mediciones_antropometricas`
--

DROP TABLE IF EXISTS `mediciones_antropometricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mediciones_antropometricas` (
  `id_medicion` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `talla_cm` decimal(5,1) DEFAULT NULL,
  `envergadura_cm` decimal(5,1) DEFAULT NULL,
  `perimetro_abdominal_cm` decimal(5,1) DEFAULT NULL,
  `imc` decimal(4,1) DEFAULT NULL,
  `porcentaje_grasa` decimal(4,1) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_medicion`),
  UNIQUE KEY `id_atleta` (`id_atleta`,`fecha`),
  CONSTRAINT `fk_ma_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mediciones_antropometricas`
--

LOCK TABLES `mediciones_antropometricas` WRITE;
/*!40000 ALTER TABLE `mediciones_antropometricas` DISABLE KEYS */;
/*!40000 ALTER TABLE `mediciones_antropometricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesociclos`
--

DROP TABLE IF EXISTS `mesociclos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesociclos` (
  `id_mesociclo` int(11) NOT NULL AUTO_INCREMENT,
  `id_macrociclo` int(11) NOT NULL,
  `id_fase` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `semana_inicio` int(11) DEFAULT NULL,
  `semana_fin` int(11) DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `volumen_objetivo_m` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_mesociclo`),
  KEY `fk_me_macro` (`id_macrociclo`),
  KEY `fk_me_fase` (`id_fase`),
  CONSTRAINT `fk_me_fase` FOREIGN KEY (`id_fase`) REFERENCES `fases_periodizacion` (`id_fase`) ON DELETE CASCADE,
  CONSTRAINT `fk_me_macro` FOREIGN KEY (`id_macrociclo`) REFERENCES `macrociclos` (`id_macrociclo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesociclos`
--

LOCK TABLES `mesociclos` WRITE;
/*!40000 ALTER TABLE `mesociclos` DISABLE KEYS */;
/*!40000 ALTER TABLE `mesociclos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metas_competitivas`
--

DROP TABLE IF EXISTS `metas_competitivas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metas_competitivas` (
  `id_meta` int(11) NOT NULL AUTO_INCREMENT,
  `id_evento` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia` int(11) NOT NULL,
  `marca_objetivo_seg` decimal(8,2) DEFAULT NULL,
  `pb_actual_seg` decimal(8,2) DEFAULT NULL,
  `diferencia_pct` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_meta`),
  UNIQUE KEY `id_evento` (`id_evento`,`id_atleta`,`estilo`,`distancia`),
  KEY `fk_mce_atleta` (`id_atleta`),
  CONSTRAINT `fk_mce_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_mce_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metas_competitivas`
--

LOCK TABLES `metas_competitivas` WRITE;
/*!40000 ALTER TABLE `metas_competitivas` DISABLE KEYS */;
/*!40000 ALTER TABLE `metas_competitivas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microciclos`
--

DROP TABLE IF EXISTS `microciclos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microciclos` (
  `id_microciclo` int(11) NOT NULL AUTO_INCREMENT,
  `id_mesociclo` int(11) NOT NULL,
  `numero_semana` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `volumen_planificado_m` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_microciclo`),
  KEY `fk_mi_meso` (`id_mesociclo`),
  CONSTRAINT `fk_mi_meso` FOREIGN KEY (`id_mesociclo`) REFERENCES `mesociclos` (`id_mesociclo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microciclos`
--

LOCK TABLES `microciclos` WRITE;
/*!40000 ALTER TABLE `microciclos` DISABLE KEYS */;
/*!40000 ALTER TABLE `microciclos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `protocolo_retorno`
--

DROP TABLE IF EXISTS `protocolo_retorno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protocolo_retorno` (
  `id_paso` int(11) NOT NULL AUTO_INCREMENT,
  `id_lesion` int(11) NOT NULL,
  `descripcion_paso` varchar(255) NOT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_completado` date DEFAULT NULL,
  PRIMARY KEY (`id_paso`),
  UNIQUE KEY `id_lesion` (`id_lesion`,`descripcion_paso`),
  CONSTRAINT `fk_pr_lesion` FOREIGN KEY (`id_lesion`) REFERENCES `lesiones` (`id_lesion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `protocolo_retorno`
--

LOCK TABLES `protocolo_retorno` WRITE;
/*!40000 ALTER TABLE `protocolo_retorno` DISABLE KEYS */;
/*!40000 ALTER TABLE `protocolo_retorno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registro_rpe`
--

DROP TABLE IF EXISTS `registro_rpe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registro_rpe` (
  `id_rpe` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rpe`),
  KEY `fk_rpe_atleta` (`id_atleta`),
  KEY `fk_rpe_sesion` (`id_sesion`),
  CONSTRAINT `fk_rpe_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_rpe_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_rpe`
--

LOCK TABLES `registro_rpe` WRITE;
/*!40000 ALTER TABLE `registro_rpe` DISABLE KEYS */;
INSERT INTO `registro_rpe` VALUES (1,3,1,'2026-06-11',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:07:44'),(2,3,NULL,'2026-06-10',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(3,3,NULL,'2026-06-09',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(4,3,NULL,'2026-06-08',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(5,3,NULL,'2026-06-07',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(6,3,NULL,'2026-06-06',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(7,3,NULL,'2026-06-05',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(8,3,NULL,'2026-06-04',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28');
/*!40000 ALTER TABLE `registro_rpe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reglas_ia`
--

DROP TABLE IF EXISTS `reglas_ia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reglas_ia` (
  `id_regla` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `antecedente` text NOT NULL COMMENT 'JSON con condiciones',
  `consecuente` text NOT NULL COMMENT 'Texto de recomendación',
  `prioridad` int(11) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'En plan_seguridad',
  `descripcion_explicativa` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_regla`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reglas_ia`
--

LOCK TABLES `reglas_ia` WRITE;
/*!40000 ALTER TABLE `reglas_ia` DISABLE KEYS */;
INSERT INTO `reglas_ia` VALUES (1,'R-001','Sobreentrenamiento por ACWR alto y RPE','{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.5},{\"campo\":\"rpe_promedio_3\",\"operador\":\">\",\"valor\":8}],\"logica\":\"AND\"}','Riesgo de sobreentrenamiento. Reducir volumen 30%. Evaluar descanso 24h.',10,1,NULL,'ACWR mayor a 1.5 combinado con RPE promedio mayor a 8 en las últimas 3 sesiones.','2026-05-17 16:39:25',NULL),(2,'R-002','Alto riesgo de re-lesión','{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.5},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":true}],\"logica\":\"AND\"}','Alto riesgo de re-lesión. Suspender carga completa. Derivar a médico.',9,1,NULL,'ACWR elevado con lesión activa presente.','2026-05-17 16:39:25',NULL),(3,'R-003','Fatiga por privación de sueño','{\"condiciones\":[{\"campo\":\"rpe_promedio_3\",\"operador\":\">\",\"valor\":7},{\"campo\":\"horas_sueno_promedio\",\"operador\":\"<\",\"valor\":6},{\"campo\":\"sesiones_consecutivas\",\"operador\":\">=\",\"valor\":3}],\"logica\":\"AND\"}','Fatiga acumulada por privación de sueño. Programar sesión de recuperación activa.',8,1,NULL,'RPE elevado con promedio de sueño menor a 6 horas en 3+ sesiones.','2026-05-17 16:39:25',NULL),(4,'R-004','Carga elevada en fase de Acumulación','{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Acumulacion\"},{\"campo\":\"srpe_promedio_semanal\",\"operador\":\">\",\"valor\":600}],\"logica\":\"AND\"}','Carga subjetiva elevada para fase de acumulación. Reducir intensidad de series principales.',7,1,NULL,'En fase de acumulación el sRPE semanal no debería exceder el umbral.','2026-05-17 16:39:25',NULL),(5,'R-005','Exceso de volumen en Tapering','{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_volumen\",\"operador\":\">\",\"valor\":10}],\"logica\":\"AND\"}','Exceso de volumen en taper. Ajustar a planificación original de realización.',8,1,NULL,'Volumen ejecutado no debería superar el planificado en más de 10% en taper.','2026-05-17 16:39:25',NULL),(6,'R-006','Transmutación efectiva','{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Transmutacion\"},{\"campo\":\"mejora_marca_fase\",\"operador\":\">\",\"valor\":3}],\"logica\":\"AND\"}','Transmutación efectiva. Mantener intensidad y progresión actual.',5,1,NULL,'Mejora mayor al 3% en marcas de control durante transmutación.','2026-05-17 16:39:25',NULL),(7,'R-007','Estirón de crecimiento','{\"condiciones\":[{\"campo\":\"crecimiento_estatura_6m\",\"operador\":\">\",\"valor\":3},{\"campo\":\"categoria\",\"operador\":\"IN\",\"valor\":[\"Pre-Infantil\",\"Infantil A\",\"Infantil B\",\"Juvenil A\"]}],\"logica\":\"AND\"}','Estirón de crecimiento detectado. Monitorear técnica y reducir carga en hombros.',6,1,NULL,'Crecimiento mayor a 3cm en 6 meses en categorías infantil/juvenil.','2026-05-17 16:39:25',NULL),(8,'R-008','Readiness óptimo','{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\">=\",\"valor\":0.8},{\"campo\":\"acwr\",\"operador\":\"<=\",\"valor\":1.3},{\"campo\":\"rpe_promedio_3\",\"operador\":\">=\",\"valor\":5},{\"campo\":\"rpe_promedio_3\",\"operador\":\"<=\",\"valor\":7},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":false}],\"logica\":\"AND\"}','Readiness óptimo. Mantener carga planificada.',1,1,NULL,'ACWR en rango óptimo (0.8-1.3), RPE moderado (5-7) y sin lesiones activas.','2026-05-17 16:39:25',NULL),(9,'R-009','Desviación significativa del plan','{\"condiciones\":[{\"campo\":\"sesiones_desviacion\",\"operador\":\">=\",\"valor\":3},{\"campo\":\"desviacion_volumen\",\"operador\":\"<\",\"valor\":-20}],\"logica\":\"AND\"}','Desviación significativa del plan en 3+ sesiones. Evaluar causas y reajustar.',6,1,NULL,'Volumen 20% o más por debajo del planificado en 3+ sesiones consecutivas.','2026-05-17 16:39:25',NULL),(10,'R-010','Tapering efectivo','{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_marca_control\",\"operador\":\"<=\",\"valor\":2}],\"logica\":\"AND\"}','Tapering efectivo. Mantener plan de competición.',4,1,NULL,'Marca en control dentro del 2% del PB en fase de realización.','2026-05-17 16:39:25',NULL),(11,'R-011','Tapering inefectivo','{\"condiciones\":[{\"campo\":\"fase_actual\",\"operador\":\"=\",\"valor\":\"Realizacion\"},{\"campo\":\"desviacion_marca_control\",\"operador\":\">\",\"valor\":3}],\"logica\":\"AND\"}','Tapering inefectivo. Revisar volumen y distribución de la semana de realización.',7,1,NULL,'Marca en control peor en más de 3% respecto al PB en taper.','2026-05-17 16:39:25',NULL),(12,'R-012','Desacondicionamiento por baja carga','{\"condiciones\":[{\"campo\":\"acwr\",\"operador\":\"<\",\"valor\":0.8}],\"logica\":\"AND\"}','Atleta sub-entrenado. Incrementar progresivamente la carga.',6,1,NULL,'ACWR menor a 0.8 indica carga aguda muy por debajo de la crónica.','2026-05-17 16:39:25',NULL),(13,'R-013','Alerta por asistencia irregular','{\"condiciones\":[{\"campo\":\"inasistencias_consecutivas\",\"operador\":\">=\",\"valor\":3},{\"campo\":\"inasistencias_justificadas\",\"operador\":\"=\",\"valor\":false}],\"logica\":\"AND\"}','3+ inasistencias consecutivas sin justificación. Contactar al representante.',5,1,NULL,'Atleta con 3 o más inasistencias consecutivas sin justificar.','2026-05-17 16:39:25',NULL),(14,'R-014','Variación de peso relevante','{\"condiciones\":[{\"campo\":\"variacion_peso_30d\",\"operador\":\">\",\"valor\":5}],\"logica\":\"AND\"}','Variación de peso mayor al 5% en 30 días. Evaluar con médico.',7,1,NULL,'Cambio significativo de peso corporal en período corto.','2026-05-17 16:39:25',NULL),(15,'R-015','Riesgo en competición próxima','{\"condiciones\":[{\"campo\":\"dias_para_competencia\",\"operador\":\"<=\",\"valor\":7},{\"campo\":\"acwr\",\"operador\":\">\",\"valor\":1.3},{\"campo\":\"lesion_activa\",\"operador\":\"=\",\"valor\":true}],\"logica\":\"AND\"}','Competencia próxima (≤7 días) con carga elevada y lesión activa. Reevaluar participación.',10,1,NULL,'Condiciones de riesgo a menos de 7 días de competencia.','2026-05-17 16:39:25',NULL);
/*!40000 ALTER TABLE `reglas_ia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reglas_log`
--

DROP TABLE IF EXISTS `reglas_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reglas_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_regla` int(11) NOT NULL,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `fecha_disparo` datetime NOT NULL DEFAULT current_timestamp(),
  `valores_hechos` text DEFAULT NULL,
  `recomendacion_generada` text DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `fk_rl_regla` (`id_regla`),
  KEY `fk_rl_atleta` (`id_atleta`),
  KEY `fk_rl_sesion` (`id_sesion`),
  CONSTRAINT `fk_rl_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_rl_regla` FOREIGN KEY (`id_regla`) REFERENCES `reglas_ia` (`id_regla`) ON DELETE CASCADE,
  CONSTRAINT `fk_rl_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reglas_log`
--

LOCK TABLES `reglas_log` WRITE;
/*!40000 ALTER TABLE `reglas_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `reglas_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `representantes`
--

DROP TABLE IF EXISTS `representantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `representantes` (
  `id_representante` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `parentesco` enum('Padre','Madre','Tutor','Otro') NOT NULL,
  `telefono_principal` varchar(20) DEFAULT NULL,
  `telefono_secundario` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_representante`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `representantes`
--

LOCK TABLES `representantes` WRITE;
/*!40000 ALTER TABLE `representantes` DISABLE KEYS */;
INSERT INTO `representantes` VALUES (1,'V-8591799','Jose  Gregorio','Pirolo Gonzalez','Padre','04121273248','02517183360','jose.pirolo@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca Las Delicias',1,'2026-05-20 16:48:30','Activo'),(2,'V-10762015','Josefina','Navarro Corro','Madre','04245728016','02517183361','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca, Las Delicias',1,'2026-05-20 18:09:41','Activo'),(3,'2383050','Lourdes','Corro','Tutor','04120121212','02517183360','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',1,'2026-05-20 22:40:25','Inactivo'),(4,'V-8555900','Pedro','Ramones','Padre','04121273240','02517183360','pedro.ramones@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',3,'2026-06-23 00:45:41','Activo'),(5,'E-21232340','Rosanny','Rivero','Madre','04245728016','02517183360','rosanny@gmail.com','La sabana Carrera 5',3,'2026-06-23 02:26:18','Activo');
/*!40000 ALTER TABLE `representantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `series_sesion`
--

DROP TABLE IF EXISTS `series_sesion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_sesion` (
  `id_serie` int(11) NOT NULL AUTO_INCREMENT,
  `id_sesion` int(11) NOT NULL,
  `id_drill` int(11) DEFAULT NULL,
  `orden_ejecucion` int(11) NOT NULL,
  `bloque` enum('Calentamiento','Principal','VueltaCalma') NOT NULL DEFAULT 'Principal',
  `ejercicio_descripcion` varchar(255) DEFAULT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `distancia_m` int(11) DEFAULT NULL,
  `descanso_seg` int(11) DEFAULT NULL,
  `zona_intensidad` enum('Z1','Z2','Z3','Z4','Z5') DEFAULT NULL,
  `ritmo_objetivo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_serie`),
  KEY `fk_ss_sesion` (`id_sesion`),
  KEY `fk_ss_drill` (`id_drill`),
  CONSTRAINT `fk_ss_drill` FOREIGN KEY (`id_drill`) REFERENCES `drills` (`id_drill`) ON DELETE SET NULL,
  CONSTRAINT `fk_ss_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series_sesion`
--

LOCK TABLES `series_sesion` WRITE;
/*!40000 ALTER TABLE `series_sesion` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_sesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones`
--

DROP TABLE IF EXISTS `sesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones` (
  `id_sesion` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_usuario_creador` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_sesion`),
  KEY `fk_se_micro` (`id_microciclo`),
  KEY `fk_se_grupo` (`id_grupo`),
  KEY `fk_se_fase` (`id_fase_actual`),
  CONSTRAINT `fk_se_fase` FOREIGN KEY (`id_fase_actual`) REFERENCES `fases_periodizacion` (`id_fase`) ON DELETE SET NULL,
  CONSTRAINT `fk_se_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`),
  CONSTRAINT `fk_se_micro` FOREIGN KEY (`id_microciclo`) REFERENCES `microciclos` (`id_microciclo`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
INSERT INTO `sesiones` VALUES (1,NULL,1,'2026-06-13','Tecnica',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parcial',NULL,'2026-06-11 16:05:47','2026-06-16 20:20:59');
/*!40000 ALTER TABLE `sesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temporadas`
--

DROP TABLE IF EXISTS `temporadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temporadas` (
  `id_temporada` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_temporada`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temporadas`
--

LOCK TABLES `temporadas` WRITE;
/*!40000 ALTER TABLE `temporadas` DISABLE KEYS */;
INSERT INTO `temporadas` VALUES (1,'Temporada 2025-2026','2025-09-01','2026-08-31',1),(2,'Temporada 2026-2027','2026-09-01','2027-08-31',0);
/*!40000 ALTER TABLE `temporadas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiempos_corte_evento`
--

DROP TABLE IF EXISTS `tiempos_corte_evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiempos_corte_evento` (
  `id_tiempo_corte` int(11) NOT NULL AUTO_INCREMENT,
  `id_evento` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia` int(11) NOT NULL,
  `tiempo_corte_segundos` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_tiempo_corte`),
  UNIQUE KEY `id_evento` (`id_evento`,`id_categoria`,`estilo`,`distancia`),
  KEY `fk_tce_categoria` (`id_categoria`),
  CONSTRAINT `fk_tce_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_feveda` (`id_categoria`),
  CONSTRAINT `fk_tce_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiempos_corte_evento`
--

LOCK TABLES `tiempos_corte_evento` WRITE;
/*!40000 ALTER TABLE `tiempos_corte_evento` DISABLE KEYS */;
/*!40000 ALTER TABLE `tiempos_corte_evento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_asistencia_resumen`
--

DROP TABLE IF EXISTS `v_asistencia_resumen`;
/*!50001 DROP VIEW IF EXISTS `v_asistencia_resumen`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_asistencia_resumen` AS SELECT
 1 AS `id_atleta`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `total_registros`,
  1 AS `presentes`,
  1 AS `ausentes`,
  1 AS `tardanzas`,
  1 AS `porcentaje_asistencia` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_atleta_info`
--

DROP TABLE IF EXISTS `v_atleta_info`;
/*!50001 DROP VIEW IF EXISTS `v_atleta_info`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_atleta_info` AS SELECT
 1 AS `id_atleta`,
  1 AS `cedula`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `fecha_nacimiento`,
  1 AS `edad_actual`,
  1 AS `sexo`,
  1 AS `estado`,
  1 AS `categoria`,
  1 AS `grupo_sanguineo`,
  1 AS `alergias`,
  1 AS `numero_feveda`,
  1 AS `fecha_registro_club` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_carga_semanal`
--

DROP TABLE IF EXISTS `v_carga_semanal`;
/*!50001 DROP VIEW IF EXISTS `v_carga_semanal`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_carga_semanal` AS SELECT
 1 AS `id_atleta`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `fecha`,
  1 AS `srpe_total`,
  1 AS `volumen_total_m`,
  1 AS `carga_aguda_7d`,
  1 AS `carga_cronica_28d`,
  1 AS `acwr`,
  1 AS `semaforo_acwr`,
  1 AS `monotonia_semanal`,
  1 AS `strain_semanal` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_lesion_activa`
--

DROP TABLE IF EXISTS `v_lesion_activa`;
/*!50001 DROP VIEW IF EXISTS `v_lesion_activa`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_lesion_activa` AS SELECT
 1 AS `id_atleta`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `zona_anatomica`,
  1 AS `tipo`,
  1 AS `nivel_molestia`,
  1 AS `estado`,
  1 AS `fecha_inicio`,
  1 AS `fecha_estimada_recup` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_ranking`
--

DROP TABLE IF EXISTS `v_ranking`;
/*!50001 DROP VIEW IF EXISTS `v_ranking`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ranking` AS SELECT
 1 AS `id_marca`,
  1 AS `id_atleta`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `estilo`,
  1 AS `distancia_m`,
  1 AS `tipo_piscina`,
  1 AS `tiempo_final_seg`,
  1 AS `fecha`,
  1 AS `categoria`,
  1 AS `sexo`,
  1 AS `posicion_ranking` */;
SET character_set_client = @saved_cs_client;

--
-- Current Database: `sis_seguridad`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sis_seguridad` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sis_seguridad`;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (2,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-06-08_23-15-35.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-08 17:15:36'),(3,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-06-08_23-18-41.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-08 17:18:42'),(4,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: SGRD_Backup_2026-06-08_23-22-59.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-08 17:34:31'),(5,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-06-09_00-41-15.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-08 18:41:16'),(6,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: SGRD_Backup_2026-06-09_01-04-23.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-08 19:18:42'),(7,3,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_marca\":\"\",\"id_atleta\":\"4\",\"fecha\":\"2026-06-08\",\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"nivel_evento\":\"Control\",\"id_sesion\":\"\",\"id_evento\":\"\",\"tiempo_reaccion_seg\":\"\",\"tiempo_viraje_seg\":\"\",\"brazadas_por_largo\":\"15\",\"tiempo_final_seg\":\"60.00\",\"splits\":{\"25\":\"25.00\",\"50\":\"35.00\"},\"observaciones\":\"\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-08 23:24:00'),(8,3,'Seguridad','CREATE',0,'usuario',NULL,'Jose Miguel Pirolo Narro','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-08 23:57:30'),(9,3,'Lesiones','INSERT',NULL,'Registro de nueva lesión',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"Activa\",\"observaciones\":\"asas\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 12:24:57'),(10,3,'Seguridad','CREATE',0,'usuario',NULL,'jose pirolo','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 13:39:04'),(11,6,'Lesiones','UPDATE',1,'Actualización de lesión','Ver registro previo','{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"Activa\",\"observaciones\":\"sdfsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 14:46:41'),(12,6,'Lesiones','UPDATE',1,'Actualización de lesión','Ver registro previo','{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"EnRehabilitacion\",\"observaciones\":\"sdfsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 14:46:50'),(13,6,'Lesiones','UPDATE',1,'Actualización de lesión','Ver registro previo','{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"Recuperada\",\"observaciones\":\"sdfsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 14:47:04'),(14,6,'Lesiones','UPDATE',1,'Actualización',NULL,'{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"Activa\",\"observaciones\":\"sdfsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 16:36:12'),(15,6,'Lesiones','UPDATE',1,'Actualización de lesión','Ver registro previo','{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-11\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Izquierdo\",\"tipo\":\"Recidiva\",\"nivel_molestia\":\"2\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hiel\",\"profesional\":\"jose antonio\",\"estado\":\"Recuperada\",\"observaciones\":\"sdfsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-10 16:37:05'),(16,6,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"3\",\"fecha_inicio\":\"2026-06-11\",\"fecha_estimada_recup\":\"2026-06-12\",\"zona_anatomica\":\"Pie\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"8\",\"diagnostico\":\"pie dolorido\",\"tratamiento\":\"hielo\",\"profesional\":\"jose antonio\",\"estado\":\"Activa\",\"observaciones\":\"dfdfhd\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-11 07:57:52'),(17,6,'Lesiones','UPDATE',2,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"2\",\"accion\":\"actualizar\",\"id_atleta\":\"3\",\"fecha_inicio\":\"2026-06-11\",\"fecha_estimada_recup\":\"2026-06-12\",\"zona_anatomica\":\"Pie\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"8\",\"diagnostico\":\"pie dolorido\",\"tratamiento\":\"hielo\",\"profesional\":\"jose antonio\",\"estado\":\"EnRehabilitacion\",\"observaciones\":\"sdgsdsdf\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-11 07:58:43'),(18,6,'Lesiones','',2,'Movido a papelera',NULL,'Motivo: fsfdfsfsdfsdfsdf','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-11 13:31:01'),(19,6,'Lesiones','',2,'Restaurado desde papelera',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-11 13:33:35'),(20,6,'Lesiones','',1,'Movido a papelera',NULL,'Motivo: sfdfsdfsdfsdfsdf','::1',NULL,'2026-06-11 16:34:51'),(21,6,'Lesiones','',1,'Eliminación física en base de datos',NULL,'Registro borrado permanentemente','::1',NULL,'2026-06-11 17:23:00'),(22,6,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-12\",\"fecha_estimada_recup\":\"2026-06-14\",\"zona_anatomica\":\"Hombro\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"3\",\"diagnostico\":\"contractura\",\"tratamiento\":\"hielo\",\"profesional\":\"jose antonio\",\"estado\":\"Activa\",\"observaciones\":\"adfasd\"}','::1',NULL,'2026-06-12 01:05:51'),(23,6,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"2\",\"fecha_inicio\":\"2026-06-10\",\"fecha_estimada_recup\":\"2026-06-12\",\"zona_anatomica\":\"Codo\",\"lado\":\"Derecho\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"4\",\"diagnostico\":\"dolor de codo\",\"tratamiento\":\"hielo\",\"profesional\":\"josea ntonio\",\"estado\":\"Activa\",\"observaciones\":\"sfasd\"}','::1',NULL,'2026-06-12 02:14:28'),(24,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-06-12_08-44-30.sql','::1',NULL,'2026-06-12 02:44:31'),(25,3,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_marca\":\"\",\"id_atleta\":\"1\",\"fecha\":\"2026-06-12\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"nivel_evento\":\"Control\",\"id_sesion\":\"\",\"id_evento\":\"\",\"tiempo_reaccion_seg\":\"12.33\",\"tiempo_viraje_seg\":\"05.00\",\"brazadas_por_largo\":\"25\",\"tiempo_final_seg\":\"90.00\",\"splits\":{\"25\":\"25.00\",\"50\":\"30.00\",\"75\":\"27.00\",\"100\":\"08.00\"},\"observaciones\":\"Buen rendimiento \"}','::1',NULL,'2026-06-12 12:34:33'),(26,3,'Marcas','UPDATE',10,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"4\",\"fecha\":\"2026-06-08\",\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"nivel_evento\":\"Control\",\"id_sesion\":\"\",\"id_evento\":\"\",\"tiempo_reaccion_seg\":\"\",\"tiempo_viraje_seg\":\"\",\"brazadas_por_largo\":\"15\",\"tiempo_final_seg\":\"60.00\",\"splits\":{\"25\":\"25.00\",\"50\":\"35.00\"},\"observaciones\":\"Probando\"}','::1',NULL,'2026-06-12 12:35:54'),(27,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-06-12 12:42:00'),(28,3,'Seguridad','CREATE',0,'usuario',NULL,'Juana  Ramones','::1',NULL,'2026-06-12 12:45:00'),(29,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 21:26:13'),(30,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:06:46'),(31,5,'Asistencias','',4,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 22:07:10'),(32,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 22:07:29'),(33,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:24:26'),(34,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 22:24:46'),(35,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:24:59'),(36,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:25:43'),(37,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:25:45'),(38,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:25:59'),(39,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:26:03'),(40,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 22:26:09'),(41,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 22:29:44'),(42,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 22:30:10'),(43,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:30'),(44,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:32'),(45,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:35'),(46,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:37'),(47,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:39'),(48,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:42'),(49,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:44'),(50,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:47'),(51,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:49'),(52,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:51'),(53,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 22:30:54'),(54,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-12 23:49:26'),(55,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 23:49:32'),(56,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:49:56'),(57,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:13'),(58,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:15'),(59,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:18'),(60,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:20'),(61,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:23'),(62,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:25'),(63,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:28'),(64,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-12 23:50:30'),(65,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 23:52:55'),(66,5,'Asistencias','',4,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-12 23:53:58'),(67,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 00:37:04'),(68,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 00:37:06'),(69,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-13 00:37:13'),(70,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 00:41:26'),(71,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 00:41:28'),(72,5,'Asistencias','',4,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-13 00:42:20'),(73,5,'Asistencias','',5,'estado','','Ajuste manual a: Presente','192.168.0.185',NULL,'2026-06-13 00:59:19'),(74,5,'Asistencias','',4,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-13 01:30:33'),(75,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-13 01:30:48'),(76,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 01:31:13'),(77,5,'Asistencias','',5,'estado','','Ajuste manual a: Falto','192.168.0.185',NULL,'2026-06-13 01:31:38'),(78,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 01:32:20'),(79,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 01:36:22'),(80,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 01:57:26'),(81,5,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: Juana Ramones','192.168.0.185',NULL,'2026-06-13 02:04:31'),(82,3,'Marcas','UPDATE',11,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"1\",\"fecha\":\"2026-06-15\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"nivel_evento\":\"Regional\",\"id_sesion\":\"\",\"id_evento\":\"\",\"tiempo_reaccion_seg\":\"12.33\",\"tiempo_viraje_seg\":\"10.00\",\"brazadas_por_largo\":\"30\",\"tiempo_final_seg\":\"90.00\",\"splits\":{\"25\":\"25.00\",\"50\":\"30.00\",\"75\":\"27.00\",\"100\":\"8.00\"},\"observaciones\":\"Buen rendimiento eee\"}','::1',NULL,'2026-06-16 02:18:25'),(83,3,'Marcas','RESTORE',2,'estado','Inactivo','Activo','::1',NULL,'2026-06-16 02:25:59'),(84,3,'Marcas','DELETE',11,'estado','Activo','Inactivo (Motivo: joslakskskksks)','::1',NULL,'2026-06-16 02:33:24'),(85,3,'Marcas','RESTORE',11,'estado','Inactivo','Activo','::1',NULL,'2026-06-16 02:34:45'),(86,3,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_marca\":\"\",\"id_atleta\":\"3\",\"fecha\":\"2026-06-16\",\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"nivel_evento\":\"Nacional\",\"id_sesion\":\"\",\"id_evento\":\"\",\"tiempo_reaccion_seg\":\"05.00\",\"tiempo_viraje_seg\":\"06.00\",\"brazadas_por_largo\":\"15\",\"tiempo_final_seg\":\"120.00\",\"splits\":{\"25\":\"45.00\",\"50\":\"75.00\"},\"observaciones\":\"comentarios\"}','::1',NULL,'2026-06-16 02:36:25'),(87,3,'Marcas','UPDATE',12,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"3\",\"id_sesion\":null,\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"120.00\",\"tiempo_reaccion_seg\":\"5.00\",\"tiempo_viraje_seg\":\"6.00\",\"nivel_evento\":\"Nacional\",\"fecha\":\"2026-06-16\",\"observaciones\":\"comentarios dsfsd\",\"num_brazadas\":null,\"splits\":{\"25\":\"45.00\",\"50\":\"75.00\"},\"brazadas_por_largo\":\"15\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-16 03:22:39'),(88,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-06-17_00-26-20.sql','::1',NULL,'2026-06-16 18:26:21'),(89,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: SGRD_Backup_2026-06-17_07-01-00.sql','::1',NULL,'2026-06-17 11:53:47'),(90,3,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"120.00\",\"tiempo_reaccion_seg\":\"02.00\",\"tiempo_viraje_seg\":\"03.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-13\",\"observaciones\":\"hola\",\"num_brazadas\":null,\"splits\":{\"25\":\"60.00\",\"50\":\"60.00\"},\"brazadas_por_largo\":\"15\",\"id_marca\":null,\"motivo_eliminacion\":null}','::1',NULL,'2026-06-17 12:47:47'),(91,3,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"1\",\"id_sesion\":null,\"id_evento\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"25m\",\"tiempo_final_seg\":\"180.00\",\"tiempo_reaccion_seg\":\"02.50\",\"tiempo_viraje_seg\":\"05.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-17\",\"observaciones\":\"prueba de registro\",\"num_brazadas\":null,\"splits\":{\"25\":\"48.00\",\"50\":\"52.00\",\"75\":\"40.00\",\"100\":\"40.00\"},\"brazadas_por_largo\":\"16\",\"id_marca\":null,\"motivo_eliminacion\":null}','::1',NULL,'2026-06-17 14:54:58'),(92,3,'Marcas','UPDATE',14,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"2\",\"id_sesion\":null,\"id_evento\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"25m\",\"tiempo_final_seg\":\"180.00\",\"tiempo_reaccion_seg\":\"2.50\",\"tiempo_viraje_seg\":\"5.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-16\",\"observaciones\":\"prueba de registro\",\"num_brazadas\":null,\"splits\":{\"25\":\"48.00\",\"50\":\"52.00\",\"75\":\"40.00\",\"100\":\"40.00\"},\"brazadas_por_largo\":\"16\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-17 15:05:16'),(93,3,'Marcas','UPDATE',13,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"120.00\",\"tiempo_reaccion_seg\":\"2.00\",\"tiempo_viraje_seg\":\"3.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-13\",\"observaciones\":\"hola\",\"num_brazadas\":null,\"splits\":{\"25\":\"60.00\",\"50\":\"60.00\"},\"brazadas_por_largo\":\"15\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-18 18:21:44'),(94,3,'Marcas','UPDATE',13,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"120.00\",\"tiempo_reaccion_seg\":\"2.00\",\"tiempo_viraje_seg\":\"3.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-13\",\"observaciones\":\"hola\",\"num_brazadas\":null,\"splits\":{\"25\":\"60.00\",\"50\":\"60.00\"},\"brazadas_por_largo\":\"15\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-18 18:23:29'),(95,3,'Marcas','UPDATE',13,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"120.00\",\"tiempo_reaccion_seg\":\"2.00\",\"tiempo_viraje_seg\":\"3.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-13\",\"observaciones\":\"hola\",\"num_brazadas\":null,\"splits\":{\"25\":\"60.00\",\"50\":\"60.00\"},\"brazadas_por_largo\":\"15\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-18 18:25:52'),(96,3,'Marcas','UPDATE',14,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"1\",\"id_sesion\":null,\"id_evento\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"25m\",\"tiempo_final_seg\":\"180.00\",\"tiempo_reaccion_seg\":\"2.50\",\"tiempo_viraje_seg\":\"5.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-16\",\"observaciones\":\"prueba de registro\",\"num_brazadas\":null,\"splits\":{\"25\":\"48.00\",\"50\":\"52.00\",\"75\":\"40.00\",\"100\":\"40.00\"},\"brazadas_por_largo\":\"16\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-18 18:40:56'),(97,3,'Marcas','UPDATE',14,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"1\",\"id_sesion\":null,\"id_evento\":\"1\",\"estilo\":\"Braza\",\"distancia_m\":\"100\",\"tipo_piscina\":\"25m\",\"tiempo_final_seg\":\"180.00\",\"tiempo_reaccion_seg\":\"2.50\",\"tiempo_viraje_seg\":\"5.00\",\"nivel_evento\":null,\"fecha\":\"2026-06-16\",\"observaciones\":\"prueba de registro\",\"num_brazadas\":null,\"splits\":{\"25\":\"48.00\",\"50\":\"52.00\",\"75\":\"40.00\",\"100\":\"40.00\"},\"brazadas_por_largo\":\"16\",\"motivo_eliminacion\":null}','::1',NULL,'2026-06-18 18:42:36');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intentos_login`
--

DROP TABLE IF EXISTS `intentos_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=610 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
INSERT INTO `intentos_login` VALUES (1,NULL,'admin@sistema.com','::1',1,'2026-05-20 15:47:29'),(2,NULL,'admin@sistema.com','::1',1,'2026-05-20 21:21:21'),(3,NULL,'admin@sistema.com','::1',1,'2026-05-20 21:24:30'),(4,NULL,'admin@sistema.com','::1',1,'2026-05-22 13:09:09'),(5,NULL,'admin@sistema.com','::1',1,'2026-06-01 18:50:38'),(6,NULL,'admin@sistema.com','127.0.0.1',0,'2026-06-06 15:43:09'),(7,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-06 15:44:02'),(8,4,'correo@correo.com','127.0.0.1',1,'2026-06-06 17:59:29'),(9,NULL,'admin@sistema.com','127.0.0.1',0,'2026-06-06 18:01:18'),(10,NULL,'admin@sistema.com','127.0.0.1',0,'2026-06-06 18:01:31'),(11,3,'admin@sgrd.com','127.0.0.1',0,'2026-06-06 18:01:54'),(12,3,'admin@sgrd.com','127.0.0.1',0,'2026-06-06 18:01:58'),(13,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-06 18:02:09'),(14,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-06 18:03:15'),(15,3,'admin@sgrd.com','127.0.0.1',0,'2026-06-07 08:39:14'),(16,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-07 08:39:31'),(17,3,'admin@sgrd.com','::1',1,'2026-06-08 14:07:19'),(18,3,'admin@sgrd.com','::1',0,'2026-06-08 14:19:38'),(19,3,'admin@sgrd.com','::1',1,'2026-06-08 14:19:55'),(20,4,'correo@correo.com','::1',1,'2026-06-08 14:33:08'),(21,3,'admin@sgrd.com','::1',1,'2026-06-08 15:23:26'),(22,3,'admin@sgrd.com','::1',1,'2026-06-08 15:24:02'),(23,3,'admin@sgrd.com','::1',1,'2026-06-08 15:51:02'),(24,3,'admin@sgrd.com','::1',1,'2026-06-08 15:58:53'),(25,3,'admin@sgrd.com','::1',1,'2026-06-08 16:00:05'),(26,3,'admin@sgrd.com','::1',1,'2026-06-08 16:03:52'),(27,3,'admin@sgrd.com','::1',1,'2026-06-08 16:55:48'),(28,3,'admin@sgrd.com','::1',1,'2026-06-08 17:50:39'),(29,NULL,'foo-bar@example.com','127.0.0.1',0,'2026-06-08 21:25:10'),(30,3,'admin@sgrd.com','::1',1,'2026-06-08 21:32:24'),(31,3,'admin@sgrd.com','::1',1,'2026-06-08 21:32:35'),(32,NULL,'foo-bar@example.com','127.0.0.1',0,'2026-06-08 21:56:51'),(33,3,'admin@sgrd.com','192.168.0.185',1,'2026-06-08 22:03:07'),(34,3,'admin@sgrd.com','::1',1,'2026-06-08 22:16:47'),(35,5,'josemiguel.pirolo@gmail.com','::1',1,'2026-06-08 23:57:57'),(36,3,'admin@sgrd.com','::1',1,'2026-06-08 23:59:10'),(37,5,'josemiguel.pirolo@gmail.com','::1',1,'2026-06-09 00:11:52'),(38,3,'admin@sgrd.com','::1',1,'2026-06-09 10:07:23'),(39,3,'admin@sgrd.com','::1',1,'2026-06-09 10:14:10'),(40,3,'admin@sgrd.com','::1',1,'2026-06-09 10:15:10'),(41,NULL,'foo-bar@example.com','127.0.0.1',0,'2026-06-09 10:39:28'),(42,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:30'),(43,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:30'),(44,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(45,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(46,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(47,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(48,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(49,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(50,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(51,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(52,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:31'),(53,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(54,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(55,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(56,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(57,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(58,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(59,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(60,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:32'),(61,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(62,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(63,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(64,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(65,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(66,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:33'),(67,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(68,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(69,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(70,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(71,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(72,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(73,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:34'),(74,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(75,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(76,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(77,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(78,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(79,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(80,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(81,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:35'),(82,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(83,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(84,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(85,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(86,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(87,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(88,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:36'),(89,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(90,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(91,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(92,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(93,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(94,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(95,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:37'),(96,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(97,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(98,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(99,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(100,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(101,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(102,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(103,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(104,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:38'),(105,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(106,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(107,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(108,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(109,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(110,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(111,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:39'),(112,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(113,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(114,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(115,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(116,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(117,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:40'),(118,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(119,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(120,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(121,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(122,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(123,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(124,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(125,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:41'),(126,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(127,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(128,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(129,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(130,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(131,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(132,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(133,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:42'),(134,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:43'),(135,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:43'),(136,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:43'),(137,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:43'),(138,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:43'),(139,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(140,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(141,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(142,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(143,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(144,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(145,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(146,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(147,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(148,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(149,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(150,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:44'),(151,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(152,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(153,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(154,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(155,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(156,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(157,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:45'),(158,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:46'),(159,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:46'),(160,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:46'),(161,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:46'),(162,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:46'),(163,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(164,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(165,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(166,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(167,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(168,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(169,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(170,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(171,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(172,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(173,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(174,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:47'),(175,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(176,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(177,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(178,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(179,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(180,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:48'),(181,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(182,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(183,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(184,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(185,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(186,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(187,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:49'),(188,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:50'),(189,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:50'),(190,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:50'),(191,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:50'),(192,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:50'),(193,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:52'),(194,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:52'),(195,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:53'),(196,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:53'),(197,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:54'),(198,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:55'),(199,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:55'),(200,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:55'),(201,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:56'),(202,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:57'),(203,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:51:59'),(204,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:00'),(205,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:02'),(206,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:03'),(207,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:03'),(208,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:03'),(209,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:04'),(210,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:06'),(211,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:06'),(212,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:06'),(213,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:08'),(214,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:08'),(215,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:09'),(216,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:11'),(217,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:12'),(218,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:12'),(219,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:12'),(220,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:12'),(221,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:13'),(222,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:14'),(223,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:15'),(224,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:16'),(225,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:16'),(226,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:16'),(227,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:17'),(228,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:17'),(229,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:18'),(230,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:19'),(231,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:19'),(232,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:19'),(233,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:20'),(234,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:20'),(235,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:20'),(236,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:20'),(237,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:20'),(238,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(239,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(240,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(241,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(242,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(243,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(244,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:21'),(245,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:22'),(246,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:22'),(247,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:23'),(248,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:23'),(249,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:23'),(250,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:23'),(251,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:24'),(252,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:24'),(253,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:24'),(254,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:24'),(255,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:25'),(256,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:25'),(257,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:25'),(258,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:26'),(259,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:26'),(260,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:26'),(261,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:26'),(262,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:26'),(263,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:27'),(264,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:27'),(265,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:27'),(266,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:27'),(267,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:28'),(268,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:28'),(269,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:28'),(270,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:28'),(271,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(272,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(273,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(274,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(275,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(276,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:29'),(277,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:30'),(278,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:30'),(279,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:30'),(280,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:30'),(281,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:30'),(282,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:31'),(283,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 10:52:31'),(284,3,'admin@sgrd.com','::1',1,'2026-06-09 10:56:11'),(285,3,'admin@sgrd.com','::1',1,'2026-06-09 10:58:42'),(286,3,'admin@sgrd.com','::1',1,'2026-06-09 11:01:31'),(287,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:21'),(288,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:21'),(289,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:21'),(290,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:21'),(291,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:21'),(292,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(293,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(294,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(295,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(296,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(297,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(298,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(299,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:22'),(300,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(301,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(302,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(303,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(304,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(305,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(306,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(307,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:23'),(308,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(309,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(310,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(311,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(312,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(313,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:24'),(314,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(315,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(316,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(317,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(318,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(319,3,'admin@sgrd.com','::1',1,'2026-06-09 11:02:25'),(320,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(321,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(322,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:25'),(323,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(324,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(325,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(326,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(327,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(328,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(329,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(330,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(331,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:26'),(332,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:27'),(333,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:27'),(334,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:27'),(335,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:27'),(336,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:27'),(337,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(338,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(339,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(340,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(341,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(342,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(343,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(344,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(345,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(346,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(347,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:28'),(348,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(349,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(350,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(351,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(352,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(353,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:29'),(354,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:30'),(355,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:30'),(356,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:30'),(357,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:30'),(358,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(359,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(360,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(361,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(362,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(363,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(364,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(365,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(366,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:31'),(367,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(368,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(369,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(370,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(371,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(372,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:32'),(373,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(374,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(375,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(376,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(377,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(378,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(379,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(380,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(381,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(382,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(383,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(384,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:33'),(385,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(386,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(387,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(388,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(389,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(390,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(391,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:34'),(392,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(393,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(394,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(395,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(396,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(397,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(398,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:35'),(399,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(400,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(401,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(402,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(403,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(404,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(405,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:36'),(406,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(407,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(408,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(409,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(410,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(411,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(412,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:37'),(413,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:38'),(414,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:38'),(415,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:38'),(416,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:38'),(417,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:38'),(418,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(419,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(420,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(421,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(422,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(423,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(424,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(425,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(426,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(427,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:39'),(428,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(429,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(430,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(431,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(432,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(433,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(434,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(435,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:40'),(436,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:43'),(437,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:43'),(438,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:43'),(439,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:44'),(440,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:45'),(441,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:48'),(442,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:48'),(443,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:49'),(444,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:50'),(445,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:50'),(446,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:50'),(447,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:50'),(448,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:51'),(449,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:53'),(450,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:53'),(451,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:53'),(452,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:53'),(453,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:53'),(454,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:54'),(455,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:54'),(456,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:55'),(457,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:55'),(458,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:56'),(459,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:57'),(460,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:57'),(461,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:57'),(462,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:57'),(463,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:58'),(464,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:59'),(465,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:02:59'),(466,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:01'),(467,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:01'),(468,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:02'),(469,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:02'),(470,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:02'),(471,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:02'),(472,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:03'),(473,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:03'),(474,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:03'),(475,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:03'),(476,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:03'),(477,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:04'),(478,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:04'),(479,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:05'),(480,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:05'),(481,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:06'),(482,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:07'),(483,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:07'),(484,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:08'),(485,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:09'),(486,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(487,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(488,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(489,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(490,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(491,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(492,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:10'),(493,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:11'),(494,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:11'),(495,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:11'),(496,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(497,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(498,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(499,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(500,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(501,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:12'),(502,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:13'),(503,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:13'),(504,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:13'),(505,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:13'),(506,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:14'),(507,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:14'),(508,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:14'),(509,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:14'),(510,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(511,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(512,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(513,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(514,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(515,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:15'),(516,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:16'),(517,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:16'),(518,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:16'),(519,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:17'),(520,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:17'),(521,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:19'),(522,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:19'),(523,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:20'),(524,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:20'),(525,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:20'),(526,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:20'),(527,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(528,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(529,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(530,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(531,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(532,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(533,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(534,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(535,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:21'),(536,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:03:22'),(537,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-09 11:04:08'),(538,3,'admin@sgrd.com','::1',1,'2026-06-09 11:25:40'),(539,3,'admin@sgrd.com','127.0.0.1',0,'2026-06-10 07:56:39'),(540,3,'admin@sgrd.com','127.0.0.1',0,'2026-06-10 07:56:59'),(541,3,'admin@sgrd.com','::1',0,'2026-06-10 08:39:34'),(542,3,'admin@sgrd.com','::1',1,'2026-06-10 11:52:05'),(543,3,'admin@sgrd.com','::1',1,'2026-06-10 12:56:16'),(544,3,'admin@sgrd.com','::1',1,'2026-06-10 13:27:43'),(545,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-10 13:39:32'),(546,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-10 13:48:08'),(547,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-10 14:16:28'),(548,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 04:58:29'),(549,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 05:28:51'),(550,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 07:26:51'),(551,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 07:56:11'),(552,3,'admin@sgrd.com','::1',1,'2026-06-11 08:08:29'),(553,3,'admin@sgrd.com','::1',1,'2026-06-11 08:34:39'),(554,3,'admin@sgrd.com','::1',1,'2026-06-11 08:47:26'),(555,3,'admin@sgrd.com','::1',1,'2026-06-11 09:54:54'),(556,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 12:04:28'),(557,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 12:37:19'),(558,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 12:42:32'),(559,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 13:41:58'),(560,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 14:01:59'),(561,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 14:51:00'),(562,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 16:44:05'),(563,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 16:46:13'),(564,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 16:53:25'),(565,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 17:01:08'),(566,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-11 17:19:17'),(567,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-12 00:49:18'),(568,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-12 02:12:03'),(569,3,'admin@sgrd.com','::1',1,'2026-06-12 02:36:04'),(570,3,'admin@sgrd.com','::1',1,'2026-06-12 02:44:16'),(571,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-12 11:22:19'),(572,3,'admin@sgrd.com','::1',1,'2026-06-12 12:31:54'),(573,3,'admin@sgrd.com','::1',1,'2026-06-12 12:38:26'),(574,3,'admin@sgrd.com','::1',1,'2026-06-12 12:44:13'),(575,NULL,'juana.ramones@gmail.com','::1',0,'2026-06-12 12:45:43'),(576,7,'juana.ramones@sgrd.com','::1',1,'2026-06-12 12:47:24'),(577,7,'juana.ramones@sgrd.com','::1',1,'2026-06-12 13:00:24'),(578,5,'josemiguel.pirolo@gmail.com','192.168.0.185',1,'2026-06-12 19:27:45'),(579,5,'josemiguel.pirolo@gmail.com','192.168.0.185',1,'2026-06-12 19:31:53'),(580,5,'josemiguel.pirolo@gmail.com','192.168.0.185',1,'2026-06-12 19:45:30'),(581,5,'josemiguel.pirolo@gmail.com','192.168.0.185',1,'2026-06-12 19:48:01'),(582,3,'admin@sgrd.com','::1',1,'2026-06-12 19:53:20'),(583,5,'josemiguel.pirolo@gmail.com','::1',1,'2026-06-12 20:17:48'),(584,3,'admin@sgrd.com','::1',1,'2026-06-12 20:18:26'),(585,7,'juana.ramones@sgrd.com','::1',1,'2026-06-12 20:18:43'),(586,5,'josemiguel.pirolo@gmail.com','192.168.0.185',1,'2026-06-12 21:14:42'),(587,7,'juana.ramones@sgrd.com','::1',1,'2026-06-13 02:47:33'),(588,NULL,'juana.ramones@gmail.com','192.168.0.185',0,'2026-06-13 02:49:02'),(589,7,'juana.ramones@sgrd.com','192.168.0.185',1,'2026-06-13 02:49:45'),(590,7,'juana.ramones@sgrd.com','192.168.0.185',1,'2026-06-13 11:17:03'),(591,7,'juana.ramones@sgrd.com','192.168.0.185',1,'2026-06-13 11:30:57'),(592,7,'juana.ramones@sgrd.com','192.168.0.185',1,'2026-06-13 12:25:08'),(593,3,'admin@sgrd.com','::1',1,'2026-06-14 01:19:43'),(594,3,'admin@sgrd.com','::1',1,'2026-06-14 01:22:59'),(595,3,'admin@sgrd.com','::1',1,'2026-06-14 01:36:17'),(596,6,'joseantoniopirolo@sgrd.com','::1',1,'2026-06-14 02:04:15'),(597,3,'admin@sgrd.com','::1',1,'2026-06-15 13:44:17'),(598,3,'admin@sgrd.com','::1',1,'2026-06-15 15:15:48'),(599,NULL,'admin\'\'\'\'\'@sgrd.com','::1',0,'2026-06-15 15:16:36'),(600,3,'admin@sgrd.com','::1',1,'2026-06-15 15:16:54'),(601,3,'admin@sgrd.com','::1',1,'2026-06-16 17:51:53'),(602,3,'admin@sgrd.com','::1',1,'2026-06-16 19:46:50'),(603,3,'admin@sgrd.com','::1',1,'2026-06-16 22:01:37'),(604,3,'admin@sgrd.com','::1',1,'2026-06-16 22:08:47'),(605,3,'admin@sgrd.com','::1',1,'2026-06-17 12:04:29'),(606,3,'admin@sgrd.com','::1',1,'2026-06-18 18:13:52'),(607,3,'admin@sgrd.com','::1',1,'2026-06-22 21:24:22'),(608,3,'admin@sgrd.com','::1',1,'2026-06-23 20:47:54'),(609,3,'admin@sgrd.com','::1',1,'2026-06-24 20:29:28');
/*!40000 ALTER TABLE `intentos_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(80) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `modulo` (`modulo`,`accion`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'atletas','ver','Ver expedientes de atletas'),(2,'atletas','crear','Crear nuevo expediente'),(3,'atletas','editar','Editar expediente existente'),(4,'atletas','eliminar','Cambiar estado del atleta (baja lógica)'),(5,'asistencia','ver','Ver registros de asistencia'),(6,'asistencia','registrar','Registrar asistencia QR o manual'),(7,'carriles','ver','Ver asignación de carriles'),(8,'carriles','gestionar','Crear/editar asignaciones de carriles y horarios'),(9,'sesiones','ver','Ver sesiones planificadas'),(10,'sesiones','crear','Crear sesiones de entrenamiento'),(11,'sesiones','editar','Editar sesiones existentes'),(12,'sesiones','completar','Registrar volumen ejecutado post-sesión'),(13,'drills','ver','Ver catálogo de ejercicios'),(14,'drills','crear','Crear nuevos ejercicios'),(15,'drills','editar','Editar ejercicios existentes'),(16,'marcas','ver','Ver marcas registradas'),(17,'marcas','registrar','Registrar nuevas marcas'),(18,'antropometria','ver','Ver mediciones antropométricas'),(19,'antropometria','registrar','Registrar nuevas mediciones'),(20,'lesiones','ver','Ver historial de lesiones'),(21,'lesiones','registrar','Registrar nuevas lesiones'),(22,'lesiones','editar','Actualizar estado y protocolo de retorno'),(23,'rpe','ver','Ver registros de RPE'),(24,'rpe','registrar','Registrar RPE post-sesión'),(25,'eventos','ver','Ver calendario de eventos'),(26,'eventos','crear','Crear eventos'),(27,'eventos','editar','Editar eventos existentes'),(28,'carga','ver','Ver métricas de carga ACWR/TSS'),(29,'rankings','ver','Consultar rankings'),(30,'reportes','generar','Generar reportes PDF'),(31,'periodizacion','ver','Ver planes de periodización'),(32,'periodizacion','generar','Generar plan ATR automático'),(33,'periodizacion','editar','Editar plan de periodización'),(34,'ia','ver','Ver recomendaciones del motor IA'),(35,'ia','gestionar','CRUD de reglas del motor IA'),(36,'seguridad','usuarios','Gestión de usuarios del sistema'),(37,'seguridad','roles','Gestión de roles y permisos'),(38,'seguridad','bitacora','Consulta de bitácora de auditoría'),(39,'representantes','ver','Ver datos de representantes'),(40,'representantes','gestionar','Gestión de representantes legales'),(41,'grupos','ver','Ver grupos de entrenamiento'),(42,'grupos','gestionar','Crear/editar grupos de entrenamiento'),(43,'atletas','gestionar','Acceso al modulo de gestion de entrenadores'),(44,'lesiones','eliminar','Cambiar estado de la lesion (baja lógica)'),(45,'lesiones','reactivar','Cambiar estado de la lesion (baja lógica)'),(48,'lesiones','eliminardb','eliminar fisico'),(49,'seguridad','mantenimiento','importar y exportar bd'),(50,'mi_perfil','ver','ver perfil del atleta');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permisos`
--

DROP TABLE IF EXISTS `rol_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol_permisos` (
  `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  PRIMARY KEY (`id_rol_permiso`),
  UNIQUE KEY `id_rol` (`id_rol`,`id_permiso`),
  KEY `fk_rp_permiso` (`id_permiso`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (8,1,1),(5,1,2),(6,1,3),(7,1,4),(4,1,5),(3,1,6),(11,1,7),(10,1,8),(42,1,9),(40,1,10),(41,1,11),(39,1,12),(14,1,13),(12,1,14),(13,1,15),(26,1,16),(25,1,17),(2,1,18),(1,1,19),(24,1,20),(23,1,21),(22,1,22),(35,1,23),(34,1,24),(17,1,25),(15,1,26),(16,1,27),(9,1,28),(30,1,29),(31,1,30),(29,1,31),(28,1,32),(27,1,33),(21,1,34),(20,1,35),(38,1,36),(37,1,37),(36,1,38),(33,1,39),(32,1,40),(19,1,41),(18,1,42),(152,1,43),(159,1,49),(70,2,1),(68,2,2),(69,2,3),(67,2,5),(66,2,6),(72,2,7),(95,2,9),(93,2,10),(94,2,11),(92,2,12),(75,2,13),(73,2,14),(74,2,15),(84,2,16),(83,2,17),(65,2,18),(64,2,19),(82,2,20),(81,2,21),(91,2,23),(90,2,24),(78,2,25),(76,2,26),(77,2,27),(71,2,28),(88,2,29),(89,2,30),(87,2,31),(86,2,32),(85,2,33),(80,2,34),(79,2,41),(153,2,43),(129,3,1),(128,3,18),(127,3,19),(132,3,20),(131,3,21),(130,3,22),(156,3,23),(157,3,24),(154,3,44),(155,3,45),(158,3,48),(136,4,1),(142,4,16),(141,4,17),(135,4,18),(134,4,19),(140,4,20),(139,4,21),(144,4,23),(143,4,24),(138,4,25),(137,4,28),(160,4,50),(149,5,1),(150,5,25);
/*!40000 ALTER TABLE `rol_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema. Gestión de usuarios, configuración global.',1,'2026-05-17 13:16:25'),(2,'Entrenador','Gestión de atletas asignados, sesiones, marcas, reportes.',1,'2026-05-17 13:16:25'),(3,'Medico','Acceso a módulos médicos y antropometría. Solo lectura en datos deportivos.',1,'2026-05-17 13:16:25'),(4,'Atleta','Solo lectura de su perfil propio y registro de su RPE.',1,'2026-05-17 13:16:25'),(5,'Representante','Solo lectura del atleta bajo su tutela.',1,'2026-05-17 13:16:25');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_activas`
--

DROP TABLE IF EXISTS `sesiones_activas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_activas`
--

LOCK TABLES `sesiones_activas` WRITE;
/*!40000 ALTER TABLE `sesiones_activas` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_activas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_roles`
--

DROP TABLE IF EXISTS `usuario_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_roles`
--

LOCK TABLES `usuario_roles` WRITE;
/*!40000 ALTER TABLE `usuario_roles` DISABLE KEYS */;
INSERT INTO `usuario_roles` VALUES (2,3,1,'2026-06-06 15:30:31'),(3,4,4,'2026-06-06 17:58:37'),(4,5,2,'2026-06-08 23:57:30'),(5,6,3,'2026-06-10 13:39:04'),(6,7,4,'2026-06-12 12:45:00');
/*!40000 ALTER TABLE `usuario_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (3,'00000000','Administrador','Sistema','admin@sgrd.com','$2y$10$26SZiPtKViEwm7tHHEVWl.Z8Y2U.gMOIpdZjYNTeoXUQtMqZH.LZu',1,NULL,0,NULL,NULL,'2026-06-06 15:30:31','2026-06-10 11:52:05'),(4,'28591974','Jesús','Regalado','correo@correo.com','$2y$10$5Cd91bE/v5btQEGF5XfH4usk0MzjoJ09cmnETL1A0zNXV7jRE6TTm',1,NULL,0,NULL,NULL,'2026-06-06 17:58:37','2026-06-06 18:03:42'),(5,'25854831','Jose Miguel','Pirolo Narro','josemiguel.pirolo@gmail.com','$2y$10$YhNg4TYimPC8qRTv8YTcLOAmICRO6BGOVLCPglZdUkZKlC6N5mElK',1,NULL,0,NULL,NULL,'2026-06-08 23:57:30',NULL),(6,'28425405','jose','pirolo','joseantoniopirolo@sgrd.com','$2y$10$62ynRLsgfM0WBCinzTla7eaBT3y/dazo.rVqcOLQeH6rM3rTnXil2',1,NULL,0,NULL,NULL,'2026-06-10 13:39:04',NULL),(7,'V-35000125','Juana ','Ramones','juana.ramones@sgrd.com','$2y$10$4Fq7cjIq9uTOC7NxfzfLn.uhBy2tCC7I3nZIrfF2BtR.LwNWzhbnK',1,NULL,0,NULL,NULL,'2026-06-12 12:45:00',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_usuario_completo`
--

DROP TABLE IF EXISTS `v_usuario_completo`;
/*!50001 DROP VIEW IF EXISTS `v_usuario_completo`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_usuario_completo` AS SELECT
 1 AS `id_usuario`,
  1 AS `cedula`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `correo`,
  1 AS `activo`,
  1 AS `bloqueado_hasta`,
  1 AS `intentos_fallidos`,
  1 AS `roles` */;
SET character_set_client = @saved_cs_client;

--
-- Current Database: `sis_natacion`
--

USE `sis_natacion`;

--
-- Final view structure for view `v_asistencia_resumen`
--

/*!50001 DROP VIEW IF EXISTS `v_asistencia_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_asistencia_resumen` AS select `asis`.`id_atleta` AS `id_atleta`,`a`.`nombres` AS `nombres`,`a`.`apellidos` AS `apellidos`,count(0) AS `total_registros`,sum(case when `asis`.`estado` = 'Presente' then 1 else 0 end) AS `presentes`,sum(case when `asis`.`estado` in ('Ausente','Justificado') then 1 else 0 end) AS `ausentes`,sum(case when `asis`.`estado` = 'Tardanza' then 1 else 0 end) AS `tardanzas`,round(sum(case when `asis`.`estado` in ('Presente','Tardanza') then 1 else 0 end) / count(0) * 100,1) AS `porcentaje_asistencia` from (`asistencia` `asis` join `atletas` `a` on(`asis`.`id_atleta` = `a`.`id_atleta`)) where `asis`.`fecha` >= curdate() - interval 30 day group by `asis`.`id_atleta`,`a`.`nombres`,`a`.`apellidos` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_atleta_info`
--

/*!50001 DROP VIEW IF EXISTS `v_atleta_info`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_atleta_info` AS select `a`.`id_atleta` AS `id_atleta`,`a`.`cedula` AS `cedula`,`a`.`nombres` AS `nombres`,`a`.`apellidos` AS `apellidos`,`a`.`fecha_nacimiento` AS `fecha_nacimiento`,timestampdiff(YEAR,`a`.`fecha_nacimiento`,curdate()) AS `edad_actual`,`a`.`sexo` AS `sexo`,`a`.`estado` AS `estado`,`c`.`nombre` AS `categoria`,`dm`.`grupo_sanguineo` AS `grupo_sanguineo`,`dm`.`alergias` AS `alergias`,`dm`.`numero_feveda` AS `numero_feveda`,`a`.`fecha_registro_club` AS `fecha_registro_club` from ((`atletas` `a` join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) left join `atleta_datos_medicos` `dm` on(`a`.`id_atleta` = `dm`.`id_atleta`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_carga_semanal`
--

/*!50001 DROP VIEW IF EXISTS `v_carga_semanal`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_carga_semanal` AS select `cd`.`id_atleta` AS `id_atleta`,`a`.`nombres` AS `nombres`,`a`.`apellidos` AS `apellidos`,`cd`.`fecha` AS `fecha`,`cd`.`srpe_total` AS `srpe_total`,`cd`.`volumen_total_m` AS `volumen_total_m`,`cd`.`carga_aguda_7d` AS `carga_aguda_7d`,`cd`.`carga_cronica_28d` AS `carga_cronica_28d`,`cd`.`acwr` AS `acwr`,`cd`.`semaforo_acwr` AS `semaforo_acwr`,`cd`.`monotonia_semanal` AS `monotonia_semanal`,`cd`.`strain_semanal` AS `strain_semanal` from (`carga_diaria` `cd` join `atletas` `a` on(`cd`.`id_atleta` = `a`.`id_atleta`)) where `cd`.`fecha` >= curdate() - interval 30 day */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_lesion_activa`
--

/*!50001 DROP VIEW IF EXISTS `v_lesion_activa`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_lesion_activa` AS select `l`.`id_atleta` AS `id_atleta`,`a`.`nombres` AS `nombres`,`a`.`apellidos` AS `apellidos`,`l`.`zona_anatomica` AS `zona_anatomica`,`l`.`tipo` AS `tipo`,`l`.`nivel_molestia` AS `nivel_molestia`,`l`.`estado` AS `estado`,`l`.`fecha_inicio` AS `fecha_inicio`,`l`.`fecha_estimada_recup` AS `fecha_estimada_recup` from (`lesiones` `l` join `atletas` `a` on(`l`.`id_atleta` = `a`.`id_atleta`)) where `l`.`estado` in ('Activa','EnRehabilitacion') order by `l`.`fecha_inicio` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ranking`
--

/*!50001 DROP VIEW IF EXISTS `v_ranking`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ranking` AS select `m`.`id_marca` AS `id_marca`,`m`.`id_atleta` AS `id_atleta`,`a`.`nombres` AS `nombres`,`a`.`apellidos` AS `apellidos`,`m`.`estilo` AS `estilo`,`m`.`distancia_m` AS `distancia_m`,`m`.`tipo_piscina` AS `tipo_piscina`,`m`.`tiempo_final_seg` AS `tiempo_final_seg`,`m`.`fecha` AS `fecha`,`c`.`nombre` AS `categoria`,`a`.`sexo` AS `sexo`,row_number() over ( partition by `m`.`estilo`,`m`.`distancia_m`,`a`.`id_categoria`,`a`.`sexo` order by `m`.`tiempo_final_seg`) AS `posicion_ranking` from ((`marcas` `m` join `atletas` `a` on(`m`.`id_atleta` = `a`.`id_atleta`)) join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) where `m`.`es_pb` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Current Database: `sis_seguridad`
--

USE `sis_seguridad`;

--
-- Final view structure for view `v_usuario_completo`
--

/*!50001 DROP VIEW IF EXISTS `v_usuario_completo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_usuario_completo` AS select `u`.`id_usuario` AS `id_usuario`,`u`.`cedula` AS `cedula`,`u`.`nombres` AS `nombres`,`u`.`apellidos` AS `apellidos`,`u`.`correo` AS `correo`,`u`.`activo` AS `activo`,`u`.`bloqueado_hasta` AS `bloqueado_hasta`,`u`.`intentos_fallidos` AS `intentos_fallidos`,group_concat(`r`.`nombre` order by `r`.`nombre` ASC separator ', ') AS `roles` from ((`usuarios` `u` left join `usuario_roles` `ur` on(`u`.`id_usuario` = `ur`.`id_usuario`)) left join `roles` `r` on(`ur`.`id_rol` = `r`.`id_rol`)) group by `u`.`id_usuario` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-24 20:30:03
