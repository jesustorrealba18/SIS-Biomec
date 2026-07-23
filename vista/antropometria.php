<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'antropometria';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Seguimiento Antropométrico | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
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
                $tituloPagina = "Expedientes Antropométricos";
                $tituloPaginaResponsive = "Antropometría";
                $iconModulo = "fas fa-child";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado con resumen -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] shadow-2xl relative overflow-hidden transition-colors duration-300">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-child text-indigo-500"></i> Dashboard Antropométrico
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Control y evolución biológica de atletas (RF-05)</p>
                    </div>
                    <div class="relative z-10 flex gap-3">
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar')): ?>
                        <button onclick="abrirModalMedicion()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fas fa-plus-circle text-sm"></i> Nueva Medición
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de atletas con última medición -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-[#1c1a3a] text-indigo-600 dark:text-indigo-300 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-[#252345]">
                                    <th class="p-4 font-semibold">Atleta</th>
                                    <th class="p-4 font-semibold">Categoría</th>
                                    <th class="p-4 font-semibold text-center">Última Eval.</th>
                                    <th class="p-4 font-semibold text-center">Peso / Talla</th>
                                    <th class="p-4 font-semibold text-center">IMC</th>
                                    <th class="p-4 font-semibold text-center">Estado</th>
                                    <th class="p-4 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDashboardBody" class="text-sm divide-y divide-gray-200 dark:divide-[#252345] text-gray-800 dark:text-gray-300">
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR MEDICIÓN ===== -->
    <div id="modalMedicion" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            
            <button type="button" onclick="cerrarModalMedicion()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-gray-100 dark:bg-[#161430] p-6 border-b border-gray-200 dark:border-white/5 flex items-center relative z-10 transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-weight text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <h2 id="modalMedicionTitulo" class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Medición</h2>
            </div>

            <form id="formMedicion" class="p-8 space-y-6 relative z-10">
                <input type="hidden" id="accion" name="accion" value="guardar">
                <input type="hidden" id="id_medicion" name="id_medicion">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-user text-indigo-500 w-5"></i> Atleta *
                        </label>
                        <select id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta" class="w-full p-3.5 rounded-xl input-adapt cursor-pointer text-sm font-medium">
                            <option value="">Seleccione un atleta...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-calendar-alt text-indigo-500 w-5"></i> Fecha de Evaluación *
                        </label>
                        <input type="date" id="fecha" name="fecha" data-validar="requerido" data-nombre="Fecha" class="w-full p-3.5 rounded-xl input-adapt text-sm" max="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-weight-hanging text-indigo-500 w-5"></i> Peso (kg) *
                        </label>
                        <input type="number" step="0.1" id="peso" name="peso" data-validar="requerido" data-nombre="Peso" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 75.5">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-vertical text-indigo-500 w-5"></i> Talla (cm) *
                        </label>
                        <input type="number" step="0.1" id="talla" name="talla" data-validar="requerido" data-nombre="Talla" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 180">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-horizontal text-indigo-500 w-5"></i> Envergadura (cm) *
                        </label>
                        <input type="number" step="0.1" id="envergadura" name="envergadura" data-validar="requerido" data-nombre="Envergadura" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 185">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-circle-notch text-indigo-500 w-5"></i> Perím. Abdominal (cm) *
                        </label>
                        <input type="number" step="0.1" id="perimetro_abdominal" name="perimetro_abdominal" data-validar="requerido" data-nombre="Perímetro Abdominal" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Ej: 80">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-percent text-indigo-500 w-5"></i> % Grasa Corporal
                        </label>
                        <input type="number" step="0.1" id="grasa_corporal" name="grasa_corporal" class="w-full p-3.5 rounded-xl input-adapt text-sm" placeholder="Opcional">
                    </div>
                </div>

                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-500/30 rounded-xl flex justify-between items-center mt-4 transition-colors duration-300">
                    <span class="text-sm text-indigo-600 dark:text-indigo-300"><i class="fas fa-calculator mr-2"></i>IMC Proyectado:</span>
                    <span id="imc_preview" class="text-xl font-bold text-gray-900 dark:text-white">--</span>
                </div>

                <div id="contenedorJustificacion" class="space-y-2 hidden mt-4">
                    <label class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Justificación de la Modificación *
                    </label>
                    <textarea id="justificacion" name="justificacion" rows="2" class="w-full p-3.5 rounded-xl input-adapt text-sm border-orange-500/50 focus:border-orange-500" placeholder="Auditoría: Explique brevemente por qué corrige este registro."></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-white/5">
                    <button type="button" onclick="cerrarModalMedicion()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-[#252345] dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL GRÁFICAS ===== -->
    <div id="modalGraficas" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-[#060512]/90 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-5xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            
            <button type="button" onclick="cerrarModalGraficas()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-gray-100 dark:bg-[#161430] p-6 border-b border-gray-200 dark:border-white/5 flex items-center relative z-10 transition-colors duration-300">
                <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Evolución Antropométrica</h2>
            </div>

            <div class="p-6 space-y-8 relative z-10">
                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 transition-colors duration-300">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/30 flex items-center justify-center text-xl font-bold text-indigo-600 dark:text-indigo-300">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <div>
                        <h3 id="graficaAtletaNombre" class="text-lg font-bold text-gray-900 dark:text-white">Cargando...</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Historial de mediciones corporales</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="tarjeta p-4 transition-colors duration-300">
                        <h4 class="text-center text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Evolución de Peso (kg) y Talla (cm)</h4>
                        <canvas id="chartPesoTalla" height="200"></canvas>
                    </div>
                    <div class="tarjeta p-4 transition-colors duration-300">
                        <h4 class="text-center text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Curva del Índice de Masa Corporal (IMC)</h4>
                        <canvas id="chartIMC" height="200"></canvas>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Registros Históricos</h4>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-[#252345]">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-gray-100 dark:bg-[#252345] text-gray-700 dark:text-indigo-200">
                                <tr>
                                    <th class="p-3">Fecha</th>
                                    <th class="p-3">Peso</th>
                                    <th class="p-3">Talla</th>
                                    <th class="p-3">Envergadura</th>
                                    <th class="p-3">IMC</th>
                                    <th class="p-3">Responsable</th>
                                    <th class="p-3 text-center">Edición</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialBody" class="divide-y divide-gray-200 dark:divide-[#252345] bg-white dark:bg-[#161430] text-gray-800 dark:text-gray-300">
                            </tbody>
                        </table>
                    </div>
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
    <script>
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/antropometria.js"></script>
</body>
</html>