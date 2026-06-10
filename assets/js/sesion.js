const API_URL = 'index.php?p=sesiones';

const modalSesion = document.getElementById('modalSesion');
const modalVerSesion = document.getElementById('modalVerSesion');
const modalCompletarSesion = document.getElementById('modalCompletarSesion');
const formSesion = document.getElementById('formSesion');
const formCompletarSesion = document.getElementById('formCompletarSesion');

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
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud de sesiones.');
        return null;
    }
}

async function cargarTablaSesiones() {
    const filtroGrupo = document.getElementById('filtroGrupo').value || '';
    const filtroEstado = document.getElementById('filtroEstado').value || '';

    const datos = await peticionAjax(`listarSesiones&id_grupo=${filtroGrupo}&estado=${filtroEstado}`);
    const tbody = document.getElementById('tbodySesiones');

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-500">No se encontraron sesiones de entrenamiento.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(s => `
        <tr class="hover:bg-white/5 transition border-b border-[#252345]">
            <td class="p-4">
                <p class="text-white font-medium">${s.grupo_nombre}</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Microciclo: ${s.microciclo_nombre || 'Sesión Suelta'}</p>
            </td>
            <td class="p-4 font-mono text-sm text-gray-300">${s.fecha}</td>
            <td class="p-4">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-blue-500/10 text-blue-400">
                    ${s.tipo_sesion}
                </span>
            </td>
            <td class="p-4 font-mono text-xs text-gray-400">
                Planificado: <span class="text-indigo-400 font-bold">${s.volumen_planificado || 0}m</span>
                ${s.volumen_ejecutado ? `<br><span class="text-emerald-400">Ejecutado: ${s.volumen_ejecutado}m</span>` : ''}
            </td>
            <td class="p-4">
                <span class="${coloresEstado[s.estado]} px-2 py-1 rounded-full text-xs font-medium">
                    ${s.estado}
                </span>
            </td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="verDetalleSesion(${s.id_sesion})" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 rounded-lg transition cursor-pointer" title="Ver Detalles y Series">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    
                    ${s.estado === 'Planificada' ? `
                        <button onclick="abrirModalCompletarSesion(${s.id_sesion})" class="p-2 text-green-400 hover:text-green-300 hover:bg-green-500/10 rounded-lg transition cursor-pointer" title="Completar Sesión">
                            <i class="fas fa-check-circle text-sm"></i>
                        </button>
                        <button onclick="abrirModalSesion(${s.id_sesion})" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer" title="Editar">
                            <i class="fas fa-pen text-sm"></i>
                        </button>
                        <button onclick="cancelarSesion(${s.id_sesion})" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition cursor-pointer" title="Cancelar Sesión">
                            <i class="fas fa-ban text-sm"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalSesion(id_sesion = null) {
    formSesion.reset();
    document.getElementById('id_sesion').value = '';
    document.getElementById('contenedorSeries').innerHTML = '';
    
    const inputFecha = document.getElementById('fecha');
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.min = hoy; 

    if (id_sesion) {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Sesión de Entrenamiento';
        peticionAjax(`obtenerDetalle&id=${id_sesion}`).then(det => {
            if (!det) return;
            document.getElementById('id_sesion').value = det.id_sesion;
            document.getElementById('id_grupo').value = det.id_grupo || '';
            document.getElementById('id_microciclo').value = det.id_microciclo || '';
            document.getElementById('fecha').value = det.fecha || '';
            document.getElementById('tipo_sesion').value = det.tipo_sesion || 'Tecnica';
            document.getElementById('id_fase_actual').value = det.id_fase_actual || '';
            document.getElementById('calentamiento').value = det.calentamiento || '';
            document.getElementById('vuelta_calma').value = det.vuelta_calma || '';
            document.getElementById('observaciones').value = det.observaciones || '';

            if (det.series && det.series.length > 0) {
                det.series.forEach(serie => agregarFilaSerie(serie));
            }
            calcularVolumenTotalSesion();
        });
    } else {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-swimmer text-emerald-400"></i> Diseñar Sesión de Entrenamiento';
        inputFecha.value = hoy;
        agregarFilaSerie();
    }

    modalSesion.classList.remove('hidden');
    setTimeout(() => modalSesion.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalSesion() {
    modalSesion.classList.add('hidden');
    modalSesion.firstElementChild.classList.add('scale-95', 'opacity-0');
}

function agregarFilaSerie(datos = null) {
    const contenedor = document.getElementById('contenedorSeries');
    const filasExistentes = contenedor.querySelectorAll('.fila-serie').length;
    const nuevoOrden = filasExistentes + 1; 

    const div = document.createElement('div');
    div.className = 'fila-serie bg-black/10 p-3 rounded-xl border border-[#252345] grid grid-cols-1 md:grid-cols-9 gap-2 items-center mb-2';

    const opcionesDrills = `<option value="">-- Ejercicio Libre --</option>` + 
        drillsCache.map(d => `<option value="${d.id_drill}" ${datos && datos.id_drill == d.id_drill ? 'selected' : ''}>${d.nombre}</option>`).join('');

    div.innerHTML = `
        <input type="hidden" name="serie_orden_ejecucion[]" value="${datos ? datos.orden_ejecucion : nuevoOrden}" class="serie-orden">
        
        <div class="md:col-span-1">
            <select name="serie_bloque[]" class="input-dark p-2 rounded-lg text-xs w-full bloque-select" onchange="calcularVolumenTotalSesion()">
                <option value="Calentamiento" ${datos && datos.bloque === 'Calentamiento' ? 'selected' : ''}>Calentamiento</option>
                <option value="Principal" ${datos && datos.bloque === 'Principal' ? 'selected' : ( !datos && filasExistentes === 1 ? 'selected' : '' )}>Principal</option>
                <option value="VueltaCalma" ${datos && datos.bloque === 'VueltaCalma' ? 'selected' : ( !datos && filasExistentes === 2 ? 'selected' : '' )}>Vuelta Calma</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <select name="serie_id_drill[]" class="input-dark p-2 rounded-lg text-xs w-full drill-select" onchange="alternarCampoDescripcion(this)">
                ${opcionesDrills}
            </select>
        </div>

        <div class="md:col-span-2 contenedor-descripcion">
            <input type="text" name="serie_ejercicio_descripcion[]" value="${datos ? datos.ejercicio_descripcion || '' : ''}" placeholder="Descripción del ejercicio..." class="input-dark p-2 rounded-lg text-xs w-full desc-input">
        </div>

        <div class="md:col-span-1">
            <input type="number" min="1" name="serie_repeticiones[]" value="${datos ? datos.repeticiones : '1'}" class="input-dark p-2 rounded-lg text-xs w-full text-center font-mono rep-input" oninput="calcularVolumenSerie(this)">
        </div>

        <div class="md:col-span-1">
            <input type="number" min="25" step="25" name="serie_distancia_m[]" value="${datos ? datos.distancia_m : '50'}" class="input-dark p-2 rounded-lg text-xs w-full text-center font-mono dist-input" oninput="calcularVolumenSerie(this)">
        </div>

        <div class="md:col-span-1 flex flex-col items-center justify-center">
            <select name="serie_zona_intensidad[]" class="input-dark p-1 rounded-lg text-[10px] w-full mb-1">
                <option value="Z1" ${datos && datos.zona_intensidad === 'Z1' ? 'selected' : ''}>Z1</option>
                <option value="Z2" ${datos && datos.zona_intensidad === 'Z2' ? 'selected' : ''}>Z2</option>
                <option value="Z3" ${datos && datos.zona_intensidad === 'Z3' ? 'selected' : ''}>Z3</option>
                <option value="Z4" ${datos && datos.zona_intensidad === 'Z4' ? 'selected' : ''}>Z4</option>
                <option value="Z5" ${datos && datos.zona_intensidad === 'Z5' ? 'selected' : ''}>Z5</option>
            </select>
            <span class="text-[10px] font-mono font-bold text-indigo-400 vol-serie-badge">Subtotal: 0m</span>
        </div>

        <div class="md:col-span-1 flex items-center gap-1">
            <div class="w-full">
                <input type="number" min="0" name="serie_descanso_seg[]" value="${datos ? datos.descanso_seg : '15'}" placeholder="Seg" class="input-dark p-1 rounded-lg text-[10px] w-full text-center font-mono" title="Descanso (segundos)">
                <input type="text" name="serie_ritmo_objetivo[]" value="${datos ? datos.ritmo_objetivo || '' : ''}" placeholder="Ritmo" class="input-dark p-1 rounded-lg text-[10px] w-full text-center mt-1 font-mono" title="Ritmo objetivo (ej: 1:30/100m)">
            </div>
            <button type="button" onclick="removerFilaSerie(this)" class="text-red-400 hover:text-red-300 p-1 transition cursor-pointer">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </div>
    `;

    contenedor.appendChild(div);
    
    const selectDrill = div.querySelector('.drill-select');
    alternarCampoDescripcion(selectDrill);
    calcularVolumenSerie(div.querySelector('.rep-input'));
}

function removerFilaSerie(boton) {
    const contenedor = document.getElementById('contenedorSeries');
    boton.closest('.fila-serie').remove();
    
    contenedor.querySelectorAll('.fila-serie').forEach((fila, index) => {
        fila.querySelector('.serie-orden').value = index + 1;
    });
    calcularVolumenTotalSesion();
}

function alternarCampoDescripcion(selectElement) {
    const fila = selectElement.closest('.fila-serie');
    const inputDesc = fila.querySelector('.desc-input');
    if (selectElement.value !== "") {
        inputDesc.disabled = true;
        inputDesc.classList.add('opacity-40');
        inputDesc.placeholder = "Usando Drill del catálogo";
    } else {
        inputDesc.disabled = false;
        inputDesc.classList.remove('opacity-40');
        inputDesc.placeholder = "Descripción del ejercicio...";
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

    document.getElementById('badgeVolCalentamiento').textContent = `${volCalentamiento}m`;
    document.getElementById('badgeVolPrincipal').textContent = `${volPrincipal}m`;
    document.getElementById('badgeVolVueltaCalma').textContent = `${volVueltaCalma}m`;
    document.getElementById('badgeVolTotal').textContent = `${volTotal}m`;
}

formSesion.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id_grupo = document.getElementById('id_grupo').value;
    if (!id_grupo) { 
        UI.advertencia('Validación', 'No se permitirán sesiones sin grupo asignado.');
        return;
    }

    const id_sesion = document.getElementById('id_sesion').value;
    const formData = new FormData(formSesion);

    const series = [];
    const filas = document.querySelectorAll('.fila-serie');
    
    filas.forEach(f => {
        series.push({
            orden_ejecucion: f.querySelector('.serie-orden').value,
            bloque: f.querySelector('.bloque-select').value,
            id_drill: f.querySelector('.drill-select').value || null,
            ejercicio_descripcion: f.querySelector('.desc-input').value || null,
            repeticiones: f.querySelector('.rep-input').value,
            distancia_m: f.querySelector('.dist-input').value,
            descanso_seg: f.querySelector('[name="serie_descanso_seg[]"]').value,
            zona_intensidad: f.querySelector('[name="serie_zona_intensidad[]"]').value,
            ritmo_objetivo: f.querySelector('[name="serie_ritmo_objetivo[]"]').value || null
        });
    });

    formData.append('series', JSON.stringify(series));

    const accion = id_sesion ? 'editar' : 'guardar';
    if (id_sesion) formData.append('id_sesion', id_sesion);

    const resultado = await peticionAjax(accion, formData);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Correcto', resultado.message);
            cerrarModalSesion();
            cargarTablaSesiones();
        } else {
            UI.error('Error', resultado.message);
        }
    }
});

async function verDetalleSesion(id_sesion) {
    const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!det) { UI.error('Error', 'No se pudieron recuperar los detalles de la sesión.'); return; }

    const contenedor = document.getElementById('detalleSesionContenido'); 

    /* NOTA: Tu backend PHP debe calcular al vuelo e incluir los índices
       'volumen_calentamiento', 'volumen_principal' y 'volumen_vuelta_calma' en el JSON */
    let html = `
        <div class="mb-4 border-b border-[#252345] pb-4">
            <h3 class="text-lg font-bold text-white">${det.grupo_nombre}</h3>
            <p class="text-xs text-gray-400">Fecha: <span class="text-white font-mono">${det.fecha}</span> | Tipo: <span class="text-blue-400">${det.tipo_sesion}</span></p>
            <p class="text-xs text-gray-400 mt-1">Estado de Planificación: <span class="${coloresEstado[det.estado]} px-2 py-0.5 rounded text-[10px] font-bold">${det.estado}</span></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 text-center">
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-[10px] text-gray-500 uppercase">Calentamiento</p><p class="text-sm font-bold text-gray-300">${det.volumen_calentamiento || 0}m</p></div>
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-[10px] text-gray-500 uppercase">Bloque Principal</p><p class="text-sm font-bold text-indigo-400">${det.volumen_principal || 0}m</p></div>
            <div class="bg-black/20 p-2 rounded-lg"><p class="text-[10px] text-gray-500 uppercase">Vuelta a la Calma</p><p class="text-sm font-bold text-emerald-400">${det.volumen_vuelta_calma || 0}m</p></div>
        </div>

        <div class="mb-4 bg-black/10 p-3 rounded-xl border border-[#252345]">
            <p class="text-xs text-indigo-300 font-semibold mb-1"><i class="fas fa-bullhorn mr-1"></i> Indicaciones de Cabecera</p>
            <p class="text-xs text-gray-300"><strong>Calentamiento general:</strong> ${det.calentamiento || 'Ninguno'}</p>
            <p class="text-xs text-gray-300 mt-1"><strong>Vuelta a la calma general:</strong> ${det.vuelta_calma || 'Ninguno'}</p>
        </div>

        <h4 class="text-sm font-bold text-white mb-2"><i class="fas fa-list-ol text-gray-400 mr-1"></i> Series de Nado Planificadas</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-[#252345]">
                <thead>
                    <tr class="bg-black/30 text-gray-400 uppercase tracking-wider text-[10px]">
                        <th class="p-2">#</th><th class="p-2">Bloque</th><th class="p-2">Ejercicio / Drill</th><th class="p-2 text-center">Estructura</th><th class="p-2 text-center">Volumen</th><th class="p-2 text-center">Intensidad</th><th class="p-2">Descanso / Ritmo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#252345] text-gray-300">
                    ${det.series.map(ser => `
                        <tr class="hover:bg-white/5">
                            <td class="p-2 font-mono text-gray-500">${ser.orden_ejecucion}</td>
                            <td class="p-2"><span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-white/10">${ser.bloque}</span></td>
                            <td class="p-2">
                                <span class="text-white font-medium">${ser.drill_nombre || 'Ejercicio Libre'}</span>
                                ${ser.ejercicio_descripcion ? `<br><span class="text-gray-500 text-[10px]">${ser.ejercicio_descripcion}</span>` : ''}
                            </td>
                            <td class="p-2 text-center font-mono">${ser.repeticiones} x ${ser.distancia_m}m</td>
                            <td class="p-2 text-center font-mono text-indigo-400 font-bold">${ser.repeticiones * ser.distancia_m}m</td>
                            <td class="p-2 text-center"><span class="text-amber-400 font-bold">${ser.zona_intensidad}</span></td>
                            <td class="p-2 font-mono text-[11px]">${ser.descanso_seg}s ${ser.ritmo_objetivo ? `| @ ${ser.ritmo_objetivo}` : ''}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    contenedor.innerHTML = html; 
    modalVerSesion.classList.remove('hidden');
}

function cerrarModalVerSesion() {
    modalVerSesion.classList.add('hidden');
}

async function abrirModalCompletarSesion(id_sesion) {
    formCompletarSesion.reset();
    
    const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!det) return;
 
    document.getElementById('comp_id_sesion').value = det.id_sesion;
    document.getElementById('comp_txt_fecha').textContent = det.fecha;
    document.getElementById('comp_txt_tipo').textContent = det.tipo_sesion;
    document.getElementById('comp_txt_grupo').textContent = det.grupo_nombre;
    document.getElementById('comp_txt_vol_planificado').textContent = `${det.volumen_planificado} metros`;
    
    document.getElementById('volumen_ejecutado').value = det.volumen_planificado;
    document.getElementById('comp_observaciones').value = det.observaciones || '';

    modalCompletarSesion.classList.remove('hidden');
}

function cerrarModalCompletarSesion() {
    modalCompletarSesion.classList.add('hidden');
}

formCompletarSesion.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(formCompletarSesion);
    formData.append('accion', 'completarSesion'); 

    const resultado = await peticionAjax('completarSesion', formData);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Sesión Cerrada', resultado.message);
            cerrarModalCompletarSesion();
            cargarTablaSesiones();
        } else {
            UI.error('Error', resultado.message);
        }
    }
});

async function cancelarSesion(id_sesion) {
    const { value: confirmado } = await UI.confirmar(
        '¿Cancelar Sesión?', 
        'La sesión cambiará su estado a "Cancelada".'
    );
    if (!confirmado) return;

    const datos = new URLSearchParams({
        accion: 'cancelarSesion',
        id_sesion: id_sesion
    });

    const resultado = await peticionAjax('cancelarSesion', datos);
    if (resultado && resultado.status === 'success') {
        UI.exito('Sesión Cancelada', 'La planificación ha sido anulada con éxito.'); 
        cargarTablaSesiones();
    } else {
        UI.error('Error', 'No se pudo cambiar el estado de la sesión.');
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
        if (selectGrupoForm) {
            selectGrupoForm.innerHTML = '<option value="">-- Seleccione un Grupo --</option>' +
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
        if (!modalSesion.classList.contains('hidden')) cerrarModalSesion();
        if (!modalVerSesion.classList.contains('hidden')) cerrarModalVerSesion();
        if (!modalCompletarSesion.classList.contains('hidden')) cerrarModalCompletarSesion();
    }
});

cargarRecursosIniciales().then(() => cargarTablaSesiones());