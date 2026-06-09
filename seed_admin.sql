-- =============================================================
-- SEED: Primer Administrador del Sistema (CA-19.4)
-- Contraseña: admin123  |  Hash bcrypt cost=10
-- Ejecutar UNA VEZ en phpMyAdmin sobre la BD sis_seguridad
-- =============================================================

INSERT INTO `usuarios` (`cedula`, `nombres`, `apellidos`, `correo`, `contrasena_hash`, `activo`)
VALUES ('00000000', 'Administrador', 'Sistema', 'admin@sgrd.com', '$2y$10$26SZiPtKViEwm7tHHEVWl.Z8Y2U.gMOIpdZjYNTeoXUQtMqZH.LZu', 1);

INSERT INTO `usuario_roles` (`id_usuario`, `id_rol`)
SELECT u.id_usuario, r.id_rol
FROM `usuarios` u CROSS JOIN `roles` r
WHERE u.correo = 'admin@sgrd.com' AND r.nombre = 'Administrador';
