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
-- Table structure for table `alertas_biologicas`
--

DROP TABLE IF EXISTS `alertas_biologicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alertas_biologicas` (
  `id_alerta` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `modulo_origen` varchar(50) NOT NULL,
  `id_registro_origen` int(11) NOT NULL,
  `tipo_alerta` varchar(50) NOT NULL,
  `gravedad` int(11) NOT NULL COMMENT '1=Baja, 2=Media, 3=Alta',
  `mensaje` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_alerta`),
  KEY `id_atleta` (`id_atleta`),
  CONSTRAINT `alertas_biologicas_ibfk_1` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas_biologicas`
--

LOCK TABLES `alertas_biologicas` WRITE;
/*!40000 ALTER TABLE `alertas_biologicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas_biologicas` ENABLE KEYS */;
UNLOCK TABLES;

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
  `fecha_completacion` datetime DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `estado` varchar(20) DEFAULT 'activa',
  PRIMARY KEY (`id_asignacion`),
  KEY `fk_ac_carril` (`id_carril`),
  KEY `fk_ac_bloque` (`id_bloque_horario`),
  KEY `fk_ac_grupo` (`id_grupo`),
  KEY `idx_asignacion_estado` (`estado`),
  KEY `idx_asignacion_fecha_completacion` (`fecha_completacion`),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aspectos_tecnicos`
--

DROP TABLE IF EXISTS `aspectos_tecnicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aspectos_tecnicos` (
  `id_aspecto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_aspecto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aspectos_tecnicos`
--

LOCK TABLES `aspectos_tecnicos` WRITE;
/*!40000 ALTER TABLE `aspectos_tecnicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `aspectos_tecnicos` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_datos_medicos`
--

LOCK TABLES `atleta_datos_medicos` WRITE;
/*!40000 ALTER TABLE `atleta_datos_medicos` DISABLE KEYS */;
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
  `recibe_notificaciones` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_atleta_rep`),
  UNIQUE KEY `id_atleta` (`id_atleta`,`id_representante`),
  KEY `fk_ar_repre` (`id_representante`),
  CONSTRAINT `fk_ar_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_repre` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_representante`
--

LOCK TABLES `atleta_representante` WRITE;
/*!40000 ALTER TABLE `atleta_representante` DISABLE KEYS */;
/*!40000 ALTER TABLE `atleta_representante` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_fecha_autorizacion_insert` BEFORE INSERT ON `atleta_representante` FOR EACH ROW BEGIN
    IF NEW.autorizacion_medica = 1 THEN
        SET NEW.fecha_aut_medica = CURDATE();
    ELSE
        SET NEW.fecha_aut_medica = NULL;
    END IF;

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
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_fecha_autorizacion_update` BEFORE UPDATE ON `atleta_representante` FOR EACH ROW BEGIN
    IF NEW.autorizacion_medica = 1 AND OLD.autorizacion_medica = 0 THEN
        SET NEW.fecha_aut_medica = CURDATE();
    ELSEIF NEW.autorizacion_medica = 0 THEN
        SET NEW.fecha_aut_medica = NULL;
    ELSE
        SET NEW.fecha_aut_medica = OLD.fecha_aut_medica;
    END IF;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atletas`
--

LOCK TABLES `atletas` WRITE;
/*!40000 ALTER TABLE `atletas` DISABLE KEYS */;
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
  `id_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_entrenador`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador`
--

LOCK TABLES `entrenador` WRITE;
/*!40000 ALTER TABLE `entrenador` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento_inscripcion`
--

LOCK TABLES `evento_inscripcion` WRITE;
/*!40000 ALTER TABLE `evento_inscripcion` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_atleta`
--

LOCK TABLES `grupo_atleta` WRITE;
/*!40000 ALTER TABLE `grupo_atleta` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupo_atleta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_entrenador`
--

DROP TABLE IF EXISTS `grupo_entrenador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo_entrenador` (
  `id_grupo_entrenador` int(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` int(11) NOT NULL,
  `id_entrenador` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_grupo_entrenador`),
  KEY `fk_historial_grupo` (`id_grupo`),
  KEY `fk_historial_entrenador` (`id_entrenador`),
  CONSTRAINT `fk_historial_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historial_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_entrenador`
--

LOCK TABLES `grupo_entrenador` WRITE;
/*!40000 ALTER TABLE `grupo_entrenador` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupo_entrenador` ENABLE KEYS */;
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
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Entrenador responsable en plan_seguridad (modulos Jesus)',
  `id_entrenador` int(11) DEFAULT NULL COMMENT 'FK a tabla entrenador (modulos Vero)',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_grupo`),
  KEY `fk_grupos_usuario` (`id_usuario`),
  KEY `fk_grupos_entrenador` (`id_entrenador`),
  CONSTRAINT `fk_grupos_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_entrenamiento`
--

LOCK TABLES `grupos_entrenamiento` WRITE;
/*!40000 ALTER TABLE `grupos_entrenamiento` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesiones`
--

LOCK TABLES `lesiones` WRITE;
/*!40000 ALTER TABLE `lesiones` DISABLE KEYS */;
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
  `nivel_evento` enum('Regional','Nacional','Internacional','Control') NOT NULL DEFAULT 'Control',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
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
  `tiempo_viraje_seg` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_split`),
  UNIQUE KEY `id_marca` (`id_marca`,`parcial_numero`),
  CONSTRAINT `fk_ms_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_splits`
--

LOCK TABLES `marcas_splits` WRITE;
/*!40000 ALTER TABLE `marcas_splits` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_swolf`
--

LOCK TABLES `marcas_swolf` WRITE;
/*!40000 ALTER TABLE `marcas_swolf` DISABLE KEYS */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `motivo_eliminacion` text DEFAULT NULL,
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
-- Table structure for table `observaciones_tecnicas`
--

DROP TABLE IF EXISTS `observaciones_tecnicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observaciones_tecnicas` (
  `id_observacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `id_sesion` int(11) DEFAULT NULL,
  `id_aspecto_tecnico` int(11) NOT NULL,
  `calificacion` tinyint(4) NOT NULL DEFAULT 1,
  `observacion_texto` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_observacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observaciones_tecnicas`
--

LOCK TABLES `observaciones_tecnicas` WRITE;
/*!40000 ALTER TABLE `observaciones_tecnicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `observaciones_tecnicas` ENABLE KEYS */;
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
-- Table structure for table `recomendaciones_carga`
--

DROP TABLE IF EXISTS `recomendaciones_carga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recomendaciones_carga` (
  `id_recomendacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `tipo` varchar(50) NOT NULL COMMENT 'Ej. SOBRECARGA, RECUPERACION, FATIGA',
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_recomendacion`),
  KEY `idx_atleta` (`id_atleta`),
  CONSTRAINT `fk_recom_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recomendaciones_carga`
--

LOCK TABLES `recomendaciones_carga` WRITE;
/*!40000 ALTER TABLE `recomendaciones_carga` DISABLE KEYS */;
/*!40000 ALTER TABLE `recomendaciones_carga` ENABLE KEYS */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `justificacion_softdelete` text DEFAULT NULL,
  PRIMARY KEY (`id_rpe`),
  KEY `fk_rpe_atleta` (`id_atleta`),
  KEY `fk_rpe_sesion` (`id_sesion`),
  CONSTRAINT `fk_rpe_atleta` FOREIGN KEY (`id_atleta`) REFERENCES `atletas` (`id_atleta`) ON DELETE CASCADE,
  CONSTRAINT `fk_rpe_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_rpe`
--

LOCK TABLES `registro_rpe` WRITE;
/*!40000 ALTER TABLE `registro_rpe` DISABLE KEYS */;
/*!40000 ALTER TABLE `registro_rpe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registros_test`
--

DROP TABLE IF EXISTS `registros_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registros_test` (
  `id_registro_test` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `id_tipo_test` int(11) DEFAULT NULL,
  `id_test_pers` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `id_usuario_toma` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('Completo','Parcial','Cancelado') NOT NULL DEFAULT 'Completo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_registro_test`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registros_test`
--

LOCK TABLES `registros_test` WRITE;
/*!40000 ALTER TABLE `registros_test` DISABLE KEYS */;
/*!40000 ALTER TABLE `registros_test` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `representantes`
--

LOCK TABLES `representantes` WRITE;
/*!40000 ALTER TABLE `representantes` DISABLE KEYS */;
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
  `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'Entrenador via sis_seguridad (modulos Jesus)',
  `id_entrenador` int(11) DEFAULT NULL COMMENT 'FK a tabla entrenador (modulos Vero)',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_sesion`),
  KEY `fk_se_micro` (`id_microciclo`),
  KEY `fk_se_grupo` (`id_grupo`),
  KEY `fk_se_fase` (`id_fase_actual`),
  KEY `fk_sesiones_entrenador` (`id_entrenador`),
  CONSTRAINT `fk_se_fase` FOREIGN KEY (`id_fase_actual`) REFERENCES `fases_periodizacion` (`id_fase`) ON DELETE SET NULL,
  CONSTRAINT `fk_se_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_entrenamiento` (`id_grupo`),
  CONSTRAINT `fk_se_micro` FOREIGN KEY (`id_microciclo`) REFERENCES `microciclos` (`id_microciclo`) ON DELETE SET NULL,
  CONSTRAINT `fk_sesiones_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenador` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemetria_live`
--

DROP TABLE IF EXISTS `telemetria_live`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemetria_live` (
  `id_live` int(11) NOT NULL AUTO_INCREMENT,
  `id_atleta` int(11) NOT NULL,
  `distancia_total` int(11) NOT NULL,
  `tipo_piscina` enum('25m','50m') NOT NULL,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `estado_carrera` enum('esperando','iniciando','en_curso','en_viraje','finalizado','cancelado') NOT NULL DEFAULT 'esperando',
  `inicio_timestamp_ms` double DEFAULT NULL,
  `ultima_distancia_recorrida_m` int(11) DEFAULT 0,
  `ultimo_tiempo_parcial_ms` double DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_live`),
  KEY `idx_atleta_live` (`id_atleta`)
) ENGINE=MEMORY AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemetria_live`
--

LOCK TABLES `telemetria_live` WRITE;
/*!40000 ALTER TABLE `telemetria_live` DISABLE KEYS */;
/*!40000 ALTER TABLE `telemetria_live` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temporadas`
--

LOCK TABLES `temporadas` WRITE;
/*!40000 ALTER TABLE `temporadas` DISABLE KEYS */;
/*!40000 ALTER TABLE `temporadas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests_personalizados`
--

DROP TABLE IF EXISTS `tests_personalizados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests_personalizados` (
  `id_test_pers` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_medicion` varchar(100) DEFAULT NULL,
  `unidad_medida` varchar(30) DEFAULT NULL,
  `valor_referencia_min` decimal(10,2) DEFAULT NULL,
  `valor_referencia_max` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario_creador` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_test_pers`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests_personalizados`
--

LOCK TABLES `tests_personalizados` WRITE;
/*!40000 ALTER TABLE `tests_personalizados` DISABLE KEYS */;
INSERT INTO `tests_personalizados` VALUES (1,'algo','prueba de velocidad','velocidad','seg',10.00,50.00,1,3,'2026-07-05 15:37:43');
/*!40000 ALTER TABLE `tests_personalizados` ENABLE KEYS */;
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
-- Table structure for table `tipos_test_predefinidos`
--

DROP TABLE IF EXISTS `tipos_test_predefinidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_test_predefinidos` (
  `id_tipo_test` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_medicion` varchar(100) DEFAULT NULL,
  `unidad_medida` varchar(30) DEFAULT NULL,
  `valor_referencia_min` decimal(10,2) DEFAULT NULL,
  `valor_referencia_max` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `es_personalizado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_tipo_test`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_test_predefinidos`
--

LOCK TABLES `tipos_test_predefinidos` WRITE;
/*!40000 ALTER TABLE `tipos_test_predefinidos` DISABLE KEYS */;
INSERT INTO `tipos_test_predefinidos` VALUES (1,'Lactato 4x200 Acum','Lactato en sangre tras 4x200m acumulativo paso a paso','Lactato','mmol/L',2.00,12.00,1,0),(2,'Sit and Reach','Flexibilidad de espalda posterior','Distancia','cm',-15.00,15.00,1,0),(3,'Flexiones','Fuerza tren superior','Repeticiones','reps',0.00,100.00,1,0),(4,'Dominadas','Fuerza tren superior','Repeticiones','reps',0.00,50.00,1,0),(5,'Salto Horizontal','Potencia de tren inferior','Distancia','cm',0.00,300.00,1,0),(6,'Salto Vertical','Potencia explosiva','Altura','cm',0.00,80.00,1,0),(7,'VO2max Cooper','Capacidad aeróbica estimada','VO2max','ml/kg/min',20.00,80.00,1,0),(8,'Plancha Abdominal','Resistencia core','Segundos','seg',0.00,300.00,1,0),(9,'Sprint 30m Seco','Velocidad en seco','Tiempo','seg',0.00,6.00,1,0);
/*!40000 ALTER TABLE `tipos_test_predefinidos` ENABLE KEYS */;
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
-- Table structure for table `valores_test`
--

DROP TABLE IF EXISTS `valores_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `valores_test` (
  `id_valor` int(11) NOT NULL AUTO_INCREMENT,
  `id_registro_test` int(11) NOT NULL,
  `id_variable` int(11) NOT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `unidad_medida` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_valor`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `valores_test`
--

LOCK TABLES `valores_test` WRITE;
/*!40000 ALTER TABLE `valores_test` DISABLE KEYS */;
INSERT INTO `valores_test` VALUES (1,1,1,0.03,'mmol/L'),(2,1,2,0.06,'bpm'),(3,1,3,0.09,'bpm'),(4,2,7,10.00,'reps'),(76,5,1,2.80,'mmol/L'),(77,5,2,165.00,'bpm'),(78,5,3,130.00,'bpm'),(79,6,4,8.00,'cm'),(80,6,5,12.00,'cm'),(81,6,6,4.00,'cm'),(82,7,7,25.00,'reps'),(83,8,1,3.50,'mmol/L'),(84,8,2,172.00,'bpm'),(85,8,3,140.00,'bpm'),(86,9,4,10.00,'cm'),(87,9,5,18.00,'cm'),(88,9,6,8.00,'cm'),(89,10,10,55.00,'cm'),(90,10,11,2100.00,'m'),(91,10,12,52.00,'ml/kg/min'),(92,11,7,18.00,'reps'),(93,12,1,3.20,'mmol/L'),(94,12,2,168.00,'bpm'),(95,12,3,135.00,'bpm'),(96,13,4,6.00,'cm'),(97,13,5,14.00,'cm'),(98,13,6,8.00,'cm'),(99,14,8,12.00,'reps'),(100,15,1,4.10,'mmol/L'),(101,15,2,175.00,'bpm'),(102,15,3,145.00,'bpm'),(103,16,14,4.20,'seg'),(104,17,1,3.80,'mmol/L'),(105,17,2,170.00,'bpm'),(106,17,3,138.00,'bpm'),(107,18,4,5.00,'cm'),(108,18,5,10.00,'cm'),(109,18,6,5.00,'cm'),(110,19,13,180.00,'seg'),(111,20,1,3.00,'mmol/L'),(112,20,2,168.00,'bpm'),(113,20,3,132.00,'bpm'),(114,21,14,4.80,'seg'),(115,22,9,230.00,'cm'),(116,23,10,62.00,'cm'),(117,24,1,4.50,'mmol/L'),(118,24,2,178.00,'bpm'),(119,24,3,148.00,'bpm'),(120,25,4,7.00,'cm'),(121,25,5,15.00,'cm'),(122,25,6,8.00,'cm'),(123,26,4,5.00,'cm'),(124,26,5,8.00,'cm'),(125,26,6,3.00,'cm'),(126,27,4,3.00,'cm'),(127,27,5,10.00,'cm'),(128,27,6,7.00,'cm'),(129,28,4,4.00,'cm'),(130,28,5,11.00,'cm'),(131,28,6,7.00,'cm'),(132,29,1,2.50,'mmol/L'),(133,29,2,160.00,'bpm'),(134,29,3,125.00,'bpm'),(135,30,1,3.00,'mmol/L'),(136,30,2,162.00,'bpm'),(137,30,3,128.00,'bpm'),(138,31,1,2.80,'mmol/L'),(139,31,2,158.00,'bpm'),(140,31,3,122.00,'bpm'),(141,32,1,3.20,'mmol/L'),(142,32,2,165.00,'bpm'),(143,32,3,130.00,'bpm'),(144,33,7,15.00,'reps'),(145,34,7,12.00,'reps'),(146,35,7,20.00,'reps');
/*!40000 ALTER TABLE `valores_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variables_test`
--

DROP TABLE IF EXISTS `variables_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variables_test` (
  `id_variable` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_test` int(11) DEFAULT NULL,
  `id_test_pers` int(11) DEFAULT NULL,
  `nombre_variable` varchar(100) NOT NULL,
  `unidad` varchar(30) DEFAULT NULL,
  `orden_mostrar` int(11) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_variable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variables_test`
--

LOCK TABLES `variables_test` WRITE;
/*!40000 ALTER TABLE `variables_test` DISABLE KEYS */;
/*!40000 ALTER TABLE `variables_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vista_representantes_atletas`
--

DROP TABLE IF EXISTS `vista_representantes_atletas`;
/*!50001 DROP VIEW IF EXISTS `vista_representantes_atletas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vista_representantes_atletas` AS SELECT
 1 AS `id_representante`,
  1 AS `cedula`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `telefono_principal`,
  1 AS `parentesco`,
  1 AS `estado`,
  1 AS `atletas_vinculados` */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
INSERT INTO `intentos_login` VALUES (1,1,'admin@sgrd.com','::1',1,'2026-09-10 01:52:49');
/*!40000 ALTER TABLE `intentos_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `icono` varchar(50) DEFAULT 'fa-bell',
  `color` varchar(20) DEFAULT 'indigo',
  `enlace_url` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `fk_notif_usuario` (`id_usuario`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'atletas','ver','Ver expedientes de atletas'),(2,'atletas','crear','Crear nuevo expediente'),(3,'atletas','editar','Editar expediente existente'),(4,'atletas','eliminar','Cambiar estado del atleta (baja lógica)'),(5,'asistencia','ver','Ver registros de asistencia'),(6,'asistencia','registrar','Registrar asistencia QR o manual'),(7,'carriles','ver','Ver asignación de carriles'),(8,'carriles','gestionar','Crear/editar asignaciones de carriles y horarios'),(9,'sesiones','ver','Ver sesiones planificadas'),(10,'sesiones','crear','Crear sesiones de entrenamiento'),(11,'sesiones','editar','Editar sesiones existentes'),(12,'sesiones','completar','Registrar volumen ejecutado post-sesión'),(13,'drills','ver','Ver catálogo de ejercicios'),(14,'drills','crear','Crear nuevos ejercicios'),(15,'drills','editar','Editar ejercicios existentes'),(16,'marcas','ver','Ver marcas registradas'),(17,'marcas','registrar','Registrar nuevas marcas'),(18,'antropometria','ver','Ver mediciones antropométricas'),(19,'antropometria','registrar','Registrar nuevas mediciones'),(20,'lesiones','ver','Ver historial de lesiones'),(21,'lesiones','registrar','Registrar nuevas lesiones'),(22,'lesiones','editar','Actualizar estado y protocolo de retorno'),(23,'rpe','ver','Ver registros de RPE'),(24,'rpe','registrar','Registrar RPE post-sesión'),(25,'eventos','ver','Ver calendario de eventos'),(26,'eventos','crear','Crear eventos'),(27,'eventos','editar','Editar eventos existentes'),(28,'carga','ver','Ver métricas de carga ACWR/TSS'),(29,'rankings','ver','Consultar rankings'),(30,'reportes','generar','Generar reportes PDF'),(31,'periodizacion','ver','Ver planes de periodización'),(32,'periodizacion','generar','Generar plan ATR automático'),(33,'periodizacion','editar','Editar plan de periodización'),(34,'entrenadores','ver','Ver información de entrenadores'),(35,'seguridad','usuarios','Gestión de usuarios del sistema'),(36,'seguridad','roles','Gestión de roles y permisos'),(37,'seguridad','bitacora','Consulta de bitácora del sistema'),(38,'seguridad','backup','Realizar backups y restauraciones'),(39,'seguridad','login','Iniciar sesión'),(40,'seguridad','logout','Cerrar sesión'),(41,'metas','ver','Ver metas competitivas'),(42,'metas','gestionar','Crear/editar metas competitivas'),(43,'atletas','ver_propio','Atleta: ver su propio expediente'),(44,'atletas','rpe_propio','Atleta: registrar su propio RPE'),(45,'representantes','ver_hijos','Representante: ver expedientes de sus atletas'),(46,'representantes','asistencia_hijos','Representante: ver asistencia de sus atletas'),(47,'representantes','rpe_hijos','Representante: ver RPE de sus atletas'),(49,'seguridad','mantenimiento','Acceso al módulo de mantenimiento y respaldos'),(50,'testFisico','ver','Ver modulo de tests fisicos'),(51,'testFisico','registrar','Registrar, editar y eliminar tests fisicos'),(52,'lesiones','eliminar','Eliminar lesion (baja logica)'),(53,'lesiones','eliminardb','Eliminar lesion de la base de datos'),(54,'lesiones','reactivar','Reactivar lesion eliminada'),(55,'rpe','eliminar','Anular registro RPE'),(56,'carga_bienestar','registrar','Registrar carga de bienestar'),(57,'carga_bienestar','anular','Anular carga de bienestar'),(58,'normalizacion','ver','Ver modulo de normalizacion'),(59,'normalizacion','registrar','Registrar normalizacion'),(60,'normalizacion','editar','Editar normalizacion'),(61,'normalizacion','eliminar','Eliminar normalizacion'),(62,'normalizacion','anular','Anular normalizacion'),(63,'normalizacion_tiempos','registrar','Registrar normalizacion de tiempos'),(64,'observacionesTecnicas','ver','Ver modulo de observaciones tecnicas'),(65,'observacionesTecnicas','registrar','Registrar observacion tecnica'),(66,'representantes','ver','Ver modulo de representantes'),(67,'representantes','gestionar','Gestionar representantes'),(68,'temporadas','ver','Ver modulo de temporadas'),(69,'temporadas','registrar','Registrar temporada'),(70,'mi_perfil','ver','Ver y editar perfil propio'),(71,'sesiones','eliminar','Eliminar sesion de entrenamiento'),(72,'atletas','gestionar','Gestionar atletas (crear, editar, eliminar)'),(73,'marcas','editar','puede editar'),(74,'marcas','eliminar','se puede eliminar'),(75,'marcas','restaurar','se puede restaurar'),(76,'grupo','ver','grupo de entrenamientos'),(77,'antropometria','eliminar','Permite anular (soft delete) mediciones antropométricas'),(78,'antropometria','reactivar','Permite reactivar mediciones antropométricas'),(79,'carga_bienestar','eliminar','eliminar carga de bienestar'),(80,'carga_bienestar','reactivar','reactivar carga de bienestar'),(81,'carga_bienestar','eliminardb','eliminar permanente carga de bienestar'),(82,'rpe','reactivar','reactivar registro RPE'),(83,'rpe','editar','editar RPE post-sesión'),(84,'reportes','ver','visualizar moduo reportes PDF'),(85,'sistemaExperto','ver','Acceso al modulo del Sistema Experto (recomendaciones y alertas)'),(87,'antropometria','eliminardb','Eliminado Fisico de medicion antropometrica'),(88,'asignacion','ver','ver asignacion de carriles'),(89,'asignacion','gestionar','crear, editar y eliminar'),(90,'horario','ver','ver horarios'),(91,'horario','gestionar','crear, editar, eliminar');
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
) ENGINE=InnoDB AUTO_INCREMENT=598 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (528,1,1),(524,1,2),(525,1,3),(526,1,4),(523,1,5),(522,1,6),(533,1,7),(532,1,8),(588,1,9),(585,1,10),(586,1,11),(584,1,12),(536,1,13),(534,1,14),(535,1,15),(552,1,16),(550,1,17),(521,1,18),(520,1,19),(547,1,20),(546,1,21),(542,1,22),(576,1,23),(575,1,24),(540,1,25),(538,1,26),(539,1,27),(529,1,28),(567,1,29),(568,1,30),(566,1,31),(565,1,32),(564,1,33),(537,1,34),(583,1,35),(582,1,36),(578,1,37),(577,1,38),(579,1,39),(580,1,40),(554,1,41),(553,1,42),(581,1,49),(593,1,50),(592,1,51),(543,1,52),(544,1,53),(545,1,54),(573,1,55),(531,1,56),(530,1,57),(560,1,58),(559,1,59),(557,1,60),(558,1,61),(556,1,62),(561,1,63),(563,1,64),(562,1,65),(571,1,66),(570,1,67),(591,1,68),(590,1,69),(555,1,70),(587,1,71),(527,1,72),(548,1,73),(549,1,74),(551,1,75),(541,1,76),(517,1,77),(519,1,78),(574,1,82),(572,1,83),(569,1,84),(589,1,85),(518,1,87),(594,1,88),(595,1,89),(596,1,90),(597,1,91),(70,2,1),(68,2,2),(69,2,3),(67,2,5),(66,2,6),(72,2,7),(95,2,9),(93,2,10),(94,2,11),(92,2,12),(75,2,13),(73,2,14),(74,2,15),(84,2,16),(83,2,17),(65,2,18),(64,2,19),(82,2,20),(81,2,21),(91,2,23),(90,2,24),(78,2,25),(76,2,26),(77,2,27),(71,2,28),(88,2,29),(89,2,30),(87,2,31),(86,2,32),(85,2,33),(80,2,34),(79,2,41),(153,2,43),(251,2,50),(252,2,51),(257,2,52),(261,2,53),(265,2,54),(269,2,55),(273,2,56),(275,2,57),(277,2,58),(279,2,59),(281,2,60),(283,2,61),(285,2,62),(287,2,63),(289,2,64),(291,2,65),(295,2,68),(297,2,69),(299,2,70),(304,2,71),(255,2,72),(310,2,73),(309,2,74),(308,2,75),(439,2,85),(129,3,1),(314,3,16),(128,3,18),(127,3,19),(132,3,20),(131,3,21),(130,3,22),(156,3,23),(157,3,24),(154,3,43),(155,3,44),(158,3,47),(253,3,50),(258,3,52),(262,3,53),(266,3,54),(270,3,55),(300,3,70),(318,3,77),(320,3,78),(348,4,1),(352,4,16),(346,4,18),(354,4,23),(351,4,25),(350,4,28),(349,4,43),(347,4,44),(353,4,70),(356,5,1),(358,5,16),(355,5,18),(357,5,25),(362,5,45),(360,5,46),(361,5,47),(359,5,70);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_roles`
--

LOCK TABLES `usuario_roles` WRITE;
/*!40000 ALTER TABLE `usuario_roles` DISABLE KEYS */;
INSERT INTO `usuario_roles` VALUES (1,1,1,'2026-09-10 01:42:46');
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
  `preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Guarda ajustes de UI del usuario como {"tema":"dark", "crono":"live"}' CHECK (json_valid(`preferencias`)),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'V-00000001','Administrador','Sistema','admin@sgrd.com','$2y$10$JRFTfiOWeKn30Nn1DpiniO.kt90cMi4Mm.mUUk.BBjFy8bZnvr/gq',1,NULL,0,NULL,NULL,'2025-01-15 10:00:00','2026-09-10 01:57:02','{\"crono_mode\": \"manual\"}');
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
-- Final view structure for view `vista_representantes_atletas`
--

/*!50001 DROP VIEW IF EXISTS `vista_representantes_atletas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_representantes_atletas` AS select `r`.`id_representante` AS `id_representante`,`r`.`cedula` AS `cedula`,`r`.`nombres` AS `nombres`,`r`.`apellidos` AS `apellidos`,`r`.`telefono_principal` AS `telefono_principal`,`r`.`parentesco` AS `parentesco`,`r`.`estado` AS `estado`,group_concat(concat(`a`.`id_atleta`,':',`a`.`nombres`,' ',`a`.`apellidos`) separator '|') AS `atletas_vinculados` from ((`representantes` `r` left join `atleta_representante` `ar` on(`r`.`id_representante` = `ar`.`id_representante`)) left join `atletas` `a` on(`ar`.`id_atleta` = `a`.`id_atleta`)) group by `r`.`id_representante` */;
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

-- Dump completed on 2026-09-10  2:01:13
