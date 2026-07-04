const modalGrupo = document.getElementById('modalGrupo');
const modalAsignacion = document.getElementById('modalAsignacion');
const formGrupo = document.getElementById('formGrupo');
const formAsignacion = document.getElementById('formAsignacion');
const btnGuardar = document.getElementById('btnGuardar');
const btnAsignar = document.getElementById('btnAsignar');

const API_URL = 'index.php?p=grupo';

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
        }
        return null;
    }
}

class ValidadorTiempoReal {
    constructor() {
        this.errores = {};
        this.reglas = {
            'nombre': {
                requerido: true,
                maxlength: 100,
                regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9\-]+$/,
                mensajes: {
                    requerido: 'El nombre del grupo es requerido',
                    maxlength: 'El nombre no puede tener más de 100 caracteres',
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
                // Manejo especial para checkboxes
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

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (!modalGrupo.classList.contains('hidden')) {
            cerrarModalGrupo();
        }
        if (!modalAsignacion.classList.contains('hidden')) {
            cerrarModalAsignacion();
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
    await cargarAtletasAsignados(idGrupo);
}

async function cargarAtletasDisponibles() {
    const container = document.getElementById('atletas-disponibles');
    container.innerHTML = '<div class="text-center py-4 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando atletas...</div>';

    try {
        const atletas = await peticionAjax('listarAtletasDisponibles');

        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
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
                <label class="flex items-center p-2 hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium">${atleta.nombres} ${atleta.apellidos}</span>
                        <span class="text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-400 text-xs ml-2">${categoria}</span>
                    </span>
                    <span class="text-gray-500 text-xs">${atleta.cedula || 'Sin cédula'}</span>
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
        container.innerHTML = '<div class="text-center py-4 text-red-400">Error al cargar atletas disponibles</div>';
    }
}

function actualizarContadorAtletas() {
    const checkboxes = document.querySelectorAll('input[name="atletas[]"]:checked');
    const contador = document.getElementById('contador-atletas');
    if (contador) {
        contador.textContent = `${checkboxes.length} seleccionados`;
    }
}

async function cargarAtletasAsignados(idGrupo) {
    console.log("=== CARGANDO ATLETAS ASIGNADOS ===");
    console.log("ID Grupo recibido:", idGrupo);
    console.log("Tipo de dato:", typeof idGrupo);
  
    const container = document.getElementById('atletas-asignados');
    container.innerHTML = '<div class="text-center py-4 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando atletas asignados...</div>';

    try {
        const atletas = await peticionAjax(`listarAtletasPorGrupo&id_grupo=${idGrupo}`);

        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-user-plus text-4xl mb-3 block opacity-30"></i>
                    <span class="text-sm">No hay atletas asignados a este grupo</span>
                </div>
            `;
            return;
        }

        let html = `<div class="space-y-2 max-h-60 overflow-y-auto">`;
        atletas.forEach(atleta => {
            const edad = atleta.edad || 'N/A';
            const categoria = atleta.categoria_nombre || 'Sin categoría';
            html += `
                <div class="flex items-center justify-between p-2 bg-white/5 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-user-circle text-indigo-400 text-lg mr-3"></i>
                        <div>
                            <span class="font-medium text-sm">${atleta.nombres} ${atleta.apellidos}</span>
                            <span class="text-gray-400 text-xs ml-2">${edad} años</span>
                            <span class="text-emerald-400 text-xs ml-2">${categoria}</span>
                            <div class="text-gray-500 text-[10px]">${atleta.cedula || 'Sin cédula'}</div>
                        </div>
                    </div>
                    <button onclick="desasignarAtleta(${atleta.id_atleta})" 
                            class="text-red-400 hover:text-red-300 p-1 rounded hover:bg-red-500/10 transition text-sm"
                            title="Desasignar atleta">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });
        html += `</div>`;

        container.innerHTML = html;

    } catch (error) {
        console.error('Error cargando atletas asignados:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-400">Error al cargar atletas asignados</div>';
    }
}

async function desasignarAtleta(id_atleta) {
    if (!confirm('¿Está seguro de desasignar este atleta del grupo?')) return;

    const formData = new FormData();
    formData.append('accion', 'desasignarAtleta');
    formData.append('id_atleta', id_atleta);

    const resultado = await peticionAjax('desasignarAtleta', formData);

    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') {
            UI.exito('Desasignado', 'El atleta ha sido desasignado del grupo.');
        }
        const idGrupo = document.getElementById('id_grupo_asignacion').value;
        await cargarAtletasAsignados(idGrupo);
        await cargarAtletasDisponibles();
        cargarTablaGrupos();
    } else {
        if (typeof UI !== 'undefined') {
            UI.error('Error', resultado?.message || 'No se pudo desasignar el atleta.');
        }
    }
}

async function filtrarAtletasPorCategoria() {
    const idCategoria = document.getElementById('filtroCategoria')?.value;
    
    if (!idCategoria) {
        await cargarAtletasDisponibles();
        return;
    }
    
    const container = document.getElementById('atletas-disponibles');
    container.innerHTML = '<div class="text-center py-4 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando atletas...</div>';
    
    try {
        const atletas = await peticionAjax(`listarAtletasPorCategoria&id_categoria=${idCategoria}`);
        
        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
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
                <label class="flex items-center p-2 hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium">${atleta.nombres} ${atleta.apellidos}</span>
                        <span class="text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-400 text-xs ml-2">${categoria}</span>
                    </span>
                    <span class="text-gray-500 text-xs">${atleta.cedula || 'Sin cédula'}</span>
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
        container.innerHTML = '<div class="text-center py-4 text-red-400">Error al filtrar atletas</div>';
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
    container.innerHTML = '<div class="text-center py-4 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Filtrando atletas...</div>';

    try {
        const atletas = await peticionAjax(`listarAtletasPorEdad&edad_min=${edadMin}&edad_max=${edadMax}`);

        if (!atletas || atletas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
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
                <label class="flex items-center p-2 hover:bg-white/5 rounded-lg cursor-pointer transition">
                    <input type="checkbox" name="atletas[]" value="${atleta.id_atleta}" 
                           class="form-checkbox h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                    <span class="ml-3 text-sm flex-1">
                        <span class="font-medium">${atleta.nombres} ${atleta.apellidos}</span>
                        <span class="text-gray-400 text-xs ml-2">${edad} años</span>
                        <span class="text-emerald-400 text-xs ml-2">${categoria}</span>
                    </span>
                    <span class="text-gray-500 text-xs">${atleta.cedula || 'Sin cédula'}</span>
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
        container.innerHTML = '<div class="text-center py-4 text-red-400">Error al filtrar atletas</div>';
    }
}

function limpiarFiltros() {
    document.getElementById('filtroCategoria').value = '';
    document.getElementById('edad_min').value = '';
    document.getElementById('edad_max').value = '';
    cargarAtletasDisponibles();
}

async function cargarTablaGrupos() {
    const tbody = document.getElementById('listaGrupos');
    tbody.innerHTML = `<tr><td colspan="6" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando grupos...</span></td></tr>`;

    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const grupos = await peticionAjax(`listarGrupos&estado=${filtroEstado}`);

    if (!grupos || grupos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center p-12 text-gray-500">
                    <i class="fas fa-layer-group text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay grupos registrados en este estado</span>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    grupos.forEach(g => {
        const entrenadorText = g.entrenador_nombre ? `${g.entrenador_nombre} <span class="text-[10px] text-gray-500">(${g.entrenador_cedula})</span>` : '<span class="text-xs text-gray-600 italic">Sin entrenador asignado</span>';
        const busqueda = `${g.nombre} ${g.descripcion} ${g.entrenador_nombre || ''}`.toLowerCase();
        
        let botonAccion = '';
        if (g.activo == 1) {
            botonAccion = `
                <button onclick="eliminarGrupo(${g.id_grupo})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Archivar Grupo">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>
            `;
        } else {
            botonAccion = `
                <button onclick="reactivarGrupo(${g.id_grupo})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Reactivar Grupo">
                    <i class="fas fa-check-circle text-base"></i>
                </button>
            `;
        }

        html += `
            <tr class="grupo-row hover:bg-white/5 transition-colors duration-200" data-busqueda="${busqueda}">
                <td class="p-4 font-medium text-white">${g.nombre}</td>
                <td class="p-4 text-gray-300 text-xs max-w-xs truncate">${g.descripcion || '—'}</td>
                <td class="p-4 text-gray-300">${entrenadorText}</td>
                <td class="p-4 text-center">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full ${g.total_atletas > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'}">
                        ${g.total_atletas || 0}
                    </span>
                </td>
                <td class="p-4">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full ${g.activo == 1 ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'} uppercase tracking-wide">
                        ${g.activo == 1 ? 'Activo' : 'Archivado'}
                    </span>
                </td>
                <td class="p-4 text-right space-x-1">
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="abrirModalGrupo(${g.id_grupo})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Grupo">
                        <i class="fas fa-edit text-base"></i>
                    </button>
                    <button onclick="abrirModalAsignacion(${g.id_grupo})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Asignar Atletas">
                        <i class="fas fa-users text-base"></i>
                    </button>
                    ${botonAccion}
                    ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    const contador = document.getElementById('contadorGrupos');
    if (contador) {
        contador.textContent = `(${grupos.length})`;
    }
}

const inputBusqueda = document.getElementById('busquedaNombre');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.grupo-row');
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda');
            fila.style.display = textoFila.includes(valor) ? '' : 'none';
        });
    });
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