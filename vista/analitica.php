<?php
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
        .menu-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .gradiente-boton { background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%); }
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
        .input-adapt {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #1f2937;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.25rem;
            cursor: pointer;
            transition: border-color .2s, background-color .2s, color .2s;
        }
        .input-adapt:hover { border-color: #a5b4fc; }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .dark .input-adapt {
            background-color: #161430;
            border-color: #252345;
            color: #e5e7eb;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a5b4fc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        }
        .dark .input-adapt:hover { border-color: #3a3a6e; }
        .dark .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        .input-adapt option { background-color: #ffffff; color: #1f2937; }
        .dark .input-adapt option { background-color: #161430; color: #e5e7eb; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">

        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

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

                <!-- Encabezado -->
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

                <!-- KPIs -->
                <?php
                    $kpis = $datosAnalitica['kpis'];
                    $rendimiento = $datosAnalitica['rendimiento'];

                    if (!function_exists('badgeTendenciaAnalitica')) {
                        function badgeTendenciaAnalitica(array $t): string {
                            $dir = $t['dir'] ?? 'flat';
                            $pct = $t['pct'] ?? null;

                            if (($dir === 'up' || $dir === 'down') && $pct !== null) {
                                $esUp = ($dir === 'up');
                                $clase = $esUp
                                    ? 'text-emerald-500 dark:text-emerald-400'
                                    : 'text-red-500 dark:text-red-400';
                                $icono = $esUp ? 'fa-arrow-up' : 'fa-arrow-down';
                                $signo = $esUp ? '+' : '';

                                return sprintf(
                                    '<span class="text-[10px] %s"><i class="fas %s mr-1"></i> %s%s%%</span>',
                                    $clase, $icono, $signo, $pct
                                );
                            }

                            return '<span class="text-[10px] text-amber-500 dark:text-amber-400"><i class="fas fa-minus mr-1"></i> Estable</span>';
                        }
                    }
                ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Atletas Activos</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $kpis['atletas']['valor']; ?></h4>
                        <?php echo badgeTendenciaAnalitica($kpis['atletas']['tendencia']); ?>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Volumen Semanal (m)</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $kpis['volumen']['valor']; ?></h4>
                        <?php echo badgeTendenciaAnalitica($kpis['volumen']['tendencia']); ?>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">RPE Promedio</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $kpis['rpe']['valor']; ?></h4>
                        <?php echo badgeTendenciaAnalitica($kpis['rpe']['tendencia']); ?>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Asistencia (%)</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $kpis['asistencia']['valor']; ?></h4>
                        <?php echo badgeTendenciaAnalitica($kpis['asistencia']['tendencia']); ?>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">PBs últimos 30d</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1"><?php echo $kpis['pbs']['valor']; ?></h4>
                        <?php echo badgeTendenciaAnalitica($kpis['pbs']['tendencia']); ?>
                    </div>
                </div>

                <!-- Fila: Rendimiento (2 columnas) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Evolución de Marcas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4"><?php echo $rendimiento['prueba'] ? htmlspecialchars($rendimiento['prueba']) . ' - Promedio del equipo' : 'Sin datos registrados aún'; ?></p>
                        <div class="h-48"><canvas id="graficaEvolucion"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Comparativa Top Atletas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4"><?php echo $rendimiento['prueba'] ? 'Mejores tiempos en ' . htmlspecialchars($rendimiento['prueba']) : 'Sin datos registrados aún'; ?></p>
                        <div class="h-48"><canvas id="graficaComparativa"></canvas></div>
                    </div>
                </div>

                <!-- Fila: Carga + Categorías -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Carga de Entrenamiento</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Volumen semanal (barras) y RPE promedio (línea)</p>
                        <div class="h-48"><canvas id="graficaCarga"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Distribución por Categoría</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Composición del equipo</p>
                        <div class="h-48"><canvas id="graficaCategorias"></canvas></div>
                    </div>
                </div>

                <!-- Fila: Salud + Metas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Lesiones Activas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Distribución por zona anatómica</p>
                        <div class="h-48"><canvas id="graficaLesiones"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Progreso de Metas</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Porcentaje de cumplimiento por atleta</p>
                        <div class="h-48"><canvas id="graficaMetas"></canvas></div>
                    </div>
                </div>

                <!-- Fila: Grupos + Entrenadores -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Atletas por Grupo</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Grupos con mayor cantidad de atletas activos</p>
                        <div class="h-56"><canvas id="graficaGrupos"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Atletas por Entrenador</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Carga de atletas asignados a cada entrenador</p>
                        <div class="h-56"><canvas id="graficaEntrenadores"></canvas></div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- NUEVA SECCIÓN: Sesiones de entrenamiento -->
                <!-- ============================================================ -->
                <div class="pt-2">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="fas fa-dumbbell text-indigo-500"></i> Estadísticas de Sesiones
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Análisis del plan de entrenamiento y su ejecución.</p>
                </div>

                <!-- Fila: Tipos de sesión + Estado de sesiones -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Distribución de Tipos de Sesión</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Balance del plan de entrenamiento</p>
                        <div class="h-56"><canvas id="graficaTiposSesion"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado de las Sesiones</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Planificadas vs Completadas vs Canceladas</p>
                        <div class="h-56"><canvas id="graficaEstadoSesiones"></canvas></div>
                    </div>
                </div>

                <!-- Fila: Cumplimiento + Bloques -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cumplimiento del Plan por Grupo</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">% promedio de volumen ejecutado vs planificado</p>
                        <div class="h-56"><canvas id="graficaCumplimiento"></canvas></div>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Bloques Más Trabajados</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4">Volumen total (m) por fase de la sesión</p>
                        <div class="h-56"><canvas id="graficaBloques"></canvas></div>
                    </div>
                </div>

                <!-- Tabla de alertas -->
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
        const esOscuro = document.documentElement.classList.contains('dark');
        const colorTexto = esOscuro ? '#6b7280' : '#4b5563';
        const colorGrid = esOscuro ? '#25234540' : '#e5e7eb40';

        Chart.defaults.color = colorTexto;
        Chart.defaults.font.family = 'Inter';

        const DATA = <?php echo json_encode($datosAnalitica, JSON_UNESCAPED_UNICODE); ?>;

        const MESES_CORTOS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const etiquetaMes = m => {
            const partes = String(m).split('-');
            const idx = parseInt(partes[1], 10) - 1;
            return (MESES_CORTOS[idx] || m) + (partes[0] ? " '" + partes[0].slice(2) : '');
        };
        const nombreCorto = n => {
            const p = String(n).split(' ').filter(Boolean);
            return p.length > 1 ? p[0] + ' ' + p[1].charAt(0) + '.' : String(n);
        };
        const tieneDatos = arr => Array.isArray(arr) && arr.length > 0 && arr.some(v => v !== null && v !== undefined && v !== '');
        const marcarSinDatos = id => {
            const canvas = document.getElementById(id);
            if (canvas && canvas.parentElement) {
                canvas.parentElement.innerHTML = '<div class="h-full flex items-center justify-center text-xs text-gray-400 dark:text-gray-500"><span>Sin datos registrados aún</span></div>';
            }
        };

        // 1. Evolución
        if (tieneDatos(DATA.rendimiento.evolucion.valores)) {
            new Chart(document.getElementById('graficaEvolucion'), {
                type: 'line',
                data: {
                    labels: DATA.rendimiento.evolucion.labels.map(etiquetaMes),
                    datasets: [{
                        label: 'Tiempo (seg)',
                        data: DATA.rendimiento.evolucion.valores,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3, tension: 0.4, fill: true,
                        pointRadius: 4, pointBackgroundColor: '#6366f1'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: colorGrid }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto }, reverse: true }
                    }
                }
            });
        } else { marcarSinDatos('graficaEvolucion'); }

        // 2. Comparativa
        if (tieneDatos(DATA.rendimiento.comparativa.valores)) {
            new Chart(document.getElementById('graficaComparativa'), {
                type: 'bar',
                data: {
                    labels: DATA.rendimiento.comparativa.labels.map(nombreCorto),
                    datasets: [{
                        label: 'Mejor tiempo (seg)',
                        data: DATA.rendimiento.comparativa.valores,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto }, reverse: true }
                    }
                }
            });
        } else { marcarSinDatos('graficaComparativa'); }

        // 3. Carga
        if (tieneDatos(DATA.carga.volumen_km)) {
            new Chart(document.getElementById('graficaCarga'), {
                type: 'bar',
                data: {
                    labels: DATA.carga.labels,
                    datasets: [
                        { label: 'Volumen (km)', data: DATA.carga.volumen_km, backgroundColor: 'rgba(99, 102, 241, 0.6)', borderRadius: 4, order: 2, yAxisID: 'y' },
                        { label: 'RPE Promedio', data: DATA.carga.rpe, type: 'line', borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', borderWidth: 2, tension: 0.3, pointBackgroundColor: '#f59e0b', fill: true, order: 1, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: colorTexto, font: { size: 10 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { type: 'linear', position: 'left', grid: { color: colorGrid }, ticks: { color: colorTexto }, beginAtZero: true },
                        y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, ticks: { color: colorTexto, max: 10, min: 0 }, beginAtZero: true }
                    }
                }
            });
        } else { marcarSinDatos('graficaCarga'); }

        // 4. Categorías
        if (tieneDatos(DATA.categorias.valores)) {
            new Chart(document.getElementById('graficaCategorias'), {
                type: 'doughnut',
                data: {
                    labels: DATA.categorias.labels,
                    datasets: [{ data: DATA.categorias.valores, backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'], borderWidth: 0 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: colorTexto, font: { size: 9 }, boxWidth: 10 } } }
                }
            });
        } else { marcarSinDatos('graficaCategorias'); }

        // 5. Lesiones
        if (tieneDatos(DATA.lesiones.valores)) {
            new Chart(document.getElementById('graficaLesiones'), {
                type: 'bar',
                data: {
                    labels: DATA.lesiones.labels,
                    datasets: [{ label: 'Casos activos', data: DATA.lesiones.valores, backgroundColor: ['#ef4444', '#f59e0b', '#6366f1', '#10b981', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'], borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto, stepSize: 1, beginAtZero: true } }
                    }
                }
            });
        } else { marcarSinDatos('graficaLesiones'); }

        // 6. Metas
        if (tieneDatos(DATA.metas.valores)) {
            new Chart(document.getElementById('graficaMetas'), {
                type: 'bar',
                data: {
                    labels: DATA.metas.labels.map(nombreCorto),
                    datasets: [{ label: '% de meta alcanzado', data: DATA.metas.valores, backgroundColor: DATA.metas.valores.map(v => v >= 85 ? '#10b981' : (v >= 60 ? '#f59e0b' : '#ef4444')), borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto, max: 100, beginAtZero: true }, max: 100 }
                    }
                }
            });
        } else { marcarSinDatos('graficaMetas'); }

        // 7. Grupos
        if (tieneDatos(DATA.grupos.valores)) {
            new Chart(document.getElementById('graficaGrupos'), {
                type: 'bar',
                data: {
                    labels: DATA.grupos.labels,
                    datasets: [{ label: 'Atletas', data: DATA.grupos.valores, backgroundColor: '#6366f1', borderRadius: 6 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { afterLabel: (ctx) => 'Entrenador: ' + (DATA.grupos.entrenador[ctx.dataIndex] || 'Sin asignar') } }
                    },
                    scales: {
                        x: { grid: { color: colorGrid }, ticks: { color: colorTexto, stepSize: 1, beginAtZero: true } },
                        y: { grid: { display: false }, ticks: { color: colorTexto } }
                    }
                }
            });
        } else { marcarSinDatos('graficaGrupos'); }

        // 8. Entrenadores
        if (tieneDatos(DATA.entrenadores.valores)) {
            new Chart(document.getElementById('graficaEntrenadores'), {
                type: 'bar',
                data: {
                    labels: DATA.entrenadores.labels.map(nombreCorto),
                    datasets: [{ label: 'Atletas', data: DATA.entrenadores.valores, backgroundColor: '#10b981', borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { afterLabel: (ctx) => 'Grupos activos: ' + (DATA.entrenadores.grupos[ctx.dataIndex] || 0) } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto, stepSize: 1, beginAtZero: true } }
                    }
                }
            });
        } else { marcarSinDatos('graficaEntrenadores'); }

        // ============================================================
        // NUEVOS: Sesiones
        // ============================================================

        // 9. Tipos de sesión (dona)
        if (tieneDatos(DATA.tipos_sesion.valores)) {
            new Chart(document.getElementById('graficaTiposSesion'), {
                type: 'doughnut',
                data: {
                    labels: DATA.tipos_sesion.labels,
                    datasets: [{
                        data: DATA.tipos_sesion.valores,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colorTexto, font: { size: 10 }, boxWidth: 10 } }
                    }
                }
            });
        } else { marcarSinDatos('graficaTiposSesion'); }

        // 10. Estado de sesiones (dona)
        if (tieneDatos(DATA.estado_sesiones.valores)) {
            const coloresEstado = {
                'Planificada': '#6366f1',
                'Parcial':     '#f59e0b',
                'Completada':  '#10b981',
                'Cancelada':   '#ef4444'
            };
            new Chart(document.getElementById('graficaEstadoSesiones'), {
                type: 'doughnut',
                data: {
                    labels: DATA.estado_sesiones.labels,
                    datasets: [{
                        data: DATA.estado_sesiones.valores,
                        backgroundColor: DATA.estado_sesiones.labels.map(l => coloresEstado[l] || '#94a3b8'),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colorTexto, font: { size: 10 }, boxWidth: 10 } }
                    }
                }
            });
        } else { marcarSinDatos('graficaEstadoSesiones'); }

        // 11. Cumplimiento por grupo
        if (tieneDatos(DATA.cumplimiento.valores)) {
            new Chart(document.getElementById('graficaCumplimiento'), {
                type: 'bar',
                data: {
                    labels: DATA.cumplimiento.labels,
                    datasets: [{
                        label: '% cumplimiento',
                        data: DATA.cumplimiento.valores,
                        backgroundColor: DATA.cumplimiento.valores.map(v => v >= 95 ? '#10b981' : (v >= 85 ? '#f59e0b' : '#ef4444')),
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                afterLabel: (ctx) => 'Sesiones completadas: ' + (DATA.cumplimiento.sesiones[ctx.dataIndex] || 0)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { color: colorGrid }, ticks: { color: colorTexto, beginAtZero: true, max: 120 }, max: 120 },
                        y: { grid: { display: false }, ticks: { color: colorTexto } }
                    }
                }
            });
        } else { marcarSinDatos('graficaCumplimiento'); }

        // 12. Bloques más trabajados
        if (tieneDatos(DATA.bloques.volumen_total)) {
            const coloresBloque = {
                'Calentamiento':       '#6366f1',
                'Principal':           '#10b981',
                'Vuelta a la calma':   '#f59e0b'
            };
            new Chart(document.getElementById('graficaBloques'), {
                type: 'bar',
                data: {
                    labels: DATA.bloques.labels,
                    datasets: [{
                        label: 'Volumen total (m)',
                        data: DATA.bloques.volumen_total,
                        backgroundColor: DATA.bloques.labels.map(l => coloresBloque[l] || '#8b5cf6'),
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                afterLabel: (ctx) => 'Series: ' + (DATA.bloques.series[ctx.dataIndex] || 0)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colorTexto } },
                        y: { grid: { color: colorGrid }, ticks: { color: colorTexto, beginAtZero: true } }
                    }
                }
            });
        } else { marcarSinDatos('graficaBloques'); }
    </script>

</body>
</html>