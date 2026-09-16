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

function fechaHace6Meses() {
    var d = new Date();
    d.setMonth(d.getMonth() - 6);
    return d.toISOString().split('T')[0];
}

function destruirGrafica() {
    if (graficaActual) {
        graficaActual.destroy();
        graficaActual = null;
    }
}

function coloresTema() {
    return document.documentElement.classList.contains('dark')
        ? { texto: '#e2e8f0', grid: '#334155' }
        : { texto: '#475569', grid: '#e2e8f0' };
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
          + labelFiltro('Desde', 'fFechaIni', inputFecha('fFechaIni', fechaHace6Meses()))
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
        labelFiltro('Atleta', 'fAtleta', selectHtml('fAtleta', opcionesAtletas()));
}

function renderFiltrosListaAtletas() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Grupo', 'fGrupo', selectHtml('fGrupo', opcionesGrupos()))
        + labelFiltro('Categoria', 'fCategoria', selectHtml('fCategoria', opcionesCategorias()))
        + labelFiltro('Estado', 'fEstado', selectHtml('fEstado', opcionesEstados()));
}

function renderFiltrosListaRepresentantes() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Estado', 'fEstado', selectHtml('fEstado', opcionesEstadosRep()));
}

function renderFiltrosListaEntrenadores() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Entrenador', 'fEntrenador', selectHtml('fEntrenador', opcionesEntrenadores()));
}

function renderFiltrosListaGrupos() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Estado', 'fEstadoGrupo', selectHtml('fEstadoGrupo', opcionesEstadosGrupo()));
}

function renderFiltrosDetalleGrupo() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Grupo', 'fGrupoDetalle', selectHtml('fGrupoDetalle', opcionesGruposDetalle()));
}

function renderFiltrosDetalleSesion() {
    document.getElementById('contenedorFiltros').innerHTML =
        labelFiltro('Sesion', 'fSesionDetalle', selectHtml('fSesionDetalle', opcionesSesionesDetalle()));
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
    }else if (reporteActivo === 'evolucion_marcas') {
        var paramsMarcas = {
            id_atleta: document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : 0,
            estilo: document.getElementById('fEstilo') ? document.getElementById('fEstilo').value : '',
            distancia: document.getElementById('fDistancia') ? document.getElementById('fDistancia').value : 0,
            piscina: document.getElementById('fPiscina') ? document.getElementById('fPiscina').value : '',
            fecha_ini: document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '',
            fecha_fin: document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : ''
        };

        if (!paramsMarcas.id_atleta || !paramsMarcas.estilo || !paramsMarcas.distancia) {
            UI.advertencia('Filtros incompletos', 'Atleta, Estilo y Distancia son obligatorios.');
            return;
        }

        // 1. Buscar el historial de marcas del atleta
        var datosMarcas = await peticionAjax('evolucion_marcas', paramsMarcas);
        if (!datosMarcas) return;
        datosGlobales = datosMarcas;

        if (datosMarcas.length === 0) {
            document.getElementById('contenedorGrafica').classList.add('hidden');
            document.getElementById('contenedorTabla').classList.add('hidden');
            document.getElementById('estadoVacio').classList.remove('hidden');
            return;
        }

        // 2. Novedad: Buscar la comparativa con la categoría del club
        var comparativa = await peticionAjax('comparativa_categoria', {
            id_atleta: paramsMarcas.id_atleta,
            estilo: paramsMarcas.estilo,
            distancia: paramsMarcas.distancia,
            piscina: paramsMarcas.piscina
        });
        
        document.getElementById('estadoVacio').classList.add('hidden');
        document.getElementById('contenedorGrafica').classList.remove('hidden');
        document.getElementById('contenedorTabla').classList.remove('hidden');
        
        // Pasamos ambos sets de datos al motor visual
        renderEvolucionMarcas(datosMarcas, comparativa);
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

/* function renderEvolucionMarcas(datos) {
    if (graficaActual) graficaActual.destroy();

    // 1. Ejecutar motor estadístico
    let stats = calcularEstadisticasMarcas(datos);
    
    // 2. Construir el Dashboard de KPIs y Alerta
    let tituloHTML = `
        <div class="flex justify-between items-center mb-1">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Análisis de Rendimiento</h3>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md font-bold">N = ${stats ? stats.n : 0} pruebas</span>
        </div>
        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4">Evaluación estadística y técnica del nadador.</p>
    `;

    if (stats) {
        // Lógica visual para KPIs
        let cvColor = stats.cv < 2.5 ? 'text-emerald-500' : (stats.cv > 5 ? 'text-red-500' : 'text-amber-500');
        let tendenciaIcono = stats.pendiente <= -0.05 ? '<i class="fas fa-arrow-down text-emerald-500"></i>' 
                           : (stats.pendiente >= 0.05 ? '<i class="fas fa-arrow-up text-red-500"></i>' 
                           : '<i class="fas fa-arrow-right text-amber-500"></i>');

        tituloHTML += `
            <!-- Semáforo de Toma de Decisiones -->
            <div class="mb-5 p-4 rounded-xl flex items-start gap-4 shadow-sm ${stats.estado.bg}">
                <div class="mt-1"><i class="fas ${stats.estado.icono} text-2xl"></i></div>
                <div>
                    <h4 class="font-black text-sm uppercase tracking-wider mb-1">Diagnóstico del Sistema</h4>
                    <p class="text-xs font-medium opacity-90 leading-relaxed">${stats.estado.texto}</p>
                </div>
            </div>

            <!-- Tarjetas KPI -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Mejor Tiempo (PB)</p>
                    <p class="text-xl font-black text-indigo-600 dark:text-indigo-400">${formatoTiempoJS(stats.min)}</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Promedio</p>
                    <p class="text-xl font-black text-gray-800 dark:text-white">${formatoTiempoJS(stats.media)}</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]" title="Coeficiente de Variación (<2.5% = Muy Regular)">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Estabilidad (CV%)</p>
                    <p class="text-xl font-black ${cvColor}">${stats.cv.toFixed(2)}%</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]" title="Tendencia de progresión en segundos por prueba">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Tendencia</p>
                    <p class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                        ${stats.pendiente.toFixed(2)}s ${tendenciaIcono}
                    </p>
                </div>
            </div>
        `;
    }

    // Inyectar títulos y KPIs antes de la gráfica
    document.getElementById('tituloGrafica').innerHTML = tituloHTML;
    document.getElementById('subtituloGrafica').textContent = '';

    // 3. Renderizar Gráfica
    var labels = datos.map(d => d.fecha);
    var tiempos = datos.map(d => parseFloat(d.tiempo_final_seg));
    var pointColors = datos.map(d => d.es_pb == 1 ? '#f59e0b' : '#4f46e5');
    var pointRadius = datos.map(d => d.es_pb == 1 ? 6 : 4);

    var ctx = document.getElementById('graficaReporte').getContext('2d');
    graficaActual = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tiempo (seg)',
                data: tiempos,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: pointColors,
                pointRadius: pointRadius,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var idx = context.dataIndex;
                            var esPb = datos[idx].es_pb == 1 ? ' 🏆 (PB)' : '';
                            return 'Tiempo: ' + formatoTiempoJS(context.raw) + esPb;
                        }
                    }
                }
            },
            scales: { x: { grid: { display: false } }, y: { title: { display: true, text: 'Segundos' } } }
        }
    });

    // 4. Renderizar Tabla Extendida (Aprovechando los nuevos datos del backend)
    document.getElementById('theadReporte').innerHTML = `
        <tr>
            <th class="p-3">Fecha</th>
            <th class="p-3 text-center">Contexto</th>
            <th class="p-3 text-center">Tiempo</th>
            <th class="p-3 text-center" title="Ritmo cada 100m">Ritmo/100m</th>
            <th class="p-3 text-center">Brazadas</th>
            <th class="p-3 text-center" title="Índice de Eficiencia">SWOLF</th>
            <th class="p-3">Observaciones</th>
        </tr>
    `;

    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var pbBadge = d.es_pb == 1 ? ' <span class="text-amber-500 ml-1" title="Personal Best"><i class="fas fa-trophy"></i></span>' : '';
        var contextoBadge = d.contexto === 'Competencia' ? '<span class="text-indigo-600 font-bold dark:text-indigo-400">Competición</span>' : '<span class="text-gray-500">Control</span>';
        
        // Datos técnicos (pueden venir nulos si no hubo registro de SWOLF)
        var ritmo = d.tiempo_100m ? formatoTiempoJS(d.tiempo_100m) : '-';
        var brazadas = d.num_brazadas ? d.num_brazadas : '-';
        var swolf = d.swolf ? d.swolf : '-';

        return `
            <tr onclick="verSplits(${d.id_marca}, '${d.fecha}', '${d.tiempo_final_seg}')" class="hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors border-b border-gray-100 dark:border-[#252345] cursor-pointer" title="Haga clic para ver los parciales (splits)">
                <td class="p-3 text-gray-900 dark:text-white text-sm whitespace-nowrap" data-label="Fecha">${d.fecha}</td>
                <td class="p-3 text-center text-xs" data-label="Contexto">${contextoBadge}</td>
                <td class="p-3 text-center text-gray-900 dark:text-white font-mono font-bold text-sm whitespace-nowrap" data-label="Tiempo">
                    ${formatoTiempoJS(d.tiempo_final_seg)} ${pbBadge}
                </td>
                <td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono text-xs" data-label="Ritmo/100m">${ritmo}</td>
                <td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono text-xs" data-label="Brazadas">${brazadas}</td>
                <td class="p-3 text-center font-bold text-teal-600 dark:text-teal-400 font-mono text-xs" data-label="SWOLF">${swolf}</td>
                <td class="p-3 text-gray-500 dark:text-gray-400 text-[11px]" data-label="Observaciones">${d.observaciones || 'Ninguna'}</td>
            </tr>
        `;
    }).join('');
} */


    function renderEvolucionMarcas(datos, comparativa) {
    if (graficaActual) graficaActual.destroy();

    // 1. Ejecutar motor estadístico para el atleta
    let stats = calcularEstadisticasMarcas(datos);
    
    // 2. Construir el Dashboard
    let tituloHTML = `
        <div class="flex justify-between items-center mb-1">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Análisis de Rendimiento y Splits</h3>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-md font-bold">N = ${stats ? stats.n : 0} pruebas</span>
        </div>
        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4">Haz clic sobre cualquier marca en la tabla inferior para ver su análisis de parciales (Splits).</p>
    `;

    if (stats) {
        // --- INICIO CÁLCULO DE COMPARATIVA (LA BARRA NUEVA) ---
        let barraComparativaHTML = '';
        if (comparativa && comparativa.promedio_categoria) {
            let pbAtleta = stats.min; // El mejor tiempo (PB) del atleta
            let promCat = parseFloat(comparativa.promedio_categoria);
            let recCat = parseFloat(comparativa.record_categoria);
            let evaluados = comparativa.atletas_evaluados;

            // En natación, si (Promedio - Mi Tiempo) es positivo, soy más rápido.
            let diffSeg = promCat - pbAtleta; 
            let diffPct = (diffSeg / promCat) * 100;
            
            let colorBarra = diffPct >= 0 
                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' 
                : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800';
            
            let iconoDir = diffPct >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
            
            let textoBarra = diffPct >= 0 
                ? `¡Estás un <strong>${diffPct.toFixed(1)}% por encima</strong> del promedio! (<strong>-${diffSeg.toFixed(2)}s</strong>)`
                : `Estás un <strong>${Math.abs(diffPct).toFixed(1)}% por debajo</strong> del promedio. (<strong>+${Math.abs(diffSeg).toFixed(2)}s</strong>)`;

            barraComparativaHTML = `
                <div class="mb-5 p-3 rounded-xl border flex flex-col md:flex-row items-start md:items-center justify-between shadow-sm ${colorBarra}">
                    <div class="flex items-center gap-3 mb-2 md:mb-0">
                        <div class="bg-white/50 dark:bg-black/20 p-2 rounded-lg"><i class="fas ${iconoDir} text-lg"></i></div>
                        <span class="text-xs font-medium">
                            Tu PB: <span class="font-black font-mono text-sm">${formatoTiempoJS(pbAtleta)}</span> <span class="mx-2 opacity-50">|</span>
                            Promedio Categoría: <span class="font-bold font-mono">${formatoTiempoJS(promCat)}</span> 
                            <span class="text-[10px] opacity-80 block md:inline">(${evaluados} atleta(s) evaluados en tu categoría)</span>
                        </span>
                    </div>
                    <div class="text-xs text-left md:text-right">
                        ${textoBarra}
                        <div class="text-[10px] mt-0.5 opacity-80">Récord absoluto de la categoría en el club: <strong>${formatoTiempoJS(recCat)}</strong></div>
                    </div>
                </div>
            `;
            tituloHTML += barraComparativaHTML;
        }
        // --- FIN CÁLCULO DE COMPARATIVA ---

        // Lógica visual para KPIs
        let cvColor = stats.cv < 2.5 ? 'text-emerald-500' : (stats.cv > 5 ? 'text-red-500' : 'text-amber-500');
        let tendenciaIcono = stats.pendiente <= -0.05 ? '<i class="fas fa-arrow-down text-emerald-500"></i>' 
                           : (stats.pendiente >= 0.05 ? '<i class="fas fa-arrow-up text-red-500"></i>' 
                           : '<i class="fas fa-arrow-right text-amber-500"></i>');

        tituloHTML += `
            <!-- Semáforo de Toma de Decisiones -->
            <div class="mb-5 p-4 rounded-xl flex items-start gap-4 shadow-sm ${stats.estado.bg}">
                <div class="mt-1"><i class="fas ${stats.estado.icono} text-2xl"></i></div>
                <div>
                    <h4 class="font-black text-sm uppercase tracking-wider mb-1">Diagnóstico del Sistema</h4>
                    <p class="text-xs font-medium opacity-90 leading-relaxed">${stats.estado.texto}</p>
                </div>
            </div>

            <!-- Tarjetas KPI -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Mejor Tiempo (PB)</p>
                    <p class="text-xl font-black text-indigo-600 dark:text-indigo-400">${formatoTiempoJS(stats.min)}</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Promedio Periodo</p>
                    <p class="text-xl font-black text-gray-800 dark:text-white">${formatoTiempoJS(stats.media)}</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]" title="Coeficiente de Variación (<2.5% = Muy Regular)">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Estabilidad (CV%)</p>
                    <p class="text-xl font-black ${cvColor}">${stats.cv.toFixed(2)}%</p>
                </div>
                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-100 dark:border-[#252345]" title="Tendencia de progresión en segundos por prueba">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Tendencia</p>
                    <p class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                        ${stats.pendiente.toFixed(2)}s ${tendenciaIcono}
                    </p>
                </div>
            </div>
        `;
    }

    // Inyectar títulos y KPIs antes de la gráfica
    document.getElementById('tituloGrafica').innerHTML = tituloHTML;
    document.getElementById('subtituloGrafica').textContent = '';

    // 3. Renderizar Gráfica
    var labels = datos.map(d => d.fecha);
    var tiempos = datos.map(d => parseFloat(d.tiempo_final_seg));
    var pointColors = datos.map(d => d.es_pb == 1 ? '#f59e0b' : '#4f46e5');
    var pointRadius = datos.map(d => d.es_pb == 1 ? 6 : 4);

    var ctx = document.getElementById('graficaReporte').getContext('2d');
    graficaActual = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tiempo (seg)',
                data: tiempos,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: pointColors,
                pointRadius: pointRadius,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var idx = context.dataIndex;
                            var esPb = datos[idx].es_pb == 1 ? ' 🏆 (PB)' : '';
                            return 'Tiempo: ' + formatoTiempoJS(context.raw) + esPb;
                        }
                    }
                }
            },
            scales: { x: { grid: { display: false } }, y: { title: { display: true, text: 'Segundos' } } }
        }
    });

    // 4. Renderizar Tabla Extendida (Interactiva para Splits)
    document.getElementById('theadReporte').innerHTML = `
        <tr>
            <th class="p-3">Fecha</th>
            <th class="p-3 text-center">Contexto</th>
            <th class="p-3 text-center">Tiempo</th>
            <th class="p-3 text-center" title="Ritmo cada 100m">Ritmo/100m</th>
            <th class="p-3 text-center">Brazadas</th>
            <th class="p-3 text-center" title="Índice de Eficiencia">SWOLF</th>
            <th class="p-3">Observaciones</th>
        </tr>
    `;

    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var pbBadge = d.es_pb == 1 ? ' <span class="text-amber-500 ml-1" title="Personal Best"><i class="fas fa-trophy"></i></span>' : '';
        var contextoBadge = d.contexto === 'Competencia' ? '<span class="text-indigo-600 font-bold dark:text-indigo-400">Competición</span>' : '<span class="text-gray-500">Control</span>';
        
        var ritmo = d.tiempo_100m ? formatoTiempoJS(d.tiempo_100m) : '-';
        var brazadas = d.num_brazadas ? d.num_brazadas : '-';
        var swolf = d.swolf ? d.swolf : '-';

        return `
            <tr onclick="verSplits(${d.id_marca}, '${d.fecha}', '${d.tiempo_final_seg}')" class="hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors border-b border-gray-100 dark:border-[#252345] cursor-pointer" title="Haga clic para ver los parciales (splits)">
                <td class="p-3 text-gray-900 dark:text-white text-sm whitespace-nowrap" data-label="Fecha">${d.fecha}</td>
                <td class="p-3 text-center text-xs" data-label="Contexto">${contextoBadge}</td>
                <td class="p-3 text-center text-gray-900 dark:text-white font-mono font-bold text-sm whitespace-nowrap" data-label="Tiempo">
                    ${formatoTiempoJS(d.tiempo_final_seg)} ${pbBadge}
                </td>
                <td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono text-xs" data-label="Ritmo/100m">${ritmo}</td>
                <td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono text-xs" data-label="Brazadas">${brazadas}</td>
                <td class="p-3 text-center font-bold text-teal-600 dark:text-teal-400 font-mono text-xs" data-label="SWOLF">${swolf}</td>
                <td class="p-3 text-gray-500 dark:text-gray-400 text-[11px]" data-label="Observaciones">${d.observaciones || 'Ninguna'}</td>
            </tr>
        `;
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
    var nombreEntrenador = selEntrenador.options[selEntrenador.selectedIndex] 
        ? selEntrenador.options[selEntrenador.selectedIndex].text 
        : '';
    
    var form = new FormData();
    form.append('accion', 'generar_pdf');
    form.append('tipo_reporte', 'lista_entrenadores');
    
    if (idEntrenador) {
        form.append('id_entrenador', idEntrenador);
    } else {
        form.append('id_entrenador', '');
    }
    
    var mensaje = idEntrenador 
        ? 'La ficha del entrenador se descargara automaticamente.' 
        : 'La lista completa de entrenadores se descargara automaticamente.';
    
    UI.exito('Generando PDF', mensaje);
    
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
       
        var nombreArchivo = idEntrenador 
            ? 'ficha_entrenador_' + idEntrenador + '.pdf'
            : 'lista_entrenadores_' + new Date().toISOString().slice(0,10) + '.pdf';
        
        a.download = nombreArchivo;
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

// Convierte segundos puros (ej. 65.2) a formato natación (1:05.20)
function formatoTiempoJS(segundos) {
    if (segundos === null || segundos === '' || isNaN(segundos)) return '-';
    let s = parseFloat(segundos);
    let min = Math.floor(s / 60);
    let sec = (s % 60).toFixed(2);
    if (sec < 10 && min > 0) sec = '0' + sec;
    return min > 0 ? `${min}:${sec}` : `${sec}`;
}

// Motor estadístico para el reporte de marcas
function calcularEstadisticasMarcas(datos) {
    let tiempos = datos.map(d => parseFloat(d.tiempo_final_seg)).filter(v => !isNaN(v));
    let n = tiempos.length;
    if (n === 0) return null;

    let media = tiempos.reduce((a, b) => a + b, 0) / n;
    let orden = [...tiempos].sort((a, b) => a - b);
    let min = orden[0]; // PB del periodo
    
    // Coeficiente de Variación (CV%)
    let varianza = tiempos.reduce((a, b) => a + Math.pow(b - media, 2), 0) / n;
    let sd = Math.sqrt(varianza);
    let cv = (sd / media) * 100;

    // Tendencia (Regresión lineal simple)
    let xs = datos.map((_, i) => i);
    let mx = xs.reduce((a, b) => a + b, 0) / n;
    let my = media;
    let num = 0, den = 0;
    for (let i = 0; i < n; i++) {
        num += (xs[i] - mx) * (tiempos[i] - my);
        den += Math.pow(xs[i] - mx, 2);
    }
    let pendiente = den ? num / den : 0; // Negativo = mejora

    // Sistema Experto de Decisión (Semáforo)
    let estado = { color: 'gray', bg: 'bg-gray-100 text-gray-700', texto: 'Datos insuficientes para diagnóstico.', icono: 'fa-info-circle' };

    if (n >= 3) {
        let ultimas = tiempos.slice(-3);
        // Reglas de negocio deportivas
        let retroceso = tiempos[n - 1] > tiempos[n - 2] && tiempos[n - 2] > tiempos[n - 3];
        let estancado = Math.abs(ultimas[0] - ultimas[2]) <= 0.3 && !retroceso;

        if (retroceso) {
            estado = { color: 'red', bg: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800', texto: 'ALERTA DE RETROCESO: 2 o más marcas empeorando. Verificar fatiga neuromuscular, horas de sueño (sRPE) o técnica.', icono: 'fa-exclamation-triangle' };
        } else if (estancado) {
            estado = { color: 'yellow', bg: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800', texto: 'ESTANCAMIENTO DETECTADO: Tiempos planos. Considerar ajuste en la carga de entrenamiento o microciclo de choque.', icono: 'fa-hand-paper' };
        } else if (pendiente < 0) {
            estado = { color: 'green', bg: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800', texto: 'PROGRESANDO: Curva de asimilación positiva. El plan de entrenamiento está dando resultados óptimos.', icono: 'fa-check-circle' };
        }
    }

    // Cálculo de mejora (% y segundos) comparando la primera vs última marca del periodo
    let mejoraSeg = tiempos[0] - tiempos[n - 1]; // Positivo = mejoró (bajó el tiempo)
    let mejoraPct = tiempos[0] > 0 ? (mejoraSeg / tiempos[0]) * 100 : 0;

    // Promedios técnicos (filtrando valores nulos o ceros)
    let swolfs = datos.map(d => parseFloat(d.swolf)).filter(v => !isNaN(v) && v > 0);
    let avgSwolf = swolfs.length ? (swolfs.reduce((a, b) => a + b, 0) / swolfs.length).toFixed(1) : '-';

    let reaccion = datos.map(d => parseFloat(d.tiempo_reaccion_seg)).filter(v => !isNaN(v) && v > 0);
    let avgReac = reaccion.length ? (reaccion.reduce((a, b) => a + b, 0) / reaccion.length).toFixed(2) + 's' : '-';

    return { n, media, min, sd, cv, pendiente, estado, mejoraPct, avgSwolf, avgReac };
}


// Función para mostrar parciales y calcular Split Neto
async function verSplits(idMarca, fecha, tiempoTotal) {
    var splits = await peticionAjax('splits_marca', { id_marca: idMarca });
    
    if (!splits || splits.length === 0) {
        UI.info('Sin parciales', 'Esta marca no tiene splits registrados en el sistema.');
        return;
    }

    // Cálculo del Split Neto (solo si hay más de 1 parcial)
    var badgeSplit = '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">No aplicable</span>';
    
    if (splits.length > 1) {
        var primero = parseFloat(splits[0].tiempo_parcial_seg);
        var ultimo = parseFloat(splits[splits.length - 1].tiempo_parcial_seg);
        var splitNeto = (ultimo - primero).toFixed(2);
        
        if (splitNeto < 0) {
            badgeSplit = `<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">Split Negativo (${splitNeto}s) - ¡Excelente cierre!</span>`;
        } else {
            badgeSplit = `<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Split Positivo (+${splitNeto}s) - Caída de ritmo</span>`;
        }
    }

    var htmlTabla = `
        <div class="mb-4 text-left">
            <div class="flex justify-between items-center mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase">Tiempo Total</p>
                    <p class="text-lg font-black text-indigo-600">${formatoTiempoJS(tiempoTotal)}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-1">Gestión de Carrera</p>
                    ${badgeSplit}
                </div>
            </div>
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600 text-[10px] uppercase tracking-wider">
                        <tr>
                            <th class="p-2 text-center border-b">Parcial</th>
                            <th class="p-2 border-b">Distancia</th>
                            <th class="p-2 text-center border-b">Tiempo</th>
                            <th class="p-2 text-center border-b">Viraje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${splits.map(s => `
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 text-center font-bold text-gray-500">${s.parcial_numero}</td>
                                <td class="p-2 font-medium">${s.distancia_parcial_m}m</td>
                                <td class="p-2 text-center font-mono font-bold text-gray-900">${formatoTiempoJS(s.tiempo_parcial_seg)}</td>
                                <td class="p-2 text-center font-mono text-gray-500 text-xs">${s.tiempo_viraje_seg ? s.tiempo_viraje_seg + 's' : '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;

    Swal.fire({
        title: `<span class="text-lg">Análisis de Parciales <br><span class="text-sm text-gray-500 font-normal">${fecha}</span></span>`,
        html: htmlTabla,
        width: '500px',
        showCloseButton: true,
        confirmButtonColor: '#4f46e5',
        confirmButtonText: 'Cerrar'
    });
}

document.addEventListener('DOMContentLoaded', function() {
    cargarSelects();
    cargarSesionesParaSelect();
});