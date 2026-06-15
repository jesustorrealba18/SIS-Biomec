<?php
$tituloPagina = 'Observaciones Técnicas';
$iconoPagina = 'fa-clipboard-check';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-clipboard-check"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Evaluaciones Técnicas</span>
            </div>
            <button onclick="abrirModalObservacion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nueva Observación
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaObservaciones">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Observación</th>
                            <th class="p-4">Entrenador</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaObservaciones">
                        <tr>
                            <td colspan="6" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando observaciones...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalObservacion" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-indigo-500"></i> Registrar Observación
                </h2>
                <button onclick="cerrarModalObservacion()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formObservacion" class="space-y-6">
                <input type="hidden" id="id_observacion" name="id_observacion">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Atleta *</label>
                        <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-3 py-2.5 rounded-xl text-sm">
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fecha *</label>
                        <input type="date" id="fecha" name="fecha" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                    </div>
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Tipo de Observación *</label>
                    <select id="tipo_observacion" name="tipo_observacion" data-validar="requerido" class="w-full input-dark p-3 rounded-xl">
                        <option value="">Seleccione...</option><option value="Técnica">Técnica</option><option value="Psicológica">Psicológica</option><option value="Física">Física</option>
                    </select>
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Observación *</label>
                    <textarea id="observacion" name="observacion" rows="4" data-validar="requerido|texto" data-min="10" class="w-full input-dark p-3 rounded-xl resize-none"></textarea>
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalObservacion()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
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
    <script src="assets/js/observacionesTecnicas.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>