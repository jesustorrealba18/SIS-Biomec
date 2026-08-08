<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'temporadas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Temporadas | SGRD</title>
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
                $tituloPagina = "Gestión de Temporadas";
                $tituloPaginaResponsive = "Temporadas";
                $iconModulo = "fas fa-calendar-check";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-calendar-check text-indigo-500"></i> Temporadas
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Gestión de temporadas deportivas del club.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'registrar')): ?>
                    <button onclick="abrirModalTemporada()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Nueva Temporada
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-4 transition-colors duration-300">
                    <div class="relative w-full md:w-72">
                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 dark:text-gray-500"></i>
                        <input type="text" id="busquedaTemporada" onkeyup="filtrarTabla()" placeholder="Buscar por nombre..." class="w-full input-adapt pl-11 pr-4 py-2.5 rounded-xl text-sm">
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-[#0f0d23] text-gray-600 dark:text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                    <th class="p-4">Temporada</th>
                                    <th class="p-4">Fecha Inicio</th>
                                    <th class="p-4">Fecha Fin</th>
                                    <th class="p-4 text-center">Macrociclos</th>
                                    <th class="p-4 text-center">Estado</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTemporadas" class="divide-y divide-gray-200 dark:divide-[#252345] text-sm text-gray-800 dark:text-gray-300">
                                <tr>
                                    <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Cargando datos...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL CREAR/EDITAR TEMPORADA ===== -->
    <div id="modalTemporada" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <h3 id="modalTemporadaTitulo" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-check text-emerald-400"></i> Nueva Temporada
                </h3>
                <button onclick="cerrarModalTemporada()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formTemporada" autocomplete="off">
                <input type="hidden" id="id_temporada" name="id_temporada" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Nombre de la Temporada *</label>
                        <input type="text" id="nombre" name="nombre" class="w-full input-adapt p-3 rounded-xl text-sm" placeholder="Ej: Temporada 2026-2027">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha Inicio *</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" required class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 uppercase font-bold mb-2">Fecha Fin *</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" required class="w-full input-adapt p-3 rounded-xl text-sm font-mono">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-100 dark:bg-[#0f0d23] p-3 rounded-xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                        <input type="checkbox" id="activa" name="activa" value="1" checked
                               class="w-4 h-4 text-indigo-600 bg-gray-200 dark:bg-gray-900 border-gray-300 dark:border-gray-700 rounded focus:ring-indigo-500">
                        <label for="activa" class="text-xs text-gray-600 dark:text-gray-400 font-medium cursor-pointer">Marcar como temporada activa (desactiva las demás)</label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalTemporada()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR <i class="fas fa-save ml-2"></i>
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

    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/tour.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/temporadas.js"></script>
</body>
</html>