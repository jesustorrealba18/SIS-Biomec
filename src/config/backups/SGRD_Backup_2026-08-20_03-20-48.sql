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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacion_carril`
--

LOCK TABLES `asignacion_carril` WRITE;
/*!40000 ALTER TABLE `asignacion_carril` DISABLE KEYS */;
INSERT INTO `asignacion_carril` VALUES (1,1,1,1,NULL,'2026-06-01','2026-12-31',NULL,0,'activa'),(2,2,1,2,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(3,3,1,3,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(4,1,2,3,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(5,2,2,1,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(6,3,2,2,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(7,1,3,2,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(8,2,3,3,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(9,3,3,1,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(10,4,4,4,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(11,5,4,5,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(12,4,5,4,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(13,5,5,5,NULL,'2026-06-01','2026-12-31',NULL,0,'activa'),(14,6,6,4,NULL,'2026-06-01','2026-12-31','2026-08-13 21:58:27',0,'completada'),(15,6,6,5,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(16,1,7,1,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(17,2,7,2,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(18,3,7,3,'2026-08-05','2026-06-01','2026-12-31',NULL,1,'activa'),(19,4,7,4,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(20,5,7,5,NULL,'2026-06-01','2026-12-31',NULL,1,'activa'),(21,3,10,3,'2026-07-28','2026-07-28','2026-08-28',NULL,0,'activa'),(22,3,10,10,'2026-07-30','2026-07-30','2026-08-30',NULL,0,'activa'),(23,5,11,12,'2026-07-22','2026-07-20','2026-08-30','2026-08-12 14:09:03',0,'completada'),(24,3,11,12,'2026-08-10','2026-08-05','2026-09-05',NULL,0,'activa'),(25,2,4,6,'2026-08-14','2026-08-12','2026-09-12',NULL,1,'activa'),(26,5,11,6,'2026-08-15','2026-08-10','2026-08-11','2026-08-13 22:00:58',0,'completada'),(27,4,5,7,'2026-08-20','2026-08-14','2026-09-14',NULL,1,'activa'),(28,4,4,8,'2026-09-10','2026-08-14','2026-09-14',NULL,1,'activa'),(29,3,1,13,NULL,'2026-08-18','2026-09-18',NULL,0,'activa'),(30,5,1,13,NULL,'2026-08-18','2026-09-18',NULL,1,'activa');
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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (1,1,2,1,1,'2026-06-12','2026-06-12 07:05:00','QR','Presente',NULL),(2,2,2,1,1,'2026-06-12','2026-06-12 07:06:00','QR','Presente',NULL),(3,3,2,1,1,'2026-06-12','2026-06-12 07:08:00','QR','Presente',NULL),(4,1,4,5,1,'2026-06-15','2026-06-15 07:02:00','QR','Presente',NULL),(5,2,4,5,1,'2026-06-15','2026-06-15 07:04:00','QR','Presente',NULL),(6,3,4,5,1,'2026-06-15','2026-06-15 07:12:00','QR','Tardanza','Llego tarde por transporte'),(7,1,5,2,1,'2026-06-16','2026-06-16 07:03:00','QR','Presente',NULL),(8,2,5,2,1,'2026-06-16','2026-06-16 07:05:00','QR','Presente',NULL),(9,3,5,2,1,'2026-06-16','2026-06-16 07:07:00','QR','Presente',NULL),(10,1,6,7,1,'2026-06-18','2026-06-18 07:01:00','QR','Presente',NULL),(11,2,6,7,1,'2026-06-18','2026-06-18 07:03:00','QR','Presente',NULL),(12,8,3,8,1,'2026-06-13','2026-06-13 07:04:00','QR','Presente',NULL),(13,9,3,8,1,'2026-06-13','2026-06-13 07:06:00','QR','Presente',NULL),(14,11,3,8,1,'2026-06-13','2026-06-13 07:10:00','QR','Tardanza',NULL),(15,8,4,10,1,'2026-06-15','2026-06-15 07:05:00','QR','Presente',NULL),(16,9,4,10,1,'2026-06-15','2026-06-15 07:07:00','QR','Presente',NULL),(17,8,5,5,1,'2026-06-16','2026-06-16 07:10:00','QR','Ausente',NULL),(18,9,5,5,1,'2026-06-16','2026-06-16 07:02:00','QR','Presente',NULL),(19,10,5,6,3,'2026-06-16','2026-06-16 07:04:00','Manual','Presente',NULL),(20,12,7,9,3,'2026-06-17','2026-06-17 07:03:00','QR','Presente',NULL),(21,13,7,9,3,'2026-06-17','2026-06-17 07:05:00','QR','Presente',NULL),(22,14,7,9,3,'2026-06-17','2026-06-17 07:08:00','QR','Presente',NULL),(23,15,7,9,3,'2026-06-17','2026-06-17 07:15:00','Manual','Justificado','Cita medica'),(24,1,9,2,1,'2026-06-22','2026-06-22 07:02:00','QR','Presente',NULL),(25,2,9,2,1,'2026-06-22','2026-06-22 07:04:00','QR','Presente',NULL),(26,3,9,2,1,'2026-06-22','2026-06-22 07:05:00','QR','Presente',NULL),(27,8,10,10,1,'2026-06-23','2026-06-23 07:01:00','QR','Presente',NULL),(28,9,10,10,1,'2026-06-23','2026-06-23 07:03:00','QR','Presente',NULL),(29,11,10,10,1,'2026-06-23','2026-06-23 07:06:00','QR','Presente',NULL),(30,1,10,5,1,'2026-06-23','2026-06-23 07:00:00','QR','Presente',NULL),(31,2,10,5,1,'2026-06-23','2026-06-23 07:02:00','QR','Presente',NULL),(32,10,11,9,3,'2026-06-24','2026-06-24 07:04:00','QR','Presente',NULL),(33,12,11,9,3,'2026-06-24','2026-06-24 07:06:00','QR','Presente',NULL),(34,13,11,9,3,'2026-06-24','2026-06-24 07:09:00','QR','Presente',NULL),(35,1,12,7,1,'2026-06-25','2026-06-25 07:03:00','QR','Presente',NULL),(36,2,12,7,1,'2026-06-25','2026-06-25 07:05:00','QR','Presente',NULL),(37,3,12,7,1,'2026-06-25','2026-06-25 07:10:00','Manual','Tardanza','Problemas de transporte'),(38,14,14,1,1,'2026-06-29','2026-06-29 07:02:00','QR','Presente',NULL),(39,15,14,1,1,'2026-06-29','2026-06-29 07:04:00','QR','Presente',NULL),(40,1,15,2,1,'2026-06-30','2026-06-30 07:01:00','QR','Presente',NULL),(41,2,15,2,1,'2026-06-30','2026-06-30 07:03:00','QR','Presente',NULL),(42,16,15,1,1,'2026-06-30','2026-06-30 07:05:00','QR','Presente',NULL),(43,17,15,1,1,'2026-06-30','2026-06-30 07:08:00','QR','Presente',NULL),(44,18,15,1,1,'2026-06-30','2026-06-30 07:12:00','Manual','Justificado','Certificado medico'),(45,19,15,1,1,'2026-06-30','2026-06-30 07:15:00','QR','Presente',NULL),(46,1,19,5,1,'2026-07-06','2026-07-06 07:02:00','QR','Presente',NULL),(47,2,19,5,1,'2026-07-06','2026-07-06 07:04:00','QR','Presente',NULL),(48,3,19,5,1,'2026-07-06','2026-07-06 07:06:00','QR','Presente',NULL),(49,14,20,11,1,'2026-07-07','2026-07-07 07:01:00','QR','Presente',NULL),(50,15,20,11,1,'2026-07-07','2026-07-07 07:03:00','QR','Presente',NULL),(51,16,20,11,1,'2026-07-07','2026-07-07 07:05:00','QR','Presente',NULL),(52,17,20,11,1,'2026-07-07','2026-07-07 07:08:00','QR','Presente',NULL),(53,8,21,10,1,'2026-07-07','2026-07-07 07:00:00','QR','Presente',NULL),(54,9,21,10,1,'2026-07-07','2026-07-07 07:02:00','QR','Presente',NULL),(55,11,21,10,1,'2026-07-07','2026-07-07 07:06:00','QR','Presente',NULL),(56,20,21,10,1,'2026-07-07','2026-07-07 07:09:00','QR','Presente',NULL),(57,23,21,10,1,'2026-07-07','2026-07-07 07:11:00','QR','Presente',NULL),(58,26,21,10,1,'2026-07-07','2026-07-07 07:13:00','QR','Tardanza',NULL),(59,1,25,2,1,'2026-07-13','2026-07-13 07:01:00','QR','Presente',NULL),(60,2,25,2,1,'2026-07-13','2026-07-13 07:03:00','QR','Presente',NULL),(61,3,25,2,1,'2026-07-13','2026-07-13 07:05:00','QR','Presente',NULL),(62,14,28,1,1,'2026-07-16','2026-07-16 07:02:00','QR','Presente',NULL),(63,15,28,1,1,'2026-07-16','2026-07-16 07:04:00','QR','Presente',NULL),(64,16,28,1,1,'2026-07-16','2026-07-16 07:06:00','QR','Presente',NULL),(65,19,28,1,1,'2026-07-16','2026-07-16 07:08:00','QR','Presente',NULL),(66,22,28,1,1,'2026-07-16','2026-07-16 07:10:00','QR','Presente',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aspectos_tecnicos`
--

LOCK TABLES `aspectos_tecnicos` WRITE;
/*!40000 ALTER TABLE `aspectos_tecnicos` DISABLE KEYS */;
INSERT INTO `aspectos_tecnicos` VALUES (1,'Salida','Posicion de salida, reaccion al toque y entrada al agua',1,'2026-06-14 14:56:15'),(2,'Bajo el agua','Dolphin kick, posicion del cuerpo y control de burbujas underwater',1,'2026-06-14 14:56:15'),(3,'Nado libre - Brazada','Entrada, agarre, tirada, empuje y recuperacion del brazo en crol',1,'2026-06-14 14:56:15'),(4,'Nado libre - Patada','Aleteo de piernas, frecuencia y amplitud del kick en crol',1,'2026-06-14 14:56:15'),(5,'Nado libre - Respiracion','Tecnica de giro lateral, sincronizacion con la brazada en crol',1,'2026-06-14 14:56:15'),(6,'Nado espalda - Brazada','Entrada, tirada y recuperacion del brazo en espalda',1,'2026-06-14 14:56:15'),(7,'Nado espalda - Patada','Aleteo de piernas y posicion del cuerpo en espalda',1,'2026-06-14 14:56:15'),(8,'Nado pecho - Brazada','Tirada simultanea, recuperacion y tiempo subacuatico',1,'2026-06-14 14:56:15'),(9,'Nado pecho - Patada','Patada de rana, sincronizacion con la brazada en pecho',1,'2026-06-14 14:56:15'),(10,'Mariposa - Brazada','Tirada simultanea, entrada del brazo y recuperacion en mariposa',1,'2026-06-14 14:56:15'),(11,'Mariposa - Patada','Patada de delfin, undulacion corporal y ritmo en mariposa',1,'2026-06-14 14:56:15'),(12,'Virajes','Giro volteado, impulso y salida del muro en flips y open turns',1,'2026-06-14 14:56:15'),(13,'Llegada','Toque de pared, timing y precision en los ultimos metros',1,'2026-06-14 14:56:15'),(14,'Posicion del cuerpo','Alineacion, rotacion y equilibrio general en el agua',1,'2026-06-14 14:56:15'),(15,'Coordinacion general','Sincronizacion de brazos, piernas y respiracion',1,'2026-06-14 14:56:15');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_datos_medicos`
--

LOCK TABLES `atleta_datos_medicos` WRITE;
/*!40000 ALTER TABLE `atleta_datos_medicos` DISABLE KEYS */;
INSERT INTO `atleta_datos_medicos` VALUES (1,5,'A-','abejas','ninguna','yohander','04245156664','Otro','ninguno','FED-45063','maximo'),(2,6,'A+','ninguna','Asma','Jose Maria','04245156664','Otro','ninguno','FED-0142','ninguno'),(4,8,'O+',NULL,NULL,'Carlos Rodriguez','04145551234','Padre','Seguros La Prevision','FED-52100',NULL),(5,9,'A+','Polvo',NULL,'Maria Garcia','04126667890','Madre','Seguros La Prevision','FED-52101','Acuatica Lara'),(6,10,'B+',NULL,'Asma leve','Antonio Hernandez','04167772345','Padre','Humano Vital','FED-52102',NULL),(7,11,'O-',NULL,NULL,'Luisa Martinez','04148889012','Madre','Humano Vital','FED-52103','Natacion Meridiano'),(8,12,'AB+','Penicilina',NULL,'Rafael Lopez','04249993456','Padre',NULL,'FED-52104',NULL),(9,13,'A-',NULL,NULL,'Ana Diaz','04121115678','Madre','Seguros La Prevision','FED-52105',NULL),(10,14,'B-',NULL,'Alergia estacional','Pedro Vargas','04162226789','Padre','Humano Vital','FED-52106','Club Acuatico Barquisimeto'),(11,15,'O+',NULL,NULL,'Carmen Rojas','04243337890','Madre',NULL,'FED-52107',NULL),(12,16,'A+','Cloro',NULL,'Jose Castillo','04144448901','Padre','Seguros La Prevision','FED-52108',NULL),(13,17,'AB-',NULL,NULL,'Yelitza Torres','04125559012','Madre','Humano Vital','FED-52109','Acuatica Lara'),(14,18,'O-',NULL,'Tendinitis rodilla derecha','Miguel Fuentes','04166660123','Padre',NULL,'FED-52110',NULL),(15,19,'B+',NULL,NULL,'Rosa Paredes','04247771234','Madre','Seguros La Prevision','FED-52111',NULL),(16,20,'A-','Dermatitis',NULL,'Victor Urbina','04148882345','Padre','Humano Vital','FED-52112',NULL),(17,21,'O+',NULL,NULL,'Nancy Colmenares','04129993456','Madre',NULL,'FED-52113','Club Tiburones'),(18,22,'AB+',NULL,NULL,'Alberto Rangel','04161114567','Padre','Seguros La Prevision','FED-52114',NULL),(19,23,'A+','Latex',NULL,'Carlos Rodriguez','04145551234','Padre',NULL,'FED-52115',NULL),(20,24,'O-',NULL,'Escoliosis leve','Antonio Hernandez','04167772345','Padre','Humano Vital','FED-52116',NULL),(21,25,'B-',NULL,NULL,'Luisa Martinez','04148889012','Madre',NULL,'FED-52117',NULL),(22,26,'A+',NULL,NULL,'Rafael Lopez','04249993456','Padre','Seguros La Prevision','FED-52118',NULL),(23,27,'O+','Ibuprofeno',NULL,'Ana Diaz','04121115678','Madre','Humano Vital','FED-52119',NULL),(24,1,'O+','amoxixilina','no posee','jose gregorio','04120560145','Padre','Miranda','FED-45063','maximo'),(25,28,'AB+','ninguna','ninguna','Emergencia','03646665','Hermano/a','sin','feb777','ninguno'),(26,29,'B-','Ninguna','Ninguna','Emergencia','03646665','Otro','sin','feb777','ninguno');
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atleta_representante`
--

LOCK TABLES `atleta_representante` WRITE;
/*!40000 ALTER TABLE `atleta_representante` DISABLE KEYS */;
INSERT INTO `atleta_representante` VALUES (1,1,1,0,NULL,0,NULL,0),(14,4,2,0,NULL,0,NULL,0),(15,3,2,0,NULL,0,NULL,0),(16,2,4,0,NULL,0,NULL,0),(17,8,5,1,'2026-07-09',1,'2026-07-09',1),(18,9,6,1,'2026-07-09',1,'2026-07-09',1),(19,10,7,1,'2026-07-09',0,NULL,1),(20,11,8,1,'2026-07-09',1,'2026-07-09',1),(21,12,9,0,NULL,0,NULL,0),(22,13,10,1,'2026-07-09',1,'2026-07-09',1),(23,14,11,1,'2026-07-09',1,'2026-07-09',1),(24,15,12,1,'2026-07-09',0,NULL,1),(25,16,13,1,'2026-07-09',1,'2026-07-09',1),(26,17,14,1,'2026-07-09',1,'2026-07-09',0),(27,18,15,0,NULL,0,NULL,1),(28,19,16,1,'2026-07-09',1,'2026-07-09',1),(29,20,5,1,'2026-07-09',1,'2026-07-09',1),(30,21,7,1,'2026-07-09',0,NULL,1),(31,22,8,1,'2026-07-09',1,'2026-07-09',1),(32,23,9,0,NULL,1,'2026-07-09',1),(33,24,10,1,'2026-07-09',1,'2026-07-09',0),(34,25,11,1,'2026-07-09',1,'2026-07-09',1),(35,26,12,0,NULL,1,'2026-07-09',1),(36,27,13,1,'2026-07-09',1,'2026-07-09',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atletas`
--

LOCK TABLES `atletas` WRITE;
/*!40000 ALTER TABLE `atletas` DISABLE KEYS */;
INSERT INTO `atletas` VALUES (1,'V-25854831','Jose Miguel','Pirolo Navarro','2016-05-10','M','Tamaca, Las Delicias','04120560145','josemiguel.pirolo@gmail.com',NULL,'2025-05-14','Activo',NULL,2,NULL,'2026-05-20 15:56:31','2026-07-12 23:46:36'),(2,'28425405','Jose Antonio','Pirolo Navarro','2017-05-24','M','Tamaca Las Delicias','04121112323','jose@gmsil.com',NULL,'2024-05-01','Activo',NULL,2,NULL,'2026-05-20 17:49:29',NULL),(3,'31536131','Joselin Paola','Pirolo Navarro','2017-05-10','M','Tamaca, Las Delicias','04120560145','jose@gmail.com',NULL,'2025-05-07','Activo',NULL,2,NULL,'2026-05-20 17:50:59',NULL),(4,'32296296','Francelys Adriana','Camacho Rivero','2017-05-01','F','MEtropolis','04121112323','jose@gmail.com',NULL,'2026-05-04','Inactivo',NULL,3,NULL,'2026-05-20 22:37:47','2026-06-12 08:30:56'),(5,'V-10777888','jesus','regalado','2000-10-10','M','caldera','04145050505','algo@ejemplo.com','assets/uploads/fotos/atleta_6a2bf20a9cc5a.jpg','2026-06-10','Retirado','2c9746923d30a6aa0b4b06b39c20ca65',5,3,'2026-06-12 07:48:26','2026-06-12 08:31:14'),(6,'V-28591971','Michael','Phepls','2002-02-10','M','barquisimeto','0412554566','mphelps@swiming.com','assets/uploads/fotos/atleta_6a3c9289cce75.jpg','2026-06-24','Activo','b4702a1ac6efce8c646fb514e9a46cfc',2,3,'2026-06-24 22:29:29',NULL),(8,'V-28901234','Santiago Andres','Rodriguez Garcia','2016-03-15','M','Av. 20 con Calle 31, Barquisimeto','04145551234','santiago.rodriguez@gmail.com',NULL,'2025-09-01','Activo',NULL,2,NULL,'2026-01-15 10:00:00',NULL),(9,'V-29012345','Valentina Sofia','Hernandez Martinez','2015-08-22','F','Calle 40 entre Av. 18 y 19, Barquisimeto','04126667890','valentina.hernandez@gmail.com',NULL,'2025-09-15','Activo',NULL,2,NULL,'2026-01-20 11:30:00',NULL),(10,'V-30123456','Sebastian Gabriel','Lopez Diaz','2014-01-10','M','Urbanizacion La Concordia, Barquisimeto','04167772345','sebastian.lopez@gmail.com',NULL,'2025-10-01','Activo',NULL,3,NULL,'2026-02-01 09:15:00',NULL),(11,'V-31234567','Camila Alejandra','Vargas Rojas','2013-06-18','F','Sector El Cardenal, Barquisimeto','04148889012','camila.vargas@gmail.com',NULL,'2025-10-15','Activo',NULL,3,NULL,'2026-02-10 14:00:00',NULL),(12,'V-32345678','Diego Emilio','Martinez Castillo','2012-11-05','M','Av. Intercomunal, Tamaca','04249993456','diego.martinez@gmail.com',NULL,'2026-01-10','Activo',NULL,3,NULL,'2026-02-15 08:45:00',NULL),(13,'V-33456789','Isabella Carolina','Fuentes Torres','2011-04-30','F','Urbanizacion Toroes, Barquisimeto','04121115678','isabella.fuentes@gmail.com',NULL,'2026-01-20','Activo',NULL,4,NULL,'2026-03-01 10:30:00',NULL),(14,'V-34567890','Mateo Alejandro','Castillo Paredes','2010-09-12','M','Calle 52 con Av. 30, Barquisimeto','04162226789','mateo.castillo@gmail.com',NULL,'2026-02-01','Activo',NULL,4,NULL,'2026-03-10 11:00:00',NULL),(15,'V-35678901','Sofia Valentina','Urbina Colmenares','2009-02-28','F','Av. Libertador con Calle 28, Barquisimeto','04243337890','sofia.urbina@gmail.com',NULL,'2026-02-15','Activo',NULL,5,NULL,'2026-03-15 13:00:00',NULL),(16,'V-36789012','Nicolas Eduardo','Rangel Bracho','2008-07-14','M','Barrio La Union, Cabudare','04144448901','nicolas.rangel@gmail.com',NULL,'2026-03-01','Activo',NULL,5,NULL,'2026-03-20 09:00:00',NULL),(17,'V-37890123','Mariana Isabel','Colmenares Rangel','2007-12-20','F','Urbanizacion Creatividad, Barquisimeto','04125559012','mariana.colmenares@gmail.com',NULL,'2026-03-10','Activo',NULL,6,NULL,'2026-04-01 10:00:00',NULL),(18,'V-38901234','Samuel David','Paredes Urdaneta','2016-05-08','M','Av. 19 con Calle 45, Barquisimeto','04166660123','samuel.paredes@gmail.com',NULL,'2026-03-15','Activo',NULL,2,NULL,'2026-04-10 14:30:00',NULL),(19,'V-39012345','Luciana Paola','Diaz Vargas','2014-10-25','F','Sector Buena Vista, Barquisimeto','04247771234','luciana.diaz@gmail.com',NULL,'2026-03-20','Activo',NULL,3,NULL,'2026-04-15 08:30:00',NULL),(20,'V-40123456','Thiago Sebastian','Vargas Moreno','2013-03-03','M','Calle 35 entre Av. 22 y 23, Barquisimeto','04148882345','thiago.vargas@gmail.com',NULL,'2026-04-01','Activo',NULL,3,NULL,'2026-04-20 11:15:00',NULL),(21,'V-41234567','Emma Victoria','Rojas Blanco','2012-08-17','F','Urbanizacion El Trigal, Barquisimeto','04129993456','emma.rojas@gmail.com',NULL,'2026-04-05','Activo',NULL,3,NULL,'2026-05-01 10:45:00',NULL),(22,'V-42345678','Daniel Antonio','Garcia Hernandez','2011-01-22','M','Av. 20 de Barquisimeto, Cabudare','04161114567','daniel.garcia@gmail.com',NULL,'2026-04-10','Activo',NULL,4,NULL,'2026-05-05 09:30:00',NULL),(23,'V-43456789','Olivia Cristina','Rodriguez Mendoza','2010-06-10','F','Av. 20 con Calle 31, Barquisimeto','04242225678','olivia.rodriguez@gmail.com',NULL,'2026-04-15','Activo',NULL,4,NULL,'2026-05-10 08:00:00',NULL),(24,'V-44567890','Pablo Andres','Hernandez Perez','2009-11-28','M','Calle 40 entre Av. 18 y 19, Barquisimeto','04143336789','pablo.hernandez@gmail.com',NULL,'2026-04-20','Activo',NULL,5,NULL,'2026-05-15 11:00:00',NULL),(25,'V-45678901','Ariana Sofia','Lopez Sanchez','2008-04-15','F','Sector El Cardenal, Barquisimeto','04124447890','ariana.lopez@gmail.com',NULL,'2026-05-01','Activo',NULL,5,NULL,'2026-05-20 10:00:00',NULL),(26,'V-46789012','Joaquin Emilio','Martinez Vargas','2007-09-05','M','Urbanizacion La Concordia, Barquisimeto','04165558901','joaquin.martinez@gmail.com',NULL,'2026-05-05','Activo',NULL,6,NULL,'2026-05-25 09:15:00',NULL),(27,'V-47890123','Elena Gabriela','Fuentes Castillo','2008-02-14','F','Av. Intercomunal, Tamaca','04246669012','elena.fuentes@gmail.com',NULL,'2026-05-10','Inactivo',NULL,5,NULL,'2026-05-30 14:00:00',NULL),(28,'V-7777777','Antonio Dimarco','Santiago de la Rosa','2000-09-09','M','URB. RAFAEL CALDERA II ETAPA AV 6 CASA N. 75','042345566','ejemplo@gmail.com',NULL,'2026-07-29','Activo','e54debfc2ee4bdca811b5372632c5036',4,3,'2026-07-29 14:59:50',NULL),(29,'E-23456788','Carolina Valentina','Rios Suarez','2026-08-05','F','URB. RAFAEL CALDERA II ETAPA AV 6 CASA N. 75','0326786537','correo@gmail.com','assets/uploads/fotos/atleta_6a738f1c7526d.jpg','2026-08-05','Activo','b23433496856a31a4660d6eb4c528436',2,3,'2026-08-05 15:29:32',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloques_horarios`
--

LOCK TABLES `bloques_horarios` WRITE;
/*!40000 ALTER TABLE `bloques_horarios` DISABLE KEYS */;
INSERT INTO `bloques_horarios` VALUES (1,'Lunes','07:00:00','09:00:00'),(2,'Martes','07:00:00','09:00:00'),(3,'Miercoles','07:00:00','09:00:00'),(4,'Jueves','07:00:00','09:00:00'),(5,'Viernes','07:00:00','09:00:00'),(6,'Sabado','09:00:00','11:00:00'),(7,'Lunes','16:00:00','18:00:00'),(8,'Martes','16:00:00','18:00:00'),(9,'Miercoles','16:00:00','18:00:00'),(10,'Jueves','16:00:00','18:00:00'),(11,'Viernes','16:00:00','18:00:00'),(12,'Miercoles','14:16:00','15:50:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carga_diaria`
--

LOCK TABLES `carga_diaria` WRITE;
/*!40000 ALTER TABLE `carga_diaria` DISABLE KEYS */;
INSERT INTO `carga_diaria` VALUES (1,1,'2026-06-12',85.00,NULL,540,3200,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-12 11:00:00'),(2,1,'2026-06-15',95.00,NULL,720,2900,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-15 11:00:00'),(3,1,'2026-06-16',110.00,NULL,630,4100,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 11:30:00'),(4,1,'2026-06-22',80.00,NULL,425,2900,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-22 11:00:00'),(5,1,'2026-06-23',100.00,NULL,NULL,4600,470.00,420.00,1.12,0.85,82.00,'Verde','2026-06-23 11:30:00'),(6,1,'2026-07-06',105.00,NULL,735,4200,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-06 11:30:00'),(7,1,'2026-07-13',90.00,NULL,510,2850,495.00,440.00,1.13,0.90,88.00,'Verde','2026-07-13 11:00:00'),(8,2,'2026-06-12',85.00,NULL,630,3200,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-12 11:05:00'),(9,2,'2026-06-15',95.00,NULL,630,2900,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-15 11:05:00'),(10,2,'2026-06-23',100.00,NULL,NULL,4600,480.00,415.00,1.16,0.88,85.00,'Verde','2026-06-23 11:35:00'),(11,3,'2026-06-12',70.00,NULL,450,3200,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-12 11:10:00'),(12,3,'2026-06-15',100.00,NULL,810,2900,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-15 11:10:00'),(13,3,'2026-06-23',85.00,NULL,NULL,4600,455.00,400.00,1.14,0.82,80.00,'Verde','2026-06-23 11:40:00'),(14,8,'2026-06-13',75.00,NULL,510,2700,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-13 11:00:00'),(15,8,'2026-06-15',80.00,NULL,NULL,2900,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-15 11:15:00'),(16,8,'2026-06-23',90.00,NULL,700,3600,420.00,380.00,1.11,0.78,75.00,'Verde','2026-06-23 11:45:00'),(17,9,'2026-06-13',78.00,NULL,595,2700,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-13 11:05:00'),(18,9,'2026-06-23',88.00,NULL,600,3600,435.00,390.00,1.12,0.80,78.00,'Verde','2026-06-23 11:50:00'),(19,10,'2026-06-17',72.00,NULL,540,2800,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-17 11:00:00'),(20,14,'2026-06-29',45.00,NULL,180,1500,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-29 11:00:00'),(21,14,'2026-07-07',105.00,NULL,720,3200,380.00,350.00,1.09,0.85,78.00,'Verde','2026-07-07 11:00:00'),(22,14,'2026-07-16',88.00,NULL,510,3100,420.00,370.00,1.14,0.88,85.00,'Verde','2026-07-16 11:00:00'),(23,15,'2026-06-29',50.00,NULL,240,1500,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-29 11:05:00'),(24,15,'2026-07-07',98.00,NULL,630,3200,390.00,355.00,1.10,0.83,76.00,'Verde','2026-07-07 11:05:00'),(25,16,'2026-06-30',65.00,NULL,810,2000,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-30 11:00:00'),(26,16,'2026-07-07',110.00,NULL,810,3200,450.00,400.00,1.13,0.92,90.00,'Verde','2026-07-07 11:10:00'),(27,17,'2026-06-30',70.00,NULL,NULL,2000,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-30 11:05:00'),(28,17,'2026-07-07',95.00,NULL,NULL,3200,430.00,385.00,1.12,0.87,82.00,'Verde','2026-07-07 11:15:00'),(29,22,'2026-07-16',92.00,NULL,680,3100,460.00,410.00,1.12,0.89,86.00,'Verde','2026-07-16 11:15:00'),(30,19,'2026-07-16',85.00,NULL,510,3100,410.00,375.00,1.09,0.82,76.00,'Verde','2026-07-16 11:20:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drills`
--

LOCK TABLES `drills` WRITE;
/*!40000 ALTER TABLE `drills` DISABLE KEYS */;
INSERT INTO `drills` VALUES (1,'Taper de Velocidad','Libre','Velocidad','velocidad maxima','10x100 metros libre','ir muy rapido descansar poco',10,'Avanzado','Ninguno',0,1,1,'2026-10-11 12:00:00'),(2,'Kick con tabla crol','Libre','Tecnico','patada','6x50 metros patada de crol con tabla','Mantener cadera alta, kick desde la cadera no las rodillas',300,'Basico','Tabla',0,1,1,'2026-03-01 08:00:00'),(3,'Pullboy brazada crol','Libre','Tecnico','brazada','8x50 metros crol con pullboy','Enfoque en la traccion del brazo, alto codo',400,'Intermedio','Pullboy',0,1,1,'2026-03-01 08:10:00'),(5,'Braza 2 golpes 1 brazada','Braza','Tecnico','sincronizacion','6x50 metros braza con ritmo 2:1','Dos patadas de rana por cada ciclo de brazada',300,'Intermedio','Ninguno',0,1,1,'2026-03-02 09:15:00'),(6,'Mariposa con aletas','Mariposa','Tecnico','patada de delfin','8x25 metros mariposa con aletas','Enfoque en el movimiento ondulatorio del cuerpo',200,'Intermedio','Aletas',0,1,1,'2026-03-03 10:00:00'),(7,'Sprint 25m crol','Libre','Velocidad','salida y velocidad','8x25 metros crol sprint','Salida explosiva, aceleracion maxima hasta la pared',200,'Avanzado','Ninguno',0,1,1,'2026-03-03 10:20:00'),(8,'Viraje flip crol','Libre','Tecnico','viraje','6x25 metros con viraje volteado','Acercarse a 5m, sumergir, girar e impulsionar del muro',150,'Intermedio','Ninguno',0,1,1,'2026-03-04 08:00:00'),(9,'Resistencia 200m libre','Libre','Resistencia','resistencia aerobica','4x200 metros libre Z2-Z3','Mantener ritmo constante, descanso 30 seg',800,'Avanzado','Ninguno',0,3,1,'2026-03-04 09:00:00'),(10,'Combinado 100m IM','Combinado','Coordinacion','transicion estilos','4x100 metros combinado','Mariposa-Espalda-Braza-Libre, foco en las transiciones',400,'Avanzado','Ninguno',0,3,1,'2026-03-05 08:00:00'),(11,'Respiracion bilateral','Libre','Tecnico','respiracion','8x25 metros crol respirando cada 3 brazadas','Rotacion completa, exhalacion bajo el agua',200,'Basico','Ninguno',0,1,1,'2026-03-05 10:00:00'),(12,'Salida desde bloque','Multi','Tecnico','salida','8x salidas desde el bloque','Posicion de track start, reaccion al silbato',0,'Intermedio','Ninguno',0,1,1,'2026-03-06 07:00:00'),(13,'Fuerza con paddle','Libre','Fuerza','fuerza tren superior','6x50 metros crol con paddles','Brazada con paddles, sentir la presion del agua',300,'Intermedio','Paddle',0,3,1,'2026-03-06 09:00:00'),(14,'Llegada a pared','Multi','Tecnico','llegada','8x25 metros sprint con llegada','No desacelerar, tocar pared a maxima velocidad',200,'Intermedio','Ninguno',0,1,1,'2026-03-07 07:30:00'),(15,'Aleteo subacueatico','Libre','Coordinacion','dolphin kick','6x25 metros bajo el agua con dolphin kick','Mantener posicion hidrodinamica, maximo 15m underwater',150,'Avanzado','Ninguno',0,3,1,'2026-03-07 09:30:00'),(16,'Patada espalda con tabla','Espalda','Tecnico','patada espalda','6x50 metros espalda patada con tabla en alto','Hombros rotados, kick alternado desde la cadera',300,'Basico','Tabla',0,1,1,'2026-03-08 08:00:00'),(17,'Mariposa undulacion','Mariposa','Tecnico','undulacion corporal','6x25 metros mariposa con un brazo','Enfoque en el movimiento de onda del cuerpo',150,'Basico','Ninguno',0,1,1,'2026-03-08 10:00:00'),(18,'Descanso activo','Multi','','recuperacion','200 metros espalda suave + movilidad articular','Nadar relajado, estirar hombros y espalda',200,'Basico','Ninguno',0,3,1,'2026-03-09 07:00:00'),(19,'Piramide de velocidad','Libre','Velocidad','progresion','50-100-150-200-150-100-50 metros crol','Incrementar intensidad hasta el 200, luego decrecer',800,'Avanzado','Ninguno',0,3,1,'2026-03-09 09:00:00'),(20,'Resistente con paracaidas','Libre','Fuerza','resistencia al avance','6x25 metros crol con paracaidas','Empujar fuerte, mantener alineacion',150,'Avanzado','Resistente',0,3,1,'2026-03-10 08:00:00'),(21,'Pullboy y aletas','Libre','Resistencia','volumen','10x100 metros crol con pullboy y aletas','Foco en brazada con piernas asistidas',1000,'Intermedio','Pullboy_Aletas',0,1,1,'2026-03-10 10:00:00'),(23,'Mariposa furiosa','Mariposa','Coordinacion','Mejorar las mariposas','Mejorar la coordinación y el uso de las mariposas','Realizar 5 brazadas de mariposa',50,'Basico','Ninguno',1,3,1,'2026-07-28 18:33:00');
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
  `id_usuario` int(3) NOT NULL COMMENT 'FK a sis_seguridad.usuarios',
  PRIMARY KEY (`id_entrenador`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador`
--

LOCK TABLES `entrenador` WRITE;
/*!40000 ALTER TABLE `entrenador` DISABLE KEYS */;
INSERT INTO `entrenador` VALUES (3,'8591799','JOSEFINA','Pirolo','1969-07-12','F','josefinavarro@gmail.com','04120560145','Carrera 5 Entre Calles 6 Y 7 Tamaca','',3),(4,'28591974','jesus','david','2000-10-10','M','ejemplo@correo.com','04122222323','santa isabel','',3),(5,'30948948','Heliana','Garcia','2000-04-12','F','eliana@gmail.com','04239394784','Calle 6 con Carrera 7B','',3),(6,'30768900','Pedro Antonio','Jimenez Suarez','2000-02-23','M','pedrojimenezs@gmail.com','04124686397','Calle 5 San Francisco','',3),(7,'23564790','Rosa Valentina','Suarez ','1998-03-15','F','delarosa@gmail.com','04124758865','Santa Rosa ','',3),(8,'23574648','Piu Antonio','Siu','2000-08-05','M','correo@gmail.com','04124402959','Calle 5 con carrera 7 centro','',3),(10,'30759776','Veronica','Villamizar','2004-04-10','F','vero.cvv10@gmail.com','04165501950','San Francisco','',3);
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador_asignacion`
--

LOCK TABLES `entrenador_asignacion` WRITE;
/*!40000 ALTER TABLE `entrenador_asignacion` DISABLE KEYS */;
INSERT INTO `entrenador_asignacion` VALUES (25,1,1,'2025-05-14'),(26,1,2,'2025-05-01'),(27,1,3,'2025-05-07'),(28,1,8,'2026-01-15'),(29,1,9,'2026-01-20'),(30,1,11,'2026-02-10'),(31,1,20,'2026-04-15'),(32,3,10,'2026-02-01'),(33,3,12,'2026-02-15'),(34,3,13,'2026-03-01'),(35,3,21,'2026-04-05'),(36,3,24,'2026-04-20'),(37,3,25,'2026-04-25'),(38,1,14,'2026-03-10'),(39,1,15,'2026-03-15'),(40,1,16,'2026-03-20'),(41,1,19,'2026-04-10'),(42,1,22,'2026-04-15'),(43,3,17,'2026-04-01'),(44,3,18,'2026-04-05'),(45,3,6,'2026-06-24'),(46,1,23,'2026-04-20'),(47,3,26,'2026-05-01'),(48,3,27,'2026-05-05');
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
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento_inscripcion`
--

LOCK TABLES `evento_inscripcion` WRITE;
/*!40000 ALTER TABLE `evento_inscripcion` DISABLE KEYS */;
INSERT INTO `evento_inscripcion` VALUES (1,1,5,'2026-06-12 07:53:24'),(39,1,8,'2026-06-20 10:30:00'),(40,1,9,'2026-06-20 10:35:00'),(41,1,11,'2026-06-20 10:40:00'),(42,1,14,'2026-06-20 10:45:00'),(43,1,15,'2026-06-20 10:50:00'),(44,1,16,'2026-06-20 10:55:00'),(45,1,17,'2026-06-20 11:00:00'),(46,1,22,'2026-06-20 11:05:00'),(47,1,6,'2026-06-20 11:10:00'),(48,2,5,'2026-06-18 18:40:00'),(49,2,6,'2026-06-18 18:45:00'),(50,2,1,'2026-06-18 18:50:00'),(51,2,2,'2026-06-18 18:55:00'),(52,2,14,'2026-06-18 19:00:00'),(53,2,15,'2026-06-18 19:05:00'),(54,2,16,'2026-06-18 19:10:00'),(55,2,22,'2026-06-18 19:15:00'),(56,2,17,'2026-06-18 19:20:00'),(57,3,8,'2026-07-02 09:00:00'),(58,3,9,'2026-07-02 09:05:00'),(59,3,10,'2026-07-02 09:10:00'),(60,3,11,'2026-07-02 09:15:00'),(61,3,12,'2026-07-02 09:20:00'),(62,3,13,'2026-07-02 09:25:00'),(63,3,14,'2026-07-02 09:30:00'),(64,3,15,'2026-07-02 09:35:00'),(65,3,16,'2026-07-02 09:40:00'),(66,3,17,'2026-07-02 09:45:00'),(67,3,18,'2026-07-02 09:50:00'),(68,3,19,'2026-07-02 09:55:00'),(69,3,22,'2026-07-02 10:00:00'),(70,4,14,'2026-07-06 10:30:00'),(71,4,15,'2026-07-06 10:35:00'),(72,4,16,'2026-07-06 10:40:00'),(73,4,17,'2026-07-06 10:45:00'),(74,4,22,'2026-07-06 10:50:00'),(75,4,6,'2026-07-06 10:55:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,'Gala Regional Miranda 2026','2026-06-12','2026-06-18','Complejo acuatico miranda','Regional','A','FEVEDA','Planificado','valida para buscar marcas al nacional','2026-06-12 07:52:17'),(2,'Nacional copa Feveda 2026','2026-06-18','2026-06-20','Complejo de piscinas Bolivarianas','Nacional','A','FEVEDA','Planificado','Copa nacional feveda, buscando marcas para el panamericano 2027','2026-06-14 18:37:18'),(6,'Selectivo Pre-Panamericano','2026-08-10','2026-08-14','Complejo Acuatico de Valencia','Nacional','A','FEVEDA','Planificado','Clasificatorio para el Panamericano 2027','2026-06-20 10:00:00'),(7,'Control de Marcas Interno','2026-07-20','2026-07-20','Piscina Olimpica de Barquisimeto','Control',NULL,'Club Furia Criolla','Planificado','Control de tiempos para evaluar progreso del macrociclo','2026-07-01 09:00:00'),(8,'Gala Regional Lara 2026','2026-09-15','2026-09-18','Complejo Acuatico Metropolis','Regional','B','FEVEDA','Planificado','Gala regional de cierre de temporada','2026-07-05 14:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_periodizacion`
--

LOCK TABLES `fases_periodizacion` WRITE;
/*!40000 ALTER TABLE `fases_periodizacion` DISABLE KEYS */;
INSERT INTO `fases_periodizacion` VALUES (1,1,'Acumulacion',1,6,'2026-06-12','2026-07-23',75.00,'Z1-Z3','#10b981'),(2,1,'Deload',4,4,'2026-07-03','2026-07-09',40.00,'Z1-Z2','#6b7280'),(3,1,'Transmutacion',7,9,'2026-07-24','2026-08-13',50.00,'Z3-Z4','#f59e0b'),(4,1,'Realizacion',10,11,'2026-08-14','2026-08-27',30.00,'Z4-Z5','#ef4444'),(8,6,'Acumulacion',1,6,'2026-07-01','2026-08-11',75.00,'Z1-Z3','#10b981'),(9,6,'Deload',4,4,'2026-07-22','2026-07-28',40.00,'Z1-Z2','#6b7280'),(10,6,'Transmutacion',7,9,'2026-08-12','2026-09-01',50.00,'Z3-Z4','#f59e0b'),(11,6,'Realizacion',10,12,'2026-09-02','2026-09-20',30.00,'Z4-Z5','#ef4444'),(12,2,'Acumulacion',1,3,'2026-07-16','2026-08-05',75.00,'Z1-Z3','#10b981'),(13,2,'Transmutacion',4,5,'2026-08-06','2026-08-19',50.00,'Z3-Z4','#f59e0b'),(14,2,'Realizacion',6,7,'2026-08-20','2026-08-31',30.00,'Z4-Z5','#ef4444');
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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_atleta`
--

LOCK TABLES `grupo_atleta` WRITE;
/*!40000 ALTER TABLE `grupo_atleta` DISABLE KEYS */;
INSERT INTO `grupo_atleta` VALUES (1,1,1,'2025-05-14'),(2,1,2,'2025-05-01'),(3,1,3,'2025-05-07'),(4,2,8,'2026-01-15'),(5,2,9,'2026-01-20'),(6,2,11,'2026-02-10'),(7,2,20,'2026-04-15'),(8,2,23,'2026-04-20'),(9,2,26,'2026-05-01'),(10,3,10,'2026-02-01'),(11,3,12,'2026-02-15'),(12,3,13,'2026-03-01'),(13,3,21,'2026-04-05'),(14,3,24,'2026-04-20'),(15,3,25,'2026-04-25'),(16,3,27,'2026-05-05'),(17,4,14,'2026-03-10'),(18,4,15,'2026-03-15'),(19,4,16,'2026-03-20'),(20,4,19,'2026-04-10'),(21,4,22,'2026-04-15'),(22,5,17,'2026-04-01'),(23,5,18,'2026-04-05'),(24,5,6,'2026-06-24'),(25,5,26,'2026-05-01'),(26,5,27,'2026-05-05'),(27,6,2,'2026-07-26'),(28,6,1,'2026-07-26'),(29,6,3,'2026-07-26'),(30,7,28,'2026-07-29'),(31,13,29,'2026-08-05'),(32,12,29,'2026-08-12');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_entrenador`
--

LOCK TABLES `grupo_entrenador` WRITE;
/*!40000 ALTER TABLE `grupo_entrenador` DISABLE KEYS */;
INSERT INTO `grupo_entrenador` VALUES (1,1,3,'2025-01-01',NULL),(2,2,3,'2026-01-01',NULL),(3,3,4,'2026-02-01',NULL),(4,4,3,'2026-03-01',NULL),(5,5,4,'2026-04-01',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_entrenamiento`
--

LOCK TABLES `grupos_entrenamiento` WRITE;
/*!40000 ALTER TABLE `grupos_entrenamiento` DISABLE KEYS */;
INSERT INTO `grupos_entrenamiento` VALUES (1,'Furia criolla','jsjsjsjsjsj',1,NULL,0),(2,'Tiburones Dorados','Grupo infantil nivel intermedio, enfocado en tecnica de crol y espalda',1,3,1),(3,'Delfines Veloces','Grupo juvenil avanzado, preparacion competitiva regional',1,4,1),(4,'Aguas Profundas','Grupo maxima categoria, entrenamiento de alto rendimiento',3,3,1),(5,'Equipo 34','Equipo para Categoria Infantil',NULL,4,1),(6,'Equipo juvenil A','xssjsjsjs',NULL,5,1),(7,'Equipo dinamita','Equipo jovenes de 14',NULL,6,1),(8,'Equipo Juvenil B','',NULL,4,1),(9,'Dinamita pura','',NULL,4,1),(10,'Suave Suavecitto','',NULL,3,1),(11,'Pura sazon','',NULL,6,0),(12,'pado b','Pura diversion',NULL,3,1),(13,'Equipo Infatil B','Equipo profesional infantil B',NULL,10,1),(14,'Equipo chichi','Equipo Infatil A',NULL,7,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesiones`
--

LOCK TABLES `lesiones` WRITE;
/*!40000 ALTER TABLE `lesiones` DISABLE KEYS */;
INSERT INTO `lesiones` VALUES (2,3,'Pie','Izquierdo','Sobreuso',8,'pie dolorido','hielo','2026-06-11','2026-06-12','EnRehabilitacion','jose antonio','sdgsdsdf','2026-06-11 07:57:52','2026-06-11 13:33:35',1,NULL),(3,2,'Hombro','Izquierdo','Sobreuso',3,'contractura','hielo','2026-06-12','2026-06-14','Activa','jose antonio','adfasd','2026-06-12 01:05:51',NULL,1,NULL),(4,2,'Codo','Derecho','Sobreuso',4,'dolor de codo','hielo','2026-06-10','2026-06-12','Activa','josea ntonio','sfasd','2026-06-12 02:14:28',NULL,1,NULL),(5,8,'Hombro','Derecho','Sobreuso',5,'Tendinitis del manguito rotador','Reposo relativo, hielo, fisioterapia 3x/semana','2026-06-20','2026-07-15','EnRehabilitacion','Dr. Miguel Alvarez','Revisar en 2 semanas','2026-06-20 10:00:00','2026-06-25 08:00:00',1,NULL),(6,10,'Rodilla','Bilateral','Sobreuso',4,'Dolor patelar','Ejercicios de cuadriceps, hielo post-sesion','2026-06-25','2026-07-10','EnRehabilitacion','Dr. Luis Ferrer',NULL,'2026-06-25 14:00:00',NULL,1,NULL),(7,14,'Espalda','','Aguda',6,'Lumbalgia aguda','Antiinflamatorios, reposo 3 dias, estiramientos','2026-06-28','2026-07-05','Recuperada','Dr. Carlos Mendez','Originada por mala tecnica en salidas','2026-06-28 09:00:00','2026-07-06 10:00:00',1,NULL),(8,17,'Tobillo','Derecho','Aguda',3,'Esguince grado I','Hielo, vendaje funcional, reposo relativo','2026-07-01','2026-07-08','Recuperada','Dr. Pedro Gomez','Tropezo en el deck','2026-07-01 11:00:00','2026-07-09 08:00:00',1,NULL),(9,9,'Codo','Izquierdo','Sobreuso',4,'Epicondilitis','Fisioterapia, modificar tecnica de brazada','2026-07-03','2026-07-20','Activa','Dr. Ana Rojas','Verificar si necesita paracaidas diferente','2026-07-03 15:00:00',NULL,1,NULL),(10,16,'Hombro','Izquierdo','Recidiva',7,'Tendinitis recurrente manguito rotador','Fisioterapia intensiva, evaluar carga semanal','2026-07-05','2026-07-25','Activa','Dr. Miguel Alvarez','Tercer episodio en el ano','2026-07-05 09:00:00',NULL,1,NULL),(11,22,'Muslo','Izquierdo','Aguda',5,'Distension del recto femoral','Crioterapia, reposo activo, fortalecimiento progresivo','2026-07-08','2026-07-22','EnRehabilitacion','Dr. Luis Ferrer','Ocurrio durante sprint 25m','2026-07-08 10:00:00','2026-07-12 08:00:00',1,NULL),(12,19,'Gemelo','Derecho','Sobreuso',3,'Contractura del gemelo','Masaje deportivo, estiramientos, hielo','2026-07-10','2026-07-15','Activa','Fisioterapeuta Maria Luz','Leve molestia','2026-07-10 13:00:00',NULL,1,NULL),(13,5,'Hombro','Izquierdo','Sobreuso',3,'dadadsdasdaa','asdasdad','2026-07-23','2026-07-23','Activa','asdasd','adsdasda','2026-07-23 19:12:24',NULL,1,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `macrociclos`
--

LOCK TABLES `macrociclos` WRITE;
/*!40000 ALTER TABLE `macrociclos` DISABLE KEYS */;
INSERT INTO `macrociclos` VALUES (1,1,1,NULL,'2026-06-12','2026-08-27',NULL,'Planificado'),(2,1,1,'prueba','2026-07-16','2026-08-31',NULL,'Planificado'),(6,2,2,'Preparacion Gala Regional','2026-07-01','2026-09-20',7,'En Progreso'),(7,2,4,'Preparacion Selectivo','2026-07-01','2026-08-27',3,'En Progreso'),(8,2,5,'Preparacion Maxima','2026-07-01','2026-09-30',3,'Planificado');
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
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,1,NULL,NULL,'Libre',50,'50m',65.00,NULL,NULL,'Control',1,'2026-05-21','','2026-05-21 18:22:27','Activo',NULL),(2,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,'Control',1,'2026-05-21','presento quejas','2026-05-21 18:31:46','Inactivo','prueba'),(3,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,'Control',0,'2026-05-21','calambre','2026-05-21 20:11:20','Activo',NULL),(4,1,NULL,NULL,'Libre',100,'50m',240.00,NULL,NULL,'Control',1,'2026-05-11','','2026-05-21 20:13:17','Activo',NULL),(5,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,'Control',0,'2026-05-12','','2026-05-21 20:16:21','Activo',NULL),(6,1,NULL,NULL,'Espalda',50,'50m',120.00,NULL,NULL,'Control',1,'2026-05-14','','2026-05-23 11:57:30','Activo',NULL),(7,4,NULL,NULL,'Espalda',100,'50m',120.00,0.50,0.60,'Control',1,'2026-05-18','Hola','2026-05-31 01:00:43','Activo',NULL),(8,3,NULL,NULL,'Espalda',50,'50m',180.00,0.70,0.50,'Control',1,'2026-05-18','nada','2026-05-31 09:27:44','Activo',NULL),(9,3,NULL,NULL,'Braza',50,'50m',180.00,40.00,50.00,'Control',1,'2026-05-19','nada','2026-05-31 09:48:55','Activo',NULL),(10,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,'Control',1,'2026-06-08',NULL,'2026-06-08 23:24:00','Activo',NULL),(11,1,4,NULL,'Libre',50,'50m',58.50,0.65,NULL,'Control',1,'2026-06-15',NULL,'2026-06-15 10:00:00','Activo',NULL),(12,1,5,NULL,'Libre',200,'50m',230.00,0.60,0.80,'Control',1,'2026-06-16',NULL,'2026-06-16 10:00:00','Activo',NULL),(13,1,9,NULL,'Espalda',50,'50m',110.00,NULL,NULL,'Control',1,'2026-06-22',NULL,'2026-06-22 10:00:00','Activo',NULL),(14,1,10,NULL,'Libre',100,'50m',115.00,0.62,0.75,'Control',1,'2026-06-23','Buen tiempo','2026-06-23 10:00:00','Activo',NULL),(15,1,19,NULL,'Braza',50,'50m',115.00,0.55,NULL,'Control',0,'2026-07-06',NULL,'2026-07-06 10:00:00','Activo',NULL),(16,1,25,NULL,'Libre',50,'50m',56.80,0.58,NULL,'Control',1,'2026-07-13','Nuevo PB!','2026-07-13 10:00:00','Activo',NULL),(17,2,4,NULL,'Libre',50,'50m',62.00,0.70,NULL,'Control',1,'2026-06-15',NULL,'2026-06-15 10:05:00','Activo',NULL),(18,2,5,NULL,'Libre',200,'50m',245.00,0.68,0.90,'Control',1,'2026-06-16',NULL,'2026-06-16 10:05:00','Activo',NULL),(19,2,9,NULL,'Libre',50,'50m',63.50,NULL,NULL,'Control',0,'2026-06-22',NULL,'2026-06-22 10:05:00','Activo',NULL),(20,2,10,NULL,'Espalda',100,'50m',125.00,0.72,0.85,'Control',1,'2026-06-23',NULL,'2026-06-23 10:05:00','Activo',NULL),(21,3,4,NULL,'Libre',50,'50m',66.00,0.62,NULL,'Control',1,'2026-06-15',NULL,'2026-06-15 10:10:00','Activo',NULL),(22,3,5,NULL,'Libre',200,'50m',260.00,0.60,0.88,'Control',0,'2026-06-16',NULL,'2026-06-16 10:10:00','Activo',NULL),(23,3,9,NULL,'Braza',50,'50m',140.00,NULL,NULL,'Control',1,'2026-06-22',NULL,'2026-06-22 10:10:00','Activo',NULL),(24,8,3,NULL,'Libre',50,'50m',48.00,0.55,NULL,'Control',1,'2026-06-13','Buen tiempo para su edad','2026-06-13 10:00:00','Activo',NULL),(25,8,4,NULL,'Libre',50,'50m',47.20,0.52,NULL,'Control',1,'2026-06-15','Mejoro','2026-06-15 10:10:00','Activo',NULL),(26,8,10,NULL,'Espalda',50,'50m',52.00,0.58,NULL,'Control',1,'2026-06-23',NULL,'2026-06-23 10:10:00','Activo',NULL),(27,8,19,NULL,'Libre',100,'50m',105.00,0.55,0.70,'Control',1,'2026-07-06',NULL,'2026-07-06 10:10:00','Activo',NULL),(28,9,3,NULL,'Libre',50,'50m',55.00,0.60,NULL,'Control',1,'2026-06-13',NULL,'2026-06-13 10:05:00','Activo',NULL),(29,9,4,NULL,'Mariposa',50,'50m',65.00,0.62,NULL,'Control',1,'2026-06-15','Primera vez mariposa timed','2026-06-15 10:15:00','Activo',NULL),(30,9,10,NULL,'Braza',100,'50m',130.00,0.58,0.80,'Control',1,'2026-06-23',NULL,'2026-06-23 10:15:00','Activo',NULL),(31,10,7,NULL,'Libre',100,'50m',110.00,0.50,0.65,'Control',1,'2026-06-17',NULL,'2026-06-17 10:00:00','Activo',NULL),(32,10,11,NULL,'Espalda',50,'50m',95.00,NULL,NULL,'Control',1,'2026-06-24',NULL,'2026-06-24 10:00:00','Activo',NULL),(33,10,22,NULL,'Combinado',100,'50m',140.00,0.55,0.78,'Control',1,'2026-07-07',NULL,'2026-07-07 10:00:00','Activo',NULL),(34,12,7,NULL,'Libre',50,'50m',90.00,0.65,NULL,'Control',1,'2026-06-17',NULL,'2026-06-17 10:05:00','Activo',NULL),(35,12,11,NULL,'Libre',100,'50m',180.00,0.60,0.85,'Control',0,'2026-06-24',NULL,'2026-06-24 10:05:00','Activo',NULL),(36,13,7,NULL,'Mariposa',50,'50m',75.00,0.58,NULL,'Control',1,'2026-06-17',NULL,'2026-06-17 10:10:00','Activo',NULL),(37,13,11,NULL,'Libre',200,'50m',230.00,0.55,0.80,'Control',1,'2026-06-24',NULL,'2026-06-24 10:10:00','Activo',NULL),(38,14,14,NULL,'Libre',100,'50m',85.00,0.48,0.60,'Control',1,'2026-06-29',NULL,'2026-06-29 10:00:00','Activo',NULL),(39,14,20,NULL,'Libre',50,'50m',38.00,0.45,NULL,'Control',1,'2026-07-07','Excelente','2026-07-07 10:00:00','Activo',NULL),(40,14,28,NULL,'Espalda',100,'50m',82.00,0.50,0.65,'Control',1,'2026-07-16',NULL,'2026-07-16 10:00:00','Activo',NULL),(41,15,14,NULL,'Libre',50,'50m',42.00,0.52,NULL,'Control',1,'2026-06-29',NULL,'2026-06-29 10:05:00','Activo',NULL),(42,15,20,NULL,'Braza',50,'50m',70.00,0.55,NULL,'Control',1,'2026-07-07',NULL,'2026-07-07 10:05:00','Activo',NULL),(43,15,28,NULL,'Mariposa',100,'50m',100.00,0.48,0.70,'Control',1,'2026-07-16',NULL,'2026-07-16 10:05:00','Activo',NULL),(44,16,15,NULL,'Combinado',200,'50m',190.00,0.50,0.72,'Control',1,'2026-06-30',NULL,'2026-06-30 10:00:00','Activo',NULL),(45,16,20,NULL,'Libre',100,'50m',78.00,0.47,0.58,'Control',1,'2026-07-07',NULL,'2026-07-07 10:10:00','Activo',NULL),(46,17,15,NULL,'Libre',50,'50m',40.50,0.46,NULL,'Control',1,'2026-06-30','Rapido','2026-06-30 10:05:00','Activo',NULL),(47,17,20,NULL,'Espalda',50,'50m',44.00,0.50,NULL,'Control',0,'2026-07-07',NULL,'2026-07-07 10:15:00','Activo',NULL),(48,18,15,NULL,'Libre',100,'50m',82.00,0.52,0.68,'Control',1,'2026-06-30',NULL,'2026-06-30 10:10:00','Activo',NULL),(49,19,15,NULL,'Braza',50,'50m',60.00,0.55,NULL,'Control',1,'2026-06-30',NULL,'2026-06-30 10:15:00','Activo',NULL),(50,19,28,NULL,'Libre',200,'50m',165.00,0.48,0.62,'Control',1,'2026-07-16',NULL,'2026-07-16 10:10:00','Activo',NULL),(51,20,21,NULL,'Libre',50,'50m',75.00,0.60,NULL,'Control',1,'2026-07-07',NULL,'2026-07-07 10:20:00','Activo',NULL),(52,22,28,NULL,'Libre',50,'50m',42.50,0.49,NULL,'Control',1,'2026-07-16',NULL,'2026-07-16 10:15:00','Activo',NULL),(53,22,NULL,1,'Libre',50,'50m',41.00,0.48,NULL,'Regional',1,'2026-06-14','Competencia regional','2026-06-14 15:00:00','Activo',NULL),(54,22,NULL,1,'Mariposa',50,'50m',55.00,0.50,NULL,'Regional',1,'2026-06-14','Segundo lugar','2026-06-14 16:00:00','Activo',NULL),(55,22,NULL,2,'Libre',100,'50m',82.00,0.47,0.60,'Nacional',1,'2026-06-18','Final A','2026-06-18 14:00:00','Activo',NULL),(56,22,NULL,2,'Combinado',200,'50m',170.00,0.50,0.72,'Nacional',0,'2026-06-19','Cansado en braza','2026-06-19 10:00:00','Activo',NULL),(57,14,NULL,1,'Libre',50,'50m',36.50,0.44,NULL,'Regional',1,'2026-06-14','Primer lugar','2026-06-14 14:00:00','Activo',NULL),(58,14,NULL,2,'Espalda',100,'50m',78.00,0.46,0.58,'Nacional',1,'2026-06-18','PB en nacional','2026-06-18 16:00:00','Activo',NULL),(59,6,NULL,1,'Libre',100,'50m',90.00,0.50,0.65,'Regional',1,'2026-06-14',NULL,'2026-06-14 17:00:00','Activo',NULL),(60,6,NULL,2,'Libre',50,'50m',38.00,0.48,NULL,'Nacional',1,'2026-06-18','Semifinal','2026-06-18 18:00:00','Activo',NULL),(61,11,3,NULL,'Mariposa',50,'50m',68.00,0.65,NULL,'Control',0,'2026-06-13',NULL,'2026-06-13 10:15:00','Activo',NULL),(62,11,NULL,1,'Braza',50,'50m',72.00,0.60,NULL,'Regional',1,'2026-06-15',NULL,'2026-06-15 12:00:00','Activo',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_splits`
--

LOCK TABLES `marcas_splits` WRITE;
/*!40000 ALTER TABLE `marcas_splits` DISABLE KEYS */;
INSERT INTO `marcas_splits` VALUES (1,1,1,25,32.50),(2,1,2,50,32.50),(3,2,1,25,28.00),(4,2,2,50,32.00),(5,3,1,25,48.00),(6,3,2,50,72.00),(7,4,1,25,60.00),(8,4,2,50,60.00),(9,4,3,75,60.00),(10,4,4,100,60.00),(11,5,1,25,60.00),(12,5,2,50,60.00),(13,6,1,25,60.00),(14,6,2,50,60.00),(15,9,1,25,130.00),(16,9,2,50,50.00),(17,10,1,25,25.00),(18,10,2,50,35.00),(19,11,1,25,28.00),(20,11,2,50,30.50),(21,12,1,50,112.00),(22,12,2,100,118.00),(23,13,1,25,32.50),(24,13,2,50,33.50),(25,14,1,25,23.50),(26,14,2,50,24.50),(27,15,1,25,26.50),(28,15,2,50,28.50),(29,19,1,25,52.00),(30,19,2,50,58.00),(31,20,1,50,52.00),(32,20,2,100,58.00),(33,21,1,50,55.00),(34,21,2,100,55.00),(35,23,1,25,22.00),(36,23,2,50,23.00),(37,24,1,50,40.00),(38,24,2,100,45.00),(39,25,1,50,37.00),(40,25,2,100,43.00),(41,26,1,50,38.50),(42,26,2,100,41.50),(43,27,1,50,34.00),(44,27,2,100,44.00),(45,28,1,50,42.00),(46,28,2,100,48.00),(47,29,1,50,35.00),(48,29,2,100,43.00),(49,29,3,150,35.00),(50,29,4,200,42.00),(51,30,1,50,36.00),(52,30,2,100,42.00),(53,31,1,25,20.00),(54,31,2,50,22.00),(55,31,3,75,20.50),(56,31,4,100,21.50),(57,33,1,50,43.00),(58,33,2,100,39.00),(59,35,1,25,18.50),(60,35,2,50,20.00),(61,36,1,50,36.00),(62,36,2,100,42.00),(63,37,1,25,19.00),(64,37,2,50,23.00),(65,37,3,75,19.50),(66,37,4,100,21.00),(67,39,1,50,38.00),(68,39,2,100,44.00),(69,40,1,25,18.00),(70,40,2,50,20.00),(71,41,1,25,19.50),(72,41,2,50,24.00),(73,41,3,75,19.00),(74,41,4,100,23.50),(75,42,1,50,40.00),(76,42,2,100,42.00),(77,43,1,25,18.00),(78,43,2,50,19.00),(79,43,3,75,19.00),(80,43,4,100,21.00),(81,46,1,25,18.50),(82,46,2,50,19.50),(83,47,1,25,19.00),(84,47,2,50,19.00),(85,48,1,25,20.50),(86,48,2,50,22.50),(87,48,3,75,20.50),(88,48,4,100,22.00),(89,49,1,50,40.00),(90,49,2,100,44.00),(91,50,1,25,18.00),(92,50,2,50,20.00),(93,52,1,50,38.50),(94,52,2,100,43.50);
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas_swolf`
--

LOCK TABLES `marcas_swolf` WRITE;
/*!40000 ALTER TABLE `marcas_swolf` DISABLE KEYS */;
INSERT INTO `marcas_swolf` VALUES (1,2,32,92),(2,3,43,163),(3,4,111,231),(4,5,45,165),(5,6,44,164),(6,9,80,260),(7,10,15,75),(8,11,35,93),(9,12,120,230),(10,14,28,76),(11,15,30,85),(12,19,48,158),(13,20,45,130),(14,21,42,127),(15,23,22,70),(16,24,65,165),(17,25,40,120),(18,26,38,118),(19,27,42,130),(20,28,36,120),(21,29,50,142),(22,30,78,178),(23,31,42,122),(24,33,44,124),(25,35,25,63),(26,36,32,88),(27,37,38,108),(28,39,30,80),(29,40,20,48),(30,41,34,94),(31,42,35,95),(32,43,30,74),(33,46,22,56),(34,47,25,67),(35,48,36,106),(36,49,32,82),(37,50,22,52),(38,52,35,93);
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mediciones_antropometricas`
--

LOCK TABLES `mediciones_antropometricas` WRITE;
/*!40000 ALTER TABLE `mediciones_antropometricas` DISABLE KEYS */;
INSERT INTO `mediciones_antropometricas` VALUES (1,5,'2026-06-12',75.50,180.0,170.0,70.0,23.3,NULL,'Administrador Sistema'),(22,1,'2026-06-01',35.00,140.0,138.0,58.0,17.9,15.0,'Lic. Maria Torres'),(23,1,'2026-07-01',35.50,141.0,139.0,58.5,17.9,14.8,'Lic. Maria Torres'),(24,2,'2026-06-01',32.00,135.0,133.0,56.0,17.6,16.0,'Lic. Maria Torres'),(25,2,'2026-07-01',33.00,136.0,134.0,56.5,17.8,15.5,'Lic. Maria Torres'),(26,3,'2026-06-01',30.00,132.0,130.0,55.0,17.2,16.5,'Lic. Maria Torres'),(27,8,'2026-06-01',38.00,145.0,144.0,60.0,18.1,14.0,'Lic. Maria Torres'),(28,8,'2026-07-01',39.00,147.0,146.0,61.0,18.1,13.5,'Lic. Maria Torres'),(29,9,'2026-06-01',34.00,142.0,141.0,57.0,16.9,15.0,'Lic. Maria Torres'),(30,10,'2026-06-01',42.00,150.0,149.0,62.0,18.7,13.0,'Lic. Maria Torres'),(31,10,'2026-07-01',43.00,151.0,150.0,62.5,18.9,12.8,'Lic. Maria Torres'),(32,14,'2026-06-01',55.00,165.0,168.0,68.0,20.2,11.0,'Lic. Maria Torres'),(33,14,'2026-07-01',56.00,166.0,169.0,68.0,20.3,10.8,'Lic. Maria Torres'),(34,15,'2026-06-01',52.00,160.0,162.0,66.0,20.3,12.0,'Lic. Maria Torres'),(35,16,'2026-06-01',60.00,170.0,173.0,70.0,20.8,10.5,'Lic. Maria Torres'),(36,17,'2026-06-01',58.00,168.0,171.0,69.0,20.5,11.0,'Lic. Maria Torres'),(37,17,'2026-07-01',59.00,169.0,172.0,69.0,20.7,10.5,'Lic. Maria Torres'),(38,18,'2026-06-01',62.00,172.0,175.0,72.0,21.0,10.0,'Lic. Maria Torres'),(39,22,'2026-06-01',64.00,175.0,178.0,73.0,20.9,9.5,'Lic. Maria Torres'),(40,22,'2026-07-01',65.00,176.0,179.0,73.0,21.0,9.2,'Lic. Maria Torres'),(41,19,'2026-06-01',57.00,166.0,169.0,68.0,20.7,11.5,'Lic. Maria Torres'),(42,4,'2026-08-08',70.80,176.0,176.0,90.0,22.9,NULL,'Administrador Sistema');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesociclos`
--

LOCK TABLES `mesociclos` WRITE;
/*!40000 ALTER TABLE `mesociclos` DISABLE KEYS */;
INSERT INTO `mesociclos` VALUES (1,1,1,'Acumulacion',1,6,'Desarrollar base aerobica y tecnica',80000),(2,1,2,'Deload',4,4,'Recuperacion activa y regeneracion',20000),(3,1,3,'Transmutacion',7,9,'Convertir volumen en velocidad especifica',55000),(4,1,4,'Taper / Realizacion',10,11,'Afinar rendimiento para competencia',30000),(8,6,8,'Acumulacion',1,6,'Desarrollar base aerobica y tecnica',80000),(9,6,9,'Deload',4,4,'Recuperacion activa y regeneracion',20000),(10,6,10,'Transmutacion',7,9,'Convertir volumen en velocidad especifica',55000),(11,6,11,'Taper / Realizacion',10,12,'Afinar rendimiento para competencia',30000),(12,2,12,'Acumulacion',1,3,'Desarrollar base aerobica y tecnica',80000),(13,2,13,'Transmutacion',4,5,'Convertir volumen en velocidad especifica',55000),(14,2,14,'Taper / Realizacion',6,7,'Afinar rendimiento para competencia',30000);
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metas_competitivas`
--

LOCK TABLES `metas_competitivas` WRITE;
/*!40000 ALTER TABLE `metas_competitivas` DISABLE KEYS */;
INSERT INTO `metas_competitivas` VALUES (1,1,5,'Libre',50,30.41,NULL,NULL),(21,1,14,'Libre',50,34.00,36.50,-5.77),(22,1,14,'Espalda',100,75.00,78.00,-3.85),(23,1,15,'Braza',50,65.00,70.00,-7.14),(24,1,16,'Combinado',200,180.00,190.00,-5.26),(25,1,17,'Libre',50,38.00,40.50,-6.17),(26,1,22,'Libre',50,39.00,41.00,-4.88),(27,1,22,'Mariposa',50,52.00,55.00,-5.45),(28,1,6,'Libre',100,85.00,90.00,-5.56),(29,1,6,'Libre',50,36.00,38.00,-5.26),(30,2,14,'Libre',50,35.50,36.50,-2.74),(31,2,14,'Espalda',100,76.00,78.00,-2.56),(32,2,22,'Libre',100,79.00,82.00,-3.66),(33,3,8,'Libre',50,46.00,47.20,-2.54),(34,3,9,'Libre',50,52.00,55.00,-5.45),(35,3,10,'Libre',100,105.00,110.00,-4.55),(36,4,14,'Libre',50,35.00,36.50,-4.11),(37,4,15,'Mariposa',100,95.00,100.00,-5.00),(38,4,16,'Libre',100,75.00,78.00,-3.85),(39,4,22,'Libre',50,40.00,41.00,-2.44);
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microciclos`
--

LOCK TABLES `microciclos` WRITE;
/*!40000 ALTER TABLE `microciclos` DISABLE KEYS */;
INSERT INTO `microciclos` VALUES (1,1,1,'2026-06-12','2026-06-18',20000),(2,1,2,'2026-06-19','2026-06-25',20000),(3,1,3,'2026-06-26','2026-07-02',20000),(4,1,4,'2026-07-03','2026-07-09',20000),(5,1,5,'2026-07-10','2026-07-16',20000),(6,1,6,'2026-07-17','2026-07-23',20000),(7,2,4,'2026-07-03','2026-07-09',6000),(8,3,7,'2026-07-24','2026-07-30',15000),(9,3,8,'2026-07-31','2026-08-06',15000),(10,3,9,'2026-08-07','2026-08-13',15000),(11,4,10,'2026-08-14','2026-08-20',8000),(12,4,11,'2026-08-21','2026-08-27',8000),(20,8,1,'2026-07-01','2026-07-07',20000),(21,8,2,'2026-07-08','2026-07-14',20000),(22,8,3,'2026-07-15','2026-07-21',20000),(23,8,4,'2026-07-22','2026-07-28',20000),(24,8,5,'2026-07-29','2026-08-04',20000),(25,8,6,'2026-08-05','2026-08-11',20000),(26,9,4,'2026-07-22','2026-07-28',6000),(27,10,7,'2026-08-12','2026-08-18',15000),(28,10,8,'2026-08-19','2026-08-25',15000),(29,10,9,'2026-08-26','2026-09-01',15000),(30,11,10,'2026-09-02','2026-09-08',8000),(31,11,11,'2026-09-09','2026-09-15',8000),(32,11,12,'2026-09-16','2026-09-20',8000),(33,12,1,'2026-07-16','2026-07-22',20000),(34,12,2,'2026-07-23','2026-07-29',20000),(35,12,3,'2026-07-30','2026-08-05',20000),(36,13,4,'2026-08-06','2026-08-12',15000),(37,13,5,'2026-08-13','2026-08-19',15000),(38,14,6,'2026-08-20','2026-08-26',8000),(39,14,7,'2026-08-27','2026-08-31',8000);
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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observaciones_tecnicas`
--

LOCK TABLES `observaciones_tecnicas` WRITE;
/*!40000 ALTER TABLE `observaciones_tecnicas` DISABLE KEYS */;
INSERT INTO `observaciones_tecnicas` VALUES (1,5,1,2,3,'al saltar del taco y sumergise en la salida sale a 10 metros de la salida y no los 15',3,'2026-06-14 15:14:06'),(2,5,NULL,3,4,'mejoro la salida de pechos',3,'2026-06-14 15:20:49'),(28,8,2,3,4,'Buena brazada, necesita mejorar entrada al agua',1,'2026-06-12 09:30:00'),(29,8,4,1,3,'Posicion de salida OK pero entrada al agua muy profunda',1,'2026-06-15 09:30:00'),(30,8,2,5,5,'Excelente respiracion bilateral',1,'2026-06-12 09:35:00'),(31,9,3,10,4,'Mariposa mejorando, buen ritmo undulatorio',1,'2026-06-13 09:30:00'),(32,9,10,8,3,'Patada de rana necesita mas propulsion',1,'2026-06-23 09:30:00'),(33,10,7,6,5,'Brazada de espalda muy limpia',3,'2026-06-17 09:30:00'),(34,10,7,14,4,'Buena posicion del cuerpo en espalda',3,'2026-06-17 09:35:00'),(35,12,7,3,2,'Brazada de crol con alto codo irregular',3,'2026-06-17 09:40:00'),(36,13,7,11,4,'Patada de delfin con buena amplitud',3,'2026-06-17 09:45:00'),(37,14,14,1,5,'Salida explosiva, entrada limpia',1,'2026-06-29 09:30:00'),(38,14,14,12,4,'Viraje flip rapido pero necesita mejorar posicion de pies',1,'2026-06-29 09:35:00'),(39,14,20,3,5,'Brazada de crol impecable',1,'2026-07-07 09:30:00'),(40,15,14,13,5,'Llegada precisa con buen timing',1,'2026-06-29 09:40:00'),(41,15,20,9,4,'Patada de rana con buen ritmo',1,'2026-07-07 09:35:00'),(42,16,20,1,3,'Posicion de salida puede mejorar, pierde tiempo en reaccion',1,'2026-07-07 09:40:00'),(43,17,15,7,5,'Patada de espalda muy consistente',1,'2026-06-30 09:30:00'),(44,17,20,4,4,'Kick de crol buen ritmo, necesita mas amplitud',1,'2026-07-07 09:45:00'),(45,22,28,3,5,'Brazada de crol tecnica perfecta',1,'2026-07-16 09:30:00'),(46,22,28,12,5,'Virajes rapidos y limpios',1,'2026-07-16 09:35:00'),(47,1,2,15,3,'Coordinacion general aceptable, mejorar sincronizacion',1,'2026-06-12 09:50:00'),(48,1,4,1,4,'Salida mejorando, buen dolphin kick underwater',1,'2026-06-15 09:50:00'),(49,2,5,6,4,'Espalda buena tecnica',1,'2026-06-16 09:50:00'),(50,3,9,9,3,'Patada de rana necesita mas trabajo',1,'2026-06-22 09:50:00'),(51,19,28,3,4,'Brazada de crol solida, mantener trabajo',1,'2026-07-16 09:50:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `protocolo_retorno`
--

LOCK TABLES `protocolo_retorno` WRITE;
/*!40000 ALTER TABLE `protocolo_retorno` DISABLE KEYS */;
INSERT INTO `protocolo_retorno` VALUES (1,8,'Rango de movilidad completo sin dolor',1,'2026-06-25'),(2,8,'Nadar 500m sin molestia',0,NULL),(3,8,'Completar sesion completa de tecnica',0,NULL),(4,8,'Volver a sesiones de velocidad',0,NULL),(5,10,'Caminar sin dolor',1,'2026-06-27'),(6,10,'Sentadillas sin molestia',1,'2026-06-29'),(7,10,'Nadar 200m libre suave',0,NULL),(8,10,'Completar sesion completa',0,NULL),(9,16,'Rango de movilidad completo',0,NULL),(10,16,'Ejercicios de fortalecimiento con banda',0,NULL),(11,16,'Nadar con pullboy sin dolor',0,NULL),(12,16,'Sesion completa sin molestia',0,NULL),(13,22,'Caminar sin dolor ni cojera',1,'2026-07-10'),(14,22,'Correr suave 10 minutos',0,NULL),(15,22,'Nadar espalda sin molestia',0,NULL),(16,22,'Nadar crol progresivo',0,NULL),(17,22,'Sesion completa de velocidad',0,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_rpe`
--

LOCK TABLES `registro_rpe` WRITE;
/*!40000 ALTER TABLE `registro_rpe` DISABLE KEYS */;
INSERT INTO `registro_rpe` VALUES (1,3,1,'2026-06-11',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:07:44'),(2,3,NULL,'2026-06-10',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(3,3,NULL,'2026-06-09',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(4,3,NULL,'2026-06-08',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(5,3,NULL,'2026-06-07',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(6,3,NULL,'2026-06-06',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(7,3,NULL,'2026-06-05',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(8,3,NULL,'2026-06-04',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(9,1,2,'2026-06-12',6,8.0,4,3,2,NULL,3200,90,540,'2026-06-12 10:30:00'),(10,2,2,'2026-06-12',7,7.5,3,4,3,NULL,3200,90,630,'2026-06-12 10:35:00'),(11,3,2,'2026-06-12',5,9.0,5,2,1,NULL,3200,90,450,'2026-06-12 10:40:00'),(12,1,4,'2026-06-15',8,7.0,3,5,3,'Sesion de velocidad intensa',2900,90,720,'2026-06-15 10:30:00'),(13,2,4,'2026-06-15',7,8.0,4,4,2,NULL,2900,90,630,'2026-06-15 10:35:00'),(14,3,4,'2026-06-15',9,6.5,2,6,4,'Muy cansado al final',2900,90,810,'2026-06-15 10:40:00'),(15,8,3,'2026-06-13',6,8.5,4,3,2,NULL,2700,85,510,'2026-06-13 10:30:00'),(16,9,3,'2026-06-13',7,7.0,3,4,3,NULL,2700,85,595,'2026-06-13 10:35:00'),(17,11,3,'2026-06-13',5,9.0,5,2,1,'Bien descansado',2700,85,425,'2026-06-13 10:40:00'),(18,10,7,'2026-06-17',6,8.0,4,3,2,NULL,2800,90,540,'2026-06-17 10:30:00'),(19,12,7,'2026-06-17',8,6.0,2,5,4,'Dolor leve en hombro',2800,90,720,'2026-06-17 10:35:00'),(20,13,7,'2026-06-17',5,8.5,4,2,1,NULL,2800,90,450,'2026-06-17 10:40:00'),(21,1,5,'2026-06-16',6,8.0,4,3,2,NULL,4100,105,630,'2026-06-16 11:00:00'),(22,2,5,'2026-06-16',7,7.0,3,4,3,NULL,4100,105,735,'2026-06-16 11:05:00'),(23,14,14,'2026-06-29',3,9.0,5,2,1,'Sesion de recuperacion',1500,60,180,'2026-06-29 10:30:00'),(24,15,14,'2026-06-29',4,8.5,4,3,1,NULL,1500,60,240,'2026-06-29 10:35:00'),(25,1,9,'2026-06-22',5,8.0,4,3,2,NULL,2900,85,425,'2026-06-22 10:30:00'),(26,8,10,'2026-06-23',7,7.5,3,4,3,NULL,3600,100,700,'2026-06-23 10:30:00'),(27,9,10,'2026-06-23',6,8.0,4,3,2,NULL,3600,100,600,'2026-06-23 10:35:00'),(28,14,20,'2026-07-07',8,7.0,3,5,3,'Sesion fuerte',3200,90,720,'2026-07-07 10:30:00'),(29,15,20,'2026-07-07',7,8.0,4,4,2,NULL,3200,90,630,'2026-07-07 10:35:00'),(30,16,20,'2026-07-07',9,6.0,2,6,5,'Hombro molesto',3200,90,810,'2026-07-07 10:40:00'),(31,1,19,'2026-07-06',7,8.0,4,4,3,NULL,4200,105,735,'2026-07-06 11:00:00'),(32,2,19,'2026-07-06',6,7.5,3,3,2,NULL,4200,105,630,'2026-07-06 11:05:00'),(33,3,19,'2026-07-06',5,9.0,5,2,1,NULL,4200,105,525,'2026-07-06 11:10:00'),(34,14,28,'2026-07-16',6,8.0,4,3,2,NULL,3100,85,510,'2026-07-16 10:30:00'),(35,15,28,'2026-07-16',7,7.0,3,4,3,NULL,3100,85,595,'2026-07-16 10:35:00'),(36,22,28,'2026-07-16',8,6.5,2,5,4,'Molestia en muslo',3100,85,680,'2026-07-16 10:40:00'),(37,19,28,'2026-07-16',6,8.5,4,3,2,NULL,3100,85,510,'2026-07-16 10:45:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registros_test`
--

LOCK TABLES `registros_test` WRITE;
/*!40000 ALTER TABLE `registros_test` DISABLE KEYS */;
INSERT INTO `registros_test` VALUES (1,5,1,NULL,'2026-06-12',3,'ninguna','Completo','2026-06-12 09:35:44'),(2,5,3,NULL,'2026-06-12',3,NULL,'Completo','2026-06-12 09:36:50'),(33,8,1,NULL,'2026-06-01',1,'Primer test lactato de la temporada','Completo','2026-06-01 10:00:00'),(34,8,2,NULL,'2026-06-01',1,NULL,'Completo','2026-06-01 10:30:00'),(35,8,3,NULL,'2026-06-01',1,NULL,'Completo','2026-06-01 11:00:00'),(36,9,1,NULL,'2026-06-01',1,NULL,'Completo','2026-06-01 11:30:00'),(37,9,5,NULL,'2026-06-01',1,NULL,'Completo','2026-06-01 12:00:00'),(38,10,1,NULL,'2026-06-01',3,NULL,'Completo','2026-06-01 13:00:00'),(39,10,6,NULL,'2026-06-01',3,NULL,'Completo','2026-06-01 13:30:00'),(40,14,1,NULL,'2026-06-05',1,'Test pre-macro','Completo','2026-06-05 08:00:00'),(41,14,2,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 08:30:00'),(42,14,6,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 09:00:00'),(43,14,7,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 09:30:00'),(44,14,3,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 10:00:00'),(45,15,1,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 10:30:00'),(46,15,4,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 11:00:00'),(47,16,1,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 11:30:00'),(48,16,9,NULL,'2026-06-05',1,NULL,'Completo','2026-06-05 12:00:00'),(49,17,1,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 13:00:00'),(50,17,2,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 13:30:00'),(51,17,8,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 14:00:00'),(52,22,1,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 14:30:00'),(53,22,9,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 15:00:00'),(54,22,5,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 15:30:00'),(55,22,6,NULL,'2026-06-05',3,NULL,'Completo','2026-06-05 16:00:00'),(56,1,2,NULL,'2026-06-15',1,NULL,'Completo','2026-06-15 08:00:00'),(57,1,3,NULL,'2026-06-15',1,NULL,'Completo','2026-06-15 08:30:00'),(58,2,2,NULL,'2026-06-15',1,NULL,'Completo','2026-06-15 09:00:00'),(59,3,2,NULL,'2026-06-15',1,NULL,'Completo','2026-06-15 09:30:00'),(60,8,1,NULL,'2026-07-01',1,'Test mid-macro','Completo','2026-07-01 08:00:00'),(61,14,1,NULL,'2026-07-01',1,NULL,'Completo','2026-07-01 09:00:00'),(62,22,1,NULL,'2026-07-01',3,NULL,'Completo','2026-07-01 10:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `representantes`
--

LOCK TABLES `representantes` WRITE;
/*!40000 ALTER TABLE `representantes` DISABLE KEYS */;
INSERT INTO `representantes` VALUES (1,'8591799','Jose Gregorio','Pirolo Gonzalez','Padre','04121273248','02517183360','jose@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',1,'2026-05-20 16:48:30','Activo'),(2,'10762010','Josefina','Navarro Corro','Madre','04245728016','02517183361','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca, Las Delicias',1,'2026-05-20 18:09:41','Activo'),(3,'2383050','Lourdes','Corro','Tutor','04120121212','02517183360','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',1,'2026-05-20 22:40:25','Inactivo'),(4,'25626233','jorge','casanovas','Padre','04265515431','0425666326','jorgec@gmail.com','Caracas, las mercedes',3,'2026-06-12 07:49:34','Activo'),(5,'V-15234567','Carlos Eduardo','Rodriguez Mendoza','Padre','04145551234','02514442211','carlos.rodriguez@gmail.com','Av. 20 con Calle 31, Barquisimeto',1,'2026-01-15 10:00:00','Activo'),(6,'V-18990123','Maria Isabel','Garcia Torres','Madre','04126667890','02513334456','maria.garcia@hotmail.com','Urbanizacion La Concordia, Barquisimeto',20,'2026-01-20 11:30:00','Activo'),(7,'V-12456789','Antonio Jose','Hernandez Perez','Padre','04167772345','02515556677','antonio.h@gmail.com','Calle 40 entre Av. 18 y 19, Barquisimeto',7,'2026-02-01 09:15:00','Activo'),(8,'V-16789012','Luisa Fernanda','Martinez Vargas','Madre','04148889012','02516667788','luisa.martinez@gmail.com','Av. Intercomunal, Tamaca',8,'2026-02-10 14:00:00','Activo'),(9,'V-13579024','Rafael Alberto','Lopez Sanchez','Padre','04249993456','02517778899','rafael.lopez@gmail.com','Sector El Cardenal, Barquisimeto',12,'2026-02-15 08:45:00','Activo'),(10,'V-14680235','Ana Cristina','Diaz Rios','Madre','04121115678','02518889900','ana.diaz@gmail.com','Calle 52 con Av. 30, Barquisimeto',9,'2026-03-01 10:30:00','Activo'),(11,'V-15891346','Pedro Manuel','Vargas Moreno','Padre','04162226789','02519990011','pedro.vargas@gmail.com','Urbanizacion Toroes, Barquisimeto',13,'2026-03-10 11:00:00','Activo'),(12,'V-16002457','Carmen Elena','Rojas Blanco','Madre','04243337890','02511112233','carmen.rojas@gmail.com','Av. Libertador con Calle 28, Barquisimeto',14,'2026-03-15 13:00:00','Activo'),(13,'V-17113568','Jose Ramon','Castillo Paredes','Padre','04144448901','02512223344','jose.castillo@gmail.com','Barrio La Union, Cabudare',10,'2026-03-20 09:00:00','Activo'),(14,'V-18224679','Yelitza Maria','Torres Alvarado','Madre','04125559012','02513334455','yelitza.torres@gmail.com','Urbanizacion Creatividad, Barquisimeto',11,'2026-04-01 10:00:00','Activo'),(15,'V-19335780','Miguel Angel','Fuentes Castillo','Padre','04166660123','02514445566','miguel.fuentes@gmail.com','Av. 19 con Calle 45, Barquisimeto',15,'2026-04-10 14:30:00','Activo'),(16,'V-20446891','Rosa Elena','Paredes Urdaneta','Madre','04247771234','02515556677','rosa.paredes@gmail.com','Sector Buena Vista, Barquisimeto',16,'2026-04-15 08:30:00','Activo'),(17,'V-21557902','Victor Manuel','Urbina Colmenares','Padre','04148882345','02516667788','victor.urbina@gmail.com','Calle 35 entre Av. 22 y 23, Barquisimeto',17,'2026-04-20 11:15:00','Activo'),(18,'V-22668013','Nancy Patricia','Colmenares Rangel','Madre','04129993456','02517778899','nancy.colmenares@gmail.com','Urbanizacion El Trigal, Barquisimeto',18,'2026-05-01 10:45:00','Activo'),(19,'V-23779124','Alberto Daniel','Rangel Bracho','Padre','04161114567','02518889900','alberto.rangel@gmail.com','Av. 20 de Barquisimeto, Cabudare',19,'2026-05-05 09:30:00','Activo');
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series_sesion`
--

LOCK TABLES `series_sesion` WRITE;
/*!40000 ALTER TABLE `series_sesion` DISABLE KEYS */;
INSERT INTO `series_sesion` VALUES (1,2,1,1,'Calentamiento','6x50 patada con tabla',6,50,15,'Z1',NULL),(2,2,2,2,'Principal','8x50 pullboy crol',8,50,20,'Z2',NULL),(3,2,NULL,3,'Principal','4x200 crol a ritmo',4,200,30,'Z3','2:15/100m'),(4,2,NULL,4,'VueltaCalma','200m braza suave',1,200,0,'Z1',NULL),(5,4,6,1,'Principal','8x25 sprint crol',8,25,30,'Z5','Maximo'),(6,4,7,2,'Principal','6x25 viraje flip',6,25,20,'Z3',NULL),(7,5,8,1,'Principal','4x200 resistencia crol',4,200,30,'Z2','2:20/100m'),(8,5,NULL,2,'Principal','2x400 libre Z2',2,400,60,'Z2','2:30/100m'),(9,6,2,1,'Principal','8x50 pullboy',8,50,15,'Z2',NULL),(10,6,1,2,'Principal','6x50 kick tabla',6,50,15,'Z1',NULL),(11,9,1,1,'Principal','6x50 patada tabla',6,50,15,'Z1',NULL),(12,9,NULL,2,'Principal','6x50 braza 2:1',6,50,20,'Z2',NULL),(13,10,8,1,'Principal','4x200 resistencia',4,200,30,'Z3','2:10/100m'),(14,10,NULL,2,'Principal','6x100 crol Z2',6,100,20,'Z2','1:40/100m'),(15,11,6,1,'Principal','8x25 sprint',8,25,30,'Z5','Maximo'),(16,12,12,1,'Principal','8x50 con paddles',8,50,20,'Z3',NULL),(17,13,9,1,'Principal','4x100 combinado',4,100,45,'Z3',NULL),(18,15,17,1,'Calentamiento','200m espalda suave',1,200,0,'Z1',NULL),(19,15,NULL,2,'Principal','500m mixto relajado',1,500,0,'Z1',NULL),(20,18,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:25/100m'),(21,19,6,1,'Principal','8x25 sprint',8,25,30,'Z5','Maximo'),(22,20,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:15/100m'),(23,21,18,1,'Principal','Piramide de velocidad',1,800,60,'',NULL),(24,22,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:15/100m'),(25,23,6,1,'Principal','8x25 sprint',8,25,30,'Z5','Maximo'),(26,24,8,1,'Principal','4x200 resistencia',4,200,30,'Z3','2:10/100m'),(27,26,6,1,'Principal','8x25 sprint',8,25,30,'Z5','Maximo'),(28,27,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:20/100m'),(29,28,6,1,'Principal','8x25 sprint',8,25,30,'Z5','Maximo'),(30,29,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:15/100m'),(31,31,1,1,'Calentamiento','6x50 patada tabla',6,50,15,'Z1',NULL),(32,31,2,2,'Principal','8x50 pullboy',8,50,20,'Z2',NULL),(33,33,8,1,'Principal','4x200 resistencia',4,200,30,'Z2','2:15/100m'),(35,37,9,1,'Principal',NULL,1,50,15,'Z1',NULL),(36,37,20,2,'',NULL,1,25,15,'Z1',NULL),(38,39,20,1,'Principal',NULL,1,50,15,'Z1',NULL),(39,38,9,1,'Principal',NULL,1,50,15,'Z1','1,3'),(43,41,6,1,'Principal',NULL,1,50,15,'Z1','12'),(44,41,23,2,'Calentamiento',NULL,1,50,15,'Z4','23'),(46,42,19,1,'Principal',NULL,1,50,15,'Z1','12'),(47,40,NULL,1,'Principal','Competencia nacional',1,50,15,'Z1',NULL),(48,43,20,1,'Principal',NULL,1,50,15,'Z1',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
INSERT INTO `sesiones` VALUES (1,NULL,1,'2026-07-12','Tecnica',NULL,NULL,NULL,NULL,3200,NULL,'','Parcial',NULL,NULL,'2026-06-11 16:05:47','2026-07-12 21:14:07'),(6,1,1,'2026-06-12','Tecnica',1,'600m espalda suave + 200m crol','200m braza suave',3000,3200,90,'Buen trabajo en salidas','Completada',1,3,'2026-06-11 18:00:00','2026-06-12 10:00:00'),(7,1,2,'2026-06-12','Resistencia',1,'400m libre Z1 + estiramientos','200m espalda suave',3500,3600,100,'Atletas motivados','Completada',1,3,'2026-06-11 18:10:00','2026-06-12 10:30:00'),(8,1,3,'2026-06-13','Tecnica',1,'500m crol suave + drill de respiracion','200m espalda suave',2500,2700,85,NULL,'Completada',3,4,'2026-06-12 16:00:00','2026-06-13 10:00:00'),(9,2,1,'2026-06-15','Velocidad',1,'400m libre + 4x50 build-up','300m vuelta calma',2800,2900,90,'Sprint 25m excelente','Completada',1,3,'2026-06-14 18:00:00','2026-06-15 10:00:00'),(10,2,1,'2026-06-16','Resistencia',1,'600m mixto suave','200m braza',4000,4100,105,'Buen ritmo en Z2','Completada',1,3,'2026-06-14 18:10:00','2026-06-16 10:00:00'),(11,2,2,'2026-06-16','Tecnica',1,'400m libre + drill brazada','200m espalda',3000,3100,85,NULL,'Completada',1,3,'2026-06-15 18:00:00','2026-06-16 10:00:00'),(12,2,3,'2026-06-17','Velocidad',1,'500m libre Z1 + dinamicas','300m suave',2600,2550,90,NULL,'Completada',3,4,'2026-06-16 16:00:00','2026-06-17 10:00:00'),(13,2,1,'2026-06-18','Fuerza',1,'400m crol + movilidad','200m relajado',3200,3300,95,'Trabajo con paddles','Completada',1,3,'2026-06-17 18:00:00','2026-06-18 10:00:00'),(14,3,1,'2026-06-22','Tecnica',1,'500m libre + drill espalda','200m braza suave',2800,2900,85,NULL,'Completada',1,3,'2026-06-21 18:00:00','2026-06-22 10:00:00'),(15,3,1,'2026-06-23','Resistencia',1,'600m mixto Z1','200m espalda',4500,4600,110,'Atletas progresando bien','Completada',1,3,'2026-06-22 18:00:00','2026-06-23 10:00:00'),(16,3,2,'2026-06-23','Velocidad',1,'400m libre + 4x25 sprint','300m suave',2500,2650,90,'Mejoraron tiempos de sprint','Completada',1,3,'2026-06-22 18:10:00','2026-06-23 10:30:00'),(17,3,3,'2026-06-24','Tecnica',1,'500m crol + drill viraje','200m libre suave',3000,2900,90,NULL,'Completada',3,4,'2026-06-23 16:00:00','2026-06-24 10:00:00'),(18,3,1,'2026-06-25','Fuerza',1,'400m crol + paracaidas','200m braza suave',3200,3150,95,NULL,'Completada',1,3,'2026-06-24 18:00:00','2026-06-25 10:00:00'),(19,4,1,'2026-06-29','Recuperacion',2,'400m mixto suave','300m espalda + estiramientos',1500,1500,60,'Sesion ligera pre-deload','Completada',1,3,'2026-06-28 18:00:00','2026-06-29 10:00:00'),(20,4,2,'2026-06-30','Flexibilidad',2,'300m libre suave + movilidad articular','200m relajado',1200,1200,55,NULL,'Completada',1,3,'2026-06-29 18:00:00','2026-06-30 10:00:00'),(21,4,3,'2026-07-01','Recuperacion',2,'400m mixto suave','200m espalda',1400,1400,60,NULL,'Completada',3,4,'2026-06-30 16:00:00','2026-07-01 10:00:00'),(22,4,1,'2026-07-02','Flexibilidad',2,'300m braza suave + estiramientos','200m libre',1000,1000,50,NULL,'Completada',1,3,'2026-07-01 18:00:00','2026-07-02 10:00:00'),(23,5,1,'2026-07-06','Resistencia',1,'600m libre Z1-Z2','200m braza',4000,4200,105,'Buena intensidad tras deload','Completada',1,3,'2026-07-05 18:00:00','2026-07-06 10:00:00'),(24,5,1,'2026-07-07','Tecnica',1,'500m mixto + drills','200m espalda',3000,3100,85,NULL,'Completada',1,3,'2026-07-06 18:00:00','2026-07-07 10:00:00'),(25,5,2,'2026-07-07','Velocidad',1,'400m libre + 6x25 sprint','200m suave',2500,2600,90,NULL,'Completada',1,3,'2026-07-06 18:10:00','2026-07-07 10:30:00'),(26,5,3,'2026-07-08','Resistencia',1,'600m libre Z2','200m braza',3500,3600,95,NULL,'Completada',3,4,'2026-07-07 16:00:00','2026-07-08 10:00:00'),(27,5,1,'2026-07-09','Fuerza',1,'400m crol + paddles','200m espalda',3000,3050,90,NULL,'Completada',1,3,'2026-07-08 18:00:00','2026-07-09 10:00:00'),(28,6,1,'2026-07-13','Velocidad',1,'500m libre + dinamicas','300m suave',2800,2850,90,NULL,'Completada',1,3,'2026-07-12 18:00:00','2026-07-13 10:00:00'),(29,6,2,'2026-07-13','Tecnica',1,'400m libre + drill coordinacion','200m braza',2500,2600,85,NULL,'Completada',1,3,'2026-07-12 18:10:00','2026-07-13 10:30:00'),(30,6,1,'2026-07-14','Resistencia',1,'600m mixto Z2-Z3','200m espalda',4000,3900,100,NULL,'Completada',1,3,'2026-07-13 18:00:00','2026-07-14 10:00:00'),(31,6,3,'2026-07-15','Velocidad',1,'400m libre + 8x25 sprint','300m suave',2400,2500,90,'Tiempo de sprint mejoro','Completada',3,4,'2026-07-14 16:00:00','2026-07-15 10:00:00'),(32,6,1,'2026-07-16','Tecnica',1,'500m crol + drills de viraje','200m libre suave',3000,3100,85,NULL,'Completada',1,3,'2026-07-15 18:00:00','2026-07-16 10:00:00'),(33,6,1,'2026-07-17','Fuerza',1,'400m crol + paracaidas','200m braza',3200,3250,95,NULL,'Completada',1,3,'2026-07-16 18:00:00','2026-07-17 10:00:00'),(34,6,2,'2026-07-17','Resistencia',1,'600m libre Z2','200m espalda',3800,3900,100,NULL,'Completada',1,3,'2026-07-16 18:10:00','2026-07-17 10:30:00'),(37,NULL,4,'2026-07-28','Recuperacion',NULL,'223m','12m',75,NULL,15,'jdjd','Cancelada',NULL,4,'2026-07-28 17:19:39','2026-07-28 17:20:19'),(38,NULL,7,'2026-07-31','Tecnica',NULL,'100M Libre','125M',50,NULL,23,'Grupo entrenamiento de 6 a 12','Planificada',NULL,4,'2026-07-29 18:21:07','2026-07-30 16:41:42'),(39,NULL,7,'2026-07-30','Fuerza',NULL,'100M','150M',50,50,15,'jsjsjjssj','Completada',NULL,4,'2026-07-29 18:36:52','2026-08-12 13:00:05'),(40,NULL,8,'2026-08-16','Competencia',NULL,'150M','100M',50,NULL,23,'Para la competencia juvenil','Planificada',NULL,4,'2026-07-30 16:42:30','2026-08-15 21:38:52'),(41,NULL,7,'2026-08-05','Flexibilidad',NULL,'100M','150M',100,NULL,15,'Sesiones de flexibilidad sin pausa','Planificada',NULL,8,'2026-08-05 15:31:55','2026-08-05 15:32:13'),(42,NULL,5,'2026-08-12','Tecnica',NULL,'20M','23M',50,NULL,20,'jsjsjs pisa','Cancelada',NULL,5,'2026-08-12 12:59:29','2026-08-12 12:59:55'),(43,NULL,7,'2026-08-16','Tecnica',NULL,'200M','100M',50,NULL,18,'nznznz','Planificada',NULL,5,'2026-08-15 21:39:31',NULL);
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
  `estado_carrera` enum('esperando','en_curso','finalizado') NOT NULL DEFAULT 'esperando',
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temporadas`
--

LOCK TABLES `temporadas` WRITE;
/*!40000 ALTER TABLE `temporadas` DISABLE KEYS */;
INSERT INTO `temporadas` VALUES (1,'Temporada 2026','2026-01-01','2026-12-31',0),(2,'Temporada 2026 - 2027','2026-05-10','2027-05-10',1),(3,'Temporada 2028','2028-03-02','2028-10-08',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiempos_corte_evento`
--

LOCK TABLES `tiempos_corte_evento` WRITE;
/*!40000 ALTER TABLE `tiempos_corte_evento` DISABLE KEYS */;
INSERT INTO `tiempos_corte_evento` VALUES (31,1,2,'Libre',50,55.00),(32,1,2,'Libre',100,120.00),(33,1,2,'Espalda',50,60.00),(34,1,2,'Braza',50,70.00),(35,1,3,'Libre',50,50.00),(36,1,3,'Libre',100,105.00),(37,1,3,'Espalda',100,115.00),(38,1,3,'Braza',50,65.00),(39,1,3,'Mariposa',50,60.00),(40,1,4,'Libre',50,42.00),(41,1,4,'Libre',100,85.00),(42,1,4,'Espalda',100,85.00),(43,1,4,'Combinado',200,195.00),(44,1,5,'Libre',50,38.00),(45,1,5,'Libre',100,80.00),(46,1,5,'Mariposa',50,55.00),(47,1,6,'Libre',50,36.00),(48,1,6,'Libre',100,78.00),(49,1,6,'Espalda',100,82.00),(50,3,2,'Libre',50,60.00),(51,3,3,'Libre',50,55.00),(52,3,3,'Libre',100,110.00),(53,3,4,'Libre',50,45.00),(54,3,4,'Libre',100,90.00),(55,3,5,'Libre',50,40.00),(56,3,5,'Libre',100,85.00),(57,4,4,'Libre',50,43.00),(58,4,4,'Libre',100,90.00),(59,4,5,'Libre',50,39.00),(60,4,5,'Libre',100,82.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variables_test`
--

LOCK TABLES `variables_test` WRITE;
/*!40000 ALTER TABLE `variables_test` DISABLE KEYS */;
INSERT INTO `variables_test` VALUES (1,1,NULL,'Lactato final','mmol/L',1,1),(2,1,NULL,'Frecuencia cardíaca final','bpm',2,1),(3,1,NULL,'Frecuencia cardíaca 1 min post','bpm',3,1),(4,2,NULL,'Sin flexión','cm',1,1),(5,2,NULL,'Con flexión','cm',2,1),(6,2,NULL,'Diferencia','cm',3,1),(7,3,NULL,'Repeticiones','reps',1,1),(8,4,NULL,'Repeticiones','reps',1,1),(9,5,NULL,'Distancia','cm',1,1),(10,6,NULL,'Altura','cm',1,1),(11,7,NULL,'Distancia recorrida','m',1,1),(12,7,NULL,'VO2max estimado','ml/kg/min',2,1),(13,8,NULL,'Tiempo','seg',1,1),(14,9,NULL,'Tiempo','seg',1,1),(17,NULL,1,'tiempo de reaccion','segundos',1,1);
/*!40000 ALTER TABLE `variables_test` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-06-12 07:48:26'),(2,3,'Modulo Eventos','INSERT',NULL,'evento',NULL,'Gala Regional Miranda 2026','127.0.0.1',NULL,'2026-06-12 07:52:17'),(3,3,'Modulo Eventos','UPDATE',1,'datos evento',NULL,'Gala Regional Miranda 2026','127.0.0.1',NULL,'2026-06-12 07:53:17'),(4,3,'Modulo Eventos','INSERT',1,'evento_inscripcion',NULL,'1 atletas','127.0.0.1',NULL,'2026-06-12 07:53:24'),(5,3,'Modulo Metas','INSERT',1,'metas_competitivas',NULL,'1 metas','127.0.0.1',NULL,'2026-06-12 07:53:57'),(6,3,'Seguridad','UPDATE',1,'permisos rol',NULL,'45 permisos','127.0.0.1',NULL,'2026-06-12 07:54:46'),(7,3,'Seguridad','UPDATE',1,'permisos rol',NULL,'43 permisos','127.0.0.1',NULL,'2026-06-12 08:28:52'),(8,3,'Atleta','DELETE',4,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-06-12 08:30:56'),(9,3,'Atleta','DELETE',4,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-06-12 08:30:59'),(10,3,'Atleta','UPDATE',5,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-06-12 08:31:14'),(11,3,'Modulo Periodizacion','CREATE',NULL,'macrociclo',NULL,'','127.0.0.1',NULL,'2026-06-12 08:33:44'),(12,3,'Modulo Periodizacion','UPDATE',1,'plan_atr',NULL,'Generado: 11 semanas','127.0.0.1',NULL,'2026-06-12 08:33:51'),(13,3,'Tests Fisicos','CREATE',NULL,'Registro completo',NULL,'{\"id_registro_test\":\"\",\"id_atleta\":\"5\",\"id_tipo_test\":\"1\",\"id_test_pers\":\"\",\"fecha\":\"2026-06-12\",\"estado\":\"Completo\",\"valores\":{\"1\":\"0.03\",\"2\":\"0.06\",\"3\":\"0.09\"},\"observaciones\":\"ninguna\",\"id_usuario_toma\":3}','127.0.0.1',NULL,'2026-06-12 09:35:45'),(14,3,'Tests Fisicos','CREATE',NULL,'Registro completo',NULL,'{\"id_registro_test\":\"\",\"id_atleta\":\"5\",\"id_tipo_test\":\"3\",\"id_test_pers\":\"\",\"fecha\":\"2026-06-12\",\"estado\":\"Completo\",\"valores\":{\"7\":\"10\"},\"observaciones\":\"\",\"id_usuario_toma\":3}','127.0.0.1',NULL,'2026-06-12 09:36:50'),(15,3,'Modulo Sesiones','UPDATE',1,'estado/ejecucion',NULL,'Estado cambiado a Completada. Vol: 3200','127.0.0.1',NULL,'2026-06-13 19:28:43'),(16,3,'Observaciones Tecnicas','CREATE',NULL,'Registro completo',NULL,'{\"id_observacion\":\"\",\"id_atleta\":\"5\",\"id_aspecto_tecnico\":\"2\",\"id_sesion\":\"1\",\"calificacion\":\"3\",\"observacion_texto\":\"al saltar del taco y sumergise en la salida sale a 10 metros de la salida y no los 15\",\"id_usuario\":3}','127.0.0.1',NULL,'2026-06-14 11:14:06'),(17,3,'Observaciones Tecnicas','CREATE',NULL,'Registro completo',NULL,'{\"id_observacion\":\"\",\"id_atleta\":\"5\",\"id_aspecto_tecnico\":\"2\",\"id_sesion\":\"1\",\"calificacion\":\"4\",\"observacion_texto\":\"mejoro la salida de pecho\",\"id_usuario\":3}','127.0.0.1',NULL,'2026-06-14 11:20:49'),(18,3,'Modulo Eventos','INSERT',NULL,'evento',NULL,'Nacional copa Feveda 2026','127.0.0.1',NULL,'2026-06-14 18:37:18'),(19,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-06-24 22:29:29'),(20,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-07-03_01-16-28.sql','127.0.0.1',NULL,'2026-07-02 19:16:29'),(21,3,'Tipos de Tests','CREATE',NULL,'tipos_test_predefinidos',NULL,'Tipo creado: ad{}{','127.0.0.1',NULL,'2026-07-02 20:17:21'),(22,3,'Tipos de Tests','DELETE',10,'tipos_test_predefinidos',NULL,'Tipo eliminado','127.0.0.1',NULL,'2026-07-02 20:17:32'),(23,3,'Tipos de Tests','CREATE',NULL,'tipos_test_predefinidos',NULL,'Tipo creado: wqeq2eq/////}+{','127.0.0.1',NULL,'2026-07-02 20:38:08'),(24,3,'Tipos de Tests','DELETE',11,'tipos_test_predefinidos',NULL,'Tipo eliminado','127.0.0.1',NULL,'2026-07-02 20:38:19'),(25,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-07-04_22-44-05.sql','127.0.0.1',NULL,'2026-07-04 16:44:06'),(26,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-07-04 17:48:47'),(27,3,'Tipos de Tests','CREATE',NULL,'tests_personalizados',NULL,'Test personalizado creado: algo','127.0.0.1',NULL,'2026-07-05 15:37:43'),(28,3,'Observaciones Tecnicas','CREATE',NULL,'Registro completo',NULL,'{\"id_observacion\":\"\",\"id_atleta\":\"7\",\"id_aspecto_tecnico\":\"3\",\"id_sesion\":\"\",\"calificacion\":\"3\",\"observacion_texto\":\"hla\",\"id_usuario\":3}','127.0.0.1',NULL,'2026-07-05 16:24:48'),(29,3,'Atleta','DELETE',7,NULL,NULL,NULL,'127.0.0.1',NULL,'2026-07-07 16:33:07'),(30,3,'Modulo Periodizacion','CREATE',NULL,'macrociclo',NULL,'prueba ','127.0.0.1',NULL,'2026-07-07 17:07:12'),(31,3,'Observaciones Tecnicas','DELETE',3,'Registro completo',NULL,'Eliminado','127.0.0.1',NULL,'2026-07-07 19:06:14'),(32,3,'Observaciones Tecnicas','UPDATE',2,'Datos de la observacion','Ver historial previo','{\"id_atleta\":\"5\",\"id_aspecto_tecnico\":\"2\",\"calificacion\":\"4\",\"observacion_texto\":\"mejoro la salida de pecho\"}','127.0.0.1',NULL,'2026-07-07 19:06:20'),(33,3,'Observaciones Tecnicas','UPDATE',2,'Datos de la observacion','Ver historial previo','{\"id_atleta\":\"5\",\"id_aspecto_tecnico\":\"2\",\"id_sesion\":\"\",\"calificacion\":\"4\",\"observacion_texto\":\"mejoro la salida de pechos\"}','127.0.0.1',NULL,'2026-07-07 19:06:25'),(34,3,'Observaciones Tecnicas','UPDATE',2,'Datos de la observacion','Ver historial previo','{\"id_atleta\":\"5\",\"id_aspecto_tecnico\":\"3\",\"id_sesion\":\"\",\"calificacion\":\"4\",\"observacion_texto\":\"mejoro la salida de pechos\"}','127.0.0.1',NULL,'2026-07-07 19:06:32'),(35,3,'Modulo Temporadas','CREATE',NULL,'temporada',NULL,'Temporada 2026 - 2027','127.0.0.1',NULL,'2026-07-07 19:10:19'),(36,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-07-09_03-47-07.sql','127.0.0.1',NULL,'2026-07-08 21:47:08'),(37,3,'Tipos de Tests','DELETE',1,'tests_personalizados',NULL,'Test personalizado eliminado','127.0.0.1',NULL,'2026-07-08 22:20:19'),(38,3,'Modulo Temporadas','DELETE',1,'temporada',NULL,'Eliminada','127.0.0.1',NULL,'2026-07-08 22:20:55'),(39,3,'Observaciones Tecnicas','CREATE',NULL,'Registro completo',NULL,'{\"id_observacion\":\"\",\"id_atleta\":\"87\",\"id_aspecto_tecnico\":\"2\",\"id_sesion\":\"\",\"calificacion\":\"4\",\"observacion_texto\":\"puede  mejorar\",\"id_usuario\":3}','127.0.0.1',NULL,'2026-07-08 22:21:30'),(40,3,'Modulo Periodizacion','UPDATE',2,'plan_atr',NULL,'Generado: 7 semanas','127.0.0.1',NULL,'2026-07-08 22:46:43'),(41,3,'Modulo Periodizacion','UPDATE',6,'plan_atr',NULL,'Generado: 12 semanas','127.0.0.1',NULL,'2026-07-08 22:47:00'),(42,3,'Modulo Periodizacion','UPDATE',2,'plan_atr',NULL,'Generado: 7 semanas','127.0.0.1',NULL,'2026-07-08 22:47:06'),(43,3,'Modulo Periodizacion','UPDATE',6,'macrociclo',NULL,'Preparacion Gala Regional','127.0.0.1',NULL,'2026-07-08 22:47:31'),(44,3,'Seguridad','UPDATE',4,'contrasena','***','***','127.0.0.1',NULL,'2026-07-08 22:50:37'),(45,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: ultimo respaldo(con pruebas).sql','::1',NULL,'2026-07-09 08:35:47'),(46,3,'Atleta','UPDATE',5,NULL,NULL,NULL,'::1',NULL,'2026-07-12 23:34:17'),(47,3,'Atleta','UPDATE',1,NULL,NULL,NULL,'::1',NULL,'2026-07-12 23:38:30'),(48,3,'Lesiones','INSERT',NULL,'Nueva lesión registrada',NULL,'{\"id_lesion\":\"\",\"accion\":\"registrar\",\"id_atleta\":\"5\",\"fecha_inicio\":\"2026-07-23\",\"fecha_estimada_recup\":\"2026-07-23\",\"zona_anatomica\":\"Hombro\",\"lado\":\"Izquierdo\",\"tipo\":\"Sobreuso\",\"nivel_molestia\":\"3\",\"diagnostico\":\"dadadsdasdaa\",\"tratamiento\":\"asdasdad\",\"profesional\":\"asdasd\",\"estado\":\"Activa\",\"observaciones\":\"adsdasda\"}','::1',NULL,'2026-07-23 19:12:24'),(49,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: SGRD_Backup_2026-07-24_06-02-15.sql','::1',NULL,'2026-07-26 17:45:17'),(50,3,'Seguridad','CREATE',0,'usuario',NULL,'Veronica Villamizar','::1',NULL,'2026-07-26 18:03:11'),(51,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 4','::1',NULL,'2026-07-28 17:19:39'),(52,3,'Modulo Sesiones','UPDATE',37,'datos sesion',NULL,'Modificación de planificación','::1',NULL,'2026-07-28 17:20:05'),(53,3,'Modulo Sesiones','',37,'estado',NULL,'Cancelada','::1',NULL,'2026-07-28 17:20:19'),(54,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-07-29 14:59:50'),(55,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 7','::1',NULL,'2026-07-29 18:21:07'),(56,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 7','::1',NULL,'2026-07-29 18:36:52'),(57,3,'Modulo Sesiones','UPDATE',38,'datos sesion',NULL,'Modificación de planificación','::1',NULL,'2026-07-30 16:41:42'),(58,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 8','::1',NULL,'2026-07-30 16:42:30'),(59,3,'Atleta','INSERT',NULL,NULL,NULL,NULL,'::1',NULL,'2026-08-05 15:29:32'),(60,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 7','::1',NULL,'2026-08-05 15:31:55'),(61,3,'Modulo Sesiones','UPDATE',41,'datos sesion',NULL,'Modificación de planificación','::1',NULL,'2026-08-05 15:32:13'),(62,3,'Modulo Temporadas','CREATE',NULL,'temporada',NULL,'Temporada 2028','::1',NULL,'2026-08-08 16:38:30'),(63,3,'Modulo Temporadas','UPDATE',2,'activa',NULL,'Activada','::1',NULL,'2026-08-08 16:38:40'),(64,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 5','::1',NULL,'2026-08-12 12:59:29'),(65,3,'Modulo Sesiones','UPDATE',42,'datos sesion',NULL,'Modificación de planificación','::1',NULL,'2026-08-12 12:59:47'),(66,3,'Modulo Sesiones','',42,'estado',NULL,'Cancelada','::1',NULL,'2026-08-12 12:59:55'),(67,3,'Modulo Sesiones','UPDATE',39,'estado/ejecucion',NULL,'Estado cambiado a Completada','::1',NULL,'2026-08-12 13:00:05'),(68,3,'Mantenimiento','EXPORT',NULL,'Base de Datos',NULL,'Backup generado: SGRD_Backup_2026-08-12_20-02-27.sql','::1',NULL,'2026-08-12 14:02:30'),(69,3,'Mantenimiento','RESTORE',NULL,'Base de Datos','Estado Anterior','Restauración ejecutada con: SGRD_Backup_2026-08-14_23-06-49.sql','::1',NULL,'2026-08-14 17:07:11'),(70,3,'Modulo Sesiones','UPDATE',40,'datos sesion',NULL,'Modificación de planificación','::1',NULL,'2026-08-15 21:38:52'),(71,3,'Modulo Sesiones','INSERT',NULL,'sesiones',NULL,'Planificada para grupo: 7','::1',NULL,'2026-08-15 21:39:31'),(72,3,'Seguridad','CREATE',0,'usuario',NULL,'Carolina Valentina Rios Suarez','::1',NULL,'2026-08-18 16:30:22'),(73,3,'Seguridad','UPDATE',21,'contrasena','***','***','::1',NULL,'2026-08-18 16:57:59');
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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
INSERT INTO `intentos_login` VALUES (1,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 07:55:08'),(2,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 07:57:38'),(3,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:19:51'),(4,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:21:20'),(5,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:23:49'),(6,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:28:00'),(7,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:29:14'),(8,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-12 08:37:38'),(9,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-13 15:35:40'),(10,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-14 09:53:03'),(11,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-14 10:21:50'),(12,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-14 10:45:18'),(13,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-14 12:10:34'),(14,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-15 10:29:30'),(15,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-23 13:18:41'),(16,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-24 14:15:37'),(17,3,'admin@sgrd.com','127.0.0.1',1,'2026-06-24 15:09:58'),(18,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-02 18:51:55'),(19,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-04 16:43:59'),(20,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-05 15:17:15'),(21,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-07 11:28:26'),(22,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-08 20:27:17'),(23,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-08 22:22:22'),(24,24,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-12 07:00:00'),(25,25,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-13 07:00:00'),(26,26,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-14 07:05:00'),(27,27,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-15 07:02:00'),(28,28,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-16 07:01:00'),(29,29,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-17 07:03:00'),(30,30,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-18 07:00:00'),(31,31,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-22 07:01:00'),(32,32,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-23 07:02:00'),(33,33,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-29 07:00:00'),(34,34,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-06-30 07:01:00'),(35,35,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-07-06 07:02:00'),(36,36,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-07-07 07:01:00'),(37,37,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-07-13 07:00:00'),(38,38,'carlos.rodriguez@gmail.com','192.168.1.10',1,'2026-07-16 07:01:00'),(39,39,'antonio.h@gmail.com','192.168.1.15',1,'2026-06-13 07:04:00'),(40,40,'antonio.h@gmail.com','192.168.1.15',1,'2026-06-17 07:05:00'),(41,41,'antonio.h@gmail.com','192.168.1.15',1,'2026-06-24 07:03:00'),(42,42,'luisa.martinez@gmail.com','192.168.1.20',1,'2026-06-15 07:05:00'),(43,43,'luisa.martinez@gmail.com','192.168.1.20',1,'2026-06-17 07:08:00'),(44,44,'luisa.martinez@gmail.com','192.168.1.20',1,'2026-07-07 07:01:00'),(45,45,'luisa.martinez@gmail.com','192.168.1.20',1,'2026-07-16 07:02:00'),(46,46,'admin@sgrd.com','127.0.0.1',1,'2026-07-08 22:30:00'),(47,47,'admin@sgrd.com','127.0.0.1',1,'2026-07-09 08:00:00'),(48,48,'admin@sgrd.com','127.0.0.1',1,'2026-07-09 09:15:00'),(49,20,'maria.garcia@hotmail.com','127.0.0.1',0,'2026-07-08 22:49:09'),(50,20,'maria.garcia@hotmail.com','127.0.0.1',0,'2026-07-08 22:49:52'),(51,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-08 22:50:09'),(52,4,'correo@correo.com','127.0.0.1',1,'2026-07-08 22:50:54'),(53,3,'admin@sgrd.com','127.0.0.1',1,'2026-07-08 22:51:33'),(54,5,'josemiguel.pirolo@gmail.com','192.168.28.88',1,'2026-07-09 08:45:41'),(55,3,'admin@sgrd.com','::1',1,'2026-07-10 13:24:31'),(56,3,'admin@sgrd.com','::1',1,'2026-07-12 20:18:43'),(57,3,'admin@sgrd.com','::1',1,'2026-07-12 20:28:19'),(58,3,'admin@sgrd.com','::1',1,'2026-07-18 21:04:26'),(59,3,'admin@sgrd.com','::1',1,'2026-07-18 21:16:49'),(60,3,'admin@sgrd.com','::1',1,'2026-07-18 21:23:33'),(61,3,'admin@sgrd.com','::1',1,'2026-07-18 21:27:07'),(62,3,'admin@sgrd.com','::1',1,'2026-07-18 21:39:21'),(63,3,'admin@sgrd.com','::1',1,'2026-07-22 21:05:18'),(64,3,'admin@sgrd.com','::1',1,'2026-07-22 21:25:11'),(65,3,'admin@sgrd.com','::1',1,'2026-07-22 21:41:04'),(66,3,'admin@sgrd.com','::1',1,'2026-07-23 18:30:30'),(67,3,'admin@sgrd.com','::1',1,'2026-07-23 18:33:44'),(68,3,'admin@sgrd.com','::1',1,'2026-07-23 18:36:36'),(69,3,'admin@sgrd.com','::1',1,'2026-07-23 19:21:50'),(70,3,'admin@sgrd.com','::1',1,'2026-07-23 19:34:27'),(71,3,'admin@sgrd.com','::1',1,'2026-07-23 19:48:02'),(72,3,'admin@sgrd.com','::1',1,'2026-07-23 20:05:40'),(73,3,'admin@sgrd.com','::1',1,'2026-07-23 20:11:13'),(74,3,'admin@sgrd.com','::1',1,'2026-07-23 20:25:57'),(75,3,'admin@sgrd.com','::1',1,'2026-07-23 21:05:16'),(76,21,'vero.cvv10@gmail.com','::1',1,'2026-07-26 18:03:29'),(77,3,'admin@sgrd.com','::1',1,'2026-07-26 18:05:27'),(78,3,'admin@sgrd.com','::1',1,'2026-07-28 16:41:09'),(79,3,'admin@sgrd.com','::1',1,'2026-07-29 14:15:32'),(80,3,'admin@sgrd.com','::1',1,'2026-07-29 18:17:11'),(81,3,'admin@sgrd.com','::1',1,'2026-07-30 16:14:20'),(82,3,'admin@sgrd.com','::1',1,'2026-07-30 17:17:36'),(83,3,'admin@sgrd.com','::1',1,'2026-08-05 15:10:07'),(84,3,'admin@sgrd.com','::1',1,'2026-08-08 14:42:16'),(85,3,'admin@sgrd.com','::1',1,'2026-08-12 11:41:41'),(86,3,'admin@sgrd.com','::1',1,'2026-08-13 14:26:55'),(87,3,'admin@sgrd.com','::1',1,'2026-08-13 18:17:09'),(88,3,'admin@sgrd.com','::1',1,'2026-08-13 22:57:57'),(89,3,'admin@sgrd.com','::1',1,'2026-08-13 23:08:21'),(90,3,'admin@sgrd.com','::1',1,'2026-08-14 16:20:25'),(91,3,'admin@sgrd.com','::1',1,'2026-08-14 16:42:40'),(92,3,'admin@sgrd.com','::1',1,'2026-08-14 16:48:49'),(93,3,'admin@sgrd.com','::1',1,'2026-08-14 16:53:44'),(94,3,'admin@sgrd.com','::1',1,'2026-08-14 17:05:11'),(95,3,'admin@sgrd.com','::1',1,'2026-08-14 17:07:44'),(96,3,'admin@sgrd.com','::1',1,'2026-08-14 17:14:10'),(97,3,'admin@sgrd.com','::1',1,'2026-08-15 21:07:56'),(98,3,'admin@sgrd.com','::1',1,'2026-08-15 21:40:00'),(99,3,'admin@sgrd.com','::1',1,'2026-08-16 10:21:51'),(100,3,'admin@sgrd.com','::1',1,'2026-08-18 16:15:44'),(101,22,'carolina@gmail.com','::1',0,'2026-08-18 16:56:09'),(102,22,'carolina@gmail.com','::1',0,'2026-08-18 16:56:47'),(103,NULL,'admin@gmail.com','::1',0,'2026-08-18 16:57:12'),(104,3,'admin@sgrd.com','::1',1,'2026-08-18 16:57:30'),(105,21,'vero.cvv10@gmail.com','::1',1,'2026-08-18 16:58:20'),(106,3,'admin@sgrd.com','::1',1,'2026-08-19 21:20:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'atletas','ver','Ver expedientes de atletas'),(2,'atletas','crear','Crear nuevo expediente'),(3,'atletas','editar','Editar expediente existente'),(4,'atletas','eliminar','Cambiar estado del atleta (baja lógica)'),(5,'asistencia','ver','Ver registros de asistencia'),(6,'asistencia','registrar','Registrar asistencia QR o manual'),(7,'carriles','ver','Ver asignación de carriles'),(8,'carriles','gestionar','Crear/editar asignaciones de carriles y horarios'),(9,'sesiones','ver','Ver sesiones planificadas'),(10,'sesiones','crear','Crear sesiones de entrenamiento'),(11,'sesiones','editar','Editar sesiones existentes'),(12,'sesiones','completar','Registrar volumen ejecutado post-sesión'),(13,'drills','ver','Ver catálogo de ejercicios'),(14,'drills','crear','Crear nuevos ejercicios'),(15,'drills','editar','Editar ejercicios existentes'),(16,'marcas','ver','Ver marcas registradas'),(17,'marcas','registrar','Registrar nuevas marcas'),(18,'antropometria','ver','Ver mediciones antropométricas'),(19,'antropometria','registrar','Registrar nuevas mediciones'),(20,'lesiones','ver','Ver historial de lesiones'),(21,'lesiones','registrar','Registrar nuevas lesiones'),(22,'lesiones','editar','Actualizar estado y protocolo de retorno'),(23,'rpe','ver','Ver registros de RPE'),(24,'rpe','registrar','Registrar RPE post-sesión'),(25,'eventos','ver','Ver calendario de eventos'),(26,'eventos','crear','Crear eventos'),(27,'eventos','editar','Editar eventos existentes'),(28,'carga','ver','Ver métricas de carga ACWR/TSS'),(29,'rankings','ver','Consultar rankings'),(30,'reportes','generar','Generar reportes PDF'),(31,'periodizacion','ver','Ver planes de periodización'),(32,'periodizacion','generar','Generar plan ATR automático'),(33,'periodizacion','editar','Editar plan de periodización'),(34,'entrenadores','ver','Ver información de entrenadores'),(35,'seguridad','usuarios','Gestión de usuarios del sistema'),(36,'seguridad','roles','Gestión de roles y permisos'),(37,'seguridad','bitacora','Consulta de bitácora del sistema'),(38,'seguridad','backup','Realizar backups y restauraciones'),(39,'seguridad','login','Iniciar sesión'),(40,'seguridad','logout','Cerrar sesión'),(41,'metas','ver','Ver metas competitivas'),(42,'metas','gestionar','Crear/editar metas competitivas'),(43,'atletas','ver_propio','Atleta: ver su propio expediente'),(44,'atletas','rpe_propio','Atleta: registrar su propio RPE'),(45,'representantes','ver_hijos','Representante: ver expedientes de sus atletas'),(46,'representantes','asistencia_hijos','Representante: ver asistencia de sus atletas'),(47,'representantes','rpe_hijos','Representante: ver RPE de sus atletas'),(49,'seguridad','mantenimiento','Acceso al módulo de mantenimiento y respaldos'),(50,'testFisico','ver','Ver modulo de tests fisicos'),(51,'testFisico','registrar','Registrar, editar y eliminar tests fisicos'),(52,'lesiones','eliminar','Eliminar lesion (baja logica)'),(53,'lesiones','eliminardb','Eliminar lesion de la base de datos'),(54,'lesiones','reactivar','Reactivar lesion eliminada'),(55,'rpe','anular','Anular registro RPE'),(56,'carga_bienestar','registrar','Registrar carga de bienestar'),(57,'carga_bienestar','anular','Anular carga de bienestar'),(58,'normalizacion','ver','Ver modulo de normalizacion'),(59,'normalizacion','registrar','Registrar normalizacion'),(60,'normalizacion','editar','Editar normalizacion'),(61,'normalizacion','eliminar','Eliminar normalizacion'),(62,'normalizacion','anular','Anular normalizacion'),(63,'normalizacion_tiempos','registrar','Registrar normalizacion de tiempos'),(64,'observacionesTecnicas','ver','Ver modulo de observaciones tecnicas'),(65,'observacionesTecnicas','registrar','Registrar observacion tecnica'),(66,'representantes','ver','Ver modulo de representantes'),(67,'representantes','gestionar','Gestionar representantes'),(68,'temporadas','ver','Ver modulo de temporadas'),(69,'temporadas','registrar','Registrar temporada'),(70,'mi_perfil','ver','Ver y editar perfil propio'),(71,'sesiones','eliminar','Eliminar sesion de entrenamiento'),(72,'atletas','gestionar','Gestionar atletas (crear, editar, eliminar)'),(73,'marcas','editar','puede editar'),(74,'marcas','eliminar','se puede eliminar'),(75,'marcas','restaurar','se puede restaurar'),(76,'grupo','ver','grupo de entrenamientos'),(78,'asignacion','ver','ver asignacion de carriles'),(79,'asignacion','gestionar','crear,  editar y eliminar'),(80,'horario','ver','ver horarios'),(81,'horario','gestionar','crear, editar, eliminar'),(82,'reportes','ver','ver reportes'),(83,'reportes','exportar','exportar pdf');
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
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (213,1,1),(210,1,2),(211,1,3),(212,1,4),(209,1,5),(208,1,6),(216,1,7),(215,1,8),(248,1,9),(246,1,10),(247,1,11),(245,1,12),(219,1,13),(217,1,14),(218,1,15),(228,1,16),(227,1,17),(207,1,18),(206,1,19),(226,1,20),(225,1,21),(224,1,22),(237,1,23),(236,1,24),(223,1,25),(221,1,26),(222,1,27),(214,1,28),(234,1,29),(235,1,30),(233,1,31),(232,1,32),(231,1,33),(220,1,34),(244,1,35),(243,1,36),(239,1,37),(238,1,38),(240,1,39),(241,1,40),(230,1,41),(229,1,42),(242,1,49),(249,1,50),(250,1,51),(256,1,52),(260,1,53),(264,1,54),(268,1,55),(272,1,56),(274,1,57),(276,1,58),(278,1,59),(280,1,60),(282,1,61),(284,1,62),(286,1,63),(288,1,64),(290,1,65),(292,1,66),(293,1,67),(294,1,68),(296,1,69),(298,1,70),(303,1,71),(254,1,72),(311,1,73),(312,1,74),(313,1,75),(316,1,76),(318,1,78),(320,1,79),(322,1,80),(323,1,81),(329,1,82),(332,1,83),(70,2,1),(68,2,2),(69,2,3),(67,2,5),(66,2,6),(72,2,7),(95,2,9),(93,2,10),(94,2,11),(92,2,12),(75,2,13),(73,2,14),(74,2,15),(84,2,16),(83,2,17),(65,2,18),(64,2,19),(82,2,20),(81,2,21),(91,2,23),(90,2,24),(78,2,25),(76,2,26),(77,2,27),(71,2,28),(88,2,29),(89,2,30),(87,2,31),(86,2,32),(85,2,33),(80,2,34),(79,2,41),(153,2,43),(251,2,50),(252,2,51),(257,2,52),(261,2,53),(265,2,54),(269,2,55),(273,2,56),(275,2,57),(277,2,58),(279,2,59),(281,2,60),(283,2,61),(285,2,62),(287,2,63),(289,2,64),(291,2,65),(295,2,68),(297,2,69),(299,2,70),(304,2,71),(255,2,72),(310,2,73),(309,2,74),(308,2,75),(129,3,1),(314,3,16),(128,3,18),(127,3,19),(132,3,20),(131,3,21),(130,3,22),(156,3,23),(157,3,24),(154,3,43),(155,3,44),(158,3,47),(253,3,50),(258,3,52),(262,3,53),(266,3,54),(270,3,55),(300,3,70),(136,4,1),(142,4,16),(135,4,18),(134,4,19),(140,4,20),(139,4,21),(144,4,23),(143,4,24),(138,4,25),(137,4,28),(259,4,52),(263,4,53),(267,4,54),(271,4,55),(301,4,70),(149,5,1),(315,5,16),(150,5,25),(305,5,45),(306,5,46),(307,5,47),(302,5,70);
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_roles`
--

LOCK TABLES `usuario_roles` WRITE;
/*!40000 ALTER TABLE `usuario_roles` DISABLE KEYS */;
INSERT INTO `usuario_roles` VALUES (2,3,1,'2026-06-06 15:30:31'),(3,4,4,'2026-06-06 17:58:37'),(4,5,2,'2026-06-08 23:57:30'),(5,6,3,'2026-06-10 13:39:04'),(6,1,2,'2025-01-15 10:00:00'),(7,2,5,'2025-01-20 11:30:00'),(8,7,2,'2026-02-01 09:15:00'),(9,8,2,'2026-02-10 14:00:00'),(10,9,3,'2026-03-01 10:30:00'),(11,10,2,'2026-03-20 09:00:00'),(12,11,3,'2026-04-01 10:00:00'),(13,12,5,'2026-02-15 08:45:00'),(14,13,5,'2026-03-10 11:00:00'),(15,14,5,'2026-03-15 13:00:00'),(16,15,3,'2026-04-10 14:30:00'),(17,16,5,'2026-04-15 08:30:00'),(18,17,5,'2026-04-20 11:15:00'),(19,18,5,'2026-05-01 10:45:00'),(20,19,5,'2026-05-05 09:30:00'),(21,20,5,'2026-01-20 11:30:00'),(22,21,2,'2026-07-26 18:03:11'),(23,22,4,'2026-08-18 16:30:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'V-00000001','Carlos Eduardo','Rodriguez Mendoza','carlos.rodriguez@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2025-01-15 10:00:00',NULL,NULL),(2,'V-00000002','Josefina','Navarro Corro','josefinavarro@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2025-01-20 11:30:00',NULL,NULL),(3,'00000000','Administrador','Sistema','admin@sgrd.com','$2y$10$26SZiPtKViEwm7tHHEVWl.Z8Y2U.gMOIpdZjYNTeoXUQtMqZH.LZu',1,NULL,0,NULL,NULL,'2026-06-06 15:30:31','2026-08-12 13:09:30','{\"tema\": \"dark\", \"crono_mode\": \"manual\"}'),(4,'28591974','Jesús','Regalado','correo@correo.com','$2y$10$ujsRprDBXNievFONorjWbeNU.a8wbMR2L7HBJXK1KItnL2hIJptMe',1,NULL,0,NULL,NULL,'2026-06-06 17:58:37','2026-07-08 22:50:37',NULL),(5,'25854831','Jose Miguel','Pirolo Narro','josemiguel.pirolo@gmail.com','$2y$10$YhNg4TYimPC8qRTv8YTcLOAmICRO6BGOVLCPglZdUkZKlC6N5mElK',1,NULL,0,NULL,NULL,'2026-06-08 23:57:30',NULL,NULL),(6,'28425405','jose','pirolo','joseantoniopirolo@sgrd.com','$2y$10$62ynRLsgfM0WBCinzTla7eaBT3y/dazo.rVqcOLQeH6rM3rTnXil2',1,NULL,0,NULL,NULL,'2026-06-10 13:39:04',NULL,NULL),(7,'V-15234567','Antonio Jose','Hernandez Perez','antonio.h@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-02-01 09:15:00',NULL,NULL),(8,'V-16789012','Luisa Fernanda','Martinez Vargas','luisa.martinez@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-02-10 14:00:00',NULL,NULL),(9,'V-14680235','Ana Cristina','Diaz Rios','ana.diaz@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-03-01 10:30:00',NULL,NULL),(10,'V-17113568','Jose Ramon','Castillo Paredes','jose.castillo@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-03-20 09:00:00',NULL,NULL),(11,'V-18224679','Yelitza Maria','Torres Alvarado','yelitza.torres@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-04-01 10:00:00',NULL,NULL),(12,'V-13579024','Rafael Alberto','Lopez Sanchez','rafael.lopez@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-02-15 08:45:00',NULL,NULL),(13,'V-15891346','Pedro Manuel','Vargas Moreno','pedro.vargas@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-03-10 11:00:00',NULL,NULL),(14,'V-16002457','Carmen Elena','Rojas Blanco','carmen.rojas@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-03-15 13:00:00',NULL,NULL),(15,'V-19335780','Miguel Angel','Fuentes Castillo','miguel.fuentes@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-04-10 14:30:00',NULL,NULL),(16,'V-20446891','Rosa Elena','Paredes Urdaneta','rosa.paredes@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-04-15 08:30:00',NULL,NULL),(17,'V-21557902','Victor Manuel','Urbina Colmenares','victor.urbina@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-04-20 11:15:00',NULL,NULL),(18,'V-22668013','Nancy Patricia','Colmenares Rangel','nancy.colmenares@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-05-01 10:45:00',NULL,NULL),(19,'V-23779124','Alberto Daniel','Rangel Bracho','alberto.rangel@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,0,NULL,NULL,'2026-05-05 09:30:00',NULL,NULL),(20,'V-18990123','Maria Isabel','Garcia Torres','maria.garcia@hotmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,2,NULL,NULL,'2026-01-20 11:30:00','2026-07-08 22:49:52',NULL),(21,'V-30759776','Veronica','Villamizar','vero.cvv10@gmail.com','$2y$10$VVnrbffRnRssU3UyJ/4C8.aMIuo7tLyiAECnmH2s6NSBgeZywWAIK',1,NULL,0,NULL,NULL,'2026-07-26 18:03:11','2026-08-18 16:57:59',NULL),(22,'E-23456788','Carolina Valentina','Rios Suarez','carolina@gmail.com','$2y$10$WQwzR8I7wsBy0p30WIMHGepzX87RCQ9zLkdCSpUXr2cUO4qzuA7di',1,NULL,2,NULL,NULL,'2026-08-18 16:30:22','2026-08-18 16:56:47',NULL);
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

-- Dump completed on 2026-08-19 21:20:49
