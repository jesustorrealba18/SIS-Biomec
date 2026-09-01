// =====================================================================
// CONTROLADOR FRONTEND: CARGA INTERNA Y BIENESTAR (RPE)
// Refactorizado con la misma arquitectura que lesion.js
// =====================================================================

const API_RPE = 'index.php?p=cargaBienestar';
let modoPapeleraRPE = false;
let dataTableRPE = null;

// Elementos DOM principales
const modalRPE = document.getElementById('modalRPE');
const formRPE = document.getElementById('formRPE');
const tablaRPE = document.getElementById('tablaRPE');
const filtroAtleta = document.getElementById('filtroAtletaRPE');
const filtroFechaInicio = document.getElementById('filtroFechaInicio');
const filtroFechaFin = document.getElementById('filtroFechaFin');
const toggleBtnRPE = document.getElementById('toggleEstadoRPEBtn');
const tituloTablaRPE = document.getElementById('tituloTablaRPE');
const kpiRpePromedio = document.getElementById('kpi_rpe_promedio');
const kpiSuenoPromedio = document.getElementById('kpi_sueno_promedio');
const kpiSrpeSemanal = document.getElementById('kpi_srpe_semanal');

// ================== PETICIONES AJAX ==================
async function peticionAjaxRPE(accion, datos = null, metodo = 'GET', params = {}) {
    let url = `${API_RPE}&accion=${accion}`;
    if (metodo === 'GET' && Object.keys(params).length) {
        url += '&' + new URLSearchParams(params).toString();
    }
    const opciones = { method: metodo };
    if (datos) {
        if (datos instanceof FormData) {
            opciones.body = datos;
        } else {
            opciones.body = JSON.stringify(datos);
            opciones.headers = { 'Content-Type': 'application/json' };
        }
    }
    try {
        const respuesta = await fetch(url, opciones);
        if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
        return await respuesta.json();
    } catch (error) {
        console.error(error);
        if (typeof UI !== 'undefined') UI.error('Error de comunicación', 'No se pudo conectar con el servidor.');
        return null;
    }
}

// ================== CARGAR ATLETAS EN SELECTS ==================
async function cargarAtletasRPE() {
    const atletas = await peticionAjaxRPE('listarAtletasSelect');
    if (!Array.isArray(atletas)) return;
    let opcionesForm = '<option value="">Seleccione atleta...</option>';
    let opcionesFiltro = '<option value="">👤 Todos los Atletas</option>';
    atletas.forEach(a => {
        const txt = `${a.nombres} ${a.apellidos} - ${a.cedula}`;
        opcionesForm += `<option value="${a.id_atleta}">${txt}</option>`;
        opcionesFiltro += `<option value="${a.id_atleta}">${txt}</option>`;
    });
    document.getElementById('id_atleta_rpe').innerHTML = opcionesForm;
    if (filtroAtleta) filtroAtleta.innerHTML = opcionesFiltro;
}

// ================== TABLA PRINCIPAL (DataTables) ==================
async function cargarTablaRPE() {
    const params = {
        modo: modoPapeleraRPE ? 'papelera' : 'activos',
        fechaInicio: filtroFechaInicio?.value || '',
        fechaFin: filtroFechaFin?.value || '',
        id_atleta: filtroAtleta?.value || '0'
    };
    const registros = await peticionAjaxRPE('listarRPE', null, 'GET', params);
    if (!Array.isArray(registros)) return;

    let html = '';
    registros.forEach(r => {
        const estadoBadge = r.deleted_at 
            ? '<span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded text-xs border border-red-200 dark:border-red-500/30"><i class="fas fa-trash-alt"></i> Anulado</span>' 
            : '<span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded text-xs border border-emerald-200 dark:border-emerald-500/30"><i class="fas fa-check"></i> Activo</span>';
        
        let botones = `<button onclick="verDetalleRPE(${r.id_rpe})" class="bg-gray-200 dark:bg-[#252345] hover:bg-indigo-600 text-gray-700 dark:text-white w-8 h-8 rounded-lg transition-colors" title="Ver detalle"><i class="fas fa-eye text-xs"></i></button>`;
        if (!r.deleted_at) {
            if (PERMISOS_RPE.editar) botones += `<button onclick="abrirModalRPE(${r.id_rpe})" class="bg-gray-200 dark:bg-[#252345] hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
            if (PERMISOS_RPE.eliminar) botones += `<button onclick="softDeleteRPE(${r.id_rpe})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Mover a papelera"><i class="fas fa-trash-alt text-xs"></i></button>`;
        } else {
            if (PERMISOS_RPE.reactivar) botones += `<button onclick="reactivarRPE(${r.id_rpe})" class="bg-gray-200 dark:bg-[#252345] hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar"><i class="fas fa-undo-alt text-xs"></i></button>`;
            if (PERMISOS_RPE.eliminardb) botones += `<button onclick="eliminarFisicoRPE(${r.id_rpe})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminar permanentemente"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
        }
        const alertaIcono = r.inconsistencia ? '<i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400 ml-2" title="Inconsistencia biológica"></i>' : '';

        let colorRpe = r.rpe >= 8 ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : (r.rpe >= 5 ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10');
        html += `<tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${formatearFecha(r.fecha)}</td>
            <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${r.nombre_atleta} ${alertaIcono}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold border border-current ${colorRpe}">${r.rpe}/10</span></td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.srpe || '-'}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.horas_sueno || '-'}</td>
            <td class="px-6 py-4 text-center">${estadoBadge}</td>
            <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
        </tr>`;
    });

    const tbody = document.getElementById('tablaCuerpoRPE');
    if (dataTableRPE) {
        dataTableRPE.destroy();
        tbody.innerHTML = html;
        dataTableRPE = $('#tablaRPE').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 2 },
                { responsivePriority: 3, targets: 6 }
            ]
        });
    } else {
        tbody.innerHTML = html;
        dataTableRPE = $('#tablaRPE').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 2 },
                { responsivePriority: 3, targets: 6 }
            ]
        });
    }
    actualizarKPIsRPE(registros.filter(r => !r.deleted_at));
}

// ================== KPIs ==================
function actualizarKPIsRPE(activos) {
    if (!activos.length) {
        if (kpiRpePromedio) kpiRpePromedio.innerText = '--';
        if (kpiSuenoPromedio) kpiSuenoPromedio.innerText = '--';
        if (kpiSrpeSemanal) kpiSrpeSemanal.innerText = '--';
        return;
    }
    let sumaRpe = 0, sumaSueno = 0, sumaSrpe = 0;
    let countRpe = 0, countSueno = 0, countSrpe = 0;
    activos.forEach(r => {
        if (r.rpe) { sumaRpe += r.rpe; countRpe++; }
        if (r.horas_sueno) { sumaSueno += parseFloat(r.horas_sueno); countSueno++; }
        if (r.srpe) { sumaSrpe += r.srpe; countSrpe++; }
    });
    if (kpiRpePromedio) kpiRpePromedio.innerText = countRpe ? (sumaRpe / countRpe).toFixed(1) : '--';
    if (kpiSuenoPromedio) kpiSuenoPromedio.innerText = countSueno ? (sumaSueno / countSueno).toFixed(1) : '--';
    if (kpiSrpeSemanal) kpiSrpeSemanal.innerText = countSrpe ? Math.round(sumaSrpe / countSrpe) : '--';
}

// ================== TOGGLE PAPELERA ==================
function actualizarUIRPE() {
    const isActive = !modoPapeleraRPE;
    if (toggleBtnRPE) {
        if (isActive) {
            toggleBtnRPE.classList.remove('active');
            document.getElementById('toggleTextoRPE').innerText = 'Activos';
            document.getElementById('toggleIconoRPE').className = 'fas fa-trash-alt text-gray-500 dark:text-gray-400';
            document.getElementById('estadoBadgeRPE').innerHTML = 'A';
            document.getElementById('estadoBadgeRPE').classList.remove('bg-red-500');
            document.getElementById('estadoBadgeRPE').classList.add('bg-indigo-500');
        } else {
            toggleBtnRPE.classList.add('active');
            document.getElementById('toggleTextoRPE').innerText = 'Inactivos';
            document.getElementById('toggleIconoRPE').className = 'fas fa-trash-restore text-red-400';
            document.getElementById('estadoBadgeRPE').innerHTML = 'I';
            document.getElementById('estadoBadgeRPE').classList.remove('bg-indigo-500');
            document.getElementById('estadoBadgeRPE').classList.add('bg-red-500');
        }
    }
    // Actualizar título de la tabla con colores adaptados
    const container = document.getElementById('tablaRPEContainer');
    if (tituloTablaRPE) {
        if (modoPapeleraRPE) {
            tituloTablaRPE.innerHTML = '<i class="fas fa-trash-alt"></i> Mostrando Registros Anulados (Papelera)';
            tituloTablaRPE.className = 'text-lg font-bold text-red-600 dark:text-red-400 mb-3 ml-2 flex items-center gap-2';
            if (container) {
                container.classList.remove('border-t-indigo-500');
                container.classList.add('border-t-red-500');
            }
        } else {
            tituloTablaRPE.innerHTML = '<i class="fas fa-check-circle"></i> Mostrando Registros Activos';
            tituloTablaRPE.className = 'text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2';
            if (container) {
                container.classList.remove('border-t-red-500');
                container.classList.add('border-t-indigo-500');
            }
        }
    }
    // Disparar evento para que el HTML lo capture si es necesario
    document.dispatchEvent(new CustomEvent('modoPapeleraRPEChanged'));
}

if (toggleBtnRPE) {
    toggleBtnRPE.addEventListener('click', () => {
        modoPapeleraRPE = !modoPapeleraRPE;
        actualizarUIRPE();
        cargarTablaRPE();
    });
}

// ================== MODAL: ABRIR / CERRAR / GUARDAR ==================
function cerrarModalRPE() {
    modalRPE.classList.add('hidden');
    formRPE.reset();
    document.getElementById('id_rpe').value = '';
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formRPE);
}

/* function abrirModalRPE(id_rpe = null) {
    formRPE.reset();
    document.getElementById('id_rpe').value = '';
    document.getElementById('accionRPE').value = 'registrar';
    document.getElementById('modalTituloRPE').innerHTML = 'Registrar Carga Interna (RPE)';
    document.getElementById('btnGuardarRPE').innerHTML = 'Guardar Registro <i class="fas fa-save ml-2"></i>';
    document.getElementById('btnGuardarRPE').classList.replace('bg-emerald-600', 'bg-indigo-600');
    document.getElementById('btnGuardarRPE').classList.replace('hover:bg-emerald-500', 'hover:bg-indigo-500');
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formRPE);
    if (id_rpe) cargarDatosParaEdicionRPE(id_rpe);
    modalRPE.classList.remove('hidden');
} */

function abrirModalRPE(id_rpe = null) {
    formRPE.reset();
    document.getElementById('id_rpe').value = '';
    document.getElementById('accionRPE').value = 'registrar';
    document.getElementById('modalTituloRPE').innerHTML = 'Registrar Carga Interna (RPE)';
    document.getElementById('btnGuardarRPE').innerHTML = 'Guardar Registro <i class="fas fa-save ml-2"></i>';
    document.getElementById('btnGuardarRPE').classList.replace('bg-emerald-600', 'bg-indigo-600');
    document.getElementById('btnGuardarRPE').classList.replace('hover:bg-emerald-500', 'hover:bg-indigo-500');
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formRPE);
    
    // Si hay ID, cargar datos para edición
    if (id_rpe) {
        cargarDatosParaEdicionRPE(id_rpe);
    }
    
    modalRPE.classList.remove('hidden');
}

async function cargarDatosParaEdicionRPE(id_rpe) {
    const btn = document.getElementById('btnGuardarRPE');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    btn.disabled = true;
    
    const data = await peticionAjaxRPE('obtenerRPE', null, 'GET', { id: id_rpe });
    
    if (!data || data.status === 'error') {
        if (typeof UI !== 'undefined') UI.error('Error', data?.message || 'No se pudo cargar el registro.');
        cerrarModalRPE();
        return;
    }
    
    // Asignar valores a los campos correctos del formulario
    document.getElementById('id_rpe').value = data.id_rpe;
    document.getElementById('id_atleta_rpe').value = data.id_atleta;
    document.getElementById('fecha_rpe').value = data.fecha;
    document.getElementById('rpe_valor').value = data.rpe;
    document.getElementById('duracion_minutos').value = data.duracion_minutos || '';
    document.getElementById('metros_nadados').value = data.metros_nadados || '';
    document.getElementById('horas_sueno').value = data.horas_sueno || '';
    document.getElementById('calidad_sueno').value = data.calidad_sueno || '';
    document.getElementById('sensacion_muscular').value = data.sensacion_muscular || '';
    document.getElementById('estres_percibido').value = data.estres_percibido || '';
    document.getElementById('observaciones_rpe').value = data.observaciones || '';
    
    // Cambiar el modo del formulario a "actualizar"
    document.getElementById('accionRPE').value = 'actualizar';
    document.getElementById('modalTituloRPE').innerHTML = 'Editar Registro RPE';
    
    // Actualizar el botón
    btn.innerHTML = 'Actualizar Registro <i class="fas fa-sync-alt ml-2"></i>';
    btn.classList.replace('bg-indigo-600', 'bg-emerald-600');
    btn.classList.replace('hover:bg-indigo-500', 'hover:bg-emerald-500');
    btn.disabled = false;
}

formRPE.addEventListener('submit', async (e) => {
    e.preventDefault();
    // Validación básica (puedes ampliarla con Validador si lo deseas)
    const rpe = document.getElementById('rpe_valor').value;
    if (rpe < 1 || rpe > 10) {
        if (typeof UI !== 'undefined') UI.error('Validación', 'El RPE debe estar entre 1 y 10.');
        return;
    }
    const formData = new FormData(formRPE);
    const accion = document.getElementById('accionRPE').value;
    const btn = document.getElementById('btnGuardarRPE');
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    btn.disabled = true;
    const resultado = await peticionAjaxRPE(accion, formData, 'POST');
    btn.innerHTML = original;
    btn.disabled = false;
    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Éxito', resultado.message);
        cerrarModalRPE();
        cargarTablaRPE();
        cargarTablaInconsistenciasRPE();
        cargarRecomendacionesEntrenador();
    } else {
        if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo guardar.');
    }
});

// ================== SOFT DELETE, REACTIVAR, ELIMINAR FÍSICO ==================
async function softDeleteRPE(id_rpe) {
    const justificacion = await (typeof UI !== 'undefined' && UI.pedirJustificacion ? 
        UI.pedirJustificacion('Mover a Papelera', 'Justifique por qué se anula este registro (ej. Inconsistencia biológica):', 'Motivo obligatorio (mínimo 10 caracteres)') :
        Swal.fire({
            title: 'Mover a Papelera',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputPlaceholder: 'Ej: Inconsistencia biológica detectada...',
            inputAttributes: { required: true, minlength: 5 },
            showCancelButton: true,
            confirmButtonText: 'Mover a papelera',
            background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
        })
    );
    if (!justificacion.isConfirmed) return;
    const formData = new FormData();
    formData.append('id_rpe', id_rpe);
    formData.append('motivo', justificacion.value);
    const resultado = await peticionAjaxRPE('anularRPE', formData, 'POST');
    if (resultado?.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Anulado', resultado.message);
        cargarTablaRPE();
        cargarTablaInconsistenciasRPE();
        cargarRecomendacionesEntrenador();
    } else {
        if (typeof UI !== 'undefined') UI.error('Error', resultado?.message);
    }
}

async function reactivarRPE(id_rpe) {
    const confirm = await (typeof UI !== 'undefined' && UI.confirmar ? 
        UI.confirmar('Restaurar Registro', '¿Desea reactivar este registro?') :
        Swal.fire({
            title: 'Restaurar Registro',
            text: '¿Desea reactivar este registro?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
        })
    );
    if (!confirm.isConfirmed) return;
    const formData = new FormData();
    formData.append('id_rpe', id_rpe);
    const resultado = await peticionAjaxRPE('reactivarRPE', formData, 'POST');
    if (resultado?.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Restaurado', resultado.message);
        cargarTablaRPE();
        cargarTablaInconsistenciasRPE(); 
        cargarRecomendacionesEntrenador();
    } else {
        if (typeof UI !== 'undefined') UI.error('Error', resultado?.message);
    }
}

async function eliminarFisicoRPE(id_rpe) {
    const confirm = await (typeof UI !== 'undefined' && UI.confirmar ? 
        UI.confirmar('Eliminación permanente', 'Esta acción es irreversible. ¿Está seguro?', { icon: 'error' }) :
        Swal.fire({
            title: 'Eliminación permanente',
            text: 'Esta acción es irreversible. ¿Está seguro?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Sí, purgar',
            background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
        })
    );
    if (!confirm.isConfirmed) return;
    const formData = new FormData();
    formData.append('id_rpe', id_rpe);
    const resultado = await peticionAjaxRPE('eliminarFisicoRPE', formData, 'POST');
    if (resultado?.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Purgado', resultado.message);
        cargarTablaRPE();
    } else {
        if (typeof UI !== 'undefined') UI.error('Error', resultado?.message);
    }
}

// ================== VER DETALLE ==================
async function verDetalleRPE(id_rpe) {
    const modal = document.getElementById('modalVerRPE');
    const contenedor = document.getElementById('contenidoDetalleRPE');
    modal.classList.remove('hidden');
    contenedor.innerHTML = '<div class="text-center py-20 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>';
    const data = await peticionAjaxRPE('obtenerRPE', null, 'GET', { id: id_rpe });
    if (!data) return cerrarModalVerRPE();
    const anulado = data.deleted_at ? `<div class="bg-red-50 dark:bg-red-500/20 border border-red-200 dark:border-red-500/50 p-3 rounded-xl mb-4"><i class="fas fa-trash-alt text-red-600 dark:text-red-400 mr-2"></i><strong class="text-red-600 dark:text-red-400">Anulado:</strong> <span class="text-gray-700 dark:text-gray-300">${data.justificacion_softdelete || 'Sin motivo'}</span></div>` : '';
    contenedor.innerHTML = `
        ${anulado}
        <div class="flex items-center gap-4 mb-8 border-b border-gray-200 dark:border-white/10 pb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-3xl">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white">${data.nombre_atleta}</h2>
                <p class="text-indigo-600 dark:text-indigo-400 text-sm">${formatearFecha(data.fecha)}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">RPE</p><p class="text-2xl font-bold text-amber-600 dark:text-amber-400">${data.rpe}/10</p></div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">sRPE</p><p class="text-gray-900 dark:text-white font-bold">${data.srpe || '-'}</p></div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Sueño (h)</p><p class="text-gray-900 dark:text-white">${data.horas_sueno || '-'}</p></div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Calidad sueño</p><p class="text-gray-900 dark:text-white">${data.calidad_sueno || '-'}</p></div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Sensación muscular</p><p class="text-gray-900 dark:text-white">${data.sensacion_muscular || '-'}</p></div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Estrés</p><p class="text-gray-900 dark:text-white">${data.estres_percibido || '-'}</p></div>
            <div class="col-span-2 bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]"><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Observaciones</p><p class="text-gray-700 dark:text-gray-300 text-sm">${data.observaciones || 'Ninguna'}</p></div>
        </div>
    `;
}

function cerrarModalVerRPE() {
    const modal = document.getElementById('modalVerRPE');
    if (modal) modal.classList.add('hidden');
}

// ================== INCONSISTENCIAS BIOLÓGICAS ==================
async function cargarTablaInconsistenciasRPE() {
    const tbody = document.getElementById('listaInconsistenciasRPE');
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando alertas...</td></tr>`;

    const inconsistencias = await peticionAjaxRPE('listarInconsistencias');
    if (!inconsistencias || inconsistencias.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 mr-2"></i> No se detectaron inconsistencias biológicas.</td></tr>`;
        return;
    }

    let html = '';
    inconsistencias.forEach(inc => {
        html += `<tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
            <td class="px-6 py-4 text-gray-900 dark:text-white">${formatearFecha(inc.fecha)}</td>
            <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${inc.nombre_atleta}</td>
            <td class="px-6 py-4"><span class="px-2 py-1 bg-red-50 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-full text-xs font-bold border border-red-200 dark:border-red-500/30">RPE = 1</span></td>
            <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400">🏆 ${inc.estilo} ${inc.distancia_m}m - ${inc.marca_segundos}s</td>
            <td class="px-6 py-4">
                <button onclick="anularPorInconsistencia(${inc.id_rpe})" class="btn-blink bg-red-50 dark:bg-red-500/20 border border-red-200 dark:border-red-500 text-red-600 dark:text-red-400 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">
                    <i class="fas fa-exclamation-triangle"></i> Anular y Auditar
                </button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

// Función para anular con motivo automático pre-llenado
async function anularPorInconsistencia(id_registro) {
    const motivoAutomatico = "Inconsistencia biológica detectada: RPE (Reposo) incongruente con marcas de rendimiento (Récord) registradas este día.";
    
    const confirmacion = await Swal.fire({
        title: '¿Anular Registro Inválido?',
        text: motivoAutomatico,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, Anular y Auditar',
        cancelButtonText: 'Mantener',
        background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
    });

    if (confirmacion.isConfirmed) {
        let datos = new FormData();
        datos.append('id_rpe', id_registro);
        datos.append('motivo', motivoAutomatico);
        
        const res = await peticionAjaxRPE('anularRPE', datos, 'POST');
        if (res?.status === 'success') {
            if (typeof UI !== 'undefined') UI.exito('Anulado', res.message);
            cargarTablaRPE();
            cargarTablaInconsistenciasRPE();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', res?.message || 'No se pudo anular.');
        }
    }
}

// ================== UTILIDAD ==================
function formatearFecha(fechaISO) {
    if (!fechaISO) return '—';
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString('es-ES');
}

// ================== INICIALIZACIÓN ==================
document.addEventListener('DOMContentLoaded', async () => {
    await cargarAtletasRPE();
    cargarTablaRPE();
    cargarTablaInconsistenciasRPE();

    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formRPE);
    }

    actualizarUIRPE();
});

// ================== RECOMENDACIONES DE CARGA (DATATABLE) ==================
let dataTableRecomendaciones = null;

async function cargarRecomendacionesEntrenador() {
    const panel = document.getElementById('recomendacionesPanel');
    const tbody = document.getElementById('listaRecomendaciones');
    const contador = document.getElementById('contadorRecomendaciones');

    if (!panel || !tbody) return;

    try {
        const response = await fetch('index.php?p=cargaBienestar&accion=listarRecomendacionesEntrenador');
        const data = await response.json();

        if (data.status === 'error' || data.length === 0) {
            panel.classList.add('hidden');
            return;
        }

        // Mostrar el panel
        panel.classList.remove('hidden');
        contador.textContent = data.length;

        // Construir HTML para el tbody
        let html = '';
        data.forEach(rec => {
            const tipoColor = rec.tipo === 'SOBRECARGA' 
                ? 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/30' 
                : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/30';
            
            html += `
                <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${rec.nombre_atleta}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">${new Date(rec.fecha).toLocaleString()}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold border ${tipoColor}">${rec.tipo}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 max-w-xs truncate">${rec.mensaje}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="marcarRecomendacionLeida(${rec.id_recomendacion})" 
                                class="bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors border border-indigo-200 dark:border-indigo-500/30">
                            <i class="fas fa-check"></i> Marcar Leída
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        // Destruir DataTable existente si existe
        if (dataTableRecomendaciones) {
            dataTableRecomendaciones.destroy();
            dataTableRecomendaciones = null;
        }

        // Inicializar DataTable
        dataTableRecomendaciones = $('#tablaRecomendaciones').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 1 },
                { responsivePriority: 3, targets: 4 }
            ],
            order: [[1, 'desc']], // Ordenar por fecha descendente
            pageLength: 5,
            lengthMenu: [[5, 10, 25, -1], [5, 10, 25, 'Todos']]
        });

    } catch (error) {
        console.error(error);
        panel.classList.add('hidden');
    }
}

async function marcarRecomendacionLeida(id_recomendacion) {
    const formData = new FormData();
    formData.append('id_recomendacion', id_recomendacion);
    
    try {
        const response = await fetch('index.php?p=cargaBienestar&accion=marcarRecomendacionLeida', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            // Recargar la tabla de recomendaciones
            await cargarRecomendacionesEntrenador();
            // Opcional: recargar la tabla de RPE
            cargarTablaRPE();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo marcar como leída.');
        }
    } catch (error) {
        console.error(error);
        if (typeof UI !== 'undefined') UI.error('Error', 'Error de comunicación.');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await cargarAtletasRPE();
    cargarTablaRPE();
    cargarTablaInconsistenciasRPE();
    cargarRecomendacionesEntrenador(); // <-- nuevo

    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formRPE);
    }

    actualizarUIRPE();
});

