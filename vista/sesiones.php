<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesiones de Entrenamiento | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-swimming-pool text-indigo-500"></i> Planificación de Sesiones 
            </h1>
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
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <p class="text-sm text-gray-400 mt-1">Diseño de entrenamientos</p>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'crear')): ?>
            <button onclick="abrirModalSesion()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR ENTRENAMIENTO
            </button>
            <?php endif; ?>
        </div>

        <div class="tarjeta p-4 flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-white">
                <i class="fas fa-filter text-gray-500"></i>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Filtros de Búsqueda</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <select id="filtroGrupo" onchange="cargarTablaSesiones()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23] w-full md:w-48">
                    <option value="">Todos los Grupos</option>
                </select>
                <select id="filtroTipoSesion" onchange="cargarTablaSesiones()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23] w-full md:w-44">
                    <option value="">Todos los Estados</option>
                    <option value="Planificada">Planificada</option>
                    <option value="Completada">Completada</option>
                    <option value="Parcial">Parcial</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Fecha / Info</th>
                            <th class="p-4">Grupo / Planificación</th>
                            <th class="p-4">Tipo de Sesión / Estado</th>
                            <th class="p-4 text-center">Vol. Planificado</th>
                            <th class="p-4 text-center">Vol. Ejecutado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySesiones" class="divide-y divide-[#252345] text-sm text-gray-300"></tbody>
                </table>
            </div>
        </div>

    </main>

    <div id="modalSesion" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-6xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalSesionTitulo" class="text-lg font-bold text-white flex items-center gap-2"></h3>
                <button onclick="cerrarModalSesion()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="formSesion" autocomplete="off"> 
                <input type="hidden" id="id_sesion" name="id_sesion" value="">
                <input type="hidden" id="id_fase_actual" name="id_fase_actual" value="">
                <input type="hidden" id="volumen_planificado" name="volumen_planificado" value="0">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Grupo de Entrenamiento *</label>
                        <select id="id_grupo" name="id_grupo" required class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]"></select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Microciclo Vinculado</label>
                        <select id="id_microciclo" name="id_microciclo" class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]"></select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha Planificada *</label>
                        <input type="date" id="fecha" name="fecha" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Sesión *</label>
                        <select id="tipo_sesion" name="tipo_sesion" required class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]">
                            <option value="Tecnica">Técnica</option>
                            <option value="Resistencia">Resistencia</option>
                            <option value="Velocidad">Velocidad</option>
                            <option value="Recuperacion">Recuperación</option>
                            <option value="Fuerza">Fuerza</option>
                            <option value="Flexibilidad">Flexibilidad</option>
                            <option value="Competencia">Competencia</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="md:col-span-1">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Duración (Minutos)</label>
                        <input type="number" id="duracion_minutos" name="duracion_minutos" min="1" placeholder="Ej: 90" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Indicación de Calentamiento General</label>
                        <textarea id="calentamiento" name="calentamiento" rows="2" placeholder="Ej: 200m Libre..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Indicación de Vuelta a la Calma General</label>
                        <textarea id="vuelta_calma" name="vuelta_calma" rows="2" placeholder="Ej: 100m Afloje..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 text-center">
                    <div class="bg-black/20 p-2 rounded-xl border border-[#252345]">
                        <p class="text-[10px] text-gray-500 uppercase font-bold">Calentamiento</p>
                        <p id="lblVolCalentamiento" class="text-sm font-bold text-gray-300 font-mono">0m</p>
                    </div>
                    <div class="bg-black/20 p-2 rounded-xl border border-[#252345]">
                        <p class="text-[10px] text-gray-500 uppercase font-bold">Bloque Principal</p>
                        <p id="lblVolPrincipal" class="text-sm font-bold text-indigo-400 font-mono">0m</p>
                    </div>
                    <div class="bg-black/20 p-2 rounded-xl border border-[#252345]">
                        <p class="text-[10px] text-gray-500 uppercase font-bold">Vuelta a la Calma</p>
                        <p id="lblVolVueltaCalma" class="text-sm font-bold text-emerald-400 font-mono">0m</p>
                    </div>
                    <div class="bg-indigo-600/10 p-2 rounded-xl border border-indigo-500/30">
                        <p class="text-[10px] text-indigo-400 uppercase font-bold">Volumen Total</p>
                        <p id="lblVolTotalPlanificado" class="text-base font-bold text-white font-mono">0m</p>
                    </div>
                </div>

                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs uppercase text-indigo-400 font-bold tracking-widest">
                            <i class="fas fa-list-ol mr-1"></i> Series Planificadas
                        </h4>
                        <button type="button" onclick="agregarFilaSerie()" class="text-xs bg-indigo-500/20 text-indigo-400 hover:bg-indigo-500/30 px-3 py-1.5 rounded-lg transition cursor-pointer font-bold flex items-center gap-1">
                            <i class="fas fa-plus"></i> Añadir Serie
                        </button>
                    </div>

                    <div class="hidden md:grid grid-cols-9 gap-2 px-3 mb-2 text-[10px] uppercase font-bold text-gray-500">
                        <div>Bloque</div>
                        <div class="col-span-2">Drill / Catálogo y Desc.</div>
                        <div>Ritmo Objetivo</div>
                        <div class="text-center">Rep.</div>
                        <div class="text-center">Metros</div>
                        <div class="text-center">Descanso (s)</div>
                        <div>Intensidad</div>
                        <div class="text-right">Subtotal / Borrar</div>
                    </div>

                    <table class="w-full"><tbody id="tbodySeries" class="space-y-2"></tbody></table>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones Generales de Planificación</label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Indicaciones logísticas o notas para el grupo..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalSesion()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR PLANIFICACIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white transition cursor-pointer p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleContenido" class="mt-2"></div>
            <div class="mt-6 flex justify-end">
                <button type="button" onclick="cerrarModalVer()" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition">Cerrar Ventana</button>
            </div>
        </div>
    </div>

    <div id="modalCompletar" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 p-6 md:p-8">
            <div class="flex justify-between items-center mb-4 border-b border-gray-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-400"></i> Cierre de Sesión de Entrenamiento
                </h3>
                <button onclick="cerrarModalCompletar()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form id="formCompletar" autocomplete="off"> 
                <input type="hidden" id="id_sesion_completar" name="id_sesion" value="">

                <div class="bg-black/20 border border-[#252345] p-3 rounded-xl mb-4 space-y-1.5 text-xs">
                    <p class="text-gray-400"><strong>Grupo:</strong> <span id="compGrupo" class="text-white"></span></p>
                    <p class="text-gray-400"><strong>Fecha:</strong> <span id="compFecha" class="text-white font-mono"></span> | <strong>Tipo:</strong> <span id="compTipo" class="text-blue-400"></span></p>
                    <p class="text-indigo-400 font-semibold"><strong>Volumen Planificado:</strong> <span id="compVolPlanificado"></span></p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Volumen Real Ejecutado (Metros) *</label>
                    <input type="number" id="volumen_ejecutado" name="volumen_ejecutado" required min="0" class="w-full input-dark p-3 rounded-xl text-sm font-mono text-emerald-400 font-bold" placeholder="Ej: 3200">
                    <p class="text-[10px] text-gray-500 mt-1">Modifica la marca si el volumen final varió respecto a lo planificado.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones de Ejecución / Rendimiento</label>
                    <textarea id="observaciones_completar" name="observaciones" rows="3" placeholder="Ej: Excelentes ritmos..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalCompletar()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3 rounded-xl font-bold transition text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-green-600 hover:bg-green-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-green-500/20 text-xs tracking-wider uppercase">
                        CERRAR ENTRENAMIENTO <i class="fas fa-lock ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'ver') ? 'true' : 'false'; ?>,
        };
    </script>
  <script>
    const API_URL = '?p=sesiones';
</script>
<script src="assets/js/sesion.js"></script>
</body>
</html>