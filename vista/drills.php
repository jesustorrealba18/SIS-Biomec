<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Entrenamientos | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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

        /* ===== TABS ===== */
        .tab-btn {
            padding: 10px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            cursor: pointer;
        }
        .dark .tab-btn {
            color: #6b7280;
        }
        .tab-btn:hover {
            color: #4f46e5;
        }
        .dark .tab-btn:hover {
            color: #818cf8;
        }
        .tab-btn.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
        }
        .dark .tab-btn.active {
            color: #818cf8;
            border-bottom-color: #6366f1;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* ===== BADGES ===== */
        .badge-dificultad {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dificultad-Basico {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .dificultad-Basico {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .dificultad-Intermedio {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .dark .dificultad-Intermedio {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }
        .dificultad-Avanzado {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .dificultad-Avanzado {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .estado-activo {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .estado-activo {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .estado-inactivo {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .estado-inactivo {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
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
                $tituloPagina = "Gestión de Entrenamientos";
                $tituloPaginaResponsive = "Drills";
                $iconModulo = "fas fa-dumbbell";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-dumbbell text-indigo-500"></i> Repositorio de Entrenamientos
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Catálogo de ejercicios técnicos, fuerza, velocidad y resistencia.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('drills', 'crear')): ?>
                    <button onclick="abrirModalDrills()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Entrenamiento
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                            <input type="text" id="busquedaID" placeholder="Buscar por nombre o estilo..."
                                   class="input-adapt w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                        </div>
                        <span id="totalDrills" class="flex items-center gap-2 text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/20 self-center">0 Registrados</span>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5">
                        <h3 class="text-gray-900 dark:text-white font-semibold">Listado General</h3>
                        <span id="infoTabla" class="text-xs text-gray-500 dark:text-gray-500"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                                <tr>
                                    <th class="p-4">Entrenamiento</th>
                                    <th class="p-4">Estilo</th>
                                    <th class="p-4">Categoría</th>
                                    <th class="p-4">Dificultad</th>
                                    <th class="p-4">Material</th>
                                    <th class="p-4">Personalizado</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaDrills">
                                <tr>
                                    <td colspan="8" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span>
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
    <div id="modalDrills" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-dumbbell"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitulo">Registrar Entrenamiento</h2>
                </div>
                <button onclick="cerrarModalDrills()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="formDrills" enctype="multipart/form-data">
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_drill" name="id_drill" value="">
                <input type="hidden" id="id_usuario_creador" name="id_usuario_creador" value="<?php echo $_SESSION['id'] ?? '1'; ?>">

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-800 mb-6">
                    <button type="button" onclick="cambiarTabDrill('basica')" class="tab-btn active" data-tab="basica">
                        <i class="fas fa-info-circle mr-2"></i>Información Básica
                    </button>
                    <button type="button" onclick="cambiarTabDrill('detalles')" class="tab-btn" data-tab="detalles">
                        <i class="fas fa-clipboard-list mr-2"></i>Detalles y Ejecución
                    </button>
                    <button type="button" onclick="cambiarTabDrill('configuracion')" class="tab-btn" data-tab="configuracion">
                        <i class="fas fa-cog mr-2"></i>Configuración
                    </button>
                </div>

                <!-- Tab: Información Básica -->
                <div id="tab-basica" class="tab-content active">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Nombre del Entrenamiento</label>
                            <input type="text" name="nombre" id="nombre" placeholder="Ej: Ejercicio de patada de crol"
                                   data-validar="requerido|letras" data-nombre="Nombre" data-min="2" data-max="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Estilo de Natación</label>
                            <select name="estilo" id="estilo" class="input-adapt w-full p-3 rounded-xl">
                                <option value="Libre">Libre</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Braza">Braza</option>
                                <option value="Mariposa">Mariposa</option>
                                <option value="Combinado">Combinado</option>
                                <option value="Multi">Multi</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Categoría</label>
                            <select name="categoria" id="categoria" class="input-adapt w-full p-3 rounded-xl">
                                <option value="Tecnico">Técnico</option>
                                <option value="Fuerza">Fuerza</option>
                                <option value="Velocidad">Velocidad</option>
                                <option value="Coordinacion">Coordinación</option>
                                <option value="Resistencia">Resistencia</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Enfoque Técnico</label>
                            <input type="text" name="enfoque_tecnico" id="enfoque_tecnico" placeholder="Ej: Mejora de brazada, patada continua..."
                                   data-validar="requerido|texto" data-nombre="Enfoque Técnico" data-min="5" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Describe detalladamente el entrenamiento..."
                                      data-validar="requerido|texto" data-nombre="Descripción" data-min="10" class="input-adapt w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab: Detalles y Ejecución -->
                <div id="tab-detalles" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Instrucciones</label>
                            <textarea name="instrucciones" id="instrucciones" rows="4" placeholder="Pasos a seguir, series, repeticiones, descansos..."
                                      data-validar="requerido|texto" data-nombre="Instrucciones" class="input-adapt w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Metraje Sugerido</label>
                            <input type="text" name="metraje_sugerido" id="metraje_sugerido" 
                                   placeholder="Ej: 50m, 4x50m, 3x100m, 2000m, 8x25m" data-validar="requerido|metraje" data-nombre="Metraje sugerido" data-min="1" 
                                   data-max="50" class="input-adapt w-full p-3 rounded-xl"> 
                            <span class="text-[8px] text-gray-500 dark:text-gray-400 mt-1 block">Formatos válidos: 50m, 4x50m, 3x100m, 2000m, 8x25m</span>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Material Requerido</label>
                            <select name="material_requerido" id="material_requerido" class="input-adapt w-full p-3 rounded-xl">
                                <option value="Ninguno">Ninguno</option>
                                <option value="Pullboy">Pullboy</option>
                                <option value="Aletas">Aletas</option>
                                <option value="Tabla">Tabla de patada</option>
                                <option value="Paddle">Paddle</option>
                                <option value="Resistente">Resistente</option>
                                <option value="Pullboy_Aletas">Pullboy + Aletas</option>
                                <option value="Multiple">Múltiple equipamiento</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 p-4 rounded-xl bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-white/5 transition-colors duration-300">
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest mb-4"><i class="fas fa-calendar-alt mr-2"></i>Programación Temporal</p>
                        <div class="grid grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">Fecha y Hora de Ejecución</label>
                                <input type="datetime-local" name="fecha_creacion" id="fecha_creacion"
                                       class="input-adapt w-full p-3 rounded-xl text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">Dificultad</label>
                                <select name="dificultad" id="dificultad" class="input-adapt w-full p-3 rounded-xl">
                                    <option value="Basico">Básico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Configuración -->
                <div id="tab-configuracion" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Tipo de Entrenamiento</label>
                            <div class="flex items-center gap-4 p-3 bg-gray-100 dark:bg-black/30 rounded-xl border border-gray-200 dark:border-white/10 transition-colors duration-300">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="personalizado" name="personalizado" value="1" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                    <label for="personalizado" class="text-xs text-gray-700 dark:text-gray-300">Personalizado (Solo para este atleta)</label>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Estado del Entrenamiento</label>
                            <div class="flex items-center gap-4 p-3 bg-gray-100 dark:bg-black/30 rounded-xl border border-gray-200 dark:border-white/10 transition-colors duration-300">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="activo" name="activo" value="1" checked class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                    <label for="activo" class="text-xs text-gray-700 dark:text-gray-300">Activo (Disponible para asignar)</label>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 col-span-2">
                            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-500/5 border border-indigo-200 dark:border-indigo-500/20 transition-colors duration-300">
                                <p class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest mb-2">Información del Creador</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Este entrenamiento será registrado por: <span class="text-indigo-600 dark:text-indigo-400 font-mono"><?php echo $_SESSION['nombre'] ?? 'Usuario actual'; ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModalDrills()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer uppercase text-xs tracking-wider">GUARDAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVerDrill" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVerDrill()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-10 cursor-pointer p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleDrillContenido" class="relative p-8"></div>
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
    <script>
        const PERMISOS_DRILLS = {
            crear: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('drills', 'crear') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/drills.js"></script>
</body>
</html>