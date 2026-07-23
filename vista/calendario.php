<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'calendario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Calendario General | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
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

        /* ===== FULLCALENDAR - MODO CLARO ===== */
        #calendario-container { padding: 1.5rem; }

        .fc {
            --fc-border-color: #e5e7eb;
            --fc-button-bg-color: #e5e7eb;
            --fc-button-border-color: #d1d5db;
            --fc-button-text-color: #374151;
            --fc-button-hover-bg-color: #d1d5db;
            --fc-button-hover-border-color: #9ca3af;
            --fc-button-active-bg-color: #6366f1;
            --fc-button-active-border-color: #4f46e5;
            --fc-button-active-text-color: #ffffff;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #f9fafb;
            --fc-list-event-hover-bg-color: #f3f4f6;
            --fc-today-bg-color: #eef2ff;
            --fc-event-border-color: transparent;
            --fc-button-icon-color: #374151;
            --fc-button-icon-hover-color: #1f2937;
            --fc-button-icon-active-color: #fff;
        }

        /* ===== FULLCALENDAR - MODO OSCURO ===== */
        .dark .fc {
            --fc-border-color: #252345;
            --fc-button-bg-color: #1e1b4b;
            --fc-button-border-color: #3730a3;
            --fc-button-text-color: #c7d2fe;
            --fc-button-hover-bg-color: #312e81;
            --fc-button-hover-border-color: #4338ca;
            --fc-button-active-bg-color: #4338ca;
            --fc-button-active-border-color: #6366f1;
            --fc-button-active-text-color: #ffffff;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #111026;
            --fc-list-event-hover-bg-color: #1e1b4b;
            --fc-today-bg-color: #1e1b4b44;
            --fc-event-border-color: transparent;
            --fc-button-icon-color: #c7d2fe;
            --fc-button-icon-hover-color: #e0e7ff;
            --fc-button-icon-active-color: #fff;
        }

        .fc .fc-toolbar-title {
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .dark .fc .fc-toolbar-title {
            color: #ffffff;
        }

        .fc .fc-col-header-cell-cushion {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .dark .fc .fc-col-header-cell-cushion {
            color: #6b7280;
        }

        .fc .fc-daygrid-day-number {
            color: #4b5563;
            text-decoration: none;
        }
        .dark .fc .fc-daygrid-day-number {
            color: #9ca3af;
        }

        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            background: #6366f1;
            color: #ffffff;
            border-radius: 9999px;
            padding: 2px 6px;
        }

        .fc .fc-event {
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            color: #ffffff !important;
        }
        .fc .fc-event:hover {
            opacity: 0.85;
            filter: brightness(1.1);
        }

        .fc .fc-daygrid-more-link {
            color: #6366f1;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .dark .fc .fc-daygrid-more-link {
            color: #818cf8;
        }

        .fc .fc-scrollgrid,
        .fc th,
        .fc td {
            border-color: #e5e7eb;
        }
        .dark .fc .fc-scrollgrid,
        .dark .fc th,
        .dark .fc td {
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
                $tituloPagina = "Calendario General";
                $tituloPaginaResponsive = "Calendario";
                $iconModulo = "fas fa-calendar-alt";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-500"></i> Calendario General
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Visualización de eventos competitivos y entrenamientos programados.</p>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div id="calendario-container" class="p-4 sm:p-6"></div>
                </div>

                <!-- Leyenda -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-4 transition-colors duration-300">
                    <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-bold tracking-widest mb-3">Leyenda por Tipo de Evento</p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#3b82f6]"></span> Regional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#10b981]"></span> Nacional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f59e0b]"></span> Internacional</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f97316]"></span> Selectivo</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#6b7280]"></span> Control</span>
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

        // ===== INICIALIZAR FULLCALENDAR =====
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendario-container');
            if (!calendarEl) return;

            // Detectamos el tema actual para los colores del calendario (opcional)
            // FullCalendar usa variables CSS que ya hemos definido en el <style>

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                height: 'auto',
                firstDay: 1,
                eventSources: [
                    {
                        url: 'index.php?p=eventos&accion=calendario',
                        color: '#6366f1'
                    }
                ],
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });
            calendar.render();
        });
    </script>

</body>
</html>