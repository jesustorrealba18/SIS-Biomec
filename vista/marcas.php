<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control de Marcas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
        .input-dark::-webkit-calendar-picker-indicator {filter: invert(1);  /* convierte el icono negro en blanco */ }
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

          /* ---------------------------------------------------
           AJUSTES DATATABLES DARK THEME (Tailwind override)
           --------------------------------------------------- */
 /* =========================================================
   DATATABLES DARK THEME & RESPONSIVE NATIVO
   ========================================================= */
table.dataTable {
    border-collapse: collapse !important;
    width: 100% !important;
    margin: 1rem 0 !important;
}

/* Cabeceras y controles - Matando el fondo gris */
table.dataTable thead th,
table.dataTable.no-footer {
    border-bottom: 1px solid #252345 !important;
}
table.dataTable thead th {
    background-color: #0f0d23 !important;
    background-image: none !important;
    color: #94a3b8 !important;
    padding: 12px 16px !important;
}
table.dataTable tbody tr {
    background-color: transparent !important;
}
table.dataTable tbody td {
    border-bottom: 1px solid #252345 !important;
}

/* Botones de Paginación (igual que en Representantes) */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background: #161430 !important;
    border: 1px solid #252345 !important;
    border-radius: 0.5rem !important;
    color: #a0a0c0 !important;
    padding: 0.4rem 0.8rem !important;
    margin: 0 0.2rem !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #4f46e5 !important;
    color: white !important;
    border-color: #4f46e5 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #252345 !important;
    color: white !important;
}

/* Buscador y Selector de registros */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    background: #0f0d23 !important;
    border: 1px solid #252345 !important;
    color: white !important;
    border-radius: 0.75rem !important;
    padding: 0.6rem 1rem !important;
    outline: none !important;
}
.dataTables_wrapper .dataTables_filter input {
    padding-left: 2.5rem !important; /* espacio para el ícono */
}
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    gap: 0.75rem;
    align-items: center;
}

/* =========================================================
   RESTAURAR ÍCONO RESPONSIVE (el + verde nativo)
   ========================================================= */

/* El controlador de responsive */
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
    position: relative;
    cursor: pointer;
}

/* Estilo para el ícono + (cuando está colapsado) */
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
    content: "+";
    display: inline-block;
    color: white;
    background-color: #22c55e; /* verde */
    border: 2px solid white;
    border-radius: 50%;
    box-shadow: 0 0 3px rgba(0,0,0,0.3);
    box-sizing: content-box;
    text-align: center;
    text-indent: 0 !important;
    font-family: 'Courier New', Courier, monospace;
    line-height: 1.4;
    width: 1.2rem;
    height: 1.2rem;
    font-size: 1rem;
    font-weight: bold;
    margin-right: 0.5rem;
    transition: transform 0.15s ease;
}

/* Cuando está expandido (muestra -) */
table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
    content: "-";
    background-color: #d33;
    transform: rotate(0deg);
}
        
        </style>
</head>
<body class="overflow-x-hidden">

<?php
// Recargar permisos del usuario actual en cada acceso
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
                $tituloPagina = "Gestión de Marcas Técnicas";
                $tituloPaginaResponsive = "Marcas";
                $iconModulo = "fas fa-stopwatch";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#161430] p-6 rounded-2xl border border-[#252345]">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-stopwatch text-indigo-500"></i> Repositorio de Marcas Analíticas
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Gestión avanzada de tiempos de carrera, ritmos de caída y progresión biométrica.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar')): ?>
                    <button onclick="abrirModalMarca()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Nueva Marca
                    </button>
                    <?php endif; ?>
                </div>

                <div class="tarjeta p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-toggle-on text-indigo-400"></i> Estado del Registro
                            </label>
                            <select id="filtroEstado" onchange="cargarTablaMarcas()" class="w-full input-dark rounded-xl p-3 text-sm cursor-pointer">
                                <option value="Activo">Registros Activos</option>
                                <option value="Inactivo">Registros Archivados</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-user text-indigo-400"></i> Filtrar Nadador
                            </label>
                            <select id="filtroAtleta" onchange="cargarTablaMarcas()" class="w-full input-dark rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todos los Atletas</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-water text-indigo-400"></i> Estilo Técnico
                            </label>
                            <select id="filtroEstilo" onchange="cargarTablaMarcas()" class="w-full input-dark rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todos los Estilos</option>
                                <option value="Libre">Libre (Crawl)</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Pecho">Pecho (Braza)</option>
                                <option value="Mariposa">Mariposa</option>
                                <option value="Combinado">Combinado (Medley)</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-ruler-horizontal text-indigo-400"></i> Distancia (Metros)
                            </label>
                            <select id="filtroDistancia" onchange="cargarTablaMarcas()" class="w-full input-dark rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Todas las Distancias</option>
                                <option value="50">50 metros</option>
                                <option value="100">100 metros</option>
                                <option value="200">200 metros</option>
                                <option value="400">400 metros</option>
                                <option value="800">800 metros</option>
                                <option value="1500">1500 metros</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] uppercase tracking-wider text-gray-400 font-bold flex items-center gap-1">
                                <i class="fas fa-swimming-pool text-indigo-400"></i> Tipo de Alberca
                            </label>
                            <select id="filtroPiscina" onchange="cargarTablaMarcas()" class="w-full input-dark rounded-xl p-3 text-sm cursor-pointer">
                                <option value="">Cualquier Longitud</option>
                                <option value="25m">Piscina Corta (25m)</option>
                                <option value="50m">Piscina Olímpica (50m)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tarjeta p-4 sm:p-6 overflow-hidden">
                    <table id="tablaMarcas" class=" display" style="width:100%">
                        <thead>
                            <tr>
                                <th >Atleta / Cédula</th>
                                <th >Prueba Especialidad</th>
                                <th >Dimensión Piscina</th>
                                <th >Tiempo Oficial</th>
                                <th >Origen / Nivel</th>
                                <th >Fecha</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMarcas">
                            </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>


</main>


    <!-- Empiezan los modales -->

    <!-- <div id="modalMarca" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300"> -->
    <div id="modalMarca" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">   
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-stopwatch text-emerald-400"></i> Registrar Control de Tiempo
                </h3>
                <button onclick="cerrarModalMarca()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

         <form id="formMarca" autocomplete="off">
                <input type="hidden" id="accion_form" name="accion" value="registrar">
                
                <input type="hidden" id="id_marca" name="id_marca" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="block text-xs text-indigo-300 uppercase font-bold mb-2">Contexto de la Marca *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Entrenamiento (Sesión)</label>
                                <select id="id_sesion" name="id_sesion" class="w-full input-dark p-3 rounded-xl text-sm transition-all">
                                    <option value="">Ninguna - No aplica</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Competencia (Evento)</label>
                                <select id="id_evento" name="id_evento" class="w-full input-dark p-3 rounded-xl text-sm transition-all">
                                    <option value="">Ninguno - No aplica</option>
                                </select>
                            </div>
                        </div>
                    </div>      
                    
                    <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta Participante *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta Seleccionado">
                        
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" disabled placeholder="Seleccione sesión o evento primero..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all" autocomplete="off" maxlength="40" required>
                            
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden transition-all">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]"></ul>
                        </div>
                    </div>


                            <?php
                            // Define tu zona horaria local (ejemplo: America/New_York)
                            date_default_timezone_set('America/Caracas'); 
                            ?>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha del Registro *</label>
                        <input type="date" id="fecha" name="fecha"  max="<?php echo date('Y-m-d'); ?>" data-validar="requerido|fecha_reciente" required  data-nombre="Fecha" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Estilo *</label>
                        <select id="estilo" name="estilo" data-validar="requerido" data-nombre="Estilo" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Libre">Libre (Crawl)</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Braza">Braza (Pecho)</option>
                            <option value="Mariposa">Mariposa</option>
                            <option value="Combinado">Combinado (Medley)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Distancia Total *</label>
                        <select id="distancia_m" name="distancia_m" data-validar="requerido" data-nombre="Distancia" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="" disabled selected>Seleccione distancia...</option>
                            <option value="50">50 Metros</option>
                            <option value="100">100 Metros</option>
                            <option value="200">200 Metros</option>
                            <option value="400">400 Metros</option>
                            <option value="800">800 Metros</option>
                            <option value="1500">1500 Metros</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Piscina *</label>
                        <select id="tipo_piscina" name="tipo_piscina" data-validar="requerido" data-nombre="Tipo de Piscina" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="50m">Olímpica (50 metros)</option>
                            <option value="25m">Corta (25 metros)</option>
                        </select>
                    </div>

                 
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 p-4 bg-black/20 rounded-xl border border-white/5">

                    <div>
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Reacción (s)</label>
                        <input type="text" 
                               inputmode="decimal" 
                               data-validar="decimal_tiempo" data-nombre="Reacción" 
                               maxlength="5"
                               id="tiempo_reaccion_seg" 
                               name="tiempo_reaccion_seg" 
                               placeholder="00.00" 
                               class="w-full input-dark p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Viraje (s)</label>
                        <input type="text" 
                               inputmode="decimal" 
                               data-validar="decimal_tiempo" data-nombre="Viraje"
                               maxlength="5"
                               id="tiempo_viraje_seg" 
                               name="tiempo_viraje_seg" 
                               placeholder="00.00" 
                               class="w-full input-dark p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1" title="Para calcular SWOLF">Brazadas/Largo</label>
                        <input type="number" id="brazadas_por_largo" name="brazadas_por_largo" min="1" max="999" oninput="if(this.value.length > 3) this.value = this.value.slice(0,3);" data-validar="numeros" data-max="4" data-nombre="Brazadas"  placeholder="Ej: 16" class="w-full bg-[#161430] border border-amber-500/50 text-white p-2 rounded-lg text-sm text-center font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] text-indigo-400 uppercase font-bold mb-1">Tiempo Final *</label>
                        <input type="text" id="tiempo_final_humano" placeholder="MM:SS.cc" data-validar="requerido|tiempo" data-nombre="Tiempo Final" maxlength="8" class="w-full bg-[#161430] border border-indigo-500 text-white font-mono text-sm rounded-lg p-2 text-center focus:ring-2 focus:ring-indigo-500 font-bold">
                        <input type="hidden" id="tiempo_final_seg" name="tiempo_final_seg">
                    </div>
                </div>

                <div id="contenedorSplits" class="hidden mt-6 bg-black/30 p-4 rounded-2xl border border-dashed border-gray-700 transition-all">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-emerald-400 font-bold tracking-widest">
                            <i class="fas fa-chart-bar mr-2"></i>Desglose Cronometrado cada 25m
                        </p>
                        <span id="contadorSplits" class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded font-mono font-bold">0 Tramos</span>
                    </div>
                    <div id="rejillaSplits" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        </div>
                    <div id="alertaCoherencia" class="mt-3 text-right text-[11px] font-medium transition-all"></div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones Técnicas</label>
                    <textarea id="observaciones" name="observaciones" data-validar="texto" data-max="255" maxlength="255" data-nombre="Observaciones" rows="2"  placeholder="Detalles sobre las condiciones de nado, descalificaciones o comentarios del entrenador..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalMarca()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>
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
            ver: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'ver') ? 'true' : 'false'; ?>,
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'eliminar') ? 'true' : 'false'; ?>,
            restaurar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'restaurar') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/marcas.js"></script>
</body>
</html>