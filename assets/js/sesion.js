const API_URL = 'index.php?p=sesiones';

const modalSesion = document.getElementById('modalSesion');
const modalCompletar = document.getElementById('modalCompletar');
const modalVer = document.getElementById('modalVer');
const formSesion = document.getElementById('formSesion');
const formCompletar = document.getElementById('formCompletar');

let gruposCache = [];
let microciclosCache = [];
let drillsCache = [];

const coloresTipo = {
    'Técnica':       'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    'Resistencia':   'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'Velocidad':     'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    'Recuperación':  'bg-gray-500/20 text-gray-400 border border-gray-500/30',
    'Fuerza':        'bg-purple-500/20 text-purple-400 border border-purple-500/30',
    'Flexibilidad':  'bg-pink-500/20 text-pink-400 border border-pink-500/30',
    'Competencia':   'bg-orange-500/20 text-orange-400 border border-orange-500/30'
};

const coloresEstado = {
    'Planificada':   'bg-indigo-500/20 text-indigo-400',
    'En Progreso':   'bg-yellow-500/20 text-yellow-400',
    'Completada':    'bg-green-500/20 text-green-400',
    'Cancelada':     'bg-red-500/20 text-red-400'
};

const opcionesBloque = ['Calentamiento', 'Principal', 'VueltaCalma'];
const opcionesZonas = ['Z1', 'Z2', 'Z3', 'Z4', 'Z5'];

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicacion con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud de entrenamientos.');
        return null;
    }
}

async function cargarTablaSesiones() {
    const datos = await peticionAjax('listarSesiones');
    const tbody = document.getElementById('tbodySesiones');

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-500">No se encontraron sesiones planificadas.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(se => `
        <tr class="hover:bg-white/5 transition">
            <td class="p-4">
                <p class="text-white font-medium">Grupo: ${se.grupo_nombre}</p>
                ${se.microciclo_nombre ? `<p class="text-[10px] text-gray-500 mt-0.5">${se.microciclo_nombre}</p>` : `<p class="text-[10px] text-gray-400 italic mt-0.5">Sesión suelta</p>`}
            </td>
            <td class="p-4 font-mono text-xs">${formatoFecha(se.fecha)}</td>
            <td class="p-4">${badgeTipo(se.tipo_sesion)}</td>
            <td class="p-4 text-xs font-mono text-indigo-300 font-bold">${se.volumen_planificado_total}m</td>
            <td class="p-4 text-xs font-mono text-emerald-400 font-bold">${se.volumen_ejecutado_total ? se.volumen_ejecutado_total + 'm' : '-'}</td>
            <td class="p-4">${badgeEstado(se.estado)}</td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="verDetalle(${se.id_sesion})" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 rounded-lg transition cursor-pointer" title="Ver estructura">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    <button onclick="abrirModalSesion(${se.id_sesion})" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer" title="Editar planificación">
                        <i class="fas fa-pen text-sm"></i>
                    </button>
                    <button onclick="abrirModalCompletar(${se.id_sesion})" class="p-2 text-green-400 hover:text-green-300 hover:bg-green-500/10 rounded-lg transition cursor-pointer" title="Cerrar y Completar sesión">
                        <i class="fas fa-flag-checkered text-sm"></i>
                    </button>
                    <button onclick="accionEstado(${se.id_sesion}, '${se.estado}')" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer" title="Cambiar estado u Ordenar cancelación">
                        <i class="fas fa-exchange-alt text-sm"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filtrarTablaSesiones() {
    const texto = document.getElementById('busquedaSesion').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodySesiones tr');
    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
}

function abrirModalSesion(id_sesion = null) {
    formSesion.reset();
    document.getElementById('id_sesion').value = '';
    document.getElementById('contenedorSeries').innerHTML = '';
    actualizarVolumenTotalesPlano();

    if (id_sesion) {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Sesión de Entrenamiento';
        peticionAjax(`obtenerDetalle&id=${id_sesion}`).then(detalle => {
            if (!detalle) return;
            document.getElementById('id_sesion').value = detalle.id_sesion;
            document.getElementById('id_grupo').value = detalle.id_grupo || '';
            document.getElementById('id_microciclo').value = detalle.id_microciclo || '';
            document.getElementById('fecha').value = detalle.fecha || '';
            document.getElementById('tipo_sesion').value = detalle.tipo_sesion || 'Resistencia';
            document.getElementById('calentamiento').value = detalle.calentamiento || '';
            document.getElementById('vuelta_calma').value = detalle.vuelta_calma || '';
            document.getElementById('observaciones').value = detalle.observaciones || '';

            if (detalle.series && detalle.series.length > 0) {
                detalle.series.forEach(se => agregarFilaSerie(se));
            }
            actualizarVolumenTotalesPlano();
        });
    } else {
        document.getElementById('modalSesionTitulo').innerHTML = '<i class="fas fa-swimmer text-emerald-400"></i> Planificar Sesión de Entrenamiento';
        // Agrega una fila vacía por defecto
        agregarFilaSerie();
    }

    modalSesion.classList.remove('hidden');
    setTimeout(() => modalSesion.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalSesion() {
    modalSesion.classList.add('hidden');
    modalSesion.firstElementChild.classList.add('scale-95', 'opacity-0');
    formSesion.reset();
    document.getElementById('contenedorSeries').innerHTML = '';
}

function agregarFilaSerie(datos = null) {
    const contenedor = document.getElementById('contenedorSeries');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-white/5 transition table-row-serie';

    const opcionesBlq = opcionesBloque.map(b => `<option value="${b}" ${datos && datos.bloque === b ? 'selected' : ''}>${b}</option>`).join('');
    const opcionesZon = opcionesZonas.map(z => `<option value="${z}" ${datos && datos.zona_intensidad === z ? 'selected' : ''}>${z}</option>`).join('');
    const opcionesDrl = drillsCache.map(d => `<option value="${d.id_drill}" ${datos && datos.id_drill == d.id_drill ? 'selected' : ''}>${d.nombre}</option>`).join('');

    tr.innerHTML = `
        <td class="p-2"><select name="serie_bloque[]" class="input-dark p-2 rounded-lg text-xs select-bloque-fila">${opcionesBlq}</select></td>
        <td class="p-2">
            <select name="serie_id_drill[]" class="input-dark p-2 rounded-lg text-xs w-32">
                <option value="">-- Libre/Ninguno --</option>
                ${opcionesDrl}
            </select>
        </td>
        <td class="p-2"><input type="text" name="serie_ejercicio_descripcion[]" value="${datos ? datos.ejercicio_descripcion || '' : ''}" placeholder="Ej: Patada c/tabla" class="input-dark p-2 rounded-lg text-xs w-36"></td>
        <td class="p-2"><input type="number" name="serie_repeticiones[]" value="${datos ? datos.repeticiones : 1}" min="1" class="input-dark p-2 rounded-lg text-xs font-mono text-center w-14 input-calculo-volumen"></td>
        <td class="p-2"><input type="number" name="serie_distancia_m[]" value="${datos ? datos.distancia_m : 50}" min="25" step="25" class="input-dark p-2 rounded-lg text-xs font-mono text-center w-16 input-calculo-volumen"></td>
        <td class="p-2"><input type="number" name="serie_descanso_seg[]" value="${datos ? datos.descanso_seg : 15}" min="0" class="input-dark p-2 rounded-lg text-xs font-mono text-center w-14"></td>
        <td class="p-2"><select name="serie_zona_intensidad[]" class="input-dark p-2 rounded-lg text-xs">${opcionesZon}</select></td>
        <td class="p-2"><input type="text" name="serie_ritmo_objetivo[]" value="${datos ? datos.ritmo_objetivo || '' : ''}" placeholder="1:30" class="input-dark p-2 rounded-lg text-xs font-mono text-center w-16"></td>
        <td class="p-2 text-center"><span class="text-xs font-mono text-indigo-400 font-bold span-subtotal-volumen-serie">0m</span></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove(); actualizarVolumenTotalesPlano();" class="text-red-400 hover:text-red-300 p-1 transition cursor-pointer"><i class="fas fa-trash-alt text-xs"></i></button>
        </td>
    `;

    contenedor.appendChild(tr);

    tr.querySelectorAll('.input-calculo-volumen').forEach(input => {
        input.addEventListener('input', () => calcularVolumenFila(tr));
    });
    tr.querySelector('.select-bloque-fila').addEventListener('change', () => actualizarVolumenTotalesPlano());

    calcularVolumenFila(tr);
}

function calcularVolumenFila(fila) {
    const reps = parseInt(fila.querySelector('[name="serie_repeticiones[]"]').value) || 0;
    const dist = parseInt(fila.querySelector('[name="serie_distancia_m[]"]').value) || 0;
    const subtotal = reps * dist;

    fila.querySelector('.span-subtotal-volumen-serie').textContent = `${subtotal}m`;
    actualizarVolumenTotalesPlano();
}

function actualizarVolumenTotalesPlano() {
    let cal = 0, prin = 0, vlt = 0;

    document.querySelectorAll('.table-row-serie').forEach(fila => {
        const bloque = fila.querySelector('.select-bloque-fila').value;
        const reps = parseInt(fila.querySelector('[name="serie_repeticiones[]"]').value) || 0;
        const dist = parseInt(fila.querySelector('[name="serie_distancia_m[]"]').value) || 0;
        const totalFila = reps * dist;

        if (bloque === 'Calentamiento') cal += totalFila;
        else if (bloque === 'Principal') prin += totalFila;
        else if (bloque === 'VueltaCalma') vlt += totalFila;
    });

    if(document.getElementById('lblVolCalentamiento')) document.getElementById('lblVolCalentamiento').textContent = cal + 'm';
    if(document.getElementById('lblVolPrincipal')) document.getElementById('lblVolPrincipal').textContent = prin + 'm';
    if(document.getElementById('lblVolVueltaCalma')) document.getElementById('lblVolVueltaCalma').textContent = vlt + 'm';
    if(document.getElementById('lblVolTotalPlanificado')) document.getElementById('lblVolTotalPlanificado').textContent = (cal + prin + vlt) + 'm';
}

formSesion.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id_sesion = document.getElementById('id_sesion').value;
    const formData = new FormData(formSesion);


    const series = [];
    const filas = document.querySelectorAll('.table-row-serie');

    filas.forEach((fila, index) => {
        const blq = fila.querySelector('[name="serie_bloque[]"]').value;
        const drl = fila.querySelector('[name="serie_id_drill[]"]').value;
        const dsc = fila.querySelector('[name="serie_ejercicio_descripcion[]"]').value;
        const rep = fila.querySelector('[name="serie_repeticiones[]"]').value;
        const dst = fila.querySelector('[name="serie_distancia_m[]"]').value;
        const des = fila.querySelector('[name="serie_descanso_seg[]"]').value;
        const zon = fila.querySelector('[name="serie_zona_intensidad[]"]').value;
        const rit = fila.querySelector('[name="serie_ritmo_objetivo[]"]').value;

        series.push({
            orden_ejecucion: index + 1,
            bloque: blq,
            id_drill: drl || null,
            ejercicio_descripcion: dsc || null,
            repeticiones: rep,
            distancia_m: dst,
            descanso_seg: des,
            zona_intensidad: zon,
            ritmo_objetivo: rit || null
        });
    });

    formData.append('series', JSON.stringify(series));

    if (id_sesion) {
        formData.append('id_sesion', id_sesion);
        formData.append('accion', 'editar');

        const resultado = await peticionAjax('editar', formData);
        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Sesión Actualizada', resultado.message);
                cerrarModalSesion();
                cargarTablaSesiones();
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
                UI.exito('Sesión Planificada', resultado.message);
                cerrarModalSesion();
                cargarTablaSesiones();
            } else if (resultado.status === 'warning') {
                mostrarErroresFormulario(resultado.errores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
    }
});

async function abrirModalCompletar(id_sesion) {
    formCompletar.reset();
    document.getElementById('id_sesion_comp').value = id_sesion;

    const detalle = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!detalle) return;

    document.getElementById('comp_fecha').textContent = formatoFecha(detalle.fecha);
    document.getElementById('comp_tipo').textContent = detalle.tipo_sesion;
    document.getElementById('comp_grupo').textContent = detalle.grupo_nombre;
    document.getElementById('comp_planificado').textContent = `${detalle.volumen_planificado_total} metros`;
    
    document.getElementById('volumen_ejecutado').value = detalle.volumen_planificado_total;
    document.getElementById('comp_observaciones').value = detalle.observaciones || '';

    modalCompletar.classList.remove('hidden');
    setTimeout(() => modalCompletar.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalCompletar() {
    modalCompletar.classList.add('hidden');
    modalCompletar.firstElementChild.classList.add('scale-95', 'opacity-0');
}

formCompletar.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(formCompletar);
    formData.append('accion', 'completarSesion');

    const resultado = await peticionAjax('completarSesion', formData);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Sesión Ejecutada', resultado.message);
            cerrarModalCompletar();
            cargarTablaSesiones();
        } else if (resultado.status === 'warning') {
            mostrarErroresFormulario(resultado.errores);
        } else {
            UI.error('Error', resultado.message);
        }
    }
});

async function verDetalle(id_sesion) {
    const detalle = await peticionAjax(`obtenerDetalle&id=${id_sesion}`);
    if (!detalle) { UI.error('Error', 'No se pudo obtener la estructura de la sesión.'); return; }

    const contenedor = document.getElementById('detalleContenido');

    let html = `
        <div class="mb-6">
            <h2 class="text-xl font-bold text-white mb-1">Estructura del Entrenamiento</h2>
            <p class="text-xs text-gray-400">Grupo: <span class="text-white font-medium">${detalle.grupo_nombre}</span></p>
            <div class="flex flex-wrap gap-2 mt-2">
                ${badgeTipo(detalle.tipo_sesion)}
                ${badgeEstado(detalle.estado)}
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-black/20 rounded-xl text-center">
            <div><p class="text-[10px] text-gray-500 uppercase">Fecha</p><p class="text-white font-mono text-sm">${formatoFecha(detalle.fecha)}</p></div>
            <div><p class="text-[10px] text-gray-500 uppercase">Fase ATR</p><p class="text-white text-sm font-medium">${detalle.fase_atr_nombre || 'N/A'}</p></div>
            <div><p class="text-[10px] text-gray-500 uppercase">Vol. Planificado</p><p class="text-indigo-400 font-mono text-sm font-bold">${detalle.volumen_planificado_total}m</p></div>
            <div><p class="text-[10px] text-gray-500 uppercase">Vol. Ejecutado Real</p><p class="text-emerald-400 font-mono text-sm font-bold">${detalle.volumen_ejecutado_total ? detalle.volumen_ejecutado_total + 'm' : '-'}</p></div>
        </div>
    `;

    if (detalle.series && detalle.series.length > 0) {
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-indigo-400 mb-3"><i class="fas fa-list-ol mr-2"></i>Series Técnicas Planificadas</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-gray-500 uppercase tracking-wider border-b border-[#252345]">
                                <th class="p-2">Bloque</th>
                                <th class="p-2">Ejercicio / Drill</th>
                                <th class="p-2 text-center">Estructura</th>
                                <th class="p-2 text-center">Descanso</th>
                                <th class="p-2 text-center">Intensidad</th>
                                <th class="p-2 text-center">Ritmo</th>
                                <th class="p-2 text-center">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#252345] text-gray-300">
                            ${detalle.series.map(s => `
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-2"><span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-white/10">${s.bloque}</span></td>
                                    <td class="p-2">
                                        <p class="text-white font-medium">${s.drill_nombre || 'Ejercicio Libre'}</p>
                                        ${s.ejercicio_descripcion ? `<p class="text-[10px] text-gray-500">${s.ejercicio_descripcion}</p>` : ''}
                                    </td>
                                    <td class="p-2 text-center font-mono">${s.repeticiones} x ${s.distancia_m}m</td>
                                    <td class="p-2 text-center font-mono">${s.descanso_seg}s</td>
                                    <td class="p-2 text-center"><span class="text-amber-400 font-bold">${s.zona_intensidad}</span></td>
                                    <td class="p-2 text-center font-mono text-gray-400">${s.ritmo_objetivo || '-'}</td>
                                    <td class="p-2 text-center font-mono font-bold text-white">${s.repeticiones * s.distancia_m}m</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (detalle.observaciones || detalle.observaciones_ejecucion) {
        html += `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                ${detalle.observaciones ? `<div class="p-3 bg-black/20 rounded-xl"><p class="text-[10px] text-gray-500 uppercase mb-1">Planificación</p><p class="text-xs text-gray-300">${detalle.observaciones}</p></div>` : ''}
                ${detalle.observaciones_ejecucion ? `<div class="p-3 bg-emerald-500/5 border border-emerald-500/10 rounded-xl"><p class="text-[10px] text-emerald-500 uppercase mb-1">Cierre de Ejecución</p><p class="text-xs text-gray-300">${detalle.observaciones_ejecucion}</p></div>` : ''}
            </div>
        `;
    }

    contenedor.innerHTML = html;
    modalVer.classList.remove('hidden');
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    document.getElementById('detalleContenido').innerHTML = '';
}

async function accionEstado(id_sesion, estadoActual) {
    let opciones = [];

    if (estadoActual === 'Planificada') {
        opciones = [
            { valor: 'En Progreso', icono: 'fa-play', color: 'yellow' },
            { valor: 'Cancelada', icono: 'fa-ban', color: 'red' }
        ];
    } else if (estadoActual === 'En Progreso') {
        opciones = [
            { valor: 'Completada', icono: 'fa-flag-checkered', color: 'green' },
            { valor: 'Cancelada', icono: 'fa-ban', color: 'red' }
        ];
    } else if (estadoActual === 'Cancelada') {
        opciones = [
            { valor: 'Planificada', icono: 'fa-redo', color: 'indigo' }
        ];
    } else if (estadoActual === 'Completada') {
        UI.advertencia('Sesión Cerrada', 'Esta sesión ya ha sido completada y sus volúmenes consolidados.');
        return;
    }

    if (opciones.length === 0) return;

    const botones = opciones.map(o =>
        `<button class="p-3 bg-${o.color}-500/20 hover:bg-${o.color}-500/30 text-${o.color}-400 rounded-xl transition cursor-pointer text-center" data-estado="${o.valor}">
            <i class="fas ${o.icono} text-lg mb-1 block"></i>
            <span class="text-[10px] font-bold uppercase">${o.valor}</span>
        </button>`
    ).join('');

    const { isConfirmed, value } = await Swal.fire({
        ...UI.config,
        title: 'Cambiar Estado de Sesión',
        html: `<p class="text-sm text-gray-400 mb-4">Estado actual: <span class="font-bold text-white">${estadoActual}</span></p><div class="grid grid-cols-${opciones.length} gap-3">${botones}</div>`,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            document.querySelectorAll('[data-estado]').forEach(btn => {
                btn.addEventListener('click', function() {
                    Swal.close(this.dataset.estado);
                });
            });
        }
    });

    if (!isConfirmed || !value) return;

    const datos = new URLSearchParams({
        accion: 'actualizarEstado',
        id_sesion: id_sesion,
        nuevo_estado: value
    });

    const resultado = await peticionAjax('actualizarEstado', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Estado Actualizado', resultado.message);
            cargarTablaSesiones();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

function mostrarErroresFormulario(errores) {
    Object.entries(errores).forEach(([campo, mensaje]) => {
        const input = document.getElementById(campo);
        if (input) {
            input.classList.add('border-red-500');
            const errorSpan = document.createElement('p');
            errorSpan.className = 'text-red-400 text-[10px] mt-1';
            errorSpan.textContent = mensaje;
            input.parentElement.appendChild(errorSpan);
            setTimeout(() => {
                input.classList.remove('border-red-500');
                if (errorSpan.parentElement) errorSpan.remove();
            }, 5000);
        }
    });
}

function badgeTipo(tipo) {
    return `<span class="px-2 py-1 rounded-lg text-xs font-bold ${coloresTipo[tipo] || 'bg-gray-500/20 text-gray-400'}">${tipo}</span>`;
}

function badgeEstado(estado) {
    return `<span class="px-2 py-1 rounded-full text-[10px] font-bold ${coloresEstado[estado] || 'bg-gray-500/20 text-gray-400'}">${estado}</span>`;
}

function formatoFecha(fechaStr) {
    if (!fechaStr) return '-';
    const partes = fechaStr.split('-');
    if (partes.length !== 3) return fechaStr;
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
        else if (!modalCompletar.classList.contains('hidden')) cerrarModalCompletar();
        else if (!modalSesion.classList.contains('hidden')) cerrarModalSesion();
    }
});

async function cargarRecursos() {
    const [grupos, microciclos, drills] = await Promise.all([
        peticionAjax('listarGruposSelect'),
        peticionAjax('listarMicrociclosSelect'),
        peticionAjax('listarDrillsSelect')
    ]);
    
    if (grupos) gruposCache = grupos;
    if (microciclos) microciclosCache = microciclos;
    if (drills) drillsCache = drills;
}

cargarRecursos().then(() => cargarTablaSesiones());