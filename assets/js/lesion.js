// =====================================================================
// CONFIGURACIÓN PRINCIPAL Y MAPEO DEL DOM
// =====================================================================
const modalFormulario = document.getElementById('modalFormulario');
const modalVer = document.getElementById('modalVer');
const formulario = document.getElementById('formularioLesion');
const btnGuardar = document.getElementById('btnGuardar');
const tablaCuerpo = document.getElementById('tablaCuerpo');

// Filtros
const filtroAtleta = document.getElementById('filtroAtleta');
const filtroTipo = document.getElementById('filtroTipo');
const filtroGravedad = document.getElementById('filtroGravedad');
const filtroEstado = document.getElementById('filtroEstado');

// Ruta al controlador pivote (ajusta 'lesion' según tu ruteo)
const API_URL = 'index.php?p=lesion';

// Variable global para almacenar el historial actual (para el modal de detalle)
let historialActual = [];

// Variable global para la gráfica (si se usa Chart.js)
let instanciaGrafica = null;

/**
 * Función centralizada para peticiones AJAX al servidor (Principio DRY)
 */
async function peticionAjax(accion, datos = null, metodo = 'GET') {
    let url = `${API_URL}&accion=${accion}`;
    const opciones = { method: metodo };
    
    if (datos) {
        if (metodo === 'POST' && !(datos instanceof FormData)) {
            opciones.body = JSON.stringify(datos);
            opciones.headers = { 'Content-Type': 'application/json' };
        } else {
            opciones.body = datos; // FormData
        }
    }

    try {
        const respuesta = await fetch(url, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        if (typeof UI !== 'undefined') {
            UI.error('Error Crítico', 'No se pudo procesar la solicitud con el servidor.');
        } else {
            alert('Error de red. Consulte la consola.');
        }
        return null;
    }
}

// =====================================================================
// MANEJO DE INTERFAZ Y MODALES
// =====================================================================

function abrirModal(id_lesion = null) {
    // Limpieza total del formulario
    formulario.reset();
    document.getElementById('id_lesion').value = '';
    document.getElementById('accion').value = 'registrar';
    document.getElementById('tituloModal').innerText = 'Registrar Nuevo Evento Médico';
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formulario);
    
    // Reseteamos el color y texto del botón
    btnGuardar.innerHTML = 'GUARDAR INFORME CLÍNICO <i class="fas fa-save ml-2"></i>';
    btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
    btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
    
    // Si recibimos un id_lesion, cargamos los datos para edición
    if (id_lesion) {
        cargarDatosParaEdicion(id_lesion);
    }
    
    // Mostramos el modal
    modalFormulario.classList.remove('hidden');
    // Pequeña animación si se desea (opcional)
    setTimeout(() => {
        modalFormulario.firstElementChild?.classList?.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarDatosParaEdicion(id_lesion) {
    // Mostrar estado de carga en el botón (opcional)
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando datos...';
    btnGuardar.disabled = true;
    
    const data = await peticionAjax(`obtenerDetalleLesion&id=${id_lesion}`);
    if (!data || data.status === 'error') {
        if (typeof UI !== 'undefined') UI.error('Error', data?.message || 'No se pudo cargar el registro para edición.');
        cerrarModal();
        return;
    }
    
    // Llenar campos del formulario
    document.getElementById('id_lesion').value = data.id_lesion;
    document.getElementById('accion').value = 'actualizar';
    document.getElementById('id_atleta').value = data.id_atleta;
    document.getElementById('fecha_lesion').value = data.fecha_lesion;
    document.getElementById('tipo_lesion').value = data.tipo_lesion;
    document.getElementById('zona_corporal').value = data.zona_corporal;
    document.getElementById('gravedad').value = data.gravedad;
    document.getElementById('diagnostico').value = data.diagnostico || '';
    document.getElementById('tratamiento').value = data.tratamiento || '';
    document.getElementById('dias_reposo_estimados').value = data.dias_reposo_estimados || '';
    document.getElementById('observaciones').value = data.observaciones || '';
    
    // Cambiar título y estilo del botón
    document.getElementById('tituloModal').innerText = 'Editar Informe Médico';
    btnGuardar.innerHTML = 'ACTUALIZAR INFORME <i class="fas fa-sync-alt ml-2"></i>';
    btnGuardar.classList.remove('bg-indigo-600', 'hover:bg-indigo-500');
    btnGuardar.classList.add('bg-emerald-600', 'hover:bg-emerald-500');
    
    btnGuardar.disabled = false;
}

function cerrarModal() {
    modalFormulario.classList.add('hidden');
    formulario.reset();
    if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formulario);
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    if (instanciaGrafica) {
        instanciaGrafica.destroy();
        instanciaGrafica = null;
    }
}

// =====================================================================
// CARGA DE DATOS (LECTURA)
// =====================================================================

/**
 * Carga los atletas en el select del formulario y en el filtro superior
 */
async function cargarAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    if (!atletas || !Array.isArray(atletas)) return;
    
    let opcionesForm = '<option value="">Seleccione el atleta...</option>';
    let opcionesFiltro = '<option value="">👤 Todos los Atletas</option>';
    
    atletas.forEach(atleta => {
        const texto = `${atleta.nombres} ${atleta.apellidos} - ${atleta.cedula}`;
        opcionesForm += `<option value="${atleta.id_atleta}">${texto}</option>`;
        opcionesFiltro += `<option value="${atleta.id_atleta}">${texto}</option>`;
    });
    
    document.getElementById('id_atleta').innerHTML = opcionesForm;
    if (filtroAtleta) filtroAtleta.innerHTML = opcionesFiltro;
}

/**
 * Carga la tabla de lesiones y actualiza los KPIs
 */
async function cargarTabla() {
    // Obtener valores de filtros
    const params = new URLSearchParams();
    if (filtroAtleta && filtroAtleta.value) params.append('id_atleta', filtroAtleta.value);
    if (filtroTipo && filtroTipo.value) params.append('tipo', filtroTipo.value);
    if (filtroGravedad && filtroGravedad.value) params.append('gravedad', filtroGravedad.value);
    if (filtroEstado && filtroEstado.value) params.append('estado', filtroEstado.value);
    
    // Mostrar carga
    tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">
        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando registros médicos...
    </td></tr>`;
    
    const registros = await peticionAjax(`listarLesiones&${params.toString()}`);
    historialActual = registros || [];
    
    if (!registros || registros.length === 0) {
        tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">
            No hay registros médicos con los filtros seleccionados.
        </td></tr>`;
        actualizarKPIs([]);
        return;
    }
    
    let filas = '';
    registros.forEach(reg => {
        // Estilos según gravedad
        let colorGravedad = 'text-gray-400 bg-gray-500/10';
        if (reg.gravedad === 'Leve') colorGravedad = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
        if (reg.gravedad === 'Moderada') colorGravedad = 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20';
        if (reg.gravedad === 'Grave') colorGravedad = 'text-red-400 bg-red-500/10 border-red-500/20';
        
        // Estado visual
        const estadoBadge = reg.estado === 'Activo' 
            ? '<span class="text-emerald-400 text-xs font-bold uppercase"><i class="fas fa-circle text-[8px] mr-1"></i> Activo</span>'
            : '<span class="text-red-400 text-xs font-bold uppercase"><i class="fas fa-circle text-[8px] mr-1"></i> Anulado</span>';
        
        // Botones de acción según permisos (se pueden controlar con PERMISOS_MODULO)
        const puedeEditar = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.editar && reg.estado === 'Activo';
        const puedeAnular = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.anular && reg.estado === 'Activo';
        const puedeEliminarFisico = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.eliminarFisico && reg.estado === 'Anulado';
        
        let botones = `
            <button onclick="verDetalle(${reg.id_lesion})" class="bg-[#252345] hover:bg-indigo-600 text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Ver Informe Completo">
                <i class="fas fa-eye text-xs"></i>
            </button>
        `;
        
        if (puedeEditar) {
            botones += `
                <button onclick="abrirModal(${reg.id_lesion})" class="bg-[#252345] hover:bg-amber-600 text-amber-400 hover:text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Editar Registro">
                    <i class="fas fa-edit text-xs"></i>
                </button>
            `;
        }
        
        if (puedeAnular) {
            botones += `
                <button onclick="anularRegistro(${reg.id_lesion})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Anular Registro (Baja lógica)">
                    <i class="fas fa-ban text-xs"></i>
                </button>
            `;
        }
        
        if (puedeEliminarFisico) {
            botones += `
                <button onclick="eliminarFisico(${reg.id_lesion})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg transition-colors cursor-pointer" title="Eliminar Físicamente (Permanente)">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            `;
        }
        
        filas += `
            <tr class="hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-medium text-white">${reg.fecha_lesion}</td>
                <td class="px-6 py-4 text-indigo-300">${reg.nombre_atleta || `ID: ${reg.id_atleta}`}</td>
                <td class="px-6 py-4">
                    <span class="block text-white font-semibold">${reg.tipo_lesion}</span>
                    <span class="text-xs text-gray-500">${reg.zona_corporal}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold border ${colorGravedad}">${reg.gravedad}</span>
                </td>
                <td class="px-6 py-4 text-gray-400">${reg.dias_reposo_estimados || 'N/A'} días</td>
                <td class="px-6 py-4 text-center">${estadoBadge}</td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">${botones}</td>
            </tr>
        `;
    });
    
    tablaCuerpo.innerHTML = filas;
    actualizarKPIs(registros);
}

/**
 * Calcula y actualiza los KPIs (tarjetas superiores)
 */
function actualizarKPIs(datos) {
    // Lesiones activas (solo las que tienen estado 'Activo')
    const activas = datos.filter(d => d.estado === 'Activo').length;
    // Casos graves (solo activos o todos? Definimos sobre los que están activos)
    const graves = datos.filter(d => d.estado === 'Activo' && d.gravedad === 'Grave').length;
    
    let totalReposo = 0;
    let registrosConReposo = 0;
    datos.forEach(d => {
        if (d.estado === 'Activo' && d.dias_reposo_estimados && !isNaN(d.dias_reposo_estimados)) {
            totalReposo += parseInt(d.dias_reposo_estimados);
            registrosConReposo++;
        }
    });
    const promReposo = registrosConReposo > 0 ? (totalReposo / registrosConReposo).toFixed(1) : 0;
    
    const kpiActivas = document.getElementById('kpi_activas');
    const kpiGraves = document.getElementById('kpi_graves');
    const kpiReposo = document.getElementById('kpi_reposo');
    if (kpiActivas) kpiActivas.innerText = activas;
    if (kpiGraves) kpiGraves.innerText = graves;
    if (kpiReposo) kpiReposo.innerText = promReposo;
}

// =====================================================================
// TRANSACCIONES (ESCRITURA, ANULACIÓN, ELIMINACIÓN FÍSICA)
// =====================================================================

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Validación frontend
    if (typeof Validador !== 'undefined') {
        const errores = Validador.validarFormulario(formulario);
        if (errores && errores.length > 0) {
            if (typeof UI !== 'undefined') {
                UI.advertencia('Campos Incompletos', errores.join('<br>'));
            } else {
                alert('Complete todos los campos requeridos.');
            }
            return;
        }
    }
    
    // Bloquear botón para evitar doble envío
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    btnGuardar.disabled = true;
    
    let datosEnvio = new FormData(formulario);
    const accion = document.getElementById('accion').value; // 'registrar' o 'actualizar'
    datosEnvio.set('accion', accion);
    
    const resultado = await peticionAjax(accion, datosEnvio, 'POST');
    
    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
    
    if (resultado && resultado.status === 'success') {
        if (typeof UI !== 'undefined') {
            UI.exito('Éxito', resultado.message || 'Operación realizada correctamente.');
        } else {
            alert('Operación exitosa');
        }
        cerrarModal();
        cargarTabla(); // Recargar tabla y KPIs
    } else {
        if (typeof UI !== 'undefined') {
            UI.error('Error', resultado?.message || 'Ocurrió un error inesperado.');
        } else {
            alert('Error: ' + (resultado?.message || 'Contacte al administrador'));
        }
    }
});

/**
 * Anulación lógica (cambia estado a 'Anulado')
 */
async function anularRegistro(id_lesion) {
    if (typeof UI === 'undefined' || typeof UI.pedirJustificacion !== 'function') {
        alert('Función de justificación no disponible. Consulte al administrador.');
        return;
    }
    
    const justificacion = await UI.pedirJustificacion(
        'Anular Registro Clínico',
        'Esta acción ocultará el registro para no afectar estadísticas. Motivo de la anulación:',
        'Ej. Error de diagnóstico, lesión no confirmada...'
    );
    
    if (justificacion.isConfirmed) {
        let datos = new FormData();
        datos.append('id_lesion', id_lesion);
        datos.append('motivo', justificacion.value);
        
        const resultado = await peticionAjax('anular', datos, 'POST');
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') UI.exito('Anulado', resultado.message);
            cargarTabla();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo anular el registro.');
        }
    }
}

/**
 * Eliminación física permanente (solo para registros ya anulados, con doble confirmación)
 */
async function eliminarFisico(id_lesion) {
    if (typeof UI === 'undefined') {
        if (confirm('¿Eliminar permanentemente? No se podrá recuperar.')) {
            let datos = new FormData();
            datos.append('id_lesion', id_lesion);
            const resultado = await peticionAjax('eliminarFisico', datos, 'POST');
            if (resultado && resultado.status === 'success') {
                alert('Eliminado permanentemente');
                cargarTabla();
            } else {
                alert('Error al eliminar');
            }
        }
        return;
    }
    
    const confirmacion = await UI.confirmar(
        'Eliminación Permanente',
        'Esta acción borrará el registro de la base de datos de forma irreversible. ¿Está completamente seguro?'
    );
    
    if (confirmacion.isConfirmed) {
        let datos = new FormData();
        datos.append('id_lesion', id_lesion);
        const resultado = await peticionAjax('eliminarFisico', datos, 'POST');
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', resultado.message);
            cargarTabla();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}

// =====================================================================
// MODAL DE VER DETALLE CON GRÁFICA DE IMPACTO (RPE)
// =====================================================================

async function verDetalle(id_lesion) {
    if (!modalVer) return;
    // Mostrar modal y estado de carga
    modalVer.classList.remove('hidden');
    const contenedorContenido = document.querySelector('#modalVer .p-8.md\\:p-10');
    if (contenedorContenido) {
        contenedorContenido.innerHTML = `
            <div class="flex justify-center items-center py-20">
                <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                <span class="ml-3 text-gray-400">Cargando ficha clínica...</span>
            </div>
        `;
    }
    
    const data = await peticionAjax(`obtenerDetalleLesion&id=${id_lesion}`);
    if (!data || data.status === 'error') {
        if (typeof UI !== 'undefined') UI.error('Error', data?.message || 'No se pudo obtener el detalle.');
        cerrarModalVer();
        return;
    }
    
    // Construir el contenido del modal de detalle
    const html = `
        <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-3xl shrink-0">
                <i class="fas fa-file-medical"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-white leading-tight">${data.nombre_atleta || 'Atleta'}</h2>
                <p class="text-indigo-400 font-medium flex items-center gap-2 mt-1">
                    <i class="fas fa-calendar-alt"></i> <span>${data.fecha_lesion}</span>
                </p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-[#161430] p-4 rounded-xl border border-white/5">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Zona Afectada</p>
                <p class="text-white font-medium">${data.tipo_lesion} / ${data.zona_corporal}</p>
            </div>
            <div class="bg-[#161430] p-4 rounded-xl border border-white/5">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Gravedad</p>
                <p class="text-white font-medium">${data.gravedad}</p>
            </div>
            <div class="col-span-2 bg-[#161430] p-4 rounded-xl border border-white/5">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Diagnóstico Clínico</p>
                <p class="text-gray-300">${data.diagnostico || 'Sin descripción.'}</p>
            </div>
            <div class="col-span-2 bg-[#161430] p-4 rounded-xl border border-white/5">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Tratamiento y Reposo</p>
                <p class="text-gray-300">${data.tratamiento || 'No indicado'} | ${data.dias_reposo_estimados || 0} días estimados</p>
            </div>
            <div class="col-span-2 bg-[#161430] p-4 rounded-xl border border-white/5">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Observaciones</p>
                <p class="text-gray-300">${data.observaciones || 'Ninguna.'}</p>
            </div>
        </div>
        <div class="bg-[#161430] border border-white/5 rounded-2xl p-5 mb-8" id="contenedorGraficaImpacto">
            <h3 class="text-sm font-bold text-gray-300 mb-4 uppercase tracking-wider">Impacto en la Carga (RPE) - Últimos 30 días</h3>
            <div class="h-48 w-full">
                <canvas id="graficaImpacto"></canvas>
            </div>
        </div>
        <div class="flex justify-end pt-4">
            <button onclick="cerrarModalVer()" class="bg-[#252345] hover:bg-white/10 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors cursor-pointer">
                Cerrar Ficha
            </button>
        </div>
    `;
    
    if (contenedorContenido) {
        contenedorContenido.innerHTML = html;
    } else {
        // Fallback: reemplazar todo el contenido interno del modal
        const modalInner = document.querySelector('#modalVer .relative.bg-\\[\\#111026\\]');
        if (modalInner) modalInner.innerHTML = `<div class="p-8 md:p-10">${html}</div>`;
    }
    
    // Si el controlador envía datos para la gráfica (por ejemplo, un array de valores RPE)
    if (data.rpe_historico && Array.isArray(data.rpe_historico) && typeof Chart !== 'undefined') {
        const ctx = document.getElementById('graficaImpacto')?.getContext('2d');
        if (ctx) {
            if (instanciaGrafica) instanciaGrafica.destroy();
            instanciaGrafica = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.rpe_fechas || data.rpe_historico.map((_, i) => `Día ${i+1}`),
                    datasets: [{
                        label: 'RPE (0-10)',
                        data: data.rpe_historico,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { min: 0, max: 10, ticks: { stepSize: 1 } } }
                }
            });
        }
    } else {
        // Mostrar mensaje de que no hay datos de RPE
        const contGrafica = document.getElementById('contenedorGraficaImpacto');
        if (contGrafica) {
            contGrafica.innerHTML = '<div class="text-center text-gray-400 py-4">No hay datos de carga (RPE) disponibles para este período.</div>';
        }
    }
}

// =====================================================================
// INICIALIZADOR AL CARGAR EL DOM
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Vincular validaciones en tiempo real si existe Validador
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formulario);
    }
    
    // Cargar atletas en selects
    cargarAtletas();
    
    // Cargar tabla inicial (con filtros por defecto: estado Activo)
    if (filtroEstado) filtroEstado.value = 'Activo';
    cargarTabla();
    
    // Eventos de filtros
    if (filtroAtleta) filtroAtleta.addEventListener('change', cargarTabla);
    if (filtroTipo) filtroTipo.addEventListener('change', cargarTabla);
    if (filtroGravedad) filtroGravedad.addEventListener('change', cargarTabla);
    if (filtroEstado) filtroEstado.addEventListener('change', cargarTabla);
    
    // Botón de refrescar manual (si existe)
    const btnRefresh = document.querySelector('button[onclick="cargarTabla()"]');
    if (btnRefresh) btnRefresh.onclick = cargarTabla;
});