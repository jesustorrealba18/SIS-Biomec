<?php
$tituloPagina = 'Tests Físicos';
$iconoPagina = 'fa-heartbeat';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-heartbeat"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Evaluaciones Físicas</span>
            </div>
            <button onclick="abrirModalTest()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nuevo Test
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaTests">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Tipo de Test</th>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Resultados</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaTests">
                        <tr>
                            <td colspan="5" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando pruebas...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalTest" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-heartbeat text-indigo-500"></i> Registrar Test Físico
                </h2>
                <button onclick="cerrarModalTest()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formTest" class="space-y-6">
                <input type="hidden" id="id_test" name="id_test">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Atleta *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-xs"></i>
                            </div>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-3 py-2.5 rounded-xl text-sm">
                            <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido">
                            <button type="button" id="btnLimpiarAtleta" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                                <i class="fas fa-times-circle text-red-400 hover:text-red-300"></i>
                            </button>
                        </div>
                        <div id="dropdownAtletas" class="absolute z-50 w-full bg-[#161430] border border-[#252345] rounded-xl shadow-2xl hidden max-h-60 overflow-y-auto mt-1">
                            <ul id="ulAtletas" class="divide-y divide-[#252345]"></ul>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Tipo de Test</label>
                            <select id="tipo_test" name="tipo_test" data-validar="requerido" class="w-full input-dark p-3 rounded-xl">
                                <option value="">Seleccione...</option><option value="Cooper">Cooper</option><option value="30s Wingate">30s Wingate</option><option value="Vertical">Vertical</option><option value="Fuerza">Fuerza</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Fecha</label>
                            <input type="date" id="fecha" name="fecha" data-validar="requerido" class="w-full input-dark p-3 rounded-xl text-sm">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3" class="w-full input-dark p-3 rounded-xl resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalTest()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/testFisico.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>