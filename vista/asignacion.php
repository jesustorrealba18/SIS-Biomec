<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Asignación de Carriles | SisBiomec</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        select.input-dark { cursor: pointer; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
        
        main::-webkit-scrollbar { width: 8px; }
        main::-webkit-scrollbar-track { background: #0f0d23; }
        main::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
        main::-webkit-scrollbar-thumb:hover { background: #6366f1; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-12">
            <div>
                <h1 class="text-2xl font-bold text-white">Asignación de Carriles</h1>
                <p class="text-sm text-gray-500 mt-1">Administra la asignación de carriles a grupos con horarios</p>
            </div>
            <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                <div class="text-right mr-2">
                    <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                    <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                        Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                    </a>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff" class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg">
            </div>
        </header>
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-exchange-alt"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Asignaciones registradas</span>
                <span id="totalAsignaciones" class="bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full text-xs ml-2">0</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaAsignacion" placeholder="Buscar por carril, día o grupo..." class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <div class="flex items-center gap-2">
                    <label for="filtroEstado" class="text-xs text-gray-400 uppercase font-bold tracking-wider">Ver:</label>
                    <select id="filtroEstado" onchange="cargarTablaAsignaciones()" class="input-dark p-2 rounded-xl text-xs bg-[#161430] border border-gray-700 text-white">
                        <option value="Activo" selected>✅ Asignaciones Activas</option>
                        <option value="Inactivo">Asignaciones Inactivas</option>
                    </select>
                </div>                
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asignacion', 'gestionar')): ?>
                <button onclick="abrirModalAsignacion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-plus"></i> Nueva Asignación
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
                            <th class="p-4">Bloque Horario</th>
                            <th class="p-4">Grupo</th>
                            <th class="p-4">Día Específico</th>
                            <th class="p-4">Vigencia Inicio</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaAsignaciones">
                        <tr>
                            <td colspan="7" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando asignaciones...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalAsignacion" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-indigo-500"></i> 
                    <span id="modalTitulo">Registrar Asignación</span>
                </h2>
                <button onclick="cerrarModalAsignacion()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formAsignacion" class="space-y-6">
                <input type="hidden" id="id_asignacion" name="id_asignacion" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Carril</label>
                        <select id="id_carril" name="id_carril" data-validar="requerido" data-nombre="Carril" class="input-dark w-full p-3 rounded-xl">
                            <option value="">Seleccione un carril</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Horario</label>
                        <select id="id_bloque_horario" name="id_bloque_horario" data-validar="requerido" data-nombre="Bloque horario" class="input-dark w-full p-3 rounded-xl">
                            <option value="">Seleccione un horario</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Grupo</label>
                        <select id="id_grupo" name="id_grupo" data-validar="requerido" data-nombre="Grupo" class="input-dark w-full p-3 rounded-xl">
                            <option value="">Seleccione un grupo</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Dia Especifico</label>
                        <input type="date" id="dia_especifico" name="dia_especifico" class="input-dark w-full p-3 rounded-xl">
                        <p class="text-[10px] text-gray-500 mt-1">Si es una asignación recurrente, dejar vacío</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Fecha Inicio</label>
                            <input type="date" id="fecha_vigente_inicio" name="fecha_vigente_inicio" data-validar="requerido" data-nombre="Fecha inicio" class="input-dark w-full p-3 rounded-xl">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Fecha Fin</label>
                            <input type="date" id="fecha_vigente_fin" name="fecha_vigente_fin" data-validar="requerido" data-nombre="Fecha fin" class="input-dark w-full p-3 rounded-xl">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <input type="checkbox" id="activa" name="activa" value="1" checked class="w-5 h-5 text-indigo-600 bg-gray-900 border-gray-700 rounded focus:ring-indigo-500">
                        <label for="activa" class="text-sm text-gray-400 font-medium cursor-pointer">Asignación Activa</label>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalAsignacion()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg">
                        GUARDAR ASIGNACIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerAsignacion" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> 
                    Detalle de la Asignación
                </h2>
                <button onclick="cerrarModalVer()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Carril</p>
                        <p id="verCarril" class="text-xl font-bold text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Bloque Horario</p>
                        <p id="verBloqueHorario" class="text-white mt-1">-</p>
                    </div>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Grupo</p>
                    <p id="verGrupo" class="text-white mt-1">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Día Específico</p>
                        <p id="verDiaEspecifico" class="text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Estado</p>
                        <p id="verEstado" class="mt-1">-</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Vigencia Inicio</p>
                        <p id="verFechaInicio" class="text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Vigencia Fin</p>
                        <p id="verFechaFin" class="text-white mt-1">-</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-6 border-t border-gray-800 mt-6">
                <button onclick="cerrarModalVer()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CERRAR</button>
            </div>
        </div>
    </div>

    <div id="modalVerCarril" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-md p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-road text-indigo-500"></i> 
                    Detalle del Carril
                </h2>
                <button type="button" onclick="cerrarModalVerCarril()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Número de Carril</p>
                    <p id="verCarrilNumero" class="text-2xl font-bold text-white mt-1">-</p>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Capacidad Máxima</p>
                    <p id="verCarrilCapacidad" class="text-2xl font-bold text-white mt-1">-</p>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Estado</p>
                    <p id="verCarrilEstado" class="text-lg font-bold mt-1">-</p>
                </div>
            </div>
            <div class="flex gap-4 pt-6 border-t border-gray-800 mt-6">
                <button onclick="cerrarModalVerCarril()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CERRAR</button>
            </div>
        </div>
    </div>

    <div id="modalVerBloque" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-md p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clock text-indigo-500"></i> 
                    Detalle del Bloque de Horario
                </h2>
                <button type="button" onclick="cerrarModalVerBloque()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Día de la Semana</p>
                    <p id="verBloqueDia" class="text-xl font-bold text-white mt-1">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora Inicio</p>
                        <p id="verBloqueInicio" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora Fin</p>
                        <p id="verBloqueFin" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                </div>
                <div class="bg-indigo-500/10 p-4 rounded-xl border border-indigo-500/20">
                    <p class="text-xs text-gray-500 uppercase">Rango de Horario</p>
                    <p id="verBloqueRango" class="text-lg font-bold mt-1">-</p>
                </div>
            </div>
            <div class="flex gap-4 pt-6 border-t border-gray-800 mt-6">
                <button onclick="cerrarModalVerBloque()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CERRAR</button>
            </div>
        </div>
    </div>

    <div id="modalVerGrupo" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-lg p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-layer-group text-indigo-500"></i> 
                    Detalle del Grupo
                </h2>
                <button type="button" onclick="cerrarModalVerGrupo()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Nombre del Grupo</p>
                    <p id="verGrupoNombre" class="text-xl font-bold text-white mt-1">-</p>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Descripción</p>
                    <p id="verGrupoDescripcion" class="text-white mt-1">-</p>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Entrenador Responsable</p>
                    <p id="verGrupoEntrenador" class="text-white mt-1">-</p>
                </div>
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Estado</p>
                    <p id="verGrupoEstado" class="text-lg font-bold mt-1">-</p>
                </div>
            </div>
            <div class="flex gap-4 pt-6 border-t border-gray-800 mt-6">
                <button onclick="cerrarModalVerGrupo()" class="flex-1 bg-gray-800 text-gray-400 py-3 rounded-xl font-bold hover:bg-gray-700 transition">CERRAR</button>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asignacion', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/asignacion.js"></script>
</body>
</html>