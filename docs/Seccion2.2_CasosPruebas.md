# 2.2 Casos de Pruebas Incluidos

**SIS-Biomec — Plan Maestro de Pruebas · Equipo QA Piscinas Bolivarianas**
Complemento del Plan Maestro de Pruebas v1.0 · Actualizado con la iteración 1b (2026-11-08)

> **Nota de llenado:** la columna "# Casos Disponibles" contabiliza casos **ya diseñados y ejecutados**
> en las iteraciones 1 y 1b (145 en total: 0 fallos, 0 errores, 2 omitidos condicionales por R-01).
> Para los módulos cuya campaña de pruebas **aún no se ha realizado**, la celda indica el estado y se
> deja el espacio reservado (sección 2.2.4) para completar cuando se ejecuten.

## 2.2.1 Tabla resumen de casos de prueba por módulo

| # Casos Disponibles | # Estimado Casos Nuevos | Tipo | Módulo | Total de Casos | Estado |
|---|---|---|---|---|---|
| 22 | N/A | Unitaria, Integración (BD), Humo (HTTP) | **Seguridad y Login** (RNF-01, RNF-11) | 22 | ⚠ Parcialmente realizadas. Falta campaña de fuerza bruta e IDOR (condicionada a R-01/R-02: captcha y rate-limit desactivados). 2 casos de humo omitidos por R-01. |
| 6 | N/A | Unitaria, Integración (BD), Caja Negra | **Atletas** (RF-01) y Representantes (RF-18) | 6 | ⚠ Parcialmente realizadas: CRUD, doble tabla, token/QR server-side. Pendientes: trigger categoría FEVEDA, QR extremo a extremo, representantes. |
| 34 | N/A | Unitaria, Integración (BD) | **Test Físico** (RF-02/RF-04): tipos predefinidos, personalizados, registros y valores | 34 | ✔ **Realizadas (iteración 1b, 08/11/2026):** 100 % aprobadas. Detectaron 3 hallazgos (TF-2, TF-3, TF-5) y 1 defecto ya documentado del módulo (ninguno bloqueante de la suite). |
| 22 | N/A | Unitaria, Integración (BD) | **Observaciones Técnicas** (RF-04/RF-17): aspectos, calificaciones 1–5, historial | 22 | ✔ **Realizadas (iteración 1b, 08/11/2026):** 100 % aprobadas. Detectaron OF-1, OF-2 y el defecto DE-03 (resumenPorAspecto roto, ya corregido). |
| — | (por definir) | Unitaria, API, Integración, Caja Negra (planificado) | **Antropometría** (RF-05) y Alertas Biológicas | — | ✘ Aún no se han realizado. Espacio reservado (ver 2.2.4). |
| — | (por definir) | Unitaria, Integración, Caja Negra, Humo (planificado) | **Sesiones** (RF-14) y **Asistencia** (RF-03) | — | ✘ Aún no se han realizado. Espacio reservado (ver 2.2.4). |
| — | (por definir) | Unitaria, API, Integración, Caja Negra, Humo (planificado) | **Marcas y Telemetría** (RF-06 a RF-09) | — | ✘ Aún no se han realizado (asignadas al integrante de QA de Marcas). Espacio reservado (ver 2.2.4). |
| — | (por definir) | Unitaria, API, Integración, Caja Negra (planificado) | **Carga y Bienestar / RPE** (RF-11) | — | ✘ Aún no se han realizado. Espacio reservado (ver 2.2.4). |
| — | (por definir) | Unitaria, API, Integración, Caja Negra (planificado) | **Lesiones y Coherencia Clínica** (RF-10) | — | ✘ Aún no se han realizado. Espacio reservado (ver 2.2.4). |
| 61 | N/A | Unitaria, Integración (BD/Trigger), Humo | **Integración Cruzada Global y Transversales** (Validaciones, Usuarios/RBAC, Eventos, Notificaciones, Respuesta JSON) | 61 | ✔ Realizadas en iteración 1 (100 % aprobadas). |
| **145** | — | — | **TOTAL GENERAL** | **145 ejecutadas** + pendientes por llenar | **450 aserciones · 0 fallos · 0 errores · 2 omitidas (R-01)** |

## 2.2.2 Distribución por tipo de prueba (casos ya ejecutados)

| Tipo de prueba | Suite (ubicación) | # Casos | Resultado | Estado |
|---|---|---|---|---|
| Unitarias | tests/Unit | 79 | 79 ✔ / 0 ✘ | ✔ Realizadas (25/10 y 08/11/2026) |
| Humo (HTTP real, caja negra) | tests/Feature | 18 | 16 ✔ / 2 ⏭ omitidas (R-01) | ✔ Realizadas (25/10 y 08/11/2026) |
| Integración (BD real, transaccional) | tests/Integration | 48 | 48 ✔ / 0 ✘ | ✔ Realizadas (25/10 y 08/11/2026) |
| API (endpoints AJAX JSON) | tests/Feature (pendiente crear) | — | — | ✘ Aún no realizadas |
| Seguridad (fuerza bruta, IDOR, ZAP) | por definir | — | — | ✘ Aún no realizadas (condicionadas a R-01, R-02) |
| **TOTAL EJECUTADO** | | **145** | **143 ✔ · 2 omitidas · 0 fallos (450 aserciones)** | — |

*Nota:* las pruebas de Humo e Integración actuales ejercen el sistema por sus interfaces públicas
(HTTP y BD), por lo que cubren parcialmente la clasificación "Caja Negra".

## 2.2.3 Detalle de casos ejecutados por módulo

### Seguridad y Login (RNF-01, RNF-11) — 22 casos

| ID | Tipo | Escenario |
|---|---|---|
| CP-UNI-30 | Unitaria | Páginas públicas permiten acceso sin sesión |
| CP-UNI-31 | Unitaria | Página no mapeada se deniega |
| CP-UNI-32 | Unitaria | Sin permisos en sesión, toda acción protegida falla |
| CP-UNI-33 | Unitaria | Con permiso exacto en sesión, la acción se permite |
| CP-UNI-34 | Unitaria | Comparación de permisos estricta (case-sensitive) |
| CP-UNI-35 | Unitaria | tieneRol evalúa lista CSV de roles |
| CP-INT-22 | Integración | cargarPermisos puebla la sesión con permisos reales del rol |
| CP-INT-23 | Integración | Usuario sin roles obtiene permisos vacíos; privadas denegadas |
| CP-HUM-01 | Humo | index.php sin sesión redirige 302 a ?p=login |
| CP-HUM-02 | Humo | Login carga formulario completo (usuario/password/captcha) — **omitido por R-01** |
| CP-HUM-04 (×8) | Humo | Rutas privadas redirigen sin sesión: atleta, usuarios, eventos, marcas, bitacora, notificaciones, **testFisico**, **observacionesTecnicas** |
| CP-HUM-05 | Humo | Página inexistente no expone errores (redirige) |
| CP-HUM-06 | Humo | captcha.php genera imagen válida |
| CP-HUM-07 | Humo | POST de login rechaza captcha inválido — **omitido por R-01** |
| CP-HUM-08 | Humo | Cabeceras X-Frame-Options: DENY y X-Content-Type-Options: nosniff |
| CP-HUM-09 (×2) | Humo | Accesibilidad de ambas BDs (sis_natacion y sis_seguridad) |

*(CP-UNI-36..39 de Respuesta JSON se contabilizan en transversales.)*

### Atletas (RF-01) — 6 casos

| ID | Tipo | Escenario |
|---|---|---|
| CP-INT-07 | Integración | setDatos válido: sin errores y token de asistencia 32 hex |
| CP-INT-08 | Integración | guardar() persiste en atletas y atleta_datos_medicos (1:1) |
| CP-INT-09 | Integración | Datos incompletos generan errores y no insertan |
| CP-INT-10 | Integración | actualizar() modifica el teléfono |
| CP-INT-11 | Integración | eliminar() es borrado lógico (Inactivo) |
| CP-INT-12 | Integración | listar() incluye al atleta creado |

### Test Físico (RF-02/RF-04) — 34 casos · iteración 1b

| ID | Tipo | Escenario |
|---|---|---|
| CP-UNI-40 | Unitaria | Registro completo válido no genera errores |
| CP-UNI-41 | Unitaria | Registro vacío solo exige id_atleta (fecha/estado opcionales) |
| CP-UNI-42 | Unitaria | id_atleta no numérico se rechaza |
| CP-UNI-43 | Unitaria | Fecha inválida (15/01/2026) se rechaza |
| CP-UNI-44 | Unitaria | Observaciones >500 caracteres se rechazan |
| CP-UNI-45 | Unitaria | Estado fuera del catálogo (Aplazado) se rechaza |
| CP-UNI-46 | Unitaria | Estados válidos Completo/Parcial/Cancelado se aceptan |
| CP-UNI-47 | Unitaria | Registrar sin tipo predefinido ni personalizado → error sin tocar BD |
| CP-UNI-48 | Unitaria | Registrar sin valores → error sin tocar BD |
| CP-UNI-49 | Unitaria | Valores no-array → error sin tocar BD |
| CP-UNI-50 | Unitaria | Actualizar sin id de registro → error |
| CP-UNI-51 | Unitaria | Actualización permite omitir id_atleta |
| CP-UNI-52 | Unitaria | Eliminar con id inválido → error |
| CP-UNI-53 | Unitaria | Tipo predefinido sin nombre → error sin tocar BD |
| CP-UNI-54 | Unitaria | Nombre de tipo demasiado corto (min 2) → error |
| CP-UNI-55 | Unitaria | Tipo sin variables → error |
| CP-UNI-56 | Unitaria | Variable sin nombre → error variable_0 |
| CP-UNI-57 | Unitaria | Valor de referencia no decimal → error |
| CP-INT-24 | Integración | Crear tipo predefinido con 2 variables: persiste y aparece en listarTiposTests |
| CP-INT-25 | Integración | Registrar test: estado default Completo, valores con unidad auto-resuelta (cm/bpm) |
| CP-INT-26 | Integración | Registrar sin valores: rechazado, sin persistencia |
| CP-INT-27 | Integración | Registrar sin tipo ni personalizado: rechazado, sin persistencia |
| CP-INT-28 | Integración | Estado explícito Parcial se respeta |
| CP-INT-29 | Integración | Actualizar cambia datos y reemplaza valores (2 → 1) |
| CP-INT-30 | Integración | **Actualizar sin estado corrompe silenciosamente** (estado queda '' — hallazgo TF-5) |
| CP-INT-31 | Integración | Actualizar con datos idénticos → rowCount 0 → "no encontrado" (TF-2b) |
| CP-INT-32 | Integración | Eliminar test borra registro + valores |
| CP-INT-33 | Integración | Eliminar registro inexistente → false |
| CP-INT-34 | Integración | **Regla de negocio:** eliminar tipo con registros → bloqueado |
| CP-INT-35 | Integración | Eliminar tipo sin registros → elimina tipo + variables |
| CP-INT-36 | Integración | Test personalizado con creador: persiste y listing filtrado por creador con variables |
| CP-INT-37 | Integración | Personalizado sin creador → id_usuario_creador = 0 (hallazgo TF-3) |
| CP-INT-38 | Integración | Detalle: valores ordenados; historial de evolución solo con estado Completo |
| CP-INT-39 | Integración | Resumen por atleta cuenta solo aplicaciones Completas |

### Observaciones Técnicas (RF-04/RF-17) — 22 casos · iteración 1b

| ID | Tipo | Escenario |
|---|---|---|
| CP-UNI-58 | Unitaria | Observación completa válida no genera errores |
| CP-UNI-59 | Unitaria | Vacío exige los 3 obligatorios (id_atleta, id_aspecto_tecnico, calificacion) |
| CP-UNI-60 | Unitaria | id_atleta no numérico se rechaza |
| CP-UNI-61 | Unitaria | id_aspecto_tecnico no numérico se rechaza |
| CP-UNI-62 | Unitaria | Calificación '6' fuera de rango se rechaza |
| CP-UNI-62b | Unitaria | Calificación '0' se acepta (comportamiento actual — hallazgo OF-2) |
| CP-UNI-63 | Unitaria | Calificaciones '1'–'5' se aceptan |
| CP-UNI-64 | Unitaria | Texto >500 caracteres se rechaza |
| CP-UNI-65 | Unitaria | id_sesion no numérico se rechaza |
| CP-UNI-66 | Unitaria | Registrar sin datos retorna false sin tocar BD |
| CP-UNI-67 | Unitaria | Actualizar sin id → error |
| CP-UNI-68 | Unitaria | Eliminar con id inválido → error |
| CP-UNI-69 | Unitaria | Actualización permite campos omitidos |
| CP-INT-40 | Integración | Registrar observación completa: persiste calificación, texto y fecha |
| CP-INT-41 | Integración | **Sin id_usuario falla con error BD genérico** (hallazgo OF-1: NOT NULL sin validación) |
| CP-INT-42 | Integración | Calificación '9' rechazada, sin persistencia |
| CP-INT-43 | Integración | Actualizar cambia calificación (3 → 5) y texto |
| CP-INT-44 | Integración | Actualizar id inexistente → false + "no encontrado" |
| CP-INT-45 | Integración | Eliminar es físico y no idempotente |
| CP-INT-46 | Integración | Listar filtro por atleta trae nombres de atleta y aspecto |
| CP-INT-47 | Integración | Detalle con historial por aspecto (2 evaluaciones, orden ascendente) |
| CP-INT-48 | Integración | Resumen por aspecto: promedio 4.0, max 5, min 3 (valida fix DE-03) |

### Transversales (Validaciones, Usuarios/RBAC, Eventos, Notificaciones) — 61 casos

| Grupo | # Casos | Tipo | Resumen |
|---|---|---|---|
| ValidacionesTrait | 17 | Unitaria | Requerido, longitud, letras, fechas, edades, enums, números, correo, cédula, teléfono, decimales, acumulación de errores |
| Contraseñas (UsuarioModelo) | 8 | Unitaria | Corta, sin números, sin letras, límite 6, 130 caracteres, vacía, válida |
| Validación de eventos | 13 | Unitaria | Campos obligatorios, enums tipo/nivel/estado, fechas coherentes, metas (hallazgos HA-01/HA-02) |
| Respuesta JSON | 4 | Unitaria | Catálogo de mensajes íntegro, sin filtración de detalles SQL |
| Usuarios (integración) | 6 | Integración | Crear con hash+rol, correo duplicado, actualizar, toggle activo, reset con desbloqueo, eliminar |
| Eventos (integración) | 5 | Integración | Registrar+detalle, editar, máquina de estados (10 transiciones), inscripción idempotente, conflictos de fechas |
| Notificaciones (integración) | 4 | Integración | Envío+listado, conteo no leídas+idempotencia, regla de propiedad, resolución por correo |
| Humo global | 4 | Humo | Rutas públicas 200 (×3), accesibilidad de ambas BDs |

## 2.2.4 Módulos pendientes — espacio reservado para llenar

> **Estado: AÚN NO SE HAN REALIZADO.** Las siguientes plantillas se completarán cuando se
> diseñen y ejecuten los casos de cada módulo (iteración 2). Test Físico y Observaciones
> Técnicas ya no figuran aquí: sus campañas quedaron ejecutadas en la iteración 1b.

### Antropometría (RF-05) y Alertas Biológicas

| ID Caso | Tipo | Escenario | Resultado Esperado | Estado |
|---|---|---|---|---|
| (por llenar) | | | | |
| (por llenar) | | | | |
| (por llenar) | | | | |

### Sesiones (RF-14) y Asistencia (RF-03)

| ID Caso | Tipo | Escenario | Resultado Esperado | Estado |
|---|---|---|---|---|
| (por llenar) | | | | |
| (por llenar) | | | | |
| (por llenar) | | | | |

### Marcas y Telemetría (RF-06 a RF-09) — responsable: integrante QA de Marcas

| ID Caso | Tipo | Escenario | Resultado Esperado | Estado |
|---|---|---|---|---|
| (por llenar) | | | | |
| (por llenar) | | | | |
| (por llenar) | | | | |

### Carga y Bienestar / RPE (RF-11)

| ID Caso | Tipo | Escenario | Resultado Esperado | Estado |
|---|---|---|---|---|
| (por llenar) | | | | |
| (por llenar) | | | | |
| (por llenar) | | | | |

### Lesiones y Coherencia Clínica (RF-10)

| ID Caso | Tipo | Escenario | Resultado Esperado | Estado |
|---|---|---|---|---|
| (por llenar) | | | | |
| (por llenar) | | | | |
| (por llenar) | | | | |

### Casos nuevos estimados (a definir por el equipo)

| Módulo | # Estimado Casos Nuevos | Responsable |
|---|---|---|
| Antropometría | (por definir) | (por asignar) |
| Sesiones y Asistencia | (por definir) | (por asignar) |
| Marcas y Telemetría | (por definir) | Integrante QA de Marcas |
| Carga y Bienestar / RPE | (por definir) | (por asignar) |
| Lesiones | (por definir) | (por asignar) |
| Seguridad (fuerza bruta, IDOR) | (por definir) | (por asignar) |

---

*Fuente de cifras: ejecuciones PHPUnit 9.6.38 del 25/10/2026 (`tests/results/evidencia-2026-10-25.xml`,
87 pruebas) y del 08/11/2026 (`tests/results/evidencia-2026-11-08.xml`, 145 pruebas).*
*Equipo QA Piscinas Bolivarianas.*
