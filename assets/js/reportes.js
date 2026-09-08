const API_URL = 'index.php?p=reportes';

let reporteActivo = null;
let graficaActual = null;
let datosGlobales = [];
let atletasCache = [];
let gruposCache = [];
let categoriasCache = [];
let entrenadoresCache = [];

const ESTILOS = ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado'];
const DISTANCIAS = [50, 100, 200, 400, 800, 1500];
const PISCINAS = ['50m', '25m'];

const TITULOS = {
    evolucion_marcas: { titulo: 'Evolucion de Marcas', sub: 'Progresion temporal de tiempos por prueba' },
    asistencia_grupo: { titulo: 'Asistencia por Grupo', sub: 'Resumen de asistencia por atleta' },
    volumen_semanal: { titulo: 'Volumen Semanal', sub: 'Metros planificados vs ejecutados por semana' },
    carga_srpe: { titulo: 'Monitoreo de Carga (sRPE)', sub: 'Carga subjetiva, sueno y bienestar diario' },
    ficha_atleta: { titulo: 'Ficha del Atleta', sub: 'Hoja de vida completa - Descarga directa en PDF' },
    lista_atletas: { titulo: 'Lista de Atletas', sub: 'Directorio completo - Descarga directa en PDF' },
    lista_representantes: { titulo: 'Lista de Representantes', sub: 'Directorio de representantes - Descarga directa en PDF' },
    lista_entrenadores: { titulo: 'Lista de Entrenadores', sub: 'Directorio completo de entrenadores - Descarga directa en PDF' },
    lista_grupos: { titulo: 'Lista de Grupos', sub: 'Directorio completo de grupos de entrenamiento - Descarga directa en PDF' },
    detalle_grupo: { titulo: 'Detalle del Grupo', sub: 'Informacion completa del grupo con lista de atletas asignados - Descarga directa en PDF' },
    detalle_sesion: { titulo: 'Detalle de Sesion', sub: 'Informacion completa de la sesion con series y volumen - Descarga directa en PDF' },
    resumen_sesiones_grupo: { titulo: 'Resumen de Sesiones por Grupo', sub: 'Resumen de volumen y sesiones por grupo en un periodo - Descarga directa en PDF' }
};

async function peticionAjax(accion, params = {}) {
    let url = API_URL + '&accion=' + accion;
    Object.entries(params).forEach(function([k, v]) { url += '&' + k + '=' + encodeURIComponent(v); });
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    } catch (e) {
        console.error('Error fetch:', e);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

async function cargarSelects() {
    const [atletas, grupos, categorias, entrenadores] = await Promise.all([
        peticionAjax('select_atletas'),
        peticionAjax('select_grupos'),
        peticionAjax('select_categorias'),
        peticionAjax('select_entrenadores')
    ]);
    if (atletas) atletasCache = atletas;
    if (grupos) gruposCache = grupos;
    if (categorias) categoriasCache = categorias;
    if (entrenadores) entrenadoresCache = entrenadores;
}

function opcionesAtletas(selected) {
    selected = selected || '';
    return '<option value="">Seleccione un atleta...</option>' +
        atletasCache.map(a => '<option value="' + a.id_atleta + '" ' + (a.id_atleta == selected ? 'selected' : '') + '>' + a.nombre_completo + '</option>').join('');
}

function opcionesGrupos(selected) {
    selected = selected || '';
    return '<option value="">Seleccione un grupo...</option>' +
        gruposCache.map(g => '<option value="' + g.id_grupo + '" ' + (g.id_grupo == selected ? 'selected' : '') + '>' + g.nombre + '</option>').join('');
}

function opcionesCategorias(selected) {
    selected = selected || '';
    return '<option value="">Todas</option>' +
        categoriasCache.map(c => '<option value="' + c.id_categoria + '" ' + (c.id_categoria == selected ? 'selected' : '') + '>' + c.nombre + '</option>').join('');
}

function opcionesEstilos() {
    return ESTILOS.map(e => '<option value="' + e + '">' + e + '</option>').join('');
}

function opcionesDistancias() {
    return DISTANCIAS.map(d => '<option value="' + d + '">' + d + 'm</option>').join('');
}

function opcionesPiscinas() {
    return PISCINAS.map(p => '<option value="' + p + '">Piscina ' + p + '</option>').join('');
}

function opcionesEstados(selected) {
    selected = selected || '';
    var estados = ['Activo', 'Inactivo', 'Retirado', 'Transferido'];
    return '<option value="">Todos</option>' +
        estados.map(e => '<option value="' + e + '" ' + (e === selected ? 'selected' : '') + '>' + e + '</option>').join('');
}

function opcionesEstadosRep(selected) {
    selected = selected || 'Activo';
    return '<option value="Activo" ' + (selected === 'Activo' ? 'selected' : '') + '>Activo</option>' +
        '<option value="Inactivo" ' + (selected === 'Inactivo' ? 'selected' : '') + '>Inactivo</option>';
}

function opcionesEstadosSesion(selected) {
    selected = selected || '';
    return '<option value="">Todos</option>' +
        '<option value="Planificada" ' + (selected === 'Planificada' ? 'selected' : '') + '>Planificada</option>' +
        '<option value="Parcial" ' + (selected === 'Parcial' ? 'selected' : '') + '>Parcial</option>' +
        '<option value="Completada" ' + (selected === 'Completada' ? 'selected' : '') + '>Completada</option>' +
        '<option value="Cancelada" ' + (selected === 'Cancelada' ? 'selected' : '') + '>Cancelada</option>';
}

function fechaHoy() { return new Date().toISOString().split('T')[0]; }

function fechaHaceUnMes() {
    var d = new Date();
    d.setMonth(d.getMonth() - 1);
    return d.toISOString().split('T')[0];
}

function labelFiltro(label, forId, contenido) {
    return '<div class="space-y-1.5"><label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest" for="' + forId + '">' + label + '</label>' + contenido + '</div>';
}

function selectHtml(id, opciones) {
    return '<select id="' + id + '" class="input-adapt w-full p-3 rounded-xl text-sm">' + opciones + '</select>';
}

function inputFecha(id, valor) {
    return '<input type="date" id="' + id + '" value="' + valor + '" class="input-adapt w-full p-3 rounded-xl text-sm">';
}

function botonAplicar() {
    return '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="aplicarFiltros()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-filter"></i> Aplicar Filtros</button></div>';
}

function mostrarReporte(tipo) {
    reporteActivo = tipo;
    var info = TITULOS[tipo];
    document.getElementById('gridCards').classList.add('hidden');
    var seccion = document.getElementById('seccionReporte');
    seccion.classList.remove('hidden');
    document.getElementById('tituloReporte').textContent = info.titulo;
    document.getElementById('subtituloReporte').textContent = info.sub;

    if (tipo === 'ficha_atleta') {
        renderFiltrosFichaAtleta();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'lista_atletas') {
        renderFiltrosListaAtletas();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'lista_representantes') {
        renderFiltrosListaRepresentantes();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'lista_entrenadores') {
        renderFiltrosListaEntrenadores();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'lista_grupos') {
        renderFiltrosListaGrupos();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'detalle_grupo') {
        renderFiltrosDetalleGrupo();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'detalle_sesion') {
        renderFiltrosDetalleSesion();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    if (tipo === 'resumen_sesiones_grupo') {
        renderFiltrosResumenSesionesGrupo();
        document.getElementById('contenedorGrafica').classList.add('hidden');
        document.getElementById('contenedorTabla').classList.add('hidden');
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('btnDescargarPDF').classList.remove('hidden');
        return;
    }

    document.getElementById('btnDescargarPDF').classList.remove('hidden');
    renderFiltros(tipo);
    seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function volverASelector() {
    if (graficaActual) { graficaActual.destroy(); graficaActual = null; }
    datosGlobales = [];
    reporteActivo = null;
    document.getElementById('gridCards').classList.remove('hidden');
    document.getElementById('seccionReporte').classList.add('hidden');
    document.getElementById('contenedorFiltros').innerHTML = '';
    document.getElementById('theadReporte').innerHTML = '';
    document.getElementById('tbodyReporte').innerHTML = '';
    document.getElementById('contenedorGrafica').classList.add('hidden');
    document.getElementById('contenedorTabla').classList.add('hidden');
    document.getElementById('estadoVacio').classList.add('hidden');
}

function renderFiltros(tipo) {
    var c = document.getElementById('contenedorFiltros');
    var h = '';
    if (tipo === 'evolucion_marcas') {
        h = labelFiltro('Atleta', 'fAtleta', selectHtml('fAtleta', opcionesAtletas()))
          + labelFiltro('Estilo', 'fEstilo', selectHtml('fEstilo', opcionesEstilos()))
          + labelFiltro('Distancia', 'fDistancia', selectHtml('fDistancia', opcionesDistancias()))
          + labelFiltro('Piscina', 'fPiscina', selectHtml('fPiscina', opcionesPiscinas()))
          + labelFiltro('Desde', 'fFechaIni', inputFecha('fFechaIni', fechaHaceUnMes()))
          + labelFiltro('Hasta', 'fFechaFin', inputFecha('fFechaFin', fechaHoy()))
          + botonAplicar();
    } else if (tipo === 'asistencia_grupo' || tipo === 'volumen_semanal') {
        h = labelFiltro('Grupo', 'fGrupo', selectHtml('fGrupo', opcionesGrupos()))
          + labelFiltro('Desde', 'fFechaIni', inputFecha('fFechaIni', fechaHaceUnMes()))
          + labelFiltro('Hasta', 'fFechaFin', inputFecha('fFechaFin', fechaHoy()))
          + botonAplicar();
    } else if (tipo === 'carga_srpe') {
        h = labelFiltro('Grupo', 'fGrupo', selectHtml('fGrupo', opcionesGrupos()))
          + labelFiltro('O Atleta', 'fAtleta', selectHtml('fAtleta', opcionesAtletas()))
          + labelFiltro('Desde', 'fFechaIni', inputFecha('fFechaIni', fechaHaceUnMes()))
          + labelFiltro('Hasta', 'fFechaFin', inputFecha('fFechaFin', fechaHoy()))
          + botonAplicar();
    }
    c.innerHTML = h;
}

function renderFiltrosFichaAtleta() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Atleta', 'fAtleta', selectHtml('fAtleta', opcionesAtletas()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarFichaDirecta()" class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-purple-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosListaAtletas() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Grupo', 'fGrupo', selectHtml('fGrupo', opcionesGrupos()))
        + labelFiltro('Categoria', 'fCategoria', selectHtml('fCategoria', opcionesCategorias()))
        + labelFiltro('Estado', 'fEstado', selectHtml('fEstado', opcionesEstados()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarListaAtletasDirecta()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosListaRepresentantes() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Estado', 'fEstado', selectHtml('fEstado', opcionesEstadosRep()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarListaRepresentantesDirecta()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosListaEntrenadores() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Entrenador', 'fEntrenador', selectHtml('fEntrenador', opcionesEntrenadores()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarListaEntrenadoresDirecta()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosListaGrupos() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Estado', 'fEstadoGrupo', selectHtml('fEstadoGrupo', opcionesEstadosGrupo()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarListaGruposDirecta()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosDetalleGrupo() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Grupo', 'fGrupoDetalle', selectHtml('fGrupoDetalle', opcionesGruposDetalle()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarDetalleGrupoDirecta()" class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-purple-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosDetalleSesion() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Sesion', 'fSesionDetalle', selectHtml('fSesionDetalle', opcionesSesionesDetalle()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="descargarDetalleSesionDirecta()" class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-amber-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-file-pdf"></i> Generar y Descargar PDF</button></div>';
}

function renderFiltrosResumenSesionesGrupo() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Grupo', 'fGrupo', selectHtml('fGrupo', opcionesGrupos()))
        + labelFiltro('Estado', 'fEstadoSesion', selectHtml('fEstadoSesion', opcionesEstadosSesion()))
        + labelFiltro('Desde', 'fFechaIni', inputFecha('fFechaIni', fechaHaceUnMes()))
        + labelFiltro('Hasta', 'fFechaFin', inputFecha('fFechaFin', fechaHoy()))
        + '<div class="sm:col-span-2 lg:col-span-4 flex justify-end"><button onclick="aplicarFiltros()" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"><i class="fas fa-filter"></i> Aplicar Filtros</button></div>';
}

function opcionesEntrenadores(selected) {
    selected = selected || '';
    return '<option value="">Todos</option>' +
        entrenadoresCache.map(e => '<option value="' + e.id_entrenador + '" ' + (e.id_entrenador == selected ? 'selected' : '') + '>' + e.nombre_completo + '</option>').join('');
}

function opcionesEstadosGrupo(selected) {
    selected = selected || 'Activo';
    return '<option value="Activo" ' + (selected === 'Activo' ? 'selected' : '') + '>Activos</option>' +
        '<option value="Inactivo" ' + (selected === 'Inactivo' ? 'selected' : '') + '>Archivados</option>' +
        '<option value="Todos" ' + (selected === 'Todos' ? 'selected' : '') + '>Todos</option>';
}

function opcionesGruposDetalle(selected) {
    selected = selected || '';
    return '<option value="">Seleccione un grupo...</option>' +
        gruposCache.map(g => '<option value="' + g.id_grupo + '" ' + (g.id_grupo == selected ? 'selected' : '') + '>' + g.nombre + '</option>').join('');
}

function opcionesSesionesDetalle(selected) {
    selected = selected || '';
    return '<option value="">Seleccione una sesion...</option>' +
        sesionesCache.map(s => '<option value="' + s.id_sesion + '" ' + (s.id_sesion == selected ? 'selected' : '') + '>' + s.fecha + ' - ' + s.grupo_nombre + ' (' + s.tipo_sesion + ')</option>').join('');
}

// Variable para cache de sesiones
let sesionesCache = [];

// Funciones para reportes de sesiones

async function descargarDetalleSesionDirecta() {
    var selSesion = document.getElementById('fSesionDetalle');
    if (!selSesion) return;
    
    var idSesion = selSesion.value;
    if (!idSesion) {
        UI.advertencia('Seleccion requerida', 'Debe seleccionar una sesion para generar el detalle.');
        return;
    }
    
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'detalle_sesion');
    form.append('id_sesion', idSesion);
    
    UI.exito('Generando PDF', 'El detalle de la sesion se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { 
            var err = await res.json(); 
            UI.error('Error', err.message || 'No se pudo generar el detalle.'); 
            return; 
        }
        
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); 
        a.href = url; 
        a.download = 'detalle_sesion_' + idSesion + '.pdf';
        document.body.appendChild(a); 
        a.click(); 
        document.body.removeChild(a); 
        URL.revokeObjectURL(url);
    } catch (e) { 
        console.error('Error Detalle Sesion:', e); 
        UI.error('Error', 'No se pudo generar el PDF.'); 
    }
}

async function cargarSesionesParaSelect() {
    try {
        const sesiones = await peticionAjax('listar_sesiones_select');
        if (sesiones && Array.isArray(sesiones)) {
            sesionesCache = sesiones;
        }
    } catch (e) {
        console.error('Error cargando sesiones:', e);
    }
}

// Aplicar filtros para resumen de sesiones
async function aplicarFiltros() {
    if (!reporteActivo) return;
    
    if (reporteActivo === 'resumen_sesiones_grupo') {
        var params = {};
        params.id_grupo = document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0;
        params.estado = document.getElementById('fEstadoSesion') ? document.getElementById('fEstadoSesion').value : '';
        params.fecha_ini = document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '';
        params.fecha_fin = document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '';
        
        if (!params.id_grupo) {
            UI.advertencia('Filtros incompletos', 'Seleccione un grupo.');
            return;
        }
        
        var datos = await peticionAjax('resumen_sesiones_grupo', params);
        if (!datos) return;
        datosGlobales = datos;
        
        if (datos.length === 0) {
            document.getElementById('contenedorGrafica').classList.add('hidden');
            document.getElementById('contenedorTabla').classList.add('hidden');
            document.getElementById('estadoVacio').classList.remove('hidden');
            return;
        }
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('contenedorGrafica').classList.remove('hidden');
        document.getElementById('contenedorTabla').classList.remove('hidden');
        
        renderResumenSesionesGrupo(datos);
        return;
    }
    
    // Resto de filtros para otros reportes...
}

function renderResumenSesionesGrupo(datos) {
    destruirGrafica();
    var c = coloresTema();
    
    document.getElementById('tituloGrafica').textContent = 'Resumen de Sesiones por Grupo';
    document.getElementById('subtituloGrafica').textContent = 'Volumen y cantidad de sesiones en el periodo seleccionado';
    
    var labels = datos.map(function(d) { return d.fecha || d.semana || 'Sin fecha'; });
    var planificados = datos.map(function(d) { return parseInt(d.volumen_planificado) || 0; });
    var ejecutados = datos.map(function(d) { return parseInt(d.volumen_ejecutado) || 0; });
    
    graficaActual = new Chart(document.getElementById('graficaReporte').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Volumen Planificado (m)', data: planificados, backgroundColor: 'rgba(99,102,241,0.6)', borderRadius: 4 },
                { label: 'Volumen Ejecutado (m)', data: ejecutados, backgroundColor: 'rgba(16,185,129,0.6)', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: c.texto, font: { size: 10 }, boxWidth: 12 } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: c.texto, font: { size: 9 }, maxRotation: 45 } },
                y: { grid: { color: c.grid }, ticks: { color: c.texto }, beginAtZero: true }
            }
        }
    });
    
    document.getElementById('theadReporte').innerHTML = '<tr><th class="p-3">Fecha/Semana</th><th class="p-3 text-center">Tipo</th><th class="p-3 text-center">Estado</th><th class="p-3 text-center">Planificado</th><th class="p-3 text-center">Ejecutado</th><th class="p-3 text-center">Cumplimiento</th></tr>';
    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var pl = parseInt(d.volumen_planificado) || 0;
        var ej = parseInt(d.volumen_ejecutado) || 0;
        var pct = pl > 0 ? ((ej/pl)*100).toFixed(1) : '0.0';
        var clr = pct >= 95 ? 'text-emerald-500' : (pct >= 80 ? 'text-amber-500' : 'text-red-500');
        var estadoColor = d.estado === 'Completada' ? 'text-emerald-500' : (d.estado === 'Parcial' ? 'text-amber-500' : (d.estado === 'Planificada' ? 'text-indigo-500' : 'text-red-500'));
        return '<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">' +
            '<td class="p-3 text-gray-900 dark:text-white text-sm" data-label="Fecha">' + (d.fecha || d.semana || '-') + '</td>' +
            '<td class="p-3 text-center text-gray-600 dark:text-gray-400" data-label="Tipo">' + (d.tipo_sesion || '-') + '</td>' +
            '<td class="p-3 text-center font-bold ' + estadoColor + '" data-label="Estado">' + (d.estado || '-') + '</td>' +
            '<td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono" data-label="Planificado">' + pl.toLocaleString() + ' m</td>' +
            '<td class="p-3 text-center text-gray-900 dark:text-white font-mono font-bold" data-label="Ejecutado">' + ej.toLocaleString() + ' m</td>' +
            '<td class="p-3 text-center font-bold ' + clr + '" data-label="Cumplimiento">' + pct + '%</td>' +
            '</tr>';
    }).join('');
}

// Funciones existentes de descarga...
async function descargarPDF() {
    if (!reporteActivo) return;
    if (reporteActivo === 'ficha_atleta') { await descargarFichaDirecta(); return; }
    if (reporteActivo === 'lista_atletas') { await descargarListaAtletasDirecta(); return; }
    if (reporteActivo === 'lista_representantes') { await descargarListaRepresentantesDirecta(); return; }
    if (reporteActivo === 'lista_entrenadores') { await descargarListaEntrenadoresDirecta(); return; }
    if (reporteActivo === 'lista_grupos') { await descargarListaGruposDirecta(); return; }
    if (reporteActivo === 'detalle_grupo') { await descargarDetalleGrupoDirecta(); return; }
    if (reporteActivo === 'detalle_sesion') { await descargarDetalleSesionDirecta(); return; }
    if (reporteActivo === 'resumen_sesiones_grupo') {
        // Para resumen de sesiones, ya se genera con el botón de aplicar filtros
        // Pero podemos descargar el PDF con los datos actuales
        var canvas = document.getElementById('graficaReporte');
        var img = (canvas && graficaActual) ? canvas.toDataURL('image/png') : '';
        var form = new FormData();
        form.append('accion', 'generar_pdf');
        form.append('tipo_reporte', 'resumen_sesiones_grupo');
        form.append('grafica_imagen', img);
        form.append('id_grupo', document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0);
        form.append('estado', document.getElementById('fEstadoSesion') ? document.getElementById('fEstadoSesion').value : '');
        form.append('fecha_ini', document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '');
        form.append('fecha_fin', document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '');
        
        UI.exito('Generando PDF', 'El reporte se descargara automaticamente.');
        try {
            var res = await fetch(API_URL, { method: 'POST', body: form });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var blob = await res.blob();
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a'); a.href = url;
            a.download = 'resumen_sesiones_' + new Date().toISOString().slice(0,10) + '.pdf';
            document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
        } catch (e) { console.error('Error PDF:', e); UI.error('Error', 'No se pudo generar el PDF.'); }
        return;
    }

    var canvas = document.getElementById('graficaReporte');
    var img = (canvas && graficaActual) ? canvas.toDataURL('image/png') : '';

    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', reporteActivo);
    form.append('grafica_imagen', img);

    if (reporteActivo === 'evolucion_marcas') {
        form.append('id_atleta', document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : 0);
        form.append('estilo', document.getElementById('fEstilo') ? document.getElementById('fEstilo').value : '');
        form.append('distancia', document.getElementById('fDistancia') ? document.getElementById('fDistancia').value : 0);
        form.append('piscina', document.getElementById('fPiscina') ? document.getElementById('fPiscina').value : '');
        form.append('fecha_ini', document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '');
        form.append('fecha_fin', document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '');
    } else if (reporteActivo === 'asistencia_grupo' || reporteActivo === 'volumen_semanal') {
        form.append('id_grupo', document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0);
        form.append('fecha_ini', document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '');
        form.append('fecha_fin', document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '');
    } else if (reporteActivo === 'carga_srpe') {
        form.append('id_grupo', document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0);
        form.append('id_atleta', document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : 0);
        form.append('fecha_ini', document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '');
        form.append('fecha_fin', document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '');
    }

    UI.exito('Generando PDF', 'El reporte se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); a.href = url;
        a.download = 'reporte_' + reporteActivo + '_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    } catch (e) { console.error('Error PDF:', e); UI.error('Error', 'No se pudo generar el PDF.'); }
}

// Resto de funciones de descarga...
async function descargarFichaDirecta() {
    var idAtleta = document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : '';
    if (!idAtleta) { UI.advertencia('Filtros incompletos', 'Seleccione un atleta.'); return; }
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'ficha_atleta');
    form.append('id_atleta', idAtleta);
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { var err = await res.json(); UI.error('Error', err.message || 'No se pudo generar la ficha.'); return; }
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); a.href = url; a.download = 'ficha_atleta_' + idAtleta + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    } catch (e) { console.error('Error Ficha:', e); UI.error('Error', 'No se pudo generar la ficha.'); }
}

async function descargarListaAtletasDirecta() {
    var selGrupo = document.getElementById('fGrupo');
    var selCat = document.getElementById('fCategoria');
    var selEstado = document.getElementById('fEstado');
    if (!selGrupo || !selCat || !selEstado) return;
    var grupoNombre = selGrupo.options[selGrupo.selectedIndex] ? selGrupo.options[selGrupo.selectedIndex].text : '';
    var catNombre = selCat.options[selCat.selectedIndex] ? selCat.options[selCat.selectedIndex].text : '';
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'lista_atletas');
    form.append('id_grupo', selGrupo.value);
    form.append('id_categoria', selCat.value);
    form.append('estado', selEstado.value);
    form.append('grupo_nombre', grupoNombre);
    form.append('categoria_nombre', catNombre);
    UI.exito('Generando PDF', 'El reporte se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { var err = await res.json(); UI.error('Error', err.message || 'No se pudo generar la lista.'); return; }
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); a.href = url; a.download = 'lista_atletas_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    } catch (e) { console.error('Error Lista Atletas:', e); UI.error('Error', 'No se pudo generar el PDF.'); }
}

async function descargarListaRepresentantesDirecta() {
    var selEstado = document.getElementById('fEstado');
    if (!selEstado) return;
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'lista_representantes');
    form.append('estado', selEstado.value);
    UI.exito('Generando PDF', 'El reporte se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { var err = await res.json(); UI.error('Error', err.message || 'No se pudo generar la lista.'); return; }
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); a.href = url; a.download = 'lista_representantes_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    } catch (e) { console.error('Error Lista Representantes:', e); UI.error('Error', 'No se pudo generar el PDF.'); }
}

async function descargarListaEntrenadoresDirecta() {
    var selEntrenador = document.getElementById('fEntrenador');
    if (!selEntrenador) return;
    
    var idEntrenador = selEntrenador.value;
    if (!idEntrenador) {
        UI.advertencia('Seleccione un entrenador', 'Para descargar la ficha individual, debe seleccionar un entrenador especifico.');
        return;
    }
    
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'lista_entrenadores');
    form.append('id_entrenador', idEntrenador);
    
    UI.exito('Generando PDF', 'La ficha del entrenador se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        var ct = res.headers.get('content-type');
        if (!ct || !ct.includes('application/pdf')) { 
            var textoError = await res.text();
            console.error('ERROR DEL SERVIDOR:', textoError);
            UI.error('Error', 'El servidor devolvio un error en lugar del PDF.');
            return; 
        }

        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); 
        a.href = url;
        a.download = 'ficha_entrenador_' + idEntrenador + '.pdf';
        document.body.appendChild(a); 
        a.click(); 
        document.body.removeChild(a); 
        URL.revokeObjectURL(url);
    } catch (e) { 
        console.error('Error en descarga:', e); 
        UI.error('Error', 'No se pudo generar el PDF.'); 
    }
}

async function descargarListaGruposDirecta() {
    var selEstado = document.getElementById('fEstadoGrupo');
    if (!selEstado) return;
    
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'lista_grupos');
    form.append('estado', selEstado.value);
    
    UI.exito('Generando PDF', 'El reporte se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { 
            var err = await res.json(); 
            UI.error('Error', err.message || 'No se pudo generar el listado.'); 
            return; 
        }
        
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); 
        a.href = url; 
        a.download = 'lista_grupos_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a); 
        a.click(); 
        document.body.removeChild(a); 
        URL.revokeObjectURL(url);
    } catch (e) { 
        console.error('Error Lista Grupos:', e); 
        UI.error('Error', 'No se pudo generar el PDF.'); 
    }
}

async function descargarDetalleGrupoDirecta() {
    var selGrupo = document.getElementById('fGrupoDetalle');
    if (!selGrupo) return;
    
    var idGrupo = selGrupo.value;
    if (!idGrupo) {
        UI.advertencia('Seleccion requerida', 'Debe seleccionar un grupo para generar el detalle.');
        return;
    }
    
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'detalle_grupo');
    form.append('id_grupo', idGrupo);
    
    UI.exito('Generando PDF', 'El detalle del grupo se descargara automaticamente.');
    try {
        var res = await fetch(API_URL, { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        
        var ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) { 
            var err = await res.json(); 
            UI.error('Error', err.message || 'No se pudo generar el detalle.'); 
            return; 
        }
        
        var blob = await res.blob();
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); 
        a.href = url; 
        a.download = 'detalle_grupo_' + idGrupo + '.pdf';
        document.body.appendChild(a); 
        a.click(); 
        document.body.removeChild(a); 
        URL.revokeObjectURL(url);
    } catch (e) { 
        console.error('Error Detalle Grupo:', e); 
        UI.error('Error', 'No se pudo generar el PDF.'); 
    }
}

document.addEventListener('DOMContentLoaded', function() {
    cargarSelects();
    cargarSesionesParaSelect();
});