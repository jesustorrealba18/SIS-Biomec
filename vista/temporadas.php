<?php
$tituloPagina = 'Temporadas';
$iconoPagina = 'fa-calendar-check';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-calendar-check"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Temporadas Deportivas</span>
            </div>
            <button onclick="abrirModalTemporada()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                <i class="fas fa-plus"></i> Nueva Temporada
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaTemporadas">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Fecha Inicio</th>
                            <th class="p-4">Fecha Fin</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaTemporadas">
                        <tr>
                            <td colspan="5" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando temporadas...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalTemporada" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-calendar-check text-indigo-500"></i> Registrar Temporada
                </h2>
                <button onclick="cerrarModalTemporada()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formTemporada" class="space-y-6">
                <input type="hidden" id="id_temporada" name="id_temporada">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido|texto" class="w-full input-dark p-3 rounded-xl">
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
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalTemporada()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
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
    <script src="assets/js/temporadas.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>