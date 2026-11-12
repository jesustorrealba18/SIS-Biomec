# Plan Maestro de Pruebas — SIS-Biomec

**Sistema de Información para el Seguimiento Biomecánico y Deportivo (Natación)**
**Equipo QA: Piscinas Bolivarianas**

| Campo | Detalle |
|---|---|
| Proyecto | SIS-Biomec (`GrupoProyecto\SisBiomec`) |
| Documento | Plan Maestro de Pruebas |
| Versión | 1.0 — 2026-10-25 |
| Alcance | 7 módulos funcionales + 3 transversales |
| Estado iteración 1 | Ejecutada: 87 casos, 266 aserciones, 100 % aprobadas |
| Documento complementario | `docs/PlanPruebas.md` (reporte detallado iteración 1) |

---

## 1. Alcance y Módulos del Plan

El plan cubre la totalidad de los módulos funcionales del sistema. Los módulos 1 y 2 y los
transversales ya cuentan con una primera ronda de pruebas ejecutada; los módulos 3–7 tienen
pruebas planificadas en la siguiente iteración.

### Módulo 1 — Login y Seguridad (RNF-01) · *probado parcialmente*

| Submódulo | Descripción |
|---|---|
| Autenticación | `password_verify` contra hash bcrypt; mensaje único anti-enumeración |
| Captcha | Imagen GD de 6 caracteres (`abcdefghjkmnpqrstuvwxyz23456789`), case-insensitive, un solo uso |
| Anti-fuerza bruta por cuenta | `intentos_fallidos`: **5 intentos → bloqueo 15 minutos** (`bloqueado_hasta`) |
| Anti-fuerza bruta por IP | Tabla `intentos_login`: **20 fallos / 15 minutos** |
| RBAC | `Autorizacion`: permisos `modulo.accion` recargados por petición; mapa página→permiso; `exigir()` → HTTP 403 |
| Gestión de sesión | Timeout **SESSION_TIMEOUT=28800 s (8 h)**, `session_regenerate_id(true)`, cookies httponly/strict/samesite |
| Bitácora | Registro de operaciones (`CREATE/UPDATE/DELETE/LOGIN/LOGOUT/EXPORT`) con IP y user-agent |
| Recuperación de contraseña | Token + expiración + SMTP (PHPMailer) |

### Módulo 2 — Atletas (RF-01) · *probado parcialmente (CRUD/token)*

| Submódulo | Descripción |
|---|---|
| CRUD transaccional | Doble tabla `atletas` + `atleta_datos_medicos` (1:1) en transacción; borrado **lógico** (`estado='Inactivo'`) |
| Categoría FEVEDA automática | Trigger SQL por edad: Pre-Infantil (8-10), Infantil A (11-12), Infantil B (13-14), Juvenil A (15-16), Juvenil B (17-18), Maxima (19-99); fallbacks: < 8 → cat 1, sin match → cat 6 |
| Foto de perfil | JPEG/PNG máx **2 MB**, recorte 300×300, calidad 85/9 |
| Token de asistencia | `bin2hex(random_bytes(16))` → **32 hex**, UNIQUE en BD |
| Pase QR | Generación client-side (`qrcodejs`, nivel H) + lectura con cámara (`html5-qrcode`); la seguridad real es la validación server-side del token |
| Vinculación representante | Solo atletas **< 18 años**, un solo representante activo; autorizaciones médica/imagen con triggers de fecha |
| Validaciones | Cédula `V/E-1234567(8)` única; enums sexo M/F, estado (Activo/Inactivo/Retirado/Transferido), grupo sanguíneo (8 valores); fechas no futuras |

### Módulo 3 — Antropometría (RF-05) · *sin pruebas aún*

| Submódulo | Descripción |
|---|---|
| Registro de medición | peso_kg, talla_cm, envergadura_cm, perímetro_abdominal_cm, % grasa (manual) + IMC calculado server-side (1 decimal) |
| Edición controlada | Justificación obligatoria; `id_atleta` nunca se actualiza |
| Anulación/reactivación | Soft-delete (`deleted_at` + motivo); reactivar restaura alertas |
| Historial y gráficas | Evolución por atleta (Chart.js) |
| KPIs | Vencidas **> 84 días**, cobertura ≤ 90 días, mediciones últimos 30 días |
| Alertas biológicas | **Pérdida de peso ≥ 2.0 %** vs medición previa → `PÉRDIDA_PESO_SEVERA`, gravedad 3 → staff (roles 1,3) + entrenador |
| Cron de vencimiento | Notifica el día exacto **85** (ciclo objetivo 90 días) a atleta y representante |
| Aislamiento por rol | Atleta ve solo lo suyo (`id_usuario`); representante solo sus menores |

**Límites biológicos validados (blindaje):** peso **20–200 kg** · talla y envergadura **100–250 cm** · perímetro abdominal **40–150 cm** · % grasa **3–50 %** · fecha no futura con máx **1 mes** de antigüedad.

### Módulo 4 — Sesiones y Asistencia (RF-14 / RF-03) · *sin pruebas aún*

| Submódulo | Descripción |
|---|---|
| Planificación de sesiones | Cabecera + series por bloques (Calentamiento/Principal/VueltaCalma) y zonas Z1–Z5, con drills |
| Ciclo de vida | Planificada → Parcial (iniciar) → Completada / Cancelada; iniciar solo desde Planificada |
| Volumen | Planificado = Σ(repeticiones × distancia); ejecutado **≤ planificado** al cerrar |
| Asistencia por QR | Token del atleta valida pertenencia al grupo de la sesión (`estado='Activo'`); valores forzados Presente/QR; **inmutable** (no sobrescribible) |
| Asistencia manual | Upsert Presente/Ausente/Justificado/Tardanza; Justificado exige motivo (5–255) |
| Convocados | Listado del grupo + estado actual + categoría FEVEDA |
| Notificaciones | Creación/inicio/cierre/cancelación por grupo; asistencia notifica a atleta (y representante si menor) |

**Validaciones:** duración de sesión **15–480 min** · fecha no pasada al crear · series (repeticiones 1–100, distancia 25–10000 m, descanso ≤ 600 s) · cierre exige volumen ejecutado válido.

### Módulo 5 — Marcas y Telemetría (RF-06) · *sin pruebas aún (compañero asignado)*

| Submódulo | Descripción |
|---|---|
| Registro manual CRUD | Alta/editar/archivar (motivo ≥ 5)/reactivar, con bitácora y notificaciones |
| **Regla XOR** | `id_sesion` XOR `id_evento`: ambos → error; ninguno → error |
| Motor PB auto-reparable | Al insertar/editar/archivar/reactivar: reset `es_pb=0` de la categoría (atleta+estilo+distancia+piscina) y coronación del mejor `Activo` (tiempo ASC, desempate por fecha) |
| SWOLF | `round(tiempo/largo + brazadas)`, largo 25/50 según piscina; se guarda solo si brazadas > 0 |
| Splits | **Exactamente distancia/25** tramos; claves múltiplos de 25 ≤ distancia |
| Integridad sesión | Atleta con asistencia **Presente** + fecha de marca **idéntica** a la sesión |
| Integridad evento | Inscripción en `evento_inscripcion` + fecha dentro de `[inicio, fin]` del evento |
| Telemetría en vivo | Tabla MEMORY `telemetria_live` (1 fila/atleta); estados `esperando/iniciando/en_curso/en_viraje/finalizado/cancelado`; GC > 15 min |
| Pizarra/lobby | Ghost PB (PB + reacción 0.65 s si no tiene) como objetivo en vivo |
| Selectores y conversión | Cascada atletas→sesiones/eventos→presentes/inscritos; conversión 25↔50 m vía `factores_conversion` |

### Módulo 6 — Carga y Bienestar / RPE (RF-11) · *sin pruebas aún*

| Submódulo | Descripción |
|---|---|
| Registro RPE + bienestar | RPE, sueño, sensación muscular, estrés, metros, duración; vínculo opcional a sesión |
| Cálculo sRPE | Foster: `sRPE = RPE × duración_min` (en registro y edición) |
| Carga semanal | SUM(sRPE) últimos **7 días** |
| Recomendaciones | **Sobrecarga: carga semanal > 600 sRPE** → reducir volumen 20 %; **Recuperación: RPE promedio 3 días > 7 y sueño promedio 3 días < 6** |
| Papelera | Soft-delete con justificación + reactivación + eliminación física solo desde papelera |
| Auditoría de inconsistencias | **RPE = 1 el mismo día de un PB** (`es_pb=1`) → listado y anulación asistida |
| Sesiones pendientes de RPE | Completadas con asistencia y sin registro (límite 15) |

**Límites validados:** **RPE > 10 rechazado** (escala 1–10) · bienestar 1–10 por ítem · horas de sueño **0–24** · duración/metros ≥ 0 · fecha no futura, máx **1 mes** de antigüedad · unicidad: 1 registro activo por (atleta, sesión) · la sesión debe estar **Completada** con asistencia Presente/Tardanza.

### Módulo 7 — Lesiones y Coherencia Clínica (RF-10) · *sin pruebas aún*

| Submódulo | Descripción |
|---|---|
| Informe clínico CRUD | Registrar/editar/anular (motivo)/reactivar; borrado siempre lógico |
| **Biyección clínica** | `nivel_molestia` (1–10) → gravedad **obligatoria**: **1-3→Leve, 4-7→Moderada, 8-10→Grave** (molestia 9 = Grave, NUNCA Leve) |
| Coherencia zona↔lado | Zonas médicas (Respiratorio, Gastrointestinal, General, Oído/Ojo/Cabeza) sin lado; zonas centrales (Espalda, Cervical, Lumbar) solo Bilateral |
| Coherencia tipo↔zona | `Enfermedad` exige zona médica u Otra; tipos traumáticos (Sobreuso/Aguda/Recidiva) sin zona médica; Enfermedad sin lateralidad |
| Catálogos | 16 zonas · lados Izquierdo/Derecho/Bilateral · 4 tipos · estados Activa/EnRehabilitacion/Recuperada/Cronica |
| Detalle con cruce RPE | Historial `registro_rpe` en **± 15 días** + promedio RPE de **3 días previos** a `fecha_inicio` |
| Alertas clínicas | Molestia **≥ 8** o Recidiva → gravedad 2/3 (`DOLOR_AGUDO`/`ALERTA_RECIDIVA`); alta médica → `ALTA_MEDICA` gravedad 1 |
| Detección de vulneración | Enums inválidos o bypass → bitácora `INTENTO_BLOQUEADO` + **HTTP 403** |
| Notificaciones | 4 audiencias: atleta (+representante si menor), entrenador del grupo, staff médico/admin (roles 1,3) |

### Módulos transversales · *ya probados (iteración 1)*

| Módulo | Estado |
|---|---|
| Usuarios y RBAC | 6 casos unitarios + 6 integración (CP-INT-01..06, CP-INT-22..23) |
| Eventos | 13 unitarios + 5 integración (máquina de estados, inscripciones idempotentes, conflictos) |
| Notificaciones | 4 integración (envío, conteo, idempotencia, regla de propiedad) |
| Humo global | 16 casos (arranque, rutas, captcha, cabeceras, BDs) |

---

## 2. Objetivos de las Pruebas

1. **Validar la seguridad de acceso**: mecanismos anti-fuerza bruta (cuenta: 5 intentos/15 min;
   IP: 20 fallos/15 min), captcha, mensajes anti-enumeración, endurecimiento de sesión y RBAC.
2. **Validar la protección contra IDOR** (referencia directa de objetos insegura): scoping por
   rol — Atleta solo accede a sus recursos, Representante a sus menores, Entrenador a sus
   grupos. Carga/Bienestar y Antropometría son la referencia de protección completa; se
   verificará que Marcas y la ruta pública `live` no expongan recursos ajenos.
3. **Validar los límites biológicos**: rechazo de valores fisiológicamente imposibles
   (Peso > 200 kg, RPE > 10, talla/envergadura > 250 cm, % grasa > 50 %, sueño > 24 h,
   perímetro > 150 cm) y de los rangos mínimos correspondientes.
4. **Validar la biyección clínica estricta**: la combinación molestia↔gravedad es determinista
   (Molestia 9 = Grave, NUNCA Leve), además de las coherencias zona↔lado y tipo↔zona.
5. **Validar la regla XOR en marcas**: una marca pertenece a una sesión de entrenamiento **O**
   a un evento competitivo, jamás a ambos ni a ninguno.
6. **Validar la integridad de datos**: transacciones (atleta, usuario, evento), claves
   foráneas, triggers (categoría FEVEDA, fechas de autorización), unicidades (token QR,
   cédula, correo, RPE por sesión) y borrado lógico con motivo trazable.
7. **Validar reglas de negocio derivadas**: IMC (1 decimal), SWOLF, sRPE de Foster, recálculo
   auto-reparable de PB, generación de alertas (pérdida de peso ≥ 2 %, sobrecarga > 600 sRPE,
   dolor agudo/recidiva, alta médica).
8. **Verificar la continuidad operativa** (humo): arranque de la aplicación, protección de
   rutas privadas, captcha operativo, cabeceras de seguridad y salud de ambas bases de datos.

---

## 3. Orden de Ejecución de los Módulos

La secuencia responde a las dependencias de datos de la base transaccional `sis_natacion`:
cada módulo solo se prueba cuando sus datos pre-requisito ya existen y son consistentes.

| # | Módulo (RF) | Dependencia que lo habilita |
|---|---|---|
| 1 | **Login y Seguridad (RNF-01)** | Sin dependencias. Genera usuarios con rol y sesiones válidas que todos los demás módulos consumen. |
| 2 | **Registro y Gestión de Atletas (RF-01) + QR** | Requiere usuarios/roles (paso 1). `atletas.id_atleta` es la FK raíz de antropometría, asistencia, marcas, RPE y lesiones; además genera `token_asistencia` que consume el paso 4. |
| 3 | **Antropometría (RF-05) y Alertas Biológicas** | Requiere atletas (paso 2). Produce filas en `alertas_biologicas` que se reutilizan para validar la integración con lesiones (paso 7). |
| 4 | **Sesiones y Asistencia (RF-14 / RF-03)** | Requiere atletas en grupos (paso 2). Produce los estados que bloquean/desbloquean los pasos 5 y 6: asistencia `Presente` y sesiones `Completada`. |
| 5 | **Marcas y Telemetría (RF-06)** | Requiere asistencia `Presente` + sesión con fecha idéntica (vía sesión) o inscripción en evento (vía competencia). Produce `es_pb=1` que consume el paso 6. |
| 6 | **Carga y Bienestar (RF-11)** | Requiere sesiones `Completada` + asistencia `Presente/Tardanza` (paso 4) y marcas PB (paso 5) para la auditoría de inconsistencias biológicas. |
| 7 | **Lesiones y Coherencia Clínica (RF-10)** | Requiere atletas (paso 2); su detalle clínico cruza `registro_rpe` de los pasos 4–6 (±15 días), por eso se ejecuta al final. Escribe `alertas_biologicas` y se valida contra las del paso 3. |

Flujo de datos resumido:

```
Login/Seguridad ──▶ Atletas (+QR) ──▶ Antropometría (alertas)
        │                 │
        │                 ├──▶ Sesiones + Asistencia (Presente / Completada)
        │                 │            │
        │                 │            ├──▶ Marcas + Telemetría (PB)
        │                 │            │            │
        │                 │            └──▶ Carga y Bienestar (RPE)◀─┘
        │                 │                         │
        └─────────────────┴────────▶ Lesiones (cruce RPE + alertas)◀───┘
```

---

## 4. Responsabilidad de la Prueba

**Equipo QA Piscinas Bolivarianas** — responsable de ejecutar, registrar y validar el presente plan.

| Rol | Responsabilidad | Integrante |
|---|---|---|
| Coordinador QA | Plan maestro, criterios de entrada/salida, consolidación de resultados y aprobación | `<Nombre>` |
| Analista de Pruebas Backend | Suites unitarias e integración (BD real, autolimpieza `TESTPRUEBA`), reporte de defectos | `<Nombre>` |
| Probador de Seguridad | Fuerza bruta, IDOR, RBAC, sesiones, cabeceras (con apoyo de análisis estático progpilot y ZAP) | `<Nombre>` |
| Probador Marcas / Telemetría | Módulo RF-06 completo: XOR, PB, SWOLF/splits, telemetría en vivo | `<Nombre (compañero asignado)>` |
| Probador Funcional | Humo HTTP, flujos de UI (QR, gráficas, papelera), verificación manual de hallazgos | `<Nombre>` |

Reglas operativas del equipo:
- Todo dato de prueba usa el prefijo `TESTPRUEBA` y se elimina al cerrar cada caso (autolimpieza en `tearDown()`).
- Toda corrida deja evidencia JUnit en `tests/results/evidencia-FECHA.xml`.
- Los defectos se registran con la convención DE-xx (defecto) / HA-xx (hallazgo) del reporte de iteración.

---

## 5. Tipos de Prueba por Módulo

| Módulo | Unitarias | Humo | Integración BD | Seguridad |
|---|---|---|---|---|
| 1. Login y Seguridad | Validaciones de credenciales/contraseña ✔ | Página login, captcha, cabeceras ✔ | Intentos/bloqueos en BD, roles ✔ | Fuerza bruta, enumeración, timeout — *planificado* |
| 2. Atletas | Validaciones del modelo ✔ | Ruta protegida ✔ | CRUD doble tabla, trigger categoría, token ✔ (CP-INT-07..12) | Cédula/ correo únicos, anti-manipulación representante — *planificado* |
| 3. Antropometría | Límites biológicos, IMC — *planificado* | Ruta protegida ✔ | Mediciones, alertas, cron — *planificado* | Aislamiento por rol (IDOR) — *planificado* |
| 4. Sesiones/Asistencia | Series, duración, volumen — *planificado* | Ruta protegida ✔ | Ciclo de vida, QR inmutable, upsert — *planificado* | Pertenencia al grupo, tipos de registro — *planificado* |
| 5. Marcas | XOR, splits, SWOLF, enums — *planificado* | Ruta protegida, live ✔ | PB auto-reparable, integridad sesión/evento — *compañero* | Ownership, exposición de `live` — *compañero* |
| 6. Carga/Bienestar | Escalas 1-10, sRPE, ventana 1 mes — *planificado* | Ruta protegida ✔ | Sobrecarga, unicidad, inconsistencias — *planificado* | IDOR (referencia de protección), 403 vulneración — *planificado* |
| 7. Lesiones | Biyección clínica, catálogos, coherencias — *planificado* | Ruta protegida ✔ | Alertas, alta médica, cruce RPE — *planificado* | HTTP 403 anti-manipulación — *planificado* |
| Transversales (Usuarios/Eventos/Notificaciones) | ✔ 27 casos | ✔ 16 casos | ✔ 18 casos | RBAC ✔ (CP-INT-22..23) |

✔ = ejecutado en iteración 1. *Planificado/compañero* = iteración 2.

---

## 6. Riesgos y Condiciones Previas Detectadas

| ID | Riesgo / condición | Impacto en las pruebas | Mitigación |
|---|---|---|---|
| R-01 | Captcha desactivado temporalmente (`if (false && ...)` en `loginControlador`) | Las pruebas de login con captcha (CP-HUM-07) pasan pero no ejercen la protección real | Reactivar antes de la iteración de seguridad; documentar en resultados |
| R-02 | `RATE_LIMIT_ENABLED=false` en `.env` | Fuerza bruta por cuenta e IP inoperativa en este entorno | Activar solo para la campaña de pruebas de seguridad |
| R-03 | Ruta `live` pública sin ownership | Exposición de telemetría/datos por `id_atleta` (IDOR potencial) | Caso de seguridad dedicado en iteración 2; decidir si requiere sesión o token de pizarra |
| R-04 | Discrepancia de enums asistencia: modelo valida `Retardo`, BD usa `Tardanza` | Registro manual de tardanza puede ser rechazado por el modelo | Caso de regresión específico; alinear modelo o BD |
| R-05 | Esquema `lesiones` desfasado con migraciones (columnas/enums nuevos sin versión) | Entornos nuevos fallarán al instalar | Generar migración `007` con el DDL real antes de probar el módulo |
| R-06 | Hallazgos de iteración 1 pendientes de decisión: marca=0 aceptada (HA-01), fechas sin ceros (HA-02) | Comportamiento no estricto aceptado por diseño actual | Tests fijan el comportamiento actual; decisión pendiente del equipo |

---

## 7. Entorno, Criterios y Evidencias

**Entorno:** PHP 8.0.30 · MariaDB 10.4.32 (XAMPP) · Apache 2.4 · PHPUnit 9.6.38 · BDs
`sis_natacion` + `sis_seguridad` con migraciones `001`–`006` aplicadas.

**Criterios de entrada:**
1. Apache y MySQL de XAMPP activos; `http://localhost/SIS-Biomec/index.php` responde.
2. Suite de humo (16 casos) en verde antes de ejecutar cualquier suite de integración.
3. BD con migraciones al día; respaldo reciente en `src/config/backups/`.

**Criterios de salida (por módulo):**
1. 100 % de casos planificados ejecutados; sin fallos abiertos de severidad alta.
2. Cero registros residuales `TESTPRUEBA%` en la BD.
3. Evidencia JUnit generada y reporte actualizado (DE-xx/HA-xx).
4. Hallazgos documentados con severidad y propuesta de mitigación.

**Comandos:**

```bat
vendor\bin\phpunit.bat --testsuite Unit          :: unitarias
vendor\bin\phpunit.bat --testsuite Feature       :: humo (prerequisito)
vendor\bin\phpunit.bat --testsuite Integration   :: integración BD real
vendor\bin\phpunit.bat --testdox --log-junit tests\results\evidencia-FECHA.xml
```

---
*Plan Maestro de Pruebas — Equipo QA Piscinas Bolivarianas. Versión 1.0 (2026-10-25).*
