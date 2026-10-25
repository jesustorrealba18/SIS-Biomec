<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'calendario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Calendario General | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <!-- Locale en español (el bundle global no incluye locales) -->
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* ===== TARJETAS ===== */
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        /* ===== FULLCALENDAR - MODO CLARO ===== */
        #calendario-container { padding: 1.5rem; }

        .fc {
            --fc-border-color: #e5e7eb;
            --fc-button-bg-color: #e5e7eb;
            --fc-button-border-color: #d1d5db;
            --fc-button-text-color: #374151;
            --fc-button-hover-bg-color: #d1d5db;
            --fc-button-hover-border-color: #9ca3af;
            --fc-button-active-bg-color: #6366f1;
            --fc-button-active-border-color: #4f46e5;
            --fc-button-active-text-color: #ffffff;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #f9fafb;
            --fc-list-event-hover-bg-color: #f3f4f6;
            --fc-today-bg-color: #eef2ff;
            --fc-event-border-color: transparent;
            --fc-button-icon-color: #374151;
            --fc-button-icon-hover-color: #1f2937;
            --fc-button-icon-active-color: #fff;
        }

        /* ===== FULLCALENDAR - MODO OSCURO ===== */
        .dark .fc {
            --fc-border-color: #252345;
            --fc-button-bg-color: #1e1b4b;
            --fc-button-border-color: #3730a3;
            --fc-button-text-color: #c7d2fe;
            --fc-button-hover-bg-color: #312e81;
            --fc-button-hover-border-color: #4338ca;
            --fc-button-active-bg-color: #4338ca;
            --fc-button-active-border-color: #6366f1;
            --fc-button-active-text-color: #ffffff;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #111026;
            --fc-list-event-hover-bg-color: #1e1b4b;
            --fc-today-bg-color: #1e1b4b44;
            --fc-event-border-color: transparent;
            --fc-button-icon-color: #c7d2fe;
            --fc-button-icon-hover-color: #e0e7ff;
            --fc-button-icon-active-color: #fff;
        }

        .fc .fc-toolbar-title {
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .dark .fc .fc-toolbar-title {
            color: #ffffff;
        }

        .fc .fc-col-header-cell-cushion {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .dark .fc .fc-col-header-cell-cushion {
            color: #6b7280;
        }

        .fc .fc-daygrid-day-number {
            color: #4b5563;
            text-decoration: none;
        }
        .dark .fc .fc-daygrid-day-number {
            color: #9ca3af;
        }

        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            background: #6366f1;
            color: #ffffff;
            border-radius: 9999px;
            padding: 2px 6px;
        }

        .fc .fc-event {
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            color: #ffffff !important;
        }
        .fc .fc-event:hover {
            opacity: 0.85;
            filter: brightness(1.1);
        }

        .fc .fc-daygrid-more-link {
            color: #6366f1;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .dark .fc .fc-daygrid-more-link {
            color: #818cf8;
        }

        .fc .fc-scrollgrid,
        .fc th,
        .fc td {
            border-color: #e5e7eb;
        }
        .dark .fc .fc-scrollgrid,
        .dark .fc th,
        .dark .fc td {
            border-color: #252345;
        }

        /* ===== ICONOS DE DESPLAZAMIENTO (prev/next) ===== */
        /* La fuente embebida 'fcicons' (base64) es bloqueada por la CSP font-src,
           por lo que se reemplazan los chevrons por glifos de Font Awesome (cdn.jsdelivr/cdnjs permitidos). */
        .fc .fc-icon-chevron-left:before,
        .fc .fc-icon-chevrons-left:before,
        .fc .fc-icon-chevron-right:before,
        .fc .fc-icon-chevrons-right:before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
        }
        .fc .fc-icon-chevron-left:before  { content: "\f053"; }
        .fc .fc-icon-chevrons-left:before { content: "\f100"; }
        .fc .fc-icon-chevron-right:before { content: "\f054"; }
        .fc .fc-icon-chevrons-right:before { content: "\f101"; }
        .fc .fc-button .fc-icon {
            font-size: 0.85em;
            vertical-align: middle;
        }

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">

        <!-- Overlay para móvil cuando el menú está abierto -->
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <!-- Sidebar - responsive -->
        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-[#0f0d23] border-r border-gray-200 dark:border-[#252345] z-40 transform -translate-x-full menu-transition lg:relative lg:translate-x-0 lg:flex-shrink-0 overflow-y-auto transition-colors duration-300">
            <div class="p-4 flex justify-between items-center border-b border-gray-200 dark:border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-gray-900 dark:text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <?php
                $tituloPagina = "Calendario General";
                $tituloPaginaResponsive = "Calendario";
                $iconModulo = "fas fa-calendar-alt";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-500"></i> Calendario General
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Visualización de eventos competitivos y entrenamientos programados.</p>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div id="calendario-container" class="p-4 sm:p-6"></div>
                </div>

                <!-- Leyenda -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-4 transition-colors duration-300">
                    <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-bold tracking-widest mb-3">Leyenda por Tipo de Evento</p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#3b82f6]"></span> Regional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#10b981]"></span> Nacional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f59e0b]"></span> Internacional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f97316]"></span> Selectivo</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#6b7280]"></span> Control</span>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- ===== MODAL DETALLE EVENTO ===== -->
    <div id="modalDetalleEvento" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/80 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-2xl shadow-2xl max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarDetalleEvento()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[10] cursor-pointer p-2" title="Cerrar">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleEventoContenido" class="p-6 md:p-8"></div>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script>
        (function() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('menuOverlay');
            const openBtn = document.getElementById('openMenuBtn');
            const closeBtn = document.getElementById('closeMenuBtn');

            function openMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (overlay) {
                    overlay.classList.remove('opacity-0', 'pointer-events-none');
                    overlay.classList.add('opacity-100', 'pointer-events-auto');
                }
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('opacity-100', 'pointer-events-auto');
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                }
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            if (overlay) overlay.addEventListener('click', closeMenu);

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    if (sidebar && sidebar.classList.contains('translate-x-0')) {
                        sidebar.classList.remove('translate-x-0');
                        sidebar.classList.add('-translate-x-full');
                    }
                    if (overlay) {
                        overlay.classList.remove('opacity-100', 'pointer-events-auto');
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                    }
                    document.body.style.overflow = '';
                }
            });
        })();

        // ===== MODAL DE DETALLE DE EVENTO =====
        const modalDetalleEvento = document.getElementById('modalDetalleEvento');
        let eventosDiaCache = [];

        const coloresTipoCal = {
            'Regional':      'bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/30',
            'Nacional':      'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30',
            'Internacional': 'bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30',
            'Selectivo':     'bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-500/30',
            'Control':       'bg-gray-500/20 text-gray-600 dark:text-gray-400 border border-gray-500/30'
        };
        const coloresEstadoCal = {
            'Planificado':   'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400',
            'Inscrito':      'bg-cyan-500/20 text-cyan-600 dark:text-cyan-400',
            'En Progreso':   'bg-yellow-500/20 text-yellow-600 dark:text-yellow-400',
            'Finalizado':    'bg-green-500/20 text-green-600 dark:text-green-400',
            'Cancelado':     'bg-red-500/20 text-red-600 dark:text-red-400'
        };

        function formatoFechaCal(iso) {
            if (!iso) return '-';
            const p = String(iso).substring(0, 10).split('-');
            return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
        }

        function rangoFechasCal(inicio, fin) {
            if (!inicio) return '-';
            const f = formatoFechaCal(inicio);
            if (fin && fin !== inicio) return `${f} al ${formatoFechaCal(fin)}`;
            return f;
        }

        function datosBasicosDeEventoFC(ev) {
            return {
                nombre: ev.title,
                tipo: ev.extendedProps ? ev.extendedProps.tipo : null,
                inicio: ev.startStr ? ev.startStr.substring(0, 10) : null,
                fin: ev.endStr ? ev.endStr.substring(0, 10) : null,
                conPermisoEventos: !!ev.url
            };
        }

        function abrirDetalleEvento(id_evento, basico) {
            const contenedor = document.getElementById('detalleEventoContenido');
            contenedor.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm text-center py-10"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando detalle del evento...</p>';
            modalDetalleEvento.classList.remove('hidden');
            modalDetalleEvento.classList.add('flex');

            fetch(`index.php?p=eventos&accion=obtenerDetalle&id=${id_evento}`)
                .then(r => { if (!r.ok) throw new Error('Sin permiso o error'); return r.json(); })
                .then(d => {
                    if (d && d.id_evento) renderDetalleEvento(d);
                    else renderDetalleBasico(basico);
                })
                .catch(() => renderDetalleBasico(basico));
        }

        function renderDetalleBasico(b) {
            const contenedor = document.getElementById('detalleEventoContenido');
            if (!b) {
                contenedor.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm text-center py-10">No hay informacion disponible de este evento.</p>';
                return;
            }
            contenedor.innerHTML = `
                <div class="flex items-center gap-3 mb-6 pr-10">
                    <div class="bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-3 rounded-xl"><i class="fas fa-calendar-day text-xl"></i></div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">${b.nombre || 'Evento'}</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${coloresTipoCal[b.tipo] || coloresTipoCal['Control']}">${b.tipo || 'Evento'}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Fecha</p>
                    <p class="text-gray-900 dark:text-white font-mono text-sm">${rangoFechasCal(b.inicio, b.fin)}</p>
                </div>
            `;
        }

        function renderDetalleEvento(d) {
            const contenedor = document.getElementById('detalleEventoContenido');

            let html = `
                <div class="mb-5 pr-10">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">${d.nombre}</h3>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${coloresTipoCal[d.tipo] || coloresTipoCal['Control']}">${d.tipo}</span>
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${coloresEstadoCal[d.estado] || ''}">${d.estado}</span>
                        ${d.nivel ? `<span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/30">Nivel ${d.nivel}</span>` : ''}
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5 p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Inicio</p><p class="text-gray-900 dark:text-white font-mono text-sm">${formatoFechaCal(d.fecha_inicio)}</p></div>
                    <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Fecha Fin</p><p class="text-gray-900 dark:text-white font-mono text-sm">${d.fecha_fin ? formatoFechaCal(d.fecha_fin) : 'Mismo dia'}</p></div>
                    <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Sede</p><p class="text-gray-900 dark:text-white text-sm">${d.sede || '-'}</p></div>
                    <div><p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">Organizador</p><p class="text-gray-900 dark:text-white text-sm">${d.organizador || '-'}</p></div>
                </div>
            `;

            if (d.tiempos_corte && d.tiempos_corte.length > 0) {
                html += `
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 mb-2"><i class="fas fa-cut mr-2"></i>Tiempos de Corte</h4>
                        <table class="w-full text-xs text-left">
                            <thead><tr class="text-gray-600 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                <th class="p-2">Categoria</th><th class="p-2">Estilo</th><th class="p-2">Distancia</th><th class="p-2">Tiempo Corte</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-800 dark:text-gray-300">
                                ${d.tiempos_corte.map(tc => `
                                    <tr>
                                        <td class="p-2">${tc.categoria_nombre || '-'}</td>
                                        <td class="p-2">${tc.estilo}</td>
                                        <td class="p-2">${tc.distancia}m</td>
                                        <td class="p-2 font-mono">${tc.tiempo_corte_segundos || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            if (d.metas && d.metas.length > 0) {
                html += `
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 mb-2"><i class="fas fa-bullseye mr-2"></i>Metas Competitivas (${d.metas.length})</h4>
                        <div class="space-y-1">
                            ${d.metas.map(m => `
                                <div class="flex justify-between items-center p-2 bg-gray-100 dark:bg-black/20 rounded-lg border border-gray-200 dark:border-white/5">
                                    <span class="text-sm text-gray-900 dark:text-white">${m.nombre_atleta}</span>
                                    <span class="text-xs font-mono text-gray-600 dark:text-gray-400">${m.distancia}m ${m.estilo} - Objetivo: ${m.marca_objetivo_seg}s</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            if (d.inscripciones && d.inscripciones.length > 0) {
                html += `
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-cyan-600 dark:text-cyan-400 mb-2"><i class="fas fa-users mr-2"></i>Atletas Inscritos (${d.inscripciones.length})</h4>
                        <div class="flex flex-wrap gap-1">
                            ${d.inscripciones.map(i => `
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">${i.nombre_atleta}</span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            if (d.observaciones) {
                html += `
                    <div class="mb-5 p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mb-1">Observaciones</p>
                        <p class="text-sm text-gray-800 dark:text-gray-300">${d.observaciones}</p>
                    </div>
                `;
            }

            html += `
                <button type="button" onclick="window.location.href='index.php?p=eventos&h=${d.id_evento}'" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider transition">
                    <i class="fas fa-external-link-alt mr-2"></i>Ver en modulo de Eventos
                </button>
            `;

            contenedor.innerHTML = html;
        }

        function listarEventosDia(dia, eventos) {
            eventosDiaCache = eventos;
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
            const titulo = `${diasSemana[dia.getDay()]} ${String(dia.getDate()).padStart(2, '0')}/${String(dia.getMonth() + 1).padStart(2, '0')}/${dia.getFullYear()}`;

            const items = eventos.map((ev, i) => {
                const b = datosBasicosDeEventoFC(ev);
                return `
                    <button type="button" onclick="abrirDetalleEventoDesdeLista(${i})" class="w-full text-left p-3 rounded-xl border border-gray-200 dark:border-[#252345] hover:border-indigo-500 dark:hover:border-indigo-500/50 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition cursor-pointer">
                        <div class="flex justify-between items-center gap-3">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">${b.nombre || 'Evento'}</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">${rangoFechasCal(b.inicio, b.fin)}</p>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold shrink-0 ${coloresTipoCal[b.tipo] || coloresTipoCal['Control']}">${b.tipo || 'Evento'}</span>
                        </div>
                    </button>
                `;
            }).join('');

            document.getElementById('detalleEventoContenido').innerHTML = `
                <div class="flex items-center gap-3 mb-5 pr-10">
                    <div class="bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-3 rounded-xl"><i class="fas fa-calendar-day text-xl"></i></div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">${titulo}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${eventos.length} evento(s) programado(s) para esta fecha</p>
                    </div>
                </div>
                <div class="space-y-2">${items}</div>
            `;

            modalDetalleEvento.classList.remove('hidden');
            modalDetalleEvento.classList.add('flex');
        }

        function abrirDetalleEventoDesdeLista(indice) {
            const ev = eventosDiaCache[indice];
            if (!ev) return;
            abrirDetalleEvento(parseInt(ev.extendedProps.id_evento, 10), datosBasicosDeEventoFC(ev));
        }

        function cerrarDetalleEvento() {
            modalDetalleEvento.classList.add('hidden');
            modalDetalleEvento.classList.remove('flex');
            document.getElementById('detalleEventoContenido').innerHTML = '';
        }

        modalDetalleEvento.addEventListener('click', function(e) {
            if (e.target === modalDetalleEvento) cerrarDetalleEvento();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modalDetalleEvento.classList.contains('hidden')) cerrarDetalleEvento();
        });

        // ===== INICIALIZAR FULLCALENDAR =====
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendario-container');
            if (!calendarEl) return;

            // Detectamos el tema actual para los colores del calendario (opcional)
            // FullCalendar usa variables CSS que ya hemos definido en el <style>

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                height: 'auto',
                firstDay: 1,
                eventSources: [
                    {
                        url: 'index.php?p=calendario&accion=calendario',
                        color: '#6366f1'
                    }
                ],
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    const id = parseInt(info.event.extendedProps.id_evento, 10);
                    if (!id) return;
                    abrirDetalleEvento(id, datosBasicosDeEventoFC(info.event));
                },
                dateClick: function(info) {
                    // Normalizar a medianoche (en timeGridWeek info.date incluye hora)
                    const dia = new Date(info.date.getFullYear(), info.date.getMonth(), info.date.getDate());
                    const delDia = calendar.getEvents().filter(function(ev) {
                        if (!ev.start) return false;
                        const inicio = new Date(ev.start.getFullYear(), ev.start.getMonth(), ev.start.getDate());
                        // 'end' es exclusivo en FullCalendar: el ultimo dia real es end - 1
                        const fin = ev.end
                            ? new Date(ev.end.getFullYear(), ev.end.getMonth(), ev.end.getDate() - 1)
                            : inicio;
                        return dia >= inicio && dia <= fin;
                    });
                    if (delDia.length === 0) return;
                    if (delDia.length === 1) {
                        abrirDetalleEvento(parseInt(delDia[0].extendedProps.id_evento, 10), datosBasicosDeEventoFC(delDia[0]));
                    } else {
                        listarEventosDia(dia, delDia);
                    }
                }
            });
            calendar.render();
        });
    </script>

</body>
</html>