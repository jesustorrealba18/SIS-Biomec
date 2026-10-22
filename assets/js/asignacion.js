let asignacionData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

let carrilesSeleccionados = new Set();
let carrilesEstadoActual = [];

const modalAsignacion = document.getElementById('modalAsignacion');
const modalVer = document.getElementById('modalVerAsignacion');
const formAsignacion = document.getElementById('formAsignacion');
const btnGuardar = document.getElementById('btnGuardar');
const totalAsignaciones = document.getElementById('totalAsignaciones');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

const API_URL = 'index.php?p=asignacion';

function validarCampoAsignacion(input) {
    const valor = input.value.trim();
    const nombre = input.dataset.nombre || input.name || 'Campo';
    const reglas = input.dataset.validar || '';
    let error = '';

    input.classList.remove('border-red-500', 'border-2', 'border-green-500', 'border');

    let errorContainer = input.parentElement.querySelector('.error-msg');
    if (!errorContainer) {
        errorContainer = document.createElement('span');
        errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
        input.parentElement.appendChild(errorContainer);
    }

    if (reglas.includes('requerido') && !valor) {
        error = `${nombre} es requerido`;
    }

    if (input.type === 'date' && valor) {
        const fecha = new Date(valor);
        if (isNaN(fecha.getTime())) {
            error = `${nombre} debe ser una fecha válida`;
        }
    }

    if (input.id === 'fecha_vigente_inicio' || input.id === 'fecha_vigente_fin') {
        const fechaInicio = document.getElementById('fecha_vigente_inicio');
        const fechaFin = document.getElementById('fecha_vigente_fin');

        if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);

            if (inicio > fin) {
                if (input.id === 'fecha_vigente_fin') {
                    error = 'La fecha de fin debe ser mayor o igual a la fecha de inicio';
                }
            }
        }
    }

    if (error) {
        input.classList.add('border-red-500', 'border-2');
        errorContainer.textContent = error;
        errorContainer.style.display = 'block';
        return false;
    } else if (valor) {
        input.classList.add('border-green-500', 'border');
        errorContainer.textContent = '';
        errorContainer.style.display = 'none';
        return true;
    } else {
        errorContainer.textContent = '';
        errorContainer.style.display = 'none';
        return true;
    }
}

function setupValidacionTiempoRealAsignacion() {
    const campos = [
        { id: 'id_bloque_horario', reglas: 'requerido', nombre: 'Bloque horario' },
        { id: 'id_grupo', reglas: 'requerido', nombre: 'Grupo' },
        { id: 'fecha_vigente_inicio', reglas: 'requerido', nombre: 'Fecha inicio' },
        { id: 'fecha_vigente_fin', reglas: 'requerido', nombre: 'Fecha fin' }
    ];

    campos.forEach(({ id, reglas, nombre }) => {
        const input = document.getElementById(id);
        if (!input) return;

        input.dataset.validar = reglas;
        input.dataset.nombre = nombre;

        let errorContainer = input.parentElement.querySelector('.error-msg');
        if (!errorContainer) {
            errorContainer = document.createElement('span');
            errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
            input.parentElement.appendChild(errorContainer);
        }

        input.addEventListener('blur', function() {
            validarCampoAsignacion(this);
        });

        input.addEventListener('change', function() {
            if (this.dataset.touched === 'true') {
                validarCampoAsignacion(this);
                if (this.id === 'fecha_vigente_inicio' || this.id === 'fecha_vigente_fin') {
                    const otro = document.getElementById(
                        this.id === 'fecha_vigente_inicio' ? 'fecha_vigente_fin' : 'fecha_vigente_inicio'
                    );
                    if (otro && otro.value) {
                        validarCampoAsignacion(otro);
                    }
                }
            }
        });

        input.addEventListener('focus', function() {
            this.dataset.touched = 'true';
        });
    });
}

function validarFormularioCompletoAsignacion(form) {
    const inputs = form.querySelectorAll('input[data-validar], select[data-validar]');
    let hasError = false;
    let primerosErrores = [];

    inputs.forEach(input => {
        input.dataset.touched = 'true';
        const isValid = validarCampoAsignacion(input);
        if (!isValid) {
            hasError = true;
            const errorMsg = input.parentElement.querySelector('.error-msg');
            if (errorMsg && errorMsg.textContent) {
                primerosErrores.push(errorMsg.textContent);
            }
        }
    });

    if (carrilesSeleccionados.size === 0) {
        hasError = true;
        primerosErrores.push('Debes seleccionar al menos un carril en el mapa de la piscina.');
    }

    return { hasError, errores: primerosErrores };
}

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        if (typeof UI !== 'undefined') {
            UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        }
        return null;
    }
}

function cerrarModalAsignacion() {
    if (modalAsignacion && modalAsignacion.firstElementChild) {
        modalAsignacion.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modalAsignacion) modalAsignacion.classList.add('hidden');
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

function cerrarModalVerCarril() {
    const modal = document.getElementById('modalVerCarril');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function cerrarModalVerBloque() {
    const modal = document.getElementById('modalVerBloque');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function cerrarModalVerGrupo() {
    const modal = document.getElementById('modalVerGrupo');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function cerrarModalHistorial() {
    const modal = document.getElementById('modalHistorial');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (modalAsignacion && !modalAsignacion.classList.contains('hidden')) cerrarModalAsignacion();
        if (modalVer && !modalVer.classList.contains('hidden')) cerrarModalVer();

        const modalHistorial = document.getElementById('modalHistorial');
        if (modalHistorial && !modalHistorial.classList.contains('hidden')) cerrarModalHistorial();

        const modalCarril = document.getElementById('modalVerCarril');
        const modalBloque = document.getElementById('modalVerBloque');
        const modalGrupo = document.getElementById('modalVerGrupo');

        if (modalCarril && !modalCarril.classList.contains('hidden')) cerrarModalVerCarril();
        if (modalBloque && !modalBloque.classList.contains('hidden')) cerrarModalVerBloque();
        if (modalGrupo && !modalGrupo.classList.contains('hidden')) cerrarModalVerGrupo();
    }
});

async function verDetalleCarril(id) {
    const carril = await peticionAjax(`obtenerDetalleCarril&id=${id}`);
    if (!carril) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del carril');
        return;
    }

    const verNumero = document.getElementById('verCarrilNumero');
    const verCapacidad = document.getElementById('verCarrilCapacidad');
    const verEstado = document.getElementById('verCarrilEstado');

    if (verNumero) verNumero.innerText = carril.numero;
    if (verCapacidad) verCapacidad.innerText = carril.capacidad_maxima;
    if (verEstado) {
        verEstado.innerHTML = carril.activo == 1
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-xs">Activo</span>'
            : '<span class="px-2 py-1 bg-red-500/20 text-red-600 dark:text-red-400 rounded-full text-xs">Inactivo</span>';
    }

    const modal = document.getElementById('modalVerCarril');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

async function verDetalleBloque(id) {
    const bloque = await peticionAjax(`obtenerDetalleBloque&id=${id}`);
    if (!bloque) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del bloque horario');
        return;
    }

    const verDia = document.getElementById('verBloqueDia');
    const verInicio = document.getElementById('verBloqueInicio');
    const verFin = document.getElementById('verBloqueFin');
    const verRango = document.getElementById('verBloqueRango');

    if (verDia) verDia.innerText = bloque.dia_semana;
    if (verInicio) verInicio.innerText = bloque.hora_inicio;
    if (verFin) verFin.innerText = bloque.hora_fin;
    if (verRango) {
        verRango.innerHTML = `<span class="px-3 py-1 bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-full text-sm">${bloque.hora_inicio} - ${bloque.hora_fin}</span>`;
    }

    const modal = document.getElementById('modalVerBloque');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

async function verDetalleGrupo(id) {
    const grupo = await peticionAjax(`obtenerDetalleGrupo&id=${id}`);
    if (!grupo) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del grupo');
        return;
    }

    const verNombre = document.getElementById('verGrupoNombre');
    const verDescripcion = document.getElementById('verGrupoDescripcion');
    const verEntrenador = document.getElementById('verGrupoEntrenador');
    const verEstado = document.getElementById('verGrupoEstado');

    if (verNombre) verNombre.innerText = grupo.nombre;
    if (verDescripcion) verDescripcion.innerText = grupo.descripcion || 'Sin descripción';
    if (verEntrenador) {
        verEntrenador.innerHTML = grupo.entrenador_nombre
            ? `${grupo.entrenador_nombre} <span class="text-xs text-gray-500">(${grupo.entrenador_cedula})</span>`
            : '<span class="text-gray-500">Sin entrenador asignado</span>';
    }
    if (verEstado) {
        verEstado.innerHTML = grupo.activo == 1
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-xs">Activo</span>'
            : '<span class="px-2 py-1 bg-red-500/20 text-red-600 dark:text-red-400 rounded-full text-xs">Inactivo</span>';
    }

    const modal = document.getElementById('modalVerGrupo');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

async function cargarSelects() {
    try {
        const bloques = await peticionAjax('listarHorarios');
        const selectBloque = document.getElementById('id_bloque_horario');
        if (selectBloque && bloques && Array.isArray(bloques)) {
            selectBloque.innerHTML = '<option value="">Seleccione un horario</option>';
            if (bloques.length > 0) {
                bloques.forEach(bloque => {
                    selectBloque.innerHTML += `<option value="${bloque.id_bloque}">${bloque.dia_semana} - ${bloque.hora_inicio} a ${bloque.hora_fin}</option>`;
                });
            } else {
                selectBloque.innerHTML += `<option value="" disabled>No hay horarios disponibles</option>`;
            }
        }

        const grupos = await peticionAjax('listarGruposParaSelect');
        const selectGrupo = document.getElementById('id_grupo');
        if (selectGrupo && grupos && Array.isArray(grupos)) {
            selectGrupo.innerHTML = '<option value="">Seleccione un grupo</option>';
            if (grupos.length > 0) {
                grupos.forEach(grupo => {
                    selectGrupo.innerHTML += `<option value="${grupo.id_grupo}">${grupo.nombre}</option>`;
                });
            } else {
                selectGrupo.innerHTML += `<option value="" disabled>No hay grupos disponibles</option>`;
            }
        }
    } catch (error) {
        if (typeof UI !== 'undefined') {
            UI.error('Error', 'No se pudieron cargar los datos del formulario');
        }
    }
}

async function cargarMapaCarriles() {
    const idBloque = document.getElementById('id_bloque_horario')?.value;
    const fechaInicio = document.getElementById('fecha_vigente_inicio')?.value;
    const fechaFin = document.getElementById('fecha_vigente_fin')?.value;
    const container = document.getElementById('carrilesContainer');
    const idAsignacionEditando = document.getElementById('id_asignacion')?.value;

    if (!container) return;

    if (!idBloque) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 text-xs">
            <i class="fas fa-info-circle mr-1"></i> Selecciona un bloque horario para ver los carriles
        </div>`;
        return;
    }

    container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando carriles...</div>`;

    let url = `${API_URL}&accion=estadoCarrilesPorBloque&id_bloque=${idBloque}`;
    if (fechaInicio && fechaFin) {
        url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    }

    try {
        const resp = await fetch(url);
        const carriles = await resp.json();
        carrilesEstadoActual = carriles || [];

        if (idAsignacionEditando) {
            const asig = await peticionAjax(`obtenerAsignacion&id=${idAsignacionEditando}`);
            if (asig && asig.id_carril) {
                carrilesSeleccionados = new Set([parseInt(asig.id_carril)]);
            }
        }

        renderMapaCarriles();
    } catch (e) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-red-500 text-xs">Error al cargar carriles</div>`;
    }
}

function renderMapaCarriles() {
    const container = document.getElementById('carrilesContainer');
    if (!container) return;

    if (!carrilesEstadoActual.length) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 text-xs">No hay carriles disponibles</div>`;
        actualizarContadorSeleccionados();
        return;
    }

    container.innerHTML = carrilesEstadoActual.map(c => {
        const seleccionado = carrilesSeleccionados.has(c.id_carril);
        // Si está ocupado por OTRA asignación (no la que estamos editando), bloquear
        const ocupadoPorOtra = c.ocupado && !seleccionado;

        let bgClass, borderClass, iconClass, cursorClass, title;
        if (ocupadoPorOtra) {
            bgClass = 'bg-red-500/20 dark:bg-red-500/30';
            borderClass = 'border-red-500';
            iconClass = 'text-red-600 dark:text-red-400';
            cursorClass = 'cursor-not-allowed opacity-70';
            title = `Ocupado por: ${c.detalle_ocupacion?.grupo_nombre || 'Otro grupo'}`;
        } else if (seleccionado) {
            bgClass = 'bg-indigo-500/30 dark:bg-indigo-500/40';
            borderClass = 'border-indigo-500 ring-2 ring-indigo-400';
            iconClass = 'text-indigo-600 dark:text-indigo-300';
            cursorClass = 'cursor-pointer';
            title = `Carril ${c.numero} - Seleccionado`;
        } else {
            bgClass = 'bg-emerald-500/20 dark:bg-emerald-500/30';
            borderClass = 'border-emerald-500';
            iconClass = 'text-emerald-600 dark:text-emerald-400';
            cursorClass = 'cursor-pointer hover:scale-105';
            title = `Carril ${c.numero} - Libre (Cap: ${c.capacidad_maxima})`;
        }

        return `
            <div onclick="${ocupadoPorOtra ? '' : `toggleCarril(${c.id_carril})`}"
                 title="${title}"
                 class="carril-card ${ocupadoPorOtra ? 'ocupado' : ''} relative aspect-square rounded-xl border-2 ${borderClass} ${bgClass} ${cursorClass} transition-all duration-200 flex flex-col items-center justify-center p-2 select-none">
                <i class="fas fa-swimmer text-2xl ${iconClass} mb-1"></i>
                <span class="text-xs font-bold text-gray-800 dark:text-white">Carril ${c.numero}</span>
                <span class="text-[10px] text-gray-600 dark:text-gray-400">Cap: ${c.capacidad_maxima}</span>
                ${ocupadoPorOtra ? '<i class="fas fa-lock absolute top-1 right-1 text-red-500 text-xs"></i>' : ''}
                ${seleccionado ? '<i class="fas fa-check-circle absolute top-1 right-1 text-indigo-500 text-sm"></i>' : ''}
            </div>
        `;
    }).join('');

    actualizarHiddenSeleccionados();
    actualizarContadorSeleccionados();
}

function toggleCarril(idCarril) {
    if (carrilesSeleccionados.has(idCarril)) {
        carrilesSeleccionados.delete(idCarril);
    } else {
        carrilesSeleccionados.add(idCarril);
    }
    renderMapaCarriles();
}

function actualizarHiddenSeleccionados() {
    const hidden = document.getElementById('carriles_seleccionados');
    if (hidden) hidden.value = Array.from(carrilesSeleccionados).join(',');
}

function actualizarContadorSeleccionados() {
    const contador = document.getElementById('contadorSeleccionados');
    if (contador) {
        const n = carrilesSeleccionados.size;
        contador.textContent = n === 0
            ? 'Ningún carril seleccionado'
            : `${n} carril${n !== 1 ? 'es' : ''} seleccionado${n !== 1 ? 's' : ''}`;
    }
}

async function abrirModalAsignacion(id_asignacion = null) {
    if (formAsignacion) formAsignacion.reset();

    try {
        if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formAsignacion);
    } catch(e) {}

    document.querySelectorAll('#formAsignacion .error-msg').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
    document.querySelectorAll('#formAsignacion input, #formAsignacion select').forEach(el => {
        el.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
    });

    carrilesSeleccionados = new Set();
    carrilesEstadoActual = [];

    const inputIdHidden = document.getElementById('id_asignacion');
    const modalTitulo = document.getElementById('modalTitulo');

    await cargarSelects();

    if (id_asignacion) {
        if (inputIdHidden) inputIdHidden.value = id_asignacion;
        if (modalTitulo) modalTitulo.textContent = 'Editar Asignación de Carril';
        if (btnGuardar) btnGuardar.innerHTML = 'EDITAR ASIGNACIÓN <i class="fas fa-sync-alt ml-2"></i>';

        const asignacion = await peticionAjax(`obtenerAsignacion&id=${id_asignacion}`);

        if (asignacion) {
            const idBloque = document.getElementById('id_bloque_horario');
            const idGrupo = document.getElementById('id_grupo');
            const diaEspecifico = document.getElementById('dia_especifico');
            const fechaInicio = document.getElementById('fecha_vigente_inicio');
            const fechaFin = document.getElementById('fecha_vigente_fin');
            const activa = document.getElementById('activa');

            if (idBloque) idBloque.value = asignacion.id_bloque_horario;
            if (idGrupo) idGrupo.value = asignacion.id_grupo;
            if (diaEspecifico) diaEspecifico.value = asignacion.dia_especifico;
            if (fechaInicio) fechaInicio.value = asignacion.fecha_vigencia_inicio;
            if (fechaFin) fechaFin.value = asignacion.fecha_vigencia_fin;
            if (activa) activa.checked = asignacion.activa == 1;

            if (asignacion.id_carril) {
                carrilesSeleccionados.add(parseInt(asignacion.id_carril));
            }
        }
    } else {
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Asignación de Carril';
        if (btnGuardar) btnGuardar.innerHTML = 'GUARDAR ASIGNACIÓN <i class="fas fa-save ml-2"></i>';

        const activaCheck = document.getElementById('activa');
        if (activaCheck) activaCheck.checked = true;

        const hoy = new Date().toISOString().split('T')[0];
        const fechaInicio = document.getElementById('fecha_vigente_inicio');
        if (fechaInicio) fechaInicio.value = hoy;

        const fechaFin = document.getElementById('fecha_vigente_fin');
        if (fechaFin) {
            const fecha = new Date();
            fecha.setMonth(fecha.getMonth() + 1);
            fechaFin.value = fecha.toISOString().split('T')[0];
        }
    }

    if (modalAsignacion) {
        modalAsignacion.classList.remove('hidden');
        setTimeout(() => {
            if (modalAsignacion.firstElementChild) {
                modalAsignacion.firstElementChild.classList.remove('scale-95', 'opacity-0');
            }
        }, 10);
    }

    setTimeout(() => cargarMapaCarriles(), 100);
}

async function verDetalle(id) {
    const asignacion = await peticionAjax(`obtenerAsignacion&id=${id}`);

    if (!asignacion) {
        if (typeof UI !== 'undefined') {
            UI.error('Error', 'No se pudo cargar el detalle de la asignación');
        } else {
            alert('Error: No se pudo cargar el detalle de la asignación.');
        }
        return;
    }

    const verCarril = document.getElementById('verCarril');
    const verBloque = document.getElementById('verBloqueHorario');
    const verGrupo = document.getElementById('verGrupo');
    const verDia = document.getElementById('verDiaEspecifico');
    const verFechaInicio = document.getElementById('verFechaInicio');
    const verFechaFin = document.getElementById('verFechaFin');
    const verEstado = document.getElementById('verEstado');

    if (verCarril) {
        verCarril.innerText = asignacion.carril_numero || asignacion.id_carril || '—';
    }

    if (verBloque) {
        const dia = asignacion.dia_semana || '';
        const inicio = asignacion.hora_inicio || '';
        const fin = asignacion.hora_fin || '';
        verBloque.innerHTML = `${dia} ${inicio} - ${fin}`.trim() || '—';
    }

    if (verGrupo) {
        verGrupo.innerText = asignacion.grupo_nombre || asignacion.id_grupo || '—';
    }

    if (verDia) {
        verDia.innerText = asignacion.dia_especifico || '—';
    }

    if (verFechaInicio) {
        verFechaInicio.innerText = asignacion.fecha_vigencia_inicio || 'No definida';
    }

    if (verFechaFin) {
        verFechaFin.innerText = asignacion.fecha_vigencia_fin || 'No definida';
    }

    if (verEstado) {
        if (asignacion.estado === 'completada') {
            verEstado.innerHTML = '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-xs"><i class="fas fa-check-circle mr-1"></i> Completada</span>';
        } else if (asignacion.activa == 1) {
            verEstado.innerHTML = '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-xs"><i class="fas fa-circle text-[6px] mr-1 text-emerald-500 animate-pulse"></i> Activa</span>';
        } else {
            verEstado.innerHTML = '<span class="px-2 py-1 bg-gray-500/20 text-gray-600 dark:text-gray-400 rounded-full text-xs">Inactiva</span>';
        }
    }

    if (modalVer) {
        modalVer.classList.remove('hidden');
        setTimeout(() => {
            if (modalVer.firstElementChild) {
                modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
            }
        }, 10);
    }
}

async function completarAsignacion(id_asignacion) {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: '¿Completar asignación?',
            text: 'Esto liberará el carril y horario para que otros grupos puedan utilizarlo.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, completar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;
    } else {
        if (!confirm('¿Estás seguro de completar esta asignación?\nEsto liberará el carril y horario para otros grupos.')) {
            return;
        }
    }

    let datos = new FormData();
    datos.append('accion', 'completar');
    datos.append('id_asignacion', id_asignacion);

    const resultado = await peticionAjax('completar', datos);
    if (resultado && resultado.status === 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Completada!',
                text: 'La asignación ha sido completada y los recursos liberados.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (typeof UI !== 'undefined') {
            UI.exito('Asignación Completada', 'Recursos liberados correctamente.');
        }
        cargarTablaAsignaciones();
    } else {
        const errorMsg = resultado?.message || 'No se pudo completar la asignación.';
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', errorMsg, 'error');
        } else if (typeof UI !== 'undefined') {
            UI.error('Error', errorMsg);
        }
    }
}

async function reactivarAsignacion(id_asignacion) {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: '¿Reactivar asignación?',
            text: 'Solo se puede reactivar si la fecha de fin no ha pasado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;
    } else {
        if (!confirm('¿Estás seguro de reactivar esta asignación?')) {
            return;
        }
    }

    let datos = new FormData();
    datos.append('accion', 'reactivar');
    datos.append('id_asignacion', id_asignacion);

    const resultado = await peticionAjax('reactivar', datos);
    if (resultado && resultado.status === 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Reactivada', resultado.message || 'La asignación ha sido reactivada.', 'success');
        } else if (typeof UI !== 'undefined') {
            UI.exito('Reactivada', resultado.message || 'La asignación ha sido reactivada.');
        }
        cargarTablaAsignaciones();
    } else {
        const errorMsg = resultado?.message || 'No se pudo reactivar la asignación.';
        if (typeof Swal !== 'undefined') {
            Swal.fire('No se puede reactivar', errorMsg, 'error');
        } else if (typeof UI !== 'undefined') {
            UI.error('Error', errorMsg);
        }
    }
}

async function eliminarAsignacion(id_asignacion) {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: '¿Desactivar asignación?',
            text: 'La asignación quedará inactiva y el carril será liberado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        });
        if (!result.isConfirmed) return;
    } else {
        if (!confirm('¿Está seguro de desactivar esta asignación?')) return;
    }

    let datosDelete = new FormData();
    datosDelete.append('accion', 'eliminar');
    datosDelete.append('id_asignacion', id_asignacion);

    const resultado = await peticionAjax('eliminar', datosDelete);
    if (resultado && resultado.status === 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Desactivada', 'La asignación ha sido desactivada.', 'success');
        } else if (typeof UI !== 'undefined') {
            UI.exito('Desactivada', 'La asignación ha sido desactivada.');
        }
        cargarTablaAsignaciones();
    } else {
        const msg = resultado?.message || 'No se pudo desactivar la asignación.';
        if (typeof Swal !== 'undefined') Swal.fire('Error', msg, 'error');
        else if (typeof UI !== 'undefined') UI.error('Error', msg);
    }
}

async function verHistorialCompletadas() {
    const modal = document.getElementById('modalHistorial');
    const tbody = document.getElementById('listaCompletadas');

    if (!modal || !tbody) return;

    modal.classList.remove('hidden');
    setTimeout(() => {
        if (modal.firstElementChild) {
            modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }
    }, 10);

    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center p-8 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Cargando historial...
            </td>
        </tr>
    `;

    const completadas = await peticionAjax('listarCompletadas');

    if (!completadas || completadas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-3xl block mb-2 text-gray-300 dark:text-gray-600"></i>
                    No hay asignaciones completadas en el historial
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = completadas.map(a => `
        <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
            <td class="p-3 font-medium text-gray-900 dark:text-white">
                <span class="flex items-center gap-2">
                    Carril ${a.carril_numero}
                    <span class="text-[10px] px-2 py-0.5 bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-full">#${a.id_carril}</span>
                </span>
            </td>
            <td class="p-3 text-gray-700 dark:text-gray-300">
                <span class="text-sm">${a.dia_semana || '—'}</span>
                <span class="text-xs text-gray-500 block">${a.hora_inicio || ''} - ${a.hora_fin || ''}</span>
            </td>
            <td class="p-3 text-gray-700 dark:text-gray-300">${a.grupo_nombre || '—'}</td>
            <td class="p-3 text-gray-700 dark:text-gray-300 text-xs">
                <div>Inicio: ${a.fecha_vigencia_inicio || '—'}</div>
                <div>Fin: ${a.fecha_vigencia_fin || '—'}</div>
            </td>
            <td class="p-3">
                <div class="flex flex-col items-end gap-1">
                    <span class="text-xs text-gray-700 dark:text-gray-300">
                        ${a.fecha_completacion || '—'}
                    </span>
                    <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-medium">
                        <i class="fas fa-check-circle mr-1"></i> Completada
                    </span>
                </div>
            </td>
        </tr>
    `).join('');
}

async function verificarAsignacionesVencidas() {
    try {
        const resultado = await peticionAjax('verificarVencidas');
        if (resultado && resultado.status === 'success') {
            const cantidad = parseInt(resultado.message.match(/\d+/)?.[0] || 0);
            if (cantidad > 0 && typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Asignaciones Vencidas',
                    text: `Se completaron automáticamente ${cantidad} asignaciones vencidas.`,
                    icon: 'info',
                    timer: 3000,
                    showConfirmButton: false
                });
            } else if (cantidad > 0 && typeof UI !== 'undefined') {
                UI.informacion('Asignaciones Vencidas', `Se completaron automáticamente ${cantidad} asignaciones vencidas.`);
            }
            if (cantidad > 0) {
                cargarTablaAsignaciones();
            }
        }
    } catch (error) {
    }
}

function renderTabla() {
    const tbody = document.getElementById('listaAsignaciones');
    if (!tbody) return;

    let datos = asignacionData.slice();

    if (tablaFiltro) {
        const q = tablaFiltro.toLowerCase().trim();
        datos = datos.filter(a => {
            const texto = `${a.carril_numero} ${a.dia_semana} ${a.grupo_nombre} ${a.fecha_vigencia_inicio}`.toLowerCase();
            return texto.includes(q);
        });
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
    if (totalAsignaciones) totalAsignaciones.textContent = `${asignacionData.length} Asignaciones`;

    if (infoTabla) {
        if (total === 0) {
            infoTabla.textContent = '0 registros';
        } else {
            const inicio = (tablaPagina - 1) * tablaPorPagina + 1;
            const fin = Math.min(tablaPagina * tablaPorPagina, total);
            infoTabla.textContent = `Mostrando ${inicio}–${fin} de ${total}`;
        }
    }

    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;
    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-exchange-alt text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay asignaciones registradas en el sistema</span>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = pagina.map(a => {
            const busqueda = `${a.carril_numero} ${a.dia_semana} ${a.grupo_nombre}`.toLowerCase();

            let botonesAccion = '';
            const tienePermiso = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar;

            if (tienePermiso) {
                botonesAccion += `
                    <button onclick="verDetalle(${a.id_asignacion})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Ver Detalle">
                        <i class="fas fa-eye text-base"></i>
                    </button>
                `;

                if (a.activa == 1) {
                    botonesAccion += `
                        <button onclick="abrirModalAsignacion(${a.id_asignacion})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Asignación">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                    `;
                }

                if (a.activa == 1) {
                    botonesAccion += `
                        <button onclick="completarAsignacion(${a.id_asignacion})" class="text-amber-400 hover:text-amber-300 p-2 rounded-lg hover:bg-amber-500/10 transition duration-200" title="Completar Asignación">
                            <i class="fas fa-check-double text-base"></i>
                        </button>
                    `;
                }

                if (a.activa == 0) {
                    botonesAccion += `
                        <button onclick="reactivarAsignacion(${a.id_asignacion})" class="text-blue-400 hover:text-blue-300 p-2 rounded-lg hover:bg-blue-500/10 transition duration-200" title="Reactivar Asignación">
                            <i class="fas fa-sync-alt text-base"></i>
                        </button>
                    `;
                }

                if (a.activa == 1) {
                    botonesAccion += `
                        <button onclick="eliminarAsignacion(${a.id_asignacion})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Desactivar Asignación">
                            <i class="fas fa-trash-alt text-base"></i>
                        </button>
                    `;
                }
            } else {
                botonesAccion = '<span class="text-gray-600 text-xs">Solo lectura</span>';
            }

            return `
                <tr class="asignacion-row hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200" data-busqueda="${busqueda}">
                    <td class="p-4 font-medium text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            Carril ${a.carril_numero}
                            <button onclick="verDetalleCarril(${a.id_carril})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del carril">
                                <i class="fas fa-info-circle text-xs"></i>
                            </button>
                        </div>
                    </td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            ${a.dia_semana || '—'} ${a.hora_inicio || ''} - ${a.hora_fin || ''}
                            <button onclick="verDetalleBloque(${a.id_bloque_horario})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del horario">
                                <i class="fas fa-info-circle text-xs"></i>
                            </button>
                        </div>
                    </td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            ${a.grupo_nombre || '—'}
                            <button onclick="verDetalleGrupo(${a.id_grupo})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del grupo">
                                <i class="fas fa-info-circle text-xs"></i>
                            </button>
                        </div>
                    </td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${a.dia_especifico || '—'}</td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${a.fecha_vigencia_inicio || '—'}</td>
                    <td class="p-4">
                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full ${a.activa == 1 ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20'} uppercase tracking-wide">
                            ${a.activa == 1 ? 'Activa' : 'Inactiva'}
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-1">
                        ${botonesAccion}
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

async function cargarTablaAsignaciones() {
    const tbody = document.getElementById('listaAsignaciones');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando asignaciones...</span></tr>`;

    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const asignaciones = await peticionAjax(`listarAsignaciones&estado=${filtroEstado}`);

    if (!asignaciones || asignaciones.length === 0) {
        asignacionData = [];
        if(totalAsignaciones) totalAsignaciones.textContent = '0 Asignaciones';
        tablaFiltro = '';
        tablaSortCol = '';
        tablaSortDir = '';
        tablaPagina = 1;
        if(infoTabla) infoTabla.textContent = '';
        if(pieTabla) pieTabla.innerHTML = '';
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-exchange-alt text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay asignaciones registradas</span>
                </td>
            </tr>
        `;
        return;
    }

    asignacionData = asignaciones;
    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;
    renderTabla();
}

const inputBusqueda = document.getElementById('busquedaAsignacion');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
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

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaAsignaciones();

    verificarAsignacionesVencidas();

    try {
        setupValidacionTiempoRealAsignacion();
    } catch (e) {
    }

    try {
        if (typeof Validador !== 'undefined' && formAsignacion) {
            Validador.vincularTiempoReal(formAsignacion);
        }
    } catch (e) {}

    const filtroEstado = document.getElementById('filtroEstado');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function() {
            cargarTablaAsignaciones();
        });
    }

    // Listeners para recargar el mapa cuando cambien bloque o fechas
    ['id_bloque_horario', 'fecha_vigente_inicio', 'fecha_vigente_fin'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                cargarMapaCarriles();
            });
        }
    });

    if (formAsignacion) {
        formAsignacion.addEventListener('submit', async function (e) {
            e.preventDefault();

            const { hasError, errores } = validarFormularioCompletoAsignacion(this);

            if (hasError) {
                const mensaje = errores.length > 0
                    ? errores.join('<br>')
                    : 'Por favor corrige los campos marcados en rojo.';

                if (typeof UI !== 'undefined') {
                    UI.advertencia('Datos Incompletos o Inválidos', mensaje);
                } else {
                    alert('Por favor corrige los campos marcados en rojo:\n' + errores.join('\n'));
                }

                const primerError = this.querySelector('.border-red-500');
                if (primerError) {
                    primerError.focus();
                    primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            let erroresJS = false;
            try {
                if (typeof Validador !== 'undefined') {
                    erroresJS = Validador.validarFormulario(formAsignacion);
                }
            } catch(e) {}

            if (erroresJS) {
                if (typeof UI !== 'undefined') UI.advertencia('Datos Inválidos', erroresJS);
                return;
            }

            const textoOriginal = btnGuardar ? btnGuardar.innerHTML : '';
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';
            }

            const datosForm = new FormData(formAsignacion);

            // Enviar carriles seleccionados como JSON
            datosForm.delete('carriles_seleccionados');
            datosForm.append('carriles_seleccionados', JSON.stringify(Array.from(carrilesSeleccionados)));

            const resultado = await peticionAjax('guardar', datosForm);

            if (resultado) {
                if (resultado.status === 'success') {
                    if (typeof UI !== 'undefined') UI.exito('Transacción Exitosa', resultado.message);
                    cerrarModalAsignacion();
                    cargarTablaAsignaciones();
                } else if (resultado.status === 'warning') {
                    let msjErrores = Object.values(resultado.errores).join("<br>");
                    if (typeof UI !== 'undefined') UI.advertencia('Validación', msjErrores);
                } else {
                    if (typeof UI !== 'undefined') UI.error('Error', resultado.message);
                }
            }

            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
            }
        });
    }
});