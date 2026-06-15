<?php
$tituloPagina = 'Control de Lesiones';
$iconoPagina = 'fa-notes-medical';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-notes-medical"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Registro Clínico de Lesiones (RF-10)</span>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaCedula" placeholder="Buscar por cédula de atleta..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <div class="flex items-center gap-2">
                    <label for="filtroEstado" class="text-xs text-gray-400 uppercase font-bold tracking-wider">Ver:</label>
                    <select id="filtroEstado" onchange="cargarTablaLesiones()" class="input-dark p-2 rounded-xl text-xs bg-[#161430] border border-gray-700 text-white">
                        <option value="Activo" selected>✅ Lesiones Activas</option>
                        <option value="Recuperado">🏥 Lesiones Recuperadas</option>
                        <option value="Inactivo">🗑️ Archivados</option>
                    </select>
                </div>
                <button onclick="abrirModalLesion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Reportar Lesión
                </button>
            </div>
        </div>

        <div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20 mb-6">
            <div class="flex items-center gap-2 border-b border-[#252345] pb-2">
                <i class="fas fa-chart-pie text-indigo-400 text-sm"></i>
                <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Resumen de Estado de Salud</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3">
                    <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-1">Sanos</p>
                    <p class="text-xl font-bold text-white" id="estadisticaSanos">0</p>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3">
                    <p class="text-[10px] text-amber-400 font-bold uppercase tracking-wider mb-1">En Recuperación</p>
                    <p class="text-xl font-bold text-white" id="estadisticaRecuperando">0</p>
                </div>
                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3">
                    <p class="text-[10px] text-red-400 font-bold uppercase tracking-wider mb-1">Lesionados</p>
                    <p class="text-xl font-bold text-white" id="estadisticaLesionados">0</p>
                </div>
                <div class="bg-gray-500/10 border border-gray-500/30 rounded-xl p-3">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Total Casos</p>
                    <p class="text-xl font-bold text-white" id="estadisticaTotal">0</p>
                </div>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaLesiones">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Tipo de Lesión</th>
                            <th class="p-4">Parte Afectada</th>
                            <th class="p-4">Fecha Reporte</th>
                            <th class="p-4">Diagnóstico</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaLesiones">
                        <tr>
                            <td colspan="7" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos clínicos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalLesion" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-3xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-medkit text-indigo-500"></i>
                    <span id="modalTitulo">Reportar Nueva Lesión</span>
                </h2>
                <button onclick="cerrarModalLesion()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formLesion" autocomplete="off">
                <input type="hidden" id="id_lesion" name="id_lesion">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Atleta *</label>
                        <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-3 py-2.5 rounded-xl text-sm">
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido">
                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl hidden">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]"></ul>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fecha del Reporte *</label>
                        <input type="date" id="fecha" name="fecha" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Tipo de Lesión *</label>
                        <select id="tipo_lesion" name="tipo_lesion" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                            <option value="">Seleccione...</option><option value="Distension">Esguince</option><option value="Contractura">Contractura</option><option value="Tendinopatía">Tendinopatía</option><option value="Luxación">Luxación</option><option value="Fractura">Fractura</option><option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Parte del Cuerpo *</label>
                        <select id="parte_cuerpo" name="parte_cuerpo" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                            <option value="">Seleccione...</option><option value="Hombro">Hombro</option><option value="Muñeca">Muñeca</option><option value="Cuello">Cuello</option><option value="Espalda">Espalda</option><option value="Rodilla">Rodilla</option><option value="Tobillo">Tobillo</option><option value="Pie">Pie</option><option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Severidad</label>
                        <select id="severidad" name="severidad" class="w-full input-dark p-2.5 rounded-xl text-sm">
                            <option value="Leve">Leve</option><option value="Moderada">Moderada</option><option value="Grave">Grave</option>
                        </select>
                    </div>
                    <div class="space-y-2 col-span-2">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Diagnóstico Médico</label>
                        <input type="text" id="diagnostico" name="diagnostico" placeholder="Descripción del diagnóstico..." class="w-full input-dark p-2.5 rounded-xl text-sm">
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Tratamiento Recomendado</label>
                    <textarea id="tratamiento" name="tratamiento" rows="2" placeholder="RPM, fisioterapia, reposo..." class="w-full input-dark p-3 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalLesion()" class="bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        REGISTRAR LESIÓN <i class="fas fa-plus ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/lesion.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>