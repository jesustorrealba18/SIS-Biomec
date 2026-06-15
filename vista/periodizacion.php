<?php
$tituloPagina = 'Periodización ATR';
$iconoPagina = 'fa-project-diagram';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-project-diagram"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Planificación Anual</span>
            </div>
            <button onclick="abrirModalPeriodo()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nuevo Periodo
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaPeriodos">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Fase</th>
                            <th class="p-4">Fecha Inicio</th>
                            <th class="p-4">Fecha Fin</th>
                            <th class="p-4">Semanas</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaPeriodos">
                        <tr>
                            <td colspan="8" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando periodización...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalPeriodo" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-project-diagram text-indigo-500"></i> Registrar Periodo
                </h2>
                <button onclick="cerrarModalPeriodo()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formPeriodo" class="space-y-6">
                <input type="hidden" id="id_periodo" name="id_periodo">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido|texto" class="w-full input-dark p-3 rounded-xl">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Tipo *</label>
                            <select id="tipo" name="tipo" data-validar="requerido" class="w-full input-dark p-3 rounded-xl">
                                <option value="Preparación">Preparación</option><option value="Competencia">Competencia</option><option value="Transición">Transición</option><option value="Recuperación">Recuperación</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fase *</label>
                            <select id="fase" name="fase" data-validar="requerido" class="w-full input-dark p-3 rounded-xl">
                                <option value="General">General</option><option value="Específico">Específico</option><option value="Competitivo">Competitivo</option><option value="Recuperación">Recuperación</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fecha Inicio *</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fecha Fin *</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Objetivos</label>
                        <textarea id="objetivos" name="objetivos" rows="2" class="w-full input-dark p-3 rounded-xl resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalPeriodo()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/periodizacion.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>