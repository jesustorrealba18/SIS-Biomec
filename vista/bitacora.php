<?php
$tituloPagina = 'Bitácora del Sistema';
$iconoPagina = 'fa-history';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script><script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
             <div>
                <p class="text-sm text-gray-400 mt-1">Registro inalterable de actividades, transacciones y accesos de los usuarios al sistema.</p>
            </div>
            <button onclick="exportarBitacoraPDF()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer text-xs uppercase tracking-wider">
                <i class="fas fa-file-pdf"></i> EXPORTAR REPORTE
            </button>
        </div>

        <div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20 mb-6">

            <div class="flex items-center gap-2 border-b border-[#252345] pb-2">
                <i class="fas fa-filter text-indigo-400 text-sm"></i>
                <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Auditoría</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">

                <div class="flex flex-col gap-1">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider pl-1">Usuario responsable:</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user-shield text-gray-400 group-hover:text-white transition-colors text-xs"></i>
                        </div>
                        <select id="filtroUsuario" onchange="aplicarFiltros()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] transition-all cursor-pointer appearance-none">
                            <option value="">🛡️ Todos los Usuarios</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider pl-1">Área del sistema:</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-puzzle-piece text-indigo-400/70 group-hover:text-indigo-400 transition-colors text-xs"></i>
                        </div>
                        <select id="filtroModulo" onchange="aplicarFiltros()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] transition-all cursor-pointer appearance-none">
                            <option value="">🧩 Todos los Módulos</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider pl-1 text-emerald-400">Desde (Inicio):</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-day text-emerald-400/70 text-xs"></i>
                        </div>
                        <input type="date" id="filtroFechaInicio" onchange="validarFechasYFiltrar()" class="w-full input-dark pl-9 pr-3 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] transition-all cursor-pointer">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider pl-1 text-red-400">Hasta (Fin):</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-check text-red-400/70 text-xs"></i>
                        </div>
                        <input type="date" id="filtroFechaFin" onchange="validarFechasYFiltrar()" class="w-full input-dark pl-9 pr-3 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] transition-all cursor-pointer">
                    </div>
                </div>

            </div>
        </div>


        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Fecha y Hora</th>
                            <th class="p-4">Usuario</th>
                            <th class="p-4">Módulo</th>
                            <th class="p-4">Acción</th>
                            <th class="p-4 text-right">Detalles</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBitacora" class="divide-y divide-[#252345] text-sm text-gray-300">

                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalBitacora" class="fixed inset-0 bg-[#060512]/50 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 p-6 md:p-8">

            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i> Detalle de la Transacción
                </h3>
                <button onclick="cerrarModalBitacora()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-2">Descripción Exacta de la Acción</p>
                    <div class="input-dark p-4 rounded-xl text-gray-300 text-sm font-mono h-32 overflow-y-auto leading-relaxed border border-gray-700" id="textoDetalleAccion">
                        "El usuario registró una nueva marca para el Atleta ID 15 en la distancia de 50m. Tiempo final establecido en 00:25.50."
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-[#252345]">
                    <div>
                        <span class="block text-[10px] text-gray-500 uppercase font-bold mb-1">Dirección IP de Origen:</span>
                        <span class="text-emerald-400 font-mono text-sm" id="detalleIP">192.168.1.104</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-500 uppercase font-bold mb-1">Dispositivo / Navegador:</span>
                        <span class="text-gray-300 text-sm" id="detalleNavegador">Chrome 114.0 / Windows 11</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="button" onclick="cerrarModalBitacora()" class="bg-gray-800 hover:bg-gray-700 text-gray-300 py-2.5 px-6 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider shadow-lg">
                    Cerrar Detalle
                </button>
            </div>

        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/bitacora.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>