const modalObs = document.getElementById('modalObservacion');
const formObs = document.getElementById('formObservacion');
const btnGuardar = document.getElementById('btnGuardar');
const modalVer = document.getElementById('modalVer');
const modalResumen = document.getElementById('modalResumen');

const API_URL = 'index.php?p=observacionesTecnicas';

let atletasGlobal = [];
let aspectosGlobal = [];
let sesionesGlobal = [];

const LABELS_CALIFICACION = {
    1: { texto: 'Necesita trabajo urgente', color: 'text-red-400', bg: 'bg-red-500/20' },
    2: { texto: 'Regular', color: 'text-amber-400', bg: 'bg-amber-500/20' },
    3: { texto: 'Bueno', color: 'text-yellow-400', bg: 'bg-yellow-500/20' },
    4: { texto: 'Muy bueno', color: 'text-emerald-400', bg: 'bg-emerald-500/20' },
    5: { texto: 'Excelente', color: 'text-green-400', bg: 'bg-green-500/20' }
};

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

function renderEstrellas(calificacion) {
    let html = '<div class="flex items-center gap-1">';
    for (let i = 1; i <= 5; i++) {
        const activa = i <= calificacion;
        const color = activa ? 'text-indigo-400' : 'text-gray-700';
        html += `<i class="fas fa-circle ${color}" style="font-size: 8px;"></i>`;
    }
    html += '</div>';
    return html;
}

function renderBadgeCalificacion(calificacion) {
    const info = LABELS_CALIFICACION[calificacion] || LABELS_CALIFICACION[3];
    return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold ${info.bg} ${info.color}">
                ${calificacion} - ${info.texto}
            </span>`;
}

function seleccionarCalificacion(valor) {
    document.getElementById('calificacion').value = valor;
    const estrellas = document.querySelectorAll('.estrella');
    estrellas.forEach(e => {
        const v = parseInt(e.dataset.valor);
        e.classList.toggle('seleccionada', v <= valor);
    });
    const info = LABELS_CALIFICACION[valor];
    document.getElementById('textoCalificacion').textContent = info.texto;
    document.getElementById('textoCalificacion').className = `text-[10px] mt-1 font-bold ${info.color}`;
}

function cerrarModalObservacion() {
    modalObs.classList.add('hidden');
    modalObs.firstElementChild.classList.add('scale-95', 'opacity-0');
    formObs.reset();
    document.getElementById('id_observacion').value = '';
    document.getElementById('id_atleta').value = '';
    document.getElementById('calificacion').value = '';
    document.querySelectorAll('.estrella').forEach(e => e.classList.remove('seleccionada'));
    document.getElementById('textoCalificacion').textContent = '1=Necesita trabajo | 2=Regular | 3=Bueno | 4=Muy bueno | 5=Excelente';
    document.getElementById('textoCalificacion').className = 'text-[10px] text-gray-500 mt-1';
    const inputBuscar = document.getElementById('inputBuscarAtleta');
    if (inputBuscar) {
        inputBuscar.value = '';
        inputBuscar.classList.remove('text-emerald-400', 'font-bold', 'opacity-50', 'cursor-not-allowed', 'bg-gray-800');
        inputBuscar.removeAttribute('readonly');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');
    }
    try { Validador.limpiarEstilos(formObs); } catch(e) {}
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    document.getElementById('detalleContenido').innerHTML = '';
}

function cerrarModalResumen() {
    modalResumen.classList.add('hidden');
    document.getElementById('resumenContenido').innerHTML = '';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalObs.classList.contains('hidden')) cerrarModalObservacion();
        else if (!modalVer.classList.contains('hidden')) cerrarModalVer();
        else if (!modalResumen.classList.contains('hidden')) cerrarModalResumen();
    }
});

async function abrirModalObservacion(id_observacion = null) {
    cerrarModalObservacion();
    modalObs.classList.remove('hidden');
    setTimeout(() => {
        modalObs.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    cargarAtletasBuscador();
    await cargarAspectosSelect();
    await cargarSesionesSelect();

    if (id_observacion) {
        const data = await peticionAjax(`obtenerDetalle&id=${id_observacion}`);
        if (!data || !data.id_observacion) {
            UI.error('Error', 'No se pudieron cargar los datos.');
            cerrarModalObservacion();
            return;
        }

        document.getElementById('id_observacion').value = data.id_observacion;
        document.getElementById('id_atleta').value = data.id_atleta;
        const inputAtleta = document.getElementById('inputBuscarAtleta');
        inputAtleta.value = `${data.nombre_atleta} (CI: ${data.cedula})`;
        inputAtleta.readOnly = true;
        inputAtleta.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-800');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');

        document.getElementById('id_aspecto_tecnico').value = data.id_aspecto_tecnico;
        document.getElementById('id_sesion').value = data.id_sesion || '';
        document.getElementById('observacion_texto').value = data.observacion_texto || '';

        seleccionarCalificacion(parseInt(data.calificacion));

        document.getElementById('accion_form').value = 'actualizar';
        btnGuardar.innerHTML = 'ACTUALIZAR OBSERVACION <i class="fas fa-sync-alt ml-2"></i>';
        btnGuardar.classList.remove('bg-indigo-600', 'hover:bg-indigo-500');
        btnGuardar.classList.add('bg-emerald-600', 'hover:bg-emerald-500');
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit text-amber-400"></i> Editar Observacion Tecnica';
    } else {
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-clipboard-check text-indigo-400"></i> Registrar Observacion Tecnica';
        btnGuardar.innerHTML = 'GUARDAR OBSERVACION <i class="fas fa-save ml-2"></i>';
        btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
        btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
    }
}

async function cargarAtletasBuscador() {
    const respuesta = await peticionAjax('listarAtletasSelect');
    if (respuesta) atletasGlobal = respuesta;
}

async function cargarAspectosSelect() {
    const aspectos = await peticionAjax('listarAspectosTecnicos');
    if (!aspectos) return;
    aspectosGlobal = aspectos;

    const selectForm = document.getElementById('id_aspecto_tecnico');
    const filtroSelect = document.getElementById('filtroAspecto');

    [selectForm, filtroSelect].forEach((sel, idx) => {
        if (!sel) return;
        const valorActual = sel.value;
        while (sel.options.length > (idx === 0 ? 1 : 1)) sel.remove(sel.options.length - 1);
        aspectos.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id_aspecto;
            opt.textContent = a.nombre;
            sel.appendChild(opt);
        });
        if (valorActual) sel.value = valorActual;
    });
}

async function cargarSesionesSelect() {
    const select = document.getElementById('id_sesion');
    if (!select || select.dataset.cargado) return;
    select.dataset.cargado = 'true';

    try {
        const respuesta = await fetch('index.php?p=sesiones&accion=listarSesiones');
        if (!respuesta.ok) return;
        const sesiones = await respuesta.json();
        if (sesiones && Array.isArray(sesiones)) {
            sesionesGlobal = sesiones;
            sesiones.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id_sesion;
                opt.textContent = `${s.fecha} - ${s.tipo_sesion}`;
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.warn('No se pudieron cargar sesiones:', e);
    }
}

const inputBuscar = document.getElementById('inputBuscarAtleta');
const dropdown = document.getElementById('dropdownAtletas');
const ulAtletas = document.getElementById('ulAtletas');
const inputIdOculto = document.getElementById('id_atleta');
const btnLimpiar = document.getElementById('btnLimpiarAtleta');

function renderizarDropdown(lista) {
    ulAtletas.innerHTML = '';
    if (lista.length === 0) {
        ulAtletas.innerHTML = '<li class="p-4 text-gray-500 text-center text-xs">No se encontraron coincidencias</li>';
        return;
    }
    lista.forEach(atleta => {
        const li = document.createElement('li');
        li.className = 'p-3 hover:bg-indigo-600/20 hover:text-indigo-300 cursor-pointer transition-colors flex justify-between items-center';
        li.innerHTML = `<div><div class="font-bold text-white">${atleta.nombres} ${atleta.apellidos}</div><div class="text-[10px] text-gray-500 font-mono mt-0.5">C.I: ${atleta.cedula}</div></div>`;
        li.onclick = () => seleccionarAtleta(atleta);
        ulAtletas.appendChild(li);
    });
}

function seleccionarAtleta(atleta) {
    inputIdOculto.value = atleta.id_atleta;
    inputBuscar.value = `${atleta.nombres} ${atleta.apellidos}`;
    inputBuscar.classList.add('text-emerald-400', 'font-bold');
    inputBuscar.setAttribute('readonly', true);
    dropdown.classList.add('hidden');
    btnLimpiar.classList.remove('hidden');
}

btnLimpiar.onclick = () => {
    inputIdOculto.value = '';
    inputBuscar.value = '';
    inputBuscar.classList.remove('text-emerald-400', 'font-bold');
    inputBuscar.removeAttribute('readonly');
    btnLimpiar.classList.add('hidden');
    inputBuscar.focus();
};

inputBuscar.addEventListener('input', (e) => {
    const texto = e.target.value.toLowerCase();
    const filtrados = atletasGlobal.filter(a =>
        a.nombres.toLowerCase().includes(texto) ||
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.includes(texto)
    );
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
});

inputBuscar.addEventListener('focus', () => {
    if (!inputIdOculto.value) {
        dropdown.classList.remove('hidden');
        renderizarDropdown(atletasGlobal);
    }
});

document.addEventListener('click', (e) => {
    if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

formObs.addEventListener('submit', async (e) => {
    e.preventDefault();

    const erroresFormulario = Validador.validarFormulario(formObs);
    if (erroresFormulario) {
        UI.error('Datos Incompletos', `<div class="text-left text-sm mt-2 text-gray-300"><p class="mb-2 font-bold text-white">Corrige lo siguiente:</p>${erroresFormulario}</div>`);
        return;
    }

    if (!document.getElementById('calificacion').value) {
        UI.error('Calificacion Requerida', 'Debe seleccionar una calificacion del 1 al 5.');
        return;
    }

    let datosFormulario = new FormData(formObs);
    const accionActual = document.getElementById('accion_form').value;
    datosFormulario.set('accion', accionActual);

    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...';
    btnGuardar.disabled = true;

    const resultado = await peticionAjax(accionActual, datosFormulario);

    if (resultado) {
        if (resultado.status === 'success') {
            const msj = accionActual === 'actualizar' ? 'Observacion actualizada correctamente.' : 'Observacion registrada correctamente.';
            UI.exito('Operacion Exitosa', msj);
            cerrarModalObservacion();
            cargarTabla();
        } else if (resultado.status === 'warning') {
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Datos Incompletos', mensajesError);
        } else {
            UI.error('Error', resultado.message || 'Ocurrio un error inesperado.');
        }
    }

    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});

async function cargarTabla() {
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const id_aspecto = document.getElementById('filtroAspecto')?.value || '';
    const id_sesion = 0;

    let params = new URLSearchParams({});
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (id_aspecto) params.append('id_aspecto', id_aspecto);
    if (id_sesion) params.append('id_sesion', id_sesion);

    const tbody = document.getElementById('tbodyObservaciones');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando...</td></tr>';

    const observaciones = await peticionAjax(`listarObservaciones&${params.toString()}`);

    if (!observaciones || observaciones.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-500 font-mono text-xs">No hay observaciones registradas.</td></tr>';
        return;
    }

    let html = '';
    observaciones.forEach(obs => {
        const fecha = formatoFechaHora(obs.fecha_registro);
        const badge = renderBadgeCalificacion(obs.calificacion);
        const obsTexto = obs.observacion_texto
            ? (obs.observacion_texto.length > 60 ? obs.observacion_texto.substring(0, 60) + '...' : obs.observacion_texto)
            : '<span class="text-gray-600 italic text-xs">Sin notas</span>';

        const puedeEditar = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar;

        html += `<tr class="hover:bg-white/5 transition-colors duration-200 border-b border-[#252345]">
            <td class="p-4 text-xs font-mono text-gray-400">${fecha}</td>
            <td class="p-4">
                <div class="font-bold text-white text-sm">${obs.nombre_atleta}</div>
                <div class="text-[10px] text-gray-500 font-mono">C.I: ${obs.cedula}</div>
            </td>
            <td class="p-4">
                <span class="text-indigo-300 text-sm font-medium">${obs.nombre_aspecto}</span>
            </td>
            <td class="p-4 text-center">${badge}</td>
            <td class="p-4 text-xs text-gray-400 max-w-xs">${obsTexto}</td>
            <td class="p-4 text-right space-x-1">
                <button onclick="verDetalle(${obs.id_observacion})" class="text-indigo-400 hover:bg-indigo-500/10 p-2 rounded-lg transition" title="Ver Detalle">
                    <i class="fas fa-eye text-base"></i>
                </button>
                ${puedeEditar ? `
                <button onclick="abrirModalObservacion(${obs.id_observacion})" class="text-amber-400 hover:bg-amber-500/10 p-2 rounded-lg transition" title="Editar">
                    <i class="fas fa-edit text-base"></i>
                </button>
                <button onclick="eliminarObservacion(${obs.id_observacion})" class="text-red-400 hover:bg-red-500/10 p-2 rounded-lg transition" title="Eliminar">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>` : ''}
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function filtrarTabla() {
    const texto = document.getElementById('busquedaGeneral').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodyObservaciones tr');
    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
}

async function verDetalle(id) {
    const contenedor = document.getElementById('detalleContenido');
    contenedor.innerHTML = '<div class="text-center p-12 text-gray-500"><i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i><p class="text-xs font-mono uppercase tracking-widest">Cargando detalle...</p></div>';
    modalVer.classList.remove('hidden');

    const data = await peticionAjax(`obtenerDetalle&id=${id}`);
    if (!data || !data.id_observacion) {
        UI.error('Error', 'No se pudo cargar el detalle.');
        cerrarModalVer();
        return;
    }

    const fecha = formatoFechaHora(data.fecha_registro);
    const badge = renderBadgeCalificacion(data.calificacion);
    const info = LABELS_CALIFICACION[data.calificacion];

    let html = `
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-indigo-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Observacion Tecnica</h2>
                    <p class="text-xs text-gray-400">${fecha}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-black/20 rounded-xl p-4 border border-white/5">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Atleta</p>
                <p class="text-white font-bold">${data.nombre_atleta}</p>
                <p class="text-xs text-gray-500 font-mono">C.I: ${data.cedula}</p>
            </div>
            <div class="bg-black/20 rounded-xl p-4 border border-white/5">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Aspecto Tecnico</p>
                <p class="text-indigo-300 font-bold">${data.nombre_aspecto}</p>
                <p class="text-xs text-gray-500">${data.desc_aspecto || ''}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-black/20 rounded-xl p-4 border border-white/5 text-center">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-2">Calificacion</p>
                ${badge}
            </div>
            <div class="bg-black/20 rounded-xl p-4 border border-white/5">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Sesion</p>
                <p class="text-white text-sm">${data.fecha_sesion ? data.fecha_sesion + ' - ' + (data.tipo_sesion || '') : 'Sin sesion asociada'}</p>
            </div>
        </div>`;

    if (data.observacion_texto) {
        html += `<div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6">
            <p class="text-[10px] text-gray-500 uppercase font-bold mb-2">Observacion del Entrenador</p>
            <p class="text-gray-300 text-sm leading-relaxed">${data.observacion_texto}</p>
        </div>`;
    }

    if (data.historial_aspecto && data.historial_aspecto.length > 0) {
        html += `<div class="mt-6 border-t border-[#252345] pt-4">
            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-3">
                <i class="fas fa-chart-line mr-2 text-emerald-400"></i>Evolucion del Aspecto: ${data.nombre_aspecto}
            </p>
            <div class="space-y-2">`;

        data.historial_aspecto.forEach(h => {
            const fechaH = formatoFecha(h.fecha_registro);
            const badgeH = renderBadgeCalificacion(h.calificacion);
            html += `<div class="flex items-center justify-between bg-black/20 rounded-lg p-3 border border-white/5">
                <span class="text-xs font-mono text-gray-400">${fechaH}</span>
                ${badgeH}
            </div>`;
        });

        html += '</div></div>';
    }

    contenedor.innerHTML = html;
}

async function verResumenAtleta() {
    const select = document.getElementById('filtroAtletaResumen');
    const id_atleta = parseInt(select.value);
    if (!id_atleta) {
        UI.advertencia('Seleccion Requerida', 'Seleccione un atleta para ver el resumen.');
        return;
    }

    const contenedor = document.getElementById('resumenContenido');
    contenedor.innerHTML = '<div class="text-center p-12 text-gray-500"><i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i><p class="text-xs font-mono uppercase tracking-widest">Calculando promedios...</p></div>';
    modalResumen.classList.remove('hidden');

    const data = await peticionAjax(`resumenAspectos&id_atleta=${id_atleta}`);
    if (!data) {
        UI.error('Error', 'No se pudo generar el resumen.');
        cerrarModalResumen();
        return;
    }

    const nombreAtleta = select.options[select.selectedIndex].text;
    let html = `
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-chart-bar text-emerald-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Resumen Tecnico</h2>
                    <p class="text-xs text-gray-400">${nombreAtleta}</p>
                </div>
            </div>
        </div>
        <div class="space-y-3">`;

    data.forEach(aspecto => {
        const promedio = parseFloat(aspecto.promedio) || 0;
        const total = parseInt(aspecto.total_evaluaciones) || 0;
        const info = LABELS_CALIFICACION[Math.round(promedio)] || LABELS_CALIFICACION[3];

        const porcentaje = Math.min((promedio / 5) * 100, 100);
        const barColor = promedio < 2.5 ? 'bg-red-500' : promedio < 3.5 ? 'bg-yellow-500' : promedio < 4.5 ? 'bg-emerald-500' : 'bg-green-500';

        html += `<div class="bg-black/20 rounded-xl p-4 border border-white/5">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-white font-bold text-sm">${aspecto.nombre}</p>
                    <p class="text-[10px] text-gray-500">${aspecto.descripcion || ''}</p>
                </div>
                <div class="text-right">
                    ${total > 0 ? `<span class="text-lg font-bold ${info.color}">${promedio.toFixed(1)}</span><span class="text-gray-500 text-xs">/5</span>` : '<span class="text-gray-600 text-xs">Sin datos</span>'}
                </div>
            </div>
            <div class="w-full bg-gray-800 rounded-full h-2">
                <div class="${barColor} h-2 rounded-full transition-all" style="width: ${porcentaje}%"></div>
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-[10px] text-gray-600">${total} evaluacion(es)</span>
                ${aspecto.ultima_evaluacion ? `<span class="text-[10px] text-gray-600">Ultima: ${formatoFecha(aspecto.ultima_evaluacion)}</span>` : ''}
            </div>
        </div>`;
    });

    if (data.length === 0) {
        html += '<div class="text-center py-8 text-gray-500"><i class="fas fa-inbox text-3xl mb-2"></i><p class="text-xs">No hay aspectos evaluados para este atleta.</p></div>';
    }

    html += '</div>';
    contenedor.innerHTML = html;
}

async function eliminarObservacion(id) {
    const confirmado = await UI.confirmar(
        'Eliminar Observacion',
        'Esta accion eliminara permanentemente la observacion. Desea continuar?'
    );

    if (!confirmado.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_observacion', id);

    const resultado = await peticionAjax('eliminar', formData);
    if (resultado && resultado.status === 'success') {
        UI.exito('Eliminada', 'La observacion fue eliminada correctamente.');
        cargarTabla();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo eliminar la observacion.');
    }
}

async function cargarFiltrosAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    if (!atletas) return;

    const filtroAtleta = document.getElementById('filtroAtleta');
    const filtroResumen = document.getElementById('filtroAtletaResumen');

    [filtroAtleta, filtroResumen].forEach(sel => {
        if (!sel) return;
        while (sel.options.length > 1) sel.remove(sel.options.length - 1);
        atletas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id_atleta;
            opt.textContent = `${a.nombres} ${a.apellidos} - CI: ${a.cedula}`;
            sel.appendChild(opt);
        });
    });
}

async function cargarRecursos() {
    await Promise.all([
        cargarFiltrosAtletas(),
        cargarAspectosSelect()
    ]);
}

document.getElementById('filtroAtletaResumen')?.addEventListener('change', function() {
    if (this.value) verResumenAtleta();
});

cargarRecursos().then(() => cargarTabla());
Validador.vincularTiempoReal(formObs);
