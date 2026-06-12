<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporadas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-calendar-check text-indigo-500"></i> Temporadas
            </h1>
            <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                <div class="text-right mr-2">
                    <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                    <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                        Cerrar Sesion <i class="fas fa-sign-out-alt ml-1"></i>
                    </a>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff"
                     class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <p class="text-sm text-gray-400 mt-1">Gestion de temporadas deportivas del club.</p>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'registrar')): ?>
            <button onclick="abrirModalTemporada()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> NUEVA TEMPORADA
            </button>
            <?php endif; ?>
        </div>

        <div class="tarjeta p-4 mb-4">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                <input type="text" id="busquedaTemporada" onkeyup="filtrarTabla()" placeholder="Buscar por nombre..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Temporada</th>
                            <th class="p-4">Fecha Inicio</th>
                            <th class="p-4">Fecha Fin</th>
                            <th class="p-4 text-center">Macrociclos</th>
                            <th class="p-4 text-center">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTemporadas" class="divide-y divide-[#252345] text-sm text-gray-300">
                        <tr>
                            <td colspan="6" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL CREAR/EDITAR TEMPORADA -->
    <div id="modalTemporada" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTemporadaTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-calendar-check text-emerald-400"></i> Nueva Temporada
                </h3>
                <button onclick="cerrarModalTemporada()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formTemporada" autocomplete="off">
                <input type="hidden" id="id_temporada" name="id_temporada" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre de la Temporada *</label>
                        <input type="text" id="nombre" name="nombre" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Temporada 2026-2027">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha Inicio *</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha Fin *</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-[#0f0d23] p-3 rounded-xl border border-[#252345]">
                        <input type="checkbox" id="activa" name="activa" value="1" checked
                               class="w-4 h-4 text-indigo-600 bg-gray-900 border-gray-700 rounded focus:ring-indigo-500">
                        <label for="activa" class="text-xs text-gray-400 font-medium cursor-pointer">Marcar como temporada activa (desactiva las demas)</label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalTemporada()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/temporadas.js"></script>
</body>
</html>
