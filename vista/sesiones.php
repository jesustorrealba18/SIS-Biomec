<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Sesiones de Entrenamiento | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                $tituloPagina = "Planificación de Sesiones";
                $tituloPaginaResponsive = "Sesiones";
                $iconModulo = "fas fa-swimming-pool";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-swimming-pool text-indigo-500"></i> Planificación de Sesiones
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Diseño, planificación y seguimiento de entrenamientos.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'crear')): ?>
                    <button onclick="abrirModalSesion()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Entrenamiento
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-4 transition-colors duration-300">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-filter text-gray-500"></i>
                            <span class="text-xs font-bold uppercase tracking-wider">Filtros de Búsqueda</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                            <select id="filtroGrupo" onchange="cargarTablaSesiones()" class="input-adapt p-2.5 rounded-xl text-xs w-full md:w-48 cursor-pointer">
                                <option value="">Todos los Grupos</option>
                            </select>
                            <select id="filtroTipoSesion" onchange="cargarTablaSesiones()" class="input-adapt p-2.5 rounded-xl text-xs w-full md:w-44 cursor-pointer">
                                <option value="">Todos los Estados</option>
                                <option value="Planificada">Planificada</option>
                                <option value="Completada">Completada</option>
                                <option value="Parcial">Parcial</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                    <th class="p-4">Fecha / Info</th>
                                    <th class="p-4">Grupo / Planificación</th>
                                    <th class="p-4">Tipo de Sesión / Estado</th>
                                    <th class="p-4 text-center">Vol. Planificado</th>
                                    <th class="p-4 text-center">Vol. Ejecutado</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySesiones" class="divide-y divide-gray-200 dark:divide-[#252345] text-sm text-gray-800 dark:text-gray-300">
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR ===== -->
    <div id="modalSesion" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-6xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 id="modalSesionTitulo" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2"></h3>
                <button onclick="cerrarModalSesion()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="formSesion" autocomplete="off"> 
                <input type="hidden" id="id_sesion" name="id_sesion" value="">
                <input type="hidden" id="id_fase_actual" name="id_fase_actual" value="">
                <input type="hidden" id="volumen_planificado" name="volumen_planificado" value="0">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Entrenador *</label>
                        <select id="id_entrenador" name="id_entrenador" required class="w-full input-adapt p-3 rounded-xl text-sm cursor-pointer">
                            <option value="">Seleccione un Entrenador</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Grupo de Entrenamiento *</label>
                        <select id="id_grupo" name="id_grupo" required class="w-full input-adapt p-3 rounded-xl text-sm cursor-pointer">
                            <option value="">Seleccione un Grupo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Microciclo Vinculado</label>
                        <select id="id_microciclo" name="id_microciclo" class="w-full input-adapt p-3 rounded-xl text-sm cursor-pointer">
                            <option value="">Microciclo (Ninguno)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha Planificada *</label>
                        <input type="date" id="fecha" name="fecha" required class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Tipo de Sesión *</label>
                        <select id="tipo_sesion" name="tipo_sesion" required class="w-full input-adapt p-3 rounded-xl text-sm cursor-pointer">
                            <option value="Tecnica">Técnica</option>
                            <option value="Resistencia">Resistencia</option>
                            <option value="Velocidad">Velocidad</option>
                            <option value="Recuperacion">Recuperación</option>
                            <option value="Fuerza">Fuerza</option>
                            <option value="Flexibilidad">Flexibilidad</option>
                            <option value="Competencia">Competencia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Duración (Minutos)</label>
                        <input type="number" id="duracion_minutos" name="duracion_minutos" min="1" placeholder="Ej: 90" class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Observaciones Generales de Planificación</label>
                        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Indicaciones logísticas o notas para el grupo..." class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Indicación de Calentamiento General</label>
                        <textarea id="calentamiento" name="calentamiento" rows="2" placeholder="Ej: 200m Libre..." class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Indicación de Vuelta a la Calma General</label>
                        <textarea id="vuelta_calma" name="vuelta_calma" rows="2" placeholder="Ej: 100m" class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-600/10 p-3 rounded-xl border border-indigo-200 dark:border-indigo-500/30 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold">Volumen Total</p>
                            <p id="lblVolTotalPlanificado" class="text-2xl font-bold text-gray-900 dark:text-white font-mono">0m</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4 text-center">
                    <div class="bg-gray-100 dark:bg-black/20 p-2 rounded-xl border border-gray-200 dark:border-[#252345]">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold">Calentamiento</p>
                        <p id="lblVolCalentamiento" class="text-sm font-bold text-gray-700 dark:text-gray-300 font-mono">0m</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-black/20 p-2 rounded-xl border border-gray-200 dark:border-[#252345]">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold">Bloque Principal</p>
                        <p id="lblVolPrincipal" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 font-mono">0m</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-black/20 p-2 rounded-xl border border-gray-200 dark:border-[#252345]">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold">Vuelta a la Calma</p>
                        <p id="lblVolVueltaCalma" class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">0m</p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest">
                            <i class="fas fa-list-ol mr-1"></i> Series Planificadas
                        </h4>
                        <button type="button" onclick="agregarFilaSerie()" class="text-xs bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/30 px-3 py-1.5 rounded-lg transition cursor-pointer font-bold flex items-center gap-1">
                            <i class="fas fa-plus"></i> Añadir Serie
                        </button>
                    </div>

                    <div class="hidden md:grid grid-cols-9 gap-2 px-3 mb-2 text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400">
                        <div>Bloque</div>
                        <div class="col-span-2">Drill / Catálogo y Desc.</div>
                        <div>Ritmo Objetivo</div>
                        <div class="text-center">Rep.</div>
                        <div class="text-center">Metros</div>
                        <div class="text-center">Descanso (s)</div>
                        <div>Intensidad</div>
                        <div class="text-right">Subtotal / Borrar</div>
                    </div>

                    <table class="w-full"><tbody id="tbodySeries" class="space-y-2"></tbody></table>
                </div>

                <!-- BOTONES -->
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalSesion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR PLANIFICACIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVer" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleContenido" class="mt-2"></div>
            <div class="mt-6 flex justify-end">
                <button type="button" onclick="cerrarModalVer()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition">Cerrar Ventana</button>
            </div>
        </div>
    </div>

    <!-- ===== MODAL COMPLETAR SESIÓN ===== -->
    <div id="modalCompletar" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-800 pb-3">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-400"></i> Cierre de Sesión de Entrenamiento
                </h3>
                <button onclick="cerrarModalCompletar()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form id="formCompletar" autocomplete="off"> 
                <input type="hidden" id="id_sesion_completar" name="id_sesion" value="">

                <div class="bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-[#252345] p-3 rounded-xl mb-4 space-y-1.5 text-xs transition-colors duration-300">
                    <p class="text-gray-600 dark:text-gray-400"><strong>Grupo:</strong> <span id="compGrupo" class="text-gray-900 dark:text-white"></span></p>
                    <p class="text-gray-600 dark:text-gray-400"><strong>Fecha:</strong> <span id="compFecha" class="text-gray-900 dark:text-white font-mono"></span> | <strong>Tipo:</strong> <span id="compTipo" class="text-blue-600 dark:text-blue-400"></span></p>
                    <p class="text-indigo-600 dark:text-indigo-400 font-semibold"><strong>Volumen Planificado:</strong> <span id="compVolPlanificado"></span></p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Volumen Real Ejecutado (Metros) *</label>
                    <input type="number" id="volumen_ejecutado" name="volumen_ejecutado" required min="0" class="w-full input-adapt p-3 rounded-xl text-sm font-mono text-emerald-600 dark:text-emerald-400 font-bold" placeholder="Ej: 3200">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Modifica la marca si el volumen final varió respecto a lo planificado.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Observaciones de Ejecución / Rendimiento</label>
                    <textarea id="observaciones_completar" name="observaciones" rows="3" placeholder="Ej: Excelentes ritmos..." class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalCompletar()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 rounded-xl font-bold transition text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-green-600 hover:bg-green-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-green-500/20 text-xs tracking-wider uppercase">
                        CERRAR ENTRENAMIENTO <i class="fas fa-lock ml-1"></i>
                    </button>
                </div>
            </form>
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
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'ver') ? 'true' : 'false'; ?>,
        };
        const API_URL = '?p=sesiones';
    </script>
    <script src="assets/js/sesion.js"></script>
</body>
</html>