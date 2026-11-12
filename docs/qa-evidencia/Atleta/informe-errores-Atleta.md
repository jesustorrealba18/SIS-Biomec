# INFORME FORMAL DE DEFECTOS — MÓDULO ATLETA

| Campo | Valor |
|---|---|
| **Archivo bajo prueba** | `src/modelo/Atleta.php` (637 líneas) |
| **Suite de pruebas** | `tests/Unit/AtletaCasosEsquinaQATest.php` (14 pruebas) |
| **Fecha de ejecución** | 2026-11-09 |
| **Entorno** | PHP 8.0.30 (XAMPP), PHPUnit 9.6.38, MariaDB `sis_natacion` (XAMPP) |
| **Rol** | Ingeniero de QA (pruebas adversariales de casos esquina) |
| **Resultado global** | **13 defectos CONFIRMADOS / 13 pruebas en FALLO** — 1 prueba de control OK — 1 caso analizado y descartado |

**Evidencia completa de la ejecución:** `docs/qa-evidencia/Atleta/phpunit-ejecucion-AtletaCasosEsquina.txt`
**Comando de reproducción:** `vendor\bin\phpunit tests\Unit\AtletaCasosEsquinaQATest.php`

> Nota de condiciones: la sesión de QA inició el servicio MySQL de XAMPP (`mysqld.exe`) porque estaba
> detenido. Las pruebas crean filas con prefijo `qaatleta%` y archivos temporales propios; se verificó
> tras la ejecución que la BD quedó sin residuos (0 filas `qaatleta%`, 0 registros huérfanos en
> `atleta_datos_medicos`) y que el directorio `assets/uploads/fotos/` quedó sin archivos de prueba.

---

## 1. Metodología

Análisis función por función de `Atleta.php` identificando: valores nulos/indefinidos, tipos de datos
incorrectos (arrays, enteros, objetos donde se esperan strings), desbordamientos de enteros, entradas
malformadas de upload, inyección de datos por vías alternas (`setCampo`) e inyección de rutas en
manejo de archivos. Cada caso se materializó en una prueba PHPUnit que **aserta el contrato correcto**;
si la prueba falla, el defecto quedó reproducido y documentado con su salida real.

La prueba **QA-15 (control)** valida el camino feliz (`setDatos` válidos + `guardar()` = true): pasa
correctamente, lo que descarta problemas de entorno y confirma que los 13 fallos son defectos reales
del código.

---

## 2. Resumen de defectos confirmados

| ID | Severidad | Función (línea) | Defecto | Prueba QA |
|------|-----------|-----------------|---------|-----------|
| BUG-01 | Media | `guardar()` / `guardarAtleta()` (:192) | Warning `Undefined array key "token_asistencia"` al guardar sin `setDatos()` | QA-01 |
| BUG-02 | **Alta** | `guardar()` (:165-229) + `validar()` (:110) | `guardar()` ignora los errores de validación: retorna `true` y persiste datos inválidos | QA-02 |
| BUG-03 | **Crítica** | `setCampo()` (:31) + `guardar()` | **XSS almacenado**: inyección de HTML crudo a la BD sin sanitización ni revalidación | QA-03 |
| BUG-04 | **Alta** | `setDatos()`/`validar()` (:18, :76) | `TypeError` no capturado (HTTP 500) con input de tipo array | QA-04 |
| BUG-05 | Media | `validar()` (:152) + `vincular()` (:59) | `id_categoria` no numérico pasa la validación; fallo posterior silencioso | QA-05 |
| BUG-06 | Media | `actualizar()` (:232) | Desbordamiento de entero en `id_atleta` satura a `PHP_INT_MAX`; fallo sin error registrado | QA-06 |
| BUG-07 | Media | `eliminar()` (:333) | Falso éxito: retorna `true` con 0 filas afectadas (id inexistente) | QA-07 |
| BUG-08 | Media | `procesarFoto()` (:557) | Warning `Undefined array key "error"` con array de upload vacío | QA-08 |
| BUG-09 | Media | `procesarFoto()` (:572) | Warning `Undefined array key "size"` con upload sin esa clave | QA-09 |
| BUG-10 | **Crítica** | `procesarFoto()` (:631-633) | **Borrado arbitrario de archivos**: `unlink(RAIZ . $fotoActual)` sin validar ruta | QA-10 |
| BUG-11 | Media | `validar()` teléfono (:110-112) | Regex acepta teléfonos sin un solo dígito (`+++++++`) | QA-11 |
| BUG-12 | Media | `validar()` (:92-95) | Acepta fecha de nacimiento 1900 (edad 126); `edadMaxima()` existe pero jamás se invoca | QA-12 |
| BUG-14 | Baja | `validar()` (:92-95, :118-121) | Sin validación cruzada: registro en club anterior al nacimiento | QA-14 |

---

## 3. Detalle de cada defecto

### BUG-01 — `guardar()` revienta sin datos previos
- **Función:** `Atleta::guardar()` → `guardarAtleta()`, `src/modelo/Atleta.php:192`
- **Prueba:** `testGuardarSinSetDatosNoDebeEmitirWarningNiIntentarInsertar`
- **Entrada:** `(new Atleta())->guardar()` sin llamar a `setDatos()`.
- **Contrato esperado:** retornar `false` y registrar un error explícito, sin emitir warnings.
- **Resultado observado (log):**
  ```
  [PHPUnit\Framework\Error\Warning] Undefined array key "token_asistencia"
  en Atleta.php línea 192
  ```
  La línea 192 accede directamente a `$this->datos['token_asistencia']`, clave que solo existe si
  `setDatos()` la generó. En producción este warning contamina la salida (rompe headers/JSON) y la
  transacción queda abierta porque la excepción escapa al `catch (PDOException)`.

### BUG-02 — `guardar()` persiste datos que la propia validación rechazó
- **Función:** `Atleta::guardar()` (:165-229); validación de teléfono `:110-112`.
- **Prueba:** `testGuardarNoDebePersistirUnAtletaConTelefonoInvalido`
- **Entrada:** `setDatos()` con teléfono de 15 dígitos (`041412345670123`). La validación genera el
  error (`telefono debe tener entre 7 y 11`), pero la columna BD es `varchar(20)` y lo admite.
- **Contrato esperado:** `guardar()` debe retornar `false` cuando `hayErrores() === true`.
- **Resultado observado (log):**
  ```
  BUG-02: guardar() retorno true con errores de validacion activos y
  persistio un telefono invalido en la BD. — Failed asserting that true is false.
  ```
  **Causa raíz:** `guardar()` nunca consulta `hayErrores()`. La fila inválida quedó insertada en la
  BD durante la prueba (eliminada en `tearDown`). Misma clase de fallo aplicable a `direccion`,
  `alergias` y `condiciones_previas` (validaciones más estrictas que la BD).

### BUG-03 — XSS ALMACENADO vía `setCampo()` + `guardar()`
- **Función:** `Atleta::setCampo()` (:31-34) en combinación con `guardar()`.
- **Prueba:** `testSetCampoNoDebePermitirAlmacenarXssEnTelefono`
- **Entrada:** `setDatos()` válidos → `setCampo('telefono', '<b>HACK</b>123')` → `guardar()`.
- **Contrato esperado:** ningún dato debe llegar a la BD sin pasar por `sanitizar()`/`validar()`.
- **Resultado observado (log):**
  ```
  BUG-03: XSS ALMACENADO — la BD guarda "<b>HACK</b>123" (HTML crudo inyectado
  via setCampo()+guardar()). — Failed asserting that true is false.
  ```
  `setCampo()` escribe en `$this->datos` sin sanitizar ni revalidar, y `guardar()` no revalida.
  La carga útil persistió en `atletas.telefono`. Con `<svg onload=...>` u operadores equivalentes
  caben en `varchar(20)` → **XSS almacenado explotable contra cualquier usuario que consulte el listado.**

### BUG-04 — `TypeError` no capturado (crash 500) con input de tipo array
- **Función:** `Atleta::setDatos()` / `sanitizar()` / `validar()` (:18-25, :40-50, :70-163).
- **Prueba:** `testSetDatosConArrayEnCedulaDebeRegistrarErrorNoLanzarTypeError`
- **Entrada:** `setDatos(['cedula' => ['V-1234567'], ...])` (un atacante puede enviar `cedula[]=x`
  por HTTP y llegar como array).
- **Contrato esperado:** registrar error de validación; jamás lanzar excepción no controlada.
- **Resultado observado (log):**
  ```
  [TypeError] Atleta::requerido(): Argument #1 ($valor) must be of type string,
  array given, called in Atleta.php on line 76
  ```
  `sanitizar()` solo procesa strings y deja pasar arrays intactos; los validadores del trait exigen
  `string`. En web esto es un **HTTP 500 (denegación de servicio)** reproducible por el cliente.

### BUG-05 — `id_categoria` no numérico pasa la validación
- **Función:** `Atleta::validar()` (:152) y `vincular()` (:59).
- **Prueba:** `testIdCategoriaNoNumericoDebeGenerarErrorDeValidacion`
- **Entrada:** `id_categoria => 'abc'` (cumple `requerido`; no se valida tipo numérico).
- **Contrato esperado:** error de validación en `id_categoria`.
- **Resultado observado (log):** `BUG-05: id_categoria="abc" pasa la validacion sin ser numerico.`
  En `guardar()`, `vincular()` lo castea a `(int)'abc' = 0`, el FK `fk_at_cat` revienta, el `catch`
  lo traga y `guardar()` retorna `false` **sin ningún error en `obtenerErrores()`** (fallo mudo para
  el usuario final). Lo mismo aplica a `id_usuario`.

### BUG-06 — Desbordamiento de entero en `actualizar()` con fallo silencioso
- **Función:** `Atleta::actualizar()` (:232, cast `(int)` del id).
- **Prueba:** `testActualizarConIdDesbordadoDebeRegistrarErrorExplicativo`
- **Entrada:** `id_atleta => '99999999999999999999'` (> `PHP_INT_MAX`).
- **Contrato esperado:** rechazo del id con error registrado.
- **Resultado observado (log):**
  ```
  BUG-06: fallo de actualizar() por id desbordado no deja ningun error
  registrado (fallo silencioso). — Failed asserting that an array is not empty.
  ```
  El cast satura a `9223372036854775807`, el `UPDATE` afecta 0 filas, el `INSERT` posterior en
  `atleta_datos_medicos` viola el FK (`1452`, visible en el log de `error_log`) y el método retorna
  `false` con `obtenerErrores()` vacío: el fallo solo es visible en el log del servidor.

### BUG-07 — `eliminar()` reporta éxito sin eliminar nada
- **Función:** `Atleta::eliminar()` / `eliminarAtleta()` (:333).
- **Prueba:** `testEliminarIdInexistenteNoDebeReportarExito`
- **Entrada:** `eliminar(999999999)` (id inexistente).
- **Contrato esperado:** `false` o error registrado cuando 0 filas fueron afectadas.
- **Resultado observado (log):** `eliminar(999999999) retorno true sin haber desactivado ninguna fila
  (falso exito). — Failed asserting that true is false.` `execute()` devuelve `true` aunque el
  `UPDATE` afecte 0 filas y el método propaga ese `true` sin comprobar `rowCount()`.

### BUG-08 — `procesarFoto()` emite Warning con array de upload vacío
- **Función:** `Atleta::procesarFoto()` (:557).
- **Prueba:** `testProcesarFotoConArrayVacioNoDebeEmitirWarnings`
- **Entrada:** `procesarFoto([])`.
- **Contrato esperado:** degradación controlada con `agregarError('foto', ...)`.
- **Resultado observado (log):** `[Warning] Undefined array key "error"` en `Atleta.php:557`.
  No se valida la existencia de las claves del array `$_FILES` antes de usarlas.

### BUG-09 — `procesarFoto()` emite Warning sin la clave `size`
- **Función:** `Atleta::procesarFoto()` (:572).
- **Prueba:** `testProcesarFotoSinClaveSizeNoDebeEmitirWarnings`
- **Entrada:** `['error' => UPLOAD_ERR_OK, 'tmp_name' => <png real>, 'type' => 'image/png']` sin `size`.
- **Resultado observado (log):** `[Warning] Undefined array key "size"` en `Atleta.php:572`.
  Misma clase que BUG-08: acceso a claves de upload sin verificar su existencia.

### BUG-10 — BORRADO ARBITRARIO DE ARCHIVOS vía `fotoActual`
- **Función:** `Atleta::procesarFoto()` (:631-633).
- **Prueba:** `testProcesarFotoNoDebeBorrarArchivosFueraDelDirectorioDeFotos`
- **Entrada:** upload PNG válido + `fotoActual = 'docs/qa-evidencia/Atleta/tmp-victima-unlink.txt'`
  (archivo fuera de `assets/uploads/fotos/`).
- **Contrato esperado:** `unlink()` solo debe operar sobre archivos del directorio de fotos.
- **Resultado observado (log):**
  ```
  BUG-10: procesarFoto() BORRO un archivo fuera de assets/uploads/fotos/
  (unlink sin validar ruta; con "../" permite borrado arbitrario en el servidor).
  ```
  El archivo víctima **fue eliminado del disco** durante la prueba. `unlink(RAIZ . $fotoActual)`
  no valida la ruta: anteponiendo `../` de forma repetida se puede borrar cualquier archivo
  editable por el usuario del proceso web (ej. `.env`, `index.php`).

### BUG-11 — Teléfono compuesto solo de símbolos pasa la validación
- **Función:** validación de teléfono en `Atleta::validar()` (:110-112), regex en
  `ValidacionesTrait.php:168-174`.
- **Prueba:** `testTelefonoCompuestoSoloDeSimbolosDebeSerRechazado`
- **Entrada:** `telefono => '+++++++'`.
- **Resultado observado (log):** `telefono="+++++++" (sin un solo digito) pasa la validacion.`
  La regex `[\d\-\+\(\)\s]{7,11}` no exige presencia de dígitos. Se persiste basura en la BD con
  `guardar()` = true (combinado con BUG-02).

### BUG-12 — Edad imposible aceptada (1900)
- **Función:** validación de `fecha_nacimiento` en `Atleta::validar()` (:92-95).
- **Prueba:** `testFechaNacimientoAnio1900DebeSerRechazadaPorEdadImposible`
- **Entrada:** `fecha_nacimiento => '1900-01-01'` (atleta de 126 años).
- **Resultado observado (log):** pasa la validación completa sin errores. El trait dispone de
  `edadMaxima()` (ValidacionesTrait.php:93) pero `Atleta::validar()` **jamás la invoca**. Los
  cálculos de categoría FEVEDA y `TIMESTAMPDIFF` de los listados quedan adulterados.

### BUG-14 — Registro en el club anterior al nacimiento
- **Función:** `Atleta::validar()` (:92-95 y :118-121), sin validación cruzada.
- **Prueba:** `testFechaRegistroClubAnteriorAlNacimientoDebeSerRechazada`
- **Entrada:** `fecha_nacimiento = '2010-06-15'` + `fecha_registro_club = '1901-01-01'`.
- **Resultado observado (log):** ambas fechas son individualmente válidas y el conjunto pasa sin
  errores: se puede inscribir a un atleta en el club 109 años antes de nacer.

---

## 4. Hallazgos estáticos adicionales (revisión de código, sin prueba ejecutable)

| ID | Función (línea) | Observación |
|----|-----------------|-------------|
| OBS-01 | `listarMenoresSinRepresentante()` (:503-506) | `catch (PDOException)` **vacío**: ni registra `error_log` ni reporta; un fallo de BD se percibe como "no hay menores". Inconsistente con el resto del módulo. |
| OBS-02 | `procesarFoto()` (:556-582) | Confía en `$archivo['type']` (cabecera falsificable por el cliente) y no llama a `is_uploaded_file()`; el `tmp_name` puede apuntar a cualquier ruta local del servidor. |
| OBS-03 | `listarMenoresParaRepresentante()` (:447-473) | `NOT IN (SELECT id_atleta FROM atleta_representante ...)` devuelve conjunto vacío si la subconsulta contiene algún `NULL`; además el parámetro por defecto `0` no se valida. |
| OBS-04 | `eliminar()` (:327-338) | El soft-delete (`estado='Inactivo'`) no libera los vínculos en `atleta_representante`: menores inactivos siguen apareciendo como vinculados. |
| OBS-05 | `vincular()` (:52-56) | Convierte `''` a `NULL` de forma silenciosa: campos obligatorios que lleguen como cadena vacía se enlazan como NULL en lugar de rebotar por validación. |

**Causa raíz transversal:** `guardar()` y `actualizar()` nunca consultan `hayErrores()` antes de
escribir en la BD. De ahí se derivan BUG-02, BUG-03 y la mitad silenciosa de BUG-05.

---

## 5. Caso analizado y descartado

| ID | Caso | Motivo de descarte |
|----|------|--------------------|
| QA-13 | Correo con formato válido de 160+ caracteres (columna `varchar(150)`) | Verificado empíricamente: `filter_var(FILTER_VALIDATE_EMAIL)` en PHP 8.0.30 rechaza partes locales > 64 caracteres y dominios largos, por lo que `correoValido()` ya bloquea este caso esquina. No es defecto. |

---

## 6. Prueba de control (entorno validado)

| Prueba | Resultado | Propósito |
|--------|-----------|-----------|
| `testControlGuardarDatosValidosRetornaTrue` (QA-15) | **OK** | Confirma que el arnés funciona y la BD es accesible; los 13 fallos restantes son defectos del código, no del entorno. |

---

## 7. Inventario de pruebas por función

| Función probada | Pruebas | Defectos expuestos |
|-----------------|---------|--------------------|
| `guardar()` / `guardarAtleta()` | QA-01, QA-02, QA-03, QA-15 (control) | BUG-01, BUG-02, BUG-03 |
| `setDatos()` / `sanitizar()` / `validar()` | QA-04, QA-05, QA-11, QA-12, QA-14 | BUG-04, BUG-05, BUG-11, BUG-12, BUG-14 |
| `setCampo()` | QA-03 | BUG-03 |
| `actualizar()` | QA-06 | BUG-06 |
| `eliminar()` / `eliminarAtleta()` | QA-07 | BUG-07 |
| `procesarFoto()` | QA-08, QA-09, QA-10 | BUG-08, BUG-09, BUG-10 |
| `vincular()` (indirecta) | QA-05, QA-06 | BUG-05, BUG-06 |

Funciones revisadas sin defecto reproducible en pruebas adversarias: `getCampo()`, `hayErrores()`,
`listar()`, `obtenerDetallePorId()`, `obtenerDetallePorIdUSER()`, `obtenerCategorias()`,
`obtenerAtletasPorSesion()`, `obtenerAtletasPorEvento()` (degradan a `[]`/`null` ante ids inválidos,
aunque ver OBS-01/OBS-03/OBS-04 para las observaciones estáticas asociadas).
