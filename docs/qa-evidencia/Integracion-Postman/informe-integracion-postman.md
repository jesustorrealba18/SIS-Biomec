# INFORME FORMAL — PRUEBAS DE HUMO Y DE INTEGRACIÓN (POSTMAN)
## Módulos: Atleta, TestFisico, ObservacionTecnica, Periodizacion

| Campo | Valor |
|---|---|
| **Fecha** | 2026-11-12 |
| **Rol** | Ingeniero de QA Senior (enfoque adversarial) |
| **Entorno** | Apache XAMPP (http://localhost/SIS-Biomec), PHP 8.0.30, MariaDB `sis_natacion` + `sis_seguridad`, sesiones PHP reales |
| **Mocks** | **Ninguno**: toda la cadena HTTP → FrontController → controlador → modelo → MySQL → RBAC (`rol_permisos`) → bitácora/notificaciones es real |
| **Colección Postman** | `docs/qa-postman/SIS-Biomec-QA-Modulos.postman_collection.json` (48 requests, 50 aserciones) |
| **Entorno Postman** | `docs/qa-postman/SIS-Biomec-QA.postman_environment.json` |
| **Setup de usuarios QA** | `docs/qa-postman/setup-qa-usuarios.php` (admin con permisos + usuario sin permisos para RBAC) |
| **Limpieza** | `docs/qa-postman/limpieza-qa.php` (verificado: 0 residuos) |

**Evidencia:**
- Smoke PHPUnit: `phpunit-smoke-modulos.txt` — **8/8 OK**
- Newman/Postman: `newman-ejecucion.txt` — **48 requests, 50 aserciones, 13 fallos = 12 defectos confirmados vía HTTP**

Reproducción:
```
php docs/qa-postman/setup-qa-usuarios.php
vendor\bin\phpunit tests\Feature\SmokeModulosQATest.php
npx newman run docs\qa-postman\SIS-Biomec-QA-Modulos.postman_collection.json -e docs\qa-postman\SIS-Biomec-QA.postman_environment.json --timeout-request 20000
```

---

## 1. Pruebas de humo (PHPUnit sobre HTTP real) — 8/8 OK

| ID | Verificación | Resultado |
|----|--------------|-----------|
| CP-HUM-M1 | Login QA real contra `sis_seguridad` establece `PHPSESSID` | OK |
| CP-HUM-M2 | Password incorrecto NO establece sesión (redirige a login) | OK |
| CP-HUM-M3 | `atleta&accion=listar` sin sesión → 302 login | OK |
| CP-HUM-M4 | Atleta: `listar`, `categorias` (JSON 200 + datos) y vista HTML con sesión | OK |
| CP-HUM-M5 | TestFisico: `listarTiposTests`, `listarTests`, `listarAtletasSelect` JSON 200 con datos; sin sesión → 302 | OK |
| CP-HUM-M6 | ObservacionTecnica: `listarAspectosTecnicos`, `listarObservaciones` JSON 200 con datos; sin sesión → 302 | OK |
| CP-HUM-M7 | Periodizacion: `obtenerTemporadas`, `obtenerGrupos`, `listarMacrociclos` JSON 200 con datos; sin sesión → 302 | OK |
| CP-HUM-M8 | Usuario autenticado SIN permisos → **403** en módulo privado | OK |

Nota de arnés: el login regenera el `PHPSESSID` (dos `Set-Cookie`); el helper debía conservar el
último. Corregido en la suite (no es defecto del sistema).

---

## 2. Pruebas de integración Postman — resultado global

| Métrica | Valor |
|---|---|
| Requests ejecutados | 48 |
| Aserciones | 50 |
| Fallos | **13 aserciones → 12 defectos reales (INT-01 … INT-12)** |
| Verificaciones correctas destacables | Login válido/inválido, cédula duplicada rechazada, **concurrencia 2 INSERT simultáneos con misma cédula → exactamente 1 éxito (UNIQUE + rollback OK)**, inyección SQL rechazada por validación, **foto con código PHP rechazada** (fixture `fixtures/shell.png`), RBAC 403 en página y en escritura, transición de estado inválida rechazada, jerarquía fases→mesociclos→microciclos consistente, tiempos de respuesta < 5 s (sin timeouts) |

---

## 3. Defectos confirmados vía HTTP (los 13 fallos de aserción)

### INT-01 (Media) — Atleta: input malformado responde "Error interno" en vez de warning
- **Endpoint:** `POST index.php?p=atleta` (`accion=guardar`, `cedula[]=...` duplicado)
- **Contrato:** rechazo de validación (`status=warning` con `errores.cedula`).
- **Evidencia:** `{"status":"error","message":"Error interno del servidor."}`.
- **Causa:** el `TypeError` de BUG-04 (unitario) llega al `catch (Throwable)` del controlador: el usuario
  ve un error genérico 500-like sin detalle. Degrada el diagnóstico de la API.

### INT-02 (Media) — Atleta: `eliminar` id inexistente reporta `success` por HTTP
- **Endpoint:** `POST ?p=atleta` (`accion=eliminar`, `id_atleta=999999999`)
- **Evidencia:** `{"status":"success","message":"Atleta desactivado correctamente."}` (BUG-07 vía HTTP).

### INT-03 (Baja) — Atleta: `obtener` id inexistente → `200 null` en vez de `404`
- **Endpoint:** `GET ?p=atleta&accion=obtener&id=99999999`
- **Evidencia:** `respuesta = null: expected 200 to equal 404`. Patrón REST ausente en todo el backend.

### INT-04 (Alta) — TestFisico: valor no numérico se registra vía API como 0.00
- **Endpoint:** `POST ?p=testFisico` (`accion=registrar`, `valores[{id}]=no-es-un-numero`)
- **Evidencia:** `{"status":"success","message":"Test fisico registrado correctamente."}` (BUG-TF-03 vía HTTP): corrupción de mediciones por la API pública.

### INT-05 (Crítica) — TestFisico: campo array produce **HTTP 500**
- **Endpoint:** `POST ?p=testFisico` (`accion=registrar`, `estado[]=Completo&estado[]=Parcial`)
- **Evidencia:** `INT-05: el servidor reviento con 500: <!DOCTYPE html>...`
- **Causa:** la rama `registrar` del controlador **no tiene `try/catch`** (a diferencia de Atleta); el
  `TypeError` de `enEnum()` (BUG-TF-01) escala al handler global → página HTML de error 500.
  Denegación de servicio trivial desde el cliente.

### INT-06 (Baja) — TestFisico: `obtenerDetalle` id inexistente → `200 null` (no 404)
- **Endpoint:** `GET ?p=testFisico&accion=obtenerDetalle&id=999999999` — mismo patrón REST que INT-03.

### INT-07 (Baja) — TestFisico: acción POST desconocida → `200` (no 4xx)
- **Endpoint:** `POST ?p=testFisico` (`accion=accion_fantasma_xyz`)
- **Evidencia:** `200` con `{"status":"error","message":"Accion no soportada."}`.

### INT-08 (Alta) — ObservacionTecnica: calificación `0` aceptada vía API
- **Endpoint:** `POST ?p=observacionesTecnicas` (`accion=registrar`, `calificacion=0`)
- **Evidencia:** `success` (BUG-OT-06 vía HTTP). Se persisten evaluaciones fuera de escala.

### INT-09 (Crítica) — ObservacionTecnica: XSS almacenado, visible en la API
- **Endpoints:** `POST ?p=observacionesTecnicas` (`observacion_texto=<b>HACK</b> ...`) + readback `GET ?p=observacionesTecnicas&accion=listarObservaciones&id_atleta=1`
- **Evidencia:** registro OK (`success`) y el listado devuelve `"<b>HACK</b> QAPM-XSS…"` crudo.
- **Causa:** BUG-OT-02. La API almacena y **sirve** HTML crudo a todos los consumidores.

### INT-10 (Media) — Periodizacion: macrociclo de 973 años aceptado vía API
- **Endpoint:** `POST ?p=periodizacion` (`accion=guardar`, `fecha_fin=2999-12-31`)
- **Evidencia:** `success` (BUG-PER-02 vía HTTP). La fila quedó persistida (eliminada en limpieza).

### INT-11 (Alta) — Periodizacion: config imposible (184%) aceptada y genera plan incoherente
- **Endpoint:** `POST ?p=periodizacion` (`accion=generarPeriodizacion`, `pct_realizacion=101`)
- **Evidencia:** `success` con `porcentajes = {acumulacion 7.7%, transmutacion 30.8%, realizacion 100%}`
  y advertencia de desvío — el controlador solo exige `is_numeric`, no valida la suma (BUG-PER-05 vía HTTP).

### INT-12 (Media) — Periodizacion: `eliminarMesociclo` id inexistente reporta `success`
- **Endpoint:** `POST ?p=periodizacion` (`accion=eliminarMesociclo`, `id_mesociclo=999999999`)
- **Evidencia:** `{"status":"success"}` (BUG-PER-04 vía HTTP).

---

## 4. Verificaciones adversariales que SÍ superaron el sistema

| Prueba | Resultado |
|---|---|
| A4 cédula duplicada (validación `unico()` + BD) | Rechazada con `warning.errores.cedula` |
| A5 **Concurrencia**: 2 `POST guardar` simultáneos, misma cédula | Exactamente 1 éxito (UNIQUE + rollback correcto) |
| A6 sexo inválido | Rechazado con warning |
| A10 payload SQL (`V-' OR '1'='1'; --`) | Rechazado por validación, sin error interno |
| A11 **upload de "PNG" con código PHP** (fixture real enviado) | Rechazado con `errores.foto`; payload ni ejecutado ni reflejado |
| P6 consistencia jerárquica del plan ATR | 1 mesociclo por fase, microciclos completos |
| P9 transición de estado inválida | Rechazada por la máquina de estados |
| P11 tiempo de respuesta | < 5 s (sin timeouts con `--timeout-request 20000`) |
| R2/R3 RBAC: usuario autenticado sin permisos | **403** en página y en escritura; 0 filas creadas |
| S2 credenciales inválidas | Error visible, sin sesión emitida |

Hallazgo de arnés documentado (no defecto del sistema): con sesión activa, `POST ?p=login`
redirige a inicio sin revalidar (`loginControlador.php:7-10`) — por eso la colección ejecuta
`?p=salir` antes de las pruebas de sesión negativas.

---

## 5. Inventario de cobertura por endpoint

| Módulo | Endpoints ejercitados |
|---|---|
| Atleta | `listar`, `categorias`, `obtener`, `guardar` (válido/duplicado/sexo inválido/array/SQLi/foto maliciosa), `eliminar` |
| TestFisico | `listarTiposTests`, `obtenerVariables`, `listarTests`, `obtenerDetalle`, `registrar` (válido/sin tipo/valor corrupto/array), `eliminar`, acción desconocida |
| ObservacionTecnica | `listarAspectosTecnicos`, `listarObservaciones`, `registrar` (válido/calificación 0/XSS), `actualizar` (inexistente), `eliminar` (válido e id=0) |
| Periodizacion | `obtenerTemporadas`, `obtenerGrupos`, `listarMacrociclos`, `obtenerDetalle`, `guardar` (válido/año 2999), `generarPeriodizacion` (normal/imposible), `actualizarEstado` (inválida), `eliminarMesociclo` (inexistente) |
| Transversal | login válido/inválido, logout, RBAC 403, concurrencia, timeout, upload malicioso |
