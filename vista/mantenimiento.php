<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'mantenimiento';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Mantenimiento y Respaldo | SGRD</title>
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

        /* ===== ZONA DROP ===== */
        .zona-drop {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        .dark .zona-drop {
            border-color: #252345;
        }
        .zona-drop.dragover {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
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
                $tituloPagina = "Mantenimiento y Respaldo";
                $tituloPaginaResponsive = "Mantenimiento";
                $iconModulo = "fas fa-server";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-server text-indigo-500"></i> Mantenimiento
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Gestión de respaldos y restauración del sistema.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Tarjeta: Generar Respaldo -->
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-8 flex flex-col justify-between shadow-lg shadow-black/20 border-t-4 border-t-emerald-500 relative overflow-hidden transition-colors duration-300">
                        <i class="fas fa-database absolute -bottom-10 -right-10 text-9xl text-gray-300 dark:text-white opacity-[0.02]"></i>

                        <div>
                            <div class="flex items-center gap-3 mb-6">
                                <div class="bg-emerald-50 dark:bg-emerald-500/20 p-3 rounded-lg text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-cloud-download-alt text-xl"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Generar Respaldo</h2>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                                Crea una copia de seguridad empaquetada de ambas bases de datos:
                                <span class="text-indigo-600 dark:text-indigo-400 font-mono text-xs">sis_natacion</span> y
                                <span class="text-indigo-600 dark:text-indigo-400 font-mono text-xs">sis_seguridad</span>.
                                Es recomendable realizar este proceso semanalmente.
                            </p>

                            <div class="bg-gray-100 dark:bg-[#0f0d23] border border-gray-200 dark:border-[#252345] rounded-xl p-4 mb-8 transition-colors duration-300">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Estado de Conexión</span>
                                    <span class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Estable
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">Último respaldo: <span id="txtUltimoRespaldo">Consultando...</span></div>
                            </div>
                        </div>

                        <button type="button" onclick="Mantenimiento.generarRespaldo()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-4 rounded-xl transition duration-300 shadow-lg shadow-emerald-500/20 uppercase text-xs tracking-widest flex items-center justify-center gap-3 cursor-pointer">
                            <i class="fas fa-download text-lg"></i> Iniciar Respaldo Completo
                        </button>
                    </div>

                    <!-- Tarjeta: Restaurar Sistema -->
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-8 flex flex-col justify-between shadow-lg shadow-black/20 border-t-4 border-t-red-500 relative overflow-hidden transition-colors duration-300">
                        <i class="fas fa-exclamation-triangle absolute -bottom-10 -right-10 text-9xl text-gray-300 dark:text-white opacity-[0.02]"></i>

                        <div>
                            <div class="flex items-center gap-3 mb-6">
                                <div class="bg-red-50 dark:bg-red-500/20 p-3 rounded-lg text-red-600 dark:text-red-400">
                                    <i class="fas fa-history text-xl"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Restaurar Sistema</h2>
                            </div>

                            <p class="text-sm text-red-600 dark:text-red-400/80 mb-6 leading-relaxed font-medium">
                                <i class="fas fa-engine-warning mr-1"></i> Precaución: La restauración sobrescribirá los datos actuales del sistema con la información contenida en el archivo de respaldo.
                            </p>

                            <form id="formRestaurar" class="mb-8">
                                <div id="zonaDrop" class="zona-drop bg-gray-100 dark:bg-[#0f0d23] rounded-xl p-6 text-center cursor-pointer group hover:border-red-500 transition-colors">
                                    <input type="file" id="archivoRespaldo" name="archivoRespaldo" accept=".sql,.zip" class="hidden" onchange="Mantenimiento.archivoSeleccionado(this)">

                                    <div id="infoArchivo" class="flex flex-col items-center justify-center pointer-events-none">
                                        <i class="fas fa-file-upload text-3xl text-gray-400 dark:text-gray-600 group-hover:text-red-400 transition-colors mb-3"></i>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 font-bold">Haz clic o arrastra el archivo aquí</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos soportados: .sql o .zip</span>
                                    </div>

                                    <div id="archivoCargado" class="hidden flex flex-col items-center justify-center">
                                        <i class="fas fa-file-archive text-3xl text-red-400 mb-2"></i>
                                        <span id="nombreArchivoTxt" class="text-sm text-gray-900 dark:text-white font-mono font-bold"></span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 mt-2 cursor-pointer pointer-events-auto hover:underline" onclick="Mantenimiento.limpiarArchivo(event)">Cambiar archivo</span>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <button type="button" onclick="Mantenimiento.iniciarRestauracion()" id="btnRestaurar" class="w-full bg-red-600/50 text-white/50 cursor-not-allowed font-bold py-4 rounded-xl transition duration-300 uppercase text-xs tracking-widest flex items-center justify-center gap-3">
                            <i class="fas fa-upload text-lg"></i> Ejecutar Restauración
                        </button>
                    </div>

                </div>

            </main>
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
    <script src="assets/js/mantenimiento.js"></script>
</body>
</html>