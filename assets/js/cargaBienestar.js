// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalCarga = document.getElementById('modalCarga');
const formCarga = document.getElementById('formCarga');
const btnGuardar = document.getElementById('btnGuardar');

// Ruta al controlador pivote a través del index
const API_URL = 'index.php?p=cargaBienestar';

/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
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

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================

function cerrarModalCarga() {
    modalCarga.classList.add('hidden');
    modalCarga.firstElementChild.classList.add('scale-95', 'opacity-0');
    
    // Resetear formulario
    formCarga.reset();
    document.getElementById('id_rpe').value = '';
    document.getElementById('accion_form').value = 'guardar';
    
    // Resetear buscador de atletas
    document.getElementById('id_atleta').value = '';
    const inputBuscar = document.getElementById('inputBuscarAtleta');
    if(inputBuscar) {
        inputBuscar.value = '';
        inputBuscar.classList.remove('text-emerald-400', 'font-bold');
        inputBuscar.removeAttribute('readonly');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');
    }
    
    // Resetear preview de sRPE
    document.getElementById('srpePreview').innerText = '0';
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalCarga.classList.contains('hidden')) {
        cerrarModalCarga();
    }
});

// =====================================================================
// ABRIR MODAL (REGISTRAR / EDITAR)
// =====================================================================
async function abrirModalCarga(id_rpe = null) {
    // Limpieza completa
    formCarga.reset();
    try { Validador.limpiarEstilos(formCarga); } catch(e) {}
    
    document.getElementById('id_rpe').value = '';
    document.getElementById('accion_form').value = 'guardar';
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.innerHTML = 'GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>';
    btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
    btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
    
    // Desbloquear buscador de atletas
    const inputAtleta = document.getElementById('inputBuscarAtleta');
    inputAtleta.readOnly = false;
    inputAtleta.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-gray-800');
    document.getElementById('btnLimpiarAtleta').classList.add('hidden');
    
    // Mostrar modal con animación
    modalCarga.classList.remove('hidden');
    setTimeout(() => {
        modalCarga.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
    
    // Cargar lista de atletas para el buscador
    cargarAtletasBuscador();
    
    // Si es edición, cargar datos
    if (id_rpe) {
        const data = await peticionAjax(`obtenerDetalleCarga&id=${id_rpe}`);
        if (!data) {
            UI.error('Error', 'No se pudieron cargar los datos para edición.');
            cerrarModalCarga();
            return;
        }
        
        // Llenar campos
        document.getElementById('id_rpe').value = data.id_rpe;
        document.getElementById('id_atleta').value = data.id_atleta;
        document.getElementById('fecha').value = data.fecha;
        document.getElementById('rpe').value = data.rpe;
        document.getElementById('horas_sueno').value = data.horas_sueno;
        document.getElementById('calidad_sueno').value = data.calidad_sueno;
        document.getElementById('sensacion_muscular').value = data.sensacion_muscular;
        document.getElementById('estres_percibido').value = data.estres_percibido;
        document.getElementById('duracion_minutos').value = data.duracion_minutos;
        document.getElementById('metros_nadados').value = data.metros_nadados;
        document.getElementById('observaciones').value = data.observaciones || '';
        
        // Mostrar nombre del atleta en el buscador
        inputAtleta.value = `${data.nombre_atleta}`;
        inputAtleta.readOnly = true;
        inputAtleta.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-800');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');
        
        // Calcular y mostrar sRPE preview
        calcularSRPE();
        
        // Cambiar modo a actualizar
        document.getElementById('accion_form').value = 'actualizar';
        btnGuardar.innerHTML = 'ACTUALIZAR REGISTRO <i class="fas fa-sync-alt ml-2"></i>';
        btnGuardar.classList.replace('bg-indigo-600', 'bg-emerald-600');
        btnGuardar.classList.replace('hover:bg-indigo-500', 'hover:bg-emerald-500');
    }
}

// =====================================================================
// BUSCADOR PREDICTIVO DE ATLETAS (COMPONENTE CUSTOM)
// =====================================================================
let atletasGlobal = [];

const inputBuscar = document.getElementById('inputBuscarAtleta');
const dropdown = document.getElementById('dropdownAtletas');
const ulAtletas = document.getElementById('ulAtletas');
const inputIdOculto = document.getElementById('id_atleta');
const btnLimpiar = document.getElementById('btnLimpiarAtleta');

async function cargarAtletasBuscador() {
    const respuesta = await peticionAjax('listarAtletasSelect');
    if (respuesta) atletasGlobal = respuesta;
}

function renderizarDropdown(lista) {
    ulAtletas.innerHTML = '';
    if (lista.length === 0) {
        ulAtletas.innerHTML = '<li class="p-4 text-gray-500 text-center text-xs">No se encontraron coincidencias</li>';
        return;
    }
    lista.forEach(atleta => {
        const li = document.createElement('li');
        li.className = "p-3 hover:bg-indigo-600/20 hover:text-indigo-300 cursor-pointer transition-colors flex justify-between items-center";
        li.innerHTML = `
            <div>
                <div class="font-bold text-white">${atleta.nombres} ${atleta.apellidos}</div>
                <div class="text-[10px] text-gray-500 font-mono mt-0.5">C.I: ${atleta.cedula}</div>
            </div>
            <i class="fas fa-check-circle text-indigo-500/0 transition-all"></i>
        `;
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

// =====================================================================
// CÁLCULO AUTOMÁTICO DE sRPE
// =====================================================================
function calcularSRPE() {
    const rpe = parseInt(document.getElementById('rpe')?.value) || 0;
    const duracion = parseInt(document.getElementById('duracion_minutos')?.value) || 0;
    const srpe = rpe * duracion;
    document.getElementById('srpePreview').innerText = srpe;
    // Asignar al campo oculto o directo si existe
    const inputSrpe = document.getElementById('srpe');
    if (inputSrpe) inputSrpe.value = srpe;
}

// Escuchar cambios en RPE y duración
document.getElementById('rpe')?.addEventListener('input', calcularSRPE);
document.getElementById('duracion_minutos')?.addEventListener('input', calcularSRPE);

// =====================================================================
// ENVÍO DEL FORMULARIO (GUARDAR / ACTUALIZAR)
// =====================================================================
formCarga.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Validaciones frontend
    const erroresFormulario = Validador.validarFormulario(formCarga);
    if (erroresFormulario && erroresFormulario.length > 0) {
        const listaErrores = Array.isArray(erroresFormulario) 
                             ? erroresFormulario.join('<br>') 
                             : erroresFormulario;
        UI.error('Datos Incompletos', `<div class="text-left text-sm mt-2 text-gray-300">
            <p class="mb-2 font-bold text-white">Por favor, corrige lo siguiente:</p>
            ${listaErrores}
        </div>`);
        return;
    }
    
    // Asegurar que srpe esté actualizado
    calcularSRPE();
    
    let datosFormulario = new FormData(formCarga);
    const accionActual = document.getElementById('accion_form').value;
    datosFormulario.set('accion', accionActual);
    
    // Cambiar botón a estado de carga
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...';
    btnGuardar.disabled = true;
    
    const resultado = await peticionAjax(accionActual, datosFormulario);
    
    if (resultado) {
        if (resultado.status === 'success') {
            const msjExito = (accionActual === 'actualizar') 
                             ? 'El registro ha sido actualizado correctamente.' 
                             : 'Registro de carga guardado exitosamente.';
            UI.exito('¡Operación Exitosa!', msjExito);
            cerrarModalCarga();
            cargarTablaCargas();
        } 
        else if (resultado.status === 'warning') {
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Datos Incompletos', mensajesError);
        } 
        else {
            UI.error('Error de Sistema', resultado.message || 'Ocurrió un error inesperado.');
        }
    }
    
    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});

// =====================================================================
// CARGAR TABLA PRINCIPAL (LISTADO CON FILTROS)
// =====================================================================
async function cargarTablaCargas() {
    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const fecha_desde = document.getElementById('filtroFechaDesde')?.value || '';
    const fecha_hasta = document.getElementById('filtroFechaHasta')?.value || '';
    
    let params = new URLSearchParams({ estado: filtroEstado });
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (fecha_desde) params.append('fecha_desde', fecha_desde);
    if (fecha_hasta) params.append('fecha_hasta', fecha_hasta);
    
    const tbody = document.getElementById('tbodyCargas');
    tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando registros...</td></tr>';
    
    const cargas = await peticionAjax(`listarCargas&${params.toString()}`);
    
    if (!cargas || cargas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-gray-500 font-mono text-xs">No hay registros de carga en esta vista.</td></tr>';
        return;
    }
    
    let html = '';
    cargas.forEach(carga => {
        const fechaFormateada = formatearFecha(carga.fecha);
        const calidadMap = {1:'⭐',2:'⭐⭐',3:'⭐⭐⭐',4:'⭐⭐⭐⭐',5:'⭐⭐⭐⭐⭐'};
        const calidadEstrellas = calidadMap[carga.calidad_sueno] || '—';
        
        const botonEstado = (carga.estado === 'Activo')
            ? `<button onclick="anularRegistro(${carga.id_rpe})" class="text-red-400 hover:bg-red-500/10 p-2 rounded-lg transition" title="Anular Registro"><i class="fas fa-ban"></i></button>`
            : `<button onclick="reactivarRegistro(${carga.id_rpe})" class="text-emerald-400 hover:bg-emerald-500/10 p-2 rounded-lg transition" title="Reactivar Registro"><i class="fas fa-undo-alt"></i></button>`;
        
        html += `
            <tr class="hover:bg-white/5 transition-colors duration-200 border-b border-[#252345]">
                <td class="p-4 font-bold text-white text-sm">${carga.nombre_atleta}</td>
                <td class="p-4 text-xs font-mono text-gray-400">${fechaFormateada}</td>
                <td class="p-4"><span class="bg-indigo-500/20 text-indigo-300 px-2 py-1 rounded text-xs font-bold">${carga.rpe}</span></td>
                <td class="p-4">${carga.horas_sueno ?? '—'}</td>
                <td class="p-4">${calidadEstrellas}</td>
                <td class="p-4">${carga.sensacion_muscular ?? '—'}</td>
                <td class="p-4">${carga.estres_percibido ?? '—'}</td>
                <td class="p-4">${carga.srpe ?? '—'}</td>
                <td class="p-4 text-right space-x-1">
                    <button onclick="verDetalleCarga(${carga.id_rpe})" class="text-indigo-400 hover:bg-indigo-500/10 p-2 rounded-lg transition" title="Ver Detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar ? 
                        `<button onclick="abrirModalCarga(${carga.id_rpe})" class="text-amber-400 hover:bg-amber-500/10 p-2 rounded-lg transition" title="Editar">
                            <i class="fas fa-edit"></i>
                         </button>` : ''
                    }
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.anular ? botonEstado : ''}
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// =====================================================================
// CARGAR FILTRO DE ATLETAS (SELECT)
// =====================================================================
async function cargarFiltroAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    const select = document.getElementById('filtroAtleta');
    if (atletas && atletas.length > 0) {
        select.innerHTML = '<option value="">👤 Todos los Atletas</option>';
        atletas.forEach(atleta => {
            select.insertAdjacentHTML('beforeend', `<option value="${atleta.id_atleta}">${atleta.nombres} ${atleta.apellidos} (${atleta.cedula})</option>`);
        });
    }
}

// =====================================================================
// VER DETALLE COMPLETO (MODAL)
// =====================================================================
let instanciaGraficaCarga = null;

async function verDetalleCarga(id_rpe) {
    const modalVer = document.getElementById('modalVer');
    const contenedor = document.getElementById('detalleContenido');
    
    contenedor.innerHTML = `<div class="text-center p-12 text-gray-500">
        <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i>
        <p class="text-xs font-mono uppercase tracking-widest">Cargando datos de bienestar...</p>
    </div>`;
    
    modalVer.classList.remove('hidden');
    
    const data = await peticionAjax(`obtenerDetalleCarga&id=${id_rpe}`);
    if (!data) {
        UI.error('Error', 'No se pudo obtener el detalle del registro.');
        cerrarModalVer();
        return;
    }
    
    const calidadMap = {1:'Muy mala',2:'Mala',3:'Regular',4:'Buena',5:'Excelente'};
    const calidadTexto = calidadMap[data.calidad_sueno] || 'No registrado';
    
    const motivoHTML = data.motivo_anulacion 
        ? `<div class="mt-2 p-2 bg-red-900/20 border border-red-500/30 rounded text-xs text-red-300">
            <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Motivo de anulación:</strong> ${data.motivo_anulacion}
           </div>` 
        : '';
    
    contenedor.innerHTML = `
        <div class="mb-4">
            <span class="px-2.5 py-0.5 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-bold rounded-md uppercase tracking-widest">
                <i class="fas fa-heartbeat mr-1"></i> Percepción de Bienestar
            </span>
            <h2 class="text-xl font-bold text-white mt-2">${data.nombre_atleta}</h2>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Registro del ${formatearFecha(data.fecha)}</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">RPE</p>
                <p class="text-2xl font-mono text-indigo-400 font-black">${data.rpe}/10</p>
                <p class="text-[10px] text-gray-500">Esfuerzo percibido</p>
            </div>
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">sRPE</p>
                <p class="text-2xl font-mono text-emerald-400 font-black">${data.srpe || '—'}</p>
                <p class="text-[10px] text-gray-500">RPE × minutos</p>
            </div>
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Horas sueño</p>
                <p class="text-xl font-mono text-white">${data.horas_sueno ?? '—'}</p>
            </div>
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Calidad sueño</p>
                <p class="text-sm font-bold text-amber-400">${calidadTexto}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-black/30 p-3 rounded-xl border border-white/5">
                <p class="text-[10px] uppercase text-gray-400 font-bold">Sensación muscular</p>
                <p class="text-lg font-mono text-white">${data.sensacion_muscular ?? '—'} <span class="text-xs text-gray-500">/10</span></p>
            </div>
            <div class="bg-black/30 p-3 rounded-xl border border-white/5">
                <p class="text-[10px] uppercase text-gray-400 font-bold">Estrés percibido</p>
                <p class="text-lg font-mono text-white">${data.estres_percibido ?? '—'} <span class="text-xs text-gray-500">/10</span></p>
            </div>
            <div class="bg-black/30 p-3 rounded-xl border border-white/5">
                <p class="text-[10px] uppercase text-gray-400 font-bold">Metros nadados</p>
                <p class="text-lg font-mono text-white">${data.metros_nadados ?? '—'} m</p>
            </div>
            <div class="bg-black/30 p-3 rounded-xl border border-white/5">
                <p class="text-[10px] uppercase text-gray-400 font-bold">Duración</p>
                <p class="text-lg font-mono text-white">${data.duracion_minutos ?? '—'} min</p>
            </div>
        </div>
        
        ${data.observaciones ? `<div class="mb-6 bg-black/20 p-3 rounded-xl border border-white/5">
            <p class="text-[10px] uppercase text-gray-400 font-bold">Observaciones</p>
            <p class="text-sm text-gray-300 mt-1">${data.observaciones}</p>
        </div>` : ''}
        
        ${motivoHTML}
        
        <div class="flex justify-end mt-4">
            <button onclick="cerrarModalVer()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">Cerrar</button>
        </div>
    `;
}

function cerrarModalVer() {
    document.getElementById('modalVer').classList.add('hidden');
}

// =====================================================================
// OPERACIONES DE ESTADO (ANULAR / REACTIVAR)
// =====================================================================
async function anularRegistro(id_rpe) {
    const alerta = await UI.pedirJustificacion(
        'Anular Registro de Carga',
        'Indique el motivo de la anulación (ej. registro erróneo, atleta no entrenó, etc.):',
        'Escriba la justificación aquí...'
    );
    
    if (alerta.isConfirmed && alerta.value) {
        let datosAnular = new FormData();
        datosAnular.append('accion', 'anular');
        datosAnular.append('id_rpe', id_rpe);
        datosAnular.append('motivo', alerta.value);
        
        const resultado = await peticionAjax('anular', datosAnular);
        if (resultado && resultado.status === 'success') {
            UI.exito('Anulado', resultado.message);
            cargarTablaCargas();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo anular el registro.');
        }
    }
}

async function reactivarRegistro(id_rpe) {
    const confirmacion = await UI.confirmar(
        '¿Reactivar registro?',
        'Este registro volverá a estar activo en el histórico del atleta.'
    );
    
    if (confirmacion.isConfirmed) {
        let datosReactivar = new FormData();
        datosReactivar.append('accion', 'reactivar');
        datosReactivar.append('id_rpe', id_rpe);
        
        const resultado = await peticionAjax('reactivar', datosReactivar);
        if (resultado && resultado.status === 'success') {
            UI.exito('Reactivado', resultado.message);
            cargarTablaCargas();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo reactivar el registro.');
        }
    }
}

// =====================================================================
// INICIALIZACIÓN
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Validador !== 'undefined' && formCarga) {
        Validador.vincularTiempoReal(formCarga);
    }
    cargarFiltroAtletas();
    cargarTablaCargas();
    
    // Aplicar filtros cuando cambien los campos
    document.getElementById('filtroEstado')?.addEventListener('change', cargarTablaCargas);
    document.getElementById('filtroAtleta')?.addEventListener('change', cargarTablaCargas);
    document.getElementById('filtroFechaDesde')?.addEventListener('change', cargarTablaCargas);
    document.getElementById('filtroFechaHasta')?.addEventListener('change', cargarTablaCargas);
});