const API_URL = 'index.php?p=atleta';

const modalAtleta = document.getElementById('modalAtleta');
const modalVer = document.getElementById('modalVer');
const formAtleta = document.getElementById('formAtleta');
const btnGuardar = document.getElementById('btnGuardar');
const tbodyLista = document.getElementById('listaAtletas');
const inputBusqueda = document.getElementById('busquedaAtleta');
const detalleContenido = document.getElementById('detalleContenido');
const inputFoto = document.getElementById('foto');
const fotoPreview = document.getElementById('fotoPreview');
const totalAtletas = document.getElementById('totalAtletas');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

let categoriasCache = [];
let atletasData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
        const texto = await respuesta.text();
        console.log('[DEBUG API]', accion.toUpperCase(), 'Status:', respuesta.status, 'Body:', texto.substring(0, 500));
        return JSON.parse(texto);
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

async function cargarTabla() {
    if (!tbodyLista) return;

    tbodyLista.innerHTML = `<tr><td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const atletas = await peticionAjax('listar');

    if (!atletas || atletas.length === 0) {
        atletasData = [];
        totalAtletas.textContent = '0 Registrados';
        if (infoTabla) infoTabla.textContent = '';
        if (pieTabla) pieTabla.innerHTML = '';
        tbodyLista.innerHTML = `
            <tr>
                <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-swimmer text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay atletas registrados en el sistema</span>
                </td>
            </tr>`;
        return;
    }

    atletasData = atletas;
    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;
    renderTabla();
}

function renderTabla() {
    if (!tbodyLista) return;

    let datos = atletasData.slice();

    if (tablaFiltro) {
        const q = tablaFiltro;
        datos = datos.filter(a =>
            (a.nombres + ' ' + a.apellidos + ' ' + a.cedula).toLowerCase().includes(q)
        );
    }

    if (tablaSortCol) {
        const col = tablaSortCol;
        const dir = tablaSortDir === 'asc' ? 1 : -1;
        datos.sort((a, b) => {
            let va = '', vb = '';
            if (col === 'nombre') { va = (a.nombres || '') + ' ' + (a.apellidos || ''); vb = (b.nombres || '') + ' ' + (b.apellidos || ''); }
            else if (col === 'cedula') { va = a.cedula || ''; vb = b.cedula || ''; }
            else if (col === 'categoria') { va = a.categoria_nombre || ''; vb = b.categoria_nombre || ''; }
            else if (col === 'feveda') { va = a.numero_feveda || ''; vb = b.numero_feveda || ''; }
            else if (col === 'estado') { va = a.estado || ''; vb = b.estado || ''; }
            return va.localeCompare(vb, 'es') * dir;
        });
    }

    const total = datos.length;
    totalAtletas.textContent = `${atletasData.length} Registrados`;
    if (infoTabla) infoTabla.textContent = `Mostrando ${total === 0 ? 0 : (tablaPagina - 1) * tablaPorPagina + 1}–${Math.min(tablaPagina * tablaPorPagina, total)} de ${total}`;

    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;
    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    actualizarSortIcons();

    if (pagina.length === 0 && total > 0) {
        tbodyLista.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbodyLista.innerHTML = `
            <tr>
                <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-swimmer text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay atletas registrados en el sistema</span>
                </td>
            </tr>`;
    } else {
        tbodyLista.innerHTML = pagina.map(a => `
            <tr class="atleta-row hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" data-busqueda="${a.nombres} ${a.apellidos} ${a.cedula}">
                <td class="p-4 flex items-center gap-3">
                    ${a.foto
                        ? `<img src="${a.foto}" class="w-10 h-10 rounded-full object-cover border-2 border-indigo-500/30">`
                        : `<div class="bg-indigo-50 dark:bg-indigo-500/10 p-2.5 rounded-full text-indigo-600 dark:text-indigo-400"><i class="fas fa-user"></i></div>`
                    }
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">${a.nombres} ${a.apellidos}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${a.edad} años · ${a.sexo === 'M' ? 'Masculino' : 'Femenino'}</p>
                    </div>
                </td>
                <td class="p-4 font-mono text-gray-700 dark:text-gray-300">${a.cedula}</td>
                <td class="p-4">
                    ${a.categoria_nombre
                        ? `<span class="text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 px-2 py-1 rounded-lg">${a.categoria_nombre}</span>`
                        : `<span class="text-gray-500 dark:text-gray-500">S/C</span>`
                    }
                </td>
                <td class="p-4 font-mono text-indigo-600 dark:text-indigo-300">${a.numero_feveda ? a.numero_feveda : '—'}</td>
                <td class="p-4">
                    <span class="estado-badge estado-${a.estado}">${a.estado}</span>
                </td>
                ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.editar ? `
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick='verDetalle(${a.id_atleta})' class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all" title="Ver Perfil">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick='abrirModal(${a.id_atleta})' class="w-9 h-9 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmarEliminar(${a.id_atleta})" class="w-9 h-9 rounded-xl flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white transition-all" title="Desactivar">
                            <i class="fas fa-user-slash"></i>
                        </button>
                    </div>
                </td>
                ` : `
                <td class="p-4 text-right">
                    <button onclick='verDetalle(${a.id_atleta})' class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all" title="Ver Perfil">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
                `}
            </tr>
        `).join('');
    }

    renderPaginacion(totalPaginas);
}

function renderPaginacion(totalPaginas) {
    if (!pieTabla || totalPaginas <= 1) { if (pieTabla) pieTabla.innerHTML = ''; return; }

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

if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function (e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1;
        renderTabla();
    });
}

function cambiarTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');
}

async function cargarCategorias() {
    if (categoriasCache.length > 0) return categoriasCache;
    categoriasCache = await peticionAjax('categorias');
    if (!categoriasCache) categoriasCache = [];

    const select = document.getElementById('id_categoria');
    if (select) {
        select.innerHTML = '<option value="">Seleccione una categoría...</option>';
        categoriasCache.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id_categoria;
            option.textContent = `${cat.nombre} (${cat.edad_minima}-${cat.edad_maxima} años)`;
            select.appendChild(option);
        });
    }
    return categoriasCache;
}

async function abrirModal(id = null) {
    formAtleta.reset();
    try { Validador.limpiarEstilos(formAtleta); } catch (e) {}

    document.getElementById('id_atleta').value = '';
    fotoPreview.innerHTML = '<i class="fas fa-camera text-gray-600 text-lg"></i>';
    document.getElementById('estado').value = 'Activo';

    await cargarCategorias();

    if (id) {
        document.getElementById('modalTitulo').textContent = 'Editar Atleta';
        btnGuardar.innerHTML = 'ACTUALIZAR DATOS <i class="fas fa-sync-alt ml-2"></i>';

        const datos = await peticionAjax(`obtener&id=${id}`);
        if (datos) {
            document.getElementById('id_atleta').value = datos.id_atleta;
            document.getElementById('cedula').value = datos.cedula || '';
            document.getElementById('nombres').value = datos.nombres || '';
            document.getElementById('apellidos').value = datos.apellidos || '';
            document.getElementById('fecha_nacimiento').value = datos.fecha_nacimiento || '';
            document.getElementById('sexo').value = datos.sexo || '';
            document.getElementById('estado').value = datos.estado || 'Activo';
            document.getElementById('direccion').value = datos.direccion || '';
            document.getElementById('telefono').value = datos.telefono || '';
            document.getElementById('correo').value = datos.correo || '';
            document.getElementById('fecha_registro_club').value = datos.fecha_registro_club || '';
            document.getElementById('grupo_sanguineo').value = datos.grupo_sanguineo || '';
            document.getElementById('seguro_medico').value = datos.seguro_medico || '';
            document.getElementById('alergias').value = datos.alergias || '';
            document.getElementById('condiciones_previas').value = datos.condiciones_previas || '';
            document.getElementById('contacto_emergencia_nombre').value = datos.contacto_emergencia_nombre || '';
            document.getElementById('contacto_emergencia_telefono').value = datos.contacto_emergencia_telefono || '';
            document.getElementById('contacto_emergencia_parentesco').value = datos.contacto_emergencia_parentesco || '';
            document.getElementById('numero_feveda').value = datos.numero_feveda || '';
            document.getElementById('club_procedencia').value = datos.club_procedencia || '';
            document.getElementById('id_categoria').value = datos.id_categoria || '';

            if (datos.foto) {
                fotoPreview.innerHTML = `<img src="${datos.foto}" class="w-full h-full object-cover">`;
            }
        }
    } else {
        document.getElementById('modalTitulo').textContent = 'Registrar Atleta';
        btnGuardar.innerHTML = 'GUARDAR DATOS <i class="fas fa-save ml-2"></i>';
    }

    cambiarTab('personal');
    modalAtleta.classList.remove('hidden');
    setTimeout(() => {
        modalAtleta.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function cerrarModal() {
    modalAtleta.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalAtleta.classList.add('hidden');
    }, 200);
}

function cerrarModalVer() {
    modalVer.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalVer.classList.add('hidden');
    }, 200);
}

if (inputFoto) {
    inputFoto.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                fotoPreview.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        }
    });
}

async function verDetalle(id) {
    const datos = await peticionAjax(`obtener&id=${id}`);
    if (!datos) return;

    const fotoHtml = datos.foto
        ? `<img src="${datos.foto}" class="w-28 h-28 rounded-full mx-auto mb-4 border-4 border-indigo-500/20 shadow-xl object-cover">`
        : `<div class="w-28 h-28 rounded-full mx-auto mb-4 bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20"><i class="fas fa-user"></i></div>`;

    const html = `
        <div class="text-center mb-8">
            ${fotoHtml}
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">${datos.nombres} ${datos.apellidos}</h2>
            <p class="text-indigo-600 dark:text-indigo-400 mb-2 font-mono tracking-widest text-sm">${datos.cedula}</p>
            <span class="estado-badge estado-${datos.estado}">${datos.estado}</span>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-3"><i class="fas fa-user mr-2"></i>Datos Personales</p>
            <div class="grid grid-cols-3 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Edad</p><p class="text-gray-900 dark:text-white">${datos.edad} años</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Sexo</p><p class="text-gray-900 dark:text-white">${datos.sexo === 'M' ? 'Masculino' : 'Femenino'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Categoría</p><p class="text-indigo-600 dark:text-indigo-300">${datos.categoria_nombre || 'S/C'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Teléfono</p><p class="text-gray-900 dark:text-white">${datos.telefono || '—'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Correo</p><p class="text-gray-900 dark:text-white text-xs">${datos.correo || '—'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Fichaje Club</p><p class="text-gray-900 dark:text-white">${datos.fecha_registro_club ? formatoFecha(datos.fecha_registro_club) : '—'}</p></div>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-3"><i class="fas fa-heartbeat mr-2"></i>Datos Médicos</p>
            <div class="grid grid-cols-2 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Grupo Sanguíneo</p><p class="text-gray-900 dark:text-white font-bold">${datos.grupo_sanguineo || '—'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Seguro Médico</p><p class="text-gray-900 dark:text-white">${datos.seguro_medico || '—'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Alergias</p><p class="text-gray-900 dark:text-white text-xs">${datos.alergias || 'Ninguna registrada'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Condiciones</p><p class="text-gray-900 dark:text-white text-xs">${datos.condiciones_previas || 'Ninguna registrada'}</p></div>
            </div>
            ${datos.contacto_emergencia_nombre ? `
            <div class="mt-3 p-3 rounded-xl bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                <p class="text-[10px] uppercase text-amber-600 dark:text-amber-400 font-bold mb-2"><i class="fas fa-phone-alt mr-2"></i>Contacto Emergencia</p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div><p class="text-gray-900 dark:text-white text-sm">${datos.contacto_emergencia_nombre}</p><p class="text-[10px] text-gray-500 dark:text-gray-400">${datos.contacto_emergencia_parentesco || ''}</p></div>
                    <div><p class="text-gray-900 dark:text-white text-sm">${datos.contacto_emergencia_telefono || '—'}</p></div>
                </div>
            </div>` : ''}
        </div>

        <div>
            <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-3"><i class="fas fa-trophy mr-2"></i>Datos Federativos</p>
            <div class="grid grid-cols-2 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">FEVEDA</p><p class="text-indigo-600 dark:text-indigo-300 font-mono">${datos.numero_feveda || 'S/F'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Club Procedencia</p><p class="text-gray-900 dark:text-white">${datos.club_procedencia || '—'}</p></div>
            </div>
        </div>
        <div class="flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-xl mt-4">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wider mb-3">Carnet de Asistencia</span>
            
            <div id="contenedorQR" class="bg-white dark:bg-[#161430] p-3 rounded-lg shadow-lg border border-gray-200 dark:border-[#252345]">
                </div>
            
            <span id="txtTokenVisible" class="text-[10px] text-gray-600 dark:text-gray-500 font-mono mt-2 select-all"></span>
        </div>        
        `;

    // Inyectar el HTML
    const detalleContenido = document.getElementById('detalleContenido');
    detalleContenido.innerHTML = html;

    // Generar QR
    const contenedorQR = document.getElementById('contenedorQR');
    const txtTokenVisible = document.getElementById('txtTokenVisible');
    
    if (contenedorQR && txtTokenVisible) {
        contenedorQR.innerHTML = ''; 

        if (datos.token_asistencia) {
            new QRCode(contenedorQR, {
                text: datos.token_asistencia,
                width: 150,
                height: 150,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            txtTokenVisible.textContent = datos.token_asistencia.substring(0, 8) + '...';
        } else {
            contenedorQR.innerHTML = '<span class="text-xs text-red-500 font-bold">Token no generado</span>';
        }
    }

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function confirmarEliminar(id) {
    const confirmacion = await UI.confirmar(
        '¿Desactivar atleta?',
        'El atleta será marcado como inactivo. Podrá reactivarlo después.'
    );

    if (confirmacion.isConfirmed) {
        let datosDelete = new FormData();
        datosDelete.append('accion', 'eliminar');
        datosDelete.append('id_atleta', id);

        const resultado = await peticionAjax('eliminar', datosDelete);

        if (resultado && resultado.status === 'success') {
            UI.exito('Desactivado', resultado.message);
            cargarTabla();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo desactivar el atleta.');
        }
    }
}


document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalAtleta.classList.contains('hidden')) cerrarModal();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formAtleta); } catch (e) {}

    cargarTabla();

    formAtleta.addEventListener('submit', async function (e) {
        e.preventDefault();

        const erroresJS = Validador.validarFormulario(formAtleta);
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return;
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const idAtleta = document.getElementById('id_atleta').value;
        const accion = idAtleta ? 'editar' : 'guardar';

        const datosForm = new FormData(formAtleta);
        datosForm.append('accion', accion);

        const resultado = await peticionAjax(accion, datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModal();
                cargarTabla();
            } else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join('<br>');
                UI.advertencia('Validación del Servidor', msjErrores);
            } else {
                UI.error('Error de Sistema', resultado.message);
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});
