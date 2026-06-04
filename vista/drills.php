<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamiento | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght=300;400;600;700&display=swap" rel="stylesheet">
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
            <h1 class="text-2xl font-bold text-white">Gestión de Entrenamientos</h1>
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
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>

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
                <i class="fas fa-swimmer"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Entrenamientos registrados</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaID" placeholder="Buscar por ID o Nombre..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <button onclick="abrirModalDrills()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Entrenamiento
                </button>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaDrills">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Entrenamiento</th>
                            <th class="p-4">Estilo</th>
                            <th class="p-4">Categoría</th>
                            <th class="p-4">Enfoque Técnico</th>
                            <th class="p-4">Descripción</th>
                            <th class="p-4">Instrucciones</th>
                            <th class="p-4">Metraje</th>
                            <th class="p-4">Dificultad</th>
                            <th class="p-4">Material</th>
                            <th class="p-4">Personalizado</th>
                            <th class="p-4">Activo</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaDrills">
                        <tr>
                            <td colspan="12" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos del sistema...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalDrills" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-dumbbell text-indigo-500"></i> 
                    <span id="modalTitulo">Registrar Entrenamiento</span>
                </h2>
                <button type="button" onclick="cerrarModalDrills()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formDrills" class="space-y-6">
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_drill" name="id_drill" value="">
                <input type="hidden" id="id_usuario_creador" name="id_usuario_creador" value="<?php echo $_SESSION['id'] ?? '1'; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Información Básica</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Nombre</label>
                                <input type="text" id="nombre" name="nombre" 
                                       data-validar="requerido|letras" data-nombre="Nombre" data-min="2"
                                       class="input-dark w-full p-3 rounded-xl">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Estilo</label>
                                <select name="estilo" id="estilo" class="input-dark w-full p-3 rounded-xl">
                                    <option value="Libre">Libre</option>
                                    <option value="Espalda">Espalda</option>
                                    <option value="Braza">Braza</option>
                                    <option value="Mariposa">Mariposa</option>
                                    <option value="Combinado">Combinado</option>
                                    <option value="Multi">Multi</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Categoría</label>
                            <select name="categoria" id="categoria" class="input-dark w-full p-3 rounded-xl">
                                <option value="Tecnico">Técnico</option>
                                <option value="Fuerza">Fuerza</option>
                                <option value="Velocidad">Velocidad</option>
                                <option value="Coordinacion">Coordinación</option>
                                <option value="Resistencia">Resistencia</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Enfoque Técnico</label>
                            <input type="text" id="enfoque_tecnico" name="enfoque_tecnico" 
                                   data-validar="requerido" data-nombre="Enfoque Técnico" data-min="5"
                                   class="input-dark w-full p-3 rounded-xl">
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="flex items-center gap-3 bg-[#0f0d23] p-3 rounded-xl border border-[#252345]">
                                <input type="checkbox" id="personalizado" name="personalizado" value="1"
                                       class="w-4 h-4 text-indigo-600 bg-gray-900 border-gray-700 rounded focus:ring-indigo-500">
                                <label for="personalizado" class="text-xs text-gray-400 font-medium cursor-pointer">Personalizado</label>
                            </div>

                            <div class="flex items-center gap-3 bg-[#0f0d23] p-3 rounded-xl border border-[#252345]">
                                <input type="checkbox" id="activo" name="activo" value="1" checked
                                       class="w-4 h-4 text-indigo-600 bg-gray-900 border-gray-700 rounded focus:ring-indigo-500">
                                <label for="activo" class="text-xs text-gray-400 font-medium cursor-pointer">Activo</label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Detalles del Drill</p>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3" 
                                      data-validar="requerido" data-nombre="Descripción" data-min="10"
                                      class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Instrucciones</label>
                            <textarea id="instrucciones" name="instrucciones" rows="3" 
                                      data-validar="requerido" data-nombre="Instrucciones"
                                      class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>

                        <div class="space-y-1">
    <label class="text-[11px] text-gray-500 font-bold ml-1">Fecha y Hora de Ejecución</label>
    <input type="datetime-local" id="fecha_creacion" name="fecha_creacion" 
           data-validar="requerido" data-nombre="Fecha de ejecución"
           class="input-dark w-full p-3 rounded-xl text-sm text-gray-300 choice-dark">
</div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Metraje Sugerido</label>
                                <input type="text" id="metraje_sugerido" name="metraje_sugerido" 
                                       data-validar="requerido" data-nombre="Metraje"
                                       class="input-dark w-full p-3 rounded-xl" placeholder="Ej: 4x50m">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Dificultad</label>
                                <select name="dificultad" id="dificultad" class="input-dark w-full p-3 rounded-xl">
                                    <option value="Basico">Básico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Material Requerido</label>
                            <select name="material_requerido" id="material_requerido" class="input-dark w-full p-3 rounded-xl">
                                <option value="Ninguno">Ninguno</option>
                                <option value="Pullboy">Pullboy</option>
                                <option value="Aletas">Aletas</option>
                                <option value="Tabla">Tabla</option>
                                <option value="Paddle">Paddle</option>
                                <option value="Resistente">Resistente</option>
                                <option value="Pullboy_Aletas">Pullboy y Aletas</option>
                                <option value="Multiple">Múltiple</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalDrills()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/drills.js"></script>
</body>
</html>