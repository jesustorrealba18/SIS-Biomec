// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalMedicion = document.getElementById('modalMedicion');
const formMedicion = document.getElementById('formMedicion');
const btnGuardar = document.getElementById('btnGuardar');

const modalGraficas = document.getElementById('modalGraficas');
let chartPesoTallaInstancia = null;
let chartIMCInstancia = null;

// RUTA AL CONTROLADOR PIVOTE
const API_URL = 'index.php?p=antropometria'; 

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
        
        // Colores dinámicos según estándar de la OMS (adaptados claro/oscuro)
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

// =====================================================================
// MANEJO DE LA INTERFAZ (MODALES)
// =====================================================================

function abrirModalMedicion() {
    formMedicion.reset();
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formMedicion);
    
    document.getElementById('accion').value = 'guardar';
    document.getElementById('id_medicion').value = '';
    document.getElementById('modalMedicionTitulo').innerHTML = `
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
            <i class="fas fa-weight text-indigo-600 dark:text-indigo-400"></i>
        </div> Registrar Medición
    `;
    
    document.getElementById('contenedorJustificacion').classList.add('hidden');
    document.getElementById('justificacion').removeAttribute('data-validar');
    
    imcPreview.textContent = '--';

    modalMedicion.classList.remove('hidden');
    setTimeout(() => {
        modalMedicion.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
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

// =====================================================================
// CARGA DE DATOS (DASHBOARD Y SELECTS)
// =====================================================================

async function cargarAtletas() {
    const data = await peticionAjax('listarAtletasSelect');
    if (data) {
        const select = document.getElementById('id_atleta');
        select.innerHTML = '<option value="">Seleccione un atleta...</option>';
        data.forEach(atleta => {
            select.innerHTML += `<option value="${atleta.id_atleta}">${atleta.cedula} - ${atleta.nombres} ${atleta.apellidos} - ${atleta.categoria_nombre}</option>`;
        });
    }
}

async function cargarDashboard() {
    const tbody = document.getElementById('tablaDashboardBody');
    tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando...</td></tr>`;
    
    const respuesta = await peticionAjax('cargarDashboard');
    
    if (respuesta && respuesta.data) {
        tbody.innerHTML = '';
        if (respuesta.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-gray-500 dark:text-gray-400">No hay atletas registrados.</td></tr>`;
            return;
        }

        respuesta.data.forEach(fila => {
            let alertaDias = '';
            if (fila.dias_sin_evaluacion === null) {
                alertaDias = `<span class="bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 py-1 px-2 rounded font-bold text-xs"><i class="fas fa-exclamation-circle"></i> Sin evaluar</span>`;
            } else if (fila.dias_sin_evaluacion > 90) {
                alertaDias = `<span class="bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 py-1 px-2 rounded font-bold text-xs"><i class="fas fa-clock"></i> Hace ${fila.dias_sin_evaluacion} días</span>`;
            } else {
                alertaDias = `<span class="text-emerald-600 dark:text-emerald-400 font-medium text-xs">Hace ${fila.dias_sin_evaluacion} días</span>`;
            }

            let pesoTalla = fila.peso ? `${fila.peso} kg / ${fila.talla} cm` : '--';
            let imc = fila.imc ? `<span class="font-bold text-gray-900 dark:text-white">${fila.imc}</span>` : '--';
            let fechaEval = fila.ultima_fecha || '--';

            tbody.innerHTML += `
                <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition duration-200 border-b border-gray-200 dark:border-[#252345]">
                    <td class="p-4 font-medium text-gray-900 dark:text-white">${fila.nombres} ${fila.apellidos}</td>
                    <td class="p-4 text-gray-500 dark:text-gray-400">${fila.categoria}</td>
                    <td class="p-4 text-center text-gray-700 dark:text-gray-300">${formatearFecha(fechaEval)}<br>${alertaDias}</td>
                    <td class="p-4 text-center text-gray-700 dark:text-gray-300">${pesoTalla}</td>
                    <td class="p-4 text-center">${imc}</td>
                    <td class="p-4 text-center">
                        <span class="bg-green-50 dark:bg-green-500/20 text-green-600 dark:text-green-400 px-3 py-1 rounded-full text-xs font-bold border border-green-200 dark:border-green-500/30">Activo</span>
                    </td>
                    <td class="p-4 text-center space-x-2">
                        <button onclick="verHistorial(${fila.id_atleta}, '${fila.nombres} ${fila.apellidos}')" 
                                class="bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" 
                                title="Ver Gráficas e Historial">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
}

// =====================================================================
// PROCESAR FORMULARIO (GUARDAR Y EDITAR)
// =====================================================================
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

    const resultado = await peticionAjax(accionActual, datos);

    btnGuardar.disabled = false;
    btnGuardar.innerHTML = 'GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>';

    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('¡Éxito!', resultado.message);
            cerrarModalMedicion();
            cargarDashboard();
            
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

// =====================================================================
// HISTORIAL, GRÁFICAS (RF-05.2) Y EDICIÓN (RF-05.3)
// =====================================================================

async function verHistorial(id_atleta, nombreCompleto) {
    document.getElementById('graficaAtletaNombre').textContent = nombreCompleto;
    document.getElementById('graficaAtletaNombre').dataset.id = id_atleta;
    
    const respuesta = await peticionAjax(`verHistorial&id_atleta=${id_atleta}`);
    
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
                        ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar ? `
                        <button onclick="prepararEdicion('${encodeURIComponent(JSON.stringify(r))}')" 
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors" 
                                title="Corregir Registro">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="eliminarMedicion(${r.id_medicion})" 
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

function prepararEdicion(registroStr) {
    const r = JSON.parse(decodeURIComponent(registroStr));
    
    document.getElementById('id_medicion').value = r.id_medicion;
    document.getElementById('id_atleta').value = r.id_atleta;
    document.getElementById('fecha').value = r.fecha;
    document.getElementById('peso').value = r.peso;
    document.getElementById('talla').value = r.talla;
    document.getElementById('envergadura').value = r.envergadura;
    document.getElementById('perimetro_abdominal').value = r.perimetro_abdominal;
    document.getElementById('grasa_corporal').value = r.grasa_corporal || '';
    
    document.getElementById('accion').value = 'editar';
    document.getElementById('modalMedicionTitulo').innerHTML = `
        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-500/20 flex items-center justify-center mr-4">
            <i class="fas fa-edit text-orange-600 dark:text-orange-400"></i>
        </div> Corregir Medición
    `;
    
    document.getElementById('contenedorJustificacion').classList.remove('hidden');
    document.getElementById('justificacion').value = '';
    document.getElementById('justificacion').setAttribute('data-validar', 'requerido');
    
    calcularIMCEnVivo();
    
    modalGraficas.classList.add('hidden');
    modalMedicion.classList.remove('hidden');
    setTimeout(() => {
        modalMedicion.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}


async function eliminarMedicion(id_medicion) {
    const { value: motivo } = await Swal.fire({
        title: '¿Eliminar Medición?',
        text: "Esta acción no se puede deshacer. Escriba un motivo para la auditoría (Bitácora):",
        input: 'text',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: document.documentElement.classList.contains('dark') ? '#161430' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#374151',
        inputValidator: (value) => {
            if (!value) return '¡El motivo es obligatorio para la auditoría!';
        }
    });

    if (motivo) {
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('id_medicion', id_medicion);
        formData.append('motivo', motivo);

        const resultado = await peticionAjax('eliminar', formData);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', resultado.message);
            
            // Recargamos el dashboard de fondo
            cargarDashboard();
            
            // Refrescamos la gráfica y tabla si el modal está abierto
            const idAtleta = document.getElementById('graficaAtletaNombre').dataset.id;
            const nombreAtleta = document.getElementById('graficaAtletaNombre').innerText;
            if (idAtleta) {
                verHistorial(idAtleta, nombreAtleta);
            }
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}

// =====================================================================
// CIERRE DE MODALES CON ESC
// =====================================================================
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalMedicion.classList.contains('hidden')) {
            cerrarModalMedicion();
        }
        if (!modalGraficas.classList.contains('hidden')) {
            cerrarModalGraficas();
        }
    }
});

// =====================================================================
// INICIALIZADOR
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    cargarAtletas();
    cargarDashboard();
});