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
        const estadoBadge = r.deleted_at ? '<span class="text-red-400 bg-red-500/10 px-2 py-1 rounded text-xs"><i class="fas fa-trash-alt"></i> Anulado</span>' : '<span class="text-green-400 bg-green-500/10 px-2 py-1 rounded text-xs"><i class="fas fa-check"></i> Activo</span>';
        let botones = `<button onclick="verDetalleRPE(${r.id_rpe})" class="bg-[#252345] hover:bg-indigo-600 text-white w-8 h-8 rounded-lg transition-colors" title="Ver detalle"><i class="fas fa-eye text-xs"></i></button>`;
        if (!r.deleted_at) {
            if (PERMISOS_RPE.editar) botones += `<button onclick="abrirModalRPE(${r.id_rpe})" class="bg-[#252345] hover:bg-amber-600 text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
            if (PERMISOS_RPE.eliminar) botones += `<button onclick="softDeleteRPE(${r.id_rpe})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Mover a papelera"><i class="fas fa-trash-alt text-xs"></i></button>`;
        } else {
            if (PERMISOS_RPE.reactivar) botones += `<button onclick="reactivarRPE(${r.id_rpe})" class="bg-[#252345] hover:bg-green-600 text-green-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar"><i class="fas fa-undo-alt text-xs"></i></button>`;
            if (PERMISOS_RPE.eliminardb) botones += `<button onclick="eliminarFisicoRPE(${r.id_rpe})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminar permanentemente"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
        }
        const alertaIcono = r.inconsistencia ? '<i class="fas fa-exclamation-triangle text-amber-400 ml-2" title="Inconsistencia biológica"></i>' : '';

        let colorRpe = r.rpe >= 8 ? 'text-red-400 bg-red-500/10' : (r.rpe >= 5 ? 'text-yellow-400 bg-yellow-500/10' : 'text-emerald-400 bg-emerald-500/10');
        html += `<tr class="hover:bg-white/5 transition-colors">
            <td class="px-6 py-4 font-medium text-white">${formatearFecha(r.fecha)}</td>
            <td class="px-6 py-4 text-indigo-300 font-semibold">${r.nombre_atleta} ${alertaIcono}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold border border-current ${colorRpe}">${r.rpe}/10</span></td>
            <td class="px-6 py-4">${r.srpe || '-'}</td>
            <td class="px-6 py-4">${r.horas_sueno || '-'}</td>
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
            document.getElementById('toggleIconoRPE').className = 'fas fa-trash-alt text-gray-400';
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
    if (tituloTablaRPE) {
        if (modoPapeleraRPE) {
            tituloTablaRPE.innerHTML = '<i class="fas fa-trash-alt text-red-400"></i> Mostrando Registros Anulados (Papelera)';
            tituloTablaRPE.classList.remove('text-emerald-400');
            tituloTablaRPE.classList.add('text-red-400');
        } else {
            tituloTablaRPE.innerHTML = '<i class="fas fa-check-circle text-emerald-400"></i> Mostrando Registros Activos';
            tituloTablaRPE.classList.remove('text-red-400');
            tituloTablaRPE.classList.add('text-emerald-400');
        }
    }
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

function abrirModalRPE(id_rpe = null) {
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
    for (let key in data) {
        const campo = document.getElementById(key === 'id_rpe' ? 'id_rpe' : key);
        if (campo) campo.value = data[key];
    }
    document.getElementById('accionRPE').value = 'actualizar';
    document.getElementById('modalTituloRPE').innerHTML = 'Editar Registro RPE';
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
            background: '#111026',
            color: '#e5e7eb'
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
            background: '#111026',
            color: '#e5e7eb'
        })
    );
    if (!confirm.isConfirmed) return;
    const formData = new FormData();
    formData.append('id_rpe', id_rpe);
    const resultado = await peticionAjaxRPE('reactivarRPE', formData, 'POST');
    if (resultado?.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Restaurado', resultado.message);
        cargarTablaRPE();
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
            background: '#111026',
            color: '#e5e7eb'
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
    contenedor.innerHTML = '<div class="text-center py-20"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>';
    const data = await peticionAjaxRPE('obtenerRPE', null, 'GET', { id: id_rpe });
    if (!data) return cerrarModalVerRPE();
    const anulado = data.deleted_at ? `<div class="bg-red-500/20 border border-red-500/50 p-3 rounded-xl mb-4"><i class="fas fa-trash-alt text-red-400 mr-2"></i><strong class="text-red-400">Anulado:</strong> <span class="text-gray-300">${data.justificacion_softdelete || 'Sin motivo'}</span></div>` : '';
    contenedor.innerHTML = `
        ${anulado}
        <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-3xl">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white">${data.nombre_atleta}</h2>
                <p class="text-indigo-400 text-sm">${formatearFecha(data.fecha)}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">RPE</p><p class="text-2xl font-bold text-amber-400">${data.rpe}/10</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">sRPE</p><p class="text-white font-bold">${data.srpe || '-'}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Sueño (h)</p><p class="text-white">${data.horas_sueno || '-'}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Calidad sueño</p><p class="text-white">${data.calidad_sueno || '-'}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Sensación muscular</p><p class="text-white">${data.sensacion_muscular || '-'}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Estrés</p><p class="text-white">${data.estres_percibido || '-'}</p></div>
            <div class="col-span-2 bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Observaciones</p><p class="text-gray-300 text-sm">${data.observaciones || 'Ninguna'}</p></div>
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
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando alertas...</td></tr>`;

    const inconsistencias = await peticionAjaxRPE('listarInconsistencias');
    if (!inconsistencias || inconsistencias.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-check-circle text-green-500 mr-2"></i> No se detectaron inconsistencias biológicas.</td></tr>`;
        return;
    }

    let html = '';
    inconsistencias.forEach(inc => {
        html += `<tr class="hover:bg-white/5 transition-colors">
            <td class="px-6 py-4 text-white">${formatearFecha(inc.fecha)}</td>
            <td class="px-6 py-4 text-indigo-300 font-semibold">${inc.nombre_atleta}</td>
            <td class="px-6 py-4"><span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-bold">RPE = 1</span></td>
            <td class="px-6 py-4 text-emerald-400">🏆 ${inc.estilo} ${inc.distancia_m}m - ${inc.marca_segundos}s</td>
            <td class="px-6 py-4">
                <button onclick="anularPorInconsistencia(${inc.id_rpe})" class="btn-blink bg-red-500/20 border border-red-500 text-red-400 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-red-500 hover:text-white transition">
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
    
    // Mostramos un SweetAlert configurado para este caso especial
    const confirmacion = await Swal.fire({
        title: '¿Anular Registro Inválido?',
        text: motivoAutomatico,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, Anular y Auditar',
        cancelButtonText: 'Mantener'
    });

    if (confirmacion.isConfirmed) {
        let datos = new FormData();
        datos.append('id_registro', id_registro);
        datos.append('motivo', motivoAutomatico); // Se envía directamente al backend para el Soft Delete
        
        const res = await peticionAjax('?c=carga&accion=anular', datos, 'POST');
        if (res.status === 'success') cargarTablaRPE();
    }
}

// Al generar la tabla, si detectas la bandera 'inconsistencia', inyectas este botón:
// <button onclick="anularPorInconsistencia(${registro.id_registro})" class="bg-red-500 text-white p-2 rounded animate-pulse shadow-[0_0_10px_red]">
//     <i class="fas fa-exclamation-triangle"></i> Corregir Inconsistencia
// </button>

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

    // Validación en tiempo real para el formulario (opcional, si usas Validador)
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formRPE);
    }

    // Actualizar título de tabla al inicio
    actualizarUIRPE();
});