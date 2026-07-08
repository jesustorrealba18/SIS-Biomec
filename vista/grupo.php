<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Grupos de Entrenamiento | SisBiomec</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        .input-dark.is-valid { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
        .input-dark.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }
        .invalid-feedback { display: none; font-size: 0.75rem; color: #ef4444; margin-top: 0.25rem; padding-left: 0.5rem; }
        .valid-feedback { display: none; font-size: 0.75rem; color: #10b981; margin-top: 0.25rem; padding-left: 0.5rem; }
        .invalid-feedback.show { display: block; }
        .valid-feedback.show { display: block; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        
        .form-checkbox {
            appearance: none;
            width: 1.1rem;
            height: 1.1rem;
            border: 2px solid #4b5563;
            border-radius: 0.25rem;
            background: #0f0d23;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .form-checkbox:checked {
            background: #6366f1;
            border-color: #6366f1;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
            background-size: 0.8rem;
            background-position: center;
            background-repeat: no-repeat;
        }
        .form-checkbox:focus { outline: none; border-color: #6366f1; }
        
        .scroll-atletas::-webkit-scrollbar { width: 4px; }
        .scroll-atletas::-webkit-scrollbar-track { background: #0f0d23; border-radius: 10px; }
        .scroll-atletas::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
        
        .border-danger { border-color: #ef4444 !important; }
        .border-success { border-color: #10b981 !important; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-20">
            <h1 class="text-2xl font-bold text-white">Grupos de Entrenamiento</h1>
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
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-layer-group"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Listado de Grupos</span>
                <span class="text-xs text-gray-500 ml-2" id="contadorGrupos"></span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaNombre" placeholder="Buscar por nombre..." class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <div class="flex items-center gap-2">
                    <label for="filtroEstado" class="text-xs text-gray-400 uppercase font-bold tracking-wider">Ver:</label>
                    <select id="filtroEstado" class="input-dark p-2 rounded-xl text-xs bg-[#161430] border border-gray-700 text-white">
                        <option value="Activo" selected>✅ Grupos Activos</option>
                        <option value="Inactivo">🗑️ Grupos Archivados</option>
                    </select>
                </div>                
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                <button onclick="abrirModalGrupo()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Grupo
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaGrupos">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Nombre del Grupo</th>
                            <th class="p-4">Descripción</th>
                            <th class="p-4">Entrenador</th>
                            <th class="p-4 text-center">Atletas</th>
                            <th class="p-4 text-center">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaGrupos">
                        <tr>
                            <td colspan="6" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando módulos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ============================================ -->
    <!-- MODAL: GESTIÓN DE GRUPOS (CREAR/EDITAR) -->
    <!-- ============================================ -->
    <div id="modalGrupo" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-folder-plus text-indigo-500"></i> Gestión de Grupo
                </h2>
                <button onclick="cerrarModalGrupo()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formGrupo" class="space-y-6">
                <input type="hidden" id="id_grupo_original" name="id_grupo_original" value="">

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">NOMBRE DEL GRUPO *</label>
                        <input type="text" id="nombre" name="nombre" data-validate="true" 
                               class="input-dark w-full p-3 rounded-xl" 
                               placeholder="Ej: Equipo Juvenil A"
                               maxlength="100">
                        <div class="invalid-feedback" id="nombre-error"></div>
                        <small class="text-gray-500 text-xs ml-1">Máximo 100 caracteres</small>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">ENTRENADOR ASIGNADO *</label>
                        <select id="id_entrenador" name="id_entrenador" data-validate="true" 
                                class="input-dark w-full p-3 rounded-xl">
                            <option value="">Seleccione un entrenador...</option>
                        </select>
                        <div class="invalid-feedback" id="id_entrenador-error"></div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">DESCRIPCIÓN</label>
                        <textarea id="descripcion" name="descripcion" rows="3" 
                                  class="input-dark w-full p-3 rounded-xl resize-none" 
                                  placeholder="Detalles opcionales del grupo de natación..."></textarea>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalGrupo()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg transition active:scale-95">
                        GUARDAR GRUPO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: ASIGNACIÓN DE ATLETAS (SOLO DISPONIBLES) -->
    <!-- ============================================ -->
    <div id="modalAsignacion" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-user-plus text-emerald-500"></i> Asignar Atletas al Grupo
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        <span id="grupo_nombre" class="text-indigo-400 font-semibold">Cargando...</span>
                        <span id="grupo_info" class="text-gray-500 text-xs ml-2"></span>
                    </p>
                </div>
                <button onclick="cerrarModalAsignacion()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formAsignacion" class="space-y-6">
                <input type="hidden" id="id_grupo_asignacion" name="id_grupo" value="">

                <!-- FILTROS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">CATEGORÍA</label>
                        <select id="filtroCategoria" class="input-dark w-full p-2 rounded-xl" onchange="filtrarAtletasPorCategoria()">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">EDAD MÍNIMA</label>
                        <input type="number" id="edad_min" name="edad_min" 
                               class="input-dark w-full p-2 rounded-xl" 
                               placeholder="Ej: 12" min="5" max="99">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-gray-500 font-bold ml-1">EDAD MÁXIMA</label>
                        <input type="number" id="edad_max" name="edad_max" 
                               class="input-dark w-full p-2 rounded-xl" 
                               placeholder="Ej: 15" min="5" max="99">
                        <div class="invalid-feedback" id="edad-error"></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="filtrarAtletasPorCategoria()" 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold transition">
                        <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                    </button>
                    <button type="button" onclick="limpiarFiltros()" 
                            class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-bold transition">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                </div>

                <!-- ATLETAS DISPONIBLES (SOLO ESTOS) -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-[11px] text-gray-500 font-bold ml-1 uppercase tracking-wider">
                            <i class="fas fa-user-plus mr-2"></i> Atletas Disponibles para Asignar
                        </label>
                        <span class="text-xs text-gray-400" id="contador-atletas">0 seleccionados</span>
                    </div>
                    <div id="atletas-container" class="border border-gray-700 rounded-xl p-3 bg-[#0f0d23]">
                        <div id="atletas-disponibles" class="scroll-atletas max-h-64 overflow-y-auto">
                            <div class="text-center py-4 text-gray-400">
                                <i class="fas fa-spinner fa-spin"></i> Cargando atletas...
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="atletas-error"></div>
                </div>

                <!-- BOTÓN PARA VER ATLETAS ASIGNADOS -->
                <div>
                    <button type="button" onclick="abrirModalVerGrupoDesdeAsignacion()" 
                            class="w-full bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 py-3 rounded-xl font-bold transition border border-indigo-500/20">
                        <i class="fas fa-eye mr-2"></i> Ver todos los atletas asignados actualmente
                    </button>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalAsignacion()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnAsignar" class="flex-[2] bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-xl font-bold shadow-lg transition active:scale-95">
                        ASIGNAR ATLETAS <i class="fas fa-user-plus ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: VER GRUPO (DETALLES COMPLETOS) -->
    <!-- ============================================ -->
    <div id="modalVerGrupo" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVerGrupo()" class="absolute top-6 right-6 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-10">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleGrupoContenido" class="relative p-8">
                <!-- Contenido dinámico cargado por JavaScript -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                    <p class="text-gray-400 mt-3 text-sm">Cargando detalles del grupo...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/grupo.js"></script>
</body>
</html>