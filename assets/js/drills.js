const API_URL = 'index.php?p=drills'; 

const modalDrills = document.getElementById('modalDrills');
const formDrills = document.getElementById('formDrills');
const btnGuardar = document.getElementById('btnGuardar');
const totalDrills = document.getElementById('totalDrills');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

let drillsData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

let atletasList = [];
let atletaSeleccionadoId = '';

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        if (typeof UI !== 'undefined') {
            UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        } else {
            alert('Error del Servidor: No se pudo procesar la solicitud.');
        }
        return null;
    }
}

function cerrarModalDrills() {
    modalDrills.classList.add('hidden');
    modalDrills.firstElementChild.classList.add('scale-95', 'opacity-0');
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalDrills.classList.contains('hidden')) {
        cerrarModalDrills();
    }
});

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

async function cargarAtletasSelect() {
    if (atletasList.length > 0) return;
    
    const atletas = await peticionAjax('listarAtletas');
    
    if (Array.isArray(atletas)) {
        atletasList = atletas;
    } else {
        console.error("El servidor no devolvió un arreglo de atletas.");
    }
}

function toggleSelectAtleta(mostrar) {
    const wrapper = document.getElementById('wrapperSelectAtleta');
    if (wrapper) {
        if (mostrar) wrapper.classList.remove('hidden');
        else wrapper.classList.add('hidden');
    }
}

function filtrarAtletas(input) {
    const query = input.value.toLowerCase().trim();
    const lista = document.getElementById('listaAtletasDropdown');
    if (!lista) return;

    if (atletasList.length === 0) {
        cargarAtletasSelect().then(() => filtrarAtletas(input));
        return;
    }

    const filtrados = atletasList.filter(a => {
        const nombre = (a.nombre_completo || '').toLowerCase();
        const cedula = (a.cedula || '').toLowerCase();
        return nombre.includes(query) || cedula.includes(query);
    });

    if (filtrados.length === 0) {
        lista.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No se encontraron atletas</div>';
    } else {
        lista.innerHTML = filtrados.map(a => `
            <div onclick="seleccionarAtleta(${a.id_usuario}, '${escapeHtml(a.nombre_completo)}')" 
                 class="p-3 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors">
                <p class="font-bold text-sm text-gray-900 dark:text-white">${escapeHtml(a.nombre_completo)}</p>
                <p class="text-[11px] text-gray-500">C.I: ${escapeHtml(a.cedula || 'N/A')}</p>
            </div>
        `).join('');
    }
    
    lista.style.display = 'block';
}

function seleccionarAtleta(id, nombreCompleto) {
    atletaSeleccionadoId = id;
    document.getElementById('buscarAtletaInput').value = nombreCompleto;
    document.getElementById('id_usuario_creador').value = id;
    
    const lista = document.getElementById('listaAtletasDropdown');
    if (lista) lista.style.display = 'none';
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('wrapperSelectAtleta');
    if (wrapper && !wrapper.contains(e.target)) {
        const lista = document.getElementById('listaAtletasDropdown');
        if (lista) lista.style.display = 'none';
    }
});

async function abrirModalDrills(id_drill = null) {
    formDrills.reset(); 
    try { Validador.limpiarEstilos(formDrills); } catch(e) {}
  
    atletaSeleccionadoId = '';
    document.getElementById('id_usuario_creador').value = '';
    const inputAtleta = document.getElementById('buscarAtletaInput');
    if (inputAtleta) inputAtleta.value = '';
    
    const dropdownAtletas = document.getElementById('listaAtletasDropdown');
    if (dropdownAtletas) dropdownAtletas.style.display = 'none';
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_drill');
    const modalTitulo = document.getElementById('modalTitulo');

    await cargarAtletasSelect();

    if (id_drill) {
        if (inputAction) inputAction.value = 'editar';
        if (inputIdHidden) inputIdHidden.value = id_drill;
        if (modalTitulo) modalTitulo.textContent = 'Editar Datos del Entrenamiento';
        
        btnGuardar.innerHTML = 'Editar los datos <i class="fas fa-sync-alt ml-2"></i>';
        
        const drill = await peticionAjax(`obtenerDrills&id=${id_drill}`);
        
        if (drill) {
            document.getElementById('nombre').value = drill.nombre || '';
            document.getElementById('estilo').value = drill.estilo || 'Libre';
            document.getElementById('categoria').value = drill.categoria || 'Tecnico';
            document.getElementById('enfoque_tecnico').value = drill.enfoque_tecnico || '';
            document.getElementById('descripcion').value = drill.descripcion || '';
            document.getElementById('instrucciones').value = drill.instrucciones || '';
            document.getElementById('metraje_sugerido').value = drill.metraje_sugerido || '';
            document.getElementById('dificultad').value = drill.dificultad || 'Basico';
            document.getElementById('material_requerido').value = drill.material_requerido || 'Ninguno';
            
            const esPersonalizado = parseInt(drill.personalizado) === 1;
            document.getElementById('personalizado').checked = esPersonalizado;
            document.getElementById('activo').checked = parseInt(drill.activo) === 1;
            
            if (drill.id_usuario_creador && esPersonalizado) {
                const atleta = atletasList.find(a => a.id_usuario == drill.id_usuario_creador);
                if (atleta) {
                    seleccionarAtleta(atleta.id_usuario, atleta.nombre_completo);
                }
            }
            toggleSelectAtleta(esPersonalizado);
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Entrenamiento';
        document.getElementById('personalizado').checked = false;
        document.getElementById('activo').checked = true;
        toggleSelectAtleta(false);
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    pasoActualDrill = 1;
    pasosCompletadosDrill.clear();
    cambiarTabDrill('basica');
  
    actualizarContadores();

    modalDrills.classList.remove('hidden');
    setTimeout(() => {
        modalDrills.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaDrills() {
    const tbody = document.getElementById('listaDrills');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="8" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const drills = await peticionAjax(`listarDrills&inactivos=1`);

    if (!drills || drills.length === 0) {
        drillsData = [];
        if (totalDrills) totalDrills.textContent = '0 Activos';
        if (infoTabla) infoTabla.textContent = '';
        if (pieTabla) pieTabla.innerHTML = '';
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-dumbbell text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay entrenamientos registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    drillsData = drills;
    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;
    renderTabla();
}

function renderTabla() {
    const tbody = document.getElementById('listaDrills');
    if (!tbody) return;

    let datos = drillsData.slice();

    const chkInactivos = document.getElementById('chkVerInactivos');
    const verInactivos = chkInactivos && chkInactivos.checked;

    if (verInactivos) {
        datos = datos.filter(ent => parseInt(ent.activo) === 0);
    } else {
        datos = datos.filter(ent => parseInt(ent.activo) === 1);
    }

    if (tablaFiltro) {
        const q = tablaFiltro;
        datos = datos.filter(ent =>
            (ent.nombre + ' ' + ent.estilo + ' ' + ent.categoria + ' ' + ent.material_requerido).toLowerCase().includes(q)
        );
    }

    if (tablaSortCol) {
        const col = tablaSortCol;
        const dir = tablaSortDir === 'asc' ? 1 : -1;
        datos.sort((a, b) => {
            let va = a[col] ? a[col].toString() : '';
            let vb = b[col] ? b[col].toString() : '';
            return va.localeCompare(vb, 'es') * dir;
        });
    }

    const total = datos.length;
    const totalActivos = drillsData.filter(e => parseInt(e.activo) === 1).length;
    const totalInactivos = drillsData.filter(e => parseInt(e.activo) === 0).length;

    if (totalDrills) {
        totalDrills.textContent = verInactivos
            ? `${totalInactivos} Inactivos`
            : `${totalActivos} Activos`;
    }
    if (infoTabla) {
        infoTabla.textContent = `Mostrando ${total === 0 ? 0 : (tablaPagina - 1) * tablaPorPagina + 1}–${Math.min(tablaPagina * tablaPorPagina, total)} de ${total}`;
    }

    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;
    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    actualizarSortIcons();

    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-dumbbell text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">${verInactivos ? 'No hay entrenamientos inactivos' : 'No hay entrenamientos activos registrados'}</span>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pagina.map(ent => {
            const badgePersonalizado = parseInt(ent.personalizado) === 1 
                ? `<span class="px-2 py-1 text-[10px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-md border border-purple-200 dark:border-purple-500/20">SÍ</span>`
                : `<span class="px-2 py-1 text-[10px] font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-md">NO</span>`;

            const badgeActivo = parseInt(ent.activo) === 1 
                ? `<span class="px-2 py-1 text-[10px] font-bold bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 rounded-md border border-green-200 dark:border-green-500/20">ACTIVO</span>`
                : `<span class="px-2 py-1 text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-md border border-red-200 dark:border-red-500/20">INACTIVO</span>`;

            const badgeDificultad = `<span class="badge-dificultad dificultad-${ent.dificultad}">${ent.dificultad}</span>`;
            const esActivo = parseInt(ent.activo) === 1;

            const botonEstado = esActivo
                ? `<button onclick="cambiarEstadoDrill(${ent.id_drill}, ${ent.activo})" class="bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 p-2 rounded-lg transition" title="Desactivar">
                        <i class="fas fa-ban text-xs"></i>
                   </button>`
                : `<button onclick="cambiarEstadoDrill(${ent.id_drill}, ${ent.activo})" class="bg-green-50 dark:bg-green-500/10 hover:bg-green-100 dark:hover:bg-green-500/20 text-green-600 dark:text-green-400 p-2 rounded-lg transition" title="Activar">
                        <i class="fas fa-undo text-xs"></i>
                   </button>`;

            return `
                <tr class="drills-row border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-[#1c1a3a]/40 transition-colors duration-200">
                    <td class="p-4 font-medium text-gray-900 dark:text-white max-w-[180px] truncate" title="${escapeHtml(ent.nombre)}">${escapeHtml(ent.nombre)}</td>
                    <td class="p-4 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(ent.estilo)}</td>
                    <td class="p-4 text-gray-600 dark:text-gray-400 text-xs">${escapeHtml(ent.categoria)}</td>
                    <td class="p-4">${badgeDificultad}</td>
                    <td class="p-4 text-gray-600 dark:text-gray-400 text-xs">${escapeHtml(ent.material_requerido)}</td>
                    <td class="p-4 text-center">${badgePersonalizado}</td>
                    <td class="p-4 text-center">${badgeActivo}</td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="verDetalleDrill(${ent.id_drill})" class="bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2 rounded-lg transition" title="Ver Detalles">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            <button onclick="abrirModalDrills(${ent.id_drill})" class="bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            ${botonEstado}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    renderPaginacion(totalPaginas);
}

function renderPaginacion(totalPaginas) {
    if (!pieTabla || totalPaginas <= 1) { 
        if (pieTabla) pieTabla.innerHTML = ''; 
        return; 
    }

    let html = `<span class="text-xs text-gray-500 dark:text-gray-400">Página ${tablaPagina} de ${totalPaginas}</span><div class="flex gap-1">`;

    const btnClass = 'px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition';
    const btnActivo = 'bg-indigo-600 text-white';
    const btnInactivo = 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-700';

    if (tablaPagina > 1) {
        html += `<button onclick="tablaPagina--; renderTabla()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-left"></i></button>`;
    }

    const maxVisible = 5;
    let start = Math.max(1, tablaPagina - Math.floor(maxVisible / 2));
    let end = Math.min(totalPaginas, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);

    for (let i = start; i <= end; i++) {
        if (i === tablaPagina) {
            html += `<button class="${btnClass} ${btnActivo}">${i}</button>`;
        } else {
            html += `<button onclick="tablaPagina=${i}; renderTabla()" class="${btnClass} ${btnInactivo}">${i}</button>`;
        }
    }

    if (tablaPagina < totalPaginas) {
        html += `<button onclick="tablaPagina++; renderTabla()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-right"></i></button>`;
    }

    html += '</div>';
    pieTabla.innerHTML = html;
}

function actualizarSortIcons() {
    document.querySelectorAll('[data-sort]').forEach(th => {
        const icon = th.querySelector('i');
        if (!icon) return;
        icon.className = 'fas fa-sort ml-1 text-gray-600 text-[10px]';
        if (th.getAttribute('data-sort') === tablaSortCol) {
            icon.className = `fas fa-sort-${tablaSortDir === 'asc' ? 'up' : 'down'} ml-1 text-indigo-400 text-[10px]`;
        }
    });
}

async function verDetalleDrill(id) {
    const drill = await peticionAjax(`obtenerDrills&id=${id}`);
    
    if (!drill) {
        if (typeof UI !== 'undefined') {
            UI.error('Error', 'No se pudo cargar la información del entrenamiento.');
        } else {
            alert('Error: No se pudo cargar la información del entrenamiento.');
        }
        return;
    }

    const badgePersonalizado = parseInt(drill.personalizado) === 1 
        ? `<span class="px-2 py-1 text-[10px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-md border border-purple-200 dark:border-purple-500/20">PERSONALIZADO</span>`
        : `<span class="px-2 py-1 text-[10px] font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-md">ESTÁNDAR</span>`;

    const badgeActivo = parseInt(drill.activo) === 1 
        ? `<span class="estado-activo px-3 py-1 rounded-full text-xs font-bold">ACTIVO</span>`
        : `<span class="estado-inactivo px-3 py-1 rounded-full text-xs font-bold">INACTIVO</span>`;

    const badgeDificultad = `<span class="badge-dificultad dificultad-${drill.dificultad}">${drill.dificultad}</span>`;

    const infoAtleta = (parseInt(drill.personalizado) === 1 && drill.nombre_atleta) 
        ? `<div class="col-span-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
             <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-1">Atleta Asignado</p>
             <p class="text-gray-900 dark:text-white font-medium"><i class="fas fa-user-circle mr-1"></i> ${escapeHtml(drill.nombre_atleta)}</p>
           </div>` 
        : '';

    const html = `
        <div class="text-center mb-8">
            <div class="w-28 h-28 rounded-full mx-auto mb-4 bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20">
                <i class="fas fa-dumbbell"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">${escapeHtml(drill.nombre)}</h2>
            <div class="flex justify-center gap-2 mt-3 flex-wrap">
                ${badgeDificultad}
                ${badgeActivo}
                ${badgePersonalizado}
            </div>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-3"><i class="fas fa-tags mr-2"></i>Clasificación</p>
            <div class="grid grid-cols-2 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Estilo</p><p class="text-gray-900 dark:text-white">${escapeHtml(drill.estilo)}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Categoría</p><p class="text-indigo-600 dark:text-indigo-300">${escapeHtml(drill.categoria)}</p></div>
                <div class="col-span-2"><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Enfoque Técnico</p><p class="text-gray-900 dark:text-white text-sm">${escapeHtml(drill.enfoque_tecnico)}</p></div>
                ${infoAtleta}
            </div>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-3"><i class="fas fa-align-left mr-2"></i>Descripción</p>
            <div class="bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <p class="text-gray-800 dark:text-gray-300 text-sm leading-relaxed">${escapeHtml(drill.descripcion) || 'Sin descripción registrada'}</p>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-amber-600 dark:text-amber-400 font-bold tracking-widest mb-3"><i class="fas fa-tasks mr-2"></i>Instrucciones</p>
            <div class="bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <p class="text-gray-800 dark:text-gray-300 text-sm leading-relaxed whitespace-pre-line">${escapeHtml(drill.instrucciones) || 'Sin instrucciones registradas'}</p>
            </div>
        </div>

        <div>
            <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-3"><i class="fas fa-chart-line mr-2"></i>Especificaciones Técnicas</p>
            <div class="grid grid-cols-2 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Metraje Sugerido</p><p class="text-indigo-600 dark:text-indigo-300 font-mono font-bold">${escapeHtml(drill.metraje_sugerido) || 'No especificado'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Material Requerido</p><p class="text-gray-900 dark:text-white">${escapeHtml(drill.material_requerido) || 'Ninguno'}</p></div>
                <div class="col-span-2"><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Fecha de Creación</p><p class="text-gray-700 dark:text-gray-400 text-sm">${drill.fecha_creacion ? new Date(drill.fecha_creacion).toLocaleString() : 'No registrada'}</p></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-xl mt-4">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wider mb-2">Información del Creador</span>
            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">ID Usuario: ${drill.id_usuario_creador || '1'}</span>
        </div>
    `;

    const detalleContenido = document.getElementById('detalleDrillContenido');
    if (detalleContenido) {
        detalleContenido.innerHTML = html;
    }
    
    const modalVer = document.getElementById('modalVerDrill');
    if (modalVer) {
        modalVer.classList.remove('hidden');
        setTimeout(() => {
            const child = modalVer.firstElementChild;
            if (child) child.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

function cerrarModalVerDrill() {
    const modalVer = document.getElementById('modalVerDrill');
    if (!modalVer) return;
    const child = modalVer.firstElementChild;
    if (child) child.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { modalVer.classList.add('hidden'); }, 200);
}

async function cambiarEstadoDrill(id, estadoActual) {
    const nuevoEstado = parseInt(estadoActual) === 1 ? 0 : 1;
    const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
    const color = nuevoEstado === 1 ? '#10b981' : '#ef4444';

    const confirmacion = await Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} entrenamiento?`,
        text: `El drill será marcado como ${nuevoEstado === 1 ? 'ACTIVO' : 'INACTIVO'}.`,
        icon: nuevoEstado === 1 ? 'success' : 'warning',
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    let datos = new FormData();
    datos.append('id_drill', id);
    datos.append('estado', nuevoEstado);

    const resultado = await peticionAjax('cambiarEstado', datos);
    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') UI.exito('Estado actualizado', resultado.message);
        else Swal.fire('Éxito', resultado.message, 'success');
        cargarTablaDrills();
    } else {
        if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo cambiar el estado');
        else Swal.fire('Error', resultado?.message || 'No se pudo cambiar el estado', 'error');
    }
}

const PASOS_DRILL = ['basica', 'detalles', 'configuracion'];
const CAMPOS_PASO_DRILL = {
    1: ['nombre', 'enfoque_tecnico', 'descripcion'],
    2: ['instrucciones', 'metraje_sugerido'],
    3: []
};
let pasoActualDrill = 1;
let pasosCompletadosDrill = new Set();

function actualizarStepperDrill() {
    const stepper = document.getElementById('stepperDrill');
    if (!stepper) return;

    for (let i = 1; i <= 3; i++) {
        const circle = stepper.querySelector(`.step-circle[data-step="${i}"]`);
        const label = stepper.querySelector(`.step-label[data-step="${i}"]`);
        if (!circle || !label) continue;
        circle.classList.remove('active', 'completed');
        label.classList.remove('active', 'completed');
        circle.innerHTML = i;

        if (i === pasoActualDrill) {
            circle.classList.add('active');
            label.classList.add('active');
        } else if (pasosCompletadosDrill.has(i)) {
            circle.classList.add('completed');
            label.classList.add('completed');
            circle.innerHTML = '<i class=\'fas fa-check text-xs\'></i>';
        }
    }

    for (let i = 1; i <= 2; i++) {
        const line = stepper.querySelector(`.step-line[data-line="${i}"]`);
        if (!line) continue;
        if (pasosCompletadosDrill.has(i)) line.classList.add('completed');
        else line.classList.remove('completed');
    }

    const btnAtras = document.getElementById('btnAtrasDrill');
    const btnSiguiente = document.getElementById('btnSiguienteDrill');
    const btnGuardar = document.getElementById('btnGuardar');

    btnAtras.classList.toggle('visible', pasoActualDrill > 1);
    btnGuardar.classList.toggle('visible', pasoActualDrill === 3);
    btnSiguiente.classList.toggle('visible', pasoActualDrill < 3);
}

function cambiarTabDrill(tab) {
    const index = PASOS_DRILL.indexOf(tab);
    if (index === -1) return;
    pasoActualDrill = index + 1;

    document.querySelectorAll('#formDrills .tab-content').forEach(c => c.classList.remove('active'));
    const content = document.getElementById(`tab-${tab}`);
    if (content) content.classList.add('active');

    actualizarStepperDrill();
}

function irAPasoDrill(numPaso) {
    if (numPaso < pasoActualDrill || pasosCompletadosDrill.has(numPaso - 1) || numPaso === 1) {
        cambiarTabDrill(PASOS_DRILL[numPaso - 1]);
    }
}

function validarPasoDrill(numPaso) {
    const campos = CAMPOS_PASO_DRILL[numPaso];
    const errores = [];

    campos.forEach(id => {
        const campo = document.getElementById(id);
        if (!campo) return;
        const reglas = campo.getAttribute('data-validar');
        if (!reglas) return;
        const nombre = campo.getAttribute('data-nombre') || id;

        if (reglas.includes('requerido') && !campo.value.trim()) {
            errores.push(nombre);
        }
    });

    return errores;
}

function avanzarPasoDrill() {
    const errores = validarPasoDrill(pasoActualDrill);
    if (errores.length > 0) {
        const msg = 'Falta completar: <strong>' + errores.join(', ') + '</strong>';
        if (typeof UI !== 'undefined') UI.advertencia('Datos Incompletos', msg);
        else alert('Datos Incompletos: ' + errores.join(', '));
        return;
    }

    pasosCompletadosDrill.add(pasoActualDrill);
    if (pasoActualDrill < 3) cambiarTabDrill(PASOS_DRILL[pasoActualDrill]);
}

function retrocederPasoDrill() {
    if (pasoActualDrill > 1) cambiarTabDrill(PASOS_DRILL[pasoActualDrill - 2]);
}

const inputBusquedaID = document.getElementById('busquedaID');
if (inputBusquedaID) {
    inputBusquedaID.addEventListener('input', function(e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1;
        renderTabla();
    });
}

const chkVerInactivos = document.getElementById('chkVerInactivos');
if (chkVerInactivos) {
    chkVerInactivos.addEventListener('change', () => {
        tablaPagina = 1;
        renderTabla();
    });
}

document.querySelectorAll('[data-sort]').forEach(th => {
    th.addEventListener('click', () => {
        const col = th.getAttribute('data-sort');
        if (tablaSortCol === col) {
            tablaSortDir = tablaSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            tablaSortCol = col;
            tablaSortDir = 'asc';
        }
        tablaPagina = 1;
        renderTabla();
    });
});

function actualizarContadores() {
    const mapeo = [
        { id: 'nombre', max: 100 },
        { id: 'enfoque_tecnico', max: 100 },
        { id: 'descripcion', max: 500 },
        { id: 'instrucciones', max: 800 },
        { id: 'metraje_sugerido', max: 50 }
    ];

    mapeo.forEach(({ id, max }) => {
        const input = document.getElementById(id);
        if (!input) return;
        
        let contador = input.parentElement.querySelector('.contador-chars');
        if (!contador) {
            contador = document.createElement('span');
            contador.className = 'contador-chars text-[9px] text-gray-400 text-right block mt-1';
            input.parentElement.appendChild(contador);
        }
        
        const actualizar = () => {
            contador.textContent = `${input.value.length} / ${max}`;
            if (input.value.length >= max) {
                contador.classList.add('text-red-400');
            } else {
                contador.classList.remove('text-red-400');
            }
        };
        
        input.addEventListener('input', actualizar);
        actualizar(); 
    });
}

function setupValidacionTiempoReal() {
    const campos = [
        { id: 'nombre', reglas: 'requerido|letras', nombre: 'Nombre', min: 2, max: 100 },
        { id: 'enfoque_tecnico', reglas: 'requerido|texto', nombre: 'Enfoque Técnico', min: 5, max: 100 },
        { id: 'descripcion', reglas: 'requerido|texto', nombre: 'Descripción', min: 15, max: 500 },
        { id: 'instrucciones', reglas: 'requerido|texto', nombre: 'Instrucciones', min: 20, max: 800 },
        { id: 'metraje_sugerido', reglas: 'requerido|metraje', nombre: 'Metraje sugerido', min: 1, max: 50 }
    ];

    campos.forEach(({ id, reglas, nombre, min, max }) => {
        const input = document.getElementById(id);
        if (!input) return;

        let errorContainer = input.parentElement.querySelector('.error-msg');
        if (!errorContainer) {
            errorContainer = document.createElement('span');
            errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
            input.parentElement.appendChild(errorContainer);
        }

        input.addEventListener('blur', function() {
            validarCampo(this, reglas, nombre, min, max);
        });

        input.addEventListener('input', function() {
            if (this.dataset.touched === 'true') {
                validarCampo(this, reglas, nombre, min, max);
            }
        });

        input.addEventListener('focus', function() {
            this.dataset.touched = 'true';
        });
    });
}

function validarCampo(input, reglas, nombre, min, max) {
    const valor = input.value.trim();
    let error = '';

    if (reglas.includes('requerido') && !valor) {
        error = `${nombre} es requerido`;
    } else if (valor) {
        if (reglas.includes('letras') && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(valor)) {
            error = `${nombre} solo debe contener letras`;
        }

        if (reglas.includes('metraje')) {
            if (!/^[\d\sxXmM\+\-\(\)\/]+$/.test(valor)) {
                error = 'Formato inválido. Ej: 50m, 4x50m, 3x100m, 2000m';
            }
            if (!/\d/.test(valor)) {
                error = 'Debe contener al menos un número';
            }
        }
   
        if (min && valor.length < min) {
            error = `${nombre} debe tener al menos ${min} caracteres`;
        }
        if (max && valor.length > max) {
            error = `${nombre} no debe exceder ${max} caracteres`;
        }
    }

    let errorContainer = input.parentElement.querySelector('.error-msg');
    if (!errorContainer) {
        errorContainer = document.createElement('span');
        errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
        input.parentElement.appendChild(errorContainer);
    }

    if (error) {
        input.classList.add('border-red-500', 'border-2');
        input.classList.remove('border-green-500', 'border');
        errorContainer.textContent = error;
        errorContainer.style.display = 'block';
        return false;
    } else if (valor) {
        input.classList.remove('border-red-500', 'border-2');
        input.classList.add('border-green-500', 'border');
        errorContainer.textContent = '';
        errorContainer.style.display = 'none';
        return true;
    } else {
        input.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
        errorContainer.textContent = '';
        errorContainer.style.display = 'none';
        return true;
    }
}

function validarFormularioCompleto(form) {
    const inputs = form.querySelectorAll('input[data-validar], textarea[data-validar]');
    let hasError = false;
    let primerosErrores = [];

    inputs.forEach(input => {
        const reglas = input.dataset.validar || '';
        const nombre = input.dataset.nombre || input.name || 'Campo';
        const min = parseInt(input.dataset.min) || 0;
        const max = parseInt(input.dataset.max) || 9999;
    
        input.dataset.touched = 'true';
        
        const isValid = validarCampo(input, reglas, nombre, min, max);
        if (!isValid) {
            hasError = true;
            const errorMsg = input.parentElement.querySelector('.error-msg');
            if (errorMsg && errorMsg.textContent) {
                primerosErrores.push(errorMsg.textContent);
            }
        }
    });

    return { hasError, errores: primerosErrores };
}

document.addEventListener('DOMContentLoaded', () => {
    try { 
        if (typeof Validador !== 'undefined') {
            Validador.vincularTiempoReal(formDrills);
        }
        setupValidacionTiempoReal();
        actualizarContadores();
    } catch(e){ console.warn('Error en validaciones:', e); }
    
    cargarTablaDrills();

    formDrills.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        if (document.getElementById('personalizado').checked && !document.getElementById('id_usuario_creador').value) {
            if (typeof UI !== 'undefined') UI.advertencia('Falta Atleta', 'Debe buscar y seleccionar un atleta para el entrenamiento personalizado.');
            else alert('Debe seleccionar un atleta.');
            return;
        }

        const { hasError, errores } = validarFormularioCompleto(this);
        
        if (hasError) {
            const mensaje = errores.length > 0 ? errores.join('<br>') : 'Por favor corrige los campos marcados en rojo.';
            if (typeof UI !== 'undefined') UI.advertencia('Datos Inválidos', mensaje);
            else alert('Por favor corrige los campos:\n' + errores.join('\n'));
      
            const primerError = this.querySelector('.border-red-500');
            if (primerError) {
                primerError.focus();
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        if (typeof Validador !== 'undefined' && typeof Validador.validarFormulario === 'function') {
            const erroresJS = Validador.validarFormulario(formDrills);
            if (erroresJS) {
                if (typeof UI !== 'undefined') UI.advertencia('Datos Inválidos', erroresJS);
                return; 
            }
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formDrills);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') UI.exito('Transacción Exitosa', resultado.message);
                else alert('Éxito: ' + resultado.message);
                cerrarModalDrills();
                cargarTablaDrills();
            } 
            else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') UI.advertencia('Validación del Servidor', msjErrores);
                else alert('Errores: ' + msjErrores);
            } 
            else {
                if (typeof UI !== 'undefined') UI.error('Error de Sistema', resultado.message);
                else alert('Error: ' + resultado.message);
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

async function eliminarDrills(id_drill) {
    const confirmacion = await Swal.fire({
        title: '¿Eliminar entrenamiento?',
        text: 'El registro se ocultará del listado. Esta acción no se puede deshacer desde la interfaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if (confirmacion.isConfirmed) {
        let datosDelete = new FormData();
        datosDelete.append('id_drill', id_drill);
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            cargarTablaDrills();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}