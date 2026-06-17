<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Marcas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

   <main class="flex-1 p-8 overflow-y-auto">

  <!-- Header común -->
            <?php 
                $tituloPagina = 'Gestión de Marcas y Tiempos';
                include RAIZ . 'vista/complementos/header.php'; 
            ?>


         <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4 mt-6">
             <div>
                <p class="text-sm text-gray-400 mt-1">Historial de tiempos competitivos, controles técnicos y desgloses de parciales (Splits).</p>
            </div>
             <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar')): ?>
             <button onclick="abrirModalMarca()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                 <i class="fas fa-plus"></i> REGISTRAR TIEMPO
             </button>
             <?php endif; ?>
         </div>
        


<div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20">
    
    <div class="flex items-center gap-2 border-b border-[#252345] pb-2">
        <i class="fas fa-filter text-indigo-400 text-sm"></i>
        <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Búsqueda</h3>
    </div>

    <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fas fa-user-circle text-gray-400 text-lg"></i>
        </div>
        
        <select id="filtroAtleta" onchange="cargarTablaMarcas()" class="w-full input-dark pl-12 pr-10 py-3 rounded-xl text-sm bg-[#0f0d23] border border-[#252345] hover:border-indigo-500/50 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer appearance-none shadow-inner">
            <option value="">👤 Todos los Atletas (Búsqueda General)</option>
        </select>
        
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full">
        
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-ruler-horizontal text-emerald-400/70 group-hover:text-emerald-400 transition-colors text-xs"></i>
            </div>
            <select id="filtroDistancia" onchange="cargarTablaMarcas()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] hover:border-emerald-500/50 focus:border-emerald-500 transition-all cursor-pointer appearance-none">
                <option value="">📏 Todas las Distancias</option>
                <option value="25">25m</option>
                <option value="50">50m</option>
                <option value="100">100m</option>
                <option value="200">200m</option>
                <option value="400">400m</option>
                <option value="800">800m</option>
                <option value="1500">1500m</option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
            </div>
        </div>

        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-swimmer text-cyan-400/70 group-hover:text-cyan-400 transition-colors text-xs"></i>
            </div>
            <select id="filtroEstilo" onchange="cargarTablaMarcas()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] hover:border-cyan-500/50 focus:border-cyan-500 transition-all cursor-pointer appearance-none">
                <option value="">🏊 Todos los Estilos</option>
                <option value="Libre">Libre</option>
                <option value="Espalda">Espalda</option>
                <option value="Pecho">Pecho</option>
                <option value="Mariposa">Mariposa</option>
                <option value="Combinado">Combinado</option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
            </div>
        </div>

        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-water text-blue-400/70 group-hover:text-blue-400 transition-colors text-xs"></i>
            </div>
            <select id="filtroPiscina" onchange="cargarTablaMarcas()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] hover:border-blue-500/50 focus:border-blue-500 transition-all cursor-pointer appearance-none">
                <option value="">🏢 Todas las Piscinas</option>
                <option value="25m">Piscina Corta (25m)</option>
                <option value="50m">Piscina Olímpica (50m)</option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
            </div>
        </div>

        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-history text-amber-400/70 group-hover:text-amber-400 transition-colors text-xs"></i>
            </div>
            <select id="filtroEstado" onchange="cargarTablaMarcas()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] hover:border-amber-500/50 focus:border-amber-500 transition-all cursor-pointer appearance-none">
                <option value="Activo" selected>⏱️ Marcas Vigentes</option>
                <option value="Inactivo">🗑️ Marcas Archivadas</option>
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
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Prueba / Estilo</th>
                            <th class="p-4">Piscina</th>
                            <th class="p-4">Tiempo Final</th>
                            <th class="p-4">Origen / Nivel</th>
                            <th class="p-4">Fecha</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMarcas" class="divide-y divide-[#252345] text-sm text-gray-300">
                        </tbody>
                </table>
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


<!--                     <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta Seleccionado">
                        
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm" autocomplete="off" maxlength="40" required>
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden transition-all">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]">
                                </ul>
                        </div>
                    </div> -->
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

                   <!--  <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nivel / Contexto *</label>
                        <select id="nivel_evento" name="nivel_evento" data-validar="requerido" data-nombre="Nivel del Evento" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Control">Control Técnico Interno</option>
                            <option value="Regional">Gala Regional FEVEDA</option>
                            <option value="Nacional">Campeonato Nacional</option>
                            <option value="Internacional">Cita Internacional</option>
                        </select>
                    </div> -->

                   <!--  <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Sesión de Entrenamiento (Opcional)</label>
                        <select id="id_sesion" name="id_sesion" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Ninguna - No aplica</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Evento Competitivo (Opcional)</label>
                        <select id="id_evento" name="id_evento" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Ninguno - No aplica</option>
                        </select>
                    </div> -->
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
                               placeholder="0.00" 
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
                               placeholder="0.00" 
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

    
    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/marcas.js"></script>
</body>
</html>