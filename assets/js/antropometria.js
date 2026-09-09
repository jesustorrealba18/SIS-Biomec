// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalMedicion = document.getElementById('modalMedicion');
const formMedicion = document.getElementById('formMedicion');
const btnGuardar = document.getElementById('btnGuardar');

const modalGraficas = document.getElementById('modalGraficas');
let chartPesoTallaInstancia = null;
let chartIMCInstancia = null;

const API_URL = 'index.php?p=antropometria';
let dataTableAntropometria = null;
let dataTableAlertasAntropometria = null;
let dataTableHistorialAntropometria = null;
let modoPapeleraAntropometria = false;


const toggleBtn = document.getElementById('toggleEstadoAntropometriaBtn');

// ================== PETICIONES AJAX ==================
async function peticionAjaxAntropometria(accion, datos = null, metodo = 'GET', params = {}) {
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

// =====================================================================
// DETECCIÓN DE TEMA PARA CHART.JS
// =====================================================================
function getChartColors() {
    const esOscuro = document.documentElement.classList.contains('dark');
    return {
        texto: esOscuro ? '#a0a0c0' : '#4b5563',
        grid: esOscuro ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)'
    };
}

// ================== CARGAR ATLETAS EN SELECTS ==================
 async function cargarAtletasAntropometria() {
    const atletas = await peticionAjaxAntropometria('listarAtletasSelect');
    if (!Array.isArray(atletas)) return;
    // Select del formulario
    let opcionesForm = '<option value="">Seleccione un atleta...</option>';
    // Select del filtro
    let opcionesFiltro = '<option value="">👤 Todos los Atletas</option>';
    atletas.forEach(a => {
        const txt = `${a.cedula} - ${a.nombres} ${a.apellidos} - ${a.categoria_nombre}`;
        /* opcionesForm += `<option value="${a.id_atleta}">${txt}</option>`;
        opcionesFiltro += `<option value="${a.id_atleta}">${txt}</option>`; */
        opcionesForm += `<option value="${a.id_atleta}" data-sexo="${a.sexo}">${txt}</option>`;
        opcionesFiltro += `<option value="${a.id_atleta}">${txt}</option>`;
    });
    document.getElementById('id_atleta').innerHTML = opcionesForm;
     /* if (filtroAtleta) filtroAtleta.innerHTML = opcionesFiltro;  */
} 

// ================== KPIs ==================
async function cargarKPIsAntropometria() {
    const kpis = await peticionAjaxAntropometria('obtenerKPIs');
    if (!kpis) return;
    document.getElementById('kpi_pendientes').innerText = kpis.pendientes ?? '--';
    document.getElementById('kpi_imc_promedio').innerText = kpis.imc_promedio !== null ? kpis.imc_promedio.toFixed(1) : '--';
    document.getElementById('kpi_mediciones_mes').innerText = kpis.mediciones_mes ?? '--';
    document.getElementById('kpi_cobertura').innerText = kpis.cobertura !== null ? kpis.cobertura + '%' : '--%';
}

// ================== TABLA PRINCIPAL (DataTables) ==================

async function cargarTablaAntropometria() {
    const params = {
        modo: modoPapeleraAntropometria ? 'papelera' : 'activos',
        id_atleta: 0 
    };
    const respuesta = await peticionAjaxAntropometria('cargarDashboard', null, 'GET', params);
    const registros = respuesta?.data || [];

    let html = '';
    
   
    registros.forEach(r => {
        let fecha = modoPapeleraAntropometria ? formatearFecha(r.fecha) : (r.ultima_fecha ? formatearFecha(r.ultima_fecha) : 'Sin registro');
        let nombre = modoPapeleraAntropometria ? r.nombre_atleta : `${r.nombres} ${r.apellidos}`;
        let peso = (r.peso_kg || r.peso) ? `${r.peso_kg || r.peso} kg` : '--';
        let talla = (r.talla_cm || r.talla) ? `${r.talla_cm || r.talla} cm` : '--';
        let imc = r.imc || '--';
        let responsable = r.responsable || '--';
        let id_medicion = r.id_medicion;
        let deleted_at = r.deleted_at;

        const estadoBadge = deleted_at
            ? '<span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded text-xs border border-red-200 dark:border-red-500/30"><i class="fas fa-trash-alt"></i> Anulado</span>'
            : '<span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded text-xs border border-emerald-200 dark:border-emerald-500/30"><i class="fas fa-check"></i> Activo</span>';

        let botones = `<button onclick="verHistorial(${r.id_atleta}, '${nombre}')" class="bg-gray-200 dark:bg-[#252345] hover:bg-indigo-600 text-gray-700 dark:text-white w-8 h-8 rounded-lg transition-colors" title="Ver Historial"><i class="fas fa-chart-line text-xs"></i></button>`;

        if (id_medicion) {
            if (!deleted_at) {
                if (PERMISOS_ANTROPOMETRIA.editar) botones += `<button onclick="prepararEdicion(${id_medicion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
                if (PERMISOS_ANTROPOMETRIA.eliminar) botones += `<button onclick="anularMedicion(${id_medicion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Anular"><i class="fas fa-trash-alt text-xs"></i></button>`;
            } else {
                if (PERMISOS_ANTROPOMETRIA.reactivar) botones += `<button onclick="reactivarMedicion(${id_medicion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar"><i class="fas fa-undo-alt text-xs"></i></button>`;
                if (PERMISOS_ANTROPOMETRIA.eliminardb) botones += `<button onclick="eliminarFisicoMedicion(${id_medicion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminar permanentemente"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
            }
        }

        html += `<tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${fecha}</td>
            <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${nombre}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${peso}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${talla}</td>
            <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">${imc}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">${responsable}</td>
            <td class="px-6 py-4 text-center">${estadoBadge}</td>
            <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
        </tr>`;
    });

    const tbody = document.getElementById('tablaCuerpoAntropometria');
    
    
    if (dataTableAntropometria) {
        dataTableAntropometria.destroy();
    }
    
    
    tbody.innerHTML = html;
    
    // Inicializamos DataTables SIEMPRE
    dataTableAntropometria = $('#tablaAntropometria').DataTable({
        responsive: true,
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            emptyTable: modoPapeleraAntropometria ? 'La papelera está vacía.' : 'No hay mediciones registradas.'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 1 },
            { responsivePriority: 2, targets: 4 },
            { responsivePriority: 3, targets: 7 }
        ]
    });
}

// ================== TOGGLE PAPELERA ==================
function actualizarUIAntropometria() {
    if (!toggleBtn) return;
    const isActive = !modoPapeleraAntropometria;
    if (toggleBtn) {
        if (isActive) {
            toggleBtn.classList.remove('active');
            document.getElementById('toggleTextoAntropometria').innerText = 'Activos';
            document.getElementById('toggleIconoAntropometria').className = 'fas fa-trash-alt text-gray-500 dark:text-gray-400';
            document.getElementById('estadoBadgeAntropometria').innerHTML = 'A';
            document.getElementById('estadoBadgeAntropometria').classList.remove('bg-red-500');
            document.getElementById('estadoBadgeAntropometria').classList.add('bg-indigo-500');
        } else {
            toggleBtn.classList.add('active');
            document.getElementById('toggleTextoAntropometria').innerText = 'Inactivos';
            document.getElementById('toggleIconoAntropometria').className = 'fas fa-trash-restore text-red-400';
            document.getElementById('estadoBadgeAntropometria').innerHTML = 'I';
            document.getElementById('estadoBadgeAntropometria').classList.remove('bg-indigo-500');
            document.getElementById('estadoBadgeAntropometria').classList.add('bg-red-500');
        }
    }
    document.dispatchEvent(new CustomEvent('modoPapeleraAntropometriaChanged'));
}

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        modoPapeleraAntropometria = !modoPapeleraAntropometria;
        window.modoPapeleraAntropometria = modoPapeleraAntropometria;
        actualizarUIAntropometria();
        cargarTablaAntropometria();
        // También recargamos alertas? No, las alertas siempre muestran activos.
    });
}


// =====================================================================
// RF-05.1: CÁLCULO DE IMC EN TIEMPO REAL (PREVIEW VISUAL)
// =====================================================================
const inputPeso = document.getElementById('peso');
const inputTalla = document.getElementById('talla');
const imcPreview = document.getElementById('imc_preview');

function calcularIMCEnVivo() {
    let p = parseFloat(inputPeso.value);
    let t = parseFloat(inputTalla.value);

    if (p > 0 && t > 0) {
        let tallaMetros = t / 100;
        let imc = (p / (tallaMetros * tallaMetros)).toFixed(2);
        
        let colorClass = 'text-gray-900 dark:text-white';
        if (imc < 18.5) colorClass = 'text-blue-600 dark:text-blue-400';
        else if (imc >= 18.5 && imc <= 24.9) colorClass = 'text-emerald-600 dark:text-emerald-400';
        else if (imc >= 25 && imc <= 29.9) colorClass = 'text-amber-600 dark:text-amber-400';
        else colorClass = 'text-red-600 dark:text-red-400';

        imcPreview.className = `text-xl font-bold ${colorClass}`;
        imcPreview.textContent = imc;
    } else {
        imcPreview.className = 'text-xl font-bold text-gray-900 dark:text-white';
        imcPreview.textContent = '--';
    }
}

inputPeso.addEventListener('input', calcularIMCEnVivo);
inputTalla.addEventListener('input', calcularIMCEnVivo);

// ================== MODAL: ABRIR / CERRAR / GUARDAR (con adaptación a soft delete) ==================
function abrirModalMedicion(id_medicion = null) {
    formMedicion.reset();
    document.getElementById('id_medicion').value = '';
    document.getElementById('accion').value = 'guardar';
    document.getElementById('modalMedicionTitulo').innerHTML = `
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
            <i class="fas fa-weight text-indigo-600 dark:text-indigo-400"></i>
        </div> Registrar Medición
    `;
    document.getElementById('contenedorJustificacion').classList.add('hidden');
    document.getElementById('justificacion').removeAttribute('data-validar');
    imcPreview.textContent = '--';
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formMedicion);
    
    if (id_medicion) {
        // Cargar datos para edición (usando la misma lógica de antes)
        cargarDatosEdicion(id_medicion);
    } else {
        btnGuardar.innerHTML = 'GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>';
        btnGuardar.classList.replace('bg-emerald-600', 'bg-indigo-600');
        btnGuardar.classList.replace('hover:bg-emerald-500', 'hover:bg-indigo-500');
    }

    
    modalMedicion.classList.remove('hidden');
    setTimeout(() => {
        modalMedicion.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarDatosEdicion(id_medicion) {
    const btn = btnGuardar;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    btn.disabled = true;
    const data = await peticionAjaxAntropometria('obtenerMedicion', null, 'GET', { id: id_medicion });
    if (!data || data.status === 'error') {
        UI.error('Error', data?.message || 'No se pudo cargar la medición.');
        cerrarModalMedicion();
        return;
    }
    // Llenar campos
    document.getElementById('id_medicion').value = data.id_medicion;
    document.getElementById('id_atleta').value = data.id_atleta;
    document.getElementById('fecha').value = data.fecha;
    document.getElementById('peso').value = data.peso;
    document.getElementById('talla').value = data.talla;
    document.getElementById('envergadura').value = data.envergadura;
    document.getElementById('perimetro_abdominal').value = data.perimetro_abdominal;
    document.getElementById('grasa_corporal').value = data.porcentaje_grasa || '';
    
    document.getElementById('accion').value = 'editar';
    document.getElementById('modalMedicionTitulo').innerHTML = `
        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-500/20 flex items-center justify-center mr-4">
            <i class="fas fa-edit text-orange-600 dark:text-orange-400"></i>
        </div> Corregir Medición
    `;
    document.getElementById('contenedorJustificacion').classList.remove('hidden');
    document.getElementById('justificacion').setAttribute('data-validar', 'requerido|texto');
    calcularIMCEnVivo();
    btn.innerHTML = 'ACTUALIZAR MEDICIÓN <i class="fas fa-sync-alt ml-2"></i>';
    btn.classList.replace('bg-indigo-600', 'bg-emerald-600');
    btn.classList.replace('hover:bg-indigo-500', 'hover:bg-emerald-500');
    btn.disabled = false;
}

function cerrarModalMedicion() {
    modalMedicion.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalMedicion.classList.add('hidden');
    }, 300);
}

function cerrarModalGraficas() {
    modalGraficas.classList.add('hidden');
    if (chartPesoTallaInstancia) {
        chartPesoTallaInstancia.destroy();
        chartPesoTallaInstancia = null;
    }
    if (chartIMCInstancia) {
        chartIMCInstancia.destroy();
        chartIMCInstancia = null;
    }
}

// ===== PROCESAR FORMULARIO (GUARDAR Y EDITAR) =====
formMedicion.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (typeof Validador !== 'undefined') {
        const erroresHTML = Validador.validarFormulario(formMedicion);
        if (erroresHTML) {
            UI.error('Error de Validación', erroresHTML);
            return;
        }
    }

    const datos = new FormData(formMedicion);
    const accionActual = document.getElementById('accion').value;

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const resultado = await peticionAjaxAntropometria(accionActual, datos, 'POST');

    btnGuardar.disabled = false;
    btnGuardar.innerHTML = accionActual === 'editar' ? 'ACTUALIZAR MEDICIÓN <i class="fas fa-sync-alt ml-2"></i>' : 'GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>';

    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('¡Éxito!', resultado.message);
            cerrarModalMedicion();
            cargarTablaAntropometria();
            cargarKPIsAntropometria();
            cargarAlertasAntropometria();
            cargarAlertasBiologicas();
            
            // Si el modal de gráficas está abierto, refrescar
            if (!modalGraficas.classList.contains('hidden')) {
                const idAtleta = document.getElementById('id_atleta').value;
                const nombreAtleta = document.getElementById('graficaAtletaNombre').innerText;
                verHistorial(idAtleta, nombreAtleta);
            }
        } else {
            let msgError = resultado.message || 'Corrige los siguientes campos:';
            if (resultado.errores) {
                msgError += '<br><div class="text-left mt-2 text-sm">' + resultado.errores.join('<br>') + '</div>';
            }
            UI.error('Atención', msgError);
        }
    }
});

// ================== SOFT DELETE, REACTIVAR, ELIMINAR FÍSICO ==================
async function anularMedicion(id_medicion) {
    const justificacion = await (typeof UI !== 'undefined' && UI.pedirJustificacion ? 
        UI.pedirJustificacion('Anular Medición', 'Justifique por qué se anula este registro (ej. Error en la toma):', 'Motivo obligatorio (mínimo 10 caracteres)') :
        Swal.fire({
            title: 'Anular Medición',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputPlaceholder: 'Ej. Error en la medición, dato inconsistente...',
            inputAttributes: { required: true, minlength: 5 },
            showCancelButton: true,
            confirmButtonText: 'Anular',
            background: document.documentElement.classList.contains('dark') ? '#111026' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937'
        })
    );
    if (!justificacion.isConfirmed) return;
    const formData = new FormData();
    formData.append('accion', 'anular');
    formData.append('id_medicion', id_medicion);
    formData.append('motivo', justificacion.value);
    const resultado = await peticionAjaxAntropometria('anular', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Anulado', resultado.message);
        cargarTablaAntropometria();
        cargarKPIsAntropometria();
        cargarAlertasAntropometria();
        cargarAlertasBiologicas();
    } else {
        UI.error('Error', resultado?.message);
    }
}

async function reactivarMedicion(id_medicion) {
    const confirm = await (typeof UI !== 'undefined' && UI.confirmar ? 
        UI.confirmar('Restaurar Medición', '¿Desea reactivar esta medición?') :
        Swal.fire({
            title: 'Restaurar Medición',
            text: '¿Desea reactivar esta medición?',
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
    formData.append('id_medicion', id_medicion);
    const resultado = await peticionAjaxAntropometria('reactivar', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Restaurado', resultado.message);
        cargarTablaAntropometria();
        cargarKPIsAntropometria();
        cargarAlertasAntropometria();
        cargarAlertasBiologicas();
    } else {
        UI.error('Error', resultado?.message);
    }
}

async function eliminarFisicoMedicion(id_medicion) {
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
    formData.append('id_medicion', id_medicion);
    const resultado = await peticionAjaxAntropometria('eliminarFisico', formData, 'POST');
    if (resultado?.status === 'success') {
        UI.exito('Purgado', resultado.message);
        cargarTablaAntropometria();
        cargarKPIsAntropometria();
        cargarAlertasAntropometria();
        cargarAlertasBiologicas();
    } else {
        UI.error('Error', resultado?.message);
    }
}

// ================== ALERTAS: ATLETAS CON MEDICIÓN VENCIDA ==================
async function cargarAlertasAntropometria() {
    const tbody = document.getElementById('listaAlertasAntropometria');
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando alertas...</td></tr>`;

    const alertas = await peticionAjaxAntropometria('listarAlertas');
    if (!alertas || alertas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-emerald-600 dark:text-emerald-400"><i class="fas fa-check-circle mr-2"></i> No hay atletas con medición vencida.</td></tr>`;
        // Destruir DataTable si existe
        if (dataTableAlertasAntropometria) {
            dataTableAlertasAntropometria.destroy();
            dataTableAlertasAntropometria = null;
        }
        return;
    }

    let html = '';
    alertas.forEach(a => {
        const dias = a.dias_sin_evaluacion !== null ? a.dias_sin_evaluacion : 'Nunca';

        let botonMedir = '';
        if (PERMISOS_ANTROPOMETRIA.registrar) {
            botonMedir = `<button onclick="abrirModalMedicionConAtleta(${a.id_atleta})" class="btn-blink bg-indigo-50 dark:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition">
                            <i class="fas fa-ruler-combined"></i> Medir ahora
                        </button>`;
        } else {
            botonMedir = `<span class="text-gray-400 text-xs">Sin permiso</span>`;
        }


        html += `<tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">${a.nombres} ${a.apellidos}</td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${a.categoria}</td>
            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${a.ultima_fecha ? formatearFecha(a.ultima_fecha) : 'Sin registro'}</td>
            <td class="px-6 py-4">
                <span class="bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 px-2 py-1 rounded font-bold text-xs border border-red-200 dark:border-red-500/30">
                    ${dias === 'Nunca' ? 'Sin evaluar' : dias + ' días'}
                </span>
            </td>
            <td class="px-6 py-4">
                ${botonMedir}
            </td>
        </tr>`;
    });

    // Insertar HTML en el tbody
    tbody.innerHTML = html;

    // Destruir instancia anterior si existe
    if (dataTableAlertasAntropometria) {
        dataTableAlertasAntropometria.destroy();
        dataTableAlertasAntropometria = null;
    }

    // Inicializar DataTable en la tabla de alertas
    dataTableAlertasAntropometria = $('#tablaAlertasAntropometria').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            emptyTable: 'No hay atletas con medición vencida.'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // Atleta
            { responsivePriority: 2, targets: 4 }, // Acción
            { responsivePriority: 3, targets: 3 }  // Días sin medir
        ],
        order: [[3, 'desc']] // Ordenar por "Días sin medir" descendente
    });
}

async function cargarAlertasBiologicas() {
    const lista = document.getElementById('listaAlertasBiologicas');
    if (!lista) return;

    const alertas = await peticionAjaxAntropometria('obtenerAlertasActivas');
    if (!alertas || alertas.length === 0) {
        lista.innerHTML = `
            <li class="text-center text-gray-500 dark:text-gray-400 py-4">
                <i class="fas fa-check-circle text-green-500 mr-2"></i> No hay alertas activas.
            </li>
        `;
        return;
    }

    let html = '';
    alertas.forEach(alert => {
        // Determinar clase de gravedad (igual que en lesiones)
        let gravedadClase, gravedadTexto;
        if (alert.gravedad == 3) {
            gravedadClase = 'bg-red-500';
            gravedadTexto = 'Alta';
        } else if (alert.gravedad == 2) {
            gravedadClase = 'bg-amber-500';
            gravedadTexto = 'Media';
        } else {
            gravedadClase = 'bg-blue-500';
            gravedadTexto = 'Baja';
        }

        html += `
            <li class="flex justify-between items-center bg-white dark:bg-[#161430] p-3 rounded shadow-sm border border-red-100 dark:border-red-500/20">
                <div>
                    <p class="font-bold text-gray-800 dark:text-white">
                        ${alert.nombres} ${alert.apellidos}
                        <span class="text-xs ${gravedadClase} text-white px-2 py-0.5 rounded ml-1">${gravedadTexto}</span>
                    </p>
                    <p class="text-xs text-gray-500">${alert.tipo_alerta}: ${alert.mensaje}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">${new Date(alert.fecha_creacion).toLocaleString()}</p>
                </div>
                <a href="javascript:verHistorial(${alert.id_atleta}, '${alert.nombres} ${alert.apellidos}')" 
                   class="text-indigo-600 hover:text-indigo-800 text-sm font-bold">
                   Ver Evolución &rarr;
                </a>
            </li>
        `;
    });

    lista.innerHTML = html;
}


// Función para abrir modal con atleta preseleccionado
function abrirModalMedicionConAtleta(id_atleta) {
    abrirModalMedicion();
   
    setTimeout(() => {
        const select = document.getElementById('id_atleta');
        if (select) {
            select.value = id_atleta;
           
            select.dispatchEvent(new Event('change'));
        }
    }, 200);
}


// =====================================================================
// HISTORIAL, GRÁFICAS (RF-05.2) Y EDICIÓN (RF-05.3)
// =====================================================================

async function verHistorial(id_atleta, nombreCompleto) {
    document.getElementById('graficaAtletaNombre').textContent = nombreCompleto;
    document.getElementById('graficaAtletaNombre').dataset.id = id_atleta;
    
    const respuesta = await peticionAjaxAntropometria('verHistorial', null, 'GET', { id_atleta: id_atleta });
    
    if (respuesta && respuesta.status === 'success') {
        const registros = respuesta.data;
        const tbody = document.getElementById('tablaHistorialBody');
        tbody.innerHTML = '';

        let labels = [];
        let dataPeso = [];
        let dataTalla = [];
        let dataIMC = [];

        if (registros.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-gray-500 dark:text-gray-400">No hay mediciones previas.</td></tr>`;
            // Destruir DataTable si existe
            if (dataTableHistorialAntropometria) {
                dataTableHistorialAntropometria.destroy();
                dataTableHistorialAntropometria = null;
            }
        } else {
            registros.forEach(r => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-200 dark:border-[#252345]';
                tr.innerHTML = `
                    <td class="p-3 text-gray-700 dark:text-gray-300">${formatearFecha(r.fecha)}</td>
                    <td class="p-3 text-gray-700 dark:text-gray-300">${r.peso} kg</td>
                    <td class="p-3 text-gray-700 dark:text-gray-300">${r.talla} cm</td>
                    <td class="p-3 text-gray-700 dark:text-gray-300">${r.envergadura} cm</td>
                    <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">${r.imc}</td>
                    <td class="p-3 text-gray-500 dark:text-gray-400 text-xs">${r.responsable}</td>
                    <td class="p-3 text-center flex justify-center gap-2">
                        ${typeof PERMISOS_ANTROPOMETRIA !== 'undefined' && PERMISOS_ANTROPOMETRIA.registrar ? `
                        <button onclick="prepararEdicion(${r.id_medicion})" 
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors" 
                                title="Corregir Registro">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                        ${typeof PERMISOS_ANTROPOMETRIA !== 'undefined' && PERMISOS_ANTROPOMETRIA.eliminar ? `
                        <button onclick="anularMedicion(${r.id_medicion})" 
                                class="text-red-600 dark:text-red-400 hover:text-red-700 transition-colors" 
                                title="Eliminar Registro">
                            <i class="fas fa-trash"></i>
                        </button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(tr);

                labels.push(formatearFecha(r.fecha));
                dataPeso.push(r.peso);
                dataTalla.push(r.talla);
                dataIMC.push(r.imc);
            });

            // Destruir instancia anterior si existe
            if (dataTableHistorialAntropometria) {
                dataTableHistorialAntropometria.destroy();
                dataTableHistorialAntropometria = null;
            }

            // Inicializar DataTables en la tabla de historial
            dataTableHistorialAntropometria = $('#tablaHistorialAntropometria').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                columnDefs: [
                    { responsivePriority: 1, targets: 1 }, // Peso
                    { responsivePriority: 2, targets: 4 }, // IMC
                    { responsivePriority: 3, targets: 6 }  // Edición
                ],
                order: [[0, 'desc']] // Ordenar por fecha (columna 0) descendente
            });
        }

        renderizarGraficos(labels, dataPeso, dataTalla, dataIMC);
        
        modalGraficas.classList.remove('hidden');
        setTimeout(() => {
            modalGraficas.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}


function renderizarGraficos(labels, dataPeso, dataTalla, dataIMC) {
    if (chartPesoTallaInstancia) {
        chartPesoTallaInstancia.destroy();
        chartPesoTallaInstancia = null;
    }
    if (chartIMCInstancia) {
        chartIMCInstancia.destroy();
        chartIMCInstancia = null;
    }

    const ctxPT = document.getElementById('chartPesoTalla').getContext('2d');
    const ctxIMC = document.getElementById('chartIMC').getContext('2d');

    const colors = getChartColors();
    
    Chart.defaults.color = colors.texto;
    Chart.defaults.font.family = 'Inter';

    chartPesoTallaInstancia = new Chart(ctxPT, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Peso (kg)',
                    data: dataPeso,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    yAxisID: 'y',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Talla (cm)',
                    data: dataTalla,
                    borderColor: '#10b981',
                    borderDash: [5, 5],
                    yAxisID: 'y1',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { 
                    type: 'linear', 
                    display: true, 
                    position: 'left',
                    grid: { color: colors.grid }
                },
                y1: { 
                    type: 'linear', 
                    display: true, 
                    position: 'right', 
                    grid: { drawOnChartArea: false }
                },
                x: {
                    grid: { color: colors.grid }
                }
            }
        }
    });

    chartIMCInstancia = new Chart(ctxIMC, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'IMC',
                data: dataIMC,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4
            }]
        },
        options: { 
            responsive: true,
            scales: {
                y: {
                    grid: { color: colors.grid }
                },
                x: {
                    grid: { color: colors.grid }
                }
            }
        }
    });
}


function prepararEdicion(id_medicion) {
    abrirModalMedicion(id_medicion);
}



// ================== UTILIDAD ==================
function formatearFecha(fechaISO) {
    if (!fechaISO) return '—';
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString('es-ES');
}

// =====================================================================
// BLOQUEO FÍSICO DEL CALENDARIO NATIVO (1 mes atrás, sin futuro)
// =====================================================================
function configurarLimitesCalendario() {
    const inputFecha = document.getElementById('fecha');
    if (!inputFecha) return;

    // Obtener fecha de hoy y de hace un mes
    const hoy = new Date();
    const haceUnMes = new Date();
    haceUnMes.setMonth(hoy.getMonth() - 1);

    // Ajuste de zona horaria local para evitar saltos de día
    const tzOffset = (new Date()).getTimezoneOffset() * 60000;
    const strHoy = (new Date(hoy - tzOffset)).toISOString().split('T')[0];
    const strHaceUnMes = (new Date(haceUnMes - tzOffset)).toISOString().split('T')[0];

    // Inyectar los límites físicos en el input
    inputFecha.min = strHaceUnMes;
    inputFecha.max = strHoy;
}

// =====================================================================
// ESTIMACIÓN AUTOMÁTICA DE GRASA CORPORAL (Fórmula RFM)
// =====================================================================
function estimarGrasaCorporal() {
    const selectAtleta = document.getElementById('id_atleta');
    const inputTalla = document.getElementById('talla');
    const inputAbdominal = document.getElementById('perimetro_abdominal');
    const inputGrasa = document.getElementById('grasa_corporal');

    // Validar que se haya seleccionado un atleta
    if (!selectAtleta.value) {
        if (typeof UI !== 'undefined') UI.error('Atención', 'Por favor seleccione un atleta primero.');
        else alert('Por favor seleccione un atleta primero.');
        return;
    }

    // Obtener el sexo del option seleccionado mediante el atributo data-sexo
    const optionSeleccionada = selectAtleta.options[selectAtleta.selectedIndex];
    const sexo = optionSeleccionada.getAttribute('data-sexo'); // 'M' o 'F'

    const tallaCm = parseFloat(inputTalla.value);
    const perimetroCm = parseFloat(inputAbdominal.value);

    // Validar que existan talla y perímetro abdominal
    if (!tallaCm || !perimetroCm || tallaCm <= 0 || perimetroCm <= 0) {
        if (typeof UI !== 'undefined') UI.error('Datos insuficientes', 'Debe ingresar la Talla y el Perímetro Abdominal para poder estimar la grasa.');
        else alert('Debe ingresar la Talla y el Perímetro Abdominal.');
        return;
    }

    let rfmMagnitud = 0;

    // Aplicar fórmula RFM (Relative Fat Mass)
    // Hombres: 64 - 20 * (Talla / Perímetro Abdominal)
    // Mujeres: 76 - 20 * (Talla / Perímetro Abdominal)
    if (sexo === 'M' || sexo === 'm') {
        rfmMagnitud = 64 - (20 * (tallaCm / perimetroCm));
    } else {
        rfmMagnitud = 76 - (20 * (tallaCm / perimetroCm));
    }

    // Limitar lógicamente el resultado (por ejemplo, entre 3% y 50%)
    let grasaEstimada = Math.max(3, Math.min(50, rfmMagnitud));

    // Asignar al input redondeado a 1 decimal
    inputGrasa.value = grasaEstimada.toFixed(1);

    // Disparar validación visual en tiempo real si el validador está activo
    if (typeof Validador !== 'undefined' && typeof Validador.validarCampo === 'function') {
        Validador.validarCampo(inputGrasa);
    }

    if (typeof UI !== 'undefined') {
        UI.exito('Estimación aplicada', `Se calculó un ~${grasaEstimada.toFixed(1)}% de grasa basado en la antropometría.`);
    }
}

// ================== INICIALIZACIÓN ==================
document.addEventListener('DOMContentLoaded', async () => {
    await cargarAtletasAntropometria();
    cargarKPIsAntropometria();
    cargarTablaAntropometria();
    cargarAlertasAntropometria();
    cargarAlertasBiologicas();

    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formMedicion);
    }

    configurarLimitesCalendario();

    // Inicializar estado del toggle
    actualizarUIAntropometria();
});

