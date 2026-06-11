<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga de Bienestar (RPE) | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <header class="flex justify-between items-center mb-20">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-heartbeat text-indigo-500"></i> Carga de Bienestar (RPE)
            </h1>
            <div class="flex items-center gap-6">
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>
                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div>
                <p class="text-sm text-gray-400 mt-1">Registro de percepción de esfuerzo (RPE), calidad de sueño, fatiga muscular y estrés.</p>
            </div>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar')): ?>
            <button onclick="abrirModalCarga()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR CARGA
            </button>
            <?php endif; ?>
        </div>

        <!-- Filtros -->
        <div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20">
            <div class="flex items-center gap-2 border-b border-[#252345] pb-2">
                <i class="fas fa-filter text-indigo-400 text-sm"></i>
                <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Búsqueda</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                    </div>
                    <select id="filtroAtleta" class="w-full input-dark pl-12 pr-10 py-3 rounded-xl text-sm bg-[#0f0d23] border border-[#252345] hover:border-indigo-500/50 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer appearance-none shadow-inner">
                        <option value="">👤 Todos los Atletas</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase mb-1">Fecha Desde</label>
                    <input type="date" id="filtroFechaDesde" class="w-full input-dark p-3 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase mb-1">Fecha Hasta</label>
                    <input type="date" id="filtroFechaHasta" class="w-full input-dark p-3 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase mb-1">Estado</label>
                    <select id="filtroEstado" class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23] border border-[#252345]">
                        <option value="Activo">Activos</option>
                        <option value="Anulado">Anulados</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="cargarTablaCargas()" class="bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 transition">
                    <i class="fas fa-search"></i> Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="tarjeta overflow-hidden mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">RPE</th>
                            <th class="p-4">Sueño (h)</th>
                            <th class="p-4">Calidad Sueño</th>
                            <th class="p-4">Sensación Muscular</th>
                            <th class="p-4">Estrés</th>
                            <th class="p-4">sRPE</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCargas" class="divide-y divide-[#252345] text-sm text-gray-300">
                        <!-- Filas dinámicas -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal para crear/editar -->
    <div id="modalCarga" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-heartbeat text-emerald-400"></i> Registrar Carga de Bienestar
                </h3>
                <button onclick="cerrarModalCarga()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formCarga" autocomplete="off">
                <input type="hidden" id="accion_form" name="accion" value="guardar">
                <input type="hidden" id="id_rpe" name="id_rpe" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Atleta con buscador -->
                    <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cédula..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm" autocomplete="off" required>
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden transition-all">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]"></ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha *</label>
                        <input type="date" id="fecha" name="fecha" max="<?php echo date('Y-m-d'); ?>" data-validar="requerido" class="w-full input-dark p-3 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">RPE (1-10) *</label>
                        <input type="number" id="rpe" name="rpe" min="1" max="10" data-validar="requerido|rango:1,10" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: 7">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Horas de Sueño</label>
                        <input type="number" step="0.5" id="horas_sueno" name="horas_sueno" min="0" max="24" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: 7.5">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Calidad Sueño (1-5)</label>
                        <select id="calidad_sueno" name="calidad_sueno" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccionar</option>
                            <option value="1">1 - Muy mala</option>
                            <option value="2">2 - Mala</option>
                            <option value="3">3 - Regular</option>
                            <option value="4">4 - Buena</option>
                            <option value="5">5 - Excelente</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Sensación Muscular (1-10)</label>
                        <input type="number" id="sensacion_muscular" name="sensacion_muscular" min="1" max="10" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="1=muy fatigado, 10=recuperado">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Estrés Percibido (1-10)</label>
                        <input type="number" id="estres_percibido" name="estres_percibido" min="1" max="10" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="1=bajo, 10=alto">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Duración (minutos)</label>
                        <input type="number" id="duracion_minutos" name="duracion_minutos" min="0" step="1" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Para cálculo sRPE">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Metros Nadados</label>
                        <input type="number" id="metros_nadados" name="metros_nadados" min="0" step="100" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Opcional">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="2" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Comentarios adicionales..."></textarea>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-indigo-500/10 rounded-xl text-xs text-gray-300 flex justify-between items-center">
                    <span><i class="fas fa-calculator"></i> sRPE (RPE × duración) se calcula automáticamente:</span>
                    <span id="srpePreview" class="font-mono font-bold text-indigo-300">0</span>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalCarga()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para ver detalle -->
    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar') ? 'true' : 'false'; ?>,
            anular: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'anular') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/cargaBienestar.js"></script>
</body>
</html>