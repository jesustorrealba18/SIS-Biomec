<?php
$tituloPagina = 'Mantenimiento y Respaldo';
$iconoPagina = 'fa-database';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-database"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Administración de Base de Datos</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="tarjeta p-5 flex flex-col items-center justify-center gap-4">
                <div class="bg-indigo-600/10 p-4 rounded-full">
                    <i class="fas fa-database text-indigo-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Respaldo</h3>
                <p class="text-xs text-gray-400 text-center">Crear respaldo SQL</p>
                <button onclick="crearRespaldo()" class="mt-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white py-2 px-6 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>

            <div class="tarjeta p-5 flex flex-col items-center justify-center gap-4">
                <div class="bg-emerald-500/10 p-4 rounded-full">
                    <i class="fas fa-upload text-emerald-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Restaurar</h3>
                <p class="text-xs text-gray-400 text-center">Subir archivo .sql</p>
                <div class="mt-3">
                    <input type="file" id="archivoRespaldo" accept=".sql" class="w-full text-gray-400 text-xs file:mr-2 file:py-2 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20">
                    <button onclick="restaurarRespaldo()" class="mt-2 w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                        <i class="fas fa-upload"></i> Cargar y Restaurar
                    </button>
                </div>
            </div>

            <div class="tarjeta p-5 flex flex-col items-center justify-center gap-4">
                <div class="bg-amber-500/10 p-4 rounded-full">
                    <i class="fas fa-broom text-amber-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Limpiar</h3>
                <p class="text-xs text-gray-400 text-center">Optimizar tablas</p>
                <button onclick="limpiarTablas()" class="mt-3 bg-amber-600 hover:bg-amber-500 text-white py-2 px-6 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-broom"></i> Limpiar Tablas
                </button>
            </div>
        </div>

        <div class="tarjeta p-5">
            <h3 class="text-sm font-bold text-white mb-4">Información del Sistema</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-gray-500">Versión PHP:</span>
                    <span class="text-white ml-2 font-mono"><?php echo PHP_VERSION; ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Base de Datos:</span>
                    <span class="text-white ml-2 font-mono">MySQL</span>
                </div>
            </div>
        </div>

    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/mantenimiento.js"></script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>