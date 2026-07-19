const modalDrills = document.getElementById('modalDrills');
const formDrills = document.getElementById('formDrills');
const btnGuardar = document.getElementById('btnGuardar');

const API_URL = 'index.php?p=drills'; 

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

async function abrirModalDrills(id_drill = null) {
    formDrills.reset(); 
    try { Validador.limpiarEstilos(formDrills); } catch(e) {}
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_drill');
    const modalTitulo = document.getElementById('modalTitulo');

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
            
            document.getElementById('personalizado').checked = parseInt(drill.personalizado) === 1;
            document.getElementById('activo').checked = parseInt(drill.activo) === 1;
            
            if (document.getElementById('id_usuario_creador')) {
                document.getElementById('id_usuario_creador').value = drill.id_usuario_creador || '1';
            }
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Entrenamiento';
        document.getElementById('personalizado').checked = false;
        document.getElementById('activo').checked = true;
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalDrills.classList.remove('hidden');
    setTimeout(() => {
        modalDrills.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaDrills() {
    const tbody = document.getElementById('listaDrills');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="8" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const drills = await peticionAjax('listarDrills');

    if (!drills || drills.length === 0) {
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

    const totalDrills = document.getElementById('totalDrills');
    if (totalDrills) {
        totalDrills.textContent = `${drills.length} Registrados`;
    }

    tbody.innerHTML = drills.map(ent => {
        const badgePersonalizado = parseInt(ent.personalizado) === 1 
            ? `<span class="px-2 py-1 text-[10px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-md border border-purple-200 dark:border-purple-500/20">SÍ</span>`
            : `<span class="px-2 py-1 text-[10px] font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-md">NO</span>`;

        const badgeActivo = parseInt(ent.activo) === 1 
            ? `<span class="px-2 py-1 text-[10px] font-bold bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 rounded-md border border-green-200 dark:border-green-500/20">ACTIVO</span>`
            : `<span class="px-2 py-1 text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-md border border-red-200 dark:border-red-500/20">INACTIVO</span>`;

        const badgeDificultad = `<span class="badge-dificultad dificultad-${ent.dificultad}">${ent.dificultad}</span>`;

        return `
            <tr class="drills-row border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${ent.nombre} ${ent.estilo} ${ent.categoria}">
                <td class="p-4 font-medium text-gray-900 dark:text-white max-w-[180px] truncate" title="${escapeHtml(ent.nombre)}">${escapeHtml(ent.nombre)}</td>
                <td class="p-4 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(ent.estilo)}</td>
                <td class="p-4 text-gray-600 dark:text-gray-400 text-xs">${escapeHtml(ent.categoria)}</td>
                <td class="p-4">${badgeDificultad}</td>
                <td class="p-4 text-gray-600 dark:text-gray-400 text-xs">${escapeHtml(ent.material_requerido)}</td>
                <td class="p-4 text-center">${badgePersonalizado}</td>
                <td class="p-4 text-center">${badgeActivo}</td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="verDetalleDrill(${ent.id_drill})" class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all" title="Ver Perfil">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="abrirModalDrills(${ent.id_drill})" class="w-9 h-9 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="eliminarDrills(${ent.id_drill})" class="w-9 h-9 rounded-xl flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white transition-all" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function verDetalleDrill(id) {
    console.log('Ver detalle del drill ID:', id);
    
    const drill = await peticionAjax(`obtenerDrills&id=${id}`);
    console.log('Datos recibidos:', drill);
    
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
    } else {
        console.error('No se encontró el elemento detalleDrillContenido');
    }
    
    const modalVer = document.getElementById('modalVerDrill');
    if (modalVer) {
        modalVer.classList.remove('hidden');
        setTimeout(() => {
            const child = modalVer.firstElementChild;
            if (child) {
                child.classList.remove('scale-95', 'opacity-0');
            }
        }, 10);
    } else {
        console.error('No se encontró el elemento modalVerDrill');
    }
}

function cerrarModalVerDrill() {
    const modalVer = document.getElementById('modalVerDrill');
    if (!modalVer) return;
    
    const child = modalVer.firstElementChild;
    if (child) {
        child.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        modalVer.classList.add('hidden');
    }, 200);
}

function cambiarTabDrill(tab) {
    document.querySelectorAll('#formDrills .tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('#formDrills .tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector(`#formDrills .tab-btn[data-tab="${tab}"]`);
    const content = document.getElementById(`tab-${tab}`);
    if (btn) btn.classList.add('active');
    if (content) content.classList.add('active');
}

const inputBusqueda = document.getElementById('busquedaID');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.drills-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda') || '';
            fila.style.display = textoFila.toLowerCase().includes(valor) ? '' : 'none';
        });
    });
}

function setupValidacionTiempoReal() {
    const campos = [
        { id: 'nombre', reglas: 'requerido|letras', nombre: 'Nombre', min: 2, max: 100 },
        { id: 'enfoque_tecnico', reglas: 'requerido|texto', nombre: 'Enfoque Técnico', min: 5, max: 100 },
        { id: 'descripcion', reglas: 'requerido|texto', nombre: 'Descripción', min: 10, max: 500 },
        { id: 'instrucciones', reglas: 'requerido|texto', nombre: 'Instrucciones', min: 5, max: 1000 },
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
    console.log('DOM cargado - Inicializando drills...');
    
    try { 
        if (typeof Validador !== 'undefined') {
            Validador.vincularTiempoReal(formDrills);
        }
        setupValidacionTiempoReal();
    } catch(e){ console.warn('Error en validaciones:', e); }
    
    cargarTablaDrills();

    formDrills.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        const { hasError, errores } = validarFormularioCompleto(this);
        
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

        if (typeof Validador !== 'undefined' && typeof Validador.validarFormulario === 'function') {
            const erroresJS = Validador.validarFormulario(formDrills);
            if (erroresJS) {
                if (typeof UI !== 'undefined') {
                    UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
                }
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
                if (typeof UI !== 'undefined') {
                    UI.exito('Transacción Exitosa', resultado.message);
                } else {
                    alert('Éxito: ' + resultado.message);
                }
                cerrarModalDrills();
                cargarTablaDrills();
            } 
            else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') {
                    UI.advertencia('Validación del Servidor', msjErrores);
                } else {
                    alert('Errores: ' + msjErrores);
                }
            } 
            else {
                if (typeof UI !== 'undefined') {
                    UI.error('Error de Sistema', resultado.message);
                } else {
                    alert('Error: ' + resultado.message);
                }
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

async function eliminarDrills(id_drill) {
    const confirmacion = confirm("¿Está seguro de eliminar este entrenamiento? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_drill', id_drill);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') {
                UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            }
            cargarTablaDrills();
        } else {
            if (typeof UI !== 'undefined') {
                UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
            }
        }
    }
}