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
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

   <main class="flex-1 p-8 overflow-y-auto">

           <header class="flex justify-between items-center mb-20">
             
                <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                    <i class="fas fa-stopwatch text-indigo-500"></i> Marcas y Tiempos
                </h1>
                
            <div class="flex items-center gap-6">

                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>

                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>

                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

         <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
             <div>
                <!-- <h5 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                    <i class="fas fa-stopwatch text-indigo-500"></i> Rendimiento: Captura de Marcas
                </h5> -->
                <p class="text-sm text-gray-400 mt-1">Historial de tiempos competitivos, controles técnicos y desgloses de parciales (Splits).</p>
            </div>
            <button onclick="abrirModalMarca()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR TIEMPO
            </button>
         </div>
        
<!--         <div class="max-w-7xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                    <i class="fas fa-stopwatch text-indigo-500"></i> Rendimiento: Captura de Marcas
                </h1>
                <p class="text-sm text-gray-400 mt-1">Historial de tiempos competitivos, controles técnicos y desgloses de parciales (Splits).</p>
            </div>
            <button onclick="abrirModalMarca()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR TIEMPO
            </button>
        </div> -->

        <div class="tarjeta p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                <input type="text" id="busquedaAtleta" onkeyup="filtrarTabla()" placeholder="Buscar por atleta o cédula..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <select id="filtroEstilo" onchange="filtrarTabla()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">🏊 Todos los Estilos</option>
                    <option value="Libre">Libre</option>
                    <option value="Espalda">Espalda</option>
                    <option value="Braza">Braza</option>
                    <option value="Mariposa">Mariposa</option>
                    <option value="Combinado">Combinado</option>
                </select>

                <select id="filtroPiscina" onchange="filtrarTabla()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">🏢 Todas las Piscinas</option>
                    <option value="25m">Piscina Corta (25m)</option>
                    <option value="50m">Piscina Olímpica (50m)</option>
                </select>

                <select id="filtroEstado" onchange="cargarTablaMarcas()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="Activo" selected>⏱️ Marcas Vigentes</option>
                    <option value="Inactivo">🗑️ Marcas Archivadas</option>
                </select>
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

    <div id="modalMarca" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
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
                <input type="hidden" id="id_marca_original" name="id_marca_original" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<!--                     <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <select id="id_atleta" name="id_atleta" required class="w-full input-dark p-3 rounded-xl text-sm"></select>
                    </div> -->

                    <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta">
                        
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm" autocomplete="off" required>
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden transition-all">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]">
                                </ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha del Registro *</label>
                        <input type="date" id="fecha" name="fecha" max="<?php echo date('Y-m-d'); ?>" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Estilo *</label>
                        <select id="estilo" name="estilo" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Libre">Libre (Crawl)</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Braza">Braza (Pecho)</option>
                            <option value="Mariposa">Mariposa</option>
                            <option value="Combinado">Combinado (Medley)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Distancia Total *</label>
                        <select id="distancia_m" name="distancia_m" required class="w-full input-dark p-3 rounded-xl text-sm">
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
                        <select id="tipo_piscina" name="tipo_piscina" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="50m">Olímpica (50 metros)</option>
                            <option value="25m">Corta (25 metros)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nivel / Contexto *</label>
                        <select id="nivel_evento" name="nivel_evento" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Control">Control Técnico Interno</option>
                            <option value="Regional">Gala Regional FEVEDA</option>
                            <option value="Nacional">Campeonato Nacional</option>
                            <option value="Internacional">Cita Internacional</option>
                        </select>
                    </div>

                    <div>
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
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 p-4 bg-black/20 rounded-xl border border-white/5">
                    <div>
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Reacción (s)</label>
                        <input type="number" step="0.01" id="tiempo_reaccion_seg" name="tiempo_reaccion_seg" placeholder="0.00" class="w-full input-dark p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">Viraje (s)</label>
                        <input type="number" step="0.01" id="tiempo_viraje_seg" name="tiempo_viraje_seg" placeholder="0.00" class="w-full input-dark p-2 rounded-lg text-sm text-center font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1" title="Para calcular SWOLF">Brazadas/Largo</label>
                        <input type="number" id="brazadas_por_largo" name="brazadas_por_largo" placeholder="Ej: 16" class="w-full bg-[#161430] border border-amber-500/50 text-white p-2 rounded-lg text-sm text-center font-mono focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] text-indigo-400 uppercase font-bold mb-1">Tiempo Final *</label>
                        <input type="text" id="tiempo_final_humano" placeholder="MM:SS.cc" required class="w-full bg-[#161430] border border-indigo-500 text-white font-mono text-sm rounded-lg p-2 text-center focus:ring-2 focus:ring-indigo-500 font-bold">
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
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Detalles sobre las condiciones de nado, descalificaciones o comentarios del entrenador..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
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
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/marcas.js"></script>
</body>
</html>