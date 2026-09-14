-- =============================================================
-- MIGRACION 005: Alinear BD con el codigo actual
-- 1) sis_natacion.entrenador: id_usuario opcional + foto con default
-- 2) sis_natacion.marcas_splits: columna tiempo_viraje_seg
-- 3) sis_natacion.alertas_biologicas: tabla requerida por Lesiones/Antropometria
-- 4) sis_seguridad: permisos de Grupos, Horarios y Asignacion de carriles
-- Idempotente: segura de ejecutar mas de una vez (MariaDB/XAMPP)
-- =============================================================

-- -------------------------------------------------------------
-- 1. entrenador: id_usuario nullable y foto con valor por defecto
--    (fallaba el registro de entrenadores con error 1048)
-- -------------------------------------------------------------
USE sis_natacion;

ALTER TABLE entrenador
    MODIFY id_usuario INT(11) DEFAULT NULL,
    MODIFY foto VARCHAR(100) NOT NULL DEFAULT '';

-- -------------------------------------------------------------
-- 2. marcas_splits: tiempo de viraje por tramo
--    (fallaba el detalle de marcas con columna desconocida)
-- -------------------------------------------------------------
ALTER TABLE marcas_splits
    ADD COLUMN IF NOT EXISTS tiempo_viraje_seg DECIMAL(5,2) DEFAULT NULL AFTER tiempo_parcial_seg;

-- -------------------------------------------------------------
-- 3. alertas_biologicas: alertas biologicas de atletas
--    (fallaba ?p=lesion y ?p=antropometria con 503)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alertas_biologicas (
    id_alerta INT(11) NOT NULL AUTO_INCREMENT,
    id_atleta INT(11) NOT NULL,
    modulo_origen VARCHAR(50) NOT NULL,
    id_registro_origen INT(11) NOT NULL,
    tipo_alerta VARCHAR(50) NOT NULL,
    gravedad INT(11) NOT NULL COMMENT '1=Baja, 2=Media, 3=Alta',
    mensaje TEXT NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    leida TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_alerta),
    KEY id_atleta (id_atleta),
    CONSTRAINT alertas_biologicas_ibfk_1 FOREIGN KEY (id_atleta) REFERENCES atletas (id_atleta) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -------------------------------------------------------------
-- 4. sis_seguridad: permisos faltantes de menu
--    grupo.ver / horario.ver / asignacion.ver -> Administrador y Entrenador
--    horario.gestionar -> solo Administrador (patron carriles.gestionar)
-- -------------------------------------------------------------
USE sis_seguridad;

INSERT INTO permisos (modulo, accion, descripcion)
SELECT 'grupo', 'ver', 'Ver grupos de entrenamiento'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE modulo = 'grupo' AND accion = 'ver');

INSERT INTO permisos (modulo, accion, descripcion)
SELECT 'horario', 'ver', 'Ver horarios de entrenamiento'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE modulo = 'horario' AND accion = 'ver');

INSERT INTO permisos (modulo, accion, descripcion)
SELECT 'asignacion', 'ver', 'Ver asignacion de carriles'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE modulo = 'asignacion' AND accion = 'ver');

INSERT INTO permisos (modulo, accion, descripcion)
SELECT 'horario', 'gestionar', 'Crear, editar y eliminar bloques de horario'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE modulo = 'horario' AND accion = 'gestionar');

-- Otorgar permisos de solo lectura a Administrador y Entrenador
INSERT INTO rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
JOIN permisos p
  ON (p.modulo, p.accion) IN (('grupo','ver'), ('horario','ver'), ('asignacion','ver'))
WHERE r.nombre IN ('Administrador', 'Entrenador')
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp2
    WHERE rp2.id_rol = r.id_rol AND rp2.id_permiso = p.id_permiso
);

-- horario.gestionar solo para Administrador
INSERT INTO rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
JOIN permisos p ON p.modulo = 'horario' AND p.accion = 'gestionar'
WHERE r.nombre = 'Administrador'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp2
    WHERE rp2.id_rol = r.id_rol AND rp2.id_permiso = p.id_permiso
);
