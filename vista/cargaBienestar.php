<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'cargaBienestar';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Carga y Bienestar (RPE) | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- DataTables CSS + Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* ===== INPUTS ADAPTATIVOS ===== */
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
        /* .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(0);
        } */

         /* Para el calendario (date) en modo oscuro */
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

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

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== MODALES ===== */
        .modal-scroll { max-height: 90vh; overflow-y: auto; }
        .modal-header-sticky { position: sticky; top: 0; z-index: 20; }

        /* ===== BOTÓN PARPADEANTE PARA INCONSISTENCIAS ===== */
        @keyframes blink {
            0% { background-color: rgba(239,68,68,0.2); border-color: #ef4444; }
            50% { background-color: rgba(239,68,68,0.8); border-color: #ef4444; color: white; }
            100% { background-color: rgba(239,68,68,0.2); border-color: #ef4444; }
        }
        .btn-blink { animation: blink 1s infinite; font-weight: bold; }

        /* ===== TOGGLE PAPELERA ===== */
        #toggleEstadoRPEBtn.active {
            border-color: #ef4444;
            background: rgba(239,68,68,0.1);
        }
        #toggleEstadoRPEBtn.active #toggleIconoRPE { color: #ef4444; }
        #toggleEstadoRPEBtn.active #toggleTextoRPE { color: #ef4444; }

        /* ===== DATATABLES ADAPTATIVO ===== */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151 !important;
        }
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_processing,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #a0a0c0 !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            width: 280px;
            max-width: 100%;
        }
        .dark .dataTables_wrapper .dataTables_filter input {
            background: #0f0d23;
            border-color: #252345;
            color: white;
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 0.4rem 1.5rem 0.4rem 0.75rem !important;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
        }
        .dark .dataTables_wrapper .dataTables_length select {
            background-color: #0f0d23;
            border-color: #252345;
            color: white;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #374151 !important;
            background: #f3f4f6 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.4rem 0.8rem !important;
            margin: 0 0.2rem !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #a0a0c0 !important;
            background: #161430 !important;
            border-color: #252345 !important;
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

        table.dataTable tbody tr {
            background-color: transparent !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #e5e7eb !important;
        }
        .dark table.dataTable.no-footer {
            border-bottom-color: #252345 !important;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e5e7eb !important;
            color: #6b7280 !important;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark table.dataTable thead th {
            border-bottom-color: #252345 !important;
            color: #9ca3af !important;
        }

         /* ===== DATATABLES ADAPTATIVO (copiado de antropometria) ===== */
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

        /* Para el resto de tablas (inconsistencias) - opcional */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151 !important;
        }
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_processing,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #a0a0c0 !important;
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
                $tituloPagina = "Carga Interna y Bienestar (RPE)";
                $tituloPaginaResponsive = "Carga y Bienestar";
                $iconModulo = "fas fa-heartbeat";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-heartbeat text-indigo-500"></i> Carga Interna y Bienestar (RPE)
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Monitoreo de fatiga, sueño y percepción de esfuerzo (RF-11)</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <!-- Toggle papelera -->
                        <button id="toggleEstadoRPEBtn" class="group relative flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] hover:border-red-500/50 transition-all duration-200">
                            <i id="toggleIconoRPE" class="fas fa-trash-alt text-gray-500 dark:text-gray-400 group-hover:text-red-400 transition-colors"></i>
                            <span id="toggleTextoRPE" class="text-xs font-medium text-gray-700 dark:text-gray-300">Activos</span>
                            <div class="absolute -top-2 -right-2">
                                <span id="estadoBadgeRPE" class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">A</span>
                            </div>
                        </button>
                        <!-- Botón nuevo registro -->
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar')): ?>
                        <button onclick="abrirModalRPE()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-plus-circle text-sm"></i> Nuevo Registro
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KPIs (adaptados a RPE) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                            <i class="fas fa-chart-line text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl z-10">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">RPE Promedio (Últ. 30d)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_rpe_promedio">--</h3>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-amber-500/10 group-hover:text-amber-500/20 transition-colors">
                            <i class="fas fa-moon text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl z-10">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Horas Sueño Promedio</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_sueno_promedio">--</h3>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                            <i class="fas fa-charging-station text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl z-10">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">sRPE Semanal Promedio</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_srpe_semanal">--</h3>
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                            </div>
                            <select id="filtroAtletaRPE" class="w-full input-adapt pl-12 pr-10 py-3 rounded-xl text-sm appearance-none shadow-inner cursor-pointer">
                                <option value="">👤 Todos los Atletas</option>
                            </select>
                        </div>
                        <input type="date" id="filtroFechaInicio" class="input-adapt px-4 py-3 rounded-xl text-sm" placeholder="Fecha inicio">
                        <input type="date" id="filtroFechaFin" class="input-adapt px-4 py-3 rounded-xl text-sm" placeholder="Fecha fin">
                    </div>
                    <div class="flex justify-end mt-3">
                        <button onclick="cargarTablaRPE()" class="bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl flex items-center gap-2 transition cursor-pointer py-2.5 px-5 text-xs font-bold uppercase tracking-wider shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-sync-alt"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de registros RPE -->
                  <!-- ===== TABLA PRINCIPAL (con el nuevo diseño) ===== -->
                <div class="mt-2">
                    <h2 id="tituloTablaRPE" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                    </h2>
                    <div id="tablaRPEContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300 p-4 sm:p-6">
                        <div class="overflow-x-auto">
                            <table id="tablaRPE" class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Atleta</th>
                                        <th>RPE (0-10)</th>
                                        <th>sRPE</th>
                                        <th>Sueño (h)</th>
                                        <th>Estado DB</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCuerpoRPE" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sección de Inconsistencias Biológicas -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg transition-colors duration-300 p-4 sm:p-6">
                    <div class="flex justify-between items-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="text-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400"></i>
                                Alertas de Inconsistencia Biológica
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Registros RPE = 1 (reposo total) con récord personal el mismo día</p>
                        </div>
                        <button onclick="cargarTablaInconsistenciasRPE()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">
                            <i class="fas fa-sync-alt"></i> Refrescar
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-[#252345] uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Fecha</th>
                                    <th class="px-6 py-4 font-bold">Atleta</th>
                                    <th class="px-6 py-4 font-bold">RPE</th>
                                    <th class="px-6 py-4 font-bold">Récord Personal</th>
                                    <th class="px-6 py-4 font-bold">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaInconsistenciasRPE" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando alertas...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>
 <!--                <div class="mt-2">
                    <h2 id="tituloTablaRPE" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                    </h2>
                    <div id="tablaRPEContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300">
                        <div class="overflow-x-auto">
                            <table id="tablaRPE" class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-[#252345] uppercase text-[10px] tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Fecha</th>
                                        <th class="px-6 py-4 font-bold">Atleta</th>
                                        <th class="px-6 py-4 font-bold">RPE (0-10)</th>
                                        <th class="px-6 py-4 font-bold">sRPE</th>
                                        <th class="px-6 py-4 font-bold">Sueño (h)</th>
                                        <th class="px-6 py-4 font-bold">Estado DB</th>
                                        <th class="px-6 py-4 font-bold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCuerpoRPE" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->

                <!-- Sección de Inconsistencias Biológicas -->
    <!--             <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg transition-colors duration-300">
                    <div class="p-5 border-b border-gray-200 dark:border-[#252345] flex justify-between items-center flex-wrap gap-3">
                        <div>
                            <h3 class="text-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400"></i>
                                Alertas de Inconsistencia Biológica
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Registros RPE = 1 (reposo total) con récord personal el mismo día</p>
                        </div>
                        <button onclick="cargarTablaInconsistenciasRPE()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">
                            <i class="fas fa-sync-alt"></i> Refrescar
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-[#252345] uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Fecha</th>
                                    <th class="px-6 py-4 font-bold">Atleta</th>
                                    <th class="px-6 py-4 font-bold">RPE</th>
                                    <th class="px-6 py-4 font-bold">Récord Personal</th>
                                    <th class="px-6 py-4 font-bold">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaInconsistenciasRPE" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando alertas...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div> -->

    <!-- ===== MODAL REGISTRO/EDICIÓN RPE ===== -->
    <div id="modalRPE" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] flex flex-col max-h-[90vh] transition-colors duration-300">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 relative modal-header-sticky">
                <button type="button" onclick="cerrarModalRPE()" class="absolute top-6 right-6 text-white/70 hover:text-white hover:rotate-90 transition-all duration-300 cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-black text-white" id="modalTituloRPE">Registrar Carga Interna (RPE)</h2>
                <p class="text-indigo-100 text-sm mt-1">Complete los datos de fatiga y bienestar subjetivo.</p>
            </div>
            <div class="overflow-y-auto p-8 modal-scroll">
                <form id="formRPE" class="space-y-6">
                    <input type="hidden" name="id_rpe" id="id_rpe">
                    <input type="hidden" name="accion" id="accionRPE" value="registrar">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Atleta *</label>
                            <select name="id_atleta" id="id_atleta_rpe" class="w-full input-adapt rounded-xl px-4 py-3 cursor-pointer" required>
                                <option value="">Seleccione atleta...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Fecha *</label>
                            <input type="date" name="fecha" id="fecha_rpe" class="w-full input-adapt rounded-xl px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">RPE (1-10) *</label>
                            <input type="number" name="rpe" id="rpe_valor" min="1" max="10" class="w-full input-adapt rounded-xl px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Duración (minutos)</label>
                            <input type="number" name="duracion_minutos" id="duracion_minutos" class="w-full input-adapt rounded-xl px-4 py-3" placeholder="Opcional, se calcula sRPE">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Metros nadados</label>
                            <input type="number" name="metros_nadados" id="metros_nadados" class="w-full input-adapt rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Horas de sueño</label>
                            <input type="number" step="0.5" name="horas_sueno" id="horas_sueno" class="w-full input-adapt rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Calidad de sueño (1-10)</label>
                            <input type="number" name="calidad_sueno" id="calidad_sueno" min="1" max="10" class="w-full input-adapt rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Sensación muscular (1-10)</label>
                            <input type="number" name="sensacion_muscular" id="sensacion_muscular" min="1" max="10" class="w-full input-adapt rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Estrés percibido (1-10)</label>
                            <input type="number" name="estres_percibido" id="estres_percibido" min="1" max="10" class="w-full input-adapt rounded-xl px-4 py-3">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Observaciones</label>
                            <textarea name="observaciones" id="observaciones_rpe" rows="2" class="w-full input-adapt rounded-xl px-4 py-3 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="cerrarModalRPE()" class="flex-1 border border-gray-300 dark:border-[#252345] text-gray-700 dark:text-gray-300 py-3 rounded-xl font-bold hover:bg-gray-100 dark:hover:bg-[#252345] transition uppercase text-xs">Cancelar</button>
                        <button type="submit" id="btnGuardarRPE" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold uppercase text-xs shadow-lg shadow-indigo-500/20">
                            Guardar Registro <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVerRPE" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVerRPE()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 md:p-10" id="contenidoDetalleRPE"></div>
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

    <!-- Permisos inyectados desde PHP -->
    <script>
        const PERMISOS_RPE = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>

    <!-- ===== INICIALIZADOR (para que el JS sepa el modo) ===== -->
    <script>
        // Esta variable se usará desde cargaBienestar.js para saber el modo
        window.modoPapeleraRPE = false;

        function actualizarTituloTablaRPE() {
            const titulo = document.getElementById('tituloTablaRPE');
            const container = document.getElementById('tablaRPEContainer');
            if (titulo) {
                if (window.modoPapeleraRPE) {
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

        // Escuchamos el evento personalizado que disparará el JS cuando cambie el modo
        document.addEventListener('modoPapeleraRPEChanged', actualizarTituloTablaRPE);

        // Actualizar al cargar
        document.addEventListener('DOMContentLoaded', actualizarTituloTablaRPE);
    </script>

    <!-- Nuestro JS refactorizado (cargaBienestar.js) - Asegurarse de que exista -->
    <script src="assets/js/cargaBienestar.js"></script>
</body>
</html>