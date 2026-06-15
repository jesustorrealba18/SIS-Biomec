<?php
$tituloPagina = 'Monitoreo de Carga y Bienestar (RPE)';
$iconoPagina = 'fa-notes-medical';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-chart-line"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Registro de Percepción de Esfuerzo</span>
            </div>
            <button onclick="abrirModalCarga()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nuevo RPE
            </button>
        </div>

        <div class="tarjeta p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Filtro Estado</label>
                    <select id="filtroEstado" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        <option value="Activo">Activos</option>
                        <option value="Anulado">Anulados</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Filtrar por Atleta</label>
                    <select id="filtroAtleta" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        <option value="">👤 Todos los Atletas</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Fecha Desde</label>
                    <input type="date" id="filtroFechaDesde" class="w-full input-dark p-2.5 rounded-xl text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Fecha Hasta</label>
                    <input type="date" id="filtroFechaHasta" class="w-full input-dark p-2.5 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaRPE">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">RPE</th>
                            <th class="p-4">Horas Sueño</th>
                            <th class="p-4">Calidad Sueño</th>
                            <th class="p-4">Sensación Muscular</th>
                            <th class="p-4">Estrés Percibido</th>
                            <th class="p-4">sRPE</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="tbodyCargas">
                        <tr>
                            <td colspan="9" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando registros...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalCarga" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-md shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="sticky top-0 bg-[#161430] z-10 flex justify-between items-center p-4 sm:p-6 border-b border-gray-800 rounded-t-2xl">
                <h2 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-chart-line text-indigo-500"></i> 
                    <span>Registrar RPE</span>
                </h2>
                <button onclick="cerrarModalCarga()" class="text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formCarga" class="p-4 sm:p-6 space-y-6">
                <input type="hidden" id="id_rpe" name="id_rpe">
                <input type="hidden" id="accion_form" name="accion_form" value="guardar">
                <div class="space-y-4">
                    <p class="text-[11px] text-indigo-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                        <i class="fas fa-user-circle"></i> Atleta y Fecha
                    </p>
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Atleta *</label>
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
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Fecha *</label>
                        <input type="date" id="fecha" name="fecha" data-validar="requerido" class="w-full input-dark p-2.5 rounded-xl text-sm">
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="text-[11px] text-emerald-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                        <i class="fas fa-chart-bar"></i> Carga de Entrenamiento
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">RPE (1-10) *</label>
                            <input type="number" id="rpe" name="rpe" min="1" max="10" data-validar="requerido|numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Duración (min)</label>
                            <input type="number" id="duracion_minutos" name="duracion_minutos" min="1" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Metros Nadados</label>
                        <input type="number" id="metros_nadados" name="metros_nadados" min="0" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                    </div>
                    <div class="bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-xl text-center">
                        <p class="text-[9px] text-indigo-400 uppercase font-bold tracking-wider mb-1">sRPE Calculado</p>
                        <p id="srpePreview" class="text-2xl font-black text-indigo-300">0</p>
                        <input type="hidden" id="srpe" name="srpe" value="0">
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="text-[11px] text-amber-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                        <i class="fas fa-bed"></i> Bienestar y Sueño
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Horas Sueño</label>
                            <input type="number" id="horas_sueno" name="horas_sueno" min="0" max="24" step="0.5" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Calidad (1-5)</label>
                            <select id="calidad_sueno" name="calidad_sueno" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                                <option value="">Seleccione...</option>
                                <option value="1">1 ⭐ Pésima</option>
                                <option value="2">2 ⭐⭐ Mala</option>
                                <option value="3">3 ⭐⭐⭐ Regular</option>
                                <option value="4">4 ⭐⭐⭐⭐ Buena</option>
                                <option value="5">5 ⭐⭐⭐⭐⭐ Excelente</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="text-[11px] text-purple-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                        <i class="fas fa-heartbeat"></i> Sensación y Estrés
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Sensación Muscular (1-10)</label>
                            <input type="number" id="sensacion_muscular" name="sensacion_muscular" min="1" max="10" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Estrés Percibido (1-10)</label>
                            <input type="number" id="estres_percibido" name="estres_percibido" min="1" max="10" data-validar="numeros" class="w-full input-dark p-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] text-gray-500 uppercase font-bold ml-1">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="2" class="w-full input-dark p-3 rounded-xl resize-none text-sm"></textarea>
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalCarga()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition order-2 sm:order-1">
                        CANCELAR
                    </button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition flex items-center justify-center gap-2 order-1 sm:order-2">
                        <i class="fas fa-save"></i> GUARDAR REGISTRO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-3xl rounded-2xl sm:rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVer()" class="absolute top-4 right-4 z-50 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 bg-black/30 rounded-full w-8 h-8 flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-times text-lg"></i>
            </button>
            <div class="p-5 sm:p-8 relative z-10" id="detalleContenido"></div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carga_bienestar', 'registrar') ? 'true' : 'false'; ?>,
            anular: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carga_bienestar', 'anular') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/cargaBienestar.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>