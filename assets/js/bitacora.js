// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================

// NUEVA RUTA DIRECTA AL CONTROLADOR PIVOTE A TRAVÉS DEL INDEX:
const API_URL = 'index.php?p=bitacora'; 
const modalRep = document.getElementById('modalBitacora');
let registrosBitacora = []; // Variable global para guardar los datos cargados
let registrosActuales = []; // SOLO los datos filtrados (Lo que se va al PDF)

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaBitacora();
});
/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}


async function cargarTablaBitacora() {
    const tbody = document.getElementById('tbodyBitacora');
    tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando registros...</td></tr>';
    
    const respuesta = await peticionAjax('listar');
    
    if (respuesta && respuesta.status === 'success') {
        registrosBitacora = respuesta.data;
        registrosActuales = registrosBitacora;
        extraerModulosDinamicamente(registrosBitacora);
        extraerUsuariosDinamicamente(registrosBitacora);
        dibujarTabla(registrosBitacora);
    } else {
        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500">Error al cargar la bitácora.</td></tr>';
    }
}

function dibujarTabla(datos) {
    const tbody = document.getElementById('tbodyBitacora');
    tbody.innerHTML = '';

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">No hay registros en la bitácora.</td></tr>';
        return;
    }

    datos.forEach((fila, index) => {
        // Colores según la operación (ENUM de tu BD)
        let colorBadge = 'bg-gray-500/20 text-gray-400 border-gray-500/30';
        if (fila.tipo_operacion === 'CREATE') colorBadge = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
        if (fila.tipo_operacion === 'UPDATE') colorBadge = 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        if (fila.tipo_operacion === 'DELETE') colorBadge = 'bg-red-500/20 text-red-400 border-red-500/30';
        if (fila.tipo_operacion === 'LOGIN' || fila.tipo_operacion === 'LOGOUT') colorBadge = 'bg-purple-500/20 text-purple-400 border-purple-500/30';

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 transition-colors';
        tr.innerHTML = `
            <td class="p-4 font-mono text-xs text-gray-400">${formatoFechaHora(fila.fecha_operacion)}</td>
            <td class="p-4">
                <div class="font-bold text-white text-xs">${fila.nombres} ${fila.apellidos}</div>
                <div class="text-[10px] text-gray-500">${fila.rol_nombre || 'Sin Rol'}</div>
            </td>
            <td class="p-4"><span class="text-indigo-400 font-semibold text-xs">${fila.modulo_afectado}</span></td>
            <td class="p-4">
                <span class="${colorBadge} px-2 py-1 rounded text-[10px] font-bold tracking-wider uppercase">
                    ${fila.tipo_operacion}
                </span>
            </td>
            <td class="p-4 text-right">
                <button onclick="verDetalleBitacora(${index})" class="text-blue-400 hover:text-blue-300 transition" title="Ver detalle completo">
                    <i class="fas fa-eye fa-lg"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL Y FORMATO DE DETALLES)
// =====================================================================

function verDetalleBitacora(indiceArreglo) {
    // Obtenemos el registro exacto del arreglo global usando su índice
    const registro = registrosBitacora[indiceArreglo];
    
    // 1. Llenamos los datos fijos de la parte inferior del modal
    document.getElementById('detalleIP').textContent = registro.ip_origen || 'No registrada';
    // Si tuvieras el User-Agent del navegador en BD podrías ponerlo aquí, por ahora un texto fijo o guión
    document.getElementById('detalleNavegador').textContent = formatearNavegador(registro.navegador) ||'Registrado por Sistema';

    // 2. Contenedor principal donde inyectaremos el contenido
    const divDetalle = document.getElementById('textoDetalleAccion');
    let htmlContenido = '';

    // Mostramos la descripción de qué campo se tocó (Si existe)
    if (registro.campo_modificado) {
         htmlContenido += `<div class="mb-3 text-sm"><span class="text-indigo-400 font-bold uppercase tracking-wider text-[10px]">Contexto:</span> ${registro.campo_modificado}</div>`;
    }

    // 3. Función auxiliar mágica: Detecta si es JSON o Texto y le da diseño
    const procesarValor = (valor, titulo, colorClase, borderClase) => {
        if (!valor) return ''; // Si está vacío (ej: no hay valor anterior al crear), no dibuja nada
        
        let contenido = valor;
        try {
            // Intentamos convertirlo a objeto. Si falla, salta al catch.
            const obj = JSON.parse(valor);
            
            // Si funciona, lo convertimos de nuevo a texto pero con formato bonito (indentado a 4 espacios)
            const jsonBonito = JSON.stringify(obj, null, 4);
            contenido = `<pre class="mt-1 ${colorClase} bg-[#0a0914] p-3 rounded-lg border ${borderClase} overflow-x-auto text-[11px] font-mono leading-relaxed">${jsonBonito}</pre>`;
            
        } catch (e) {
            // Si falló el parseo, significa que es texto normal
            contenido = `<div class="mt-1 text-gray-300 bg-[#0a0914] p-3 rounded-lg border border-gray-700/50 text-xs italic">${valor}</div>`;
        }
        
        return `<div class="mt-4">
                    <span class="text-gray-500 font-bold text-[10px] uppercase tracking-widest">${titulo}</span>
                    ${contenido}
                </div>`;
    };

    // 4. Inyectamos los valores (si existen) con colores semánticos
    // Rojo para lo que se borró/cambió, Verde esmeralda para lo nuevo
    htmlContenido += procesarValor(registro.valor_anterior, 'Dato Anterior / Borrado', 'text-red-400', 'border-red-500/20');
    htmlContenido += procesarValor(registro.valor_nuevo, 'Dato Nuevo / Registrado', 'text-emerald-400', 'border-emerald-500/20');

    // 5. Metemos todo el HTML procesado al div
    divDetalle.innerHTML = htmlContenido;

    // 6. Abrimos el Modal con la animación
    const modal = document.getElementById('modalBitacora');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================


        function cerrarModalBitacora() {
            const modal = document.getElementById('modalBitacora');
            modal.firstElementChild.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape" && !modalRep.classList.contains('hidden')) {
                cerrarModalBitacora();
            }
        });

// =====================================================================
// TRADUCTOR DE USER-AGENT (NAVEGADOR Y SO)
// =====================================================================

function formatearNavegador(uaString) {
    if (!uaString || uaString === 'Desconocido') return 'Desconocido';
    
    let navegador = "Navegador Desconocido";
    let os = "SO Desconocido";

    // Detectar Navegador
    if (uaString.includes("Edge") || uaString.includes("Edg")) navegador = "Microsoft Edge";
    else if (uaString.includes("Chrome")) navegador = "Google Chrome";
    else if (uaString.includes("Firefox")) navegador = "Mozilla Firefox";
    else if (uaString.includes("Safari") && !uaString.includes("Chrome")) navegador = "Apple Safari";
    else if (uaString.includes("Opera") || uaString.includes("OPR")) navegador = "Opera";

    // Detectar Sistema Operativo
    if (uaString.includes("Windows")) os = "Windows";
    else if (uaString.includes("Mac OS") || uaString.includes("Macintosh")) os = "MacOS";
    else if (uaString.includes("Android")) os = "Android";
    else if (uaString.includes("iPhone") || uaString.includes("iPad")) os = "iOS";
    else if (uaString.includes("Linux")) os = "Linux";

    return `${os} / ${navegador}`;
}



function validarFechasYFiltrar() {
    const inputDesde = document.getElementById('filtroFechaInicio');
    const inputHasta = document.getElementById('filtroFechaFin');

    // Calculamos las fechas límites permitidas (Hoy y hace 120 años)
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    const strHoy = `${año}-${mes}-${dia}`;
    const strMinimo = `${año - 120}-${mes}-${dia}`;

    // Función auxiliar interna para revisar una fecha a la vez
    const validarLogicaTemporal = (inputElement, etiquetaVisual) => {
        if (inputElement.value !== "") {
            if (inputElement.value > strHoy) {
                UI.error('Fecha Inválida', `La fecha de <b>${etiquetaVisual}</b> no puede ser en el futuro.`);
                inputElement.value = ""; return false;
            }
            if (inputElement.value < strMinimo) {
                UI.error('Fecha Inválida', `La fecha de <b>${etiquetaVisual}</b> es ilógica (demasiado antigua).`);
                inputElement.value = ""; return false;
            }
        }
        return true;
    };

    // 1. Verificamos que ninguna de las dos fechas sea futurista o prehistórica
    if (!validarLogicaTemporal(inputDesde, 'Desde (Inicio)')) return;
    if (!validarLogicaTemporal(inputHasta, 'Hasta (Fin)')) return;

    // 2. Si ambas tienen datos, verificamos que el rango tenga sentido
    if (inputDesde.value !== "" && inputHasta.value !== "") {
        if (inputDesde.value > inputHasta.value) {
            UI.error('Rango de Fechas Inválido', 'La fecha <b>Desde (Inicio)</b> no puede ser mayor que la fecha <b>Hasta (Fin)</b>.');
            inputHasta.value = ""; 
            return; 
        }
    }

    // Si todo está correcto y lógico, procedemos a filtrar la tabla
    aplicarFiltros();
}

// =====================================================================
// FILTROS DE AUDITORÍA EN TIEMPO REAL
// =====================================================================

function aplicarFiltros() {
    // 1. Capturamos lo que el usuario seleccionó
    const valUsuario = document.getElementById('filtroUsuario').value;
    const valModulo = document.getElementById('filtroModulo').value.toLowerCase();
    const valDesde = document.getElementById('filtroFechaInicio').value;
    const valHasta = document.getElementById('filtroFechaFin').value;

    // 2. Filtramos el arreglo global que ya tenemos en memoria
    const datosFiltrados = registrosBitacora.filter(fila => {
        let cumpleUsuario = true;
        let cumpleModulo = true;
        let cumpleDesde = true;
        let cumpleHasta = true;

        // Filtro por ID de Usuario
        if (valUsuario !== "") {
            cumpleUsuario = (fila.id_usuario == valUsuario);
        }

        // Filtro por Módulo (Búsqueda parcial, ignora mayúsculas)
        if (valModulo !== "") {
            cumpleModulo = fila.modulo_afectado.toLowerCase().includes(valModulo);
        }

        // Filtro por Fecha (La fecha de BD viene como "YYYY-MM-DD HH:MM:SS")
        if (valDesde !== "") {
            // Comparamos asumiendo que la fecha de BD es mayor o igual al inicio del día
            cumpleDesde = fila.fecha_operacion >= valDesde + " 00:00:00";
        }
        if (valHasta !== "") {
            // Comparamos asumiendo que la fecha de BD es menor o igual al final del día
            cumpleHasta = fila.fecha_operacion <= valHasta + " 23:59:59";
        }

        // El registro solo se muestra si cumple con todos los filtros activos
        return cumpleUsuario && cumpleModulo && cumpleDesde && cumpleHasta;
    });

    registrosActuales = datosFiltrados;
    // 3. Enviamos los datos filtrados a la función que ya pinta la tabla
    dibujarTabla(datosFiltrados);
}

function extraerUsuariosDinamicamente(datos) {
    const selectUsuario = document.getElementById('filtroUsuario');
    const usuariosUnicos = [];
    const idsAgregados = new Set();

    // 1. Recorremos toda la bitácora
    datos.forEach(fila => {
        // Si el registro tiene un ID de usuario y aún no lo hemos guardado
        if (fila.id_usuario && !idsAgregados.has(fila.id_usuario)) {
            idsAgregados.add(fila.id_usuario);
            usuariosUnicos.push({
                id: fila.id_usuario,
                nombreCompleto: `${fila.nombres} ${fila.apellidos}`.trim()
            });
        }
    });

    // 2. Los ordenamos alfabéticamente de la A a la Z
    usuariosUnicos.sort((a, b) => a.nombreCompleto.localeCompare(b.nombreCompleto));

    // 3. Inyectamos los options en el HTML
    let opcionesHTML = '<option value="">🛡️ Todos los Usuarios</option>';
    usuariosUnicos.forEach(usr => {
        opcionesHTML += `<option value="${usr.id}">${usr.nombreCompleto}</option>`;
    });

    selectUsuario.innerHTML = opcionesHTML;
}

function extraerModulosDinamicamente(datos) {
    const selectModulo = document.getElementById('filtroModulo');
    const modulosUnicos = [];
    const modulosAgregados = new Set();

    // 1. Recorremos toda la bitácora
    datos.forEach(fila => {
        const nombreModulo = fila.modulo_afectado;
        
        // Si el módulo existe y no lo hemos guardado aún
        if (nombreModulo && !modulosAgregados.has(nombreModulo)) {
            modulosAgregados.add(nombreModulo);
            modulosUnicos.push(nombreModulo);
        }
    });

    // 2. Ordenamos los módulos alfabéticamente
    modulosUnicos.sort((a, b) => a.localeCompare(b));

    // 3. Inyectamos las opciones en el HTML
    let opcionesHTML = '<option value="">🧩 Todos los Módulos</option>';
    modulosUnicos.forEach(mod => {
        // Ponemos la primera letra en mayúscula por si en la BD se guardó en minúscula
        const modCapitalizado = mod.charAt(0).toUpperCase() + mod.slice(1);
        opcionesHTML += `<option value="${mod}">${modCapitalizado}</option>`;
    });

    selectModulo.innerHTML = opcionesHTML;
}

// =====================================================================
// EXPORTACIÓN A PDF (RESPETANDO FILTROS)
// =====================================================================

function exportarBitacoraPDF() {
    // 1. Verificamos que haya algo que exportar
    if (registrosActuales.length === 0) {
        UI.error('Tabla vacía', 'No hay registros para exportar con los filtros actuales.');
        return;
    }

    // 2. Inicializamos jsPDF en orientación horizontal ('landscape')
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('landscape');

    // 3. Diseño del Encabezado del PDF
    doc.setFontSize(18);
    doc.setTextColor(40, 40, 40);
    doc.text("Reporte de Auditoría y Bitácora", 14, 20);
    
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text(`Fecha de generación: ${new Date().toLocaleString()}`, 14, 28);
    doc.text(`Total de registros exportados: ${registrosActuales.length}`, 14, 34);

    // 4. Preparamos las filas de la tabla extrayendo solo las columnas que queremos imprimir
   const filasTabla = registrosActuales.map(fila => [
        formatoFechaHora(fila.fecha_operacion),
        `${fila.nombres} ${fila.apellidos}`,
        fila.rol_nombre || 'N/A',
        fila.modulo_afectado,
        fila.tipo_operacion,
        fila.ip_origen || 'No registrada'
    ]);

    // 5. Dibujamos la tabla usando AutoTable
    doc.autoTable({
        startY: 40,
        head: [['Fecha y Hora', 'Usuario', 'Rol', 'Módulo', 'Acción', 'Dirección IP']],
        body: filasTabla,
        theme: 'grid',
        headStyles: { 
            fillColor: [79, 70, 229], // Color Indigo-600 para mantener tu paleta
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        styles: { 
            fontSize: 8,
            cellPadding: 3
        },
        alternateRowStyles: {
            fillColor: [245, 245, 250]
        }
    });

    // 6. Descargamos el archivo con un nombre dinámico
    const nombreArchivo = `Bitacora_SGRD_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(nombreArchivo);
}