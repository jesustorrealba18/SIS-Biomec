<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SisBiomec - Gestión de Sesiones de Entrenamiento</title>
    </head>
<body class="bg-[#0f0d23] text-gray-200 font-sans">

<div class="container mx-auto p-4 md:p-6 space-y-6">
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/10 pb-5">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Sesiones de Entrenamiento</h1>
            <p class="text-sm text-gray-400 mt-1">Planificación metodológica y control biomecánico del rendimiento.</p>
        </div>
        <button onclick="abrirModalSesion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-600/20 transition flex items-center gap-2 self-start md:self-auto">
            <i class="fas fa-plus text-xs"></i> Planificar Sesión
        </button>
    </div>

    <div class="bg-[#161430] border border-white/5 p-4 rounded-2xl flex flex-wrap gap-4 items-center">
        <div class="flex items-center gap-2">
            <i class="fas fa-filter text-indigo-400 text-sm"></i>
            <span class="text-xs font-bold uppercase text-gray-400 tracking-wider">Filtrar por:</span>
        </div>
        <select id="filtroGrupo" onchange="cargarTablaSesiones()" class="bg-[#0f0d23] border border-white/10 rounded-xl px-3 py-1.5 text-xs text-gray-300 focus:outline-none focus:border-indigo-500"></select>
        <select id="filtroTipoSesion" onchange="cargarTablaSesiones()" class="bg-[#0f0d23] border border-white/10 rounded-xl px-3 py-1.5 text-xs text-gray-300 focus:outline-none focus:border-indigo-500">
            <option value="">Todos los Tipos</option>
            <option value="Tecnica">Técnica</option>
            <option value="Resistencia">Resistencia</option>
            <option value="Velocidad">Velocidad</option>
            <option value="Recuperacion">Recuperación</option>
            <option value="Fuerza">Fuerza</option>
            <option value="Flexibilidad">Flexibilidad</option>
            <option value="Competencia">Competencia</option>
        </select>
    </div>

    <div class="bg-[#161430] border border-white/5 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/10 bg-[#121026] text-xs font-bold uppercase text-gray-400 tracking-wider">
                        <th class="p-4">Fecha / Duración</th>
                        <th class="p-4">Grupo / Ciclo</th>
                        <th class="p-4">Tipo / Estado</th>
                        <th class="p-4 text-center">Vol. Planificado</th>
                        <th class="p-4 text-center">Vol. Ejecutado</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodySesiones" class="divide-y divide-white/5 text-sm">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalSesion" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-40 p-4">
    <div class="bg-[#15132d] border border-white/10 rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto shadow-2xl transition-all transform scale-95 opacity-0 duration-300">
        
        <div class="p-6 border-b border-white/10 flex items-center justify-between bg-[#121026]">
            <h3 id="modalSesionTitulo" class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-calendar-plus text-indigo-400"></i> Planificar Sesión de Entrenamiento
            </h3>
            <button onclick="cerrarModalSesion()" class="text-gray-400 hover:text-white transition"><i class="fas fa-times text-lg"></i></button>
        </div>

        <form id="formSesion" class="p-6 space-y-6">
            <input type="hidden" id="id_sesion" name="id_sesion">
            <input type="hidden" id="volumen_planificado" name="volumen_planificado" value="0">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Grupo de Entrenamiento *</label>
                    <select id="id_grupo" name="id_grupo" required class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"></select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Microciclo Vinculado</label>
                    <select id="id_microciclo" name="id_microciclo" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"></select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Fecha de Ejecución *</label>
                    <input type="date" id="fecha" name="fecha" required class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Tipo de Sesión *</label>
                    <select id="tipo_sesion" name="tipo_sesion" required class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="Tecnica">Técnica (Metodológica)</option>
                        <option value="Resistencia">Resistencia</option>
                        <option value="Velocidad">Velocidad</option>
                        <option value="Recuperacion">Recuperación</option>
                        <option value="Fuerza">Fuerza</option>
                        <option value="Flexibilidad">Flexibilidad</option>
                        <option value="Competencia">Competencia</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Descripción de Calentamiento</label>
                    <textarea id="calentamiento" name="calentamiento" placeholder="Ej: Movilidad articular fuera del agua, 200m nado libre suave..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white h-24 focus:outline-none focus:border-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Descripción de Vuelta a la Calma</label>
                    <textarea id="vuelta_calma" name="vuelta_calma" placeholder="Ej: 100m afloje espalda, estiramientos pasivos fuera del agua..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white h-24 focus:outline-none focus:border-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Observaciones Generales</label>
                    <textarea id="observaciones" name="observaciones" placeholder="Indicaciones libres de control o asistencia..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white h-24 focus:outline-none focus:border-indigo-500"></textarea>
                </div>
            </div>

            <div class="w-full md:w-1/4">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Duración Estimada (Minutos)</label>
                <input type="number" id="duracion_minutos" name="duracion_minutos" min="1" placeholder="90" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500">
            </div>

            <div class="border-t border-white/10 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-wider"><i class="fas fa-stream mr-1"></i> Composición Estructural de Series</h4>
                    <button type="button" onclick="agregarFilaSerie()" class="bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                        <i class="fas fa-plus"></i> Añadir Serie
                    </button>
                </div>

                <div class="bg-[#0f0d23] rounded-xl overflow-hidden border border-white/5">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-[#121026] text-gray-400 font-bold uppercase tracking-wider border-b border-white/5">
                                <th class="p-3 w-1/6">Bloque *</th>
                                <th class="p-3 w-1/4">Ejercicio / Drill *</th>
                                <th class="p-3 w-1/6">Ritmo Obj.</th>
                                <th class="p-3 text-center">Rep. *</th>
                                <th class="p-3 text-center">Dist.(m) *</th>
                                <th class="p-3 text-center">Pausa(s) *</th>
                                <th class="p-3">Zona *</th>
                                <th class="p-3 text-center">Volumen</th>
                                <th class="p-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodySeries" class="divide-y divide-white/5">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-[#110f27] border border-white/5 p-4 rounded-xl text-center">
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Vol. Calentamiento</p>
                    <p id="lblVolCalentamiento" class="text-sm font-mono font-bold text-gray-400">0m</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Vol. Principal</p>
                    <p id="lblVolPrincipal" class="text-sm font-mono font-bold text-indigo-400">0m</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Vol. Vuelta Calma</p>
                    <p id="lblVolVueltaCalma" class="text-sm font-mono font-bold text-gray-400">0m</p>
                </div>
                <div class="border-l border-white/10 bg-indigo-600/10 rounded-lg p-1">
                    <p class="text-[10px] uppercase font-bold text-indigo-400">Volumen Total Global</p>
                    <p id="lblVolTotalPlanificado" class="text-base font-mono font-bold text-indigo-300">0m</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/10 font-bold uppercase tracking-wider text-xs">
                <button type="button" onclick="cerrarModalSesion()" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition shadow-lg shadow-indigo-600/20">Guardar Planificación</button>
            </div>
        </form>
    </div>
</div>

<div id="modalVer" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-40 p-4">
    <div class="bg-[#15132d] border border-white/10 p-6 rounded-2xl w-full max-w-3xl shadow-2xl relative max-h-[85vh] overflow-y-auto">
        <button onclick="cerrarModalVer()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
        <div id="detalleContenido">
            </div>
    </div>
</div>

<div id="modalCompletar" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-[#15132d] border border-white/10 p-6 rounded-2xl w-full max-w-md shadow-2xl transition-all transform scale-95 opacity-0 duration-300">
        <h3 class="text-xl font-bold text-white mb-4 uppercase tracking-wide flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-400"></i> Completar Sesión
        </h3>
        
        <div class="bg-black/20 p-3 rounded-xl mb-4 space-y-1.5 text-xs text-gray-400 border border-white/5">
            <p><strong>Fecha de Sesión:</strong> <span id="compFecha" class="text-white font-medium"></span></p>
            <p><strong>Tipo Metodológico:</strong> <span id="compTipo" class="text-white font-medium"></span></p>
            <p><strong>Grupo Asignado:</strong> <span id="compGrupo" class="text-indigo-300 font-medium"></span></p>
            <p><strong>Volumen Planificado Inicial:</strong> <span id="compVolPlanificado" class="font-mono text-amber-400 font-bold"></span></p>
        </div>

        <form id="formCompletar">
            <input type="hidden" id="id_sesion_completar" name="id_sesion_completar">

            <div class="mb-3">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Volumen Real Ejecutado (Metros) *</label>
                <input type="number" id="volumen_ejecutado" name="volumen_ejecutado" required min="0" placeholder="Ej: 3200" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white font-mono focus:outline-none focus:border-emerald-500">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Observaciones de Ejecución / Bitácora</label>
                <textarea id="observaciones_completar" name="observaciones_completar" placeholder="Escriba comentarios sobre el rendimiento físico del lote..." class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white h-20 focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Estado de Cierre *</label>
                <select id="estado_completar" name="estado_completar" class="w-full bg-[#0f0d23] border border-white/10 rounded-xl p-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                    <option value="Completada">Completada (Totalidad de series cubiertas)</option>
                    <option value="Parcial">Parcial (Sesión incompleta / recortada)</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 text-xs font-bold uppercase tracking-wider">
                <button type="button" onclick="cerrarModalCompletar()" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white transition">Cancelar</button>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-white transition shadow-lg shadow-emerald-600/20">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/sesion.js"></script>
</body>
</html>