// =====================================================================
// CONFIGURACIÓN PRINCIPAL Y MAPEO DEL DOM
// =====================================================================
const modalFormulario = document.getElementById('modalFormulario');
const modalVer = document.getElementById('modalVer');
const formulario = document.getElementById('formularioLesion');
const btnGuardar = document.getElementById('btnGuardar');
const tablaCuerpo = document.getElementById('tablaCuerpo');
const filtroAtleta = document.getElementById('filtroAtleta');

// Ruta al controlador pivote (ajusta 'lesion' según el ruteo de tu index)
const API_URL = 'index.php?p=lesion'; 

// Variable global para almacenar el historial de la sesión actual (útil para el Modal de Vista Detallada)
let historialActual = [];

/**
 * Función centralizada para peticiones AJAX al servidor (Principio DRY)
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
        UI.error('Error Crítico', 'No se pudo procesar la solicitud con el servidor.');
        return null;
    }
}

// =====================================================================
// MANEJO DE INTERFAZ Y MODALES
// =====================================================================

function abrirModal() {
    formulario.reset();
    document.getElementById('id_lesion').value = '';
    document.getElementById('accion').value = 'registrar';
    document.getElementById('tituloModal').innerText = 'Registrar Nuevo Evento Médico';
    Validador.limpiarEstilos(formulario); // Limpia validaciones previas si las hay
    
    modalFormulario.classList.remove('hidden');
}

function cerrarModal() {
    modalFormulario.classList.add('hidden');
    formulario.reset();
    Validador.limpiarEstilos(formulario);
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
}

// =====================================================================
// CARGA DE DATOS (LECTURA)
// =====================================================================

/**
 * Carga los atletas en los Selects (Filtro principal y Modal)
 */
async function cargarAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    if (!atletas || !Array.isArray(atletas)) return;

    let opciones = '<option value="">Seleccione un atleta...</option>';
    atletas.forEach(atleta => {
        opciones += `<option value="${atleta.id_atleta}">${atleta.cedula} - ${atleta.nombres} ${atleta.apellidos}</option>`;
    });

    document.getElementById('id_atleta').innerHTML = opciones;
    
    let opcionesFiltro = '<option value="">Seleccione un atleta para ver su historial...</option>';
    atletas.forEach(atleta => {
        opcionesFiltro += `<option value="${atleta.id_atleta}">${atleta.nombres} ${atleta.apellidos}</option>`;
    });
    filtroAtleta.innerHTML = opcionesFiltro;
}

/**
 * Carga la tabla de lesiones y actualiza las tarjetas de KPIs
 */
async function cargarTabla() {
    const idAtletaSeleccionado = filtroAtleta.value;

    if (!idAtletaSeleccionado) {
        tablaCuerpo.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-search text-3xl mb-3 block"></i>
                    Por favor, seleccione un atleta en el filtro superior para cargar su historial clínico.
                </td>
            </tr>`;
        actualizarKPIs([]); // Resetea KPIs a 0
        return;
    }

    tablaCuerpo.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando registros...</td></tr>';
    
    const registros = await peticionAjax(`listarHistorial&id_atleta=${idAtletaSeleccionado}`);
    historialActual = registros || [];

    if (!registros || registros.length === 0) {
        tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No hay registros médicos activos para este atleta.</td></tr>`;
        actualizarKPIs([]);
        return;
    }

    let filas = '';
    registros.forEach(reg => {
        // Estilos dinámicos para la gravedad
        let colorGravedad = 'text-gray-400 bg-gray-500/10';
        if(reg.gravedad === 'Leve') colorGravedad = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
        if(reg.gravedad === 'Moderada') colorGravedad = 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20';
        if(reg.gravedad === 'Grave') colorGravedad = 'text-red-400 bg-red-500/10 border-red-500/20';

        filas += `
            <tr class="hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-medium text-white">${reg.fecha_lesion}</td>
                <td class="px-6 py-4 text-indigo-300">ID: ${idAtletaSeleccionado}</td>
                <td class="px-6 py-4">
                    <span class="block text-white font-semibold">${reg.tipo_lesion}</span>
                    <span class="text-xs text-gray-500">${reg.zona_corporal}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold border ${colorGravedad}">${reg.gravedad}</span>
                </td>
                <td class="px-6 py-4 text-gray-400">${reg.dias_reposo_estimados || 'N/A'} días</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-emerald-400 text-xs font-bold uppercase"><i class="fas fa-circle text-[8px] mr-1"></i> ${reg.estado}</span>
                </td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <button onclick="verDetalle(${reg.id_lesion})" class="bg-[#252345] hover:bg-indigo-600 text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Ver Informe Completo">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar ? `
                    <button onclick="anularRegistro(${reg.id_lesion})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Anular Registro (IA)">
                        <i class="fas fa-ban text-xs"></i>
                    </button>
                    ` : ''}
                </td>
            </tr>
        `;
    });

    tablaCuerpo.innerHTML = filas;
    actualizarKPIs(registros);
}

/**
 * Calcula dinámicamente las métricas de las tarjetas superiores
 */
function actualizarKPIs(datos) {
    const activas = datos.length;
    const graves = datos.filter(d => d.gravedad === 'Grave').length;
    
    let totalReposo = 0;
    let registrosConReposo = 0;
    datos.forEach(d => {
        if(d.dias_reposo_estimados && !isNaN(d.dias_reposo_estimados)) {
            totalReposo += parseInt(d.dias_reposo_estimados);
            registrosConReposo++;
        }
    });
    const promReposo = registrosConReposo > 0 ? (totalReposo / registrosConReposo).toFixed(1) : 0;

    document.getElementById('kpi_activas').innerText = activas;
    document.getElementById('kpi_graves').innerText = graves;
    document.getElementById('kpi_reposo').innerText = promReposo;
}

// =====================================================================
// TRANSACCIONES (ESCRITURA Y ELIMINACIÓN)
// =====================================================================

/**
 * Intercepción y envío del formulario principal
 */
formulario.addEventListener('submit', async (e) => {
    e.preventDefault();

    // 1. Validación Frontend usando tu validador.js
    const erroresValidacion = Validador.validarFormulario(formulario);
    if (erroresValidacion && erroresValidacion.length > 0) {
        UI.advertencia('Campos Incompletos', erroresValidacion.join('<br>'));
        return;
    }

    // Bloquear botón para evitar doble submit
    const btnOriginalText = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btnGuardar.disabled = true;

    // 2. Preparar payload
    let datosEnvio = new FormData(formulario);

    // 3. Petición POST
    const resultado = await peticionAjax('registrar', datosEnvio);

    // Restaurar botón
    btnGuardar.innerHTML = btnOriginalText;
    btnGuardar.disabled = false;

    // 4. Manejo de respuesta
    if (resultado && resultado.status === 'success') {
        UI.exito('Registro Exitoso', resultado.message);
        cerrarModal();
        
        // Si el filtro actual es el del atleta que acabamos de registrar, recargamos la tabla
        if (filtroAtleta.value === document.getElementById('id_atleta').value) {
            cargarTabla();
        } else {
            // Sino, cambiamos el filtro para mostrar al atleta recién procesado
            filtroAtleta.value = document.getElementById('id_atleta').value;
            cargarTabla();
        }
    } else {
        UI.error('Error de Guardado', resultado?.message || 'Ocurrió un error inesperado al guardar el registro.');
    }
});

/**
 * Elimina (Anulación Lógica) utilizando UI.pedirJustificacion de alertas.js
 */
async function anularRegistro(id_lesion) {
    // Requisito 11.4 de SRS y Protección IA: Pedir justificación obligatoria
    const justificacion = await UI.pedirJustificacion(
        'Anular Registro Clínico', 
        'Esta acción ocultará el registro para evitar contaminar los cálculos del Componente Inteligente. Escriba el motivo de la anulación:',
        'Ej. Error de transcripción, atleta equivocado...'
    );

    if (justificacion.isConfirmed) {
        let datosBorrar = new FormData();
        datosBorrar.append('accion', 'eliminar');
        datosBorrar.append('id_lesion', id_lesion);
        datosBorrar.append('motivo', justificacion.value); // El valor del textarea del modal de SweetAlert

        const resultado = await peticionAjax('eliminar', datosBorrar);

        if (resultado && resultado.status === 'success') {
            UI.exito('Registro Anulado', resultado.message);
            cargarTabla(); // Recargamos para que desaparezca
        } else {
            UI.error('Error', resultado?.message || 'No se pudo anular el registro clínico.');
        }
    }
}

// =====================================================================
// FUNCIONES AUXILIARES (VISTA DETALLADA)
// =====================================================================

function verDetalle(id_lesion) {
    const registro = historialActual.find(r => r.id_lesion == id_lesion);
    if(!registro) return;

    // Poblar modal de lectura
    document.getElementById('ver_atleta').innerText = filtroAtleta.options[filtroAtleta.selectedIndex].text;
    document.getElementById('ver_fecha').innerText = registro.fecha_lesion;
    document.getElementById('ver_zona').innerText = `${registro.tipo_lesion} / ${registro.zona_corporal}`;
    document.getElementById('ver_gravedad').innerText = registro.gravedad;
    document.getElementById('ver_diagnostico').innerText = registro.diagnostico || 'Sin descripción detallada.';
    document.getElementById('ver_tratamiento').innerText = registro.tratamiento || 'Ningún tratamiento registrado.';

    modalVer.classList.remove('hidden');
}

// =====================================================================
// INICIALIZADOR AL CARGAR EL DOM
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Vincular eventos de validación en tiempo real (de validador.js)
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formulario);
    }
    
    // 2. Cargar listas desplegables iniciales
    cargarAtletas();

    // 3. Escuchar cambios en el filtro principal para recargar tabla
    filtroAtleta.addEventListener('change', cargarTabla);
});