const API_URL = 'index.php?p=entrenador'; 

const modalEntrenador = document.getElementById('modalEntrenador');
const modalVer = document.getElementById('modalVerEntrenador');
const formEntrenador = document.getElementById('formEntrenador');
const btnGuardar = document.getElementById('btnGuardar');
const detalleContenido = document.getElementById('detalleContenido');
const inputFoto = document.getElementById('foto');
const fotoPreview = document.getElementById('previsualizarFoto');
const iconoFotoDefecto = document.getElementById('iconoFotoPorDefecto'); 
const totalEntrenador = document.getElementById('totalEntrenador');

// Referencias para la tabla paginada y ordenada
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

// Estado de la tabla
let entrenadoresData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

function setupValidacionTiempoReal() {
    const campos = [
        { id: 'cedula', reglas: 'requerido|numeros', nombre: 'Cédula', min: 8, max: 8 },
        { id: 'nombres', reglas: 'requerido|letras', nombre: 'Nombres', min: 2, max: 50 },
        { id: 'apellidos', reglas: 'requerido|letras', nombre: 'Apellidos', min: 2, max: 50 },
        { id: 'fecha_nacimiento', reglas: 'requerido|mayor18', nombre: 'Fecha de Nacimiento' },
        { id: 'telefono', reglas: 'requerido|numeros', nombre: 'Teléfono', min: 11, max: 11 },
        { id: 'correo', regras: 'requerido|email', nombre: 'Correo Electrónico' },
        { id: 'direccion', reglas: 'requerido', nombre: 'Dirección', min: 5, max: 50 },
        { id: 'genero', reglas: 'requerido', nombre: 'Género' }
    ];

    campos.forEach(({ id, reglas, nombre, min, max }) => {
        const input = document.getElementById(id);
        if (!input) {
            console.warn(`Campo no encontrado: ${id}`);
            return;
        }

        let errorContainer = input.parentElement.querySelector('.error-msg');
        if (!errorContainer) {
            errorContainer = document.createElement('span');
            errorContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
            errorContainer.style.display = 'none';
            input.parentElement.appendChild(errorContainer);
        }

        input.addEventListener('input', function() {
            validarCampo(this, reglas, nombre, min, max);
        });

        input.addEventListener('blur', function() {
            validarCampo(this, reglas, nombre, min, max);
        });

        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                validarCampo(this, reglas, nombre, min, max);
            });
        }
    });
}

function validarCampo(input, reglas, nombre, min, max) {
    const valor = input.value.trim();
    let error = '';

    if (!reglas.includes('requerido') && valor === '') {
        input.classList.remove('border-red-500', 'border-2', 'border-green-500', 'border');
        const errorContainer = input.parentElement.querySelector('.error-msg');
        if (errorContainer) {
            errorContainer.textContent = '';
            errorContainer.style.display = 'none';
        }
        return true;
    }

    if (reglas.includes('requerido') && !valor) {
        error = `${nombre} es requerido`;
    } else if (valor) {
        if (reglas.includes('numeros') && !/^\d+$/.test(valor)) {
            error = `${nombre} solo debe contener números`;
        }
        if (reglas.includes('letras') && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(valor)) {
            error = `${nombre} solo debe contener letras`;
        }
        if (reglas.includes('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            error = `Ingrese un correo electrónico válido`;
        }
        if (reglas.includes('mayor18')) {
            if (!valor) {
                error = `La fecha de nacimiento es requerida`;
            } else {
                const fecha = new Date(valor);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const mes = hoy.getMonth() - fecha.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < fecha.getDate())) {
                    edad--;
                }
                if (edad < 18) {
                    error = `Debe ser mayor de 18 años`;
                }
            }
        }
  
        if (min && valor.length < min) {
            error = `${nombre} debe tener al menos ${min} caracteres`;
        }
        if (max && valor.length > max) {
            error = `${nombre} no debe exceder ${max} caracteres`;
        }
    }

    const errorContainer = input.parentElement.querySelector('.error-msg');
    if (!errorContainer) {
        const newContainer = document.createElement('span');
        newContainer.className = 'error-msg text-red-400 text-[10px] mt-1 block';
        newContainer.style.display = 'none';
        input.parentElement.appendChild(newContainer);
    }

    const finalErrorContainer = input.parentElement.querySelector('.error-msg');

    if (error) {
        input.classList.remove('border-green-500', 'border');
        input.classList.add('border-red-500', 'border-2');
        if (finalErrorContainer) {
            finalErrorContainer.textContent = error;
            finalErrorContainer.style.display = 'block';
        }
        return false;
    } else if (valor) {
        input.classList.remove('border-red-500', 'border-2');
        input.classList.add('border-green-500', 'border');
        if (finalErrorContainer) {
            finalErrorContainer.textContent = '';
            finalErrorContainer.style.display = 'none';
        }
        return true;
    } else {
        input.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
        if (finalErrorContainer) {
            finalErrorContainer.textContent = '';
            finalErrorContainer.style.display = 'none';
        }
        return true;
    }
}

function validarFormularioCompleto(form) {
    const inputs = form.querySelectorAll('input[data-validar], select[data-validar], textarea[data-validar]');
    let hasError = false;
    let primerosErrores = [];

    inputs.forEach(input => {
        const reglas = input.dataset.validar || '';
        const nombre = input.dataset.nombre || input.name || 'Campo';
        const min = parseInt(input.dataset.min) || 0;
        const max = parseInt(input.dataset.max) || 9999;
        
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

function cerrarModalEntrenador() {
    modalEntrenador.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalEntrenador.classList.add('hidden');
        const inputs = formEntrenador.querySelectorAll('input:not([type="hidden"]), select, textarea');
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
            const errorContainer = input.parentElement.querySelector('.error-msg');
            if (errorContainer) {
                errorContainer.textContent = '';
                errorContainer.style.display = 'none';
            }
        });
    }, 200);
}

function cerrarModalVer() {
    modalVer.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalVer.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (!modalEntrenador.classList.contains('hidden')) cerrarModalEntrenador();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

if (inputFoto) {
    inputFoto.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (fotoPreview) {
                    fotoPreview.src = ev.target.result;
                    fotoPreview.classList.remove('hidden');
                }
                if (iconoFotoDefecto) iconoFotoDefecto.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

async function abrirModalEntrenador(id_entrenador = null) {
    formEntrenador.reset(); 
    
    const inputs = formEntrenador.querySelectorAll('input:not([type="hidden"]), select, textarea');
    inputs.forEach(input => {
        input.classList.remove('border-red-500', 'border-green-500', 'border-2', 'border');
        const errorContainer = input.parentElement.querySelector('.error-msg');
        if (errorContainer) {
            errorContainer.textContent = '';
            errorContainer.style.display = 'none';
        }
    });
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_entrenador');
    const modalTitulo = document.getElementById('modalTitulo');
    
    if (fotoPreview) {
        fotoPreview.src = '';
        fotoPreview.classList.add('hidden');
    }
    if (iconoFotoDefecto) iconoFotoDefecto.classList.remove('hidden');

    if (id_entrenador) {
        if (inputAction) inputAction.value = 'editar';
        if (inputIdHidden) inputIdHidden.value = id_entrenador;
        if (modalTitulo) modalTitulo.textContent = 'Editar Datos del Entrenador';
        
        btnGuardar.innerHTML = 'EDITAR ENTRENADOR <i class="fas fa-sync-alt ml-2"></i>';
        
        const entrenador = await peticionAjax(`obtenerEntrenador&id=${id_entrenador}`);
        
        if (entrenador) {
            document.getElementById('cedula').value = entrenador.cedula || '';
            document.getElementById('nombres').value = entrenador.nombres || '';
            document.getElementById('apellidos').value = entrenador.apellidos || '';
            document.getElementById('fecha_nacimiento').value = entrenador.fecha_nacimiento || '';
            document.getElementById('genero').value = entrenador.genero || '';
            document.getElementById('correo').value = entrenador.correo || '';
            document.getElementById('telefono').value = entrenador.telefono || '';
            document.getElementById('direccion').value = entrenador.direccion || '';
          
            setTimeout(() => {
                const camposConDatos = ['cedula', 'nombres', 'apellidos', 'fecha_nacimiento', 'telefono', 'correo', 'direccion', 'genero'];
                camposConDatos.forEach(id => {
                    const campo = document.getElementById(id);
                    if (campo && campo.value) {
                        const reglas = campo.dataset.validar || '';
                        const nombre = campo.dataset.nombre || id;
                        const min = parseInt(campo.dataset.min) || 0;
                        const max = parseInt(campo.dataset.max) || 9999;
                        validarCampo(campo, reglas, nombre, min, max);
                    }
                });
            }, 100);
            
            if (entrenador.foto && fotoPreview) {
                fotoPreview.src = entrenador.foto;
                fotoPreview.classList.remove('hidden');
                if (iconoFotoDefecto) iconoFotoDefecto.classList.add('hidden');
            }
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Entrenador';
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalEntrenador.classList.remove('hidden');
    setTimeout(() => {
        modalEntrenador.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function verDetalle(id) {
    const entrenador = await peticionAjax(`obtenerEntrenador&id=${id}`);
    if (!entrenador) return;

    document.getElementById("verNombreCompleto").innerText = `${entrenador.nombres} ${entrenador.apellidos}`;
    document.getElementById("verCedula").innerText = entrenador.cedula;
    document.getElementById("verGenero").innerText = entrenador.genero === 'M' ? 'Masculino' : 'Femenino';
    document.getElementById("verTelefono").innerText = entrenador.telefono;
    document.getElementById("verFechaNac").innerText = entrenador.fecha_nacimiento || '—';
    document.getElementById("verCorreo").innerText = entrenador.correo || '—';
    document.getElementById("verDireccion").innerText = entrenador.direccion || '—';

    const imgVer = document.getElementById("verFoto");
    const iconoVerDefecto = document.getElementById("verIconoPorDefecto");

    if (entrenador.foto && imgVer) {
        imgVer.src = entrenador.foto;
        imgVer.classList.remove("hidden");
        if (iconoVerDefecto) iconoVerDefecto.classList.add("hidden");
    } else if (imgVer) {
        imgVer.src = "";
        imgVer.classList.add("hidden");
        if (iconoVerDefecto) iconoVerDefecto.classList.remove("hidden");
    }

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaEntrenador() {
    const tbody = document.getElementById('listaEntrenador');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const entrenadores = await peticionAjax('listarEntrenador');

    if (!entrenadores || entrenadores.length === 0) {
        entrenadoresData = [];
        if (totalEntrenador) totalEntrenador.textContent = '0 Registrados';
        if (infoTabla) infoTabla.textContent = '';
        if (pieTabla) pieTabla.innerHTML = '';
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users-slash text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay entrenadores registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    entrenadoresData = entrenadores;
    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;
    renderTablaEntrenador();
}

function renderTablaEntrenador() {
    const tbody = document.getElementById('listaEntrenador');
    if (!tbody) return;

    let datos = entrenadoresData.slice();

    // 1. Filtrar por búsqueda
    if (tablaFiltro) {
        const q = tablaFiltro;
        datos = datos.filter(ent =>
            (ent.nombres + ' ' + ent.apellidos + ' ' + ent.cedula + ' ' + (ent.correo || '') + ' ' + ent.telefono + ' ' + ent.direccion).toLowerCase().includes(q)
        );
    }

    // 2. Ordenar datos
    if (tablaSortCol) {
        const col = tablaSortCol;
        const dir = tablaSortDir === 'asc' ? 1 : -1;
        datos.sort((a, b) => {
            let va = '', vb = '';
            if (col === 'nombre') { va = (a.nombres || '') + ' ' + (a.apellidos || ''); vb = (b.nombres || '') + ' ' + (b.apellidos || ''); }
            else if (col === 'cedula') { va = a.cedula || ''; vb = b.cedula || ''; }
            else if (col === 'telefono') { va = a.telefono || ''; vb = b.telefono || ''; }
            else if (col === 'direccion') { va = a.direccion || ''; vb = b.direccion || ''; }
            return va.localeCompare(vb, 'es') * dir;
        });
    }

    // 3. Paginación
    const total = datos.length;
    if (totalEntrenador) totalEntrenador.textContent = `${entrenadoresData.length} Registrados`;
    if (infoTabla) infoTabla.textContent = `Mostrando ${total === 0 ? 0 : (tablaPagina - 1) * tablaPorPagina + 1}–${Math.min(tablaPagina * tablaPorPagina, total)} de ${total}`;

    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;

    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    actualizarSortIcons();

    // 4. Inyección del HTML
    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users-slash text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay entrenadores registrados en el sistema</span>
                </td>
            </tr>`;
    } else {
        tbody.innerHTML = pagina.map(ent => {
            const InicialesHtml = ent.foto 
                ? `<img src="${ent.foto}" class="w-8 h-8 rounded-full object-cover">`
                : `<div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold uppercase">${ent.nombres[0]}${ent.apellidos[0]}</div>`;

            return `
            <tr class="entrenador-row border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${ent.cedula} ${ent.nombres} ${ent.apellidos}">
                <td class="p-4 font-medium text-gray-900 dark:text-white flex items-center gap-3">
                    ${InicialesHtml}
                    <div>
                        <span class="block">${ent.nombres} ${ent.apellidos}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${ent.correo || ''}</span>
                    </div>
                </td>
                <td class="p-4 text-gray-700 dark:text-gray-300 font-mono text-xs">${ent.cedula}</td>
                <td class="p-4 text-gray-600 dark:text-gray-400">${ent.telefono}</td>
                <td class="p-4 text-gray-600 dark:text-gray-400 max-w-xs truncate">${ent.direccion}</td>
                <td class="p-4 text-right">
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <div class="flex justify-end gap-2">
                        <button onclick="verDetalle(${ent.id_entrenador})" class="bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2 rounded-lg transition" title="Ver Perfil">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        <button onclick="abrirModalEntrenador(${ent.id_entrenador})" class="bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg transition" title="Editar">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="eliminarEntrenador(${ent.id_entrenador})" class="bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 p-2 rounded-lg transition" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                    ` : '<span class="text-gray-500 dark:text-gray-400 text-xs">Solo lectura</span>'}
                </td>
            </tr>
        `}).join('');
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
        html += `<button onclick="tablaPagina--; renderTablaEntrenador()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-left"></i></button>`;
    }

    const maxVisible = 5;
    let start = Math.max(1, tablaPagina - Math.floor(maxVisible / 2));
    let end = Math.min(totalPaginas, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);

    for (let i = start; i <= end; i++) {
        if (i === tablaPagina) {
            html += `<button class="${btnClass} ${btnActivo}">${i}</button>`;
        } else {
            html += `<button onclick="tablaPagina=${i}; renderTablaEntrenador()" class="${btnClass} ${btnInactivo}">${i}</button>`;
        }
    }

    if (tablaPagina < totalPaginas) {
        html += `<button onclick="tablaPagina++; renderTablaEntrenador()" class="${btnClass} ${btnInactivo}"><i class="fas fa-chevron-right"></i></button>`;
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
        renderTablaEntrenador();
    });
});

const inputBusqueda = document.getElementById('busquedaCedula');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1;
        renderTablaEntrenador();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaEntrenador();

    try { 
        setupValidacionTiempoReal();
        console.log('Validaciones en tiempo real inicializadas correctamente');
    } catch(e){ 
        console.warn('Error en validaciones:', e); 
    }

    formEntrenador.addEventListener('submit', async function (e) {
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

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formEntrenador);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') {
                    UI.exito('Transacción Exitosa', resultado.message);
                } else {
                    alert('Éxito: ' + resultado.message);
                }
                cerrarModalEntrenador();
                cargarTablaEntrenador();
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

async function eliminarEntrenador(id_entrenador) {
    const confirmacion = confirm("¿Está seguro de eliminar este entrenador? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_entrenador', id_entrenador);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') {
                UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            }
            cargarTablaEntrenador();
        } else {
            if (typeof UI !== 'undefined') {
                UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
            }
        }
    }
}