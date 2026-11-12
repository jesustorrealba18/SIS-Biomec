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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas_biologicas`
--

LOCK TABLES `alertas_biologicas` WRITE;
/*!40000 ALTER TABLE `alertas_biologicas` DISABLE KEYS */;
INSERT INTO `alertas_biologicas` VALUES (1,16,'ANTROPOMETRÍA',2,'PÉRDIDA_PESO_SEVERA',3,'Pérdida de peso crítica detectada: -10.45% respecto a la medición anterior.','2026-10-12 18:52:09',0,0),(2,16,'LESIONES',1,'DOLOR_AGUDO',3,'Atención requerida: Lesión (Sobreuso) en Cervical con nivel de molestia 9/10.','2026-10-12 19:20:25',0,0),(3,16,'LESIONES',1,'ALTA_MEDICA',1,'Evolución favorable: Atleta dado de alta médica.','2026-10-12 19:22:15',0,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacion_carril`
--

LOCK TABLES `asignacion_carril` WRITE;
/*!40000 ALTER TABLE `asignacion_carril` DISABLE KEYS */;
INSERT INTO `asignacion_carril` VALUES (1,1,1,2,'2027-10-13','2026-10-12','2026-11-16','2026-10-12 12:31:18',1,'completada'),(2,1,1,2,NULL,'2026-10-12','2026-10-12',NULL,1,'activa');
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (2,9,1,NULL,2,'2026-09-15','2026-09-15 07:06:00','Manual','Presente','No aplica'),(3,17,1,NULL,2,'2026-09-15','2026-09-15 07:07:00','Manual','Presente','No aplica'),(4,8,1,NULL,2,'2026-09-15','2026-09-15 07:08:00','Manual','Presente','No aplica'),(5,13,1,NULL,2,'2026-09-15','2026-09-15 07:10:00','Manual','Presente','No aplica'),(6,15,1,NULL,2,'2026-09-15','2026-09-15 07:11:00','Manual','Presente','No aplica'),(7,14,1,NULL,2,'2026-09-15','2026-09-15 07:12:00','Manual','Ausente','No aplica'),(8,5,2,NULL,2,'2026-09-16','2026-09-16 07:14:32','Manual','Presente','No aplica'),(9,9,2,NULL,2,'2026-09-16','2026-09-16 07:15:47','Manual','Presente','No aplica'),(10,17,2,NULL,2,'2026-09-16','2026-09-16 07:17:08','Manual','Presente','No aplica'),(11,8,2,NULL,2,'2026-09-16','2026-09-16 07:18:51','Manual','Presente','No aplica'),(12,13,2,NULL,2,'2026-09-16','2026-09-16 07:20:19','Manual','Presente','No aplica'),(13,15,2,NULL,2,'2026-09-16','2026-09-16 07:21:42','Manual','Presente','No aplica'),(14,14,2,NULL,2,'2026-09-16','2026-09-16 07:22:58','Manual','Ausente','No aplica'),(15,16,3,NULL,3,'2026-10-12','2026-10-12 15:49:22','Manual','Presente','No aplica'),(16,1,3,NULL,3,'2026-10-12','2026-10-12 15:49:28','Manual','Justificado','otitis ssdjsifjs'),(18,13,4,NULL,3,'2026-10-16','2026-10-16 20:59:18','Manual','Presente','No aplica'),(19,14,4,NULL,3,'2026-10-16','2026-10-16 20:59:22','Manual','Presente','No aplica'),(20,15,4,NULL,3,'2026-10-16','2026-10-16 20:59:27','Manual','Presente','No aplica'),(21,9,4,NULL,3,'2026-10-16','2026-10-16 20:59:34','Manual','Presente','No aplica'),(22,8,4,NULL,3,'2026-10-16','2026-10-16 20:59:38','Manual','Presente','No aplica'),(23,5,4,NULL,3,'2026-10-16','2026-10-16 20:59:42','Manual','Ausente','No aplica'),(24,17,4,NULL,3,'2026-10-16','2026-10-16 20:59:47','Manual','Ausente','No aplica'),(25,16,5,NULL,3,'2026-10-17','2026-10-17 22:27:01','Manual','Presente','No aplica'),(26,1,5,NULL,3,'2026-10-17','2026-10-17 22:27:10','Manual','Ausente','No aplica'),(27,13,6,NULL,3,'2026-10-17','2026-10-17 22:29:21','Manual','Presente','No aplica'),(28,9,6,NULL,3,'2026-10-17','2026-10-17 22:29:29','Manual','Presente','No aplica'),(29,8,6,NULL,3,'2026-10-17','2026-10-17 22:29:34','Manual','Presente','No aplica'),(30,5,6,NULL,3,'2026-10-17','2026-10-17 22:29:40','Manual','Ausente','No aplica'),(31,17,6,NULL,3,'2026-10-17','2026-10-17 22:29:46','Manual','Ausente','No aplica'),(32,15,6,NULL,3,'2026-10-17','2026-10-17 22:29:50','Manual','Ausente','No aplica'),(33,14,6,NULL,3,'2026-10-17','2026-10-17 22:29:57','Manual','Ausente','No aplica'),(34,13,7,NULL,2,'2026-10-18','2026-10-18 00:01:58','Manual','Presente','No aplica'),(35,15,7,NULL,2,'2026-10-18','2026-10-18 00:02:03','Manual','Presente','No aplica'),(36,17,7,NULL,2,'2026-10-18','2026-10-18 00:02:08','Manual','Ausente','No aplica'),(37,8,7,NULL,2,'2026-10-18','2026-10-18 00:02:12','Manual','Ausente','No aplica'),(38,9,7,NULL,2,'2026-10-18','2026-10-18 00:02:16','Manual','Ausente','No aplica'),(39,5,7,NULL,2,'2026-10-18','2026-10-18 00:02:19','Manual','Ausente','No aplica'),(40,14,7,NULL,2,'2026-10-18','2026-10-18 00:02:23','Manual','Ausente','No aplica'),(41,13,8,NULL,2,'2026-11-09','2026-11-09 22:55:11','Manual','Presente','No aplica'),(42,14,8,NULL,2,'2026-11-09','2026-11-09 22:55:17','Manual','Presente','No aplica'),(43,15,8,NULL,2,'2026-11-09','2026-11-09 22:55:21','Manual','Presente','No aplica'),(44,17,8,NULL,2,'2026-11-09','2026-11-09 22:55:27','Manual','Ausente','No aplica'),(45,8,8,NULL,2,'2026-11-09','2026-11-09 22:55:31','Manual','Ausente','No aplica'),(46,9,8,NULL,2,'2026-11-09','2026-11-09 22:55:36','Manual','Presente','No aplica'),(47,5,8,NULL,2,'2026-11-09','2026-11-09 22:55:40','Manual','Ausente','No aplica'),(61,13,9,NULL,1,'2026-11-11','2026-11-11 00:20:52','QR','Presente','Validación Biométrica QR'),(62,5,9,NULL,1,'2026-11-11','2026-11-11 00:22:22','Manual','Ausente','No aplica');
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
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_datos_medicos`
--

LOCK TABLES `atleta_datos_medicos` WRITE;
/*!40000 ALTER TABLE `atleta_datos_medicos` DISABLE KEYS */;
INSERT INTO `atleta_datos_medicos` VALUES (2,1,'A+','NA','NA','NA','02514413261','Otro','NA','NA','NA'),(3,2,'A+','NA','NA','NA','0412000000','Otro','NA','NA','NA'),(4,3,'O+','Picadura de Hormigas','NA','NA','04121315674','Otro','NA','NA','NA'),(5,4,'O+','NA','NA','NA','0412000000','Otro','NA','NA','NA'),(6,5,'O+','NA','Dolores de Cabeza (Migrañas)','NA','0412000000','Otro','NA','NA','NA'),(7,6,'B-','Polvo,Pelusa, olores fuertes','Rinitis, operador por fractura en brazo izquierdo','NA','02512629484','Otro','NA','NA','NA'),(8,7,'A+','NA','NA','NA','02514429136','Otro','NA','NA','NA'),(9,8,'O+','NA','operado de apendicitis','NA','04120551206','Otro','NA','NA','NA'),(10,9,'O+','NA','NA','NA','0412000000','Otro','NA','NA','NA'),(11,10,'O+','NA','NA','NA','0412000000','Otro','NA','NA','NA'),(12,11,'O+','NA','Operacion de Hidrocele','NA','04167503563','Otro','NA','NA','NA'),(13,12,'A+','NA','Operado de los Ojos','NA','0412000000','Otro','NA','NA','NA'),(14,13,'A-','NA','NA','NA','04145723023','Otro','NA','NA','NA'),(15,14,'A+','NA','gripe, operacion de peritonitis por apendicitis aguda en 2017','NA','02512672025','Otro','NA','NA','NA'),(16,15,'O+','NA','NA','NA','04226391969','Otro','NA','NA','NA'),(17,16,'O+','NA','NA','NA','0412000000','Otro','NA','NA','NA'),(18,17,'A+','NA','Asmatico','Annye Josefina Ures Galviz','04168509115','Madre','NA','FED-45063','NA'),(19,18,'A+','ninguna','ninguna','jose pirolo','04121273248','Padre','Miranda','feb-123','maximo'),(20,19,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(21,20,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(22,21,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(23,22,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(24,23,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(25,24,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(26,25,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(27,26,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(28,27,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(29,28,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(30,29,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(31,30,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(32,31,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(33,32,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(34,33,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(35,34,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(36,35,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(37,36,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(38,37,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(39,38,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(40,39,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(41,40,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(42,41,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(43,42,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(44,43,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(45,44,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(46,45,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(47,46,'A+','Ninguna','Ninguna','Pedrito Perez','04120560145','Padre','Miranda','FEB-00998','Maximo'),(48,47,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(49,48,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(50,49,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(51,50,'A+','holaaaaa','asmaaa','josegres','04120560145','Padre','miranda','feb737373','maximo'),(52,51,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(53,52,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(54,53,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(55,54,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(56,55,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(57,56,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(58,57,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(59,58,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(60,59,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(61,60,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(62,61,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(63,62,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(64,63,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(65,64,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(66,65,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(67,66,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(68,67,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(69,68,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(70,69,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(71,70,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(72,71,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(73,72,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(74,73,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(75,74,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(76,75,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(77,76,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(78,77,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(79,78,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(80,79,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(81,80,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(82,81,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(83,82,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(84,83,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(85,84,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(86,85,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(87,86,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(88,87,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(89,88,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(90,89,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(91,90,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(92,91,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(93,92,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(94,93,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(95,94,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(96,95,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(97,96,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(98,97,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(99,98,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(100,99,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(101,100,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(102,101,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(103,102,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(104,103,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(105,104,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(106,105,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(107,106,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(108,107,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(109,108,'A+','fddsfdsf','dsfsdf','sdfsdfsdf','04121273248','Padre','mirandoa','freb51515','askdsadas'),(110,109,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(111,110,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(112,111,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(113,112,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(114,113,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(115,114,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(116,115,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo'),(117,116,'A+','Ninguna','Ninguna','Jose Pirolo','04121273248','Padre','Miranda','feb-123','Maximo');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_representante`
--

LOCK TABLES `atleta_representante` WRITE;
/*!40000 ALTER TABLE `atleta_representante` DISABLE KEYS */;
INSERT INTO `atleta_representante` VALUES (1,17,1,0,NULL,0,NULL,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atletas`
--

LOCK TABLES `atletas` WRITE;
/*!40000 ALTER TABLE `atletas` DISABLE KEYS */;
INSERT INTO `atletas` VALUES (1,'V-34703536','Enmanuel Andres','Rodriguez Alvarado','2011-04-30','M','Brisas del obelisco carrera 3 entre calles 3 y 3A #3-56','04261510247','crrsmusic@gmail.com',NULL,'2026-01-01','Activo','e73805379676a225d64e61ad0332194c',4,NULL,'2026-09-12 08:15:22','2026-09-17 03:53:06'),(2,'V-36768581','Celida Coromoto','Bastidas Querales','2015-02-08','F','Calle 6 entre av 4 y 5 La mata, Cabudare','04120496052','celidaquerales25@gmail.com',NULL,'2026-01-01','Activo','a48f93e34c53a8af087f927b27da9c82',2,NULL,'2026-09-12 08:23:47','2026-09-17 03:53:06'),(3,'V-33123327','Dagne Victoria','Carvajal Linarez','2008-10-21','F','Calle 32 entre carreras 16 y 17 resid magnolia Plaza apt B8C','04167520560','dagnecarvajal21@gmail.com',NULL,'2026-01-01','Activo','8e677c9c30cb70985fe878a73d97aa83',5,NULL,'2026-09-12 08:41:15','2026-09-17 03:53:06'),(4,'V-32575361','Yadier David','Lemus Valera','2007-03-31','M','La Carucieña Urbanismo los Encantos','04125517357','yadierlemus255@gmail.com',NULL,'2026-01-01','Activo','707ff21653bb52d3feb087c33d285d5c',6,NULL,'2026-09-12 09:02:33','2026-09-17 03:53:06'),(5,'V-35177308','Andrea Luisymar','Gonzalez Parra','2012-08-28','F','Colinas del Sur, sector 2, av principal con los caiqueties-Cabudare','04165777348','Jennifer_diegoa@gmail.com',NULL,'2026-01-01','Activo','f6cc40160d535d16206c3913c23890e9',3,NULL,'2026-09-12 09:18:56','2026-09-17 03:53:06'),(6,'V-33184513','ORLANDO JOSE','FALCON ORELLANA','2009-03-10','M','URB COPACOA DEL ESTE, CALLE 5A CASA #5-02','04145725331','falconorlando11@gmail.com',NULL,'2026-01-01','Activo','e7e25989db0a67720ac5cb649ec896ae',5,NULL,'2026-09-12 09:35:11','2026-09-17 03:53:06'),(7,'V-34156151','CAMILA ANTONELLA','YEPEZ MENDOZA','2010-09-10','F','CALLE 56 ENTRE CRR 13B Y 13C CASA #56-17','04245041261','camiyepez1009@gmail.com',NULL,'2026-01-01','Activo','9fd53e67deeaf1c5203cbb27ebdc7cca',4,NULL,'2026-09-12 09:52:08','2026-09-17 03:53:06'),(8,'V-34367518','CAMILA SOFIA','PEREZ ROJAS','2012-02-23','F','AV LOS HORCONES, ENTRE CALLES 5 Y 6 CASA #15-15 SECTOR PUEBLO NUEVO BQTO, EDO LARA','04124533601','camidiannyperez2012@gmail.com',NULL,'2026-01-01','Activo','3999f9bc3dde6a75820dc0d30edc35fd',3,NULL,'2026-09-12 10:14:29','2026-09-17 03:53:06'),(9,'V-34472124','ARIANNY VALENTINA','BALVUENA FRANCO','2012-04-23','F','12 DE OCTUBRE CALLE 1-A, CON CARRERAS 2 Y 3','04245750894','maryoryt333@gmail.com',NULL,'2026-01-01','Activo','1cdeab88836f0f31f31a65ec3e5bab96',3,NULL,'2026-09-12 10:31:44','2026-09-17 03:53:06'),(10,'V-32911933','JAVIER ANDRES','ALDAZORO GUTIERREZ','2008-12-17','M','CALLE LIDICE CON CALLE JOSE LUIS ANDRADE CASA #15-83','04125009001','javieraldazoro@gmail.com',NULL,'2026-01-01','Activo','ac610a6f836d9250b32a192e7e6772f0',5,NULL,'2026-09-12 10:48:03','2026-09-17 03:53:06'),(11,'V-36272674','SANTIAGO ASIER','Carvajal Linarez','2013-05-29','M','CALLE 32 ENTRE CARRERAS 16 Y 17 RESD MAGNOLIA PLAZA APT B8C','04121315674','arelyslinarez@gmail.com',NULL,'2026-01-01','Activo','48867ce6362a00d4c63c05fa8859a81f',3,NULL,'2026-09-12 11:05:17','2026-09-17 03:53:06'),(12,'V-21244212','OSWAL FERNANDO','YZARRA GONZALEZ','1990-02-02','M','RANCHO 5A EL UJANO','04245761537','oswalyzarra@gmail.com',NULL,'2026-01-01','Activo','1562460ff9069d78502a769ead31bb81',6,NULL,'2026-09-12 11:22:55','2026-09-17 03:53:06'),(13,'V-34396688','JOSE DANIEL','GONZALEZ FANEITE','2012-01-04','M','URBANISMO ALI PRIMERA','04245240231','josegonzalezdf.12@gmail.com',NULL,'2026-01-01','Activo','cf224e1c831e89f027980f234b99d280',3,4,'2026-09-12 11:41:12','2026-10-16 20:45:46'),(14,'V-34322324','LUIS DANIEL','PERALTA UNDA','2011-02-24','M','URBANIZACION LA NUEVA PAZ CALLE 2A CON CARRERA 1 DETRAS DEL HOSP ROTARIO','04120969345','luispera0808@gmail.com',NULL,'2026-01-01','Activo','382e6bca4ac58999caf90e451ffbce07',4,NULL,'2026-09-12 14:08:36','2026-09-17 03:53:06'),(15,'V-34703144','FABIAN ANDRES','GHERBASSI BARRIOS','2012-03-15','M','CALLE 62B ENTRE AV FUERZAS ARMADAS Y SAN VICENTE #9-69','04145301723','robertogherbassi@gmail.com',NULL,'2026-01-01','Activo','1814e290024ac93089959f9d9101eab3',3,NULL,'2026-09-12 14:25:49','2026-09-17 03:53:06'),(16,'V-33484519','JESUS MANUEL','SUAREZ DURAN','2010-07-23','M','CALLE 40 ENTRE CARRERA 30 Y 31 #128-A','04165057375','na@sgrd.com',NULL,'2026-01-01','Activo','821cb11f6626942b831de03b9bbb26e9',4,NULL,'2026-09-12 14:43:02','2026-09-22 12:20:57'),(17,'V-33650288','EDUARDO JOSE','SARMIENTO URES','2010-09-20','M','URB SIMON BOLIVAR , CALLE 8 ENTRE 3 Y 4, BARQTO','04164627402','sarmientoeduardo843@gmail.com',NULL,'2026-01-01','Activo','27a917645094348092c6b7867eeb1258',4,NULL,'2026-09-12 15:02:27','2026-09-22 12:21:30'),(18,'V-00000012','Prueba','Codegen','2014-05-27','M','Prueba','04120560145','aaaa@sgrd.com',NULL,'2026-10-27','Activo','763fd96b39c02a6d137015ec6ce38ceb',2,NULL,'2026-10-27 20:21:15',NULL),(19,'V-00000013','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','aaaab@sgrd.com',NULL,'2026-10-27','Activo','ecce4a020b1812d3948a4de0786beacd',2,NULL,'2026-10-27 21:41:12',NULL),(20,'V-00000014','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','aaaac@sgrd.com',NULL,'2026-10-27','Activo','4b0ab9609985f1a20a1a798caceb0eb6',2,NULL,'2026-10-27 21:47:32',NULL),(21,'V-00000015','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','aaaad@sgrd.com',NULL,'2026-10-27','Activo','e6d3c8c9eb7d73889aba9d2128c14ead',2,NULL,'2026-10-27 21:51:00',NULL),(22,'V-00000016','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','aaaae@sgrd.com',NULL,'2026-10-27','Activo','e773ac19e080f0f01e4b7fd294c87dce',2,NULL,'2026-10-27 22:05:15',NULL),(23,'V-53471015','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793153471015@sgrd.com',NULL,'2026-10-27','Activo','df3b7a206af1bc47cf6753f55325cbec',2,NULL,'2026-10-27 22:11:12',NULL),(24,'V-53472903','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793153472903@sgrd.com',NULL,'2026-10-27','Activo','97217fec738ae3970e8969fd65c4c362',2,NULL,'2026-10-27 22:11:14',NULL),(25,'V-54347383','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793154347383@sgrd.com',NULL,'2026-10-27','Activo','2f2f6c3a167971b98134ae715b9d07d4',2,NULL,'2026-10-27 22:25:48',NULL),(26,'V-54349534','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793154349534@sgrd.com',NULL,'2026-10-27','Activo','15f1e3bf233f56cdaf43dcfb26b50cce',2,NULL,'2026-10-27 22:25:51',NULL),(27,'V-55952786','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793155952786@sgrd.com',NULL,'2026-10-27','Activo','6bed41efdfc4526fc3f0bac8dc336502',2,NULL,'2026-10-27 22:52:34',NULL),(28,'V-55954416','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793155954416@sgrd.com',NULL,'2026-10-27','Activo','487b6190ef2ef0f635bb3308d5d3589f',2,NULL,'2026-10-27 22:52:36',NULL),(29,'V-55957403','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793155957403@sgrd.com',NULL,'2026-10-27','Activo','26a733eb771af1252df64310acb2c24e',2,NULL,'2026-10-27 22:52:38',NULL),(30,'V-56143032','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793156143032@sgrd.com',NULL,'2026-10-27','Activo','a697274fb6b6101f6a28270cc350e138',2,NULL,'2026-10-27 22:55:44',NULL),(31,'V-56144136','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793156144136@sgrd.com',NULL,'2026-10-27','Activo','560eb60aea5468a7f8b725d8e3acda5a',2,NULL,'2026-10-27 22:55:46',NULL),(32,'V-56147075','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793156147075@sgrd.com',NULL,'2026-10-27','Activo','a7a998728f81d3034f02769942ab591d',2,NULL,'2026-10-27 22:55:48',NULL),(33,'V-58541489','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158541489@sgrd.com',NULL,'2026-10-27','Activo','0c716da4a2dffffd1778e72c2853ae07',2,NULL,'2026-10-27 23:35:44',NULL),(34,'V-58541459','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158541459@sgrd.com',NULL,'2026-10-27','Activo','8f5031ea66a25ef7636be1d57b1580a8',2,NULL,'2026-10-27 23:35:46',NULL),(35,'V-58544850','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158544850@sgrd.com',NULL,'2026-10-27','Activo','a4bd47a3c16578b435cf4553ca891351',2,NULL,'2026-10-27 23:35:48',NULL),(36,'V-58546231','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158546231@sgrd.com',NULL,'2026-10-27','Activo','b8b3b6f8130d369528f7d409ec3bc31a',2,NULL,'2026-10-27 23:35:51',NULL),(37,'V-58547576','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158547576@sgrd.com',NULL,'2026-10-27','Activo','9a08bb03f0bf6c9e9c5e0acde443443a',2,NULL,'2026-10-27 23:35:51',NULL),(38,'V-58616662','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158616662@sgrd.com',NULL,'2026-10-27','Activo','23d245a055ab7896e71935806da33006',2,NULL,'2026-10-27 23:36:58',NULL),(39,'V-58620466','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793158620466@sgrd.com',NULL,'2026-10-27','Activo','9c67ff34179b6c450c1057bf4f27ba2c',2,NULL,'2026-10-27 23:37:03',NULL),(40,'V-59161868','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793159161868@sgrd.com',NULL,'2026-10-27','Activo','09641c6a472646f44a8dac28261a708a',2,NULL,'2026-10-27 23:46:02',NULL),(41,'V-59169421','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793159169421@sgrd.com',NULL,'2026-10-27','Activo','44971b1e7a158c92d7ebf6c8f3056de7',2,NULL,'2026-10-27 23:46:11',NULL),(42,'V-59341162','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793159341162@sgrd.com',NULL,'2026-10-27','Activo','0d772743895b2cda72374d9b3320e36e',2,NULL,'2026-10-27 23:49:02',NULL),(43,'V-59348675','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793159348675@sgrd.com',NULL,'2026-10-27','Activo','a93382e10b6d3a2297df817005ad1270',2,NULL,'2026-10-27 23:49:10',NULL),(44,'V-63424991','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793163424991@sgrd.com',NULL,'2026-10-27','Activo','331f3b9eec427d132eca284886455d8e',2,NULL,'2026-10-28 00:57:06',NULL),(45,'V-03060951','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793203060951@sgrd.com',NULL,'2026-10-27','Activo','316b99470ab4739295df5edead91be76',2,NULL,'2026-10-28 11:57:41',NULL),(46,'V-12345678','SujetoPrueba','Prueba','2014-02-04','M','Carrera prueba','04121273248','aaaffff@sgrd.com',NULL,'2026-02-25','Activo','b66bb04115fe9622828d6f79f0687917',2,NULL,'2026-10-29 15:50:03',NULL),(47,'V-14915039','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793314915039@sgrd.com',NULL,'2026-10-27','Activo','3496189b61c729657ed3a27d847eb7b4',2,NULL,'2026-10-29 19:01:55',NULL),(48,'V-16685195','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793316685195@sgrd.com',NULL,'2026-10-27','Activo','8149c69c2d50b34868402fc88923ca70',2,NULL,'2026-10-29 19:31:26',NULL),(49,'V-18608905','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793318608905@sgrd.com',NULL,'2026-10-27','Activo','e61b546df2dec8dcea8a1a1024db0f51',2,NULL,'2026-10-29 20:03:31',NULL),(50,'V-55555555','jose','pirolo','2000-06-06','M','pruebitaaaa','04121273248','joseaaaa@gmail.com',NULL,'2026-06-07','Activo','ccb019ee00996b95342cb2310319c9d2',6,NULL,'2026-10-29 20:37:41',NULL),(51,'V-23491240','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793323491240@sgrd.com',NULL,'2026-10-27','Activo','43c298a820b62454681826e9315423e6',2,NULL,'2026-10-29 21:24:52',NULL),(52,'V-23494526','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793323494526@sgrd.com',NULL,'2026-10-27','Activo','18f76f7a583bae9ef99140aa51ee6a50',2,NULL,'2026-10-29 21:24:56',NULL),(53,'V-23497897','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793323497897@sgrd.com',NULL,'2026-10-27','Activo','c17f9ce3ccbd24ca327a0f97d220bb68',2,NULL,'2026-10-29 21:24:59',NULL),(54,'V-23988228','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793323988228@sgrd.com',NULL,'2026-10-27','Activo','4bb4654f399d3902a7800da4aa538c47',2,NULL,'2026-10-29 21:33:09',NULL),(55,'V-24060313','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324060313@sgrd.com',NULL,'2026-10-27','Activo','9b2404c5fc0f8d82c4d85b9885299fa2',2,NULL,'2026-10-29 21:34:21',NULL),(56,'V-24061920','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324061920@sgrd.com',NULL,'2026-10-27','Activo','b44a5f19c773c0b0649ef2588d5e1277',2,NULL,'2026-10-29 21:34:23',NULL),(57,'V-24111594','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324111594@sgrd.com',NULL,'2026-10-27','Activo','adebac0760cf9e7bc82d8ba778fdcb4e',2,NULL,'2026-10-29 21:35:12',NULL),(58,'V-24113166','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324113166@sgrd.com',NULL,'2026-10-27','Activo','d0e892b0c65a83a65858e516022b2dc4',2,NULL,'2026-10-29 21:35:14',NULL),(59,'V-24178571','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324178571@sgrd.com',NULL,'2026-10-27','Activo','7d978e0344deec2ab40d90ef3f31ec17',2,NULL,'2026-10-29 21:36:19',NULL),(60,'V-24185721','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324185721@sgrd.com',NULL,'2026-10-27','Activo','f1af7116d28d84eadf67923bcc4e382d',2,NULL,'2026-10-29 21:36:27',NULL),(61,'V-24305096','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324305096@sgrd.com',NULL,'2026-10-27','Activo','7b507cbfa8100c3f064b240021315d25',2,NULL,'2026-10-29 21:38:25',NULL),(62,'V-24316588','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324316588@sgrd.com',NULL,'2026-10-27','Activo','76c90caa9fd0e6ae897f8038f7c34de4',2,NULL,'2026-10-29 21:38:38',NULL),(63,'V-24539042','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324539042@sgrd.com',NULL,'2026-10-27','Activo','7e29ed47256fbdd1362df7f4c1024d27',2,NULL,'2026-10-29 21:42:21',NULL),(64,'V-24988936','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793324988936@sgrd.com',NULL,'2026-10-27','Activo','06002d04118c0edf937a7020c705a160',2,NULL,'2026-10-29 21:49:51',NULL),(65,'V-25534574','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325534574@sgrd.com',NULL,'2026-10-27','Activo','0ced03320f3fd60fa313bd03dde058b9',2,NULL,'2026-10-29 21:58:57',NULL),(66,'V-25538894','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325538894@sgrd.com',NULL,'2026-10-27','Activo','1c6b1ebefb6141c43444e7f7caaefcc1',2,NULL,'2026-10-29 21:59:03',NULL),(67,'V-25541150','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325541150@sgrd.com',NULL,'2026-10-27','Activo','abb7324f7c37731dba0a7131e0bd6e8a',2,NULL,'2026-10-29 21:59:04',NULL),(68,'V-25614978','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325614978@sgrd.com',NULL,'2026-10-27','Activo','01adc7e565547761e5edb2c79ab5d778',2,NULL,'2026-10-29 22:00:16',NULL),(69,'V-25625281','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325625281@sgrd.com',NULL,'2026-10-27','Activo','2aaef4dc5fc22a076b0026a71e637a12',2,NULL,'2026-10-29 22:00:27',NULL),(70,'V-25633911','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793325633911@sgrd.com',NULL,'2026-10-27','Activo','8d2f826369c26f50ff396d5535775700',2,NULL,'2026-10-29 22:00:36',NULL),(71,'V-34702466','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793334702466@sgrd.com',NULL,'2026-10-27','Activo','ee045a333067849ac106967bbcd49279',2,NULL,'2026-10-30 00:31:43',NULL),(72,'V-34712817','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793334712817@sgrd.com',NULL,'2026-10-27','Activo','47e765e8bff3a5787bd4f94a01354256',2,NULL,'2026-10-30 00:31:54',NULL),(73,'V-34720845','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793334720845@sgrd.com',NULL,'2026-10-27','Activo','d4d3cd2ccd075fd50b39f935e33dc39c',2,NULL,'2026-10-30 00:32:03',NULL),(74,'V-35143151','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793335143151@sgrd.com',NULL,'2026-10-27','Activo','5bf1634ed41169b6879e2eafd2c4a29b',2,NULL,'2026-10-30 00:39:18',NULL),(75,'V-35187911','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793335187911@sgrd.com',NULL,'2026-10-27','Activo','31608d0b5a718b2fe983e3e0d3bdaaca',2,NULL,'2026-10-30 00:39:54',NULL),(76,'V-40454860','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793340454860@sgrd.com',NULL,'2026-10-27','Activo','8b8f9ba66385c1e0e179afe44ba8fcd1',2,NULL,'2026-10-30 02:07:36',NULL),(77,'V-40464611','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793340464611@sgrd.com',NULL,'2026-10-27','Activo','f63b09ecfbcf3749e5f522b1a20076e1',2,NULL,'2026-10-30 02:07:46',NULL),(78,'V-40473053','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793340473053@sgrd.com',NULL,'2026-10-27','Activo','e7a3853d1f1870657b409f980c1c6c0c',2,NULL,'2026-10-30 02:07:55',NULL),(79,'V-43868697','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343868697@sgrd.com',NULL,'2026-10-27','Activo','4ed0da29ebfd9ce4bc9920bd3a9fc594',2,NULL,'2026-10-30 03:04:34',NULL),(80,'V-43879897','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343879897@sgrd.com',NULL,'2026-10-27','Activo','12cf17d1825dfdd7cc9871f728334cd0',2,NULL,'2026-10-30 03:04:43',NULL),(81,'V-43877890','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343877890@sgrd.com',NULL,'2026-10-27','Activo','8f16022f2bdb4dbc49df91354d470f95',2,NULL,'2026-10-30 03:04:44',NULL),(82,'V-43932478','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343932478@sgrd.com',NULL,'2026-10-27','Activo','6ef156f5fe03fa74b9f794b525a7dca1',2,NULL,'2026-10-30 03:05:35',NULL),(83,'V-43947616','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343947616@sgrd.com',NULL,'2026-10-27','Activo','89b9abf85b1947d4f1ea9385fd57598a',2,NULL,'2026-10-30 03:05:50',NULL),(84,'V-43960709','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793343960709@sgrd.com',NULL,'2026-10-27','Activo','93dfa3f6631fd8f489d6c6ef8099c943',2,NULL,'2026-10-30 03:06:05',NULL),(85,'V-44147567','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344147567@sgrd.com',NULL,'2026-10-27','Activo','1349d826ab4e878ce06f678d4bca9de6',2,NULL,'2026-10-30 03:09:09',NULL),(86,'V-44151116','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344151116@sgrd.com',NULL,'2026-10-27','Activo','ceb3e30503b16a8a6f981ddff2daa285',2,NULL,'2026-10-30 03:09:13',NULL),(87,'V-44586053','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344586053@sgrd.com',NULL,'2026-10-27','Activo','420c3a58385c39ef50143537e51be0f6',2,NULL,'2026-10-30 03:16:31',NULL),(88,'V-44597825','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344597825@sgrd.com',NULL,'2026-10-27','Activo','9152882cb5f1ad7e219f387f3b2ff0bc',2,NULL,'2026-10-30 03:16:42',NULL),(89,'V-44596237','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344596237@sgrd.com',NULL,'2026-10-27','Activo','b0a7f8a7fead3b09d689c88af27accb1',2,NULL,'2026-10-30 03:16:44',NULL),(90,'V-44645354','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344645354@sgrd.com',NULL,'2026-10-27','Activo','9cffdb4f8e00090c5fa87f8865e59f1f',2,NULL,'2026-10-30 03:17:27',NULL),(91,'V-44660635','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344660635@sgrd.com',NULL,'2026-10-27','Activo','cd60a4f5ae5066efbe41ff5aa3605424',2,NULL,'2026-10-30 03:17:44',NULL),(92,'V-44675391','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344675391@sgrd.com',NULL,'2026-10-27','Activo','1a8b2a2a127b74663bd869ffcf5ab676',2,NULL,'2026-10-30 03:18:00',NULL),(93,'V-44845080','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344845080@sgrd.com',NULL,'2026-10-27','Activo','00152217d2894fefdb004a8c587f38aa',2,NULL,'2026-10-30 03:20:46',NULL),(94,'V-44853952','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344853952@sgrd.com',NULL,'2026-10-27','Activo','f331c2ddc910564971b0dc5873c24216',2,NULL,'2026-10-30 03:20:56',NULL),(95,'V-44923506','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793344923506@sgrd.com',NULL,'2026-10-27','Activo','64a69d5975d12c37c9ce50ac0d8cd9ca',2,NULL,'2026-10-30 03:22:05',NULL),(96,'V-45231477','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345231477@sgrd.com',NULL,'2026-10-27','Activo','77782da5cd9e8a368057e839633a6888',2,NULL,'2026-10-30 03:27:13',NULL),(97,'V-45565181','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345565181@sgrd.com',NULL,'2026-10-27','Activo','3150500fbda20d0d0cfdd1da204de83d',2,NULL,'2026-10-30 03:32:50',NULL),(98,'V-45571120','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345571120@sgrd.com',NULL,'2026-10-27','Activo','2d8b3a38b3d464b2c9ec05d85ad8de5f',2,NULL,'2026-10-30 03:32:56',NULL),(99,'V-45575272','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345575272@sgrd.com',NULL,'2026-10-27','Activo','a7d78394dd0a7bf0219f6aad2b5ee634',2,NULL,'2026-10-30 03:33:00',NULL),(100,'V-45641277','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345641277@sgrd.com',NULL,'2026-10-27','Activo','b1a12dcbb61fe49a150028e336a47399',2,NULL,'2026-10-30 03:34:03',NULL),(101,'V-45661199','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345661199@sgrd.com',NULL,'2026-10-27','Activo','aeb76221f8189be9c57428e424bb289f',2,NULL,'2026-10-30 03:34:25',NULL),(102,'V-45675948','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345675948@sgrd.com',NULL,'2026-10-27','Activo','0a9ed7aebfd7b4e2e8521542db49de54',2,NULL,'2026-10-30 03:34:41',NULL),(103,'V-45709434','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345709434@sgrd.com',NULL,'2026-10-27','Activo','ab16cd853dc729b6e3c745c4445c3919',2,NULL,'2026-10-30 03:35:12',NULL),(104,'V-45723336','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345723336@sgrd.com',NULL,'2026-10-27','Activo','76bd817f07803068548cca45f38a51db',2,NULL,'2026-10-30 03:35:27',NULL),(105,'V-45855152','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345855152@sgrd.com',NULL,'2026-10-27','Activo','0f5c927d8fa34ae9f7d526c5be063bfb',2,NULL,'2026-10-30 03:37:36',NULL),(106,'V-45863487','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345863487@sgrd.com',NULL,'2026-10-27','Activo','c75300871fb994131a715ad78bd4ff69',2,NULL,'2026-10-30 03:37:45',NULL),(107,'V-45921470','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793345921470@sgrd.com',NULL,'2026-10-27','Activo','2c37877a223074c5246d2217b33c0eb1',2,NULL,'2026-10-30 03:38:43',NULL),(108,'V-25838383','osajoasd','asdasdsadas','2000-02-02','M','sdfsdfsdfs','04121273248','jjjjj@gmail.com',NULL,'2026-10-26','Activo','be1c9113f1d4712f1406ca6b8b6bbe5e',6,NULL,'2026-10-30 08:41:00',NULL),(109,'V-65226018','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365226018@sgrd.com',NULL,'2026-10-27','Activo','113dcece36bfeb2539487c3539a35220',2,NULL,'2026-10-30 09:00:29',NULL),(110,'V-65227867','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365227867@sgrd.com',NULL,'2026-10-27','Activo','57608b5ee77914e0b3480251fb399e01',2,NULL,'2026-10-30 09:00:32',NULL),(111,'V-65232687','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365232687@sgrd.com',NULL,'2026-10-27','Activo','a6a3fddf637ba78b7ca8241f7be6ec7c',2,NULL,'2026-10-30 09:00:36',NULL),(112,'V-65265633','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365265633@sgrd.com',NULL,'2026-10-27','Activo','88b71de07b903495169657d1ef8ad467',2,NULL,'2026-10-30 09:01:06',NULL),(113,'V-65279796','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365279796@sgrd.com',NULL,'2026-10-27','Activo','2fd6cc1a4bf507409ba8410138d35f4f',2,NULL,'2026-10-30 09:01:21',NULL),(114,'V-65290738','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365290738@sgrd.com',NULL,'2026-10-27','Activo','aa6e7a8aa18d5e14e93f818138557351',2,NULL,'2026-10-30 09:01:33',NULL),(115,'V-65337386','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793365337386@sgrd.com',NULL,'2026-10-27','Activo','c57b382dd30a73dda84d955601e176d9',2,NULL,'2026-10-30 09:02:18',NULL),(116,'V-02473566','PruebaDos','Codegen','2014-05-27','M','Dirección de Prueba 123','04120560145','atleta_1793902473566@sgrd.com',NULL,'2026-10-27','Activo','649dfcba35b27ac27b084ff8f1ced525',2,NULL,'2026-11-05 14:14:35',NULL);
/*!40000 ALTER TABLE `atletas` ENABLE KEYS */;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_asignar_categoria_insert
BEFORE INSERT ON atletas
FOR EACH ROW
BEGIN
    DECLARE v_edad INT;
    DECLARE v_id_categoria INT;

    
    SET v_edad = YEAR(CURDATE()) - YEAR(NEW.fecha_nacimiento);

    SELECT id_categoria INTO v_id_categoria
    FROM categorias_feveda
    WHERE v_edad BETWEEN edad_minima AND edad_maxima
      AND activa = 1
    LIMIT 1;

    
    IF v_id_categoria IS NULL THEN
        IF v_edad < 8 THEN
            SET v_id_categoria = 1; 
        ELSE
            SET v_id_categoria = 6; 
        END IF;
    END IF;

    SET NEW.id_categoria = v_id_categoria;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_asignar_categoria_update
BEFORE UPDATE ON atletas
FOR EACH ROW
BEGIN
    DECLARE v_edad INT;
    DECLARE v_id_categoria INT;

    
    IF NEW.fecha_nacimiento != OLD.fecha_nacimiento THEN
        
        SET v_edad = YEAR(CURDATE()) - YEAR(NEW.fecha_nacimiento);

        
        SELECT id_categoria INTO v_id_categoria
        FROM categorias_feveda
        WHERE v_edad BETWEEN edad_minima AND edad_maxima
          AND activa = 1
        LIMIT 1;

        
        IF v_id_categoria IS NULL THEN
            IF v_edad < 8 THEN
                SET v_id_categoria = 1; 
            ELSE
                SET v_id_categoria = 6; 
            END IF;
        END IF;

        SET NEW.id_categoria = v_id_categoria;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloques_horarios`
--

LOCK TABLES `bloques_horarios` WRITE;
/*!40000 ALTER TABLE `bloques_horarios` DISABLE KEYS */;
INSERT INTO `bloques_horarios` VALUES (1,'Lunes','12:35:00','13:36:00'),(3,'Martes','11:55:00','14:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drills`
--

LOCK TABLES `drills` WRITE;
/*!40000 ALTER TABLE `drills` DISABLE KEYS */;
INSERT INTO `drills` VALUES (2,'Patadas Croll','Libre','Tecnico','mejorar ','Pruebassss','Pruebas',4,'Basico','Aletas',1,1,1,'2026-10-12 13:01:00');
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
  `curriculum_pdf` varchar(255) DEFAULT NULL,
  `curriculum_nombre_original` varchar(255) DEFAULT NULL,
  `foto` varchar(100) NOT NULL DEFAULT '',
  `id_usuario` int(3) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_entrenador`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador`
--

LOCK TABLES `entrenador` WRITE;
/*!40000 ALTER TABLE `entrenador` DISABLE KEYS */;
INSERT INTO `entrenador` VALUES (1,'V-16137930','Pastor Alberto','Mogollon Rivero','1982-07-24','M','profpastorm07@gmail.com','04245969144','Barrio la Paz vereda 12A, carrera 3 y 4',NULL,NULL,'',2,'Inactivo'),(2,'V-08591799','JOSE','Gregorio','1965-01-05','M','jose.pirolo@gmail.com','04120560145','Carrera 5 Entre Calles 6 Y 7 Tamaca',NULL,NULL,'',3,'Activo');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento_inscripcion`
--

LOCK TABLES `evento_inscripcion` WRITE;
/*!40000 ALTER TABLE `evento_inscripcion` DISABLE KEYS */;
INSERT INTO `evento_inscripcion` VALUES (1,1,12,'2026-09-17 09:59:05'),(2,1,5,'2026-09-30 23:54:39'),(3,2,17,'2026-10-12 14:34:28'),(4,2,15,'2026-10-12 14:34:28');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,'REGIONAL','2026-09-09','2026-09-30','COMPLEJO PISCINAS BOLIVARIANAS','Control','B','FEVEDA','En Progreso','NINGUNO','2026-09-17 09:57:28'),(2,'Miranda','2026-10-04','2026-10-12','Maximo','Regional','A','Feveda','Inscrito','sadasdsadas','2026-10-12 14:32:23');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_periodizacion`
--

LOCK TABLES `fases_periodizacion` WRITE;
/*!40000 ALTER TABLE `fases_periodizacion` DISABLE KEYS */;
INSERT INTO `fases_periodizacion` VALUES (8,1,'Acumulacion',1,3,'2026-10-14','2026-11-03',75.00,'Z1-Z3','#10b981'),(9,1,'Transmutacion',4,4,'2026-11-04','2026-11-10',50.00,'Z3-Z4','#f59e0b'),(10,1,'Realizacion',5,5,'2026-11-11','2026-11-17',30.00,'Z4-Z5','#ef4444');
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_atleta`
--

LOCK TABLES `grupo_atleta` WRITE;
/*!40000 ALTER TABLE `grupo_atleta` DISABLE KEYS */;
INSERT INTO `grupo_atleta` VALUES (9,1,9,'2026-09-12'),(10,1,15,'2026-09-12'),(11,1,13,'2026-09-12'),(12,1,5,'2026-09-12'),(13,1,14,'2026-09-12'),(14,1,8,'2026-09-12'),(15,1,17,'2026-09-12'),(16,2,1,'2026-10-12'),(17,2,16,'2026-10-12');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_entrenamiento`
--

LOCK TABLES `grupos_entrenamiento` WRITE;
/*!40000 ALTER TABLE `grupos_entrenamiento` DISABLE KEYS */;
INSERT INTO `grupos_entrenamiento` VALUES (1,'Juveniles','',NULL,1,1),(2,'Aguas Profunda','Pruebass',NULL,2,1);
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
  `zona_anatomica` enum('Hombro','Rodilla','Espalda','Codo','Tobillo','Cervical','Lumbar','Muslo','Gemelo','Pie','Mano/Muñeca','Oido/Ojo/Cabeza','Respiratorio','Gastrointestinal','General','Otra') NOT NULL,
  `lado` enum('Izquierdo','Derecho','Bilateral') DEFAULT NULL,
  `tipo` enum('Sobreuso','Aguda','Recidiva','Enfermedad') NOT NULL,
  `nivel_molestia` int(11) NOT NULL,
  `gravedad_clinica` enum('Leve','Moderada','Grave') DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_estimada_recup` date DEFAULT NULL,
  `fecha_alta_real` date DEFAULT NULL,
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
INSERT INTO `lesiones` VALUES (1,16,'Cervical','Bilateral','Sobreuso',9,'Grave','djslkjdlajdlkasjdkasjldkjasd','ewrwerwerwe','2026-10-05','2026-10-16','2026-10-16','Recuperada','Dr Barrueta','werwerwerwerwerewr','2026-10-12 15:20:25','2026-10-21 19:33:46',1,NULL),(2,15,'Hombro','Izquierdo','Aguda',4,'Moderada','holasdssads','asdasdsadas','2026-10-10','2026-10-15',NULL,'EnRehabilitacion','asdsadsada','asdasdasdas','2026-10-12 15:34:29','2026-10-21 23:17:13',1,NULL),(3,17,'Oido/Ojo/Cabeza',NULL,'Enfermedad',5,'Moderada','Pruebassssskk','Pruebasssss','2026-10-21','2026-11-26',NULL,'EnRehabilitacion','Pruebasssss','Pruebasssss','2026-10-21 23:13:59','2026-10-21 23:19:18',1,NULL),(4,15,'Rodilla','Derecho','Aguda',5,'Moderada','Esguince leve de prueba de validacion',NULL,'2026-10-20',NULL,NULL,'Activa','Dr Test',NULL,'2026-10-22 00:28:12',NULL,1,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `macrociclos`
--

LOCK TABLES `macrociclos` WRITE;
/*!40000 ALTER TABLE `macrociclos` DISABLE KEYS */;
INSERT INTO `macrociclos` VALUES (1,2,2,'Nacional 2026','2026-10-14','2026-11-18',NULL,'Finalizado');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,13,8,NULL,'Libre',100,'50m',58.00,NULL,'Control',1,'2026-11-10',NULL,'2026-11-10 20:21:01','Activo',NULL),(2,13,8,NULL,'Libre',100,'50m',59.00,NULL,'Control',0,'2026-11-10',NULL,'2026-11-10 20:27:22','Activo',NULL),(3,13,8,NULL,'Libre',100,'50m',60.00,NULL,'Control',0,'2026-11-10',NULL,'2026-11-10 20:38:31','Activo',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_splits`
--

LOCK TABLES `marcas_splits` WRITE;
/*!40000 ALTER TABLE `marcas_splits` DISABLE KEYS */;
INSERT INTO `marcas_splits` VALUES (5,2,1,25,15.00,NULL),(6,2,2,50,15.00,NULL),(7,2,3,75,15.00,NULL),(8,2,4,100,15.00,NULL),(9,3,1,25,15.00,NULL),(10,3,2,50,15.00,NULL),(11,3,3,75,15.00,NULL),(12,3,4,100,15.00,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mediciones_antropometricas`
--

LOCK TABLES `mediciones_antropometricas` WRITE;
/*!40000 ALTER TABLE `mediciones_antropometricas` DISABLE KEYS */;
INSERT INTO `mediciones_antropometricas` VALUES (1,16,'2026-05-12',67.00,175.0,120.0,75.0,21.9,17.3,'Administrador Sistema',NULL,NULL),(2,16,'2026-10-12',60.00,190.0,100.0,60.0,16.6,3.0,'Administrador Sistema','2026-10-12 18:57:15','gdfgdfgd');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesociclos`
--

LOCK TABLES `mesociclos` WRITE;
/*!40000 ALTER TABLE `mesociclos` DISABLE KEYS */;
INSERT INTO `mesociclos` VALUES (8,1,8,'Acumulacion',1,3,'Desarrollar base aerobica y tecnica',80000),(9,1,9,'Transmutacion',4,4,'Convertir volumen en velocidad especifica',55000),(10,1,10,'Taper / Realizacion',5,5,'Afinar rendimiento para competencia',30000);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metas_competitivas`
--

LOCK TABLES `metas_competitivas` WRITE;
/*!40000 ALTER TABLE `metas_competitivas` DISABLE KEYS */;
INSERT INTO `metas_competitivas` VALUES (6,1,12,'Libre',50,30.00,NULL,NULL),(7,1,12,'Espalda',100,60.00,NULL,NULL),(8,1,5,'Libre',100,60.00,4.23,999.99);
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microciclos`
--

LOCK TABLES `microciclos` WRITE;
/*!40000 ALTER TABLE `microciclos` DISABLE KEYS */;
INSERT INTO `microciclos` VALUES (20,8,1,'2026-10-14','2026-10-20',20000),(21,8,2,'2026-10-21','2026-10-27',20000),(22,8,3,'2026-10-28','2026-11-03',20000),(23,9,4,'2026-11-04','2026-11-10',15000),(24,10,5,'2026-11-11','2026-11-17',8000);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recomendaciones_carga`
--

LOCK TABLES `recomendaciones_carga` WRITE;
/*!40000 ALTER TABLE `recomendaciones_carga` DISABLE KEYS */;
INSERT INTO `recomendaciones_carga` VALUES (1,13,'2026-10-17 00:18:31','SOBRECARGA','Carga subjetiva semanal muy alta (810 sRPE). Se recomienda reducir volumen en un 20% y priorizar sesiones regenerativas.',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_rpe`
--

LOCK TABLES `registro_rpe` WRITE;
/*!40000 ALTER TABLE `registro_rpe` DISABLE KEYS */;
INSERT INTO `registro_rpe` VALUES (1,13,NULL,'2026-10-16',1,0.0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 00:18:31',NULL,NULL),(2,13,1,'2026-10-16',8,7.0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 09:17:01',NULL,NULL),(3,9,4,'2026-10-16',4,5.0,3,5,5,'555',50,90,360,'2026-10-17 09:35:33',NULL,NULL),(4,13,4,'2026-10-16',8,7.0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 10:08:03',NULL,NULL),(5,13,7,'2026-10-18',5,6.0,7,6,7,'dsfsdfsfsd',50,90,450,'2026-10-18 00:05:21',NULL,NULL),(10,13,NULL,'2026-11-05',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 03:52:04',NULL,NULL),(11,13,NULL,'2026-11-05',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 03:55:49',NULL,NULL),(12,13,NULL,'2026-11-05',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:22:05',NULL,NULL),(13,13,NULL,'2026-11-05',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 10:52:51',NULL,NULL),(14,13,NULL,'2026-11-05',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 11:49:15',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registros_test`
--

LOCK TABLES `registros_test` WRITE;
/*!40000 ALTER TABLE `registros_test` DISABLE KEYS */;
INSERT INTO `registros_test` VALUES (1,16,NULL,2,'2026-10-12',1,'Hola','Parcial','2026-10-12 15:43:16');
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
  `fecha_nacimiento` date DEFAULT NULL,
  `parentesco` enum('Padre','Madre','Tutor','Otro') NOT NULL,
  `telefono_principal` varchar(20) DEFAULT NULL,
  `telefono_secundario` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `foto` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_representante`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `representantes`
--

LOCK TABLES `representantes` WRITE;
/*!40000 ALTER TABLE `representantes` DISABLE KEYS */;
INSERT INTO `representantes` VALUES (1,'V-15816665','Annye Josefina','Ures Galvis','2008-09-17','Madre','04168509115','02510000000','annyeu2010@gmail.com','urb simon bolivar calle 8 entre 3 y 4 ',NULL,NULL,'2026-09-12 15:18:44','Activo');
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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series_sesion`
--

LOCK TABLES `series_sesion` WRITE;
/*!40000 ALTER TABLE `series_sesion` DISABLE KEYS */;
INSERT INTO `series_sesion` VALUES (1,1,NULL,1,'Calentamiento','100 L a R2',10,100,10,'Z2',NULL),(2,1,NULL,2,'Calentamiento','25 L y estilo a máxima',4,25,60,'Z5',NULL),(3,1,NULL,3,'Calentamiento','25 L y estilo a máxima',4,25,60,'Z5',NULL),(4,1,NULL,4,'Calentamiento','25 L y estilo a máxima',4,25,60,'Z5',NULL),(5,1,NULL,5,'Principal','400 L pala',1,400,0,'Z3',NULL),(6,1,NULL,6,'Principal','400 CI por 50 sin nada',1,400,0,'Z3',NULL),(7,1,NULL,7,'Principal','400: 50 L + 50 E chap',1,400,0,'Z3',NULL),(8,1,NULL,8,'Principal','400 L sin nada',1,400,0,'Z3',NULL),(9,1,NULL,9,'Principal','300: 75 pata estilo + 75 Tc + 150 libre',3,300,20,'Z3',NULL),(10,1,NULL,10,'Principal','200 patada L',1,200,0,'Z2',NULL),(11,1,NULL,11,'Principal','200: 50 E + 50 L',1,200,0,'Z2',NULL),(12,1,NULL,12,'Principal','200: 100 L + 100 E',1,200,0,'Z2',NULL),(13,1,NULL,13,'VueltaCalma','50 (4 L + 1 Est)',5,50,0,'Z2','0:50'),(14,1,NULL,14,'VueltaCalma','50 (3 L + 2 Est)',5,50,0,'Z2','0:50'),(15,1,NULL,15,'VueltaCalma','50 (2 L + 3 Est)',5,50,0,'Z2','0:50'),(16,1,NULL,16,'VueltaCalma','50 (1 L + 4 Est)',5,50,0,'Z2','0:50'),(17,1,NULL,17,'VueltaCalma','50 libre',5,50,0,'Z1','0:50'),(18,1,NULL,18,'VueltaCalma','50 estilo',5,50,0,'Z1','1:05'),(19,2,NULL,1,'Calentamiento','100 RII chapaleta',10,100,10,'Z2',NULL),(20,2,NULL,2,'Principal','200 RII PALA CHAP',5,200,10,'Z3',NULL),(21,2,NULL,3,'Principal','400 RII PALET',4,400,10,'Z3',NULL),(22,2,NULL,4,'Principal','300 RII SIN NADA',3,300,10,'Z3',NULL),(23,2,NULL,5,'VueltaCalma','50 (4 L + 1 Est)',5,50,0,'Z2','0:50'),(24,2,NULL,6,'VueltaCalma','50 (3 L + 2 Est)',5,50,0,'Z2','0:50'),(25,2,NULL,7,'VueltaCalma','50 (2 L + 3 Est)',5,50,0,'Z2','0:50'),(26,2,NULL,8,'VueltaCalma','50 (1 L + 4 Est)',5,50,0,'Z2','0:50'),(27,3,2,1,'Principal',NULL,1,50,15,'Z1','1:10'),(28,4,2,1,'Principal',NULL,1,50,15,'Z1','1:30'),(29,5,2,1,'Principal',NULL,1,50,15,'Z1','1:30'),(30,6,2,1,'Principal',NULL,1,50,15,'Z1','1:30'),(31,7,2,1,'Principal',NULL,1,50,15,'Z1','1:20'),(32,8,2,1,'Principal',NULL,1,50,15,'Z1','1:30'),(33,8,NULL,2,'VueltaCalma','Es Libre',1,50,15,'Z1','2:30'),(34,9,NULL,1,'Principal','Libre',1,50,15,'Z1','1:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
INSERT INTO `sesiones` VALUES (1,NULL,1,'2026-09-15','Resistencia',NULL,'10x100 L a R2 con 10\" + 3x(4x25 L y estilo a máxima a 1\')','30x50 combinaciones (4L1E)(3L2E)(2L3E)(1L4E)(5L)(5E). Libres a 50\", estilos a 1\'05\"',5900,5900,120,'Sesión martes grupo Juveniles','Completada',NULL,1,'2026-09-12 16:20:11','2026-10-16 20:52:04'),(2,NULL,1,'2026-09-16','Resistencia',NULL,'10x100 RII chapaleta + 5x200 RII PALA CHAP + 4x400 RII PALET + 3x300 RII SIN NADA. Todas las salidas con 10\" de pausa','20x50 RII (4L 1Est)(3L 2Est)(2L 3Est)(1L 4Est). Libres a 50\", estilos a 1\'05\"',5500,5500,120,'Sesión miércoles grupo Juveniles','Completada',2,1,'2026-09-15 20:00:00','2026-09-16 10:26:00'),(3,NULL,2,'2026-10-12','Tecnica',NULL,'213213','12312312',50,NULL,90,'saasdasdasd','Parcial',NULL,2,'2026-10-12 15:46:03','2026-10-12 15:48:46'),(4,NULL,1,'2026-10-16','Tecnica',NULL,'Prueba','Prueba',50,50,90,'Prueba','Completada',NULL,2,'2026-10-16 20:54:35','2026-10-16 21:00:28'),(5,NULL,2,'2026-10-17','Tecnica',NULL,'90','90',50,50,90,'90','Completada',NULL,2,'2026-10-17 22:20:33','2026-10-17 22:27:39'),(6,NULL,1,'2026-10-17','Tecnica',NULL,'55','555',50,50,90,'5555','Completada',NULL,2,'2026-10-17 22:28:18','2026-10-17 22:30:38'),(7,NULL,1,'2026-10-18','Tecnica',NULL,'90','90',50,50,90,'90','Completada',NULL,1,'2026-10-18 00:00:16','2026-10-18 00:03:50'),(8,NULL,1,'2026-11-10','Tecnica',NULL,'Prueba para registrar Marca','Prueba para registrar Marca',100,NULL,90,'Prueba para registrar Marca','Parcial',NULL,1,'2026-11-09 22:49:58','2026-11-10 20:20:40'),(9,NULL,1,'2026-11-11','Tecnica',NULL,'prueba','prueba',50,NULL,90,'hola','Planificada',NULL,1,'2026-11-10 23:41:56',NULL);
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
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temporadas`
--

LOCK TABLES `temporadas` WRITE;
/*!40000 ALTER TABLE `temporadas` DISABLE KEYS */;
INSERT INTO `temporadas` VALUES (2,'2026-2027','2026-01-01','2027-01-01',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests_personalizados`
--

LOCK TABLES `tests_personalizados` WRITE;
/*!40000 ALTER TABLE `tests_personalizados` DISABLE KEYS */;
INSERT INTO `tests_personalizados` VALUES (1,'algo','prueba de velocidad','velocidad','seg',10.00,50.00,1,3,'2026-07-05 15:37:43'),(2,'Prueba','Hola','Velocidad','Seg',12.00,17.00,1,1,'2026-10-12 15:42:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `valores_test`
--

LOCK TABLES `valores_test` WRITE;
/*!40000 ALTER TABLE `valores_test` DISABLE KEYS */;
INSERT INTO `valores_test` VALUES (1,1,1,0.03,'mmol/L'),(2,1,2,0.06,'bpm'),(3,1,3,0.09,'bpm'),(4,2,7,10.00,'reps'),(76,5,1,2.80,'mmol/L'),(77,5,2,165.00,'bpm'),(78,5,3,130.00,'bpm'),(79,6,4,8.00,'cm'),(80,6,5,12.00,'cm'),(81,6,6,4.00,'cm'),(82,7,7,25.00,'reps'),(83,8,1,3.50,'mmol/L'),(84,8,2,172.00,'bpm'),(85,8,3,140.00,'bpm'),(86,9,4,10.00,'cm'),(87,9,5,18.00,'cm'),(88,9,6,8.00,'cm'),(89,10,10,55.00,'cm'),(90,10,11,2100.00,'m'),(91,10,12,52.00,'ml/kg/min'),(92,11,7,18.00,'reps'),(93,12,1,3.20,'mmol/L'),(94,12,2,168.00,'bpm'),(95,12,3,135.00,'bpm'),(96,13,4,6.00,'cm'),(97,13,5,14.00,'cm'),(98,13,6,8.00,'cm'),(99,14,8,12.00,'reps'),(100,15,1,4.10,'mmol/L'),(101,15,2,175.00,'bpm'),(102,15,3,145.00,'bpm'),(103,16,14,4.20,'seg'),(104,17,1,3.80,'mmol/L'),(105,17,2,170.00,'bpm'),(106,17,3,138.00,'bpm'),(107,18,4,5.00,'cm'),(108,18,5,10.00,'cm'),(109,18,6,5.00,'cm'),(110,19,13,180.00,'seg'),(111,20,1,3.00,'mmol/L'),(112,20,2,168.00,'bpm'),(113,20,3,132.00,'bpm'),(114,21,14,4.80,'seg'),(115,22,9,230.00,'cm'),(116,23,10,62.00,'cm'),(117,24,1,4.50,'mmol/L'),(118,24,2,178.00,'bpm'),(119,24,3,148.00,'bpm'),(120,25,4,7.00,'cm'),(121,25,5,15.00,'cm'),(122,25,6,8.00,'cm'),(123,26,4,5.00,'cm'),(124,26,5,8.00,'cm'),(125,26,6,3.00,'cm'),(126,27,4,3.00,'cm'),(127,27,5,10.00,'cm'),(128,27,6,7.00,'cm'),(129,28,4,4.00,'cm'),(130,28,5,11.00,'cm'),(131,28,6,7.00,'cm'),(132,29,1,2.50,'mmol/L'),(133,29,2,160.00,'bpm'),(134,29,3,125.00,'bpm'),(135,30,1,3.00,'mmol/L'),(136,30,2,162.00,'bpm'),(137,30,3,128.00,'bpm'),(138,31,1,2.80,'mmol/L'),(139,31,2,158.00,'bpm'),(140,31,3,122.00,'bpm'),(141,32,1,3.20,'mmol/L'),(142,32,2,165.00,'bpm'),(143,32,3,130.00,'bpm'),(144,33,7,15.00,'reps'),(145,34,7,12.00,'reps'),(146,35,7,20.00,'reps'),(147,1,1,30.00,'20');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variables_test`
--

LOCK TABLES `variables_test` WRITE;
/*!40000 ALTER TABLE `variables_test` DISABLE KEYS */;
INSERT INTO `variables_test` VALUES (1,NULL,2,'Fuerza','20',1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=378 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (2,1,'Seguridad','UPDATE',1,'permisos rol',NULL,'82 permisos','::1',NULL,'2026-09-12 06:00:00'),(3,1,'Seguridad','UPDATE',4,'permisos rol',NULL,'9 permisos','::1',NULL,'2026-09-12 06:08:00'),(5,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 08:15:22'),(6,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 08:23:47'),(7,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 08:41:15'),(8,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 09:02:33'),(9,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 09:18:56'),(10,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 09:35:11'),(11,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 09:52:08'),(12,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 10:14:29'),(13,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 10:31:44'),(14,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 10:48:03'),(15,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 11:05:17'),(16,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 11:22:55'),(17,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 11:41:12'),(18,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 14:08:36'),(19,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 14:25:49'),(20,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 14:43:02'),(21,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-09-12 15:02:27'),(22,1,'Representantes','INSERT',NULL,'Nuevo Representante',NULL,'V-15816665 - Annye Josefina Ures Galvis','::1',NULL,'2026-09-12 15:18:44'),(23,1,'Entrenadores','INSERT',0,'entrenador',NULL,'Pastor Alberto Mogollon Rivero','::1',NULL,'2026-09-12 15:35:00'),(24,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-12_15-55-00.sql','::1',NULL,'2026-09-12 15:55:00'),(25,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','127.0.0.1',NULL,'2026-09-12 16:20:11'),(26,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-12_16-35-00.sql','::1',NULL,'2026-09-12 16:35:00'),(27,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-12_16-45-00.sql','::1',NULL,'2026-09-12 16:45:00'),(28,1,'Seguridad','CREATE',2,'usuario',NULL,'Pastor Alberto Mogollon Rivero','::1',NULL,'2026-09-15 06:00:00'),(29,1,'Seguridad','UPDATE',2,'usuario',NULL,'Pastor Alberto Mogollon Rivero','::1',NULL,'2026-09-15 06:05:00'),(30,2,'Modulo Sesiones','UPDATE',1,'estado','Planificada','Parcial','::1',NULL,'2026-09-15 07:00:00'),(31,2,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:05:00'),(32,2,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:06:00'),(33,2,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:07:00'),(34,2,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:08:00'),(35,2,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:10:00'),(36,2,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-15 07:11:00'),(37,2,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-09-15 07:12:00'),(38,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"79.85\"}','::1',NULL,'2026-09-15 09:15:00'),(39,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"8\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"72.45\"}','::1',NULL,'2026-09-15 09:18:22'),(40,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"9\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"35.30\"}','::1',NULL,'2026-09-15 09:22:14'),(41,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"73.10\"}','::1',NULL,'2026-09-15 09:26:00'),(42,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"15\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"200\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"149.20\"}','::1',NULL,'2026-09-15 09:29:45'),(43,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"17\",\"id_sesion\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"200\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"167.40\"}','::1',NULL,'2026-09-15 09:33:30'),(44,2,'Modulo Sesiones','UPDATE',1,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-09-15 09:45:00'),(45,2,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1 - 16/sep','127.0.0.1',NULL,'2026-09-15 20:00:00'),(46,2,'Modulo Sesiones','UPDATE',2,'estado','Planificada','Parcial','::1',NULL,'2026-09-16 07:08:00'),(47,2,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:14:32'),(48,2,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:15:47'),(49,2,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:17:08'),(50,2,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:18:51'),(51,2,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:20:19'),(52,2,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-09-16 07:21:42'),(53,2,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-09-16 07:22:58'),(54,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"79.20\"}','::1',NULL,'2026-09-16 09:52:14'),(55,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"8\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"71.85\"}','::1',NULL,'2026-09-16 09:56:03'),(56,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"9\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"35.10\"}','::1',NULL,'2026-09-16 10:00:38'),(57,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"72.55\"}','::1',NULL,'2026-09-16 10:04:52'),(58,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"15\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"200\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"148.30\"}','::1',NULL,'2026-09-16 10:08:21'),(59,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"17\",\"id_sesion\":\"2\",\"estilo\":\"Libre\",\"distancia_m\":\"200\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"166.20\"}','::1',NULL,'2026-09-16 10:12:47'),(60,2,'Modulo Sesiones','UPDATE',2,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-09-16 10:26:00'),(61,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-17_15-24-37.sql','::1',NULL,'2026-09-17 09:24:40'),(62,1,'Modulo Eventos','INSERT',NULL,'evento',NULL,'REGIONAL','::1',NULL,'2026-09-17 09:57:28'),(63,1,'Modulo Eventos','INSERT',1,'evento_inscripcion',NULL,'1 atletas','::1',NULL,'2026-09-17 09:59:05'),(64,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"50\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"3.96\",\"tiempo_reaccion_seg\":\"1.50\",\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-09-15\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"2.36\",\"50\":\"1.60\"},\"brazadas_por_largo\":null,\"id_marca\":\"13\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-17 10:11:33'),(65,1,'Módulo Antropometría','CREATE',0,'N/A','N/A','Nueva medición. Atleta ID: 16, Peso: 67 kg, Talla: 175 cm','::1',NULL,'2026-09-17 10:27:37'),(66,1,'Entrenadores','UPDATE',1,'entrenador','Activo','Inactivo','::1',NULL,'2026-09-17 10:39:37'),(67,1,'Marcas','DELETE',78787878,'estado','Activo','Inactivo (Motivo: jjjjjjj)','::1',NULL,'2026-09-17 10:41:53'),(68,1,'Marcas','DELETE',7,'estado','Activo','Inactivo (Motivo: jjjjj)','::1',NULL,'2026-09-17 10:42:08'),(69,1,'Marcas','DELETE',8,'estado','Activo','Inactivo (Motivo: hgghghg)','::1',NULL,'2026-09-17 10:42:46'),(70,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"16.46\",\"tiempo_reaccion_seg\":\"1.79\",\"virajes\":{\"50\":\"2.88\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"5.50\",\"100\":\"2.39\",\"50\":\"2.78\",\"75\":\"5.79\"},\"brazadas_por_largo\":null,\"id_marca\":\"14\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','192.168.230.68',NULL,'2026-09-17 11:11:50'),(71,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-17_17-24-47.sql','::1',NULL,'2026-09-17 11:24:48'),(72,1,'Entrenadores','INSERT',0,'entrenador',NULL,'JOSE Gregorio','::1',NULL,'2026-09-22 12:11:50'),(73,1,'Entrenadores','UPDATE',2,'entrenador','Activo','Inactivo','::1',NULL,'2026-09-22 12:12:37'),(74,1,'Entrenadores','UPDATE',2,'entrenador','Inactivo','Activo','::1',NULL,'2026-09-22 12:12:51'),(75,1,'Atleta','DELETE',17,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:19:37'),(76,1,'Atleta','DELETE',17,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:19:46'),(77,1,'Atleta','DELETE',17,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:19:56'),(78,1,'Atleta','DELETE',16,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:20:02'),(79,1,'Atleta','DELETE',16,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:20:09'),(80,1,'Atleta','UPDATE',16,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:20:57'),(81,1,'Atleta','UPDATE',17,NULL,NULL,NULL,'::1',NULL,'2026-09-22 12:21:30'),(82,1,'Modulo Temporadas','CREATE',NULL,'temporada',NULL,'2026-2027','::1',NULL,'2026-09-22 12:29:57'),(83,1,'Modulo Temporadas','UPDATE',1,'temporada',NULL,'2026-2027 p','::1',NULL,'2026-09-22 12:30:26'),(84,1,'Modulo Temporadas','UPDATE',1,'temporada',NULL,'2028-2029 p','::1',NULL,'2026-09-22 12:31:34'),(85,1,'Modulo Temporadas','CREATE',NULL,'temporada',NULL,'2026-2027','::1',NULL,'2026-09-22 12:32:10'),(86,1,'Modulo Temporadas','UPDATE',1,'activa',NULL,'Activada','::1',NULL,'2026-09-22 12:32:26'),(87,1,'Modulo Temporadas','DELETE',1,'temporada',NULL,'Eliminada','::1',NULL,'2026-09-22 12:32:37'),(88,1,'Modulo Temporadas','UPDATE',2,'activa',NULL,'Activada','::1',NULL,'2026-09-22 12:33:44'),(89,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-09-26_03-05-41.sql','::1',NULL,'2026-09-25 21:05:42'),(90,1,'Marcas','RESTORE',7,'estado','Inactivo','Activo','::1',NULL,'2026-09-29 19:45:39'),(91,1,'Marcas','RESTORE',8,'estado','Inactivo','Activo','::1',NULL,'2026-09-29 19:45:43'),(92,1,'Marcas','DELETE',14,'estado','Activo','Inactivo (Motivo: sdfsdfxvxcvxcv)','::1',NULL,'2026-09-29 19:46:58'),(93,1,'Marcas','DELETE',77777,'estado','Activo','Inactivo (Motivo: hkhjkhjkhj)','::1',NULL,'2026-09-29 19:57:30'),(94,1,'Marcas','DELETE',3435347,'estado','Activo','Inactivo (Motivo: ergergergerg)','::1',NULL,'2026-09-29 19:58:10'),(95,1,'Marcas','DELETE',7,'estado','Activo','Inactivo (Motivo: dfsgfdg)','::1',NULL,'2026-09-29 21:53:43'),(96,1,'Marcas','RESTORE',14,'estado','Inactivo','Activo','::1',NULL,'2026-09-29 22:03:28'),(97,1,'Marcas','DELETE',14,'estado','Activo','Inactivo (Motivo: sdfsdfsdf)','::1',NULL,'2026-09-29 22:05:47'),(98,1,'Marcas','RESTORE',7,'estado','Inactivo','Activo','::1',NULL,'2026-09-29 22:06:22'),(99,1,'Marcas','RESTORE',14,'estado','Inactivo','Activo','::1',NULL,'2026-09-30 14:28:45'),(100,1,'Marcas','DELETE',14,'estado','Activo','Inactivo (Motivo: sfsdfsdf)','::1',NULL,'2026-09-30 14:28:53'),(101,1,'Marcas','RESTORE',14,'estado','Inactivo','Activo','::1',NULL,'2026-09-30 14:29:01'),(102,1,'Marcas','DELETE',14,'estado','Activo','Inactivo (Motivo: falooo)','::1',NULL,'2026-09-30 17:13:18'),(103,1,'Marcas','RESTORE',14,'estado','Inactivo','Activo','::1',NULL,'2026-09-30 17:14:23'),(104,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"3.73\",\"tiempo_reaccion_seg\":\"0.65\",\"virajes\":{\"50\":\"0.53\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"1.28\",\"100\":\"0.85\",\"50\":\"0.53\",\"75\":\"1.07\"},\"brazadas_por_largo\":null,\"id_marca\":\"15\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 17:15:16'),(105,1,'Marcas','UPDATE',15,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"4.73\",\"tiempo_reaccion_seg\":\"00.65\",\"virajes\":{\"50\":\"00.53\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":\"prueba\",\"num_brazadas\":null,\"splits\":{\"25\":\"01.28\",\"50\":\"00.53\",\"75\":\"02.07\",\"100\":\"00.85\"},\"brazadas_por_largo\":\"13\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 17:25:38'),(106,1,'Marcas','UPDATE',15,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"16.73\",\"tiempo_reaccion_seg\":\"00.65\",\"virajes\":{\"50\":\"00.53\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":\"prueba\",\"num_brazadas\":null,\"splits\":{\"25\":\"05.28\",\"50\":\"03.53\",\"75\":\"04.07\",\"100\":\"03.85\"},\"brazadas_por_largo\":\"13\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 17:30:55'),(107,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"4.23\",\"tiempo_reaccion_seg\":\"0.71\",\"virajes\":{\"50\":\"0.66\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"1.31\",\"100\":\"1.12\",\"50\":\"0.48\",\"75\":\"1.32\"},\"brazadas_por_largo\":null,\"id_marca\":\"16\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 17:31:26'),(108,1,'Marcas','DELETE',15,'estado','Activo','Inactivo (Motivo: eliminar)','::1',NULL,'2026-09-30 17:32:01'),(109,1,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'REGIONAL','::1',NULL,'2026-09-30 17:32:49'),(110,1,'Marcas','UPDATE',16,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"5\",\"id_sesion\":\"2\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"4.23\",\"tiempo_reaccion_seg\":\"00.71\",\"virajes\":{\"50\":\"00.66\"},\"nivel_evento\":null,\"fecha\":\"2026-09-16\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"01.31\",\"50\":\"00.48\",\"75\":\"01.32\",\"100\":\"01.12\"},\"brazadas_por_largo\":\"16\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 17:34:28'),(111,1,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'REGIONAL','::1',NULL,'2026-09-30 17:38:08'),(112,1,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'REGIONAL','::1',NULL,'2026-09-30 17:38:21'),(113,1,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'REGIONAL','::1',NULL,'2026-09-30 17:46:09'),(114,1,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'REGIONAL','::1',NULL,'2026-09-30 19:23:15'),(115,1,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'1 metas','::1',NULL,'2026-09-30 23:07:36'),(116,1,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'1 metas','::1',NULL,'2026-09-30 23:42:13'),(117,1,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'1 metas','::1',NULL,'2026-09-30 23:44:02'),(118,1,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'2 metas','::1',NULL,'2026-09-30 23:46:01'),(119,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"12\",\"id_sesion\":null,\"id_evento\":\"1\",\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"7.91\",\"tiempo_reaccion_seg\":\"1.80\",\"virajes\":{\"50\":\"1.03\"},\"nivel_evento\":null,\"fecha\":\"2026-09-30\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"2.99\",\"100\":\"1.11\",\"50\":\"0.78\",\"75\":\"3.03\"},\"brazadas_por_largo\":\"23\",\"id_marca\":\"17\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-09-30 23:47:15'),(120,1,'Modulo Eventos','INSERT',1,'evento_inscripcion',NULL,'1 atletas','::1',NULL,'2026-09-30 23:54:39'),(121,1,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'3 metas','::1',NULL,'2026-09-30 23:55:29'),(122,1,'Módulo Antropometría','UPDATE',98798798,'deleted_at','NULL','Medición anulada. Motivo: sdfsdf','::1',NULL,'2026-10-01 12:13:04'),(123,1,'Modulo Sesiones','',1,'estado',NULL,'Cancelada','::1',NULL,'2026-10-12 13:30:03'),(124,1,'Modulo Sesiones','UPDATE',1,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-12 13:31:01'),(125,1,'Modulo Periodizacion','CREATE',NULL,'macrociclo',NULL,'Nacional 2026','::1',NULL,'2026-10-12 13:45:08'),(126,1,'Modulo Periodizacion','UPDATE',1,'plan_atr',NULL,'Generado: 9 semanas','::1',NULL,'2026-10-12 13:46:39'),(127,1,'Modulo Periodizacion','UPDATE',1,'plan_atr',NULL,'Generado: 9 semanas','::1',NULL,'2026-10-12 14:12:08'),(128,1,'Modulo Periodizacion','UPDATE',1,'macrociclo',NULL,'Nacional 2026','::1',NULL,'2026-10-12 14:15:33'),(129,1,'Modulo Periodizacion','UPDATE',1,'macrociclo',NULL,'Nacional 2026','::1',NULL,'2026-10-12 14:15:53'),(130,1,'Modulo Periodizacion','UPDATE',1,'plan_atr',NULL,'Generado: 5 semanas','::1',NULL,'2026-10-12 14:16:34'),(131,1,'Modulo Periodizacion','UPDATE',1,'estado',NULL,'En Progreso','::1',NULL,'2026-10-12 14:18:05'),(132,1,'Modulo Periodizacion','UPDATE',1,'estado',NULL,'Planificado','::1',NULL,'2026-10-12 14:18:15'),(133,1,'Modulo Periodizacion','UPDATE',1,'estado',NULL,'Finalizado','::1',NULL,'2026-10-12 14:18:19'),(134,1,'Modulo Periodizacion','UPDATE',1,'estado',NULL,'Planificado','::1',NULL,'2026-10-12 14:22:10'),(135,1,'Modulo Periodizacion','UPDATE',1,'estado',NULL,'Finalizado','::1',NULL,'2026-10-12 14:22:13'),(136,1,'Modulo Eventos','INSERT',NULL,'evento',NULL,'Miranda','::1',NULL,'2026-10-12 14:32:23'),(137,1,'Modulo Eventos','INSERT',2,'evento_inscripcion',NULL,'3 atletas','::1',NULL,'2026-10-12 14:34:28'),(138,1,'Modulo Eventos','DELETE',2,'evento_inscripcion','12',NULL,'::1',NULL,'2026-10-12 14:34:53'),(139,1,'Modulo Eventos','UPDATE',2,'datos evento',NULL,'Miranda','::1',NULL,'2026-10-12 14:38:14'),(140,1,'Modulo Eventos','UPDATE',2,'datos evento',NULL,'Miranda','::1',NULL,'2026-10-12 14:38:34'),(141,1,'Modulo Eventos','UPDATE',2,'datos evento',NULL,'Miranda','::1',NULL,'2026-10-12 14:42:14'),(142,1,'Módulo Antropometría','UPDATE',1,'deleted_at','NULL','Medición anulada. Motivo: dsfsdfsdfsd','::1',NULL,'2026-10-12 14:48:28'),(143,1,'Módulo Antropometría','UPDATE',1,'deleted_at','fecha anterior','Medición reactivada','::1',NULL,'2026-10-12 14:50:09'),(144,1,'Módulo Antropometría','UPDATE',1,'peso_kg, talla_cm, envergadura_cm, perimetro_abdominal_cm, porcentaje_grasa','Ver valores previos en auditoría','Medición actualizada. Justificación: Prueba. Nuevos datos: Peso 67.00 kg, Talla 175.0 cm','::1',NULL,'2026-10-12 14:50:42'),(145,1,'Módulo Antropometría','CREATE',0,'N/A','N/A','Nueva medición. Atleta ID: 16, Peso: 60.00 kg, Talla: 190.0 cm','::1',NULL,'2026-10-12 14:52:09'),(146,1,'Módulo Antropometría','UPDATE',2,'deleted_at','NULL','Medición anulada. Motivo: gdfgdfgd','::1',NULL,'2026-10-12 14:57:15'),(147,1,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"16\",\"fecha_inicio\":\"2026-10-05\",\"fecha_estimada_recup\":\"2026-10-16\",\"zona_anatomica\":\"Cervical\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"9\",\"diagnostico\":\"djslkjdlajdlkasjdkasjldkjasd\",\"tratamiento\":\"ewrwerwerwe\",\"profesional\":\"Dr Barrueta\",\"estado\":\"Activa\",\"observaciones\":\"werwerwerwerwerewr\"}','::1',NULL,'2026-10-12 15:20:25'),(148,1,'Lesiones','UPDATE',1,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-05\",\"fecha_estimada_recup\":\"2026-10-16\",\"zona_anatomica\":\"Cervical\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"9\",\"diagnostico\":\"djslkjdlajdlkasjdkasjldkjasd\",\"tratamiento\":\"ewrwerwerwe\",\"profesional\":\"Dr Barrueta\",\"estado\":\"EnRehabilitacion\",\"observaciones\":\"werwerwerwerwerewr\"}','::1',NULL,'2026-10-12 15:21:25'),(149,1,'Lesiones','UPDATE',1,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"1\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-05\",\"fecha_estimada_recup\":\"2026-10-16\",\"zona_anatomica\":\"Cervical\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"9\",\"diagnostico\":\"djslkjdlajdlkasjdkasjldkjasd\",\"tratamiento\":\"ewrwerwerwe\",\"profesional\":\"Dr Barrueta\",\"estado\":\"Recuperada\",\"observaciones\":\"werwerwerwerwerewr\"}','::1',NULL,'2026-10-12 15:22:15'),(150,1,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"15\",\"fecha_inicio\":\"2026-10-10\",\"fecha_estimada_recup\":\"2026-10-15\",\"zona_anatomica\":\"Hombro\",\"lado\":\"Izquierdo\",\"tipo\":\"Aguda\",\"nivel_molestia\":\"4\",\"diagnostico\":\"holasdssads\",\"tratamiento\":\"asdasdsadas\",\"profesional\":\"asdsadsada\",\"estado\":\"Activa\",\"observaciones\":\"asdasdasdas\"}','::1',NULL,'2026-10-12 15:34:29'),(151,1,'Tipos de Tests','CREATE',NULL,'tests_personalizados',NULL,'Test personalizado creado: Prueba','::1',NULL,'2026-10-12 15:42:14'),(152,1,'Tests Fisicos','CREATE',NULL,'Registro completo',NULL,'{\"id_registro_test\":\"\",\"id_atleta\":\"16\",\"id_tipo_test\":\"\",\"id_test_pers\":\"2\",\"fecha\":\"2026-10-12\",\"estado\":\"Parcial\",\"valores\":{\"1\":\"30\"},\"observaciones\":\"Hola\",\"id_usuario_toma\":1}','::1',NULL,'2026-10-12 15:43:16'),(153,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 2','::1',NULL,'2026-10-12 15:46:03'),(154,1,'Seguridad','CREATE',3,'usuario',NULL,'JOSE Gregorio','::1',NULL,'2026-10-12 15:47:38'),(155,1,'Seguridad','UPDATE',3,'usuario',NULL,'JOSE Gregorio','::1',NULL,'2026-10-12 15:47:55'),(156,3,'Modulo Sesiones','UPDATE',3,'estado','Planificada','Parcial','::1',NULL,'2026-10-12 15:48:46'),(157,3,'Asistencias','INSERT',16,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-12 15:49:22'),(158,3,'Asistencias','INSERT',1,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-12 15:49:28'),(159,3,'Asistencias','INSERT',1,'estado','','Ajuste manual a: Justificado','::1',NULL,'2026-10-12 15:51:17'),(160,3,'Marcas','DELETE',17,'estado','Activo','Inactivo (Motivo: fdsfsdfsd)','::1',NULL,'2026-10-12 16:06:17'),(161,3,'Marcas','RESTORE',17,'estado','Inactivo','Activo','::1',NULL,'2026-10-12 16:06:25'),(162,3,'Marcas','RESTORE',15,'estado','Inactivo','Activo','::1',NULL,'2026-10-12 16:06:28'),(163,1,'Seguridad','UPDATE',3,'usuario',NULL,'JOSE Gregorio','::1',NULL,'2026-10-12 16:13:55'),(164,1,'Seguridad','CREATE',4,'usuario',NULL,'Jose Daniel Prueba','::1',NULL,'2026-10-16 20:45:28'),(165,1,'Seguridad','UPDATE',4,'usuario',NULL,'Jose Daniel Prueba','::1',NULL,'2026-10-16 20:45:48'),(166,1,'Seguridad','UPDATE',4,'permisos rol',NULL,'10 permisos','::1',NULL,'2026-10-16 20:49:24'),(167,1,'Seguridad','UPDATE',1,'permisos rol',NULL,'81 permisos','::1',NULL,'2026-10-16 20:50:01'),(168,1,'Modulo Sesiones','UPDATE',1,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-16 20:52:04'),(169,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','::1',NULL,'2026-10-16 20:54:35'),(170,3,'Modulo Sesiones','UPDATE',4,'estado','Planificada','Parcial','::1',NULL,'2026-10-16 20:59:08'),(171,3,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-16 20:59:18'),(172,3,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-16 20:59:22'),(173,3,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-16 20:59:27'),(174,3,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-16 20:59:34'),(175,3,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-16 20:59:38'),(176,3,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-16 20:59:42'),(177,3,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-16 20:59:47'),(178,3,'Modulo Sesiones','UPDATE',4,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-16 21:00:28'),(179,1,'Seguridad','UPDATE',4,'usuario',NULL,'Jose Daniel GONZALEZ FANEITE','::1',NULL,'2026-10-16 21:55:47'),(180,1,'Seguridad','UPDATE',4,'usuario',NULL,'Jose Daniel GONZALEZ FANEITE','::1',NULL,'2026-10-16 21:56:07'),(181,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-10-17_03-56-57.sql','::1',NULL,'2026-10-16 21:56:58'),(182,4,'RegistroRPE','INSERT',1,'Nuevo registro RPE',NULL,'{\"id_rpe\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"\",\"id_sesion\":\"4\",\"fecha\":\"2026-10-16\",\"rpe\":\"9\",\"duracion_minutos\":\"90\",\"metros_nadados\":\"50\",\"horas_sueno\":\"20\",\"calidad_sueno\":\"3\",\"sensacion_muscular\":\"5\",\"estres_percibido\":\"5\",\"observaciones\":\"prueba\"}','::1',NULL,'2026-10-17 00:18:31'),(183,4,'RegistroRPE','UPDATE',1,'Actualización de registro RPE',NULL,'{\"id_rpe\":\"1\",\"id_atleta\":\"13\",\"fecha\":\"2026-10-16\",\"rpe\":\"1\",\"horas_sueno\":\"0\",\"accion\":\"actualizar\"}','::1',NULL,'2026-10-17 09:16:28'),(184,4,'RegistroRPE','INSERT',2,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"1\",\"fecha\":\"2026-10-16\",\"rpe\":\"8\",\"horas_sueno\":\"7\",\"accion\":\"registrar\"}','::1',NULL,'2026-10-17 09:17:01'),(185,1,'RegistroRPE','UPDATE',1,'Actualización de registro RPE',NULL,'{\"id_rpe\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"13\",\"id_sesion\":\"\",\"fecha\":\"2026-10-16\",\"rpe\":\"1\",\"duracion_minutos\":\"\",\"metros_nadados\":\"\",\"horas_sueno\":\"0.0\",\"calidad_sueno\":\"\",\"sensacion_muscular\":\"\",\"estres_percibido\":\"\",\"observaciones\":\"\"}','::1',NULL,'2026-10-17 09:27:53'),(186,1,'RegistroRPE','UPDATE',1,'Actualización de registro RPE',NULL,'{\"id_rpe\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"5\",\"id_sesion\":\"\",\"fecha\":\"2026-10-16\",\"rpe\":\"1\",\"duracion_minutos\":\"\",\"metros_nadados\":\"\",\"horas_sueno\":\"0.0\",\"calidad_sueno\":\"\",\"sensacion_muscular\":\"\",\"estres_percibido\":\"\",\"observaciones\":\"\"}','::1',NULL,'2026-10-17 09:28:31'),(187,1,'Seguridad','UPDATE',1,'permisos rol',NULL,'84 permisos','::1',NULL,'2026-10-17 09:31:45'),(188,1,'Seguridad','UPDATE',3,'permisos rol',NULL,'22 permisos','::1',NULL,'2026-10-17 09:32:19'),(189,1,'Seguridad','UPDATE',2,'permisos rol',NULL,'60 permisos','::1',NULL,'2026-10-17 09:32:27'),(190,1,'Seguridad','UPDATE',5,'permisos rol',NULL,'9 permisos','::1',NULL,'2026-10-17 09:32:48'),(191,1,'RegistroRPE','UPDATE',1,'Actualización de registro RPE',NULL,'{\"id_rpe\":\"1\",\"accion\":\"actualizar\",\"id_atleta\":\"13\",\"id_sesion\":\"\",\"fecha\":\"2026-10-16\",\"rpe\":\"1\",\"duracion_minutos\":\"\",\"metros_nadados\":\"\",\"horas_sueno\":\"0.0\",\"calidad_sueno\":\"\",\"sensacion_muscular\":\"\",\"estres_percibido\":\"\",\"observaciones\":\"\"}','::1',NULL,'2026-10-17 09:34:10'),(192,1,'RegistroRPE','INSERT',3,'Nuevo registro RPE',NULL,'{\"id_rpe\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"9\",\"id_sesion\":\"4\",\"fecha\":\"2026-10-16\",\"rpe\":\"4\",\"duracion_minutos\":\"90\",\"metros_nadados\":\"50\",\"horas_sueno\":\"5\",\"calidad_sueno\":\"3\",\"sensacion_muscular\":\"5\",\"estres_percibido\":\"5\",\"observaciones\":\"555\"}','::1',NULL,'2026-10-17 09:35:33'),(193,4,'RegistroRPE','INSERT',4,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"id_sesion\":\"4\",\"fecha\":\"2020-01-01\",\"rpe\":\"8\",\"horas_sueno\":\"7\",\"accion\":\"registrar\"}','::1',NULL,'2026-10-17 10:08:03'),(194,4,'RegistroRPE','UPDATE',3,'Acceso denegado a RPE',NULL,'Intento de leer RPE ajeno (rol: Atleta)','::1',NULL,'2026-10-17 11:51:34'),(195,4,'RegistroRPE','UPDATE',3,'Intento bloqueado',NULL,'No tienes permiso para editar este registro.','::1',NULL,'2026-10-17 11:54:12'),(196,4,'RegistroRPE','UPDATE',3,'Intento bloqueado',NULL,'No tienes permiso para editar este registro.','::1',NULL,'2026-10-17 12:11:20'),(197,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 2','::1',NULL,'2026-10-17 22:20:34'),(198,3,'Modulo Sesiones','UPDATE',5,'estado','Planificada','Parcial','::1',NULL,'2026-10-17 22:26:55'),(199,3,'Asistencias','INSERT',16,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-17 22:27:01'),(200,3,'Asistencias','INSERT',1,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-17 22:27:11'),(201,3,'Modulo Sesiones','UPDATE',5,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-17 22:27:39'),(202,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','::1',NULL,'2026-10-17 22:28:18'),(203,3,'Modulo Sesiones','UPDATE',6,'estado','Planificada','Parcial','::1',NULL,'2026-10-17 22:29:13'),(204,3,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-17 22:29:21'),(205,3,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-17 22:29:29'),(206,3,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-17 22:29:34'),(207,3,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-17 22:29:40'),(208,3,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-17 22:29:46'),(209,3,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-17 22:29:50'),(210,3,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-17 22:29:57'),(211,3,'Modulo Sesiones','UPDATE',6,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-17 22:30:38'),(212,1,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-10-18_05-51-48.sql','::1',NULL,'2026-10-17 23:51:50'),(213,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','::1',NULL,'2026-10-18 00:00:16'),(214,2,'Modulo Sesiones','UPDATE',7,'estado','Planificada','Parcial','::1',NULL,'2026-10-18 00:01:48'),(215,2,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-18 00:01:58'),(216,2,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-10-18 00:02:03'),(217,2,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-18 00:02:08'),(218,2,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-18 00:02:12'),(219,2,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-18 00:02:16'),(220,2,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-18 00:02:19'),(221,2,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-10-18 00:02:23'),(222,2,'Modulo Sesiones','UPDATE',7,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-10-18 00:03:50'),(223,4,'RegistroRPE','INSERT',5,'Nuevo registro RPE',NULL,'{\"id_rpe\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"\",\"id_sesion\":\"7\",\"fecha\":\"2026-10-18\",\"rpe\":\"5\",\"duracion_minutos\":\"90\",\"metros_nadados\":\"50\",\"horas_sueno\":\"6\",\"calidad_sueno\":\"7\",\"sensacion_muscular\":\"6\",\"estres_percibido\":\"7\",\"observaciones\":\"dsfsdfsfsd\"}','::1',NULL,'2026-10-18 00:05:21'),(224,1,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"17\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Espalda\",\"lado\":\"\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebasssss\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:13:59'),(225,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Espalda\",\"lado\":\"Bilateral\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebasssss\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:14:55'),(226,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Espalda\",\"lado\":\"\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebasssss\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:15:52'),(227,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Espalda\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebassssskk\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:16:17'),(228,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Mano\\/Mu\\u00f1eca\",\"lado\":\"Derecho\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebassssskk\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:16:48'),(229,1,'Lesiones','UPDATE',2,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"2\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-10\",\"fecha_estimada_recup\":\"2026-10-15\",\"zona_anatomica\":\"Hombro\",\"lado\":\"Izquierdo\",\"tipo\":\"Aguda\",\"nivel_molestia\":\"4\",\"gravedad_clinica\":\"Moderada\",\"diagnostico\":\"holasdssads\",\"tratamiento\":\"asdasdsadas\",\"profesional\":\"asdsadsada\",\"estado\":\"EnRehabilitacion\",\"observaciones\":\"asdasdasdas\"}','::1',NULL,'2026-10-21 23:17:13'),(230,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Oido\\/Ojo\\/Cabeza\",\"tipo\":\"Enfermedad\",\"nivel_molestia\":\"2\",\"gravedad_clinica\":\"Leve\",\"diagnostico\":\"Pruebassssskk\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:17:50'),(231,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Oido\\/Ojo\\/Cabeza\",\"tipo\":\"Enfermedad\",\"nivel_molestia\":\"5\",\"gravedad_clinica\":\"Moderada\",\"diagnostico\":\"Pruebassssskk\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"Activa\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:19:05'),(232,1,'Lesiones','UPDATE',3,'Actualización de diagnóstico/estado',NULL,'{\"id_lesion\":\"3\",\"accion\":\"actualizar\",\"fecha_inicio\":\"2026-10-21\",\"fecha_estimada_recup\":\"2026-11-26\",\"zona_anatomica\":\"Oido\\/Ojo\\/Cabeza\",\"tipo\":\"Enfermedad\",\"nivel_molestia\":\"5\",\"gravedad_clinica\":\"Moderada\",\"diagnostico\":\"Pruebassssskk\",\"tratamiento\":\"Pruebasssss\",\"profesional\":\"Pruebasssss\",\"estado\":\"EnRehabilitacion\",\"observaciones\":\"Pruebasssss\"}','::1',NULL,'2026-10-21 23:19:18'),(233,1,'Lesiones','UPDATE',1,'INTENTO_BLOQUEADO',NULL,'Gravedad clínica no válida.','::1',NULL,'2026-10-22 00:24:39'),(234,1,'Lesiones','UPDATE',1,'INTENTO_BLOQUEADO',NULL,'Molestia 3/10 requiere gravedad Leve, pero recibió Grave. Reglas: 1-3→Leve, 4-7→Moderada, 8-10→Grave.','::1',NULL,'2026-10-22 00:26:20'),(235,1,'Lesiones','UPDATE',1,'INTENTO_BLOQUEADO',NULL,'Una enfermedad no tiene lateralidad. Deje el lado vacío.','::1',NULL,'2026-10-22 00:27:13'),(236,1,'Lesiones','UPDATE',99999,'Acceso a ID inexistente',NULL,'GET obtenerDetalleLesion id=99999','::1',NULL,'2026-10-22 00:27:27'),(237,1,'Lesiones','UPDATE',NULL,'INTENTO_BLOQUEADO',NULL,'ID de lesión inválido inyectado','::1',NULL,'2026-10-22 00:27:39'),(238,1,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"accion\":\"registrar\",\"id_atleta\":\"15\",\"zona_anatomica\":\"Rodilla\",\"lado\":\"Derecho\",\"tipo\":\"Aguda\",\"nivel_molestia\":\"5\",\"gravedad_clinica\":\"Moderada\",\"fecha_inicio\":\"2026-10-20\",\"diagnostico\":\"Esguince leve de prueba de validacion\",\"profesional\":\"Dr Test\"}','::1',NULL,'2026-10-22 00:28:12'),(239,1,'Lesiones','UPDATE',1,'INTENTO_BLOQUEADO',NULL,'Gravedad clínica no válida.','::1',NULL,'2026-10-22 00:32:51'),(240,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 20:21:15'),(241,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 21:41:12'),(242,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 21:47:32'),(243,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 21:51:00'),(244,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:05:15'),(245,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:11:12'),(246,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:11:14'),(247,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:25:48'),(248,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:25:51'),(249,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:52:34'),(250,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:52:36'),(251,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-27 22:52:38'),(252,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:55:44'),(253,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 22:55:46'),(254,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-27 22:55:48'),(255,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:35:44'),(256,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:35:46'),(257,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-27 23:35:48'),(258,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:35:51'),(259,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:35:51'),(260,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:36:58'),(261,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:37:03'),(262,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:46:02'),(263,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:46:11'),(264,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:49:02'),(265,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-27 23:49:10'),(266,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-28 00:57:06'),(267,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-28 11:57:42'),(268,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 15:50:03'),(269,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 19:01:55'),(270,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 19:31:26'),(271,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 20:03:31'),(272,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 20:37:41'),(273,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:24:52'),(274,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:24:56'),(275,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-29 21:24:59'),(276,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:33:09'),(277,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:34:21'),(278,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:34:23'),(279,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:35:12'),(280,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:35:15'),(281,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:36:19'),(282,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:36:27'),(283,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:38:25'),(284,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:38:38'),(285,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:42:21'),(286,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:49:51'),(287,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:58:57'),(288,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 21:59:03'),(289,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-29 21:59:04'),(290,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 22:00:16'),(291,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-29 22:00:27'),(292,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-29 22:00:36'),(293,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 00:31:43'),(294,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 00:31:54'),(295,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 00:32:03'),(296,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 00:39:18'),(297,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 00:39:54'),(298,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 02:07:36'),(299,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 02:07:46'),(300,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 02:07:55'),(301,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:04:34'),(302,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:04:43'),(303,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:04:44'),(304,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:05:35'),(305,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:05:50'),(306,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:06:05'),(307,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:09:09'),(308,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:09:13'),(309,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:16:31'),(310,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:16:42'),(311,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:16:44'),(312,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:17:27'),(313,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:17:44'),(314,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:18:00'),(315,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:20:46'),(316,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:20:56'),(317,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:22:05'),(318,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:27:13'),(319,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:32:50'),(320,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:32:56'),(321,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:33:00'),(322,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:34:03'),(323,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 03:34:25'),(324,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:34:41'),(325,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:35:12'),(326,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:35:27'),(327,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:37:36'),(328,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:37:45'),(329,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 03:38:43'),(330,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 08:41:00'),(331,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 09:00:29'),(332,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 09:00:32'),(333,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 09:00:36'),(334,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 09:01:06'),(335,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-10-30 09:01:21'),(336,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 09:01:33'),(337,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-10-30 09:02:18'),(338,1,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-11-05 14:14:35'),(339,1,'RegistroRPE','INSERT',0,'Intento bloqueado al registrar',NULL,'Fraude Logístico: El atleta o la sesión especificada no existen en el sistema.','::1',NULL,'2026-11-05 14:40:42'),(340,1,'Lesiones','INSERT',1,'INTENTO_BLOQUEADO',NULL,'Molestia 9/10 requiere gravedad Grave, pero recibió Leve. Reglas: 1-3→Leve, 4-7→Moderada, 8-10→Grave.','::1',NULL,'2026-11-05 15:28:08'),(341,1,'Seguridad','UPDATE',4,'contrasena','***','***','::1',NULL,'2026-11-06 02:48:17'),(342,4,'RegistroRPE','INSERT',10,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"rpe\":\"5\",\"fecha\":\"2026-11-05\"}','::1',NULL,'2026-11-06 03:52:04'),(343,4,'RegistroRPE','INSERT',11,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"rpe\":\"5\",\"fecha\":\"2026-11-05\"}','::1',NULL,'2026-11-06 03:55:49'),(344,4,'RegistroRPE','INSERT',12,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"rpe\":\"6\",\"fecha\":\"2026-11-05\"}','::1',NULL,'2026-11-06 04:22:05'),(345,4,'RegistroRPE','INSERT',13,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"rpe\":\"6\",\"fecha\":\"2026-11-05\"}','::1',NULL,'2026-11-06 10:52:51'),(346,4,'RegistroRPE','INSERT',14,'Nuevo registro RPE',NULL,'{\"id_atleta\":\"5\",\"rpe\":\"6\",\"fecha\":\"2026-11-05\"}','::1',NULL,'2026-11-06 11:49:15'),(347,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','::1',NULL,'2026-11-09 22:49:58'),(348,2,'Modulo Sesiones','UPDATE',8,'estado','Planificada','Parcial','::1',NULL,'2026-11-09 22:55:01'),(349,2,'Asistencias','INSERT',13,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-09 22:55:11'),(350,2,'Asistencias','INSERT',14,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-09 22:55:17'),(351,2,'Asistencias','INSERT',15,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-09 22:55:21'),(352,2,'Asistencias','INSERT',17,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-11-09 22:55:27'),(353,2,'Asistencias','INSERT',8,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-11-09 22:55:31'),(354,2,'Asistencias','INSERT',9,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-09 22:55:36'),(355,2,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-11-09 22:55:40'),(356,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"18\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-09 23:04:08'),(357,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"19\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-09 23:23:55'),(358,2,'Marcas','UPDATE',18,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"58.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":null,\"brazadas_por_largo\":null,\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-09 23:23:55'),(359,2,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"1\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-09 23:45:33'),(360,2,'Marcas','UPDATE',1,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"58.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":null,\"brazadas_por_largo\":null,\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-09 23:45:33'),(361,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"9\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"8.77\",\"tiempo_reaccion_seg\":\"0.75\",\"virajes\":{\"50\":\"1.23\"},\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"2.51\",\"100\":\"1.05\",\"50\":\"2.09\",\"75\":\"3.12\"},\"brazadas_por_largo\":null,\"id_marca\":\"2\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 17:07:37'),(362,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"9\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Espalda\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":\"prueba\",\"num_brazadas\":null,\"splits\":[],\"brazadas_por_largo\":null,\"id_marca\":\"3\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 17:48:56'),(363,1,'Marcas','UPDATE',3,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"9\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Espalda\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":\"01.20\",\"virajes\":{\"50\":\"02.10\"},\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":\"prueba\",\"num_brazadas\":null,\"splits\":{\"25\":\"20.00\",\"50\":\"10.00\",\"75\":\"20.00\",\"100\":\"10.00\"},\"brazadas_por_largo\":\"16\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 17:50:28'),(364,1,'Marcas','UPDATE',3,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"9\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Espalda\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":\"01.20\",\"virajes\":{\"50\":\"02.10\"},\"nivel_evento\":null,\"fecha\":\"2026-11-09\",\"observaciones\":\"prueba\",\"num_brazadas\":null,\"splits\":{\"25\":\"20.00\",\"50\":\"10.00\",\"75\":\"20.00\",\"100\":\"10.00\"},\"brazadas_por_largo\":\"18\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 17:50:55'),(365,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"1\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:21:01'),(366,1,'Marcas','UPDATE',1,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"58.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":null,\"brazadas_por_largo\":null,\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:25:34'),(367,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"2\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:27:22'),(368,1,'Marcas','UPDATE',1,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"58.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":null,\"brazadas_por_largo\":null,\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:30:14'),(369,1,'Marcas','CREATE',NULL,'Múltiples campos (Registro Completo)',NULL,'{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"60.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":{\"25\":\"15.00\",\"50\":\"15.00\",\"75\":\"15.00\",\"100\":\"15.00\"},\"brazadas_por_largo\":null,\"id_marca\":\"3\",\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:38:31'),(370,1,'Marcas','UPDATE',1,'Datos de la Marca','Ver historial previo','{\"id_atleta\":\"13\",\"id_sesion\":\"8\",\"id_evento\":null,\"estilo\":\"Libre\",\"distancia_m\":\"100\",\"tipo_piscina\":\"50m\",\"tiempo_final_seg\":\"58.00\",\"tiempo_reaccion_seg\":null,\"virajes\":null,\"nivel_evento\":null,\"fecha\":\"2026-11-10\",\"observaciones\":null,\"num_brazadas\":null,\"splits\":null,\"brazadas_por_largo\":null,\"motivo_eliminacion\":null,\"distancia_total\":null,\"estado_carrera\":null,\"ultima_distancia_recorrida_m\":null,\"ultimo_tiempo_parcial_ms\":null}','::1',NULL,'2026-11-10 20:38:31'),(371,1,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 1','::1',NULL,'2026-11-10 23:41:57'),(372,1,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: JOSE DANIEL GONZALEZ FANEITE','::1',NULL,'2026-11-11 00:18:01'),(373,1,'Asistencia','INSERT',0,'asistencia_qr','','Escaneo QR exitoso: JOSE DANIEL GONZALEZ FANEITE','::1',NULL,'2026-11-11 00:20:52'),(374,1,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-11 00:22:22'),(375,1,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-11-11 00:30:13'),(376,1,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Presente','::1',NULL,'2026-11-11 00:32:41'),(377,1,'Asistencias','INSERT',5,'estado','','Ajuste manual a: Ausente','::1',NULL,'2026-11-11 00:32:42');
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
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
INSERT INTO `intentos_login` VALUES (1,1,'admin@sgrd.com','::1',1,'2026-09-10 01:52:49'),(2,1,'admin@sgrd.com','::1',1,'2026-09-15 17:08:53'),(3,1,'admin@sgrd.com','::1',1,'2026-09-15 17:32:08'),(4,1,'admin@sgrd.com','::1',1,'2026-09-15 18:27:47'),(5,1,'admin@sgrd.com','::1',1,'2026-09-15 19:10:47'),(6,1,'admin@sgrd.com','::1',1,'2026-09-15 19:30:29'),(7,1,'admin@sgrd.com','::1',1,'2026-09-15 19:46:59'),(8,1,'admin@sgrd.com','::1',1,'2026-09-15 19:57:02'),(9,1,'admin@sgrd.com','::1',1,'2026-09-15 20:12:49'),(10,1,'admin@sgrd.com','::1',1,'2026-09-15 21:51:28'),(11,1,'admin@sgrd.com','::1',1,'2026-09-16 00:05:38'),(12,1,'admin@sgrd.com','::1',1,'2026-09-17 01:10:23'),(13,2,'profpastorm07@gmail.com','::1',1,'2026-09-15 05:55:00'),(14,1,'admin@sgrd.com','::1',1,'2026-09-17 04:20:17'),(15,2,'profpastorm07@gmail.com','::1',1,'2026-09-15 06:10:00'),(16,1,'admin@sgrd.com','::1',1,'2026-09-17 04:53:56'),(17,2,'profpastorm07@gmail.com','::1',1,'2026-09-16 06:30:00'),(18,1,'admin@sgrd.com','::1',1,'2026-09-17 09:24:17'),(19,1,'admin@sgrd.com','192.168.230.68',1,'2026-09-17 09:36:22'),(20,1,'admin@sgrd.com','::1',1,'2026-09-22 12:06:45'),(21,1,'admin@sgrd.com','::1',1,'2026-09-25 21:05:33'),(22,1,'admin@sgrd.com','::1',1,'2026-09-29 19:42:41'),(23,1,'admin@sgrd.com','::1',1,'2026-09-29 22:01:40'),(24,1,'admin@sgrd.com','::1',1,'2026-09-29 23:42:31'),(25,1,'admin@sgrd.com','::1',1,'2026-09-30 00:02:19'),(26,1,'admin@sgrd.com','::1',1,'2026-09-30 00:20:49'),(27,1,'admin@sgrd.com','::1',1,'2026-09-30 14:23:47'),(28,1,'admin@sgrd.com','::1',1,'2026-09-30 14:44:22'),(29,1,'admin@sgrd.com','::1',1,'2026-09-30 17:24:47'),(30,1,'admin@sgrd.com','::1',1,'2026-09-30 23:41:55'),(31,1,'admin@sgrd.com','::1',1,'2026-10-01 00:39:29'),(32,1,'admin@sgrd.com','::1',1,'2026-10-01 12:09:37'),(33,1,'admin@sgrd.com','::1',1,'2026-10-12 11:31:14'),(34,1,'admin@sgrd.com','::1',1,'2026-10-12 15:45:31'),(35,3,'jose.pirolo@gmail.com','::1',1,'2026-10-12 15:48:30'),(36,1,'admin@sgrd.com','::1',1,'2026-10-12 16:09:24'),(37,1,'admin@sgrd.com','::1',1,'2026-10-16 20:34:11'),(38,1,'admin@sgrd.com','::1',1,'2026-10-16 20:39:12'),(39,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 20:46:43'),(40,1,'admin@sgrd.com','::1',1,'2026-10-16 20:48:41'),(41,1,'admin@sgrd.com','::1',1,'2026-10-16 20:50:37'),(42,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 20:50:56'),(43,1,'admin@sgrd.com','::1',1,'2026-10-16 20:51:35'),(44,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 20:52:27'),(45,1,'admin@sgrd.com','::1',1,'2026-10-16 20:53:06'),(46,NULL,'jose.pirolo@gmail.com','::1',0,'2026-10-16 20:55:13'),(47,NULL,'jose.pirolo@gmail.com','::1',0,'2026-10-16 20:55:36'),(48,NULL,'jose.pirolo@gmail.com','::1',0,'2026-10-16 20:56:00'),(49,1,'admin@sgrd.com','::1',1,'2026-10-16 20:56:09'),(50,3,'jose.pirolo@sgrd.com','::1',1,'2026-10-16 20:56:39'),(51,3,'jose.pirolo@sgrd.com','::1',1,'2026-10-16 20:57:27'),(52,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 21:01:00'),(53,1,'admin@sgrd.com','::1',1,'2026-10-16 21:41:37'),(54,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 21:42:12'),(55,1,'admin@sgrd.com','::1',1,'2026-10-16 21:55:16'),(56,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 22:05:43'),(57,1,'admin@sgrd.com','::1',1,'2026-10-16 23:37:13'),(58,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 23:37:56'),(59,4,'josedaniel@sgrd.com','::1',1,'2026-10-16 23:47:44'),(60,1,'admin@sgrd.com','::1',1,'2026-10-17 00:25:53'),(61,1,'admin@sgrd.com','::1',1,'2026-10-17 08:52:22'),(62,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 09:15:11'),(63,1,'admin@sgrd.com','::1',1,'2026-10-17 09:24:15'),(64,1,'admin@sgrd.com','::1',1,'2026-10-17 09:31:26'),(65,1,'admin@sgrd.com','::1',1,'2026-10-17 09:33:35'),(66,4,'josedaniel@sgrd.com','::1',0,'2026-10-17 09:38:05'),(67,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 09:38:21'),(68,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 11:09:40'),(69,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 11:22:11'),(70,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 11:51:19'),(71,3,'jose.pirolo@sgrd.com','::1',1,'2026-10-17 22:19:36'),(72,4,'josedaniel@sgrd.com','::1',1,'2026-10-17 22:31:22'),(73,2,'profpastorm07@gmail.com','::1',1,'2026-10-17 22:34:48'),(74,1,'admin@sgrd.com','::1',1,'2026-10-17 23:51:41'),(75,2,'profpastorm07@gmail.com','::1',1,'2026-10-18 00:01:32'),(76,4,'josedaniel@sgrd.com','::1',1,'2026-10-18 00:04:35'),(77,1,'admin@sgrd.com','::1',1,'2026-10-21 11:15:04'),(78,1,'admin@sgrd.com','::1',1,'2026-10-21 11:58:31'),(79,1,'admin@sgrd.com','::1',1,'2026-10-21 18:02:11'),(80,1,'admin@sgrd.com','::1',1,'2026-10-21 18:18:50'),(81,1,'admin@sgrd.com','::1',1,'2026-10-21 19:36:58'),(82,1,'admin@sgrd.com','::1',1,'2026-10-21 23:11:53'),(83,1,'admin@sgrd.com','::1',1,'2026-10-22 00:24:02'),(84,NULL,'usuario_fantasma_999@test.com',NULL,0,'2026-10-25 19:51:01'),(85,1,'admin@sgrd.com',NULL,0,'2026-10-25 19:51:01'),(86,1,'admin@sgrd.com','::1',1,'2026-10-27 20:18:31'),(87,1,'admin@sgrd.com','::1',1,'2026-10-27 20:55:32'),(88,1,'admin@sgrd.com','::1',1,'2026-10-27 21:05:06'),(89,1,'admin@sgrd.com','::1',1,'2026-10-27 21:12:27'),(90,1,'admin@sgrd.com','::1',1,'2026-10-27 21:15:36'),(91,1,'admin@sgrd.com','::1',1,'2026-10-27 21:20:53'),(92,1,'admin@sgrd.com','::1',1,'2026-10-27 21:30:13'),(93,1,'admin@sgrd.com','::1',1,'2026-10-27 21:41:10'),(94,1,'admin@sgrd.com','::1',1,'2026-10-27 21:47:30'),(95,1,'admin@sgrd.com','::1',1,'2026-10-27 21:49:40'),(96,1,'admin@sgrd.com','::1',1,'2026-10-27 21:50:58'),(97,1,'admin@sgrd.com','::1',1,'2026-10-27 22:01:12'),(98,1,'admin@sgrd.com','::1',1,'2026-10-27 22:05:13'),(99,1,'admin@sgrd.com','::1',1,'2026-10-27 22:08:40'),(100,1,'admin@sgrd.com','::1',1,'2026-10-27 22:08:40'),(101,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:08:44'),(102,1,'admin@sgrd.com','::1',1,'2026-10-27 22:11:09'),(103,1,'admin@sgrd.com','::1',1,'2026-10-27 22:11:09'),(104,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:11:13'),(105,1,'admin@sgrd.com','::1',1,'2026-10-27 22:18:10'),(106,1,'admin@sgrd.com','::1',1,'2026-10-27 22:18:10'),(107,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:18:13'),(108,1,'admin@sgrd.com','::1',1,'2026-10-27 22:24:45'),(109,1,'admin@sgrd.com','::1',1,'2026-10-27 22:24:45'),(110,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:24:49'),(111,1,'admin@sgrd.com','::1',1,'2026-10-27 22:25:46'),(112,1,'admin@sgrd.com','::1',1,'2026-10-27 22:25:46'),(113,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:25:50'),(114,1,'admin@sgrd.com','::1',1,'2026-10-27 22:52:31'),(115,1,'admin@sgrd.com','::1',1,'2026-10-27 22:52:31'),(116,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:52:35'),(117,1,'admin@sgrd.com','::1',1,'2026-10-27 22:55:41'),(118,1,'admin@sgrd.com','::1',1,'2026-10-27 22:55:41'),(119,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 22:55:45'),(120,1,'admin@sgrd.com','::1',1,'2026-10-27 23:35:36'),(121,1,'admin@sgrd.com','::1',1,'2026-10-27 23:35:36'),(122,1,'admin@sgrd.com','::1',1,'2026-10-27 23:35:38'),(123,1,'admin@sgrd.com','::1',1,'2026-10-27 23:35:38'),(124,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-27 23:35:38'),(125,1,'admin@sgrd.com','::1',1,'2026-10-27 23:36:54'),(126,1,'admin@sgrd.com','::1',1,'2026-10-27 23:36:55'),(127,1,'admin@sgrd.com','::1',1,'2026-10-27 23:46:00'),(128,1,'admin@sgrd.com','::1',1,'2026-10-27 23:46:06'),(129,1,'admin@sgrd.com','::1',1,'2026-10-27 23:48:59'),(130,1,'admin@sgrd.com','::1',1,'2026-10-27 23:49:05'),(131,1,'admin@sgrd.com','::1',1,'2026-10-28 00:47:44'),(132,1,'admin@sgrd.com','::1',1,'2026-10-28 00:51:22'),(133,1,'admin@sgrd.com','::1',1,'2026-10-28 00:55:47'),(134,1,'admin@sgrd.com','::1',1,'2026-10-28 00:55:47'),(135,1,'admin@sgrd.com','::1',1,'2026-10-28 00:55:48'),(136,1,'admin@sgrd.com','::1',1,'2026-10-28 00:55:48'),(137,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-28 00:55:50'),(138,1,'admin@sgrd.com','::1',1,'2026-10-28 00:57:03'),(139,1,'admin@sgrd.com','::1',1,'2026-10-28 00:57:37'),(140,1,'admin@sgrd.com','::1',1,'2026-10-28 11:57:38'),(141,1,'admin@sgrd.com','::1',1,'2026-10-28 22:53:33'),(142,1,'admin@sgrd.com','::1',1,'2026-10-28 23:08:18'),(143,1,'admin@sgrd.com','::1',1,'2026-10-28 23:08:20'),(144,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-28 23:08:23'),(145,1,'admin@sgrd.com','::1',1,'2026-10-28 23:13:43'),(146,1,'admin@sgrd.com','::1',1,'2026-10-28 23:14:03'),(147,1,'admin@sgrd.com','::1',1,'2026-10-29 00:03:56'),(148,1,'admin@sgrd.com','::1',1,'2026-10-29 00:09:44'),(149,1,'admin@sgrd.com','::1',1,'2026-10-29 00:18:58'),(150,1,'admin@sgrd.com','::1',1,'2026-10-29 00:18:59'),(151,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-29 00:19:03'),(152,1,'admin@sgrd.com','::1',1,'2026-10-29 00:19:59'),(153,1,'admin@sgrd.com','::1',1,'2026-10-29 00:21:56'),(154,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-29 00:22:03'),(155,1,'admin@sgrd.com','::1',1,'2026-10-29 00:22:09'),(156,1,'admin@sgrd.com','::1',1,'2026-10-29 15:45:49'),(157,1,'admin@sgrd.com','::1',1,'2026-10-29 15:53:53'),(158,1,'admin@sgrd.com','::1',1,'2026-10-29 15:55:45'),(159,1,'admin@sgrd.com','::1',1,'2026-10-29 19:01:52'),(160,1,'admin@sgrd.com','::1',1,'2026-10-29 19:31:23'),(161,1,'admin@sgrd.com','::1',1,'2026-10-29 20:03:26'),(162,1,'admin@sgrd.com','::1',1,'2026-10-29 20:35:24'),(163,1,'admin@sgrd.com','::1',1,'2026-10-29 20:39:32'),(164,1,'admin@sgrd.com','::1',1,'2026-10-29 21:18:57'),(165,1,'admin@sgrd.com','::1',1,'2026-10-29 21:24:49'),(166,1,'admin@sgrd.com','::1',1,'2026-10-29 21:24:49'),(167,1,'admin@sgrd.com','::1',1,'2026-10-29 21:24:50'),(168,1,'admin@sgrd.com','::1',1,'2026-10-29 21:24:50'),(169,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-29 21:24:55'),(170,1,'admin@sgrd.com','::1',1,'2026-10-29 21:33:05'),(171,1,'admin@sgrd.com','::1',1,'2026-10-29 21:34:18'),(172,1,'admin@sgrd.com','::1',1,'2026-10-29 21:34:18'),(173,1,'admin@sgrd.com','::1',1,'2026-10-29 21:35:09'),(174,1,'admin@sgrd.com','::1',1,'2026-10-29 21:35:10'),(175,1,'admin@sgrd.com','::1',1,'2026-10-29 21:36:16'),(176,1,'admin@sgrd.com','::1',1,'2026-10-29 21:36:22'),(177,1,'admin@sgrd.com','::1',1,'2026-10-29 21:38:23'),(178,1,'admin@sgrd.com','::1',1,'2026-10-29 21:38:33'),(179,1,'admin@sgrd.com','::1',1,'2026-10-29 21:42:16'),(180,1,'admin@sgrd.com','::1',1,'2026-10-29 21:49:45'),(181,1,'admin@sgrd.com','::1',1,'2026-10-29 21:58:52'),(182,1,'admin@sgrd.com','::1',1,'2026-10-29 21:58:52'),(183,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-29 21:58:58'),(184,1,'admin@sgrd.com','::1',1,'2026-10-29 22:00:13'),(185,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-29 22:00:23'),(186,1,'admin@sgrd.com','::1',1,'2026-10-29 22:00:31'),(187,1,'admin@sgrd.com','::1',1,'2026-10-30 00:31:40'),(188,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 00:31:50'),(189,1,'admin@sgrd.com','::1',1,'2026-10-30 00:31:58'),(190,1,'admin@sgrd.com','::1',1,'2026-10-30 00:34:22'),(191,1,'admin@sgrd.com','::1',1,'2026-10-30 00:35:21'),(192,1,'admin@sgrd.com','::1',1,'2026-10-30 00:35:21'),(193,1,'admin@sgrd.com','::1',1,'2026-10-30 00:38:55'),(194,1,'admin@sgrd.com','::1',1,'2026-10-30 00:38:56'),(195,1,'admin@sgrd.com','::1',1,'2026-10-30 00:38:56'),(196,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 00:39:26'),(197,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:34'),(198,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:35'),(199,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:38'),(200,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:43'),(201,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:45'),(202,1,'admin@sgrd.com','::1',1,'2026-10-30 00:39:46'),(203,1,'admin@sgrd.com','::1',1,'2026-10-30 00:40:04'),(204,1,'admin@sgrd.com','::1',1,'2026-10-30 00:40:04'),(205,1,'admin@sgrd.com','::1',1,'2026-10-30 00:40:05'),(206,1,'admin@sgrd.com','::1',1,'2026-10-30 02:07:33'),(207,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 02:07:42'),(208,1,'admin@sgrd.com','::1',1,'2026-10-30 02:07:50'),(209,1,'admin@sgrd.com','::1',1,'2026-10-30 02:14:12'),(210,1,'admin@sgrd.com','::1',1,'2026-10-30 02:14:12'),(211,1,'admin@sgrd.com','::1',1,'2026-10-30 02:21:46'),(212,1,'admin@sgrd.com','::1',1,'2026-10-30 02:21:46'),(213,1,'admin@sgrd.com','::1',1,'2026-10-30 03:04:26'),(214,1,'admin@sgrd.com','::1',1,'2026-10-30 03:04:28'),(215,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:04:37'),(216,1,'admin@sgrd.com','::1',1,'2026-10-30 03:05:30'),(217,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:05:44'),(218,1,'admin@sgrd.com','::1',1,'2026-10-30 03:05:57'),(219,1,'admin@sgrd.com','::1',1,'2026-10-30 03:06:48'),(220,1,'admin@sgrd.com','::1',1,'2026-10-30 03:06:48'),(221,1,'admin@sgrd.com','::1',1,'2026-10-30 03:09:05'),(222,1,'admin@sgrd.com','::1',1,'2026-10-30 03:09:05'),(223,1,'admin@sgrd.com','::1',1,'2026-10-30 03:16:23'),(224,1,'admin@sgrd.com','::1',1,'2026-10-30 03:16:27'),(225,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:16:34'),(226,1,'admin@sgrd.com','::1',1,'2026-10-30 03:17:23'),(227,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:17:37'),(228,1,'admin@sgrd.com','::1',1,'2026-10-30 03:17:51'),(229,1,'admin@sgrd.com','::1',1,'2026-10-30 03:18:44'),(230,1,'admin@sgrd.com','::1',1,'2026-10-30 03:18:45'),(231,1,'admin@sgrd.com','::1',1,'2026-10-30 03:20:43'),(232,1,'admin@sgrd.com','::1',1,'2026-10-30 03:20:50'),(233,1,'admin@sgrd.com','::1',1,'2026-10-30 03:22:00'),(234,1,'admin@sgrd.com','::1',1,'2026-10-30 03:27:08'),(235,1,'admin@sgrd.com','::1',1,'2026-10-30 03:32:41'),(236,1,'admin@sgrd.com','::1',1,'2026-10-30 03:32:42'),(237,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:32:43'),(238,1,'admin@sgrd.com','::1',1,'2026-10-30 03:33:59'),(239,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 03:34:16'),(240,1,'admin@sgrd.com','::1',1,'2026-10-30 03:34:32'),(241,1,'admin@sgrd.com','::1',1,'2026-10-30 03:35:07'),(242,1,'admin@sgrd.com','::1',1,'2026-10-30 03:35:18'),(243,1,'admin@sgrd.com','::1',1,'2026-10-30 03:35:58'),(244,1,'admin@sgrd.com','::1',1,'2026-10-30 03:35:59'),(245,1,'admin@sgrd.com','::1',1,'2026-10-30 03:37:33'),(246,1,'admin@sgrd.com','::1',1,'2026-10-30 03:37:40'),(247,1,'admin@sgrd.com','::1',1,'2026-10-30 03:38:38'),(248,1,'admin@sgrd.com','::1',1,'2026-10-30 08:39:26'),(249,1,'admin@sgrd.com','::1',1,'2026-10-30 08:42:06'),(250,1,'admin@sgrd.com','::1',1,'2026-10-30 08:43:50'),(251,1,'admin@sgrd.com','::1',1,'2026-10-30 09:00:22'),(252,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 09:00:23'),(253,1,'admin@sgrd.com','::1',1,'2026-10-30 09:00:23'),(254,1,'admin@sgrd.com','::1',1,'2026-10-30 09:01:02'),(255,1,'admin@sgrd.com','127.0.0.1',1,'2026-10-30 09:01:16'),(256,1,'admin@sgrd.com','::1',1,'2026-10-30 09:01:27'),(257,1,'admin@sgrd.com','::1',1,'2026-10-30 09:02:06'),(258,1,'admin@sgrd.com','::1',1,'2026-10-30 09:02:37'),(259,1,'admin@sgrd.com','::1',1,'2026-10-30 09:02:55'),(260,1,'admin@sgrd.com','::1',1,'2026-11-05 13:32:33'),(261,1,'admin@sgrd.com','::1',1,'2026-11-05 14:14:31'),(262,1,'admin@sgrd.com','::1',1,'2026-11-05 14:14:39'),(263,1,'admin@sgrd.com','::1',1,'2026-11-05 14:14:54'),(264,1,'admin@sgrd.com','::1',1,'2026-11-05 16:59:52'),(265,NULL,'josemiguel.pirolo@gmail.com','::1',0,'2026-11-06 02:47:09'),(266,1,'admin@sgrd.com','::1',1,'2026-11-06 02:47:19'),(267,4,'josedaniel@sgrd.com','::1',0,'2026-11-06 02:47:49'),(268,1,'admin@sgrd.com','::1',1,'2026-11-06 02:47:54'),(269,4,'josedaniel@sgrd.com','::1',1,'2026-11-06 02:48:30'),(270,1,'admin@sgrd.com','::1',1,'2026-11-06 03:47:04'),(271,4,'josedaniel@sgrd.com','::1',1,'2026-11-06 03:47:24'),(272,4,'josedaniel@sgrd.com','::1',1,'2026-11-06 03:51:21'),(273,1,'admin@sgrd.com','::1',1,'2026-11-07 17:02:14'),(274,1,'admin@sgrd.com','::1',1,'2026-11-08 18:03:59'),(275,1,'admin@sgrd.com','::1',1,'2026-11-09 21:48:42'),(276,1,'admin@sgrd.com','::1',1,'2026-11-09 22:33:40'),(277,2,'profpastorm07@gmail.com','::1',1,'2026-11-09 22:54:03'),(278,1,'admin@sgrd.com','::1',1,'2026-11-10 17:05:57'),(279,1,'admin@sgrd.com','::1',1,'2026-11-10 17:06:45'),(280,1,'admin@sgrd.com','::1',1,'2026-11-10 17:13:10'),(281,1,'admin@sgrd.com','::1',1,'2026-11-10 17:48:17'),(282,1,'admin@sgrd.com','::1',1,'2026-11-12 00:09:57');
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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Equipo Juvenil A\". Volumen ejecutado: 5900m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=1',1,'2026-09-15 09:45:00'),(2,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 5500m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=2',1,'2026-09-16 10:26:00'),(3,1,'Nuevo Evento Registrado','Se ha creado el evento \"REGIONAL\" (Control). Sede: COMPLEJO PISCINAS BOLIVARIANAS.','fa-calendar-plus','emerald','?p=eventos',1,'2026-09-17 09:57:28'),(4,1,'Clínica: Nueva Evaluación Antropométrica','Se ha registrado una nueva medición: Peso 67kg, Talla 175cm.','fa-weight','emerald','?p=antropometria',1,'2026-09-17 10:27:37'),(5,1,'Evento Actualizado','Se han actualizado los datos del evento \"REGIONAL\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=1',1,'2026-09-30 17:32:49'),(6,1,'Evento Actualizado','Se han actualizado los datos del evento \"REGIONAL\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=1',1,'2026-09-30 17:38:08'),(7,1,'Evento Actualizado','Se han actualizado los datos del evento \"REGIONAL\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=1',1,'2026-09-30 17:38:21'),(8,1,'Evento Actualizado','Se han actualizado los datos del evento \"REGIONAL\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=1',1,'2026-09-30 17:46:09'),(9,1,'Evento Actualizado','Se han actualizado los datos del evento \"REGIONAL\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=1',1,'2026-09-30 19:23:15'),(10,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 5900m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=1',1,'2026-10-12 13:31:01'),(11,1,'Nuevo Macrociclo Creado','Se ha creado el macrociclo \"Nacional 2026\" para .','fa-project-diagram','emerald','?p=periodizacion',0,'2026-10-12 13:45:08'),(12,1,'Plan ATR Generado','Se generó el plan de periodización para \"Plan ATR\" (9 semanas).','fa-magic','cyan','?p=periodizacion&h=1',1,'2026-10-12 13:46:39'),(13,1,'Plan ATR Generado','Se generó el plan de periodización para \"Plan ATR\" (9 semanas).','fa-magic','cyan','?p=periodizacion&h=1',1,'2026-10-12 14:12:08'),(14,1,'Macrociclo Actualizado','Se han actualizado los datos del macrociclo \"Nacional 2026\".','fa-edit','amber','?p=periodizacion&h=1',1,'2026-10-12 14:15:33'),(15,1,'Macrociclo Actualizado','Se han actualizado los datos del macrociclo \"Nacional 2026\".','fa-edit','amber','?p=periodizacion&h=1',1,'2026-10-12 14:15:53'),(16,1,'Plan ATR Generado','Se generó el plan de periodización para \"Plan ATR\" (5 semanas).','fa-magic','cyan','?p=periodizacion&h=1',1,'2026-10-12 14:16:34'),(17,1,'Estado de Macrociclo Cambiado','El macrociclo \"\" cambió a estado: En Progreso.','fa-exchange-alt','indigo','?p=periodizacion&h=1',1,'2026-10-12 14:18:05'),(18,1,'Estado de Macrociclo Cambiado','El macrociclo \"\" cambió a estado: Planificado.','fa-exchange-alt','indigo','?p=periodizacion&h=1',1,'2026-10-12 14:18:15'),(19,1,'Estado de Macrociclo Cambiado','El macrociclo \"\" cambió a estado: Finalizado.','fa-exchange-alt','indigo','?p=periodizacion&h=1',1,'2026-10-12 14:18:19'),(20,1,'Estado de Macrociclo Cambiado','El macrociclo \"\" cambió a estado: Planificado.','fa-exchange-alt','indigo','?p=periodizacion&h=1',1,'2026-10-12 14:22:10'),(21,1,'Estado de Macrociclo Cambiado','El macrociclo \"\" cambió a estado: Finalizado.','fa-exchange-alt','indigo','?p=periodizacion&h=1',1,'2026-10-12 14:22:13'),(22,1,'Nuevo Evento Registrado','Se ha creado el evento \"Miranda\" (Control). Sede: Maximo.','fa-calendar-plus','emerald','?p=eventos',1,'2026-10-12 14:32:23'),(23,1,'Evento Actualizado','Se han actualizado los datos del evento \"Miranda\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=2',1,'2026-10-12 14:38:14'),(24,1,'Evento Actualizado','Se han actualizado los datos del evento \"Miranda\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=2',1,'2026-10-12 14:38:34'),(25,1,'Evento Actualizado','Se han actualizado los datos del evento \"Miranda\".','fa-edit','amber','?p=eventos&accion=obtenerDetalle&id=2',0,'2026-10-12 14:42:14'),(26,1,'Clínica: Medición Anulada','Un registro antropométrico ha sido movido a la papelera.','fa-trash-alt','red','?p=antropometria&modo=papelera',1,'2026-10-12 14:48:28'),(27,1,'Clínica: Medición Restaurada','Se ha restaurado un registro antropométrico en el historial.','fa-undo','indigo','?p=antropometria&id=1',1,'2026-10-12 14:50:09'),(28,1,'Clínica: Evaluación Antropométrica Actualizada','Se han corregido los datos de la medición: Peso 67.00kg, Talla 175.0cm.','fa-edit','amber','?p=antropometria&id=1',1,'2026-10-12 14:50:42'),(29,1,'Clínica: Nueva Evaluación Antropométrica','Se ha registrado una nueva medición: Peso 60.00kg, Talla 190.0cm.','fa-weight','emerald','?p=antropometria',1,'2026-10-12 14:52:09'),(30,1,'Clínica: ⚠️ ALERTA CLÍNICA: Antropometría','Pérdida de peso crítica detectada: -10.45% respecto a la medición anterior.','fa-exclamation-triangle','red','?p=antropometria&id=2',1,'2026-10-12 14:52:09'),(31,1,'Clínica: Medición Anulada','Un registro antropométrico ha sido movido a la papelera.','fa-trash-alt','red','?p=antropometria&modo=papelera',1,'2026-10-12 14:57:15'),(32,1,'Clínica: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Sobreuso en la zona: Cervical.','fa-notes-medical','emerald','?p=lesiones&id=1',1,'2026-10-12 15:20:25'),(33,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Cervical. Nuevo estado: EnRehabilitacion.','fa-laptop-medical','amber','?p=lesiones&id=1',1,'2026-10-12 15:21:25'),(34,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Cervical. Nuevo estado: Recuperada.','fa-laptop-medical','amber','?p=lesiones&id=1',1,'2026-10-12 15:22:15'),(35,2,'Atleta de tu grupo: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Aguda en la zona: Hombro.','fa-notes-medical','emerald','?p=lesiones&id=2',1,'2026-10-12 15:34:29'),(36,1,'Clínica: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Aguda en la zona: Hombro.','fa-notes-medical','emerald','?p=lesiones&id=2',1,'2026-10-12 15:34:29'),(37,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 5900m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=1',1,'2026-10-16 20:52:04'),(38,2,'Sesión creada exitosamente','Has creado una sesión de Técnica para el grupo \"Juveniles\" el día 17/10/2026. Los atletas han sido notificados.','fa-check-circle','indigo','?p=sesiones&accion=ver&id=4',1,'2026-10-16 20:54:35'),(39,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 50m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=4',1,'2026-10-16 21:00:28'),(40,2,'Sesión creada exitosamente','Has creado una sesión de Técnica para el grupo \"Juveniles\" el día 18/10/2026. Los atletas han sido notificados.','fa-check-circle','indigo','?p=sesiones&accion=ver&id=6',1,'2026-10-17 22:28:18'),(41,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 50m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=6',1,'2026-10-17 22:30:38'),(42,2,'Sesión creada exitosamente','Has creado una sesión de Técnica para el grupo \"Juveniles\" el día 18/10/2026. Los atletas han sido notificados.','fa-check-circle','indigo','?p=sesiones&accion=ver&id=7',1,'2026-10-18 00:00:16'),(43,4,'Sesión completada - RPE Pendiente','La sesión del grupo \"Juveniles\" ha finalizado. Por favor ingresa y registra tu RPE.','fa-flag-checkered','emerald','?p=cargaBienestar&id_sesion=7',1,'2026-10-18 00:03:50'),(44,2,'Sesión completada exitosamente','Has completado la sesión del grupo \"Juveniles\". Volumen ejecutado: 50m. Los atletas han sido notificados.','fa-check-double','indigo','?p=sesiones&accion=ver&id=7',1,'2026-10-18 00:03:50'),(45,2,'Atleta de tu grupo: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Sobreuso en la zona: Espalda.','fa-notes-medical','emerald','?p=lesiones&id=3',0,'2026-10-21 23:13:59'),(46,1,'Clínica: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Sobreuso en la zona: Espalda.','fa-notes-medical','emerald','?p=lesiones&id=3',1,'2026-10-21 23:13:59'),(47,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:14:55'),(48,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:14:55'),(49,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:15:52'),(50,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:15:52'),(51,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:16:17'),(52,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Espalda. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:16:17'),(53,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Mano/Muñeca. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:16:48'),(54,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Mano/Muñeca. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:16:48'),(55,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Hombro. Nuevo estado: EnRehabilitacion.','fa-laptop-medical','amber','?p=lesiones&id=2',1,'2026-10-21 23:17:13'),(56,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Hombro. Nuevo estado: EnRehabilitacion.','fa-laptop-medical','amber','?p=lesiones&id=2',1,'2026-10-21 23:17:13'),(57,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:17:50'),(58,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:17:50'),(59,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:19:05'),(60,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: Activa.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:19:05'),(61,2,'Atleta de tu grupo: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: EnRehabilitacion.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:19:18'),(62,1,'Clínica: Evolución Clínica Actualizada','Se ha actualizado el estado de la lesión en Oido/Ojo/Cabeza. Nuevo estado: EnRehabilitacion.','fa-laptop-medical','amber','?p=lesiones&id=3',1,'2026-10-21 23:19:18'),(63,2,'Atleta de tu grupo: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Aguda en la zona: Rodilla.','fa-notes-medical','emerald','?p=lesiones&id=4',1,'2026-10-22 00:28:12'),(64,1,'Clínica: Nuevo Informe Médico Registrado','Se ha registrado un diagnóstico de Aguda en la zona: Rodilla.','fa-notes-medical','emerald','?p=lesiones&id=4',1,'2026-10-22 00:28:12'),(65,2,'Sesión creada exitosamente','Has creado una sesión de Técnica para el grupo \"Juveniles\" el día 10/11/2026. Los atletas han sido notificados.','fa-check-circle','indigo','?p=sesiones&accion=ver&id=8',1,'2026-11-09 22:49:58'),(66,2,'Sesión creada exitosamente','Has creado una sesión de Técnica para el grupo \"Juveniles\" el día 11/11/2026. Los atletas han sido notificados.','fa-check-circle','indigo','?p=sesiones&accion=ver&id=9',0,'2026-11-10 23:41:57');
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
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'atletas','ver','Ver expedientes de atletas'),(2,'atletas','crear','Crear nuevo expediente'),(3,'atletas','editar','Editar expediente existente'),(4,'atletas','eliminar','Cambiar estado del atleta (baja lógica)'),(5,'asistencia','ver','Ver registros de asistencia'),(6,'asistencia','registrar','Registrar asistencia QR o manual'),(7,'carriles','ver','Ver asignación de carriles'),(8,'carriles','gestionar','Crear/editar asignaciones de carriles y horarios'),(9,'sesiones','ver','Ver sesiones planificadas'),(10,'sesiones','crear','Crear sesiones de entrenamiento'),(11,'sesiones','editar','Editar sesiones existentes'),(12,'sesiones','completar','Registrar volumen ejecutado post-sesión'),(13,'drills','ver','Ver catálogo de ejercicios'),(14,'drills','crear','Crear nuevos ejercicios'),(15,'drills','editar','Editar ejercicios existentes'),(16,'marcas','ver','Ver marcas registradas'),(17,'marcas','registrar','Registrar nuevas marcas'),(18,'antropometria','ver','Ver mediciones antropométricas'),(19,'antropometria','registrar','Registrar nuevas mediciones'),(20,'lesiones','ver','Ver historial de lesiones'),(21,'lesiones','registrar','Registrar nuevas lesiones'),(22,'lesiones','editar','Actualizar estado y protocolo de retorno'),(23,'rpe','ver','Ver registros de RPE'),(24,'rpe','registrar','Registrar RPE post-sesión'),(25,'eventos','ver','Ver calendario de eventos'),(26,'eventos','crear','Crear eventos'),(27,'eventos','editar','Editar eventos existentes'),(28,'carga','ver','Ver métricas de carga ACWR/TSS'),(29,'rankings','ver','Consultar rankings'),(30,'reportes','generar','Generar reportes PDF'),(31,'periodizacion','ver','Ver planes de periodización'),(32,'periodizacion','generar','Generar plan ATR automático'),(33,'periodizacion','editar','Editar plan de periodización'),(34,'entrenadores','ver','Ver información de entrenadores'),(35,'seguridad','usuarios','Gestión de usuarios del sistema'),(36,'seguridad','roles','Gestión de roles y permisos'),(37,'seguridad','bitacora','Consulta de bitácora del sistema'),(38,'seguridad','backup','Realizar backups y restauraciones'),(39,'seguridad','login','Iniciar sesión'),(40,'seguridad','logout','Cerrar sesión'),(41,'metas','ver','Ver metas competitivas'),(42,'metas','gestionar','Crear/editar metas competitivas'),(43,'atletas','ver_propio','Atleta: ver su propio expediente'),(44,'atletas','rpe_propio','Atleta: registrar su propio RPE'),(45,'representantes','ver_hijos','Representante: ver expedientes de sus atletas'),(46,'representantes','asistencia_hijos','Representante: ver asistencia de sus atletas'),(47,'representantes','rpe_hijos','Representante: ver RPE de sus atletas'),(49,'seguridad','mantenimiento','Acceso al módulo de mantenimiento y respaldos'),(50,'testFisico','ver','Ver modulo de tests fisicos'),(51,'testFisico','registrar','Registrar, editar y eliminar tests fisicos'),(52,'lesiones','eliminar','Eliminar lesion (baja logica)'),(53,'lesiones','eliminardb','Eliminar lesion de la base de datos'),(54,'lesiones','reactivar','Reactivar lesion eliminada'),(55,'rpe','eliminar','Anular registro RPE'),(56,'carga_bienestar','registrar','Registrar carga de bienestar'),(57,'carga_bienestar','anular','Anular carga de bienestar'),(58,'normalizacion','ver','Ver modulo de normalizacion'),(59,'normalizacion','registrar','Registrar normalizacion'),(60,'normalizacion','editar','Editar normalizacion'),(61,'normalizacion','eliminar','Eliminar normalizacion'),(62,'normalizacion','anular','Anular normalizacion'),(63,'normalizacion_tiempos','registrar','Registrar normalizacion de tiempos'),(64,'observacionesTecnicas','ver','Ver modulo de observaciones tecnicas'),(65,'observacionesTecnicas','registrar','Registrar observacion tecnica'),(66,'representantes','ver','Ver modulo de representantes'),(67,'representantes','gestionar','Gestionar representantes'),(68,'temporadas','ver','Ver modulo de temporadas'),(69,'temporadas','registrar','Registrar temporada'),(70,'mi_perfil','ver','Ver y editar perfil propio'),(71,'sesiones','eliminar','Eliminar sesion de entrenamiento'),(72,'atletas','gestionar','Gestionar atletas (crear, editar, eliminar)'),(73,'marcas','editar','puede editar'),(74,'marcas','eliminar','se puede eliminar'),(75,'marcas','restaurar','se puede restaurar'),(76,'grupo','ver','grupo de entrenamientos'),(77,'antropometria','eliminar','Permite anular (soft delete) mediciones antropométricas'),(78,'antropometria','reactivar','Permite reactivar mediciones antropométricas'),(79,'carga_bienestar','eliminar','eliminar carga de bienestar'),(80,'carga_bienestar','reactivar','reactivar carga de bienestar'),(81,'carga_bienestar','eliminardb','eliminar permanente carga de bienestar'),(82,'rpe','reactivar','reactivar registro RPE'),(83,'rpe','editar','editar RPE post-sesión'),(84,'reportes','ver','visualizar moduo reportes PDF'),(85,'sistemaExperto','ver','Acceso al modulo del Sistema Experto (recomendaciones y alertas)'),(87,'antropometria','eliminardb','Eliminado Fisico de medicion antropometrica'),(88,'asignacion','ver','ver asignacion de carriles'),(89,'asignacion','gestionar','crear, editar y eliminar'),(90,'horario','ver','ver horarios'),(91,'horario','gestionar','crear, editar, eliminar'),(92,'calendario','ver','Ver calendario de competencias'),(93,'rpe','ver_inconsistencias','Ver alertas de inconsistencias biológicas (RPE=1 + récord)'),(94,'rpe','ver_recomendaciones','Ver recomendaciones de carga generadas por IA');
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
) ENGINE=InnoDB AUTO_INCREMENT=962 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (800,1,1),(796,1,2),(797,1,3),(798,1,4),(795,1,5),(794,1,6),(806,1,7),(805,1,8),(865,1,9),(862,1,10),(863,1,11),(861,1,12),(809,1,13),(807,1,14),(808,1,15),(827,1,16),(825,1,17),(791,1,18),(790,1,19),(822,1,20),(821,1,21),(817,1,22),(851,1,23),(850,1,24),(813,1,25),(811,1,26),(812,1,27),(802,1,28),(842,1,29),(843,1,30),(841,1,31),(840,1,32),(839,1,33),(810,1,34),(860,1,35),(859,1,36),(855,1,37),(854,1,38),(856,1,39),(857,1,40),(829,1,41),(828,1,42),(858,1,49),(870,1,50),(869,1,51),(818,1,52),(819,1,53),(820,1,54),(848,1,55),(804,1,56),(803,1,57),(835,1,58),(834,1,59),(832,1,60),(833,1,61),(831,1,62),(836,1,63),(838,1,64),(837,1,65),(846,1,66),(845,1,67),(868,1,68),(867,1,69),(830,1,70),(864,1,71),(799,1,72),(823,1,73),(824,1,74),(826,1,75),(814,1,76),(787,1,77),(789,1,78),(849,1,82),(847,1,83),(844,1,84),(866,1,85),(788,1,87),(793,1,88),(792,1,89),(816,1,90),(815,1,91),(801,1,92),(852,1,93),(853,1,94),(900,2,1),(897,2,2),(898,2,3),(896,2,5),(895,2,6),(905,2,7),(947,2,9),(944,2,10),(945,2,11),(943,2,12),(908,2,13),(906,2,14),(907,2,15),(922,2,16),(920,2,17),(894,2,18),(893,2,19),(917,2,20),(916,2,21),(940,2,23),(912,2,25),(910,2,26),(911,2,27),(902,2,28),(936,2,29),(937,2,30),(935,2,31),(934,2,32),(933,2,33),(909,2,34),(923,2,41),(901,2,43),(952,2,50),(951,2,51),(913,2,52),(914,2,53),(915,2,54),(938,2,55),(904,2,56),(903,2,57),(929,2,58),(928,2,59),(926,2,60),(927,2,61),(925,2,62),(930,2,63),(932,2,64),(931,2,65),(950,2,68),(949,2,69),(924,2,70),(946,2,71),(899,2,72),(918,2,73),(919,2,74),(921,2,75),(939,2,82),(948,2,85),(941,2,93),(942,2,94),(876,3,1),(884,3,16),(874,3,18),(873,3,19),(883,3,20),(882,3,21),(878,3,22),(889,3,23),(877,3,43),(875,3,44),(886,3,47),(892,3,50),(879,3,52),(880,3,53),(881,3,54),(887,3,55),(885,3,70),(871,3,77),(872,3,78),(888,3,82),(890,3,93),(891,3,94),(691,4,1),(695,4,16),(689,4,18),(698,4,23),(697,4,24),(694,4,28),(692,4,43),(690,4,44),(696,4,70),(786,4,83),(693,4,92),(954,5,1),(956,5,16),(953,5,18),(961,5,23),(955,5,25),(960,5,45),(958,5,46),(959,5,47),(957,5,70);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_roles`
--

LOCK TABLES `usuario_roles` WRITE;
/*!40000 ALTER TABLE `usuario_roles` DISABLE KEYS */;
INSERT INTO `usuario_roles` VALUES (1,1,1,'2026-09-10 01:42:46'),(3,2,2,'2026-09-17 04:20:36'),(6,3,2,'2026-10-12 16:13:55'),(10,4,4,'2026-10-16 21:56:07');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'V-00000001','Administrador','Sistema','admin@sgrd.com','$2y$10$JRFTfiOWeKn30Nn1DpiniO.kt90cMi4Mm.mUUk.BBjFy8bZnvr/gq',1,NULL,0,NULL,NULL,'2025-01-15 10:00:00','2026-11-10 17:09:12','{\"crono_mode\": \"manual\", \"tema\": \"light\"}'),(2,'V-16137930','Pastor Alberto','Mogollon Rivero','profpastorm07@gmail.com','$2y$10$UtEbQPuzdXyx49pad1LkyO1kZVtsRl935hHvR/8/IKZVAgcSFr/Fy',1,NULL,0,NULL,NULL,'2026-09-17 04:17:21',NULL,NULL),(3,'V-8591799','JOSE','Gregorio','jose.pirolo@sgrd.com','$2y$10$colp2sGvhjmtDkpsfYa.2e0.UH3pwEx0ERAxmsFAxasVhAeq0fSXi',1,NULL,0,NULL,NULL,'2026-10-12 15:47:38','2026-10-12 16:13:55','{\"crono_mode\": \"live\", \"tema\": \"light\"}'),(4,'V-34396688','Jose Daniel','GONZALEZ FANEITE','josedaniel@sgrd.com','$2y$10$9CN88SgxS008uUq8dNyqceXueCrH1BQ6ROty3wDzWq9RfIlxUouEa',1,NULL,0,NULL,NULL,'2026-10-16 20:45:28','2026-11-06 10:18:01','{\"crono_mode\": \"manual\", \"tema\": \"light\"}');
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

-- Dump completed on 2026-11-12  0:10:19
