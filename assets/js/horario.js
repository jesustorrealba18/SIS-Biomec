const modalHorario = document.getElementById('modalHorario');
const modalVer = document.getElementById('modalVerHorario'); 
const formHorario = document.getElementById('formHorario');
const btnGuardar = document.getElementById('btnGuardar');
const totalHorarios = document.getElementById('totalHorarios');

const API_URL = 'index.php?p=horario';

function normalizarHora(hora) {
    if (!hora) return '';
    if (hora.length > 5 && hora.includes(':')) {
        return hora.substring(0, 5);
    }
    return hora;
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
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

function cerrarModalHorario() {
    modalHorario.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalHorario.classList.add('hidden');
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
        if (!modalHorario.classList.contains('hidden')) cerrarModalHorario();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

async function abrirModalHorario(id_bloque = null) {
    formHorario.reset(); 
    try { Validador.limpiarEstilos(formHorario); } catch(e) {}
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_bloque');
    const modalTitulo = document.getElementById('modalTitulo');

    if (id_bloque) {
        if (inputAction) inputAction.value = 'actualizar';
        if (inputIdHidden) inputIdHidden.value = id_bloque;
        if (modalTitulo) modalTitulo.textContent = 'Actualizar Bloque de Horario';
        
        btnGuardar.innerHTML = 'Actualizar datos <i class="fas fa-sync-alt ml-2"></i>';
        
        const horario = await peticionAjax(`obtenerBloque&id=${id_bloque}`);
        
        if (horario) {
            document.getElementById('dia_semana').value = horario.dia_semana;
            // Normalizar horas al cargar para edición
            document.getElementById('hora_inicio').value = normalizarHora(horario.hora_inicio);
            document.getElementById('hora_fin').value = normalizarHora(horario.hora_fin);
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Bloque de Horario';
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalHorario.classList.remove('hidden');
    setTimeout(() => {
        modalHorario.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function verDetalle(id) {
    const horario = await peticionAjax(`obtenerBloque&id=${id}`);
    if (!horario) return;

    const horaInicioNormalizada = normalizarHora(horario.hora_inicio);
    const horaFinNormalizada = normalizarHora(horario.hora_fin);

    document.getElementById("verDia").innerText = horario.dia_semana; 
    document.getElementById("verHoraInicio").innerText = horaInicioNormalizada;
    document.getElementById("verHoraFin").innerText = horaFinNormalizada;
    document.getElementById("verRango").innerHTML = `<span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-sm">${horaInicioNormalizada} - ${horaFinNormalizada}</span>`;

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaHorario() {
    const tbody = document.getElementById('listaHorario');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="4" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando horarios...</span></td></tr>`;

    const horarios = await peticionAjax('listarHorario'); 

    if (!horarios || horarios.length === 0) {
        if(totalHorarios) totalHorarios.textContent = '0 Registrados';
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-12 text-gray-500">
                    <i class="fas fa-clock text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay bloques de horarios registrados</span>
                </td>
            </tr>
        `;
        return;
    }

    if(totalHorarios) totalHorarios.textContent = `${horarios.length} Horarios`;

    tbody.innerHTML = horarios.map(horario => {
        const horaInicioMostrar = normalizarHora(horario.hora_inicio);
        const horaFinMostrar = normalizarHora(horario.hora_fin);
        
        return `
        <tr class="horario-row border-b border-gray-800/50 hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${horario.dia_semana} ${horaInicioMostrar} ${horaFinMostrar}">
            <td class="p-4 font-medium text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold">
                        ${horario.dia_semana.substring(0, 2)}
                    </div>
                    <span>${horario.dia_semana}</span>
                </div>
            </td>
            <td class="p-4 text-gray-300">${horaInicioMostrar}</td>
            <td class="p-4 text-gray-300">${horaFinMostrar}</td>
            <td class="p-4 text-right">
                ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                <div class="flex justify-end gap-2">
                    <button onclick="verDetalle(${horario.id_bloque})" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 p-2 rounded-lg transition" title="Ver Detalle">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button onclick="abrirModalHorario(${horario.id_bloque})" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 p-2 rounded-lg transition" title="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="eliminarHorario(${horario.id_bloque})" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-2 rounded-lg transition" title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
                ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
            </td>
        </tr>
    `}).join('');
}
    
const inputBusqueda = document.getElementById('busquedaHorario');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.horario-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda') || '';
            fila.style.display = textoFila.toLowerCase().includes(valor) ? '' : 'none';
        });
    });
}

function normalizarHorasFormulario() {
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');
    
    if (horaInicio.value) {
        horaInicio.value = normalizarHora(horaInicio.value);
    }
    if (horaFin.value) {
        horaFin.value = normalizarHora(horaFin.value);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formHorario); } catch(e){}
    cargarTablaHorario();

    formHorario.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        normalizarHorasFormulario();

        const erroresJS = Validador.validarFormulario(formHorario);
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; 
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formHorario);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalHorario();
                cargarTablaHorario();
            } 
            else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                UI.advertencia('Validación del Servidor', msjErrores);
            } 
            else {
                UI.error('Error de Sistema', resultado.message);
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

async function eliminarHorario(id_bloque) {
    const confirmacion = confirm("¿Está seguro de eliminar este bloque de horario? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_bloque', id_bloque);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', 'El horario ha sido removido exitosamente.');
            cargarTablaHorario();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el horario.');
        }
    }
}