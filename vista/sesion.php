<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesiones de Entrenamiento | SGRD</title>
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
                <i class="fas fa-swimming-pool text-indigo-500"></i> Planificación de Sesiones de Entrenamiento
            </h1>
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
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <p class="text-sm text-gray-400 mt-1">Planificación diaria de cargas, volumen de metros, zonas de intensidad y series.</p>
            <button onclick="abrirModalSesion()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> PLANIFICAR SESIÓN
            </button>
        </div>

        <div class="tarjeta p-4 flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                <input type="text" id="busquedaSesion" onkeyup="filtrarTablaSesiones()" placeholder="Buscar por observaciones o tipo..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <select id="filtroGrupo" onchange="cargarTablaSesiones()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Grupos</option>
                </select>
                <select id="filtroTipoSesion" onchange="cargarTablaSesiones()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Tipos</option>
                    <option value="Agua">Agua (Piscina)</option>
                    <option value="Tierra">Tierra (Seco)</option>
                    <option value="Mixta">Mixta</option>
                </select>
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Fecha / Horario</th>
                            <th class="p-4">Grupo / Microciclo</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4 text-center">Vol. Planificado</th>
                            <th class="p-4 text-center">Vol. Ejecutado</th>
                            <th class="p-4 text-center">Duración</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySesiones" class="divide-y divide-[#252345] text-sm text-gray-300">
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <div id="modalSesion" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-5xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalSesionTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-swimming-pool text-emerald-400"></i> Planificar Sesión de Entrenamiento
                </h3>
                <button onclick="cerrarModalSesion()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="formSesion" autocomplete="off">
                <input type="hidden" id="id_sesion" name="id_sesion" value="">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Grupo de Trabajo *</label>
                        <select id="id_grupo" name="id_grupo" required class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]">
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Microciclo Asociado *</label>
                        <select id="id_microciclo" name="id_microciclo" required class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]">
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Sesión *</label>
                        <input type="date" id="fecha" name="fecha" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Sesión *</label>
                        <select id="tipo_sesion" name="tipo_sesion" required class="w-full input-dark p-3 rounded-xl text-sm bg-[#0f0d23]">
                            <option value="Agua">Agua (Piscina)</option>
                            <option value="Tierra">Tierra (Seco)</option>
                            <option value="Mixta">Mixta</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Calentamiento (Previo)</label>
                        <textarea id="calentamiento" name="calentamiento" rows="2" placeholder="Ej: 400m Variado suelto, 4x50m técnica de patada..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Vuelta a la Calma (Afloje)</label>
                        <textarea id="vuelta_calma" name="vuelta_calma" rows="2" placeholder="Ej: 200m respiración bilateral, estiramientos activos..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-indigo-950/20 p-4 rounded-xl border border-[#252345]">
                    <div>
                        <label class="block text-xs text-indigo-300 uppercase font-bold mb-2">
                            <i class="fas fa-flag-checkered mr-1"></i> Vol. Planificado Total (m)
                        </label>
                        <input type="number" id="vuelta_planificado" name="vuelta_planificado" placeholder="Ej: 3500" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-emerald-400 uppercase font-bold mb-2">
                            <i class="fas fa-check-circle mr-1"></i> Vol. Ejecutado Total (m)
                        </label>
                        <input type="number" id="vuelta_ejecutado" name="vuelta_ejecutado" placeholder="Se llena en bitácora o cierre" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-amber-400 uppercase font-bold mb-2">
                            <i class="fas fa-clock mr-1"></i> Duración Total (Minutos)
                        </label>
                        <input type="number" id="duracion_minutos" name="duracion_minutos" placeholder="Ej: 90" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                </div>

                <div class="bg-black/20 p-4 rounded-xl border border-dashed border-indigo-500/30">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-indigo-400 font-bold tracking-widest">
                            <i class="fas fa-layer-group mr-2"></i>Estructura de Series Principales de la Sesión
                        </p>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-400">Total Planificado: <strong class="text-white" id="lblVolTotalPlanificado">0m</strong></span>
                            <button type="button" onclick="agregarFilaSerie()" class="text-xs bg-indigo-500/20 text-indigo-400 hover:bg-indigo-500/30 px-3 py-1 rounded-lg transition cursor-pointer font-bold">
                                <i class="fas fa-plus mr-1"></i> Agregar Serie
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-[10px] text-gray-500 uppercase tracking-wider border-b border-[#252345]">
                                    <th class="p-2 w-24">Bloque</th>
                                    <th class="p-2 w-48">Ejercicio / Drill</th>
                                    <th class="p-2">Descripción Libre</th>
                                    <th class="p-2 w-20">Repet.</th>
                                    <th class="p-2 w-24">Dist.(m)</th>
                                    <th class="p-2 w-20">Desc.(s)</th>
                                    <th class="p-2 w-24">Zona Int.</th>
                                    <th class="p-2 w-28">Ritmo Obj.</th>
                                    <th class="p-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodySeries" class="divide-y divide-[#252345]">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones / Indicaciones del Coach</label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Detalles de hidratación, enfoque del día o comentarios post-entreno..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalSesion()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR SESIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalCompletar" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-amber-400"></i> <span id="tituloModalCompletar">Bitácora de Asistencia y Rendimiento</span>
                </h3>
                <button onclick="cerrarModalCompletar()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formCompletar" autocomplete="off">
                <input type="hidden" id="id_sesion_completar" name="id_sesion" value="">

                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-500">Registre la ejecución real por cada nadador convocado a la sesión.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[10px] text-gray-500 uppercase tracking-wider border-b border-[#252345]">
                                <th class="p-2">Atleta</th>
                                <th class="p-2 w-32">Asistencia</th>
                                <th class="p-2 w-36">Vol. Ejecutado (m)</th>
                                <th class="p-2 w-48">RPE (Esfuerzo Percibido)</th>
                                <th class="p-2">Comentarios / Limitantes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCompletarAtletas" class="divide-y divide-[#252345]">
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalCompletar()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-amber-600 hover:bg-amber-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-amber-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR ASISTENCIAS Y MÉTRICAS <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/sesion.js"></script>
</body>
</html>