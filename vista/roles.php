<?php
$tituloPagina = 'Roles y Permisos';
$iconoPagina = 'fa-shield-alt';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-shield-alt"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Gestión de Roles</span>
            </div>
            <button onclick="abrirModalRol()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Nuevo Rol
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaRoles">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Nombre del Rol</th>
                            <th class="p-4">Descripción</th>
                            <th class="p-4">Usuarios Asignados</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaRoles">
                        <tr>
                            <td colspan="4" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando roles...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalRol" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-md p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-shield-alt text-indigo-500"></i> Gestión de Rol
                </h2>
                <button onclick="cerrarModalRol()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="formRol" class="space-y-6">
                <input type="hidden" id="id_rol" name="id_rol">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido|texto" class="w-full input-dark p-3 rounded-xl">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="2" class="w-full input-dark p-3 rounded-xl resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalRol()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/roles.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>