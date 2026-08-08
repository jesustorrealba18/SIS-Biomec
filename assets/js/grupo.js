const API_URL = 'index.php?p=grupo';

const modalGrupo = document.getElementById('modalGrupo');
const modalAsignacion = document.getElementById('modalAsignacion');
const modalVerGrupo = document.getElementById('modalVerGrupo');
const formGrupo = document.getElementById('formGrupo');
const formAsignacion = document.getElementById('formAsignacion');
const btnGuardar = document.getElementById('btnGuardar');
const btnAsignar = document.getElementById('btnAsignar');
const totalGrupos = document.getElementById('totalGrupos');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

let gruposData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

async function peticionAjax(accion, datos = null) {
    const url = `${API_URL}&accion=${accion}`;
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(url, opciones);
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

// ============================================
// FUNCIÓN AUXILIAR: ESCAPE HTML
// ============================================
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

class ValidadorTiempoReal {
    constructor() {
        this.errores = {};
        this.reglas = {
            'nombre': {
                requerido: true,
                minLength: 5,
                maxlength: 50,
                regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9\-]+$/,
                mensajes: {
                    requerido: 'El nombre del grupo es requerido',
                    minLength: 'El nombre no puede tener menos de 5 caracteres',
                    maxlength: 'El nombre no puede tener más de 50 caracteres',
                    regex: 'Solo se permiten letras, números, guiones y espacios'
                }
            },
            'id_entrenador': {
                requerido: true,
                numerico: true,
                mensajes: {
                    requerido: 'Debe seleccionar un entrenador',
                    numerico: 'Seleccione un entrenador válido'
                }
            }
        };
        
        this.inicializar();
    }

    inicializar() {
        document.querySelectorAll('[data-validate]').forEach(campo => {
            const eventos = ['input', 'blur', 'change'];
            eventos.forEach(evento => {
                campo.addEventListener(evento, (e) => {
                    this.validarCampo(e.target);
                });
            });
        });

        document.querySelectorAll('input[name="atletas[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.validarAtletasSeleccionados();
            });
        });

        const edadMin = document.getElementById('edad_min');
        const edadMax = document.getElementById('edad_max');
        if (edadMin && edadMax) {
            edadMin.addEventListener('input', () => this.validarRangoEdad());
            edadMax.addEventListener('input', () => this.validarRangoEdad());
        }
    }

    validarCampo(campo) {
        const nombre = campo.name || campo.id;
        const valor = campo.value;
        const tipo = campo.type;
        const regla = this.reglas[nombre];

        if (!regla) return;

        this.errores[nombre] = [];
        let valido = true;

        if (regla.requerido) {
            if (tipo === 'select-one') {
                if (!valor || valor === '') {
                    this.errores[nombre].push(regla.mensajes.requerido);
                    valido = false;
                }
            } else if (tipo === 'checkbox') {
            
            } else {
                if (!valor || valor.trim() === '') {
                    this.errores[nombre].push(regla.mensajes.requerido);
                    valido = false;
                }
            }
        }

        if (regla.maxlength && valor && valor.length > regla.maxlength) {
            this.errores[nombre].push(regla.mensajes.maxlength);
            valido = false;
        }

        if (regla.regex && valor && !regla.regex.test(valor)) {
            this.errores[nombre].push(regla.mensajes.regex);
            valido = false;
        }

        this.mostrarFeedback(campo, valido, this.errores[nombre]);

        if (valido && nombre === 'nombre' && valor.length >= 3) {
            this.verificarDuplicado(valor);
        }

        return valido;
    }

    mostrarFeedback(campo, valido, errores) {
        const feedback = document.getElementById(`${campo.id}-error`);
        if (!feedback) return;

        if (valido) {
            campo.classList.remove('is-invalid');
            campo.classList.add('is-valid');
            feedback.textContent = '';
            feedback.style.display = 'none';
        } else {
            campo.classList.remove('is-valid');
            campo.classList.add('is-invalid');
            feedback.textContent = errores.join(', ');
            feedback.style.display = 'block';
        }
    }

    async verificarDuplicado(nombre) {
        const idExcluir = document.getElementById('id_grupo_original')?.value || '';
        const campo = document.getElementById('nombre');
        
        try {
            const formData = new FormData();
            formData.append('accion', 'verificarDuplicado');
            formData.append('nombre', nombre);
            if (idExcluir) {
                formData.append('id_excluir', idExcluir);
            }

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.existe) {
                const feedback = document.getElementById('nombre-error');
                campo.classList.remove('is-valid');
                campo.classList.add('is-invalid');
                feedback.textContent = 'Este nombre de grupo ya existe';
                feedback.style.display = 'block';
                this.errores['nombre'] = ['Este nombre ya existe'];
            }
        } catch (error) {
            console.error('Error verificando duplicado:', error);
        }
    }

    validarAtletasSeleccionados() {
        const checkboxes = document.querySelectorAll('input[name="atletas[]"]:checked');
        const feedback = document.getElementById('atletas-error');
        const container = document.getElementById('atletas-container');

        if (checkboxes.length === 0) {
            if (container) container.classList.add('border-danger');
            if (feedback) {
                feedback.textContent = 'Debe seleccionar al menos un atleta';
                feedback.style.display = 'block';
            }
            this.errores['atletas'] = ['Debe seleccionar al menos un atleta'];
            return false;
        } else {
            if (container) {
                container.classList.remove('border-danger');
                container.classList.add('border-success');
            }
            if (feedback) {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
            this.errores['atletas'] = [];
            return true;
        }
    }

    validarRangoEdad() {
        const edadMin = document.getElementById('edad_min');
        const edadMax = document.getElementById('edad_max');
        const feedback = document.getElementById('edad-error');

        if (!edadMin || !edadMax || !feedback) return;

        const min = parseInt(edadMin.value);
        const max = parseInt(edadMax.value);

        if (min && max && min > max) {
            feedback.textContent = 'La edad mínima no puede ser mayor que la máxima';
            feedback.style.display = 'block';
            edadMin.classList.add('is-invalid');
            edadMax.classList.add('is-invalid');
            return false;
        } else {
            feedback.style.display = 'none';
            edadMin.classList.remove('is-invalid');
            edadMax.classList.remove('is-invalid');
            edadMin.classList.add('is-valid');
            edadMax.classList.add('is-valid');
            return true;
        }
    }

    validarFormulario(form) {
        let valido = true;
        const campos = form.querySelectorAll('[data-validate]');
        
        campos.forEach(campo => {
            if (!this.validarCampo(campo)) {
                valido = false;
            }
        });

        if (form.id === 'formAsignacion') {
            if (!this.validarAtletasSeleccionados()) {
                valido = false;
            }
        }

        if (!this.validarRangoEdad()) {
            valido = false;
        }

        return valido;
    }
}

function cerrarModalGrupo() {
    modalGrupo.classList.add('hidden');
    modalGrupo.firstElementChild.classList.add('scale-95', 'opacity-0');
}

function cerrarModalAsignacion() {
    modalAsignacion.classList.add('hidden');
    modalAsignacion.firstElementChild.classList.add('scale-95', 'opacity-0');
}

function cerrarModalVerGrupo() {
    if (!modalVerGrupo) return;
    const child = modalVerGrupo.firstElementChild;
    if (child) {
        child.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        modalVerGrupo.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (!modalGrupo.classList.contains('hidden')) {
            cerrarModalGrupo();
        }
        if (!modalAsignacion.classList.contains('hidden')) {
            cerrarModalAsignacion();
        }
        if (!modalVerGrupo.classList.contains('hidden')) {
            cerrarModalVerGrupo();
        }
    }
});

async function cargarCategorias() {
    const select = document.getElementById('filtroCategoria');
    if (!select) return;
    
    select.innerHTML = '<option value="">Todas las categorías</option>';
    
    try {
        const categorias = await peticionAjax('listarCategorias');
        
        if (Array.isArray(categorias)) {
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id_categoria;
                option.textContent = cat.nombre_completo || cat.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando categorías:', error);
    }
}

async function abrirModalGrupo(idGrupo = null) {
    formGrupo.reset();
    document.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    const inputIdOriginal = document.getElementById('id_grupo_original');
    if (inputIdOriginal) {
        inputIdOriginal.value = idGrupo || '';
    }

    modalGrupo.classList.remove('hidden');
    setTimeout(() => {
        modalGrupo.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const selectEntrenador = document.getElementById('id_entrenador');
    selectEntrenador.innerHTML = '<option value="">Cargando entrenadores...</option>';

    try {
        const entrenadores = await peticionAjax('listarEntrenador');

        if (!Array.isArray(entrenadores)) {
            selectEntrenador.innerHTML = '<option value="">Error: Formato de datos incorrecto</option>';
            return;
        }

        if (entrenadores.length > 0) {
            selectEntrenador.innerHTML = '<option value="">Seleccione un entrenador...</option>';
            entrenadores.forEach(ent => {
                const option = document.createElement('option');
                option.value = ent.id_entrenador;
                const nombreCompleto = `${ent.nombres || ''} ${ent.apellidos || ''}`.trim();
                const cedula = ent.cedula || '';
                option.textContent = cedula ? `${nombreCompleto} (${cedula})` : nombreCompleto;
                selectEntrenador.appendChild(option);
            });
        } else {
            selectEntrenador.innerHTML = '<option value="">No hay entrenadores registrados</option>';
        }

    } catch (err) {
        console.error("Error cargando entrenadores:", err);
        selectEntrenador.innerHTML = `<option value="">Error al cargar entrenadores</option>`;
    }

    if (idGrupo) {
        btnGuardar.innerHTML = 'ACTUALIZAR GRUPO <i class="fas fa-sync-alt ml-2"></i>';
        const grupo = await peticionAjax(`obtenerGrupo&id=${idGrupo}`);

        if (grupo) {
            document.getElementById('nombre').value = grupo.nombre;
            document.getElementById('descripcion').value = grupo.descripcion || '';
            setTimeout(() => {
                selectEntrenador.value = grupo.id_entrenador || '';
            }, 100);
        }
    } else {
        btnGuardar.innerHTML = 'GUARDAR GRUPO <i class="fas fa-save ml-2"></i>';
    }

    if (typeof validadorTiempoReal === 'undefined') {
        window.validadorTiempoReal = new ValidadorTiempoReal();
    }
}

async function abrirModalAsignacion(idGrupo = null) {
    if (!idGrupo) {
        if (typeof UI !== 'undefined') {
            UI.advertencia('Selección requerida', 'Primero selecciona un grupo para asignar atletas.');
        }
        return;
    }

    formAsignacion.reset();
    document.getElementById('id_grupo_asignacion').value = idGrupo;

    const grupo = await peticionAjax(`obtenerGrupo&id=${idGrupo}`);
    if (grupo) {
        document.getElementById('grupo_nombre').textContent = grupo.nombre;
        document.getElementById('grupo_info').textContent = `Entrenador: ${grupo.entrenador_nombre || 'Sin asignar'}`;
    }

    modalAsignacion.classList.remove('hidden');
    setTimeout(() => {
        modalAsignacion.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    await cargarCategorias();
    await cargarAtletasDisponibles();
}

async function cargarAtletasDisponibles() {
    const container = document.getElementById('atletas-disponibles');
    container.innerHTML = '<div class="text-center py-4 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando atletas...</div>';

    try {
        const atletas = await peticionAjax('listarAtletasDisponibles');

        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                    <span class="text-sm">No hay atletas disponibles para asignar</span>
                </div>
            `;
            return;
        }

        let html = `<div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto">`;
        atletas.forEach(atleta => {
            const edad = atleta.edad || 'N/A';
            const categoria = atleta.categoria_nombre || 'Sin categoría';
            html += `
                <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium text-gray-900 dark:text-white">${escapeHtml(atleta.nombres)} ${escapeHtml(atleta.apellidos)}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-600 dark:text-emerald-400 text-xs ml-2">${escapeHtml(categoria)}</span>
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs">${escapeHtml(atleta.cedula || 'Sin cédula')}</span>
                </label>
            `;
        });
        html += `</div>`;

        container.innerHTML = html;

        document.querySelectorAll('input[name="atletas[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (window.validadorTiempoReal) {
                    window.validadorTiempoReal.validarAtletasSeleccionados();
                }
                actualizarContadorAtletas();
            });
        });

        actualizarContadorAtletas();

    } catch (error) {
        console.error('Error cargando atletas:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-600 dark:text-red-400">Error al cargar atletas disponibles</div>';
    }
}

function actualizarContadorAtletas() {
    const checkboxes = document.querySelectorAll('input[name="atletas[]"]:checked');
    const contador = document.getElementById('contador-atletas');
    if (contador) {
        contador.textContent = `${checkboxes.length} seleccionados`;
    }
}

async function filtrarAtletasPorCategoria() {
    const idCategoria = document.getElementById('filtroCategoria')?.value;
    
    if (!idCategoria) {
        await cargarAtletasDisponibles();
        return;
    }
    
    const container = document.getElementById('atletas-disponibles');
    container.innerHTML = '<div class="text-center py-4 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando atletas...</div>';
    
    try {
        const atletas = await peticionAjax(`listarAtletasPorCategoria&id_categoria=${idCategoria}`);
        
        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                    <span class="text-sm">No hay atletas disponibles en esta categoría</span>
                </div>
            `;
            return;
        }
        
        let html = `<div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto">`;
        atletas.forEach(atleta => {
            const edad = atleta.edad || 'N/A';
            const categoria = atleta.categoria_nombre || 'Sin categoría';
            html += `
                <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium text-gray-900 dark:text-white">${escapeHtml(atleta.nombres)} ${escapeHtml(atleta.apellidos)}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-600 dark:text-emerald-400 text-xs ml-2">${escapeHtml(categoria)}</span>
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs">${escapeHtml(atleta.cedula || 'Sin cédula')}</span>
                </label>
            `;
        });
        html += `</div>`;
        
        container.innerHTML = html;
        
        document.querySelectorAll('input[name="atletas[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (window.validadorTiempoReal) {
                    window.validadorTiempoReal.validarAtletasSeleccionados();
                }
                actualizarContadorAtletas();
            });
        });
        
        actualizarContadorAtletas();
        
    } catch (error) {
        console.error('Error filtrando atletas:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-600 dark:text-red-400">Error al filtrar atletas</div>';
    }
}

async function filtrarAtletasPorEdad() {
    const edadMin = document.getElementById('edad_min')?.value;
    const edadMax = document.getElementById('edad_max')?.value;

    if (!edadMin || !edadMax) {
        await cargarAtletasDisponibles();
        return;
    }

    if (parseInt(edadMin) > parseInt(edadMax)) {
        if (typeof UI !== 'undefined') {
            UI.advertencia('Rango inválido', 'La edad mínima no puede ser mayor que la máxima.');
        }
        return;
    }

    const container = document.getElementById('atletas-disponibles');
    container.innerHTML = '<div class="text-center py-4 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Filtrando atletas...</div>';

    try {
        const atletas = await peticionAjax(`listarAtletasPorEdad&edad_min=${edadMin}&edad_max=${edadMax}`);

        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search text-4xl mb-3 block opacity-30"></i>
                    <span class="text-sm">No hay atletas en este rango de edad</span>
                </div>
            `;
            return;
        }

        let html = `<div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto">`;
        atletas.forEach(atleta => {
            const edad = atleta.edad || 'N/A';
            const categoria = atleta.categoria_nombre || 'Sin categoría';
            html += `
                <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium text-gray-900 dark:text-white">${escapeHtml(atleta.nombres)} ${escapeHtml(atleta.apellidos)}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-600 dark:text-emerald-400 text-xs ml-2">${escapeHtml(categoria)}</span>
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs">${escapeHtml(atleta.cedula || 'Sin cédula')}</span>
                </label>
            `;
        });
        html += `</div>`;

        container.innerHTML = html;

        document.querySelectorAll('input[name="atletas[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (window.validadorTiempoReal) {
                    window.validadorTiempoReal.validarAtletasSeleccionados();
                }
                actualizarContadorAtletas();
            });
        });

        actualizarContadorAtletas();

    } catch (error) {
        console.error('Error filtrando atletas:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-600 dark:text-red-400">Error al filtrar atletas</div>';
    }
}

function limpiarFiltros() {
    document.getElementById('filtroCategoria').value = '';
    document.getElementById('edad_min').value = '';
    document.getElementById('edad_max').value = '';
    cargarAtletasDisponibles();
}

let grupoActualVer = null;

async function abrirModalVerGrupo(idGrupo) {
    if (!idGrupo) {
        if (typeof UI !== 'undefined') {
            UI.advertencia('Selección requerida', 'Primero selecciona un grupo para ver sus detalles.');
        }
        return;
    }

    const contenido = document.getElementById('detalleGrupoContenido');
    
    contenido.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
            <p class="text-gray-500 dark:text-gray-400 mt-3 text-sm">Cargando detalles del grupo...</p>
        </div>
    `;

    modalVerGrupo.classList.remove('hidden');
    setTimeout(() => {
        modalVerGrupo.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    try {
        const grupo = await peticionAjax(`obtenerGrupo&id=${idGrupo}`);
        
        if (!grupo) {
            contenido.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">No se pudo cargar la información del grupo</p>
                </div>
            `;
            return;
        }

        const atletas = await peticionAjax(`listarAtletasPorGrupo&id_grupo=${idGrupo}`);
        const atletasArray = Array.isArray(atletas) ? atletas : [];
        
        grupoActualVer = grupo;
        renderizarDetalleGrupo(grupo, atletasArray);

    } catch (error) {
        console.error('Error cargando detalles del grupo:', error);
        contenido.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-exclamation-circle text-4xl text-red-400 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">Error al cargar los detalles del grupo</p>
            </div>
        `;
    }
}

function renderizarDetalleGrupo(grupo, atletas) {
    const contenido = document.getElementById('detalleGrupoContenido');

    const badgeEstado = grupo.activo == 1 
        ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">ACTIVO</span>`
        : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20">ARCHIVADO</span>`;

    const tieneAtletas = atletas && Array.isArray(atletas) && atletas.length > 0;

    let atletasHtml = '';
    if (tieneAtletas) {
        atletasHtml = `
            <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                ${atletas.map(atleta => `
                    <div class="flex items-center justify-between p-3 bg-gray-100 dark:bg-white/5 rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="text-gray-900 dark:text-white font-medium text-sm">${escapeHtml(atleta.nombres || '')} ${escapeHtml(atleta.apellidos || '')}</p>
                                <div class="flex gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>${atleta.edad || 'N/A'} años</span>
                                    <span class="text-emerald-600 dark:text-emerald-400">${escapeHtml(atleta.categoria_nombre || 'Sin categoría')}</span>
                                    <span class="text-gray-500 dark:text-gray-400">${escapeHtml(atleta.cedula || 'Sin cédula')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        atletasHtml = `
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                <p class="text-sm">No hay atletas asignados a este grupo</p>
            </div>
        `;
    }

    contenido.innerHTML = `
        <div class="text-center mb-8">
            <div class="w-28 h-28 rounded-full mx-auto mb-4 bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">${escapeHtml(grupo.nombre)}</h2>
            <div class="flex justify-center gap-2 mt-3 flex-wrap">
                ${badgeEstado}
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                    ${tieneAtletas ? atletas.length : 0} atletas
                </span>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-3">
                <i class="fas fa-info-circle mr-2"></i>INFORMACIÓN DEL GRUPO
            </p>
            <div class="grid grid-cols-2 gap-3 text-left bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                <div>
                    <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Entrenador</p>
                    <p class="text-gray-900 dark:text-white font-medium">${escapeHtml(grupo.entrenador_nombre || 'Sin asignar')}</p>
                    ${grupo.entrenador_cedula ? `<p class="text-gray-500 dark:text-gray-400 text-xs">${escapeHtml(grupo.entrenador_cedula)}</p>` : ''}
                </div>
                <div>
                    <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Estado</p>
                    <p class="${grupo.activo == 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'}">${grupo.activo == 1 ? 'Activo' : 'Archivado'}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Descripción</p>
                    <p class="text-gray-700 dark:text-gray-300 text-sm">${escapeHtml(grupo.descripcion) || 'Sin descripción registrada'}</p>
                </div>
            </div>
        </div>

        <div>
            <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-3">
                <i class="fas fa-user-check mr-2"></i>ATLETAS ASIGNADOS (${tieneAtletas ? atletas.length : 0})
            </p>
            <div class="bg-gray-100 dark:bg-black/20 p-4 rounded-2xl border border-gray-200 dark:border-white/5">
                ${atletasHtml}
            </div>
        </div>

        <div class="mt-6 flex gap-3 pt-4 border-t border-gray-200 dark:border-white/5">
            <button onclick="cerrarModalVerGrupo()" class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-400 py-3 rounded-xl font-bold transition">
                <i class="fas fa-times mr-2"></i> CERRAR
            </button>
        </div>
    `;
}

function abrirModalVerGrupoDesdeAsignacion() {
    const idGrupo = document.getElementById('id_grupo_asignacion').value;
    if (idGrupo) {
        cerrarModalAsignacion();
        setTimeout(() => {
            abrirModalVerGrupo(idGrupo);
        }, 300);
    }
}

async function cargarTablaGrupos() {
    const tbody = document.getElementById('listaGrupos');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando grupos...</span></td></tr>`;

    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const grupos = await peticionAjax(`listarGrupos&estado=${filtroEstado}`);

    if (!grupos || grupos.length === 0) {
        gruposData = [];
        actualizarInfoYPieTabla(0, 0);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-layer-group text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay grupos registrados en este estado</span>
                </td>
            </tr>
        `;
        return;
    }

    gruposData = grupos;
    tablaPagina = 1; // Reiniciar a la primera página tras recargar
    renderTablaGrupos();
}

function renderTablaGrupos() {
    const tbody = document.getElementById('listaGrupos');
    if (!tbody) return;

    let datos = gruposData.slice();

    // 1. Filtrado de búsqueda
    if (tablaFiltro) {
        datos = datos.filter(g => {
            const busqueda = `${g.nombre} ${g.descripcion} ${g.entrenador_nombre || ''}`.toLowerCase();
            return busqueda.includes(tablaFiltro);
        });
    }

    // 2. Ordenamiento por columnas
    if (tablaSortCol) {
        const col = tablaSortCol;
        const dir = tablaSortDir === 'asc' ? 1 : -1;
        datos.sort((a, b) => {
            let va = '', vb = '';
            if (col === 'nombre') { va = a.nombre || ''; vb = b.nombre || ''; }
            else if (col === 'descripcion') { va = a.descripcion || ''; vb = b.descripcion || ''; }
            else if (col === 'entrenador') { va = a.entrenador_nombre || ''; vb = b.entrenador_nombre || ''; }
            else if (col === 'atletas') { return ((a.total_atletas || 0) - (b.total_atletas || 0)) * dir; }
            else if (col === 'estado') { return ((a.activo || 0) - (b.activo || 0)) * dir; }
            return va.localeCompare(vb, 'es') * dir;
        });
    }

    // 3. Cálculos de Paginación
    const total = datos.length;
    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;

    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    actualizarInfoYPieTabla(total, totalPaginas);

    // 4. Renderizado HTML de los 10 registros de la página actual
    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
        return;
    }

    let html = '';
    pagina.forEach(g => {
        const entrenadorText = g.entrenador_nombre ? `${escapeHtml(g.entrenador_nombre)} <span class="text-[10px] text-gray-500 dark:text-gray-400">(${escapeHtml(g.entrenador_cedula)})</span>` : '<span class="text-xs text-gray-500 dark:text-gray-400 italic">Sin entrenador asignado</span>';
        
        let botonAccion = '';
        if (g.activo == 1) {
            botonAccion = `
                <button onclick="eliminarGrupo(${g.id_grupo})" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition duration-200" title="Archivar Grupo">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>
            `;
        } else {
            botonAccion = `
                <button onclick="reactivarGrupo(${g.id_grupo})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition duration-200" title="Reactivar Grupo">
                    <i class="fas fa-check-circle text-base"></i>
                </button>
            `;
        }

        html += `
            <tr class="grupo-row hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200 border-b border-gray-200 dark:border-gray-800/50">
                <td class="p-4 font-medium text-gray-900 dark:text-white">${escapeHtml(g.nombre)}</td>
                <td class="p-4 text-gray-600 dark:text-gray-300 text-xs max-w-xs truncate">${escapeHtml(g.descripcion) || '—'}</td>
                <td class="p-4 text-gray-700 dark:text-gray-300">${entrenadorText}</td>
                <td class="p-4 text-center">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full ${g.total_atletas > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20'}">
                        ${g.total_atletas || 0}
                    </span>
                </td>
                <td class="p-4">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full ${g.activo == 1 ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20' : 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20'} uppercase tracking-wide">
                        ${g.activo == 1 ? 'Activo' : 'Archivado'}
                    </span>
                </td>
                <td class="p-4 text-right space-x-1">
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="abrirModalVerGrupo(${g.id_grupo})" class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 p-2 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-500/10 transition duration-200" title="Ver Detalles">
                        <i class="fas fa-eye text-base"></i>
                    </button>
                    <button onclick="abrirModalGrupo(${g.id_grupo})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition duration-200" title="Editar Grupo">
                        <i class="fas fa-edit text-base"></i>
                    </button>
                    <button onclick="abrirModalAsignacion(${g.id_grupo})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition duration-200" title="Asignar Atletas">
                        <i class="fas fa-user-plus text-base"></i>
                    </button>
                    ${botonAccion}
                    ` : '<span class="text-gray-500 dark:text-gray-400 text-xs">Solo lectura</span>'}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function actualizarInfoYPieTabla(total, totalPaginas) {
    const contador = document.getElementById('contadorGrupos');
    const infoTabla = document.getElementById('infoTabla');
    const pieTabla = document.getElementById('pieTabla');

    if (contador) contador.textContent = `(${gruposData.length})`;
    
    if (infoTabla) {
        infoTabla.textContent = total === 0 ? '' : `Mostrando ${(tablaPagina - 1) * tablaPorPagina + 1}–${Math.min(tablaPagina * tablaPorPagina, total)} de ${total}`;
    }

    if (!pieTabla) return;

    if (totalPaginas <= 1) {
        pieTabla.innerHTML = '';
        return;
    }

    let html = `<span class="text-xs text-gray-500 dark:text-gray-400">Página ${tablaPagina} de ${totalPaginas}</span><div class="flex gap-1">`;
    const btnClass = 'px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition';
    const btnActivo = 'bg-indigo-600 text-white';
    const btnInactivo = 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-700';

    if (tablaPagina > 1) {
        html += `<button onclick="tablaPagina--; renderTablaGrupos()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-left"></i></button>`;
    }

    const maxVisible = 5;
    let start = Math.max(1, tablaPagina - Math.floor(maxVisible / 2));
    let end = Math.min(totalPaginas, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);

    for (let i = start; i <= end; i++) {
        if (i === tablaPagina) {
            html += `<button class="${btnClass} ${btnActivo}">${i}</button>`;
        } else {
            html += `<button onclick="tablaPagina=${i}; renderTablaGrupos()" class="${btnClass} ${btnInactivo}">${i}</button>`;
        }
    }

    if (tablaPagina < totalPaginas) {
        html += `<button onclick="tablaPagina++; renderTablaGrupos()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-right"></i></button>`;
    }

    html += '</div>';
    pieTabla.innerHTML = html;
}

// Listener para la Búsqueda Instantánea con Paginación
const inputBusqueda = document.getElementById('busquedaNombre');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1; // Resetea a la página 1 al filtrar
        renderTablaGrupos();
    });
}

// Oyente de clic en los encabezados para ordenar columnas
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
        renderTablaGrupos();
    });
});

async function eliminarGrupo(id_grupo) {
    if (!confirm("¿Está seguro de archivar este grupo de entrenamiento?")) return;

    let datosDelete = new FormData();
    datosDelete.append('accion', 'eliminar');
    datosDelete.append('id_grupo', id_grupo);

    const resultado = await peticionAjax('eliminar', datosDelete);
    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') {
            UI.exito('Archivado', 'El grupo ha sido desactivado.');
        }
        cargarTablaGrupos();
    } else {
        if (typeof UI !== 'undefined') {
            UI.error('Error', 'No se pudo desactivar el registro.');
        }
    }
}

async function reactivarGrupo(id_grupo) {
    if (!confirm("¿Desea reactivar este grupo de entrenamiento?")) return;

    let datosReactivar = new FormData();
    datosReactivar.append('accion', 'reactivar');
    datosReactivar.append('id_grupo', id_grupo);

    const resultado = await peticionAjax('reactivar', datosReactivar);
    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') {
            UI.exito('Reactivado', 'El grupo vuelve a estar activo.');
        }
        cargarTablaGrupos();
    } else {
        if (typeof UI !== 'undefined') {
            UI.error('Error', 'No se pudo reactivar el grupo.');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.validadorTiempoReal = new ValidadorTiempoReal();

    cargarTablaGrupos();

    formGrupo.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!window.validadorTiempoReal.validarFormulario(formGrupo)) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Datos Inválidos', 'Por favor, corrige los campos marcados en rojo.');
            }
            const primerError = document.querySelector('.is-invalid');
            if (primerError) {
                primerError.focus();
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formGrupo);
        datosForm.append('accion', 'guardarGrupo');

        const resultado = await peticionAjax('guardarGrupo', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') {
                    UI.exito('Transacción Exitosa', resultado.message);
                }
                cerrarModalGrupo();
                cargarTablaGrupos();
            } else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') {
                    UI.advertencia('Validación', msjErrores);
                }
            } else {
                if (typeof UI !== 'undefined') {
                    UI.error('Error', resultado.message);
                }
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });

    formAsignacion.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!window.validadorTiempoReal.validarAtletasSeleccionados()) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Selección requerida', 'Debe seleccionar al menos un atleta para asignar.');
            }
            return;
        }

        const textoOriginal = btnAsignar.innerHTML;
        btnAsignar.disabled = true;
        btnAsignar.innerHTML = 'Asignando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formAsignacion);
        datosForm.append('accion', 'asignarAtletas');

        const resultado = await peticionAjax('asignarAtletas', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') {
                    UI.exito('Asignación Exitosa', resultado.message);
                }
                cerrarModalAsignacion();
                cargarTablaGrupos();
            } else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') {
                    UI.advertencia('Validación', msjErrores);
                }
            } else {
                if (typeof UI !== 'undefined') {
                    UI.error('Error', resultado.message);
                }
            }
        }

        btnAsignar.disabled = false;
        btnAsignar.innerHTML = textoOriginal;
    });

    const filtroEstado = document.getElementById('filtroEstado');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', cargarTablaGrupos);
    }

    const btnFiltrarEdad = document.getElementById('btnFiltrarEdad');
    if (btnFiltrarEdad) {
        btnFiltrarEdad.addEventListener('click', filtrarAtletasPorEdad);
    }

    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            document.getElementById('edad_min').value = '';
            document.getElementById('edad_max').value = '';
            cargarAtletasDisponibles();
        });
    }
});