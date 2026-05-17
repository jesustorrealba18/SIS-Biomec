<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Representantes | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Mismos estilos base de tus compañeros */
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

    <!-- Menú Lateral -->
    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">


        <header class="flex justify-between items-center mb-20">
            <h1 class="text-2xl font-bold text-white">Gestión de Representantes</h1>
            <div class="flex items-center gap-6">

                <!-- Botón Notificaciones -->
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>

                <!-- Botón Guía de Ayuda -->
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>

                <!-- Perfil y Botón de Salida -->
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
        
        <!-- Barra de Control -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-users"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Directorio Familiar</span>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaCedula" placeholder="Buscar por cédula..." 
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <button onclick="abrirModalRepresentante()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Representante
                </button>
            </div>
        </div>

        <!-- Tabla de Datos (Estructura visual idéntica a la tuya)[cite: 19] -->
        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tablaRepresentantes">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Representante</th>
                            <th class="p-4">Cédula</th>
                            <th class="p-4">Teléfono</th>
                            <th class="p-4">Parentesco</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaRepresentantes">
                        <!-- Aquí se inyectarán las filas con JS (Fetch) o PHP -->
                        <tr>
                            <td colspan="5" class="text-center p-4 text-gray-500">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL DE REGISTRO (CON SELECCIÓN MULTIPLE DE ATLETAS) -->
   <div id="modalRepresentante" class="fixed inset-0 bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-id-card text-indigo-500"></i> Ficha del Representante
                </h2>
                <button onclick="cerrarModalRepresentante()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="formRepresentante" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-tighter">Información de Identidad</p>
                        
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">CÉDULA</label>
                            <input type="text" id="cedula" name="cedula" required class="input-dark w-full p-3 rounded-xl" placeholder="Ej: 25888999">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">NOMBRES</label>
                                <input type="text" id="nombres" name="nombres" required class="input-dark w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">APELLIDOS</label>
                                <input type="text" id="apellidos" name="apellidos" required class="input-dark w-full p-3 rounded-xl">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">TLF. PRINCIPAL</label>
                                <input type="text" id="telefono_principal" name="telefono_principal" required class="input-dark w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 font-bold ml-1">TLF. EMERGENCIA</label>
                                <input type="text" id="telefono_emergencia" name="telefono_emergencia" class="input-dark w-full p-3 rounded-xl">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">CORREO ELECTRÓNICO</label>
                            <input type="email" id="correo" name="correo" class="input-dark w-full p-3 rounded-xl">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-tighter">Localización y Parentesco</p>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">PARENTESCO CON EL ATLETA</label>
                            <select id="parentesco" name="parentesco" class="input-dark w-full p-3 rounded-xl">
                                <option value="Padre/Madre">Padre / Madre</option>
                                <option value="Tío/a">Tío / Tía</option>
                                <option value="Abuelo/a">Abuelo / Abuela</option>
                                <option value="Representante Legal">Representante Legal</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">DIRECCIÓN DE RESIDENCIA</label>
                            <textarea id="direccion_residencia" name="direccion_residencia" rows="2" class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] text-gray-500 font-bold ml-1">SELECCIONAR ATLETAS A CARGO</label>
                            <div class="input-dark rounded-xl h-32 overflow-y-auto p-2 scroll-custom space-y-1" id="contenedorCheckboxes">
                                <div class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="atletas[]" value="1" class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-indigo-600">
                                    <span class="text-xs text-gray-300">Cargando lista de atletas...</span>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-600 mt-1">* Se muestran atletas que aún no tienen representante asignado.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="button" onclick="cerrarModalRepresentante()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold hover:bg-gray-700 transition">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20">
                        GUARDAR Y ASOCIAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Fin del modal -->

    <!-- Referencia a utilidades globales -->
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/representante.js"></script>
</body>
</html>