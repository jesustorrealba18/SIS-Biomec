const modalSesion = document.getElementById('modalSesion');
const modalVerSesion = document.getElementById('modalVer');
const modalCompletarSesion = document.getElementById('modalCompletar');
const formSesion = document.getElementById('formSesion');
const formCompletarSesion = document.getElementById('formCompletar');

let gruposCache = [];       
let microciclosCache = [];   
let drillsCache = [];        

const coloresEstado = {
    'Planificada': 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30',
    'Completada':  'bg-green-500/20 text-green-400 border border-green-500/30',
    'Parcial':     'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
    'Cancelada':   'bg-red-500/20 text-red-400 border border-red-500/30'
};

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        return null;
    }
}

async function cargarTablaSesiones() {
    const filtroGrupo = document.getElementById('filtroGrupo').value || '';
    const filtroTipo = document.getElementById('filtroTipoSesion').value || '';

    const datos = await peticionAjax(`listarSesiones&id_grupo=${filtroGrupo}&tipo_sesion=${filtroTipo}`);
    const tbody = document.getElementById('tbodySesiones');

    if (!tbody) return;

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-gray-500">No se encontraron sesiones de entrenamiento.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(s => `
        <tr class="hover:bg-white/5 transition border-b border-[#252345]">
            <td class="p-4">
                <p class="text-white font-medium">${s.fecha}</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Duración: ${s.duracion_minutos || 0} min</p>
            </td>
            <td class="p-4">
                <p class="text-white font-medium">${s.grupo_nombre}</p>
                <p class="text-[10px] text-gray-400">Microciclo: ${s.microciclo_nombre || 'Ninguno'}</p>
            </td>
            <td class="p-4">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-blue-500/10 text-blue-400">
                    ${s.tipo_sesion}
                </span>
                <br>
                <span class="${coloresEstado[s.estado]} px-2 py-0.5 rounded-full text-[10px] inline-block mt-1">
                    ${s.estado}
                </span>
            </td>
            <td class="p-4 text-center font-mono text-indigo-400 font-bold">${s.volumen_planificado || 0}m</td>
            <td class="p-4 text-center font-mono text-emerald-400 font-bold">${s.volumen_ejecutado || 0}m</td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="verDetalleSesion(${s.id_sesion})" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 rounded-lg transition" title="Ver Detalles">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    ${s.estado === 'Planificada' ? `
                        <button onclick="abrirModalCompletarSesion(${s.id_sesion})" class="p-2 text-green-400 hover:text-green-300 hover:bg-green-500/10 rounded-lg transition" title="Completar">
                            <i class="fas fa-check-circle text-sm"></i>
                        </button>
                        <button onclick="abrirModalSesion(${s.id_sesion})" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition" title="Editar">
                            <i class="fas fa-pen text-sm"></i>
                        </button>
                        <button onclick="cancelarSesion(${s.id_sesion})" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition" title="Cancelar">
                            <i class="fas fa-ban text-sm"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalSesion(id_sesion = null) {
    if (!formSesion || !modalSesion) return;
    formSesion.reset();
    document.getElementById('id_sesion').value = '';
    document.getElementById('tbodySeries').innerHTML = '';
    
    const inputFecha = document.getElementById('fecha');
    const hoy = new Date().toISOString().split('T')[0];
    if (inputFecha) inputFecha.min = hoy; 

    if (id_sesion) {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Sesión';
        peticionAjax(`obtenerDetalle&id=${id_sesion}`).then(det => {
            if (!det) return;
            document.getElementById('id_sesion').value = det.id_sesion;
            document.getElementById('id_grupo').value = det.id_grupo || '';
            document.getElementById('id_microciclo').value = det.id_microciclo || '';
            document.getElementById('fecha').value = det.fecha || '';
            document.getElementById('tipo_sesion').value = det.tipo_sesion || 'Tecnica';
            document.getElementById('calentamiento').value = det.calentamiento || '';
            document.getElementById('vuelta_calma').value = det.vuelta_calma || '';
            document.getElementById('observaciones').value = det.observaciones || '';
            document.getElementById('duracion_minutos').value = det.duracion_minutos || '';

            if (det.series && det.series.length > 0) {
                det.series.forEach(serie => agregarFilaSerie(serie));
            }
            calcularVolumenTotalSesion();
        });
    } else {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-calendar-plus text-indigo-400"></i> Planificar Sesión';
        if (inputFecha) inputFecha.value = hoy;
        agregarFilaSerie();
    }

    modalSesion.classList.remove('hidden');
    setTimeout(() => modalSesion.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalSesion() {
    if (!modalSesion) return;
    modalSesion.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modalSesion.classList.add('hidden'), 300);
}

function agregarFilaSerie(datos = null) {
    const contenedor = document.getElementById('tbodySeries');
    if (!contenedor) return;
    const filasExistentes = contenedor.querySelectorAll('.fila-serie').length;
    const nuevoOrden = filasExistentes + 1; 

    const tr = document.createElement('tr');
    tr.className = 'fila-serie hover:bg-white/5 transition';

    const opcionesDrills = `<option value="">-- Ejercicio Libre --</option>` + 
        drillsCache.map(d => `<option value="${d.id_drill}" ${datos && datos.id_drill == d.id_drill ? 'selected' : ''}>${d.nombre}</option>`).join('');

    tr.innerHTML = `
        <input type="hidden" name="serie_orden_ejecucion[]" value="${datos ? datos.orden_ejecucion : nuevoOrden}" class="serie-orden">
        
        <td class="p-2">
            <select name="serie_bloque[]" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white bloque-select" onchange="calcularVolumenTotalSesion()">
                <option value="Calentamiento" ${datos && datos.bloque === 'Calentamiento' ? 'selected' : ''}>Calentamiento</option>
                <option value="Principal" ${datos && datos.bloque === 'Principal' ? 'selected' : (!datos && filasExistentes === 1 ? 'selected' : '')}>Principal</option>
                <option value="VueltaCalma" ${datos && datos.bloque === 'VueltaCalma' ? 'selected' : (!datos && filasExistentes === 2 ? 'selected' : '')}>Vuelta Calma</option>
            </select>
        </td>

        <td class="p-2">
            <select name="serie_id_drill[]" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white w-full drill-select" onchange="alternarCampoDescripcion(this)">
                ${opcionesDrills}
            </select>
            <input type="text" name="serie_ejercicio_descripcion[]" value="${datos ? datos.ejercicio_descripcion || '' : ''}" placeholder="Descripción libre..." class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white w-full mt-1 desc-input">
        </td>

        <td class="p-2">
            <input type="text" name="serie_ritmo_objetivo[]" value="${datos ? datos.ritmo_objetivo || '' : ''}" placeholder="Ej: 1:30" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-full">
        </td>

        <td class="p-2 text-center">
            <input type="number" min="1" name="serie_repeticiones[]" value="${datos ? datos.repeticiones : '1'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-14 rep-input" oninput="calcularVolumenSerie(this)">
        </td>

        <td class="p-2 text-center">
            <input type="number" min="0" step="25" name="serie_distancia_m[]" value="${datos ? datos.distancia_m : '50'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-16 dist-input" oninput="calcularVolumenSerie(this)">
        </td>

        <td class="p-2 text-center">
            <input type="number" min="0" name="serie_descanso_seg[]" value="${datos ? datos.descanso_seg : '15'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-14">
        </td>

        <td class="p-2">
            <select name="serie_zona_intensidad[]" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white">
                <option value="Z1" ${datos && datos.zona_intensidad === 'Z1' ? 'selected' : ''}>Z1</option>
                <option value="Z2" ${datos && datos.zona_intensidad === 'Z2' ? 'selected' : ''}>Z2</option>
                <option value="Z3" ${datos && datos.zona_intensidad === 'Z3' ? 'selected' : ''}>Z3</option>
                <option value="Z4" ${datos && datos.zona_intensidad === 'Z4' ? 'selected' : ''}>Z4</option>
                <option value="Z5" ${datos && datos.zona_intensidad === 'Z5' ? 'selected' : ''}>Z5</option>
            </select>
        </td>

        <td class="p-2 text-center font-mono font-bold text-indigo-400 vol-serie-badge">0m</td>

        <td class="p-2 text-right">
            <button type="button" onclick="removerFilaSerie(this)" class="text-red-400 hover:text-red-300 transition">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;

    contenedor.appendChild(tr);
    alternarCampoDescripcion(tr.querySelector('.drill-select'));
    calcularVolumenSerie(tr.querySelector('.rep-input'));
}

function removerFilaSerie(boton) {
    const contenedor = document.getElementById('tbodySeries');
    boton.closest('.fila-serie').remove();
    if (contenedor) {
        contenedor.querySelectorAll('.fila-serie').forEach((fila, index) => {
            fila.querySelector('.serie-orden').value = index + 1;
        });
    }
    calcularVolumenTotalSesion();
}

function alternarCampoDescripcion(selectElement) {
    const fila = selectElement.closest('.fila-serie');
    const inputDesc = fila.querySelector('.desc-input');
    if (selectElement.value !== "") {
        inputDesc.disabled = true;
        inputDesc.classList.add('opacity-40');
    } else {
        inputDesc.disabled = false;
        inputDesc.classList.remove('opacity-40');
    }
}

function calcularVolumenSerie(input) {
    const fila = input.closest('.fila-serie');
    const repeticiones = parseInt(fila.querySelector('.rep-input').value) || 0;
    const distancia = parseInt(fila.querySelector('.dist-input').value) || 0;
    
    const volumenSerie = repeticiones * distancia; 
    fila.querySelector('.vol-serie-badge').textContent = `${volumenSerie}m`;
    
    calcularVolumenTotalSesion();
}

function calcularVolumenTotalSesion() {
    let volCalentamiento = 0; 
    let volPrincipal = 0;    
    let volVueltaCalma = 0; 

    document.querySelectorAll('.fila-serie').forEach(fila => {
        const bloque = fila.querySelector('.bloque-select').value;
        const repeticiones = parseInt(fila.querySelector('.rep-input').value) || 0;
        const distancia = parseInt(fila.querySelector('.dist-input').value) || 0;
        const subtotal = repeticiones * distancia;

        if (bloque === 'Calentamiento') volCalentamiento += subtotal; 
        else if (bloque === 'Principal') volPrincipal += subtotal;    
        else if (bloque === 'VueltaCalma') volVueltaCalma += subtotal; 
    });

    const volTotal = volCalentamiento + volPrincipal + volVueltaCalma; 

    if(document.getElementById('lblVolCalentamiento')) document.getElementById('lblVolCalentamiento').textContent = `${volCalentamiento}m`;
    if(document.getElementById('lblVolPrincipal')) document.getElementById('lblVolPrincipal').textContent = `${volPrincipal}m`;
    if(document.getElementById('lblVolVueltaCalma')) document.getElementById('lblVolVueltaCalma').textContent = `${volVueltaCalma}m`;
    if(document.getElementById('lblVolTotalPlanificado')) {
        document.getElementById('lblVolTotalPlanificado').textContent = `${volTotal}m`;
        document.getElementById('volumen_planificado').value = volTotal;
    }
}

if (formSesion) {
    formSesion.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id_grupo = document.getElementById('id_grupo').value;
        if (!id_grupo) { 
            Swal.fire('Validación', 'Debe seleccionar un grupo obligatoriamente.', 'warning');
            return;
        }

        const id_sesion = document.getElementById('id_sesion').value;
        const formData = new FormData(formSesion);
        const series = [];
        
        document.querySelectorAll('.fila-serie').forEach(f => {
            series.push({
                orden_ejecucion: f.querySelector('.serie-orden').value,
                bloque: f.querySelector('.bloque-select').value,
                id_drill: f.querySelector('.drill-select').value || null,
                ejercicio_descripcion: f.querySelector('.desc-input').value || null,
                repeticiones: f.querySelector('.rep-input').value,
                distancia_m: f.querySelector('.dist-input').value,
                descanso_seg: f.querySelectorAll('input[type="number"]')[2].value, 
                zona_intensidad: f.querySelector('.fila-serie select[name="serie_zona_intensidad[]"]').value,
                ritmo_objetivo: f.querySelector('input[name="serie_ritmo_objetivo[]"]').value || null
            });
        });

        formData.append('series', JSON.stringify(series));
        const accion = id_sesion ? 'editar' : 'guardar';

        const resultado = await peticionAjax(accion, formData);
        if (resultado && resultado.status === 'success') {
            Swal.fire('Correcto', resultado.message, 'success');
            cerrarModalSesion();
            cargarTablaSesiones();
        } else {
            Swal.fire('Error', resultado?.message || 'No se pudo procesar', 'error');
        }
    });
}

async function verDetalleSesion(id_sesion) {
    const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!det) { Swal.fire('Error', 'No se pudieron recuperar los detalles.', 'error'); return; }

    const contenedor = document.getElementById('detalleContenido'); 
    if(!contenedor) return;

    contenedor.innerHTML = `
        <div class="mb-4 border-b border-white/10 pb-4">
            <h3 class="text-xl font-bold text-white">${det.grupo_nombre}</h3>
            <p class="text-xs text-gray-400">Fecha: <span class="text-white font-mono">${det.fecha}</span> | Tipo: <span class="text-indigo-400">${det.tipo_sesion}</span></p>
            <p class="text-xs text-gray-400 mt-1">Estado: <span class="${coloresEstado[det.estado]} px-2 py-0.5 rounded text-[10px] font-bold">${det.estado}</span></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 text-center text-xs">
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Calentamiento</p><p class="text-sm font-bold text-gray-300">${det.volumen_calentamiento || 0}m</p></div>
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Bloque Principal</p><p class="text-sm font-bold text-indigo-400">${det.volumen_principal || 0}m</p></div>
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Vuelta a la Calma</p><p class="text-sm font-bold text-emerald-400">${det.volumen_vuelta_calma || 0}m</p></div>
        </div>
        <div class="space-y-1 text-xs bg-black/10 p-3 rounded-xl border border-white/5 mb-4 text-gray-300">
            <p><strong>Calentamiento:</strong> ${det.calentamiento || 'Ninguno'}</p>
            <p><strong>Vuelta a la Calma:</strong> ${det.vuelta_calma || 'Ninguno'}</p>
            <p><strong>Observaciones:</strong> ${det.observaciones || 'Ninguna'}</p>
        </div>
    `;
    modalVerSesion.classList.remove('hidden');
}

function cerrarModalVer() {
    if(modalVerSesion) modalVerSesion.classList.add('hidden');
}

async function abrirModalCompletarSesion(id_sesion) {
    if(!formCompletarSesion) return;
    formCompletarSesion.reset();
    
    const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!det) return;
 
    document.getElementById('id_sesion_completar').value = det.id_sesion;
    document.getElementById('compFecha').textContent = det.fecha;
    document.getElementById('compTipo').textContent = det.tipo_sesion;
    document.getElementById('compGrupo').textContent = det.grupo_nombre;
    document.getElementById('compVolPlanificado').textContent = `${det.volumen_planificado} metros`;
    
    document.getElementById('volumen_ejecutado').value = det.volumen_planificado;
    document.getElementById('observaciones_completar').value = det.observaciones || '';

    modalCompletarSesion.classList.remove('hidden');
    setTimeout(() => modalCompletarSesion.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalCompletar() {
    if(!modalCompletarSesion) return;
    modalCompletarSesion.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modalCompletarSesion.classList.add('hidden'), 300);
}

if(formCompletarSesion) {
    formCompletarSesion.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(formCompletarSesion);

        const resultado = await peticionAjax('completarSesion', formData);
        if (resultado && resultado.status === 'success') {
            Swal.fire('Sesión Cerrada', resultado.message, 'success');
            cerrarModalCompletar();
            cargarTablaSesiones();
        } else {
            Swal.fire('Error', resultado?.message || 'No se pudo guardar', 'error');
        }
    });
}

async function cancelarSesion(id_sesion) {
    const result = await Swal.fire({
        title: '¿Cancelar Sesión?',
        text: 'La sesión cambiará su estado a "Cancelada".',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Sí, cancelar'
    });

    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('id_sesion', id_sesion);

    const resultado = await peticionAjax('cancelarSesion', formData);
    if (resultado && resultado.status === 'success') {
        Swal.fire('Cancelada', 'La planificación ha sido anulada.', 'success'); 
        cargarTablaSesiones();
    } else {
        Swal.fire('Error', 'No se pudo anular la sesión.', 'error');
    }
}

async function cargarRecursosIniciales() {
    const [grupos, microciclos, drills] = await Promise.all([
        peticionAjax('listarGruposEntrenador'),
        peticionAjax('listarMicrociclos'),
        peticionAjax('listarDrillsActivos')
    ]);

    if (grupos) {
        gruposCache = grupos;
        const selectGrupoForm = document.getElementById('id_grupo');
        const selectGrupoFiltro = document.getElementById('filtroGrupo');
        if (selectGrupoForm) {
            selectGrupoForm.innerHTML = '<option value="">-- Seleccione un Grupo --</option>' +
                grupos.map(g => `<option value="${g.id_grupo}">${g.nombre}</option>`).join('');
        }
        if (selectGrupoFiltro) {
            selectGrupoFiltro.innerHTML = '<option value="">Todos los Grupos</option>' +
                grupos.map(g => `<option value="${g.id_grupo}">${g.nombre}</option>`).join('');
        }
    }
    if (microciclos) {
        microciclosCache = microciclos;
        const selectMicroForm = document.getElementById('id_microciclo');
        if (selectMicroForm) {
            selectMicroForm.innerHTML = '<option value="">-- Sesión Suelta (Ninguno) --</option>' +
                microciclos.map(m => `<option value="${m.id_microciclo}">${m.nombre}</option>`).join('');
        }
    }
    if (drills) drillsCache = drills;
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalSesion();
        cerrarModalVer();
        cerrarModalCompletar();
    }
});

cargarRecursosIniciales().then(() => cargarTablaSesiones());