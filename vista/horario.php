<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloques de Horarios | SGRD</title>
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
                <h1 class="text-2xl font-bold text-white">Gestión de Bloques de Horarios</h1>
                <p class="text-sm text-gray-500 mt-1">Administra los horarios</p>
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
                <i class="fas fa-calendar-alt"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Bloques de horarios registrados</span>
                <span id="totalHorarios" class="bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full text-xs ml-2">0</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaHorario" placeholder="Buscar por día o horario..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('horario', 'gestionar')): ?>
                <button onclick="abrirModalHorario()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Horario
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Día</th>
                            <th class="p-4">Hora Inicio</th>
                            <th class="p-4">Hora Fin</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaHorario">
                        <tr>
                            <td colspan="4" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando horarios...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalHorario" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clock text-indigo-500"></i> 
                    <span id="modalTitulo">Registrar Bloque Horario</span>
                </h2>
                <button type="button" onclick="cerrarModalHorario()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formHorario" class="space-y-6">
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_bloque" name="id_bloque" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">Día de la Semana</label>
                        <select id="dia_semana" name="dia_semana" 
                                data-validar="requerido" data-nombre="Día de la semana"
                                class="input-dark w-full p-3 rounded-xl">
                            <option value="">Seleccione un día</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Hora de Inicio</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" 
                                   data-validar="requerido" data-nombre="Hora de inicio"
                                   class="input-dark w-full p-3 rounded-xl">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Hora de Fin</label>
                            <input type="time" id="hora_fin" name="hora_fin" 
                                   data-validar="requerido" data-nombre="Hora de fin"
                                   class="input-dark w-full p-3 rounded-xl">
                        </div>
                    </div>

                    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
                        <p class="text-xs text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            <span>Los horarios no pueden superponerse con bloques existentes del mismo día</span>
                        </p>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalHorario()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerHorario" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> 
                    Detalle del Bloque Horario
                </h2>
                <button type="button" onclick="cerrarModalVer()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                    <p class="text-xs text-gray-500 uppercase">Día de la Semana</p>
                    <p id="verDia" class="text-xl font-bold text-white mt-1">-</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora de Inicio</p>
                        <p id="verHoraInicio" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                    <div class="bg-[#0f0d23] p-4 rounded-xl border border-[#252345]">
                        <p class="text-xs text-gray-500 uppercase">Hora de Fin</p>
                        <p id="verHoraFin" class="text-2xl font-bold text-white mt-1">-</p>
                    </div>
                </div>
                <div class="bg-indigo-500/10 p-4 rounded-xl border border-indigo-500/20">
                    <p class="text-xs text-gray-500 uppercase">Rango de Horario</p>
                    <p id="verRango" class="text-lg font-bold mt-1">-</p>
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
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('horario', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/horario.js"></script>
</body>
</html>