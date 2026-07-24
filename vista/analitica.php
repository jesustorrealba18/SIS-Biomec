<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'analitica';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Analítica | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 1.5rem;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
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

        <!-- Sidebar -->
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
                $tituloPagina = "Analítica Deportiva";
                $tituloPaginaResponsive = "Analítica";
                $iconModulo = "fas fa-chart-pie";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con filtros -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-chart-pie text-indigo-500"></i> Dashboard Analítico
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Métricas de rendimiento, evolución y estadísticas deportivas.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <select id="filtroTemporada" class="input-adapt px-4 py-2 rounded-xl text-sm">
                            <option value="">Todas las temporadas</option>
                            <option value="1">2025-2026</option>
                            <option value="2">2024-2025</option>
                        </select>
                        <select id="filtroGrupo" class="input-adapt px-4 py-2 rounded-xl text-sm">
                            <option value="">Todos los grupos</option>
                            <option value="1">Élite</option>
                            <option value="2">Desarrollo</option>
                            <option value="3">Iniciación</option>
                        </select>
                        <button class="gradiente-boton px-5 py-2 rounded-xl font-bold text-white hover:scale-105 transition text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> Exportar
                        </button>
                    </div>
                </div>

                <!-- KPIs principales -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Atletas Activos</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">156</h4>
                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400"><i class="fas fa-arrow-up mr-1"></i> +8%</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Volumen Semanal (m)</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">48.2k</h4>
                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400"><i class="fas fa-arrow-up mr-1"></i> +5%</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">RPE Promedio</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">6.8</h4>
                        <span class="text-[10px] text-amber-500 dark:text-amber-400"><i class="fas fa-minus mr-1"></i> Estable</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Asistencia (%)</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">92%</h4>
                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400"><i class="fas fa-arrow-up mr-1"></i> +2%</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">PBs batidos</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">12</h4>
                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400"><i class="fas fa-arrow-up mr-1"></i> +3</span>
                    </div>
                </div>

                <!-- Gráficas principales (2 columnas) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Evolución de Rendimiento -->
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Evolución de Marcas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">100m Libre - Promedio del equipo</p>
                        <div class="h-48">
                            <canvas id="graficaEvolucion"></canvas>
                        </div>
                    </div>

                    <!-- Comparativa Atletas -->
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Comparativa Top Atletas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Mejores tiempos en 100m Libre</p>
                        <div class="h-48">
                            <canvas id="graficaComparativa"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Segunda fila: Carga y Distribución -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Carga de entrenamiento -->
                    <div class="lg:col-span-2 tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Carga de Entrenamiento</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Volumen semanal (barras) y RPE promedio (línea)</p>
                        <div class="h-48">
                            <canvas id="graficaCarga"></canvas>
                        </div>
                    </div>

                    <!-- Distribución por categoría -->
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Distribución por Categoría</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Composición del equipo</p>
                        <div class="h-48">
                            <canvas id="graficaCategorias"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tercera fila: Estado de salud y metas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Lesiones activas -->
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Lesiones Activas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Distribución por zona anatómica</p>
                        <div class="h-48">
                            <canvas id="graficaLesiones"></canvas>
                        </div>
                    </div>

                    <!-- Progreso de metas -->
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Progreso de Metas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Porcentaje de cumplimiento por atleta</p>
                        <div class="h-48">
                            <canvas id="graficaMetas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla de alertas / insights -->
                <div class="tarjeta transition-colors duration-300">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Alertas Inteligentes</h3>
                        <span class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full">3 nuevas</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-[10px] text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-[#252345]">
                                <tr>
                                    <th class="p-2 text-left">Atleta</th>
                                    <th class="p-2 text-left">Alerta</th>
                                    <th class="p-2 text-left">Recomendación</th>
                                    <th class="p-2 text-right">Prioridad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-[#252345]">
                                <tr>
                                    <td class="p-2 font-medium text-gray-900 dark:text-white">Jesús Hernández</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">RPE alto (8.5) + molestia en hombro</td>
                                    <td class="p-2 text-gray-600 dark:text-gray-400">Reducir carga de brazada y evaluar</td>
                                    <td class="p-2 text-right text-red-500 font-bold">Alta</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-medium text-gray-900 dark:text-white">Maikol Parra</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">Sin mejora en 200m Espalda (3 meses)</td>
                                    <td class="p-2 text-gray-600 dark:text-gray-400">Revisar técnica de giro</td>
                                    <td class="p-2 text-right text-amber-500 font-bold">Media</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-medium text-gray-900 dark:text-white">Ana García</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">Volumen semanal > 60k por 3 semanas</td>
                                    <td class="p-2 text-gray-600 dark:text-gray-400">Programar semana de descarga</td>
                                    <td class="p-2 text-right text-amber-500 font-bold">Media</td>
                                </tr>
                            </tbody>
                        </table>
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

    <script>
        // Detectar tema para colores de gráficas
        const esOscuro = document.documentElement.classList.contains('dark');
        const colorTexto = esOscuro ? '#6b7280' : '#4b5563';
        const colorGrid = esOscuro ? '#25234540' : '#e5e7eb40';

        Chart.defaults.color = colorTexto;
        Chart.defaults.font.family = 'Inter';

        // 1. Evolución de marcas (100m Libre)
        new Chart(document.getElementById('graficaEvolucion'), {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Tiempo (seg)',
                    data: [58.2, 57.5, 56.8, 56.1, 55.4, 54.7, 53.9],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: colorGrid }, ticks: { color: colorTexto } },
                    y: { 
                        grid: { color: colorGrid }, 
                        ticks: { color: colorTexto },
                        reverse: true  // tiempos menores = mejor
                    }
                }
            }
        });

        // 2. Comparativa top atletas (100m Libre)
        new Chart(document.getElementById('graficaComparativa'), {
            type: 'bar',
            data: {
                labels: ['Jesús H.', 'Maikol P.', 'Ana G.', 'Carlos R.', 'Luisa M.'],
                datasets: [{
                    label: 'Mejor tiempo (seg)',
                    data: [52.3, 53.1, 54.5, 55.0, 55.8],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colorTexto } },
                    y: { 
                        grid: { color: colorGrid }, 
                        ticks: { color: colorTexto },
                        reverse: false
                    }
                }
            }
        });

        // 3. Carga de entrenamiento (Volumen + RPE)
        new Chart(document.getElementById('graficaCarga'), {
            type: 'bar',
            data: {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
                datasets: [
                    {
                        label: 'Volumen (km)',
                        data: [42, 45, 48, 50, 52, 48],
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderRadius: 4,
                        order: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'RPE Promedio',
                        data: [5.2, 5.8, 6.1, 6.8, 7.2, 6.5],
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: '#f59e0b',
                        fill: true,
                        order: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: colorTexto, font: { size: 10 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colorTexto } },
                    y: { 
                        type: 'linear',
                        position: 'left',
                        grid: { color: colorGrid },
                        ticks: { color: colorTexto },
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: colorTexto, max: 10, min: 0 },
                        beginAtZero: true
                    }
                }
            }
        });

        // 4. Distribución por categoría
        new Chart(document.getElementById('graficaCategorias'), {
            type: 'doughnut',
            data: {
                labels: ['Élite', 'Desarrollo', 'Iniciación', 'Master'],
                datasets: [{
                    data: [45, 60, 35, 16],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: colorTexto, font: { size: 9 }, boxWidth: 10 }
                    }
                }
            }
        });

        // 5. Lesiones activas por zona
        new Chart(document.getElementById('graficaLesiones'), {
            type: 'bar',
            data: {
                labels: ['Hombro', 'Rodilla', 'Espalda', 'Tobillo', 'Codo'],
                datasets: [{
                    label: 'Casos activos',
                    data: [4, 3, 2, 1, 1],
                    backgroundColor: ['#ef4444', '#f59e0b', '#6366f1', '#10b981', '#8b5cf6'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colorTexto } },
                    y: { 
                        grid: { color: colorGrid }, 
                        ticks: { color: colorTexto, stepSize: 1, beginAtZero: true }
                    }
                }
            }
        });

        // 6. Progreso de metas (porcentaje de cumplimiento)
        new Chart(document.getElementById('graficaMetas'), {
            type: 'bar',
            data: {
                labels: ['Jesús H.', 'Maikol P.', 'Ana G.', 'Carlos R.', 'Luisa M.'],
                datasets: [{
                    label: '% de meta alcanzado',
                    data: [85, 72, 90, 60, 78],
                    backgroundColor: ['#10b981', '#f59e0b', '#10b981', '#ef4444', '#6366f1'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colorTexto } },
                    y: { 
                        grid: { color: colorGrid }, 
                        ticks: { color: colorTexto, max: 100, beginAtZero: true },
                        max: 100
                    }
                }
            }
        });
    </script>

</body>
</html>