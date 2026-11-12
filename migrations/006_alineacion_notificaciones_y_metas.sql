-- ============================================================================
-- 006: Alineacion de esquema detectada por las pruebas automatizadas (Fase de
-- pruebas, ejecucion 2026-10-25).
--
-- DE-01: La tabla sis_seguridad.notificaciones no existia en la BD de
--        desarrollo. El modulo Notificacion (enviar/listar/contar) fallaba
--        silenciosamente. Se crea segun el DDL canonico de
--        migrations/sis_seguridad_vacia.sql (PK, indice y FK CASCADE).
--
-- DE-02: La tabla sis_natacion.metas_competitivas carecia de la columna
--        id_usuario_creador que UsuarioModelo::eliminarUsuario() consulta
--        para impedir borrar usuarios con metas creadas. Sin ella, eliminar
--        usuario lanzaba PDOException y retornaba false SIEMPRE. Se agrega
--        la columna con el mismo patron de tests_personalizados.
--
-- Ambos cambios son aditivos: no modifican ni eliminan datos existentes.
-- ============================================================================

USE sis_seguridad;

CREATE TABLE IF NOT EXISTS `notificaciones` (
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
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

USE sis_natacion;

ALTER TABLE `metas_competitivas`
  ADD COLUMN IF NOT EXISTS `id_usuario_creador` int(11) DEFAULT NULL COMMENT 'En plan_seguridad';
