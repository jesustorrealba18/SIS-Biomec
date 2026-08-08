<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Bloques de Horarios | SGRD</title>
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

        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        .dark .bg-\[0f0d23\] {
            background-color: #0f0d23;
        }
        .dark .bg-\[161430\] {
            background-color: #161430;
        }
        .dark .border-\[252345\] {
            border-color: #252345;
        }
        .dark .bg-\[\#1c1a3a\] {
            background-color: #1c1a3a;
        }

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
            $tituloPagina = "Gestión de Bloques de Horarios";
            $tituloPaginaResponsive = "Horarios";
            $iconModulo = "fas fa-clock";
            include 'vista/complementos/header.php'; 
        ?>

        <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
            
            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="fas fa-clock text-indigo-500"></i> Bloques de Horarios
                    </h2>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Administra los bloques de horarios disponibles</p>
                </div>
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('horario', 'gestionar')): ?>
                <button onclick="abrirModalHorario()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-plus-circle text-sm"></i> Nuevo Horario
                </button>
                <?php endif; ?>
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                        <input type="text" id="busquedaHorario" placeholder="Buscar por día o horario..."
                               class="input-adapt w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                    </div>
                    <span id="totalHorarios" class="flex items-center gap-2 text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/20 self-center">0 Registrados</span>
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
                                <th class="p-4 cursor-pointer hover:text-indigo-500 transition" data-sort="dia_semana">
                                    Día <i class="fas fa-sort ml-1 text-gray-600 text-[10px]"></i>
                                </th>
                                <th class="p-4 cursor-pointer hover:text-indigo-500 transition" data-sort="hora_inicio">
                                    Hora Inicio <i class="fas fa-sort ml-1 text-gray-600 text-[10px]"></i>
                                </th>
                                <th class="p-4 cursor-pointer hover:text-indigo-500 transition" data-sort="hora_fin">
                                    Hora Fin <i class="fas fa-sort ml-1 text-gray-600 text-[10px]"></i>
                                </th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaHorario">
                            <tr>
                                <td colspan="4" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                    <span class="text-xs uppercase tracking-wider block">Cargando horarios...</span>
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
<div id="modalHorario" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#0f0d23]/95 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
        <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-clock"></i></div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitulo">Registrar Bloque Horario</h2>
            </div>
            <button type="button" onclick="cerrarModalHorario()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form id="formHorario" class="space-y-6">
            <input type="hidden" id="action_type" name="action_type" value="registrar">
            <input type="hidden" id="id_bloque" name="id_bloque" value="">

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] text-gray-600 dark:text-gray-400 font-bold ml-1">Día de la Semana <span class="text-red-400">*</span></label>
                    <select id="dia_semana" name="dia_semana" 
                            data-validar="requerido" data-nombre="Día de la semana"
                            class="input-adapt w-full p-3 rounded-xl">
                        <option value="">Seleccione un día</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-600 dark:text-gray-400 font-bold ml-1">Hora de Inicio <span class="text-red-400">*</span></label>
                        <input type="time" id="hora_inicio" name="hora_inicio" 
                               data-validar="requerido" data-nombre="Hora de inicio"
                               class="input-adapt w-full p-3 rounded-xl">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-600 dark:text-gray-400 font-bold ml-1">Hora de Fin <span class="text-red-400">*</span></label>
                        <input type="time" id="hora_fin" name="hora_fin" 
                               data-validar="requerido" data-nombre="Hora de fin"
                               class="input-adapt w-full p-3 rounded-xl">
                    </div>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl p-4 transition-colors duration-300">
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <span>Los horarios no pueden superponerse con bloques existentes del mismo día</span>
                    </p>
                </div>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                <button type="button" onclick="cerrarModalHorario()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer uppercase text-xs tracking-wider">GUARDAR</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL VER DETALLE ===== -->
<div id="modalVerHorario" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
        <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-10 cursor-pointer p-2">
            <i class="fas fa-times text-xl"></i>
        </button>
        <div class="relative p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> 
                    Detalle del Bloque Horario
                </h2>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-100 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Día de la Semana</p>
                    <p id="verDia" class="text-xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-100 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora de Inicio</p>
                        <p id="verHoraInicio" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-[#0f0d23] p-4 rounded-xl border border-gray-200 dark:border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora de Fin</p>
                        <p id="verHoraFin" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                    </div>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-500/10 p-4 rounded-xl border border-indigo-200 dark:border-indigo-500/20">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Rango de Horario</p>
                    <p id="verRango" class="text-lg font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
            </div>

            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-800 mt-6">
                <button onclick="cerrarModalVer()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CERRAR</button>
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
        gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('horario', 'gestionar') ? 'true' : 'false'; ?>,
    };
</script>
<script src="assets/js/horario.js"></script>
</body>
</html>