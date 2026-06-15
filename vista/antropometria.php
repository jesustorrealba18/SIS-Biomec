<?php
$tituloPagina = 'Seguimiento Antropométrico';
$iconoPagina = 'fa-ruler-combined';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-ruler-combined"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Control Antropométrico</span>
            </div>
            <button onclick="abrirModalAntropometria()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nuevo Registro
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaAntropometria">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Peso (kg)</th>
                            <th class="p-4">Altura (cm)</th>
                            <th class="p-4">IMC</th>
                            <th class="p-4">Grasa %</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaAntropometria">
                        <tr>
                            <td colspan="7" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalAntropometria" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-md p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-ruler-combined text-indigo-500"></i> Registro Antropométrico
                </h2>
                <button onclick="cerrarModalAntropometria()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formAntropometria" class="space-y-4">
                <input type="hidden" id="id_antropometria" name="id_antropometria">
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Atleta *</label>
                        <input type="text" id="inputBuscarAtleta" placeholder="Nombre o cédula..." class="w-full input-dark pl-10 pr-3 py-2.5 rounded-xl text-sm">
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Fecha *</label>
                            <input type="date" id="fecha" name="fecha" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Peso (kg) *</label>
                            <input type="number" id="peso" name="peso" step="0.1" min="20" max="200" data-validar="requerido|numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Altura (cm) *</label>
                            <input type="number" id="altura" name="altura" step="0.1" min="100" max="250" data-validar="requerido|numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Cintura (cm)</label>
                            <input type="number" id="cintura" name="cintura" step="0.1" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalAntropometria()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
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
    <script src="assets/js/antropometria.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>