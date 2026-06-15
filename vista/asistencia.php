<?php
$tituloPagina = 'Control de Asistencia';
$iconoPagina = 'fa-calendar-check';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-calendar-check"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Registro de Asistencia Diaria</span>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busqueda" placeholder="Buscar atleta o cédula..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <div class="flex items-center gap-2">
                    <label for="fecha" class="text-xs text-gray-400 uppercase font-bold tracking-wider">Fecha:</label>
                    <input type="date" id="fecha" class="input-dark p-2 rounded-xl text-xs bg-[#0f0d23] text-white">
                </div>
                <button onclick="exportarAsistenciaPDF()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaAsistencia">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Cédula</th>
                            <th class="p-4">Grupo</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Hora Llegada</th>
                            <th class="p-4">Hora Salida</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaAsistencia">
                        <tr>
                            <td colspan="7" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando asistencia...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asistencia', 'ver') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/asistencia.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>