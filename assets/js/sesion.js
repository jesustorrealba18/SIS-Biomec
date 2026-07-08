const modalSesion = document.getElementById('modalSesion');
const modalVerSesion = document.getElementById('modalVer');
const modalCompletarSesion = document.getElementById('modalCompletar');
const formSesion = document.getElementById('formSesion');
const formCompletarSesion = document.getElementById('formCompletar');

let gruposCache = [];       
let microciclosCache = [];   
let drillsCache = [];        
let entrenadoresCache = [];  
let sesionEditando = false;

const coloresEstado = {
    'Planificada': 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30',
    'Completada':  'bg-green-500/20 text-green-400 border border-green-500/30',
    'Parcial':     'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
    'Cancelada':   'bg-red-500/20 text-red-400 border border-red-500/30'
};

function setupValidacionTiempoRealSesion() {
    const campos = [
        { id: 'id_entrenador', reglas: 'requerido', nombre: 'Entrenador' },
        { id: 'fecha', reglas: 'requerido|fecha', nombre: 'Fecha' },
        { id: 'id_grupo', reglas: 'requerido', nombre: 'Grupo' },
        { id: 'tipo_sesion', reglas: 'requerido', nombre: 'Tipo de sesión' },
        { id: 'duracion_minutos', reglas: 'requerido|numero', nombre: 'Duración', min: 15 },
        { id: 'observaciones', reglas: 'texto', nombre: 'Observaciones', max: 5000 },
        { id: 'volumen_ejecutado', reglas: 'requerido|numero', nombre: 'Volumen ejecutado', min: 0 },
        { id: 'observaciones_completar', reglas: 'texto', nombre: 'Observaciones', max: 5000 }
    ];

    campos.forEach(({ id, reglas, nombre, min, max }) => {
        const input = document.getElementById(id);
        if (!input) return;

        let errorContainer = input.parentElement.querySelector('.error-msg');
        if (!errorContainer) {
            errorContainer = document.createElement('span');
            errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block hidden';
            input.parentElement.appendChild(errorContainer);
        }

        if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
            input.addEventListener('input', function() {
                validarCampoSesion(this, reglas, nombre, min, max);
            });
        }
        
        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                validarCampoSesion(this, reglas, nombre, min, max);
            });
        }
        
        input.addEventListener('blur', function() {
            this.dataset.touched = 'true';
            validarCampoSesion(this, reglas, nombre, min, max);
        });
        
        input.addEventListener('focus', function() {
            this.dataset.touched = 'true';
        });
    });

    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        fechaInput.addEventListener('input', function() {
            const reglasAplicar = sesionEditando ? 'requerido' : 'requerido|fecha';
            validarCampoSesion(this, reglasAplicar, 'Fecha');
        });
    }
}

function validarCampoSesion(input, reglas, nombre, min = null, max = null) {
    const valor = input.value.trim();
    let error = '';
    let errorContainer = input.parentElement.querySelector('.error-msg');
    
    if (!errorContainer) {
        errorContainer = document.createElement('span');
        errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block hidden';
        input.parentElement.appendChild(errorContainer);
    }

    input.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');

    if (!valor && !reglas.includes('requerido')) {
        errorContainer.textContent = '';
        errorContainer.classList.add('hidden');
        return true;
    }

    if (reglas.includes('requerido') && !valor) {
        error = `${nombre} es requerido`;
    } else if (valor) {
        if (reglas.includes('fecha')) {
            const hoy = new Date().toISOString().split('T')[0];
            if (valor < hoy && !sesionEditando) {
                error = 'No se permite planificar sesiones para fechas pasadas';
            }
        }

        if (reglas.includes('numero')) {
            const num = parseFloat(valor);
            if (isNaN(num) || num < 0) {
                error = `${nombre} debe ser un número válido mayor o igual a 0`;
            } else if (min !== null && num < min) {
                error = `${nombre} debe ser al menos ${min}`;
            }
        }

        if (reglas.includes('texto') && valor) {
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/.test(valor)) {
                error = `${nombre} contiene caracteres no permitidos`;
            }
        }

        if (max && valor.length > max) {
            error = `${nombre} no debe exceder ${max} caracteres`;
        }
    }

    if (error) {
        input.classList.add('border-red-500', 'border-2');
        errorContainer.textContent = error;
        errorContainer.classList.remove('hidden');
        return false;
    } else if (valor) {
        input.classList.add('border-green-500', 'border');
        errorContainer.textContent = '';
        errorContainer.classList.add('hidden');
        return true;
    } else {
        errorContainer.textContent = '';
        errorContainer.classList.add('hidden');
        return true;
    }
}

function activarValidacionesEnModal() {
    document.querySelectorAll('#formSesion input, #formSesion select, #formSesion textarea').forEach(el => {
        if (!el.dataset.touched || el.dataset.touched === 'false') {
            el.dataset.touched = 'true';
        }
        if (el.tagName === 'SELECT') {
            el.dispatchEvent(new Event('change'));
        } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            el.dispatchEvent(new Event('input'));
        }
    });
}

function validarFormularioSesionCompleto(form) {
    const camposRequeridos = [
        { id: 'id_entrenador', reglas: 'requerido', nombre: 'Entrenador' },
        { id: 'id_grupo', reglas: 'requerido', nombre: 'Grupo' },
        { id: 'fecha', reglas: 'requerido', nombre: 'Fecha' },
        { id: 'tipo_sesion', reglas: 'requerido', nombre: 'Tipo de sesión' },
        { id: 'duracion_minutos', reglas: 'requerido|numero', nombre: 'Duración', min: 15 }
    ];
    
    let hasError = false;
    let errores = [];

    camposRequeridos.forEach(({ id, reglas, nombre, min }) => {
        const input = document.getElementById(id);
        if (input) {
            input.dataset.touched = 'true';
            const reglasAplicar = (id === 'fecha' && sesionEditando) ? 'requerido' : reglas;
            const isValid = validarCampoSesion(input, reglasAplicar, nombre, min);
            if (!isValid) {
                hasError = true;
                const errorMsg = input.parentElement.querySelector('.error-msg');
                if (errorMsg && errorMsg.textContent) {
                    errores.push(errorMsg.textContent);
                }
            }
        }
    });

    return { hasError, errores };
}

async function peticionAjax(accion, datos = null) {
    try {
        let url = `${API_URL}&accion=${accion}`;
        let opciones = {
            method: datos ? 'POST' : 'GET'
        };

        if (datos) {
            if (datos instanceof FormData) {
                opciones.body = datos;
            } else {
                opciones.headers = {
                    'Content-Type': 'application/json'
                };
                opciones.body = JSON.stringify(datos);
            }
        }

        const respuesta = await fetch(url, opciones);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        return await respuesta.json();
    } catch (error) {
        Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
        return null;
    }
}

async function cargarTablaSesiones() {
    const filtroGrupo = document.getElementById('filtroGrupo').value || '';
    const filtroEstado = document.getElementById('filtroTipoSesion').value || '';

    const datos = await peticionAjax(`listarSesiones&id_grupo=${filtroGrupo}&estado=${filtroEstado}`);
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
    
    sesionEditando = !!id_sesion;
    
    formSesion.reset();
    document.getElementById('id_sesion').value = '';
    document.getElementById('tbodySeries').innerHTML = '';

    document.querySelectorAll('.error-msg').forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
    });
    document.querySelectorAll('.border-red-500, .border-green-500').forEach(el => {
        el.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
    });
    
    const inputFecha = document.getElementById('fecha');
    const hoy = new Date().toISOString().split('T')[0];
    if (inputFecha) {
        inputFecha.min = hoy;
        if (!id_sesion) inputFecha.value = hoy;
        inputFecha.dataset.touched = 'false';
    }

    if (id_sesion) {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Sesión';
        peticionAjax(`obtenerDetalle&id=${id_sesion}`).then(det => {
            if (!det) {
                Swal.fire('Error', 'No se pudo cargar la sesión', 'error');
                return;
            }
            
            document.getElementById('id_sesion').value = det.id_sesion;
            document.getElementById('id_entrenador').value = det.id_entrenador || '';
            document.getElementById('id_grupo').value = det.id_grupo || '';
            document.getElementById('id_microciclo').value = det.id_microciclo || '';
            document.getElementById('fecha').value = det.fecha || '';
            document.getElementById('tipo_sesion').value = det.tipo_sesion || 'Tecnica';
            document.getElementById('calentamiento').value = det.calentamiento || '';
            document.getElementById('vuelta_calma').value = det.vuelta_calma || '';
            document.getElementById('observaciones').value = det.observaciones || '';
            document.getElementById('duracion_minutos').value = det.duracion_minutos || '';

            document.getElementById('tbodySeries').innerHTML = '';
            
            if (det.series && det.series.length > 0) {
                det.series.forEach(serie => agregarFilaSerie(serie));
            } else {
                agregarFilaSerie();
            }
            calcularVolumenTotalSesion();
            
            setTimeout(() => activarValidacionesEnModal(), 200);
        });
    } else {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-calendar-plus text-indigo-400"></i> Planificar Sesión';
        document.getElementById('tbodySeries').innerHTML = '';
        agregarFilaSerie();
        setTimeout(() => activarValidacionesEnModal(), 200);
    }
    modalSesion.classList.remove('hidden');
    setTimeout(() => {
        const content = modalSesion.firstElementChild;
        if (content) content.classList.remove('scale-95', 'opacity-0');
    }, 50);
}

function cerrarModalSesion() {
    if (!modalSesion) return;
    const content = modalSesion.firstElementChild;
    if (content) content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalSesion.classList.add('hidden');
        sesionEditando = false;
    }, 300);
}

async function verDetalleSesion(id_sesion) {
    if (!modalVerSesion) return;

    try {
        const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
        if (!det) {
            Swal.fire('Error', 'No se pudieron recuperar los detalles.', 'error');
            return;
        }

        const contenedor = document.getElementById('detalleContenido');
        if (!contenedor) return;

        let volCalentamiento = 0;
        let volPrincipal = 0;
        let volVueltaCalma = 0;
        
        if (det.series && det.series.length > 0) {
            det.series.forEach(serie => {
                const volumen = (parseInt(serie.repeticiones) || 0) * (parseInt(serie.distancia_m) || 0);
                if (serie.bloque === 'Calentamiento') volCalentamiento += volumen;
                else if (serie.bloque === 'Principal') volPrincipal += volumen;
                else if (serie.bloque === 'VuletaCalma') volVueltaCalma += volumen;
            });
        }

        let html = `
            <div class="mb-4 border-b border-white/10 pb-4">
                <h3 class="text-xl font-bold text-white">${det.grupo_nombre || 'Sin grupo'}</h3>
                <p class="text-xs text-gray-400">Fecha: <span class="text-white font-mono">${det.fecha || 'N/A'}</span> | Tipo: <span class="text-indigo-400">${det.tipo_sesion || 'N/A'}</span></p>
                <p class="text-xs text-gray-400 mt-1">Estado: <span class="${coloresEstado[det.estado] || 'bg-gray-500/20 text-gray-400'} px-2 py-0.5 rounded text-[10px] font-bold">${det.estado || 'Desconocido'}</span></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 text-center text-xs">
                <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Calentamiento</p><p class="text-sm font-bold text-gray-300">${volCalentamiento}m</p></div>
                <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Bloque Principal</p><p class="text-sm font-bold text-indigo-400">${volPrincipal}m</p></div>
                <div class="bg-black/20 p-2 rounded-lg"><p class="text-gray-500 uppercase font-bold">Vuelta a la Calma</p><p class="text-sm font-bold text-emerald-400">${volVueltaCalma}m</p></div>
            </div>
            <div class="space-y-1 text-xs bg-black/10 p-3 rounded-xl border border-white/5 mb-4 text-gray-300">
                <p><strong>Calentamiento:</strong> ${det.calentamiento || 'Ninguno'}</p>
                <p><strong>Vuelta a la Calma:</strong> ${det.vuelta_calma || 'Ninguno'}</p>
                <p><strong>Observaciones:</strong> ${det.observaciones || 'Ninguna'}</p>
                <p><strong>Volumen Planificado:</strong> <span class="text-indigo-400 font-mono">${det.volumen_planificado || 0}m</span></p>
                ${det.volumen_ejecutado ? `<p><strong>Volumen Ejecutado:</strong> <span class="text-emerald-400 font-mono">${det.volumen_ejecutado}m</span></p>` : ''}
            </div>
        `;

        if (det.series && det.series.length > 0) {
            html += `
                <div class="bg-black/20 p-3 rounded-xl border border-white/5">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Series Planificadas (${det.series.length})</p>
                    <div class="space-y-1 max-h-60 overflow-y-auto">
                        ${det.series.map((s, i) => `
                            <div class="flex justify-between items-center p-2 bg-black/20 rounded-lg text-xs border border-white/5">
                                <span class="text-gray-400">#${i+1}</span>
                                <span class="text-indigo-400 font-medium">${s.bloque}</span>
                                <span class="text-white">${s.drill_nombre || s.ejercicio_descripcion || 'Ejercicio libre'}</span>
                                <span class="text-gray-300">${s.repeticiones}x${s.distancia_m}m</span>
                                <span class="text-gray-400">${s.zona_intensidad}</span>
                                <span class="text-emerald-400 font-mono">${(s.repeticiones || 0) * (s.distancia_m || 0)}m</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        contenedor.innerHTML = html;

        modalVerSesion.classList.remove('hidden');
        modalVerSesion.style.display = 'flex';
        
        const modalContent = modalVerSesion.firstElementChild;
        if (modalContent) {
            modalContent.classList.remove('scale-95', 'opacity-0');
        }

    } catch (error) {
        Swal.fire('Error', 'Error al cargar los detalles', 'error');
    }
}

function cerrarModalVer() {
    if (!modalVerSesion) return;
    const modalContent = modalVerSesion.firstElementChild;
    if (modalContent) {
        modalContent.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        modalVerSesion.classList.add('hidden');
        modalVerSesion.style.display = '';
    }, 300);
}

async function abrirModalCompletarSesion(id_sesion) {
    if (!formCompletarSesion || !modalCompletarSesion) return;
    
    formCompletarSesion.reset();
    document.querySelectorAll('#modalCompletar .error-msg').forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
    });
    document.querySelectorAll('#modalCompletar .border-red-500, #modalCompletar .border-green-500').forEach(el => {
        el.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
    });
    
    const det = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!det) {
        Swal.fire('Error', 'No se pudo cargar la sesión', 'error');
        return;
    }
 
    document.getElementById('id_sesion_completar').value = det.id_sesion;
    document.getElementById('compFecha').textContent = det.fecha || 'N/A';
    document.getElementById('compTipo').textContent = det.tipo_sesion || 'N/A';
    document.getElementById('compGrupo').textContent = det.grupo_nombre || 'N/A';
    document.getElementById('compVolPlanificado').textContent = `${det.volumen_planificado || 0} metros`;
    
    document.getElementById('volumen_ejecutado').value = det.volumen_planificado || 0;
    document.getElementById('observaciones_completar').value = det.observaciones || '';

    setTimeout(() => {
        document.querySelectorAll('#formCompletar input, #formCompletar textarea').forEach(el => {
            el.dataset.touched = 'true';
            if (el.id === 'volumen_ejecutado') {
                validarCampoSesion(el, 'requerido|numero', 'Volumen ejecutado', 0);
            }
        });
    }, 200);

    modalCompletarSesion.classList.remove('hidden');
    setTimeout(() => {
        const modalContent = modalCompletarSesion.firstElementChild;
        if (modalContent) {
            modalContent.classList.remove('scale-95', 'opacity-0');
        }
    }, 50);
}

function cerrarModalCompletar() {
    if (!modalCompletarSesion) return;
    const modalContent = modalCompletarSesion.firstElementChild;
    if (modalContent) {
        modalContent.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => modalCompletarSesion.classList.add('hidden'), 300);
}

function agregarFilaSerie(datos = null) {
    const contenedor = document.getElementById('tbodySeries');
    if (!contenedor) return;
    
    const filasExistentes = contenedor.querySelectorAll('.fila-serie').length;
    const nuevoOrden = filasExistentes + 1; 

    const tr = document.createElement('tr');
    tr.className = 'fila-serie hover:bg-white/5 transition';

    const opcionesDrills = `<option value="">Ejercicio Libre</option>` + 
        (drillsCache || []).map(d => `<option value="${d.id_drill}" ${datos && datos.id_drill == d.id_drill ? 'selected' : ''}>${d.nombre}</option>`).join('');

    tr.innerHTML = `
        <input type="hidden" name="serie_orden_ejecucion[]" value="${datos ? datos.orden_ejecucion : nuevoOrden}" class="serie-orden">
        
        <td class="p-2">
            <select name="serie_bloque[]" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white bloque-select" onchange="calcularVolumenTotalSesion()">
                <option value="Calentamiento" ${datos && datos.bloque === 'Calentamiento' ? 'selected' : ''}>Calentamiento</option>
                <option value="Principal" ${datos && datos.bloque === 'Principal' ? 'selected' : (!datos && filasExistentes === 0 ? 'selected' : '')}>Principal</option>
                <option value="VuletaCalma" ${datos && datos.bloque === 'VuletaCalma' ? 'selected' : (!datos && filasExistentes === 1 ? 'selected' : '')}>Vuelta Calma</option>
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
            <input type="number" min="1" max="100" name="serie_repeticiones[]" value="${datos ? datos.repeticiones : '1'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-14 rep-input" oninput="calcularVolumenSerie(this)">
         </td>

        <td class="p-2 text-center">
            <input type="number" min="0" max="10000" step="25" name="serie_distancia_m[]" value="${datos ? datos.distancia_m : '50'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-16 dist-input" oninput="calcularVolumenSerie(this)">
         </td>

        <td class="p-2 text-center">
            <input type="number" min="0" max="600" name="serie_descanso_seg[]" value="${datos ? datos.descanso_seg : '15'}" class="bg-[#0f0d23] border border-white/10 rounded-xl p-1.5 text-xs text-white text-center font-mono w-14">
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
    const fila = boton.closest('.fila-serie');
    if (contenedor.querySelectorAll('.fila-serie').length > 1) {
        fila.remove();
        contenedor.querySelectorAll('.fila-serie').forEach((fila, index) => {
            fila.querySelector('.serie-orden').value = index + 1;
        });
        calcularVolumenTotalSesion();
    } else {
        Swal.fire('Aviso', 'Debe haber al menos una serie en la sesión.', 'info');
    }
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
        else if (bloque === 'VuletaCalma') volVueltaCalma += subtotal; 
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
    
        const { hasError, errores } = validarFormularioSesionCompleto(this);
        
        if (hasError) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos Incompletos o Inválidos',
                html: errores.join('<br>') || 'Por favor corrige los campos marcados en rojo.',
                confirmButtonColor: '#4f46e5'
            });
            
            const primerError = this.querySelector('.border-red-500');
            if (primerError) {
                primerError.focus();
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        const id_grupo = document.getElementById('id_grupo').value;
        if (!id_grupo) { 
            Swal.fire('Validación', 'Debe seleccionar un grupo obligatoriamente.', 'warning');
            return;
        }

        const fecha = document.getElementById('fecha').value;
        if (!fecha) {
            Swal.fire('Validación', 'Debe seleccionar una fecha para la sesión.', 'warning');
            return;
        }

        const duracion = parseInt(document.getElementById('duracion_minutos').value);
        if (!duracion || duracion < 15) {
            Swal.fire('Validación', 'La duración mínima de la sesión debe ser de 15 minutos.', 'warning');
            return;
        }

        const filasSeries = document.querySelectorAll('.fila-serie');
        if (filasSeries.length === 0) {
            Swal.fire('Validación', 'Debe agregar al menos una serie a la sesión.', 'warning');
            return;
        }

        let errorSeries = false;
        filasSeries.forEach((fila, index) => {
            const repes = parseInt(fila.querySelector('.rep-input').value) || 0;
            const dist = parseInt(fila.querySelector('.dist-input').value) || 0;
            if (repes <= 0 || dist <= 0) {
                errorSeries = true;
                Swal.fire('Validación', `La serie ${index + 1} debe tener repeticiones y distancia mayores a 0.`, 'warning');
            }
        });
        if (errorSeries) return;

        const id_sesion = document.getElementById('id_sesion').value;
        const formData = new FormData(formSesion);
        const series = [];
        
        document.querySelectorAll('.fila-serie').forEach(f => {
            const descansoInputs = f.querySelectorAll('input[type="number"]');
            series.push({
                orden_ejecucion: f.querySelector('.serie-orden').value,
                bloque: f.querySelector('.bloque-select').value,
                id_drill: f.querySelector('.drill-select').value || null,
                ejercicio_descripcion: f.querySelector('.desc-input').value || null,
                repeticiones: f.querySelector('.rep-input').value,
                distancia_m: f.querySelector('.dist-input').value,
                descanso_seg: descansoInputs[2] ? descansoInputs[2].value : 15, 
                zona_intensidad: f.querySelector('select[name="serie_zona_intensidad[]"]').value,
                ritmo_objetivo: f.querySelector('input[name="serie_ritmo_objetivo[]"]').value || null
            });
        });

        formData.append('series', JSON.stringify(series));
        const accion = id_sesion ? 'editar' : 'guardar';
        if (id_sesion) {
            formData.append('id_sesion', id_sesion);
        }

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

if(formCompletarSesion) {
    formCompletarSesion.addEventListener('submit', async function(e) {
        e.preventDefault();
   
        const volumenEjecutado = document.getElementById('volumen_ejecutado');
        if (volumenEjecutado) {
            volumenEjecutado.dataset.touched = 'true';
            const isValid = validarCampoSesion(volumenEjecutado, 'requerido|numero', 'Volumen ejecutado', 0);
            if (!isValid) {
                const errorMsg = volumenEjecutado.parentElement.querySelector('.error-msg');
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos Inválidos',
                    text: errorMsg?.textContent || 'El volumen ejecutado debe ser un número válido mayor o igual a 0',
                    confirmButtonColor: '#4f46e5'
                });
                volumenEjecutado.focus();
                return;
            }
        }
        
        const volumenPlanificadoText = document.getElementById('compVolPlanificado').textContent;
        const volumenPlanificado = parseInt(volumenPlanificadoText) || 0;
        const volumenEjecutadoVal = parseInt(document.getElementById('volumen_ejecutado').value) || 0;
        
        if (volumenEjecutadoVal > volumenPlanificado) {
            Swal.fire('Validación', 'El volumen ejecutado no puede superar al volumen planificado.', 'warning');
            return;
        }
        
        if (volumenEjecutadoVal < 0) {
            Swal.fire('Validación', 'El volumen ejecutado no puede ser negativo.', 'warning');
            return;
        }
        
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
    try {
        const [grupos, microciclos, drills, entrenadores] = await Promise.all([
            peticionAjax('listarGrupos'),
            peticionAjax('listarMicrociclos'),
            peticionAjax('listarDrillsActivos'),
            peticionAjax('listarEntrenadores')
        ]);

        if (grupos) {
            gruposCache = grupos;
            const selectGrupoForm = document.getElementById('id_grupo');
            const selectGrupoFiltro = document.getElementById('filtroGrupo');
            if (selectGrupoForm) {
                selectGrupoForm.innerHTML = '<option value="">Seleccione un Grupo</option>' +
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
                selectMicroForm.innerHTML = '<option value="">Microciclo (Ninguno)</option>' +
                    microciclos.map(m => `<option value="${m.id_microciclo}">${m.nombre}</option>`).join('');
            }
        }
        if (drills) {
            drillsCache = drills;
        }
        if (entrenadores && entrenadores.length > 0) {
            entrenadoresCache = entrenadores;
            const selectEntrenador = document.getElementById('id_entrenador');
            if (selectEntrenador) {
                let opciones = '<option value="">Seleccione un Entrenador</option>';
                
                entrenadores.forEach(e => {
                    let nombreCompleto = '';
                    if (e.nombres && e.apellidos) {
                        nombreCompleto = e.nombres + ' ' + e.apellidos;
                    } else if (e.nombres) {
                        nombreCompleto = e.nombres;
                    } else if (e.nombre_completo) {
                        nombreCompleto = e.nombre_completo;
                    } else {
                        nombreCompleto = 'Entrenador ID: ' + e.id_entrenador;
                    }
                    
                    opciones += `<option value="${e.id_entrenador}">${nombreCompleto}</option>`;
                });
                
                selectEntrenador.innerHTML = opciones;
            }
        }
    } catch (error) {
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalSesion();
        cerrarModalVer();
        cerrarModalCompletar();
    }
});

function inicializarSesiones() {
    setupValidacionTiempoRealSesion();
    cargarRecursosIniciales().then(() => cargarTablaSesiones());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarSesiones);
} else {
    inicializarSesiones();
}