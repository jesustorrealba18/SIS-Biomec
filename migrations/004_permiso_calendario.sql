-- =============================================================
-- MIGRACION RBAC: Separar permisos de Calendario de los de Eventos
-- Ejecutar sobre la base de datos sis_seguridad
-- Idempotente: segura de ejecutar mas de una vez
-- =============================================================

-- 1. Crear permiso calendario.ver (acceso de solo lectura al calendario general)
INSERT INTO permisos (modulo, accion, descripcion)
SELECT 'calendario', 'ver', 'Ver calendario general de eventos'
WHERE NOT EXISTS (
    SELECT 1 FROM permisos WHERE modulo = 'calendario' AND accion = 'ver'
);

-- 2. Otorgar calendario.ver a todos los roles que ya tenian eventos.ver
--    (preserva el acceso al calendario de quienes ya lo tenian)
INSERT INTO rol_permisos (id_rol, id_permiso)
SELECT DISTINCT rp.id_rol, p_cal.id_permiso
FROM rol_permisos rp
JOIN permisos p_ev ON rp.id_permiso = p_ev.id_permiso
JOIN permisos p_cal ON p_cal.modulo = 'calendario' AND p_cal.accion = 'ver'
WHERE p_ev.modulo = 'eventos' AND p_ev.accion = 'ver'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp2
    WHERE rp2.id_rol = rp.id_rol AND rp2.id_permiso = p_cal.id_permiso
);

-- 3. Quitar eventos.ver al rol Atleta: conserva solo el calendario general,
--    queda bloqueado el modulo de Eventos y Metas (?p=eventos => 403)
DELETE rp FROM rol_permisos rp
JOIN permisos p ON rp.id_permiso = p.id_permiso
JOIN roles r ON r.id_rol = rp.id_rol
WHERE r.nombre = 'Atleta' AND p.modulo = 'eventos' AND p.accion = 'ver';
