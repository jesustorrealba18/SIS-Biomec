const API_URL = 'index.php?p=reportes';

let reporteActivo = null;
let graficaActual = null;
let datosGlobales = [];
let atletasCache = [];
let gruposCache = [];
let categoriasCache = [];

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
    lista_representantes: { titulo: 'Lista de Representantes', sub: 'Directorio de representantes - Descarga directa en PDF' }
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
    const [atletas, grupos, categorias] = await Promise.all([
        peticionAjax('select_atletas'),
        peticionAjax('select_grupos'),
        peticionAjax('select_categorias')
    ]);
    if (atletas) atletasCache = atletas;
    if (grupos) gruposCache = grupos;
    if (categorias) categoriasCache = categorias;
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

async function aplicarFiltros() {
    if (!reporteActivo || reporteActivo === 'ficha_atleta' || reporteActivo === 'lista_atletas' || reporteActivo === 'lista_representantes') return;
    var tipo = reporteActivo;
    var params = {};
    var fi = document.getElementById('fFechaIni') ? document.getElementById('fFechaIni').value : '';
    var ff = document.getElementById('fFechaFin') ? document.getElementById('fFechaFin').value : '';
    params.fecha_ini = fi;
    params.fecha_fin = ff;

    if (tipo === 'evolucion_marcas') {
        params.id_atleta = document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : 0;
        params.estilo = document.getElementById('fEstilo') ? document.getElementById('fEstilo').value : '';
        params.distancia = document.getElementById('fDistancia') ? document.getElementById('fDistancia').value : 0;
        params.piscina = document.getElementById('fPiscina') ? document.getElementById('fPiscina').value : '';
        if (!params.id_atleta || !params.estilo || !params.distancia || !params.piscina) {
            UI.advertencia('Filtros incompletos', 'Seleccione atleta, estilo, distancia y tipo de piscina.'); return;
        }
    } else if (tipo === 'asistencia_grupo' || tipo === 'volumen_semanal') {
        params.id_grupo = document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0;
        if (!params.id_grupo) { UI.advertencia('Filtros incompletos', 'Seleccione un grupo.'); return; }
    } else if (tipo === 'carga_srpe') {
        params.id_grupo = document.getElementById('fGrupo') ? document.getElementById('fGrupo').value : 0;
        params.id_atleta = document.getElementById('fAtleta') ? document.getElementById('fAtleta').value : 0;
        if (!params.id_grupo && !params.id_atleta) { UI.advertencia('Filtros incompletos', 'Seleccione un grupo o un atleta.'); return; }
    }

    var datos = await peticionAjax(tipo, params);
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

    if (tipo === 'evolucion_marcas') renderEvolucionMarcas(datos);
    else if (tipo === 'asistencia_grupo') renderAsistenciaGrupo(datos);
    else if (tipo === 'volumen_semanal') renderVolumenSemanal(datos);
    else if (tipo === 'carga_srpe') renderCargaSRPE(datos);
}

function formatoTiempo(seg) {
    if (seg === null || seg === '' || isNaN(seg)) return '-';
    var s = parseFloat(seg);
    var min = Math.floor(s / 60);
    var sec = s - (min * 60);
    return min > 0 ? min + ':' + sec.toFixed(2).padStart(5, '0') : s.toFixed(2);
}

function coloresTema() {
    var o = document.documentElement.classList.contains('dark');
    return { texto: o ? '#6b7280' : '#4b5563', grid: o ? '#25234540' : '#e5e7eb40' };
}

function destruirGrafica() { if (graficaActual) { graficaActual.destroy(); graficaActual = null; } }

function renderEvolucionMarcas(datos) {
    destruirGrafica();
    var c = coloresTema();
    var labels = datos.map(function(d) { return d.fecha; });
    var tiempos = datos.map(function(d) { return parseFloat(d.tiempo_final_seg); });
    var colPunto = datos.map(function(d) { return d.es_pb == 1 ? '#f59e0b' : '#6366f1'; });
    var radPunto = datos.map(function(d) { return d.es_pb == 1 ? 7 : 4; });

    document.getElementById('tituloGrafica').textContent = 'Evolucion de Tiempos';
    document.getElementById('subtituloGrafica').textContent = document.getElementById('fEstilo').value + ' ' + document.getElementById('fDistancia').value + 'm (' + document.getElementById('fPiscina').value + ')';

    var ctx = document.getElementById('graficaReporte').getContext('2d');
    graficaActual = new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Tiempo (s)', data: tiempos, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', borderWidth: 3, tension: 0.3, fill: true, pointRadius: radPunto, pointBackgroundColor: colPunto }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: c.grid }, ticks: { color: c.texto, font: { size: 10 } } }, y: { grid: { color: c.grid }, ticks: { color: c.texto }, reverse: true } } }
    });

    document.getElementById('theadReporte').innerHTML = '<tr><th class="p-3">Fecha</th><th class="p-3">Tiempo</th><th class="p-3 text-center">PB</th><th class="p-3">Contexto</th></tr>';
    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var pb = d.es_pb == 1 ? '<span class="bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase"><i class="fas fa-star mr-1"></i>PB</span>' : '<span class="text-gray-400 dark:text-gray-600">-</span>';
        return '<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"><td class="p-3 text-gray-900 dark:text-white font-mono text-sm" data-label="Fecha">' + d.fecha + '</td><td class="p-3 font-bold text-gray-900 dark:text-white font-mono" data-label="Tiempo">' + formatoTiempo(d.tiempo_final_seg) + '</td><td class="p-3 text-center" data-label="PB">' + pb + '</td><td class="p-3 text-gray-600 dark:text-gray-400 text-sm" data-label="Contexto">' + d.contexto + '</td></tr>';
    }).join('');
}

function renderAsistenciaGrupo(datos) {
    destruirGrafica();
    var c = coloresTema();
    var tot = datos.reduce(function(a, d) { a.p += parseInt(d.presentes)||0; a.au += parseInt(d.ausentes)||0; a.j += parseInt(d.justificados)||0; a.r += parseInt(d.retardos)||0; return a; }, {p:0,au:0,j:0,r:0});

    document.getElementById('tituloGrafica').textContent = 'Distribucion de Asistencia';
    document.getElementById('subtituloGrafica').textContent = 'Resumen global del grupo en el periodo seleccionado';

    graficaActual = new Chart(document.getElementById('graficaReporte').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['Presente','Ausente','Justificado','Retardo'], datasets: [{ data: [tot.p, tot.au, tot.j, tot.r], backgroundColor: ['#10b981','#ef4444','#f59e0b','#6366f1'], borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: c.texto, font: { size: 11 }, boxWidth: 12, padding: 16 } } } }
    });

    document.getElementById('theadReporte').innerHTML = '<tr><th class="p-3">Atleta</th><th class="p-3 text-center">Total</th><th class="p-3 text-center">Pres.</th><th class="p-3 text-center">Aus.</th><th class="p-3 text-center">Just.</th><th class="p-3 text-center">Ret.</th><th class="p-3 text-center">%</th></tr>';
    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var t = parseInt(d.total_sesiones)||0, p = parseInt(d.presentes)||0;
        var pct = t > 0 ? ((p/t)*100).toFixed(1) : '0.0';
        var clr = pct >= 90 ? 'text-emerald-500' : (pct >= 75 ? 'text-amber-500' : 'text-red-500');
        return '<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"><td class="p-3 text-gray-900 dark:text-white font-medium" data-label="Atleta">' + d.nombre_atleta + '</td><td class="p-3 text-center text-gray-600 dark:text-gray-400" data-label="Total">' + t + '</td><td class="p-3 text-center text-emerald-600 dark:text-emerald-400 font-bold" data-label="Presentes">' + p + '</td><td class="p-3 text-center text-red-600 dark:text-red-400" data-label="Ausentes">' + (d.ausentes||0) + '</td><td class="p-3 text-center text-amber-600 dark:text-amber-400" data-label="Justificados">' + (d.justificados||0) + '</td><td class="p-3 text-center text-indigo-600 dark:text-indigo-400" data-label="Retardos">' + (d.retardos||0) + '</td><td class="p-3 text-center font-bold ' + clr + '" data-label="%">' + pct + '%</td></tr>';
    }).join('');
}

function renderVolumenSemanal(datos) {
    destruirGrafica();
    var c = coloresTema();
    document.getElementById('tituloGrafica').textContent = 'Volumen Semanal';
    document.getElementById('subtituloGrafica').textContent = 'Metros planificados vs ejecutados';

    graficaActual = new Chart(document.getElementById('graficaReporte').getContext('2d'), {
        type: 'bar',
        data: { labels: datos.map(function(d) { return d.rango; }), datasets: [
            { label: 'Planificado (m)', data: datos.map(function(d) { return parseInt(d.metros_planificados)||0; }), backgroundColor: 'rgba(99,102,241,0.6)', borderRadius: 4 },
            { label: 'Ejecutado (m)', data: datos.map(function(d) { return parseInt(d.metros_ejecutados)||0; }), backgroundColor: 'rgba(16,185,129,0.6)', borderRadius: 4 }
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: c.texto, font: { size: 10 }, boxWidth: 12 } } }, scales: { x: { grid: { display: false }, ticks: { color: c.texto, font: { size: 9 }, maxRotation: 45 } }, y: { grid: { color: c.grid }, ticks: { color: c.texto }, beginAtZero: true } } }
    });

    document.getElementById('theadReporte').innerHTML = '<tr><th class="p-3">Semana</th><th class="p-3 text-center">Planificado</th><th class="p-3 text-center">Ejecutado</th><th class="p-3 text-center">Sesiones</th><th class="p-3 text-center">Cumplimiento</th></tr>';
    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var pl = parseInt(d.metros_planificados)||0, ej = parseInt(d.metros_ejecutados)||0;
        var pct = pl > 0 ? ((ej/pl)*100).toFixed(1) : '0.0';
        var clr = pct >= 95 ? 'text-emerald-500' : (pct >= 80 ? 'text-amber-500' : 'text-red-500');
        return '<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"><td class="p-3 text-gray-900 dark:text-white text-sm" data-label="Semana">' + d.rango + '</td><td class="p-3 text-center text-gray-600 dark:text-gray-400 font-mono" data-label="Planificado">' + pl.toLocaleString() + ' m</td><td class="p-3 text-center text-gray-900 dark:text-white font-mono font-bold" data-label="Ejecutado">' + ej.toLocaleString() + ' m</td><td class="p-3 text-center text-gray-600 dark:text-gray-400" data-label="Sesiones">' + d.total_sesiones + '</td><td class="p-3 text-center font-bold ' + clr + '" data-label="Cumplimiento">' + pct + '%</td></tr>';
    }).join('');
}

function renderCargaSRPE(datos) {
    destruirGrafica();
    var c = coloresTema();
    document.getElementById('tituloGrafica').textContent = 'Carga y Bienestar Diario';
    document.getElementById('subtituloGrafica').textContent = 'sRPE (barras) y RPE promedio (linea)';

    var porFecha = {};
    datos.forEach(function(d) { if (!porFecha[d.fecha]) porFecha[d.fecha] = []; porFecha[d.fecha].push(d); });
    var fechas = Object.keys(porFecha).sort();
    var srpeProm = fechas.map(function(f) { var v = porFecha[f].map(function(d) { return parseFloat(d.srpe)||0; }).filter(function(x) { return x > 0; }); return v.length ? Math.round(v.reduce(function(a,b){return a+b;},0)/v.length) : 0; });
    var rpeProm = fechas.map(function(f) { var v = porFecha[f].map(function(d) { return parseFloat(d.rpe)||0; }); return v.length ? parseFloat((v.reduce(function(a,b){return a+b;},0)/v.length).toFixed(1)) : 0; });

    graficaActual = new Chart(document.getElementById('graficaReporte').getContext('2d'), {
        type: 'bar',
        data: { labels: fechas, datasets: [
            { label: 'sRPE Prom.', data: srpeProm, backgroundColor: 'rgba(99,102,241,0.6)', borderRadius: 4, order: 2, yAxisID: 'y' },
            { label: 'RPE Prom.', data: rpeProm, type: 'line', borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', borderWidth: 2, tension: 0.3, pointBackgroundColor: '#f59e0b', fill: true, order: 1, yAxisID: 'y1' }
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: c.texto, font: { size: 10 }, boxWidth: 12 } } }, scales: { x: { grid: { display: false }, ticks: { color: c.texto, font: { size: 9 }, maxRotation: 45 } }, y: { type: 'linear', position: 'left', grid: { color: c.grid }, ticks: { color: c.texto }, beginAtZero: true, title: { display: true, text: 'sRPE', color: c.texto, font: { size: 10 } } }, y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, ticks: { color: c.texto, max: 10, min: 0 }, beginAtZero: true, title: { display: true, text: 'RPE', color: c.texto, font: { size: 10 } } } } }
    });

    document.getElementById('theadReporte').innerHTML = '<tr><th class="p-2">Fecha</th><th class="p-2">Atleta</th><th class="p-2 text-center">RPE</th><th class="p-2 text-center">sRPE</th><th class="p-2 text-center">Sueno</th><th class="p-2 text-center">Calidad</th><th class="p-2 text-center">Estres</th><th class="p-2 text-center">Muscular</th></tr>';
    document.getElementById('tbodyReporte').innerHTML = datos.map(function(d) {
        var clr = d.rpe >= 8 ? 'text-red-500 font-bold' : (d.rpe >= 6 ? 'text-amber-500' : 'text-emerald-500');
        return '<tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"><td class="p-2 text-gray-700 dark:text-gray-300 text-xs font-mono" data-label="Fecha">' + d.fecha + '</td><td class="p-2 text-gray-900 dark:text-white text-xs font-medium" data-label="Atleta">' + d.nombre_atleta + '</td><td class="p-2 text-center text-xs ' + clr + '" data-label="RPE">' + d.rpe + '</td><td class="p-2 text-center text-gray-700 dark:text-gray-300 text-xs font-mono" data-label="sRPE">' + (d.srpe||'-') + '</td><td class="p-2 text-center text-gray-600 dark:text-gray-400 text-xs" data-label="Sueno">' + (d.horas_sueno||'-') + 'h</td><td class="p-2 text-center text-gray-600 dark:text-gray-400 text-xs" data-label="Calidad">' + (d.calidad_sueno||'-') + '/10</td><td class="p-2 text-center text-gray-600 dark:text-gray-400 text-xs" data-label="Estres">' + (d.estres_percibido||'-') + '/10</td><td class="p-2 text-center text-gray-600 dark:text-gray-400 text-xs" data-label="Muscular">' + (d.sensacion_muscular||'-') + '/10</td></tr>';
    }).join('');
}

async function descargarPDF() {
    if (!reporteActivo) return;
    if (reporteActivo === 'ficha_atleta') { await descargarFichaDirecta(); return; }
    if (reporteActivo === 'lista_atletas') { await descargarListaAtletasDirecta(); return; }
    if (reporteActivo === 'lista_representantes') { await descargarListaRepresentantesDirecta(); return; }

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

document.addEventListener('DOMContentLoaded', cargarSelects);
