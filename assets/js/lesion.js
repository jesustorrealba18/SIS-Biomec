// =====================================================================
// CONTROLADOR FRONT-END: CONTROL CLÍNICO DE LESIONES (RF-10)
// =====================================================================
const modalFormulario = document.getElementById('modalFormulario');
const modalVer = document.getElementById('modalVer');
const formulario = document.getElementById('formularioLesion');
const btnGuardar = document.getElementById('btnGuardar');
const tablaCuerpo = document.getElementById('tablaCuerpo');
const tituloTablaState = document.getElementById('tituloTablaState');

// Referencias a los filtros
const filtroAtleta = document.getElementById('filtroAtleta');
const filtroTipo = document.getElementById('filtroTipo');
const filtroZona = document.getElementById('filtroZona');
const filtroEstadoClinico = document.getElementById('filtroEstadoClinico');
const btnPapelera = document.getElementById('btnMostrarPapelera');

const API_URL = 'index.php?p=lesion';
let dataTableLesiones = null;
let instanciaGrafica = null;
let modoPapelera = false; // Estado: false = Activos, true = Inactivos (Papelera)

// =====================================================================
// DETECCIÓN DE TEMA PARA CHART.JS Y COLORES DINÁMICOS
// =====================================================================
function getThemeColors() {
    const esOscuro = document.documentElement.classList.contains('dark');
    return {
        texto: esOscuro ? '#a0a0c0' : '#4b5563',
        grid: esOscuro ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)',
        fondo: esOscuro ? '#161430' : '#f3f4f6',
        borde: esOscuro ? '#252345' : '#e5e7eb'
    };
}

// ================== VALIDACIONES PERSONALIZADAS ==================
function validarCampoPersonalizado(campo) {
    if (!campo.hasAttribute('data-validar')) return true;
    
    const reglas = campo.getAttribute('data-validar').split('|');
    const valor = campo.value.trim();
    let valido = true;
    let mensaje = '';

    campo.style.borderColor = '';
    campo.title = '';

    if (reglas.includes('requerido') && valor === '') {
        valido = false;
        mensaje = 'Este campo es obligatorio.';
    }

    if (valor === '') {
        campo.style.borderColor = valido ? '' : '#f87171';
        return valido;
    }

    if (reglas.includes('letras') && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor)) {
        valido = false;
        mensaje = 'Solo se permiten letras.';
    }
    if (reglas.includes('numeros') && !/^[0-9]+$/.test(valor)) {
        valido = false;
        mensaje = 'Solo se permiten números.';
    }
    if (reglas.includes('texto') && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s.,;:()\-\#\/]+$/.test(valor)) {
        valido = false;
        mensaje = 'Contiene caracteres no permitidos.';
    }

    if (campo.hasAttribute('data-min')) {
        let min = parseInt(campo.getAttribute('data-min'));
        if (valor.length < min) {
            valido = false;
            mensaje = `Mínimo ${min} caracteres.`;
        }
    }
    if (campo.hasAttribute('data-max')) {
        let max = parseInt(campo.getAttribute('data-max'));
        if (valor.length > max) {
            valido = false;
            mensaje = `Máximo ${max} caracteres.`;
        }
    }

    if (reglas.includes('rango')) {
        let num = parseFloat(valor);
        if (!isNaN(num)) {
            let min = campo.hasAttribute('data-min-num') ? parseFloat(campo.getAttribute('data-min-num')) : -Infinity;
            let max = campo.hasAttribute('data-max-num') ? parseFloat(campo.getAttribute('data-max-num')) : Infinity;
            if (num < min || num > max) {
                valido = false;
                mensaje = `Debe estar entre ${min} y ${max}.`;
            }
        } else {
            valido = false;
            mensaje = 'Debe ser un número.';
        }
    }

    if (reglas.includes('fecha_logica') && valor !== '') {
        const hoy = new Date();
        const año = hoy.getFullYear();
        const fecha = new Date(valor);
        if (fecha > hoy) {
            valido = false;
            mensaje = 'La fecha no puede ser futura.';
        } else if (fecha < new Date(año - 120, 0, 1)) {
            valido = false;
            mensaje = 'Fecha demasiado antigua (más de 120 años).';
        }
    }

    if (reglas.includes('fecha_posterior') && valor !== '') {
        const dependencia = campo.getAttribute('data-depende');
        if (dependencia) {
            const campoBase = document.getElementById(dependencia);
            if (campoBase && campoBase.value) {
                if (new Date(valor) < new Date(campoBase.value)) {
                    valido = false;
                    mensaje = campo.getAttribute('data-mensaje') || 'Debe ser posterior a la fecha de inicio.';
                }
            }
        }
    }

    campo.style.borderColor = valido ? '#34d399' : '#f87171';
    if (!valido) campo.title = mensaje;
    
    return valido;
}

function validarFormularioPersonalizado(formulario) {
    let errores = [];
    const campos = formulario.querySelectorAll('[data-validar]');
    campos.forEach(campo => {
        if (!validarCampoPersonalizado(campo)) {
            const nombre = campo.getAttribute('data-nombre') || campo.name;
            const mensaje = campo.title || 'Valor inválido.';
            errores.push(`- <b>${nombre}</b>: ${mensaje}`);
        }
    });
    return errores.length ? errores.join('<br>') : false;
}

// =====================================================================
// PETICIÓN AJAX CENTRALIZADA
// =====================================================================
async function peticionAjax(accion, datos = null, metodo = 'GET') {
    let url = `${API_URL}&accion=${accion}`;
    const opciones = { method: metodo };
    
    if (datos) {
        if (metodo === 'POST' && !(datos instanceof FormData)) {
            opciones.body = JSON.stringify(datos);
            opciones.headers = { 'Content-Type': 'application/json' };
        } else {
            opciones.body = datos;
        }
    }
    
    try {
        const respuesta = await fetch(url, opciones);
        if (!respuesta.ok) throw new Error('Error HTTP: ' + respuesta.status);
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        if (typeof UI !== 'undefined') UI.error('Falla de Comunicación', 'El servidor no pudo procesar la solicitud.');
        return null;
    }
}

// =====================================================================
// CARGA DE DATOS INICIALES
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formulario);
    }
    
    cargarAtletas();
    cargarTabla();
    
    if (filtroAtleta) filtroAtleta.addEventListener('change', cargarTabla);
    if (filtroTipo) filtroTipo.addEventListener('change', cargarTabla);
    if (filtroZona) filtroZona.addEventListener('change', cargarTabla);
    if (filtroEstadoClinico) filtroEstadoClinico.addEventListener('change', cargarTabla);
 
});

async function cargarAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    if (!atletas || !Array.isArray(atletas)) return;
    
    let opc = '<option value="">Seleccione el atleta...</option>';
    let opcFiltro = '<option value="">👤 Todos los Atletas</option>';
    
    atletas.forEach(a => {
        const txt = `${a.nombres} ${a.apellidos} - ${a.cedula}`;
        opc += `<option value="${a.id_atleta}">${txt}</option>`;
        opcFiltro += `<option value="${a.id_atleta}">${txt}</option>`;
    });
    
    document.getElementById('id_atleta').innerHTML = opc;
    if (filtroAtleta) filtroAtleta.innerHTML = opcFiltro;
}

/* async function cargarTabla() {
    const params = new URLSearchParams();
    if (filtroAtleta?.value) params.append('id_atleta', filtroAtleta.value);
    if (filtroTipo?.value) params.append('tipo', filtroTipo.value);
    if (filtroZona?.value) params.append('zona', filtroZona.value);
    if (filtroEstadoClinico?.value) params.append('estado', filtroEstadoClinico.value);
    
    params.append('modo', modoPapelera ? 'papelera' : 'activos');
    
    tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando registros...</td></tr>`;
    
    const registros = await peticionAjax(`listarLesiones&${params.toString()}`);
    
    if (!registros || registros.length === 0) {
        tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-folder-open mb-2 text-2xl"></i><br>No hay registros que coincidan con los filtros en este apartado.</td></tr>`;
        actualizarKPIs([]);
        return;
    }
    
    let filas = '';
    registros.forEach(reg => {
        let colorMolestia = reg.nivel_molestia >= 8 ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : 
                           (reg.nivel_molestia >= 5 ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10');
        
        const iconos = { 'Activa':'🟢', 'EnRehabilitacion':'🟡', 'Recuperada':'✅', 'Cronica':'⚠️' };
        const lblEstado = iconos[reg.estado] ? `${iconos[reg.estado]} ${reg.estado.replace('EnRehabilitacion', 'En Rehab.')}` : reg.estado;

        const visibleBadge = reg.activo == 1 
            ? '<span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded text-xs border border-emerald-200 dark:border-emerald-500/30"><i class="fas fa-check"></i> Activo</span>'
            : '<span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded text-xs border border-red-200 dark:border-red-500/30" title="'+(reg.motivo_eliminacion||'')+'"><i class="fas fa-trash-alt"></i> Papelera</span>';
        
        let botones = `<button onclick="verDetalle(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-indigo-600 dark:hover:bg-indigo-600 text-gray-700 dark:text-white w-8 h-8 rounded-lg transition-colors" title="Ficha Médica"><i class="fas fa-eye text-xs"></i></button>`;
        
        if (reg.activo == 1) { 
            if (PERMISOS_MODULO.editar && reg.estado !== 'Recuperada') {
                botones += `<button onclick="abrirModal(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
            }
            if (PERMISOS_MODULO.eliminar) {
                botones += `<button onclick="softDelete(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminado Lógico (Papelera)"><i class="fas fa-trash-alt text-xs"></i></button>`;
            }
        } else { 
            if (PERMISOS_MODULO.reactivar) botones += `<button onclick="reactivar(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar de la papelera"><i class="fas fa-undo-alt text-xs"></i></button>`;
            if (PERMISOS_MODULO.eliminardb) botones += `<button onclick="eliminarFisico(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Destrucción total"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
        }
        
        filas += `
            <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${formatearFecha(reg.fecha_inicio)}</td>
                <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${reg.nombre_atleta}</td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${reg.zona_anatomica} ${reg.lado ? '('+reg.lado+')' : ''}<br><span class="text-xs text-gray-500 dark:text-gray-400">${reg.tipo}</span></td>
                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold border border-current ${colorMolestia}">${reg.nivel_molestia}/10</span></td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${lblEstado}</td>
                <td class="px-6 py-4 text-center">${visibleBadge}</td>
                <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
            </tr>`;
    });
    
    tablaCuerpo.innerHTML = filas;
    actualizarKPIs(registros.filter(r => r.activo == 1));
} */

    async function cargarTabla() {
    const params = new URLSearchParams();
    if (filtroAtleta?.value) params.append('id_atleta', filtroAtleta.value);
    if (filtroTipo?.value) params.append('tipo', filtroTipo.value);
    if (filtroZona?.value) params.append('zona', filtroZona.value);
    if (filtroEstadoClinico?.value) params.append('estado', filtroEstadoClinico.value);
    
    params.append('modo', modoPapelera ? 'papelera' : 'activos');
    
    tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando registros...</td></tr>`;
    
    const registros = await peticionAjax(`listarLesiones&${params.toString()}`);
    
    // Destruir instancia anterior si existe
    if (dataTableLesiones) {
        dataTableLesiones.destroy();
        dataTableLesiones = null;
    }
    
    if (!registros || registros.length === 0) {
        tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-folder-open mb-2 text-2xl"></i><br>No hay registros que coincidan con los filtros en este apartado.</td></tr>`;
        actualizarKPIs([]);
        return;
    }
    
    let filas = '';
    registros.forEach(reg => {
        let colorMolestia = reg.nivel_molestia >= 8 ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : 
                           (reg.nivel_molestia >= 5 ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10');
        
        const iconos = { 'Activa':'🟢', 'EnRehabilitacion':'🟡', 'Recuperada':'✅', 'Cronica':'⚠️' };
        const lblEstado = iconos[reg.estado] ? `${iconos[reg.estado]} ${reg.estado.replace('EnRehabilitacion', 'En Rehab.')}` : reg.estado;

        const visibleBadge = reg.activo == 1 
            ? '<span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded text-xs border border-emerald-200 dark:border-emerald-500/30"><i class="fas fa-check"></i> Activo</span>'
            : '<span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded text-xs border border-red-200 dark:border-red-500/30" title="'+(reg.motivo_eliminacion||'')+'"><i class="fas fa-trash-alt"></i> Papelera</span>';
        
        let botones = `<button onclick="verDetalle(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-indigo-600 dark:hover:bg-indigo-600 text-gray-700 dark:text-white w-8 h-8 rounded-lg transition-colors" title="Ficha Médica"><i class="fas fa-eye text-xs"></i></button>`;
        
        if (reg.activo == 1) { 
            if (PERMISOS_MODULO.editar && reg.estado !== 'Recuperada') {
                botones += `<button onclick="abrirModal(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
            }
            if (PERMISOS_MODULO.eliminar) {
                botones += `<button onclick="softDelete(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminado Lógico (Papelera)"><i class="fas fa-trash-alt text-xs"></i></button>`;
            }
        } else { 
            if (PERMISOS_MODULO.reactivar) botones += `<button onclick="reactivar(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar de la papelera"><i class="fas fa-undo-alt text-xs"></i></button>`;
            if (PERMISOS_MODULO.eliminardb) botones += `<button onclick="eliminarFisico(${reg.id_lesion})" class="bg-gray-200 dark:bg-[#252345] hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Destrucción total"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
        }
        
        filas += `
            <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors border-b border-gray-200 dark:border-[#252345]">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${formatearFecha(reg.fecha_inicio)}</td>
                <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300 font-semibold">${reg.nombre_atleta}</td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${reg.zona_anatomica} ${reg.lado ? '('+reg.lado+')' : ''}<br><span class="text-xs text-gray-500 dark:text-gray-400">${reg.tipo}</span></td>
                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold border border-current ${colorMolestia}">${reg.nivel_molestia}/10</span></td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">${lblEstado}</td>
                <td class="px-6 py-4 text-center">${visibleBadge}</td>
                <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
            </tr>`;
    });
    
    tablaCuerpo.innerHTML = filas;
    
    // Inicializar DataTables
    dataTableLesiones = $('#tablaLesiones').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            emptyTable: modoPapelera ? 'La papelera está vacía.' : 'No hay lesiones registradas.'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Atleta
            { responsivePriority: 2, targets: 4 }, // Estado clínico
            { responsivePriority: 3, targets: 6 }  // Acciones (no ordenable)
        ],
        order: [[0, 'desc']], // Ordenar por fecha de inicio
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todas"]],
        dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-2"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>'
    });
    
    actualizarKPIs(registros.filter(r => r.activo == 1));
}

function actualizarKPIs(activos) {
    if (document.getElementById('kpi_activas')) document.getElementById('kpi_activas').innerText = activos.length;
    if (document.getElementById('kpi_molestia_alta')) document.getElementById('kpi_molestia_alta').innerText = activos.filter(a => a.nivel_molestia > 7).length;
    
    let totalDias = 0, count = 0;
    activos.forEach(a => {
        if (a.fecha_inicio && a.fecha_estimada_recup) {
            const diff = Math.ceil((new Date(a.fecha_estimada_recup) - new Date(a.fecha_inicio)) / (1000*3600*24));
            if (diff > 0) { totalDias += diff; count++; }
        }
    });
    if (document.getElementById('kpi_reposo_promedio')) document.getElementById('kpi_reposo_promedio').innerText = count ? (totalDias/count).toFixed(1) : 0;
} 

// =====================================================================
// OPERACIONES DE FORMULARIO
// =====================================================================
function abrirModal(id_lesion = null) {
    formulario.reset();
    document.getElementById('id_lesion').value = '';
    document.getElementById('accion').value = 'registrar';
    document.getElementById('tituloModal').innerText = 'Registrar Nueva Lesión';
    document.getElementById('campoEstadoEdicion').style.display = 'none';
    
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formulario);
    
    btnGuardar.innerHTML = 'Guardar Informe <i class="fas fa-save ml-2"></i>';
    btnGuardar.classList.replace('bg-emerald-600', 'bg-indigo-600');
    btnGuardar.classList.replace('hover:bg-emerald-500', 'hover:bg-indigo-500');
    
    if (id_lesion) cargarDatosParaEdicion(id_lesion);
    modalFormulario.classList.remove('hidden');
}

async function cargarDatosParaEdicion(id_lesion) {
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    btnGuardar.disabled = true;
    
    const data = await peticionAjax(`obtenerDetalleLesion&id=${id_lesion}`);
    if (!data || data.status === 'error') {
        UI.error('Error', data?.message || 'No se pudo cargar la lesión.');
        cerrarModal();
        return;
    }
    
    Object.keys(data).forEach(key => {
        if (document.getElementById(key)) document.getElementById(key).value = data[key];
    });
    
    document.getElementById('accion').value = 'actualizar';
    document.getElementById('campoEstadoEdicion').style.display = 'block';
    document.getElementById('tituloModal').innerText = 'Actualizar Estado Clínico';
    
    btnGuardar.innerHTML = 'Actualizar Informe <i class="fas fa-sync-alt ml-2"></i>';
    btnGuardar.classList.replace('bg-indigo-600', 'bg-emerald-600');
    btnGuardar.classList.replace('hover:bg-indigo-500', 'hover:bg-emerald-500');
    btnGuardar.disabled = false;
}

function cerrarModal() {
    modalFormulario.classList.add('hidden');
    formulario.reset();
    document.getElementById('campoEstadoEdicion').style.display = 'none';
}

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errores = validarFormularioPersonalizado(formulario);
    if (errores !== false) {
        UI.error('Errores de validación', errores);
        return;
    }

    const originalText = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btnGuardar.disabled = true;
    
    let datos = new FormData(formulario);
    datos.set('accion', document.getElementById('accion').value);
    
    const resultado = await peticionAjax(datos.get('accion'), datos, 'POST');
    
    btnGuardar.innerHTML = originalText;
    btnGuardar.disabled = false;
    
    if (resultado && resultado.status === 'success') {
        UI.exito('Procesado', resultado.message);
        cerrarModal();
        cargarTabla();
    } else {
        UI.error('Advertencia', resultado?.message || 'Error de procesamiento');
    }
});

// =====================================================================
// OPERACIONES LÓGICAS Y FÍSICAS
// =====================================================================
async function softDelete(id_lesion) {
    const justificacion = await UI.pedirJustificacion(
        'Mover a Papelera',
        'Justifique por qué se anula este registro (ej. Error de transcripción):',
        'Motivo obligatorio (mínimo 10 caracteres)'
    );
    if (!justificacion.isConfirmed) return;
    
    let datos = new FormData();
    datos.append('id_lesion', id_lesion);
    datos.append('motivo', justificacion.value);
    
    const resultado = await peticionAjax('anular', datos, 'POST');
    if (resultado && resultado.status === 'success') {
        UI.exito('Movido a Papelera', resultado.message);
        cargarTabla();
    } else {
        UI.error('No se pudo anular', resultado?.message);
    }
}

async function reactivar(id_lesion) {
    const confirm = await UI.confirmar('Restaurar Registro', '¿Devolver este registro a los módulos de estadísticas clínicas?');
    if (!confirm.isConfirmed) return;
    
    let datos = new FormData();
    datos.append('id_lesion', id_lesion);
    
    const resultado = await peticionAjax('reactivar', datos, 'POST');
    if (resultado && resultado.status === 'success') {
        UI.exito('Restaurado', resultado.message);
        cargarTabla();
    } else {
        UI.error('Error', resultado?.message);
    }
}

async function eliminarFisico(id_lesion) {
    const confirm = await UI.confirmar(
        'Destrucción Permanente', 
        'Esta acción purgará el dato de la base de datos irreversiblemente. ¿Está completamente seguro?'
    );
    if (!confirm.isConfirmed) return;
    
    let datos = new FormData();
    datos.append('id_lesion', id_lesion);
    
    const resultado = await peticionAjax('eliminardb', datos, 'POST');
    if (resultado && resultado.status === 'success') {
        UI.exito('Purgado', resultado.message);
        cargarTabla();
    } else {
        UI.error('Acción Bloqueada', resultado?.message);
    }
}

// =====================================================================
// FICHA DE DETALLE Y GRÁFICA RPE
// =====================================================================
async function verDetalle(id_lesion) {
    modalVer.classList.remove('hidden');
    const contenedor = document.querySelector('#modalVer #contenidoDetalle');
    contenedor.innerHTML = `<div class="text-center py-20 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>`;
    
    const data = await peticionAjax(`obtenerDetalleLesion&id=${id_lesion}`);
    if (!data || data.status === 'error') return cerrarModalVer();
    
    const colors = getThemeColors();
    
    let advertenciaPapelera = data.activo == 0 
        ? `<div class="bg-red-50 dark:bg-red-500/20 border border-red-200 dark:border-red-500/50 p-3 rounded-xl mb-4"><i class="fas fa-trash-alt text-red-600 dark:text-red-400 mr-2"></i><strong class="text-red-600 dark:text-red-400">Motivo de anulación:</strong> <span class="text-gray-700 dark:text-gray-300">${data.motivo_eliminacion}</span></div>` 
        : '';

    contenedor.innerHTML = `
        ${advertenciaPapelera}
        <div class="flex items-center gap-4 mb-8 border-b border-gray-200 dark:border-white/10 pb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-3xl">
                <i class="fas fa-file-medical"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white">${data.nombres} ${data.apellidos}</h2>
                <p class="text-indigo-600 dark:text-indigo-400"><i class="fas fa-id-card"></i> ${data.cedula} | Inicio: ${formatearFecha(data.fecha_inicio)}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Zona</p>
                <p class="text-gray-900 dark:text-white font-bold">${data.zona_anatomica}</p>
            </div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Tipo</p>
                <p class="text-gray-900 dark:text-white font-bold">${data.tipo}</p>
            </div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Molestia</p>
                <p class="text-amber-600 dark:text-amber-400 font-bold">${data.nivel_molestia}/10</p>
            </div>
            <div class="bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Estado</p>
                <p class="text-emerald-600 dark:text-emerald-400 font-bold">${data.estado}</p>
            </div>
            
            <div class="col-span-2 md:col-span-4 bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Diagnóstico Clínico</p>
                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">${data.diagnostico}</p>
            </div>
            <div class="col-span-2 md:col-span-4 bg-gray-100 dark:bg-[#161430] p-4 rounded-xl border-l-2 border-indigo-500 border border-gray-200 dark:border-[#252345]">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Tratamiento Asignado por: ${data.profesional || 'No definido'}</p>
                <p class="text-gray-700 dark:text-gray-300 text-sm">${data.tratamiento || 'Sin tratamiento.'}</p>
            </div>
        </div>

        <div class="bg-gray-100 dark:bg-[#161430] border border-gray-200 dark:border-white/5 rounded-2xl p-5">
            <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4"><i class="fas fa-chart-area mr-1"></i> RPE Inteligente (Últimos 7 días)</h3>
            <div class="h-40 w-full relative"><canvas id="graficaImpacto"></canvas></div>
        </div>
    `;

    if (data.rpe_historico && Array.isArray(data.rpe_historico) && data.rpe_historico.length > 0 && typeof Chart !== 'undefined') {
        const ctx = document.getElementById('graficaImpacto')?.getContext('2d');
        if (ctx) {
            if (instanciaGrafica) instanciaGrafica.destroy();
            Chart.defaults.color = colors.texto;
            Chart.defaults.font.family = 'Inter';
            instanciaGrafica = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.rpe_fechas.map(f => formatearFecha(f)),
                    datasets: [{ 
                        label: 'RPE (0-10)', 
                        data: data.rpe_historico, 
                        borderColor: '#f59e0b', 
                        backgroundColor: 'rgba(245, 158, 11, 0.1)', 
                        tension: 0.3, 
                        fill: true,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: colors.texto
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { 
                        y: { 
                            min: 0, 
                            max: 10,
                            grid: { color: colors.grid },
                            ticks: { color: colors.texto }
                        },
                        x: {
                            grid: { color: colors.grid },
                            ticks: { color: colors.texto }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: colors.texto } }
                    }
                }
            });
        }
    } else {
        const graficaContainer = document.querySelector('#modalVer .bg-gray-100.dark\\:bg-\\[\\#161430\\].border.rounded-2xl.p-5');
        if (graficaContainer) {
            graficaContainer.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-8"><i class="fas fa-chart-line text-3xl mb-2"></i><br>No hay datos de RPE disponibles para este atleta en el período cercano a la lesión.</div>';
        }
    }
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    if (instanciaGrafica) {
        instanciaGrafica.destroy();
        instanciaGrafica = null;
    }
}

// =====================================================================
// INICIALIZACIÓN DE VALIDACIONES EN TIEMPO REAL
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formulario);
    }
    
    const camposFormulario = formulario.querySelectorAll('[data-validar]');
    camposFormulario.forEach(campo => {
        campo.addEventListener('input', () => validarCampoPersonalizado(campo));
        campo.addEventListener('change', () => validarCampoPersonalizado(campo));
        campo.addEventListener('blur', () => validarCampoPersonalizado(campo));
        
        if (campo.hasAttribute('data-depende')) {
            const dependencia = document.getElementById(campo.getAttribute('data-depende'));
            if (dependencia) {
                dependencia.addEventListener('change', () => validarCampoPersonalizado(campo));
            }
        }
    });
    
    // Toggle Papelera
    btnPapelera?.addEventListener('click', () => {
        modoPapelera = !modoPapelera;
        
        btnPapelera.innerHTML = modoPapelera ? '<i class="fas fa-folder-open"></i> Ver Activos' : '<i class="fas fa-trash-alt"></i> Ver Papelera';
        btnPapelera.classList.toggle('bg-red-50', !modoPapelera);
        btnPapelera.classList.toggle('bg-green-50', modoPapelera);
        btnPapelera.classList.toggle('dark:bg-red-500/20', !modoPapelera);
        btnPapelera.classList.toggle('dark:bg-green-500/20', modoPapelera);
        btnPapelera.classList.toggle('text-red-600', !modoPapelera);
        btnPapelera.classList.toggle('text-green-600', modoPapelera);
        btnPapelera.classList.toggle('dark:text-red-300', !modoPapelera);
        btnPapelera.classList.toggle('dark:text-green-300', modoPapelera);
        
         // Cambios visuales sobre la tabla
    const container = document.getElementById('tablaLesionesContainer');
    if (modoPapelera) {
        tituloTablaState.innerHTML = '<i class="fas fa-trash-alt"></i> Mostrando Papelera (Registros Anulados)';
        tituloTablaState.className = 'text-lg font-bold text-red-600 dark:text-red-400 mb-3 ml-2 flex items-center gap-2';
        if (container) {
            container.classList.remove('border-t-indigo-500');
            container.classList.add('border-t-red-500');
        }
    } else {
        tituloTablaState.innerHTML = '<i class="fas fa-check-circle"></i> Mostrando Registros Activos';
        tituloTablaState.className = 'text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2';
        if (container) {
            container.classList.remove('border-t-red-500');
            container.classList.add('border-t-indigo-500');
        }
    }
        
        cargarTabla();
    });
});