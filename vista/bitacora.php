<div class="container mx-auto px-4 py-8">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-wider">
                <i class="fas fa-history text-indigo-500 mr-3"></i>Bitácora del Sistema
            </h1>
            <p class="text-gray-400 text-sm mt-1">Registro inalterable de actividades y transacciones de los usuarios.</p>
        </div>
        <button id="btnExportarBitacora" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded-lg transition duration-300 shadow-lg shadow-indigo-500/30">
            <i class="fas fa-file-pdf mr-2"></i>Exportar Reporte
        </button>
    </div>

    <div class="bg-[#161430] border border-gray-700 rounded-xl p-5 mb-6 shadow-xl">
        <form id="formFiltrosBitacora" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <div>
                <label class="block text-gray-400 text-xs font-bold mb-2">Desde:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="w-full bg-black/40 border border-gray-600 text-white rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            
            <div>
                <label class="block text-gray-400 text-xs font-bold mb-2">Hasta:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="w-full bg-black/40 border border-gray-600 text-white rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-gray-400 text-xs font-bold mb-2">Módulo:</label>
                <select id="filtro_modulo" name="filtro_modulo" class="w-full bg-black/40 border border-gray-600 text-white rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Todos los módulos</option>
                    <option value="Sesión">Inicio de Sesión</option>
                    <option value="Marcas">Marcas</option>
                    <option value="Atletas">Atletas</option>
                    <option value="Drills">Drills</option>
                    <option value="Usuarios">Usuarios y Seguridad</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button" id="btnFiltrar" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-[#161430] border border-gray-700 rounded-xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table id="tablaBitacora" class="w-full text-left text-sm text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-black/20 border-b border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">Fecha y Hora</th>
                        <th scope="col" class="px-6 py-3">Usuario (Perfil)</th>
                        <th scope="col" class="px-6 py-3">Módulo</th>
                        <th scope="col" class="px-6 py-3">Acción</th>
                        <th scope="col" class="px-6 py-3 text-center">Detalles</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaBitacora">
                    <tr class="border-b border-gray-700/50 hover:bg-gray-800/50 transition">
                        <td class="px-6 py-4 font-mono text-xs">2026-06-05 18:30:45</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-white">Hendrick</span>
                            <span class="text-xs text-gray-500 block">Entrenador Jefe</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-indigo-400">Marcas</td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2 py-1 rounded text-xs font-bold">
                                INSERTAR
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="verDetallesBitacora(1)" class="text-blue-400 hover:text-blue-300 transition" title="Ver detalle completo">
                                <i class="fas fa-eye fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                    
                    <tr class="border-b border-gray-700/50 hover:bg-gray-800/50 transition">
                        <td class="px-6 py-4 font-mono text-xs">2026-06-05 15:12:10</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-white">José Miguel</span>
                            <span class="text-xs text-gray-500 block">Administrador</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-indigo-400">Seguridad</td>
                        <td class="px-6 py-4">
                            <span class="bg-purple-500/20 text-purple-400 border border-purple-500/30 px-2 py-1 rounded text-xs font-bold">
                                LOGIN
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="text-gray-600 cursor-not-allowed" title="Sin detalles adicionales">
                                <i class="fas fa-eye-slash fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalDetalleBitacora" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#161430] border border-gray-600 rounded-xl shadow-2xl w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300">
        
        <div class="flex justify-between items-center p-4 border-b border-gray-700 bg-black/20 rounded-t-xl">
            <h3 class="text-lg font-bold text-white">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Detalle de Transacción
            </h3>
            <button onclick="cerrarModalBitacora()" class="text-gray-400 hover:text-red-500 transition">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <div class="p-6">
            <div class="mb-4">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Descripción de la acción</p>
                <div class="bg-black/50 p-3 rounded border border-gray-700 text-gray-300 text-sm font-mono h-32 overflow-y-auto" id="textoDetalleAccion">
                    "Se actualizó la marca ID 45. Campo 'tiempo_final_seg' cambió de '85.50' a '85.20'."
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs">Dirección IP:</span>
                    <span class="text-gray-300 font-mono" id="detalleIP">192.168.1.104</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Navegador:</span>
                    <span class="text-gray-300" id="detalleNavegador">Chrome 114.0 / Windows 11</span>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-gray-700 flex justify-end">
            <button onclick="cerrarModalBitacora()" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded transition">
                Cerrar
            </button>
        </div>
    </div>
</div>