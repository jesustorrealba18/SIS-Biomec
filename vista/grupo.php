<?php
$tituloPagina = 'Grupos de Entrenamiento';
$iconoPagina = 'fa-layer-group';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-layer-group"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Listado de Grupos</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaNombre" placeholder="Buscar por nombre..." class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <div class="flex items-center gap-2">
                    <label for="filtroEstado" class="text-xs text-gray-400 uppercase font-bold tracking-wider">Ver:</label>
                    <select id="filtroEstado" onchange="cargarTablaGrupos()" class="input-dark p-2 rounded-xl text-xs bg-[#161430] border border-gray-700 text-white">
                        <option value="Activo" selected>✅ Grupos Activos</option>
                        <option value="Inactivo">🗑️ Grupos Archivados</option>
                    </select>
                </div>

                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                <button onclick="abrirModalGrupo()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Grupo
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaGrupos">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Nombre del Grupo</th>
                            <th class="p-4">Descripción</th>
                            <th class="p-4">Entrenador Responsable</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaGrupos">
                        <tr>
                            <td colspan="5" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando módulos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <div id="modalGrupo" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-folder-plus text-indigo-500"></i> Gestión de Grupo de Entrenamiento
                </h2>
                <button onclick="cerrarModalGrupo()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formGrupo" class="space-y-6">
                <input type="hidden" id="id_grupo_original" name="id_grupo_original" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">NOMBRE DEL GRUPO</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido" data-nombre="Nombre" class="input-dark w-full p-3 rounded-xl" placeholder="Ej: Equipo Juvenil A">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">ENTRENADOR ASIGNADO</label>
                        <select id="id_entrenador" name="id_entrenador" class="input-dark w-full p-3 rounded-xl">
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">DESCRIPCIÓN</label>
                        <textarea id="descripcion" name="descripcion" rows="3" class="input-dark w-full p-3 rounded-xl resize-none" placeholder="Detalles opcionales del grupo de natación..."></textarea>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalGrupo()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg">
                        GUARDAR GRUPO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/grupo.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>