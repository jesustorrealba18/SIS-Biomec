<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carriles | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <header class="flex justify-between items-center mb-12">
            <div>
                <h1 class="text-2xl font-bold text-white">Gestión de Carriles</h1>
                <p class="text-sm text-gray-500 mt-1">Administra los carriles de la piscina</p>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'U'); ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-road"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Carriles registrados</span>
                <span id="totalCarriles" class="bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full text-xs ml-2">0</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaCarril" placeholder="Buscar por número de carril..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carriles', 'gestionar')): ?>
                <button onclick="abrirModalCarril()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Carril
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Carril</th>
                            <th class="p-4">Capacidad Máxima</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaCarriles">
                        <tr>
                            <td colspan="4" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos del sistema...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalCarril" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-road text-indigo-500"></i> 
                    <span id="modalTitulo">Registrar Carril</span>
                </h2>
                <button type="button" onclick="cerrarModalCarril()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formCarril" class="space-y-6">
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_carril" name="id_carril" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Número de Carril *</label>
                        <input type="number" id="numero" name="numero" 
                               data-validar="requerido|numeros" data-nombre="numero" data-min="1" data-max="4" 
                               maxlength="4" class="input-dark w-full p-3 rounded-xl" placeholder="Ej: 1, 2, 3...">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Capacidad Máxima *</label>
                        <input type="number" id="capacidad_maxima" name="capacidad_maxima" 
                               data-nombre="Capacidad" data-min="1" data-max="3" 
                               class="input-dark w-full p-3 rounded-xl" placeholder="Número máximo de nadadores" value="6">
                    </div>

                    <div class="flex items-center gap-3 bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <input type="checkbox" id="activo" name="activo" value="1" checked
                               class="w-5 h-5 text-indigo-600 bg-gray-900 border-gray-700 rounded focus:ring-indigo-500">
                        <label for="activo" class="text-sm text-gray-400 font-medium cursor-pointer">Carril Activo</label>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalCarril()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerCarril" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> 
                    Detalle del Carril
                </h2>
                <button type="button" onclick="cerrarModalVer()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Número de Carril</p>
                        <p id="verNumero" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Capacidad Máxima</p>
                        <p id="verCapacidadMaxima" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Estado</p>
                    <p id="verActivo" class="text-lg font-bold mt-1">-</p>
                </div>
            </div>

            <div class="flex gap-4 pt-6 border-t border-gray-800 mt-6">
                <button onclick="cerrarModalVer()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CERRAR</button>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carriles', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/carriles.js"></script>
</body>
</html>