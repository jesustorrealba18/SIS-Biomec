<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Observaciones Tecnicas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        .input-dark::-webkit-calendar-picker-indicator { filter: invert(1); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .estrella { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .estrella:hover { transform: scale(1.15); }
        .estrella.seleccionada { transform: scale(1.1); }
        .c1 { background: #dc262620; color: #f87171; border-color: #dc262640; }
        .c2 { background: #f59e0b20; color: #fbbf24; border-color: #f59e0b40; }
        .c3 { background: #eab30820; color: #facc15; border-color: #eab30840; }
        .c4 { background: #22c55e20; color: #4ade80; border-color: #22c55e40; }
        .c5 { background: #16a34a20; color: #22c55e; border-color: #16a34a40; }
        .c1.seleccionada, .c2.seleccionada, .c3.seleccionada, .c4.seleccionada, .c5.seleccionada { box-shadow: 0 0 12px currentColor; }
    </style>
</head>
<body class="overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">

        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-[#0f0d23] border-r border-[#252345] z-40 transform -translate-x-full menu-transition lg:relative lg:translate-x-0 lg:flex-shrink-0 overflow-y-auto">
            <div class="p-4 flex justify-between items-center border-b border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-400 hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <?php 
                $tituloPagina = "Observaciones Tecnicas";
                $tituloPaginaResponsive = "Obs. Tecnicas";
                $iconModulo = "fas fa-clipboard-check";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#161430] p-6 rounded-2xl border border-[#252345]">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-indigo-500"></i> Evaluaciones Tecnicas
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Evaluaciones cualitativas de la tecnica del nadador por sesion.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('observacionesTecnicas', 'registrar')): ?>
                    <button onclick="abrirModalObservacion()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus text-sm"></i> Registrar Observacion
                    </button>
                    <?php endif; ?>
                </div>

                <div class="tarjeta p-5 border border-white/5 shadow-lg shadow-black/20">
                    <div class="flex items-center gap-2 border-b border-[#252345] pb-3 mb-4">
                        <i class="fas fa-filter text-indigo-400 text-sm"></i>
                        <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Busqueda</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user-circle text-gray-400 text-xs"></i>
                            </div>
                            <select id="filtroAtleta" onchange="cargarTabla()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                                <option value="">Todos los Atletas</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-crosshairs text-cyan-400/70 text-xs"></i>
                            </div>
                            <select id="filtroAspecto" onchange="cargarTabla()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                                <option value="">Todos los Aspectos</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-xs"></i>
                            </div>
                            <input type="text" id="busquedaGeneral" placeholder="Buscar en observaciones..." class="w-full input-dark pl-9 pr-4 py-2.5 rounded-xl text-xs" oninput="filtrarTabla()">
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-chart-bar text-emerald-400/70 text-xs"></i>
                            </div>
                            <select id="filtroAtletaResumen" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                                <option value="">Resumen por Atleta</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tarjeta overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                                    <th class="p-4">Fecha</th>
                                    <th class="p-4">Atleta</th>
                                    <th class="p-4">Aspecto</th>
                                    <th class="p-4 text-center">Calificacion</th>
                                    <th class="p-4">Observacion</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyObservaciones" class="divide-y divide-[#252345] text-sm text-gray-300">
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="modalObservacion" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">

            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-indigo-400"></i> Registrar Observacion Tecnica
                </h3>
                <button onclick="cerrarModalObservacion()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formObservacion" autocomplete="off">
                <input type="hidden" id="accion_form" name="accion" value="registrar">
                <input type="hidden" id="id_observacion" name="id_observacion" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta Seleccionado">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cedula..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm" autocomplete="off" required>
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]"></ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Aspecto Tecnico *</label>
                        <select id="id_aspecto_tecnico" name="id_aspecto_tecnico" data-validar="requerido" data-nombre="Aspecto Tecnico" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione un aspecto...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Sesion (Opcional)</label>
                        <select id="id_sesion" name="id_sesion" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Sin sesion asociada</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Calificacion *</label>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="estrella c1" data-valor="1" onclick="seleccionarCalificacion(1)">1</div>
                            <div class="estrella c2" data-valor="2" onclick="seleccionarCalificacion(2)">2</div>
                            <div class="estrella c3" data-valor="3" onclick="seleccionarCalificacion(3)">3</div>
                            <div class="estrella c4" data-valor="4" onclick="seleccionarCalificacion(4)">4</div>
                            <div class="estrella c5" data-valor="5" onclick="seleccionarCalificacion(5)">5</div>
                            <input type="hidden" id="calificacion" name="calificacion" value="">
                        </div>
                        <p id="textoCalificacion" class="text-[10px] text-gray-500 mt-1">1=Necesita trabajo | 2=Regular | 3=Bueno | 4=Muy bueno | 5=Excelente</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observacion Textual</label>
                    <textarea id="observacion_texto" name="observacion_texto" data-validar="texto" data-max="500" data-nombre="Observacion" rows="3" maxlength="500" placeholder="Notas detalladas sobre la tecnica observada..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalObservacion()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR OBSERVACION <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
            </div>
        </div>
    </div>

    <div id="modalResumen" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalResumen()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="resumenContenido">
            </div>
        </div>
    </div>

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
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('observacionesTecnicas', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/observacionesTecnicas.js"></script>
</body>
</html>