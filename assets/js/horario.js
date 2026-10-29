let horarioData = [];
let tablaFiltro = '';
let tablaFiltroEstado = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

const modalHorario = document.getElementById('modalHorario');
const modalVer = document.getElementById('modalVerHorario');
const formHorario = document.getElementById('formHorario');
const btnGuardar = document.getElementById('btnGuardar');
const totalHorarios = document.getElementById('totalHorarios');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

const API_URL = 'index.php?p=horario';

function normalizarHora(hora) {
    if (!hora) return '';
    let limpia = hora.trim();

    if (/^\d{2}:\d{2}$/.test(limpia)) return limpia;
    if (/^\d{2}:\d{2}:\d{2}$/.test(limpia)) return limpia.substring(0, 5);
    if (/^\d{2}$/.test(limpia)) return limpia + ':00';

    if (/^\d{1}:\d{2}$/.test(limpia)) {
        const p = limpia.split(':');
        return p[0].padStart(2, '0') + ':' + p[1];
    }
    if (/^\d{1}:\d{2}:\d{2}$/.test(limpia)) {
        const p = limpia.split(':');
        return p[0].padStart(2, '0') + ':' + p[1];
    }

    const numeros = limpia.replace(/[^0-9]/g, '');
    if (numeros.length >= 2) {
        let h = parseInt(numeros.substring(0, 2));
        if (h > 23) h = 23;
        let m = 0;
        if (numeros.length >= 4) {
            m = parseInt(numeros.substring(2, 4));
            if (m > 59) m = 59;
        }
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
    }
    return limpia;
}

function normalizarHoraParaDisplay(hora) {
    if (!hora) return '';
    if (/^\d{2}:\d{2}:\d{2}$/.test(hora)) return hora.substring(0, 5);
    return hora;
}

function validarHoraLocal(hora) {
    if (!hora) return false;
    return /^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/.test(hora);
}

function corregirHoraInput(hora) {
    if (!hora) return '';
    let limpia = hora.replace(/[^0-9:]/g, '');

    if (!limpia.includes(':')) {
        if (limpia.length >= 2) {
            let h = parseInt(limpia.substring(0, 2));
            if (h > 23) h = 23;
            let m = '00';
            if (limpia.length >= 4) {
                let mn = parseInt(limpia.substring(2, 4));
                if (mn > 59) mn = 59;
                m = mn.toString().padStart(2, '0');
            }
            return `${h.toString().padStart(2, '0')}:${m}`;
        } else if (limpia.length === 1) {
            return '0' + limpia + ':00';
        }
        return '00:00';
    }

    const partes = limpia.split(':');
    let horas = partes[0].substring(0, 2);
    let minutos = partes[1] ? partes[1].substring(0, 2) : '00';

    let hNum = parseInt(horas);
    if (isNaN(hNum)) hNum = 0;
    if (hNum > 23) hNum = 23;
    horas = hNum.toString().padStart(2, '0');

    let mNum = parseInt(minutos);
    if (isNaN(mNum)) mNum = 0;
    if (mNum > 59) mNum = 59;
    minutos = mNum.toString().padStart(2, '0');

    return `${horas}:${minutos}`;
}

async function peticionAjax(accion, datos = null) {
    const opciones = {
        method: datos ? 'POST' : 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        if (typeof UI !== 'undefined') {
            UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        }
        return null;
    }
}

function cerrarModalHorario() {
    if (modalHorario && modalHorario.firstElementChild) {
        modalHorario.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modalHorario) modalHorario.classList.add('hidden');
    }, 200);
}

function cerrarModalVer() {
    if (modalVer && modalVer.firstElementChild) {
        modalVer.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modalVer) modalVer.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (modalHorario && !modalHorario.classList.contains('hidden')) cerrarModalHorario();
        if (modalVer && !modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

async function abrirModalHorario(id_bloque = null) {
    formHorario.reset();
    try { Validador.limpiarEstilos(formHorario); } catch (e) {}

    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_bloque');
    const modalTitulo = document.getElementById('modalTitulo');

    if (id_bloque) {
        if (inputAction) inputAction.value = 'editar';
        if (inputIdHidden) inputIdHidden.value = id_bloque;
        if (modalTitulo) modalTitulo.textContent = 'Editar Bloque de Horario';
        btnGuardar.innerHTML = 'EDITAR HORARIO <i class="fas fa-sync-alt ml-2"></i>';

        const horario = await peticionAjax(`obtenerBloque&id=${id_bloque}`);
        if (horario) {
            document.getElementById('dia_semana').value = horario.dia_semana;
            document.getElementById('hora_inicio').value = normalizarHora(horario.hora_inicio);
            document.getElementById('hora_fin').value = normalizarHora(horario.hora_fin);
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Bloque de Horario';
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
        document.getElementById('hora_inicio').value = '';
        document.getElementById('hora_fin').value = '';
    }

    modalHorario.classList.remove('hidden');
    setTimeout(() => {
        modalHorario.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function verDetalle(id) {
    const horario = await peticionAjax(`obtenerBloque&id=${id}`);
    if (!horario) return;

    const hIni = normalizarHoraParaDisplay(normalizarHora(horario.hora_inicio));
    const hFin = normalizarHoraParaDisplay(normalizarHora(horario.hora_fin));
    const estado = horario.estado || 'Activo';
    const estadoBadge = estado === 'Activo'
        ? '<span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-sm font-bold">Activo</span>'
        : '<span class="px-3 py-1 bg-red-500/20 text-red-400 rounded-full text-sm font-bold">Inactivo</span>';

    document.getElementById("verDia").innerText = horario.dia_semana;
    document.getElementById("verHoraInicio").innerText = hIni;
    document.getElementById("verHoraFin").innerText = hFin;
    document.getElementById("verRango").innerHTML =
        `<span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-sm">${hIni} - ${hFin}</span>`;
    document.getElementById("verEstado").innerHTML = estadoBadge;

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function renderTabla() {
    const tbody = document.getElementById('listaHorario');
    if (!tbody) return;

    let datos = horarioData.slice();

    if (tablaFiltro) {
        const q = tablaFiltro.toLowerCase().trim();
        datos = datos.filter(h => {
            const txt = `${h.dia_semana} ${h.hora_inicio} ${h.hora_fin}`.toLowerCase();
            return txt.includes(q);
        });
    }

    if (tablaFiltroEstado) {
        datos = datos.filter(h => (h.estado || 'Activo') === tablaFiltroEstado);
    }

    if (tablaSortCol) {
        const col = tablaSortCol;
        const dir = tablaSortDir === 'asc' ? 1 : -1;
        datos.sort((a, b) => {
            const va = (a[col] ?? '').toString();
            const vb = (b[col] ?? '').toString();
            return va.localeCompare(vb, 'es') * dir;
        });
    }

    const total = datos.length;
    if (totalHorarios) totalHorarios.textContent = `${horarioData.length} Horarios`;

    if (infoTabla) {
        if (total === 0) {
            infoTabla.textContent = '0 registros';
        } else {
            const ini = (tablaPagina - 1) * tablaPorPagina + 1;
            const fin = Math.min(tablaPagina * tablaPorPagina, total);
            infoTabla.textContent = `Mostrando ${ini}–${fin} de ${total}`;
        }
    }

    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;
    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clock text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay bloques de horarios registrados</span>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = pagina.map(h => {
            const activo = (h.estado || 'Activo') === 'Activo';
            const badge = activo
                ? `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30">Activo</span>`
                : `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 border border-red-300 dark:border-red-500/30">Inactivo</span>`;

            const hIni = normalizarHoraParaDisplay(h.hora_inicio);
            const hFin = normalizarHoraParaDisplay(h.hora_fin);

            const btnReactivar = !activo ? `
                <button onclick="reactivarHorario(${h.id_bloque})" 
                        class="bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2 rounded-lg transition" 
                        title="Reactivar">
                    <i class="fas fa-undo text-xs"></i>
                </button>` : '';

            // ÍCONO FA-BAN AGREGADO AQUÍ
            const btnDesactivar = activo ? `
                <button onclick="eliminarHorario(${h.id_bloque})" 
                        class="bg-red-50 dark:bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 p-2 rounded-lg transition" 
                        title="Desactivar">
                    <i class="fas fa-ban text-xs"></i>
                </button>` : '';

            const acciones = (typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar) ? `
                <div class="flex justify-end gap-2">
                    <button onclick="verDetalle(${h.id_bloque})" class="bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2 rounded-lg transition" title="Ver Detalle">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button onclick="abrirModalHorario(${h.id_bloque})" class="bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg transition" title="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    ${btnReactivar}
                    ${btnDesactivar}
                </div>
            ` : '<span class="text-gray-500 dark:text-gray-600 text-xs">Solo lectura</span>';

            return `
                <tr class="horario-row border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-[#1c1a3a]/40 transition-colors duration-200 ${!activo ? 'opacity-60' : ''}">
                    <td class="p-4 font-medium text-gray-900 dark:text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                                ${(h.dia_semana || '').substring(0, 2)}
                            </div>
                            <span>${h.dia_semana}</span>
                        </div>
                    </td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${hIni}</td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${hFin}</td>
                    <td class="p-4">${badge}</td>
                    <td class="p-4 text-right">${acciones}</td>
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

    let html = `<span class="text-xs text-gray-600 dark:text-gray-400">Página ${tablaPagina} de ${totalPaginas}</span><div class="flex gap-1">`;
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

async function cargarTablaHorario() {
    const tbody = document.getElementById('listaHorario');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando horarios...</span></td></tr>`;

    const horarios = await peticionAjax('listarHorario');

    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;

    if (!horarios || horarios.length === 0) {
        horarioData = [];
        if (totalHorarios) totalHorarios.textContent = '0 Horarios';
        if (infoTabla) infoTabla.textContent = '';
        if (pieTabla) pieTabla.innerHTML = '';
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clock text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay bloques de horarios registrados</span>
                </td>
            </tr>
        `;
        return;
    }

    horarioData = horarios;
    renderTabla();
}

const inputBusqueda = document.getElementById('busquedaHorario');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function (e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1;
        renderTabla();
    });
}

const filtroEstado = document.getElementById('filtroEstado');
if (filtroEstado) {
    filtroEstado.addEventListener('change', function (e) {
        tablaFiltroEstado = e.target.value;
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

function normalizarHorasFormulario() {
    const hi = document.getElementById('hora_inicio');
    const hf = document.getElementById('hora_fin');

    if (hi && hi.value) {
        let v = hi.value;
        if (/^\d{2}:\d{2}:\d{2}$/.test(v)) v = v.substring(0, 5);
        hi.value = v;
    }
    if (hf && hf.value) {
        let v = hf.value;
        if (/^\d{2}:\d{2}:\d{2}$/.test(v)) v = v.substring(0, 5);
        hf.value = v;
    }
}

function agregarValidacionTiempoReal() {
    const hi = document.getElementById('hora_inicio');
    const hf = document.getElementById('hora_fin');

    const validarCampo = (campo) => {
        campo.addEventListener('blur', function () {
            if (this.value) {
                const corregida = corregirHoraInput(this.value);
                if (validarHoraLocal(corregida)) {
                    this.value = corregida;
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } else {
                    this.classList.add('border-red-500');
                    this.classList.remove('border-green-500');
                    if (typeof UI !== 'undefined') {
                        UI.advertencia('Formato inválido', 'El formato de hora debe ser HH:MM (ej: 14:30)');
                    }
                }
            }
        });
        campo.addEventListener('input', function () {
            this.classList.remove('border-red-500', 'border-green-500');
        });
    };

    if (hi) validarCampo(hi);
    if (hf) validarCampo(hf);
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        if (typeof Validador !== 'undefined') Validador.vincularTiempoReal(formHorario);
    } catch (e) {}

    agregarValidacionTiempoReal();
    cargarTablaHorario();

    formHorario.addEventListener('submit', async function (e) {
        e.preventDefault();

        const hi = document.getElementById('hora_inicio');
        const hf = document.getElementById('hora_fin');

        normalizarHorasFormulario();

        if (!validarHoraLocal(hi.value)) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Hora inválida', 'La hora de inicio debe tener formato HH:MM (ej: 08:00, 14:30, 23:59)');
            }
            hi.focus();
            return;
        }

        if (!validarHoraLocal(hf.value)) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Hora inválida', 'La hora de fin debe tener formato HH:MM (ej: 08:00, 14:30, 23:59)');
            }
            hf.focus();
            return;
        }

        let erroresJS = false;
        try {
            if (typeof Validador !== 'undefined') {
                erroresJS = Validador.validarFormulario(formHorario);
            }
        } catch (e) {}

        if (erroresJS) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            }
            return;
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formHorario);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalHorario();
                cargarTablaHorario();
            } else if (resultado.status === 'warning') {
                const msj = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') UI.advertencia('Validación del Servidor', msj);
            } else {
                if (typeof UI !== 'undefined') UI.error('Error de Sistema', resultado.message || 'Error al procesar la solicitud');
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

async function eliminarHorario(id_bloque) {
    const confirmacion = await Swal.fire({
        title: '¿Desactivar bloque de horario?',
        html: 'El bloque quedará <b>inactivo</b> y no podrá usarse en la programación.<br><span class="text-sm text-gray-500">No se borrará de la base de datos y podrá reactivarse.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-ban mr-1"></i> Sí, desactivar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true
    });

    if (!confirmacion.isConfirmed) return;

    const datosDelete = new FormData();
    datosDelete.append('id_bloque', id_bloque);

    const resultado = await peticionAjax('eliminar', datosDelete);

    if (resultado && resultado.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Desactivado',
            text: resultado.message || 'El bloque fue desactivado correctamente.',
            timer: 1800,
            showConfirmButton: false
        });
        cargarTablaHorario();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: resultado?.message || 'No se pudo desactivar el bloque.'
        });
    }
}

async function reactivarHorario(id_bloque) {
    const confirmacion = await Swal.fire({
        title: '¿Reactivar bloque de horario?',
        html: 'El bloque volverá a estar <b>disponible</b>.<br><span class="text-sm text-gray-500">Se validará que no se solape con otros bloques activos.</span>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-undo mr-1"></i> Sí, reactivar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    if (!confirmacion.isConfirmed) return;

    const fd = new FormData();
    fd.append('id_bloque', id_bloque);

    const resultado = await peticionAjax('reactivar', fd);

    if (resultado && resultado.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Reactivado',
            text: resultado.message || 'El bloque fue reactivado correctamente.',
            timer: 1800,
            showConfirmButton: false
        });
        cargarTablaHorario();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo reactivar',
            text: resultado?.message || 'Puede existir un solapamiento con otro bloque activo.'
        });
    }
}