const API_URL = 'index.php?p=eventos';

const modalEvento = document.getElementById('modalEvento');
const modalMetas = document.getElementById('modalMetas');
const modalInscripcion = document.getElementById('modalInscripcion');
const modalVer = document.getElementById('modalVer');
const formEvento = document.getElementById('formEvento');
const formMetas = document.getElementById('formMetas');

let atletasCache = [];
let categoriasCache = [];

const coloresTipo = {
    'Regional':      'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    'Nacional':      'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'Internacional': 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    'Selectivo':     'bg-orange-500/20 text-orange-400 border border-orange-500/30',
    'Control':       'bg-gray-500/20 text-gray-400 border border-gray-500/30'
};

const coloresEstado = {
    'Planificado':   'bg-indigo-500/20 text-indigo-400',
    'Inscrito':      'bg-cyan-500/20 text-cyan-400',
    'En Progreso':   'bg-yellow-500/20 text-yellow-400',
    'Finalizado':    'bg-green-500/20 text-green-400',
    'Cancelado':     'bg-red-500/20 text-red-400'
};

const opcionesEstilo = ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado'];
const opcionesDistancia = [50, 100, 200, 400, 800, 1500];

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicacion con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

// =====================================================================
// TABLA PRINCIPAL
// =====================================================================

async function cargarTablaEventos() {
    const estado = document.getElementById('filtroEstado').value || null;
    const tipo = document.getElementById('filtroTipo').value || null;

    const datos = await peticionAjax(`listarEventos&estado=${estado || ''}&tipo=${tipo || ''}`);
    const tbody = document.getElementById('tbodyEventos');

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-500 dark:text-gray-400">No se encontraron eventos.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(ev => `
        <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200 border-b border-gray-200 dark:border-[#252345]">
            <td class="p-4">
                <p class="text-gray-900 dark:text-white font-medium">${ev.nombre}</p>
                ${ev.organizador ? `<p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">${ev.organizador}</p>` : ''}
            </td>
            <td class="p-4 font-mono text-xs text-gray-700 dark:text-gray-300">${formatoFechaRango(ev.fecha_inicio, ev.fecha_fin)}</td>
            <td class="p-4 text-xs text-gray-700 dark:text-gray-300">${ev.sede || '-'}</td>
            <td class="p-4">${badgeTipo(ev.tipo)}</td>
            <td class="p-4 text-xs text-gray-700 dark:text-gray-300">${ev.nivel || '-'}</td>
            <td class="p-4">${badgeEstado(ev.estado)}</td>
            <td class="p-4 text-center">
                <span class="bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 px-2 py-1 rounded-lg text-xs font-bold">${ev.total_inscritos || 0}</span>
            </td>
            <td class="p-4 text-center">
                <span class="bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-1 rounded-lg text-xs font-bold">${ev.total_metas || 0}</span>
            </td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="verDetalle(${ev.id_evento})" class="p-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg transition cursor-pointer" title="Ver detalle">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                    <button onclick="abrirModalMetas(${ev.id_evento})" class="p-2 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-lg transition cursor-pointer" title="Metas">
                        <i class="fas fa-bullseye text-sm"></i>
                    </button>
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="abrirModalInscripcion(${ev.id_evento})" class="p-2 text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-500/10 rounded-lg transition cursor-pointer" title="Inscribir atletas">
                        <i class="fas fa-user-plus text-sm"></i>
                    </button>
                    <button onclick="abrirModalEvento(${ev.id_evento})" class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition cursor-pointer" title="Editar">
                        <i class="fas fa-pen text-sm"></i>
                    </button>
                    <button onclick="accionEstado(${ev.id_evento}, '${ev.estado}')" class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition cursor-pointer" title="Cambiar estado">
                        <i class="fas fa-exchange-alt text-sm"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function filtrarTablaEventos() {
    const texto = document.getElementById('busquedaEvento').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodyEventos tr');
    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
}

// =====================================================================
// MODAL EVENTO (CREAR / EDITAR)
// =====================================================================

function abrirModalEvento(id_evento = null) {
    formEvento.reset();
    try { Validador.limpiarEstilos(formEvento); } catch(e) {}
    document.getElementById('id_evento').value = '';
    document.getElementById('contenedorTiemposCorte').innerHTML = '';

    if (id_evento) {
        document.getElementById('modalEventoTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Evento';
        peticionAjax(`obtenerDetalle&id=${id_evento}`).then(detalle => {
            if (!detalle) return;
            document.getElementById('id_evento').value = detalle.id_evento;
            document.getElementById('nombre').value = detalle.nombre || '';
            document.getElementById('fecha_inicio').value = detalle.fecha_inicio || '';
            document.getElementById('fecha_fin').value = detalle.fecha_fin || '';
            document.getElementById('sede').value = detalle.sede || '';
            document.getElementById('organizador').value = detalle.organizador || '';
            document.getElementById('tipo').value = detalle.tipo || 'Control';
            document.getElementById('nivel').value = detalle.nivel || '';
            document.getElementById('estado').value = detalle.estado || 'Planificado';
            document.getElementById('observaciones').value = detalle.observaciones || '';

            if (detalle.tiempos_corte && detalle.tiempos_corte.length > 0) {
                detalle.tiempos_corte.forEach(tc => agregarFilaTiempoCorte(tc));
            }
        });
    } else {
        document.getElementById('modalEventoTitulo').innerHTML = '<i class="fas fa-calendar-plus text-emerald-400"></i> Registrar Evento';
    }

    modalEvento.classList.remove('hidden');
    setTimeout(() => modalEvento.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalEvento() {
    modalEvento.classList.add('hidden');
    modalEvento.firstElementChild.classList.add('scale-95', 'opacity-0');
    formEvento.reset();
    try { Validador.limpiarEstilos(formEvento); } catch(e) {}
    document.getElementById('contenedorTiemposCorte').innerHTML = '';
}

function agregarFilaTiempoCorte(datos = null) {
    const contenedor = document.getElementById('contenedorTiemposCorte');
    const fila = document.createElement('div');
    fila.className = 'grid grid-cols-5 gap-2 items-center';

    const opcionesCat = categoriasCache.map(c => `<option value="${c.id_categoria}" ${datos && datos.id_categoria == c.id_categoria ? 'selected' : ''}>${c.nombre}</option>`).join('');
    const opcionesEst = opcionesEstilo.map(e => `<option value="${e}" ${datos && datos.estilo === e ? 'selected' : ''}>${e}</option>`).join('');
    const opcionesDist = opcionesDistancia.map(d => `<option value="${d}" ${datos && datos.distancia == d ? 'selected' : ''}>${d}m</option>`).join('');

    fila.innerHTML = `
        <select name="tc_categoria[]" data-validar="requerido" data-nombre="Categoria" class="input-dark p-2 rounded-lg text-xs">${opcionesCat}</select>
        <select name="tc_estilo[]" data-validar="requerido" data-nombre="Estilo" class="input-dark p-2 rounded-lg text-xs">${opcionesEst}</select>
        <select name="tc_distancia[]" data-validar="requerido" data-nombre="Distancia" class="input-dark p-2 rounded-lg text-xs">${opcionesDist}</select>
        <input type="number" step="0.01" name="tc_tiempo[]" data-validar="requerido|decimal" data-nombre="Tiempo de corte" placeholder="Segundos" value="${datos ? datos.tiempo_corte_segundos : ''}" class="input-dark p-2 rounded-lg text-xs font-mono text-center">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 p-2 transition cursor-pointer"><i class="fas fa-trash-alt"></i></button>
    `;

    contenedor.appendChild(fila);
}

formEvento.addEventListener('submit', async function(e) {
    e.preventDefault();

    const erroresJS = Validador.validarFormulario(formEvento);
    if (erroresJS) {
        UI.advertencia('Datos Incompletos', erroresJS);
        return;
    }

    const id_evento = document.getElementById('id_evento').value;
    const formData = new FormData(formEvento);

    const tiempos_corte = [];
    const cats = formData.getAll('tc_categoria[]');
    const ests = formData.getAll('tc_estilo[]');
    const dists = formData.getAll('tc_distancia[]');
    const tiempos = formData.getAll('tc_tiempo[]');

    for (let i = 0; i < cats.length; i++) {
        if (cats[i] && ests[i] && dists[i]) {
            tiempos_corte.push({
                id_categoria: cats[i],
                estilo: ests[i],
                distancia: dists[i],
                tiempo_corte_segundos: tiempos[i] || null
            });
        }
    }

    if (id_evento) {
        formData.append('accion', 'editar');
        formData.append('id_evento', id_evento);
        formData.append('tiempos_corte', JSON.stringify(tiempos_corte));

        const resultado = await peticionAjax('editar', formData);
        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Evento Actualizado', resultado.message);
                cerrarModalEvento();
                cargarTablaEventos();
            } else if (resultado.status === 'warning') {
                mostrarErroresFormulario(resultado.errores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
    } else {
        formData.append('accion', 'guardar');
        formData.append('tiempos_corte', JSON.stringify(tiempos_corte));

        const resultado = await peticionAjax('guardar', formData);
        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Evento Registrado', resultado.message);
                cerrarModalEvento();
                cargarTablaEventos();
            } else if (resultado.status === 'warning') {
                mostrarErroresFormulario(resultado.errores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
    }
});

// =====================================================================
// MODAL METAS
// =====================================================================

function abrirModalMetas(id_evento) {
    document.getElementById('id_evento_metas').value = id_evento;
    document.getElementById('tbodyMetas').innerHTML = '';

    peticionAjax(`obtenerDetalle&id=${id_evento}`).then(detalle => {
        if (!detalle) return;
        document.getElementById('tituloModalMetas').textContent = `Metas: ${detalle.nombre}`;

        if (detalle.metas && detalle.metas.length > 0) {
            detalle.metas.forEach(m => {
                agregarFilaMeta({
                    id_meta: m.id_meta,
                    id_atleta: m.id_atleta,
                    nombre_atleta: m.nombre_atleta,
                    estilo: m.estilo,
                    distancia: m.distancia,
                    marca_objetivo_seg: m.marca_objetivo_seg,
                    pb_actual_seg: m.pb_actual_seg,
                    diferencia_pct: m.diferencia_pct
                });
            });
        }
    });

    modalMetas.classList.remove('hidden');
    setTimeout(() => modalMetas.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalMetas() {
    modalMetas.classList.add('hidden');
    modalMetas.firstElementChild.classList.add('scale-95', 'opacity-0');
    document.getElementById('tbodyMetas').innerHTML = '';
}

function agregarFilaMeta(datos = null) {
    const tbody = document.getElementById('tbodyMetas');
    const tr = document.createElement('tr');

    const opcionesEst = opcionesEstilo.map(e => `<option value="${e}" ${datos && datos.estilo === e ? 'selected' : ''}>${e}</option>`).join('');
    const opcionesDist = opcionesDistancia.map(d => `<option value="${d}" ${datos && datos.distancia == d ? 'selected' : ''}>${d}m</option>`).join('');

    tr.innerHTML = `
        <td class="p-2">
            <input type="hidden" name="meta_id_atleta[]" value="${datos ? datos.id_atleta : ''}" class="meta-id-atleta">
            <input type="hidden" name="meta_id_meta[]" value="${datos ? datos.id_meta || '' : ''}" class="meta-id-meta">
            <div class="relative">
                <input type="text" name="meta_atleta_nombre[]" data-validar="requerido" data-nombre="Atleta" value="${datos ? datos.nombre_atleta || '' : ''}" placeholder="Buscar atleta..." class="input-adapt p-2 rounded-lg text-xs w-40 meta-nombre-atleta" autocomplete="off">
                <div class="dropdown-atleta-meta absolute z-50 w-full mt-1 bg-white dark:bg-[#111026] border border-gray-200 dark:border-[#252345] rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
            </div>
        </td>
        <td class="p-2"><select name="meta_estilo[]" data-validar="requerido" data-nombre="Estilo" class="input-adapt p-2 rounded-lg text-xs">${opcionesEst}</select></td>
        <td class="p-2"><select name="meta_distancia[]" data-validar="requerido" data-nombre="Distancia" class="input-adapt p-2 rounded-lg text-xs">${opcionesDist}</select></td>
        <td class="p-2"><input type="number" step="0.01" name="meta_objetivo[]" data-validar="requerido|decimal" data-nombre="Marca objetivo" value="${datos ? datos.marca_objetivo_seg || '' : ''}" placeholder="0.00" class="input-adapt p-2 rounded-lg text-xs font-mono text-center w-20"></td>
        <td class="p-2"><span class="text-xs font-mono text-gray-500 dark:text-gray-400 meta-pb">${datos && datos.pb_actual_seg ? datos.pb_actual_seg : '-'}</span></td>
        <td class="p-2"><span class="text-xs font-mono meta-dif ${datos && datos.diferencia_pct !== null ? (datos.diferencia_pct <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400') : 'text-gray-500 dark:text-gray-400'}">${datos && datos.diferencia_pct !== null ? datos.diferencia_pct + '%' : '-'}</span></td>
        <td class="p-2"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition cursor-pointer"><i class="fas fa-trash-alt"></i></button></td>
    `;

    tbody.appendChild(tr);

    const inputNombre = tr.querySelector('.meta-nombre-atleta');
    const dropdown = tr.querySelector('.dropdown-atleta-meta');
    const hiddenId = tr.querySelector('.meta-id-atleta');

    inputNombre.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (query.length < 2) { dropdown.classList.add('hidden'); return; }

        const resultados = atletasCache.filter(a =>
            a.nombres.toLowerCase().includes(query) ||
            a.apellidos.toLowerCase().includes(query) ||
            a.cedula.toLowerCase().includes(query)
        ).slice(0, 8);

        if (resultados.length === 0) { dropdown.classList.add('hidden'); return; }

        dropdown.innerHTML = resultados.map(a =>
            `<div class="px-3 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 cursor-pointer text-xs text-gray-800 dark:text-gray-300 transition" data-id="${a.id_atleta}" data-nombre="${a.nombres} ${a.apellidos}">${a.nombres} ${a.apellidos} <span class="text-gray-500 dark:text-gray-400">(${a.cedula})</span></div>`
        ).join('');
        dropdown.classList.remove('hidden');

        dropdown.querySelectorAll('div[data-id]').forEach(opt => {
            opt.addEventListener('click', function() {
                hiddenId.value = this.dataset.id;
                inputNombre.value = this.dataset.nombre;
                dropdown.classList.add('hidden');
            });
        });
    });

    inputNombre.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 200));
}

formMetas.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id_evento = document.getElementById('id_evento_metas').value;
    const filas = document.querySelectorAll('#tbodyMetas tr');

    const metas = [];
    filas.forEach(fila => {
        const idAtleta = fila.querySelector('.meta-id-atleta').value;
        const estilo = fila.querySelector('[name="meta_estilo[]"]').value;
        const distancia = fila.querySelector('[name="meta_distancia[]"]').value;
        const objetivo = fila.querySelector('[name="meta_objetivo[]"]').value;

        if (idAtleta && estilo && distancia && objetivo) {
            metas.push({
                id_atleta: idAtleta,
                estilo: estilo,
                distancia: distancia,
                marca_objetivo_seg: objetivo
            });
        }
    });

    if (metas.length === 0) {
        UI.advertencia('Sin Metas', 'Debe agregar al menos una meta con atleta, estilo, distancia y objetivo.');
        return;
    }

    const datos = new URLSearchParams({
        accion: 'guardarMetas',
        id_evento: id_evento,
        metas: JSON.stringify(metas)
    });

    const resultado = await peticionAjax('guardarMetas', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Metas Guardadas', resultado.message);
            cerrarModalMetas();
            cargarTablaEventos();
        } else if (resultado.status === 'warning') {
            UI.advertencia('Validacion', 'Revise los campos: ' + JSON.stringify(resultado.errores));
        } else {
            UI.error('Error', resultado.message);
        }
    }
});

// =====================================================================
// MODAL INSCRIPCION
// =====================================================================

async function abrirModalInscripcion(id_evento) {
    document.getElementById('id_evento_inscripcion').value = id_evento;
    document.getElementById('busquedaInscripcion').value = '';
    document.getElementById('listaAtletasInscripcion').innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Cargando atletas...</p>';

    const [atletas, detalle] = await Promise.all([
        peticionAjax('listarAtletasSelect'),
        peticionAjax(`obtenerDetalle&id=${id_evento}`)
    ]);

    if (!atletas || !detalle) return;

    const inscritosIds = (detalle.inscripciones || []).map(i => i.id_atleta);

    const contenedor = document.getElementById('listaAtletasInscripcion');
    contenedor.innerHTML = atletas.filter(a => a.estado === 'Activo').map(a => {
        const inscrito = inscritosIds.includes(String(a.id_atleta));
        return `
            <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition cursor-pointer ${inscrito ? 'opacity-50' : ''}">
                <input type="checkbox" class="check-inscripcion accent-indigo-500" value="${a.id_atleta}" ${inscrito ? 'checked disabled' : ''}>
                <div>
                    <p class="text-sm text-white">${a.nombres} ${a.apellidos}</p>
                    <p class="text-[10px] text-gray-500">${a.cedula} ${a.categoria_nombre ? '- ' + a.categoria_nombre : ''} ${inscrito ? '(Ya inscrito)' : ''}</p>
                </div>
            </label>
        `;
    }).join('');

    modalInscripcion.classList.remove('hidden');
    setTimeout(() => modalInscripcion.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalInscripcion() {
    modalInscripcion.classList.add('hidden');
    modalInscripcion.firstElementChild.classList.add('scale-95', 'opacity-0');
}

function filtrarInscripciones() {
    const query = document.getElementById('busquedaInscripcion').value.toLowerCase();
    document.querySelectorAll('#listaAtletasInscripcion label').forEach(label => {
        label.style.display = label.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}

async function inscribirAtletas() {
    const id_evento = document.getElementById('id_evento_inscripcion').value;
    const checkboxes = document.querySelectorAll('.check-inscripcion:checked');

    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        UI.advertencia('Sin Seleccion', 'Seleccione al menos un atleta para inscribir.');
        return;
    }

    const datos = new URLSearchParams({
        accion: 'inscribirAtletas',
        id_evento: id_evento,
        atletas_ids: JSON.stringify(ids)
    });

    const resultado = await peticionAjax('inscribirAtletas', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Inscripcion Exitosa', resultado.message);
            cerrarModalInscripcion();
            cargarTablaEventos();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// =====================================================================
// DETALLE
// =====================================================================
async function verDetalle(id_evento) {
    const detalle = await peticionAjax(`obtenerDetalle&id=${id_evento}`);
    if (!detalle) { UI.error('Error', 'No se pudo obtener el detalle.'); return; }

    const contenedor = document.getElementById('detalleContenido');

    let html = `
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">${detalle.nombre}</h2>
            <div class="flex flex-wrap gap-2 mt-2">
                ${badgeTipo(detalle.tipo)}
                ${badgeEstado(detalle.estado)}
                ${detalle.nivel ? `<span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-50 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30">Nivel ${detalle.nivel}</span>` : ''}
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Inicio</p><p class="text-gray-900 dark:text-white font-mono text-sm">${formatoFecha(detalle.fecha_inicio)}</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Fin</p><p class="text-gray-900 dark:text-white font-mono text-sm">${formatoFecha(detalle.fecha_fin)}</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Sede</p><p class="text-gray-900 dark:text-white text-sm">${detalle.sede || '-'}</p></div>
            <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Organizador</p><p class="text-gray-900 dark:text-white text-sm">${detalle.organizador || '-'}</p></div>
        </div>
    `;

    if (detalle.tiempos_corte && detalle.tiempos_corte.length > 0) {
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 mb-3"><i class="fas fa-cut mr-2"></i>Tiempos de Corte</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead><tr class="text-gray-600 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                            <th class="p-2">Categoria</th><th class="p-2">Estilo</th><th class="p-2">Distancia</th><th class="p-2">Tiempo Corte</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-800 dark:text-gray-300">
                            ${detalle.tiempos_corte.map(tc => `
                                <tr>
                                    <td class="p-2">${tc.categoria_nombre || '-'}</td>
                                    <td class="p-2">${tc.estilo}</td>
                                    <td class="p-2">${tc.distancia}m</td>
                                    <td class="p-2 font-mono">${tc.tiempo_corte_segundos || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (detalle.metas && detalle.metas.length > 0) {
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 mb-3"><i class="fas fa-bullseye mr-2"></i>Metas Competitivas</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead><tr class="text-gray-600 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                            <th class="p-2">Atleta</th><th class="p-2">Estilo</th><th class="p-2">Distancia</th><th class="p-2">Objetivo</th><th class="p-2">PB</th><th class="p-2">Dif %</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-800 dark:text-gray-300">
                            ${detalle.metas.map(m => `
                                <tr>
                                    <td class="p-2 text-gray-900 dark:text-white">${m.nombre_atleta}</td>
                                    <td class="p-2">${m.estilo}</td>
                                    <td class="p-2">${m.distancia}m</td>
                                    <td class="p-2 font-mono">${m.marca_objetivo_seg || '-'}</td>
                                    <td class="p-2 font-mono">${m.pb_actual_seg || '-'}</td>
                                    <td class="p-2 font-mono ${m.diferencia_pct !== null ? (m.diferencia_pct <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400') : 'text-gray-500 dark:text-gray-400'}">${m.diferencia_pct !== null ? m.diferencia_pct + '%' : '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    if (detalle.inscripciones && detalle.inscripciones.length > 0) {
        html += `
            <div class="mb-6">
                <h4 class="text-sm font-bold text-cyan-600 dark:text-cyan-400 mb-3"><i class="fas fa-users mr-2"></i>Atletas Inscritos (${detalle.inscripciones.length})</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    ${detalle.inscripciones.map(i => `
                        <div class="flex items-center justify-between p-2 bg-gray-100 dark:bg-black/20 rounded-lg border border-gray-200 dark:border-white/5">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-white">${i.nombre_atleta}</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">${i.cedula}</p>
                            </div>
                            <button onclick="quitarInscripcion(${detalle.id_evento}, ${i.id_atleta})" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-1 transition cursor-pointer text-xs" title="Quitar inscripcion">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    if (detalle.observaciones) {
        html += `
            <div class="p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Observaciones</p>
                <p class="text-sm text-gray-800 dark:text-gray-300">${detalle.observaciones}</p>
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

async function quitarInscripcion(id_evento, id_atleta) {
    const { value: confirmado } = await UI.confirmar('Quitar Inscripcion', 'Se eliminara la inscripcion del atleta. Continuar?');
    if (!confirmado) return;

    const datos = new URLSearchParams({
        accion: 'eliminarInscripcion',
        id_evento: id_evento,
        id_atleta: id_atleta
    });

    const resultado = await peticionAjax('eliminarInscripcion', datos);
    if (resultado && resultado.status === 'success') {
        UI.exito('Inscripcion Eliminada', 'El atleta fue removido del evento.');
        verDetalle(id_evento);
        cargarTablaEventos();
    }
}

// =====================================================================
// ACCIONES DE ESTADO
// =====================================================================

async function accionEstado(id_evento, estadoActual) {
    let opciones = [];

    if (estadoActual === 'Planificado') {
        opciones = [
            { valor: 'Inscrito', icono: 'fa-user-check', color: 'cyan' },
            { valor: 'Cancelado', icono: 'fa-ban', color: 'red' }
        ];
    } else if (estadoActual === 'Inscrito') {
        opciones = [
            { valor: 'En Progreso', icono: 'fa-play', color: 'yellow' },
            { valor: 'Cancelado', icono: 'fa-ban', color: 'red' }
        ];
    } else if (estadoActual === 'En Progreso') {
        opciones = [
            { valor: 'Finalizado', icono: 'fa-flag-checkered', color: 'green' },
            { valor: 'Cancelado', icono: 'fa-ban', color: 'red' }
        ];
    } else if (estadoActual === 'Cancelado') {
        opciones = [
            { valor: 'Planificado', icono: 'fa-redo', color: 'indigo' }
        ];
    } else if (estadoActual === 'Finalizado') {
        UI.advertencia('Estado Terminal', 'Este evento ya fue finalizado. No se puede cambiar el estado.');
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
        title: 'Cambiar Estado',
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
        id_evento: id_evento,
        nuevo_estado: value
    });

    const resultado = await peticionAjax('actualizarEstado', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Estado Actualizado', resultado.message);
            cargarTablaEventos();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// =====================================================================
// UTILIDADES
// =====================================================================

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
            }, 4000);
        }
    });
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
        else if (!modalMetas.classList.contains('hidden')) cerrarModalMetas();
        else if (!modalInscripcion.classList.contains('hidden')) cerrarModalInscripcion();
        else if (!modalEvento.classList.contains('hidden')) cerrarModalEvento();
    }
});

async function cargarRecursos() {
    const [atletas, categorias] = await Promise.all([
        peticionAjax('listarAtletasSelect'),
        peticionAjax('listarCategorias')
    ]);
    if (atletas) atletasCache = atletas;
    if (categorias) categoriasCache = categorias;
}

cargarRecursos().then(() => cargarTablaEventos());
try { Validador.vincularTiempoReal(formEvento); } catch(e) {}
