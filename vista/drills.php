<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamientos | SGRD</title>
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Entrenamiento | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .sidebar { background-color: #161430; width: 260px; border-right: 1px solid #252345; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        .tab-btn { padding: 10px 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: 0.1em; color: #6b7280; border-bottom: 2px solid transparent;
                   transition: all 0.3s; cursor: pointer; }
        .tab-btn:hover { color: #c7d2fe; }
        .tab-btn.active { color: #818cf8; border-bottom-color: #6366f1; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .badge-dificultad {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dificultad-Basico { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .dificultad-Intermedio { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .dificultad-Avanzado { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .estado-activo { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .estado-inactivo { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-20">
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
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre']); ?>&background=4f46e5&color=fff"
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-dumbbell"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Módulo de Control de Entrenamientos</span>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaID" placeholder="Buscar por nombre o estilo..."
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <button onclick="abrirModalDrills()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Entrenamiento
                </button>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-white/5">
                <h3 class="text-white font-semibold">Listado General</h3>
                <span id="totalDrills" class="text-xs bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full border border-indigo-500/20">0 Registrados</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Entrenamiento</th>
                            <th class="p-4">Estilo</th>
                            <th class="p-4">Categoría</th>
                            <th class="p-4">Dificultad</th>
                            <th class="p-4">Material</th>
                            <th class="p-4">Personalizado</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaDrills">
                        <tr>
                            <td colspan="8" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal de Registro/Edición -->
    <div id="modalDrills" class="fixed inset-0 bg-[#0f0d23]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl scale-95 opacity-0 transition-all duration-200">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-dumbbell"></i></div>
                    <h2 class="text-xl font-bold text-white" id="modalTitulo">Registrar Entrenamiento</h2>
                </div>
                <button onclick="cerrarModalDrills()" class="text-gray-500 hover:text-white transition-colors"><i class="fas fa-times text-2xl"></i></button>
            </div>

            <form id="formDrills" enctype="multipart/form-data">
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_drill" name="id_drill" value="">
                <input type="hidden" id="id_usuario_creador" name="id_usuario_creador" value="<?php echo $_SESSION['id'] ?? '1'; ?>">

                <div class="flex border-b border-gray-800 mb-6">
                    <button type="button" onclick="cambiarTabDrill('basica')" class="tab-btn active" data-tab="basica">
                        <i class="fas fa-info-circle mr-2"></i>Información Básica
                    </button>
                    <button type="button" onclick="cambiarTabDrill('detalles')" class="tab-btn" data-tab="detalles">
                        <i class="fas fa-clipboard-list mr-2"></i>Detalles y Ejecución
                    </button>
                    <button type="button" onclick="cambiarTabDrill('configuracion')" class="tab-btn" data-tab="configuracion">
                        <i class="fas fa-cog mr-2"></i>Configuración
                    </button>
                </div>

                <!-- Pestaña: Información Básica -->
                <div id="tab-basica" class="tab-content active">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Nombre del Entrenamiento</label>
                            <input type="text" name="nombre" id="nombre" placeholder="Ej: Ejercicio de patada de crol"
                                   data-validar="requerido|letras" data-nombre="Nombre" data-min="2" data-max="100" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Estilo de Natación</label>
                            <select name="estilo" id="estilo" class="input-dark w-full p-3 rounded-xl">
                                <option value="Libre">Libre</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Braza">Braza</option>
                                <option value="Mariposa">Mariposa</option>
                                <option value="Combinado">Combinado</option>
                                <option value="Multi">Multi</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Categoría</label>
                            <select name="categoria" id="categoria" class="input-dark w-full p-3 rounded-xl">
                                <option value="Tecnico">Técnico</option>
                                <option value="Fuerza">Fuerza</option>
                                <option value="Velocidad">Velocidad</option>
                                <option value="Coordinacion">Coordinación</option>
                                <option value="Resistencia">Resistencia</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Enfoque Técnico</label>
                            <input type="text" name="enfoque_tecnico" id="enfoque_tecnico" placeholder="Ej: Mejora de brazada, patada continua..."
                                   data-validar="requerido|texto" data-nombre="Enfoque Técnico" data-min="5" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Describe detalladamente el entrenamiento..."
                                      data-validar="requerido|texto" data-nombre="Descripción" data-min="10" class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div id="tab-detalles" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Instrucciones</label>
                            <textarea name="instrucciones" id="instrucciones" rows="4" placeholder="Pasos a seguir, series, repeticiones, descansos..."
                                      data-validar="requerido|texto" data-nombre="Instrucciones" class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                       <div class="space-y-2">
    <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Metraje Sugerido</label>
    <input type="text" name="metraje_sugerido" id="metraje_sugerido" 
           placeholder="Ej: 50m, 4x50m, 3x100m, 2000m, 8x25m" data-validar="requerido|metraje" data-nombre="Metraje sugerido" data-min="1" 
           data-max="50" class="input-dark w-full p-3 rounded-xl"> <span class="text-[8px] text-gray-500 mt-1 block">Formatos válidos: 50m, 4x50m, 3x100m, 2000m, 8x25m</span>
</div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Material Requerido</label>
                            <select name="material_requerido" id="material_requerido" class="input-dark w-full p-3 rounded-xl">
                                <option value="Ninguno">Ninguno</option>
                                <option value="Pullboy">Pullboy</option>
                                <option value="Aletas">Aletas</option>
                                <option value="Tabla">Tabla de patada</option>
                                <option value="Paddle">Paddle</option>
                                <option value="Resistente">Resistente</option>
                                <option value="Pullboy_Aletas">Pullboy + Aletas</option>
                                <option value="Multiple">Múltiple equipamiento</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 p-4 rounded-xl bg-black/20 border border-white/5">
                        <p class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest mb-4"><i class="fas fa-calendar-alt mr-2"></i>Programación Temporal</p>
                        <div class="grid grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Fecha y Hora de Ejecución</label>
                                <input type="datetime-local" name="fecha_creacion" id="fecha_creacion"
                                       class="input-dark w-full p-3 rounded-xl text-sm text-gray-300">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Dificultad</label>
                                <select name="dificultad" id="dificultad" class="input-dark w-full p-3 rounded-xl">
                                    <option value="Basico">Básico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-configuracion" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Tipo de Entrenamiento</label>
                            <div class="flex items-center gap-4 p-3 bg-black/30 rounded-xl border border-white/10">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="personalizado" name="personalizado" value="1" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                    <label for="personalizado" class="text-xs text-gray-300">Personalizado (Solo para este atleta)</label>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Estado del Entrenamiento</label>
                            <div class="flex items-center gap-4 p-3 bg-black/30 rounded-xl border border-white/10">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="activo" name="activo" value="1" checked class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                    <label for="activo" class="text-xs text-gray-300">Activo (Disponible para asignar)</label>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 col-span-2">
                            <div class="p-4 rounded-xl bg-indigo-500/5 border border-indigo-500/20">
                                <p class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest mb-2">Información del Creador</p>
                                <p class="text-xs text-gray-400">Este entrenamiento será registrado por: <span class="text-indigo-400 font-mono"><?php echo $_SESSION['nombre'] ?? 'Usuario actual'; ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModalDrills()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold transition-all hover:bg-gray-700">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 py-4 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 active:scale-95 transition-all hover:bg-indigo-500">GUARDAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerDrill" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVerDrill()" class="absolute top-6 right-6 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-10">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleDrillContenido" class="relative p-8"></div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_DRILLS = {
            crear: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('drills', 'crear') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/drills.js"></script>
</body>
</html>