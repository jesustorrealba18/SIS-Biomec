<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Asignaciones | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
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

        .text-gray-900 {
            color: #111827 !important;
        }
        .dark .text-gray-900 {
            color: #ffffff !important;
        }
        .text-gray-700 {
            color: #374151 !important;
        }
        .dark .text-gray-700 {
            color: #d1d5db !important;
        }
        .text-gray-600 {
            color: #4b5563 !important;
        }
        .dark .text-gray-600 {
            color: #9ca3af !important;
        }
        .text-gray-500 {
            color: #6b7280 !important;
        }
        .dark .text-gray-500 {
            color: #9ca3af !important;
        }
        .text-gray-400 {
            color: #9ca3af !important;
        }
        .dark .text-gray-400 {
            color: #6b7280 !important;
        }

        .asignacion-row td {
            color: #1f2937 !important;
        }
        .dark .asignacion-row td {
            color: #e5e7eb !important;
        }
        .asignacion-row .font-medium {
            color: #111827 !important;
        }
        .dark .asignacion-row .font-medium {
            color: #ffffff !important;
        }

        .estado-activo {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46 !important;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .estado-activo {
            color: #34d399 !important;
        }
        .estado-inactivo {
            background: rgba(239, 68, 68, 0.15);
            color: #991b1b !important;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .estado-inactivo {
            color: #f87171 !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
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
                $tituloPagina = "Gestión de Asignaciones";
                $tituloPaginaResponsive = "Asignaciones";
                $iconModulo = "fas fa-exchange-alt";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con botón Historial -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-exchange-alt text-indigo-500"></i> Gestión de Asignaciones
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Asignación de carriles a grupos en bloques horarios.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carriles', 'gestionar')): ?>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="abrirModalAsignacion()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-plus-circle text-sm"></i> Nueva Asignación
                        </button>
                        <button onclick="verHistorialCompletadas()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-emerald-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-history text-sm"></i> Historial
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Filtros y Buscador -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                            <input type="text" id="busquedaAsignacion" placeholder="Buscar por carril, horario o grupo..."
                                   class="input-adapt w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-xs text-gray-600 dark:text-gray-400 font-medium">Estado:</label>
                            <select id="filtroEstado" class="input-adapt py-2 px-4 rounded-xl text-sm">
                                <option value="Activo">Activas</option>
                                <option value="Inactivo">Inactivas</option>
                                <option value="Todos">Todos</option>
                            </select>
                            <span id="totalAsignaciones" class="flex items-center gap-2 text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/20">0 Asignaciones</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5">
                        <h3 class="text-gray-900 dark:text-white font-semibold">Listado de Asignaciones</h3>
                        <span id="infoTabla" class="text-xs text-gray-500 dark:text-gray-500"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                                <tr>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="carril_numero">
                                        Carril <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="dia_semana">
                                        Horario <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="grupo_nombre">
                                        Grupo <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4">Día Específico</th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="fecha_vigencia_inicio">
                                        Vigencia <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="activa">
                                        Estado <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaAsignaciones">
                                <tr>
                                    <td colspan="7" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Cargando asignaciones...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pieTabla" class="p-4 border-t border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR ===== -->
    <div id="modalAsignacion" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-exchange-alt"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitulo">Registrar Asignación</h2>
                </div>
                <button onclick="cerrarModalAsignacion()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="formAsignacion" enctype="multipart/form-data">
                <input type="hidden" id="id_asignacion" name="id_asignacion" value="">

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Carril</label>
                        <select name="id_carril" id="id_carril" data-validar="requerido" data-nombre="Carril" class="input-adapt w-full p-3 rounded-xl">
                            <option value="">Seleccione un carril...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Bloque Horario</label>
                        <select name="id_bloque_horario" id="id_bloque_horario" data-validar="requerido" data-nombre="Bloque horario" class="input-adapt w-full p-3 rounded-xl">
                            <option value="">Seleccione un horario...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Grupo</label>
                        <select name="id_grupo" id="id_grupo" data-validar="requerido" data-nombre="Grupo" class="input-adapt w-full p-3 rounded-xl">
                            <option value="">Seleccione un grupo...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Día Específico (Opcional)</label>
                        <input type="date" name="dia_especifico" id="dia_especifico" class="input-adapt w-full p-3 rounded-xl">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Fecha de Inicio</label>
                            <input type="date" name="fecha_vigente_inicio" id="fecha_vigente_inicio" data-validar="requerido" data-nombre="Fecha inicio" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Fecha de Fin</label>
                            <input type="date" name="fecha_vigente_fin" id="fecha_vigente_fin" data-validar="requerido" data-nombre="Fecha fin" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-black/20 rounded-xl border border-gray-200 dark:border-white/5">
                        <input type="checkbox" name="activa" id="activa" value="1" checked class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                        <label for="activa" class="text-sm text-gray-700 dark:text-gray-300 font-medium">Asignación Activa</label>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModalAsignacion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer uppercase text-xs tracking-wider">GUARDAR ASIGNACIÓN</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVerAsignacion" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 transition-colors duration-300">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-indigo-500"></i> Detalle de Asignación
                    </h3>
                    <button onclick="cerrarModalVer()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Carril</span>
                        <span id="verCarril" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Horario</span>
                        <span id="verBloqueHorario" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grupo</span>
                        <span id="verGrupo" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Día Específico</span>
                        <span id="verDiaEspecifico" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vigencia Inicio</span>
                        <span id="verFechaInicio" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vigencia Fin</span>
                        <span id="verFechaFin" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</span>
                        <span id="verEstado" class="text-sm font-medium">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL HISTORIAL DE COMPLETADAS ===== -->
    <div id="modalHistorial" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-history text-emerald-500"></i> Historial de Asignaciones Completadas
                    </h3>
                    <button onclick="cerrarModalHistorial()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                            <tr>
                                <th class="p-3">Carril</th>
                                <th class="p-3">Horario</th>
                                <th class="p-3">Grupo</th>
                                <th class="p-3">Vigencia</th>
                                <th class="p-3 text-right">Completada</th>
                            </tr>
                        </thead>
                        <tbody id="listaCompletadas" class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr>
                                <td colspan="5" class="text-center p-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Cargando historial...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODALES VER DETALLE CARRIL ===== -->
    <div id="modalVerCarril" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 transition-colors duration-300">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-road text-indigo-500"></i> Detalle del Carril
                    </h3>
                    <button onclick="cerrarModalVerCarril()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Número</span>
                        <span id="verCarrilNumero" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Capacidad Máxima</span>
                        <span id="verCarrilCapacidad" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</span>
                        <span id="verCarrilEstado" class="text-sm font-medium">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODALES VER DETALLE BLOQUE ===== -->
    <div id="modalVerBloque" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 transition-colors duration-300">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-clock text-indigo-500"></i> Detalle del Bloque Horario
                    </h3>
                    <button onclick="cerrarModalVerBloque()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Día</span>
                        <span id="verBloqueDia" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hora Inicio</span>
                        <span id="verBloqueInicio" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hora Fin</span>
                        <span id="verBloqueFin" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rango</span>
                        <span id="verBloqueRango" class="text-sm font-medium">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODALES VER DETALLE GRUPO ===== -->
    <div id="modalVerGrupo" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 transition-colors duration-300">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users text-indigo-500"></i> Detalle del Grupo
                    </h3>
                    <button onclick="cerrarModalVerGrupo()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</span>
                        <span id="verGrupoNombre" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descripción</span>
                        <span id="verGrupoDescripcion" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entrenador</span>
                        <span id="verGrupoEntrenador" class="text-sm font-medium text-gray-900 dark:text-white">—</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</span>
                        <span id="verGrupoEstado" class="text-sm font-medium">—</span>
                    </div>
                </div>
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
        const PERMISOS_MODULO = {
          gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carriles', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/asignacion.js"></script>
</body>
</html>