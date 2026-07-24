<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Inicio | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gradiente-boton {
            background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%);
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
                $tituloPagina = "Inicio";
                $tituloPaginaResponsive = "Inicio";
                $iconModulo = "fas fa-home";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Saludo personalizado -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>👋</span>
                                <?php
                                    $hora = date('H');
                                    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
                                    $nombre = $_SESSION['nombre'] ?? 'Usuario';
                                    echo $saludo . ', ' . htmlspecialchars($nombre) . '!';
                                ?>
                            </h2>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Bienvenido al Sistema de Gestión de Rendimiento Deportivo</p>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar-alt text-indigo-500"></i>
                            <span><?php echo date('d/m/Y'); ?></span>
                            <span class="mx-1">|</span>
                            <i class="fas fa-clock text-indigo-500"></i>
                            <span><?php echo date('H:i'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Acceso rápido a módulos principales -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <?php
                    $modulos = [
                        ['url' => '?p=atleta', 'icon' => 'fa-swimmer', 'label' => 'Atletas', 'color' => 'text-indigo-400'],
                        ['url' => '?p=entrenador', 'icon' => 'fa-user-tie', 'label' => 'Entrenadores', 'color' => 'text-emerald-400'],
                        ['url' => '?p=sesiones', 'icon' => 'fa-swimming-pool', 'label' => 'Sesiones', 'color' => 'text-cyan-400'],
                        ['url' => '?p=marcas', 'icon' => 'fa-stopwatch', 'label' => 'Marcas', 'color' => 'text-amber-400'],
                        ['url' => '?p=eventos', 'icon' => 'fa-calendar-alt', 'label' => 'Eventos', 'color' => 'text-rose-400'],
                        ['url' => '?p=analitica', 'icon' => 'fa-chart-pie', 'label' => 'Analítica', 'color' => 'text-purple-400']
                    ];
                    foreach ($modulos as $modulo):
                    ?>
                    <a href="<?php echo $modulo['url']; ?>" class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-4 text-center transition-all hover:scale-105 hover:shadow-lg hover:shadow-indigo-500/10 cursor-pointer group">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 dark:bg-[#0f0d23] flex items-center justify-center mb-2 group-hover:bg-indigo-500/10 transition">
                            <i class="fas <?php echo $modulo['icon']; ?> text-xl <?php echo $modulo['color']; ?> group-hover:text-indigo-500 transition"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-500 transition"><?php echo $modulo['label']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Últimas actividades y resumen rápido -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Últimas actividades -->
                    <div class="lg:col-span-2 bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 transition-colors duration-300">
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i> Últimas Actividades
                        </h3>
                        <div class="space-y-3">
                            <?php
                            // Simulación de actividades recientes (idealmente vendrían de la bitácora)
                            $actividades = [
                                ['icon' => 'fa-user-plus', 'text' => 'Nuevo atleta registrado: Jesús Hernández', 'time' => 'Hace 5 min', 'color' => 'text-emerald-400'],
                                ['icon' => 'fa-edit', 'text' => 'Marca actualizada: 50m Libre - 00:24.50', 'time' => 'Hace 15 min', 'color' => 'text-amber-400'],
                                ['icon' => 'fa-calendar-check', 'text' => 'Sesión planificada para mañana 8:00 AM', 'time' => 'Hace 1 hora', 'color' => 'text-cyan-400'],
                                ['icon' => 'fa-trophy', 'text' => 'Evento "Gala Regional 2026" creado', 'time' => 'Hace 2 horas', 'color' => 'text-purple-400'],
                                ['icon' => 'fa-user-check', 'text' => 'Asistencia registrada para 15 atletas', 'time' => 'Hace 3 horas', 'color' => 'text-green-400']
                            ];
                            foreach ($actividades as $act):
                            ?>
                            <div class="flex items-center gap-3 p-3 bg-gray-100 dark:bg-[#0f0d23] rounded-xl border border-gray-200 dark:border-[#252345]">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-[#252345] flex items-center justify-center">
                                    <i class="fas <?php echo $act['icon']; ?> <?php echo $act['color']; ?> text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-800 dark:text-gray-200"><?php echo $act['text']; ?></p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400"><?php echo $act['time']; ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Resumen rápido -->
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 transition-colors duration-300">
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-simple text-indigo-500"></i> Resumen Rápido
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-100 dark:bg-[#0f0d23] rounded-xl border border-gray-200 dark:border-[#252345]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Atletas Activos</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">156</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-100 dark:bg-[#0f0d23] rounded-xl border border-gray-200 dark:border-[#252345]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sesiones este Mes</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">42</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-swimming-pool"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-100 dark:bg-[#0f0d23] rounded-xl border border-gray-200 dark:border-[#252345]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Eventos Próximos</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">5</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-100 dark:bg-[#0f0d23] rounded-xl border border-gray-200 dark:border-[#252345]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Marcas Registradas</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">284</p>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <i class="fas fa-stopwatch"></i>
                                </div>
                            </div>
                        </div>
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

</body>
</html>