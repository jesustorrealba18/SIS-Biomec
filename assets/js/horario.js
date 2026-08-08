// ==================== CONFIGURACIÓN DE PAGINACIÓN ====================
let horarioData = [];
let tablaFiltro = '';
let tablaSortCol = '';
let tablaSortDir = '';
let tablaPagina = 1;
const tablaPorPagina = 10;

// ==================== VARIABLES EXISTENTES ====================
const modalHorario = document.getElementById('modalHorario');
const modalVer = document.getElementById('modalVerHorario'); 
const formHorario = document.getElementById('formHorario');
const btnGuardar = document.getElementById('btnGuardar');
const totalHorarios = document.getElementById('totalHorarios');
const infoTabla = document.getElementById('infoTabla');
const pieTabla = document.getElementById('pieTabla');

const API_URL = 'index.php?p=horario';

// ==================== FUNCIONES DE NORMALIZACIÓN (sin cambios) ====================
function normalizarHora(hora) {
    if (!hora) return '';
    
    let limpia = hora.trim();
  
    if (/^\d{2}:\d{2}$/.test(limpia)) {
        return limpia;
    }

    if (/^\d{2}:\d{2}:\d{2}$/.test(limpia)) {
        return limpia.substring(0, 5);
    }
    
    if (/^\d{2}$/.test(limpia)) {
        return limpia + ':00';
    }
  
    if (/^\d{1}:\d{2}$/.test(limpia)) {
        let partes = limpia.split(':');
        let hora = partes[0].padStart(2, '0');
        return hora + ':' + partes[1];
    }
   
    if (/^\d{1}:\d{2}:\d{2}$/.test(limpia)) {
        let partes = limpia.split(':');
        let hora = partes[0].padStart(2, '0');
        return hora + ':' + partes[1];
    }

    let numeros = limpia.replace(/[^0-9]/g, '');
    if (numeros.length >= 2) {
        let horaNum = parseInt(numeros.substring(0, 2));
        if (horaNum > 23) horaNum = 23;
        let horaStr = horaNum.toString().padStart(2, '0');
        
        let minutosNum = 0;
        
        if (numeros.length >= 4) {
            minutosNum = parseInt(numeros.substring(2, 4));
            if (minutosNum > 59) minutosNum = 59;
        }
        
        return `${horaStr}:${minutosNum.toString().padStart(2, '0')}`;
    }
   
    return limpia;
}

function normalizarHoraParaDisplay(hora) {
    if (!hora) return '';
    if (/^\d{2}:\d{2}:\d{2}$/.test(hora)) {
        return hora.substring(0, 5);
    }
    return hora;
}

function validarHoraLocal(hora) {
    if (!hora) return false;
    const regex = /^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/;
    return regex.test(hora);
}

function corregirHoraInput(hora) {
    if (!hora) return '';

    let limpia = hora.replace(/[^0-9:]/g, '');
    
    if (!limpia.includes(':')) {
        if (limpia.length >= 2) {
            let horaNum = parseInt(limpia.substring(0, 2));
            if (horaNum > 23) horaNum = 23;
            let horaStr = horaNum.toString().padStart(2, '0');
            
            let minutos = '00';
            
            if (limpia.length >= 4) {
                let minutosNum = parseInt(limpia.substring(2, 4));
                if (minutosNum > 59) minutosNum = 59;
                minutos = minutosNum.toString().padStart(2, '0');
            }
            
            return `${horaStr}:${minutos}`;
        } else if (limpia.length === 1) {
            return '0' + limpia + ':00';
        } else {
            return '00:00';
        }
    } else {
        let partes = limpia.split(':');
        let horas = partes[0].substring(0, 2);
        let minutos = partes[1] ? partes[1].substring(0, 2) : '00';

        let horasNum = parseInt(horas);
        if (isNaN(horasNum)) horasNum = 0;
        if (horasNum > 23) horasNum = 23;
        horas = horasNum.toString().padStart(2, '0');

        let minutosNum = parseInt(minutos);
        if (isNaN(minutosNum)) minutosNum = 0;
        if (minutosNum > 59) minutosNum = 59;
        minutos = minutosNum.toString().padStart(2, '0');
        
        return `${horas}:${minutos}`;
    }
}

// ==================== PETICIÓN AJAX (sin cambios) ====================
async function peticionAjax(accion, datos = null) {
    const opciones = { 
        method: datos ? 'POST' : 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
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

// ==================== FUNCIONES DE CIERRE DE MODALES (sin cambios) ====================
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
        if (!modalHorario.classList.contains('hidden')) cerrarModalHorario();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

// ==================== ABRIR MODAL (sin cambios) ====================
async function abrirModalHorario(id_bloque = null) {
    formHorario.reset(); 
    try { Validador.limpiarEstilos(formHorario); } catch(e) {}
    
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
            const horaInicio = normalizarHora(horario.hora_inicio);
            const horaFin = normalizarHora(horario.hora_fin);
            document.getElementById('hora_inicio').value = horaInicio;
            document.getElementById('hora_fin').value = horaFin;
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

// ==================== VER DETALLE (sin cambios) ====================
async function verDetalle(id) {
    const horario = await peticionAjax(`obtenerBloque&id=${id}`);
    if (!horario) return;

    const horaInicioNormalizada = normalizarHora(horario.hora_inicio);
    const horaFinNormalizada = normalizarHora(horario.hora_fin);
    
    const horaInicioDisplay = normalizarHoraParaDisplay(horaInicioNormalizada);
    const horaFinDisplay = normalizarHoraParaDisplay(horaFinNormalizada);

    document.getElementById("verDia").innerText = horario.dia_semana; 
    document.getElementById("verHoraInicio").innerText = horaInicioDisplay;
    document.getElementById("verHoraFin").innerText = horaFinDisplay;
    document.getElementById("verRango").innerHTML = `<span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-sm">${horaInicioDisplay} - ${horaFinDisplay}</span>`;

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

// ==================== RENDERIZAR TABLA CON PAGINACIÓN ====================
function renderTabla() {
    const tbody = document.getElementById('listaHorario');
    if (!tbody) return;

    let datos = horarioData.slice();

    // Filtro
    if (tablaFiltro) {
        const q = tablaFiltro.toLowerCase().trim();
        datos = datos.filter(h => {
            const horaInicioDisplay = normalizarHoraParaDisplay(h.hora_inicio);
            const horaFinDisplay = normalizarHoraParaDisplay(h.hora_fin);
            const texto = `${h.dia_semana} ${horaInicioDisplay} ${horaFinDisplay}`.toLowerCase();
            return texto.includes(q);
        });
    }

    // Ordenamiento
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
    if (totalHorarios) totalHorarios.textContent = `${horarioData.length} Horarios`;
    
    if (infoTabla) {
        if (total === 0) {
            infoTabla.textContent = '0 registros';
        } else {
            const inicio = (tablaPagina - 1) * tablaPorPagina + 1;
            const fin = Math.min(tablaPagina * tablaPorPagina, total);
            infoTabla.textContent = `Mostrando ${inicio}–${fin} de ${total}`;
        }
    }

    // Paginación
    const totalPaginas = Math.max(1, Math.ceil(total / tablaPorPagina));
    if (tablaPagina > totalPaginas) tablaPagina = totalPaginas;
    const inicio = (tablaPagina - 1) * tablaPorPagina;
    const pagina = datos.slice(inicio, inicio + tablaPorPagina);

    // Generar filas
    if (pagina.length === 0 && total > 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500 dark:text-gray-400"><span class="text-xs uppercase tracking-wider">Sin resultados para la búsqueda</span></td></tr>`;
    } else if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clock text-4xl mb-3 block text-gray-400 dark:text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay bloques de horarios registrados</span>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = pagina.map(horario => {
            const horaInicioMostrar = normalizarHoraParaDisplay(horario.hora_inicio);
            const horaFinMostrar = normalizarHoraParaDisplay(horario.hora_fin);
            const busqueda = `${horario.dia_semana} ${horaInicioMostrar} ${horaFinMostrar}`;
            
            return `
                <tr class="horario-row border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${busqueda}">
                    <td class="p-4 font-medium text-gray-900 dark:text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                                ${horario.dia_semana.substring(0, 2)}
                            </div>
                            <span>${horario.dia_semana}</span>
                        </div>
                    </td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${horaInicioMostrar}</td>
                    <td class="p-4 text-gray-700 dark:text-gray-300">${horaFinMostrar}</td>
                    <td class="p-4 text-right">
                        ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                        <div class="flex justify-end gap-2">
                            <button onclick="verDetalle(${horario.id_bloque})" class="bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2 rounded-lg transition" title="Ver Detalle">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            <button onclick="abrirModalHorario(${horario.id_bloque})" class="bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button onclick="eliminarHorario(${horario.id_bloque})" class="bg-red-50 dark:bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 p-2 rounded-lg transition" title="Eliminar">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                        ` : '<span class="text-gray-500 dark:text-gray-600 text-xs">Solo lectura</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }

    renderPaginacion(totalPaginas);
}

// ==================== RENDER PAGINACIÓN ====================
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

// ==================== CARGAR TABLA DE HORARIOS ====================
async function cargarTablaHorario() {
    const tbody = document.getElementById('listaHorario');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="4" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando horarios...</span></td></tr>`;

    const horarios = await peticionAjax('listarHorario'); 

    // RESETEAR variables de paginación al cargar nuevos datos
    tablaFiltro = '';
    tablaSortCol = '';
    tablaSortDir = '';
    tablaPagina = 1;

    if (!horarios || horarios.length === 0) {
        horarioData = [];
        if(totalHorarios) totalHorarios.textContent = '0 Horarios';
        if(infoTabla) infoTabla.textContent = '';
        if(pieTabla) pieTabla.innerHTML = '';
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-12 text-gray-500 dark:text-gray-400">
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

// ==================== BUSCADOR ====================
const inputBusqueda = document.getElementById('busquedaHorario');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        tablaFiltro = e.target.value.toLowerCase().trim();
        tablaPagina = 1;
        renderTabla();
    });
}

// ==================== ORDENAMIENTO POR CLICK EN CABECERA ====================
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

// ==================== FUNCIONES DE VALIDACIÓN (sin cambios) ====================
function normalizarHorasFormulario() {
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');
    
    if (horaInicio && horaInicio.value) {
        let valor = horaInicio.value;
        if (valor.match(/^\d{2}:\d{2}:\d{2}$/)) {
            valor = valor.substring(0, 5);
        }
        horaInicio.value = valor;
    }
    
    if (horaFin && horaFin.value) {
        let valor = horaFin.value;
        if (valor.match(/^\d{2}:\d{2}:\d{2}$/)) {
            valor = valor.substring(0, 5);
        }
        horaFin.value = valor;
    }
}

function agregarValidacionTiempoReal() {
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');
    
    const validarCampoHora = (campo) => {
        campo.addEventListener('blur', function() {
            if (this.value) {
                let corregida = corregirHoraInput(this.value);
                if (validarHoraLocal(corregida)) {
                    this.value = corregida;
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } else {
                    this.classList.add('border-red-500');
                    this.classList.remove('border-green-500');
                    if (typeof UI !== 'undefined') {
                        UI.advertencia('Formato inválido', 'El formato de hora debe ser HH:MM (ejemplo: 14:30)');
                    }
                }
            }
        });
        
        campo.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'border-green-500');
        });
    };
    
    if (horaInicio) validarCampoHora(horaInicio);
    if (horaFin) validarCampoHora(horaFin);
}

// ==================== EVENTOS DOM ====================
document.addEventListener('DOMContentLoaded', () => {
    try { 
        if (typeof Validador !== 'undefined') Validador.vincularTiempoReal(formHorario); 
    } catch(e){}
    
    agregarValidacionTiempoReal();
    cargarTablaHorario();

    formHorario.addEventListener('submit', async function (e) {
        e.preventDefault();

        const horaInicio = document.getElementById('hora_inicio');
        const horaFin = document.getElementById('hora_fin');
        
        normalizarHorasFormulario();
        
        if (!validarHoraLocal(horaInicio.value)) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Hora inválida', 'La hora de inicio debe tener formato HH:MM (ejemplo: 08:00, 14:30, 23:59)');
            }
            horaInicio.focus();
            return;
        }
        
        if (!validarHoraLocal(horaFin.value)) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Hora inválida', 'La hora de fin debe tener formato HH:MM (ejemplo: 08:00, 14:30, 23:59)');
            }
            horaFin.focus();
            return;
        }

        let erroresJS = false;
        try {
            if (typeof Validador !== 'undefined') {
                erroresJS = Validador.validarFormulario(formHorario);
            }
        } catch(e) {}
        
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
        
        console.log('Enviando datos:', {
            action_type: datosForm.get('action_type'),
            id_bloque: datosForm.get('id_bloque'),
            dia_semana: datosForm.get('dia_semana'),
            hora_inicio: datosForm.get('hora_inicio'),
            hora_fin: datosForm.get('hora_fin')
        });
        
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                if (typeof UI !== 'undefined') UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalHorario();
                cargarTablaHorario();
            } 
            else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                if (typeof UI !== 'undefined') UI.advertencia('Validación del Servidor', msjErrores);
            } 
            else {
                if (typeof UI !== 'undefined') UI.error('Error de Sistema', resultado.message || 'Error al procesar la solicitud');
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

// ==================== ELIMINAR HORARIO (sin cambios) ====================
async function eliminarHorario(id_bloque) {
    const confirmacion = confirm("¿Está seguro de eliminar este bloque de horario? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_bloque', id_bloque);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') UI.exito('Eliminado', 'El horario ha sido removido exitosamente.');
            cargarTablaHorario();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo eliminar el horario.');
        }
    }
}