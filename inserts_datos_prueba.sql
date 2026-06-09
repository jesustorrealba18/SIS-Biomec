-- ============================================================
-- Inserts de datos de prueba para sis_natacion
-- Generado para pruebas de desarrollo
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- 1. temporadas
-- ============================================================
INSERT INTO `temporadas` (`id_temporada`, `nombre`, `fecha_inicio`, `fecha_fin`, `activa`) VALUES
(1, 'Temporada 2025-2026', '2025-09-01', '2026-08-31', 1),
(2, 'Temporada 2026-2027', '2026-09-01', '2027-08-31', 0);

-- ============================================================
-- 2. grupos_entrenamiento
-- ============================================================
INSERT INTO `grupos_entrenamiento` (`id_grupo`, `nombre`, `descripcion`, `id_usuario`, `activo`) VALUES
(1, 'Pre-Infantil', 'Grupo de iniciación 8-10 años', NULL, 1),
(2, 'Infantil A', 'Grupo infantil A 11-12 años', NULL, 1),
(3, 'Infantil B', 'Grupo infantil B 13-14 años', NULL, 1),
(4, 'Juvenil A', 'Grupo juvenil A 15-16 años', NULL, 1),
(5, 'Juvenil B', 'Grupo juvenil B 17-18 años', NULL, 1),
(6, 'Maxima', 'Grupo máxima categoría', NULL, 1);

-- ============================================================
-- 3. bloques_horarios
-- ============================================================
INSERT INTO `bloques_horarios` (`id_bloque`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 'Lunes', '07:00:00', '08:30:00'),
(2, 'Lunes', '16:00:00', '17:30:00'),
(3, 'Martes', '07:00:00', '08:30:00'),
(4, 'Martes', '16:00:00', '17:30:00'),
(5, 'Miercoles', '07:00:00', '08:30:00'),
(6, 'Miercoles', '16:00:00', '17:30:00'),
(7, 'Jueves', '07:00:00', '08:30:00'),
(8, 'Jueves', '16:00:00', '17:30:00'),
(9, 'Viernes', '07:00:00', '08:30:00'),
(10, 'Viernes', '16:00:00', '17:30:00'),
(11, 'Sabado', '08:00:00', '10:00:00'),
(12, 'Sabado', '10:00:00', '12:00:00');

-- ============================================================
-- 4. drills
-- ============================================================
INSERT INTO `drills` (`id_drill`, `nombre`, `estilo`, `categoria`, `enfoque_tecnico`, `descripcion`, `instrucciones`, `metraje_sugerido`, `dificultad`, `material_requerido`, `personalizado`, `id_usuario_creador`, `activo`, `fecha_creacion`) VALUES
(1, 'Kicking Libre con tabla', 'Libre', 'Tecnico', 'Patada de crol', 'Patada de crol con tabla manteniendo posición hidrodinámica', 'Agarre la tabla por el borde inferior, brazos extendidos, cara en el agua. Patada desde la cadera con tobillos flexibles.', 200, 'Basico', 'Tabla', 0, NULL, 1, '2026-05-15 10:00:00'),
(2, 'Pullboy Libre', 'Libre', 'Fuerza', 'Trabajo de brazos', 'Nado de crol solo con pullboy, enfocado en tracción y empuje', 'Colocar pullboy entre las piernas. Nado de crol con brazada completa, rotación corporal y respiración bilateral.', 400, 'Intermedio', 'Pullboy', 0, NULL, 1, '2026-05-15 10:05:00'),
(3, 'Arranque de Mariposa', 'Mariposa', 'Tecnico', 'Salida del muro', 'Práctica de arranque con golpe de delfín bajo el agua', 'Posición de agarre en el muro, empuje explosivo, entrada en ángulo, 3-5 golpes de delfín submarinos, salir a superficie.', 100, 'Intermedio', 'Ninguno', 0, NULL, 1, '2026-05-16 08:00:00'),
(4, 'Viraje tumble crol', 'Libre', 'Tecnico', 'Viraje', 'Práctica de viraje tumble para estilo libre', 'Nadar hacia la pared, sumersión a 1 metro, giro completo con impulso, empuje en dirección contraria con 3 golpes de delfín.', 100, 'Intermedio', 'Ninguno', 0, NULL, 1, '2026-05-16 08:10:00'),
(5, 'Resistencia 200m Braza', 'Braza', 'Resistencia', 'Ritmo de braza', 'Nado de braza a ritmo moderado enfocando en ritmo y respiración', '2 brazadas, 1 respiración, patada de braza sincronizada. Mantener línea del agua estable.', 600, 'Intermedio', 'Ninguno', 0, NULL, 1, '2026-05-17 09:00:00'),
(6, 'Sprint 25m Libre', 'Libre', 'Velocidad', 'Velocidad de salida', 'Sprint de 25 metros al máximo esfuerzo', 'Salida desde el muro, aceleración máxima desde los primeros metros, mantener técnica hasta el final.', 100, 'Basico', 'Ninguno', 0, NULL, 1, '2026-05-17 09:15:00'),
(7, 'Drill 6-3-6 Libre', 'Libre', 'Coordinacion', 'Equilibrio rotacional', '6 patadas brazo derecho, 3 patadas ambos brazos, 6 patadas brazo izquierdo', 'Mantener posición lateral, respirar al lado del brazo que descansa. Transición suave entre posiciones.', 200, 'Basico', 'Ninguno', 0, NULL, 1, '2026-05-18 07:00:00'),
(8, 'Nado con aletas - Espalda', 'Espalda', 'Tecnico', 'Posición de espalda', 'Nado de espalda con aletas para mejorar posición corporal', 'Caderas arriba, barbilla pegada al pecho, rotación alrededor del eje longitudinal. Brazada alternativa con recuperación recta.', 200, 'Basico', 'Aletas', 0, NULL, 1, '2026-05-18 07:15:00'),
(9, 'Circuito de fuerza con resistant', 'Multi', 'Fuerza', 'Fuerza específica', 'Circuito de nado con resistant band para desarrollo de fuerza', '4x50m con resistant band al máximo esfuerzo. Descanso 45 seg entre repeticiones.', 200, 'Avanzado', 'Resistente', 0, NULL, 1, '2026-05-19 10:00:00'),
(10, 'Vuelta a calma suave', 'Libre', 'Coordinacion', 'Recuperación activa', 'Nado suave de libre para enfriamiento y recuperación', 'Nado relajado de crol, ritmo confortable, respiración cada 3-5 brazadas. Enfoque en relajación muscular.', 200, 'Basico', 'Ninguno', 0, NULL, 1, '2026-05-19 10:20:00');

-- ============================================================
-- 5. eventos
-- ============================================================
INSERT INTO `eventos` (`id_evento`, `nombre`, `fecha_inicio`, `fecha_fin`, `sede`, `tipo`, `nivel`, `organizador`, `estado`, `observaciones`, `fecha_creacion`) VALUES
(1, 'Control Interno Junio 2026', '2026-06-15', '2026-06-15', 'Piscina Olímpica de Barquisimeto', 'Control', 'C', 'Club Biomec', 'Planificado', 'Control de tiempos de mitad de temporada', '2026-05-20 10:00:00'),
(2, 'Selectivo Regional Centro-Occidente', '2026-07-20', '2026-07-22', 'Complejo Acuático de Barquisimeto', 'Selectivo', 'A', 'FEVEDA Regional', 'Planificado', 'Clasificatorio para nacionales', '2026-05-20 10:05:00'),
(3, 'Campeonato Nacional Infantil', '2026-09-10', '2026-09-14', 'Centro Acuático Internacional de Maiquetía', 'Nacional', 'A', 'FEVEDA', 'Planificado', 'Nacional para categorías infantil', '2026-05-25 08:00:00'),
(4, 'Torneo Regional Noviembre', '2026-11-05', '2026-11-07', 'Piscina Olímpica de Mérida', 'Regional', 'B', 'Comité Regional', 'Planificado', 'Torneo de cierre de temporada regional', '2026-05-25 08:10:00'),
(5, 'Competencia Internacional Copa del Caribe', '2027-02-10', '2027-02-15', 'Centro Acuático de Santo Domingo', 'Internacional', 'A', 'CCCAN', 'Planificado', 'Selección nacional categoría máxima', '2026-05-30 12:00:00');

-- ============================================================
-- 6. atleta_datos_medicos
-- ============================================================
INSERT INTO `atleta_datos_medicos` (`id_datos_medicos`, `id_atleta`, `grupo_sanguineo`, `alergias`, `condiciones_previas`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `contacto_emergencia_parentesco`, `seguro_medico`, `numero_feveda`, `club_procedencia`) VALUES
(1, 1, 'A+', 'Ninguna', 'Ninguna', 'Josefina Navarro', '04245728016', 'Madre', 'Humano Plus', 'FEV-2025-00123', NULL),
(2, 2, 'O+', 'Polen', 'Asma leve controlada', 'Josefina Navarro', '04245728016', 'Madre', 'Humano Plus', 'FEV-2025-00124', NULL),
(3, 3, 'A+', 'Ninguna', 'Ninguna', 'Josefina Navarro', '04245728016', 'Madre', 'Humano Plus', 'FEV-2025-00125', NULL),
(4, 4, 'B+', 'Ninguna', 'Ninguna', 'Lourdes Corro', '04120121212', 'Tutora', 'Bolívar', 'FEV-2026-00150', 'Acuáticos Lara');

-- ============================================================
-- 7. asignacion_carril
-- ============================================================
INSERT INTO `asignacion_carril` (`id_asignacion`, `id_carril`, `id_bloque_horario`, `id_grupo`, `dia_especifico`, `fecha_vigencia_inicio`, `fecha_vigencia_fin`, `activa`) VALUES
(1, 1, 1, 1, NULL, '2026-05-01', '2026-08-31', 1),
(2, 2, 1, 2, NULL, '2026-05-01', '2026-08-31', 1),
(3, 3, 1, 3, NULL, '2026-05-01', '2026-08-31', 1),
(4, 1, 2, 4, NULL, '2026-05-01', '2026-08-31', 1),
(5, 2, 2, 5, NULL, '2026-05-01', '2026-08-31', 1),
(6, 3, 2, 6, NULL, '2026-05-01', '2026-08-31', 1),
(7, 1, 3, 1, NULL, '2026-05-01', '2026-08-31', 1),
(8, 2, 3, 2, NULL, '2026-05-01', '2026-08-31', 1),
(9, 3, 3, 3, NULL, '2026-05-01', '2026-08-31', 1),
(10, 1, 11, 2, NULL, '2026-05-01', '2026-08-31', 1),
(11, 2, 11, 3, NULL, '2026-05-01', '2026-08-31', 1),
(12, 3, 11, 4, NULL, '2026-05-01', '2026-08-31', 1);

-- ============================================================
-- 8. macrociclos
-- ============================================================
INSERT INTO `macrociclos` (`id_macrociclo`, `id_temporada`, `id_grupo`, `nombre`, `fecha_inicio`, `fecha_fin`, `id_evento_objetivo`, `estado`) VALUES
(1, 1, 2, 'Macrociclo Infantil A 2026', '2025-09-01', '2026-08-31', 3, 'En Progreso'),
(2, 1, 3, 'Macrociclo Infantil B 2026', '2025-09-01', '2026-08-31', 3, 'En Progreso'),
(3, 1, 4, 'Macrociclo Juvenil A 2026', '2025-09-01', '2026-08-31', 5, 'En Progreso'),
(4, 1, 5, 'Macrociclo Juvenil B 2026', '2025-09-01', '2026-08-31', 5, 'En Progreso'),
(5, 1, 6, 'Macrociclo Máxima 2026', '2025-09-01', '2026-08-31', 5, 'En Progreso'),
(6, 2, 2, 'Macrociclo Infantil A 2027', '2026-09-01', '2027-08-31', NULL, 'Planificado');

-- ============================================================
-- 9. fases_periodizacion
-- ============================================================
INSERT INTO `fases_periodizacion` (`id_fase`, `id_macrociclo`, `nombre_fase`, `semana_inicio`, `semana_fin`, `fecha_inicio`, `fecha_fin`, `porcentaje_volumen`, `rango_intensidad`, `color`) VALUES
(1, 1, 'Acumulacion', 1, 12, '2025-09-01', '2025-11-23', 100.00, '65-75%', '#3498db'),
(2, 1, 'Transmutacion', 13, 30, '2025-11-24', '2026-03-29', 80.00, '75-85%', '#f39c12'),
(3, 1, 'Realizacion', 31, 38, '2026-03-30', '2026-05-24', 60.00, '85-95%', '#e74c3c'),
(4, 1, 'Deload', 39, 42, '2026-05-25', '2026-06-21', 40.00, '50-60%', '#2ecc71'),
(5, 1, 'Realizacion-Competencia', 43, 45, '2026-06-22', '2026-07-12', 30.00, '90-100%', '#e74c3c'),
(6, 2, 'Acumulacion', 1, 12, '2025-09-01', '2025-11-23', 100.00, '65-75%', '#3498db'),
(7, 2, 'Transmutacion', 13, 30, '2025-11-24', '2026-03-29', 80.00, '75-85%', '#f39c12'),
(8, 2, 'Realizacion', 31, 38, '2026-03-30', '2026-05-24', 60.00, '85-95%', '#e74c3c'),
(9, 2, 'Deload', 39, 42, '2026-05-25', '2026-06-21', 40.00, '50-60%', '#2ecc71'),
(10, 2, 'Realizacion-Competencia', 43, 45, '2026-06-22', '2026-07-12', 30.00, '90-100%', '#e74c3c'),
(11, 3, 'Acumulacion', 1, 12, '2025-09-01', '2025-11-23', 100.00, '70-80%', '#3498db'),
(12, 3, 'Transmutacion', 13, 30, '2025-11-24', '2026-03-29', 80.00, '80-90%', '#f39c12'),
(13, 3, 'Realizacion', 31, 38, '2026-03-30', '2026-05-24', 60.00, '90-95%', '#e74c3c'),
(14, 3, 'Deload', 39, 42, '2026-05-25', '2026-06-21', 40.00, '50-60%', '#2ecc71'),
(15, 3, 'Realizacion-Competencia', 43, 45, '2026-06-22', '2026-07-12', 30.00, '92-100%', '#e74c3c'),
(16, 4, 'Acumulacion', 1, 12, '2025-09-01', '2025-11-23', 100.00, '70-80%', '#3498db'),
(17, 4, 'Transmutacion', 13, 30, '2025-11-24', '2026-03-29', 80.00, '80-90%', '#f39c12'),
(18, 4, 'Realizacion', 31, 38, '2026-03-30', '2026-05-24', 60.00, '90-95%', '#e74c3c'),
(19, 5, 'Acumulacion', 1, 12, '2025-09-01', '2025-11-23', 100.00, '75-85%', '#3498db'),
(20, 5, 'Transmutacion', 13, 30, '2025-11-24', '2026-03-29', 80.00, '85-92%', '#f39c12'),
(21, 5, 'Realizacion', 31, 38, '2026-03-30', '2026-05-24', 60.00, '92-100%', '#e74c3c');

-- ============================================================
-- 10. mesociclos
-- ============================================================
INSERT INTO `mesociclos` (`id_mesociclo`, `id_macrociclo`, `id_fase`, `nombre`, `semana_inicio`, `semana_fin`, `objetivo`, `volumen_objetivo_m`) VALUES
(1, 1, 1, 'Mesociclo Acumulación 1 - Septiembre', 1, 4, 'Adaptación aeróbica base. Enfatizar técnica de crol y espalda.', 16000),
(2, 1, 1, 'Mesociclo Acumulación 2 - Octubre', 5, 8, 'Incremento de volumen. Introducir braza y mariposa.', 20000),
(3, 1, 1, 'Mesociclo Acumulación 3 - Noviembre', 9, 12, 'Volumen máximo de acumulación. Consolidar técnica en 4 estilos.', 24000),
(4, 1, 2, 'Mesociclo Transmutación 1 - Diciembre', 13, 16, 'Transición a intensidad. Introducir Z4 y Z5.', 18000),
(5, 1, 2, 'Mesociclo Transmutación 2 - Enero', 17, 22, 'Incremento progresivo de intensidad. Series específicas por distancia.', 20000),
(6, 1, 2, 'Mesociclo Transmutación 3 - Marzo', 23, 28, 'Intensidad competitiva. Simulacros de carrera.', 22000),
(7, 1, 2, 'Mesociclo Transmutación 4 - Abril', 29, 30, 'Ajuste final antes de realización.', 18000),
(8, 1, 3, 'Mesociclo Realización 1 - Mayo', 31, 34, 'Tapering inicial. Reducir volumen, mantener intensidad.', 14000),
(9, 1, 3, 'Mesociclo Realización 2 - Mayo-Junio', 35, 38, 'Tapering final. Ensayos de competencia.', 10000),
(10, 1, 4, 'Mesociclo Deload - Junio', 39, 42, 'Descanso activo. Recuperación física y mental.', 8000),
(11, 1, 5, 'Mesociclo Competencia - Julio', 43, 45, 'Pico competitivo. Calentamientos específicos de competencia.', 6000),
(12, 2, 6, 'Mesociclo Acumulación 1 - Septiembre', 1, 4, 'Adaptación aeróbica. Técnica de crol.', 16000),
(13, 2, 7, 'Mesociclo Transmutación 1 - Diciembre', 13, 16, 'Transición a intensidad.', 18000),
(14, 3, 11, 'Mesociclo Acumulación 1', 1, 4, 'Base aeróbica juvenil', 20000),
(15, 4, 16, 'Mesociclo Acumulación 1', 1, 4, 'Base aeróbica juvenil B', 22000),
(16, 5, 19, 'Mesociclo Acumulación 1', 1, 4, 'Base aeróbica máxima', 26000);

-- ============================================================
-- 11. microciclos
-- ============================================================
INSERT INTO `microciclos` (`id_microciclo`, `id_mesociclo`, `numero_semana`, `fecha_inicio`, `fecha_fin`, `volumen_planificado_m`) VALUES
(1, 1, 1, '2025-09-01', '2025-09-06', 3500),
(2, 1, 2, '2025-09-08', '2025-09-13', 4000),
(3, 1, 3, '2025-09-15', '2025-09-20', 4200),
(4, 1, 4, '2025-09-22', '2025-09-27', 4300),
(5, 2, 5, '2025-10-06', '2025-10-11', 4800),
(6, 2, 6, '2025-10-13', '2025-10-18', 5000),
(7, 2, 7, '2025-10-20', '2025-10-25', 5100),
(8, 2, 8, '2025-10-27', '2025-11-01', 5100),
(9, 3, 9, '2025-11-03', '2025-11-08', 5800),
(10, 3, 10, '2025-11-10', '2025-11-15', 6000),
(11, 3, 11, '2025-11-17', '2025-11-22', 6100),
(12, 3, 12, '2025-11-24', '2025-11-29', 6100),
(13, 4, 13, '2025-12-01', '2025-12-06', 4500),
(14, 4, 14, '2025-12-08', '2025-12-13', 4500),
(15, 4, 15, '2025-12-15', '2025-12-20', 4400),
(16, 4, 16, '2025-12-22', '2025-12-27', 4400),
(17, 8, 31, '2026-03-30', '2026-04-04', 3500),
(18, 8, 32, '2026-04-06', '2026-04-11', 3200),
(19, 8, 33, '2026-04-13', '2026-04-18', 2800),
(20, 8, 34, '2026-04-20', '2026-04-25', 2500),
(21, 9, 35, '2026-04-27', '2026-05-02', 2200),
(22, 9, 36, '2026-05-04', '2026-05-09', 1800),
(23, 9, 37, '2026-05-11', '2026-05-16', 1600),
(24, 9, 38, '2026-05-18', '2026-05-23', 1200),
(25, 10, 39, '2026-05-25', '2026-05-30', 1500),
(26, 10, 40, '2026-06-01', '2026-06-06', 1800),
(27, 10, 41, '2026-06-08', '2026-06-13', 2000),
(28, 10, 42, '2026-06-15', '2026-06-20', 2000),
(29, 11, 43, '2026-06-22', '2026-06-27', 1200),
(30, 11, 44, '2026-06-29', '2026-07-04', 1000),
(31, 11, 45, '2026-07-06', '2026-07-11', 800),
(32, 14, 1, '2025-09-01', '2025-09-06', 4500),
(33, 15, 1, '2025-09-01', '2025-09-06', 5000),
(34, 16, 1, '2025-09-01', '2025-09-06', 6000);

-- ============================================================
-- 12. sesiones
-- ============================================================
INSERT INTO `sesiones` (`id_sesion`, `id_microciclo`, `id_grupo`, `fecha`, `tipo_sesion`, `id_fase_actual`, `calentamiento`, `vuelta_calma`, `volumen_planificado`, `volumen_ejecutado`, `duracion_minutos`, `observaciones`, `estado`, `id_usuario_creador`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 1, 2, '2025-09-01', 'Tecnica', 1, '400m libre suave + 4x50 drill 6-3-6', '200m espalda suave', 1500, 1450, 90, 'Primera sesión del macrociclo, buen ritmo', 'Completada', NULL, '2025-08-30 20:00:00', NULL),
(2, 1, 2, '2025-09-03', 'Resistencia', 1, '300m libre + 200m braza', '200m espalda suave', 2000, 1900, 90, NULL, 'Completada', NULL, '2025-08-30 20:05:00', NULL),
(3, 1, 2, '2025-09-05', 'Velocidad', 1, '400m libre + 8x25 sprint', '200m libre suave', 1500, 1500, 90, 'Buenas velocidades', 'Completada', NULL, '2025-08-30 20:10:00', NULL),
(4, 1, 2, '2025-09-06', 'Fuerza', 1, '300m libre + drill pullboy', '200m espalda', 1800, 1750, 90, NULL, 'Completada', NULL, '2025-08-30 20:15:00', NULL),
(5, 2, 2, '2025-09-08', 'Tecnica', 1, '400m libre + drill kicking tabla', '200m braza suave', 1600, 1600, 90, NULL, 'Completada', NULL, '2025-09-06 18:00:00', NULL),
(6, 2, 2, '2025-09-10', 'Resistencia', 1, '300m libre + 200m espalda', '200m libre', 2100, 2000, 90, NULL, 'Completada', NULL, '2025-09-06 18:05:00', NULL),
(7, 2, 2, '2025-09-12', 'Velocidad', 1, '400m libre + 6x25 sprint mariposa', '200m libre suave', 1600, 1550, 90, NULL, 'Completada', NULL, '2025-09-06 18:10:00', NULL),
(8, 2, 2, '2025-09-13', 'Fuerza', 1, '300m libre + circuito resistant', '200m braza', 1900, 1800, 90, NULL, 'Completada', NULL, '2025-09-06 18:15:00', NULL),
(9, 5, 2, '2025-10-06', 'Resistencia', 1, '400m libre + 4x50 drill', '200m espalda suave', 2200, 2100, 90, NULL, 'Completada', NULL, '2025-10-04 20:00:00', NULL),
(10, 5, 2, '2025-10-08', 'Tecnica', 1, '400m libre suave + drill espalda aletas', '200m braza', 2300, 2200, 90, NULL, 'Completada', NULL, '2025-10-04 20:05:00', NULL),
(11, 5, 2, '2025-10-10', 'Velocidad', 1, '300m libre + 8x25 sprint', '200m libre', 2400, 2350, 90, 'Día de altas velocidades', 'Completada', NULL, '2025-10-04 20:10:00', NULL),
(12, 5, 2, '2025-10-11', 'Fuerza', 1, '300m libre + pullboy', '200m espalda', 2400, 2400, 90, NULL, 'Completada', NULL, '2025-10-04 20:15:00', NULL),
(13, 17, 2, '2026-03-30', 'Tecnica', 3, '400m libre suave + drill 6-3-6', '200m espalda suave', 1500, 1400, 90, 'Inicio de tapering', 'Completada', NULL, '2026-03-28 18:00:00', NULL),
(14, 17, 2, '2026-04-01', 'Velocidad', 3, '300m libre + 6x25 sprint', '200m libre suave', 1200, 1200, 75, NULL, 'Completada', NULL, '2026-03-28 18:05:00', NULL),
(15, 17, 2, '2026-04-03', 'Resistencia', 3, '300m libre + drill kicking', '200m braza suave', 1200, 1150, 75, NULL, 'Completada', NULL, '2026-03-28 18:10:00', NULL),
(16, 18, 2, '2026-04-06', 'Tecnica', 3, '400m libre suave + drill viraje', '200m espalda', 1000, 1000, 75, NULL, 'Completada', NULL, '2026-04-04 20:00:00', NULL),
(17, 18, 2, '2026-04-08', 'Velocidad', 3, '300m + 8x25 sprint', '200m libre', 1000, 950, 75, NULL, 'Completada', NULL, '2026-04-04 20:05:00', NULL),
(18, 19, 2, '2026-04-13', 'Recuperacion', 3, '400m libre muy suave + flexiones', '200m espalda suave', 800, 800, 60, 'Sesión de recuperación activa', 'Completada', NULL, '2026-04-11 20:00:00', NULL),
(19, 20, 2, '2026-04-20', 'Velocidad', 3, '300m libre + 6x25 simulacro', '200m libre', 900, 900, 75, 'Preparación para control', 'Completada', NULL, '2026-04-18 20:00:00', NULL),
(20, 21, 2, '2026-04-27', 'Competencia', 3, '400m suave + calentamiento pre-competencia', '300m vuelta a calma completa', 700, 700, 60, NULL, 'Completada', NULL, '2026-04-25 18:00:00', NULL),
(21, 25, 2, '2026-05-25', 'Recuperacion', 4, '400m muy suave + estiramientos', '200m espalda relajado', 600, 600, 60, 'Semana de deload', 'Completada', NULL, '2026-05-23 20:00:00', NULL),
(22, 25, 2, '2026-05-27', 'Flexibilidad', 4, '300m suave + estiramientos dinámicos', '200m braza suave', 500, 500, 60, NULL, 'Completada', NULL, '2026-05-23 20:05:00', NULL),
(23, 29, 2, '2026-06-22', 'Competencia', 5, 'Calentamiento pre-competencia completo', 'Vuelta a calma post-competencia', 500, 500, 50, 'Pico competitivo', 'Completada', NULL, '2026-06-20 18:00:00', NULL),
(24, 32, 3, '2025-09-01', 'Tecnica', 11, '400m libre + drill espalda', '200m espalda', 1800, 1750, 90, NULL, 'Completada', NULL, '2025-08-30 20:00:00', NULL),
(25, 33, 4, '2025-09-01', 'Tecnica', 16, '500m libre + drill kicking', '200m libre', 2000, 1900, 90, NULL, 'Completada', NULL, '2025-08-30 20:00:00', NULL),
(26, NULL, 2, '2026-06-02', 'Tecnica', NULL, '400m libre suave + drill', '200m espalda', 1500, NULL, 90, 'Sesión independiente sin microciclo', 'Planificada', NULL, '2026-06-01 20:00:00', NULL),
(27, NULL, 3, '2026-06-02', 'Resistencia', NULL, '300m libre + 200m braza', '200m espalda', 2000, NULL, 90, NULL, 'Planificada', NULL, '2026-06-01 20:05:00', NULL),
(28, NULL, 2, '2026-06-03', 'Velocidad', NULL, '300m + 6x25 sprint', '200m libre suave', 1200, NULL, 75, NULL, 'Planificada', NULL, '2026-06-01 20:10:00', NULL);

-- ============================================================
-- 13. series_sesion
-- ============================================================
INSERT INTO `series_sesion` (`id_serie`, `id_sesion`, `id_drill`, `orden_ejecucion`, `bloque`, `ejercicio_descripcion`, `repeticiones`, `distancia_m`, `descanso_seg`, `zona_intensidad`, `ritmo_objetivo`) VALUES
(1, 1, NULL, 1, 'Calentamiento', '400m libre suave', 1, 400, 0, 'Z1', NULL),
(2, 1, 7, 2, 'Calentamiento', '4x50 drill 6-3-6', 4, 50, 15, 'Z1', NULL),
(3, 1, 1, 3, 'Principal', '8x50 kicking tabla libre', 8, 50, 20, 'Z2', NULL),
(4, 1, 4, 4, 'Principal', '6x50 viraje tumble crol', 6, 50, 30, 'Z3', NULL),
(5, 1, NULL, 5, 'Principal', '4x200 libre Z2', 4, 200, 45, 'Z2', NULL),
(6, 1, 10, 6, 'VueltaCalma', '200m espalda suave', 1, 200, 0, 'Z1', NULL),
(7, 2, NULL, 1, 'Calentamiento', '300m libre + 200m braza', 1, 500, 0, 'Z1', NULL),
(8, 2, 5, 2, 'Principal', '6x100 braza Z3', 6, 100, 45, 'Z3', NULL),
(9, 2, NULL, 3, 'Principal', '4x200 libre Z2', 4, 200, 60, 'Z2', NULL),
(10, 2, NULL, 4, 'Principal', '2x400 libre Z2', 2, 400, 90, 'Z2', NULL),
(11, 2, 10, 5, 'VueltaCalma', '200m espalda suave', 1, 200, 0, 'Z1', NULL),
(12, 3, NULL, 1, 'Calentamiento', '400m libre + 8x25 sprint', 1, 600, 0, 'Z1', NULL),
(13, 3, 6, 2, 'Principal', '6x25 sprint libre', 6, 25, 60, 'Z5', NULL),
(14, 3, 3, 3, 'Principal', '4x50 arranque mariposa', 4, 50, 45, 'Z4', NULL),
(15, 3, NULL, 4, 'Principal', '4x100 libre Z4', 4, 100, 60, 'Z4', NULL),
(16, 3, 10, 5, 'VueltaCalma', '200m libre suave', 1, 200, 0, 'Z1', NULL),
(17, 9, NULL, 1, 'Calentamiento', '400m libre + 4x50 drill', 1, 600, 0, 'Z1', NULL),
(18, 9, 2, 2, 'Principal', '6x100 pullboy libre', 6, 100, 45, 'Z3', NULL),
(19, 9, NULL, 3, 'Principal', '3x300 libre Z2', 3, 300, 60, 'Z2', NULL),
(20, 9, NULL, 4, 'Principal', '2x200 braza Z3', 2, 200, 60, 'Z3', NULL),
(21, 9, 10, 5, 'VueltaCalma', '200m espalda suave', 1, 200, 0, 'Z1', NULL),
(22, 13, NULL, 1, 'Calentamiento', '400m libre suave + drill 6-3-6', 1, 600, 0, 'Z1', NULL),
(23, 13, 4, 2, 'Principal', '4x50 viraje tumble', 4, 50, 30, 'Z3', NULL),
(24, 13, NULL, 3, 'Principal', '4x100 libre Z3', 4, 100, 45, 'Z3', NULL),
(25, 13, 10, 4, 'VueltaCalma', '200m espalda suave', 1, 200, 0, 'Z1', NULL),
(26, 14, NULL, 1, 'Calentamiento', '300m libre + 6x25 sprint', 1, 450, 0, 'Z1', NULL),
(27, 14, 6, 2, 'Principal', '8x25 sprint libre', 8, 25, 60, 'Z5', NULL),
(28, 14, NULL, 3, 'Principal', '2x50 simulacro competencia', 2, 50, 120, 'Z5', NULL),
(29, 14, 10, 4, 'VueltaCalma', '200m libre suave', 1, 200, 0, 'Z1', NULL),
(30, 23, NULL, 1, 'Calentamiento', 'Calentamiento pre-competencia completo', 1, 300, 0, 'Z1', NULL),
(31, 23, NULL, 2, 'Principal', 'Simulacro competencia 50m libre', 2, 50, 180, 'Z5', NULL),
(32, 23, NULL, 3, 'Principal', 'Simulacro competencia 100m libre', 1, 100, 180, 'Z5', NULL),
(33, 23, NULL, 4, 'VueltaCalma', '300m vuelta a calma completa', 1, 300, 0, 'Z1', NULL);

-- ============================================================
-- 14. asistencia
-- ============================================================
INSERT INTO `asistencia` (`id_asistencia`, `id_atleta`, `id_sesion`, `id_asignacion_carril`, `id_usuario`, `fecha`, `hora_registro`, `tipo`, `estado`, `justificacion`) VALUES
(1, 1, 1, 2, NULL, '2025-09-01', '2025-09-01 06:55:00', 'QR', 'Presente', NULL),
(2, 2, 1, 2, NULL, '2025-09-01', '2025-09-01 06:58:00', 'QR', 'Presente', NULL),
(3, 3, 1, 2, NULL, '2025-09-01', '2025-09-01 07:05:00', 'QR', 'Tardanza', NULL),
(4, 1, 2, NULL, NULL, '2025-09-03', '2025-09-03 06:50:00', 'QR', 'Presente', NULL),
(5, 2, 2, NULL, NULL, '2025-09-03', '2025-09-03 06:52:00', 'QR', 'Presente', NULL),
(6, 3, 2, NULL, NULL, '2025-09-03', '2025-09-03 06:48:00', 'QR', 'Presente', NULL),
(7, 1, 3, NULL, NULL, '2025-09-05', '2025-09-05 06:45:00', 'QR', 'Presente', NULL),
(8, 2, 3, NULL, NULL, '2025-09-05', '2025-09-05 06:50:00', 'Manual', 'Presente', NULL),
(9, 3, 3, NULL, NULL, '2025-09-05', '2025-09-05 00:00:00', 'QR', 'Ausente', NULL),
(10, 1, 4, NULL, NULL, '2025-09-06', '2025-09-06 07:55:00', 'QR', 'Presente', NULL),
(11, 2, 4, NULL, NULL, '2025-09-06', '2025-09-06 08:00:00', 'QR', 'Presente', NULL),
(12, 1, 5, NULL, NULL, '2025-09-08', '2025-09-08 06:50:00', 'QR', 'Presente', NULL),
(13, 2, 5, NULL, NULL, '2025-09-08', '2025-09-08 06:55:00', 'QR', 'Presente', NULL),
(14, 3, 5, NULL, NULL, '2025-09-08', '2025-09-08 06:58:00', 'QR', 'Presente', NULL),
(15, 1, 6, NULL, NULL, '2025-09-10', '2025-09-10 06:48:00', 'QR', 'Presente', NULL),
(16, 2, 6, NULL, NULL, '2025-09-10', '2025-09-10 06:52:00', 'QR', 'Presente', NULL),
(17, 3, 6, NULL, NULL, '2025-09-10', '2025-09-10 00:00:00', 'QR', 'Justificado', 'Cita médica'),
(18, 1, 9, NULL, NULL, '2025-10-06', '2025-10-06 06:45:00', 'QR', 'Presente', NULL),
(19, 2, 9, NULL, NULL, '2025-10-06', '2025-10-06 06:50:00', 'QR', 'Presente', NULL),
(20, 3, 9, NULL, NULL, '2025-10-06', '2025-10-06 06:48:00', 'QR', 'Presente', NULL),
(21, 1, 10, NULL, NULL, '2025-10-08', '2025-10-08 06:52:00', 'QR', 'Presente', NULL),
(22, 2, 10, NULL, NULL, '2025-10-08', '2025-10-08 06:55:00', 'QR', 'Presente', NULL),
(23, 3, 10, NULL, NULL, '2025-10-08', '2025-10-08 07:10:00', 'QR', 'Tardanza', 'Problema de transporte'),
(24, 4, 24, NULL, NULL, '2025-09-01', '2025-09-01 06:55:00', 'QR', 'Presente', NULL),
(25, 4, NULL, 1, NULL, '2025-09-03', '2025-09-03 06:50:00', 'QR', 'Presente', NULL),
(26, 4, NULL, NULL, NULL, '2025-09-05', '2025-09-05 00:00:00', 'QR', 'Ausente', NULL),
(27, 1, 13, NULL, NULL, '2026-03-30', '2026-03-30 06:50:00', 'QR', 'Presente', NULL),
(28, 2, 13, NULL, NULL, '2026-03-30', '2026-03-30 06:52:00', 'QR', 'Presente', NULL),
(29, 1, 14, NULL, NULL, '2026-04-01', '2026-04-01 06:48:00', 'QR', 'Presente', NULL),
(30, 2, 14, NULL, NULL, '2026-04-01', '2026-04-01 06:50:00', 'QR', 'Presente', NULL),
(31, 1, 18, NULL, NULL, '2026-04-13', '2026-04-13 06:55:00', 'QR', 'Presente', NULL),
(32, 2, 18, NULL, NULL, '2026-04-13', '2026-04-13 07:00:00', 'QR', 'Presente', NULL),
(33, 1, 21, NULL, NULL, '2026-05-25', '2026-05-25 07:00:00', 'QR', 'Presente', NULL),
(34, 2, 21, NULL, NULL, '2026-05-25', '2026-05-25 07:02:00', 'QR', 'Presente', NULL),
(35, 1, 22, NULL, NULL, '2026-05-27', '2026-05-27 00:00:00', 'QR', 'Justificado', 'Malestar general'),
(36, 2, 22, NULL, NULL, '2026-05-27', '2026-05-27 06:55:00', 'QR', 'Presente', NULL);

-- ============================================================
-- 15. carga_diaria
-- ============================================================
INSERT INTO `carga_diaria` (`id_carga`, `id_atleta`, `fecha`, `tss`, `trimp`, `srpe_total`, `volumen_total_m`, `carga_aguda_7d`, `carga_cronica_28d`, `acwr`, `monotonia_semanal`, `strain_semanal`, `semaforo_acwr`, `fecha_creacion`) VALUES
(1, 1, '2025-09-01', 45.50, 22.30, 220, 1450, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 20:00:00'),
(2, 1, '2025-09-03', 52.80, 25.10, 260, 1900, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-03 20:00:00'),
(3, 1, '2025-09-05', 38.20, 18.50, 190, 1500, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-05 20:00:00'),
(4, 1, '2025-09-06', 48.60, 23.80, 240, 1750, 823.00, NULL, NULL, 1.80, 524.50, 'Amarillo', '2025-09-06 20:00:00'),
(5, 1, '2025-09-08', 42.30, 20.60, 210, 1600, 830.00, NULL, NULL, 1.85, 545.60, 'Amarillo', '2025-09-08 20:00:00'),
(6, 1, '2025-09-10', 55.10, 26.40, 275, 2000, 870.00, NULL, NULL, 1.92, 578.30, 'Amarillo', '2025-09-10 20:00:00'),
(7, 1, '2025-09-12', 35.80, 17.20, 180, 1550, 865.00, NULL, NULL, 1.88, 560.40, 'Amarillo', '2025-09-12 20:00:00'),
(8, 1, '2025-09-13', 50.20, 24.50, 250, 1800, 880.00, NULL, NULL, 1.95, 610.20, 'Rojo', '2025-09-13 20:00:00'),
(9, 1, '2025-10-06', 60.30, 29.10, 300, 2100, 920.00, NULL, NULL, 1.30, 410.50, 'Verde', '2025-10-06 20:00:00'),
(10, 1, '2025-10-08', 55.00, 26.80, 275, 2200, 910.00, NULL, NULL, 1.28, 398.20, 'Verde', '2025-10-08 20:00:00'),
(11, 1, '2025-10-10', 48.50, 23.40, 242, 2350, 900.00, NULL, NULL, 1.25, 385.70, 'Verde', '2025-10-10 20:00:00'),
(12, 1, '2025-10-11', 44.20, 21.50, 221, 2400, 880.00, NULL, NULL, 1.22, 370.30, 'Verde', '2025-10-11 20:00:00'),
(13, 2, '2025-09-01', 42.00, 20.50, 210, 1450, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 20:00:00'),
(14, 2, '2025-09-03', 48.50, 23.60, 242, 1900, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-03 20:00:00'),
(15, 2, '2025-09-05', 35.00, 17.00, 175, 1500, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-05 20:00:00'),
(16, 2, '2025-09-06', 45.00, 22.00, 225, 1750, 775.00, NULL, NULL, 1.15, 320.50, 'Verde', '2025-09-06 20:00:00'),
(17, 3, '2025-09-01', 30.00, 14.50, 150, 1450, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 20:00:00'),
(18, 3, '2025-09-03', 40.00, 19.50, 200, 1900, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-03 20:00:00'),
(19, 1, '2026-03-30', 30.00, 14.50, 150, 1400, 780.00, 820.00, 0.95, 1.20, 280.50, 'Verde', '2026-03-30 20:00:00'),
(20, 1, '2026-04-01', 28.00, 13.50, 140, 1200, 750.00, 810.00, 0.93, 1.15, 265.80, 'Verde', '2026-04-01 20:00:00'),
(21, 1, '2026-04-03', 25.00, 12.00, 125, 1150, 720.00, 800.00, 0.90, 1.10, 250.30, 'Verde', '2026-04-03 20:00:00'),
(22, 1, '2026-04-06', 22.00, 10.80, 110, 1000, 690.00, 790.00, 0.87, 1.05, 235.20, 'Verde', '2026-04-06 20:00:00'),
(23, 1, '2026-04-08', 20.00, 9.80, 100, 950, 660.00, 780.00, 0.85, 1.00, 220.10, 'Verde', '2026-04-08 20:00:00'),
(24, 1, '2026-04-13', 15.00, 7.20, 75, 800, 620.00, 770.00, 0.81, 0.95, 198.50, 'Verde', '2026-04-13 20:00:00'),
(25, 1, '2026-04-20', 18.00, 8.80, 90, 900, 640.00, 775.00, 0.83, 0.98, 215.30, 'Verde', '2026-04-20 20:00:00'),
(26, 1, '2026-04-27', 16.00, 7.80, 80, 700, 610.00, 770.00, 0.79, 0.90, 185.40, 'Verde', '2026-04-27 20:00:00'),
(27, 1, '2026-05-25', 12.00, 5.80, 60, 600, 580.00, 760.00, 0.76, 0.85, 160.20, 'Verde', '2026-05-25 20:00:00'),
(28, 1, '2026-05-27', 10.00, 4.80, 50, 500, 550.00, 750.00, 0.73, 0.80, 140.50, 'Verde', '2026-05-27 20:00:00'),
(29, 2, '2026-03-30', 28.00, 13.60, 140, 1400, 720.00, 780.00, 0.92, 1.15, 250.80, 'Verde', '2026-03-30 20:00:00'),
(30, 2, '2026-04-01', 26.00, 12.60, 130, 1200, 700.00, 770.00, 0.91, 1.10, 240.50, 'Verde', '2026-04-01 20:00:00');

-- ============================================================
-- 16. evento_inscripcion
-- ============================================================
INSERT INTO `evento_inscripcion` (`id_inscripcion`, `id_evento`, `id_atleta`, `fecha_inscripcion`) VALUES
(1, 1, 1, '2026-06-01 10:00:00'),
(2, 1, 2, '2026-06-01 10:05:00'),
(3, 1, 3, '2026-06-01 10:10:00'),
(4, 1, 4, '2026-06-01 10:15:00'),
(5, 2, 1, '2026-06-10 08:00:00'),
(6, 2, 2, '2026-06-10 08:05:00'),
(7, 3, 1, '2026-08-01 12:00:00'),
(8, 3, 2, '2026-08-01 12:05:00'),
(9, 3, 4, '2026-08-01 12:10:00');

-- ============================================================
-- 17. grupo_atleta
-- ============================================================
INSERT INTO `grupo_atleta` (`id_grupo_atleta`, `id_grupo`, `id_atleta`, `fecha_asignacion`) VALUES
(1, 2, 1, '2025-05-14'),
(2, 2, 2, '2024-05-01'),
(3, 2, 3, '2025-05-07'),
(4, 3, 4, '2026-05-04');

-- ============================================================
-- 18. lesiones
-- ============================================================
INSERT INTO `lesiones` (`id_lesion`, `id_atleta`, `zona_anatomica`, `lado`, `tipo`, `nivel_molestia`, `diagnostico`, `tratamiento`, `fecha_inicio`, `fecha_estimada_recup`, `estado`, `profesional`, `observaciones`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 1, 'Hombro', 'Izquierdo', 'Sobreuso', 5, 'Tendinitis del manguito rotador', 'Reposo relativo 2 semanas, fisioterapia 3x/semana, hielo post-sesión', '2026-04-10', '2026-04-30', 'Recuperada', 'Dr. Carlos Méndez', 'Origen por exceso de volumen en fase de transmutación', '2026-04-10 10:00:00', '2026-05-01 08:00:00'),
(2, 2, 'Rodilla', 'Derecho', 'Aguda', 4, 'Esguince grado I de ligamento colateral medial', 'Reposo 10 días, vendaje funcional, rehabilitación progresiva', '2026-05-05', '2026-05-20', 'Recuperada', 'Dra. Ana Rodríguez', 'Ocurrió durante viraje tumble', '2026-05-05 14:00:00', '2026-05-21 09:00:00'),
(3, 3, 'Hombro', 'Bilateral', 'Sobreuso', 3, 'Dolor muscular por sobrecarga', 'Reducir volumen de brazada, fortalecimiento isométrico, estiramientos', '2026-05-20', '2026-06-10', 'EnRehabilitacion', 'Dr. Carlos Méndez', 'Monitorear evolución', '2026-05-20 16:00:00', NULL),
(4, 4, 'Tobillo', 'Izquierdo', 'Aguda', 6, 'Esguince grado II tobillo', 'Inmovilización 5 días, rehabilitación con bandas, progresión de carga', '2026-06-01', '2026-06-25', 'Activa', 'Dra. María González', 'Evaluación semanal', '2026-06-01 09:00:00', NULL);

-- ============================================================
-- 19. protocolo_retorno
-- ============================================================
INSERT INTO `protocolo_retorno` (`id_paso`, `id_lesion`, `descripcion_paso`, `completado`, `fecha_completado`) VALUES
(1, 1, 'Rango de movimiento completo sin dolor', 1, '2026-04-15'),
(2, 1, 'Fuerza isométrica al 70% del lado sano', 1, '2026-04-18'),
(3, 1, 'Nado suave 500m sin molestia', 1, '2026-04-22'),
(4, 1, 'Nado completo 1000m con técnica correcta', 1, '2026-04-25'),
(5, 1, 'Sprint 4x25m al 80% sin dolor', 1, '2026-04-28'),
(6, 1, 'Sesión completa de entrenamiento sin limitación', 1, '2026-04-30'),
(7, 2, 'Rango de movimiento completo', 1, '2026-05-08'),
(8, 2, 'Apoyo monopodal 30 seg sin dolor', 1, '2026-05-10'),
(9, 2, 'Nado suave 300m', 1, '2026-05-13'),
(10, 2, 'Viraje tumble sin molestia', 1, '2026-05-16'),
(11, 2, 'Sesión completa de entrenamiento', 1, '2026-05-20'),
(12, 3, 'Valoración de rango de movimiento bilateral', 1, '2026-05-22'),
(13, 3, 'Nado suave 200m sin molestia bilateral', 0, NULL),
(14, 3, 'Sesión completa con reducción 30% volumen', 0, NULL),
(15, 3, 'Sesión completa al 100%', 0, NULL),
(16, 4, 'Inmovilización y control del dolor', 0, NULL),
(17, 4, 'Rango de movimiento completo sin inflamación', 0, NULL),
(18, 4, 'Nado suave con tabla 200m', 0, NULL),
(19, 4, 'Nado completo 500m sin dolor', 0, NULL),
(20, 4, 'Viraje y arranque sin limitación', 0, NULL);

-- ============================================================
-- 20. mediciones_antropometricas
-- ============================================================
INSERT INTO `mediciones_antropometricas` (`id_medicion`, `id_atleta`, `fecha`, `peso_kg`, `talla_cm`, `envergadura_cm`, `perimetro_abdominal_cm`, `imc`, `porcentaje_grasa`, `responsable`) VALUES
(1, 1, '2025-09-01', 28.50, 140.2, 142.5, 58.3, 14.5, 12.8, 'Prof. Luis Pérez'),
(2, 1, '2025-12-01', 29.20, 142.0, 144.0, 58.8, 14.5, 12.5, 'Prof. Luis Pérez'),
(3, 1, '2026-03-01', 30.10, 144.5, 146.0, 59.2, 14.4, 12.0, 'Prof. Luis Pérez'),
(4, 1, '2026-06-01', 31.50, 147.8, 149.5, 59.8, 14.4, 11.5, 'Prof. Luis Pérez'),
(5, 2, '2025-09-01', 26.80, 135.0, 137.0, 56.0, 14.7, 13.5, 'Prof. Luis Pérez'),
(6, 2, '2025-12-01', 27.30, 136.5, 138.5, 56.5, 14.6, 13.0, 'Prof. Luis Pérez'),
(7, 2, '2026-03-01', 28.00, 138.0, 140.0, 57.0, 14.7, 12.8, 'Prof. Luis Pérez'),
(8, 3, '2025-09-01', 26.00, 134.0, 136.0, 55.5, 14.5, 14.0, 'Prof. Luis Pérez'),
(9, 3, '2025-12-01', 26.50, 135.5, 137.5, 56.0, 14.4, 13.5, 'Prof. Luis Pérez'),
(10, 4, '2026-05-04', 38.20, 148.0, 150.5, 62.0, 17.4, 15.0, 'Prof. Luis Pérez'),
(11, 4, '2026-06-01', 38.80, 148.5, 151.0, 62.3, 17.6, 14.8, 'Prof. Luis Pérez');

-- ============================================================
-- 21. registro_rpe
-- ============================================================
INSERT INTO `registro_rpe` (`id_rpe`, `id_atleta`, `id_sesion`, `fecha`, `rpe`, `horas_sueno`, `calidad_sueno`, `sensacion_muscular`, `estres_percibido`, `observaciones`, `metros_nadados`, `duracion_minutos`, `srpe`, `fecha_creacion`) VALUES
(1, 1, 1, '2025-09-01', 5, 8.5, 4, 3, 2, 'Buen inicio de semana', 1450, 90, 450, '2025-09-01 20:00:00'),
(2, 1, 2, '2025-09-03', 6, 8.0, 4, 4, 2, NULL, 1900, 90, 540, '2025-09-03 20:00:00'),
(3, 1, 3, '2025-09-05', 4, 9.0, 5, 2, 1, 'Sesión ligera', 1500, 90, 360, '2025-09-05 20:00:00'),
(4, 1, 4, '2025-09-06', 6, 7.5, 3, 4, 3, NULL, 1750, 90, 540, '2025-09-06 20:00:00'),
(5, 1, 5, '2025-09-08', 5, 8.0, 4, 3, 2, NULL, 1600, 90, 450, '2025-09-08 20:00:00'),
(6, 1, 6, '2025-09-10', 7, 7.0, 3, 5, 3, 'Día pesado', 2000, 90, 630, '2025-09-10 20:00:00'),
(7, 1, 7, '2025-09-12', 5, 8.5, 4, 3, 2, NULL, 1550, 90, 450, '2025-09-12 20:00:00'),
(8, 1, 8, '2025-09-13', 7, 7.0, 3, 5, 3, 'Sesión intensa de fuerza', 1800, 90, 630, '2025-09-13 20:00:00'),
(9, 2, 1, '2025-09-01', 5, 9.0, 4, 3, 1, NULL, 1450, 90, 450, '2025-09-01 20:00:00'),
(10, 2, 2, '2025-09-03', 6, 8.0, 3, 4, 2, NULL, 1900, 90, 540, '2025-09-03 20:00:00'),
(11, 3, 1, '2025-09-01', 4, 8.0, 4, 2, 2, NULL, 1450, 90, 360, '2025-09-01 20:00:00'),
(12, 3, 2, '2025-09-03', 5, 7.5, 3, 3, 2, NULL, 1900, 90, 450, '2025-09-03 20:00:00'),
(13, 1, 9, '2025-10-06', 7, 7.0, 3, 5, 3, 'Incremento de volumen notable', 2100, 90, 630, '2025-10-06 20:00:00'),
(14, 1, 10, '2025-10-08', 6, 8.0, 4, 4, 2, NULL, 2200, 90, 540, '2025-10-08 20:00:00'),
(15, 1, 11, '2025-10-10', 8, 6.5, 2, 6, 4, 'Día de velocidad muy intenso', 2350, 90, 720, '2025-10-10 20:00:00'),
(16, 1, 12, '2025-10-11', 6, 7.5, 3, 4, 2, NULL, 2400, 90, 540, '2025-10-11 20:00:00'),
(17, 1, 13, '2026-03-30', 4, 9.0, 5, 2, 1, 'Inicio de tapering, sensación fresca', 1400, 90, 360, '2026-03-30 20:00:00'),
(18, 1, 14, '2026-04-01', 5, 8.5, 4, 3, 1, NULL, 1200, 75, 375, '2026-04-01 20:00:00'),
(19, 1, 18, '2026-04-13', 3, 9.5, 5, 1, 1, 'Sesión de recuperación, muy fresco', 800, 60, 180, '2026-04-13 20:00:00'),
(20, 1, 19, '2026-04-20', 6, 8.0, 4, 4, 2, 'Simulacro competitivo', 900, 75, 450, '2026-04-20 20:00:00'),
(21, 2, 13, '2026-03-30', 4, 8.5, 4, 2, 1, NULL, 1400, 90, 360, '2026-03-30 20:00:00'),
(22, 2, 14, '2026-04-01', 5, 8.0, 3, 3, 2, NULL, 1200, 75, 375, '2026-04-01 20:00:00'),
(23, 1, 21, '2026-05-25', 3, 9.0, 5, 2, 1, 'Deload, sensación excelente', 600, 60, 180, '2026-05-25 20:00:00'),
(24, 2, 21, '2026-05-25', 3, 8.5, 4, 2, 1, NULL, 600, 60, 180, '2026-05-25 20:00:00');

-- ============================================================
-- 22. metas_competitivas
-- ============================================================
INSERT INTO `metas_competitivas` (`id_meta`, `id_evento`, `id_atleta`, `estilo`, `distancia`, `marca_objetivo_seg`, `pb_actual_seg`, `diferencia_pct`) VALUES
(1, 3, 1, 'Libre', 50, 58.00, 65.00, -10.77),
(2, 3, 1, 'Libre', 100, 125.00, 120.00, 4.17),
(3, 3, 1, 'Espalda', 50, 100.00, 120.00, -16.67),
(4, 3, 2, 'Libre', 50, 62.00, 0.00, NULL),
(5, 3, 2, 'Espalda', 50, 95.00, 0.00, NULL),
(6, 3, 3, 'Braza', 50, 150.00, 180.00, -16.67),
(7, 3, 3, 'Espalda', 50, 160.00, 180.00, -11.11),
(8, 3, 4, 'Espalda', 100, 110.00, 120.00, -8.33),
(9, 3, 4, 'Libre', 50, 55.00, 60.00, -8.33),
(10, 2, 1, 'Libre', 50, 60.00, 65.00, -7.69),
(11, 2, 1, 'Libre', 100, 122.00, 120.00, 1.67),
(12, 2, 2, 'Libre', 50, 65.00, 0.00, NULL),
(13, 1, 1, 'Libre', 50, 62.00, 65.00, -4.62),
(14, 1, 1, 'Espalda', 50, 110.00, 120.00, -8.33),
(15, 1, 4, 'Espalda', 100, 115.00, 120.00, -4.17);

-- ============================================================
-- 23. tiempos_corte_evento
-- ============================================================
INSERT INTO `tiempos_corte_evento` (`id_tiempo_corte`, `id_evento`, `id_categoria`, `estilo`, `distancia`, `tiempo_corte_segundos`) VALUES
(1, 3, 2, 'Libre', 50, 70.00),
(2, 3, 2, 'Libre', 100, 150.00),
(3, 3, 2, 'Espalda', 50, 80.00),
(4, 3, 2, 'Braza', 50, 85.00),
(5, 3, 2, 'Mariposa', 50, 80.00),
(6, 3, 2, 'Combinado', 100, 160.00),
(7, 3, 3, 'Libre', 50, 65.00),
(8, 3, 3, 'Libre', 100, 140.00),
(9, 3, 3, 'Espalda', 50, 75.00),
(10, 3, 3, 'Braza', 50, 80.00),
(11, 3, 3, 'Mariposa', 50, 75.00),
(12, 3, 3, 'Combinado', 200, 320.00),
(13, 2, 2, 'Libre', 50, 72.00),
(14, 2, 2, 'Libre', 100, 155.00),
(15, 2, 2, 'Espalda', 50, 82.00),
(16, 2, 2, 'Braza', 50, 88.00),
(17, 2, 3, 'Libre', 50, 68.00),
(18, 2, 3, 'Libre', 100, 145.00),
(19, 2, 3, 'Espalda', 50, 78.00),
(20, 5, 6, 'Libre', 50, 28.00),
(21, 5, 6, 'Libre', 100, 60.00),
(22, 5, 6, 'Libre', 200, 130.00),
(23, 5, 6, 'Espalda', 100, 68.00),
(24, 5, 6, 'Mariposa', 100, 62.00),
(25, 5, 6, 'Combinado', 200, 140.00),
(26, 5, 5, 'Libre', 50, 30.00),
(27, 5, 5, 'Libre', 100, 65.00),
(28, 5, 5, 'Libre', 200, 140.00);

-- ============================================================
-- 24. reglas_log
-- ============================================================
INSERT INTO `reglas_log` (`id_log`, `id_regla`, `id_atleta`, `id_sesion`, `fecha_disparo`, `valores_hechos`, `recomendacion_generada`) VALUES
(1, 12, 1, 8, '2025-09-13 20:30:00', '{\"acwr\": 1.95}', 'Atleta sub-entrenado. Incrementar progresivamente la carga.'),
(2, 8, 1, 9, '2025-10-06 20:30:00', '{\"acwr\": 1.30, \"rpe_promedio_3\": 6.5, \"lesion_activa\": false}', 'Readiness óptimo. Mantener carga planificada.'),
(3, 8, 2, 9, '2025-10-06 20:30:00', '{\"acwr\": 1.15, \"rpe_promedio_3\": 5.5, \"lesion_activa\": false}', 'Readiness óptimo. Mantener carga planificada.'),
(4, 8, 1, 17, '2026-03-30 20:30:00', '{\"acwr\": 0.95, \"rpe_promedio_3\": 4.0, \"lesion_activa\": false}', 'Readiness óptimo. Mantener carga planificada.'),
(5, 1, 1, 8, '2025-09-13 20:31:00', '{\"acwr\": 1.95, \"rpe_promedio_3\": 6.3}', 'Riesgo de sobreentrenamiento. Reducir volumen 30%. Evaluar descanso 24h.');

-- ============================================================
-- 25. entrenador_asignacion
-- ============================================================
INSERT INTO `entrenador_asignacion` (`id_asignacion`, `id_usuario`, `id_atleta`, `fecha_asignacion`) VALUES
(1, 1, 1, '2025-05-14'),
(2, 1, 2, '2024-05-01'),
(3, 1, 3, '2025-05-07'),
(4, 1, 4, '2026-05-04');

COMMIT;
