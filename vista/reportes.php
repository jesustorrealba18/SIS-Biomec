<?php
$pagina = 'reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Reportes | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        .menu-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 1.5rem;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }
        .input-adapt {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            transition: all 0.3s ease;
        }
        .dark .input-adapt {
            background-color: #0f0d23;
            border-color: #252345;
            color: #ffffff;
        }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .input-adapt::-webkit-calendar-picker-indicator { filter: invert(1); }
        .dark .input-adapt::-webkit-calendar-picker-indicator { filter: invert(0); }
        .card-reporte {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .card-reporte:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.12);
        }
        .dark .card-reporte:hover {
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.2);
        }
        @media (max-width: 767px) {
            .tabla-responsive thead { display: none; }
            .tabla-responsive tbody tr {
                display: block; padding: 12px; margin-bottom: 8px;
                border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;
            }
            .dark .tabla-responsive tbody tr { border-color: #252345; background: #161430; }
            .tabla-responsive tbody td {
                display: flex; justify-content: space-between; align-items: center;
                padding: 6px 0; border: none;
            }
            .tabla-responsive tbody td::before {
                content: attr(data-label); font-size: 10px; text-transform: uppercase;
                color: #6b7280; font-weight: 700; letter-spacing: 0.05em; margin-right: 8px;
            }
        }
        @media (min-width: 768px) {
            .tabla-responsive tbody td::before { content: none; }
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
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>
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
                $tituloPagina = "Centro de Reportes";
                $tituloPaginaResponsive = "Reportes";
                $iconModulo = "fas fa-chart-bar";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-chart-bar text-indigo-500"></i> Centro de Reportes
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Genera y descarga reportes de rendimiento deportivo.</p>
                    </div>
                </div>

                <!-- GRID DE CARDS -->
                <div id="gridCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="card-reporte tarjeta transition-colors duration-300" onclick="mostrarReporte('evolucion_marcas')">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/15 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 mt-1"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Evolucion de Marcas</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Progresion temporal de tiempos por prueba con deteccion de PBs.</p>
                    </div>

                    <div class="card-reporte tarjeta transition-colors duration-300" onclick="mostrarReporte('asistencia_grupo')">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 mt-1"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Asistencia por Grupo</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Resumen de asistencia por atleta en un rango de fechas.</p>
                    </div>

                    <div class="card-reporte tarjeta transition-colors duration-300" onclick="mostrarReporte('volumen_semanal')">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 text-xl">
                                <i class="fas fa-ruler-horizontal"></i>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 mt-1"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Volumen Semanal</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Metros planificados vs ejecutados por semana.</p>
                    </div>

                    <div class="card-reporte tarjeta transition-colors duration-300" onclick="mostrarReporte('carga_srpe')">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 mt-1"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Monitoreo de Carga (sRPE)</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Carga subjetiva, sueno y bienestar diario.</p>
                    </div>

                    <div class="card-reporte tarjeta transition-colors duration-300" onclick="mostrarReporte('ficha_atleta')">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/15 flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 mt-1"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Ficha del Atleta (PDF)</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Hoja de vida completa descargable.</p>
                    </div>
                </div>

                <!-- SECCION DINAMICA -->
                <div id="seccionReporte" class="hidden space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <button onclick="volverASelector()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-all cursor-pointer">
                                <i class="fas fa-arrow-left text-sm"></i>
                            </button>
                            <div>
                                <h2 id="tituloReporte" class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">Reporte</h2>
                                <p id="subtituloReporte" class="text-[11px] text-gray-500 dark:text-gray-400"></p>
                            </div>
                        </div>
                        <button id="btnDescargarPDF" onclick="descargarPDF()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </button>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                        <div id="contenedorFiltros" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end"></div>
                    </div>

                    <div id="contenedorGrafica" class="tarjeta transition-colors duration-300 hidden">
                        <h3 id="tituloGrafica" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"></h3>
                        <p id="subtituloGrafica" class="text-[10px] text-gray-500 dark:text-gray-400 mb-4"></p>
                        <div class="h-56 sm:h-72">
                            <canvas id="graficaReporte"></canvas>
                        </div>
                    </div>

                    <div id="contenedorTabla" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300 hidden">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-white/5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Datos del Reporte</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm tabla-responsive" id="tablaReporte">
                                <thead id="theadReporte" class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest"></thead>
                                <tbody id="tbodyReporte" class="text-sm divide-y divide-gray-200 dark:divide-gray-800"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="estadoVacio" class="hidden text-center py-16">
                        <i class="fas fa-inbox text-5xl text-gray-300 dark:text-gray-600 mb-4 block"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No se encontraron datos para los filtros seleccionados.</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        (function() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('menuOverlay');
            const openBtn = document.getElementById('openMenuBtn');
            const closeBtn = document.getElementById('closeMenuBtn');
            function openMenu() { if (!sidebar) return; sidebar.classList.remove('-translate-x-full'); sidebar.classList.add('translate-x-0'); if (overlay) { overlay.classList.remove('opacity-0','pointer-events-none'); overlay.classList.add('opacity-100','pointer-events-auto'); } document.body.style.overflow = 'hidden'; }
            function closeMenu() { if (!sidebar) return; sidebar.classList.remove('translate-x-0'); sidebar.classList.add('-translate-x-full'); if (overlay) { overlay.classList.remove('opacity-100','pointer-events-auto'); overlay.classList.add('opacity-0','pointer-events-none'); } document.body.style.overflow = ''; }
            if (openBtn) openBtn.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            if (overlay) overlay.addEventListener('click', closeMenu);
            window.addEventListener('resize', function() { if (window.innerWidth >= 1024) { if (sidebar && sidebar.classList.contains('translate-x-0')) { sidebar.classList.remove('translate-x-0'); sidebar.classList.add('-translate-x-full'); } if (overlay) { overlay.classList.remove('opacity-100','pointer-events-auto'); overlay.classList.add('opacity-0','pointer-events-none'); } document.body.style.overflow = ''; } });
        })();
    </script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/reportes.js"></script>
</body>
</html>
