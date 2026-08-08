<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'grupo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Grupos de Entrenamiento | SGRD</title>
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
        .input-adapt.is-valid { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
        .input-adapt.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }
        .invalid-feedback { display: none; font-size: 0.75rem; color: #ef4444; margin-top: 0.25rem; padding-left: 0.5rem; }
        .valid-feedback { display: none; font-size: 0.75rem; color: #10b981; margin-top: 0.25rem; padding-left: 0.5rem; }
        .invalid-feedback.show { display: block; }
        .valid-feedback.show { display: block; }
        .border-danger { border-color: #ef4444 !important; }
        .border-success { border-color: #10b981 !important; }

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

        /* ===== CHECKBOX PERSONALIZADO ===== */
        .form-checkbox {
            appearance: none;
            width: 1.1rem;
            height: 1.1rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .dark .form-checkbox {
            border-color: #4b5563;
            background: #0f0d23;
        }
        .form-checkbox:checked {
            background: #6366f1;
            border-color: #6366f1;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
            background-size: 0.8rem;
            background-position: center;
            background-repeat: no-repeat;
        }
        .form-checkbox:focus { outline: none; border-color: #6366f1; }

        /* ===== SCROLL ATLETAS ===== */
        .scroll-atletas::-webkit-scrollbar { width: 4px; }
        .scroll-atletas::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .dark .scroll-atletas::-webkit-scrollbar-track { background: #0f0d23; }
        .scroll-atletas::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
                $tituloPagina = "Grupos de Entrenamiento";
                $tituloPaginaResponsive = "Grupos";
                $iconModulo = "fas fa-layer-group";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-layer-group text-indigo-500"></i> Grupos de Entrenamiento
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Organización de atletas por niveles y objetivos.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                    <button onclick="abrirModalGrupo()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Nuevo Grupo
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador y filtros -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400">
                            <i class="fas fa-layer-group"></i>
                            <span class="font-medium tracking-wide uppercase text-xs">Listado de Grupos</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2" id="contadorGrupos"></span>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                            <div class="relative flex-1 md:w-80">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                                <input type="text" id="busquedaNombre" placeholder="Buscar por nombre..." class="w-full input-adapt pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="filtroEstado" class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Ver:</label>
                                <select id="filtroEstado" class="input-adapt p-2 rounded-xl text-xs cursor-pointer">
                                    <option value="Activo" selected>✅ Grupos Activos</option>
                                    <option value="Inactivo">🗑️ Grupos Archivados</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800/50 flex justify-between items-center bg-gray-50/50 dark:bg-white/[0.02]">
                        <span id="infoTabla" class="text-xs text-gray-500 dark:text-gray-400 font-medium"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="tablaGrupos">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                                <tr>
                                    <th class="p-4 cursor-pointer select-none" data-sort="nombre">Nombre del Grupo <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                                    <th class="p-4 cursor-pointer select-none" data-sort="descripcion">Descripción <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                                    <th class="p-4 cursor-pointer select-none" data-sort="entrenador">Entrenador <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                                    <th class="p-4 text-center cursor-pointer select-none" data-sort="atletas">Atletas <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                                    <th class="p-4 text-center cursor-pointer select-none" data-sort="estado">Estado <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaGrupos">
                                <tr>
                                    <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Cargando módulos...</span>
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

    <!-- ===== MODAL CREAR/EDITAR GRUPO ===== -->
    <div id="modalGrupo" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#0f0d23]/95 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-folder-plus"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Grupo</h2>
                </div>
                <button onclick="cerrarModalGrupo()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formGrupo" class="space-y-6">
                <input type="hidden" id="id_grupo_original" name="id_grupo_original" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">NOMBRE DEL GRUPO *</label>
                        <input type="text" id="nombre" name="nombre" data-validate="true" 
                               class="input-adapt w-full p-3 rounded-xl" 
                               placeholder="Ej: Equipo Juvenil A"
                               maxlength="100">
                        <div class="invalid-feedback" id="nombre-error"></div>
                        <small class="text-gray-500 dark:text-gray-400 text-xs ml-1">Máximo 100 caracteres</small>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">ENTRENADOR ASIGNADO *</label>
                        <select id="id_entrenador" name="id_entrenador" data-validate="true" 
                                class="input-adapt w-full p-3 rounded-xl cursor-pointer">
                            <option value="">Seleccione un entrenador...</option>
                        </select>
                        <div class="invalid-feedback" id="id_entrenador-error"></div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">DESCRIPCIÓN</label>
                        <textarea id="descripcion" name="descripcion" rows="3" 
                                  class="input-adapt w-full p-3 rounded-xl resize-none" 
                                  placeholder="Detalles opcionales del grupo de natación..."></textarea>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" onclick="cerrarModalGrupo()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition active:scale-95 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR GRUPO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL ASIGNAR ATLETAS ===== -->
    <div id="modalAsignacion" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#0f0d23]/95 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-600 p-2 rounded-lg text-white"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Asignar Atletas al Grupo</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <span id="grupo_nombre" class="text-indigo-600 dark:text-indigo-400 font-semibold">Cargando...</span>
                                <span id="grupo_info" class="text-gray-500 dark:text-gray-400 text-xs ml-2"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <button onclick="cerrarModalAsignacion()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formAsignacion" class="space-y-6">
                <input type="hidden" id="id_grupo_asignacion" name="id_grupo" value="">

                <!-- FILTROS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">CATEGORÍA</label>
                        <select id="filtroCategoria" class="input-adapt w-full p-2 rounded-xl cursor-pointer" onchange="filtrarAtletasPorCategoria()">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">EDAD MÍNIMA</label>
                        <input type="number" id="edad_min" name="edad_min" 
                               class="input-adapt w-full p-2 rounded-xl" 
                               placeholder="Ej: 12" min="5" max="99">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">EDAD MÁXIMA</label>
                        <input type="number" id="edad_max" name="edad_max" 
                               class="input-adapt w-full p-2 rounded-xl" 
                               placeholder="Ej: 15" min="5" max="99">
                        <div class="invalid-feedback" id="edad-error"></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="filtrarAtletasPorCategoria()" 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold transition cursor-pointer text-xs uppercase tracking-wider">
                        <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                    </button>
                    <button type="button" onclick="limpiarFiltros()" 
                            class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-white px-4 py-2 rounded-xl font-bold transition cursor-pointer text-xs uppercase tracking-wider">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 uppercase tracking-wider">
                            <i class="fas fa-user-plus mr-2"></i> Atletas Disponibles para Asignar
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400" id="contador-atletas">0 seleccionados</span>
                    </div>
                    <div id="atletas-container" class="border border-gray-300 dark:border-gray-700 rounded-xl p-3 bg-gray-100 dark:bg-[#0f0d23] transition-colors duration-300">
                        <div id="atletas-disponibles" class="scroll-atletas max-h-64 overflow-y-auto">
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-spinner fa-spin"></i> Cargando atletas...
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="atletas-error"></div>
                </div>

                <div>
                    <button type="button" onclick="abrirModalVerGrupoDesdeAsignacion()" 
                            class="w-full bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-100 dark:hover:bg-indigo-600/30 text-indigo-600 dark:text-indigo-400 py-3 rounded-xl font-bold transition border border-indigo-200 dark:border-indigo-500/20 cursor-pointer text-xs uppercase tracking-wider">
                        <i class="fas fa-eye mr-2"></i> Ver todos los atletas asignados actualmente
                    </button>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" onclick="cerrarModalAsignacion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnAsignar" class="flex-[2] bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition active:scale-95 cursor-pointer uppercase text-xs tracking-wider">
                        ASIGNAR ATLETAS <i class="fas fa-user-plus ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE GRUPO ===== -->
    <div id="modalVerGrupo" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVerGrupo()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-10 cursor-pointer p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleGrupoContenido" class="relative p-8">
                <!-- Contenido dinámico cargado por JavaScript -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                    <p class="text-gray-500 dark:text-gray-400 mt-3 text-sm">Cargando detalles del grupo...</p>
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
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/tour.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/grupo.js"></script>
</body>
</html>