<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Eventos y Metas | SGRD</title>
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
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }
        .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(0);
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
        
        <!-- Overlay para móvil -->
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
                $tituloPagina = "Planificacion de Eventos y Metas";
                $tituloPaginaResponsive = "Eventos";
                $iconModulo = "fas fa-calendar-alt";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-500"></i> Calendario Competitivo
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Calendario competitivo, metas por atleta y tiempos de corte.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'crear')): ?>
                    <button onclick="abrirModalEvento()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus text-sm"></i> Registrar Evento
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:w-72">
                            <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 dark:text-gray-500"></i>
                            <input type="text" id="busquedaEvento" onkeyup="filtrarTablaEventos()" placeholder="Buscar por nombre o sede..." class="w-full input-adapt pl-11 pr-4 py-3 rounded-xl text-sm">
                        </div>

                        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                            <select id="filtroTipo" onchange="cargarTablaEventos()" class="input-adapt p-3 rounded-xl text-sm cursor-pointer">
                                <option value="">Todos los Tipos</option>
                                <option value="Regional">Regional</option>
                                <option value="Nacional">Nacional</option>
                                <option value="Internacional">Internacional</option>
                                <option value="Selectivo">Selectivo</option>
                                <option value="Control">Control</option>
                            </select>

                            <select id="filtroEstado" onchange="cargarTablaEventos()" class="input-adapt p-3 rounded-xl text-sm cursor-pointer">
                                <option value="">Todos los Estados</option>
                                <option value="Planificado">Planificado</option>
                                <option value="Inscrito">Inscrito</option>
                                <option value="En Progreso">En Progreso</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                    <th class="p-4">Evento</th>
                                    <th class="p-4">Fechas</th>
                                    <th class="p-4">Sede</th>
                                    <th class="p-4">Tipo</th>
                                    <th class="p-4">Nivel</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4 text-center">Inscritos</th>
                                    <th class="p-4 text-center">Metas</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyEventos" class="divide-y divide-gray-200 dark:divide-[#252345] text-sm text-gray-800 dark:text-gray-300">
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL EVENTO ===== -->
    <div id="modalEvento" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 id="modalEventoTitulo" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-plus text-emerald-400"></i> Registrar Evento
                </h3>
                <button onclick="cerrarModalEvento()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formEvento" autocomplete="off">
                <input type="hidden" id="id_evento" name="id_evento" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Nombre del Evento *</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido|texto" data-nombre="Nombre del evento" data-min="2" data-max="200" maxlength="200" required class="w-full input-adapt p-3 rounded-xl text-sm" placeholder="Ej: Gala Regional Miranda 2026">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" data-validar="requerido" data-nombre="Fecha de inicio" required class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" data-validar="fecha_logica" data-nombre="Fecha de fin" class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Sede</label>
                        <input type="text" id="sede" name="sede" data-validar="texto" data-nombre="Sede" data-max="200" maxlength="200" class="w-full input-adapt p-3 rounded-xl text-sm" placeholder="Ej: Complejo Acuatico de Barinas">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Organizador</label>
                        <input type="text" id="organizador" name="organizador" data-validar="texto" data-nombre="Organizador" data-max="200" maxlength="200" class="w-full input-adapt p-3 rounded-xl text-sm" placeholder="Ej: FEVEDA">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Tipo *</label>
                        <select id="tipo" name="tipo" data-validar="requerido" data-nombre="Tipo" required class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="Control">Control Tecnico</option>
                            <option value="Regional">Regional</option>
                            <option value="Nacional">Nacional</option>
                            <option value="Internacional">Internacional</option>
                            <option value="Selectivo">Selectivo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Nivel</label>
                        <select id="nivel" name="nivel" class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="">Sin asignar</option>
                            <option value="A">Nivel A</option>
                            <option value="B">Nivel B</option>
                            <option value="C">Nivel C</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Estado *</label>
                        <select id="estado" name="estado" data-validar="requerido" data-nombre="Estado" required class="w-full input-adapt p-3 rounded-xl text-sm">
                            <option value="Planificado">Planificado</option>
                            <option value="Inscrito">Inscrito</option>
                            <option value="En Progreso">En Progreso</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">&nbsp;</label>
                        <div></div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" data-validar="texto" data-nombre="Observaciones" data-max="500" maxlength="500" rows="2" placeholder="Detalles adicionales del evento..." class="w-full input-adapt p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="mt-6 bg-gray-100 dark:bg-black/20 p-4 rounded-xl border border-dashed border-amber-500/30 transition-colors duration-300">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-amber-600 dark:text-amber-400 font-bold tracking-widest">
                            <i class="fas fa-cut mr-2"></i>Tiempos de Corte (CA-09.5)
                        </p>
                        <button type="button" onclick="agregarFilaTiempoCorte()" class="text-xs bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-500/30 px-3 py-1 rounded-lg transition cursor-pointer font-bold">
                            <i class="fas fa-plus mr-1"></i> Agregar
                        </button>
                    </div>

                    <div id="contenedorTiemposCorte" class="space-y-2">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalEvento()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR EVENTO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL METAS ===== -->
    <div id="modalMetas" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-bullseye text-amber-400"></i> <span id="tituloModalMetas">Metas Competitivas</span>
                </h3>
                <button onclick="cerrarModalMetas()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formMetas" autocomplete="off">
                <input type="hidden" id="id_evento_metas" name="id_evento" value="">

                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Estilo / Distancia / Marca Objetivo / PB Actual / Diferencia %</p>
                    <button type="button" onclick="agregarFilaMeta()" class="text-xs bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/30 px-3 py-1 rounded-lg transition cursor-pointer font-bold">
                        <i class="fas fa-plus mr-1"></i> Agregar Meta
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[10px] text-gray-600 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                <th class="p-2">Atleta</th>
                                <th class="p-2">Estilo</th>
                                <th class="p-2">Distancia</th>
                                <th class="p-2">Objetivo (s)</th>
                                <th class="p-2">PB Actual (s)</th>
                                <th class="p-2">Dif %</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMetas" class="divide-y divide-gray-200 dark:divide-[#252345]">
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalMetas()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-amber-600 hover:bg-amber-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-amber-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR METAS <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL INSCRIPCIÓN ===== -->
    <div id="modalInscripcion" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-check text-cyan-400"></i> Inscribir Atletas
                </h3>
                <button onclick="cerrarModalInscripcion()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <input type="hidden" id="id_evento_inscripcion" value="">
            <div class="mb-4">
                <input type="text" id="busquedaInscripcion" onkeyup="filtrarInscripciones()" placeholder="Buscar atleta..." class="w-full input-adapt p-3 rounded-xl text-sm">
            </div>
            <div id="listaAtletasInscripcion" class="space-y-2 max-h-80 overflow-y-auto">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="cerrarModalInscripcion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                <button type="button" onclick="inscribirAtletas()" class="flex-[2] bg-cyan-600 hover:bg-cyan-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-cyan-500/20 cursor-pointer uppercase text-xs tracking-wider">
                    INSCRIBIR SELECCIONADOS <i class="fas fa-user-plus ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVer" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
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
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'crear') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/eventos.js"></script>
</body>
</html>