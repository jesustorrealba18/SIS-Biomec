<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'antropometria';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Seguimiento Antropométrico | SGRD</title>
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
        /* ===== ESTILOS BASE (igual que en cargaBienestar) ===== */
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

        /* Toggle papelera (igual que RPE) */
        #toggleEstadoAntropometriaBtn.active {
            border-color: #ef4444;
            background: rgba(239,68,68,0.1);
        }
        #toggleEstadoAntropometriaBtn.active #toggleIconoAntropometria { color: #ef4444; }
        #toggleEstadoAntropometriaBtn.active #toggleTextoAntropometria { color: #ef4444; }

        /* DataTables adaptado (igual que en cargaBienestar) */
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

        /* ===== DATATABLES ADAPTATIVO (copiado de marcas) ===== */
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

/* Paginación */
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

/* Buscador y selector de registros */
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

        <!-- Sidebar (igual que antes) -->
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
                $tituloPagina = "Expedientes Antropométricos";
                $tituloPaginaResponsive = "Antropometría";
                $iconModulo = "fas fa-child";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-child text-indigo-500"></i> Dashboard Antropométrico
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Control y evolución biológica de atletas (RF-05)</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <!-- Toggle papelera -->
                         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'eliminar') || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'reactivar')): ?>
                        <button id="toggleEstadoAntropometriaBtn" class="group relative flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] hover:border-red-500/50 transition-all duration-200">
                            <i id="toggleIconoAntropometria" class="fas fa-trash-alt text-gray-500 dark:text-gray-400 group-hover:text-red-400 transition-colors"></i>
                            <span id="toggleTextoAntropometria" class="text-xs font-medium text-gray-700 dark:text-gray-300">Activos</span>
                            <div class="absolute -top-2 -right-2">
                                <span id="estadoBadgeAntropometria" class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">A</span>
                            </div>
                        </button>
                        <?php endif; ?>
                        <!-- Botón nuevo registro -->
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar')): ?>
                        <button onclick="abrirModalMedicion()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-plus-circle text-sm"></i> Nueva Medición
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KPIs (4 tarjetas) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-red-500/10 group-hover:text-red-500/20 transition-colors">
                            <i class="fas fa-clock text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 text-xl z-10">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Pendientes de Medición</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_pendientes">--</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                            <i class="fas fa-calculator text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl z-10">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">IMC Promedio (Activos)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_imc_promedio">--</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                            <i class="fas fa-calendar-check text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl z-10">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Mediciones (Últ. 30d)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_mediciones_mes">--</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-purple-500/10 group-hover:text-purple-500/20 transition-colors">
                            <i class="fas fa-percent text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl z-10">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Cobertura (Últ. 90d)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_cobertura">--%</h3>
                        </div>
                    </div>
                </div>


                <!-- Tabla de mediciones (con DataTables) -->
<div class="mt-2">
    <h2 id="tituloTablaAntropometria" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> Mostrando Mediciones Activas
    </h2>
    <div id="tablaAntropometriaContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300 p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tablaAntropometria" class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Atleta</th>
                        <th>Peso (kg)</th>
                        <th>Talla (cm)</th>
                        <th>IMC</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCuerpoAntropometria" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                    <!-- Se llena desde JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>


                <!-- Sección de Alertas: Atletas con medición vencida -->
<div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg transition-colors duration-300 p-4 sm:p-6">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="text-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-amber-500 dark:text-amber-400"></i>
                Atletas con Medición Vencida
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Atletas activos que superan los 84 días sin evaluación o nunca han sido medidos</p>
        </div>
        <button onclick="cargarAlertasAntropometria()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">
            <i class="fas fa-sync-alt"></i> Refrescar
        </button>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaAlertasAntropometria" class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th>Atleta</th>
                    <th>Categoría</th>
                    <th>Última Medición</th>
                    <th>Días sin medir</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="listaAlertasAntropometria" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando alertas...</td></tr>
            </tbody>
        </table>
    </div>
</div>

            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR MEDICIÓN ===== -->
    <div id="modalMedicion" class="fixed inset-0 z-[100] hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            
            <button type="button" onclick="cerrarModalMedicion()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-gray-100 dark:bg-[#161430] p-6 border-b border-gray-200 dark:border-white/5 flex items-center relative z-10 transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-weight text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <h2 id="modalMedicionTitulo" class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Medición</h2>
            </div>

            <form id="formMedicion" class="p-8 space-y-6 relative z-10">
                <input type="hidden" id="accion" name="accion" value="guardar">
                <input type="hidden" id="id_medicion" name="id_medicion">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-user text-indigo-500 w-5"></i> Atleta *
                        </label>
                        <select id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm font-medium">
                            <option value="">Seleccione un atleta...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-calendar-alt text-indigo-500 w-5"></i> Fecha de Evaluación *
                        </label>
                        <!-- Agregamos fecha_logica -->
                        <input type="date" id="fecha" name="fecha" data-validar="requerido|fecha_logica" data-nombre="Fecha" class="w-full p-3.5 rounded-xl input-adapt text-sm" max="<?= date('Y-m-d') ?>">
                    </div>

                   <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-weight-hanging text-indigo-500 w-5"></i> Peso (kg) *
                        </label>
                        <!-- Peso realista: de 20 kg a 200 kg -->
                        <input type="text" inputmode="decimal" maxlength="6" id="peso" name="peso" data-validar="requerido|decimal" data-min-num="20" data-max-num="200" data-nombre="Peso" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 75.50">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-vertical text-indigo-500 w-5"></i> Talla (cm) *
                        </label>
                        <!-- Talla realista: de 100 cm a 250 cm -->
                        <input type="text" inputmode="decimal" maxlength="6" id="talla" name="talla" data-validar="requerido|decimal" data-min-num="100" data-max-num="250" data-nombre="Talla" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 180.5">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-horizontal text-indigo-500 w-5"></i> Envergadura (cm) *
                        </label>
                        <!-- Envergadura realista: de 100 cm a 250 cm -->
                        <input type="text" inputmode="decimal" maxlength="6" id="envergadura" name="envergadura" data-validar="requerido|decimal" data-min-num="100" data-max-num="250" data-nombre="Envergadura" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 185">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-circle-notch text-indigo-500 w-5"></i> Perím. Abdominal (cm) *
                        </label>
                        <!-- Perímetro realista: de 40 cm a 150 cm -->
                        <input type="text" inputmode="decimal" maxlength="6" id="perimetro_abdominal" name="perimetro_abdominal" data-validar="requerido|decimal" data-min-num="40" data-max-num="150" data-nombre="Perímetro Abdominal" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 80.0">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-percent text-indigo-500 w-5"></i> % Grasa Corporal
                        </label>
                        <!-- Grasa biológica: de 3% a 50% -->
                        <input type="text" inputmode="decimal" maxlength="5" id="grasa_corporal" name="grasa_corporal" data-validar="decimal" data-min-num="3" data-max-num="50" data-nombre="Grasa Corporal" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Opcional">
                    </div>
                </div>

                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-500/30 rounded-xl flex justify-between items-center mt-4 transition-colors duration-300">
                    <span class="text-sm text-indigo-600 dark:text-indigo-300"><i class="fas fa-calculator mr-2"></i>IMC Proyectado:</span>
                    <span id="imc_preview" class="text-xl font-bold text-gray-900 dark:text-white">--</span>
                </div>

                <div id="contenedorJustificacion" class="space-y-2 hidden mt-4">
                    <label class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Justificación de la Modificación *
                    </label>
                    <textarea id="justificacion" name="justificacion" rows="2" class="w-full p-3.5 rounded-xl input-adapt text-sm border-orange-500/50 focus:border-orange-500" placeholder="Auditoría: Explique brevemente por qué corrige este registro."></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-white/5">
                    <button type="button" onclick="cerrarModalMedicion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-[#252345] dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL GRÁFICAS ===== -->
    <div id="modalGraficas" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-5xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            
            <button type="button" onclick="cerrarModalGraficas()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-gray-100 dark:bg-[#161430] p-6 border-b border-gray-200 dark:border-white/5 flex items-center relative z-10 transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Evolución Antropométrica</h2>
            </div>

            <div class="p-6 space-y-8 relative z-10">
                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 transition-colors duration-300">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/30 flex items-center justify-center text-xl font-bold text-indigo-600 dark:text-indigo-300">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <div>
                        <h3 id="graficaAtletaNombre" class="text-lg font-bold text-gray-900 dark:text-white">Cargando...</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Historial de mediciones corporales</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="tarjeta p-4 transition-colors duration-300">
                        <h4 class="text-center text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Evolución de Peso (kg) y Talla (cm)</h4>
                        <canvas id="chartPesoTalla" height="200"></canvas>
                    </div>
                    <div class="tarjeta p-4 transition-colors duration-300">
                        <h4 class="text-center text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Curva del Índice de Masa Corporal (IMC)</h4>
                        <canvas id="chartIMC" height="200"></canvas>
                    </div>
                </div>

              
<div>
    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Registros Históricos</h4>
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-[#252345] p-4 sm:p-6 bg-white dark:bg-[#161430]">
        <table id="tablaHistorialAntropometria" class="w-full text-left text-sm border-collapse">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Peso</th>
                    <th>Talla</th>
                    <th>Envergadura</th>
                    <th>IMC</th>
                    <th>Responsable</th>
                    <th class="text-center">Edición</th>
                </tr>
            </thead>
            <tbody id="tablaHistorialBody" class="divide-y divide-gray-200 dark:divide-[#252345] bg-white dark:bg-[#161430] text-gray-800 dark:text-gray-300">
                <!-- Se llena desde JS -->
            </tbody>
        </table>
    </div>
</div>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script>
        (function() {
            // Código de toggle del menú (igual que antes)
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
        const PERMISOS_ANTROPOMETRIA = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>

    <!-- Estado inicial del toggle -->
    <script>
        window.modoPapeleraAntropometria = false;
        function actualizarTituloTablaAntropometria() {
            const titulo = document.getElementById('tituloTablaAntropometria');
            const container = document.getElementById('tablaAntropometriaContainer');
            if (titulo) {
                if (window.modoPapeleraAntropometria) {
                    titulo.innerHTML = '<i class="fas fa-trash-alt"></i> Mostrando Mediciones Anuladas (Papelera)';
                    titulo.className = 'text-lg font-bold text-red-600 dark:text-red-400 mb-3 ml-2 flex items-center gap-2';
                    if (container) {
                        container.classList.remove('border-t-indigo-500');
                        container.classList.add('border-t-red-500');
                    }
                } else {
                    titulo.innerHTML = '<i class="fas fa-check-circle"></i> Mostrando Mediciones Activas';
                    titulo.className = 'text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2';
                    if (container) {
                        container.classList.remove('border-t-red-500');
                        container.classList.add('border-t-indigo-500');
                    }
                }
            }
        }
        document.addEventListener('modoPapeleraAntropometriaChanged', actualizarTituloTablaAntropometria);
        document.addEventListener('DOMContentLoaded', actualizarTituloTablaAntropometria);
    </script>

    <!-- Nuestro JS refactorizado -->
    <script src="assets/js/antropometria.js"></script>
</body>
</html>