-- ============================================================
-- BD INTEGRADA: sis_natacion (Jesus + Vero)
-- Generado: 2026-06-12
-- 
-- Contiene:
--   - Base de datos sis_natacion (estructura + datos de produccion)
--   - Base de datos sis_seguridad (estructura + datos de produccion)
--   - Tablas nuevas de Vero: entrenador, grupo_entrenador
--   - Tablas hibridas: grupos_entrenamiento y sesiones con
--     AMBAS columnas (id_usuario + id_entrenador)
--   - Columnas extra de Jesus: token_asistencia en atletas,
--     activo/motivo_eliminacion en lesiones,
--     estado/motivo_eliminacion en marcas,
--     estado en representantes
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

-- ============================================================
-- BASE DE DATOS: sis_natacion
-- ============================================================

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sis_natacion` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sis_natacion`;

-- --------------------------------------------------------
-- Tabla: asignacion_carril
-- --------------------------------------------------------

DROP TABLE IF EXISTS `asignacion_carril`;
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

-- --------------------------------------------------------
-- Tabla: asistencia
-- --------------------------------------------------------

DROP TABLE IF EXISTS `asistencia`;
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

-- --------------------------------------------------------
-- Tabla: atleta_datos_medicos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `atleta_datos_medicos`;
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

-- --------------------------------------------------------
-- Tabla: atleta_representante
-- --------------------------------------------------------

DROP TABLE IF EXISTS `atleta_representante`;
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

-- --------------------------------------------------------
-- Tabla: atletas
-- (Incluye token_asistencia de Jesus para modulo de QR)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `atletas`;
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

-- --------------------------------------------------------
-- Tabla: bloques_horarios
-- --------------------------------------------------------

DROP TABLE IF EXISTS `bloques_horarios`;
CREATE TABLE `bloques_horarios` (
  `id_bloque` int(11) NOT NULL AUTO_INCREMENT,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id_bloque`),
  UNIQUE KEY `dia_semana` (`dia_semana`,`hora_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: carga_diaria
-- --------------------------------------------------------

DROP TABLE IF EXISTS `carga_diaria`;
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

-- --------------------------------------------------------
-- Tabla: carriles
-- --------------------------------------------------------

DROP TABLE IF EXISTS `carriles`;
CREATE TABLE `carriles` (
  `id_carril` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `capacidad_maxima` int(11) NOT NULL DEFAULT 6,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_carril`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: categorias_feveda
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categorias_feveda`;
CREATE TABLE `categorias_feveda` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `edad_minima` int(11) NOT NULL,
  `edad_maxima` int(11) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: drills
-- --------------------------------------------------------

DROP TABLE IF EXISTS `drills`;
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

-- --------------------------------------------------------
-- Tabla: entrenador (NUEVA - de Vero)
-- Tabla independiente para datos especificos del entrenador.
-- id_usuario enlaza con sis_seguridad.
-- --------------------------------------------------------

DROP TABLE IF EXISTS `entrenador`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: entrenador_asignacion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `entrenador_asignacion`;
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

-- --------------------------------------------------------
-- Tabla: eventos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `eventos`;
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

-- --------------------------------------------------------
-- Tabla: evento_inscripcion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `evento_inscripcion`;
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

-- --------------------------------------------------------
-- Tabla: factores_conversion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `factores_conversion`;
CREATE TABLE `factores_conversion` (
  `id_factor` int(11) NOT NULL AUTO_INCREMENT,
  `estilo` enum('Libre','Espalda','Braza','Mariposa','Combinado') NOT NULL,
  `distancia_m` int(11) NOT NULL,
  `direccion` enum('25_a_50','50_a_25') NOT NULL,
  `factor` decimal(6,4) NOT NULL,
  `fuente` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_factor`),
  UNIQUE KEY `estilo` (`estilo`,`distancia_m`,`direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: fases_periodizacion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `fases_periodizacion`;
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

-- --------------------------------------------------------
-- Tabla: grupos_entrenamiento (HIBRIDA)
-- Mantiene AMBAS columnas para compatibilidad temporal:
--   - id_usuario: usada por modulos de Jesus (sis_seguridad)
--   - id_entrenador: usada por modulos de Vero (tabla entrenador)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `grupos_entrenamiento`;
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

-- --------------------------------------------------------
-- Tabla: grupo_atleta
-- --------------------------------------------------------

DROP TABLE IF EXISTS `grupo_atleta`;
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

-- --------------------------------------------------------
-- Tabla: grupo_entrenador (NUEVA - de Vero)
-- Historial de asignaciones entrenador-grupo con fechas.
-- --------------------------------------------------------

DROP TABLE IF EXISTS `grupo_entrenador`;
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

-- --------------------------------------------------------
-- Tabla: lesiones
-- (Incluye activo y motivo_eliminacion de la produccion)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `lesiones`;
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

-- --------------------------------------------------------
-- Tabla: macrociclos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `macrociclos`;
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

-- --------------------------------------------------------
-- Tabla: marcas
-- (Incluye estado y motivo_eliminacion de Jesus)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `marcas`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: marcas_splits
-- --------------------------------------------------------

DROP TABLE IF EXISTS `marcas_splits`;
CREATE TABLE `marcas_splits` (
  `id_split` int(11) NOT NULL AUTO_INCREMENT,
  `id_marca` int(11) NOT NULL,
  `parcial_numero` int(11) NOT NULL,
  `distancia_parcial_m` int(11) NOT NULL DEFAULT 50,
  `tiempo_parcial_seg` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id_split`),
  UNIQUE KEY `id_marca` (`id_marca`,`parcial_numero`),
  CONSTRAINT `fk_ms_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: marcas_swolf
-- --------------------------------------------------------

DROP TABLE IF EXISTS `marcas_swolf`;
CREATE TABLE `marcas_swolf` (
  `id_swolf` int(11) NOT NULL AUTO_INCREMENT,
  `id_marca` int(11) NOT NULL,
  `num_brazadas` int(11) NOT NULL,
  `swolf` int(11) NOT NULL,
  PRIMARY KEY (`id_swolf`),
  UNIQUE KEY `id_marca` (`id_marca`),
  CONSTRAINT `fk_sw_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: mediciones_antropometricas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `mediciones_antropometricas`;
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

-- --------------------------------------------------------
-- Tabla: mesociclos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `mesociclos`;
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

-- --------------------------------------------------------
-- Tabla: metas_competitivas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `metas_competitivas`;
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

-- --------------------------------------------------------
-- Tabla: microciclos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `microciclos`;
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

-- --------------------------------------------------------
-- Tabla: protocolo_retorno
-- --------------------------------------------------------

DROP TABLE IF EXISTS `protocolo_retorno`;
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

-- --------------------------------------------------------
-- Tabla: registro_rpe
-- --------------------------------------------------------

DROP TABLE IF EXISTS `registro_rpe`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: representantes
-- (Incluye estado de Jesus)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `representantes`;
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

-- --------------------------------------------------------
-- Tabla: series_sesion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `series_sesion`;
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

-- --------------------------------------------------------
-- Tabla: sesiones (HIBRIDA)
-- Mantiene AMBAS columnas para compatibilidad temporal:
--   - id_usuario_creador: usada por modulos de Jesus
--   - id_entrenador: usada por modulos de Vero
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sesiones`;
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

-- --------------------------------------------------------
-- Tabla: temporadas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `temporadas`;
CREATE TABLE `temporadas` (
  `id_temporada` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_temporada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: tiempos_corte_evento
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tiempos_corte_evento`;
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

-- ============================================================
-- DATOS DE PRODUCCION - sis_natacion
-- ============================================================

-- --------------------------------------------------------
-- Datos: atleta_representante
-- --------------------------------------------------------

LOCK TABLES `atleta_representante` WRITE;
ALTER TABLE `atleta_representante` DISABLE KEYS;
INSERT INTO `atleta_representante` VALUES (1,1,1,0,NULL,0,NULL,0),(14,4,2,0,NULL,0,NULL,0),(15,3,2,0,NULL,0,NULL,0);
ALTER TABLE `atleta_representante` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: atletas
-- --------------------------------------------------------

LOCK TABLES `atletas` WRITE;
ALTER TABLE `atletas` DISABLE KEYS;
INSERT INTO `atletas` VALUES (1,'25854831','Jose Miguel','Pirolo Navarro','2016-05-10','M','Tamaca, Las Delicias','04120560145','josemiguel.pirolo@gmail.com',NULL,'2025-05-14','Activo',NULL,2,NULL,'2026-05-20 15:56:31',NULL),(2,'28425405','Jose Antonio','Pirolo Navarro','2017-05-24','M','Tamaca Las Delicias','04121112323','jose@gmsil.com',NULL,'2024-05-01','Activo',NULL,2,NULL,'2026-05-20 17:49:29',NULL),(3,'31536131','Joselin Paola','Pirolo Navarro','2017-05-10','M','Tamaca, Las Delicias','04120560145','jose@gmail.com',NULL,'2025-05-07','Activo',NULL,2,NULL,'2026-05-20 17:50:59',NULL),(4,'32296296','Francelys Adriana','Camacho Rivero','2017-05-01','F','MEtropolis','04121112323','jose@gmail.com',NULL,'2026-05-04','Activo',NULL,3,NULL,'2026-05-20 22:37:47',NULL);
ALTER TABLE `atletas` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: carriles
-- --------------------------------------------------------

LOCK TABLES `carriles` WRITE;
ALTER TABLE `carriles` DISABLE KEYS;
INSERT INTO `carriles` VALUES (1,1,6,1),(2,2,6,1),(3,3,6,1),(4,4,6,1),(5,5,6,1),(6,6,6,1);
ALTER TABLE `carriles` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: categorias_feveda
-- --------------------------------------------------------

LOCK TABLES `categorias_feveda` WRITE;
ALTER TABLE `categorias_feveda` DISABLE KEYS;
INSERT INTO `categorias_feveda` VALUES (1,'Pre-Infantil',8,10,1),(2,'Infantil A',11,12,1),(3,'Infantil B',13,14,1),(4,'Juvenil A',15,16,1),(5,'Juvenil B',17,18,1),(6,'Maxima',19,99,1);
ALTER TABLE `categorias_feveda` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: entrenador (de Vero)
-- --------------------------------------------------------

LOCK TABLES `entrenador` WRITE;
ALTER TABLE `entrenador` DISABLE KEYS;
INSERT INTO `entrenador` VALUES (3,'8591799','JOSEFINA','Pirolo','1969-07-12','F','josefinavarro@gmail.com','04120560145','Carrera 5 Entre Calles 6 Y 7 Tamaca','',3);
ALTER TABLE `entrenador` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: factores_conversion
-- --------------------------------------------------------

LOCK TABLES `factores_conversion` WRITE;
ALTER TABLE `factores_conversion` DISABLE KEYS;
INSERT INTO `factores_conversion` VALUES (1,'Libre',50,'25_a_50',1.0000,'World Aquatics'),(2,'Libre',100,'25_a_50',1.0300,'World Aquatics'),(3,'Libre',200,'25_a_50',1.0600,'World Aquatics'),(4,'Libre',400,'25_a_50',1.0800,'World Aquatics'),(5,'Libre',800,'25_a_50',1.1000,'World Aquatics'),(6,'Libre',1500,'25_a_50',1.1200,'World Aquatics'),(7,'Espalda',50,'25_a_50',1.0000,'World Aquatics'),(8,'Espalda',100,'25_a_50',1.0300,'World Aquatics'),(9,'Espalda',200,'25_a_50',1.0600,'World Aquatics'),(10,'Braza',50,'25_a_50',1.0000,'World Aquatics'),(11,'Braza',100,'25_a_50',1.0400,'World Aquatics'),(12,'Braza',200,'25_a_50',1.0800,'World Aquatics'),(13,'Mariposa',50,'25_a_50',1.0000,'World Aquatics'),(14,'Mariposa',100,'25_a_50',1.0400,'World Aquatics'),(15,'Mariposa',200,'25_a_50',1.0800,'World Aquatics'),(16,'Combinado',100,'25_a_50',1.0300,'World Aquatics'),(17,'Combinado',200,'25_a_50',1.0600,'World Aquatics'),(18,'Combinado',400,'25_a_50',1.0900,'World Aquatics'),(19,'Libre',50,'50_a_25',1.0000,'World Aquatics'),(20,'Libre',100,'50_a_25',0.9709,'World Aquatics'),(21,'Libre',200,'50_a_25',0.9434,'World Aquatics'),(22,'Libre',400,'50_a_25',0.9259,'World Aquatics'),(23,'Libre',800,'50_a_25',0.9091,'World Aquatics'),(24,'Libre',1500,'50_a_25',0.8929,'World Aquatics'),(25,'Espalda',50,'50_a_25',1.0000,'World Aquatics'),(26,'Espalda',100,'50_a_25',0.9709,'World Aquatics'),(27,'Espalda',200,'50_a_25',0.9434,'World Aquatics'),(28,'Braza',50,'50_a_25',1.0000,'World Aquatics'),(29,'Braza',100,'50_a_25',0.9615,'World Aquatics'),(30,'Braza',200,'50_a_25',0.9259,'World Aquatics'),(31,'Mariposa',50,'50_a_25',1.0000,'World Aquatics'),(32,'Mariposa',100,'50_a_25',0.9615,'World Aquatics'),(33,'Mariposa',200,'50_a_25',0.9259,'World Aquatics'),(34,'Combinado',100,'50_a_25',0.9709,'World Aquatics'),(35,'Combinado',200,'50_a_25',0.9434,'World Aquatics'),(36,'Combinado',400,'50_a_25',0.9174,'World Aquatics');
ALTER TABLE `factores_conversion` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: grupos_entrenamiento (HIBRIDA - id_entrenador NULL por ahora)
-- --------------------------------------------------------

LOCK TABLES `grupos_entrenamiento` WRITE;
ALTER TABLE `grupos_entrenamiento` DISABLE KEYS;
INSERT INTO `grupos_entrenamiento` VALUES (1,'Furia criolla','jsjsjsjsjsj',1,NULL,1);
ALTER TABLE `grupos_entrenamiento` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: lesiones (con activo y motivo_eliminacion)
-- --------------------------------------------------------

LOCK TABLES `lesiones` WRITE;
ALTER TABLE `lesiones` DISABLE KEYS;
INSERT INTO `lesiones` VALUES (2,3,'Pie','Izquierdo','Sobreuso',8,'pie dolorido','hielo','2026-06-11','2026-06-12','EnRehabilitacion','jose antonio','sdgsdsdf','2026-06-11 07:57:52','2026-06-11 13:33:35',1,NULL),(3,2,'Hombro','Izquierdo','Sobreuso',3,'contractura','hielo','2026-06-12','2026-06-14','Activa','jose antonio','adfasd','2026-06-12 01:05:51',NULL,1,NULL),(4,2,'Codo','Derecho','Sobreuso',4,'dolor de codo','hielo','2026-06-10','2026-06-12','Activa','josea ntonio','sfasd','2026-06-12 02:14:28',NULL,1,NULL);
ALTER TABLE `lesiones` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: marcas (con estado y motivo_eliminacion)
-- --------------------------------------------------------

LOCK TABLES `marcas` WRITE;
ALTER TABLE `marcas` DISABLE KEYS;
INSERT INTO `marcas` VALUES (1,1,NULL,NULL,'Libre',50,'50m',65.00,NULL,NULL,'Control',1,'2026-05-21','','2026-05-21 18:22:27','Activo',NULL),(2,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,'Control',1,'2026-05-21','presento quejas','2026-05-21 18:31:46','Inactivo','prueba'),(3,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,'Control',0,'2026-05-21','calambre','2026-05-21 20:11:20','Activo',NULL),(4,1,NULL,NULL,'Libre',100,'50m',240.00,NULL,NULL,'Control',1,'2026-05-11','','2026-05-21 20:13:17','Activo',NULL),(5,1,NULL,NULL,'Libre',50,'50m',120.00,NULL,NULL,'Control',0,'2026-05-12','','2026-05-21 20:16:21','Activo',NULL),(6,1,NULL,NULL,'Espalda',50,'50m',120.00,NULL,NULL,'Control',1,'2026-05-14','','2026-05-23 11:57:30','Activo',NULL),(7,4,NULL,NULL,'Espalda',100,'50m',120.00,0.50,0.60,'Control',1,'2026-05-18','Hola','2026-05-31 01:00:43','Activo',NULL),(8,3,NULL,NULL,'Espalda',50,'50m',180.00,0.70,0.50,'Control',1,'2026-05-18','nada','2026-05-31 09:27:44','Activo',NULL),(9,3,NULL,NULL,'Braza',50,'50m',180.00,40.00,50.00,'Control',1,'2026-05-19','nada','2026-05-31 09:48:55','Activo',NULL),(10,4,NULL,NULL,'Libre',50,'50m',60.00,NULL,NULL,'Control',1,'2026-06-08',NULL,'2026-06-08 23:24:00','Activo',NULL);
ALTER TABLE `marcas` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: marcas_splits
-- --------------------------------------------------------

LOCK TABLES `marcas_splits` WRITE;
ALTER TABLE `marcas_splits` DISABLE KEYS;
INSERT INTO `marcas_splits` VALUES (1,1,1,25,32.50),(2,1,2,50,32.50),(3,2,1,25,28.00),(4,2,2,50,32.00),(5,3,1,25,48.00),(6,3,2,50,72.00),(7,4,1,25,60.00),(8,4,2,50,60.00),(9,4,3,75,60.00),(10,4,4,100,60.00),(11,5,1,25,60.00),(12,5,2,50,60.00),(13,6,1,25,60.00),(14,6,2,50,60.00),(15,9,1,25,130.00),(16,9,2,50,50.00),(17,10,1,25,25.00),(18,10,2,50,35.00);
ALTER TABLE `marcas_splits` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: marcas_swolf
-- --------------------------------------------------------

LOCK TABLES `marcas_swolf` WRITE;
ALTER TABLE `marcas_swolf` DISABLE KEYS;
INSERT INTO `marcas_swolf` VALUES (1,2,32,92),(2,3,43,163),(3,4,111,231),(4,5,45,165),(5,6,44,164),(6,9,80,260),(7,10,15,75);
ALTER TABLE `marcas_swolf` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: registro_rpe
-- --------------------------------------------------------

LOCK TABLES `registro_rpe` WRITE;
ALTER TABLE `registro_rpe` DISABLE KEYS;
INSERT INTO `registro_rpe` VALUES (1,3,1,'2026-06-11',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:07:44'),(2,3,NULL,'2026-06-10',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(3,3,NULL,'2026-06-09',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(4,3,NULL,'2026-06-08',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(5,3,NULL,'2026-06-07',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(6,3,NULL,'2026-06-06',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(7,3,NULL,'2026-06-05',7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28'),(8,3,NULL,'2026-06-04',8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-11 16:16:28');
ALTER TABLE `registro_rpe` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: representantes
-- --------------------------------------------------------

LOCK TABLES `representantes` WRITE;
ALTER TABLE `representantes` DISABLE KEYS;
INSERT INTO `representantes` VALUES (1,'8591799','Jose Gregorio','Pirolo Gonzalez','Padre','04121273248','02517183360','jose@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',1,'2026-05-20 16:48:30','Activo'),(2,'10762010','Josefina','Navarro Corro','Madre','04245728016','02517183361','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca, Las Delicias',1,'2026-05-20 18:09:41','Activo'),(3,'2383050','Lourdes','Corro','Tutor','04120121212','02517183360','josefinavarro@gmail.com','Carrera 5 Entre Calles 6 Y 7 Tamaca',1,'2026-05-20 22:40:25','Inactivo');
ALTER TABLE `representantes` ENABLE KEYS;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Datos: sesiones (HIBRIDA - id_entrenador NULL por ahora)
-- --------------------------------------------------------

LOCK TABLES `sesiones` WRITE;
ALTER TABLE `sesiones` DISABLE KEYS;
INSERT INTO `sesiones` VALUES (1,NULL,1,'2026-06-11','Tecnica',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Planificada',NULL,NULL,'2026-06-11 16:05:47',NULL);
ALTER TABLE `sesiones` ENABLE KEYS;
UNLOCK TABLES;

-- ============================================================
-- VISTAS - sis_natacion
-- ============================================================

DROP TABLE IF EXISTS `v_asistencia_resumen`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_asistencia_resumen` AS SELECT `asis`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, count(0) AS `total_registros`, sum(case when `asis`.`estado` = 'Presente' then 1 else 0 end) AS `presentes`, sum(case when `asis`.`estado` in ('Ausente','Justificado') then 1 else 0 end) AS `ausentes`, sum(case when `asis`.`estado` = 'Tardanza' then 1 else 0 end) AS `tardanzas`, round(sum(case when `asis`.`estado` in ('Presente','Tardanza') then 1 else 0 end) / count(0) * 100,1) AS `porcentaje_asistencia` FROM (`asistencia` `asis` join `atletas` `a` on(`asis`.`id_atleta` = `a`.`id_atleta`)) WHERE `asis`.`fecha` >= curdate() - interval 30 day GROUP BY `asis`.`id_atleta`, `a`.`nombres`, `a`.`apellidos`;

DROP TABLE IF EXISTS `v_atleta_info`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_atleta_info` AS SELECT `a`.`id_atleta` AS `id_atleta`, `a`.`cedula` AS `cedula`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `a`.`fecha_nacimiento` AS `fecha_nacimiento`, timestampdiff(YEAR,`a`.`fecha_nacimiento`,curdate()) AS `edad_actual`, `a`.`sexo` AS `sexo`, `a`.`estado` AS `estado`, `c`.`nombre` AS `categoria`, `dm`.`grupo_sanguineo` AS `grupo_sanguineo`, `dm`.`alergias` AS `alergias`, `dm`.`numero_feveda` AS `numero_feveda`, `a`.`fecha_registro_club` AS `fecha_registro_club` FROM ((`atletas` `a` join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) left join `atleta_datos_medicos` `dm` on(`a`.`id_atleta` = `dm`.`id_atleta`));

DROP TABLE IF EXISTS `v_carga_semanal`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_carga_semanal` AS SELECT `cd`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `cd`.`fecha` AS `fecha`, `cd`.`srpe_total` AS `srpe_total`, `cd`.`volumen_total_m` AS `volumen_total_m`, `cd`.`carga_aguda_7d` AS `carga_aguda_7d`, `cd`.`carga_cronica_28d` AS `carga_cronica_28d`, `cd`.`acwr` AS `acwr`, `cd`.`semaforo_acwr` AS `semaforo_acwr`, `cd`.`monotonia_semanal` AS `monotonia_semanal`, `cd`.`strain_semanal` AS `strain_semanal` FROM (`carga_diaria` `cd` join `atletas` `a` on(`cd`.`id_atleta` = `a`.`id_atleta`)) WHERE `cd`.`fecha` >= curdate() - interval 30 day;

DROP TABLE IF EXISTS `v_lesion_activa`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_lesion_activa` AS SELECT `l`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `l`.`zona_anatomica` AS `zona_anatomica`, `l`.`tipo` AS `tipo`, `l`.`nivel_molestia` AS `nivel_molestia`, `l`.`estado` AS `estado`, `l`.`fecha_inicio` AS `fecha_inicio`, `l`.`fecha_estimada_recup` AS `fecha_estimada_recup` FROM (`lesiones` `l` join `atletas` `a` on(`l`.`id_atleta` = `a`.`id_atleta`)) WHERE `l`.`estado` in ('Activa','EnRehabilitacion') ORDER BY `l`.`fecha_inicio` DESC;

DROP TABLE IF EXISTS `v_ranking`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ranking` AS SELECT `m`.`id_marca` AS `id_marca`, `m`.`id_atleta` AS `id_atleta`, `a`.`nombres` AS `nombres`, `a`.`apellidos` AS `apellidos`, `m`.`estilo` AS `estilo`, `m`.`distancia_m` AS `distancia_m`, `m`.`tipo_piscina` AS `tipo_piscina`, `m`.`tiempo_final_seg` AS `tiempo_final_seg`, `m`.`fecha` AS `fecha`, `c`.`nombre` AS `categoria`, `a`.`sexo` AS `sexo`, row_number() over ( partition by `m`.`estilo`,`m`.`distancia_m`,`a`.`id_categoria`,`a`.`sexo` order by `m`.`tiempo_final_seg`) AS `posicion_ranking` FROM ((`marcas` `m` join `atletas` `a` on(`m`.`id_atleta` = `a`.`id_atleta`)) join `categorias_feveda` `c` on(`a`.`id_categoria` = `c`.`id_categoria`)) WHERE `m`.`es_pb` = 1;

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
