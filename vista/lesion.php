<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'lesion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control Clínico de Lesiones | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
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
                $tituloPagina = "Control Clínico de Lesiones";
                $tituloPaginaResponsive = "Lesiones";
                $iconModulo = "fas fa-notes-medical";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado (resumen) -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-notes-medical text-indigo-500"></i> Control de Lesiones
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Gestión de Lesiones y Estados de Salud (RF-10)</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'registrar')): ?>
                    <button onclick="abrirModal()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Lesión
                    </button>
                    <?php endif; ?>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                            <i class="fas fa-user-injured text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl z-10">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Lesiones Activas</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_activas">0</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-red-500/10 group-hover:text-red-500/20 transition-colors">
                            <i class="fas fa-exclamation-triangle text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 text-xl z-10">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Molestia Alta (>7)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_molestia_alta">0</h3>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden group transition-colors duration-300">
                        <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                            <i class="fas fa-calendar-week text-8xl"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl z-10">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="z-10">
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Reposo Promedio (días)</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1" id="kpi_reposo_promedio">0</h3>
                        </div>
                    </div>
                </div>
<div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg p-5 shadow-sm">
    <div class="flex items-center gap-3 mb-4">
        <div class="p-2 bg-red-100 dark:bg-red-500/30 rounded-full">
            <i class="fas fa-heartbeat text-red-600 dark:text-red-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-red-800 dark:text-red-300">Atletas en Riesgo Clínico</h3>
    </div>
    
    <ul id="listaRiesgos" class="space-y-3">
        <!-- Se llenará dinámicamente desde JavaScript -->
        <li class="text-center text-gray-500 dark:text-gray-400 py-4">
            <i class="fas fa-spinner fa-spin mr-2"></i> Cargando alertas...
        </li>
    </ul>
</div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-[#252345] pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-filter text-indigo-500 dark:text-indigo-400 text-sm"></i>
                            <h3 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">Filtros de Búsqueda</h3>
                        </div>
                        <button id="btnMostrarPapelera" class="text-xs bg-red-50 dark:bg-red-500/20 hover:bg-red-100 dark:hover:bg-red-500/40 text-red-600 dark:text-red-300 px-3 py-1 rounded-full transition flex items-center gap-1 cursor-pointer">
                            <i class="fas fa-trash-alt"></i> Ver Papelera
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                            </div>
                            <select id="filtroAtleta" class="w-full input-adapt pl-12 pr-10 py-3 rounded-xl text-sm appearance-none shadow-inner cursor-pointer">
                                <option value="">👤 Todos los Atletas</option>
                            </select>
                        </div>

                        <select id="filtroEstadoClinico" class="w-full input-adapt px-4 py-2.5 rounded-xl text-xs cursor-pointer">
                            <option value="">🏥 Todos los Estados Clínicos</option>
                            <option value="Activa">🟢 Activa</option>
                            <option value="EnRehabilitacion">🟡 En Rehabilitación</option>
                            <option value="Recuperada">✅ Recuperada</option>
                            <option value="Cronica">⚠️ Crónica</option>
                        </select>

                        <select id="filtroTipo" class="w-full input-adapt px-4 py-2.5 rounded-xl text-xs cursor-pointer">
                            <option value="">📌 Todos los Tipos</option>
                            <option value="Sobreuso">Sobrecarga/Sobreuso</option>
                            <option value="Aguda">Aguda (Traumática)</option>
                            <option value="Recidiva">Recidiva (Reincidente)</option>
                        </select>

                        <select id="filtroZona" class="w-full input-adapt px-4 py-2.5 rounded-xl text-xs cursor-pointer">
                            <option value="">🦴 Todas las Zonas</option>
                            <option value="Hombro">Hombro</option>
                            <option value="Rodilla">Rodilla</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Tobillo">Tobillo</option>
                            <option value="Otra">Otra</option>
                        </select>
                    </div>

                    <div class="flex justify-end mt-3">
                        <button onclick="cargarTabla()" class="bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl flex items-center gap-2 transition cursor-pointer py-2 px-5 text-xs font-bold uppercase tracking-wider shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-sync-alt"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de lesiones -->

                <!-- Tabla de lesiones -->
<div class="mt-2">
    <h2 id="tituloTablaState" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> Mostrando Registros Activos
    </h2>
    <div id="tablaLesionesContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300 p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tablaLesiones" class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr>
                        <th>Fecha Inicio</th>
                        <th>Atleta</th>
                        <th>Zona / Lado</th>
                        <th>Molestia</th>
                        <th>Estado Clínico</th>
                        <th>Status DB</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCuerpo" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                    <!-- Se llena desde JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>


                <!-- <div class="mt-2">
                    <h2 id="tituloTablaState" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                    </h2>
                    <div id="tablaLesionesContainer" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-lg border-t-2 border-t-indigo-500 transition-colors duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-[#252345] uppercase text-[10px] tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Fecha Inicio</th>
                                        <th class="px-6 py-4 font-bold">Atleta</th>
                                        <th class="px-6 py-4 font-bold">Zona / Lado</th>
                                        <th class="px-6 py-4 font-bold">Molestia</th>
                                        <th class="px-6 py-4 font-bold">Estado Clínico</th>
                                        <th class="px-6 py-4 font-bold text-center">Status DB</th>
                                        <th class="px-6 py-4 font-bold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCuerpo" class="divide-y divide-gray-200 dark:divide-[#252345] text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando registros médicos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->
            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR ===== -->
    <div id="modalFormulario" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] flex flex-col max-h-[90vh] transition-colors duration-300">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 relative modal-header-sticky">
                <button type="button" onclick="cerrarModal()" class="absolute top-6 right-6 text-white/70 hover:text-white hover:rotate-90 transition-all duration-300 cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-black text-white" id="tituloModal">Registrar Nueva Lesión</h2>
                <p class="text-indigo-100 text-sm mt-1">Componente del Sistema Inteligente de Prevención.</p>
            </div>

            <div class="overflow-y-auto p-8 modal-scroll">
                <form id="formularioLesion" class="space-y-6">
                    <input type="hidden" name="id_lesion" id="id_lesion">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Atleta Afectado *</label>
                            <select name="id_atleta" id="id_atleta" class="w-full input-adapt rounded-xl px-4 py-3 cursor-pointer" required data-validar="requerido" data-nombre="Atleta">
                                <option value="">Seleccione el atleta...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="w-full input-adapt rounded-xl px-4 py-3" required data-validar="requerido|fecha_logica" data-nombre="Fecha de inicio">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Fecha Estimada Recuperación</label>
                            <input type="date" name="fecha_estimada_recup" id="fecha_estimada_recup" class="w-full input-adapt rounded-xl px-4 py-3" data-validar="fecha_logica" data-nombre="Fecha estimada de recuperación" data-depende="fecha_inicio" data-mensaje="La fecha estimada no puede ser anterior a la fecha de inicio">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Zona Anatómica *</label>
                            <select name="zona_anatomica" id="zona_anatomica" class="w-full input-adapt rounded-xl px-4 py-3 cursor-pointer" required data-validar="requerido" data-nombre="Zona anatómica">
                                <option value="">Seleccione...</option>
                                <option value="Hombro">Hombro</option>
                                <option value="Rodilla">Rodilla</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Codo">Codo</option>
                                <option value="Tobillo">Tobillo</option>
                                <option value="Cervical">Cervical</option>
                                <option value="Lumbar">Lumbar</option>
                                <option value="Muslo">Muslo</option>
                                <option value="Gemelo">Gemelo</option>
                                <option value="Pie">Pie</option>
                                <option value="Otra">Otra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Lado Afectado</label>
                            <select name="lado" id="lado" class="w-full input-adapt rounded-xl px-4 py-3 cursor-pointer">
                                <option value="">No especificado</option>
                                <option value="Izquierdo">Izquierdo</option>
                                <option value="Derecho">Derecho</option>
                                <option value="Bilateral">Bilateral</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Tipo de Lesión *</label>
                            <select name="tipo" id="tipo" class="w-full input-adapt rounded-xl px-4 py-3 cursor-pointer" required data-validar="requerido" data-nombre="Tipo de lesión">
                                <option value="">Seleccione el tipo...</option>
                                <option value="Sobreuso">Sobrecarga / Sobreuso</option>
                                <option value="Aguda">Aguda (Traumática)</option>
                                <option value="Recidiva">Recidiva (Reincidente)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Nivel de Molestia (1-10) *</label>
                            <input type="number" name="nivel_molestia" id="nivel_molestia" min="1" max="10" class="w-full input-adapt rounded-xl px-4 py-3" required data-validar="requerido|rango" data-nombre="Nivel de molestia" data-min-num="1" data-max-num="10">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Diagnóstico Clínico *</label>
                            <textarea name="diagnostico" id="diagnostico" rows="2" class="w-full input-adapt rounded-xl px-4 py-3 resize-none" required data-validar="requerido|texto" data-nombre="Diagnóstico" data-min="10" data-max="500"></textarea>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Tratamiento Asignado</label>
                            <textarea name="tratamiento" id="tratamiento" rows="2" class="w-full input-adapt rounded-xl px-4 py-3 resize-none" data-validar="texto" data-nombre="Tratamiento" data-max="1000"></textarea>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Profesional Responsable</label>
                            <input type="text" name="profesional" id="profesional" class="w-full input-adapt rounded-xl px-4 py-3" data-validar="letras|texto" data-nombre="Profesional responsable" data-min="3" data-max="100">
                        </div>

                        <div class="md:col-span-2" id="campoEstadoEdicion" style="display:none;">
                            <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-2">Actualizar Estado Clínico</label>
                            <select name="estado" id="estado" class="w-full input-adapt rounded-xl px-4 py-3 border-amber-500/50 focus:border-amber-500 cursor-pointer" data-validar="requerido" data-nombre="Estado clínico">
                                <option value="Activa">🟢 Activa</option>
                                <option value="EnRehabilitacion">🟡 En Rehabilitación</option>
                                <option value="Recuperada">✅ Recuperada</option>
                                <option value="Cronica">⚠️ Crónica</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="2" class="w-full input-adapt rounded-xl px-4 py-3 resize-none" data-validar="texto" data-nombre="Observaciones" data-max="500"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="cerrarModal()" class="flex-1 border border-gray-300 dark:border-[#252345] text-gray-700 dark:text-gray-300 py-3 rounded-xl font-bold hover:bg-gray-100 dark:hover:bg-[#252345] transition uppercase text-xs">Cancelar</button>
                        <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold uppercase text-xs shadow-lg shadow-indigo-500/20">
                            Guardar Informe <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVer" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 md:p-10">
                <div id="contenidoDetalle"></div>
            </div>
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
    <script>
        // Mapeo seguro de permisos del módulo
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>
    
    <script src="assets/js/lesion.js"></script>
</body>
</html>