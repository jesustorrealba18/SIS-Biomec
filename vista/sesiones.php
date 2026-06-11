<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SisBiomec - Gestión de Sesiones de Entrenamiento</title>
    </head>
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

<body class="bg-[#0f0d23] text-gray-200 font-sans">

<div class="container mx-auto p-4 md:p-6 space-y-6">
    
   <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Sesiones de Entrenamiento</h1>
        <p class="text-xs text-gray-400 mt-0.5">Planificación metodológica y control biomecánico del rendimiento.</p>
    </div>
    <div>
        <button onclick="abrirModalSesion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-lg shadow-indigo-600/10 transition cursor-pointer">
            <i class="fas fa-plus text-xs"></i> Planificar Sesión
        </button>
    </div>
</div>

<div class="bg-[#15132c] border border-white/5 rounded-2xl p-4 mb-6 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
    <div>
        <label class="block text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-1.5">Filtrar por Grupo</label>
        <select id="filtroGrupo" onchange="cargarTablaSesiones()" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none transition">
            <option value="">Todos los Grupos</option>
        </select>
    </div>
    <div>
        <label class="block text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-1.5">Tipo de Sesión</label>
        <select id="filtroTipoSesion" onchange="cargarTablaSesiones()" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none transition">
            <option value="">Todos los Tipos</option>
            <option value="Tecnica (Metodológica)">Técnica (Metodológica)</option>
            <option value="Capacidad Aerobica">Capacidad Aeróbica</option>
            <option value="Potencia Anaerobica">Potencia Anaeróbica</option>
            <option value="Velocidad">Velocidad</option>
            <option value="Recuperacion">Recuperación</option>
            <option value="Test Físico">Test Físico</option>
        </select>
    </div>
    <div class="text-right">
        <button onclick="cargarTablaSesiones()" class="w-full md:w-auto bg-[#252345] hover:bg-[#2e2c54] text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition cursor-pointer">
            <i class="fas fa-sync-alt mr-1"></i> Refrescar
        </button>
    </div>
</div>

<div class="bg-[#15132c] border border-white/5 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-black/20 text-gray-400 uppercase tracking-wider text-[10px] border-b border-white/5">
                    <th class="p-4">Fecha / Duración</th>
                    <th class="p-4">Grupo / Plan</th>
                    <th class="p-4">Tipo / Estado</th>
                    <th class="p-4 text-center">Vol. Planificado</th>
                    <th class="p-4 text-center">Vol. Ejecutado</th>
                    <th class="p-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodySesiones" class="divide-y divide-white/5 text-gray-300">
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando planificación de sesiones...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="modalSesion" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden transition-all duration-300">
    <div class="bg-[#15132c] border border-white/10 rounded-2xl w-full max-w-5xl shadow-2xl scale-95 opacity-0 transition-all duration-300 max-h-[90vh] flex flex-col">
        <div class="p-4 border-b border-white/5 flex items-center justify-between bg-black/10">
            <h2 id="modalSesionTitulo" class="text-base font-bold text-white flex items-center gap-2"></h2>
            <button type="button" onclick="cerrarModalSesion()" class="text-gray-400 hover:text-white transition cursor-pointer">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formSesion" class="flex-1 overflow-y-auto p-5 space-y-4">
            <input type="hidden" id="id_sesion" name="id_sesion">
            <input type="hidden" id="volumen_planificado" name="volumen_planificado" value="0">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Grupo de Entrenamiento *</label>
                    <select id="id_grupo" name="id_grupo" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none" required></select>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Microciclo Vinculado</label>
                    <select id="id_microciclo" name="id_microciclo" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none"></select>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Fecha de Ejecución *</label>
                    <input type="date" id="fecha" name="fecha" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white font-mono focus:border-indigo-500 focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Tipo de Sesión *</label>
                    <select id="tipo_sesion" name="tipo_sesion" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none" required>
                        <option value="Tecnica (Metodológica)">Técnica (Metodológica)</option>
                        <option value="Capacidad Aerobica">Capacidad Aeróbica</option>
                        <option value="Potencia Anaerobica">Potencia Anaeróbica</option>
                        <option value="Velocidad">Velocidad</option>
                        <option value="Recuperacion">Recuperación</option>
                        <option value="Test Físico">Test Físico</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Descripción de Calentamiento Fuera del Agua</label>
                    <textarea id="calentamiento" name="calentamiento" rows="2" placeholder="Ej: Movilidad articular, estiramientos activos..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Descripción de Vuelta a la Calma / Afloje</label>
                    <textarea id="vuelta_calma" name="vuelta_calma" rows="2" placeholder="Ej: 100m afloje espalda..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Observaciones Generales u Objetivos</label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Indicaciones libres de control..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-indigo-500 focus:outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="w-full md:w-1/4">
                <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Duración Estimada (Minutos)</label>
                <input type="number" id="duracion_minutos" name="duracion_minutos" min="1" value="90" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white font-mono focus:border-indigo-500 focus:outline-none">
            </div>

            <div class="border-t border-white/5 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Composición Estructural de Series</h3>
                    <button type="button" onclick="agregarFilaSerie()" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 px-2 py-1 rounded-lg text-[11px] font-semibold flex items-center gap-1 transition cursor-pointer">
                        <i class="fas fa-plus text-[10px]"></i> Añadir Serie
                    </button>
                </div>

                <div class="overflow-x-auto bg-black/10 rounded-xl border border-white/5">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-black/30 text-gray-400 uppercase tracking-wider text-[10px] border-b border-white/5">
                                <th class="p-2 w-32">Bloque</th>
                                <th class="p-2">Ejercicio / Drill del Catálogo</th>
                                <th class="p-2 w-24 text-center">Ritmo Obj.</th>
                                <th class="p-2 w-16 text-center">Rep.</th>
                                <th class="p-2 w-20 text-center">Dist.(m)</th>
                                <th class="p-2 w-16 text-center">Pausa(s)</th>
                                <th class="p-2 w-20">Zona</th>
                                <th class="p-2 w-20 text-center">Volumen</th>
                                <th class="p-2 w-10 text-right"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodySeries" class="divide-y divide-white/5"></tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-black/20 p-3 rounded-xl border border-white/5 text-center text-xs">
                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase block">Vol. Calentamiento</span>
                    <span id="lblVolCalentamiento" class="font-mono text-white font-bold">0m</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-indigo-400 uppercase block">Vol. Bloque Principal</span>
                    <span id="lblVolPrincipal" class="font-mono text-indigo-400 font-bold">0m</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-emerald-400 uppercase block">Vol. Vuelta Calma</span>
                    <span id="lblVolVueltaCalma" class="font-mono text-emerald-400 font-bold">0m</span>
                </div>
                <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-lg p-1">
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Carga Total Sesión</span>
                    <span id="lblVolTotalPlanificado" class="font-mono text-indigo-400 font-extrabold text-sm">0m</span>
                </div>
            </div>

            <div class="pt-2 border-t border-white/5 flex items-center justify-end gap-2 bg-black/10 -mx-5 -mb-5 p-4 rounded-b-2xl">
                <button type="button" onclick="cerrarModalSesion()" class="bg-[#252345] hover:bg-[#2e2c54] text-white px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer">Cancelar</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-lg shadow-indigo-600/20 transition cursor-pointer">Guardar Planificación</button>
            </div>
        </form>
    </div>
</div>

<div id="modalVer" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden transition-all duration-300">
    <div class="bg-[#15132c] border border-white/10 rounded-2xl w-full max-w-3xl shadow-2xl p-5 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-white/5 mb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-400"></i> Desglose Detallado de Sesión
            </h2>
            <button type="button" onclick="cerrarModalVer()" class="text-gray-400 hover:text-white transition cursor-pointer">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <div id="detalleContenido" class="flex-1 overflow-y-auto pr-1"></div>
        <div class="pt-3 border-t border-white/5 text-right mt-4">
            <button type="button" onclick="cerrarModalVer()" class="bg-[#252345] hover:bg-[#2e2c54] text-white px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer">Cerrar Ventana</button>
        </div>
    </div>
</div>

<div id="modalCompletar" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden transition-all duration-300">
    <div class="bg-[#15132c] border border-white/10 rounded-2xl w-full max-w-md shadow-2xl scale-95 opacity-0 transition-all duration-300">
        <div class="p-4 border-b border-white/5 flex items-center justify-between bg-black/10 rounded-t-2xl">
            <h2 class="text-sm font-bold text-emerald-400 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Validar Ejecución y Cerrar Sesión
            </h2>
            <button type="button" onclick="cerrarModalCompletar()" class="text-gray-400 hover:text-white transition cursor-pointer">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formCompletar" class="p-5 space-y-4">
            <input type="hidden" id="id_sesion_completar" name="id_sesion">

            <div class="bg-black/20 p-3 rounded-xl border border-white/5 space-y-1 text-xs text-gray-300">
                <p><strong>Grupo:</strong> <span id="compGrupo" class="text-white"></span></p>
                <p><strong>Fecha Planificada:</strong> <span id="compFecha" class="font-mono text-white"></span></p>
                <p><strong>Tipo de Enfoque:</strong> <span id="compTipo" class="text-indigo-400"></span></p>
                <p><strong>Volumen Inicial Previsto:</strong> <span id="compVolPlanificado" class="font-mono text-amber-400 font-bold"></span></p>
            </div>

            <div>
                <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Volumen Real Ejecutado (Metros) *</label>
                <div class="relative">
                    <input type="number" id="volumen_ejecutado" name="volumen_ejecutado" min="0" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white font-mono focus:border-emerald-500 focus:outline-none" required>
                    <span class="absolute right-3 top-2.5 text-xs text-gray-500">metros</span>
                </div>
            </div>

            <div>
                <label class="block text-[11px] text-gray-400 uppercase tracking-wider font-bold mb-1">Observaciones Finales / Feedback del Rendimiento</label>
                <textarea id="observaciones_completar" name="observaciones_ejecucion" rows="3" placeholder="Ej: Atletas mostraron fatiga en la serie principal..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2 text-xs text-white focus:border-emerald-500 focus:outline-none resize-none"></textarea>
            </div>

            <div class="pt-3 border-t border-white/5 flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalCompletar()" class="bg-[#252345] hover:bg-[#2e2c54] text-white px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer">Regresar</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-lg shadow-emerald-600/20 transition cursor-pointer">Finalizar Entrenamiento</button>
            </div>
        </form>
    </div>
</div>
      
<script src="assets/js/sesion.js"></script>
</body>
</html>