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
let instanciaGrafica = null;
let modoPapelera = false; // Estado: false = Activos, true = Inactivos (Papelera)

// ---------------------------------------------------------------------
// Petición AJAX Centralizada (Fetch API)
// ---------------------------------------------------------------------
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
// INICIALIZACIÓN REACTIVA (Actualización automática al cambiar filtros)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) {
        Validador.vincularTiempoReal(formulario);
    }
    
    cargarAtletas();
    cargarTabla();
    
    // Vinculación de eventos para filtrado reactivo (automático)
    // Al cambiar cualquier select, se dispara la carga de la tabla instantáneamente
    if (filtroAtleta) filtroAtleta.addEventListener('change', cargarTabla);
    if (filtroTipo) filtroTipo.addEventListener('change', cargarTabla);
    if (filtroZona) filtroZona.addEventListener('change', cargarTabla);
    if (filtroEstadoClinico) filtroEstadoClinico.addEventListener('change', cargarTabla);
 
});

// ---------------------------------------------------------------------
// Carga de Datos Iniciales (Atletas y Tabla)
// ---------------------------------------------------------------------
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

async function cargarTabla() {
    const params = new URLSearchParams();
    if (filtroAtleta?.value) params.append('id_atleta', filtroAtleta.value);
    if (filtroTipo?.value) params.append('tipo', filtroTipo.value);
    if (filtroZona?.value) params.append('zona', filtroZona.value);
    if (filtroEstadoClinico?.value) params.append('estado', filtroEstadoClinico.value);
    
    // CORRECCIÓN: Le decimos al controlador el modo exacto que queremos consultar
    params.append('modo', modoPapelera ? 'papelera' : 'activos');
    
    tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Cargando registros...</td></tr>`;
    
    const registros = await peticionAjax(`listarLesiones&${params.toString()}`);
    
    if (!registros || registros.length === 0) {
        tablaCuerpo.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-folder-open mb-2 text-2xl"></i><br>No hay registros que coincidan con los filtros en este apartado.</td></tr>`;
        actualizarKPIs([]);
        return;
    }
    
    let filas = '';
    registros.forEach(reg => {
        // Estilos de molestia
        let colorMolestia = reg.nivel_molestia >= 8 ? 'text-red-400 bg-red-500/10' : 
                           (reg.nivel_molestia >= 5 ? 'text-yellow-400 bg-yellow-500/10' : 'text-emerald-400 bg-emerald-500/10');
        
        // Etiqueta de Estado Clínico
        const iconos = { 'Activa':'🟢', 'EnRehabilitacion':'🟡', 'Recuperada':'✅', 'Cronica':'⚠️' };
        const lblEstado = iconos[reg.estado] ? `${iconos[reg.estado]} ${reg.estado.replace('EnRehabilitacion', 'En Rehab.')}` : reg.estado;

        // Etiqueta de Estado de BD (Activo / Papelera)
        const visibleBadge = reg.activo == 1 
            ? '<span class="text-green-400 bg-green-500/10 px-2 py-1 rounded text-xs"><i class="fas fa-check"></i> Activo</span>'
            : '<span class="text-red-400 bg-red-500/10 px-2 py-1 rounded text-xs" title="'+(reg.motivo_eliminacion||'')+'"><i class="fas fa-trash-alt"></i> Papelera</span>';
        
        // Generación de botones basada en permisos y reglas de negocio
        let botones = `<button onclick="verDetalle(${reg.id_lesion})" class="bg-[#252345] hover:bg-indigo-600 text-white w-8 h-8 rounded-lg transition-colors" title="Ficha Médica"><i class="fas fa-eye text-xs"></i></button>`;
        
        if (reg.activo == 1) { 
            // MODO ACTIVOS
            
            // CORRECCIÓN: Si el estado es "Recuperada", no se renderiza el botón editar
            if (PERMISOS_MODULO.editar && reg.estado !== 'Recuperada') {
                botones += `<button onclick="abrirModal(${reg.id_lesion})" class="bg-[#252345] hover:bg-amber-600 text-amber-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Editar"><i class="fas fa-edit text-xs"></i></button>`;
            }
            
            // CORRECCIÓN: Botón "Anular" usa el permiso "eliminar"
            if (PERMISOS_MODULO.eliminar) {
                botones += `<button onclick="softDelete(${reg.id_lesion})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Eliminado Lógico (Papelera)"><i class="fas fa-trash-alt text-xs"></i></button>`;
            }
            
        } else { 
            // MODO PAPELERA
            if (PERMISOS_MODULO.reactivar) botones += `<button onclick="reactivar(${reg.id_lesion})" class="bg-[#252345] hover:bg-green-600 text-green-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Restaurar de la papelera"><i class="fas fa-undo-alt text-xs"></i></button>`;
            if (PERMISOS_MODULO.eliminardb) botones += `<button onclick="eliminarFisico(${reg.id_lesion})" class="bg-[#252345] hover:bg-red-600 text-red-400 hover:text-white w-8 h-8 rounded-lg ml-1 transition-colors" title="Destrucción total"><i class="fas fa-skull-crossbones text-xs"></i></button>`;
        }
        
        filas += `
            <tr class="hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-medium text-white">${formatearFecha(reg.fecha_inicio)}</td>
                <td class="px-6 py-4 text-indigo-300 font-semibold">${reg.nombre_atleta}</td>
                <td class="px-6 py-4">${reg.zona_anatomica} ${reg.lado ? '('+reg.lado+')' : ''}<br><span class="text-xs text-gray-500">${reg.tipo}</span></td>
                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold border border-current ${colorMolestia}">${reg.nivel_molestia}/10</span></td>
                <td class="px-6 py-4">${lblEstado}</td>
                <td class="px-6 py-4 text-center">${visibleBadge}</td>
                <td class="px-6 py-4 text-right flex justify-end gap-1">${botones}</td>
            </tr>`;
    });
    
    tablaCuerpo.innerHTML = filas;
    actualizarKPIs(registros.filter(r => r.activo == 1)); // KPIs solo cuentan los activos
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

// ---------------------------------------------------------------------
// Operaciones de Formulario (Registrar / Actualizar)
// ---------------------------------------------------------------------
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
    if (typeof Validador !== 'undefined' && Validador.validarFormulario(formulario).length) return;
    
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

// ---------------------------------------------------------------------
// Operaciones Lógicas y Físicas (Soft Delete / Destroy)
// ---------------------------------------------------------------------
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
        'Esta acción purgará el dato de la base de datos irreversiblemente. ¿Está completamente seguro?', 
        { icon: 'error', confirmButtonText: 'Sí, Purgar' }
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

// ---------------------------------------------------------------------
// Ficha de Detalle y Gráfica RPE
// ---------------------------------------------------------------------
async function verDetalle(id_lesion) {
    modalVer.classList.remove('hidden');
    const contenedor = document.querySelector('#modalVer #contenidoDetalle');
    contenedor.innerHTML = `<div class="text-center py-20"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>`;
    
    const data = await peticionAjax(`obtenerDetalleLesion&id=${id_lesion}`);
    if (!data || data.status === 'error') return cerrarModalVer();
    
    let advertenciaPapelera = data.activo == 0 
        ? `<div class="bg-red-500/20 border border-red-500/50 p-3 rounded-xl mb-4"><i class="fas fa-trash-alt text-red-400 mr-2"></i><strong class="text-red-400">Motivo de anulación:</strong> <span class="text-gray-300">${data.motivo_eliminacion}</span></div>` 
        : '';

    contenedor.innerHTML = `
        ${advertenciaPapelera}
        <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-3xl">
                <i class="fas fa-file-medical"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-white">${data.nombres} ${data.apellidos}</h2>
                <p class="text-indigo-400"><i class="fas fa-id-card"></i> ${data.cedula} | Inicio: ${formatearFecha(data.fecha_inicio)}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Zona</p><p class="text-white font-bold">${data.zona_anatomica}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Tipo</p><p class="text-white font-bold">${data.tipo}</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Molestia</p><p class="text-amber-400 font-bold">${data.nivel_molestia}/10</p></div>
            <div class="bg-[#161430] p-4 rounded-xl"><p class="text-[10px] text-gray-500 uppercase">Estado</p><p class="text-emerald-400 font-bold">${data.estado}</p></div>
            
            <div class="col-span-2 md:col-span-4 bg-[#161430] p-4 rounded-xl">
                <p class="text-[10px] text-gray-500 uppercase mb-1">Diagnóstico Clínico</p>
                <p class="text-gray-300 text-sm leading-relaxed">${data.diagnostico}</p>
            </div>
            <div class="col-span-2 md:col-span-4 bg-[#161430] p-4 rounded-xl border-l-2 border-indigo-500">
                <p class="text-[10px] text-gray-500 uppercase mb-1">Tratamiento Asignado por: ${data.profesional || 'No definido'}</p>
                <p class="text-gray-300 text-sm">${data.tratamiento || 'Sin tratamiento.'}</p>
            </div>
        </div>

        <div class="bg-[#161430] border border-white/5 rounded-2xl p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4"><i class="fas fa-chart-area mr-1"></i> RPE Inteligente (Últimos 7 días)</h3>
            <div class="h-40 w-full relative"><canvas id="graficaImpacto"></canvas></div>
        </div>
    `;

    // Renderizar Gráfica de RPE histórico
    // Dentro de verDetalle(), después de contenedor.innerHTML = ...
    if (data.rpe_historico && Array.isArray(data.rpe_historico) && data.rpe_historico.length > 0 && typeof Chart !== 'undefined') {
    const ctx = document.getElementById('graficaImpacto')?.getContext('2d');
    

    if (ctx) {
        if (instanciaGrafica) instanciaGrafica.destroy();
        instanciaGrafica = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.rpe_fechas.map(f => formatearFecha(f)),
                datasets: [{ label: 'RPE (0-10)', data: data.rpe_historico, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', tension: 0.3, fill: true }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 10 } } }
        });
    }
    } else {
    // Mostrar mensaje en el contenedor de la gráfica
    const graficaContainer = document.querySelector('#modalVer .bg-\\[\\#161430\\].border.rounded-2xl.p-5');
    if (graficaContainer) {
        graficaContainer.innerHTML = '<div class="text-center text-gray-400 py-8"><i class="fas fa-chart-line text-3xl mb-2"></i><br>No hay datos de RPE disponibles para este atleta en el período cercano a la lesión.</div>';
    }
    }
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    if (instanciaGrafica) instanciaGrafica.destroy();
}

// ---------------------------------------------------------------------
// INICIALIZACIÓN
// ---------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Validador !== 'undefined' && Validador.vincularTiempoReal) Validador.vincularTiempoReal(formulario);
    
    cargarAtletas();
    cargarTabla();
    
    // Toggle Papelera con cambio de título en la tabla
    btnPapelera?.addEventListener('click', () => {
        modoPapelera = !modoPapelera;
        
        // Cambios en el botón
        btnPapelera.innerHTML = modoPapelera ? '<i class="fas fa-folder-open"></i> Ver Activos' : '<i class="fas fa-trash-alt"></i> Ver Papelera';
        btnPapelera.classList.toggle('bg-red-500/20', !modoPapelera);
        btnPapelera.classList.toggle('bg-green-500/20', modoPapelera);
        btnPapelera.classList.toggle('text-red-300', !modoPapelera);
        btnPapelera.classList.toggle('text-green-300', modoPapelera);
        
        // Cambios visuales sobre la tabla
        if(modoPapelera) {
            tituloTablaState.innerHTML = '<i class="fas fa-trash-alt"></i> Mostrando Papelera (Registros Anulados)';
            tituloTablaState.className = 'text-lg font-bold text-red-400 mb-3 ml-2 flex items-center gap-2';
            document.querySelector('.tarjeta.overflow-hidden').classList.replace('border-t-indigo-500', 'border-t-red-500');
        } else {
            tituloTablaState.innerHTML = '<i class="fas fa-check-circle"></i> Mostrando Registros Activos';
            tituloTablaState.className = 'text-lg font-bold text-emerald-400 mb-3 ml-2 flex items-center gap-2';
            document.querySelector('.tarjeta.overflow-hidden').classList.replace('border-t-red-500', 'border-t-indigo-500');
        }
        
        cargarTabla();
    });
});