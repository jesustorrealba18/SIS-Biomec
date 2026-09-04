-- =============================================================
-- MIGRACION: Permiso de acceso al Sistema Experto (componente inteligente)
-- Ejecutar sobre la base de datos sis_seguridad
-- Idempotente: usa INSERT IGNORE / HAVING NOT EXISTS para no romper si ya se aplico
-- =============================================================

-- 1. Agregar permiso sistemaExperto.ver
INSERT IGNORE INTO permisos (id_permiso, modulo, accion, descripcion)
VALUES (74, 'sistemaExperto', 'ver', 'Acceso al modulo del Sistema Experto (recomendaciones y alertas)');

-- 2. Asignar sistemaExperto.ver al rol Administrador (id 1)
INSERT IGNORE INTO rol_permisos (id_rol_permiso, id_rol, id_permiso)
SELECT COALESCE(MAX(id_rol_permiso), 0) + 1, 1, p.id_permiso
FROM rol_permisos, permisos p
WHERE p.modulo = 'sistemaExperto' AND p.accion = 'ver'
HAVING NOT EXISTS (
    SELECT 1 FROM rol_permisos rp WHERE rp.id_rol = 1 AND rp.id_permiso = p.id_permiso
);

-- 3. Asignar sistemaExperto.ver al rol Entrenador (id 2)
INSERT IGNORE INTO rol_permisos (id_rol_permiso, id_rol, id_permiso)
SELECT COALESCE(MAX(id_rol_permiso), 0) + 1, 2, p.id_permiso
FROM rol_permisos, permisos p
WHERE p.modulo = 'sistemaExperto' AND p.accion = 'ver'
HAVING NOT EXISTS (
    SELECT 1 FROM rol_permisos rp WHERE rp.id_rol = 2 AND rp.id_permiso = p.id_permiso
);
