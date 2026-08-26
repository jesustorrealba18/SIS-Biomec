<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'normalizacion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Normalización de Tiempos | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- DataTables CSS + Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

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
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
        }
        .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(0);
        }

        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-scroll { max-height: 90vh; overflow-y: auto; }
        .modal-header-sticky { position: sticky; top: 0; z-index: 20; }

        /* Toggle papelera */
        #toggleEstadoNormalizacionBtn.active {
            border-color: #ef4444;
            background: rgba(239,68,68,0.1);
        }
        #toggleEstadoNormalizacionBtn.active #toggleIconoNormalizacion { color: #ef4444; }
        #toggleEstadoNormalizacionBtn.active #toggleTextoNormalizacion { color: #ef4444; }

        /* ===== DATATABLES ADAPTATIVO ===== */
        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
            margin: 1rem 0 !important;
        }

        table.dataTable thead th,
        table.dataTable.no-footer {
            border-bottom: 1px solid #e5e7eb !important;
        }
        .dark table.dataTable thead th,
        .dark table.dataTable.no-footer {
            border-bottom-color: #252345 !important;
        }

        table.dataTable thead th {
            background-color: #f3f4f6 !important;
            background-image: none !important;
            color: #374151 !important;
            padding: 12px 16px !important;
        }
        .dark table.dataTable thead th {
            background-color: #0f0d23 !important;
            color: #94a3b8 !important;
        }

        table.dataTable tbody tr {
            background-color: transparent !important;
        }
        table.dataTable tbody td {
            border-bottom: 1px solid #e5e7eb !important;
        }
        .dark table.dataTable tbody td {
            border-bottom-color: #252345 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: #f3f4f6 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            color: #374151 !important;
            padding: 0.4rem 0.8rem !important;
            margin: 0 0.2rem !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: #161430 !important;
            border-color: #252345 !important;
            color: #a0a0c0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e5e7eb !important;
            color: #1f2937 !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #252345 !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: #ffffff !important;
            border: 1px solid #d1d5db !important;
            color: #1f2937 !important;
            border-radius: 0.75rem !important;
            padding: 0.6rem 1rem !important;
            outline: none !important;
        }
        .dark .dataTables_wrapper .dataTables_length select,
        .dark .dataTables_wrapper .dataTables_filter input {
            background-color: #0f0d23 !important;
            border-color: #252345 !important;
            color: white !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding-left: 2.5rem !important;
        }
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            gap: 0.75rem;
            align-items: center;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden selection:bg-indigo-500/30">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">

        <!-- Overlay para móvil -->
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <!-- Sidebar -->
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
                $tituloPagina = "Normalización de Tiempos";
                $tituloPaginaResponsive = "Normalización";
                $iconModulo = "fas fa-arrows-alt-h";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-arrows-alt-h text-indigo-500"></i> Normalización de Tiempos (RF-08)
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Conversión inteligente entre piscina de 25m y 50m</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <!-- Toggle papelera -->
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'anular') || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'reactivar')): ?>
                        <button id="toggleEstadoNormalizacionBtn" class="group relative flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] hover:border-red-500/50 transition-all duration-200">
                            <i id="toggleIconoNormalizacion" class="fas fa-trash-alt text-gray-500 dark:text-gray-400 group-hover:text-red-400 transition-colors"></i>
                            <span id="toggleTextoNormalizacion" class="text-xs font-medium text-gray-700 dark:text-gray-300">Activos</span>
                            <div class="absolute -top-2 -right-2">
                                <span id="estadoBadgeNormalizacion" class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">A</span>
                            </div>
                        </button>
                        <?php endif; ?>
                        <!-- Botón nuevo registro -->
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'registrar')): ?>
                        <button onclick="abrirModalFormulario()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-plus-circle text-sm"></i> Nueva Conversión
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KPIs (3 tarjetas) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                            <i class="fas fa-stopwatch text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl z-10">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Total Registros</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_total_registros">--</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                            <i class="fas fa-calculator text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl z-10">
                            <i class="fas fa-arrows-alt-h"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Promedio Convertido</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_promedio_convertido">--s</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-amber-500/10 group-hover:text-amber-500/20 transition-colors">
                            <i class="fas fa-clock text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl z-10">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Último Registro</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_ultimo_registro">--</h3>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex items-center justify-between gap-2 border-b border-gray-200 dark:border-[#252345] pb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-filter text-indigo-500 dark:text-indigo-400 text-sm"></i>
                            <h3 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">Filtros de Búsqueda</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                            </div>
                            <select id="filtroAtletaNormalizacion" class="w-full input-adapt pl-12 pr-10 py-3 rounded-xl text-sm appearance-none shadow-inner cursor-pointer">
                                <option value="">👤 Todos los Atletas</option>
                            </select>
                        </div>

                        <select id="filtroEstiloNormalizacion" class="w-full input-adapt px-4 py-3 rounded-xl text-sm cursor-pointer">
                            <option value="">🏊 Todos los Estilos</option>
                            <option value="Libre">Libre</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Braza">Braza</option>
                            <option value="Mariposa">Mariposa</option>
                            <option value="Combinado">Combinado</option>
                        </select>

                        <select id="filtroDistanciaNormalizacion" class="w-full input-adapt px-4 py-3 rounded-xl text-sm cursor-pointer">
                            <option value="">📏 Todas las Distancias</option>
                            <option value="50">50m</option>
                            <option value="100">100m</option>
                            <option value="200">200m</option>
                            <option value="400">400m</option>
                            <option value="800">800m</option>
                            <option value="1500">1500m</option>
                        </select>

                        <select id="filtroPiscinaNormalizacion" class="w-full input-adapt px-4 py-3 rounded-xl text-sm cursor-pointer">
                            <option value="">🏊‍♂️ Todas las Piscinas</option>
                            <option value="25m">Corta (25m)</option>
                            <option value="50m">Larga (50m)</option>
                        </select>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button onclick="cargarTablaNormalizacion()" class="bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl flex items-center gap-2 transition cursor-pointer py-2.5 px-5 text-xs font-bold uppercase tracking-wider shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-sync-alt"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de normalización -->
                <div class="mt-2">
                    <h2 id="tituloTablaNormalizacion" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                    </h2>
                    <div id="tablaNormalizacionContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300 p-4 sm:p-6">
                        <div class="overflow-x-auto">
                            <table id="tablaNormalizacion" class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Atleta</th>
                                        <th>Estilo</th>
                                        <th>Distancia</th>
                                        <th>Origen</th>
                                        <th>T. Original (s)</th>
                                        <th class="text-indigo-400 font-bold">T. Normalizado (s)</th>
                                        <th>Estado</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaNormalizacion" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                    <tr><td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR ===== -->
    <div id="modalFormulario" class="fixed inset-0 z-[100] hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">

            <button type="button" onclick="cerrarModalFormulario()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-gray-100 dark:bg-[#161430] p-6 border-b border-gray-200 dark:border-white/5 flex items-center relative z-10 transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-arrows-alt-h text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <h2 id="tituloModal" class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Conversión</h2>
            </div>

            <form id="formularioNormalizacion" class="p-8 space-y-6 relative z-10">
                <input type="hidden" id="accion" name="accion" value="registrar">
                <input type="hidden" id="id_normalizacion" name="id_normalizacion" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-user text-indigo-500 w-5"></i> Atleta *
                        </label>
                        <select id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm font-medium" required>
                            <option value="">Seleccione un atleta...</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-swimmer text-indigo-500 w-5"></i> Estilo *
                        </label>
                        <select id="estilo" name="estilo" data-validar="requerido" data-nombre="Estilo" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm">
                            <option value="Libre">Libre</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Braza">Braza</option>
                            <option value="Mariposa">Mariposa</option>
                            <option value="Combinado">Combinado</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-horizontal text-indigo-500 w-5"></i> Distancia (m) *
                        </label>
                        <select id="distancia_m" name="distancia_m" data-validar="requerido" data-nombre="Distancia" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm">
                            <option value="50">50m</option>
                            <option value="100">100m</option>
                            <option value="200">200m</option>
                            <option value="400">400m</option>
                            <option value="800">800m</option>
                            <option value="1500">1500m</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-swimming-pool text-indigo-500 w-5"></i> Piscina Origen *
                        </label>
                        <select id="tipo_piscina_origen" name="tipo_piscina_origen" data-validar="requerido" data-nombre="Piscina Origen" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm">
                            <option value="25m">Corta (25m)</option>
                            <option value="50m">Larga (50m)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-stopwatch text-indigo-500 w-5"></i> Tiempo (segundos) *
                        </label>
                        <input type="number" step="0.01" id="tiempo_original_seg" name="tiempo_original_seg" data-validar="requerido|decimal" data-min-num="0" data-max-num="999" data-nombre="Tiempo" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 24.50" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-calculator text-indigo-500 w-5"></i> Tiempo Convertido (calculado automáticamente)
                        </label>
                        <input type="text" id="tiempo_convertido_preview" readonly class="w-full p-3.5 rounded-xl bg-gray-100 dark:bg-[#0f0d23] border border-gray-300 dark:border-[#252345] text-emerald-600 dark:text-emerald-400 font-mono font-bold text-sm cursor-not-allowed" value="--">
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-white/5">
                    <button type="button" onclick="cerrarModalFormulario()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-[#252345] dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardarNormalizacion" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR Y CONVERTIR <i class="fas fa-bolt ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE (opcional) ===== -->
    <div id="modalVer" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 md:p-10" id="detalleContenido"></div>
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
    </script>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/tour.js"></script>

    <!-- Permisos inyectados -->
    <script>
        const PERMISOS_NORMALIZACION = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>

    <!-- Estado inicial del toggle -->
    <script>
        window.modoPapeleraNormalizacion = false;
        function actualizarTituloTablaNormalizacion() {
            const titulo = document.getElementById('tituloTablaNormalizacion');
            const container = document.getElementById('tablaNormalizacionContainer');
            if (titulo) {
                if (window.modoPapeleraNormalizacion) {
                    titulo.innerHTML = '<i class="fas fa-trash-alt"></i> Mostrando Registros Anulados (Papelera)';
                    titulo.className = 'text-lg font-bold text-red-600 dark:text-red-400 mb-3 ml-2 flex items-center gap-2';
                    if (container) {
                        container.classList.remove('border-t-indigo-500');
                        container.classList.add('border-t-red-500');
                    }
                } else {
                    titulo.innerHTML = '<i class="fas fa-check-circle"></i> Mostrando Registros Activos';
                    titulo.className = 'text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2';
                    if (container) {
                        container.classList.remove('border-t-red-500');
                        container.classList.add('border-t-indigo-500');
                    }
                }
            }
        }
        document.addEventListener('modoPapeleraNormalizacionChanged', actualizarTituloTablaNormalizacion);
        document.addEventListener('DOMContentLoaded', actualizarTituloTablaNormalizacion);
    </script>

    <script src="assets/js/normalizacion.js"></script>
</body>
</html>