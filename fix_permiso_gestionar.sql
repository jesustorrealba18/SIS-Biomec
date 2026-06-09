-- =============================================================
-- FIX RBAC: Agregar permiso atletas.gestionar
-- Este permiso controla el acceso al modulo de gestion de entrenadores
-- Asignado a: Administrador (1) y Entrenador (2)
-- =============================================================

INSERT INTO `permisos` (`modulo`, `accion`, `descripcion`)
VALUES ('atletas', 'gestionar', 'Acceso al modulo de gestion de entrenadores');

INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`)
SELECT 1, id_permiso FROM `permisos` WHERE modulo = 'atletas' AND accion = 'gestionar';

INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`)
SELECT 2, id_permiso FROM `permisos` WHERE modulo = 'atletas' AND accion = 'gestionar';
