<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Entrenadores | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { 
            background: #0f0d23; 
            border: 1px solid #252345; 
            color: white; 
            transition: all 0.3s ease; 
        }
        .input-dark:focus { 
            border-color: #6366f1; 
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); 
            outline: none; 
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        
        .error-msg {
            display: none;
            font-size: 10px;
            margin-top: 4px;
        }
        .error-msg.show {
            display: block;
        }
        .border-red-500 {
            border-color: #ef4444 !important;
        }
        .border-green-500 {
            border-color: #22c55e !important;
        }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <header class="flex justify-between items-center mb-12">
            <h1 class="text-2xl font-bold text-white">Gestión de Entrenadores</h1>
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
                <i class="fas fa-users"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Entrenadores registrados</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaCedula" placeholder="Buscar por cédula..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                <button onclick="abrirModalEntrenador()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Entrenador
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-white/5">
                <h3 class="text-white font-semibold">Listado General</h3>
                <span id="totalEntrenador" class="text-xs bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full border border-indigo-500/20">0 Registrados</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaEntrenador">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Entrenador</th>
                            <th class="p-4">Cédula</th>
                            <th class="p-4">Teléfono</th>
                            <th class="p-4">Dirección</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaEntrenador">
                        <tr>
                            <td colspan="5" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Cargando datos del sistema...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalEntrenador" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl p-8 shadow-2xl overflow-y-auto max-h-[90vh] scale-95 opacity-0 transition-all duration-200">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-id-card text-indigo-500"></i> 
                    <span id="modalTitulo">Registrar Entrenador</span>
                </h2>
                <button type="button" onclick="cerrarModalEntrenador()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formEntrenador" class="space-y-6" novalidate>
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_entrenador" name="id_entrenador" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-tighter">Información de los entrenadores</p>
                        
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Cédula *</label>
                            <input type="text" id="cedula" name="cedula" 
                                   data-validar="requerido|numeros" data-nombre="Cédula" data-min="8" data-max="8" 
                                   maxlength="8" class="input-dark w-full p-3 rounded-xl" placeholder="Ej: 25888999">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Nombres *</label>
                                <input type="text" id="nombres" name="nombres" 
                                       data-validar="requerido|letras" data-nombre="Nombres" data-min="2" data-max="50"
                                       maxlength="50" class="input-dark w-full p-3 rounded-xl" placeholder="Juan">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" 
                                       data-validar="requerido|letras" data-nombre="Apellidos" data-min="2" data-max="50"
                                       maxlength="50" class="input-dark w-full p-3 rounded-xl" placeholder="Pérez">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Fecha Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                                   data-validar="requerido|mayor18" data-nombre="Fecha de Nacimiento" 
                                   class="input-dark w-full p-3 rounded-xl">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Género *</label>
                            <select name="genero" id="genero" data-validar="requerido" data-nombre="Género" class="input-dark w-full p-3 rounded-xl">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-tighter">Datos de Contacto</p>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Teléfono *</label>
                            <input type="text" id="telefono" name="telefono" 
                                   data-validar="requerido|numeros" data-nombre="Teléfono" data-min="11" data-max="11"
                                   maxlength="11" class="input-dark w-full p-3 rounded-xl" placeholder="04121234567">
                        </div>

                  <div class="space-y-1">
                  <label class="text-[11px] text-gray-500 font-bold ml-1">Correo Electrónico *</label>
                 <input type="email" id="correo" name="correo" 
                 data-validar="requerido|email" data-nombre="Correo Electrónico"
                class="input-dark w-full p-3 rounded-xl" placeholder="ejemplo@correo.com">
             </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">Dirección *</label>
                            <textarea id="direccion" name="direccion" rows="4" 
                                      data-validar="requerido" data-nombre="Dirección" data-min="5" data-max="50"
                                      maxlength="50" class="input-dark w-full p-3 rounded-xl resize-none" placeholder="Calle principal #123"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalEntrenador()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerEntrenador" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl overflow-y-auto max-h-[90vh] scale-95 opacity-0 transition-all duration-200">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-circle text-indigo-500"></i>
                    <span id="verNombreCompleto">Perfil del Entrenador</span>
                </h2>
                <button type="button" onclick="cerrarModalVer()" class="text-gray-500 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 flex flex-col items-center">
                    <div class="relative w-32 h-32 rounded-full bg-[#1c1a3a] border-2 border-indigo-500/30 overflow-hidden flex items-center justify-center">
                        <img id="verFoto" src="" alt="Foto" class="w-full h-full object-cover hidden">
                        <i id="verIconoPorDefecto" class="fas fa-user text-5xl text-gray-600"></i>
                    </div>
                    <span class="text-xs text-gray-500 mt-2">Entrenador</span>
                </div>

                <div class="md:col-span-2 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Cédula</p>
                            <p id="verCedula" class="text-white font-mono text-sm">---</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Género</p>
                            <p id="verGenero" class="text-white text-sm">---</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Fecha de Nacimiento</p>
                        <p id="verFechaNac" class="text-white text-sm">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Teléfono</p>
                        <p id="verTelefono" class="text-white text-sm">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Correo Electrónico</p>
                        <p id="verCorreo" class="text-white text-sm break-all">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Dirección</p>
                        <p id="verDireccion" class="text-white text-sm">---</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-800 flex justify-end">
                <button type="button" onclick="cerrarModalVer()" class="bg-gray-800 hover:bg-gray-700 text-gray-400 px-8 py-3 rounded-xl font-bold transition">
                    CERRAR <i class="fas fa-times ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/entrenador.js"></script>
</body>
</html>