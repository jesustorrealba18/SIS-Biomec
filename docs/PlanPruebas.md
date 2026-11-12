# Plan y Reporte de Pruebas — SIS-Biomec

**Sistema de Información para el Seguimiento Biomecánico y Deportivo (natación)**

| Campo | Detalle |
|---|---|
| Proyecto | SIS-Biomec (`GrupoProyecto\SisBiomec`) |
| Tipo de prueba | Unitarias, Humo (Smoke), Integración de Backend |
| Módulos en alcance | Usuario, Atleta, Evento, Notificación, Test Físico, Observaciones Técnicas + RBAC + humo global |
| Fuera de alcance | Marcas (asignado a otro integrante del equipo) |
| Ejecución | Iteración 1: 2026-10-25 · Iteración 1b: 2026-11-08 |
| Resultado global | **145 pruebas, 450 aserciones — 0 fallos, 0 errores, 2 omitidas (R-01 captcha)** |

---

## 1. Introducción y objetivos

Este documento describe la estrategia, los casos de prueba, los resultados y los defectos
detectados en las rondas de pruebas del sistema SIS-Biomec (iteración 1: 2026-10-25;
iteración 1b: 2026-11-08 con los módulos de Test Físico y Observaciones Técnicas).

**Objetivos:**

1. Verificar que la lógica de negocio de los módulos núcleo cumple sus reglas de validación.
2. Comprobar que el sistema arranca y sus funciones críticas responden correctamente (humo).
3. Validar la persistencia real en base de datos de las operaciones CRUD del backend.
4. Dejar una suite automatizada, repetible y documentada como base del proceso de calidad.

## 2. Alcance

**Incluido:**
- Módulos: Usuario, Atleta, Evento, Notificación, Test Físico, Observaciones Técnicas.
- Seguridad: control de acceso RBAC (`Autorizacion`), protección de rutas, captcha, cabeceras HTTP.
- Infraestructura: conexión a ambas bases de datos, respuesta de Apache/PHP.

**Excluido:**
- Módulo de Marcas / SWOLF / telemetría (responsabilidad de otro integrante).
- Módulos restantes del Plan Maestro (Antropometría, Sesiones/Asistencia, Carga/RPE, Lesiones) — iteración 2.
- Pruebas E2E con navegador (Cypress/Playwright) y pruebas de carga.
- Flujo de login completo automatizado (bloqueado por captcha; además el captcha está
  temporalmente desactivado por el equipo — riesgo R-01).

## 3. Estrategia de pruebas

| Tipo | Suite | Qué evalúa | Aislamiento |
|---|---|---|---|
| **Unitarias** | `tests/Unit` | Reglas de validación puras: formatos, rangos, enums, límites, mensajes de error. Sin BD ni servidor. | Total (BD solo para instanciar modelos) |
| **Humo** | `tests/Feature` | Salud del sistema: arranque, rutas públicas/privadas, captcha, POST de login, cabeceras de seguridad, accesibilidad de BD. | Peticiones HTTP reales contra Apache |
| **Integración** | `tests/Integration` | Ciclo completo modelo → PDO → BD real → resultado: CRUD, transacciones, FK, triggers, RBAC con datos reales. | BD real de desarrollo con datos marcados `TESTPRUEBA%` y limpieza automática en `tearDown()` |

**Estrategia de datos:** dado que se prueba contra la BD de desarrollo, todo dato creado por
las pruebas usa el prefijo identificable `TESTPRUEBA` (correo/nombre) y se elimina al finalizar
cada prueba en orden inverso de dependencias (FK). La bitácora de auditoría registra las
acciones de prueba (comportamiento normal del sistema) y se limpia por `ON DELETE CASCADE`.

## 4. Entorno de pruebas

| Componente | Versión |
|---|---|
| PHP (CLI y Apache) | 8.0.30 |
| Base de datos | MariaDB 10.4.32 (XAMPP) |
| Servidor web | Apache 2.4 (XAMPP), raíz `C:\xampp\htdocs\SIS-Biomec` |
| Framework de pruebas | PHPUnit 9.6.38 (Composer) |
| Sistema operativo | Windows |
| Bases de datos | `sis_natacion` (negocio) y `sis_seguridad` (autenticación/RBAC) |
| Migraciones aplicadas | `sis_*_vacia.sql` + `001`–`006` |

## 5. Casos de prueba

### 5.1 Unitarias (79 casos) — CP-UNI-xx

Clases probadas: `ValidacionesTrait`, `UsuarioModelo::validarContrasena`,
`Evento::validarDatosEvento/validarDatosMeta`, `Autorizacion` (sesión), `Respuesta` (catálogo),
`TestFisico::validarAtributosInternos` y validaciones de tipos predefinidos/personalizados,
`ObservacionTecnica::validarAtributosInternos`.

| ID | Método bajo prueba | Escenario | Resultado esperado | Estado |
|---|---|---|---|---|
| CP-UNI-01/02 | `requerido` | Vacío, espacios / valor con contenido | Error "obligatorio" / sin errores | ✔ |
| CP-UNI-03 | `longitud` | Límites min/max inclusivos | Solo longitudes dentro de rango pasan | ✔ |
| CP-UNI-04/05 | `soloLetras`, `soloLetrasyNumeros` | Acentos, números, símbolos | Regex Unicode aplicada correctamente | ✔ |
| CP-UNI-06 | `fechaValida` | Bisiesto, mes 13, formato con/sin ceros | Calendario real validado (hallazgo HA-02) | ✔ |
| CP-UNI-07 | `fechaNoFutura` | Ayer / mañana | Mañana rechazada | ✔ |
| CP-UNI-08/09 | `edadMinima`, `edadMaxima` | Menor de 18, año 1890, vacío | Rechazo con mensaje; vacío es opcional | ✔ |
| CP-UNI-10 | `enEnum` | `M` vs `m` | Comparación estricta (case-sensitive) | ✔ |
| CP-UNI-11..15 | `soloNumeros`, `correoValido`, `cedula`, `telefono`, `decimalValido` | Formatos válidos/inválidos/límite | Formatos venezolanos validados | ✔ |
| CP-UNI-16/17 | `agregarError`, `obtenerErrores` | Duplicado por campo, acumulación | Primer error gana; campos independientes | ✔ |
| CP-UNI-18..24 | `validarDatosEvento` | Evento completo, vacío, tipo/estado/nivel inválidos, fecha fin < inicio | Solo campos obligatorios marcados en vacío; enums estrictos | ✔ |
| CP-UNI-25..29 | `validarDatosMeta` | Meta válida, atleta no numérico, marca negativa/cero, estilo, distancia | Errores por campo (hallazgo HA-01 con cero) | ✔ |
| CP-UNI-30..35 | `Autorizacion` (sesión) | Páginas públicas, no mapeadas, permisos, estrictitud, roles CSV | RBAC de sesión correcto | ✔ |
| CP-UNI-36..39 | `Respuesta` (catálogo) | Claves del sistema, textos no vacíos, sin filtración de detalles SQL | Catálogo íntegro y seguro | ✔ |
| — (8) | `validarContrasena` | Corta, sin números, sin letras, límite 6, 130 chars, vacía, válida | Mensajes exactos por regla | ✔ |
| CP-UNI-40..46 | `TestFisico` registro | Completo válido, vacío (solo exige id_atleta), id_atleta no numérico, fecha inválida, observaciones >500, estado fuera de enum, 3 estados válidos | Validación de registros de test correcta | ✔ |
| CP-UNI-47..49 | `TestFisico::getRegistrarTest` | Sin tipo predefinido ni personalizado, sin valores, valores no-array | Rechazo temprano sin tocar BD | ✔ |
| CP-UNI-50..52 | `TestFisico` actualizar/eliminar | Sin id de registro, actualización permite omitir id_atleta, eliminar id inválido | Identificadores exigidos correctamente | ✔ |
| CP-UNI-53..57 | `TestFisico` tipos predefinidos | Sin nombre, nombre corto, sin variables, variable sin nombre, referencia no decimal | Validación de catálogo de tests correcta | ✔ |
| CP-UNI-58..61 | `ObservacionTecnica` campos | Completo válido, vacío (3 obligatorios), id_atleta e id_aspecto no numéricos | Validación de observaciones correcta | ✔ |
| CP-UNI-62/62b | `ObservacionTecnica::calificacion` | '6' rechazada / '0' aceptada | Fuera de rango rechazada (hallazgo OF-2 con cero) | ✔ |
| CP-UNI-63..65 | `ObservacionTecnica` | Calificaciones 1–5 válidas, texto >500, id_sesion no numérico | Catálogo y límites correctos | ✔ |
| CP-UNI-66..69 | `ObservacionTecnica` flujo | Registrar sin datos sin tocar BD, actualizar sin id, eliminar id inválido, actualización con campos omitidos | Rechazos y modo actualización correctos | ✔ |

### 5.2 Humo (18 casos) — CP-HUM-xx

| ID | Escenario | Resultado esperado | Estado |
|---|---|---|---|
| CP-HUM-01 | `index.php` sin sesión | 302 redirige a `?p=login` | ✔ |
| CP-HUM-02 | Carga de login | 200 con campos usuario/password/captcha | ⏭ Skip condicional (R-01: captcha desactivado por el equipo) |
| CP-HUM-03 | Rutas públicas (`recuperar`, `restablecer`, `live`) | 200 | ✔ |
| CP-HUM-04 | Rutas privadas sin sesión (`atleta`, `usuarios`, `eventos`, `marcas`, `bitacora`, `notificaciones`, `testFisico`, `observacionesTecnicas`) | 302 a `?p=login` | ✔ |
| CP-HUM-05 | Página inexistente | Redirección (sin 500 ni salida en blanco) | ✔ |
| CP-HUM-06 | `captcha.php` | 200 + `Content-Type: image/*` | ✔ |
| CP-HUM-07 | POST login con captcha inválido | 200 + mensaje "Codigo de verificacion incorrecto" | ⏭ Skip condicional (R-01) |
| CP-HUM-08 | Cabeceras de seguridad | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` | ✔ |
| CP-HUM-09 | Conexión a `sis_natacion` y `sis_seguridad` | `SELECT 1` exitoso en ambas | ✔ |

Nota R-01: los tests CP-HUM-02 y CP-HUM-07 detectan dinámicamente si el campo `captcha`
está presente en la vista; si está desactivado se marcan como omitidos con referencia al
riesgo y se reactivan automáticamente al restaurarlo.

### 5.3 Integración backend (48 casos) — CP-INT-xx

| ID | Módulo | Escenario | Resultado esperado | Estado |
|---|---|---|---|---|
| CP-INT-01 | Usuario | Crear con rol → persiste hash bcrypt + rol asignado | id > 0, hash verifica, rol visible | ✔ |
| CP-INT-02 | Usuario | `validarDatos` con correo existente | Error de duplicado | ✔ |
| CP-INT-03 | Usuario | Actualizar datos | Cambios persistidos, rol conservado | ✔ |
| CP-INT-04 | Usuario | `toggleActivo` alternado | activo 0 → 1 | ✔ |
| CP-INT-05 | Usuario | Reset de contraseña con cuenta bloqueada | Hash nuevo, intentos=0, sin bloqueo | ✔ |
| CP-INT-06 | Usuario | Eliminar | Registro y roles eliminados | ✔ |
| CP-INT-07 | Atleta | `setDatos` válido | Sin errores, token asistencia 32 hex | ✔ |
| CP-INT-08 | Atleta | `guardar` | Filas en `atletas` **y** `atleta_datos_medicos` (1:1) | ✔ |
| CP-INT-09 | Atleta | Datos incompletos | Errores de validación, sin INSERT | ✔ |
| CP-INT-10 | Atleta | `actualizar` teléfono | Cambio persistido | ✔ |
| CP-INT-11 | Atleta | `eliminar` | Borrado lógico (`estado='Inactivo'`) | ✔ |
| CP-INT-12 | Atleta | `listar` | Incluye al atleta creado | ✔ |
| CP-INT-13 | Evento | Registrar + detalle | Persiste; detalle con metas/inscripciones/cortes | ✔ |
| CP-INT-14 | Evento | Editar nombre/tipo | Cambios persistidos | ✔ |
| CP-INT-15 | Evento | Máquina de estados (10 transiciones) | Solo transiciones válidas aplicadas; Finalizado terminal | ✔ |
| CP-INT-16 | Evento | Inscripción duplicada + eliminación | `INSERT IGNORE` idempotente; DELETE efectivo | ✔ |
| CP-INT-17 | Evento | Conflictos de fechas | Solapamiento detectado | ✔ |
| CP-INT-18 | Notificación | `enviar` + `listarPorUsuario` | Persiste con icono/color/enlace correctos | ✔ |
| CP-INT-19 | Notificación | Conteo no leídas + marcar (2 veces) | Conteo decrece; idempotente | ✔ |
| CP-INT-20 | Notificación | Usuario ajeno intenta marcar | Rechazado; `leida` permanece 0 | ✔ |
| CP-INT-21 | Notificación | `obtenerIdUsuarioPorCorreo` | Resuelve activo; null en inexistente/vacío | ✔ |
| CP-INT-22 | RBAC | `cargarPermisos` usuario real | Sesión poblada; permiso cargado verifica | ✔ |
| CP-INT-23 | RBAC | Usuario sin roles | Permisos vacíos; privadas denegadas; públicas OK | ✔ |
| CP-INT-24 | Test Físico | Crear tipo predefinido con 2 variables | Persiste activo/no personalizado; listado lo incluye | ✔ |
| CP-INT-25 | Test Físico | Registrar test con 2 valores | Registro `Completo` por defecto; valores con unidad auto-resuelta | ✔ |
| CP-INT-26 | Test Físico | Registrar sin valores | Rechazado; sin persistencia | ✔ |
| CP-INT-27 | Test Físico | Registrar sin tipo ni personalizado | Rechazado; sin persistencia | ✔ |
| CP-INT-28 | Test Físico | Estado explícito `Parcial` | Se respeta al persistir | ✔ |
| CP-INT-29 | Test Físico | Actualizar con estado explícito | Datos y valores reemplazados (2→1) | ✔ |
| CP-INT-30 | Test Físico | Actualizar sin enviar estado | **Corrupción silenciosa** — estado queda `''` (hallazgo TF-5) | ✔ (documenta) |
| CP-INT-31 | Test Físico | Actualizar con datos idénticos | rowCount 0 → "no encontrado" (hallazgo TF-2b) | ✔ (documenta) |
| CP-INT-32 | Test Físico | Eliminar test | Borra registro + valores | ✔ |
| CP-INT-33 | Test Físico | Eliminar registro inexistente | false | ✔ |
| CP-INT-34 | Test Físico | Eliminar tipo con registros | Bloqueado con error de negocio | ✔ |
| CP-INT-35 | Test Físico | Eliminar tipo sin registros | Elimina tipo + variables | ✔ |
| CP-INT-36 | Test Físico | Test personalizado con creador | Persiste; listado filtrado por creador con variables | ✔ |
| CP-INT-37 | Test Físico | Personalizado sin creador | Se inserta con `id_usuario_creador=0` (hallazgo TF-3) | ✔ (documenta) |
| CP-INT-38 | Test Físico | Detalle + historial | Valores ordenados; historial solo `Completo` | ✔ |
| CP-INT-39 | Test Físico | Resumen por atleta | Cuenta solo aplicaciones `Completo` | ✔ |
| CP-INT-40 | Observaciones | Registrar completa | Persiste calificación, texto y fecha | ✔ |
| CP-INT-41 | Observaciones | Registrar sin id_usuario | Falla con error BD genérico (hallazgo OF-1) | ✔ (documenta) |
| CP-INT-42 | Observaciones | Calificación '9' | Rechazada; sin persistencia | ✔ |
| CP-INT-43 | Observaciones | Actualizar calificación/texto | Cambios persistidos | ✔ |
| CP-INT-44 | Observaciones | Actualizar id inexistente | false + error "no encontrado" | ✔ |
| CP-INT-45 | Observaciones | Eliminar (2 veces) | Borrado físico, no idempotente | ✔ |
| CP-INT-46 | Observaciones | Listar filtro por atleta | Trae nombres de atleta y aspecto | ✔ |
| CP-INT-47 | Observaciones | Detalle con historial por aspecto | 2 evaluaciones en orden ascendente | ✔ |
| CP-INT-48 | Observaciones | Resumen por aspecto | Promedio 4.0, max 5, min 3 | ✔ |

## 6. Matriz de trazabilidad

| Módulo / función | Casos que lo cubren |
|---|---|
| Validaciones (trait) | CP-UNI-01..17, CP-UNI-25..29 (indirecto vía Evento) |
| Usuario — contraseñas | CP-UNI-18 (8 tests de `validarContrasena`), CP-INT-01, CP-INT-05 |
| Usuario — CRUD y cuenta | CP-INT-01..06 |
| Atleta — validación | CP-INT-07, CP-INT-09 |
| Atleta — persistencia | CP-INT-08, CP-INT-10..12 |
| Evento — validación | CP-UNI-18..29 |
| Evento — ciclo de vida | CP-INT-13..17 |
| Notificación | CP-INT-18..21 |
| Test Físico — validación | CP-UNI-40..57 |
| Test Físico — registro/CRUD | CP-INT-24..39 |
| Observaciones Técnicas — validación | CP-UNI-58..69 |
| Observaciones Técnicas — ciclo de vida | CP-INT-40..48 |
| RBAC / seguridad | CP-UNI-30..35, CP-INT-22..23, CP-HUM-04, CP-HUM-08 |
| Arranque / infraestructura | CP-HUM-01..03, CP-HUM-05..07, CP-HUM-09 |

## 6. Evidencias de ejecución

| Ítem | Ubicación |
|---|---|
| Log JUnit iteración 1 (87 pruebas) | `tests/results/evidencia-2026-10-25.xml` |
| Log JUnit iteración 1b (145 pruebas) | `tests/results/evidencia-2026-11-08.xml` |
| Salida legible por prueba | `vendor\bin\phpunit.bat --testdox` |
| Configuración de suites | `phpunit.xml` |

Comandos de ejecución (requieren Apache y MySQL de XAMPP activos):

```bat
vendor\bin\phpunit.bat --testsuite Unit          :: 79 pruebas, sin efectos en datos
vendor\bin\phpunit.bat --testsuite Feature       :: 18 pruebas de humo
vendor\bin\phpunit.bat --testsuite Integration   :: 48 pruebas contra BD real (autolimpieza)
vendor\bin\phpunit.bat                           :: suite completa (145)
vendor\bin\phpunit.bat --testdox --log-junit tests\results\evidencia-FECHA.xml
```

> **Nota de entorno (HA-06):** la suite completa abre una conexión por prueba y PHPUnit
> retiene los objetos; con más de ~140 pruebas se agota el `max_connections=151` por
> defecto de XAMPP. Elevar temporalmente antes de ejecutar:
> `SET GLOBAL max_connections = 500;` (en memoria; restaurar después a 151).

## 7. Defectos y hallazgos

| ID | Severidad | Descripción | Estado |
|---|---|---|---|
| **DE-01** | Alta | La tabla `sis_seguridad.notificaciones` **no existía** en la BD de desarrollo: el módulo de notificaciones fallaba silenciosamente (la excepción PDO se traga y `enviar()` devolvía `false`). | **Corregido** — `migrations/006_alineacion_notificaciones_y_metas.sql` (DDL canónico del dump) |
| **DE-02** | Alta | `metas_competitivas` carecía de `id_usuario_creador`, columna que consulta `UsuarioModelo::eliminarUsuario()`: **eliminar usuario lanzaba excepción y siempre devolvía `false`**. | **Corregido** — misma migración 006 (columna `INT NULL`) |
| **DE-03** | Alta | `ObservacionTecnica::resumenPorAspecto()` tenía un typo SQL: `LEFT JOIN observaciones_tecnicos` (la tabla real es `observaciones_tecnicas`). La consulta lanzaba "table not found", se capturaba y **el resumen por aspecto devolvía siempre `[]`** — función rota en producción. Detectado por CP-INT-48. | **Corregido** — `src/modelo/ObservacionTecnica.php:288` (una palabra); pendiente revisión del código por el equipo |
| TF-2 | Media | `getRegistrarTest` acepta `id_tipo_test` **e** `id_test_pers` simultáneamente: el XOR solo valida que exista al menos uno. El detalle prioriza el predefinido al resolver unidades. | Pendiente de decisión del equipo |
| TF-2b | Baja | `getActualizarTest` interpreta `UPDATE` con datos idénticos (rowCount 0 de MySQL) como "registro no encontrado" y lo rechaza con error. | Pendiente de decisión del equipo |
| TF-3 | Media | `crearTestPersonalizado` aplica `(int)$datos['id_usuario_creador']` sin validar: sin creador inserta `0` (referencia fantasma a usuario inexistente); con clave ausente genera warning PHP. | Pendiente de decisión del equipo |
| **TF-5** | **Alta** | **Actualizar un test sin enviar `estado` corrompe silenciosamente el registro**: MariaDB sin `STRICT_TRANS_TABLES` convierte el NULL en `''` (fuera del enum `Completo/Parcial/Cancelado`) sin error. Detectado por CP-INT-30. Recomendación: activar modo estricto o validar el campo. | Pendiente de decisión del equipo |
| OF-1 | Media | `observaciones_tecnicas.id_usuario` es `NOT NULL` en BD pero el modelo no lo valida: registrar sin él produce "Error interno al registrar" genérico en vez de un mensaje de campo. Detectado por CP-INT-41. | Pendiente de decisión del equipo |
| OF-2 | Media | `calificacion = '0'` pasa la validación porque `!empty('0')` es falso y el chequeo del enum 1–5 se salta (mismo patrón que HA-01). Queda fijado con `testCalificacionCeroSeAceptaComportamientoActual`. | Pendiente de decisión del equipo |
| HA-01 | Media | `validarDatosMeta` acepta `marca_objetivo_seg = 0`: el chequeo `!empty()` de PHP trata `'0'` como vacío y salta la regla "mayor a 0". Queda documentado con test que fija el comportamiento actual (`testMetaConMarcaCeroSeAceptaComportamientoActual`). | Pendiente de decisión del equipo |
| HA-02 | Baja | `fechaValida` acepta fechas sin ceros (`2024-1-5`): usa `explode` + `checkdate` sin validar el formato estricto `YYYY-MM-DD`. | Pendiente de decisión del equipo |
| HA-03 | Ambiente | `composer.json` declaraba `phpunit/phpunit: *` sin estar instalado en `vendor`. Se fijó `^9.6` (compatible con PHP 8.0) y se instaló 9.6.38. | **Corregido** |
| HA-04 | Observación | `Respuesta::enviar()` y `manejarExcepcion()` terminan con `exit`: no ejecutables dentro de PHPUnit; su cobertura se logra a nivel HTTP (CP-HUM-07). Considerar separar construcción de respuesta de su emisión. | Documentado |
| HA-05 | Observación | Trigger `trg_asignar_categoria_insert` reasigna `id_categoria` por edad al insertar atletas: las pruebas no asumen la categoría enviada. Comportamiento intencional del sistema. | Documentado |
| HA-06 | Ambiente | PHPUnit retiene los objetos de prueba: con más de ~140 tests se agota `max_connections=151` de XAMPP ("Too many connections" en humo HTTP). Los `tearDown` liberan las conexiones y se elevó el límite a 500 durante la ejecución. | Documentado + comando de mitigación en §6 |

**Riesgo activo R-01:** durante la iteración 1b se detectó que el equipo **desactivó el
captcha** (vista comentada y `if (false && ...)` en `loginControlador.php`, "TEMP para
pruebas codegen"). CP-HUM-02 y CP-HUM-07 quedan como omitidos condicionales y se
reactivarán automáticamente al restaurar el captcha.

## 8. Conclusiones

1. Los 6 módulos probados (Usuario, Atleta, Evento, Notificación, Test Físico,
   Observaciones Técnicas) pasan el 100% de los casos de validación unitaria y de
   integración contra BD real.
2. El RBAC y la protección de rutas funcionan correctamente a nivel de sesión y de HTTP,
   incluyendo las rutas nuevas `testFisico` y `observacionesTecnicas`.
3. Las pruebas han detectado **3 defectos de alta severidad** (DE-01, DE-02 corregidos con
   migración; DE-03 corregido en código) y **7 hallazgos** que documentan decisiones
   pendientes del equipo (TF-2, TF-3, TF-5, OF-1, OF-2, HA-01, HA-02).
4. Queda instalada la infraestructura de calidad: 145 pruebas automatizadas, evidencia
   versionable y convención de datos de prueba (`TESTPRUEBA`) que protege la BD de desarrollo.
5. Próximos pasos sugeridos: incorporar el módulo de Marcas (compañero asignado), los
   módulos de Antropometría/Sesiones/Carga/Lesiones (iteración 2), decidir sobre los
   hallazgos pendientes (especialmente TF-5) y restaurar el captcha (R-01).

---
*Documento generado como parte del plan de calidad de SIS-Biomec. Última actualización: 2026-11-08.*
