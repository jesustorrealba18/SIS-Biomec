<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenador | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .sidebar { background-color: #161430; width: 260px; border-right: 1px solid #252345; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-20">
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
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-user-tie"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Módulo de Control de Entrenadores</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaCedula" placeholder="Buscar por número de cédula..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <button onclick="abrirModalCrear()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Entrenador
                </button>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-white/5">
                <h3 class="text-white font-semibold">Listado General</h3>
                <span class="text-xs bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full border border-indigo-500/20">
                    <?php echo count($entrenador); ?> Registrados
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Entrenador</th>
                            <th class="p-4">Cédula</th>
                            <th class="p-4">Edad</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800">
                        <?php foreach($entrenador as $a): ?>
                        <tr class="hover:bg-white/5 transition-colors group entrenador-row" data-cedula="<?php echo $a['cedula']; ?>">
                            <td class="p-4 flex items-center gap-3">
                                <div class="bg-indigo-500/10 p-2 rounded-lg text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                    <i class="fas fa-user"></i>
                                </div>
                                <p class="text-white font-medium"><?php echo $a['nombres']." ".$a['apellidos']; ?></p>
                            </td>
                            <td class="p-4 font-mono"><?php echo $a['cedula']; ?></td>
                            <td class="p-4 text-white"><?php echo $a['edad']; ?> años</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick='verDetalles(<?php echo json_encode($a); ?>)' class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all" title="Ver Perfil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick='editarEntrenador(<?php echo json_encode($a); ?>)' class="w-9 h-9 rounded-xl flex items-center justify-center bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white transition-all" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmarEliminar(<?php echo $a['id_entrenador']; ?>)" class="w-9 h-9 rounded-xl flex items-center justify-center bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalEntrenador" class="fixed inset-0 bg-[#0f0d23]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-2xl p-8 shadow-2xl transform transition-all scale-95 animate-in zoom-in duration-300">
            <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-user-tie" id="modalIcon"></i></div>
                    <h2 class="text-xl font-bold text-white" id="modalTitulo">Registrar Entrenador</h2>
                </div>
                <button onclick="cerrarModal()" class="text-gray-500 hover:text-white transition-colors"><i class="fas fa-times text-2xl"></i></button>
            </div>

            <form action="?p=entrenador" method="POST" id="formEntrenador">
                <input type="hidden" name="id_entrenador" id="id_entrenador">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Cédula</label>
                        <input type="text" name="cedula" id="cedula" required class="input-dark w-full p-3 rounded-xl">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Nombres</label>
                        <input type="text" name="nombres" id="nombres" required class="input-dark w-full p-3 rounded-xl">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" required class="input-dark w-full p-3 rounded-xl">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required class="input-dark w-full p-3 rounded-xl">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Género</label>
                        <select name="genero" id="genero" class="input-dark w-full p-3 rounded-xl">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModal()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 py-4 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 active:scale-95">GUARDAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] transform transition-all">
            
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>

            <button onclick="cerrarModalVer()" class="absolute top-8 right-8 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-10">
                <i class="fas fa-times text-xl"></i>
            </button>

            <div id="detalleContenido" class="relative p-10">
            </div>
        </div>
    </div>

    <script>
        const MENSAJE_PHP = "<?php echo isset($mensaje) ? $mensaje : ''; ?>";
    </script>
    <script src="assets/js/entrenador.js"></script>
</body>
</html>