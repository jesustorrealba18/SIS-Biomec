-- =============================================================
-- MIGRACION RBAC: Correcciones de permisos y seguridad
-- Ejecutar sobre la base de datos sis_seguridad
-- Idempotente: usa INSERT IGNORE para no romper si ya se aplico
-- =============================================================

-- 1. Agregar permiso atletas.gestionar (requerido por paginas entrenador y categorias)
INSERT IGNORE INTO permisos (id_permiso, modulo, accion, descripcion)
VALUES (43, 'atletas', 'gestionar', 'Acceso al modulo de gestion de entrenadores');

-- 2. Asignar atletas.gestionar al rol Administrador (id 1)
INSERT IGNORE INTO rol_permisos (id_rol_permiso, id_rol, id_permiso)
SELECT COALESCE(MAX(id_rol_permiso), 0) + 1, 1, p.id_permiso
FROM rol_permisos, permisos p
WHERE p.modulo = 'atletas' AND p.accion = 'gestionar'
HAVING NOT EXISTS (
    SELECT 1 FROM rol_permisos rp WHERE rp.id_rol = 1 AND rp.id_permiso = p.id_permiso
);

-- 3. Asignar atletas.gestionar al rol Entrenador (id 2)
INSERT IGNORE INTO rol_permisos (id_rol_permiso, id_rol, id_permiso)
SELECT COALESCE(MAX(id_rol_permiso), 0) + 1, 2, p.id_permiso
FROM rol_permisos, permisos p
WHERE p.modulo = 'atletas' AND p.accion = 'gestionar'
HAVING NOT EXISTS (
    SELECT 1 FROM rol_permisos rp WHERE rp.id_rol = 2 AND rp.id_permiso = p.id_permiso
);
