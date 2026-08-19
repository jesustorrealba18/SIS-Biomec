<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control de Marcas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <style>
        /* ========== ESTILOS BASE ========== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* ========== TARJETAS ========== */
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        /* ========== INPUTS ========== */
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
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }
        .input-adapt:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Para el calendario (date) en modo oscuro */
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        /* ========== DATATABLES ADAPTATIVO ========== */
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

        /* Responsive + verde nativo */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
            position: relative;
            cursor: pointer;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
            content: "+";
            display: inline-block;
            color: white;
            background-color: #22c55e;
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
            box-sizing: content-box;
            text-align: center;
            text-indent: 0 !important;
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.4;
            width: 1.2rem;
            height: 1.2rem;
            font-size: 1rem;
            font-weight: bold;
            margin-right: 0.5rem;
            transition: transform 0.15s ease;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
            content: "-";
            background-color: #d33;
            transform: rotate(0deg);
        }

        /* ========== TRANSICIONES ========== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-transition {
            transition: all 0.3s ease;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-[#a0a0c0] font-sans antialiased transition-colors duration-300">

<?php
// Recargar permisos del usuario actual en cada acceso
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
                $tituloPagina = "Gestión de Marcas Técnicas";
                $tituloPaginaResponsive = "Marcas";
                $iconModulo = "fas fa-stopwatch";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-stopwatch text-indigo-500"></i> Repositorio de Marcas Analíticas
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gestión avanzada de tiempos de carrera, ritmos de caída y progresión biométrica.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar')): ?>
                    <button onclick="iniciarRegistroMarca()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Nueva Marca
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="tarjeta p-5 transition-colors duration-300">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-toggle-on text-indigo-500"></i> Estado del Registro
                            </label>
                            <select id="filtroEstado" onchange="cargarTablaMarcas()" class="w-full input-adapt rounded-xl p-3 text-sm cursor-pointer">
                                <option value="Activo">Registros Activos</option>
                                <option value="Inactivo">Registros Archivados</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-user text-indigo-500"></i> Filtrar Nadador
                            </label>
                            <select id="filtroAtleta" onchange="cargarTablaMarcas()" class="w-full input-adapt rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todos los Atletas</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-water text-indigo-500"></i> Estilo Técnico
                            </label>
                            <select id="filtroEstilo" onchange="cargarTablaMarcas()" class="w-full input-adapt rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todos los Estilos</option>
                                <option value="Libre">Libre (Crawl)</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Pecho">Pecho (Braza)</option>
                                <option value="Mariposa">Mariposa</option>
                                <option value="Combinado">Combinado (Medley)</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-ruler-horizontal text-indigo-500"></i> Distancia (Metros)
                            </label>
                            <select id="filtroDistancia" onchange="cargarTablaMarcas()" class="w-full input-adapt rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todas las Distancias</option>
                                <option value="50">50 metros</option>
                                <option value="100">100 metros</option>
                                <option value="200">200 metros</option>
                                <option value="400">400 metros</option>
                                <option value="800">800 metros</option>
                                <option value="1500">1500 metros</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-swimming-pool text-indigo-500"></i> Tipo de Alberca
                            </label>
                            <select id="filtroPiscina" onchange="cargarTablaMarcas()" class="w-full input-adapt rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Cualquier Longitud</option>
                                <option value="25m">Piscina Corta (25m)</option>
                                <option value="50m">Piscina Olímpica (50m)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="tarjeta p-4 sm:p-6 overflow-hidden transition-colors duration-300">
                    <table id="tablaMarcas" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Atleta / Cédula</th>
                                <th>Prueba Especialidad</th>
                                <th>Dimensión Piscina</th>
                                <th>Tiempo Oficial</th>
                                <th>Origen / Nivel</th>
                                <th>Fecha</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMarcas">
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- ========== MODAL REGISTRAR / EDITAR ========== -->
    <div id="modalMarca" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 id="modalTitulo" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-stopwatch text-emerald-400"></i> Registrar Control de Tiempo
                </h3>
                <button onclick="cerrarModalMarca()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formMarca" autocomplete="off">
                <input type="hidden" id="accion_form" name="accion" value="registrar">
                <input type="hidden" id="id_marca" name="id_marca" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="block text-xs text-indigo-600 dark:text-indigo-300 uppercase font-bold mb-2">Contexto de la Marca *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/5">
                            <div>
                                <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Entrenamiento (Sesión)</label>
                                <select id="id_sesion" name="id_sesion" class="w-full input-adapt p-3 rounded-xl text-sm transition-all">
                                    <option value="">Ninguna - No aplica</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Competencia (Evento)</label>
                                <select id="id_evento" name="id_evento" class="w-full input-adapt p-3 rounded-xl text-sm transition-all">
                                    <option value="">Ninguno - No aplica</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Atleta Participante *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta Seleccionado">
                        
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400 dark:text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" disabled placeholder="Seleccione sesión o evento primero..." class="w-full input-adapt pl-10 pr-4 py-3 rounded-xl text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all" autocomplete="off" maxlength="40" required>
                            
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-white dark:bg-[#111026] border border-gray-200 dark:border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden transition-all">
                            <ul id="ulAtletas" class="text-sm text-gray-800 dark:text-gray-300 divide-y divide-gray-200 dark:divide-[#252345]"></ul>
                        </div>
                    </div>

                    <?php date_default_timezone_set('America/Caracas'); ?>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha del Registro *</label>
                        <input type="date" id="fecha" name="fecha" max="<?php echo date('Y-m-d'); ?>" data-validar="requerido|fecha_reciente" required data-nombre="Fecha" class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Estilo *</label>
                        <select id="estilo" name="estilo" data-validar="requerido" data-nombre="Estilo" class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="" disabled selected>Seleccione estilo...</option>
                            <option value="Libre">Libre (Crawl)</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Braza">Braza (Pecho)</option>
                            <option value="Mariposa">Mariposa</option>
                            <option value="Combinado">Combinado (Medley)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Distancia Total *</label>
                        <select id="distancia_m" name="distancia_m" data-validar="requerido" data-nombre="Distancia" class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="" disabled selected>Seleccione distancia...</option>
                            <option value="50">50 Metros</option>
                            <option value="100">100 Metros</option>
                            <option value="200">200 Metros</option>
                            <option value="400">400 Metros</option>
                            <option value="800">800 Metros</option>
                            <option value="1500">1500 Metros</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Tipo de Piscina *</label>
                        <select id="tipo_piscina" name="tipo_piscina" data-validar="requerido" data-nombre="Tipo de Piscina" class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="" disabled selected>Seleccione tipo de piscina...</option>
                            <option value="50m">Olímpica (50 metros)</option>
                            <option value="25m">Corta (25 metros)</option>
                        </select>
                    </div>
                </div>

                <!-- Campos de tiempos -->

                <!-- Campos de tiempos (Actualizado a 3 columnas) -->
<div id="contenedorTiemposManuales" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4 p-4 bg-gray-50 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
    <div>
        <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Reacción (s)</label>
        <input type="text" inputmode="decimal" data-validar="decimal_tiempo" data-nombre="Reacción" maxlength="5"
               id="tiempo_reaccion_seg" name="tiempo_reaccion_seg" placeholder="00.00" 
               class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
    </div>
    <div>
        <label class="block text-[10px] text-amber-600 dark:text-amber-400 uppercase font-bold mb-1" title="Para calcular SWOLF">Brazadas/Largo</label>
        <input type="number" id="brazadas_por_largo" name="brazadas_por_largo" min="1" max="999" oninput="if(this.value.length > 3) this.value = this.value.slice(0,3);" data-validar="numeros" data-max="4" data-nombre="Brazadas" placeholder="Ej: 16" 
               class="w-full bg-white dark:bg-[#161430] border border-amber-500/50 dark:border-amber-500/50 text-gray-800 dark:text-white p-2 rounded-lg text-sm text-center font-mono focus:ring-2 focus:ring-amber-500 outline-none">
    </div>
    <div>
        <label class="block text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold mb-1">Tiempo Final *</label>
        <input type="text" id="tiempo_final_humano" placeholder="MM:SS.cc" data-validar="requerido|tiempo" data-nombre="Tiempo Final" maxlength="8" 
               class="w-full bg-white dark:bg-[#161430] border border-indigo-500 text-gray-800 dark:text-white font-mono text-sm rounded-lg p-2 text-center focus:ring-2 focus:ring-indigo-500 font-bold">
        <input type="hidden" id="tiempo_final_seg" name="tiempo_final_seg">
    </div>
</div>
                <!-- <div id="contenedorTiemposManuales" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 p-4 bg-gray-50 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                    <div>
                        <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Reacción (s)</label>
                        <input type="text" inputmode="decimal" data-validar="decimal_tiempo" data-nombre="Reacción" maxlength="5"
                               id="tiempo_reaccion_seg" name="tiempo_reaccion_seg" placeholder="00.00" 
                               class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Viraje (s)</label>
                        <input type="text" inputmode="decimal" data-validar="decimal_tiempo" data-nombre="Viraje" maxlength="5"
                               id="tiempo_viraje_seg" name="tiempo_viraje_seg" placeholder="00.00" 
                               class="w-full input-adapt p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-amber-600 dark:text-amber-400 uppercase font-bold mb-1" title="Para calcular SWOLF">Brazadas/Largo</label>
                        <input type="number" id="brazadas_por_largo" name="brazadas_por_largo" min="1" max="999" oninput="if(this.value.length > 3) this.value = this.value.slice(0,3);" data-validar="numeros" data-max="4" data-nombre="Brazadas" placeholder="Ej: 16" 
                               class="w-full bg-white dark:bg-[#161430] border border-amber-500/50 dark:border-amber-500/50 text-gray-800 dark:text-white p-2 rounded-lg text-sm text-center font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold mb-1">Tiempo Final *</label>
                        <input type="text" id="tiempo_final_humano" placeholder="MM:SS.cc" data-validar="requerido|tiempo" data-nombre="Tiempo Final" maxlength="8" 
                               class="w-full bg-white dark:bg-[#161430] border border-indigo-500 text-gray-800 dark:text-white font-mono text-sm rounded-lg p-2 text-center focus:ring-2 focus:ring-indigo-500 font-bold">
                        <input type="hidden" id="tiempo_final_seg" name="tiempo_final_seg">
                    </div>
                </div> -->

                <!-- Splits -->
                <div id="contenedorSplits" class="hidden mt-6 bg-gray-50 dark:bg-black/30 p-4 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 transition-all">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest">
                            <i class="fas fa-chart-bar mr-2"></i>Desglose Cronometrado cada 25m
                        </p>
                        <span id="contadorSplits" class="text-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded font-mono font-bold">0 Tramos</span>
                    </div>
                    <div id="rejillaSplits" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"">
                    </div>
                    <div id="alertaCoherencia" class="mt-3 text-right text-[11px] font-medium transition-all"></div>
                </div>

             

                <!-- Observaciones -->
                <div class="mt-4">
                    <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Observaciones Técnicas</label>
                    <textarea id="observaciones" name="observaciones" data-validar="texto" data-max="255" maxlength="255" data-nombre="Observaciones" rows="2" placeholder="Detalles sobre las condiciones de nado, descalificaciones o comentarios del entrenador..." 
                              class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                </div>

<div class="flex gap-3 mt-6">
    <button type="button" onclick="cerrarModalMarca()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
    
    <!-- Botón Clásico de Guardado Manual -->
    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
        GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>
    </button>

    <!-- Botón Puente para Cronómetro en Vivo (Inyectado) -->
    <button type="button" id="btnIrCrono" onclick="abrirModalCronoLive()" class="hidden flex-[2] bg-amber-500 hover:bg-amber-400 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-amber-500/20 cursor-pointer uppercase text-xs tracking-wider transition-all">
        INICIAR CRONÓMETRO <i class="fas fa-stopwatch ml-2"></i>
    </button>
</div>   
            </form>
        </div>
    </div>

    <!-- ========== MODAL VER DETALLE ========== -->
    <div id="modalVer" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            
            <div class="p-8 relative z-10" id="detalleContenido">
            </div>
        </div>
    </div>
<!-- ========== MODAL CRONÓMETRO EN VIVO (FULL SCREEN) ========== -->
<div id="modalCronoEnVivo" class="fixed inset-0 z-[100] bg-white dark:bg-[#060512] flex flex-col hidden transition-opacity duration-300 opacity-0">
    
    <!-- Header del Cronómetro: Más compacto en móvil -->
    <div class="flex-none flex justify-between items-center p-3 sm:p-5 bg-gray-100 dark:bg-[#0f0d23] border-b border-gray-200 dark:border-[#252345] shadow-lg transition-colors duration-300">
        <div>
            <span class="text-[10px] sm:text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest bg-amber-50 dark:bg-amber-500/10 px-2 sm:px-3 py-1 rounded-full animate-pulse border border-amber-200 dark:border-amber-500/30">
                <i class="fas fa-circle text-[8px] align-middle mr-1"></i> Telemetría Live
            </span>
            <h2 id="cronoAtletaNombre" class="text-gray-900 dark:text-white text-base sm:text-2xl font-black mt-1 sm:mt-2 leading-tight">Seleccione Atleta...</h2>
            <p id="cronoPruebaInfo" class="text-indigo-600 dark:text-indigo-400 text-[10px] sm:text-sm font-mono mt-0.5 sm:mt-1">100m Libre - Piscina 50m</p>
        </div>
        <button type="button" onclick="cerrarModalCronoLive()" class="flex-none text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition p-2 sm:p-3 bg-gray-200 dark:bg-white/5 hover:bg-red-100 dark:hover:bg-red-500/20 rounded-xl cursor-pointer ml-4">
            <i class="fas fa-times text-xl sm:text-2xl"></i>
        </button>
    </div>

    <!-- Pantalla Central del Reloj: flex-none en móvil (ocupa lo justo), flex-1 en PC -->
    <div class="flex-none lg:flex-1 flex flex-col items-center justify-center py-4 sm:py-6 relative overflow-hidden min-h-[120px] lg:min-h-0">
        <!-- Efecto de resplandor de fondo -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-10 dark:opacity-20 transition-opacity">
            <div class="w-64 h-64 sm:w-96 sm:h-96 bg-indigo-500 rounded-full blur-[80px] sm:blur-[100px]"></div>
        </div>

        <div class="relative z-10 text-center w-full max-w-4xl px-4">
            <!-- Reloj Principal: Ajuste de altura de línea (leading-none) para evitar espacios muertos -->
            <div id="displayReloj" class="text-6xl sm:text-[6rem] lg:text-[8rem] xl:text-[9rem] leading-none font-black text-gray-900 dark:text-white font-mono tracking-tighter tabular-nums drop-shadow-md dark:drop-shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all">
                00:00.00
            </div>
            <div class="text-gray-600 dark:text-gray-400 text-xs sm:text-base lg:text-xl font-bold tracking-widest uppercase mt-1 sm:mt-2" id="cronoEstadoTexto">Esperando Inicio</div>
        </div>
    </div>

    <!-- Panel de Registros y Botón Masivo: flex-1 en móvil (ocupa todo el resto), alto máximo en PC -->
    <div class="flex-1 lg:flex-none flex flex-col lg:flex-row bg-gray-100 dark:bg-[#0f0d23] border-t border-gray-200 dark:border-[#252345] transition-colors duration-300 min-h-0 lg:max-h-[45vh]"> 
        
        <!-- Timeline de Eventos (Splits): Ahora sí tendrá espacio para respirar -->
        <div class="flex-1 min-h-0 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-[#252345] overflow-y-auto p-3 sm:p-5 bg-gray-50 dark:bg-black/20 transition-colors duration-300">
            <h3 class="text-[10px] sm:text-xs uppercase text-gray-600 dark:text-gray-500 font-bold mb-2 sm:mb-4 tracking-widest flex justify-between sticky top-0 bg-gray-50 dark:bg-[#0b091a] py-1 z-10">
                <span>Registro de Tiempos (Splits)</span>
                <span id="contadorVueltasCrono" class="text-indigo-600 dark:text-indigo-400">0 / 8 Tramos</span>
            </h3>
            
            <ul id="listaTiemposCrono" class="space-y-2">
                <!-- Se inyectará por JS -->
            </ul>
        </div>

        <!-- Área del Botón de Acción Masivo -->
        <div class="flex-none w-full lg:w-1/3 p-3 sm:p-5 flex flex-col justify-center gap-2 sm:gap-3 bg-gradient-to-t from-indigo-500/10 dark:from-indigo-900/20 to-transparent transition-colors duration-300">
            <!-- Botón Multipropósito -->
            <button id="btnAccionCrono" onclick="accionarCrono()" class="w-full py-3 sm:py-4 lg:py-6 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl shadow-[0_0_30px_rgba(16,185,129,0.3)] transition-all flex flex-col items-center justify-center group cursor-pointer border-2 border-emerald-400/50">
                <i class="fas fa-play text-3xl sm:text-4xl lg:text-5xl mb-1 group-active:scale-90 transition-transform"></i>
                <span id="txtBtnAccionCrono" class="font-black text-sm sm:text-lg lg:text-xl uppercase tracking-widest leading-tight">Iniciar Prueba</span>
            </button>
            
            <!-- Botones Secundarios -->
            <button id="btnReiniciarCrono" onclick="reiniciarCrono()" class="hidden w-full py-2 bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-500 border border-red-500/30 rounded-xl font-bold uppercase text-xs sm:text-sm tracking-wider cursor-pointer transition">
                <i class="fas fa-undo mr-2"></i> Reiniciar
            </button>

            <button id="btnTransferirCrono" onclick="transferirCronoAlFormulario()" class="hidden w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold uppercase text-xs sm:text-sm tracking-wider cursor-pointer shadow-lg shadow-indigo-500/20 transition">
                <i class="fas fa-check-circle mr-2"></i> Confirmar y Volver
            </button>
        </div>
    </div>
</div>

    <!-- ========== SCRIPTS ========== -->
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
        const PERMISOS_MODULO = {
            ver: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'ver') ? 'true' : 'false'; ?>,
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'eliminar') ? 'true' : 'false'; ?>,
            restaurar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'restaurar') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/marcas.js"></script>
</body>
</html>