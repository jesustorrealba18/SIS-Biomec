<?php
$pagina = 'sistemaExperto';

/* Atletas reales (solo lectura) para filtros y asignacion de recomendaciones */
$atletasJson = [];
foreach (($atletas ?? []) as $a) {
    $atletasJson[] = [
        'id'        => isset($a['id_atleta']) ? (int)$a['id_atleta'] : 0,
        'nombre'    => trim(($a['nombres'] ?? '') . ' ' . ($a['apellidos'] ?? '')),
        'categoria' => $a['categoria_nombre'] ?? 'Sin categoría',
        'sexo'      => $a['sexo'] ?? '',
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Sistema Experto | SGRD</title>
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
        .input-adapt {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #111827;
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            outline: none;
        }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }
        .dark .input-adapt {
            background-color: #252345;
            border-color: #3a3663;
            color: #e5e7eb;
        }
        /* Arbol de decision */
        .arbol-nodo {
            border: 1px dashed #9ca3af;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            background-color: #f9fafb;
            text-align: center;
        }
        .dark .arbol-nodo {
            border-color: #4b4878;
            background-color: #1c1935;
        }
        .arbol-nodo.final {
            border-style: solid;
            border-width: 2px;
        }
        .arbol-no {
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
            margin-top: 2px;
        }
        .modal-scroll::-webkit-scrollbar { width: 6px; }
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
                $tituloPagina = "Sistema Experto";
                $tituloPaginaResponsive = "Sistema Experto";
                $iconModulo = "fas fa-brain";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado con filtros -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-brain text-indigo-500"></i> Motor de Recomendaciones
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Alertas y recomendaciones generadas a partir del árbol de decisión sobre carga, bienestar, lesiones y rendimiento.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <select id="filtroAtleta" class="input-adapt px-4 py-2 rounded-xl text-sm">
                            <option value="">Todos los atletas</option>
                        </select>
                        <select id="filtroTipo" class="input-adapt px-4 py-2 rounded-xl text-sm">
                            <option value="">Todos los tipos</option>
                            <option value="Carga">Carga</option>
                            <option value="Lesion">Lesión</option>
                            <option value="Descanso">Descanso</option>
                            <option value="Rendimiento">Rendimiento</option>
                        </select>
                        <select id="filtroSeveridad" class="input-adapt px-4 py-2 rounded-xl text-sm">
                            <option value="">Todas las severidades</option>
                            <option value="alerta">Alerta</option>
                            <option value="aviso">Aviso</option>
                            <option value="info">Info</option>
                        </select>
                        <button id="btnEvaluar" class="gradiente-boton px-5 py-2 rounded-xl font-bold text-white hover:scale-105 transition text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i> Evaluar ahora
                        </button>
                    </div>
                </div>

                <!-- KPIs del Sistema Experto -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Alertas Activas</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">2</h4>
                        <span class="text-[10px] text-red-500 dark:text-red-400"><i class="fas fa-circle-exclamation mr-1"></i> Requieren atención</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Atletas en Riesgo</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">3</h4>
                        <span class="text-[10px] text-amber-500 dark:text-amber-400"><i class="fas fa-user-injured mr-1"></i> Carga / lesión / descanso</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Recomendaciones Vigentes</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">8</h4>
                        <span class="text-[10px] text-indigo-500 dark:text-indigo-400"><i class="fas fa-lightbulb mr-1"></i> 2 alertas · 3 avisos · 3 infos</span>
                    </div>
                    <div class="tarjeta transition-colors duration-300">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Índice de Riesgo del Equipo</p>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">34<span class="text-sm font-bold text-gray-400">/100</span></h4>
                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400"><i class="fas fa-arrow-down mr-1"></i> -6% vs semana anterior</span>
                    </div>
                </div>

                <!-- Feed de recomendaciones -->
                <div class="tarjeta transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <i class="fas fa-clipboard-list text-indigo-500"></i> Recomendaciones del Sistema Experto
                            </h3>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Cada recomendación indica la evidencia que disparó las reglas del árbol de decisión.</p>
                        </div>
                        <span id="contadorFeed" class="text-[10px] bg-indigo-500/10 text-indigo-500 dark:text-indigo-300 px-2 py-0.5 rounded-full self-start sm:self-auto"></span>
                    </div>

                    <div id="feedRecomendaciones" class="space-y-4">
                        <!-- Cards generadas por assets/js/sistemaExperto.js -->
                    </div>
                </div>

                <!-- Evolución del índice de riesgo -->
                <div class="tarjeta transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-1">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Evolución del Índice de Riesgo</h3>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Últimas 8 semanas · calculado con RPE, bienestar, lesiones y marcas</p>
                        </div>
                        <select id="selectorAtletaGrafica" class="input-adapt px-4 py-2 rounded-xl text-sm max-w-xs"></select>
                    </div>
                    <div class="h-56 mt-3">
                        <canvas id="graficaRiesgo"></canvas>
                    </div>
                </div>

                <!-- Reglas del motor (resumen) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="tarjeta !p-4 border-l-4 !border-l-red-500 transition-colors duration-300">
                        <p class="text-xs font-bold text-gray-900 dark:text-white"><i class="fas fa-triangle-exclamation text-red-500 mr-1"></i> R-01 · Alerta roja</p>
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">RPE alto sostenido + lesión activa o molestia referida → suspender carga alta y derivar a revisión.</p>
                    </div>
                    <div class="tarjeta !p-4 border-l-4 !border-l-amber-500 transition-colors duration-300">
                        <p class="text-xs font-bold text-gray-900 dark:text-white"><i class="fas fa-circle-exclamation text-amber-500 mr-1"></i> R-02 · Aviso</p>
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">RPE > 7.0, déficit de sueño o monotonía alta → reducir volumen 20–30% y reforzar bienestar.</p>
                    </div>
                    <div class="tarjeta !p-4 border-l-4 !border-l-emerald-500 transition-colors duration-300">
                        <p class="text-xs font-bold text-gray-900 dark:text-white"><i class="fas fa-arrow-trend-up text-emerald-500 mr-1"></i> R-03 · Refuerzo positivo</p>
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Mejora de marcas + buena técnica y bienestar estable → mantener plan y considerar progresión.</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal: Árbol de decisión -->
    <div id="modalArbol" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" id="modalArbolBackdrop"></div>
        <div class="relative tarjeta w-full max-w-lg max-h-[85vh] overflow-y-auto modal-scroll">
            <div class="flex justify-between items-start gap-4 mb-4">
                <div>
                    <h3 id="modalArbolTitulo" class="text-sm font-bold text-gray-900 dark:text-white"></h3>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Camino evaluado por el motor de inferencia para esta recomendación.</p>
                </div>
                <button id="modalArbolCerrar" class="text-gray-400 hover:text-gray-700 dark:hover:text-white text-xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalArbolContenido" class="space-y-1"></div>
            <p class="text-[10px] text-gray-400 dark:text-gray-500 text-center mt-4">
                <i class="fas fa-circle-nodes mr-1"></i> Regla evaluada sobre datos de carga, bienestar, lesiones y rendimiento del módulo correspondiente.
            </p>
        </div>
    </div>

    <!-- Toast de demostración -->
    <div id="toastDemo" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="tarjeta !p-4 shadow-2xl border-l-4 !border-l-indigo-500 flex items-start gap-3 max-w-xs">
            <i class="fas fa-circle-check text-indigo-500 mt-0.5"></i>
            <p id="toastDemoTexto" class="text-xs text-gray-700 dark:text-gray-300"></p>
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
        /* Atletas reales leidos por el controlador (solo lectura) */
        window.SIS_ATLETAS = <?= json_encode($atletasJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <script src="assets/js/sistemaExperto.js"></script>
</body>
</html>
