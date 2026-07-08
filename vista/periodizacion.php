<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Periodizacion ATR | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
        .input-dark::-webkit-calendar-picker-indicator { filter: invert(1); }
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .timeline-container {
            position: relative;
            height: 60px;
            background: #0f0d23;
            border-radius: 12px;
            overflow: hidden;
        }
        .timeline-bar {
            position: absolute;
            top: 4px;
            bottom: 4px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            cursor: pointer;
            min-width: 20px;
        }
        .timeline-bar:hover {
            transform: scaleY(1.1);
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .timeline-bar.fase-activa {
            outline: 2px solid white;
            outline-offset: 2px;
            box-shadow: 0 0 15px rgba(255,255,255,0.3);
            z-index: 5;
        }
        .timeline-semanas {
            position: absolute;
            bottom: -22px;
            left: 0;
            right: 0;
            display: flex;
            font-size: 9px;
            color: #4b5563;
        }
        .timeline-semanas span {
            flex: 1;
            text-align: center;
            border-left: 1px solid #1f1d38;
        }
        .fase-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
        }
        .tooltip-timeline {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a1840;
            border: 1px solid #252345;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 100;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .timeline-bar:hover .tooltip-timeline {
            display: block;
        }

        /* Responsive table */
        @media (max-width: 768px) {
            .tabla-responsive thead { display: none; }
            .tabla-responsive tbody tr {
                display: block;
                padding: 12px;
                margin-bottom: 8px;
                border: 1px solid #252345;
                border-radius: 12px;
                background: #161430;
            }
            .tabla-responsive tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 6px 0;
                border: none;
            }
            .tabla-responsive tbody td::before {
                content: attr(data-label);
                font-size: 10px;
                text-transform: uppercase;
                color: #6b7280;
                font-weight: 700;
                letter-spacing: 0.05em;
                margin-right: 8px;
            }
            .tabla-responsive tbody td:first-child::before { content: ''; }
        }
    </style>
</head>
<body class="overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">
        
        <!-- Overlay para móvil cuando el menú está abierto -->
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <!-- Sidebar - responsive -->
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
                $tituloPagina = "Periodización ATR";
                $tituloPaginaResponsive = "Periodización";
                $iconModulo = "fas fa-project-diagram";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#161430] p-6 rounded-2xl border border-[#252345]">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-project-diagram text-indigo-500"></i> Planificación ATR
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Acumulación / Transmutación / Realización por macrociclo.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'editar')): ?>
                    <button onclick="abrirModalMacro()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Crear Macrociclo
                    </button>
                    <?php endif; ?>
                </div>

                <div class="tarjeta p-5">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative w-full sm:w-72">
                            <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                            <input type="text" id="busquedaMacro" onkeyup="filtrarTablaMacro()" placeholder="Buscar por nombre..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
                        </div>
                        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-end">
                            <select id="filtroTemporada" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs">
                                <option value="">Todas las Temporadas</option>
                            </select>
                            <select id="filtroGrupo" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs">
                                <option value="">Todos los Grupos</option>
                            </select>
                            <select id="filtroEstado" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs">
                                <option value="">Todos los Estados</option>
                                <option value="Planificado">Planificado</option>
                                <option value="En Progreso">En Progreso</option>
                                <option value="Finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tarjeta overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse tabla-responsive">
                            <thead>
                                <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                                    <th class="p-4">Macrociclo</th>
                                    <th class="p-4">Temporada</th>
                                    <th class="p-4">Grupo</th>
                                    <th class="p-4">Evento Objetivo</th>
                                    <th class="p-4">Fechas</th>
                                    <th class="p-4 text-center">Semanas</th>
                                    <th class="p-4">Fase Actual</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMacro" class="divide-y divide-[#252345] text-sm text-gray-300">
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL CREAR/EDITAR MACROCICLO -->
    <div id="modalMacro" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalMacroTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-project-diagram text-emerald-400"></i> Crear Macrociclo
                </h3>
                <button onclick="cerrarModalMacro()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formMacro" autocomplete="off">
                <input type="hidden" id="id_macrociclo" name="id_macrociclo" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre del Macrociclo</label>
                        <input type="text" id="nombre" name="nombre" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Preparacion Nacional 2026">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Temporada *</label>
                        <select id="id_temporada" name="id_temporada" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Grupo *</label>
                        <select id="id_grupo" name="id_grupo" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Fin *</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Evento Objetivo (Competencia Principal)</label>
                        <select id="id_evento_objetivo" name="id_evento_objetivo" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Sin evento objetivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalMacro()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR MACROCICLO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETALLE -->
    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
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
            ver: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'ver') ? 'true' : 'false'; ?>,
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'editar') ? 'true' : 'false'; ?>,
            generar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'generar') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/periodizacion.js"></script>
</body>
</html>
