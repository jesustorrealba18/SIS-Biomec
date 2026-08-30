const API_URL = 'index.php?p=periodizacion';

const modalMacro = document.getElementById('modalMacro');
const modalVer = document.getElementById('modalVer');
const formMacro = document.getElementById('formMacro');

let temporadasCache = [];
let gruposCache = [];
let eventosCache = [];

// ============================================================
//  COLORES CON SOPORTE CLARO/OSCURO
// ============================================================
const coloresFase = {
    'Acumulacion': {
        bg: '#10b981',
        bgAlpha: 'bg-emerald-50 dark:bg-emerald-500/20',
        text: 'text-emerald-600 dark:text-emerald-400',
        border: 'border-emerald-200 dark:border-emerald-500/30'
    },
    'Transmutacion': {
        bg: '#f59e0b',
        bgAlpha: 'bg-amber-50 dark:bg-amber-500/20',
        text: 'text-amber-600 dark:text-amber-400',
        border: 'border-amber-200 dark:border-amber-500/30'
    },
    'Realizacion': {
        bg: '#ef4444',
        bgAlpha: 'bg-red-50 dark:bg-red-500/20',
        text: 'text-red-600 dark:text-red-400',
        border: 'border-red-200 dark:border-red-500/30'
    },
    'Deload': {
        bg: '#6b7280',
        bgAlpha: 'bg-gray-100 dark:bg-gray-500/20',
        text: 'text-gray-600 dark:text-gray-400',
        border: 'border-gray-200 dark:border-gray-500/30'
    }
};

const coloresEstado = {
    'Planificado': 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20',
    'En Progreso': 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20',
    'Finalizado': 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20',
    'Cancelado': 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20'
};

// ============================================================
//  PETICIÓN AJAX
// ============================================================
async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        const texto = await respuesta.text();

        if (!texto.trim()) throw new Error('Respuesta vacia del servidor');

        if (texto.trim().charAt(0) !== '{' && texto.trim().charAt(0) !== '[') {
            console.error('Respuesta no-JSON:', texto.substring(0, 300));
            throw new Error('El servidor no devolvio JSON valido');
        }

        return JSON.parse(texto);
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

// ============================================================
//  TABLA PRINCIPAL
// ============================================================
async function cargarTablaMacro() {
    const id_temporada = document.getElementById('filtroTemporada').value || null;
    const id_grupo = document.getElementById('filtroGrupo').value || null;
    const estado = document.getElementById('filtroEstado').value || null;

    const params = [];
    if (id_temporada) params.push(`id_temporada=${id_temporada}`);
    if (id_grupo) params.push(`id_grupo=${id_grupo}`);
    if (estado) params.push(`estado=${estado}`);

    const datos = await peticionAjax(`listarMacrociclos&${params.join('&')}`);
    const tbody = document.getElementById('tbodyMacro');

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-500 dark:text-gray-400">No se encontraron macrociclos.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(m => `
        <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200 border-b border-gray-200 dark:border-[#252345]">
            <td class="p-4">
                <p class="text-gray-900 dark:text-white font-medium">${m.nombre || 'Macrociclo #' + m.id_macrociclo}</p>
            </td>
            <td class="p-4 text-xs text-gray-700 dark:text-gray-300">${m.temporada_nombre || '-'}</td>
            <td class="p-4 text-xs text-gray-700 dark:text-gray-300">${m.grupo_nombre || '-'}</td>
            <td class="p-4 text-xs">
                ${m.evento_objetivo_nombre
                    ? `<span class="text-indigo-600 dark:text-indigo-300">${m.evento_objetivo_nombre}</span><br><span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">${formatoFecha(m.evento_fecha)}</span>`
                    : '<span class="text-gray-500 dark:text-gray-400">Sin asignar</span>'}
            </td>
            <td class="p-4 font-mono text-xs text-gray-700 dark:text-gray-300">${formatoFechaRango(m.fecha_inicio, m.fecha_fin)}</td>
            <td class="p-4 text-center">
                <span class="bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 px-2 py-1 rounded-lg text-xs font-bold">${Math.round(m.total_semanas)} sem</span>
            </td>
            <td class="p-4">
                ${m.fase_actual ? badgeFase(m.fase_actual) : '<span class="text-gray-500 dark:text-gray-400 text-xs">Sin generar</span>'}
            </td>
            <td class="p-4">
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${coloresEstado[m.estado] || 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400'}">${m.estado}</span>
            </td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="verDetalle(${m.id_macrociclo})" class="p-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg transition cursor-pointer" title="Ver detalle">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    ${!m.fase_actual ? `<button onclick="generarPeriodizacion(${m.id_macrociclo})" class="p-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition cursor-pointer" title="Generar periodizacion ATR">
                        <i class="fas fa-magic text-sm"></i>
                    </button>` : `<button onclick="generarPeriodizacion(${m.id_macrociclo})" class="p-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition cursor-pointer" title="Regenerar periodizacion">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>`}
                    <button onclick="abrirModalMacro(${m.id_macrociclo})" class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition cursor-pointer" title="Editar">
                        <i class="fas fa-pen text-sm"></i>
                    </button>
                    <button onclick="accionEstado(${m.id_macrociclo}, '${m.estado}')" class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition cursor-pointer" title="Cambiar estado">
                        <i class="fas fa-exchange-alt text-sm"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}


function filtrarTablaMacro() {
    const texto = document.getElementById('busquedaMacro').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodyMacro tr');
    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
}

// ============================================================
//  MODAL MACROCICLO (CREAR / EDITAR)
// ============================================================
function abrirModalMacro(id_macrociclo = null) {
    formMacro.reset();
    try { Validador.limpiarEstilos(formMacro); } catch (e) {}
    document.getElementById('id_macrociclo').value = '';

    if (id_macrociclo) {
        document.getElementById('modalMacroTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Macrociclo';
        peticionAjax(`obtenerDetalle&id=${id_macrociclo}`).then(detalle => {
            if (!detalle) return;
            document.getElementById('id_macrociclo').value = detalle.id_macrociclo;
            document.getElementById('nombre').value = detalle.nombre || '';
            document.getElementById('id_temporada').value = detalle.id_temporada || '';
            document.getElementById('id_grupo').value = detalle.id_grupo || '';
            document.getElementById('fecha_inicio').value = detalle.fecha_inicio || '';
            document.getElementById('fecha_fin').value = detalle.fecha_fin || '';
            document.getElementById('id_evento_objetivo').value = detalle.id_evento_objetivo || '';
        });
    } else {
        document.getElementById('modalMacroTitulo').innerHTML = '<i class="fas fa-project-diagram text-emerald-400"></i> Crear Macrociclo';
    }

    modalMacro.style.display = 'flex';
    modalMacro.classList.remove('hidden');
    setTimeout(() => modalMacro.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalMacro() {
    modalMacro.style.display = 'none';
    modalMacro.classList.add('hidden');
    modalMacro.firstElementChild.classList.add('scale-95', 'opacity-0');
    formMacro.reset();
    document.getElementById('id_macrociclo').value = '';
}

formMacro.addEventListener('submit', async function(e) {
    e.preventDefault();

    const erroresJS = Validador.validarFormulario(formMacro);
    if (erroresJS) {
        UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
        return;
    }

    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    if (fechaInicio && fechaFin) {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const haceUnAnio = new Date(hoy);
        haceUnAnio.setFullYear(haceUnAnio.getFullYear() - 1);

        if (new Date(fechaInicio) < haceUnAnio) {
            UI.advertencia('Fecha Inválida', 'La <b>fecha de inicio</b> no puede ser anterior a un año atrás.');
            return;
        }
        if (fechaFin <= fechaInicio) {
            UI.advertencia('Fechas Inválidas', 'La <b>fecha de fin</b> debe ser posterior a la <b>fecha de inicio</b>.');
            return;
        }
        const diffDias = (new Date(fechaFin) - new Date(fechaInicio)) / 86400000;
        if (diffDias < 21) {
            UI.advertencia('Duración Insuficiente', 'El macrociclo debe tener al menos <b>3 semanas (21 días)</b> entre las fechas.');
            return;
        }
    }

    const id_macrociclo = document.getElementById('id_macrociclo').value;
    const formData = new FormData(formMacro);

    if (id_macrociclo) {
        formData.append('accion', 'editar');
        formData.append('id_macrociclo', id_macrociclo);

        const resultado = await peticionAjax('editar', formData);
        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Macrociclo Actualizado', resultado.message);
                cerrarModalMacro();
                cargarTablaMacro();
            } else if (resultado.status === 'warning') {
                mostrarErroresFormulario(resultado.errores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
    } else {
        formData.append('accion', 'guardar');

        const resultado = await peticionAjax('guardar', formData);
        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Macrociclo Registrado', resultado.message);
                cerrarModalMacro();
                cargarTablaMacro();
            } else if (resultado.status === 'warning') {
                mostrarErroresFormulario(resultado.errores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
    }
});

// ============================================================
//  GENERADOR ATR
// ============================================================
async function generarPeriodizacion(id_macrociclo) {
    const { value: formValues } = await Swal.fire({
        ...UI.obtenerConfig(), // <-- CAMBIADO
        title: 'Generar Plan ATR',
        html: `
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure las proporciones de periodizacion (distribucion inversa desde competencia).</p>
            <div class="grid grid-cols-2 gap-3 text-left">
                <div>
                    <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Acumulacion (%)</label>
                    <input type="number" id="swp_acum" value="55" min="10" max="90" class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                </div>
                <div>
                    <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Transmutacion (%)</label>
                    <input type="number" id="swp_trans" value="28" min="5" max="60" class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                </div>
                <div>
                    <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Realizacion (%)</label>
                    <input type="number" id="swp_real" value="12" min="5" max="40" class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                </div>
                <div>
                    <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Deload cada (semanas)</label>
                    <input type="number" id="swp_frec" value="4" min="2" max="8" class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                </div>
            </div>
            <div class="mt-4 p-3 bg-gray-100 dark:bg-black/30 rounded-xl">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-2">Leyenda de Fases</p>
                <div class="flex flex-wrap gap-2">
                    <span class="fase-badge bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#10b981"></span> Acumulacion
                    </span>
                    <span class="fase-badge bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#f59e0b"></span> Transmutacion
                    </span>
                    <span class="fase-badge bg-red-50 dark:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#ef4444"></span> Realizacion
                    </span>
                    <span class="fase-badge bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/30">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#6b7280"></span> Deload
                    </span>
                </div>
            </div>
        `,
        confirmButtonText: '<i class="fas fa-magic mr-2"></i>Generar Plan',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                pct_acumulacion: document.getElementById('swp_acum').value,
                pct_transmutacion: document.getElementById('swp_trans').value,
                pct_realizacion: document.getElementById('swp_real').value,
                frecuencia_deload: document.getElementById('swp_frec').value
            };
        }
    });

    if (!formValues) return;

    const datos = new URLSearchParams({
        accion: 'generarPeriodizacion',
        id_macrociclo: id_macrociclo,
        ...formValues
    });

    const resultado = await peticionAjax('generarPeriodizacion', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            const data = resultado.data;
            let mensajeAdvertencias = '';
            if (data.advertencias && data.advertencias.length > 0) {
                mensajeAdvertencias = '<br><br><span class="text-amber-600 dark:text-amber-400 text-xs"><i class="fas fa-exclamation-triangle mr-1"></i> Advertencias de desviacion:</span><br>' +
                    data.advertencias.map(a => `<span class="text-amber-700 dark:text-amber-300 text-xs block ml-4">- ${a}</span>`).join('');
            }

            await Swal.fire({
                ...UI.obtenerConfig(), // <-- CAMBIADO
                icon: 'success',
                title: 'Plan Generado',
                html: `
                    <p class="text-sm text-gray-700 dark:text-gray-300">${resultado.message}</p>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                        <div class="p-2 bg-gray-100 dark:bg-black/30 rounded-lg">
                            <p class="text-emerald-600 dark:text-emerald-400 font-bold">${data.porcentajes.acumulacion_generado}%</p>
                            <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase">Acumulacion</p>
                        </div>
                        <div class="p-2 bg-gray-100 dark:bg-black/30 rounded-lg">
                            <p class="text-amber-600 dark:text-amber-400 font-bold">${data.porcentajes.transmutacion_generado}%</p>
                            <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase">Transmutacion</p>
                        </div>
                        <div class="p-2 bg-gray-100 dark:bg-black/30 rounded-lg">
                            <p class="text-red-600 dark:text-red-400 font-bold">${data.porcentajes.realizacion_generado}%</p>
                            <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase">Realizacion</p>
                        </div>
                        <div class="p-2 bg-gray-100 dark:bg-black/30 rounded-lg">
                            <p class="text-gray-600 dark:text-gray-400 font-bold">${data.porcentajes.deload_generado}%</p>
                            <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase">Deload</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Total: ${data.total_semanas} semanas</p>
                    ${mensajeAdvertencias}
                `
            });
            cargarTablaMacro();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// ============================================================
//  DETALLE
// ============================================================
async function verDetalle(id_macrociclo) {
    const detalle = await peticionAjax(`obtenerDetalle&id=${id_macrociclo}`);
    if (!detalle) { UI.error('Error', 'No se pudo obtener el detalle.'); return; }

    const contenedor = document.getElementById('detalleContenido');
    const totalSemanas = Math.round(detalle.total_semanas) || 0;
    const hoy = new Date().toISOString().split('T')[0];

    let html = `
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">${detalle.nombre || 'Macrociclo #' + detalle.id_macrociclo}</h2>
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${coloresEstado[detalle.estado] || 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400'}">${detalle.estado}</span>
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-cyan-50 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-500/30">${detalle.temporada_nombre}</span>
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-50 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30">${detalle.grupo_nombre}</span>
                ${detalle.evento_objetivo_nombre ? `<span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/30"><i class="fas fa-trophy mr-1"></i>${detalle.evento_objetivo_nombre}</span>` : ''}
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Inicio</p><p class="text-gray-900 dark:text-white font-mono text-sm">${formatoFecha(detalle.fecha_inicio)}</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Fin</p><p class="text-gray-900 dark:text-white font-mono text-sm">${formatoFecha(detalle.fecha_fin)}</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Duracion</p><p class="text-gray-900 dark:text-white text-sm">${totalSemanas} semanas</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Evento Objetivo</p><p class="text-gray-900 dark:text-white text-sm">${detalle.evento_objetivo_nombre || 'Sin asignar'}</p></div>
        </div>
    `;

    if (detalle.fases && detalle.fases.length > 0) {
        const timelineHtml = renderTimeline(detalle.fases, detalle.fecha_inicio, totalSemanas, hoy);
        html += `
            <div class="mb-8">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4"><i class="fas fa-chart-gantt mr-2 text-indigo-400"></i>Timeline de Periodizacion</h4>
                <div class="px-1 pb-6">
                    ${timelineHtml}
                </div>
            </div>
        `;

        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3"><i class="fas fa-layer-group mr-2 text-indigo-400"></i>Detalle de Fases</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                <th class="p-2">Fase</th>
                                <th class="p-2">Semanas</th>
                                <th class="p-2">Periodo</th>
                                <th class="p-2">Volumen %</th>
                                <th class="p-2">Intensidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                            ${detalle.fases.map(f => `
                                <tr class="${f.fecha_inicio <= hoy && f.fecha_fin >= hoy ? 'bg-gray-100 dark:bg-white/5' : ''}">
                                    <td class="p-2">${badgeFase(f.nombre_fase)}</td>
                                    <td class="p-2 font-mono">${f.semana_inicio} - ${f.semana_fin}</td>
                                    <td class="p-2 font-mono">${formatoFecha(f.fecha_inicio)} al ${formatoFecha(f.fecha_fin)}</td>
                                    <td class="p-2 font-mono text-center">${f.porcentaje_volumen}%</td>
                                    <td class="p-2 font-mono">${f.rango_intensidad}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (detalle.mesociclos && detalle.mesociclos.length > 0) {
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3"><i class="fas fa-cubes mr-2 text-amber-400"></i>Mesociclos</h4>
                <div class="space-y-3">
                    ${detalle.mesociclos.map(m => `
                        <div class="p-4 bg-gray-100 dark:bg-black/20 rounded-xl border-l-4 border-gray-300 dark:border-gray-600" style="border-color: ${m.fase_color || '#6366f1'}">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="text-gray-900 dark:text-white font-medium text-sm">${m.nombre || 'Mesociclo'}</span>
                                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold" style="background: ${m.fase_color}20; color: ${m.fase_color}">${m.nombre_fase}</span>
                                </div>
                                <div class="text-right text-[10px] text-gray-500 dark:text-gray-400">
                                    Sem ${m.semana_inicio || '-'} - ${m.semana_fin || '-'}
                                </div>
                            </div>
                            ${m.objetivo ? `<p class="text-xs text-gray-600 dark:text-gray-400 mb-2">${m.objetivo}</p>` : ''}
                            ${m.volumen_objetivo_m ? `<p class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-ruler-horizontal mr-1"></i>Volumen objetivo: ${Number(m.volumen_objetivo_m).toLocaleString()}m</p>` : ''}
                            ${m.microciclos && m.microciclos.length > 0 ? `
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-[#252345]">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-2">Microciclos (${m.microciclos.length})</p>
                                    <div class="flex flex-wrap gap-2">
                                        ${m.microciclos.map(mi => `
                                            <div class="p-2 bg-gray-100 dark:bg-[#0f0d23] rounded-lg text-[10px] border border-gray-200 dark:border-[#252345]">
                                                <span class="text-gray-600 dark:text-gray-400">S${mi.numero_semana}</span>
                                                <span class="text-gray-500 dark:text-gray-400 ml-1">${formatoFecha(mi.fecha_inicio)}</span>
                                                ${mi.volumen_planificado_m ? `<span class="text-indigo-600 dark:text-indigo-300 ml-1">${Number(mi.volumen_planificado_m).toLocaleString()}m</span>` : ''}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    if (!detalle.fases || detalle.fases.length === 0) {
        html += `
            <div class="text-center py-8">
                <i class="fas fa-calendar-alt text-4xl text-gray-400 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Este macrociclo aun no tiene plan de periodizacion generado.</p>
                <button onclick="generarPeriodizacion(${detalle.id_macrociclo}); cerrarModalVer();" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl transition cursor-pointer">
                    <i class="fas fa-magic mr-2"></i>Generar Plan ATR
                </button>
            </div>
        `;
    }

    contenedor.innerHTML = html;
    modalVer.style.display = 'flex';
    modalVer.classList.remove('hidden');
}

function cerrarModalVer() {
    modalVer.style.display = 'none';
    modalVer.classList.add('hidden');
    document.getElementById('detalleContenido').innerHTML = '';
}

// ============================================================
//  TIMELINE (CSS GRID)
// ============================================================
function renderTimeline(fases, fechaInicio, totalSemanas, hoy) {
    if (!fases || fases.length === 0 || totalSemanas === 0) return '';

    const semanasHtml = Array.from({ length: totalSemanas }, (_, i) => `<span>${i + 1}</span>`).join('');

    const barrasHtml = fases.map(f => {
        const leftPct = ((f.semana_inicio - 1) / totalSemanas) * 100;
        const widthPct = Math.max(((f.semana_fin - f.semana_inicio + 1) / totalSemanas) * 100, 2);
        const esActiva = f.fecha_inicio <= hoy && f.fecha_fin >= hoy;
        const color = coloresFase[f.nombre_fase] || coloresFase['Deload'];
        const numSemanas = f.semana_fin - f.semana_inicio + 1;

        return `
            <div class="timeline-bar ${esActiva ? 'fase-activa' : ''}"
                 style="left: ${leftPct}%; width: ${widthPct}%; background: ${f.color || color.bg}; color: white; opacity: 0.85;"
                 title="${f.nombre_fase}">
                ${widthPct > 8 ? f.nombre_fase : ''}
                <div class="tooltip-timeline">
                    <p class="font-bold text-gray-900 dark:text-white">${f.nombre_fase}</p>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Sem ${f.semana_inicio} - ${f.semana_fin} (${numSemanas} sem)</p>
                    <p class="text-gray-600 dark:text-gray-400">Volumen: ${f.porcentaje_volumen}% | Intensidad: ${f.rango_intensidad}</p>
                    <p class="text-gray-500 dark:text-gray-500 text-[10px]">${formatoFecha(f.fecha_inicio)} al ${formatoFecha(f.fecha_fin)}</p>
                    ${esActiva ? '<p class="text-emerald-600 dark:text-emerald-400 font-bold mt-1"><i class="fas fa-circle text-[8px] mr-1"></i>Fase Activa</p>' : ''}
                </div>
            </div>
        `;
    }).join('');

    return `
        <div class="relative">
            <div class="timeline-container">
                ${barrasHtml}
            </div>
            <div class="timeline-semanas">
                ${semanasHtml}
            </div>
        </div>
    `;
}

// ============================================================
//  ACCIONES DE ESTADO
// ============================================================
async function accionEstado(id_macrociclo, estadoActual) {
    let opciones = [];

    if (estadoActual === 'Planificado') {
        opciones = [
            { valor: 'En Progreso', icono: 'fa-play', color: 'yellow' },
            { valor: 'Finalizado', icono: 'fa-flag-checkered', color: 'green' }
        ];
    } else if (estadoActual === 'En Progreso') {
        opciones = [
            { valor: 'Finalizado', icono: 'fa-flag-checkered', color: 'green' },
            { valor: 'Planificado', icono: 'fa-redo', color: 'indigo' }
        ];
    } else if (estadoActual === 'Finalizado') {
        opciones = [
            { valor: 'Planificado', icono: 'fa-redo', color: 'indigo' }
        ];
    }

    if (opciones.length === 0) return;

    const botones = opciones.map(o => {
        const cls = {
            'yellow': 'bg-yellow-50 dark:bg-yellow-500/20 hover:bg-yellow-100 dark:hover:bg-yellow-500/30 text-yellow-600 dark:text-yellow-400',
            'green': 'bg-emerald-50 dark:bg-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/30 text-emerald-600 dark:text-emerald-400',
            'indigo': 'bg-indigo-50 dark:bg-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/30 text-indigo-600 dark:text-indigo-400'
        };
        return `<button class="p-3 ${cls[o.color]} rounded-xl transition cursor-pointer text-center" data-estado="${o.valor}">
            <i class="fas ${o.icono} text-lg mb-1 block"></i>
            <span class="text-[10px] font-bold uppercase">${o.valor}</span>
        </button>`;
    }).join('');

    let estadoSeleccionado = null;

    const { isConfirmed } = await Swal.fire({
        ...UI.obtenerConfig(), // <-- CAMBIADO
        title: 'Cambiar Estado',
        html: `<p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Estado actual: <span class="font-bold text-gray-900 dark:text-white">${estadoActual}</span></p><div class="grid grid-cols-${opciones.length} gap-3">${botones}</div>`,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            document.querySelectorAll('[data-estado]').forEach(btn => {
                btn.addEventListener('click', function() {
                    estadoSeleccionado = this.dataset.estado;
                    Swal.clickConfirm();
                });
            });
        }
    });

    if (!isConfirmed || !estadoSeleccionado) return;

    const datos = new URLSearchParams({
        accion: 'actualizarEstado',
        id_macrociclo: id_macrociclo,
        nuevo_estado: estadoSeleccionado
    });

    const resultado = await peticionAjax('actualizarEstado', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Estado Actualizado', resultado.message);
            cargarTablaMacro();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// ============================================================
//  UTILIDADES
// ============================================================
function badgeFase(nombreFase) {
    const color = coloresFase[nombreFase] || coloresFase['Deload'];
    return `<span class="fase-badge ${color.bgAlpha} ${color.text} ${color.border} border">
        <span style="display:inline-block;width:8px;height:8px;border-radius:3px;background:${color.bg}"></span>
        ${nombreFase}
    </span>`;
}

function mostrarErroresFormulario(errores) {
    Object.entries(errores).forEach(([campo, mensaje]) => {
        const input = document.getElementById(campo);
        if (input) {
            input.classList.add('border-red-500');
            const errorSpan = document.createElement('p');
            errorSpan.className = 'text-red-600 dark:text-red-400 text-[10px] mt-1';
            errorSpan.textContent = mensaje;
            input.parentElement.appendChild(errorSpan);
            setTimeout(() => {
                input.classList.remove('border-red-500');
                if (errorSpan.parentElement) errorSpan.remove();
            }, 4000);
        }
    });
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
        else if (!modalMacro.classList.contains('hidden')) cerrarModalMacro();
    }
});

// ============================================================
//  CARGA DE RECURSOS INICIALES
// ============================================================
async function cargarRecursos() {
    const [temporadas, grupos, eventos] = await Promise.all([
        peticionAjax('obtenerTemporadas'),
        peticionAjax('obtenerGrupos'),
        peticionAjax('obtenerEventosObjetivo')
    ]);

    if (temporadas) {
        temporadasCache = temporadas;
        const selTemp = document.getElementById('filtroTemporada');
        const selTempModal = document.getElementById('id_temporada');
        temporadas.forEach(t => {
            selTemp.innerHTML += `<option value="${t.id_temporada}">${t.nombre}</option>`;
            selTempModal.innerHTML += `<option value="${t.id_temporada}">${t.nombre} (${formatoFecha(t.fecha_inicio)})</option>`;
        });
    }

    if (grupos) {
        gruposCache = grupos;
        const selGrupo = document.getElementById('filtroGrupo');
        const selGrupoModal = document.getElementById('id_grupo');
        grupos.forEach(g => {
            selGrupo.innerHTML += `<option value="${g.id_grupo}">${g.nombre}</option>`;
            selGrupoModal.innerHTML += `<option value="${g.id_grupo}">${g.nombre}</option>`;
        });
    }

    if (eventos) {
        eventosCache = eventos;
        const selEvento = document.getElementById('id_evento_objetivo');
        eventos.forEach(ev => {
            selEvento.innerHTML += `<option value="${ev.id_evento}">${ev.nombre} (${formatoFecha(ev.fecha_inicio)})</option>`;
        });
    }
}

function validarFechasEnVivo() {
    const campoInicio = document.getElementById('fecha_inicio');
    const campoFin = document.getElementById('fecha_fin');
    if (!campoInicio || !campoFin) return;

    const inicio = campoInicio.value;
    const fin = campoFin.value;

    if (!inicio || !fin) {
        campoInicio.style.borderColor = inicio ? '#34d399' : '';
        campoFin.style.borderColor = fin ? '#34d399' : '';
        Validador.ocultarAyuda(campoFin);
        return;
    }

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const haceUnAnio = new Date(hoy);
    haceUnAnio.setFullYear(hoy.getFullYear() - 1);

    let errorInicio = '';
    let errorFin = '';

    if (new Date(inicio) < haceUnAnio) {
        errorInicio = 'No puede ser anterior a un año atrás';
    }

    if (fin <= inicio) {
        errorFin = 'Debe ser posterior a la fecha de inicio';
    } else {
        const diffDias = (new Date(fin) - new Date(inicio)) / 86400000;
        if (diffDias < 21) {
            errorFin = 'Mínimo 3 semanas (21 días) de duración';
        }
    }

    campoInicio.style.borderColor = errorInicio ? '#f87171' : '#34d399';
    campoFin.style.borderColor = errorFin ? '#f87171' : '#34d399';

    if (errorInicio) {
        Validador.mostrarAyuda(campoInicio, errorInicio, 'error');
    } else {
        Validador.mostrarAyuda(campoInicio, 'Campo válido', 'ok');
    }

    if (errorFin) {
        Validador.mostrarAyuda(campoFin, errorFin, 'error');
    } else {
        Validador.mostrarAyuda(campoFin, 'Campo válido', 'ok');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formMacro); } catch (e) {}

    const campoInicio = document.getElementById('fecha_inicio');
    const campoFin = document.getElementById('fecha_fin');
    if (campoInicio) campoInicio.addEventListener('change', validarFechasEnVivo);
    if (campoFin) campoFin.addEventListener('change', validarFechasEnVivo);
});

cargarRecursos().then(() => cargarTablaMacro());
