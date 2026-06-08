<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento y Respaldo | SGRD</title>
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
        /* Efecto drag and drop para el archivo */
        .zona-drop { border: 2px dashed #252345; transition: all 0.3s ease; }
        .zona-drop.dragover { border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05); }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">



        <!-- ======================================================= -->
        <!-- HEADER ESTÁNDAR DEL SISTEMA                             -->
        <!-- ======================================================= -->
        <header class="flex justify-between items-center mb-10">
             
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-server text-indigo-500"></i>  Mantenimiento
            </h1>
            
            <div class="flex items-center gap-6">
                <!-- Notificaciones -->
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>

                <!-- Ayuda -->
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>

                <!-- Perfil -->
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="tarjeta p-8 flex flex-col justify-between shadow-lg shadow-black/20 border-t-4 border-t-emerald-500 relative overflow-hidden">
                <i class="fas fa-database absolute -bottom-10 -right-10 text-9xl text-white opacity-[0.02]"></i>
                
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-emerald-500/20 p-3 rounded-lg text-emerald-400">
                            <i class="fas fa-cloud-download-alt text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-white">Generar Respaldo</h2>
                    </div>
                    
                    <p class="text-sm text-gray-400 mb-6 leading-relaxed">
                        Crea una copia de seguridad empaquetada de ambas bases de datos: 
                        <span class="text-indigo-400 font-mono text-xs">sis_natacion</span> y 
                        <span class="text-indigo-400 font-mono text-xs">sis_seguridad</span>. 
                        Es recomendable realizar este proceso semanalmente.
                    </p>

                    <div class="bg-[#0f0d23] border border-[#252345] rounded-xl p-4 mb-8">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Estado de Conexión</span>
                            <span class="flex items-center gap-1 text-xs text-emerald-400 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Estable
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 font-mono">Último respaldo: <span id="txtUltimoRespaldo">Consultando...</span></div>
                    </div>
                </div>

                <button type="button" onclick="Mantenimiento.generarRespaldo()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-4 rounded-xl transition duration-300 shadow-lg shadow-emerald-500/20 uppercase text-xs tracking-widest flex items-center justify-center gap-3">
                    <i class="fas fa-download text-lg"></i> Iniciar Respaldo Completo
                </button>
            </div>

            <div class="tarjeta p-8 flex flex-col justify-between shadow-lg shadow-black/20 border-t-4 border-t-red-500 relative overflow-hidden">
                <i class="fas fa-exclamation-triangle absolute -bottom-10 -right-10 text-9xl text-white opacity-[0.02]"></i>
                
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-red-500/20 p-3 rounded-lg text-red-400">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-white">Restaurar Sistema</h2>
                    </div>
                    
                    <p class="text-sm text-red-400/80 mb-6 leading-relaxed font-medium">
                        <i class="fas fa-engine-warning mr-1"></i> Precaución: La restauración sobrescribirá los datos actuales del sistema con la información contenida en el archivo de respaldo.
                    </p>

                    <form id="formRestaurar" class="mb-8">
                        <div id="zonaDrop" class="zona-drop bg-[#0f0d23] rounded-xl p-6 text-center cursor-pointer group hover:border-red-500 transition-colors">
                            <input type="file" id="archivoRespaldo" name="archivoRespaldo" accept=".sql,.zip" class="hidden" onchange="Mantenimiento.archivoSeleccionado(this)">
                            
                            <div id="infoArchivo" class="flex flex-col items-center justify-center pointer-events-none">
                                <i class="fas fa-file-upload text-3xl text-gray-600 group-hover:text-red-400 transition-colors mb-3"></i>
                                <span class="text-sm text-gray-300 font-bold">Haz clic o arrastra el archivo aquí</span>
                                <span class="text-xs text-gray-500 mt-1">Formatos soportados: .sql o .zip</span>
                            </div>
                            
                            <div id="archivoCargado" class="hidden flex flex-col items-center justify-center">
                                <i class="fas fa-file-archive text-3xl text-red-400 mb-2"></i>
                                <span id="nombreArchivoTxt" class="text-sm text-white font-mono font-bold"></span>
                                <span class="text-xs text-blue-400 mt-2 cursor-pointer pointer-events-auto hover:underline" onclick="Mantenimiento.limpiarArchivo(event)">Cambiar archivo</span>
                            </div>
                        </div>
                    </form>
                </div>

                <button type="button" onclick="Mantenimiento.iniciarRestauracion()" id="btnRestaurar" class="w-full bg-red-600/50 text-white/50 cursor-not-allowed font-bold py-4 rounded-xl transition duration-300 uppercase text-xs tracking-widest flex items-center justify-center gap-3">
                    <i class="fas fa-upload text-lg"></i> Ejecutar Restauración
                </button>
            </div>

        </div>
    </main>

    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/mantenimiento.js"></script>
</body>
</html>