// =====================================================================
// CONTROLADOR FRONTEND: NORMALIZACIÓN DE TIEMPOS (RF-08)
// =====================================================================

const API_URL = 'index.php?p=normalizacion';
let modoPapeleraNormalizacion = false;
let dataTableNormalizacion = null;

// Elementos DOM
const modalFormulario = document.getElementById('modalFormulario');
const formNormalizacion = document.getElementById('formularioNormalizacion');
const btnGuardar = document.getElementById('btnGuardarNormalizacion');
const toggleBtn = document.getElementById('toggleEstadoNormalizacionBtn');
const filtroAtleta = document.getElementById('filtroAtletaNormalizacion');
const filtroEstilo = document.getElementById('filtroEstiloNormalizacion');
const filtroDistancia = document.getElementById('filtroDistanciaNormalizacion');
const filtroPiscina = document.getElementById('filtroPiscinaNormalizacion');

// ================== PETICIONES AJAX ==================
async function peticionAjaxNormalizacion(accion, datos = null, metodo = 'GET', params = {}) {
    let url = `${API_URL}&accion=${accion}`;
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
async function cargarAtletasNormalizacion() {
    const atletas = await peticionAjaxNormalizacion('listarAtletasSelect');
    if (!Array.isArray(atletas)) return;
    let opcionesForm = '<option value="">Seleccione un atleta...</option>';
    let opcionesFiltro = '<option value="">👤 Todos los Atletas</option>';
    atletas.forEach(a => {
        const txt = `${a.cedula} - ${a.nombres} ${a.apellidos} - ${a.categoria_nombre}`;
        opcionesForm += `<option value="${a.id_atleta}">${txt}</option>`;
        opcionesFiltro += `<option value="${a.id_atleta}">${txt}</option>`;
    });
    document.getElementById('id_atleta').innerHTML = opcionesForm;
    if (filtroAtleta) filtroAtleta.innerHTML = opcionesFiltro;
}

// ================== KPIs ==================
async function cargarKPIsNormalizacion(registros) {
    if (!registros || registros.length === 0) {
        document.getElementById('kpi_total_registros').innerText = '0';
        document.getElementById('kpi_promedio_convertido').innerText = '--s';
        document.getElementById('kpi_ultimo_registro').innerText = '--';
        return;
    }
    const activos = registros.filter(r => r.estado === 'Activo');
    document.getElementById('kpi_total_registros').innerText = activos.length;

    let sumaConvertidos = 0;
    activos.forEach(r => sumaConvertidos += parseFloat(r.tiempo_convertido_seg));
    const promedio = activos.length > 0 ? (sumaConvertidos / activos.length).toFixed(2) : '--';
    document.getElementById('kpi_promedio_convertido').innerText = promedio + 's';

    // Último registro (fecha más reciente)
    if (activos.length > 0) {
        const ultimo = activos.reduce((a, b) => new Date(a.fecha_registro) > new Date(b.fecha_registro) ? a : b);
        document.getElementById('kpi_ultimo_registro').innerText = formatearFecha(ultimo.fecha_registro);
    }
}

// ================== TABLA PRINCIPAL (DataTables) ==================
async function cargarTablaNormalizacion() {
    const params = {
        modo: modoPapeleraNormalizacion ? 'papelera' : 'activos',
        id_atleta: filtroAtleta?.value || '0',
        estilo: filtroEstilo?.value || '',
        distancia: filtroDistancia?.value || '',
        piscina: filtroPiscina?.value || ''
    };
    const respuesta = await peticionAjaxNormalizacion('listarNormalizaciones', null, 'GET', params);
    const registros = Array.isArray(respuesta) ? respuesta : [];

    let html = '';
    registros.forEach(r => {
        const estadoBadge = r.estado === 'Inactivo'
            ? '<span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded text-xs border border-red-200 dark:border-red-500/30"><i class="fas fa-trash-alt"></i> Anulado</span>'
            : '<span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded text-xs border border-emerald-200 dark:border-emerald-500/30"><i class="fas fa-check"></i> Activo</span>';

        let botones = '';
        if (r.estado === 'Activo') {
            if (PERMISOS_NORMALIZACION.eliminar) {
                botones += `<button onclick="archivarNormalizacion(${r.id_normalizacion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Anular"><i class="fas fa-trash-alt text-xs"></i></button>`;
            }
        } else {
            if (PERMISOS_NORMALIZACION.reactivar) {
                botones += `<button onclick="reactivarNormalizacion(${r.id_normalizacion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar"><i class="fas fa-undo-alt text-xs"></i></button>`;
            }
            if (PERMISOS_NORMALIZACION.eliminardb) {
                botones += `<button onclick="eliminarFisicoNormalizacion(${r.id_normalizacion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminar permanentemente"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
            }
        }

        html += `<tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${formatearFecha(r.fecha_registro)}</td>
            <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${r.nombre_atleta}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.estilo}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.distancia_m}m</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.tipo_piscina_origen}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${r.tiempo_original_seg}s</td>
            <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">${r.tiempo_convertido_seg}s</td>
            <td class="px-6 py-4 text-center">${estadoBadge}</td>
            <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
        </tr>`;
    });

    const tbody = document.getElementById('cuerpoTablaNormalizacion');
    if (dataTableNormalizacion) {
        dataTableNormalizacion.destroy();
        dataTableNormalizacion = null;
    }
    tbody.innerHTML = html;
    dataTableNormalizacion = $('#tablaNormalizacion').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            emptyTable: modoPapeleraNormalizacion ? 'La papelera está vacía.' : 'No hay registros de normalización.'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 1 },
            { responsivePriority: 2, targets: 6 },
            { responsivePriority: 3, targets: 8 }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todas"]],
        dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-2"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>'
    });

    cargarKPIsNormalizacion(registros);
}

// ================== TOGGLE PAPELERA ==================
function actualizarUINormalizacion() {
    if (!toggleBtn) return;
    const isActive = !modoPapeleraNormalizacion;
    if (isActive) {
        toggleBtn.classList.remove('active');
        document.getElementById('toggleTextoNormalizacion').innerText = 'Activos';
        document.getElementById('toggleIconoNormalizacion').className = 'fas fa-trash-alt text-gray-500 dark:text-gray-400';
        document.getElementById('estadoBadgeNormalizacion').innerHTML = 'A';
        document.getElementById('estadoBadgeNormalizacion').classList.remove('bg-red-500');
        document.getElementById('estadoBadgeNormalizacion').classList.add('bg-indigo-500');
    } else {
        toggleBtn.classList.add('active');
        document.getElementById('toggleTextoNormalizacion').innerText = 'Inactivos';
        document.getElementById('toggleIconoNormalizacion').className = 'fas fa-trash-restore text-red-400';
        document.getElementById('estadoBadgeNormalizacion').innerHTML = 'I';
        document.getElementById('estadoBadgeNormalizacion').classList.remove('bg-indigo-500');
        document.getElementById('estadoBadgeNormalizacion').classList.add('bg-red-500');
    }
    document.dispatchEvent(new CustomEvent('modoPapeleraNormalizacionChanged'));
}

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        modoPapeleraNormalizacion = !modoPapeleraNormalizacion;
        window.modoPapeleraNormalizacion = modoPapeleraNormalizacion;
        actualizarUINormalizacion();
        cargarTablaNormalizacion();
    });
}

// ================== MODAL: ABRIR / CERRAR / GUARDAR ==================
function abrirModalFormulario(id = null) {
    formNormalizacion.reset();
    document.getElementById('id_normalizacion').value = '';
    document.getElementById('accion').value = 'registrar';
    document.getElementById('tituloModal').innerHTML = 'Registrar Conversión';
    document.getElementById('tiempo_convertido_preview').value = '--';
    btnGuardar.innerHTML = 'GUARDAR Y CONVERTIR <i class="fas fa-bolt ml-2"></i>';
    btnGuardar.classList.replace('bg-emerald-600', 'bg-indigo-600');
    btnGuardar.classList.replace('hover:bg-emerald-500', 'hover:bg-indigo-500');
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formNormalizacion);

    if (id) {
        // Cargar datos para edición (opcional)
        cargarDatosEdicion(id);
    }

    modalFormulario.classList.remove('hidden');
    setTimeout(() => {
        modalFormulario.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function cerrarModalFormulario() {
    modalFormulario.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalFormulario.classList.add('hidden');
    }, 300);
}

// ===== CÁLCULO EN VIVO DEL TIEMPO CONVERTIDO =====
document.getElementById('tiempo_original_seg').addEventListener('input', function() {
    const tiempoOriginal = parseFloat(this.value);
    const piscina = document.getElementById('tipo_piscina_origen').value;
    const preview = document.getElementById('tiempo_convertido_preview');

    if (isNaN(tiempoOriginal) || tiempoOriginal <= 0) {
        preview.value = '--';
        return;
    }
    let factor = 0.02; // 2% (puedes ajustar según factores reales)
    let convertido;
    if (piscina === '25m') {
        convertido = tiempoOriginal + (tiempoOriginal * factor);
    } else if (piscina === '50m') {
        convertido = tiempoOriginal - (tiempoOriginal * factor);
    } else {
        convertido = tiempoOriginal;
    }
    preview.value = convertido.toFixed(2) + 's';
});

document.getElementById('tipo_piscina_origen').addEventListener('change', function() {
    // Disparar el cálculo nuevamente
    document.getElementById('tiempo_original_seg').dispatchEvent(new Event('input'));
});

// ===== PROCESAR FORMULARIO =====
formNormalizacion.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (typeof Validador !== 'undefined') {
        const erroresHTML = Validador.validarFormulario(formNormalizacion);
        if (erroresHTML) {
            UI.error('Error de Validación', erroresHTML);
            return;
        }
    }

    const datos = new FormData(formNormalizacion);
    const accionActual = document.getElementById('accion').value;

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const resultado = await peticionAjaxNormalizacion(accionActual, datos, 'POST');

    btnGuardar.disabled = false;
    btnGuardar.innerHTML = accionActual === 'actualizar' ? 'ACTUALIZAR <i class="fas fa-sync-alt ml-2"></i>' : 'GUARDAR Y CONVERTIR <i class="fas fa-bolt ml-2"></i>';

    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('¡Éxito!', resultado.message);
            cerrarModalFormulario();
            cargarTablaNormalizacion();
        } else {
            UI.error('Error', resultado.message || 'Error al procesar la solicitud.');
        }
    }
});

// ================== SOFT DELETE, REACTIVAR, ELIMINAR FÍSICO ==================
async function archivarNormalizacion(id) {
    const justificacion = await (typeof UI !== 'undefined' && UI.pedirJustificacion ?
        UI.pedirJustificacion('Anular Registro', 'Justifique por qué se anula este registro:', 'Motivo obligatorio (mínimo 10 caracteres)') :
        Swal.fire({
            title: 'Anular Registro',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputPlaceholder: 'Ej. Error de transcripción...',
            inputAttributes: { required: true, minlength: 5 },
            showCancelButton: true,
            confirmButtonText: 'Anular',
            background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
        })
    );
    if (!justificacion.isConfirmed) return;
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_normalizacion', id);
    formData.append('motivo', justificacion.value);
    const resultado = await peticionAjaxNormalizacion('eliminar', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Anulado', resultado.message);
        cargarTablaNormalizacion();
    } else {
        UI.error('Error', resultado?.message);
    }
}

async function reactivarNormalizacion(id) {
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
    formData.append('accion', 'reactivar');
    formData.append('id_normalizacion', id);
    const resultado = await peticionAjaxNormalizacion('reactivar', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Restaurado', resultado.message);
        cargarTablaNormalizacion();
    } else {
        UI.error('Error', resultado?.message);
    }
}

async function eliminarFisicoNormalizacion(id) {
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
    formData.append('accion', 'eliminarFisico');
    formData.append('id_normalizacion', id);
    const resultado = await peticionAjaxNormalizacion('eliminarFisico', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Purgado', resultado.message);
        cargarTablaNormalizacion();
    } else {
        UI.error('Error', resultado?.message);
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
    await cargarAtletasNormalizacion();
    cargarTablaNormalizacion();

    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formNormalizacion);
    }

    // Inicializar estado del toggle
    actualizarUINormalizacion();
});